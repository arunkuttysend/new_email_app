<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UnsubscribeController;
use Illuminate\Support\Facades\Route;

// Public tracking routes (no auth required)
Route::get('/track/open/{campaignSend}', [TrackingController::class, 'trackOpen'])->name('track.open');
Route::get('/track/click/{linkHash}/{campaignSend}', [TrackingController::class, 'trackClick'])->name('track.click');

// Public unsubscribe routes (no auth required)
Route::get('/unsubscribe/{subscriber}', [UnsubscribeController::class, 'show'])->name('unsubscribe');
Route::post('/unsubscribe/{subscriber}', [UnsubscribeController::class, 'unsubscribe'])->name('unsubscribe.confirm');

// Public bounce webhook routes (no auth required)
Route::prefix('api/webhooks/bounces')->group(function () {
    Route::post('/postal', [\App\Http\Controllers\Api\BounceWebhookController::class, 'postal'])->name('webhooks.bounces.postal');
    Route::post('/generic', [\App\Http\Controllers\Api\BounceWebhookController::class, 'generic'])->name('webhooks.bounces.generic');
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/health-check', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json(['status' => 'ok', 'database' => 'connected', 'timestamp' => now()]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'database' => $e->getMessage()], 500);
    }
});

Route::middleware([
    'auth:web',
    config('jetstream.auth_session', 'auth.session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    Route::get('/profile', function () { return 'Profile'; })->name('profile');

    // Inbox
    Route::get('/inbox', \App\Livewire\Inbox\Index::class)->name('inbox');
    Route::get('/inbox/{subscriber}', \App\Livewire\Inbox\Index::class)->name('inbox.show');
    
    // Campaigns
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', \App\Livewire\Campaigns\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Campaigns\Create::class)->name('create');
        Route::get('/{campaign}', \App\Livewire\Campaigns\Show::class)->name('show');
        Route::get('/{campaign}/edit', \App\Livewire\Campaigns\Create::class)->name('edit'); // Re-use create for edit for now
    });
    
    // Debug Trace
    Route::get('/debug-env', function () {
        return [
            'env_key' => env('APP_KEY'),
            'config_key' => config('app.key'),
            'env_file' => app()->environmentFilePath(),
            'config_cached' => app()->configurationIsCached(),
        ];
    });
    
    // Debug Trace
    Route::get('/debug-env', function () {
        return [
            'env_key' => env('APP_KEY'),
            'config_key' => config('app.key'),
            'env_file' => app()->environmentFilePath(),
            'config_cached' => app()->configurationIsCached(),
        ];
    });

    // Mailing Lists
    Route::prefix('lists')->name('lists.')->group(function () {
        Route::get('/', function () { return view('mailing-lists.index'); })->name('index');
        Route::get('/create', function () { return view('mailing-lists.create'); })->name('create');
        Route::get('/{mailingList}/edit', function (\App\Models\MailingList $mailingList) {
            return view('mailing-lists.edit', compact('mailingList'));
        })->name('edit');
    });

    // Subscribers
    // Subscribers
    Route::prefix('subscribers')->name('subscribers.')->group(function () {
        Route::get('/', function () { return view('subscribers.index'); })->name('index');
        Route::get('/create', function () { return view('subscribers.create'); })->name('create');
        Route::get('/import', function () { return view('subscribers.import'); })->name('import');
    });

    // Templates
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', function () { return 'All Templates'; })->name('index');
        Route::get('/create', function () { return 'Create Template'; })->name('create');
    });

    // Inboxes (Delivery Servers)
    Route::prefix('inboxes')->name('inboxes.')->group(function () {
        Route::get('/', function () { return view('inboxes.index'); })->name('index');
        Route::get('/create', function () { return view('inboxes.create'); })->name('create');
        Route::get('/import', function () { return view('inboxes.import'); })->name('import');
        Route::get('/{inbox}/edit', function (\App\Models\DeliveryServer $inbox) { return view('inboxes.edit', ['inbox' => $inbox]); })->name('edit');
    });



    // Bounce Logs
    Route::prefix('bounces')->name('bounces.')->group(function () {
        Route::get('/', \App\Livewire\Bounces\Index::class)->name('index');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/campaigns', function () { return 'Campaign Reports'; })->name('campaigns');
        Route::get('/delivery', function () { return 'Delivery Reports'; })->name('delivery');
    });

    // Settings (Admin only)
    Route::middleware('role:super_admin,admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', function () { return 'Settings'; })->name('index');
        Route::get('/webhooks', \App\Livewire\Settings\Webhooks::class)->name('webhooks');
    });
    
    // User Management (Admin/Super Admin only)
    Route::middleware('role:super_admin,admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', \App\Livewire\Users\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Users\CreateEdit::class)->name('create');
        Route::get('/{userId}/edit', \App\Livewire\Users\CreateEdit::class)->name('edit');
    });
});
