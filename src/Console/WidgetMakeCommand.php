<?php

namespace Log1x\AcfComposer\Console;

use Illuminate\Support\Str;

use function Laravel\Prompts\text;

class WidgetMakeCommand extends MakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'acf:widget {name* : The name of the widget}
                                       {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new ACF sidebar widget.';

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
    protected $view = 'widget';

    /**
     * {@inheritdoc}
     */
    public function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $name = Str::of($name)
            ->afterLast('\\')
            ->kebab()
            ->headline()
            ->replace('-', ' ');

        $description = "A beautiful {$name} widget.";

        $description = text(
            label: 'Enter the widget description',
            placeholder: $description,
        ) ?: $description;

        $stub = str_replace('DummyDescription', $description, $stub);

        return $stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStub('widget');
    }
}
