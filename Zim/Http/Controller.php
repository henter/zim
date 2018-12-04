<?php
/**
 * File Controller.php
 * @henter
 * Time: 2018-11-24 20:07
 *
 */

namespace Zim\Http;

use Zim\App;
use Zim\Contract\Http\Controller as ControllerContract;

class Controller implements ControllerContract
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