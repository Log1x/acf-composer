<?php

namespace Log1x\AcfComposer\Concerns;

use Illuminate\Support\Str;

trait FormatsCss
{
    /**
     * Format the given value into a CSS style.
     *
     * @param  string  $value
     * @param  string  $side
     * @param  string  $type
     */
    public function formatCss($value, $side, $type = 'padding'): string
    {
        if (! Str::startsWith($value, 'var:preset|')) {
            return sprintf('%s-%s: %s;', $type, $side, $value);
        }

        $segments = explode('|', $value);

        array_shift($segments);

        return sprintf('%s-%s: var(--wp--preset--%s);', $type, $side, implode('--', $segments));
    }
}
