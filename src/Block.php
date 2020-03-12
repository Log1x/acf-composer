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
    protected $block;

    /**
     * The block content.
     *
     * @var string
     */
    protected $content;

    /**
     * The block preview status.
     *
     * @var bool
     */
    protected $preview;

    /**
     * The current post ID.
     *
     * @param int
     */
    protected $post;

    /**
     * The block prefix.
     *
     * @var string
     */
    protected $prefix = 'acf/';

    /**
     * The block namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * The display name of the block.
     *
     * @var string
     */
    protected $name = '';

    /**
     * The slug of the block.
     *
     * @var string
     */
    protected $slug = '';

    /**
     * The description of the block.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The category this block belongs to.
     *
     * @var string
     */
    protected $category = '';

    /**
     * The icon of this block.
     *
     * @var string|array
     */
    protected $icon = '';

    /**
     * An array of keywords the block will be found under.
     *
     * @var array
     */
    protected $keywords = [];

    /**
     * An array of post types the block will be available to.
     *
     * @var array
     */
    protected $post_types = ['post', 'page'];

    /**
     * The default display mode of the block that is shown to the user.
     *
     * @var string
     */
    protected $mode = 'preview';

    /**
     * The block alignment class.
     *
     * @var string
     */
    protected $align = '';

    /**
     * Features supported by the block.
     *
     * @var array
     */
    protected $supports = [];

    /**
     * Compose and register the defined field groups with ACF.
     *
     * @param  callback $callback
     * @return void
     */
    public function compose($callback = null)
    {
        if (empty($this->namespace)) {
            $this->namespace = Str::start($this->slug, $this->prefix);
        }

        parent::compose(function () {
            if (! Arr::has($this->fields, 'location.0.0')) {
                Arr::set($this->fields, 'location.0.0', [
                    'param' => 'block',
                    'operator' => '==',
                    'value' => $this->namespace,
                ]);
            }
        });

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
    }

    /**
     * Render the block with ACF using Blade.
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
