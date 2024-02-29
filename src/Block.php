<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Log1x\AcfComposer\Concerns\FormatsCss;
use Log1x\AcfComposer\Concerns\InteractsWithBlade;
use Log1x\AcfComposer\Contracts\Block as BlockContract;

abstract class Block extends Composer implements BlockContract
{
    use FormatsCss, InteractsWithBlade;

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
     * @var int
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
     * @var \WP_Post
     */
    public $post;

    /**
     * The block classes.
     *
     * @var string
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
     * The ancestor block type allow list.
     *
     * @var array
     */
    public $ancestor = [];

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
     * Context values inherited by the block.
     *
     * @var string[]
     */
    public $uses_context = [];

    /**
     * Context provided by the block.
     *
     * @var string[]
     */
    public $provides_context = [];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [];

    /**
     * The block template.
     *
     * @var array
     */
    public $template = [];

    /**
     * The block dimensions.
     *
     * @var string
     */
    public $inlineStyle;

    /**
     * The block attributes.
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Set the attributes to the block properties.
     */
    public function mergeAttributes(): void
    {
        if (! $attributes = $this->attributes()) {
            return;
        }

        foreach ($attributes as $key => $value) {
            if (! property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    /**
     * Retrieve the active block style.
     */
    public function getStyle(): ?string
    {
        return Str::of($this->block->className ?? null)
            ->matchAll('/is-style-(\S+)/')
            ->get(0) ??
            Arr::get(collect($this->block->styles)->firstWhere('isDefault'), 'name');
    }

    /**
     * Retrieve the block styles.
     */
    public function getStyles(): array
    {
        $styles = collect($this->styles)->map(function ($value, $key) {
            if (is_array($value)) {
                return $value;
            }

            $name = is_bool($value) ? $key : $value;
            $default = is_bool($value) ? $value : false;

            return [
                'name' => $name,
                'label' => Str::headline($name),
                'isDefault' => $default,
            ];
        });

        if (! $styles->where('isDefault', true)->count()) {
            $styles = $styles->map(fn ($style, $key) => $key === 0
                ? Arr::set($style, 'isDefault', true)
                : $style
            );
        }

        return $styles->all();
    }

    /**
     * Retrieve the inline block styles.
     */
    public function getInlineStyle(): string
    {
        return collect([
            'padding' => ! empty($this->block->style['spacing']['padding'])
                ? collect($this->block->style['spacing']['padding'])->map(function ($value, $side) {
                    return $this->formatCss($value, $side);
                })->implode(' ')
                : null,
            'margin' => ! empty($this->block->style['spacing']['margin'])
                ? collect($this->block->style['spacing']['margin'])->map(function ($value, $side) {
                    return $this->formatCss($value, $side, 'margin');
                })->implode(' ')
                : null,
            'color' => ! empty($this->block->style['color']['gradient'])
                ? sprintf('background: %s;', $this->block->style['color']['gradient'])
                : null,
        ])->filter()->implode(' ');
    }

    /**
     * Handle the block template.
     */
    public function handleTemplate(array $template = []): Collection
    {
        return collect($template)->map(function ($value, $key) {
            if (is_array($value) && Arr::has($value, 'innerBlocks')) {
                $blocks = collect($value['innerBlocks'])
                    ->map(fn ($block) => $this->handleTemplate($block)->all())
                    ->collapse();

                return [$key, Arr::except($value, 'innerBlocks') ?? [], $blocks->all()];
            }

            return [$key, $value];
        })->values();
    }

    /**
     * Compose the fields and register the block.
     */
    public function compose(): ?self
    {
        $this->mergeAttributes();

        if (empty($this->name)) {
            return null;
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

        $this->register(function () {
            $settings = [
                'name' => $this->slug,
                'title' => $this->name,
                'description' => $this->description,
                'category' => $this->category,
                'icon' => $this->icon,
                'keywords' => $this->keywords,
                'parent' => $this->parent ?: null,
                'ancestor' => $this->ancestor ?: null,
                'post_types' => $this->post_types,
                'mode' => $this->mode,
                'align' => $this->align,
                'align_text' => $this->align_text ?? $this->align,
                'align_content' => $this->align_content,
                'styles' => $this->getStyles(),
                'supports' => $this->supports,
                'enqueue_assets' => fn ($block) => method_exists($this, 'assets') ? $this->assets($block) : null,
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
                if (method_exists($this, 'example') && is_array($example = $this->example())) {
                    $this->example = array_merge($this->example, $example);
                }

                $settings = Arr::add($settings, 'example', [
                    'attributes' => [
                        'mode' => 'preview',
                        'data' => $this->example,
                    ],
                ]);
            }

            if (! empty($this->uses_context)) {
                $settings['uses_context'] = $this->uses_context;
            }

            if (! empty($this->provides_context)) {
                $settings['provides_context'] = $this->provides_context;
            }

            acf_register_block_type($settings);
        });

        return $this;
    }

    /**
     * Render the ACF block.
     *
     * @param  array  $block
     * @param  string  $content
     * @param  bool  $preview
     * @param  int  $post_id
     * @param  \WP_Block  $wp_block
     * @param  array  $context
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
            'backgroundColor' => ! empty($this->block->backgroundColor) ?
                sprintf('has-background has-%s-background-color', $this->block->backgroundColor) :
                false,
            'textColor' => ! empty($this->block->textColor) ?
                sprintf('has-%s-color', $this->block->textColor) :
                false,
            'gradient' => ! empty($this->block->gradient) ?
                sprintf('has-%s-gradient-background', $this->block->gradient) :
                false,
        ])->filter()->implode(' ');

        $this->template = is_array($this->template)
            ? $this->handleTemplate($this->template)->toJson()
            : $this->template;

        $this->style = $this->getStyle();
        $this->inlineStyle = $this->getInlineStyle();

        return $this->view($this->view, ['block' => $this]);
    }

    /**
     * Assets enqueued when rendering the block.
     *
     * @return void
     *
     * @deprecated Use `assets($block)` instead.
     */
    public function enqueue()
    {
        //
    }
}
