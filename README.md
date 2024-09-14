# PHP Dependency Injection Container

Dependency injection container class for php. Extends `kris-ro/php-config` functionality and relies on it for configuration.

## Installation

Use composer to install *PHP Dependency Injection Container*.

```bash
composer require kris-ro/php-dependency-injection
```

## Configuration
The `Container` reads the definitoin for the requested service from the config array built by `Container::buildConfig()`.\
All services must be defined in the `services` branch of the configuration array.
Each service is identified by its service idendifier (`service_identifier` in the example below).\
First key in the definition array must be the `class` containing the class name prefixed by the namespace and optionally sufixed with the method name. Valid examples:
```json
{
  "services" : {
    "service_identifier" : {
      "class" : "\\Class\\With\\Namespace"
    },
    "another_service_identifier" : {
      "class" : "\\Class\\With\\Namespace::MethodName"
    }
  }
}
```
The `class` can be followed by other entries in the definition array representing the methods of the created service that will be executed (in the same order as they are listed in the definition) by the container before the service is delivered. Valid examples:
```json
{
  "services" : {
    "myPDO" : {
      "class": "\\PDO",
      "_construct": {
        "dsn": "mysql:host=localhost;dbname=test",
        "username": "k",
        "password": "123456"
      }
    },
    "validator" : {
      "class": "\\KrisRo\\Validator\\Validator",
      "createRegexRules" : {
        "rules": {
          "alphanumeric": "/^[a-z0-9\\-_]+$/i"
        }
      }
    }
  }
}
```
As you can see above, for `myPDO` the `_construct` is specified because it needs those three arguments.\
The second definition `validator` also specifies a method to be executed with one argument `rules`.

The *names of the arguments* in the definition are taken from the method.\
If you look at the `PDO` definition above you'll see that `_constructor` has three arguments `dsn`, `username` and `password` that are maped to `PDO`'s constructor arguments `$pdo`, `$username` and `$password` respectively.

There are three *types of argument values*:
- a **service identifier** prefixed with the character `@`
- an **entry path** from the config array prefixed with the character `#`
- anything else is passed as is

**Service as argument**\
Value of `credentialsOrPDO` references `myPDO` service.
```json
{
  "services" : {
    "myPDO" : {
      "class": "\\PDO",
      "_construct": {
        "dsn": "mysql:host=localhost;dbname=test",
        "username": "k",
        "password": "123456"
      }
    },
    "model" : {
      "class": "\\KrisRo\\PhpDatabaseModel\\Model",
      "_construct": {
        "credentialsOrPDO": "@myPDO"
      }
    }
  }
}
```
**Entry path from config array**\
Value of `rules` references `validator > rules` entry in configuration array.
```json
{
  "validator" : {
    "rules": {
      "alphanumeric": "/^[a-z0-9\\-_]+$/i"
    }
  },
  "services" : {
    "validator": {
      "class": "\\KrisRo\\Validator\\Validator",
      "_construct": {
        "rules": "#validator/rules"
      }
    }
  }
}
```

## Usage
Once the service is defined it is as simple as:
```php
use KrisRo\PhpDependencyInjection\Container;

# first build the configuration array. See more at https://github.com/kris-ro/php-config
Container::buildConfig('/absolute/path/to/your/folder/with/json/files');

# load the service you need
$pdo = Container::service('myPDO');
$model = Container::service('model');
$validator = Container::service('validator');
```
