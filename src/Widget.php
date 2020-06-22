<?php

namespace Log1x\AcfComposer;

use WP_Widget;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class Widget extends Composer
{
    use Traits\HasView;

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
     * The description of the widget.
     *
     * @var string
     */
    public $description = '';

    /**
     * The widget title.
     *
     * @return string
     */
    public function title()
    {
        //
    }

    /**
     * Compose and register the defined ACF field groups.
     *
     * @return void
     */
    public function compose()
    {
        if (empty($this->name)) {
            return;
        }

        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }

        $this->widget = (object) collect(
            Arr::get($GLOBALS, 'wp_registered_widgets')
        )->filter(function ($value) {
            return $value['name'] == $this->name;
        })->pop();

        $this->widget->id = Str::start($this->widget->id, 'widget_');
        $this->id = $this->widget->id;

        if (! Arr::has($this->fields, 'location.0.0')) {
            Arr::set($this->fields, 'location.0.0', [
                'param' => 'widget',
                'operator' => '==',
                'value' => $this->slug,
            ]);
        }

        add_filter('widgets_init', function () {
            register_widget($this->widget());
        });

        $this->register();
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
            public function __construct(Widget $widget)
            {
                $this->widget = $widget;
                $this->view = Str::finish('views.widgets.', $this->widget->slug);

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
             * @param  array $instance
             * @return void
             */
            public function widget($args, $instance)
            {
                if (empty($this->widget->view($this->view))) {
                    return;
                }

                echo Arr::get($args, 'before_widget');

                if (! empty($this->widget->title())) {
                    echo collect([
                        Arr::get($args, 'before_title'),
                        $this->widget->title(),
                        Arr::get($args, 'after_title')
                    ])->implode('');
                }

                echo $this->widget->view(
                    Str::finish('views.widgets.', $this->widget->slug),
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
