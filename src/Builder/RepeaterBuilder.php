<?php

namespace Log1x\AcfComposer\Builder;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Builder\Concerns\HasParentContext;
use StoutLogic\AcfBuilder\RepeaterBuilder as GroupBuilder;

/**
 * @method FieldBuilder addPartial(string $partial)
 */
class RepeaterBuilder extends GroupBuilder
{
    use HasParentContext;

    /**
     * The fields builder instance.
     *
     * @var \Log1x\AcfComposer\Builder
     */
    protected $fieldsBuilder;

    /**
     * {@inheritdoc}
     */
    public function __construct($name, $type = 'repeater', $config = [])
    {
        parent::__construct($name, $type, $config);

        $this->fieldsBuilder = Builder::make($name);

        $this->fieldsBuilder->setParentContext($this);
    }
}
