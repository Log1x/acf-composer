<?php

namespace Log1x\AcfComposer;

use WP_Widget;
use Illuminate\Support\Arr;

abstract class Widget extends Composer
{
    /**
     * The display name of the widget.
     *
     * @var string
     */
    public $name;

    /**
     * The slug of the widget.
     *
     * @var string
     */
    public $slug;

    /**
     * The description of the widget.
     *
     * @var string
     */
    public $description;

    /**
     * Compose and register the defined field groups with ACF.
     *
     * @param  callback $callback
     * @return void
     */
    public function compose($callback = null)
    {
        parent::compose(function () {
            if (! Arr::has($this->fields, 'location.0.0')) {
                Arr::set($this->fields, 'location.0.0', [
                    'param' => 'widget',
                    'operator' => '==',
                    'value' => $this->slug,
                ]);
            }
        });

        add_filter('widgets_init', function () {
            register_widget($this->widget());
        });
    }

    /**
     * Returns an instance of WP_Widget used to register the widget.
     *
     * @return WP_Widget
     */
    public function widget()
    {
        return (new class($this) extends WP_Widget {
            /**
             * Create a new WP_Widget instance.
             *
             * @param  \Log1x\AcfComposer\Widget $widget
             * @return void
             */
            public function __construct($widget) {
                $this->widget = $widget;

                parent::__construct(
                    $this->widget->slug,
                    $this->widget->name,
                    ['description' => $this->widget->description]
                );
            }

            /**
             * Render the widget for WordPress.
             *
             * @param  array $args
             * @param  void  $instance
             * @return void
             */
            public function widget($args, $instance)
            {
                echo collect(
                    Arr::get($args, 'before_widget'),
                    Arr::get($args, 'before_title'),
                    $this->widget->title(),
                    Arr::get($args, 'after_title'),
                    $this->widget->view("views.widgets.{$this->widget->slug}"),
                    Arr::get($args, 'after_widget'),
                )->implode(PHP_EOL);
            }
        });
    }

    /**
     * Returns the widget title.
     *
     * @return string
     */
    public function title()
    {
        //
    }
}
