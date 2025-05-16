<?php

namespace App\Console\Commands;

use App\Http\Controllers\CronJobController;
use Illuminate\Console\Command;

class VerifyWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:verifywithdrawal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use in verifying withdrawal with Flutterwave...';

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
        $cronJobController->verifyWithdrawal();
    }
}
