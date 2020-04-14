<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class Options extends Composer
{
    /**
     * The option page menu name.
     *
     * @var string
     */
    public $name = '';

    /**
     * The option page menu slug.
     *
     * @var string
     */
    public $slug = '';

    /**
     * The option page document title.
     *
     * @var string
     */
    public $title = '';

    /**
     * The option page permission capability.
     *
     * @var string
     */
    public $capability = 'edit_theme_options';

    /**
     * The option page menu position.
     *
     * @var int
     */
    public $position = PHP_INT_MAX;

    /**
     * The option page autoload setting.
     *
     * @var bool
     */
    public $autoload = true;

    /**
     * The icon used in the menu.
     *
     * @var string
     */
    public $icon = null;

    /**
     * Whether to redirect to child pages.
     *
     * @var boolean
     */
    public $redirect = true;

    /**
     * The parent of this page, if any.
     *
     * @var string
     */
    public $parent = null;

    /**
     * Where to save this data.
     *
     * Accepts strings (i.e. 'user_2') or numbers (i.e. 123).
     *
     * @link https://www.advancedcustomfields.com/resources/get_field/
     * @var string|int
     */
    public $post = 'options';

    /**
     * Text displayed on the option page submit button.
     *
     * Implemented as a function to support localization.
     *
     * @return string
     */
    public function updateButton()
    {
        return __('Update', 'acf');
    }

    /**
     * Text displayed above form after submission.
     *
     * Implemented as a function to support localization.
     *
     * @return string
     */
    public function updatedMessage()
    {
        return __("Options Updated", 'acf');
    }

    /**
     * Compose and register the defined field groups with ACF.
     *
     * @param  callback $callback
     * @return void
     */
    public function compose($callback = null)
    {
        if (empty($this->name)) {
            return;
        }

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (empty($this->title)) {
            $this->title = $this->name;
        }

        parent::compose(function () {
            acf_add_options_page([
                'menu_title' => $this->name,
                'menu_slug' => $this->slug,
                'page_title' => $this->title,
                'capability' => $this->capability,
                'position' => $this->position,
                'autoload' => $this->autoload,
                'icon_url' => $this->icon,
                'parent_slug' => $this->parent,
                'redirect' => $this->redirect,
                'post_id' => $this->post,
                'update_button' => $this->updateButton(),
                'updated_message' => $this->updatedMessage()
            ]);

            if (! Arr::has($this->fields, 'location.0.0')) {
                Arr::set($this->fields, 'location.0.0', [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => $this->slug,
                ]);
            }
        });
    }
}
