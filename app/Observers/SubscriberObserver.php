<?php

namespace App\Observers;

use App\Models\Subscriber;

class SubscriberObserver
{
    /**
     * Handle the Subscriber "created" event.
     */
    public function created(Subscriber $subscriber): void
    {
        // Increment the mailing list subscribers count
        $subscriber->mailingList()->increment('subscribers_count');
    }

    /**
     * Handle the Subscriber "deleted" event.
     */
    public function deleted(Subscriber $subscriber): void
    {
        // Decrement the mailing list subscribers count
        $subscriber->mailingList()->decrement('subscribers_count');
    }

    /**
     * Handle the Subscriber "restored" event.
     */
    public function restored(Subscriber $subscriber): void
    {
        // Increment the count when restored
        $subscriber->mailingList()->increment('subscribers_count');
    }

    /**
     * Handle the Subscriber "force deleted" event.
     */
    public function forceDeleted(Subscriber $subscriber): void
    {
        // Decrement the count on force delete
        $subscriber->mailingList()->decrement('subscribers_count');
    }
}
