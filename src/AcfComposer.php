<?php

namespace Log1x\AcfComposer;

use ReflectionClass;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Roots\Acorn\Application;
use Symfony\Component\Finder\Finder;

class AcfComposer
{
    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * The registered paths.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * The registered composers.
     *
     * @var array
     */
    protected $composers = [];

    /**
     * The registered plugin paths.
     *
     * @var array
     */
    protected $plugins = [];

    /**
     * The composer classes.
     *
     * @var array
     */
    protected $classes = [
        'Fields',
        'Blocks',
        'Widgets',
        'Options',
    ];

    /**
     * Create a new Composer instance.
     *
     * @param  \Roots\Acorn\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->registerPath($this->app->path());
    }

    /**
     * Register the default theme paths with ACF Composer.
     *
     * @param  string $path
     * @param  string $namespace
     * @return array
     */
    public function registerPath($path, $namespace = null)
    {
        if (! function_exists('acf')) {
            return;
        }

        $paths = collect(File::directories($path))->filter(function ($item) {
            return Str::contains($item, $this->classes);
        });

        if ($paths->isEmpty()) {
            return;
        }

        if (empty($namespace)) {
            $namespace = $this->app->getNamespace();
        }

        foreach ((new Finder())->in($paths->toArray())->files() as $file) {
            $relativePath = Str::remove(
                $this->app->path() . DIRECTORY_SEPARATOR,
                $file->getPathname()
            );

            $folders = Str::beforeLast(
                $relativePath,
                DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR;

            $className = Str::after($relativePath, $folders);

            $composer = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                $folders . $className
            );

            if (
                ! is_subclass_of($composer, Composer::class) ||
                is_subclass_of($composer, Partial::class) ||
                (new ReflectionClass($composer))->isAbstract()
            ) {
                continue;
            }

            $this->composers[$namespace][] = (new $composer($this->app))->compose();
            $this->paths[dirname($file->getPath())][] = $composer;
        }

        return $this->paths;
    }

    /**
     * Register an ACF Composer plugin with the container.
     *
     * @return void
     */
    public function registerPlugin($path, $namespace)
    {
        $namespace = str_replace('Providers', '', $namespace);

        $this->registerPath($path, $namespace);

        $this->plugins[$namespace] = dirname($path);
    }

    /**
     * Retrieve the registered composers.
     *
     * @return array
     */
    public function getComposers()
    {
        return $this->composers;
    }

    /**
     * Retrieve the registered paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return array_unique($this->paths);
    }

    /**
     * Retrieve the registered plugins.
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }
}
