<?php
/**
 * File ResponseInterface.php
 * @henter
 * Time: 2018-11-24 19:52
 *
 */

namespace Zim\Contract\Http;

interface Response
{
    public function prepare(Request $request);
}