<?php

namespace Log1x\AcfComposer\Console;

use Roots\Acorn\Console\Commands\Command;
use Illuminate\Filesystem\Filesystem;

class StubPublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acf:stubs {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the customizable ACF Composer stubs.';

    /**
     * The available stubs.
     *
     * @var array
     */
    protected $stubs = [
        'block.construct.stub',
        'block.stub',
        'field.stub',
        'options.full.stub',
        'options.stub',
        'partial.stub',
        'widget.stub',
        'views/block.stub',
        'views/widget.stub',
    ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! is_dir($stubsPath = $this->app->basePath('stubs/acf-composer'))) {
            (new Filesystem())->makeDirectory($stubsPath, 0755, true);
        }

        if (! is_dir($stubsPath . '/views')) {
            (new Filesystem())->makeDirectory($stubsPath . '/views', 0755, true);
        }

        $files = collect($this->stubs)
            ->mapWithKeys(function ($stub) use ($stubsPath) {
                return [__DIR__ . '/stubs/' . $stub => $stubsPath . '/' . $stub];
            })->toArray();

        foreach ($files as $from => $to) {
            if (! file_exists($to) || $this->option('force')) {
                file_put_contents($to, file_get_contents($from));
            }
        }

        $this->info('🎉 ACF Composer stubs successfully published.');
    }
}
