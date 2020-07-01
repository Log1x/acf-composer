<?php

namespace Log1x\AcfComposer\Concerns;

use Illuminate\Support\Str;

trait HasView
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
     * Data to be passed to the rendered view.
     *
     * @return array
     */
    public function with()
    {
        return [];
    }
}
