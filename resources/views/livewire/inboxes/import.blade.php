<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            @if($step === 1) Upload CSV
                            @elseif($step === 2) Map Columns
                            @elseif($step === 3) Import Complete
                            @endif
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        {{-- Step 1: Upload --}}
                        @if($step === 1)
                            <div class="text-center py-5">
                                <i class="fas fa-file-csv fa-4x text-muted mb-3"></i>
                                <h3>Upload CSV File</h3>
                                <p class="text-muted">Upload a CSV file containing your inbox credentials (SMTP/IMAP).</p>
                                
                                <div class="form-group w-50 mx-auto">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" wire:model="file" id="customFile" accept=".csv">
                                        <label class="custom-file-label" for="customFile">
                                            {{ $file ? $file->getClientOriginalName() : 'Choose file' }}
                                        </label>
                                    </div>
                                    @error('file') <span class="text-danger d-block mt-2">{{ $message }}</span> @enderror
                                </div>
                                
                                <div wire:loading wire:target="file">
                                    <span class="text-primary"><i class="fas fa-spinner fa-spin"></i> Uploading...</span>
                                </div>
                            </div>
                        @endif

                        {{-- Step 2: Mapping --}}
                        @if($step === 2)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Map the columns from your CSV to the system fields. We've tried to auto-detect them for you.
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%">CSV Header</th>
                                            <th style="width: 30%">First Row Sample</th>
                                            <th style="width: 40%">Map To Field</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($csvHeaders as $index => $header)
                                            <tr>
                                                <td class="font-weight-bold">{{ $header }}</td>
                                                <td class="text-muted text-sm">
                                                    {{ isset($csvSampleRow[$index]) ? Str::limit($csvSampleRow[$index], 50) : '' }}
                                                </td>
                                                <td>
                                                    <select wire:model="mapping.{{ $index }}" class="form-control form-control-sm">
                                                        <option value="ignore">-- Ignore Column --</option>
                                                        @foreach($inboxFields as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button wire:click="$set('step', 1)" class="btn btn-default">Back</button>
                                <button wire:click="processImport" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading wire:target="processImport"><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                                    <span wire:loading.remove wire:target="processImport">Start Import</span>
                                </button>
                            </div>
                        @endif

                        {{-- Step 3: Complete --}}
                        @if($step === 3)
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-check-circle text-success fa-5x"></i>
                                </div>
                                <h3>Import Completed!</h3>
                                <p class="lead">
                                    Successfully imported <strong>{{ $successCount }}</strong> inboxes.
                                    @if($errorCount > 0)
                                        <br>
                                        <span class="text-danger">{{ $errorCount }} failed or skipped.</span>
                                    @endif
                                </p>
                                
                                <div class="mt-4">
                                    <a href="{{ route('inboxes.index') }}" class="btn btn-primary">View Inboxes</a>
                                    <button wire:click="$set('step', 1)" class="btn btn-default ml-2">Import Another File</button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
