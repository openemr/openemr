<?php

/**
 * @see       https://github.com/laminas/laminas-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Console\Prompt;

use Laminas\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Laminas\Console\Console;
use Laminas\Console\Exception;
use ReflectionClass;

abstract class AbstractPrompt implements PromptInterface
{
    /**
     * @var ConsoleAdapter
     */
    protected $console;

    /**
     * @var mixed
     */
    protected $lastResponse;

    /**
     * Return last answer to this prompt.
     *
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Return console adapter to use when showing prompt.
     *
     * @return ConsoleAdapter
     */
    public function getConsole()
    {
        if (! $this->console) {
            $this->console = Console::getInstance();
        }

        return $this->console;
    }

    /**
     * Set console adapter to use when showing prompt.
     *
     * @param ConsoleAdapter $adapter
     */
    public function setConsole(ConsoleAdapter $adapter)
    {
        $this->console = $adapter;
    }

    /**
     * Create an instance of this prompt, show it and return response.
     *
     * This is a convenience method for creating statically creating prompts, i.e.:
     *
     *      $name = Laminas\Console\Prompt\Line::prompt("Enter your name: ");
     *
     * @return mixed
     * @throws Exception\BadMethodCallException
     */
    public static function prompt()
    {
        if (get_called_class() === __CLASS__) {
            throw new Exception\BadMethodCallException(
                'Cannot call prompt() on AbstractPrompt class. Use one of the Laminas\Console\Prompt\ subclasses.'
            );
        }

        $refl     = new ReflectionClass(get_called_class());
        $instance = $refl->newInstanceArgs(func_get_args());
        return $instance->show();
    }
}
