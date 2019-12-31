# ACF Composer

![Packagist Version](https://img.shields.io/packagist/v/log1x/acf-composer.svg?style=flat-square)
![CircleCI](https://img.shields.io/circleci/build/gh/Log1x/acf-composer.svg?style=flat-square)
![Packagist](https://img.shields.io/packagist/dt/log1x/acf-composer.svg?style=flat-square)

ACF Composer assists you with ~~creating~~ **composing** Fields and Blocks using [ACF Builder](https://github.com/stoutlogic/acf-builder) alongside [Sage 10](https://github.com/roots/sage).

## Requirements

- [Sage](https://github.com/roots/sage) >= 10.0
- [ACF](https://www.advancedcustomfields.com/) >= 5.8.0

## Installation

```bash
$ composer require log1x/acf-composer
```

## Usage

### Publish Config

```bash
$ wp acorn vendor:publish --provider="Log1x\AcfComposer\Providers\AcfComposerServiceProvider"
```

Initialize fields, blocks, and default field type values in `config/acf.php`.

### Create a Block or Field

```bash
$ wp acorn acf:block MyBlock
$ wp acorn acf:field MyField
```

## Bug Reports

If you discover a bug in ACF Composer, please [open an issue](https://github.com/log1x/acf-composer/issues).

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated.

## License

ACF Composer is provided under the [MIT License](https://github.com/log1x/acf-composer/blob/master/LICENSE.md).
