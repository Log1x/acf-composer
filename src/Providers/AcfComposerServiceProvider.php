<?php

namespace Log1x\AcfComposer\Providers;

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

        $this->commands([
            Console\BlockMakeCommand::class,
            Console\CacheCommand::class,
            Console\FieldMakeCommand::class,
            Console\OptionsMakeCommand::class,
            Console\PartialMakeCommand::class,
            Console\StubPublishCommand::class,
            Console\WidgetMakeCommand::class,
            Console\UpgradeCommand::class,
        ]);

        $this->app->make('AcfComposer');
    }
}
