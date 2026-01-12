<?php

namespace App\Http\Controllers;

use App\Models\CampaignClick;
use App\Models\CampaignLink;
use App\Models\CampaignOpen;
use App\Models\CampaignSend;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Track email open
     */
    public function trackOpen(Request $request, CampaignSend $campaignSend)
    {
        // Create open record
        CampaignOpen::create([
            'campaign_id' => $campaignSend->campaign_id,
            'subscriber_id' => $campaignSend->subscriber_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'opened_at' => now(),
        ]);

        // Update campaign stats
        $this->updateCampaignStats($campaignSend->campaign_id, 'opens');

        // Return 1x1 transparent GIF
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($gif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Track link click and redirect
     */
    public function trackClick(Request $request, string $linkHash, CampaignSend $campaignSend)
    {
        $link = CampaignLink::where('hash', $linkHash)
            ->where('campaign_id', $campaignSend->campaign_id)
            ->firstOrFail();

        // Create click record
        CampaignClick::create([
            'campaign_id' => $campaignSend->campaign_id,
            'subscriber_id' => $campaignSend->subscriber_id,
            'campaign_link_id' => $link->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'clicked_at' => now(),
        ]);

        // Update link clicks count
        $link->increment('clicks_count');

        // Update campaign stats
        $this->updateCampaignStats($campaignSend->campaign_id, 'clicks');

        // Redirect to original URL
        return redirect($link->url);
    }

    /**
     * Update campaign statistics
     */
    private function updateCampaignStats(string $campaignId, string $type): void
    {
        $campaign = \App\Models\Campaign::find($campaignId);
        
        if (!$campaign) {
            return;
        }

        $stats = $campaign->stats ?? [];

        if ($type === 'opens') {
            // Count unique opens
            $uniqueOpens = CampaignOpen::where('campaign_id', $campaignId)
                ->distinct('subscriber_id')
                ->count('subscriber_id');
            
            $totalOpens = CampaignOpen::where('campaign_id', $campaignId)->count();

            $stats['unique_opens'] = $uniqueOpens;
            $stats['total_opens'] = $totalOpens;
        } elseif ($type === 'clicks') {
            // Count unique clicks
            $uniqueClicks = CampaignClick::where('campaign_id', $campaignId)
                ->distinct('subscriber_id')
                ->count('subscriber_id');
            
            $totalClicks = CampaignClick::where('campaign_id', $campaignId)->count();

            $stats['unique_clicks'] = $uniqueClicks;
            $stats['total_clicks'] = $totalClicks;
        }

        $campaign->update(['stats' => $stats]);
    }
}
