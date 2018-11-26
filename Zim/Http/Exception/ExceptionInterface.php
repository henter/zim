<?php
/**
 * File ExceptionInterface.php
 * @henter
 * Time: 2018-11-26 14:30
 *
 */

namespace Zim\Http\Exception;


/**
 * Interface for HTTP error exceptions.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
interface ExceptionInterface
{
    /**
     * Returns the status code.
     *
     * @return int An HTTP response status code
     */
    public function getStatusCode();

    /**
     * Returns response headers.
     *
     * @return array Response headers
     */
    public function getHeaders();
}
