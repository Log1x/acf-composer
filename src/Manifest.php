<?php

namespace Log1x\AcfComposer;

use Exception;
use Illuminate\Support\Collection;
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
     * Create a new Manifest instance.
     */
    public function __construct(AcfComposer $composer)
    {
        $this->composer = $composer;

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
    protected function path(): string
    {
        $path = config('acf-composer.manifest', storage_path('framework/cache'));

        if (Str::endsWith($path, '.php')) {
            return $path;
        }

        return Str::finish($path, '/').'acf-composer.php';
    }

    /**
     * Determine if the manifest exists.
     */
    public function exists(): bool
    {
        return file_exists($this->path());
    }

    /**
     * Add the Composer to the manifest.
     */
    public function add(Composer $composer): bool
    {
        try {
            $this->manifest->put($composer::class, $composer->getFields(cache: false));

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
    public function write(): bool
    {
        return file_put_contents(
            $this->path(),
            '<?php return '.var_export($this->toArray(), true).';'
        ) !== false;
    }

    /**
     * Delete the manifest from disk.
     */
    public function delete(): bool
    {
        return $this->exists()
            ? unlink($this->path())
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
