<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

abstract class Block extends Composer
{
    /**
     * The block properties.
     *
     * @var array
     */
    public $block;

    /**
     * The block content.
     *
     * @var string
     */
    public $content;

    /**
     * The block preview status.
     *
     * @var bool
     */
    public $preview;

    /**
     * The current post ID.
     *
     * @param int
     */
    public $post;

    /**
     * The block prefix.
     *
     * @var string
     */
    public $prefix = 'acf/';

    /**
     * The block namespace.
     *
     * @var string
     */
    public $namespace;

    /**
     * The display name of the block.
     *
     * @var string
     */
    public $name = '';

    /**
     * The slug of the block.
     *
     * @var string
     */
    public $slug = '';

    /**
     * The description of the block.
     *
     * @var string
     */
    public $description = '';

    /**
     * The category this block belongs to.
     *
     * @var string
     */
    public $category = '';

    /**
     * The icon of this block.
     *
     * @var string|array
     */
    public $icon = '';

    /**
     * An array of keywords the block will be found under.
     *
     * @var array
     */
    public $keywords = [];

    /**
     * An array of post types the block will be available to.
     *
     * @var array
     */
    public $post_types = ['post', 'page'];

    /**
     * The default display mode of the block that is shown to the user.
     *
     * @var string
     */
    public $mode = 'preview';

    /**
     * The block alignment class.
     *
     * @var string
     */
    public $align = '';

    /**
     * Features supported by the block.
     *
     * @var array
     */
    public $supports = [];

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

        if (! empty($this->name) && empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (empty($this->namespace)) {
            $this->namespace = Str::start($this->slug, $this->prefix);
        }

        parent::compose(function () {
            acf_register_block([
                'name' => $this->slug,
                'title' => $this->name,
                'description' => $this->description,
                'category' => $this->category,
                'icon' => $this->icon,
                'keywords' => $this->keywords,
                'post_types' => $this->post_types,
                'mode' => $this->mode,
                'align' => $this->align,
                'supports' => $this->supports,
                'enqueue_assets' => [$this,'assets'],
                'render_callback' => [$this, 'render']
            ]);

            if (! Arr::has($this->fields, 'location.0.0')) {
                Arr::set($this->fields, 'location.0.0', [
                    'param' => 'block',
                    'operator' => '==',
                    'value' => $this->namespace,
                ]);
            }
        });
    }

    /**
     * Render the ACF block.
     *
     * @param  array $block
     * @param  string $content
     * @param  bool $preview
     * @param  int $post
     * @return void
     */
    public function render($block, $content = '', $preview = false, $post = 0)
    {
        $this->block = (object) $block;
        $this->content = $content;
        $this->preview = $preview;
        $this->post = $post;

        echo $this->view(
            Str::finish('views.blocks.', $this->slug),
            ['block' => $this]
        );
    }

    /**
     * Assets enqueued when rendering the block.
     *
     * @return void
     */
    public function enqueue()
    {
        //
    }
}
