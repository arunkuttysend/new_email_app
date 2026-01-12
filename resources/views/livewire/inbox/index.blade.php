<div>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Inbox</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary card-outline h-100">
                        <div class="card-header">
                            <h3 class="card-title">Conversations</h3>
                        </div>
                        <div class="card-body p-0" style="height: 600px; overflow-y: auto;">
                            {{-- Thread List Component --}}
                            <livewire:inbox.thread-list :selectedSubscriberId="$selectedSubscriberId" />
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    @if($selectedSubscriberId)
                        <livewire:inbox.thread-view :subscriberId="$selectedSubscriberId" :key="$selectedSubscriberId" />
                    @else
                        <div class="card h-100 d-flex justify-content-center align-items-center" style="min-height: 600px;">
                            <div class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>Select a conversation to start reading</h5>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
