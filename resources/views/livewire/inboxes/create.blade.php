<div>
    <div class="container-fluid">
        <form wire:submit.prevent="store">
            <div class="row">
                <div class="col-md-6">
                    {{-- General Settings --}}
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> General Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Inbox Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name" placeholder="e.g. CEO Primary">
                                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="from_name">From Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('from_name') is-invalid @enderror" id="from_name" wire:model="from_name">
                                        @error('from_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="from_email">From Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('from_email') is-invalid @enderror" id="from_email" wire:model="from_email">
                                        @error('from_email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="reply_to">Reply To (Optional)</label>
                                <input type="email" class="form-control @error('reply_to') is-invalid @enderror" id="reply_to" wire:model="reply_to" placeholder="Same as From Email if empty">
                                @error('reply_to') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="daily_quota">Daily Sending Limit</label>
                                        <input type="number" class="form-control @error('daily_quota') is-invalid @enderror" id="daily_quota" wire:model="daily_quota">
                                        @error('daily_quota') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hourly_quota">Hourly Limit</label>
                                        <input type="number" class="form-control" id="hourly_quota" wire:model="hourly_quota">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    {{-- SMTP Settings --}}
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-paper-plane"></i> SMTP (Sending) Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Host <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('smtp_host') is-invalid @enderror" wire:model="smtp_host" placeholder="smtp.gmail.com">
                                        @error('smtp_host') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Port <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('smtp_port') is-invalid @enderror" wire:model="smtp_port" placeholder="587">
                                        @error('smtp_port') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('smtp_username') is-invalid @enderror" wire:model="smtp_username">
                                @error('smtp_username') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('smtp_password') is-invalid @enderror" wire:model="smtp_password">
                                @error('smtp_password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                             <div class="form-group">
                                <label>Encryption</label>
                                <select class="form-control" wire:model="smtp_encryption">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- IMAP Settings --}}
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-inbox"></i> IMAP (Receiving) Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Host <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('imap_host') is-invalid @enderror" wire:model="imap_host" placeholder="imap.gmail.com">
                                        @error('imap_host') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Port <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('imap_port') is-invalid @enderror" wire:model="imap_port" placeholder="993">
                                        @error('imap_port') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('imap_username') is-invalid @enderror" wire:model="imap_username">
                                @error('imap_username') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('imap_password') is-invalid @enderror" wire:model="imap_password">
                                @error('imap_password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                             <div class="form-group">
                                <label>Encryption</label>
                                <select class="form-control" wire:model="imap_encryption">
                                    <option value="ssl">SSL</option>
                                    <option value="tls">TLS</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                             <button type="submit" class="btn btn-primary float-right">
                                <i class="fas fa-save"></i> Save Inbox
                            </button>
                            <a href="{{ route('inboxes.index') }}" class="btn btn-default">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
