<?php

namespace Log1x\AcfComposer\Concerns;

use function Roots\view;

trait InteractsWithBlade
{
    /**
     * Render the specified view using Blade.
     *
     * @param  string $view
     * @param  array  $with
     * @return string
     */
    public function view($view, $with = [])
    {
        if ($this->preview === true) {
            $preview = str_replace('blocks.', 'blocks.preview-', $view);
            if (view()->exists($preview)) {
                $view = $preview;
            }
        }
        if (!view()->exists($view)) {
            return;
        }

        return view($view, $with, $this->with())->render();
    }
}
