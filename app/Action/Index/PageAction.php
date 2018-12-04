<?php
/**
 * File Page.php
 * @henter
 * Time: 2018-11-25 01:48
 *
 */

namespace App\Action\Index;

use App\Controller\Action;
use Zim\Contract\Config;

class PageAction extends Action
{

    public function execute()
    {
        var_dump($this->app(Config::class)->get('routes'));
        return 'page response test';
    }
}