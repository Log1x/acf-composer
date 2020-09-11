<?php

namespace Log1x\AcfComposer\Providers;

use ReflectionClass;
use Illuminate\Support\Str;
use Log1x\AcfComposer\Composer;
use Log1x\AcfComposer\Partial;
use Roots\Acorn\ServiceProvider;
use Symfony\Component\Finder\Finder;

class AcfComposerServiceProvider extends ServiceProvider
{
    /**
     * The default paths.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $paths = [
        'Fields',
        'Blocks',
        'Widgets',
        'Options',
    ];

    /**
     * The registered field groups.
     *
     * @var array
     */
     protected $fields = [];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->paths = collect($this->paths)->map(function ($path) {
            return $this->app->path($path);
        })->filter(function ($path) {
            return is_dir($path);
        });

        $this->app->singleton('AcfComposer', function () {
            return $this->compose();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (
            function_exists('acf') &&
            ! $this->paths->isEmpty()
        ) {
            $this->app->make('AcfComposer');
        }

        $this->publishes([
            __DIR__ . '/../../config/acf.php' => $this->app->configPath('acf.php'),
        ], 'config');

        $this->commands([
            \Log1x\AcfComposer\Console\BlockMakeCommand::class,
            \Log1x\AcfComposer\Console\FieldMakeCommand::class,
            \Log1x\AcfComposer\Console\PartialMakeCommand::class,
            \Log1x\AcfComposer\Console\WidgetMakeCommand::class,
            \Log1x\AcfComposer\Console\OptionsMakeCommand::class,
        ]);
    }

    /**
     * Find and compose the available field groups.
     *
     * @return void
     */
    public function compose()
    {
        foreach ((new Finder())->in($this->paths->all())->files() as $composer) {
            $composer = $this->app->getNamespace() . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($composer->getPathname(), $this->app->path() . DIRECTORY_SEPARATOR)
            );

            if (
                is_subclass_of($composer, Composer::class) &&
                ! is_subclass_of($composer, Partial::class) &&
                ! (new ReflectionClass($composer))->isAbstract()
            ) {
                $this->fields[] = (new $composer($this->app))->compose();
            }
        }

        return $this->fields;
    }
}
