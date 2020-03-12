<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class Field extends Composer
{
    /**
     * Create an options page for this field group.
     *
     * @param string|array|bool
     */
    protected $options = false;

    /**
     * Compose and register the defined field groups with ACF.
     *
     * @param  callback $callback
     * @return void
     */
    public function compose($callback = null)
    {
        if (empty($this->options)) {
            return parent::compose();
        }

        if (is_array($this->options)) {
            $this->options = collect($this->options);

            if (! $this->options->get('menu_title')) {
                return parent::compose();
            }
        }

        if (is_string($this->options)) {
            $this->options = collect([
                'menu_title' => Str::title(Str::slug($this->options, ' ')),
                'menu_slug' => Str::slug($this->options),
            ]);
        }

        if (! $this->options->get('menu_slug')) {
            $this->options->put('menu_slug', Str::slug($this->options->get('menu_title')));
        }

        $this->options = array_merge([
            'page_title' => get_bloginfo('name', 'display'),
            'capability' => 'edit_theme_options',
            'position' => PHP_INT_MAX,
            'autoload' => true
        ], $this->options->all());

        parent::compose(function () {
            acf_add_options_page($this->options);

            if (! Arr::has($this->fields, 'location.0.0')) {
                Arr::set($this->fields, 'location.0.0', [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => $this->options['menu_slug'],
                ]);
            }
        });
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
