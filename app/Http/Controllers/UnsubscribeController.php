<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    /**
     * Show unsubscribe confirmation page
     */
    public function show(Subscriber $subscriber)
    {
        if ($subscriber->status === 'unsubscribed') {
            return view('unsubscribe.already', compact('subscriber'));
        }
        
        return view('unsubscribe.confirm', compact('subscriber'));
    }
    
    /**
     * Process unsubscribe request
     */
    public function unsubscribe(Request $request, Subscriber $subscriber)
    {
        if ($subscriber->status !== 'unsubscribed') {
            $subscriber->unsubscribe();
        }
        
        return view('unsubscribe.success', compact('subscriber'));
    }
}
