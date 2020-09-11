# ACF Composer

![Packagist Version](https://img.shields.io/packagist/v/log1x/acf-composer.svg?style=flat-square)
![CircleCI](https://img.shields.io/circleci/build/gh/Log1x/acf-composer.svg?style=flat-square)
![Packagist](https://img.shields.io/packagist/dt/log1x/acf-composer.svg?style=flat-square)

ACF Composer is the ultimate tool for creating fields, blocks, widgets, and option pages using [ACF Builder](https://github.com/stoutlogic/acf-builder) alongside [Sage 10](https://github.com/roots/sage).

![Screenshot](https://i.imgur.com/cFXqi35.png)

## Features

- 🔥 Encourages clean structuring for creating fields with Sage 10 and ACF.
- 🔥 Instantly generate working fields, blocks, widgets, and option pages. Batteries included.
- 🔥 Instantly generate re-usable field group partials.
- 🔥 Blocks and widgets are fully rendered using Blade with a native Sage 10 feel for passing view data.
- 🔥 Blocks are automatically generated with `<InnerBlocks />` support if [ACF v5.9.0+](https://www.advancedcustomfields.com/blog/acf-5-9-exciting-new-features/#InnerBlocks) is installed.
- 🔥 Automatically hooks widgets with `WP_Widget` making them instantly ready to use.
- 🔥 Automatically sets field location on blocks, widgets, and option pages.
- 🔥 Globally set default field type and field group settings. No more repeating `['ui' => 1]` on every select field.

## Requirements

- [Sage](https://github.com/roots/sage) >= 10.0
- [ACF](https://www.advancedcustomfields.com/) >= 5.8.0

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

To create your first field, start by running the following generator command from your theme directory:

```bash
$ wp acorn acf:field Example
```

This will create `src/Fields/Example.php` which is where you will create and manage your first field group.

Taking a glance at the generated `Example.php` stub, you will notice that it has a simple list configured.

```php
<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Example extends Field
{
    /**
     * The field group.
     *
     * @return array
     */
    public function fields()
    {
        $example = new FieldsBuilder('example');

        $example
            ->setLocation('post_type', '==', 'post');

        $example
            ->addRepeater('items')
                ->addText('item')
            ->endRepeater();

        return $example->build();
    }
}
```

Proceed by checking the `Add Post` for the field to ensure things are working as intended – and then [get to work](https://github.com/Log1x/acf-builder-cheatsheet).

### Generating a Field Partial

A field partial consists of a field group that can be re-used and/or added to existing field groups.

To start, let's generate a partial called _ListItems_ that we can use in the _Example_ field we generated above.

```bash
$ wp acorn acf:partial ListItems
```

```php
<?php

namespace App\Fields\Partials;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;

class ListItems extends Partial
{
    /**
     * The partial field group.
     *
     * @return array
     */
    public function fields()
    {
        $listItems = new FieldsBuilder('listItems');

        $listItems
            ->addRepeater('items')
                ->addText('item')
            ->endRepeater();

        return $listItems;
    }
}
```

Looking at `ListItems.php`, you will see out of the box it consists of an identical list repeater as seen in your generated field.

A key difference to note compared to an ordinary field is the omitting of `->build()` instead returning the `FieldsBuilder` instance itself.

This can be utilized in our _Example_ field by passing the `::class` constant to `->addFields()`.

```php
<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;
use App\Fields\Partials\ListItems;

class Example extends Field
{
    /**
     * The field group.
     *
     * @return array
     */
    public function fields()
    {
        $example = new FieldsBuilder('example');

        $example
            ->setLocation('post_type', '==', 'post');

        $example
            ->addFields($this->get(ListItems::class));

        return $example->build();
    }
}
```

### Generating a Block

Generating a block is generally the same as generating a field as seen above.

Start by creating the block field using Acorn:

```bash
$ wp acorn acf:block Example
```

```php
<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Example extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Example';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'Lorem ipsum...';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'common';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'star-half';

    /**
     * Data to be passed to the block before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'items' => $this->items(),
        ];
    }

    /**
     * The block field group.
     *
     * @return array
     */
    public function fields()
    {
        $example = new FieldsBuilder('example');

        $example
            ->addRepeater('items')
                ->addText('item')
            ->endRepeater();

        return $example->build();
    }

    /**
     * Return the items field.
     *
     * @return array
     */
    public function items()
    {
        return get_field('items') ?: [];
    }
}
```

When running the block generator, one difference to a generic field is an accompanied `View` is generated in the `resources/views/blocks` directory.

```php
@if ($items)
  <ul>
    @foreach ($items as $item)
      <li>{{ $item['item'] }}</li>
    @endforeach
  </ul>
@else
  <p>No items found!</p>
@endif

<div>
  <InnerBlocks />
</div>
```

Like the field generator, the example block contains a simple list repeater and is working out of the box.

#### Block Preview View

While `$block->preview` is an option for conditionally modifying your block when shown in the editor, you may also render your block using a seperate view.

Simply duplicate your existing view prefixing it with `preview-` (e.g. `preview-example.blade.php`).

### Generating a Widget

Creating a sidebar widget using ACF Composer is extremely easy. Widgets are automatically loaded and rendered with Blade, as well as registered with `WP_Widget` which is usually rather annoying.

Start by creating a widget using Acorn:

```bash
$ wp acorn acf:widget Example
```

```php
<?php

namespace App\Widgets;

use Log1x\AcfComposer\Widget;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Example extends Widget
{
    /**
     * The widget name.
     *
     * @var string
     */
    public $name = 'Example';

    /**
     * The widget description.
     *
     * @var string
     */
    public $description = 'Lorem ipsum...';

    /**
     * Data to be passed to the widget before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'items' => $this->items(),
        ];
    }

    /**
     * The widget title.
     *
     * @return string
     */
    public function title() {
        return get_field('title', $this->widget->id);
    }

    /**
     * The widget field group.
     *
     * @return array
     */
    public function fields()
    {
        $example = new FieldsBuilder('example');

        $example
            ->addText('title');

        $example
            ->addRepeater('items')
                ->addText('item')
            ->endRepeater();

        return $example->build();
    }

    /**
     * Return the items field.
     *
     * @return array
     */
    public function items()
    {
        return get_field('items', $this->widget->id) ?: [];
    }
}
```

Similar to blocks, widgets are also accompanied by a view generated in `resources/views/widgets`.

```php
@if ($items)
  <ul>
    @foreach ($items as $item)
      <li>{{ $item['item'] }}</li>
    @endforeach
  </ul>
@else
  <p>No items found!</p>
@endif
```

Out of the box, the Example widget is ready to go and should appear in the backend.

### Generating an Options Page

Creating an options page is similar to creating a regular field group in additional to a few configuration options available to customize the page (most of which, are optional.)

Start by creating an option page using Acorn:

```bash
$ wp acorn acf:options Example
```

```php
<?php

namespace App\Options;

use Log1x\AcfComposer\Options as Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Example extends Field
{
    /**
     * The option page menu name.
     *
     * @var string
     */
    public $name = 'Example';

    /**
     * The option page document title.
     *
     * @var string
     */
    public $title = 'Example | Options';

    /**
     * The option page field group.
     *
     * @return array
     */
    public function fields()
    {
        $example = new FieldsBuilder('example');

        $example
            ->addRepeater('items')
                ->addText('item')
            ->endRepeater();

        return $example->build();
    }
}
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
    'trueFalse' => ['ui' => 1],
    'select' => ['ui' => 1],
    'postObject' => ['ui' => 1, 'return_format' => 'object'],
    'accordion' => ['multi_expand' => 1],
    'group' => ['layout' => 'table', 'acfe_group_modal' => 1],
    'tab' => ['placement' => 'left'],
    'sidebar_selector' => ['default_value' => 'sidebar-primary', 'allow_null' => 1]
],
```

## Bug Reports

If you discover a bug in ACF Composer, please [open an issue](https://github.com/log1x/acf-composer/issues).

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated.

## License

ACF Composer is provided under the [MIT License](https://github.com/log1x/acf-composer/blob/master/LICENSE.md).
