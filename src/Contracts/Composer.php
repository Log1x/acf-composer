<?php

namespace Log1x\AcfComposer\Contracts;

interface Composer
{
    /**
     * Compose the fields.
     *
     * @param array  $args Optional arguments to pass.
     *
     * @return mixed
     */
    public function compose(array $args = []);
}
