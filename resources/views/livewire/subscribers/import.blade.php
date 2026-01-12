<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            @if($step === 1)
                                Step 1: Upload CSV
                            @elseif($step === 2)
                                Step 2: Map Columns
                            @elseif($step === 3)
                                Import Complete
                            @endif
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        {{-- Step 1: Upload --}}
                        @if($step === 1)
                            <form wire:submit.prevent="parseFile">
                                <div class="form-group">
                                    <label for="mailing_list_id">Target Mailing List <span class="text-danger">*</span></label>
                                    @if($locked && isset($mailingLists[$mailing_list_id]))
                                        <input type="text" class="form-control" value="{{ $mailingLists[$mailing_list_id] }}" disabled>
                                        <input type="hidden" wire:model="mailing_list_id">
                                    @else
                                        <select class="form-control @error('mailing_list_id') is-invalid @enderror" id="mailing_list_id" wire:model="mailing_list_id">
                                            <option value="">Select a list</option>
                                            @foreach($mailingLists as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('mailing_list_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="file">CSV File <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('file') is-invalid @enderror" id="file" wire:model="file">
                                            <label class="custom-file-label" for="file">
                                                @if($file)
                                                    {{ $file->getClientOriginalName() }}
                                                @else
                                                    Choose CSV file
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                    @error('file') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        Next: Map Columns <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </form>
                        @endif

                        {{-- Step 2: Mapping --}}
                        @if($step === 2)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Map your CSV columns to subscriber fields. Select <strong>"Create New: [TAG]"</strong> to auto-create a custom field.
                            </div>

                            @if($errors->has('mapping'))
                                <div class="alert alert-danger">{{ $errors->first('mapping') }}</div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>CSV Header</th>
                                            <th>Sample Data (First Row)</th>
                                            <th>Map To Field</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($csvHeaders as $index => $header)
                                            <tr>
                                                <td class="font-weight-bold">{{ $header }}</td>
                                                <td class="text-muted">{{ $csvSampleRow[$index] ?? '-' }}</td>
                                                <td>
                                                    <select wire:model="fieldMapping.{{ $index }}" class="form-control">
                                                        <option value="">-- Ignore Column --</option>
                                                        <option value="email">Email Address (System)</option>
                                                        
                                                        <optgroup label="Existing Fields">
                                                            @foreach($existingFields as $field)
                                                                <option value="EXISTING:{{ $field->id }}">{{ $field->label }} [{{ $field->tag }}]</option>
                                                            @endforeach
                                                        </optgroup>

                                                        <optgroup label="Create new field">
                                                            <option value="NEW:{{ $header }}">Create New: [{{ strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $header)) }}]</option>
                                                        </optgroup>
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                <button wire:click="$set('step', 1)" class="btn btn-default mr-2">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                                <button wire:click="processImport" class="btn btn-success" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="processImport">
                                        <i class="fas fa-file-import"></i> Start Import
                                    </span>
                                    <span wire:loading wire:target="processImport">
                                        <i class="fas fa-sync fa-spin"></i> Importing...
                                    </span>
                                </button>
                            </div>
                        @endif

                        {{-- Step 3: Success --}}
                        @if($step === 3)
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-check-circle text-success fa-4x"></i>
                                </div>
                                <h3>Import Completed!</h3>
                                
                                <div class="row justify-content-center mt-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-6 text-right border-right">
                                                        <span class="text-success h2 font-weight-bold">{{ $importStats['success'] ?? 0 }}</span>
                                                        <div class="text-muted">Subscribers Added</div>
                                                    </div>
                                                    <div class="col-6 text-left">
                                                        <span class="text-warning h2 font-weight-bold">{{ $importStats['skipped'] ?? 0 }}</span>
                                                        <div class="text-muted">Skipped/Invalid</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <a href="{{ route('subscribers.index', ['list_id' => $mailing_list_id]) }}" class="btn btn-primary">
                                        View Subscribers
                                    </a>
                                    <button wire:click="$set('step', 1)" class="btn btn-default ml-2">
                                        Import More
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
