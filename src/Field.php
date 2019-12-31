<?php

namespace Log1x\AcfComposer;

use Roots\Acorn\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function Roots\app_path;

abstract class Field
{
    /**
     * Acorn Container
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

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
     * Build the field group with our field type defaults.
     *
     * @return array
     */
    protected function build()
    {
        return collect($this->fields())->map(function ($value, $key) {
            if ($key !== 'fields') {
                return $value;
            }

            foreach ($value as $field) {
                if ($this->defaults->has($field['type'])) {
                    return [array_merge($field, $this->defaults->get($field['type']))];
                }
            }

            return $value;
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
