<?php

namespace Log1x\AcfComposer\Concerns;

use Illuminate\Support\Collection;

trait HasCollection
{
    /**
     * Initialize a Collection instance.
     *
     * @param  string[]  $value
     * @return Collection
     */
    public function collect($value)
    {
        return Collection::make($value);
    }
}
