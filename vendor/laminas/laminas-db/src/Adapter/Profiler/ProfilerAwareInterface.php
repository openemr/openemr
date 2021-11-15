<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Profiler;

interface ProfilerAwareInterface
{
    /**
     * @param  ProfilerInterface $profiler
     */
    public function setProfiler(ProfilerInterface $profiler);
}
