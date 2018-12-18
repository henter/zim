<?php
namespace Tests;

/**
 * File BaseTestCase.php
 * @henter
 * Time: 2018-03-02 11:12
 */

use PHPUnit\Framework\TestCase;
use Zim\Event\Dispatcher;
use Zim\Event\Event;
use Zim\Zim;

class BaseTestCase extends TestCase
{
    /**
     * @var Zim
     */
    public $zim;

    public function __construct()
    {
        parent::__construct();
        $this->zim = Zim::getInstance();
    }

    public function getConfig(string $name)
    {
        return require dirname(APP_PATH).'/config/'.$name.'.php';
    }

    /**
     * @return Dispatcher
     */
    public function getEvent()
    {
        return $this->zim->make('event');
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
