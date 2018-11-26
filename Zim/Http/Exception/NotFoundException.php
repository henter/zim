<?php
/**
 * File NotFoundException.php
 * @henter
 * Time: 2018-11-25 23:30
 *
 */

namespace Zim\Http\Exception;

class NotFoundException extends Exception
{
    /**
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     * @param array      $headers
     */
    public function __construct(string $message = null, \Exception $previous = null, int $code = 0, array $headers = array())
    {
        parent::__construct(404, $message, $previous, $headers, $code);
    }
}