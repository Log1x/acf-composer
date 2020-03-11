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
        if (! function_exists('acf')) {
            return;
        }

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

       collect($this->app->config->get('acf.widgets'))
           ->each(function ($widget) {
               if (is_string($widget)) {
                   $widget = new $widget($this->app);
               }

               $widget->compose();
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
