# Palmtree Service Container

[![License](http://img.shields.io/packagist/l/palmtree/service-container.svg)](LICENSE)
[![Travis](https://img.shields.io/travis/palmtreephp/service-container.svg)](https://travis-ci.org/palmtreephp/service-container)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/palmtreephp/service-container.svg)](https://scrutinizer-ci.com/g/palmtreephp/service-container/)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/palmtreephp/service-container.svg)](https://scrutinizer-ci.com/g/palmtreephp/service-container/)

A service container for Palmtree PHP.

Supports environment variable parameters.

## Requirements
* PHP >= 5.6

## Installation

Use composer to add the package to your dependencies:
```bash
composer require palmtree/service-container
```

## Usage

Define parameters and services:
```yaml
# config.yml
parameters:
  database_name: 'mydb'
  database_user: 'mydb_user'
  database_password: '%env(DB_PASSWORD)%'

imports:
  - { resource: services.yml }
  - { resource: secrets.yml }
```

```yaml
# services.yml
services:
  my_service:
    class: MyNamespace\MyService
    arguments: ['arg1', '%database_name%']

  my_other_service:
    class: MyNamespace\MyOtherService
    arguments: ['@my_service']
    calls:
      -
        method: doThing
        arguments: ['arg1', 'arg2']
      
```

```yaml
# secrets.yml
parameters:
    secret: 'TopsyCrett'
```

Create container:
```php
<?php
use Palmtree\ServiceContainer\ContainerFactory;

$container = ContainerFactory::create('config.yml');

$container->get('my_service')->myMethod();

$container->getParameter('db_username');
```

## License

Released under the [MIT license](LICENSE)
