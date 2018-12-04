<?php
/**
 * File Page.php
 * @henter
 * Time: 2018-11-25 01:48
 *
 */

namespace App\Action\Index;

use Zim\Config\ConfigInterface;

class PageAction extends \Zim\Routing\Action
{

    public function execute()
    {
        var_dump($this->app(ConfigInterface::class)->get('routes'));
        return 'page response test';
    }
}