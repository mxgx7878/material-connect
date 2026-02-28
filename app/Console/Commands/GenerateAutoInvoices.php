<?php
// FILE PATH: app/Console/Commands/GenerateAutoInvoices.php

namespace App\Console\Commands;

use App\Services\AutoInvoiceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAutoInvoices extends Command
{
    /**
     * The name and signature of the console command.
     * This is what you run: php artisan invoice:auto-generate
     */
    protected $signature = 'invoice:auto-generate';

    /**
     * Human-readable description shown in php artisan list
     */
    protected $description = 'Automatically generate invoices for deliveries scheduled for tomorrow where both supplier and client have confirmed.';

    public function __construct(protected AutoInvoiceService $autoInvoiceService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = now();
        $tomorrow  = Carbon::tomorrow()->toDateString();

        $this->info("========================================");
        $this->info("  Auto Invoice Generator");
        $this->info("  Run Time : {$startTime->toDateTimeString()}");
        $this->info("  Target   : Deliveries on {$tomorrow}");
        $this->info("========================================");

        try {
            $summary = $this->autoInvoiceService->run();

            $this->newLine();
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Orders Checked',   $summary['orders_checked']],
                    ['Orders Skipped',   $summary['orders_skipped']],
                    ['Invoices Created', $summary['invoices_created']],
                    ['Orders Failed',    $summary['orders_failed']],
                    ['Failures Logged',  $summary['failures_logged']],
                ]
            );
            $this->newLine();

            if ($summary['orders_failed'] > 0) {
                $this->warn("⚠  {$summary['orders_failed']} order(s) failed. Check failed_invoice_logs table for details.");
            }

            if ($summary['invoices_created'] > 0) {
                $this->info("✓  {$summary['invoices_created']} invoice(s) created successfully.");
            } else {
                $this->info("✓  No invoices needed for {$tomorrow}.");
            }

            $this->newLine();
            $this->info("Completed in " . now()->diffInSeconds($startTime) . "s");
            $this->info("========================================");

        } catch (\Throwable $e) {
            $this->error("Critical failure in AutoInvoiceService: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}