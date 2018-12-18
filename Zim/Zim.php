<?php
/**
 * File App.php
 * @henter
 * Time: 2018-11-24 19:29
 *
 */

namespace Zim;

use Zim\Container\Container;
use Zim\Event\Event;
use Zim\Service\LogService;
use Zim\Service\Service;
use Zim\Debug\ErrorHandler;
use Zim\Debug\ExceptionHandler;
use Zim\Event\Dispatcher;
use Zim\Routing\Router;
use Zim\Http\Request;
use Zim\Config\Config;
use Zim\Contract\Config as ConfigContract;
use Zim\Contract\Request as RequestContract;

/**
 * Class Zim
 * @package Zim
 */
class Zim extends Container
{
    const VERSION = 'Zim (1.0.0)';

    /**
     * All of the loaded configuration files.
     *
     * @var array
     */
    protected $loadedConfigs = [];

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The loaded services.
     *
     * @var array
     */
    protected $loadedServices = [];

    /**
     * The base path of the application installation.
     *
     * /           => basePath
     * /app        => APP_PATH
     * /config
     * /vendor
     *
     * @var string
     */
    protected $basePath;

    public function __construct(string $path = '')
    {
        $this->basePath = $path;

        $this->registerErrorHandling();
        $this->bootstrapContainer();
        $this->bootstrapConfig();
        $this->registerServices();
    }

    /**
     * Bootstrap the application container.
     *
     * @return void
     */
    protected function bootstrapContainer()
    {
        static::$instance = $this;
        $this->instance('zim', $this);
        $this->instance('config', new Config());
        $this->instance('event', new Dispatcher());
        $this->instance('router', new Router());
        $this->instance('env', $this->env());

        $this->aliases = [
            Zim::class             => 'zim',
            Container::class       => 'zim',
            Config::class          => 'config',
            ConfigContract::class  => 'config',
            Event::class           => 'event',
            Dispatcher::class      => 'event',
            RequestContract::class => 'request',
            Request::class         => 'request',
            Router::class          => 'router',
        ];
    }

    protected function bootstrapConfig()
    {
        $this->configure('app');
        $this->configure('routes');
    }

    protected function registerServices()
    {
        //base services
        $this->register(LogService::class);

        //services from config
        $services = self::config('app.services') ?: [];
        foreach ($services as $service) {
            $this->register($service);
        }
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Zim\Service\Service|string $service
     */
    public function register($service)
    {
        if (!$service instanceof Service) {
            $service = new $service($this);
        }

        if (array_key_exists($name = get_class($service), $this->loadedServices)) {
            return;
        }

        $this->loadedServices[$name] = $service;

        if (method_exists($service, 'register')) {
            $service->register();
        }

        if ($this->booted) {
            $this->bootService($service);
        }
    }

    /**
     * Boots the registered providers, for every incoming request
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        array_walk($this->loadedServices, function ($s) {
            $this->bootService($s);
        });

        $this->booted = true;
    }

    /**
     * Boot the given service provider.
     *
     * @param  \Zim\Service\Service $service
     * @return mixed
     */
    protected function bootService(Service $service)
    {
        if (method_exists($service, 'boot')) {
            return $this->call([$service, 'boot']);
        }
        return false;
    }

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    public function inConsole()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * Get the base path for the application.
     *
     * @param  string|null $path
     * @return string
     */
    public function basePath($path = null)
    {
        if ($this->basePath) {
            return $this->basePath . ($path ? '/' . $path : $path);
        }

        if ($this->inConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd() . '/../');
        }

        return $this->basePath($path);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param  string $name
     * @return void
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigs[$name])) {
            return;
        }

        $this->loadedConfigs[$name] = true;

        if ($path = $this->getConfigPath($name)) {
            $this->make('config')->set($name, require $path);
        }
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param  string|null $name
     * @return string
     */
    public function getConfigPath($name = null)
    {
        if (!$name) {
            $appConfigDir = $this->basePath('config') . '/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } else if (file_exists($path = $this->basePath('../config/'))) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config') . '/' . $name . '.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } else if (file_exists($path = $this->basePath('../config/' . $name . '.php'))) {
                return $path;
            }
        }
        return '';
    }

    public function env()
    {
        return self::config('zim.env');
    }

    /**
     * Set the error handling for the application.
     *
     * @return void
     */
    protected function registerErrorHandling()
    {
        error_reporting(E_ALL);

        //do not handle for console
        if (!$this->inConsole()) {
            ini_set('display_errors', 0);
            ExceptionHandler::register(true, "phpstorm://open?file=%f&line=%l");
            ErrorHandler::register();
        }
    }


    /**
     * helpers
     */

    /**
     * @return Dispatcher
     */
    private function getEvent()
    {
        return $this->make('event');
    }

    /**
     * @param       $event
     * @param array $payload
     * @param bool  $halt
     * @return mixed
     */
    public function fire($event, $payload = [], $halt = false)
    {
        return $this->getEvent()->fire($event, $payload, $halt);
    }

    /**
     * @param null $make
     * @return mixed
     */
    public static function app($make = null)
    {
        if (is_null($make)) {
            return Zim::getInstance();
        }

        return Zim::getInstance()->make($make);
    }

    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null $key
     * @param  mixed             $default
     * @return mixed
     */
    public static function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::app('config');
        }

        if (is_array($key)) {
            return self::app('config')->set($key);
        }

        return self::app('config')->get($key, $default);
    }
}