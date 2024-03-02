<?php

namespace Log1x\AcfComposer;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Log1x\AcfComposer\Concerns\InteractsWithPartial;
use Log1x\AcfComposer\Contracts\Composer as ComposerContract;
use Log1x\AcfComposer\Contracts\Field as FieldContract;
use Roots\Acorn\Application;
use StoutLogic\AcfBuilder\FieldsBuilder;

abstract class Composer implements ComposerContract, FieldContract
{
    use InteractsWithPartial;

    /**
     * The ACF Composer instance.
     */
    protected AcfComposer $composer;

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
    public function __construct(AcfComposer $composer)
    {
        $this->composer = $composer;
        $this->app = $composer->app;

        $this->defaults = collect($this->app->config->get('acf.defaults'))
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
        $this->beforeRegister();

        $this->compose();

        $this->afterRegister();

        return $this;
    }

    /**
     * Actions to run before registering the Composer.
     */
    public function beforeRegister(): void
    {
        //
    }

    /**
     * Actions to run after registering the Composer.
     */
    public function afterRegister(): void
    {
        //
    }

    /**
     * Register the field group with Advanced Custom Fields.
     */
    protected function register(?callable $callback = null): void
    {
        if (empty($this->fields)) {
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

        $fields = is_a($fields = $this->fields(), FieldsBuilder::class)
            ? $fields->build()
            : $fields;

        if (! is_array($fields)) {
            throw new Exception('Fields must be an array or an instance of Builder.');
        }

        if ($this->defaults->has('field_group')) {
            $fields = array_merge($this->defaults->get('field_group'), $fields);
        }

        return $fields;
    }

    /**
     * Build the field group with the default field type settings.
     */
    public function build(array $fields = []): array
    {
        return collect($fields)->map(function ($value, $key) {
            if (
                ! in_array($key, $this->keys) ||
                (Str::is($key, 'type') && ! $this->defaults->has($value))
            ) {
                return $value;
            }

            return array_map(function ($field) {
                foreach ($field as $key => $value) {
                    if (in_array($key, $this->keys)) {
                        return $this->build($field);
                    }

                    if (Str::is($key, 'type') && $this->defaults->has($value)) {
                        $field = array_merge($this->defaults->get($field['type'], []), $field);
                    }
                }

                return $field;
            }, $value);
        })->all();
    }
}
