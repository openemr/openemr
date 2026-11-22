<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\System;

/**
 * Typed access to the process environment, so callers never handle getenv()'s
 * false. Reads the process environment only, not $_ENV or $_SERVER.
 */
final class EnvironmentVariable
{
    public static function get(string $name): ?string
    {
        $value = getenv($name, true);
        return $value === false ? null : $value;
    }
}
