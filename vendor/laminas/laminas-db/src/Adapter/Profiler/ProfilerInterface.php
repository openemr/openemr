<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Profiler;

interface ProfilerInterface
{
    /**
     * @param string|\Laminas\Db\Adapter\StatementContainerInterface $target
     * @return mixed
     */
    public function profilerStart($target);
    public function profilerFinish();
}
