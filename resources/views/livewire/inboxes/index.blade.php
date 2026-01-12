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
                        <h3 class="card-title">Inboxes (SMTP & IMAP)</h3>
                        <div class="card-tools">
                             <a href="{{ route('inboxes.import') }}" class="btn btn-default btn-sm mr-1">
                                <i class="fas fa-file-upload"></i> Import CSV
                            </a>
                            <a href="{{ route('inboxes.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Inbox
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search Inboxes...">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>From Email</th>
                                        <th>Status</th>
                                        <th>Daily Limit</th>
                                        <th>Usage (Day)</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inboxes as $inbox)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ $inbox->name }}</span>
                                            </td>
                                            <td>{{ $inbox->from_email }}</td>
                                            <td>
                                                @if($inbox->status === 'active')
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($inbox->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $inbox->getDailyQuota() > 0 ? $inbox->getDailyQuota() : 'Unlimited' }}</td>
                                            <td>
                                                @php
                                                    $usage = $inbox->getDailyUsage();
                                                    $limit = $inbox->getDailyQuota();
                                                    $percent = ($limit > 0) ? min(100, round(($usage / $limit) * 100)) : 0;
                                                    $color = 'bg-success';
                                                    if($percent > 80) $color = 'bg-warning';
                                                    if($percent >= 100) $color = 'bg-danger';
                                                @endphp
                                                <div class="progress progress-xs">
                                                    <div class="progress-bar {{ $color }}" style="width: {{ $percent }}%"></div>
                                                </div>
                                                <small>{{ $usage }} sent</small>
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('inboxes.edit', $inbox) }}" class="btn btn-info btn-xs mr-1">
                                                    <i class="fas fa-pencil-alt"></i> Edit
                                                </a>
                                                <button wire:click="delete('{{ $inbox->id }}')" 
                                                        wire:confirm="Are you sure you want to delete this inbox?"
                                                        class="btn btn-danger btn-xs">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-server fa-3x mb-3"></i>
                                                    <p>No inboxes connected yet.</p>
                                                    <a href="{{ route('inboxes.create') }}" class="btn btn-primary btn-sm">
                                                        Connect your first inbox
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
                        {{ $inboxes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
