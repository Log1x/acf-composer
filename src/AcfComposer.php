<?php

namespace Log1x\AcfComposer;

use ReflectionClass;
use Illuminate\Support\Str;
use Log1x\AcfComposer\Composer;
use Log1x\AcfComposer\Partial;
use Roots\Acorn\Application;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\File;

class AcfComposer
{
   /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $file;

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
     * @return void
     */
    public function registerPath($path, $namespace = null)
    {
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
            $composer = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($file->getPathname(), Str::beforeLast($file->getPath(), '/') . DIRECTORY_SEPARATOR)
            );

            if (
                ! is_subclass_of($composer, Composer::class) ||
                is_subclass_of($composer, Partial::class) ||
                (new ReflectionClass($composer))->isAbstract()
            ) {
                continue;
            }

            $this->composers[$namespace][] = (new $composer($this->app))->compose();
            $this->paths[$file->getPath()][] = $composer;
        }

        return $this->paths;
    }

    /**
     * Retrieve the registered field.
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
        return $this->paths;
    }
}
