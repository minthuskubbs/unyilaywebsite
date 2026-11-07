<?php

namespace App\Console\Commands;

use App\Services\BrassShowroomManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

/**
 * Loads the prepared catalogue (resources/showroom/default.json) into the private draft and prints
 * a preview link. Nothing goes live until it is run again with --publish=<draft revision>.
 */
final class ImportBrassShowroomCatalogue extends Command
{
    protected $signature = 'brass-showroom:import-catalogue
        {--publish= : Publish the draft; pass the draft revision printed by the import}
        {--force : Overwrite unpublished draft edits}
        {--user=1 : User id recorded as making the change}';

    protected $description = 'Load the prepared showroom catalogue into the draft, then publish it after review';

    public function handle(BrassShowroomManager $showroom): int
    {
        $userId = (int) $this->option('user');

        if ($this->option('publish') !== null) {
            return $this->publish($showroom, (int) $this->option('publish'), $userId);
        }

        $current = $showroom->view('draft');
        if ($current['config'] != $showroom->defaults()) {
            if ($current['has_unpublished_changes'] && ! $this->option('force')) {
                $this->error('The draft has unpublished edits that this would overwrite. Publish them first, or rerun with --force to discard them.');

                return self::FAILURE;
            }
            $current = $showroom->importCatalogueToDraft($userId);
        }

        $rooms = collect($current['config']['artworks'])->countBy(fn (array $artwork): string => $artwork['room'] ?? 'brass');
        $minutes = (int) config('brass-showroom.preview_minutes', 20);

        $this->info("Draft holds {$rooms->get('silver', 0)} silver and {$rooms->get('brass', 0)} brass artworks. The live showroom has not changed.");
        $this->line("Preview (valid {$minutes} minutes; rerun this command for a new link):");
        $this->line(URL::temporarySignedRoute('brass-showroom.preview', now()->addMinutes($minutes)));
        $this->line('After reviewing, publish with:');
        $this->line("php artisan brass-showroom:import-catalogue --publish={$current['draft_revision']}");

        return self::SUCCESS;
    }

    private function publish(BrassShowroomManager $showroom, int $revision, int $userId): int
    {
        try {
            $published = $showroom->publish($revision, 'Imported prepared silver and brass catalogue', $userId);
        } catch (ValidationException $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }

        $this->info('Published. Live showroom is now revision '.$published['published_revision'].' with '.count($published['config']['artworks']).' artworks.');

        return self::SUCCESS;
    }
}
