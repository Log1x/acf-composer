<?php

namespace Log1x\AcfComposer\Console;

class OptionsMakeCommand extends MakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'acf:options {name* : The name of the options page}
                            {--full : Scaffold an options page that contains the complete configuration.}
                            {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new ACF options page.';

    /**
     * The label used when referencing the command type.
     *
     * @var string
     */
    protected $label = 'Option Page';

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
}
