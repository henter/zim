<?php

namespace Zim\Service;

use Zim\Zim;

abstract class Service
{
    /**
     * @var Zim
     */
    protected $zim;

    /**
     * Create a new service provider instance.
     *
     * @param  Zim $zim
     * @return void
     */
    public function __construct(Zim $zim)
    {
        $this->zim = $zim;
    }

    //execute for every request
    abstract public function boot();

    //execute once
    abstract public function register();

    /**
     * TODO, deferred register
     *
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * TODO, deferred register
     *
     * Get the events that trigger this service provider to register.
     *
     * @return array
     */
    public function when()
    {
        return [];
    }
}
