<?php

/**
 * One claim-status observation (STC segment) from an EDI X12 277 / 277CA file.
 *
 * Holds raw facts exactly as the file states them. STC segments are only ever
 * appended to a claim's status list; an earlier status is never overwritten.
 * Statuses at SS (service line) level also carry the preceding SVC segment.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR Community
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

final readonly class Claim277Status
{
    /**
     * @param string $category   First component of the composite STC01
     * @param string $code       Second component of the composite STC01
     * @param string $entity     Third component of the composite STC01
     * @param ?string $statusDate   STC02
     * @param ?string $actionCode   STC03
     * @param ?string $amount       STC04
     * @param ?string $payerText    STC07 free text
     * @param string $rawSegment    Complete STC segment as it appeared in the file
     * @param ?string $svcSegment   Preceding SVC segment when this status is at SS level
     */
    public function __construct(
        public string $category,
        public string $code,
        public string $entity,
        public ?string $statusDate,
        public ?string $actionCode,
        public ?string $amount,
        public ?string $payerText,
        public string $rawSegment,
        public ?string $svcSegment,
    ) {
    }
}
