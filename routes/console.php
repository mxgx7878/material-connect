<?php
// FILE PATH: routes/console.php
//
// This file registers scheduled artisan commands.
// Laravel 12 uses this file for scheduling — no need for Kernel.php.
//
// HOW THE CRON WORKS:
//  - You set ONE single cron job in WHM/cPanel that runs every minute:
//      /usr/bin/php /home/u415064741/{your-project-folder}/artisan schedule:run
//  - Laravel's scheduler then decides internally which commands to fire
//    based on the ->dailyAt() schedule defined here.
//
// This means even though cPanel runs it every minute, only at 17:00
// will Laravel actually execute invoice:auto-generate.

use Illuminate\Support\Facades\Schedule;

// ─── Auto Invoice Generator ──────────────────────────────────────────────────
// Runs daily at 5:00 PM (17:00).
// Finds all deliveries scheduled for tomorrow where:
//   - order_item.supplier_confirms = 1
//   - order_item.client_confirms   = 1
//   - order_item_delivery.invoice_id IS NULL
// Creates one combined invoice per order and pushes to Xero.
// Failures are logged to failed_invoice_logs table.
Schedule::command('invoice:auto-generate')
    // ->dailyAt('17:00')
    ->everyFiveMinutes()
    ->withoutOverlapping()   // Prevents duplicate runs if a previous run is still processing
    ->runInBackground()      // Runs as a background process (non-blocking)
    ->appendOutputTo(storage_path('logs/auto-invoice.log'));  // Keeps a dedicated log file