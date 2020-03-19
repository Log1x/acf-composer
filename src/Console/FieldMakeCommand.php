<?php

namespace Log1x\AcfComposer\Console;

class FieldMakeCommand extends MakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'acf:field {name* : The name of the field group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new ACF field group.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Field';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/field.stub';
    }
}
