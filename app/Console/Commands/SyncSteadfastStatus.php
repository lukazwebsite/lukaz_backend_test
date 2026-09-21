<?php

namespace App\Console\Commands;

use App\Models\Admin\OrderConsignment;
use App\Services\Steadfast\SteadfastBookingService;
use App\Services\Steadfast\SteadfastException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSteadfastStatus extends Command
{
    protected $signature = 'steadfast:sync {--limit=200 : How many consignments to refresh in one run}';

    protected $description = 'Refresh delivery status for open Steadfast consignments';

    public function handle(SteadfastBookingService $booking)
    {
        if (!config('steadfast.enabled')) {
            $this->info('Steadfast is disabled, nothing to sync.');

            return self::SUCCESS;
        }

        $final = config('steadfast.final_statuses', []);

        // Only consignments that can still change, oldest sync first so a
        // large backlog drains evenly instead of starving the tail.
        $consignments = OrderConsignment::with('order')
            ->where('courier', 'steadfast')
            ->where(function ($query) use ($final) {
                $query->whereNull('delivery_status')
                    ->orWhereNotIn('delivery_status', $final);
            })
            ->orderByRaw('last_synced_at is null desc')
            ->orderBy('last_synced_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($consignments->isEmpty()) {
            $this->info('No open consignments.');

            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($consignments as $consignment) {
            try {
                $booking->sync($consignment);
                $synced++;
            } catch (SteadfastException $e) {
                // One bad consignment must not stop the run.
                $failed++;

                Log::warning('Steadfast sync failed', [
                    'order_no' => $consignment->order_no,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Synced ' . $synced . ' consignment(s), ' . $failed . ' failed.');

        return self::SUCCESS;
    }
}
