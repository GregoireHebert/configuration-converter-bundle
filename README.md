# Configuration Converter

[![CircleCI](https://circleci.com/gh/GregoireHebert/configuration-converter-bundle.svg?style=shield)](https://circleci.com/gh/GregoireHebert/configuration-converter-bundle)
[![Coverage Status](https://coveralls.io/repos/github/GregoireHebert/configuration-converter-bundle/badge.svg)](https://coveralls.io/github/GregoireHebert/configuration-converter-bundle)

Do not worry about you configuration at first.
When you start working on your project, the documentation (and because it's easy to make a POC with) show you how to configure your resources with annotations.
And it's usually a good practice in some cases to use annotations.
But when your project is growing, you start to realize that you need to change for a more suitable, maintainable format like XML or YAML.
It's time consuming, not painless, and not error free.

I've written this bundle for this occasion.

Note: Always double check you new configuration for edge cases that might not be covered. And please report it here so every case can fill in the gap.

### Installation

```shell
$ composer require --dev gheb/configuration-converter-bundle
```

#### Configuration


```yaml
# Default configuration.
configuration_converter:
    api_platform_default_export_path: '%kernel.project_dir%/config/packages/api-platform/' #(default)

```

#### Usage

The most classic use case is when you started to follow the documentation originally written with annotation.
By default we recommend the 'XML' configuration format.

To convert every single one of your entities configuration in xml.

```shell
$ php bin/console configuration:convert
```

To convert a specific entity configuration in xml.

```shell
$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity'
```

To use another format, use `--format|-f` option.

```shell
$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' -f 'xml'
$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' -f 'yml'
```

By default, you'll need to copy and paste the configuration from the CLI output, but you can export the format to a specific directory.
Use `--output|-o` option. By default it will export to `config/packages/api-platform` directory.

```shell
$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' -o
$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' -o 'custom/repository'
```

#### Contributing

Fork the project, create a branch according to your contribution, code and follow the contributing.md

### Here is the way I see the next versions, any help is welcome :)

- [*] Add YAML support
- [ ] Add serialization groups conversion
- [ ] Add assertion conversion
- [ ] Add doctrine conversion
