<div>
    <form wire:submit.prevent="save">
        <div class="row">
            {{-- General Settings --}}
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> General Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Inbox Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name" placeholder="e.g. CEO Gmail">
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="from_name">From Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('from_name') is-invalid @enderror" id="from_name" wire:model="from_name" placeholder="John Doe">
                            @error('from_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="from_email">From Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('from_email') is-invalid @enderror" id="from_email" wire:model="from_email" placeholder="john@example.com">
                            @error('from_email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="reply_to">Reply To Email</label>
                            <input type="email" class="form-control @error('reply_to') is-invalid @enderror" id="reply_to" wire:model="reply_to" placeholder="Optional">
                            @error('reply_to') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <hr>
                        <h6>Sending Limits</h6>
                        <div class="form-group">
                            <label for="daily_quota">Daily Limit</label>
                            <input type="number" class="form-control @error('daily_quota') is-invalid @enderror" id="daily_quota" wire:model="daily_quota">
                            <small class="form-text text-muted">Max emails per day. 0 for unlimited.</small>
                            @error('daily_quota') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="hourly_quota">Hourly Limit</label>
                            <input type="number" class="form-control @error('hourly_quota') is-invalid @enderror" id="hourly_quota" wire:model="hourly_quota">
                            @error('hourly_quota') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- SMTP Settings --}}
            <div class="col-md-4">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-paper-plane mr-1"></i> SMTP Settings (Sending)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Host <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('smtp_host') is-invalid @enderror" wire:model="smtp_host" placeholder="smtp.gmail.com">
                            @error('smtp_host') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Port <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('smtp_port') is-invalid @enderror" wire:model="smtp_port" placeholder="587">
                            @error('smtp_port') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('smtp_username') is-invalid @enderror" wire:model="smtp_username">
                            @error('smtp_username') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control @error('smtp_password') is-invalid @enderror" wire:model="smtp_password" placeholder="Leave empty to keep current">
                            <small class="text-muted">Only fill this if you want to change the password.</small>
                            @error('smtp_password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Encryption <span class="text-danger">*</span></label>
                            <select class="form-control @error('smtp_encryption') is-invalid @enderror" wire:model="smtp_encryption">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="none">None</option>
                            </select>
                            @error('smtp_encryption') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- IMAP Settings --}}
            <div class="col-md-4">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-inbox mr-1"></i> IMAP Settings (Monitoring)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Host <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('imap_host') is-invalid @enderror" wire:model="imap_host" placeholder="imap.gmail.com">
                            @error('imap_host') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Port <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('imap_port') is-invalid @enderror" wire:model="imap_port" placeholder="993">
                            @error('imap_port') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('imap_username') is-invalid @enderror" wire:model="imap_username">
                            @error('imap_username') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control @error('imap_password') is-invalid @enderror" wire:model="imap_password" placeholder="Leave empty to keep current">
                            <small class="text-muted">Only fill this if you want to change the password.</small>
                             @error('imap_password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Encryption <span class="text-danger">*</span></label>
                            <select class="form-control @error('imap_encryption') is-invalid @enderror" wire:model="imap_encryption">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                                <option value="none">None</option>
                            </select>
                            @error('imap_encryption') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 text-right pb-4">
                 <a href="{{ route('inboxes.index') }}" class="btn btn-default mr-2">Cancel</a>
                 <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </div>
    </form>
</div>
