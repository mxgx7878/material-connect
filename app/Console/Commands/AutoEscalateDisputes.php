<?php
// FILE PATH: app/Console/Commands/AutoEscalateDisputes.php

namespace App\Console\Commands;

use App\Models\Dispute;
use App\Services\DisputeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoEscalateDisputes extends Command
{
    protected $signature   = 'disputes:auto-escalate';
    protected $description = 'Escalate disputes where the supplier has not responded within the 48-hour window';

    public function handle(DisputeService $service): int
    {
        $candidates = Dispute::where('status', 'awaiting_supplier_response')
            ->whereNotNull('supplier_response_deadline')
            ->where('supplier_response_deadline', '<', now())
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('No disputes to escalate.');
            return self::SUCCESS;
        }

        $escalated = 0;
        $failed    = 0;

        foreach ($candidates as $dispute) {
            try {
                $service->escalateDispute($dispute);
                $this->info("Escalated: {$dispute->dispute_number}");
                $escalated++;
            } catch (\Exception $e) {
                Log::error('Auto-escalate failed', [
                    'dispute_id'     => $dispute->id,
                    'dispute_number' => $dispute->dispute_number,
                    'error'          => $e->getMessage(),
                ]);
                $this->error("Failed: {$dispute->dispute_number} — {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Done. Escalated: {$escalated}, Failed: {$failed}");
        return self::SUCCESS;
    }
}