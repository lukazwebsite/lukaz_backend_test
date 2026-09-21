<?php

namespace App\Console\Commands;

use App\Services\BulkDiscountResolver;
use Illuminate\Console\Command;

class BulkDiscountSync extends Command
{
    /**
     * @var string
     */
    protected $signature = 'bulk-discount:sync';

    /**
     * @var string
     */
    protected $description = 'Apply and roll back bulk discount prices for campaigns whose window opened or closed';

    public function handle(BulkDiscountResolver $resolver)
    {
        $touched = $resolver->sync();

        $this->info("Bulk discount sync complete. {$touched} product(s) repriced.");

        return self::SUCCESS;
    }
}
