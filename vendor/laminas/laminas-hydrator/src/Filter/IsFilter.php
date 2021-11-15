<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Filter;

use function strpos;

final class IsFilter implements FilterInterface
{
    public function filter(string $property, ?object $instance = null) : bool
    {
        $pos = strpos($property, '::');
        if ($pos !== false) {
            $pos += 2;
        } else {
            $pos = 0;
        }

        return strpos($property, 'is', $pos) === $pos;
    }
}
