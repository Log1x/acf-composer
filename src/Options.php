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
     * The slug of another admin page to be used as a parent.
     *
     * @var string
     */
    public $parent = null;

    /**
     * The option page menu icon.
     *
     * @var string
     */
    public $icon = null;

    /**
     * Redirect to the first child page if one exists.
     *
     * @var boolean
     */
    public $redirect = true;

    /**
     * The post ID to save and load values from.
     *
     * @var string|int
     */
    public $post = 'options';

    /**
     * The option page autoload setting.
     *
     * @var bool
     */
    public $autoload = true;

    /**
     * Localized text displayed on the submit button.
     *
     * @return string
     */
    public function updateButton()
    {
        return __('Update', 'acf');
    }

    /**
     * Localized text displayed after form submission.
     *
     * @return string
     */
    public function updatedMessage()
    {
        return __('Options Updated', 'acf');
    }

    /**
     * Compose and register the defined ACF field groups.
     *
     * @return void
     */
    public function compose()
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

        if (! Arr::has($this->fields, 'location.0.0')) {
            Arr::set($this->fields, 'location.0.0', [
                'param' => 'options_page',
                'operator' => '==',
                'value' => $this->slug,
            ]);
        }

        $this->register(function () {
            acf_add_options_page([
                'menu_title' => $this->name,
                'menu_slug' => $this->slug,
                'page_title' => $this->title,
                'capability' => $this->capability,
                'position' => $this->position,
                'parent_slug' => $this->parent,
                'icon_url' => $this->icon,
                'redirect' => $this->redirect,
                'post_id' => $this->post,
                'autoload' => $this->autoload,
                'update_button' => $this->updateButton(),
                'updated_message' => $this->updatedMessage()
            ]);
        });

        return $this;
    }
}
