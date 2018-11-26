<?php
/**
 * File Controller.php
 * @henter
 * Time: 2018-11-24 20:07
 *
 */

namespace Zim\Routing;

class Action extends Controller
{
    protected $method = 'GET';

    public function execute()
    {
        return 'default action response';
    }

}