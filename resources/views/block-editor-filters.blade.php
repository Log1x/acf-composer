@verbatim
  wp.hooks.addFilter(
    'blocks.getBlockDefaultClassName',
    'acf-composer/block-slug-classname',
    (className, blockName) => {
        if (! blockName.startsWith('acf/')) {
            return className;
        }

        const list = (className || '').split(/\\s+/);

        const classes = list.reduce((acc, current) => {
            acc.push(current);

            if (current.startsWith('wp-block-acf-')) {
                acc.push(
                    current.replace('wp-block-acf-', 'wp-block-')
                );
            }

            return acc;
        }, []);

        return classes.join(' ');
    }
  );
@endverbatim
