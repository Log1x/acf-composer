<?php

namespace Log1x\AcfComposer\Console;

class BlockMakeCommand extends MakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'acf:block {name* : The name of the block}
                            {--full : Scaffold a block that contains the complete configuration.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new block using ACF.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Block';

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
        if ($this->option('full')) {
            return __DIR__ . '/stubs/block.full.stub';
        }

        return __DIR__ . '/stubs/block.stub';
    }
}
