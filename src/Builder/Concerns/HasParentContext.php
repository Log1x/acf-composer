<?php

namespace Log1x\AcfComposer\Builder\Concerns;

trait HasParentContext
{
    /**
     * Handle dynamic method calls into the fields builder.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (
            preg_match('/^add.+/', $method) &&
            (method_exists($this->fieldsBuilder, $method) || $this->fieldsBuilder->getFieldType($method))
        ) {
            $field = $this->handleCall($method, $args);
            $field->setParentContext($this);

            return $field;
        }

        return parent::__call($method, $args);
    }

    /**
     * Hanlde the Fields Builder method call.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    protected function handleCall($method, $args)
    {
        return call_user_func_array([$this->fieldsBuilder, $method], $args);
    }
}
