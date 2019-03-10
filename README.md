# API Platform Configuration Converter

# Installation

```shell
$ composer require --dev gheb/api-platform-config-converter-bundle
```

# Configuration

```yaml
api_platform_configuration_converter:
    default_export_path: '%kernel.project_dir%/config/packages/api-platform/' #(default)

```

# Usage

The most classic use case is when you started to follow the documentation originally written with annotation.
By default we recommend the most secure configuration format 'XML'.

```shell
$ php bin/console api:configuration:convert 'FQCN\Of\Your\Entity'
```

If you want to use another format, use --format|-f option.
```shell
$ php bin/console api:configuration:convert 'FQCN\Of\Your\Entity' -f 'xml'
```

By default, you'll get the configuration through the stdout, but you can export the format to a specific directory.
Use --output|-o option. By default it will export to `config/packages/api-platform` directory
```shell
$ php bin/console api:configuration:convert 'FQCN\Of\Your\Entity' -o
$ php bin/console api:configuration:convert 'FQCN\Of\Your\Entity' -o 'custom/repository'
```

# Contributing

```shell
$ vendor/bin/php-cs-fixer fix
$ vendor/bin/phpstan -l7 analyze src tests
```

## Run the tests

```shell
$ vendor/bin/phpunit
```

with coverage

```shell
$ vendor/bin/phpunit --coverage-html dist
```
