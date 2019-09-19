<?php

namespace Log1x\AcfComposer;

use function Roots\app_path;

abstract class Field
{
    /**
     * The blocks status.
     *
     * @var boolean
     */
    protected $enabled = true;

    /**
     * Compose the field.
     *
     * @return void
     */
    public function compose()
    {
        if (! $this->register() || ! function_exists('acf')) {
            return;
        }

        collect($this->register())->each(function ($value, $name) {
            $this->{$name} = $value;
        });

        $this->fields = $this->fields();

        if (! $this->enabled) {
            return;
        }

        add_action('init', function () {
            acf_add_local_field_group($this->fields);
        }, 20);
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

        return include_once(app_path("Fields/{$name}.php"));
    }

    /**
     * Data to be passed to the field before registering.
     *
     * @return array
     */
    public function register()
    {
        return [];
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
