<div>
    {{-- Campaign Header --}}
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $campaign->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('campaigns.index') }}">Campaigns</a></li>
                        <li class="breadcrumb-item active">{{ $campaign->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="content">
        <div class="container-fluid">
            
            {{-- Campaign Info --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Status:</strong>
                                    <span class="badge badge-{{ $campaign->status === 'sent' ? 'success' : ($campaign->status === 'sending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>List:</strong> {{ $campaign->mailingList->name }}
                                </div>
                                <div class="col-md-3">
                                    <strong>From:</strong> {{ $campaign->from_name }} ({{ $campaign->from_email }})
                                </div>
                                <div class="col-md-3">
                                    <strong>Created:</strong> {{ $campaign->created_at->format('M d, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top KPI Cards --}}
            <div class="row">
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($stats['sent']) }}</h3>
                            <p>Sent</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['open_rate'] }}<sup style="font-size: 20px">%</sup></h3>
                            <p>Open Rate</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['click_rate'] }}<sup style="font-size: 20px">%</sup></h3>
                            <p>Click Rate</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h3>{{ $stats['reply_rate'] }}<sup style="font-size: 20px">%</sup></h3>
                            <p>Reply Rate</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $stats['bounce_rate'] }}<sup style="font-size: 20px">%</sup></h3>
                            <p>Bounce Rate</p>
                        </div>
                    </div>
                </div>
                 <div class="col-lg-2 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>{{ $stats['unique_opens'] }}</h3>
                            <p>Unique Opens</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="campaign-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="overview-tab" data-toggle="pill" href="#overview" role="tab">Overview</a>
                        </li>
                        @if(count($stepMetrics) > 0)
                        <li class="nav-item">
                            <a class="nav-link" id="sequence-tab" data-toggle="pill" href="#sequence" role="tab">Sequence Flow</a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" id="activity-tab" data-toggle="pill" href="#activity" role="tab">Detailed Activity</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="campaign-tabs-content">
                        
                        {{-- Overview Tab --}}
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            {{-- Performance Metrics --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="mb-3">Performance Overview</h4>
                                    <div class="row text-center mb-4">
                                        <div class="col-md-3">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> {{ $stats['open_rate'] }}%</span>
                                                <h5 class="description-header">{{ number_format($stats['total_opens']) }}</h5>
                                                <span class="description-text">TOTAL OPENS</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-warning"><i class="fas fa-caret-up"></i> {{ $stats['click_rate'] }}%</span>
                                                <h5 class="description-header">{{ number_format($stats['total_clicks']) }}</h5>
                                                <span class="description-text">TOTAL CLICKS</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-primary"><i class="fas fa-caret-up"></i> {{ $stats['reply_rate'] }}%</span>
                                                <h5 class="description-header">{{ $stats['replies_count'] }}</h5>
                                                <span class="description-text">TOTAL REPLIES</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="description-block">
                                                <span class="description-percentage text-secondary">--</span>
                                                <h5 class="description-header">{{ $stats['ctr'] }}%</h5>
                                                <span class="description-text">CLICK-TO-OPEN</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Link Performance --}}
                            @if(count($linkPerformance) > 0)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h5 class="mb-2">Link Performance</h5>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>URL</th>
                                                <th class="text-right" style="width: 150px">Unique Clicks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($linkPerformance as $link)
                                            <tr>
                                                <td>
                                                    <a href="{{ $link->url }}" target="_blank" class="text-sm">
                                                        {{ Str::limit($link->url, 100) }}
                                                    </a>
                                                </td>
                                                <td class="text-right">
                                                    <span class="badge badge-primary">{{ $link->clicks_count }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Sequence Flow Tab --}}
                        @if(count($stepMetrics) > 0)
                        <div class="tab-pane fade" id="sequence" role="tabpanel">
                            <div class="row mb-4">
                                <div class="col-md-3 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Enrolled</span>
                                            <span class="info-box-number">{{ $sequenceStats['total'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Completed</span>
                                            <span class="info-box-number">{{ $sequenceStats['completed'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-running"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Active Now</span>
                                            <span class="info-box-number">{{ $sequenceStats['active'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger"><i class="fas fa-stop-circle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Stopped (Reply/Unsub)</span>
                                            <span class="info-box-number">{{ $sequenceStats['stopped'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Step</th>
                                            <th>Subject</th>
                                            <th class="text-center">Sent</th>
                                            <th class="text-center">Opens</th>
                                            <th class="text-center">Clicks</th>
                                            <th class="text-center">Replies</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stepMetrics as $metric)
                                        <tr>
                                            <td class="font-weight-bold">{{ $metric['name'] }}</td>
                                            <td>{{ $metric['subject'] }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary" style="font-size: 14px">{{ $metric['sent'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress-group">
                                                    {{ $metric['opens'] }}
                                                    <span class="float-right"><b>{{ $metric['open_rate'] }}%</b></span>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar bg-success" style="width: {{ $metric['open_rate'] }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress-group">
                                                    {{ $metric['clicks'] }}
                                                    <span class="float-right"><b>{{ $metric['click_rate'] }}%</b></span>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar bg-warning" style="width: {{ $metric['click_rate'] }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress-group">
                                                    {{ $metric['replies'] }}
                                                    <span class="float-right"><b>{{ $metric['reply_rate'] }}%</b></span>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar bg-purple" style="width: {{ $metric['reply_rate'] }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        {{-- Detail Activity Tab --}}
                        <div class="tab-pane fade" id="activity" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Recent Opens</h5>
                                    @if(count($recentOpens) > 0)
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Subscriber</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentOpens as $open)
                                            <tr>
                                                <td>
                                                    {{ $open->subscriber->email }}<br>
                                                    <small class="text-muted">{{ $open->subscriber->first_name }} {{ $open->subscriber->last_name }}</small>
                                                </td>
                                                <td>{{ $open->opened_at->format('M d, H:i') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @else
                                    <p class="text-muted">No opens recorded yet.</p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h5>Recent Clicks</h5>
                                    @if(count($recentClicks) > 0)
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Subscriber</th>
                                                <th>Link</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentClicks as $click)
                                            <tr>
                                                <td>{{ $click->subscriber->email }}</td>
                                                <td><a href="{{ $click->link->url }}" target="_blank" title="{{ $click->link->url }}">Link</a></td>
                                                <td>{{ $click->clicked_at->format('M d, H:i') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @else
                                    <p class="text-muted">No clicks recorded yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
