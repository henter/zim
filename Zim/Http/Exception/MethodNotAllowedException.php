<?php
/**
 * File MethodNotAllowedException.php
 * @henter
 * Time: 2018-11-27 20:27
 *
 */

namespace Zim\Http\Exception;


/**
 * The resource was found but the request method is not allowed.
 *
 * This exception should trigger an HTTP 405 response in your application code.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class MethodNotAllowedException extends \RuntimeException
{
    protected $allowedMethods = [];

    public function __construct(array $allowedMethods, int $code = 0, \Exception $previous = null)
    {
        $this->allowedMethods = array_map('strtoupper', $allowedMethods);

        $message = 'Allowed methods '.implode(',', $allowedMethods);
        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets the allowed HTTP methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }
}
