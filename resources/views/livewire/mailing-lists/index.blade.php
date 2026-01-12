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
                        <h3 class="card-title">All Mailing Lists</h3>
                        <div class="card-tools">
                            <a href="{{ route('lists.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Create New
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search lists...">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Display Name</th>
                                        <th>Subscribers</th>
                                        <th>Default From</th>
                                        <th>Created</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($lists as $list)
                                        <tr>
                                            <td>
                                                <a href="{{ route('lists.edit', $list) }}" class="font-weight-bold">
                                                    {{ $list->name }}
                                                </a>
                                                @if($list->description)
                                                    <br><small class="text-muted">{{ Str::limit($list->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $list->display_name }}</td>
                                            <td>
                                                <span class="badge badge-success">{{ number_format($list->subscribers_count) }}</span>
                                            </td>
                                            <td>
                                                @if(isset($list->defaults['from_name']))
                                                    {{ $list->defaults['from_name'] }} <br>
                                                    <small class="text-muted"><{{ $list->defaults['from_email'] ?? '' }}></small>
                                                @endif
                                            </td>
                                            <td>{{ $list->created_at->format('M d, Y') }}</td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    <a href="{{ route('subscribers.index', ['list_id' => $list->id]) }}" class="btn btn-default btn-xs" title="View Subscribers">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                    <a href="{{ route('subscribers.import', ['list_id' => $list->id]) }}" class="btn btn-default btn-xs" title="Import Subscribers">
                                                        <i class="fas fa-file-import"></i>
                                                    </a>
                                                    <a href="{{ route('lists.edit', $list) }}" class="btn btn-info btn-xs" title="Edit List">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <button wire:click="delete('{{ $list->id }}')" 
                                                            wire:confirm="Are you sure you want to delete this list?"
                                                            class="btn btn-danger btn-xs" title="Delete List">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-address-book fa-3x mb-3"></i>
                                                    <p>No mailing lists found.</p>
                                                    <a href="{{ route('lists.create') }}" class="btn btn-primary btn-sm">
                                                        Create your first list
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
                        {{ $lists->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
