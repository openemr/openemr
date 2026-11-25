<?php

/**
 * One claim observed in an EDI X12 277 / 277CA file.
 *
 * Holds raw facts exactly as the file states them, keyed by the claim's own
 * reference (TRN02). Several claims may belong to the same patient; each is a
 * separate object with its own append-only status lists. No code-label,
 * database, or session data is mixed in.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR Community
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

final readonly class Claim277Claim
{
    /** @var list<Claim277Status> */
    public array $statuses;

    /** @var list<Claim277Status> */
    public array $serviceLineStatuses;

    /**
     * @param string $reference             Claim reference key from TRN02
     * @param ?Claim277Source $informationSource  Nearest ancestor information source (level 20)
     * @param ?string $payerClaimNumber     REF*1K
     * @param ?string $clearinghouseTrace   REF*D9
     * @param ?string $patientAccountNumber REF*EJ
     * @param ?string $serviceDate          DTP*472 date, first eight digits
     * @param ?string $patientName          NM1 with entity code IL or QC
     * @param list<Claim277Status> $statuses             Claim-level statuses, in file order
     * @param list<Claim277Status> $serviceLineStatuses  SS-level statuses, in file order
     */
    public function __construct(
        public string $reference,
        public ?Claim277Source $informationSource = null,
        public ?string $payerClaimNumber = null,
        public ?string $clearinghouseTrace = null,
        public ?string $patientAccountNumber = null,
        public ?string $serviceDate = null,
        public ?string $patientName = null,
        array $statuses = [],
        array $serviceLineStatuses = [],
    ) {
        $this->statuses = $statuses;
        $this->serviceLineStatuses = $serviceLineStatuses;
    }
}
