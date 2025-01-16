<?php

namespace Log1x\AcfComposer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Log1x\AcfComposer\Concerns\HasCollection;

use function Laravel\Prompts\search;
use function Laravel\Prompts\table;

class UsageCommand extends Command
{
    use HasCollection;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acf:usage {field? : The field type to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the usage of a registered field type';

    /**
     * The registered field types.
     */
    protected Collection $types;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! function_exists('acf_get_field_types')) {
            return $this->components->error('Advanced Custom Fields must be installed and activated to use this command.');
        }

        $this->types = $this->types();

        $field = $this->argument('field') ?: search(
            label: "<fg=gray>Search</> <fg=blue>{$this->types->count()}</> <fg=gray>registered field types</>",
            options: fn (string $value) => $this->search($value)->all(),
            hint: '<fg=blue>*</> <fg=gray>indicates a custom field type</>',
            scroll: 8,
        );

        $type = $this->type($field);

        if (! $type) {
            return $this->components->error("The field type [<fg=red>{$field}</>] could not be found.");
        }

        $options = $this->collect([
            ...$type->defaults ?? [],
            ...$type->supports ?? [],
        ])->except('escaping_html');

        table(['<fg=blue>Label</>', '<fg=blue>Name</>', '<fg=blue>Category</>', '<fg=blue>Options #</>', '<fg=blue>Source</>'], [[
            $type->label,
            $type->name,
            Str::title($type->category),
            $options->count(),
            $type->source,
        ]]);

        $method = Str::of($type->name)
            ->studly()
            ->start('add')
            ->toString();

        $native = method_exists('Log1x\AcfComposer\Builder', $method) ? $method : null;

        $options = $this->collect($options)->map(fn ($value) => match (true) {
            is_string($value) => "'{$value}'",
            is_array($value) => '[]',
            is_bool($value) => $value ? 'true' : 'false',
            default => $value,
        })->map(fn ($value, $key) => "\t '<fg=blue>{$key}</>' => <fg=blue>{$value}</>,");

        $usage = view('acf-composer::field-usage', [
            'name' => $type->name,
            'label' => "{$type->label} Example",
            'options' => $options->implode("\n"),
            'native' => $native,
        ]);

        $usage = $this->collect(explode("\n", $usage))
            ->map(fn ($line) => rtrim(" {$line}"))
            ->filter()
            ->implode("\n");

        $this->line($usage);
    }

    /**
     * Search for a field type.
     */
    protected function search(?string $search = null): Collection
    {
        if (filled($search)) {
            return $this->types
                ->filter(fn ($type) => Str::contains($type->label, $search, ignoreCase: true) || Str::contains($type->name, $search, ignoreCase: true))
                ->map(fn ($type) => $type->label)
                ->values();
        }

        return $this->types->map(fn ($type) => $type->label);
    }

    /**
     * Retrieve a field type by label or name.
     */
    protected function type(string $field): ?object
    {
        $exactMatch = $this->types->first(fn ($type) => strcasecmp($type->label, $field) === 0 || strcasecmp($type->name, $field) === 0);

        if ($exactMatch) {
            return $exactMatch;
        }

        $matches = $this->types->filter(fn ($type) => strcasecmp($type->label, $field) === 0 || strcasecmp($type->name, $field) === 0
            || Str::contains($type->label, $field, ignoreCase: true)
            || Str::contains($type->name, $field, ignoreCase: true));

        if ($matches->isEmpty()) {
            return null;
        }

        if ($matches->count() === 1) {
            return $matches->first();
        }

        $selected = search(
            label: "<fg=gray>Found</> <fg=blue>{$matches->count()}</> <fg=gray>registered field types Please choose one:</>",
            options: fn () => $matches->pluck('label', 'name')->all(),
            hint: '<fg=blue>*</> <fg=gray>indicates a custom field type</>',
            scroll: 8,
        );

        return $matches->firstWhere('name', $selected);
    }

    /**
     * Retrieve all registered field types.
     */
    protected function types(): Collection
    {
        return $this->collect(
            acf_get_field_types()
        )->map(function ($type) {
            if (Str::startsWith($type->doc_url, 'https://www.advancedcustomfields.com')) {
                $type->source = 'Official';

                return $type;
            }

            $type->label = "{$type->label} <fg=blue>*</>";
            $type->source = $this->source($type);

            return $type;
        })->sortBy('label');
    }

    /**
     * Attempt to retrieve the field type source.
     */
    protected function source(object $type): string
    {
        $paths = [WPMU_PLUGIN_DIR, WP_PLUGIN_DIR];

        $plugin = (new \ReflectionClass($type))->getFileName();

        if (! Str::contains($plugin, $paths)) {
            return 'Unknown';
        }

        $source = Str::of($plugin)->replace($paths, '')
            ->trim('/')
            ->explode('/')
            ->first();

        return Str::of($source)
            ->headline()
            ->replace('Acf', 'ACF');
    }
}
