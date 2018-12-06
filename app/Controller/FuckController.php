<?php
/**
 * File IndexController.php
 * @henter
 * Time: 2018-11-25 00:29
 *
 */

namespace App\Controller;

use Zim\Contract\Config;

class FuckController extends Controller
{

    public function indexAction($x = '')
    {
        return 'fuck '.$x;
    }

    public function postAction($page = 2, $x = 'xxx', Config $config)
    {
        return 'fuck page '.$page.' '.$x ;
    }

    public function testAction()
    {
        return 'fuck test';
    }

    public function test_methodAction($page = 1, $x = 2, Config $config)
    {
        return 'fuck test method';
    }
}