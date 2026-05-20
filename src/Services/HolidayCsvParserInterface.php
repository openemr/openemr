<?php

/**
 * Holiday CSV parser interface.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

interface HolidayCsvParserInterface
{
    /**
     * Read the next non-header, non-blank data row from an open CSV handle.
     *
     * @param resource $handle
     * @return list<string>|null Null when the handle is exhausted.
     */
    public function readNextDataRow($handle): ?array;

    /**
     * Validate that the file at $path is a non-empty CSV whose rows have
     * a parseable date in column 0 and at least two columns.
     */
    public function isValidCsvContent(string $path): bool;

    /**
     * Localized error message for the last failed validation, or '' if none.
     */
    public function getLastError(): string;
}
