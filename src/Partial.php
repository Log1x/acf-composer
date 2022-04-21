<?php

namespace Log1x\AcfComposer;

abstract class Partial extends Composer
{
    /**
     * Compose and register the defined field groups with ACF.
     *
     * @return void
     */
    public function compose()
    {
        $fields = $this->fields();

        if (empty($fields)) {
            return;
        }

        return $fields;
    }
}
