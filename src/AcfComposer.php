<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Collection;
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
     * The cached manifest.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $manifest;

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

        add_action('acf/init', fn () => $this->registerPath($this->app->path()));
    }

    /**
     * Make a new Composer instance.
     */
    public static function make(Application $app): self
    {
        return new static($app);
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

            if (
                ! is_subclass_of($composer, Composer::class) ||
                is_subclass_of($composer, Partial::class) ||
                (new ReflectionClass($composer))->isAbstract()
            ) {
                continue;
            }

            $this->paths[dirname($file->getPath())][] = $composer;

            $composer = $composer::make($this);

            if (is_subclass_of($composer, Options::class) && ! is_null($composer->parent)) {
                $this->deferredComposers[$namespace][] = $composer;

                continue;
            }

            $this->composers[$namespace][] = $composer->compose();
        }

        foreach ($this->deferredComposers as $namespace => $composers) {
            foreach ($composers as $composer) {
                $this->composers[$namespace][] = $composer->compose();
            }
        }

        $this->deferredComposers = [];

        return $this->paths;
    }

    /**
     * Register an ACF Composer plugin with the container.
     */
    public function registerPlugin(string $path, string $namespace): void
    {
        $namespace = str_replace('Providers', '', $namespace);

        $this->registerPath($path, $namespace);

        $this->plugins[$namespace] = dirname($path);
    }

    /**
     * Retrieve the registered composers.
     */
    public function getComposers(): array
    {
        return $this->composers;
    }

    /**
     * Retrieve the registered paths.
     */
    public function getPaths(): array
    {
        return array_unique($this->paths);
    }

    /**
     * Retrieve the registered plugins.
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Retrieve the cache manifest.
     */
    protected function manifest(): Collection
    {
        if ($this->manifest) {
            return $this->manifest;
        }

        if (! $this->manifestExists()) {
            return $this->manifest = collect();
        }

        return $this->manifest = Collection::make(require $this->manifestPath());
    }

    /**
     * Retrieve the cache manifest path.
     */
    protected function manifestPath(): string
    {
        $path = config('acf-composer.manifest', storage_path('framework/cache'));

        if (Str::endsWith($path, '.php')) {
            return $path;
        }

        return Str::finish($path, '/').'acf-composer.php';
    }

    /**
     * Determine if the cache manifest exists.
     */
    public function manifestExists(): bool
    {
        return file_exists($this->manifestPath());
    }

    /**
     * Cache the composer.
     */
    public function cache(Composer $composer): bool
    {
        $manifest = $this->manifest()
            ->put($composer::class, $composer->getFields())
            ->all();

        return file_put_contents(
            $this->manifestPath(),
            '<?php return '.var_export($manifest, true).';'
        ) !== false;
    }

    /**
     * Retrieve the cached composer.
     */
    public function getCache(Composer $composer): array
    {
        return $this->manifest()->get($composer::class);
    }

    /**
     * Determine if the composer is cached.
     */
    public function hasCache(Composer $class): bool
    {
        return $this->manifest()->has($class::class);
    }

    /**
     * Clear the cache.
     */
    public function clearCache(): bool
    {
        return $this->manifestExists()
            ? unlink($this->manifestPath())
            : false;
    }
}
