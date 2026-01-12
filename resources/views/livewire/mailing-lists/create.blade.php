<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">General Information</h3>
                    </div>
                    
                    <form wire:submit.prevent="store">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name" placeholder="Internal name for the list">
                                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">The name used internally for your reference.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="display_name">Display Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('display_name') is-invalid @enderror" id="display_name" wire:model="display_name" placeholder="Public name shown to subscribers">
                                        @error('display_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">The name visible to your subscribers.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" wire:model="description" rows="3" placeholder="Description of the list"></textarea>
                                @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="{{ route('lists.index') }}" class="btn btn-default float-right">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
