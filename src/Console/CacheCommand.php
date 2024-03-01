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
                            {--status : Show the current cache status}
                            {--force : Force cache the field groups}';

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
            return $this->composer->manifest()->exists()
                ? $this->components->info('The <fg=blue>ACF Composer</> field groups are currently <fg=green;options=bold>cached</>.')
                : $this->components->info('The <fg=blue>ACF Composer</> field groups are currently <fg=red;options=bold>not cached</>.');
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

        if (! $this->composer->manifest()->write()) {
            return $this->components->error('Failed to write the <fg=red>ACF Composer</> manifest.');
        }

        $this->components->info("<fg=blue>{$this->count}</> field group(s) cached successfully.");
    }
}
