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

$request = \Zim\Http\Request::createFromGlobals();
$response = $zim->handle($request);
$response->send();
$this->terminate($request, $response);


