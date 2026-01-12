<?php

namespace App\Console\Commands;

use App\Models\MailingList;
use Illuminate\Console\Command;

class RecalculateListCountsCommand extends Command
{
    protected $signature = 'lists:recalculate-counts';
    protected $description = 'Recalculate subscriber counts for all mailing lists';

    public function handle()
    {
        $this->info('Recalculating subscriber counts...');

        $lists = MailingList::withTrashed()->get();

        foreach ($lists as $list) {
            $count = $list->subscribers()->count();
            $list->update(['subscribers_count' => $count]);
            
            $this->info("Updated {$list->name}: {$count} subscribers");
        }

        $this->info('Done!');

        return Command::SUCCESS;
    }
}
