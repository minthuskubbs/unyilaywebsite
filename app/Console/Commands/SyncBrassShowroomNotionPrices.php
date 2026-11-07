<?php

namespace App\Console\Commands;

use App\Services\BrassShowroomManager;
use App\Services\BrassShowroomNotionClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Copies Sale Price from the Notion databases to the live showroom.
 * Scheduled every six hours (see app/Console/Kernel.php.snippet); safe to run by hand.
 */
final class SyncBrassShowroomNotionPrices extends Command
{
    protected $signature = 'brass-showroom:sync-notion {--dry-run : Show the price changes without saving them}';

    protected $description = 'Update showroom artwork prices from the Notion databases';

    public function handle(BrassShowroomNotionClient $notion, BrassShowroomManager $showroom): int
    {
        if (! config('brass-showroom.notion.sync_enabled') && ! $this->option('dry-run')) {
            $this->info('Notion price sync is disabled (BRASS_SHOWROOM_NOTION_SYNC_ENABLED=false).');

            return self::SUCCESS;
        }

        try {
            $prices = $notion->prices();
            $changes = $showroom->syncPricesFromNotion($prices, (bool) $this->option('dry-run'));
        } catch (Throwable $error) {
            Log::warning('Brass showroom Notion sync failed', ['error' => $error->getMessage()]);
            $this->error($error->getMessage());

            return self::FAILURE;
        }

        foreach ($changes as $change) {
            $this->line(sprintf('%s (%s): %s → %s MMK', $change['title'], $change['id'], number_format($change['from']), number_format($change['to'])));
        }
        $verb = $this->option('dry-run') ? 'would change' : 'changed';
        $this->info(count($prices).' Notion prices read; '.count($changes)." price(s) {$verb}.");
        if ($changes !== [] && ! $this->option('dry-run')) {
            Log::info('Brass showroom prices updated from Notion', ['changes' => $changes]);
        }

        return self::SUCCESS;
    }
}
