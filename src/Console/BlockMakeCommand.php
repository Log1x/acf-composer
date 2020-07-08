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
                            {--full : Scaffold a block that contains the complete configuration.}
                            {--force : Overwrite any existing files}';

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
        if (
            ! empty($version = get_option('acf_version')) &&
            Str::before($version, '-') >= '5.9.0'
        ) {
            $this->view = 'repeater.innerblocks';
        }

        if ($this->option('full')) {
            return __DIR__ . '/stubs/block.full.stub';
        }

        if (Str::before($version, '-') >= '5.9.0') {
            return __DIR__ . '/stubs/block.innerblocks.stub';
        }

        return __DIR__ . '/stubs/block.stub';
    }
}
