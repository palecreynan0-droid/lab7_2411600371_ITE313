<?php

namespace App\Console\Commands;

use App\Services\ReorderService;
use Illuminate\Console\Command;

class CheckReorderPoints extends Command
{
    protected $signature = 'inventory:check-reorder';
    protected $description = 'Check stock against reorder points and generate draft purchase orders';

    public function handle(ReorderService $service): int
    {
        $this->info('Checking reorder points...');

        $orders = $service->generatePurchaseOrders();

        if ($orders->isEmpty()) {
            $this->info('No products need reordering.');
            return self::SUCCESS;
        }

        $this->table(
            ['Reference', 'Supplier', 'Items', 'Total'],
            $orders->map(fn ($po) => [
                $po->reference_number,
                $po->supplier->company_name,
                $po->items()->count(),
                number_format($po->total, 2),
            ])
        );

        $this->info($orders->count() . ' draft purchase order(s) created.');

        // Part 4: email administrators here.

        return self::SUCCESS;
    }
}