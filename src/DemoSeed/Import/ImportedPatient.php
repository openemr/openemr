<?php

/**
 * A Synthea patient after import: its OpenEMR pid.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Import;

use OpenEMR\DemoSeed\Synthea\SyntheaPatient;

final readonly class ImportedPatient
{
    public function __construct(
        public int $pid,
        public string $uuid,
        public SyntheaPatient $synthea,
    ) {
        if ($pid <= 0) {
            throw new \DomainException('Patient pid must be positive');
        }
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid) !== 1) {
            throw new \DomainException('Patient uuid must be a lowercase UUID string');
        }
    }
}
