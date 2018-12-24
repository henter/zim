<?php

namespace Zim\Container;

use Closure;
use InvalidArgumentException;

trait MagicInjection
{
    /**
     * call callable
     *
     * @param       $callback
     * @param array $parameters
     * @return mixed
     */
    public function call($callback, array $parameters = [])
    {
        return call_user_func_array(
            $callback, $this->getDependencies($callback, $parameters)
        );
    }

    /**
     * @param string $class
     * @param array  $params
     * @return object
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function buildObject(string $class, array $params = [])
    {
        $deps = $this->getDependencies($class, $params);
        return (new \ReflectionClass($class))->newInstanceArgs($deps);
    }

    /**
     * @param       $call
     * @param array $params
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getDependencies($call, array $params = [])
    {
        $deps = [];

        foreach ($this->reflectionParams($call) as $rp) {
            if (array_key_exists($rp->name, $params)) {
                $deps[] = $params[$rp->name];
                unset($params[$rp->name]);
            } else if ($rp->getClass() && array_key_exists($rp->getClass()->name, $params)) {
                $deps[] = $params[$rp->getClass()->name];
                unset($params[$rp->getClass()->name]);
            } else if ($rp->getClass()) {
                $deps[] = $this->make($rp->getClass()->name);
            } else if ($rp->isDefaultValueAvailable()) {
                $deps[] = $rp->getDefaultValue();
            }
        }

        return array_merge($deps, $params);
    }

    /**
     * @param $call
     * @return array|\ReflectionParameter[]
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function reflectionParams($call)
    {
        if ($call instanceof Closure) {
            return (new \ReflectionFunction($call))->getParameters() ?: [];
        } else if (is_string($call)) {
            $r = new \ReflectionClass($call);
            if (!$r->isInstantiable()) {
                return $this->notInstantiable($call);
            }
            if ($constructor = $r->getConstructor()) {
                return $constructor->getParameters();
            }
            return [];
        } else if (is_array($call)) {
            return (new \ReflectionMethod($call[0], $call[1]))->getParameters() ?: [];
        } else {
            throw new InvalidArgumentException('unsupported call');
        }
    }

}
