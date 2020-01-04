<?php

namespace Log1x\AcfComposer;

use Roots\Acorn\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function Roots\app_path;

abstract class Field
{
    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * The field group.
     *
     * @var array
     */
    protected $fields;

    /**
     * Default field type settings.
     *
     * @return array
     */
    protected $defaults = [];

    /**
     * Create a new Field instance.
     *
     * @param  \Roots\Acorn\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Compose the field.
     *
     * @return void
     */
    public function compose()
    {
        if (! $this->fields() || ! function_exists('acf')) {
            return;
        }

        $this->fields = $this->fields();
        $this->defaults = collect(
            $this->app->config->get('acf.defaults')
        )->merge($this->defaults)->mapWithKeys(function ($value, $key) {
            return [Str::snake($key) => $value];
        });

        add_action('init', function () {
            acf_add_local_field_group($this->build());
        }, 20);
    }

    /**
     * Build the field group with our default field type settings.
     *
     * @param  array $fields
     * @return array
     */
    protected function build($fields = [])
    {
        if (empty($fields) && $this->defaults->has('field_group')) {
            $this->fields = array_merge($this->fields, $this->defaults->get('field_group'));
        }

        return collect($fields ?: $this->fields)->map(function ($value, $key) use ($fields) {
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
     * Get field partial if it exists.
     *
     * @param  string $name
     * @return mixed
     */
    protected function get($name = '')
    {
        $name = strtr($name, [
            '.php' => '',
            '.' => '/'
        ]);

        return include app_path("Fields/{$name}.php");
    }

    /**
     * Fields to be attached to the field.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
