<?php

namespace Log1x\AcfComposer\Contracts;

interface Widget
{
    /**
     * The widget title.
     *
     * @return string
     */
    public function title();

    /**
     * Data to be passed to the rendered widget view.
     *
     * @return array
     */
    public function with();
}
