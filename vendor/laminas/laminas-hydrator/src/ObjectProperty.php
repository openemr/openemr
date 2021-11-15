<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use function sprintf;
use function trigger_error;

use const E_USER_DEPRECATED;

trigger_error(sprintf(
    'Class %s is deprecated, please use %s instead',
    ObjectProperty::class,
    ObjectPropertyHydrator::class
), E_USER_DEPRECATED);

/**
 * @deprecated since 3.0.0; to be removed in 4.0.0. Use ObjectPropertyHydrator instead.
 */
class ObjectProperty extends ObjectPropertyHydrator
{
}
