<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Package;
use App\Models\PackageScan;
use App\Models\Product;
use App\Models\Traits\HasSlug;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * Non-destructive backfill of the `slug` column.
 *
 * Only rows with a NULL slug are touched, so it can be re-run safely at any
 * time and never overwrites an existing slug. Slugs are generated with the
 * model's own {@see HasSlug} rules (source attribute,
 * tenant-scoped uniqueness and collision suffixes).
 */
class BackfillSlugs extends Command
{
    protected $signature = 'slugs:backfill';

    protected $description = 'Genera el slug de los registros existentes que aún no lo tienen';

    /**
     * @var array<int, class-string<Model>>
     */
    protected array $models = [
        Business::class,
        User::class,
        DeliveryDriver::class,
        Product::class,
        Order::class,
        Package::class,
        OrderItem::class,
        InventoryMovement::class,
        PackageScan::class,
        OrderStatusLog::class,
    ];

    public function handle(): int
    {
        foreach ($this->models as $class) {
            $updated = 0;

            $class::query()
                ->withoutGlobalScopes()
                ->whereNull('slug')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use (&$updated) {
                    foreach ($rows as $model) {
                        $model->slug = $model->generateSlug();
                        $model->saveQuietly();
                        $updated++;
                    }
                });

            $this->line(sprintf('%-18s %d registro(s) actualizado(s)', class_basename($class).':', $updated));
        }

        $this->info('Backfill de slugs completado.');

        return self::SUCCESS;
    }
}
