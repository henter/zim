<?php
/**
 * File index.php
 * @henter
 * Time: 2018-11-24 23:26
 *
 */

/**
 * @var \Zim\Zim $zim
 */
$zim = require __DIR__.'/../app/bootstrap.php';

$http = $zim->make(\Zim\Http\Kernel::class);
$request = \Zim\Http\Request::createFromGlobals();
$response = $http->handle($request);
$response->send();
$http->terminate($request, $response);


