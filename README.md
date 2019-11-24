# Configuration Converter

[![CircleCI](https://circleci.com/gh/GregoireHebert/configuration-converter-bundle.svg?style=shield)](https://circleci.com/gh/GregoireHebert/configuration-converter-bundle)
[![Coverage Status](https://coveralls.io/repos/github/GregoireHebert/configuration-converter-bundle/badge.svg)](https://coveralls.io/github/GregoireHebert/configuration-converter-bundle)

Do not worry about your configuration at first.

When you start working on your project, the documentation (because it's easy to make a POC with) shows you how to configure your resources with annotations.
And it's usually a good practice to use annotations.

But when your project is growing, you start to realize that you need to change for a more suitable, maintainable format like XML or YAML.
It's time consuming, not painless, and not error free.

This bundle is meant for this occasion.

*WARNING:* As the API-Platform and Serializer component evolves, there might be uncovered options. Always double check the output for missing pieces, and if you find ones, please help us filling the gaps.

### Installation

```shell
$ composer require --dev gheb/configuration-converter-bundle
```

Register the bundle.

For symfony < 3.4

```
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
    ];

    if (in_array($this->getEnvironment(), ['dev'])) {
        //...
        $bundles[] = new ConfigurationConverter\ConfigurationConverterBundle();
    }

    return $bundles;
}
```

For symfony > 4

```
<?php
// config/bundles.php

<?php

return [
    // ...
    ConfigurationConverter\ConfigurationConverterBundle::class => ['dev' => true],
];
```

#### Configuration

Configure the bundle, here are the default values:

```yaml
# config/packages/configuration_converter.yaml
configuration_converter:
    api_platform_default_export_dir: '%kernel.project_dir%/config/packages/api-platform/'
    serializer_group:
        default_export_dir: '%kernel.project_dir%/config/packages/serialization/'
        entities_dir: ['%kernel.project_dir%/src/Entity/']

```

#### Usage

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
For API-Platform, use `--api-platform-output|-apo` option. By default it will export to `config/packages/api-platform` directory.
For the serialization groups, use `--serializer-groups-output|-sgo` option. By default it will export to `config/packages/serialization` directory.

```shell
$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' --api-platform-output
$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' --api-platform-output 'custom/directory'

$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' --serializer-groups-output
$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' --serializer-groups-output 'custom/directory'

$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' --serializer-groups-output --api-platform-output
$ php bin/console configuration:convert -r 'FQCN\Of\Your\Entity' --api-platform-output 'custom/directory' --serializer-groups-output 'custom/directory'
```

By default, the bundle will try to convert the API-Platform *and* the attributes groups.
If you only want to convert one or the other, use the `--configurations|-c` option.

```shell
$ php bin/console configuration:convert --configurations=api_platform
$ php bin/console configuration:convert --configurations=serializer_groups
$ php bin/console configuration:convert --configurations=api_platform --configurations=serializer_groups # default
```

#### Contributing

Fork the project, create a branch according to your contribution, code and follow the [contributing.md](CONTRIBUTING.md).

### Here is the way I see the next versions, any help is welcome :)

- [x] Add YAML support
- [x] Add serialization groups conversion
- [ ] Add assertion conversion
- [ ] Add doctrine conversion
