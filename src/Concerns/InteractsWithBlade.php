<?php

namespace Log1x\AcfComposer\Concerns;

use Illuminate\Support\Str;

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
            ! empty($this->preview)
        ) {
            $preview = str_replace(
                $name = Str::afterLast($view, '.'),
                Str::start($name, 'preview-'),
                $view
            );

            $view = view()->exists($preview) ? $preview : $view;
        }

        return view($view, $with, $this->with())->render();
    }
}
