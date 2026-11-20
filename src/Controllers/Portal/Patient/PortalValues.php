<?php

/**
 * Value helpers the portal/patient controllers share, kept as the Phreeze controllers behaved.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

final class PortalValues
{
    /**
     * What empty() treated as filled: anything but null, false, 0, '', '0' and [].
     */
    public static function isFilled(mixed $value): bool
    {
        return !in_array($value, [null, false, 0, 0.0, '', '0', []], true);
    }

    /**
     * A record id from a URL or a record.
     *
     * @throws \RuntimeException "Not found" unless it is a positive whole number
     */
    public static function positiveId(mixed $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!is_int($id) || $id <= 0) {
            throw new \RuntimeException('Not found');
        }

        return $id;
    }

    /**
     * What a PHP script that writes JavaScript (such as a localisation file) prints.
     */
    public static function scriptOutput(string $path): string
    {
        ob_start();
        try {
            require $path;
        } finally {
            $output = ob_get_clean();
        }

        return $output === false ? '' : $output;
    }
}
