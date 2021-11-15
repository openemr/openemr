<?php

/**
 * @see       https://github.com/laminas/laminas-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Console\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    /**
     * Usage
     *
     * @var string
     */
    protected $usage = '';

    /**
     * Constructor
     *
     * @param string $message
     * @param string $usage
     */
    public function __construct($message, $usage = '')
    {
        $this->usage = $usage;
        parent::__construct($message);
    }

    /**
     * Returns the usage
     *
     * @return string
     */
    public function getUsageMessage()
    {
        return $this->usage;
    }
}
