<?php

namespace Zim\Routing;

use Closure;
use BadMethodCallException;
use Zim\Support\Arr;
use InvalidArgumentException;

/**
 * @method static Route get(string $uri, \Closure|array|string|null $info = null)
 * @method static Route post(string $uri, \Closure|array|string|null $info = null)
 * @method static Route put(string $uri, \Closure|array|string|null $info = null)
 * @method static Route delete(string $uri, \Closure|array|string|null $info = null)
 * @method static Route patch(string $uri, \Closure|array|string|null $info = null)
 * @method static Route options(string $uri, \Closure|array|string|null $info = null)
 * @method static Route any(string $uri, \Closure|array|string|null $info = null)
 * @method static Registrar name(string $value)
 * @method static Registrar where(array  $where)
 */
class Registrar
{
    /**
     * @var self
     */
    public static $instance;

    /**
     * The router instance.
     *
     * @var Router
     */
    protected $router;

    /**
     * The attributes to pass on to the router.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The methods to dynamically pass through to the router.
     *
     * @var array
     */
    protected $passthru = [
        'get', 'post', 'put', 'patch', 'delete', 'options', 'any'
    ];

    /**
     * The attributes that can be set through this class.
     *
     * @var array
     */
    protected $allowedAttributes = [
        'name', 'where',
    ];

    /**
     * Create a new route registrar instance.
     *
     * @param  Router  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Set the value for a given attribute.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function attribute($key, $value)
    {
        if (! in_array($key, $this->allowedAttributes)) {
            throw new InvalidArgumentException("Attribute [{$key}] does not exist.");
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Registrar a new route with the router.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  \Closure|array|string|null  $info
     * @return Route
     */
    protected function registrarRoute($method, $uri, $info = null)
    {
        return $this->router->addRoute($method, $uri, $info);
    }

    /**
     * @return RouteCollection|null
     */
    public static function getRoutes()
    {
        if (!static::$instance) {
            return null;
        }
        return static::$instance->router->getRoutes();
    }

    /**
     * Dynamically handle calls into the route registrar.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return Route|$this
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->passthru)) {
            return $this->registrarRoute($method, ...$parameters);
        }

        if (in_array($method, $this->allowedAttributes)) {
            return $this->attribute($method, $parameters[0]);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }

    /**
     * Dynamically handle calls into the route registrar.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return Route|$this
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if (!static::$instance) {
            $router = new Router(new RouteCollection());
            static::$instance = new static($router);
        }
        return call_user_func([static::$instance, $method], ...$parameters);
    }
}
