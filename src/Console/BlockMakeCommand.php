<?php

namespace Log1x\AcfComposer\Console;

use Illuminate\Support\Str;

class BlockMakeCommand extends MakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'acf:block {name* : The name of the block}
                            {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new ACF block type.';

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
    protected $view = 'block';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/block.stub';
    }
}
