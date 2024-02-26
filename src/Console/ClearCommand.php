<?php

namespace Log1x\AcfComposer\Console;

use Illuminate\Console\Command;
use Log1x\AcfComposer\AcfComposer;

class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acf:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the ACF field group cache';

    /**
     * The ACF Composer instance.
     */
    protected AcfComposer $composer;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->composer = $this->laravel['AcfComposer'];

        return $this->composer->clearCache()
            ? $this->components->info('Successfully cleared the <fg=blue>ACF Composer</> cache manifest.')
            : $this->components->info('The <fg=blue>ACF Composer</> cache manifest is already cleared.');
    }
}
