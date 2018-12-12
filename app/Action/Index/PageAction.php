<?php
/**
 * File Page.php
 * @henter
 * Time: 2018-11-25 01:48
 *
 */

namespace App\Action\Index;

use App\Action\Action;

class PageAction extends Action
{
    protected static $method = 'GET';

    public function execute()
    {
        return 'page response test';
    }
}