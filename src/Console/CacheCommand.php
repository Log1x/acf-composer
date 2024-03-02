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
                            {--clear : Clear the cache}
                            {--status : Show the current cache status}
                            {--manifest : Only write the field group manifest}
                            {--force : Ignore errors when writing cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache the ACF Composer field groups and blocks.';

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
            $status = $this->composer->manifest()->exists()
                ? '<fg=green;options=bold>cached</>'
                : '<fg=red;options=bold>not cached</>';

            return $this->components->info("<fg=blue>ACF Composer</> is currently {$status}.");
        }

        $composers = collect(
            $this->composer->composers()
        )->flatten();

        $composers->each(function ($composer) {
            if (! $this->composer->manifest()->add($composer)) {
                $name = $composer::class;

                return $this->components->error("Failed to add <fg=red>{$name}</> to the manifest.");
            }

            $this->count++;
        });

        if ($this->count !== $composers->count() && ! $this->option('force')) {
            return $this->components->error('Failed to cache the <fg=red>ACF Composer</> field groups.');
        }

        if (! $manifest = $this->composer->manifest()->write()) {
            return $this->components->error('Failed to write the <fg=red>ACF Composer</> manifest.');
        }

        $blocks = ! $this->option('manifest')
            ? $this->composer->manifest()->writeBlocks()
            : 0;

        $this->components->info("Successfully cached <fg=blue>{$manifest}</> field group(s) and <fg=blue>{$blocks}</> block(s).");
    }
}
