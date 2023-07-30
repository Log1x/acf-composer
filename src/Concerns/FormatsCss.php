<?php

namespace Log1x\AcfComposer\Concerns;

trait FormatsCss
{
    public function format($value, $side, $type = 'padding'): string
    {
        if (strpos($value, 'var:preset|') === 0) {
            $segments = explode('|', $value);
            array_shift($segments);

            return sprintf('%s-%s: var(--wp--preset--%s);', $type, $side, implode('--', $segments));
        }

        return sprintf('%s-%s: %s;', $type, $side, $value);
    }
}
