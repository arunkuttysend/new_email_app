<div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-{{ $userId ? 'edit' : 'plus' }}"></i>
                        {{ $userId ? 'Edit User' : 'Create New User' }}
                    </h3>
                </div>
                
                <form wire:submit="save">
                    <div class="card-body">
                        {{-- Name --}}
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="John Doe">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="john@example.com">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        {{-- Password --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">
                                        Password 
                                        @if($userId)
                                            <small class="text-muted">(leave blank to keep current)</small>
                                        @else
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" id="password">
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <input type="password" wire:model="password_confirmation" class="form-control" id="password_confirmation">
                                </div>
                            </div>
                        </div>
                        
                        {{-- Role --}}
                        <div class="form-group">
                            <label for="role">Role <span class="text-danger">*</span></label>
                            <select wire:model="role" class="form-control @error('role') is-invalid @enderror" id="role">
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="agent">Agent</option>
                                <option value="viewer">Viewer</option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Super Admin:</strong> Full access | 
                                <strong>Admin:</strong> Manage users & campaigns | 
                                <strong>Manager:</strong> Create campaigns | 
                                <strong>Agent:</strong> Send emails | 
                                <strong>Viewer:</strong> Read-only
                            </small>
                        </div>
                        
                        {{-- Phone & Department --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" id="phone" placeholder="+1 234 567 8900">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <input type="text" wire:model="department" class="form-control @error('department') is-invalid @enderror" id="department" placeholder="Sales, Marketing, etc.">
                                    @error('department')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        {{-- Status --}}
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" wire:model="is_active" class="custom-control-input" id="is_active">
                                <label class="custom-control-label" for="is_active">
                                    <strong>Active</strong>
                                    <small class="text-muted d-block">Inactive users cannot login</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save User
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
