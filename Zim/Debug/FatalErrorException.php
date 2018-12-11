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
 * Fatal Error Exception.
 *
 * @author Konstanton Myakshin <koc-dp@yandex.ru>
 */
class FatalErrorException extends \ErrorException
{
    private $originalClassName;

    public function __construct(\Throwable $e)
    {
        $this->originalClassName = \get_class($e);
        $this->setTrace($e->getTrace());
    }

    public function getOriginalClassName(): string
    {
        return $this->originalClassName;
    }

    protected function setTrace($trace)
    {
        $traceReflector = new \ReflectionProperty('Exception', 'trace');
        $traceReflector->setAccessible(true);
        $traceReflector->setValue($this, $trace);
    }
}
