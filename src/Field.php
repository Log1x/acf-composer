<?php

namespace Log1x\AcfComposer;

use Exception;
use StoutLogic\AcfBuilder\FieldsBuilder;

abstract class Field extends Composer
{
    /**
     * Register the field groups with Advanced Custom Fields.
     */
    protected function register(?callable $callback = null): void
    {
        if (empty($this->fields)) {
            return;
        }

        if ($callback) {
            $callback();
        }

        foreach ($this->fields as $fields) {
            acf_add_local_field_group(
                $this->build($fields)
            );
        }
    }

    /**
     * Retrieve the field group fields.
     */
    public function getFields(bool $cache = true): array
    {
        if ($this->fields && $cache) {
            return $this->fields;
        }

        if ($cache && $this->composer->manifest()->has($this)) {
            return $this->composer->manifest()->get($this);
        }

        $fields = $this->fields();

        if (empty($fields)) {
            return [];
        }

        $fields = is_a($fields, FieldsBuilder::class)
            ? [$fields]
            : $fields;

        if (! is_array($fields)) {
            throw new Exception('Fields must be an array or an instance of Builder.');
        }

        $fields = ! empty($fields['key']) ? [$fields] : $fields;

        foreach ($fields as $key => $field) {
            $fields[$key] = is_a($field, FieldsBuilder::class)
                ? $field->build()
                : $field;
        }

        if ($this->defaults->has('field_group')) {
            foreach ($fields as $key => $field) {
                $fields[$key] = array_merge($this->defaults->get('field_group'), $field);
            }
        }

        return $fields;
    }

    /**
     * Compose and register the defined field groups with ACF.
     *
     * @return mixed
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
