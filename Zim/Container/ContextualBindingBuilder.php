<?php

namespace Zim\Container;


class ContextualBindingBuilder
{
    /**
     * The underlying container instance.
     *
     * @var \Zim\Container\Container
     */
    protected $container;

    /**
     * The concrete instance.
     *
     * @var string
     */
    protected $concrete;

    /**
     * The abstract target.
     *
     * @var string
     */
    protected $needs;

    /**
     * Create a new contextual binding builder.
     *
     * @param  \Zim\Container\Container  $container
     * @param  string|array  $concrete
     * @return void
     */
    public function __construct(Container $container, $concrete)
    {
        $this->concrete = $concrete;
        $this->container = $container;
    }

    /**
     * Define the abstract target that depends on the context.
     *
     * @param  string  $abstract
     * @return $this
     */
    public function needs($abstract)
    {
        $this->needs = $abstract;

        return $this;
    }

    /**
     * Define the implementation for the contextual binding.
     *
     * @param  \Closure|string  $implementation
     * @return void
     */
    public function give($implementation)
    {
        $concretes = is_array($this->concrete) ? $this->concrete: [$this->concrete];
        foreach ($concretes as $concrete) {
            $this->container->addContextualBinding($concrete, $this->needs, $implementation);
        }
    }
}
