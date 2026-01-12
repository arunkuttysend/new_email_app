<div class="card card-primary card-outline direct-chat direct-chat-primary h-100 shadow-none border-0">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-user-circle mr-1"></i>
            {{ $subscriber->first_name }} {{ $subscriber->last_name }} 
            <small class="text-muted"><{{ $subscriber->email }}></small>
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" wire:click="toggleLead">
                @php
                    $isLead = $messages->where('is_reply', true)->first()['is_lead'] ?? false;
                @endphp
                <i class="fas fa-star {{ $isLead ? 'text-warning' : 'text-muted' }}"></i>
            </button>
            
            <div class="btn-group">
                <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-tags"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" role="menu">
                    <a href="#" class="dropdown-item" wire:click.prevent="setSentiment('interested')">Interested</a>
                    <a href="#" class="dropdown-item" wire:click.prevent="setSentiment('not_interested')">Not Interested</a>
                    <a href="#" class="dropdown-item" wire:click.prevent="setSentiment('ooo')">Out of Office</a>
                </div>
            </div>

            <span class="badge badge-info">{{ count($messages) }} Messages</span>
        </div>
    </div>
    
    <div class="card-body">
        <div class="direct-chat-messages" style="height: 500px;">
            @foreach($messages as $msg)
                <div class="direct-chat-msg {{ $msg['is_reply'] ? '' : 'right' }}"> 
                    {{-- Left is received (Prospect), Right is Sent (Us) --}}
                    
                    <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name float-{{ $msg['is_reply'] ? 'left' : 'right' }}">
                            {{ $msg['is_reply'] ? $subscriber->first_name : 'You' }}
                        </span>
                        <span class="direct-chat-timestamp float-{{ $msg['is_reply'] ? 'right' : 'left' }}">
                            {{ \Carbon\Carbon::parse($msg['created_at'])->format('d M H:i') }}
                        </span>
                    </div>
                    
                    <img class="direct-chat-img" src="https://ui-avatars.com/api/?name={{ $msg['is_reply'] ? $subscriber->first_name : 'You' }}&background=random" alt="User Image">
                    
                    <div class="direct-chat-text">
                        @if($msg['is_reply'])
                            {{-- For replies, we might want to strip quoted text in future, for now show all --}}
                            {!! $msg['body'] !!}
                        @else
                            {{-- Sent email placeholder --}}
                            <strong>{{ $msg['subject'] }}</strong>
                            <br>
                            <em class="text-muted">Content storage not enabled.</em>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <div class="card-footer">
        <div class="input-group">
            <input type="text" wire:model="message" wire:keydown.enter="sendReply" placeholder="Type Message..." class="form-control">
            <span class="input-group-append">
                <button type="button" wire:click="sendReply" class="btn btn-primary">Send</button>
            </span>
        </div>
    </div>
</div>
