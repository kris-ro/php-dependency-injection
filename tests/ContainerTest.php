<?php

use PHPUnit\Framework\TestCase;
use KrisRo\PhpDependencyInjection\Container;

class ContainerTest extends TestCase {

  public function testgetModel() {
    Container::buildConfig(dirname(dirname(__FILE__)) . '/configs');

    $model = Container::service('model');
    $this->assertEquals(TRUE, $model instanceof \KrisRo\PhpDatabaseModel\Model);

    $model = Container::service('class_with_constructor_in_callable_string');
    $this->assertEquals(TRUE, $model instanceof \KrisRo\PhpDatabaseModel\Model);

    $database = Container::service('class_with_specified_method_and_implicit_constructor');
    $this->assertEquals(TRUE, $database instanceof \KrisRo\PhpDatabaseModel\Database);

    $validator = Container::service('model_with_specified_method_and_array_argument');
    $this->assertEquals(TRUE, $validator instanceof \KrisRo\Validator\Validator);

    $pdo = Container::service('pdo');
    $this->assertEquals(TRUE, $pdo instanceof \PDO);

    $model = Container::service('class_with_service_as_argument');
    $this->assertEquals(TRUE, $model instanceof \KrisRo\PhpDatabaseModel\Model);
    $this->assertEquals(TRUE, $model->getConnection() instanceof \PDO);

    $validator = Container::service('class_with_config_key_as_argument');
    $this->assertEquals(TRUE, $validator instanceof \KrisRo\Validator\Validator);
  }

}