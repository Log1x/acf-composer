<?php

namespace Log1x\AcfComposer\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Log1x\AcfComposer\AcfComposer;
use Log1x\AcfComposer\Console;

class AcfComposerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('AcfComposer', fn () => AcfComposer::make($this->app));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/acf.php' => $this->app->configPath('acf.php'),
        ], 'acf-composer');

        $this->mergeConfigFrom(__DIR__.'/../../config/acf.php', 'acf');

        $composer = $this->app->make('AcfComposer');

        $composer->boot();

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\BlockMakeCommand::class,
                Console\CacheCommand::class,
                Console\ClearCommand::class,
                Console\FieldMakeCommand::class,
                Console\IdeHelpersCommand::class,
                Console\OptionsMakeCommand::class,
                Console\PartialMakeCommand::class,
                Console\StubPublishCommand::class,
                Console\UpgradeCommand::class,
                Console\WidgetMakeCommand::class,
            ]);

            if (class_exists(AboutCommand::class) && class_exists(InstalledVersions::class)) {
                AboutCommand::add('ACF Composer', [
                    'Status' => $composer->manifest()->exists() ? '<fg=green;options=bold>CACHED</>' : '<fg=yellow;options=bold>NOT CACHED</>',
                    'Version' => InstalledVersions::getPrettyVersion('log1x/acf-composer'),
                ]);
            }
        }
    }
}
