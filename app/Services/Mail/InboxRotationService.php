<?php

namespace App\Services\Mail;

use App\Models\Campaign;
use App\Models\DeliveryServer;
use Illuminate\Support\Collection;

class InboxRotationService
{
    /**
     * Get next available inbox for sending
     */
    public function getNextAvailableInbox(
        array $inboxIds,
        Campaign $campaign,
        string $strategy = 'round_robin'
    ): ?DeliveryServer {
        // Get all specified inboxes
        $inboxes = DeliveryServer::whereIn('id', $inboxIds)
            ->where('status', 'active')
            ->get();
        
        // Filter by rate limits
        $availableInboxes = $inboxes->filter(function ($inbox) {
            return $this->canSend($inbox);
        });
        
        if ($availableInboxes->isEmpty()) {
            return null;
        }
        
        // Apply rotation strategy
        return match ($strategy) {
            'least_used' => $this->selectLeastUsed($availableInboxes),
            'weighted' => $this->selectWeighted($availableInboxes),
            default => $this->selectRoundRobin($availableInboxes, $campaign),
        };
    }
    
    /**
     * Check if inbox can send (within rate limits)
     */
    public function canSend(DeliveryServer $inbox): bool
    {
        if ($inbox->status !== 'active') {
            return false;
        }
        
        $quotas = $inbox->quotas ?? [];
        $usage = $inbox->current_usage ?? [];
        
        // Check hourly quota
        if (isset($quotas['hourly']) && $quotas['hourly'] > 0) {
            $hourlyUsage = $this->getHourlyUsage($usage);
            if ($hourlyUsage >= $quotas['hourly']) {
                return false;
            }
        }
        
        // Check daily quota
        if (isset($quotas['daily']) && $quotas['daily'] > 0) {
            $dailyUsage = $this->getDailyUsage($usage);
            if ($dailyUsage >= $quotas['daily']) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Update inbox usage after sending
     */
    public function incrementUsage(DeliveryServer $inbox): void
    {
        $usage = $inbox->current_usage ?? [];
        $now = now();
        
        // Initialize if needed
        if (!isset($usage['hourly_reset'])) {
            $usage['hourly_reset'] = $now->copy()->addHour()->toDateTimeString();
            $usage['hourly'] = 0;
        }
        
        if (!isset($usage['daily_reset'])) {
            $usage['daily_reset'] = $now->copy()->addDay()->toDateTimeString();
            $usage['daily'] = 0;
        }
        
        // Reset hourly if needed
        if ($now->greaterThan($usage['hourly_reset'])) {
            $usage['hourly'] = 0;
            $usage['hourly_reset'] = $now->copy()->addHour()->toDateTimeString();
        }
        
        // Reset daily if needed
        if ($now->greaterThan($usage['daily_reset'])) {
            $usage['daily'] = 0;
            $usage['daily_reset'] = $now->copy()->addDay()->toDateTimeString();
        }
        
        // Increment counters
        $usage['hourly'] = ($usage['hourly'] ?? 0) + 1;
        $usage['daily'] = ($usage['daily'] ?? 0) + 1;
        $usage['monthly'] = ($usage['monthly'] ?? 0) + 1;
        $usage['total'] = ($usage['total'] ?? 0) + 1;
        $usage['last_used_at'] = $now->toDateTimeString();
        
        $inbox->update(['current_usage' => $usage]);
    }
    
    /**
     * Round-robin selection
     */
    private function selectRoundRobin(Collection $inboxes, Campaign $campaign): DeliveryServer
    {
        // Get last used inbox from campaign options
        $lastInboxId = $campaign->options['last_inbox_id'] ?? null;
        
        if (!$lastInboxId) {
            // Use first inbox
            $selected = $inboxes->first();
        } else {
            // Find next inbox after last used
            $currentIndex = $inboxes->search(function ($inbox) use ($lastInboxId) {
                return $inbox->id === $lastInboxId;
            });
            
            if ($currentIndex === false) {
                $selected = $inboxes->first();
            } else {
                $nextIndex = ($currentIndex + 1) % $inboxes->count();
                $selected = $inboxes->values()->get($nextIndex);
            }
        }
        
        // Update campaign options with last used inbox
        $options = $campaign->options ?? [];
        $options['last_inbox_id'] = $selected->id;
        $campaign->update(['options' => $options]);
        
        return $selected;
    }
    
    /**
     * Select least used inbox
     */
    private function selectLeastUsed(Collection $inboxes): DeliveryServer
    {
        return $inboxes->sortBy(function ($inbox) {
            return $inbox->current_usage['daily'] ?? 0;
        })->first();
    }
    
    /**
     * Weighted selection (placeholder for future health-based selection)
     */
    private function selectWeighted(Collection $inboxes): DeliveryServer
    {
        // For now, use round-robin
        // TODO: Implement health score-based weighting
        return $inboxes->random();
    }
    
    /**
     * Get hourly usage with reset check
     */
    private function getHourlyUsage(array $usage): int
    {
        if (!isset($usage['hourly_reset'])) {
            return 0;
        }
        
        if (now()->greaterThan($usage['hourly_reset'])) {
            return 0; // Reset occurred
        }
        
        return $usage['hourly'] ?? 0;
    }
    
    /**
     * Get daily usage with reset check
     */
    private function getDailyUsage(array $usage): int
    {
        if (!isset($usage['daily_reset'])) {
            return 0;
        }
        
        if (now()->greaterThan($usage['daily_reset'])) {
            return 0; // Reset occurred
        }
        
        return $usage['daily'] ?? 0;
    }
}
