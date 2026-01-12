<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use Livewire\Component;

class Show extends Component
{
    public Campaign $campaign;
    public array $stats = [];
    public $recentOpens = [];
    public $recentClicks = [];
    public $linkPerformance = [];
    public $sequenceHeaders = [];
    public $sequenceStats = [];
    public $stepMetrics = [];

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->loadStats();
        $this->loadSequenceMetrics();
        $this->loadRecentActivity();
        $this->loadLinkPerformance();
    }
    
    public function loadStats()
    {
        $this->stats = $this->campaign->getStats();
        
        // Add Reply Rate and Bounce Rate
        // Note: Replies are usually tracked via SequenceStepLog or CampaignReply
        // Note: Replies are usually tracked via SequenceStepLog or CampaignReply
        $totalSends = $this->stats['sent'] > 0 ? $this->stats['sent'] : $this->campaign->sends()->count();
        
        $repliesCount = \App\Models\CampaignReply::where('campaign_id', $this->campaign->id)->count();
        $bouncesCount = $this->campaign->sends()->where('status', 'failed')->count(); // For single campaigns
        
        if ($this->campaign->sequence) {
             $bouncesCount += $this->campaign->sequence->stepLogs()->where('status', 'failed')->count();
        }

        $this->stats['reply_rate'] = $totalSends > 0 ? round(($repliesCount / $totalSends) * 100, 1) : 0;
        $this->stats['bounce_rate'] = $totalSends > 0 ? round(($bouncesCount / $totalSends) * 100, 1) : 0;
        $this->stats['replies_count'] = $repliesCount;
    }

    public function loadSequenceMetrics()
    {
        $sequence = \App\Models\EmailSequence::where('campaign_id', $this->campaign->id)->first();

        if (!$sequence) {
            return;
        }

        $steps = $sequence->steps()->orderBy('step_order')->get();
        
        foreach ($steps as $step) {
            $sentCount = $step->logs()->whereIn('status', ['sent', 'opened', 'clicked', 'replied'])->count();
            $openCount = $step->logs()->whereNotNull('opened_at')->count();
            $clickCount = $step->logs()->whereNotNull('clicked_at')->count();
            $replyCount = $step->logs()->whereNotNull('replied_at')->count();

            $this->stepMetrics[] = [
                'name' => $step->name ?? 'Step ' . $step->step_order,
                'subject' => $step->subject,
                'sent' => $sentCount,
                'opens' => $openCount,
                'open_rate' => $sentCount > 0 ? round(($openCount / $sentCount) * 100, 1) : 0,
                'clicks' => $clickCount,
                'click_rate' => $sentCount > 0 ? round(($clickCount / $sentCount) * 100, 1) : 0,
                'replies' => $replyCount,
                'reply_rate' => $sentCount > 0 ? round(($replyCount / $sentCount) * 100, 1) : 0,
            ];
        }

        // Overall Sequence Progress
        $this->sequenceStats = [
            'active' => $sequence->subscriberProgress()->where('status', 'active')->count(),
            'completed' => $sequence->subscriberProgress()->where('status', 'completed')->count(),
            'stopped' => $sequence->subscriberProgress()->where('status', 'stopped')->count(),
            'total' => $sequence->subscriberProgress()->count(),
        ];
    }
    
    public function loadRecentActivity()
    {
        // Get recent 10 opens with subscriber info
        $this->recentOpens = $this->campaign->opens()
            ->with('subscriber')
            ->latest('opened_at')
            ->limit(10)
            ->get();
        
        // Get recent 10 clicks with subscriber and link info
        $this->recentClicks = $this->campaign->clicks()
            ->with(['subscriber', 'link'])
            ->latest('clicked_at')
            ->limit(10)
            ->get();
    }
    
    public function loadLinkPerformance()
    {
        $this->linkPerformance = $this->campaign->links()
            ->where('clicks_count', '>', 0)
            ->orderByDesc('clicks_count')
            ->get();
    }
    
    public function render()
    {
        return view('livewire.campaigns.show');
    }
}
