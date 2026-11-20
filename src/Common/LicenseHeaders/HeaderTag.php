<?php

/**
 * One docblock-style tag from a file header, such as `@license`
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

final readonly class HeaderTag
{
    /**
     * @param string $name lowercase, without the `@` or a trailing colon
     * @param string $value whitespace collapsed, continuation lines joined
     * @param int $line 1-based line of the tag in the file
     */
    public function __construct(
        public string $name,
        public string $value,
        public int $line,
    ) {
    }

    /**
     * Collapse runs of whitespace so a wrapped or double-spaced value compares
     * equal to its one-line form.
     */
    public static function normalizeValue(string $value): string
    {
        $words = explode(' ', str_replace(["\t", "\r", "\n"], ' ', $value));
        return implode(' ', array_filter($words, static fn(string $word): bool => $word !== ''));
    }
}
