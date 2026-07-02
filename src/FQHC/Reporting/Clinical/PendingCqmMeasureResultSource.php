<?php

/**
 * Default CqmMeasureResultSource: reports every UDS clinical measure as not
 * yet computed.
 *
 * The CQM/AMC engine (src/Services/Qdm/ResultsCalculator) computes population
 * counts per *population set*, and several of the mapped eCQMs define more
 * than one population set or stratification per measure (for example,
 * CMS117v14 Childhood Immunization Status has a separate population set per
 * vaccine combination). UDS reports a single line per measure, so wiring the
 * live engine requires picking the correct population set/stratification for
 * each measure in the map against the current-year UDS Manual and eCQM
 * specification before any number is shown — guessing here would put a wrong
 * compliance figure in front of a health center. This placeholder keeps
 * Table 6B/7 visible with an honest "not yet computed" state (the same
 * pattern the Patient Snapshot used for uncaptured fields) until that
 * measure-by-measure engine wiring ships.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\Clinical;

final class PendingCqmMeasureResultSource implements CqmMeasureResultSource
{
    public function resultsForYear(int $year): array
    {
        return [];
    }
}
