<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Common;

use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;
use ReflectionMethod;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @mixin TestCase
 */
trait AssertValidNamedArgumentsTrait
{
    /**
     * Pre-check arguments names of method passed in test are valid
     *
     * @throws InvalidArgumentException
     */
    protected static function assertValidNamedArguments(
        ?array $namedArguments,
        string $className,
        string $methodName = '__construct',
    ): void {
        $existingArgs = array_map(
            fn($parameter): string => $parameter->getName(),
            (new ReflectionMethod($className, $methodName))->getParameters()
        );
        $unknownArgs = array_diff(array_keys($namedArguments ?? []), $existingArgs);

        Assert::isEmpty($unknownArgs, sprintf(
            'Unknown argument %s for %s::%s. Valid ones are: %s.',
            implode(', ', $unknownArgs),
            $className,
            $methodName,
            implode(', ', $existingArgs),
        ));
    }
}
