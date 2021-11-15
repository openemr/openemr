<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager;

/**
 * Interface indicating that an object composes or can compose a
 * SharedEventManagerInterface instance.
 */
interface SharedEventsCapableInterface
{
    /**
     * Retrieve the shared event manager, if composed.
     *
     * @return null|SharedEventManagerInterface
     */
    public function getSharedManager();
}
