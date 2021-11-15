<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

interface HydratorProviderInterface
{
    /**
     * Provide plugin manager configuration for hydrators.
     *
     * @see    https://docs.mezzio.dev/mezzio/v3/features/container/config/#the-format
     * @return mixed[][]
     */
    public function getHydratorConfig() : array;
}
