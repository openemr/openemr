<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core;

use OpenEMR\Core\Traits\SingletonTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Typed access to environment variables. Extends Symfony ParameterBag.
 *
 * Merges values from $_SERVER, $_ENV, and getenv() (later sources win),
 * matching the priority used by Symfony DotEnv.
 *
 * Inherits typed getters from ParameterBag:
 *
 * @see ParameterBag::getString()   getString(string $key, string $default = ''): string
 * @see ParameterBag::getInt()      getInt(string $key, int $default = 0): int
 * @see ParameterBag::getBoolean()  getBoolean(string $key, bool $default = false): bool
 * @see ParameterBag::getAlpha()    getAlpha(string $key, string $default = ''): string
 * @see ParameterBag::getAlnum()    getAlnum(string $key, string $default = ''): string
 * @see ParameterBag::getDigits()   getDigits(string $key, string $default = ''): string
 * @see ParameterBag::getEnum()     getEnum(string $key, string $class, ?BackedEnum $default = null): ?BackedEnum
 */
class OEEnvBag extends ParameterBag
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        // `getenv()` with no arguments always returns an array<string, string>;
        // the string-returning overload requires a name argument.
        return new static(array_merge($_SERVER, $_ENV, getenv())); // @phpstan-ignore new.static
    }
}
