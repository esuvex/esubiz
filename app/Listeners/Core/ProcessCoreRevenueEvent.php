<?php

namespace App\Listeners\Core;

use App\Services\Core\ReferralRevenueProcessor;
use Illuminate\Support\Facades\DB;

class ProcessCoreRevenueEvent
{
    public function __construct(
        protected ReferralRevenueProcessor $referralProcessor
    ) {
    }

    public function handle($event): void
    {
        $this->processForTransaction(
            $event->sourceType,
            $event->sourceId
        );
    }

    public function process(object $revenueEvent): void
    {
        DB::table('revenue_events')
            ->where('id', $revenueEvent->id)
            ->update([
                'is_processed' => true,
                'status' => 'processed',
                'updated_at' => now(),
            ]);

        $revenueEvent = DB::table('revenue_events')
            ->where('id', $revenueEvent->id)
            ->first();

        if ($revenueEvent) {
            $this->referralProcessor->process($revenueEvent);
        }
    }

    public function processForTransaction(
        string $sourceType,
        int|string|null $sourceId
    ): void {
        $revenueEvent = DB::table('revenue_events')
            ->where('source_module', $sourceType)
            ->where('reference_id', $sourceId)
            ->latest('id')
            ->first();

        if ($revenueEvent) {
            $this->process($revenueEvent);
        }
    }
}
