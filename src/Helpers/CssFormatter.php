<?php

namespace Log1x\AcfComposer\Helpers;

class CssFormatter
{
    public static function formatCss($value, $side, $type = 'padding'): string
    {
        if (strpos($value, 'var:preset|') === 0) {
            $segments = explode('|', $value);
            array_shift($segments);

            return sprintf('%s-%s: var(--wp--preset--%s);', $type, $side, implode('--', $segments));
        }

        return sprintf('%s-%s: %s;', $type, $side, $value);
    }
}
