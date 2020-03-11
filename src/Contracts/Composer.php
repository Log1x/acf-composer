<?php

namespace Log1x\AcfComposer\Contracts;

interface Composer
{
    /**
     * Map field groups with configured default field settings.
     *
     * @param  array $build
     * @return array
     */
    public function build($fields = []);
}
