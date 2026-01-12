<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Success!</h5>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">All Campaigns</h3>
                        <div class="card-tools">
                            <a href="{{ route('campaigns.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> New Campaign
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search Campaigns...">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Name</th>
                                        <th>Audience</th>
                                        <th>Stats</th>
                                        <th>Created At</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($campaigns as $campaign)
                                        <tr>
                                            <td>
                                                @if($campaign->status === 'draft')
                                                    <span class="badge badge-secondary">Draft</span>
                                                @elseif($campaign->status === 'scheduled')
                                                    <span class="badge badge-info">Scheduled</span>
                                                @elseif($campaign->status === 'running')
                                                    <span class="badge badge-primary">Running</span>
                                                @elseif($campaign->status === 'paused')
                                                    <span class="badge badge-warning">Paused</span>
                                                @elseif($campaign->status === 'completed')
                                                    <span class="badge badge-success">Completed</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $campaign->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="font-weight-bold">{{ $campaign->name }}</span>
                                                <br>
                                                <small class="text-muted">{{ $campaign->subject }}</small>
                                            </td>
                                            <td>
                                                @if($campaign->mailingList)
                                                    <a href="{{ route('subscribers.index', ['list_id' => $campaign->mailing_list_id]) }}">
                                                        {{ $campaign->mailingList->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">No List</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    {{-- Placeholder for real stats --}}
                                                    <small>Sent: 0</small>
                                                    <small>Open Rate: 0%</small>
                                                    <small>Reply Rate: 0%</small>
                                                </div>
                                            </td>
                                            <td>{{ $campaign->created_at->format('M d, Y') }}</td>
                                            <td class="text-right">
                                                <a href="{{ route('campaigns.show', $campaign->id) }}" class="btn btn-default btn-xs mr-1">
                                                    <i class="fas fa-chart-bar"></i> Report
                                                </a>
                                                <a href="{{ route('campaigns.edit', $campaign->id) }}" class="btn btn-info btn-xs mr-1">
                                                    <i class="fas fa-pencil-alt"></i> Edit
                                                </a>
                                                <button wire:click="delete('{{ $campaign->id }}')" 
                                                        class="btn btn-danger btn-xs"
                                                        onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-paper-plane fa-3x mb-3"></i>
                                                    <p>No campaigns found.</p>
                                                    <a href="{{ route('campaigns.create') }}" class="btn btn-primary btn-sm">
                                                        Create your first campaign
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card-footer clearfix">
                        {{ $campaigns->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
