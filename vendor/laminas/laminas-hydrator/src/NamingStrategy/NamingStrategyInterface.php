<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\NamingStrategy;

/**
 * Allow property extraction / hydration for hydrator
 */
interface NamingStrategyInterface
{
    /**
     * Converts the given name so that it can be extracted by the hydrator.
     *
     * @param null|mixed[] $data The original data for context.
     */
    public function hydrate(string $name, ?array $data = null) : string;

    /**
     * Converts the given name so that it can be hydrated by the hydrator.
     *
     * @param null|object $object The original object for context.
     */
    public function extract(string $name, ?object $object = null) : string;
}
