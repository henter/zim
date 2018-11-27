<?php

namespace Zim\Service;

abstract class Service
{
    /**
     * @var \Zim\App
     */
    protected $app;

    /**
     * Create a new service provider instance.
     *
     * @param  \Zim\App $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    //execute for every request
    abstract public function boot();

    //execute once
    abstract public function register();

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Get the events that trigger this service provider to register.
     *
     * @return array
     */
    public function when()
    {
        return [];
    }
}
