<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Stdlib\Guard;

/**
 * An aggregate for all guard traits
 */
trait AllGuardsTrait
{
    use ArrayOrTraversableGuardTrait;
    use EmptyGuardTrait;
    use NullGuardTrait;
}
