<?php
/**
 * File Event.php
 * @henter
 * Time: 2018-11-24 19:56
 *
 */

namespace Zim\Event;

use Zim\App;

class Event
{
    //uncaught exception
    const EXCEPTION = 'zim.exception';

    //before route match
    const ROUTE = 'zim.route';

    //before request dispatch
    //TODO, replaced with RequestEvent
    const REQUEST = 'zim.request';

    //controller found
    const CONTROLLER = 'zim.controller';

    //action found
    const ACTION = 'zim.action';

    //response created
    const RESPONSE = 'zim.response';

    //response sent
    const TERMINATE = 'zim.terminate';

    /**
     * @param $event
     * @param array $payload
     * @param bool $halt
     */
    public static function fire($event, $payload = [], $halt = false)
    {
        App::getInstance()->make(Dispatcher::class)->fire($event, $payload, $halt);
    }

    /**
     * @param $event
     * @param array $payload
     */
    public static function listen($event, $payload = [])
    {
        App::getInstance()->make(Dispatcher::class)->listen($event, $payload);
    }

    /**
     * @param callable $callback
     */
    public static function on(callable $callback)
    {
        App::getInstance()->make(Dispatcher::class)->on($callback);
    }
}