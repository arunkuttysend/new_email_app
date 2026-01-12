<div>
    <div class="px-2 pt-2 pb-1">
        <select wire:model.live="filter" class="form-control form-control-sm mb-2">
            <option value="all">All Messages</option>
            <option value="unread">Unread</option>
            <option value="leads">Leads (Starred)</option>
            <option value="interested">Interested</option>
            <option value="not_interested">Not Interested</option>
            <option value="ooo">Out of Office</option>
        </select>
    </div>
    <ul class="nav nav-pills flex-column">
        @forelse($threads as $thread)
        {{-- For ThreadList --}}
            <li class="nav-item">
                <a href="#" wire:click.prevent="selectThread('{{ $thread->id }}')" 
                   class="nav-link {{ $selectedSubscriberId == $thread->id ? 'active' : '' }}" 
                   style="border-bottom: 1px solid #f4f4f4; border-radius: 0; padding: 15px;">
                    
                    <div class="float-right text-sm text-muted">
                        {{ $thread->campaignReplies->first()?->received_at?->diffForHumans() }}
                    </div>
                    
                    <span class="text-bold" style="font-size: 16px;">
                        {{ $thread->first_name }} {{ $thread->last_name }}
                    </span>
                    
                    <div class="text-sm text-muted text-truncate">
                        {{ $thread->email }}
                    </div>
                    
                    <p class="text-sm mb-0 mt-1 text-truncate" style="color: #666;">
                        {{ $thread->campaignReplies->first()?->subject ?? 'No subject' }}
                    </p>

                    @if($thread->unread_count > 0)
                        <span class="badge badge-primary float-right mt-1">{{ $thread->unread_count }}</span>
                    @endif
                </a>
            </li>
        @empty
            <li class="nav-item p-3 text-center text-muted">
                No conversations yet.
            </li>
        @endforelse
    </ul>

    <div class="p-2">
        {{ $threads->links(data: ['scrollTo' => false]) }}
    </div>
</div>
