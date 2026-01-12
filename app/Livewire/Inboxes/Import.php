<?php

namespace App\Livewire\Inboxes;

use App\Models\DeliveryServer;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Import extends Component
{
    use WithFileUploads;

    public $file;
    public $step = 1;
    public $csvHeaders = [];
    public $csvSampleRow = [];
    public $mapping = [];
    public $tempFilePath;
    
    // Stats
    public $successCount = 0;
    public $errorCount = 0;

    // Standard inbox fields to map to
    public $inboxFields = [
        'name' => 'Inbox Name',
        'from_name' => 'From Name',
        'from_email' => 'From Email',
        'smtp_host' => 'SMTP Host',
        'smtp_port' => 'SMTP Port',
        'smtp_username' => 'SMTP Username',
        'smtp_password' => 'SMTP Password',
        'smtp_encryption' => 'SMTP Encryption',
        'imap_host' => 'IMAP Host',
        'imap_port' => 'IMAP Port',
        'imap_username' => 'IMAP Username',
        'imap_password' => 'IMAP Password',
        'imap_encryption' => 'IMAP Encryption',
    ];

    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
    ];

    public function updatedFile()
    {
        $this->validate();
        $this->parseFile();
    }

    public function parseFile()
    {
        $path = $this->file->store('imports');
        $this->tempFilePath = $path;

        $handle = fopen(Storage::path($path), 'r');
        if ($handle !== false) {
            $this->csvHeaders = fgetcsv($handle);
            $this->csvSampleRow = fgetcsv($handle);
            fclose($handle);

            // Auto-map logic
            foreach ($this->csvHeaders as $index => $header) {
                $header = strtolower(trim($header));
                // Try to find a match in inboxFields keys
                foreach (array_keys($this->inboxFields) as $fieldKey) {
                   // Simple heuristic: if header contains the field key (e.g. 'smtp_host' or 'smtp host')
                   // or logic like 'email' -> 'from_email'
                   if ($header === $fieldKey || str_replace('_', ' ', $fieldKey) === $header) {
                       $this->mapping[$index] = $fieldKey;
                       break;
                   }
                   
                   // Special cases
                   if ($fieldKey === 'from_email' && ($header === 'email' || $header === 'email address')) {
                       $this->mapping[$index] = $fieldKey;
                       break;
                   }
                   if ($fieldKey === 'from_name' && ($header === 'name' || $header === 'sender name')) {
                       $this->mapping[$index] = $fieldKey;
                       break;
                   }
                   if ($fieldKey === 'name' && ($header === 'name' || $header === 'account name')) {
                       // If from_name is already mapped, prioritize from_name for 'name' header? 
                       // Actually let's defaults to Name = From Name if ambiguous
                       $this->mapping[$index] = $fieldKey;
                       break;
                   }
                   if ($fieldKey === 'smtp_password' && ($header === 'password' || $header === 'pass')) {
                       // Dangerous Assumption, but for 'smtp', maybe check context?
                       // Assuming 'smtp_password' is safer if header allows.
                   }
                }
            }

            $this->step = 2;
        }
    }

    public function processImport()
    {
        if (!$this->tempFilePath) {
            return;
        }

        $handle = fopen(Storage::path($this->tempFilePath), 'r');
        if ($handle === false) {
            return;
        }

        // Skip headers
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $data = [];
            foreach ($this->mapping as $columnIndex => $fieldKey) {
                if (isset($row[$columnIndex]) && $fieldKey !== 'ignore') {
                    $data[$fieldKey] = trim($row[$columnIndex]);
                }
            }

            if (!empty($data['from_email']) && !empty($data['smtp_host'])) {
                 try {
                    $this->createInbox($data);
                    $this->successCount++;
                 } catch (\Exception $e) {
                     $this->errorCount++;
                 }
            } else {
                $this->errorCount++;
            }
        }

        fclose($handle);
        Storage::delete($this->tempFilePath);
        $this->step = 3;
    }

    protected function createInbox($data)
    {
        // Defaults
        $smtpPort = $data['smtp_port'] ?? 587;
        $smtpEnc = $data['smtp_encryption'] ?? 'tls';
        $imapPort = $data['imap_port'] ?? 993;
        $imapEnc = $data['imap_encryption'] ?? 'ssl';
        
        // Smart name generation if missing
        $name = $data['name'] ?? ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '');
        $name = trim($name) ?: $data['from_email'];
        
        $fromName = $data['from_name'] ?? ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '');
        $fromName = trim($fromName) ?: $name;

        $credentials = [
            'username' => $data['smtp_username'] ?? $data['from_email'], // Fallback to email as username
            'password' => $data['smtp_password'] ?? '',
            'host' => $data['smtp_host'],
            'port' => $smtpPort,
            'encryption' => $smtpEnc,
            
            'imap' => [
                'username' => $data['imap_username'] ?? ($data['smtp_username'] ?? $data['from_email']),
                'password' => $data['imap_password'] ?? ($data['smtp_password'] ?? ''),
                'host' => $data['imap_host'] ?? '',
                'port' => $imapPort,
                'encryption' => $imapEnc,
            ]
        ];

        DeliveryServer::create([
            'name' => $name,
            'type' => 'smtp',
            'from_name' => $fromName,
            'from_email' => $data['from_email'],
            'reply_to' => null,
            'credentials' => $credentials,
            'quotas' => [
                'daily' => 50, // Default warm up limit
                'hourly' => 5,
            ],
            'status' => 'active',
        ]);
    }

    public function render()
    {
        return view('livewire.inboxes.import');
    }
}
