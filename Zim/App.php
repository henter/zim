<?php
/**
 * File App.php
 * @henter
 * Time: 2018-11-24 19:29
 *
 */
namespace Zim;

use Zim\Config\ConfigInterface;
use Zim\Container\Container;
use Zim\Debug\ErrorHandler;
use Zim\Debug\ExceptionHandler;
use Zim\Event\Dispatcher;
use Zim\Routing\Router;
use Zim\Traits\AppHelper;
use Zim\Traits\RouteRequest;
use Zim\Config\Config;

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
    protected $loadedConfigurations = [];

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

    public function __construct()
    {
        $this->bootstrapContainer();
        $this->registerErrorHandling();

        //config
        $this->singleton('config', function () {
            return new Config();
        });
        $this->configure('app');
        $this->configure('routes');

        //router after config
        $this->router = new Router();

        //event
        $this->singleton(Dispatcher::class);
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
     * Bootstrap the application container.
     *
     * @return void
     */
    protected function bootstrapContainer()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(self::class, $this);
        $this->instance('env', $this->env());

        $this->registerContainerAliases();
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
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
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
    public function getConfigurationPath($name = null)
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
    }

    /**
     * Register the core container aliases.
     *
     * @return void
     */
    protected function registerContainerAliases()
    {
        $this->aliases = [
            ConfigInterface::class => 'config',
            //TODO
            'Illuminate\Contracts\Foundation\Application' => 'app',
            'Illuminate\Contracts\Cache\Factory' => 'cache',
            'Illuminate\Contracts\Cache\Repository' => 'cache.store',
            'Illuminate\Container\Container' => 'app',
            'Illuminate\Contracts\Container\Container' => 'app',
            'Illuminate\Contracts\Events\Dispatcher' => 'events',
            'log' => 'Psr\Log\LoggerInterface',
            'request' => 'Illuminate\Http\Request',
            'Laravel\Lumen\Routing\Router' => 'router',
        ];
    }

    public function env()
    {
        //TODO
        return 'dev';
    }

    /**
     * Set the error handling for the application.
     *
     * @return void
     */
    protected function registerErrorHandling()
    {
        //TODO
        return $this->debug();

        error_reporting(-1);

        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });

        set_exception_handler(function ($e) {
            $this->handleUncaughtException($e);
        });

        register_shutdown_function(function () {
            $this->handleShutdown();
        });
    }

    public function debug()
    {
        error_reporting(E_ALL);

        if (!\in_array(\PHP_SAPI, array('cli', 'phpdbg'), true)) {
            ini_set('display_errors', 0);
            ExceptionHandler::register();
        } elseif ((!filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN) || ini_get('error_log'))) {
            // CLI - display errors only if they're not already logged to STDERR
            ini_set('display_errors', 1);
        }
        ErrorHandler::register();
    }


}