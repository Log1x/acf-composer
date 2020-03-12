<?php

namespace Log1x\AcfComposer;

use Roots\Acorn\Application;
use Illuminate\Support\Str;

class Composer
{
    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * The field groups.
     *
     * @var array
     */
    protected $fields;

    /**
     * Default field type settings.
     *
     * @return array
     */
    protected $defaults = [];

    /**
     * Create a new Field instance.
     *
     * @param  \Roots\Acorn\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->defaults = collect(
            $this->app->config->get('acf.defaults')
        )->merge($this->defaults)->mapWithKeys(function ($value, $key) {
            return [Str::snake($key) => $value];
        });

        if (! empty($this->name) && empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        $this->fields = $this->fields();
    }

    /**
     * Compose and register the defined field groups with ACF.
     *
     * @param  callback $callback
     * @return void
     */
    public function compose($callback = null)
    {
        if (! $this->fields) {
            return;
        }

        add_action('init', function () use ($callback) {
            if ($this->defaults->has('field_group')) {
                $this->fields = array_merge($this->fields, $this->defaults->get('field_group'));
            }

            if ($callback) {
                $callback();
            }

            acf_add_local_field_group($this->build());
        }, 20);
    }

   /**
     * Build the field group with the default field type settings.
     *
     * @param  array $fields
     * @return array
     */
    protected function build($fields = [])
    {
        return collect($fields ?: $this->fields)->map(function ($value, $key) {
            if (
                ! Str::contains($key, ['fields', 'sub_fields', 'layouts']) ||
                (Str::is($key, 'type') && ! $this->defaults->has($value))
            ) {
                return $value;
            }

            return array_map(function ($field) {
                if (collect($field)->keys()->intersect(['fields', 'sub_fields', 'layouts'])->isNotEmpty()) {
                    return $this->build($field);
                }

                return array_merge($this->defaults->get($field['type'], []), $field);
            }, $value);
        })->all();
    }

    /**
     * Render the view using Blade.
     *
     * @param  string $view
     * @param  array $with
     * @return string
     */
    public function view($view, $with = [])
    {
        $view = $this->app->resourcePath(
            Str::finish(
                str_replace('.', '/', basename($view, '.blade.php')),
                '.blade.php'
            )
        );

        if (! file_exists($view)) {
            return;
        }

        return $this->app->make('view')->file(
            $view,
            array_merge($this->with(), $with)
        )->render();
    }

    /**
     * Get field partial if it exists.
     *
     * @param  string $name
     * @param  string $path
     * @return mixed
     */
    public function get($name = '', $path = 'Fields')
    {
        $name = strtr($name, [
            '.php' => '',
            '.' => '/'
        ]);

        include $this->app->path(
            Str::finish(
                Str::finish($path, '/'),
                Str::finish($name, '.php')
            ),
        );
    }

    /**
     * Field groups to be composed.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    /**
     * Data to be passed to the rendered view.
     *
     * @return array
     */
    public function with()
    {
        return [];
    }
}
