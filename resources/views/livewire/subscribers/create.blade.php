<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Subscriber Information</h3>
                    </div>
                    
                    <form wire:submit.prevent="store">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model="email" placeholder="subscriber@example.com">
                                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mailing_list_id">Mailing List <span class="text-danger">*</span></label>
                                        <select class="form-control @error('mailing_list_id') is-invalid @enderror" id="mailing_list_id" wire:model="mailing_list_id">
                                            <option value="">Select a list</option>
                                            @foreach($mailingLists as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('mailing_list_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">Status <span class="text-danger">*</span></label>
                                        <select class="form-control @error('status') is-invalid @enderror" id="status" wire:model="status">
                                            <option value="confirmed">Confirmed</option>
                                            <option value="unconfirmed">Unconfirmed</option>
                                            <option value="unsubscribed">Unsubscribed</option>
                                        </select>
                                        @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            @if(count($fields) > 0)
                                <hr>
                                <h5 class="mb-3">Additional Information</h5>
                                <div class="row">
                                    @foreach($fields as $field)
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="field_{{ $field->tag }}">
                                                    {{ $field->label }}
                                                    @if($field->required) <span class="text-danger">*</span> @endif
                                                </label>
                                                
                                                @if($field->type === 'text' || $field->type === 'url')
                                                    <input type="text" 
                                                           class="form-control @error('fieldValues.'.$field->tag) is-invalid @enderror" 
                                                           id="field_{{ $field->tag }}" 
                                                           wire:model="fieldValues.{{ $field->tag }}" 
                                                           placeholder="{{ $field->label }}">
                                                @elseif($field->type === 'number')
                                                    <input type="number" 
                                                           class="form-control @error('fieldValues.'.$field->tag) is-invalid @enderror" 
                                                           id="field_{{ $field->tag }}" 
                                                           wire:model="fieldValues.{{ $field->tag }}">
                                                @elseif($field->type === 'date')
                                                    <input type="date" 
                                                           class="form-control @error('fieldValues.'.$field->tag) is-invalid @enderror" 
                                                           id="field_{{ $field->tag }}" 
                                                           wire:model="fieldValues.{{ $field->tag }}">
                                                @endif
                                                
                                                @error('fieldValues.'.$field->tag) 
                                                    <span class="invalid-feedback">{{ $message }}</span> 
                                                @enderror
                                                @if($field->help_text)
                                                    <small class="form-text text-muted">{{ $field->help_text }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Add Subscriber</button>
                            <a href="{{ route('subscribers.index') }}" class="btn btn-default float-right">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
