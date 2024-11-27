<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PendingInvoiceController;

class UpdatePendingInvoices extends Command
{
    protected $signature = 'invoices:updatePendingInvoices';

    protected $description = 'Update and process pending invoices on the 1st of every month at 2:00 AM';

    public function handle()
    {
        $pendingInvoiceController = new PendingInvoiceController();
        $pendingInvoiceController->updatePendingInvoices();

        $this->info('Pending invoices updated successfully.');
    }
}
