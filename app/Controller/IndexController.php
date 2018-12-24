<?php
/**
 * File IndexController.php
 * @henter
 * Time: 2018-11-25 00:29
 *
 */

namespace App\Controller;

use App\Action\Index\PageAction;
use Zim\Contract\Config;
use Zim\Http\JsonResponse;

class IndexController extends Controller
{
    protected $actions = [
        'page' => PageAction::class,
    ];

    public function __construct(Config $config)
    {
        //var_dump('test inject config ', $config);
    }

    public function indexAction($x = 'xx')
    {
        return \Zim\Zim::config('app.name').' '.$x;
    }

    public function postAction($page = 2, $x = 'xxx', Config $config)
    {
        return 'test page '.$page.' '.$x ;
    }

    public function testAction(Config $config)
    {
        return new JsonResponse(['test' => 'ok', 'config' => $config->all()]);
    }

    public function test_methodAction(Config $config)
    {
        return 'test method';
    }

    public function xxxAction()
    {
        return new JsonResponse(['xx' => 'xxxxxxxxx']);
    }

    public function helloAction($world)
    {
        return 'hello '.$world;
    }

    private function privateMethodTestAction()
    {
        return  __FUNCTION__;
    }
}