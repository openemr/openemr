<?php

/**
 * @see       https://github.com/laminas/laminas-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Console\Prompt;

use Laminas\Console\Adapter\AdapterInterface as ConsoleAdapter;

interface PromptInterface
{
    /**
     * Show the prompt to user and return the answer.
     *
     * @return mixed
     */
    public function show();

    /**
     * Return last answer to this prompt.
     *
     * @return mixed
     */
    public function getLastResponse();

    /**
     * Return console adapter to use when showing prompt.
     *
     * @return ConsoleAdapter
     */
    public function getConsole();

    /**
     * Set console adapter to use when showing prompt.
     *
     * @param ConsoleAdapter $adapter
     * @return void
     */
    public function setConsole(ConsoleAdapter $adapter);
}
