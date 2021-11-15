<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Stdlib;

use ArrayIterator;
use ArrayObject as PhpArrayObject;

use function array_reverse;

/**
 * ArrayObject that acts as a stack with regards to iteration
 */
class ArrayStack extends PhpArrayObject
{
    /**
     * Retrieve iterator
     *
     * Retrieve an array copy of the object, reverse its order, and return an
     * ArrayIterator with that reversed array.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $array = $this->getArrayCopy();
        return new ArrayIterator(array_reverse($array));
    }
}
