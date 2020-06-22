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
     * Default Paths
     *
     * @var array
     */
    protected $paths = [
        'Fields',
        'Blocks',
        'Widgets',
        'Options',
    ];

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

        if ($this->paths->isEmpty() || ! function_exists('acf')) {
            return;
        }

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
                (new $composer($this->app))->compose();
            }
        }
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
            \Log1x\AcfComposer\Console\BlockMakeCommand::class,
            \Log1x\AcfComposer\Console\FieldMakeCommand::class,
            \Log1x\AcfComposer\Console\PartialMakeCommand::class,
            \Log1x\AcfComposer\Console\WidgetMakeCommand::class,
            \Log1x\AcfComposer\Console\OptionsMakeCommand::class,
        ]);
    }
}
