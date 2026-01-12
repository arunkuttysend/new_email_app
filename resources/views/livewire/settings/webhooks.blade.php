<div>
    <div class="row">
        <div class="col-md-12">
            <h3><i class="fas fa-webhook"></i> Bounce Webhooks</h3>
            <p class="text-muted">Configure these webhook URLs in your mail service providers for real-time bounce notifications.</p>
        </div>
    </div>
    
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    
    <div class="row">
        @foreach($webhooks as $webhook)
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">
                            <i class="fas {{ $webhook['icon'] }}"></i>
                            {{ $webhook['name'] }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><strong>Provider:</strong></label>
                            <div>
                                <span class="badge badge-info">{{ $webhook['provider'] }}</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Webhook URL:</strong></label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $webhook['url'] }}" 
                                       readonly
                                       id="webhook-{{ $loop->index }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" 
                                            type="button"
                                            onclick="copyToClipboard('{{ $webhook['url'] }}', {{ $loop->index }})">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Method:</strong></label>
                            <div>
                                <code>{{ $webhook['method'] }}</code>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <i class="icon fas fa-info-circle"></i>
                            {{ $webhook['description'] }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    {{-- Configuration Guide --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-book"></i> Configuration Guide</h3>
                </div>
                <div class="card-body">
                    <h5>📮 Postal Configuration</h5>
                    <ol>
                        <li>Log in to your Postal admin panel</li>
                        <li>Go to <strong>Settings → Webhooks</strong></li>
                        <li>Click <strong>Add Webhook</strong></li>
                        <li>Paste the Postal webhook URL above</li>
                        <li>Select event: <code>MessageBounced</code></li>
                        <li>Save and test the webhook</li>
                    </ol>
                    
                    <hr>
                    
                    <h5>📧 IMAP Collection (Alternative)</h5>
                    <p>
                        You can also collect bounces via IMAP by configuring bounce inbox credentials in each Delivery Server.
                        Run manually: <code>php artisan bounces:collect</code>
                    </p>
                    
                    <div class="alert alert-success">
                        <strong>💡 Best Practice:</strong> Use webhooks for real-time bounce processing (instant) and IMAP as a backup (scheduled hourly).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text, index) {
    navigator.clipboard.writeText(text).then(() => {
        // Show success feedback
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        }, 2000);
    });
}
</script>
