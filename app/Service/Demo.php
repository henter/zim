<?php
/**
 * File DefaultListener.php
 * @henter
 * Time: 2018-11-27 12:07
 *
 */

namespace App\Service;

use Zim\Event\Event;
use Zim\Service\Service;

class Demo extends Service
{
    public function boot()
    {
    }

    public function register()
    {
        $events = [
            Event::ROUTE,
            Event::TERMINATE,
        ];
        $this->app->getEvent()->listen($events, function($e, $payload) {
            var_dump('event '.$e);
        });
    }
}