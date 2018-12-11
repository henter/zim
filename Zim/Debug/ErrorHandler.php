<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zim\Debug;

/**
 * A generic ErrorHandler for the PHP engine.
 *
 * Provides five bit fields that control how errors are handled:
 * - thrownErrors: errors thrown as \ErrorException
 * - loggedErrors: logged errors, when not @-silenced
 * - scopedErrors: errors thrown or logged with their local context
 * - tracedErrors: errors logged with their stack trace
 * - screamedErrors: never @-silenced errors
 *
 * Each error level can be logged by a dedicated PSR-3 logger object.
 * Screaming only applies to logging.
 * Throwing takes precedence over logging.
 * Uncaught exceptions are logged as E_ERROR.
 * E_DEPRECATED and E_USER_DEPRECATED levels never throw.
 * E_RECOVERABLE_ERROR and E_USER_ERROR levels always throw.
 * Non catchable errors that can be detected at shutdown time are logged when the scream bit field allows so.
 * As errors have a performance cost, repeated errors are all logged, so that the developer
 * can see them and weight them as more important to fix than others of the same level.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class ErrorHandler
{
    private $levels = array(
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
        E_NOTICE => 'Notice',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Runtime Notice',
        E_WARNING => 'Warning',
        E_USER_WARNING => 'User Warning',
        E_COMPILE_WARNING => 'Compile Warning',
        E_CORE_WARNING => 'Core Warning',
        E_USER_ERROR => 'User Error',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_COMPILE_ERROR => 'Compile Error',
        E_PARSE => 'Parse Error',
        E_ERROR => 'Error',
        E_CORE_ERROR => 'Core Error',
    );

    private $thrownErrors = 0x1FFF; // E_ALL - E_DEPRECATED - E_USER_DEPRECATED
    private $screamedErrors = 0x55; // E_ERROR + E_CORE_ERROR + E_COMPILE_ERROR + E_PARSE
    private $traceReflector;
    private $exceptionHandler;

    /**
     * Registers the error handler.
     *
     * @param self|null $handler The handler to register
     *
     * @return self The registered error handler
     */
    public static function register(self $handler = null)
    {
        if (null === $handler) {
            $handler = new static();
        }

        if (null === $prev = set_error_handler(array($handler, 'handleError'))) {
            restore_error_handler();
            set_error_handler(array($handler, 'handleError'), $handler->thrownErrors);
        }

        $prev = set_exception_handler(array($handler, 'handleException'));
        $handler->setExceptionHandler($prev);

        return $handler;
    }

    public function __construct()
    {
        $this->traceReflector = new \ReflectionProperty('Exception', 'trace');
        $this->traceReflector->setAccessible(true);
    }

    /**
     * Sets a user exception handler.
     *
     * @param callable $handler A handler that will be called on Exception
     *
     * @return callable|null The previous exception handler
     */
    public function setExceptionHandler(callable $handler = null)
    {
        $prev = $this->exceptionHandler;
        $this->exceptionHandler = $handler;

        return $prev;
    }

    /**
     * Handles errors by filtering then logging them according to the configured bit fields.
     *
     * @param int    $type    One of the E_* constants
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws \ErrorException When $this->thrownErrors requests so
     *
     * @internal
     */
    public function handleError($type, $message, $file, $line)
    {
        $level = error_reporting();
        $level |= E_RECOVERABLE_ERROR | E_USER_ERROR | E_DEPRECATED | E_USER_DEPRECATED;
        $type &= $level | $this->screamedErrors;

        $msg = $this->levels[$type].': '.$message;
        $errorAsException = new \ErrorException($msg, 0, $type, $file, $line);

        $this->traceReflector->setValue($errorAsException, $errorAsException->getTrace());

        throw $errorAsException;
    }

    /**
     * @param $exception
     * @param array|null $error
     * @return mixed
     * @throws FatalErrorException
     * @throws \Throwable
     */
    public function handleException($exception, array $error = null)
    {
        if (!$exception instanceof \Exception) {
            $exception = new FatalErrorException($exception);
        }
        $handlerException = null;

        $exceptionHandler = $this->exceptionHandler;
        $this->exceptionHandler = null;
        try {
            if (null !== $exceptionHandler) {
                return \call_user_func($exceptionHandler, $exception);
            }
            $handlerException = $handlerException ?: $exception;
        } catch (\Throwable $handlerException) {
        }
        if ($exception === $handlerException) {
            throw $exception; // to native
        }
        $this->handleException($handlerException);
    }

}
