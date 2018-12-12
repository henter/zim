<?php
/**
 * File Controller.php
 * @henter
 * Time: 2018-11-24 20:07
 *
 */

namespace Zim\Http;

use Zim\Zim;

class Controller
{
    protected static $method;

    protected $actions = [];

    /* inject container methods TODO */
    public function __get(string $name)
    {
        if (Zim::getInstance()->has($name)) {
            return Zim::getInstance()->make($name);
        }
        return null;
    }

    public function app(string $name)
    {
        return Zim::getInstance()->make($name);
    }

    public function bind(string $name, $value)
    {
        Zim::getInstance()->bind($name, $value);
    }

    /* TODO */



    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->app('request');
    }

    /**
     * @param $uri
     * @return mixed|null
     */
    public function getActionClass($uri)
    {
        return $this->actions[$uri] ?? null;
    }

    /**
     * @param $uri
     * @return null
     */
    public function getAction($uri)
    {
        if (method_exists($this, $uri.'Action')) {
            return $uri.'Action';
        }

        foreach (get_class_methods($this) as $method) {
            if (strtolower($method) == strtolower($uri).'action') {
                return $method;
            }
        }
        return null;
    }

    public function getActions()
    {
        return $this->actions;
    }

}