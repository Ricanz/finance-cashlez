<?php

namespace App\Console\Commands;

use App\Helpers\BackOffice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform daily tasks from Back Office';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        BackOffice::job();
        return Command::SUCCESS;
    }
}
