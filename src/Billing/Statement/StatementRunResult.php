<?php

/**
 * What a statement run produced.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

final readonly class StatementRunResult
{
    public const PORTAL_NOT_AUTHORIZED = 'not-authorized';
    public const PORTAL_FAILED = 'failed';

    /**
     * @param string $output the statements, one after another, to print or download (empty for a portal run)
     * @param list<string> $skippedNothingDue patients who got no statement: their credits leave nothing due
     * @param list<string> $emailFailures "patient: reason" for each statement that couldn't be emailed
     * @param ?string $portalError PORTAL_NOT_AUTHORIZED or PORTAL_FAILED when a portal run stopped
     * @param ?array{firstName: string, pid: int} $onlyPatient the patient, when every invoice was one patient's
     */
    public function __construct(
        public string $output,
        public int $statementsWritten,
        public array $skippedNothingDue,
        public array $emailFailures,
        public ?string $portalError,
        public ?array $onlyPatient,
    ) {
    }
}
