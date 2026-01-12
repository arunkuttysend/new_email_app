<?php

namespace App\Livewire\Bounces;

use App\Models\BounceLog;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    
    public $search = '';
    public $bounceTypeFilter = '';
    public $dateFilter = '';
    
    protected $queryString = ['search', 'bounceTypeFilter', 'dateFilter'];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function render()
    {
        $bounces = BounceLog::query()
            ->with(['subscriber', 'campaign'])
            ->when($this->search, fn($q) => 
                $q->where('email', 'like', "%{$this->search}%")
                  ->orWhere('bounce_reason', 'like', "%{$this->search}%")
            )
            ->when($this->bounceTypeFilter, fn($q) => 
                $q->where('bounce_type', $this->bounceTypeFilter)
            )
            ->when($this->dateFilter, function($q) {
                $date = match($this->dateFilter) {
                    'today' => now()->startOfDay(),
                    'week' => now()->subWeek(),
                    'month' => now()->subMonth(),
                    default => null,
                };
                
                if ($date) {
                    $q->where('bounced_at', '>=', $date);
                }
            })
            ->latest('bounced_at')
            ->paginate(50);
        
        $stats = [
            'total' => BounceLog::count(),
            'hard' => BounceLog::where('bounce_type', 'hard')->count(),
            'soft' => BounceLog::where('bounce_type', 'soft')->count(),
            'today' => BounceLog::whereDate('bounced_at', today())->count(),
        ];
            
        return view('livewire.bounces.index', [
            'bounces' => $bounces,
            'stats' => $stats,
        ]);
    }
    
    public function deleteBounce($bounceId)
    {
        $bounce = BounceLog::find($bounceId);
        $bounce->delete();
        
        session()->flash('success', 'Bounce log deleted successfully!');
    }
}
