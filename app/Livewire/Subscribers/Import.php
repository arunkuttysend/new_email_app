<?php

namespace App\Livewire\Subscribers;

use App\Models\ListField;
use App\Models\MailingList;
use App\Models\Subscriber;
use App\Models\SubscriberFieldValue;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Import extends Component
{
    use WithFileUploads;

    public $mailing_list_id;
    public $file;
    public $locked = false;
    
    // Wizard Steps
    public $step = 1;

    // Mapping Data
    public $csvHeaders = [];
    public $csvSampleRow = [];
    public $fieldMapping = []; // csv_header => system_field_or_tag
    public $tempFilePath;

    public $importStats = [];
    public $importing = false;

    // Available System Fields
    protected $systemFields = [
        'email' => 'Email Address',
    ];

    protected $rules = [
        'mailing_list_id' => 'required|exists:mailing_lists,id',
        'file' => 'required|file|mimes:csv,txt|max:10240',
    ];

    public function mount()
    {
        if (request()->has('list_id')) {
            $this->mailing_list_id = request('list_id');
            $this->locked = true;
        }
    }

    public function parseFile()
    {
        $this->validate();

        $path = $this->file->store('imports');
        $this->tempFilePath = $path;

        $handle = fopen(Storage::path($path), 'r');
        $this->csvHeaders = fgetcsv($handle);
        $this->csvSampleRow = fgetcsv($handle); // Get first row as sample
        fclose($handle);

        // Auto-guess mapping
        foreach ($this->csvHeaders as $index => $header) {
            $header = trim($header);
            $normalized = strtolower($header);

            if ($normalized === 'email' || $normalized === 'e-mail' || $normalized === 'email address') {
                $this->fieldMapping[$index] = 'email';
            } else {
                // Default to 'create_new' or guess a tag name
                // We'll store the 'tag' name here, or a special value 'new_field'
                // For now, let's default to not mapped if unknown
                $this->fieldMapping[$index] = '';
            }
        }

        $this->step = 2;
    }

    public function processImport()
    {
        // Validation: Verify 'email' is mapped
        if (!in_array('email', $this->fieldMapping)) {
            $this->addError('mapping', 'You must map one column to the Email Address field.');
            return;
        }

        $this->importing = true;
        
        $handle = fopen(Storage::path($this->tempFilePath), 'r');
        fgetcsv($handle); // Skip header

        // Prepare Custom Fields logic
        // Identify new tags to create
        $newTags = [];
        $existingTags = ListField::where('mailing_list_id', $this->mailing_list_id)->pluck('id', 'tag')->toArray();

        foreach ($this->fieldMapping as $colIndex => $mapValue) {
            if ($mapValue === 'email' || empty($mapValue) || $mapValue === 'skip') continue;

            // If mapValue starts with 'NEW:', it's a request to a create a new tag
            if (str_starts_with($mapValue, 'NEW:')) {
                $tagName = substr($mapValue, 4); // Remove "NEW:" prefix
                $tagKey = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $tagName));
                
                // create or get field
                if (!isset($existingTags[$tagKey])) {
                    $field = ListField::create([
                        'mailing_list_id' => $this->mailing_list_id,
                        'label' => $tagName,
                        'tag' => $tagKey, // e.g., FNAME
                        'type' => 'text',
                    ]);
                    $existingTags[$tagKey] = $field->id;
                }
                
                // Update mapping to use the field ID for processing
                $this->fieldMapping[$colIndex] = 'FIELD:' . $existingTags[$tagKey]; // Mark as FIELD:UUID
            } elseif (str_starts_with($mapValue, 'EXISTING:')) {
                // Already mapped to existing field ID
                $this->fieldMapping[$colIndex] = 'FIELD:' . substr($mapValue, 9);
            }
        }

        $successCount = 0;
        $skipCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $email = null;
            $fieldValues = [];

            foreach ($this->fieldMapping as $index => $mapType) {
                if (!isset($row[$index])) continue;
                $value = trim($row[$index]);
                
                if ($mapType === 'email') {
                    $email = $value;
                } elseif (str_starts_with($mapType, 'FIELD:')) {
                    $fieldId = substr($mapType, 6);
                    $fieldValues[$fieldId] = $value;
                }
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipCount++;
                continue;
            }

            // Check duplicate
            $subscriber = Subscriber::firstOrCreate(
                [
                    'mailing_list_id' => $this->mailing_list_id, 
                    'email' => $email
                ],
                [
                    'status' => 'confirmed',
                    'source' => 'import',
                    'subscribed_at' => now(),
                ]
            );
            
            // If strictly firstOrCreate was 'created', we count success.
            // If it existed, we might update fields? Let's update fields anyway.
            
            // Save Field Values
            foreach ($fieldValues as $fieldId => $val) {
                if ($val !== '') {
                    SubscriberFieldValue::updateOrCreate(
                        [
                            'subscriber_id' => $subscriber->id,
                            'list_field_id' => $fieldId,
                        ],
                        ['value' => $val]
                    );
                }
            }

            $successCount++;
        }

        fclose($handle);
        // Clean up temp file
        Storage::delete($this->tempFilePath);

        $this->importing = false;
        $this->importStats = [
            'success' => $successCount,
            'skipped' => $skipCount,
        ];
        
        $this->step = 3; // Finish step
    }

    public function render()
    {
        $mailingLists = MailingList::pluck('name', 'id');
        
        // Get existing fields for the selected list to offer as mapping options
        $existingFields = [];
        if ($this->mailing_list_id) {
            $existingFields = ListField::where('mailing_list_id', $this->mailing_list_id)->get();
        }

        return view('livewire.subscribers.import', [
            'mailingLists' => $mailingLists,
            'existingFields' => $existingFields,
        ]);
    }
}
