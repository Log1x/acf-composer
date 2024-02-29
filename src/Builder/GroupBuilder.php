<?php

namespace Log1x\AcfComposer\Builder;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Builder\Concerns\HasParentContext;
use StoutLogic\AcfBuilder\GroupBuilder as GroupBuilderBase;

/**
 * @method FieldBuilder addPartial(string $partial)
 */
class GroupBuilder extends GroupBuilderBase
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
    public function __construct($name, $type = 'group', $config = [])
    {
        parent::__construct($name, $type, $config);

        $this->fieldsBuilder = Builder::make($name);

        $this->fieldsBuilder->setParentContext($this);
    }
}
