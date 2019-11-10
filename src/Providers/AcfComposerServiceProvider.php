<?php

namespace Log1x\AcfComposer\Providers;

use Roots\Acorn\ServiceProvider;

class AcfComposerServiceProvider extends ServiceProvider
{
    /**
     * Register and compose fields.
     *
     * @return void
     */
    public function register()
    {
        collect($this->app->config->get('acf.blocks'))
            ->each(function ($block) {
                if (is_string($block)) {
                    $block = new $block($this->app);
                }

                $block->compose();
            });

        collect($this->app->config->get('acf.fields'))
            ->each(function ($field) {
                if (is_string($field)) {
                    $field = new $field($this->app);
                }

                $field->compose();
            });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/acf.php'  => $this->app->configPath('acf.php'),
            __DIR__ . '/../resources/views/view-404.blade.php' => $this->app->resourcePath('views/blocks/view-404.blade.php')
        ]);

        $this->commands([
            \Log1x\AcfComposer\Console\BlockMakeCommand::class,
            \Log1x\AcfComposer\Console\FieldMakeCommand::class,
        ]);
    }
}
