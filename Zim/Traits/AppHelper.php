<?php
/**
 * File RouteRequest.php
 * @henter
 * Time: 2018-11-26 16:07
 *
 */

namespace Zim\Traits;

use Zim\Container\Container;
use Zim\Event\Dispatcher;

trait AppHelper
{
    /**
     * @param $event
     * @param array $payload
     * @param bool $halt
     * @return mixed
     */
    public static function dispatch($event, $payload = [], $halt = false)
    {
        return self::app(Dispatcher::class)->dispatch($event, $payload, $halt);
    }

    /**
     * @param null $make
     * @return mixed|static
     */
    public static function app($make = null)
    {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make);
    }

    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::app('config');
        }

        if (is_array($key)) {
            return self::app('config')->set($key);
        }

        return self::app('config')->get($key, $default);
    }
}
