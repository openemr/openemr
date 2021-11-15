<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Exception;

use Psr\Container\NotFoundExceptionInterface;

use function sprintf;

class MissingHydratorServiceException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    public static function forService(string $serviceName) : self
    {
        return new self(sprintf(
            'Unable to resolve "%s" to a hydrator service.',
            $serviceName
        ));
    }
}
