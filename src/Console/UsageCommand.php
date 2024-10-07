<?php

namespace Log1x\AcfComposer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function Laravel\Prompts\search;
use function Laravel\Prompts\table;

class UsageCommand extends Command
{
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

        $this->types = collect(
            acf_get_field_types()
        )->sortBy(fn ($type) => $type->label);

        $field = $this->argument('field') ?: search(
            label: '<fg=gray>Search for a registered</> <fg=blue>field type</>',
            options: fn (string $value) => $this->search($value)->all(),
            hint: "<fg=gray>Found</> <fg=blue>{$this->types->count()}</> <fg=gray>registered field types.</>"
        );

        $type = $this->type($field);

        if (! $type) {
            return $this->components->error("The field type [<fg=red>{$field}</>] could not be found.");
        }

        $options = collect([
            ...$type->defaults,
            ...$type->supports,
        ])->except('escaping_html');

        table(['<fg=blue>Label</>', '<fg=blue>Name</>', '<fg=blue>Category</>', '<fg=blue>Options #</>'], [[
            $type->label,
            $type->name,
            Str::title($type->category),
            $options->count(),
        ]]);

        $method = Str::of($type->name)
            ->studly()
            ->start('add')
            ->toString();

        $native = method_exists('Log1x\AcfComposer\Builder', $method) ? $method : null;

        $options = collect($options)->map(fn ($value) => match (true) {
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

        $usage = collect(explode("\n", $usage))
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
        return $this->types->first(fn ($type) => Str::contains($type->label, $field, ignoreCase: true) || Str::contains($type->name, $field, ignoreCase: true));
    }
}
