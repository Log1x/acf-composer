<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Log1x\AcfComposer\Concerns\HasCollection;
use Log1x\AcfComposer\Concerns\InteractsWithPartial;
use Log1x\AcfComposer\Contracts\Composer as ComposerContract;
use Log1x\AcfComposer\Exceptions\InvalidFieldsException;
use Roots\Acorn\Application;
use StoutLogic\AcfBuilder\FieldsBuilder;

abstract class Composer implements ComposerContract
{
    use HasCollection, InteractsWithPartial;

    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * The field keys.
     */
    protected array $keys = ['fields', 'sub_fields', 'layouts'];

    /**
     * The field groups.
     */
    protected array $fields = [];

    /**
     * The default field settings.
     */
    protected Collection|array $defaults = [];

    /**
     * Create a new Composer instance.
     */
    public function __construct(protected AcfComposer $composer)
    {
        $this->app = $this->composer->app;

        $this->defaults = $this->collect($this->app->config->get('acf.defaults'))
            ->merge($this->defaults)
            ->mapWithKeys(fn ($value, $key) => [Str::snake($key) => $value]);

        $this->fields = $this->getFields();
    }

    /**
     * Make a new instance of the Composer.
     */
    public static function make(AcfComposer $composer): self
    {
        return new static($composer);
    }

    /**
     * Handle the Composer instance.
     */
    public function handle(): self
    {
        $this->call('beforeRegister');

        $this->compose();

        $this->call('afterRegister');

        return $this;
    }

    /**
     * Call a method using the application container.
     */
    protected function call(string $hook, array $args = []): mixed
    {
        if (! method_exists($this, $hook)) {
            return null;
        }

        return $this->app->call([$this, $hook], [
            'args' => $args,
        ]);
    }

    /**
     * Register the field group with Advanced Custom Fields.
     */
    protected function register(?callable $callback = null): void
    {
        if (blank($this->fields)) {
            return;
        }

        if ($callback) {
            $callback();
        }

        acf_add_local_field_group(
            $this->build($this->fields)
        );
    }

    /**
     * Retrieve the field group fields.
     */
    public function getFields(bool $cache = true): array
    {
        if ($this->fields && $cache) {
            return $this->fields;
        }

        if ($cache && $this->composer->manifest()->has($this)) {
            return $this->composer->manifest()->get($this);
        }

        $fields = $this->resolveFields();

        $fields = is_a($fields, FieldsBuilder::class)
            ? $fields->build()
            : $fields;

        if (! is_array($fields)) {
            throw new InvalidFieldsException;
        }

        if ($this->defaults->has('field_group')) {
            $fields = array_merge($this->defaults->get('field_group'), $fields);
        }

        return $fields;
    }

    /**
     * Resolve the fields from the Composer with the container.
     */
    public function resolveFields(array $args = []): mixed
    {
        return $this->call('fields', $args) ?? [];
    }

    /**
     * Build the field group with the default field type settings.
     */
    public function build(array $fields = []): array
    {
        return $this->collect($fields)->map(function ($value, $key) {
            if (
                ! in_array($key, $this->keys) ||
                ($key === 'type' && ! $this->defaults->has($value))
            ) {
                return $value;
            }

            return array_map(function ($field) {
                foreach ($field as $key => $value) {
                    if (in_array($key, $this->keys)) {
                        return $this->build($field);
                    }

                    if ($key === 'type' && $this->defaults->has($value)) {
                        $field = array_merge($this->defaults->get($field['type'], []), $field);
                    }
                }

                return $field;
            }, $value);
        })->all();
    }
}
