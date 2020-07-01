<?php

namespace Log1x\AcfComposer\Console;

class PartialMakeCommand extends MakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'acf:partial {name* : The name of the partial field group}
                                        {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new ACF field group partial.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Fields\\Partials';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/partial.stub';
    }
}
