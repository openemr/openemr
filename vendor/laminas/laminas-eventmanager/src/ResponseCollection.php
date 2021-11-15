<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager;

use SplStack;

use function count;

/**
 * Collection of signal handler return values
 */
class ResponseCollection extends SplStack
{
    protected $stopped = false;

    /**
     * Did the last response provided trigger a short circuit of the stack?
     *
     * @return bool
     */
    public function stopped()
    {
        return $this->stopped;
    }

    /**
     * Mark the collection as stopped (or its opposite)
     *
     * @param  bool $flag
     */
    public function setStopped($flag)
    {
        $this->stopped = (bool) $flag;
    }

    /**
     * Convenient access to the first handler return value.
     *
     * @return mixed The first handler return value
     */
    public function first()
    {
        return parent::bottom();
    }

    /**
     * Convenient access to the last handler return value.
     *
     * If the collection is empty, returns null. Otherwise, returns value
     * returned by last handler.
     *
     * @return mixed The last handler return value
     */
    public function last()
    {
        if (count($this) === 0) {
            return;
        }
        return parent::top();
    }

    /**
     * Check if any of the responses match the given value.
     *
     * @param  mixed $value The value to look for among responses
     * @return bool
     */
    public function contains($value)
    {
        foreach ($this as $response) {
            if ($response === $value) {
                return true;
            }
        }
        return false;
    }
}
