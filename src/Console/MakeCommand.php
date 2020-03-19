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
     * The category (type) of block.
     *
     * @var string|bool
     */
    protected $category = false;

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
        $this->category = strtolower($this->category ?: $this->type);

        $this->logo();

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
        $this->line("\n🎉 Your {$this->category} <fg=blue;options=bold>{$this->getNameInput()}</> has been composed.\n");
        $this->line("<fg=blue;options=bold>Class</>\n    ⮑  <fg=blue>{$this->shortenPath($this->path)}</>");

        if ($this->view) {
            $this->line("\n<fg=blue;options=bold>View</>\n    ⮑  <fg=blue>{$this->shortenPath($this->getView(), 4)}</>");
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
     * Return the ACF Composer logo.
     *
     * @return void
     */
    protected function logo()
    {
        $this->line("<fg=blue;options=bold>
             __
  __ _  ___ / _| ___ ___  _ __ ___  _ __   ___  ___  ___ _ __
 / _` |/ __| |_ / __/ _ \| '_ ` _ \| '_ \ / _ \/ __|/ _ \ '__|
| (_| | (__|  _| (_| (_) | | | | | | |_) | (_) \__ \  __/ |
 \__,_|\___|_|  \___\___/|_| |_| |_| .__/ \___/|___/\___|_|
                                   |_|
        </>\n");
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
