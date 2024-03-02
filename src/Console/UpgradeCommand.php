<?php

namespace Log1x\AcfComposer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Log1x\AcfComposer\AcfComposer;

class UpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acf:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simple upgrade helper to migrate from v2 to v3';

    /**
     * The ACF Composer instance.
     */
    protected AcfComposer $composer;

    /**
     * The replacement patterns.
     */
    protected array $replacements;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->composer = $this->laravel['AcfComposer'];

        $this->replacements = [
            'use StoutLogic\\AcfBuilder\\FieldsBuilder;' => 'use Log1x\\AcfComposer\\Builder;',
            'new FieldsBuilder(' => 'Builder::make(',
            'public function assets($block)' => 'public function assets(array $block): void',
            'public function enqueue($block)' => 'public function assets(array $block): void',
            'public function enqueue($block = [])' => 'public function assets(array $block): void',
            'public function enqueue()' => 'public function assets(array $block): void',
            '/->addFields\(\$this->get\((.*?)\)\)/' => fn ($match) => "->addPartial({$match[1]})",
        ];

        $this->components->info('Checking for outdated <fg=blue>ACF Composer</> classes...');

        $classes = collect($this->composer->paths())->flatMap(fn ($classes, $path) => collect($classes)
            ->map(fn ($class) => Str::of($class)->replace('\\', '/')->after('/')->start($path.'/')->finish('.php')->toString())
            ->all()
        )
            ->filter(fn ($class) => file_exists($class))
            ->mapWithKeys(fn ($class) => [$class => file_get_contents($class)])
            ->filter(fn ($class) => Str::contains($class, array_keys($this->replacements)));

        if ($classes->isEmpty()) {
            return $this->components->info('No outdated <fg=blue>ACF Composer</> classes found.');
        }

        $files = $classes
            ->keys()
            ->map(fn ($class) => basename($class, '.php'))
            ->map(fn ($class) => "<fg=blue>{$class}</>")
            ->all();

        $this->components->bulletList($files);

        if (! $this->components->confirm("Found <fg=blue>{$classes->count()}</> ACF Composer classes to upgrade. Do you wish to <fg=blue>continue</>?", true)) {
            return $this->components->error('The ACF Composer upgrade has been <fg=red>cancelled</>.');
        }

        $classes->each(function ($class, $path) {
            $name = basename($path, '.php');

            $this->components->task(
                "Upgrading the <fg=blue>{$name}</> class",
                fn () => $this->handleUpgrade($path, $class)
            );
        });

        $this->newLine();

        $this->components->info("Successfully upgraded <fg=blue>{$classes->count()}</> ACF Composer classes.");
    }

    /**
     * Upgrade the ACF Composer class file.
     */
    protected function handleUpgrade(string $path, string $class): bool
    {
        foreach ($this->replacements as $pattern => $replacement) {
            $class = is_callable($replacement) ?
                preg_replace_callback($pattern, $replacement, $class) :
                str_replace($pattern, $replacement, $class);
        }

        return file_put_contents($path, $class) !== false;
    }
}
