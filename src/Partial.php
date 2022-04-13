<?php

namespace Log1x\AcfComposer;

abstract class Partial extends Composer
{

    /**
     * The field groups.
     *
     * @var \StoutLogic\AcfBuilder\FieldsBuilder|array
     */
    protected $fields;

    /**
     * Compose and register the defined field groups with ACF.
     *
     * @return void
     */
    public function compose()
    {
        $this->fields = $this->fields();

        if (empty($this->fields)) {
            return;
        }

        return $this->fields;
    }
}
