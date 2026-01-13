@section('css')
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <style>
        .trix-content {
            min-height: 250px;
            max-height: 500px;
            overflow-y: auto;
        }
        /* Hide file upload button since we don't support attachments in Trix yet */
        trix-toolbar .trix-button--icon-attach { display: none; }
    </style>
@stop

@section('js')
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
@stop

<div>
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        Step {{ $step }}: 
                        @if($step == 1) Campaign Setup
                        @elseif($step == 2) Select Audience
                        @elseif($step == 3) Build Sequence
                        @elseif($step == 4) Schedule & Review
                        @endif
                    </h3>
                </div>

                <div class="card-body">
                    {{-- Progress Bar --}}
                    <div class="progress mb-4" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $step * 25 }}%"></div>
                    </div>

                    {{-- Step 1: Setup --}}
                    @if($step == 1)
                        <div class="form-group">
                            <label>Campaign Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control" placeholder="e.g. Cold Outreach Q1">
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>From Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="from_name" class="form-control" placeholder="Sender Name">
                        </div>

                        <div class="form-group">
                            <label>From Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="from_email" class="form-control" placeholder="sender@example.com">
                        </div>

                        {{-- Inboxes Selection --}}
                        <div class="form-group">
                            <label>Select Sender Accounts (Inboxes)</label>
                            
                            {{-- Search Box --}}
                            <div class="mb-2">
                                <input type="text" wire:model.live.debounce.300ms="search_inbox" class="form-control form-control-sm" placeholder="Search inboxes...">
                            </div>

                            <div class="card p-2" style="max_height: 250px; overflow-y: auto;">
                                @forelse($inboxes as $inbox)
                                    <div class="custom-control custom-checkbox mb-1">
                                        <input class="custom-control-input" type="checkbox" id="inbox_{{ $inbox->id }}" value="{{ $inbox->id }}" wire:model.live="selected_inboxes">
                                        <label for="inbox_{{ $inbox->id }}" class="custom-control-label font-weight-normal">
                                            {{ $inbox->name }} 
                                            <span class="text-muted small">&lt;{{ $inbox->from_email }}&gt;</span>
                                        </label>
                                    </div>
                                @empty
                                    <p class="text-muted small text-center mb-0">No active inboxes found matching your search.</p>
                                @endforelse
                            </div>
                            @error('selected_inboxes') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Step 2: Audience --}}
                    @if($step == 2)
                        <div class="form-group">
                            <label>Mailing List <span class="text-danger">*</span></label>
                            <select wire:model="mailing_list_id" class="form-control">
                                <option value="">-- Choose a List --</option>
                                @foreach($lists as $list)
                                    <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->subscribers_count }} subscribers)</option>
                                @endforeach
                            </select>
                             @error('mailing_list_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Step 3: Sequence --}}
                    @if($step == 3)
                        <div class="mb-3">
                            <p class="text-muted">Build your email sequence. The first email sends immediately or at the scheduled time. Follow-ups send based on the "Wait" delay if the condition is met.</p>
                        </div>

                        @foreach($steps as $index => $s)
                            <div class="card {{ $index == 0 ? 'card-primary card-outline' : 'card-secondary mb-3' }}" wire:key="step-card-{{ $index }}">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        @if($index == 0)
                                            <i class="fas fa-envelope mr-1"></i> Initial Email
                                        @else
                                            <i class="fas fa-clock mr-1"></i> Follow-up #{{ $index }}
                                        @endif
                                    </h3>
                                    <div class="card-tools">
                                        @if($index > 0)
                                            <button type="button" wire:click="removeStep({{ $index }})" class="btn btn-tool text-danger">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        {{-- LEFT COL: EDITOR --}}
                                        <div class="col-md-6 border-right">
                                            @if($index > 0)
                                                <div class="row bg-light p-2 mb-3 rounded">
                                                    <div class="col-12">
                                                        <label class="mb-1 text-sm">Wait (Days)</label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" wire:model="steps.{{ $index }}.wait_days" class="form-control" min="1">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">days</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="form-group">
                                                <label>Subject Line</label>
                                                <input type="text" wire:model.live="steps.{{ $index }}.subject" class="form-control" placeholder="Subject Line">
                                                @error('steps.'.$index.'.subject') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="form-group" x-data="{
                                                value: @entangle('steps.' . $index . '.content'),
                                                isFocused: false,
                                                insertTag(tag) {
                                                     const editor = this.$refs.trix.editor;
                                                     editor.insertString(tag);
                                                },
                                                init() {
                                                    let trix = this.$refs.trix;
                                                    
                                                    // Update Livewire on change
                                                    trix.addEventListener('trix-change', (e) => {
                                                        this.value = e.target.value;
                                                    });

                                                    // Watch for external changes (e.g. loading saved draft)
                                                    this.$watch('value', (newValue) => {
                                                        if (document.activeElement !== trix) {
                                                            // Only update if not currently typing to avoid cursor jumps
                                                            // Check if content is different to avoid loops
                                                            if(trix.editor.getDocument().toString().trim() !== newValue.replace(/<[^>]*>?/gm, '').trim()) {
                                                                 // This is a rough check. Ideally use editor.loadHTML(newValue)
                                                                 // but Trix doesn't like being updated while focused.
                                                                 // Since we mostly start empty or load once, this is okay for now.
                                                                 if (!this.isFocused) {
                                                                     trix.editor.loadHTML(newValue);
                                                                 }
                                                            }
                                                        }
                                                    });
                                                }
                                            }" wire:ignore>
                                                <label>Email Body</label>
                                                
                                                <trix-editor 
                                                    x-ref="trix" 
                                                    input="x-content-{{ $index }}" 
                                                    class="trix-content"
                                                    @focus="isFocused = true"
                                                    @blur="isFocused = false"
                                                ></trix-editor>
                                                
                                                <input id="x-content-{{ $index }}" type="hidden" :value="value">
                                                
                                                <div class="mt-2">
                                                    <small class="text-muted">Available tags (click to insert):</small>
                                                    <div class="d-flex flex-wrap mt-1">
                                                        @foreach($availableTags as $tag)
                                                            <button type="button" @click="insertTag('{{ $tag }}')" class="btn btn-xs btn-light border mr-2 mb-1 code-tag" style="padding: 2px 6px;">
                                                                <code class="text-primary">{{ $tag }}</code>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @error('steps.'.$index.'.content') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        {{-- RIGHT COL: PREVIEW --}}
                                        <div class="col-md-6">
                                            <label class="text-muted text-uppercase text-xs letter-spacing-1">Live Preview</label>
                                            
                                            <div class="card shadow-sm">
                                                <div class="card-header bg-light py-2">
                                                    <small class="text-muted">Subject:</small> 
                                                    <strong class="text-dark">
                                                        @php
                                                            $previewSubject = $steps[$index]['subject'] ?: '(No Subject)';
                                                            $replacements = [
                                                                '{first_name}' => '<span class="bg-warning px-1 rounded">John</span>',
                                                                '{last_name}' => '<span class="bg-warning px-1 rounded">Doe</span>',
                                                                '{company}' => '<span class="bg-warning px-1 rounded">Acme Corp</span>',
                                                                '{email}' => '<span class="bg-warning px-1 rounded">john@example.com</span>',
                                                                '{unsubscribe_url}' => '<a href="#">Unsubscribe</a>',
                                                            ];
                                                            
                                                            // Simplified replacements for subject (no HTML tags)
                                                            $subjectReplacements = [
                                                                '{first_name}' => 'John',
                                                                '{last_name}' => 'Doe',
                                                                '{company}' => 'Acme Corp',
                                                                '{email}' => 'john@example.com',
                                                            ];
                                                            
                                                            foreach ($subjectReplacements as $tag => $val) {
                                                                $previewSubject = str_ireplace($tag, $val, $previewSubject);
                                                            }
                                                        @endphp
                                                        {{ $previewSubject }}
                                                    </strong>
                                                </div>
                                                <div class="card-body p-4" style="min-height: 300px; background: #fff;">
                                                    {{-- Simulate Email Body --}}
                                                    {{-- Trix uses normal HTML output so we can just render it, replacing tags --}}
                                                    <div class="email-content text-break trix-content">
                                                        @if(empty($steps[$index]['content']))
                                                            <p class="text-muted text-center italic mt-5">Start typing to see preview...</p>
                                                        @else
                                                            @php
                                                                $previewContent = $steps[$index]['content'];
                                                                foreach ($replacements as $tag => $val) {
                                                                    $previewContent = str_ireplace($tag, $val, $previewContent);
                                                                }
                                                            @endphp
                                                            {!! $previewContent !!}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-white text-center py-2">
                                                    <small class="text-muted text-xs">
                                                        <i class="fas fa-eye"></i> Only a preview. Actual rendering varies by email client.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="text-center py-3">
                            <button wire:click="addStep" class="btn btn-outline-primary btn-lg border-dashed">
                                <i class="fas fa-plus"></i> Add Follow-up Step
                            </button>
                        </div>
                    @endif

                    {{-- Step 4: Schedule --}}
                    @if($step == 4)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" wire:model="start_date" class="form-control" min="{{ date('Y-m-d') }}">
                                    @error('start_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Time</label>
                                    <input type="time" wire:model="start_time" class="form-control">
                                    @error('start_time') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Daily Limit (Max emails per day)</label>
                                    <input type="number" wire:model="daily_limit" class="form-control" min="1">
                                    <small class="text-muted">Leave empty or 0 for unlimited (subject to inbox limits).</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- Test Email Section --}}
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-paper-plane"></i> Send Test Email</h5>
                            <p class="mb-2">Preview how your first email will look before launching the campaign.</p>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-2">
                                        <input type="email" wire:model="test_email" class="form-control" placeholder="your.email@example.com">
                                        @error('test_email') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button wire:click="sendTestEmail" class="btn btn-info btn-block">
                                        <i class="fas fa-flask"></i> Send Test
                                    </button>
                                </div>
                            </div>
                            
                            @if(session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show mt-2 mb-0">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>
                            @endif
                            
                            @if(session()->has('error'))
                                <div class="alert alert-danger alert-dismissible fade show mt-2 mb-0">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>
                            @endif
                        </div>

                        <hr>

                        <div class="alert alert-success">
                            <h5><i class="icon fas fa-check"></i> Ready to Launch!</h5>
                            Review your campaign details below.
                        </div>
                        <dl class="row">
                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8">{{ $name }}</dd>
                            <dt class="col-sm-4">Audience</dt>
                            <dd class="col-sm-8">Selected List ID: {{ $mailing_list_id }}</dd>
                            <dt class="col-sm-4">Steps</dt>
                            <dd class="col-sm-8">{{ count($steps) }} emails in sequence</dd>
                            <dt class="col-sm-4">Schedule</dt>
                            <dd class="col-sm-8">
                                @if($start_date)
                                    Starts on {{ $start_date }} at {{ $start_time ?: '00:00' }}
                                @else
                                    Starts Immediately
                                @endif
                            </dd>
                        </dl>
                        <button wire:click="save" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-rocket"></i> Launch Campaign
                        </button>
                    @endif

                </div>

                <div class="card-footer d-flex justify-content-between">
                    @if($step > 1)
                        <button wire:click="prevStep" class="btn btn-default">Back</button>
                    @else
                        <button disabled class="btn btn-default">Back</button>
                    @endif

                    @if($step < 4)
                        <button wire:click="nextStep" class="btn btn-primary">Next Step <i class="fas fa-arrow-right"></i></button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
