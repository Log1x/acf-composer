<?php

namespace Log1x\AcfComposer;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Manifest
{
    /**
     * The ACF Composer instance.
     */
    protected AcfComposer $composer;

    /**
     * The manifest collection.
     */
    protected Collection $manifest;

    /**
     * The block collection.
     */
    protected array $blocks = [];

    /**
     * The cache path.
     */
    protected string $path;

    /**
     * Create a new Manifest instance.
     */
    public function __construct(AcfComposer $composer)
    {
        $this->composer = $composer;

        $this->path = Str::finish(
            config('acf-composer.manifest', storage_path('framework/cache')),
            '/acf-composer'
        );

        $this->manifest = $this->exists()
            ? Collection::make(require $this->path())
            : Collection::make();
    }

    /**
     * Make a new instance of Manifest.
     */
    public static function make(AcfComposer $composer): self
    {
        return new static($composer);
    }

    /**
     * Retrieve the manifest path.
     */
    public function path(?string $filename = 'manifest.php'): string
    {
        return "{$this->path}/{$filename}";
    }

    /**
     * Determine if the manifest exists.
     */
    public function exists(?string $filename = null): bool
    {
        return file_exists($this->path($filename));
    }

    /**
     * Add the Composer to the manifest.
     */
    public function add(Composer $composer): bool
    {
        try {
            $this->manifest->put($composer::class, $composer->getFields(cache: false));

            if (is_a($composer, Block::class)) {
                $this->blocks[$composer->jsonPath()] = $composer->toJson();
            }

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Retrieve the cached Composer from the manifest.
     */
    public function get(Composer $composer): array
    {
        return $this->manifest->get($composer::class);
    }

    /**
     * Determine if the Composer is cached.
     */
    public function has(Composer $class): bool
    {
        return $this->manifest->has($class::class);
    }

    /**
     * Write the the manifest to disk.
     */
    public function write(): ?int
    {
        File::ensureDirectoryExists($this->path);

        $manifest = $this->toArray();

        return file_put_contents(
            $this->path(),
            '<?php return '.var_export($manifest, true).';'
        ) !== false ? count($manifest) : null;
    }

    /**
     * Write the block JSON to disk.
     */
    public function writeBlocks(): int
    {
        if (! $this->blocks) {
            return 0;
        }

        $path = $this->path('blocks');

        foreach ($this->blocks as $path => $block) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $block);
        }

        return count($this->blocks);
    }

    /**
     * Delete the manifest from disk.
     */
    public function delete(): bool
    {
        return File::isDirectory($this->path)
            ? File::deleteDirectory($this->path)
            : false;
    }

    /**
     * Retrieve the manifest as an array.
     */
    public function toArray(): array
    {
        return $this->manifest->all();
    }
}
