<div>
    <div class="row mb-3">
        <div class="col-md-6">
            <h3><i class="fas fa-exclamation-triangle"></i> Bounce Logs</h3>
        </div>
        <div class="col-md-6 text-right">
            <button wire:click="$refresh" class="btn btn-secondary">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>
    
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    
    {{-- Stats Cards --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Bounces</p>
                </div>
                <div class="icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['hard'] }}</h3>
                    <p>Hard Bounces</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['soft'] }}</h3>
                    <p>Soft Bounces</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['today'] }}</h3>
                    <p>Today</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search email or reason...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="bounceTypeFilter" class="form-control">
                        <option value="">All Types</option>
                        <option value="hard">Hard Bounce</option>
                        <option value="soft">Soft Bounce</option>
                        <option value="block">Blocked</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="dateFilter" class="form-control">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">Last 7 Days</option>
                        <option value="month">Last 30 Days</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Bounce Table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Campaign</th>
                            <th>Reason</th>
                            <th>Code</th>
                            <th>Bounced At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bounces as $bounce)
                            <tr>
                                <td>
                                    <strong>{{ $bounce->email }}</strong>
                                    @if($bounce->subscriber)
                                        <br><small class="text-muted">{{ $bounce->subscriber->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $bounce->badge_color }}">
                                        {{ strtoupper($bounce->bounce_type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($bounce->campaign)
                                        <a href="{{ route('campaigns.show', $bounce->campaign_id) }}">
                                            {{ $bounce->campaign->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small title="{{ $bounce->bounce_reason }}">
                                        {{ Str::limit($bounce->bounce_reason, 50) }}
                                    </small>
                                </td>
                                <td>
                                    @if($bounce->smtp_code)
                                        <code>{{ $bounce->smtp_code }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $bounce->bounced_at->format('M d, Y H:i') }}
                                        <br>{{ $bounce->bounced_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <button wire:click="deleteBounce('{{ $bounce->id }}')" 
                                            wire:confirm="Are you sure you want to delete this bounce log?"
                                            class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i>
                                    No bounce logs found - Great!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $bounces->links() }}
            </div>
        </div>
    </div>
    
    {{-- Info Box --}}
    <div class="alert alert-info mt-3">
        <h5><i class="icon fas fa-info"></i> About Bounce Collection</h5>
        <p class="mb-0">
            <strong>Hard Bounces:</strong> Permanent delivery failures (invalid email, domain doesn't exist). Subscribers are automatically marked as "bounced".<br>
            <strong>Soft Bounces:</strong> Temporary failures (mailbox full, server busy). Retried automatically.<br>
            To collect bounces automatically, run: <code>php artisan bounces:collect</code>
        </p>
    </div>
</div>
