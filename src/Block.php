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
     * The block text domain.
     *
     * @var string
     */
    public $textDomain;

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
     * The block settings.
     */
    public ?Collection $settings = null;

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
            Arr::get($this->getDefaultStyle(), 'name');
    }

    /**
     * Retrieve the default style.
     */
    public function getDefaultStyle(): array
    {
        return collect($this->getStyles())->firstWhere('isDefault') ?? [];
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
                ? collect($this->block->style['spacing']['padding'])
                    ->map(fn ($value, $side) => $this->formatCss($value, $side))
                    ->implode(' ')
                : null,

            'margin' => ! empty($this->block->style['spacing']['margin'])
                ? collect($this->block->style['spacing']['margin'])
                    ->map(fn ($value, $side) => $this->formatCss($value, $side, 'margin'))
                    ->implode(' ')
                : null,

            'color' => ! empty($this->block->style['color']['gradient'])
                ? sprintf('background: %s;', $this->block->style['color']['gradient'])
                : null,
        ])->filter()->implode(' ');
    }

    /**
     * Retrieve the block classes.
     */
    public function getClasses(): string
    {
        $classes = collect([
            'slug' => Str::of($this->slug)->slug()->start('wp-block-')->toString(),

            'className' => $this->block->className ?? null,

            'align' => ! empty($this->block->align)
                ? Str::start($this->block->align, 'align')
                : null,

            'backgroundColor' => ! empty($this->block->backgroundColor)
                ? sprintf('has-background has-%s-background-color', $this->block->backgroundColor)
                : null,

            'textColor' => ! empty($this->block->textColor)
                ? sprintf('has-%s-color', $this->block->textColor)
                : null,

            'gradient' => ! empty($this->block->gradient)
                ? sprintf('has-%s-gradient-background', $this->block->gradient)
                : null,
        ]);

        if ($alignText = $this->block->alignText ?? $this->block->align_text ?? null) {
            $classes->add(Str::start($alignText, 'align-text-'));
        }

        if ($alignContent = $this->block->alignContent ?? $this->block->align_content ?? null) {
            $classes->add(Str::start($alignContent, 'is-position-'));
        }

        if ($this->block->fullHeight ?? $this->block->full_height ?? null) {
            $classes->add('full-height');
        }

        return $classes->filter()->implode(' ');
    }

    /**
     * Retrieve the block text domain.
     */
    public function getTextDomain(): string
    {
        return $this->textDomain
            ?? wp_get_theme()?->get('TextDomain')
            ?? 'acf-composer';
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

        $this->slug = $this->slug ?: Str::slug(Str::kebab($this->name));
        $this->view = $this->view ?: Str::start($this->slug, 'blocks.');
        $this->namespace = $this->namespace ?? Str::start($this->slug, $this->prefix);

        if (! Arr::has($this->fields, 'location.0.0')) {
            Arr::set($this->fields, 'location.0.0', [
                'param' => 'block',
                'operator' => '==',
                'value' => $this->namespace,
            ]);
        }

        $this->register(fn () => $this->hasJson()
            ? register_block_type($this->jsonPath())
            : acf_register_block_type($this->settings()->all())
        );

        return $this;
    }

    /**
     * Retrieve the block settings.
     */
    public function settings(): Collection
    {
        if ($this->settings) {
            return $this->settings;
        }

        if ($this->supports) {
            $this->supports = collect($this->supports)
                ->mapWithKeys(fn ($value, $key) => [Str::camel($key) => $value])
                ->merge($this->supports)
                ->all();
        }

        $settings = Collection::make([
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
            'alignText' => $this->align_text ?? $this->align,
            'alignContent' => $this->align_content,
            'styles' => $this->getStyles(),
            'supports' => $this->supports,
            'enqueue_assets' => fn ($block) => method_exists($this, 'assets') ? $this->assets($block) : null,
            'textdomain' => $this->getTextDomain(),
            'acf_block_version' => 2,
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
        ]);

        if ($this->example !== false) {
            if (method_exists($this, 'example') && is_array($example = $this->example())) {
                $this->example = array_merge($this->example, $example);
            }

            $settings = $settings->put('example', [
                'attributes' => [
                    'mode' => 'preview',
                    'data' => $this->example,
                ],
            ]);
        }

        if (! empty($this->uses_context)) {
            $settings = $settings->put('uses_context', $this->uses_context);
        }

        if (! empty($this->provides_context)) {
            $settings = $settings->put('provides_context', $this->provides_context);
        }

        return $this->settings = $settings;
    }

    /**
     * Retrieve the Block settings as JSON.
     */
    public function toJson(): string
    {
        $settings = $this->settings()->forget([
            'acf_block_version',
            'enqueue_assets',
            'mode',
            'render_callback',
        ])->put('acf', [
            'mode' => $this->mode,
            'renderTemplate' => $this::class,
        ])->put('name', $this->namespace);

        return $settings->filter()->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * Retrieve the Block JSON path.
     */
    public function jsonPath(): string
    {
        return $this->composer->manifest()->path("blocks/{$this->slug}/block.json");
    }

    /**
     * Determine if the Block has a JSON file.
     */
    public function hasJson(): bool
    {
        return file_exists($this->jsonPath());
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

        $this->template = is_array($this->template)
            ? $this->handleTemplate($this->template)->toJson()
            : $this->template;

        $this->classes = $this->getClasses();
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
