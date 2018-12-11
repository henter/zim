<?php
/**
 * File RouteRequest.php
 * @henter
 * Time: 2018-11-26 16:07
 *
 */

namespace Zim\Traits;
use Zim\Config\Config;
use Zim\Container\Container;
use Zim\Event\Dispatcher;
use Zim\Zim;

/**
 * Trait InjectContainer
 *
 * @property Zim $zim
 * @property Container $container
 * @property Dispatcher $event
 * @property Config $config
 *
 * @package Zim\Traits
 */
trait InjectContainer
{
    use AppHelper;

    public function __get(string $name)
    {
        return Zim::getInstance()->make($name);
    }

    public function app(string $name)
    {
        return Zim::getInstance()->make($name);
    }

    public function bind(string $name, $value)
    {
        Zim::getInstance()->bind($name, $value);
    }
}
