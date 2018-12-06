<?php
/**
 * File DefaultListener.php
 * @henter
 * Time: 2018-11-27 12:07
 *
 */

namespace App\Service;

use Zim\Event\Event;
use Zim\Event\RequestEvent;
use Zim\Event\ResponseEvent;
use Zim\Http\JsonResponse;
use Zim\Http\Response;
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
            RequestEvent::class,
            ResponseEvent::class,
        ];

        Event::listen($events, function($e, $payload) {
            //var_dump('event '.$e, $payload);
            if ($e == RequestEvent::class && $payload instanceof RequestEvent) {
                /**
                 * @var Response $resp
                 */
                $resp = new Response('test request event return response');
                //$payload->setResponse($resp);
            }

            if ($e == ResponseEvent::class && $payload instanceof ResponseEvent) {
                $resp = new Response('test response event');
                //$payload->setResponse($resp);
            }
        });

        Event::on(function(RequestEvent $e) {
            $resp = new JsonResponse('test on event');
            //$e->setResponse($resp);
            //return 222;
        });


    }

}

