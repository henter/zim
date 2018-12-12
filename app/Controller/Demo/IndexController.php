<?php
/**
 * File IndexController.php
 * @henter
 * Time: 2018-11-25 00:29
 *
 */

namespace App\Controller\Demo;

use App\Controller\Controller;
use Zim\Contract\Config;
use Zim\Http\JsonResponse;

class IndexController extends Controller
{
    public function __construct(Config $config)
    {
        //var_dump('test inject config ', $config);
    }

    public function indexAction($x = 'xx')
    {
        return 'demo index';
    }

    public function testAction()
    {
        return new JsonResponse(['demo' => 'demo test']);
    }
}