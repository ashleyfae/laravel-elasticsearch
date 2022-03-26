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
    public function invokeProtectedMethod(object|string $object, string $methodName, ...$args): mixed
    {
        $reflection = new \ReflectionClass($object);
        $method     = $reflection->getMethod($methodName);

        return $method->invoke($object, $args);
    }

    public function getProtectedProperty(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass($object);

        return $reflection->getProperty($propertyName)->getValue($object);
    }
}
