<?php

namespace Log1x\AcfComposer\Exceptions;

use Exception;

class InvalidFieldsException extends Exception
{
    /**
     * The exception message.
     *
     * @var string
     */
    protected $message = 'Fields must be an array or instance of Log1x\AcfComposer\Builder.';
}
