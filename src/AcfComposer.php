<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Roots\Acorn\Application;
use Symfony\Component\Finder\Finder;

class AcfComposer
{
    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    public $app;

    /**
     * The registered paths.
     */
    protected array $paths = [];

    /**
     * The registered composers.
     */
    protected array $composers = [];

    /**
     * The deferred composers.
     */
    protected array $deferredComposers = [];

    /**
     * The registered plugin paths.
     */
    protected array $plugins = [];

    /**
     * The cache manifest.
     */
    protected Manifest $manifest;

    /**
     * The composer classes.
     */
    protected array $classes = [
        'Fields',
        'Blocks',
        'Widgets',
        'Options',
    ];

    /**
     * Create a new Composer instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->manifest = Manifest::make($this);
    }

    /**
     * Make a new Composer instance.
     */
    public static function make(Application $app): self
    {
        return new static($app);
    }

    /**
     * Handle the ACF Composer instance.
     */
    public function handle(): void
    {
        add_action('acf/init', fn () => $this->boot());
    }

    /**
     * Boot the registered Composers.
     */
    public function boot(): void
    {
        $this->registerDefaultPath();

        foreach ($this->composers as $namespace => $composers) {
            foreach ($composers as $i => $composer) {
                $this->composers[$namespace][$i] = $composer->compose();
            }
        }

        foreach ($this->deferredComposers as $namespace => $composers) {
            foreach ($composers as $index => $composer) {
                $this->composers[$namespace][] = $composer->compose();
            }
        }

        $this->deferredComposers = [];
    }

    /**
     * Register the default application path.
     */
    public function registerDefaultPath(): void
    {
        $this->registerPath($this->app->path());
    }

    /**
     * Register the specified path with ACF Composer.
     */
    public function registerPath(string $path, ?string $namespace = null): array
    {
        $paths = collect(File::directories($path))
            ->filter(fn ($item) => Str::contains($item, $this->classes));

        if ($paths->isEmpty()) {
            return [];
        }

        if (empty($namespace)) {
            $namespace = $this->app->getNamespace();
        }

        foreach ((new Finder())->in($paths->toArray())->files()->sortByName() as $file) {
            $relativePath = str_replace(
                Str::finish($path, DIRECTORY_SEPARATOR),
                '',
                $file->getPathname()
            );

            $folders = Str::beforeLast(
                $relativePath,
                DIRECTORY_SEPARATOR
            ).DIRECTORY_SEPARATOR;

            $className = Str::after($relativePath, $folders);

            $composer = $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                $folders.$className
            );

            $this->paths[$path][] = $composer;

            $this->register($composer, $namespace);
        }

        return $this->paths;
    }

    /**
     * Register a Composer with ACF Composer.
     */
    public function register(string $composer, string $namespace): bool
    {
        if (
            ! is_subclass_of($composer, Composer::class) ||
            is_subclass_of($composer, Partial::class) ||
            (new ReflectionClass($composer))->isAbstract()
        ) {
            return false;
        }

        $composer = $composer::make($this);

        if (is_subclass_of($composer, Options::class) && ! is_null($composer->parent)) {
            $this->deferredComposers[$namespace][] = $composer;

            return true;
        }

        $this->composers[$namespace][] = $composer;

        return true;
    }

    /**
     * Register an ACF Composer plugin with the container.
     */
    public function registerPlugin(string $path, string $namespace): void
    {
        $namespace = str_replace('Providers', '', $namespace);

        $this->registerPath($path, $namespace);

        $this->plugins[$namespace] = $path;
    }

    /**
     * Retrieve the registered composers.
     */
    public function composers(): array
    {
        return $this->composers;
    }

    /**
     * Retrieve the registered paths.
     */
    public function paths(): array
    {
        return array_unique($this->paths);
    }

    /**
     * Retrieve the registered plugins.
     */
    public function plugins(): array
    {
        return $this->plugins;
    }

    /**
     * Retrieve the cache manifest.
     */
    public function manifest(): Manifest
    {
        return $this->manifest;
    }
}
