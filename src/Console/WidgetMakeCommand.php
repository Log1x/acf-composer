<?php

namespace Log1x\AcfComposer\Console;

class WidgetMakeCommand extends MakeCommand
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
    protected $description = 'Create a new widget using ACF.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Widget';

    /**
     * The view stub used when generated.
     *
     * @var string|bool
     */
    protected $view = 'repeater';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/widget.stub';
    }
}
