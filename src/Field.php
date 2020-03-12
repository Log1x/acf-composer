<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Str;

abstract class Field extends Composer
{
    /**
     * Field groups to be composed.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    /**
     * A simple helper method for creating an options page.
     *
     * @param  string $name
     * @param  array $options
     * @return void
     */
    public function options($name, $options = [])
    {
        acf_add_options_page(
            collect([
                'page_title' => get_bloginfo('name'),
                'menu_title' => Str::title($name),
                'menu_slug' => Str::slug($name),
                'update_button' => 'Update Options',
                'capability' => 'edit_theme_options',
                'position' => '999',
                'autoload' => true
            ])->merge($options)->all()
        );
    }
}
