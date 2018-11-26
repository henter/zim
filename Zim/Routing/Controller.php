<?php
/**
 * File Controller.php
 * @henter
 * Time: 2018-11-24 20:07
 *
 */

namespace Zim\Routing;

class Controller
{
    protected $actions = [];

    public function getAction($uri)
    {
        return $this->actions[$uri] ?? null;
    }

    public function getActions()
    {
        return $this->actions;
    }

}