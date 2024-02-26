<?php

namespace Log1x\AcfComposer;

use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionClass;
use StoutLogic\AcfBuilder\FieldsBuilder;

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
    protected function initializeField($field)
    {
        $field->setParentContext($this);

        $this->getFieldManager()->pushField($field);

        return $field;
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
        if (method_exists($this, $method) || ! $type = $this->getFieldType($method)) {
            return parent::__call($method, $arguments);
        }

        $name = array_shift($arguments);

        return $this->addField($name, $type, ...$arguments);
    }
}
