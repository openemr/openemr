<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Exception;

class InvalidConnectionParametersException extends RuntimeException implements ExceptionInterface
{
    /**
     * @var int
     */
    protected $parameters;

    /**
     * @param string $message
     * @param int $parameters
     */
    public function __construct($message, $parameters)
    {
        parent::__construct($message);
        $this->parameters = $parameters;
    }
}
