<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Stdlib\ArrayUtils;

/**
 * Marker interface: can be used to replace keys completely in {@see ArrayUtils::merge()} operations
 */
interface MergeReplaceKeyInterface
{
    /**
     * @return mixed
     */
    public function getData();
}
