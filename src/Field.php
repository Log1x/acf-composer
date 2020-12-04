<?php

namespace Log1x\AcfComposer;

abstract class Field extends Composer
{
    /**
     * Compose and register the defined field groups with ACF.
     *
     * @return void
     */
    public function compose()
    {
        if (empty($this->fields)) {
            return;
        }

        $this->register();

        return $this;
    }
}
