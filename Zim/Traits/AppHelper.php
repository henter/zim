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
     * @return Dispatcher
     */
    public function getEvent()
    {
        return $this->make('event');
    }

    /**
     * TODO, with static dispatch function
     *
     * @param $event
     * @param array $payload
     * @param bool $halt
     * @return mixed
     */
    public function fire($event, $payload = [], $halt = false)
    {
        return $this->getEvent()->fire($event, $payload, $halt);
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  string|array  $events
     * @param  mixed  $listener
     * @return void
     */
    public function listen($events, $listener)
    {
        $this->getEvent()->listen($events, $listener);
    }

    /**
     * @param null $make
     * @return mixed
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
