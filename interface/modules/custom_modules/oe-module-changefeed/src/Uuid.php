<?php

/**
 * Formats a 32-character hex uuid (as stored by the capture triggers) into the
 * canonical dashed form used as a FHIR resource logical id.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ahmed Armaan <arman.ahmaed@carer.ai>
 * @copyright Copyright (c) 2026 Ahmed Armaan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ChangeFeed;

final class Uuid
{
    /**
     * Convert a 32-char hex string to canonical 8-4-4-4-12 form, or null if the
     * input is not exactly 32 hex characters.
     */
    public static function fromHex(string $hex): ?string
    {
        $hex = strtolower($hex);
        if (preg_match('/^[0-9a-f]{32}$/', $hex) !== 1) {
            return null;
        }

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
