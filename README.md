# :palm_tree: Palmtree Container

[![License](http://img.shields.io/packagist/l/palmtree/container.svg)](LICENSE)
[![Travis](https://img.shields.io/travis/palmtreephp/container.svg)](https://travis-ci.org/palmtreephp/container)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/palmtreephp/container.svg)](https://scrutinizer-ci.com/g/palmtreephp/container/)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/palmtreephp/container.svg)](https://scrutinizer-ci.com/g/palmtreephp/container/)

A [PSR-11](http://www.php-fig.org/psr/psr-11/) compatible service container

Supports environment variable parameters, factories and private services.

## Requirements
* PHP >= 7.1

For PHP >= 5.6 support use [v1.0](https://github.com/palmtreephp/container/tree/v1.0.0)

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

## Advanced Usage

### Factories

Services can be created by calling static factory methods. Arguments are passed
to the factory method.
```yaml
# services.yml
services:
  my_service:
    factory: 'MyNamespace\MyFactory:createService'
    arguments: [argForCreateService]
```

### Private Services

Services can be defined as private, meaning they can only be used via dependency
injection and cannot be retrieved from the container directly:

```yaml
# services.yml
services:
  my_service:
    class: MyNamespace\MyService
    public: false
    
  my_consumer:
    class: MyNamespace\MyConsumer
    arguments:
        - "@my_service"
```

The following will throw a ServiceNotPublicException:
```php
<?php
$container->get('my_service');
```

Whilst the following will work:

```php
<?php
namespace MyNamespace;

class MyConsumer {
    public function __construct(MyService $myService) {
    }
}
```

## Prior Art
Inspired by Symfony's [DependencyInjection](https://symfony.com/doc/current/components/dependency_injection.html) component.

## License

Released under the [MIT license](LICENSE)
