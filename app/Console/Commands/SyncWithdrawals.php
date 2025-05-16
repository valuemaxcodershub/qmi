<?php

namespace App\Console\Commands;

use App\Http\Controllers\CronJobController;
use Illuminate\Console\Command;

class SyncWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:withdrawal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use in disbursing payment to sellers & users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CronJobController $cronJobController)
    {
        $cronJobController->syncWithdrawals();
    }



}
