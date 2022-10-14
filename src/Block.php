<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Log1x\AcfComposer\Contracts\Block as BlockContract;
use Log1x\AcfComposer\Concerns\InteractsWithBlade;

abstract class Block extends Composer implements BlockContract
{
    use InteractsWithBlade;

    /**
     * The block properties.
     *
     * @var array|object
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
    public $post_id;

    /**
     * The block instance.
     *
     * @var \WP_Block
     */
    public $instance;

    /**
     * The block context.
     *
     * @var array
     */
    public $context;

    /**
     * The current post.
     *
     * @param \WP_Post
     */
    public $post;

    /**
     * The block classes.
     *
     * @param string
     */
    public $classes;

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
     * The block name.
     *
     * @var string
     */
    public $name = '';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = '';

    /**
     * The block view.
     *
     * @var string
     */
    public $view;

    /**
     * The block description.
     *
     * @var string
     */
    public $description = '';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = '';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = '';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [];

    /**
     * The parent block type allow list.
     *
     * @var array
     */
    public $parent = [];

    /**
     * The block post type allow list.
     *
     * @var array
     */
    public $post_types = [];

    /**
     * The default block mode.
     *
     * @var string
     */
    public $mode = 'preview';

    /**
     * The default block alignment.
     *
     * @var string
     */
    public $align = '';

    /**
     * The default block text alignment.
     *
     * @var string
     */
    public $align_text = '';

    /**
     * The default block content alignment.
     *
     * @var string
     */
    public $align_content = '';

    /**
     * The supported block features.
     *
     * @var array
     */
    public $supports = [];

    /**
     * The block styles.
     *
     * @var array
     */
    public $styles = [];

    /**
     * The block active style.
     *
     * @var string
     */
    public $style;

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [];

    /**
     * Assets enqueued when rendering the block.
     *
     * @return void
     */
    public function enqueue()
    {
        //
    }

    /**
     * Returns the active block style based on the block CSS classes.
     * If none is found, it returns the default style set in $styles.
     *
     * @return string|null
     */
    public function getStyle()
    {
        return Str::of($this->block->className ?? null)
            ->matchAll('/is-style-(\S+)/')
            ->get(0) ??
            Arr::get(collect($this->block->styles)->firstWhere('isDefault'), 'name');
    }

    /**
     * Compose the defined field group and register it
     * with Advanced Custom Fields.
     *
     * @return void
     */
    public function compose()
    {
        if (empty($this->name)) {
            return;
        }

        if (! empty($this->name) && empty($this->slug)) {
            $this->slug = Str::slug(Str::kebab($this->name));
        }

        if (empty($this->view)) {
            $this->view = Str::start($this->slug, 'blocks.');
        }

        if (empty($this->namespace)) {
            $this->namespace = Str::start($this->slug, $this->prefix);
        }

        if (! Arr::has($this->fields, 'location.0.0')) {
            Arr::set($this->fields, 'location.0.0', [
                'param' => 'block',
                'operator' => '==',
                'value' => $this->namespace,
            ]);
        }

        // The matrix isn't available on WP < 5.5
        if (Arr::has($this->supports, 'align_content') && version_compare('5.5', get_bloginfo('version'), '>')) {
            if (! is_bool($this->supports['align_content'])) {
                $this->supports['align_content'] = true;
            }
        }

        $this->register(function () {
            $settings = [
                'name' => $this->slug,
                'title' => $this->name,
                'description' => $this->description,
                'category' => $this->category,
                'icon' => $this->icon,
                'keywords' => $this->keywords,
                'parent' => $this->parent ?: null,
                'post_types' => $this->post_types,
                'mode' => $this->mode,
                'align' => $this->align,
                'align_text' => $this->align_text ?? $this->align,
                'align_content' => $this->align_content,
                'styles' => $this->styles,
                'supports' => $this->supports,
                'enqueue_assets' => function () {
                    return $this->enqueue();
                },
                'render_callback' => function (
                    $block,
                    $content = '',
                    $preview = false,
                    $post_id = 0,
                    $wp_block = false,
                    $context = false
                ) {
                    echo $this->render($block, $content, $preview, $post_id, $wp_block, $context);
                },
            ];

            if ($this->example !== false) {
                $settings = Arr::add($settings, 'example', [
                    'attributes' => [
                        'mode' => 'preview',
                        'data' => $this->example,
                    ],
                ]);
            }

            acf_register_block_type($settings);
        });

        return $this;
    }

    /**
     * Render the ACF block.
     *
     * @param  array     $block
     * @param  string    $content
     * @param  bool      $preview
     * @param  int       $post_id
     * @param  \WP_Block $wp_block
     * @param  array     $context
     * @return string
     */
    public function render($block, $content = '', $preview = false, $post_id = 0, $wp_block = false, $context = false)
    {
        $this->block = (object) $block;
        $this->content = $content;
        $this->preview = $preview;
        $this->post_id = $post_id;
        $this->instance = $wp_block;
        $this->context = $context;

        $this->post = get_post($post_id);

        $this->classes = collect([
            'slug' => Str::start(
                Str::slug($this->slug),
                'wp-block-'
            ),
            'align' => ! empty($this->block->align) ?
                Str::start($this->block->align, 'align') :
                false,
            'align_text' => ! empty($this->supports['align_text']) ?
                Str::start($this->block->align_text, 'align-text-') :
                false,
            'align_content' => ! empty($this->supports['align_content']) ?
                Str::start($this->block->align_content, 'is-position-') :
                false,
            'full_height' => ! empty($this->supports['full_height'])
                && ! empty($this->block->full_height) ?
                'full-height' :
                false,
            'classes' => $this->block->className ?? false,
        ])->filter()->implode(' ');

        $this->style = $this->getStyle();

        return $this->view($this->view, ['block' => $this]);
    }
}
