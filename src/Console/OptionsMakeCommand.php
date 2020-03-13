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
    protected $signature = 'acf:options{name* : The name of the options page}
                            {--full : Scaffold an options page that contains the complete configuration.}';

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
    protected $type = 'Options';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('full')) {
            return __DIR__ . '/stubs/options.full.stub';
        }

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
