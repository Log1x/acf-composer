# ACF Composer

![Packagist Version](https://img.shields.io/packagist/v/log1x/acf-composer.svg?style=flat-square)
![CircleCI](https://img.shields.io/circleci/build/gh/Log1x/acf-composer.svg?style=flat-square)
![Packagist](https://img.shields.io/packagist/dt/log1x/acf-composer.svg?style=flat-square)

ACF Composer is the ultimate tool for creating fields, blocks, widgets, and option pages using [ACF Builder](https://github.com/stoutlogic/acf-builder) alongside [Sage 10](https://github.com/roots/sage).

## Features

- Encourages clean structuring for creating fields with Sage 10 and ACF.
- Instantly generate working fields, blocks, widgets, and option pages. Batteries included.
- Blocks and widgets are fully rendered using Blade with a native Sage 10 feel for passing view data.
- 🔥 Automatically hooks widgets with `WP_Widget` making them instantly ready to use.
- 🔥 Automatically sets field location on blocks, widgets, and option pages.
- 🔥 Globally set default field type and field group settings. No more repeating `['ui' => 1]` on every select field.

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

### Getting Started

Start by publishing the `config/acf.php` configuration file using Acorn:

```bash
$ wp acorn vendor:publish --provider="Log1x\AcfComposer\Providers\AcfComposerServiceProvider"
```

### Generating a Field

Generating fields with ACF Composer is done using Acorn.

To create your first field, start by running the following command from your theme directory:

```bash
$ wp acorn acf:field Example
```

This will create `src/Fields/Example.php` which is where you will create and manage your field group.

Taking a glance at the generated `Example.php` stub, you will notice that it has a simple list configured.

Proceed by checking the `Add Post` for the field to ensure things are working as intended – and then [get to work](https://github.com/Log1x/acf-builder-cheatsheet).

### Generating a Block

Generating a block is generally the same as generating a field as seen above.

Start by creating the block field using Acorn:

```bash
$ wp acorn acf:block Example
```

Optionally, you may pass `--full` to the command above to generate a stub that contains additional configuration examples.

```bash
$ wp acorn acf:block Example --full
```

When running the block generator, one difference to a generic field is an accompanied `View` is generated in the `resources/views/blocks` directory.

Like the field generator, the example block contains a simple list repeater and is working out of the box.

### Generating a Widget

Creating a sidebar widget using ACF Composer is extremely easy. Widgets are automatically loaded and rendered with Blade, as well as registered with `WP_Widget` which is usually rather annoying.

Start by creating a widget using Acorn:

```bash
$ wp acorn acf:widget Example
```

Similar to blocks, widgets are also accompanied by a view generated in `resources/views/widgets`.

Out of the box, the Example widget is ready to go and should appear in the backend.

### Generating an Options Page

Creating an options page is similar to creating a regular field group in additional to a few configuration options available to customize the page (most of which, are optional.)

Start by creating an option page using Acorn:

```bash
$ wp acorn acf:options Options
```

Optionally, you may pass `--full` to the command above to generate a stub that contains additional configuration examples.

```bash
$ wp acorn acf:options Options --full
```

Once finished, you should see an Options page appear in the backend.

All fields registered will have their location automatically set to this page.

## Default Field Settings

One of my personal favorite features of ACF Composer is the ability to set field type as well as field group defaults. Any globally set default can of course be over-ridden by simply setting it on the individual field.

### Global

Taking a look at `config/acf.php`, you will see a few pre-configured defaults:

```php
'defaults' => [
    'trueFalse' => ['ui' => 1],
    'select' => ['ui' => 1],
],
```

When setting `trueFalse` and `select` to have their `ui` set to `1` by default, it is no longer necessary to repeatedly set `'ui' => 1` on your fields. This takes effect globally and can be overridden by simply setting a different value on a field.

### Field Group

It is also possible to define defaults on individual field groups. This is done by simply defining `$defaults` in your field class.

```php
/**
 * Default field type settings.
 *
 * @return array
 */
protected $defaults = ['ui' => 0];
```

### My Defaults

Here are a couple defaults I personally use. Any prefixed with `acfe_` are related to [ACF Extended](https://www.acf-extended.com/).

```php
'defaults' => [
    'fieldGroup' => ['instruction_placement' => 'acfe_instructions_tooltip'],
    'repeater' => ['layout' => 'block', 'acfe_repeater_stylised_button' => 1],
    'postObject' => ['ui' => 1, 'return_format' => 'object'],
    'accordion' => ['multi_expand' => 1],
    'group' => ['layout' => 'table', 'acfe_group_modal' => 1],
    'tab' => ['placement' => 'left'],
],
```

## Bug Reports

If you discover a bug in ACF Composer, please [open an issue](https://github.com/log1x/acf-composer/issues).

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated.

## License

ACF Composer is provided under the [MIT License](https://github.com/log1x/acf-composer/blob/master/LICENSE.md).
