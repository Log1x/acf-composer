<?php

namespace Log1x\AcfComposer;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Log1x\AcfComposer\Concerns\InteractsWithBlade;
use Log1x\AcfComposer\Contracts\Block as BlockContract;
use WP_Block_Supports;

use function Roots\asset;

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
     * The default block spacing.
     *
     * @var array
     */
    public $spacing = [
        'padding' => null,
        'margin' => null,
    ];

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
     * The current block style.
     *
     * @var string
     */
    public $style;

    /**
     * The block dimensions.
     *
     * @var string
     */
    public $inlineStyle;

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
     * @var array|string
     */
    public $template = [];

    /**
     * Determine whether to save the block's data as post meta.
     *
     * @var bool
     */
    public $usePostMeta = false;

    /**
     * The block API version.
     *
     * @var int|null
     */
    public $apiVersion = null;

    /**
     * The internal ACF block version.
     *
     * @var int
     */
    public $blockVersion = 2;

    /**
     * Validate block fields as per the field group configuration.
     *
     * @var bool
     */
    public $validate = true;

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
        foreach ($this->attributes() as $key => $value) {
            if (! property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }

        $defaults = config('acf.blocks', []);

        foreach ($defaults as $key => $value) {
            if (! property_exists($this, $key) || filled($this->{$key})) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    /**
     * Retrieve the block name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retrieve the block description.
     */
    public function getDescription(): string
    {
        return $this->description;
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
        return $this->collect($this->getStyles())->firstWhere('isDefault') ?? [];
    }

    /**
     * Retrieve the block styles.
     */
    public function getStyles(): array
    {
        $styles = $this->collect($this->styles)->map(function ($value, $key) {
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
     * Retrieve the block supports.
     */
    public function getSupports(): array
    {
        $supports = $this->collect($this->supports)
            ->mapWithKeys(fn ($value, $key) => [Str::camel($key) => $value])
            ->merge($this->supports);

        $typography = $supports->get('typography', []);

        if ($supports->has('alignText')) {
            $typography['textAlign'] = $supports->get('alignText');

            $supports->forget(['alignText', 'align_text']);
        }

        if ($typography) {
            $supports->put('typography', $typography);
        }

        return $supports->all();
    }

    /**
     * Retrieve the block support attributes.
     */
    public function getSupportAttributes(): array
    {
        $attributes = [];

        if ($this->align) {
            $attributes['align'] = [
                'type' => 'string',
                'default' => $this->align,
            ];
        }

        if ($this->align_content) {
            $attributes['alignContent'] = [
                'type' => 'string',
                'default' => $this->align_content,
            ];
        }

        $styles = [];

        if ($this->align_text) {
            $styles['typography']['textAlign'] = $this->align_text;
        }

        $spacing = array_filter($this->spacing);

        if ($spacing) {
            $styles['spacing'] = $spacing;
        }

        if ($styles) {
            $attributes['style'] = [
                'type' => 'object',
                'default' => $styles,
            ];
        }

        return $attributes;
    }

    /**
     * Retrieve the block HTML attributes.
     */
    public function getHtmlAttributes(): array
    {
        if ($this->preview) {
            return [];
        }

        return WP_Block_Supports::get_instance()?->apply_block_supports() ?? [];
    }

    /**
     * Retrieve the inline block styles.
     */
    public function getInlineStyle(): string
    {
        $supports = $this->getHtmlAttributes();

        return $supports['style'] ?? '';
    }

    /**
     * Retrieve the block classes.
     */
    public function getClasses(): string
    {
        $supports = $this->getHtmlAttributes();

        $class = $supports['class'] ?? '';

        if ($alignContent = $this->block->alignContent ?? $this->block->align_content ?? null) {
            $class = "{$class} is-position-{$alignContent}";
        }

        if ($this->block->fullHeight ?? $this->block->full_height ?? null) {
            $class = "{$class} full-height";
        }

        return str_replace(
            acf_slugify($this->namespace),
            $this->slug,
            trim($class)
        );
    }

    /**
     * Retrieve the component attribute bag.
     */
    protected function getComponentAttributeBag(): ComponentAttributeBag
    {
        return (new ComponentAttributeBag)
            ->class($this->getClasses())
            ->style($this->getInlineStyle())
            ->merge(['id' => $this->block->anchor ?? null])
            ->filter(fn ($value) => filled($value) && $value !== ';');
    }

    /**
     * Retrieve the block API version.
     */
    public function getApiVersion(): int
    {
        return $this->apiVersion ?? 2;
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
     * Retrieve the block icon.
     */
    public function getIcon(): string|array
    {
        if (is_array($this->icon)) {
            return $this->icon;
        }

        if (Str::startsWith($this->icon, 'asset:')) {
            $asset = Str::of($this->icon)
                ->after('asset:')
                ->before('.svg')
                ->replace('.', '/')
                ->finish('.svg');

            return asset($asset)->contents();
        }

        return $this->icon;
    }

    /**
     * Handle the block template.
     */
    public function handleTemplate(array $template = []): Collection
    {
        return $this->collect($template)->map(function ($block, $key) {
            $name = is_numeric($key)
                ? array_key_first((array) $block)
                : $key;

            $value = is_numeric($key)
                ? ($block[$name] ?? [])
                : $block;

            if (is_array($value) && isset($value['innerBlocks'])) {
                $innerBlocks = $this->handleTemplate($value['innerBlocks'])->all();

                unset($value['innerBlocks']);

                return [$name, $value, $innerBlocks];
            }

            return [$name, $value];
        })->values();
    }

    /**
     * Compose the fields and register the block.
     */
    public function compose(): ?self
    {
        $this->mergeAttributes();

        if (blank($this->getName())) {
            return null;
        }

        $this->slug = $this->slug ?: Str::slug(Str::kebab($this->getName()));
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
            : $this->registerBlockType()
        );

        return $this;
    }

    /**
     * Register the block type.
     */
    public function registerBlockType(): void
    {
        $block = acf_validate_block_type($this->settings()->all());
        $block = apply_filters('acf/register_block_type_args', $block);

        if (acf_has_block_type($block['name'])) {
            throw new Exception("Block type [{$block['name']}] is already registered.");
        }

        $block['attributes'] = array_merge(
            acf_get_block_type_default_attributes($block),
            $block['attributes'] ?? []
        );

        acf_get_store('block-types')->set($block['name'], $block);

        $block['render_callback'] = 'acf_render_block_callback';

        register_block_type(
            $block['name'],
            $block
        );

        add_action('enqueue_block_editor_assets', 'acf_enqueue_block_assets');
    }

    /**
     * Retrieve the block settings.
     */
    public function settings(): Collection
    {
        if ($this->settings) {
            return $this->settings;
        }

        $settings = Collection::make([
            'name' => $this->slug,
            'title' => $this->getName(),
            'description' => $this->getDescription(),
            'category' => $this->category,
            'icon' => $this->getIcon(),
            'keywords' => $this->keywords,
            'post_types' => $this->post_types,
            'mode' => $this->mode,
            'align' => $this->align,
            'attributes' => $this->getSupportAttributes(),
            'alignText' => $this->align_text ?? $this->align,
            'alignContent' => $this->align_content,
            'styles' => $this->getStyles(),
            'supports' => $this->getSupports(),
            'textdomain' => $this->getTextDomain(),
            'acf_block_version' => $this->blockVersion,
            'api_version' => $this->getApiVersion(),
            'validate' => $this->validate,
            'use_post_meta' => $this->usePostMeta,
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

        if (filled($this->parent)) {
            $settings = $settings->put('parent', $this->parent);
        }

        if (filled($this->ancestor)) {
            $settings = $settings->put('ancestor', $this->ancestor);
        }

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
        $settings = $this->settings()
            ->put('name', $this->namespace)
            ->put('apiVersion', $this->getApiVersion())
            ->put('usesContext', $this->uses_context)
            ->put('providesContext', $this->provides_context)
            ->put('acf', [
                'blockVersion' => $this->blockVersion,
                'mode' => $this->mode,
                'postTypes' => $this->post_types,
                'renderTemplate' => $this::class,
                'usePostMeta' => $this->usePostMeta,
                'validate' => $this->validate,
            ])
            ->forget([
                'api_version',
                'acf_block_version',
                'align',
                'alignContent',
                'alignText',
                'mode',
                'post_types',
                'render_callback',
                'use_post_meta',
                'validate',
                'uses_context',
                'provides_context',
            ]);

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

        if (! is_admin() && method_exists($this, 'assets')) {
            $instance = (array) ($this->block ?? []);

            add_action('enqueue_block_assets', function () use ($instance): void {
                $this->assets($instance);
            });
        }

        return $this->view($this->view, [
            'block' => $this,
            'attributes' => $this->getComponentAttributeBag(),
        ]);
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
