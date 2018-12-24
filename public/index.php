<?php
/**
 * File index.php
 * @henter
 * Time: 2018-11-24 23:26
 *
 */

require_once __DIR__.'/../vendor/autoload.php';

define('APP_PATH', __DIR__);

//simple start
\Zim\Zim::run();


////full start
//$zim = Zim\Zim::getInstance();
//$zim->singleton(\Zim\Http\Kernel::class);
//
//$http = $zim->make(\Zim\Http\Kernel::class);
//$request = \Zim\Http\Request::createFromGlobals();
//$response = $http->handle($request);
//$response->send();
//$http->terminate($request, $response);


