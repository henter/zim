<?php

namespace Zim\Container;

use Closure;
use ReflectionMethod;
use ReflectionFunction;
use InvalidArgumentException;

class BoundMethod
{
    /**
     * Call the given Closure / callable and inject its dependencies.
     *
     * @param  \Zim\Container\Container  $container
     * @param  callable|string  $callback
     * @param  array  $parameters
     * @return mixed
     */
    public static function call($container, $callback, array $parameters = [])
    {
        //callback is closure ?
        if (!is_array($callback)) {
            return call_user_func_array(
                $callback, static::getMethodDependencies($container, $callback, $parameters)
            );
        }

        //[class, method]
        return call_user_func_array(
            $callback, static::getMethodDependencies($container, $callback, $parameters)
        );
    }

    /**
     * Get all dependencies for a given method.
     *
     * @param  \Zim\Container\Container  $container
     * @param  callable|string  $callback
     * @param  array  $parameters
     * @return array
     */
    protected static function getMethodDependencies($container, $callback, array $parameters = [])
    {
        $dependencies = [];

        foreach (static::getCallReflector($callback)->getParameters() as $parameter) {
            static::addDependencyForCallParameter($container, $parameter, $parameters, $dependencies);
        }

        return array_merge($dependencies, $parameters);
    }

    /**
     * Get the proper reflection instance for the given callback.
     *
     * @param  callable|string $callback
     * @return \ReflectionFunctionAbstract
     *
     * @throws \ReflectionException
     */
    public static function getCallReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }

        return is_array($callback)
                        ? new ReflectionMethod($callback[0], $callback[1])
                        : new ReflectionFunction($callback);
    }

    /**
     * Get the dependency for the given call parameter.
     *
     * @param  \Zim\Container\Container  $container
     * @param  \ReflectionParameter  $parameter
     * @param  array  $parameters
     * @param  array  $dependencies
     * @return mixed
     */
    protected static function addDependencyForCallParameter($container, $parameter, array &$parameters, &$dependencies)
    {
        if (array_key_exists($parameter->name, $parameters)) {
            $dependencies[] = $parameters[$parameter->name];

            unset($parameters[$parameter->name]);
        } elseif ($parameter->getClass() && array_key_exists($parameter->getClass()->name, $parameters)) {
            $dependencies[] = $parameters[$parameter->getClass()->name];

            unset($parameters[$parameter->getClass()->name]);
        } elseif ($parameter->getClass()) {
            $dependencies[] = $container->make($parameter->getClass()->name);
        } elseif ($parameter->isDefaultValueAvailable()) {
            $dependencies[] = $parameter->getDefaultValue();
        }
    }
}
