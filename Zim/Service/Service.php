<?php

namespace Zim\Service;

use Zim\App;

abstract class Service
{
    /**
     * @var App
     */
    protected $app;

    /**
     * Create a new service provider instance.
     *
     * @param  App $app
     * @return void
     */
    public function __construct(App $app)
    {
        $this->app = $app;
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
