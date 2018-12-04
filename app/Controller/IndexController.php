<?php
/**
 * File IndexController.php
 * @henter
 * Time: 2018-11-25 00:29
 *
 */

namespace App\Controller;

use App\Action\Index\PageAction;

class IndexController extends Controller
{
    protected $actions = [
        'page' => PageAction::class,
    ];

    public function indexAction($x = '')
    {
        return 'hello zim '.$x;
    }

    public function testAction()
    {
        return 'hello index test';
    }

    public function test_methodAction()
    {
        return 'test method';
    }
}