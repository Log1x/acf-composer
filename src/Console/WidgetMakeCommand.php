<?php

namespace Log1x\AcfComposer\Console;

use Roots\Acorn\Console\Commands\GeneratorCommand;
use Illuminate\Support\Str;

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
    protected $description = 'Create a new widget using ACF.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Widget';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $view = Str::finish(str_replace('.', '/', Str::slug(head($this->argument('name')))), '.blade.php');
        $path = $this->getPaths() . '/widgets/';

        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path);
        }

        if ($this->files->exists($path . $view)) {
            return $this->error("File {$view} already exists!");
        }

        $this->files->put($path . $view, $this->files->get($this->getViewStub()));

        return $this->info("File {$view} created.");
    }

    /**
     * Return the applications view path.
     *
     * @param  string $name
     * @return void
     */
    protected function getPaths()
    {
        $paths = $this->app['view.finder']->getPaths();

        if (count($paths) === 1) {
            return head($paths);
        }

        return $this->choice('Where do you want to create the view(s)?', $paths, head($paths));
    }

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
     * Get the view stub file for the generator.
     *
     * @return string
     */
    protected function getViewStub()
    {
        return __DIR__ . '/stubs/views/widget.stub';
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
