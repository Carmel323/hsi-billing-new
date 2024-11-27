<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ClicksEmailAlertController;

class SendClickAlerts extends Command
{
    protected $signature = 'clicks:send-alerts';

    protected $description = 'Send email alerts for click usage thresholds';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = new ClicksEmailAlertController();
        
        $controller->sendAlerts();

        $this->info('Click alerts sent successfully.');
    }
}
