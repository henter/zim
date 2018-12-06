<?php
/**
 * File IndexController.php
 * @henter
 * Time: 2018-11-25 00:29
 *
 */

namespace App\Controller;

use App\Action\Index\PageAction;
use Zim\Container\Container;
use Zim\Contract\Config;

class IndexController extends Controller
{
    protected $actions = [
        'page' => PageAction::class,
    ];

    public function __construct(Config $config)
    {
        //var_dump('test inject config ', $config);
    }

    public function indexAction($x = '')
    {
        return 'hello zim '.$x;
    }

    public function postAction($page = 2, $x = 432141, Config $config)
    {
        return 'test page '.$page.' '.$x ;
    }

    public function testAction()
    {
        return 'hello index test';
    }

    public function test_methodAction($page, $x, Config $config)
    {
        return 'test method';
    }
}