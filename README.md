# API Platform Configuration Converter

[![CircleCI](https://circleci.com/gh/GregoireHebert/api-platform-config-converter-bundle.svg?style=shield)](https://circleci.com/gh/GregoireHebert/api-platform-config-converter-bundle)
[![Coverage Status](https://coveralls.io/repos/github/GregoireHebert/api-platform-config-converter-bundle/badge.svg)](https://coveralls.io/github/GregoireHebert/api-platform-config-converter-bundle)

Do not worry about you configuration at first.
When you start working with API Platform, the documentation (and because it's easy to make a POC with) show you how to configure your resources with annotations.
But usually, when your project is growing, you realize that you need to change for a more suitable format like XML or YAML.
It's time consuming, not painless, and not error free.

I've made this bundle for this occasion.

Note: Always double check you new configuration for edge cases that might not be covered. And please report it here so every case can fill in the gap.

### Todo

- [ ] Loop through every already available ApiResource
- [ ] Add YAML support
- [ ] Add Annotation support

### Installation

```shell
$ composer require --dev gheb/api-platform-config-converter-bundle
```

#### Configuration

```yaml
# Default configuration.
api_platform_configuration_converter:
    default_export_path: '%kernel.project_dir%/config/packages/api-platform/' #(default)

```

#### Usage

The most classic use case is when you started to follow the documentation originally written with annotation.
By default we recommend the most secure configuration format 'XML'.

```shell
$ php bin/console api:configuration:convert 'FQCN\Of\Your\Resource'
```

If you want to use another format, use `--format|-f` option.
```shell
$ php bin/console api:configuration:convert 'FQCN\Of\Your\Entity' -f 'xml'
```

By default, you'll need to copy and paste the configuration from the CLI output, but you can export the format to a specific directory.
Use `--output|-o` option. By default it will export to `config/packages/api-platform` directory.

```shell
$ php bin/console api:configuration:convert 'FQCN\Of\Your\Entity' -o
$ php bin/console api:configuration:convert 'FQCN\Of\Your\Entity' -o 'custom/repository'
```

#### Contributing

Fork the project, c

```shell
$ vendor/bin/php-cs-fixer fix
$ vendor/bin/phpstan -l7 analyze src tests
```

##### Run the tests

```shell
$ vendor/bin/phpunit
```

with coverage

```shell
$ phpdbg -qrr vendor/bin/phpunit --coverage-html dist
```
