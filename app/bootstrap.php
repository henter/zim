<?php
/**
 * File bootstrap.php
 * @henter
 * Time: 2018-11-24 19:12
 *
 */

require_once __DIR__.'/../vendor/autoload.php';

define('APP_PATH', __DIR__);

return new Zim\App();
