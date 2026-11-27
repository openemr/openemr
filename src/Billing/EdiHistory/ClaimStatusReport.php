<?php

/**
 * Everything one 277 transaction says about one claim: the source that reported it, every
 * status it gave, and the numbers the claim is known by.
 *
 * A file can hold several reports for the same claim, one per transaction: commonly one from
 * the clearinghouse and one or more from the payer.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

use DateTimeImmutable;

final readonly class ClaimStatusReport
{
    /**
     * REF qualifier of the claim number the payer assigned.
     */
    private const REF_PAYER_CLAIM_CONTROL_NUMBER = '1K';

    /**
     * REF qualifier of the clearinghouse's trace number for the claim.
     */
    private const REF_CLEARINGHOUSE_TRACE_NUMBER = 'D9';

    /**
     * @param string                      $claimReference           TRN02 of the claim, the submitter's claim number
     *                                                              (CLM01 of the 837) in a 277CA
     * @param ClaimStatusSource           $source                   who reported the statuses
     * @param ?DateTimeImmutable          $sourceReceivedDate       the date the source says it received the claims (DTP*050)
     * @param string                      $interchangeControlNumber ISA13
     * @param ?DateTimeImmutable          $interchangeDate          ISA09
     * @param string                      $transactionControlNumber ST02
     * @param string                      $patientLastName          NM103 of the patient or subscriber the claim is under
     * @param string                      $patientFirstName         NM104 of the same
     * @param list<ClaimStatus>           $statuses                 in file order, claim statuses and service line statuses
     * @param array<string, list<string>> $references               REF values of the claim keyed by qualifier
     * @param ?DateTimeImmutable          $serviceDateFrom          claim service date (DTP*472), or the first day of a range
     * @param ?DateTimeImmutable          $serviceDateTo            the same date, or the last day of a range
     */
    public function __construct(
        public string $claimReference,
        public ClaimStatusSource $source,
        public ?DateTimeImmutable $sourceReceivedDate,
        public string $interchangeControlNumber,
        public ?DateTimeImmutable $interchangeDate,
        public string $transactionControlNumber,
        public string $patientLastName,
        public string $patientFirstName,
        public array $statuses,
        public array $references,
        public ?DateTimeImmutable $serviceDateFrom,
        public ?DateTimeImmutable $serviceDateTo,
    ) {
    }

    /**
     * The claim numbers the payer assigned (REF*1K). A payer gives one only to a claim it has
     * received.
     *
     * @return list<string>
     */
    public function payerClaimControlNumbers(): array
    {
        return $this->references[self::REF_PAYER_CLAIM_CONTROL_NUMBER] ?? [];
    }

    /**
     * @return list<string>
     */
    public function clearinghouseTraceNumbers(): array
    {
        return $this->references[self::REF_CLEARINGHOUSE_TRACE_NUMBER] ?? [];
    }

    /**
     * Whether this report is about the claim known by $reference: its own reference, or any
     * of its REF values. The whole value has to match, so 12-345 does not match 112-3456.
     */
    public function isAbout(string $reference): bool
    {
        $wanted = strtoupper(trim($reference));
        if ($wanted === '') {
            return false;
        }
        if (strtoupper($this->claimReference) === $wanted) {
            return true;
        }
        foreach ($this->references as $values) {
            foreach ($values as $value) {
                if (strtoupper($value) === $wanted) {
                    return true;
                }
            }
        }
        return false;
    }
}
