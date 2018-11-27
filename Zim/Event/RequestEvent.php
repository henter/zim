<?php
/**
 * File RequestEvent.php
 * @henter
 * Time: 2018-11-25 23:15
 *
 */

namespace Zim\Event;


use Zim\Http\Request;

class RequestEvent
{

    public $request;
    public $routeInfo;

    public function __construct(Request $request = null, $routeInfo = [])
    {
        $this->request = $request;
        $this->routeInfo = $routeInfo;
    }

}