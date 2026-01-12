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
                        <h3 class="card-title">All Subscribers</h3>
                        <div class="card-tools">
                            <a href="{{ route('subscribers.import') }}" class="btn btn-default btn-sm mr-1">
                                <i class="fas fa-file-upload"></i> Import CSV
                            </a>
                            <a href="{{ route('subscribers.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Subscriber
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by email...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select wire:model.live="listFilter" class="form-control">
                                        <option value="">All Mailing Lists</option>
                                        @foreach($mailingLists as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select wire:model.live="statusFilter" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="unconfirmed">Unconfirmed</option>
                                        <option value="unsubscribed">Unsubscribed</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Company</th>
                                        <th>Mailing List</th>
                                        <th>Status</th>
                                        <th>Source</th>
                                        <th>Joined At</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($subscribers as $subscriber)
                                        <tr wire:key="{{ $subscriber->id }}">
                                            <td>
                                                <span class="font-weight-bold">{{ $subscriber->email }}</span>
                                            </td>
                                            <td>
                                                {{ $subscriber->fieldValues->first(fn($fv) => $fv->listField->tag === 'first_name')?->value ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $subscriber->fieldValues->first(fn($fv) => $fv->listField->tag === 'last_name')?->value ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $subscriber->fieldValues->first(fn($fv) => $fv->listField->tag === 'company')?->value ?? '-' }}
                                            </td>
                                            <td>
                                                @if($subscriber->mailingList)
                                                    <a href="{{ route('lists.edit', $subscriber->mailingList) }}">
                                                        {{ $subscriber->mailingList->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Deleted List</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($subscriber->status === 'confirmed')
                                                    <span class="badge badge-success">Confirmed</span>
                                                @elseif($subscriber->status === 'unconfirmed')
                                                    <span class="badge badge-warning">Unconfirmed</span>
                                                @elseif($subscriber->status === 'unsubscribed')
                                                    <span class="badge badge-danger">Unsubscribed</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($subscriber->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ ucfirst($subscriber->source) }}</td>
                                            <td>{{ $subscriber->created_at->format('M d, Y H:i') }}</td>
                                            <td class="text-right">
                                                <button type="button" 
                                                        @click="confirm('Are you sure you want to delete this subscriber?') && $wire.delete('{{ $subscriber->id }}')"
                                                        wire:loading.attr="disabled"
                                                        class="btn btn-danger btn-xs">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-users fa-3x mb-3"></i>
                                                    <p>No subscribers found.</p>
                                                    <a href="{{ route('subscribers.create') }}" class="btn btn-primary btn-sm">
                                                        Add your first subscriber
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
                        {{ $subscribers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
