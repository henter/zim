<?php
/**
 * File IndexController.php
 * @henter
 * Time: 2018-11-25 00:29
 *
 */

namespace App\Controller;

use Zim\Routing\Controller;

class FooController extends Controller
{

    public function indexAction()
    {
        return 'hello foo';
    }

    public function testAction()
    {
        return 'hello foo test';
    }
}