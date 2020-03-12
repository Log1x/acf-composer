# ACF Composer

![Packagist Version](https://img.shields.io/packagist/v/log1x/acf-composer.svg?style=flat-square)
![CircleCI](https://img.shields.io/circleci/build/gh/Log1x/acf-composer.svg?style=flat-square)
![Packagist](https://img.shields.io/packagist/dt/log1x/acf-composer.svg?style=flat-square)

ACF Composer assists you with ~~creating~~ **composing** Fields, Blocks, Widgets, and Options pages using [ACF Builder](https://github.com/stoutlogic/acf-builder) alongside [Sage 10](https://github.com/roots/sage).

## Requirements

- [Sage](https://github.com/roots/sage) >= 10.0
- [ACF](https://www.advancedcustomfields.com/) >= 5.8.0
- [PHP](https://secure.php.net/manual/en/install.php) >= 7.2
- [Composer](https://getcomposer.org/download/)

## Installation

Install via Composer:

```bash
$ composer require log1x/acf-composer
```

## Usage

### Basic Usage

Start by publishing the `config/acf.php` configuration file using Acorn:

```bash
$ wp acorn vendor:publish --provider="Log1x\AcfComposer\Providers\AcfComposerServiceProvider"
```

Looking at the config file, you will see documented keys for configuration. When creating a field group, simply add each class to their respective type.

### Generating a Field

Generating fields with ACF Composer is done using Acorn.

To create your first field, start by running the following command from your theme directory:

```bash
$ wp acorn acf:field Example
```

This will create `src/Fields/Example.php` which is where you will create and manage your field group.

Once finished, follow up by uncommenting `App\Fields\Example::class` in `acf.php`.

Taking a glance at the generated `Example.php` stub, you will notice that it has a simple list configured.

Proceed by checking the `Add Post` for the field to ensure things are working as intended– and then [get to work](https://github.com/Log1x/acf-builder-cheatsheet).

### Generating a Block

Generating a Block is generally the same as generating a field as seen above.

Start by creating the Block field using Acorn:

```bash
$ wp acorn acf:block Example
```

Optionally, you may pass `--full` to the command above to generate a stub that contains additional configuration examples.

```bash
$ wp acorn acf:block Example --full
```

Once finished, similarily to Fields, simply add the new block, `App\Blocks\Example::class` to `config/acf.php`.

When running the ACF Block generator, one difference to a generic field is an accompanied View is generated in the `resources/views/blocks` directory.

Like the Field generator, the example block contains a simple list repeater and is working out of the box.

### Generating a Widget

...

## Bug Reports

If you discover a bug in ACF Composer, please [open an issue](https://github.com/log1x/acf-composer/issues).

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated.

## License

ACF Composer is provided under the [MIT License](https://github.com/log1x/acf-composer/blob/master/LICENSE.md).
