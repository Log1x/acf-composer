<?php

namespace Log1x\AcfComposer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class IdeHelpersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acf:ide-helpers
                            {--path= : The path to generate the IDE helpers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate IDE helpers for configured custom field types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $types = config('acf.types', []);

        if (! $types) {
            return $this->components->info('You <fg=blue>do not</> have any configured custom field types.');
        }

        $path = $this->option('path') ?
            base_path($this->option('path')) :
            __DIR__.'/../../_ide-helpers.php';

        $methods = [];

        foreach ($types as $key => $value) {
            $key = Str::studly($key);

            $methods[] = "* @method \Log1x\AcfComposer\Builder\FieldBuilder add{$key}(string \$name, array \$args = [])";
        }

        $methods = implode("\n\t ", $methods);

        $builder = <<<EOT
        namespace Log1x\AcfComposer {
            /**
             {$methods}
             */
            class Builder
            {
            }
        }
        EOT;

        $fieldBuilder = <<<EOT
        namespace Log1x\AcfComposer\Builder {
            /**
             {$methods}
             */
            class FieldBuilder
            {
            }

            /**
             {$methods}
             */
            class GroupBuilder
            {
            }

            /**
             {$methods}
             */
            class RepeaterBuilder
            {
            }

            /**
             {$methods}
             */
            class AccordionBuilder
            {
            }

            /**
             {$methods}
             */
            class TabBuilder
            {
            }

            /**
             {$methods}
             */
            class ChoiceFieldBuilder
            {
            }
        }
        EOT;

        File::put($path, "<?php\n\n{$builder}\n\n{$fieldBuilder}");

        $this->components->info('The <fg=blue>IDE helpers</> have been successfully generated.');
    }
}
