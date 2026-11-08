<?php

/**
 * Parsed holiday row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use DateTimeImmutable;

final readonly class HolidayRow
{
    public function __construct(
        public DateTimeImmutable $date,
        public string $description,
    ) {
    }

    public function dateForStorage(): string
    {
        return $this->date->format('Y-m-d');
    }
}
