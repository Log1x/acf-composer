<?php

namespace Log1x\AcfComposer\Console;

use Illuminate\Console\Command;
use Log1x\AcfComposer\AcfComposer;

class CacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acf:cache
                            {--clear : Clear the cached field groups}
                            {--status : Show the current cache status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache the ACF field groups';

    /**
     * The ACF Composer instance.
     */
    protected AcfComposer $composer;

    /**
     * The cached field group count.
     */
    protected int $count = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->composer = $this->laravel['AcfComposer'];

        if ($this->option('clear')) {
            return $this->call('acf:clear');
        }

        if ($this->option('status')) {
            return $this->composer->manifestExists()
                ? $this->components->info('The <fg=blue>ACF Composer</> field groups are currently <fg=green;options=bold>cached</>.')
                : $this->components->info('The <fg=blue>ACF Composer</> field groups are currently <fg=red;options=bold>not cached</>.');
        }

        $composers = collect(
            $this->composer->getComposers()
        )->flatten();

        $composers->each(function ($composer) {
            if (! $this->composer->cache($composer)) {
                $this->components->error('<fg=red>'.$composer::class.'</fg> failed to cache.');

                return;
            }

            $this->count++;
        });

        $this->components->info("<fg=blue>{$this->count}</> field group(s) cached successfully.");
    }
}
