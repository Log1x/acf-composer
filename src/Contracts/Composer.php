<?php

namespace Log1x\AcfComposer\Contracts;

interface Composer
{
    /**
     * The field group.
     *
     * @return \StoutLogic\AcfBuilder\FieldsBuilder|array
     */
    public function fields();
}
