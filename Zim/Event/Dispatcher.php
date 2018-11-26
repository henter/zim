<?php
/**
 * File Dispatcher.php
 * @henter
 * Time: 2018-11-24 19:56
 *
 */

namespace Zim\Event;

class Dispatcher
{
    /**
     * Fire an event and call the listeners.
     *
     * @param string|object $event
     * @param mixed $payload
     * @param bool $halt
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        return null;
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
        foreach ((array) $events as $event) {
            if (Str::contains($event, '*')) {
                $this->setupWildcardListen($event, $listener);
            } else {
                $this->listeners[$event][] = $this->makeListener($listener);
            }
        }
    }

}