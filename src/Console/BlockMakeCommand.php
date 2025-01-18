<?php

namespace Log1x\AcfComposer\Console;

use Illuminate\Support\Str;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class BlockMakeCommand extends MakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'acf:block {name* : The name of the block}
                            {--localize : Localize the block name and description}
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
     * The block supports array.
     */
    protected array $supports = [
        'align',
        'align_text',
        'align_content',
        'full_height',
        'anchor',
        'mode',
        'multiple',
        'jsx',
        'color' => ['background', 'text', 'gradients'],
        'spacing' => ['padding', 'margin'],
    ];

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

        $description = "A beautiful {$name} block.";

        $description = text(
            label: 'Enter the block description',
            placeholder: $description,
        ) ?: $description;

        $categories = get_default_block_categories();

        $category = select(
            label: 'Select the block category',
            options: $this->collect($categories)->mapWithKeys(fn ($category) => [$category['slug'] => $category['title']]),
            default: 'common',
        );

        $postTypes = multiselect(
            label: 'Select the supported post types',
            options: $this->collect(
                get_post_types(['public' => true])
            )->mapWithKeys(fn ($postType) => [$postType => Str::headline($postType)])->all(),
            hint: 'Leave empty to support all post types.',
        );

        $postTypes = $this->collect($postTypes)
            ->map(fn ($postType) => sprintf("'%s'", $postType))
            ->join(', ');

        $supports = multiselect(
            label: 'Select the supported block features',
            options: $this->getSupports(),
            default: config('acf.generators.supports', []),
            scroll: 8,
        );

        $stub = str_replace(
            ['DummySupports', 'DummyDescription', 'DummyCategory', 'DummyPostTypes'],
            [$this->buildSupports($supports), $description, $category, $postTypes],
            $stub
        );

        return $stub;
    }

    /**
     * Build the block supports array.
     */
    protected function buildSupports(array $selected): string
    {
        return $this->collect($this->supports)->map(function ($value, $key) use ($selected) {
            if (is_int($key)) {
                return sprintf("'%s' => %s,", $value, in_array($value, $selected) ? 'true' : 'false');
            }

            $options = $this->collect($value)
                ->map(fn ($option) => sprintf(
                    "%s'%s' => %s,",
                    Str::repeat(' ', 12),
                    $option,
                    in_array($option, $selected) ? 'true' : 'false'
                ))
                ->join("\n");

            return sprintf("'%s' => [\n%s\n        ],", $key, $options);
        })->join("\n        ");
    }

    /**
     * Retrieve the support options.
     */
    protected function getSupports(): array
    {
        return $this->collect($this->supports)
            ->mapWithKeys(fn ($value, $key) => is_array($value)
                ? $this->collect($value)->mapWithKeys(fn ($option) => [$option => Str::of($option)->finish(" {$key}")->headline()->toString()])->all()
                : [$value => Str::headline($value)]
            )->all();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('localize')) {
            return $this->resolveStub('block.localized');
        }

        return $this->resolveStub('block');
    }
}
