<?php

namespace Log1x\AcfComposer\Console;

use Exception;
use Illuminate\Support\Str;
use Roots\Acorn\Console\Commands\GeneratorCommand;

class MakeCommand extends GeneratorCommand
{
    /**
     * The view stub used when generated.
     *
     * @var string|bool
     */
    protected $view = false;

    /**
     * The generated class path.
     *
     * @var string
     */
    protected $path;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->path = $this->getPath(
            $this->qualifyClass($this->getNameInput())
        );

        $this->type = str_replace('/', ' ', $this->type);

        $this->task("Generating {$this->type} class", function () {
            if (
                (! $this->hasOption('force') ||
                ! $this->option('force')) &&
                $this->alreadyExists($this->getNameInput())
            ) {
                throw new Exception('File `' . $this->shortenPath($this->path) . '` already exists.');
            }

            return parent::handle();
        });

        $this->task("Generating {$this->type} view", function () {
            if ($this->view) {
                if (! $this->files->exists($this->getViewPath())) {
                    $this->files->makeDirectory($this->getViewPath());
                }

                if ($this->files->exists($this->getView())) {
                    return;
                }

                $this->files->put($this->getView(), $this->files->get($this->getViewStub()));
            }
        });

        return $this->summary();
    }

    /**
     * Return the full view destination.
     *
     * @return string
     */
    public function getView()
    {
        return Str::finish($this->getViewPath(), $this->getViewName());
    }

    /**
     * Return the view destination filename.
     *
     * @return string
     */
    public function getViewName()
    {
        return Str::finish(
            str_replace('.', '/', Str::slug(Str::snake($this->getNameInput()))),
            '.blade.php'
        );
    }

    /**
     * Return the view destination path.
     *
     * @return string
     */
    public function getViewPath()
    {
        return $this->getPaths() . '/' . Str::slug(Str::plural($this->type)) . '/';
    }

    /**
     * Get the view stub file for the generator.
     *
     * @return string
     */
    protected function getViewStub()
    {
        return __DIR__ . "/stubs/views/{$this->view}.stub";
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\' . Str::plural($this->type);
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
     * Return the block creation summary.
     *
     * @return void
     */
    protected function summary()
    {
        $this->line('');
        $this->line("🎉 Your {$this->type} <fg=blue;options=bold>{$this->getNameInput()}</> has been composed.");
        $this->line('');

        $this->line('<fg=blue;options=bold>Class</>');
        $this->line("    ⮑  <fg=blue>{$this->shortenPath($this->path)}</>");

        if ($this->view) {
            $this->line('');
            $this->line('<fg=blue;options=bold>View</>');
            $this->line("    ⮑  <fg=blue>{$this->shortenPath($this->getView(), 4)}</>");
        }
    }

    /**
     * Returns a shortened path.
     *
     * @param  string $path
     * @param  int $i
     * @return string
     */
    protected function shortenPath($path, $i = 3)
    {
        return collect(
            explode('/', $path)
        )->slice(-$i, $i)->implode('/');
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        //
    }
}
