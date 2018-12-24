<?php
/**
 * File DefaultListener.php
 * @henter
 * Time: 2018-11-27 12:07
 *
 */

namespace App\Service;

use Zim\Event\DispatchEvent;
use Zim\Event\Event;
use Zim\Event\ExceptionEvent;
use Zim\Event\RequestEvent;
use Zim\Event\ResponseEvent;
use Zim\Http\JsonResponse;
use Zim\Http\Response;
use Zim\Service\Service;

class AppService extends Service
{
    public function boot()
    {
        $events = [
            RequestEvent::class,
            ResponseEvent::class,
            ExceptionEvent::class,
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
            if ($e == ExceptionEvent::class && $payload instanceof ExceptionEvent) {
                $resp = new Response('test catch exception: '.$payload->getThrowable()->getMessage());
                $payload->setResponse($resp);
            }
        });

        Event::on(function(ResponseEvent $e) {
            $resp = new JsonResponse([
                'code' => 0,
                'data' => 'test on event',
                'origin' => $e->getResponse() ? $e->getResponse()->getContent() : 'empty response',
                'callable' => $e->getRequest()->get('callable')
            ]);
            $e->setResponse($resp);
            //return 222;
        });

    }

    public function register()
    {
    }

}

