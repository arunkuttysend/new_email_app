<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\DeliveryServer;
use App\Models\MailingList;
use Livewire\Component;

class Create extends Component
{
    public $step = 1;

    // Step 1: Settings
    public $name;
    public $subject; // Initial subject for first email or campaign context
    public $from_name;
    public $from_email;
    public $reply_to;
    public $delivery_server_id; // For simplicity, select one or rotating pool? Let's start with single or many. 
    // Plan: Select multiple inboxes for rotation? Or just one? Smartlead uses "Sender Accounts".
    // Let's allow selecting multiple DeliveryServers.
    public $selected_inboxes = []; 
    public $search_inbox = '';

    // Step 2: Audience
    public $mailing_list_id;
    public $segment_id;

    // Step 3: Sequence (Data structure for steps)
    public $steps = []; 

    // Step 4: Schedule
    public $start_date;
    public $start_time;
    public $daily_limit = 50; 
    public $test_email; // For sending test emails 

    public function mount()
    {
        // Init first step (Initial Email)
        $this->steps = [
            [
                'step_number' => 1,
                'subject' => '',
                'content' => '',
                'wait_days' => 0, // Ignored for step 1
                'trigger_condition' => 'always', // Default for step 1
            ]
        ];
    }

    public function updatedSelectedInboxes()
    {
        // Auto-fill From Name and Email from the first selected inbox
        if (!empty($this->selected_inboxes)) {
            // Livewire binds checkboxes as an array of values.
            // Reset numerical keys or get first value
            $ids = array_values($this->selected_inboxes);
            $firstId = $ids[0] ?? null;

            if ($firstId) {
                $server = DeliveryServer::find($firstId);
                
                if ($server) {
                    $this->from_name = $server->from_name ?? $server->name;
                    $this->from_email = $server->from_email;
                    
                    // Optional: Validation feedback logic if needed
                    // session()->flash('info', 'Sender details updated from ' . $server->name);
                }
            }
        }
    }

    public $availableTags = [
        '{first_name}', '{last_name}', '{email}', '{company}', '{unsubscribe_url}'
    ];

    public function updatedMailingListId($value)
    {
        $this->updateAvailableTags($value);
    }

    public function updateAvailableTags($listId)
    {
        $defaultTags = ['{first_name}', '{last_name}', '{email}', '{company}', '{unsubscribe_url}'];
        
        if (!$listId) {
            $this->availableTags = $defaultTags;
            return;
        }

        $list = MailingList::find($listId);
        if ($list) {
            $customTags = $list->fields()->pluck('tag')->map(function($tag) {
                return '{' . $tag . '}';
            })->toArray();
            
            $this->availableTags = array_merge($defaultTags, $customTags);
        } else {
            $this->availableTags = $defaultTags;
        }
    }

    public function nextStep()
    {
        $this->validateStep();
        $this->step++;
    }

    public function prevStep()
    {
        $this->step--;
    }

    public function validateStep()
    {
        if ($this->step === 1) {
            $this->validate([
                'name' => 'required|string|max:255',
                'from_name' => 'required|string|max:255',
                'from_email' => 'required|email',
                'selected_inboxes' => 'required|array|min:1',
            ], [
                'selected_inboxes.required' => 'Please select at least one sender account.',
                'selected_inboxes.min' => 'Please select at least one sender account.',
            ]);
        }
        if ($this->step === 2) {
            $this->validate([
                'mailing_list_id' => 'required|exists:mailing_lists,id',
            ]);
        }
        // Step 3 validation (Sequence)
        if ($this->step === 3) {
            $this->validate([
                'steps' => 'required|array|min:1',
                'steps.*.subject' => 'required|string|max:255',
                'steps.*.content' => 'required|string',
                'steps.*.wait_days' => 'required|integer|min:0',
            ]);
        }
    }

    public function addStep()
    {
        $this->steps[] = [
            'step_number' => count($this->steps) + 1,
            'subject' => '',
            'content' => '',
            'wait_days' => 3, // Default form reference
            'trigger_condition' => 'no_reply', // Default for follow-ups
        ];
    }

