<?php

namespace Log1x\AcfComposer;

abstract class Partial extends Composer
{
    /**
     * Compose and register the defined field groups with ACF.
     *
     * @param  array  $args  Optional arguments to pass to the partial.
     * @return \StoutLogic\AcfBuilder\FieldsBuilder|void
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
