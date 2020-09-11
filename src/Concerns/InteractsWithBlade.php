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
        if (
            isset($this->block) &&
            ! empty($this->preview) &&
            view()->exists(Str::start($view, 'preview-'))
        ) {
            $view = Str::start($view, 'preview-');
        }

        return view($view, $with, $this->with())->render();
    }
}
