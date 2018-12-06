<?php
/**
 * File App.php
 * @henter
 * Time: 2018-11-24 19:29
 *
 */
namespace Zim;

use Zim\Container\Container;
use Zim\Container\ContainerInterface;
use Zim\Service\LogService;
use Zim\Service\Service;
use Zim\Debug\ErrorHandler;
use Zim\Debug\ExceptionHandler;
use Zim\Event\Dispatcher;
use Zim\Routing\Router;
use Zim\Traits\AppHelper;
use Zim\Traits\RouteRequest;
use Zim\Config\Config;
use Zim\Contract\Config as ConfigContract;

/**
 * Class App
 * @package Zim
 */
class App extends Container
{
    const VERSION = 'Zim (1.0.0)';

    use AppHelper;
    use RouteRequest;

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
     * @var string
     */
    protected $basePath;

    /**
     * @var Router
     */
    protected $router;

    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->basePath = $basePath;
        }

        $this->bootstrapConfig();
        $this->bootstrapContainer();
        $this->registerErrorHandling();

        $this->router = new Router();

        $this->registerServices();
    }

    /**
     * Bootstrap the application container.
     *
     * @return void
     */
    protected function bootstrapContainer()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance('env', $this->env());
        $this->singleton('event',Dispatcher::class);

        $this->registerContainerAliases();
    }

    protected function bootstrapConfig()
    {
        $this->singleton('config', function () {
            return new Config();
        });
        $this->configure('app');
        $this->configure('routes');
    }

    protected function registerServices()
    {
        //base services
        $this->register(LogService::class);

        //services from config
        $services = self::config('app.services');
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
        if (! $service instanceof Service) {
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
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * Get the base path for the application.
     *
     * @param  string|null  $path
     * @return string
     */
    public function basePath($path = null)
    {
        if (isset($this->basePath)) {
            return $this->basePath.($path ? '/'.$path : $path);
        }

        if ($this->runningInConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd().'/../');
        }

        return $this->basePath($path);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param  string  $name
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
     * @param  string|null  $name
     * @return string
     */
    public function getConfigPath($name = null)
    {
        if (! $name) {
            $appConfigDir = $this->basePath('config').'/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config').'/'.$name.'.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
                return $path;
            }
        }
        return '';
    }

    /**
     * Register the core container aliases.
     *
     * @return void
     */
    protected function registerContainerAliases()
    {
        $this->aliases = [
            Container::class => 'app',
            ContainerInterface::class => 'app',
            App::class => 'app',
            ConfigContract::class => 'config',
        ];
    }

    public function env()
    {
        return self::config('app.env');
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
        if (!$this->runningInConsole()) {
            ini_set('display_errors', 0);
            ExceptionHandler::register();
            ErrorHandler::register();
        }
    }

}