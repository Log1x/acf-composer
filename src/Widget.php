<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Log1x\AcfComposer\Concerns\InteractsWithBlade;
use Log1x\AcfComposer\Contracts\Widget as WidgetContract;
use WP_Widget;

abstract class Widget extends Composer implements WidgetContract
{
    use InteractsWithBlade;

    /**
     * The widget instance.
     *
     * @var object
     */
    public $widget;

    /**
     * The widget ID.
     *
     * @var string
     */
    public $id;

    /**
     * The name of the widget.
     *
     * @var string
     */
    public $name = '';

    /**
     * The widget slug.
     *
     * @var string
     */
    public $slug = '';

    /**
     * The widget description.
     *
     * @var string
     */
    public $description = '';

    /**
     * Compose and register the defined ACF field groups.
     *
     * @return mixed
     */
    public function compose()
    {
        if (empty($this->fields) || empty($this->name)) {
            return;
        }

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        if (! Arr::has($this->fields, 'location.0.0')) {
            Arr::set($this->fields, 'location.0.0', [
                'param' => 'widget',
                'operator' => '==',
                'value' => $this->slug,
            ]);
        }

        $this->register(function () {
            $this->widget = (object) $this->collect(
                Arr::get($GLOBALS, 'wp_registered_widgets')
            )->filter(fn ($value) => $value['name'] === $this->name)->pop();
        });

        add_filter('widgets_init', function () {
            register_widget($this->widget());
        }, 20);

        return $this;
    }

    /**
     * Determine if the widget should be displayed.
     */
    public function show(): bool
    {
        return true;
    }

    /**
     * Create a new WP_Widget instance.
     */
    protected function widget(): WP_Widget
    {
        return new class($this) extends WP_Widget
        {
            /**
             * Create a new WP_Widget instance.
             */
            public function __construct(public Widget $composer)
            {
                parent::__construct(
                    $this->composer->slug,
                    $this->composer->name,
                    ['description' => $this->composer->description]
                );
            }

            /**
             * Render the widget.
             *
             * @param  array  $args
             * @param  array  $instance
             * @return void
             */
            public function widget($args, $instance)
            {
                $this->composer->id = $this->composer->widget->id = Str::start($args['widget_id'], 'widget_');

                if (! $this->composer->show()) {
                    return;
                }

                echo Arr::get($args, 'before_widget');

                if (! empty($this->composer->title())) {
                    echo $this->composer->collect([
                        Arr::get($args, 'before_title'),
                        $this->composer->title(),
                        Arr::get($args, 'after_title'),
                    ])->implode(PHP_EOL);
                }

                echo $this->composer->view(
                    Str::finish('widgets.', $this->composer->slug),
                    ['widget' => $this->composer]
                );

                echo Arr::get($args, 'after_widget');
            }

            /**
             * Output the widget settings update form.
             * This is intentionally blank due to it being set by ACF.
             *
             * @param  array  $instance
             * @return void
             */
            public function form($instance)
            {
                //
            }
        };
    }
}
