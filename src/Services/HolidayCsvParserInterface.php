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
     * Parse $path into a sequence of validated holiday rows.
     *
     * @return iterable<int, HolidayRow>
     *
     * @throws InvalidHolidayCsvException on any malformed row or empty file.
     */
    public function parse(string $path): iterable;
}
