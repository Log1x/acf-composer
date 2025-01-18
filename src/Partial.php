<?php

namespace Log1x\AcfComposer;

abstract class Partial extends Composer
{
    /**
     * Compose and register the defined field groups with ACF.
     *
     * @return \StoutLogic\AcfBuilder\FieldsBuilder|void
     */
    public function compose()
    {
        $fields = $this->resolveFields();

        if (blank($fields)) {
            return;
        }

        return $fields;
    }
}
