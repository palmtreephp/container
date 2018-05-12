# Palmtree Container

[![License](http://img.shields.io/packagist/l/palmtree/container.svg)](LICENSE)
[![Travis](https://img.shields.io/travis/palmtreephp/container.svg)](https://travis-ci.org/palmtreephp/container)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/palmtreephp/container.svg)](https://scrutinizer-ci.com/g/palmtreephp/container/)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/palmtreephp/container.svg)](https://scrutinizer-ci.com/g/palmtreephp/container/)

A [PSR-11](http://www.php-fig.org/psr/psr-11/) compatible service container for Palmtree PHP.

Supports environment variable parameters.

## Requirements
* PHP >= 5.6

## Installation

Use composer to add the package to your dependencies:
```bash
composer require palmtree/container
```

## Usage

Define parameters and services:
```yaml
# config.yml
parameters:
  database_name: 'mydb'
  database_user: 'mydb_user'
  database_password: '%env(DB_PASSWORD)%'
  env(DB_PASSWORD): 123456 # Default env parameter used if environment variable is not set

imports:
  - { resource: services.yml }
  - { resource: secrets.yml }
```

```yaml
# services.yml
services:
  my_service:
    class: MyNamespace\MyService
    arguments: [arg1, '%database_name%']

  my_other_service:
    class: MyNamespace\MyOtherService
    arguments: ['@my_service']
    calls:
      -
        method: doThing
        arguments: [arg1, arg2]
      
```

```yaml
# secrets.yml
parameters:
    secret: 'TopsyCrett'
```

Create container:
```php
<?php
use Palmtree\Container\ContainerFactory;

$container = ContainerFactory::create('config.yml');

$container->get('my_service')->myMethod();

$container->getParameter('db_username');
```

## Prior Art
Inspired by Symfony's [DependencyInjection](https://symfony.com/doc/current/components/dependency_injection.html) component.

## License

Released under the [MIT license](LICENSE)
