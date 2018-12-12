<?php
/**
 * File Exception.php
 * @henter
 * Time: 2018-11-26 14:32
 *
 */

namespace Zim\Http\Exception;

/**
 * HttpException.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class Exception extends \RuntimeException implements ExceptionInterface
{
    private $statusCode;
    private $headers;

    public function __construct(int $statusCode, string $message = null, \Exception $previous = null, array $headers = [], int $code = 0)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set response headers.
     *
     * @param array $headers Response headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
}
