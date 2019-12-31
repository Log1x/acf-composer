<?php

namespace Log1x\AcfComposer;

use Roots\Acorn\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use function Roots\asset;
use function Roots\view;

abstract class Block
{
    /**
     * Acorn Container
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * Default field type settings.
     *
     * @return array
     */
    protected $defaults = [];

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
     * Assets enqueued when the block is shown.
     *
     * @var array
     */
    protected $assets = [];

    /**
     * The blocks status.
     *
     * @var boolean
     */
    protected $enabled = true;

    /**
     * The blocks fields.
     *
     * @var array
     */
    protected $fields;

    /**
     * The block namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * The block prefix.
     *
     * @var string
     */
    protected $prefix = 'acf/';

    /**
     * The block properties.
     *
     * @var array
     */
    protected $block;

    /**
     * Create a new Block instance.
     *
     * @param  \Roots\Acorn\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Compose the block.
     *
     * @return void
     */
    public function compose()
    {
        if (! $this->register() || ! function_exists('acf')) {
            return;
        }

        collect($this->register())->each(function ($value, $name) {
            $this->{$name} = $value;
        });

        $this->defaults = collect(
            $this->app->config->get('acf.defaults')
        )->merge($this->defaults)->mapWithKeys(function ($value, $key) {
            return [Str::snake($key) => $value];
        });

        $this->slug = Str::slug($this->name);
        $this->namespace = $this->prefix . $this->slug;
        $this->fields = $this->fields();

        if (! $this->enabled) {
            return;
        }

        add_action('init', function () {
            acf_register_block([
                'name'            => $this->slug,
                'title'           => $this->name,
                'description'     => $this->description,
                'category'        => $this->category,
                'icon'            => $this->icon,
                'keywords'        => $this->keywords,
                'post_types'      => $this->post_types,
                'mode'            => $this->mode,
                'align'           => $this->align,
                'supports'        => $this->supports,
                'enqueue_assets'  => [$this, 'assets'],
                'render_callback' => [$this, 'view'],
            ]);

            if (! empty($this->fields)) {
                acf_add_local_field_group($this->build());
            }
        }, 20);
    }

    /**
     * Build the field group with our default field type settings.
     *
     * @return array
     */
    protected function build()
    {
        if (! Arr::has($this->fields, 'location.0.0')) {
            Arr::set($this->fields, 'location.0.0', [
                'param' => 'block',
                'operator' => '==',
                'value' => $this->namespace,
            ]);
        }

        return collect($this->fields)->map(function ($value, $key) {
            if ($key !== 'fields') {
                return $value;
            }

            foreach ($value as $field) {
                if ($this->defaults->has($field['type'])) {
                    return [array_merge($field, $this->defaults->get($field['type']))];
                }
            }

            return $value;
        })->all();
    }

    /**
     * URI for the block.
     *
     * @return string
     */
    protected function uri($path = '')
    {
        return str_replace(
            get_theme_file_path(),
            get_theme_file_uri(),
            home_url($path)
        );
    }

    /**
     * View used for rendering the block.
     *
     * @param  array $block
     * @return string
     */
    public function view($block)
    {
        $this->block = (object) $block;

        if (file_exists($view = $this->app->resourcePath("views/blocks/{$this->slug}.blade.php"))) {
            echo view($view, array_merge($this->with(), ['block' => $this->block]));
        } elseif (file_exists($notFound = $this->app->resourcePath('views/blocks/view-404.blade.php'))) {
            echo view($notFound, ['view' => $view]);
        } else {
            echo view(__DIR__ . '/resources/views/view-404.blade.php', ['view' => $view]);
        }
    }

    /**
     * Assets used when rendering the block.
     *
     * @return void
     */
    public function assets()
    {
        $styles = [
            'css/blocks/' . $this->slug . '.css',
            'styles/blocks/' . $this->slug . '.css',
        ];

        $scripts = [
            'js/blocks/' . $this->slug . '.js',
            'scripts/blocks/' . $this->slug . '.js',
        ];

        if (! empty($this->assets)) {
            foreach ($this->assets as $asset) {
                if (Str::endsWith($asset, '.css')) {
                    $styles = Arr::prepend($styles, $asset);
                }

                if (Str::endsWith($asset, '.js')) {
                    $scripts = Arr::prepend($scripts, $asset);
                }
            }
        }

        foreach ($styles as $style) {
            if (asset($style)->exists()) {
                wp_enqueue_style($this->namespace, asset($style)->uri(), false, null);
            }
        }

        foreach ($scripts as $script) {
            if (asset($script)->exists()) {
                wp_enqueue_script($this->namespace, asset($script)->uri(), null, null, true);
            }
        }
    }

    /**
     * Data to be passed to the block before registering.
     *
     * @return array
     */
    public function register()
    {
        return [];
    }

    /**
     * Fields to be attached to the block.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    /**
     * Data to be passed to the rendered block.
     *
     * @return array
     */
    public function with()
    {
        return [];
    }
}
