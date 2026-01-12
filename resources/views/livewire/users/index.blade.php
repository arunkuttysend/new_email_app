<div>
    <div class="row mb-3">
        <div class="col-md-6">
            <h3><i class="fas fa-users"></i> User Management</h3>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add User
            </a>
        </div>
    </div>
    
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search users...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="roleFilter" class="form-control">
                        <option value="">All Roles</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="agent">Agent</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-control">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    {{-- User Table --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->adminlte_image() }}" class="img-circle mr-2" style="width: 30px; height: 30px;" alt="{{ $user->name }}">
                                    <strong>{{ $user->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-{{ $user->role === 'super_admin' ? 'danger' : ($user->role === 'admin' ? 'warning' : ($user->role === 'manager' ? 'info' : 'secondary')) }}">
                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td>{{ $user->department ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button wire:click="toggleStatus({{ $user->id }})" class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                    @if(auth()->id() !== $user->id)
                                        <button wire:click="deleteUser({{ $user->id }})" 
                                                wire:confirm="Are you sure you want to delete {{ $user->name }}?"
                                                class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
