<?php

namespace Log1x\AcfComposer\Builder;

use Log1x\AcfComposer\Builder;
use StoutLogic\AcfBuilder\FieldsBuilder;
use StoutLogic\AcfBuilder\FlexibleContentBuilder as FieldBuilder;

class FlexibleContentBuilder extends FieldBuilder
{
    /**
     * Add a layout to the Flexible Content field.
     *
     * @param  string|FieldsBuilder  $layout
     * @param  array  $args
     * @return \Log1x\AcfComposer\Builder
     */
    public function addLayout($layout, $args = [])
    {
        if ($layout instanceof FieldsBuilder) {
            $layout = clone $layout;
        } else {
            $layout = Builder::make($layout, $args);
        }

        $layout = $this->initializeLayout($layout, $args);

        $this->pushLayout($layout);

        return $layout;
    }
}
