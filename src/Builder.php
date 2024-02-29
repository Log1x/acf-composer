<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Log1x\AcfComposer\Builder\AccordionBuilder;
use Log1x\AcfComposer\Builder\ChoiceFieldBuilder;
use Log1x\AcfComposer\Builder\FieldBuilder;
use Log1x\AcfComposer\Builder\FlexibleContentBuilder;
use Log1x\AcfComposer\Builder\GroupBuilder;
use Log1x\AcfComposer\Builder\RepeaterBuilder;
use Log1x\AcfComposer\Builder\TabBuilder;
use ReflectionClass;
use StoutLogic\AcfBuilder\FieldsBuilder;
use StoutLogic\AcfBuilder\LocationBuilder;

/**
 * Builds configurations for an ACF Field.
 *
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
class Builder extends FieldsBuilder
{
    /**
     * The ACF Composer instance.
     */
    protected ?AcfComposer $composer = null;

    /**
     * The custom field types.
     */
    public ?array $types = null;

    /**
     * Make an instance of the Builder.
     */
    public static function make(string $name, array $config = []): self
    {
        return new static($name, $config);
    }

    /**
     * Add a partial to the field group.
     */
    public function addPartial(string $partial): self
    {
        if (
            is_string($partial) &&
            is_subclass_of($partial, Partial::class) &&
            ! (new ReflectionClass($partial))->isAbstract()
        ) {
            $partial = $partial::make($this->composer())->compose();
        }

        if (! is_a($partial, FieldsBuilder::class)) {
            throw new InvalidArgumentException('The partial must return an instance of Builder.');
        }

        return $this->addFields($partial);
    }

    /**
     * Retrieve the custom field types.
     */
    protected function getFieldTypes(): array
    {
        if (! is_null($this->types)) {
            return $this->types;
        }

        $types = config('acf.types', []);

        return $this->types = collect($types)->mapWithKeys(fn ($type, $key) => [
            Str::of($key)->studly()->start('add')->toString() => $type,
        ])->all();
    }

    /**
     * Retrieve the specified field type.
     */
    protected function getFieldType(string $type): ?string
    {
        return $this->getFieldTypes()[$type] ?? null;
    }

    /**
     * Retrieve an instance of ACF Composer.
     */
    protected function composer(): AcfComposer
    {
        if ($this->composer) {
            return $this->composer;
        }

        return $this->composer = app('AcfComposer');
    }

    /**
     * {@inheritdoc}
     */
    public function addField($name, $type, array $args = [])
    {
        return $this->initializeField(new FieldBuilder($name, $type, $args));
    }

    /**
     * Initialize the field and push it to the field manager.
     *
     * @param  FieldBuilder  $field
     * @return FieldBuilder
     */
    protected function initializeField($field)
    {
        $field->setParentContext($this);

        $this->getFieldManager()->pushField($field);

        return $field;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocation($param, $operator, $value)
    {
        $location = parent::setLocation($param, $operator, $value);

        $location->setParentContext($this);

        return $location;
    }

    /**
     * Add a group field.
     *
     * @param  string  $name
     * @return \Log1x\AcfComposer\Builder\GroupBuilder
     */
    public function addGroup($name, array $args = [])
    {
        return $this->initializeField(new GroupBuilder($name, 'group', $args));
    }

    /**
     * Add a repeater field.
     *
     * @param  string  $name
     * @return \Log1x\AcfComposer\Builder\RepeaterBuilder
     */
    public function addRepeater($name, array $args = [])
    {
        return $this->initializeField(new RepeaterBuilder($name, 'repeater', $args));
    }

    /**
     * Add a flexible content field.
     *
     * @param  string  $name
     * @return \Log1x\AcfComposer\Builder\FlexibleContentBuilder
     */
    public function addFlexibleContent($name, array $args = [])
    {
        return $this->initializeField(new FlexibleContentBuilder($name, 'flexible_content', $args));
    }

    /**
     * Add a tab field.
     *
     * @param  string  $label
     * @return \Log1x\AcfComposer\Builder\TabBuilder
     */
    public function addTab($label, array $args = [])
    {
        return $this->initializeField(new TabBuilder($label, 'tab', $args));
    }

    /**
     * Add an accordion field.
     *
     * @param  string  $label
     * @return \Log1x\AcfComposer\Builder\AccordionBuilder
     */
    public function addAccordion($label, array $args = [])
    {
        return $this->initializeField(new AccordionBuilder($label, 'accordion', $args));
    }

    /**
     * Add a choice field.
     *
     * @param  string  $name
     * @param  string  $type
     * @return \Log1x\AcfComposer\Builder\ChoiceFieldBuilder
     */
    public function addChoiceField($name, $type, array $args = [])
    {
        return $this->initializeField(new ChoiceFieldBuilder($name, $type, $args));
    }

    /**
     * Check for custom field types before calling the requested method.
     *
     * @param  string  $method
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if ($context = $this->getParentContext()) {
            if ($type = $context->getFieldType($method)) {
                $name = array_shift($arguments);

                return $this->addField($name, $type, ...$arguments);
            }
        }

        if ($type = $this->getFieldType($method)) {
            $name = array_shift($arguments);

            return $this->addField($name, $type, ...$arguments);
        }

        return parent::__call($method, $arguments);
    }
}
