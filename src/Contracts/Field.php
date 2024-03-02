<?php

namespace Log1x\AcfComposer\Contracts;

interface Field
{
    /**
     * The field group.
     *
     * @return \Log1x\AcfComposer\Builder|array
     */
    public function fields();
}