    public function removeStep($index)
    {
        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);
    }

    public function sendTestEmail()
    {
        // Validate test email address
        $this->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Get first selected inbox for sending
            if (empty($this->selected_inboxes)) {
                session()->flash('error', 'Please select at least one sender account first.');
                return;
            }

            $ids = array_values($this->selected_inboxes);
            $inbox = DeliveryServer::find($ids[0]);
            
            if (!$inbox) {
                session()->flash('error', 'Selected inbox not found.');
                return;
            }

            // Create a dummy subscriber for testing
            $testSubscriber = new \App\Models\Subscriber([
                'email' => $this->test_email,
            ]);
            
            // Set test field values for personalization preview
            $testSubscriber->setRelation('fieldValues', collect([
                (object)['listField' => (object)['tag' => 'first_name'], 'value' => 'John'],
                (object)['listField' => (object)['tag' => 'last_name'], 'value' => 'Doe'],
                (object)['listField' => (object)['tag' => 'company'], 'value' => 'Acme Corp'],
            ]));

            // Send test email for first step
            $mailerService = app(\App\Services\Mail\MailerService::class);
            
            $emailData = [
                'subject' => '[TEST] ' . ($this->steps[0]['subject'] ?? 'No Subject'),
                'html_content' => $this->steps[0]['content'] ?? '',
                'from_name' => $this->from_name,
                'from_email' => $this->from_email,
                'reply_to' => $this->reply_to,
            ];

            $result = $mailerService->send($inbox, $testSubscriber, $emailData);

            if ($result['success']) {
                session()->flash('success', 'Test email sent successfully to ' . $this->test_email . '!');
            } else {
                session()->flash('error', 'Failed to send test email: ' . $result['error']);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error sending test email: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $this->validate([
            'start_date' => 'nullable|date|after_or_equal:today',
            'start_time' => 'nullable', // Time format validation if needed
        ]);

        // 1. Create Campaign
        $campaign = Campaign::create([
            'user_id' => auth()->id() ?? 1, // Fallback for dev if needed
            'mailing_list_id' => $this->mailing_list_id,
            // 'segment_id' => $this->segment_id,
            'name' => $this->name,
            'subject' => $this->steps[0]['subject'] ?? 'No Subject', // Main subject usually from step 1
            'from_name' => $this->from_name,
            'from_email' => $this->from_email,
            'reply_to' => $this->reply_to,
            'status' => ($this->start_date) ? 'scheduled' : 'active', // active means running/queued immediately
            'scheduled_at' => ($this->start_date) 
                ? $this->start_date . ' ' . ($this->start_time ?: '00:00:00') 
                : now(),
            'options' => [
                'daily_limit' => $this->daily_limit,
                'sender_accounts' => $this->selected_inboxes,
                // 'track_opens' => true...
            ],
        ]);

        // 2. Create Email Sequence
        // Campaign hasOne Sequence
        $sequence = $campaign->sequence()->create([
            'name' => 'Default Sequence',
            'status' => 'active',
        ]);

        // 3. Create Steps
        foreach ($this->steps as $index => $stepData) {
            
            // Map wizard condition to model fields
            $trigger = $stepData['trigger_condition'] ?? 'always';
            $condType = null;
            $condOp = 'not'; // Default to match DB default

            if ($trigger === 'no_reply') {
                $condType = 'replied'; // \App\Models\SequenceStep::CONDITION_REPLIED
                $condOp = 'not';
            } elseif ($trigger === 'no_open') {
                $condType = 'opened';
                $condOp = 'not';
            }

            $sequence->steps()->create([
                'step_order' => $index + 1,
                'name' => 'Step ' . ($index + 1),
                'subject' => $stepData['subject'],
                'subject' => $stepData['subject'],
                'html_content' => $stepData['content'],
                'wait_value' => $stepData['wait_days'] ?? 0,
                'wait_unit' => 'days',
                'condition_type' => $condType,
                'condition_operator' => $condOp,
            ]);
        }
        
        // 4. Dispatch Job to initialize campaign
        // Always dispatch immediately - the scheduler will process it at the right time
        \App\Jobs\Campaigns\InitializeCampaignJob::dispatch($campaign);
        
        if ($campaign->status === 'scheduled' && $campaign->scheduled_at) {
            session()->flash('success', 'Campaign scheduled for ' . $campaign->scheduled_at->format('M d, Y h:i A') . '!');
        } else {
            session()->flash('success', 'Campaign created and queued for sending!');
        }

        return redirect()->route('campaigns.index');
    }

    public function render()
    {
        $inboxes = DeliveryServer::active()
            ->when($this->search_inbox, function($q) {
                $q->where('name', 'like', '%'.$this->search_inbox.'%')
                  ->orWhere('from_email', 'like', '%'.$this->search_inbox.'%');
            })
            ->limit(50) // Limit results for performance
            ->get();

        return view('livewire.campaigns.create', [
            'lists' => MailingList::all(),
            'inboxes' => $inboxes,
        ]);
    }
}
