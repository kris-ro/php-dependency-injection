<?php

namespace KrisRo\PhpDependencyInjection;

use KrisRo\PhpConfig\Config;

class Container extends Config {

  protected static $services = [];

  /**
   * Service caller
   *
   * @param string $serviceId
   *
   * @return object|bool
   */
  public static function service(string $serviceId): object|bool {
    if (empty(self::$config)) {
      trigger_error('Config is not built yet. Run Container::buildConfig("/path/to/folder/with/json/config/files");', E_USER_ERROR);
      return FALSE;
    }

    if (empty(self::$config['services'])) {
      trigger_error('$config["services"] is not set. Please define your services in one of the json config files.', E_USER_ERROR);
      return FALSE;
    }

    if (empty(self::$config['services'][$serviceId])) {
      trigger_error("{$serviceId} is not set. Please define your service first.", E_USER_ERROR);
      return FALSE;
    }

    if (!isset(self::$services[$serviceId])) {
      return self::createObject($serviceId);
    }

    return self::$services[$serviceId];
  }

  /**
   * Prep the service for build
   *
   * @param type $serviceId
   *
   * @return object|bool
   */
  protected static function createObject($serviceId): object|bool {
    if (empty(self::$config['services'][$serviceId]['class'])) {
      trigger_error("Class for {$serviceId} is not set. Please define your service first.", E_USER_ERROR);
      return FALSE;
    }

    $class = preg_replace('/::.*/', '', self::$config['services'][$serviceId]['class']);

    $methods = self::getCallableMethods($serviceId);

    return static::$services[$serviceId] = self::executeMethods($class, $methods);
  }

  /**
   * Build the argument list
   *
   * @param string|array $argument
   *
   * @return *
   */
  protected static function buildArgument(string|array $argument) {
    if (!is_string($argument)) {
      return $argument;
    }

    switch (substr($argument, 0, 1)) {
      case '#':
        return self::get(substr($argument, 1));

      case '@':
        return static::service(substr($argument, 1));

      default:
        return $argument;
    }
  }

  /**
   * Run the methods
   *
   * @param string $class
   * @param array|null $methods
   *
   * @return object|bool
   */
  protected static function executeMethods(string $class, array|null $methods = []): object|bool {
    $constructorArguments = array_shift($methods);
    if (empty($constructorArguments)) {
      $object = new $class();
    } else {
      $object = new $class(...$constructorArguments);
    }

    foreach ($methods as $method => $arguments) {
      $object->$method(...$arguments);
    }

    return $object ?: FALSE;
  }

  /**
   * Build the methods list
   *
   * @param string $serviceId
   *
   * @return array
   */
  private static function getCallableMethods(string $serviceId): array {
    list($class, $method) = explode('::', self::$config['services'][$serviceId]['class']) + [NULL, NULL];

    $methods = ['_construct' => []];
    if ($method && $method != '_construct') {
      $methods[$method] = [];
    }

    foreach (self::$config['services'][$serviceId] as $method => $arguments) {
      if ($method == 'class') {
        continue;
      }

      if (!self::isCallable($class, $method)) {
        trigger_error("I can not resolve {$serviceId} to a callable.", E_USER_ERROR);
        return FALSE;
      }

      $processedArguments = [];
      foreach ($arguments as $argumentName => $argument) {
        $processedArguments[$argumentName] = self::buildArgument($argument, NULL);
      }


      $methods[$method] = $processedArguments;
    }

    return $methods;
  }

  /**
   * Validate methods
   *
   * @param string $class
   * @param string $method
   *
   * @return bool
   */
  private static function isCallable(string $class, string $method): bool {
    /**
     * constructors
     */
    if ($method == '_construct' || preg_match("/{$method}$/", $class)) {
      return TRUE;
    }

    if (is_callable($class, $method)) {
      return TRUE;
    }

    return FALSE;
  }
}