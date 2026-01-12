<?php

namespace App\Console\Commands;

use App\Jobs\Sequences\SendSequenceStepJob;
use App\Models\SequenceStepLog;
use App\Models\SequenceSubscriberProgress;
use Illuminate\Console\Command;

class ProcessSequencesCommand extends Command
{
    protected $signature = 'sequences:process';
    protected $description = 'Process email sequences and send due emails';

    public function handle()
    {
        $this->info('Processing email sequences...');

        // Find all subscriber progress records ready to send
        $readyToSend = SequenceSubscriberProgress::where('status', 'active')
            ->where('next_send_at', '<=', now())
            ->with(['sequence.steps', 'subscriber'])
            ->get();

        $this->info("Found {$readyToSend->count()} sequences ready to send");

        $processed = 0;

        foreach ($readyToSend as $progress) {
            $sequence = $progress->sequence;
            $currentStepOrder = $progress->current_step_order;

            // Get next step
            $nextStep = $sequence->steps()
                ->where('step_order', $currentStepOrder + 1)
                ->first();

            if (!$nextStep) {
                // No more steps, mark as completed
                $progress->update([
                    'status' => 'completed',
                ]);
                continue;
            }

            // Check step conditions before sending
            if (!$this->shouldSendStep($progress, $nextStep)) {
                $this->warn("Skipping step {$nextStep->step_order} for subscriber {$progress->subscriber->email} (condition not met)");
                
                // Move to next step
                $this->moveToNextStep($progress, $nextStep);
                continue;
            }

            // Dispatch send job
            SendSequenceStepJob::dispatch($progress, $nextStep);
            $processed++;
        }

        $this->info("Queued {$processed} sequence emails for sending");

        return Command::SUCCESS;
    }

    private function shouldSendStep(SequenceSubscriberProgress $progress, $step): bool
    {
        // If no condition, always send
        if (!$step->condition_type) {
            return true;
        }

        // Get the last step log for this subscriber
        $lastLog = SequenceStepLog::where('sequence_id', $progress->sequence_id)
            ->where('subscriber_id', $progress->subscriber_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastLog) {
            return true; // No logs yet, send
        }

        // Check conditions
        if ($step->condition_type === 'replied') {
            if ($step->condition_operator === 'not') {
                return !$lastLog->replied_at; // Send if NOT replied
            } else {
                return (bool) $lastLog->replied_at; // Send if replied
            }
        }

        if ($step->condition_type === 'opened') {
            if ($step->condition_operator === 'not') {
                return !$lastLog->opened_at; // Send if NOT opened
            } else {
                return (bool) $lastLog->opened_at; // Send if opened
            }
        }

        if ($step->condition_type === 'clicked') {
            if ($step->condition_operator === 'not') {
                return !$lastLog->clicked_at; // Send if NOT clicked
            } else {
                return (bool) $lastLog->clicked_at; // Send if clicked
            }
        }

        return true;
    }

    private function moveToNextStep(SequenceSubscriberProgress $progress, $step): void
    {
        // Calculate next send time for the step after this one
        $nextStepAfter = $progress->sequence->steps()
            ->where('step_order', $step->step_order + 1)
            ->first();

        if ($nextStepAfter) {
            $waitValue = $nextStepAfter->wait_value;
            $waitUnit = $nextStepAfter->wait_unit;

            $nextSendAt = match ($waitUnit) {
                'minutes' => now()->addMinutes($waitValue),
                'hours' => now()->addHours($waitValue),
                'days' => now()->addDays($waitValue),
                default => now()->addDays($waitValue),
            };

            $progress->update([
                'current_step_order' => $step->step_order,
                'next_send_at' => $nextSendAt,
            ]);
        } else {
            $progress->update([
                'current_step_order' => $step->step_order,
                'status' => 'completed',
            ]);
        }
    }
}
