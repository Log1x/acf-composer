<?php

namespace Log1x\AcfComposer\Providers;

use Roots\Acorn\ServiceProvider;

class AcfComposerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (! function_exists('acf')) {
            return;
        }

        collect($this->app->config->get('acf.fields'))
            ->merge($this->app->config->get('acf.blocks'))
            ->merge($this->app->config->get('acf.widgets'))
            ->each(function ($field) {
                if (is_string($field)) {
                    if (! class_exists($field)) {
                        return;
                    }

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
            __DIR__ . '/../../config/acf.php' => $this->app->configPath('acf.php'),
        ], 'acf-composer');

        $this->commands([
            \Log1x\AcfComposer\Console\FieldMakeCommand::class,
            \Log1x\AcfComposer\Console\BlockMakeCommand::class,
            \Log1x\AcfComposer\Console\WidgetMakeCommand::class,
        ]);
    }
}
