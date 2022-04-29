<?php
/**
 * CanTestInaccessibleMethods.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Helpers;

trait CanTestInaccessibleMethods
{
    public function invokeInaccessibleMethod(object|string $object, string $methodName, ...$args): mixed
    {
        $reflection = new \ReflectionClass($object);
        $method     = $reflection->getMethod($methodName);

        return $method->invoke($object, ...$args);
    }

    public function getInaccessibleProperty(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass($object);

        return $reflection->getProperty($propertyName)->getValue($object);
    }

    public function setInaccessibleProperty(object $object, string $propertyName, mixed $propertyValue): void
    {
        $reflection = new \ReflectionClass($object);

        $reflection->getProperty($propertyName)->setValue($object, $propertyValue);
    }
}
