<?php

namespace Log1x\AcfComposer;

use function Roots\app_path;

abstract class Field
{
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

        add_action('init', function () {
            acf_add_local_field_group($this->fields());
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
     * Fields to be attached to the field.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
