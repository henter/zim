<?php
/**
 * File Event.php
 * @henter
 * Time: 2018-11-24 19:56
 *
 */

namespace Zim\Event;

use Zim\Zim;

class Event
{
    //uncaught exception
    const EXCEPTION = 'zim.exception';

    //before route match
    const ROUTE = 'zim.route';

    /**
     * @param $event
     * @param array $payload
     * @param bool $halt
     */
    public static function fire($event, $payload = [], $halt = false)
    {
        Zim::getInstance()->make('event')->fire($event, $payload, $halt);
    }

    /**
     * @param $event
     * @param array $payload
     */
    public static function listen($event, $payload = [])
    {
        Zim::getInstance()->make('event')->listen($event, $payload);
    }

    /**
     * @param callable $callback
     */
    public static function on(callable $callback)
    {
        Zim::getInstance()->make('event')->on($callback);
    }
}