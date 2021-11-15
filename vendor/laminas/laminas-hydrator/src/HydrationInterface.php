<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

interface HydrationInterface
{
    /**
     * Hydrate $object with the provided $data.
     *
     * @param mixed[] $data
     * @return object The implementation should return an object of any type.
     *     By purposely omitting the return type from the signature,
     *     implementations may choose to specify a more specific type.
     */
    public function hydrate(array $data, object $object);
}
