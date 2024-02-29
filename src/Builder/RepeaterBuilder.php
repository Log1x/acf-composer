<?php

namespace Log1x\AcfComposer\Builder;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Builder\Concerns\HasParentContext;
use StoutLogic\AcfBuilder\RepeaterBuilder as GroupBuilder;

/**
 * @method AccordionBuilder addAccordion(string $label, array $args = [])
 * @method Builder addLayout(string|FieldsBuilder $layout, array $args = [])
 * @method Builder endFlexibleContent()
 * @method Builder endGroup()
 * @method Builder endRepeater()
 * @method ChoiceFieldBuilder addButtonGroup(string $name, array $args = [])
 * @method ChoiceFieldBuilder addCheckbox(string $name, array $args = [])
 * @method ChoiceFieldBuilder addChoiceField(string $name, string $type, array $args = [])
 * @method ChoiceFieldBuilder addRadio(string $name, array $args = [])
 * @method ChoiceFieldBuilder addSelect(string $name, array $args = [])
 * @method FieldBuilder addColorPicker(string $name, array $args = [])
 * @method FieldBuilder addDatePicker(string $name, array $args = [])
 * @method FieldBuilder addDateTimePicker(string $name, array $args = [])
 * @method FieldBuilder addEmail(string $name, array $args = [])
 * @method FieldBuilder addField(string $name, string $type, array $args = [])
 * @method FieldBuilder addFields(FieldsBuilder|array $fields)
 * @method FieldBuilder addFile(string $name, array $args = [])
 * @method FieldBuilder addGallery(string $name, array $args = [])
 * @method FieldBuilder addGoogleMap(string $name, array $args = [])
 * @method FieldBuilder addImage(string $name, array $args = [])
 * @method FieldBuilder addLink(string $name, array $args = [])
 * @method FieldBuilder addMessage(string $label, string $message, array $args = [])
 * @method FieldBuilder addNumber(string $name, array $args = [])
 * @method FieldBuilder addOembed(string $name, array $args = [])
 * @method FieldBuilder addPageLink(string $name, array $args = [])
 * @method FieldBuilder addPartial(string $partial)
 * @method FieldBuilder addPassword(string $name, array $args = [])
 * @method FieldBuilder addPostObject(string $name, array $args = [])
 * @method FieldBuilder addRange(string $name, array $args = [])
 * @method FieldBuilder addRelationship(string $name, array $args = [])
 * @method FieldBuilder addTaxonomy(string $name, array $args = [])
 * @method FieldBuilder addText(string $name, array $args = [])
 * @method FieldBuilder addTextarea(string $name, array $args = [])
 * @method FieldBuilder addTimePicker(string $name, array $args = [])
 * @method FieldBuilder addTrueFalse(string $name, array $args = [])
 * @method FieldBuilder addUrl(string $name, array $args = [])
 * @method FieldBuilder addUser(string $name, array $args = [])
 * @method FieldBuilder addWysiwyg(string $name, array $args = [])
 * @method FlexibleContentBuilder addFlexibleContent(string $name, array $args = [])
 * @method GroupBuilder addGroup(string $name, array $args = [])
 * @method GroupBuilder end()
 * @method LocationBuilder setLocation(string $param, string $operator, string $value)
 * @method RepeaterBuilder addRepeater(string $name, array $args = [])
 * @method TabBuilder addTab(string $label, array $args = [])
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
