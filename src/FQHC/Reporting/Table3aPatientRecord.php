<?php

/**
 * One patient's already-resolved age band and sex for UDS Table 3A.
 *
 * The parsed reporting input: the age band (from the patient's age at the
 * reporting "as of" date) and the male/female column have been determined at
 * the data boundary, so the aggregator only ever sees valid, typed values.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class Table3aPatientRecord
{
    public function __construct(
        public Table3aAgeBand $ageBand,
        public UdsSex $sex,
    ) {
    }
}
