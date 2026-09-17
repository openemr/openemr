<?php

/**
 * Utilities for the Administration -> Coding interface.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Utils;

class CodingUtils
{
    public static function isActiveCheckboxChecked(mixed $active, mixed $mode): bool
    {
        return (bool) $active || ($mode === 'modify' && $active === null);
    }
}
