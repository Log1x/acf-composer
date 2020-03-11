<?php

namespace Log1x\AcfComposer\Console;

use Roots\Acorn\Console\Commands\GeneratorCommand;

class WidgetMakeCommand extends GeneratorCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'acf:widget {name* : The name of the widget}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new widget powered by ACF.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Widget';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/widget.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Widgets';
    }
}
