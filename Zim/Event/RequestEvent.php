<?php
/**
 * File RequestEvent.php
 * @henter
 * Time: 2018-11-25 23:15
 *
 */

namespace Zim\Event;

use Zim\Contract\Http\Request;
use Zim\Contract\Http\Response;

class RequestEvent
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    public function __construct(Request $request, Response $response = null)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}