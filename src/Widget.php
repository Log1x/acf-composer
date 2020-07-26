<?php

namespace Log1x\AcfComposer;

use WP_Widget;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Log1x\AcfComposer\Contracts\Widget as WidgetContract;
use Log1x\AcfComposer\Concerns\InteractsWithBlade;

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
     * @return void
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
            $this->widget = (object) collect(
                Arr::get($GLOBALS, 'wp_registered_widgets')
            )->filter(function ($value) {
                return $value['name'] == $this->name;
            })->pop();

            if (empty($this->widget) || empty($this->widget->id)) {
                return;
            }

            $this->widget->id = Str::start($this->widget->id, 'widget_');
            $this->id = $this->widget->id;
        });

        add_filter('widgets_init', function () {
            register_widget($this->widget());
        }, 20);
    }

    /**
     * Returns an instance of WP_Widget used to register the widget.
     *
     * @return WP_Widget
     */
    public function widget()
    {
        return (new class ($this) extends WP_Widget {
            /**
             * Create a new WP_Widget instance.
             *
             * @param  \Log1x\AcfComposer\Widget $widget
             * @return void
             */
            public function __construct($widget)
            {
                $this->widget = $widget;

                parent::__construct(
                    $this->widget->slug,
                    $this->widget->name,
                    ['description' => $this->widget->description]
                );
            }

            /**
             * Render the widget.
             *
             * @param  array $args
             * @param  array $instance
             * @return void
             */
            public function widget($args, $instance)
            {
                echo Arr::get($args, 'before_widget');

                if (! empty($this->widget->title())) {
                    echo collect([
                        Arr::get($args, 'before_title'),
                        $this->widget->title(),
                        Arr::get($args, 'after_title')
                    ])->implode(PHP_EOL);
                }

                echo $this->widget->view(
                    Str::finish('widgets.', $this->widget->slug),
                    ['widget' => $this->widget]
                );

                echo Arr::get($args, 'after_widget');
            }

            /**
             * Output the widget settings update form.
             * This is intentionally blank due to it being set by ACF.
             *
             * @param  array $instance
             * @return void
             */
            public function form($instance)
            {
                //
            }
        });
    }
}
