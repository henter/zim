<?php

namespace Zim\Service;

abstract class Service
{
    /**
     * Create a new service provider instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    //your logic code here, invoked before handling request
    public function boot()
    {
    }

    //only for register service, binding object to container, or other bootstrap code
    public function register()
    {
    }

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
