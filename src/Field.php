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

        if ($this->defaults->has('field_group')) {
            $this->fields = array_merge($this->fields, $this->defaults->get('field_group'));
        }

        $this->register();
    }
}
