<?php

namespace Log1x\AcfComposer;

use ReflectionClass;
use Log1x\AcfComposer\Contracts\Composer as ComposerContract;
use StoutLogic\AcfBuilder\FieldsBuilder;
use Roots\Acorn\Application;
use Illuminate\Support\Str;

abstract class Composer implements ComposerContract
{
    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * The field groups.
     *
     * @var \StoutLogic\AcfBuilder\FieldsBuilder|array
     */
    protected $fields;

    /**
     * The default field settings.
     *
     * @var \Illuminate\Support\Collection|array
     */
    protected $defaults = [];

    /**
     * Autoload and compose the current field group.
     *
     * @var bool
     */
    protected $autoload = true;

    /**
     * Create a new Composer instance.
     *
     * @param  \Roots\Acorn\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->defaults = collect(
            $this->app->config->get('acf.defaults')
        )->merge($this->defaults)->mapWithKeys(function ($value, $key) {
            return [Str::snake($key) => $value];
        });

        $this->fields = is_a($this->fields = $this->fields(), FieldsBuilder::class)
            ? $this->fields->build()
            : $this->fields;
    }

    /**
     * Register the field group with Advanced Custom Fields.
     *
     * @param  callback $callback
     * @return void
     */
    protected function register()
    {
        if (empty($this->fields)) {
            return;
        }

        return acf_add_local_field_group(
            $this->build($this->fields)
        );
    }

    /**
     * Build the field group with the default field type settings.
     *
     * @param  array $fields
     * @return array
     */
    protected function build($fields = [])
    {
        return collect($fields)->map(function ($value, $key) {
            if (
                ! Str::contains($key, ['fields', 'sub_fields', 'layouts']) ||
                (Str::is($key, 'type') && ! $this->defaults->has($value))
            ) {
                return $value;
            }

            return array_map(function ($field) {
                if (collect($field)->keys()->intersect(['fields', 'sub_fields', 'layouts'])->isNotEmpty()) {
                    return $this->build($field);
                }

                return array_merge($this->defaults->get($field['type'], []), $field);
            }, $value);
        })->all();
    }

    /**
     * Returns a field partial instance.
     *
     * @param  mixed $partial
     * @return mixed
     */
    protected function get($partial = null)
    {
        if (
            ! is_subclass_of($partial, Partial::class) ||
            (new ReflectionClass($partial))->isAbstract()
        ) {
            return is_string($partial) ? include $this->app->path(
                Str::finish(
                    Str::finish($path, '/'),
                    Str::finish($name, '.php')
                )
            ) : $partial;
        }

        return (new $partial($this->app))->compose();
    }

    /**
     * The field group.
     *
     * @return \StoutLogic\AcfBuilder\FieldsBuilder|array
     */
    public function fields()
    {
        return [];
    }
}
