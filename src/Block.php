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
     * An array of dummy data for previews
     *s
     * @var array
     */
    public $example = [];

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
     * The block text alignment class.
     *
     * @var string
     */
    public $align_text = '';

    /**
     * The block text alignment class.
     *
     * @var string
     */
    public $align_content = '';

    /**
     * Features supported by the block.
     *
     * @var array
     */
    public $supports = [];

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

        // The matrix isn't available on WP > 5.5
        if (Arr::has($this->supports, 'align_content') && version_compare('5.5', get_bloginfo('version'), '>')) {
            if (! is_bool($this->supports['align_content'])) {
                $this->supports['align_content'] = true;
            }
        }

        $this->register(function () {
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
                'align_text' => $this->align_text ?? $this->align,
                'align_content' => $this->align_content,
                'supports' => $this->supports,
                'example' => [
                    'attributes' => [
                        'mode' => 'preview',
                        'data' => $this->example,
                    ]
                ],
                'enqueue_assets' => function () {
                    return $this->enqueue();
                },
                'render_callback' => function ($block, $content = '', $preview = false, $post_id = 0) {
                    echo $this->render($block, $content, $preview, $post_id);
                }
            ]);
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
    public function render($block, $content = '', $preview = false, $post_id = 0)
    {
        $this->block = (object) $block;
        $this->content = $content;
        $this->preview = $preview;
        $this->post = get_post($post_id);
        $this->post_id = $post_id;
        $this->classes = collect([
            'slug' => Str::start(
                Str::slug($this->block->title),
                'wp-block-'
            ),
            'align' => ! empty($this->block->align) ? Str::start($this->block->align, 'align') : false,
            'classes' => $this->block->className ?? false,
        ])->filter()->implode(' ');

        return $this->view(
            Str::finish('blocks.', $this->slug),
            ['block' => $this]
        );
    }
}
