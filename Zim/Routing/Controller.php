<?php
/**
 * File Controller.php
 * @henter
 * Time: 2018-11-24 20:07
 *
 */

namespace Zim\Routing;

use Zim\App;
use Zim\Http\Request;

class Controller
{
    protected $actions = [];

    /**
     * @param $make
     * @return mixed
     */
    public function app($make)
    {
        return App::app($make);
    }

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
    public function getAction($uri)
    {
        return $this->actions[$uri] ?? null;
    }

    /**
     * @param $uri
     * @return null
     */
    public function getMethod($uri)
    {
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