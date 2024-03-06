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
     * The booted state.
     */
    protected bool $booted = false;

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
     * The legacy widgets.
     */
    protected array $legacyWidgets = [];

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
     * Boot the registered Composers.
     */
    public function boot(): void
    {
        if ($this->booted()) {
            return;
        }

        $this->registerDefaultPath();

        $this->handleBlocks();
        $this->handleWidgets();

        add_filter('acf/init', fn () => $this->handleComposers());

        $this->booted = true;
    }

    /**
     * Handle the Composer registration.
     */
    public function handleComposers(): void
    {
        foreach ($this->composers as $namespace => $composers) {
            foreach ($composers as $i => $composer) {
                $composer = $composer::make($this);

                if (is_subclass_of($composer, Options::class) && ! is_null($composer->parent)) {
                    $this->deferredComposers[$namespace][] = $composer;

                    unset($this->composers[$namespace][$i]);

                    continue;
                }

                $this->composers[$namespace][$i] = $composer->handle();
            }
        }

        foreach ($this->deferredComposers as $namespace => $composers) {
            foreach ($composers as $index => $composer) {
                $this->composers[$namespace][] = $composer->handle();
            }
        }

        $this->deferredComposers = [];
    }

    /**
     * Handle the Widget Composer registration.
     */
    public function handleWidgets(): void
    {
        foreach ($this->legacyWidgets as $namespace => $composers) {
            foreach ($composers as $composer) {
                $composer = $composer::make($this);

                $this->composers[$namespace][] = $composer->handle();
            }
        }

        $this->legacyWidgets = [];
    }

    /**
     * Handle the block rendering.
     */
    protected function handleBlocks(): void
    {
        add_filter('acf_block_render_template', function ($block, $content, $is_preview, $post_id, $wp_block, $context) {
            if (! class_exists($composer = $block['render_template'] ?? '')) {
                return;
            }

            if (! $composer = app('AcfComposer')->getComposer($composer)) {
                return;
            }

            method_exists($composer, 'assets') && $composer->assets($block);

            echo $composer->render($block, $content, $is_preview, $post_id, $wp_block, $context);
        }, 10, 6);
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

        if (is_subclass_of($composer, Widget::class)) {
            $this->legacyWidgets[$namespace][] = $composer;

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
     * Retrieve a Composer instance by class name.
     */
    public function getComposer(string $class): ?Composer
    {
        foreach ($this->composers as $composers) {
            foreach ($composers as $composer) {
                if ($composer::class === $class) {
                    return $composer;
                }
            }
        }

        return null;
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

    /**
     * Determine if ACF Composer is booted.
     */
    public function booted(): bool
    {
        return $this->booted;
    }
}
