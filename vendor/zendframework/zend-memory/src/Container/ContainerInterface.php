<?php
/**
 * @see       https://github.com/zendframework/zend-memory for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-memory/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Memory\Container;

/**
 * Memory value container interface
 */
interface ContainerInterface
{
    /**
     * Get string value reference
     *
     * _Must_ be used for value access before PHP v 5.2
     * or _may_ be used for performance considerations
     *
     * @return &string
     */
    public function &getRef();

    /**
     * Signal, that value is updated by external code.
     *
     * Should be used together with getRef()
     */
    public function touch();

    /**
     * Lock object in memory.
     */
    public function lock();

    /**
     * Unlock object
     */
    public function unlock();

    /**
     * Return true if object is locked
     *
     * @return bool
     */
    public function isLocked();
}
