<?php

namespace Log1x\AcfComposer\Console;

use Roots\Acorn\Console\Commands\GeneratorCommand;

class OptionsMakeCommand extends GeneratorCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'acf:options{name* : The name of the options page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new ACF options page.';

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
        return __DIR__ . '/stubs/options.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Fields';
    }
}
