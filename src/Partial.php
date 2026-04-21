<?php

namespace Log1x\AcfComposer;

use StoutLogic\AcfBuilder\FieldsBuilder;

abstract class Partial extends Composer
{
    /**
     * Compose and register the defined field groups with ACF.
     *
     * @param  array  $args  Optional arguments to pass to the partial.
     * @return FieldsBuilder|void
     */
    public function compose(array $args = [])
    {
        $fields = $this->resolveFields($args);

        if (blank($fields)) {
            return;
        }

        return $fields;
    }
}
