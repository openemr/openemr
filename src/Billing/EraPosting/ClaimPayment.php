<?php

/**
 * One claim payment (CLP loop 2100) from an 835, as ParseERA::parseERA()
 * hands it to its callback, parsed into types.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraPosting;

final readonly class ClaimPayment
{
    /**
     * @param string            $checkNumber       TRN02
     * @param string            $checkDate         BPR16, yyyymmdd
     * @param string            $productionDate    DTM*405, yyyymmdd (the check date when absent)
     * @param string            $ourClaimId        CLP01, the claim id we sent (pid-encounter)
     * @param string            $claimStatusCode   CLP02
     * @param string            $payerClaimId      CLP07, the payer's claim control number
     * @param string            $serviceDate       the last DTM*472 date read, else the claim date (DTM*232/233)
     * @param int               $crossover         1 when the payer forwarded the claim to the next payer (NM1*TT)
     * @param list<ServiceLine> $serviceLines
     */
    public function __construct(
        public string $checkNumber,
        public string $checkDate,
        public string $productionDate,
        public string $ourClaimId,
        public string $claimStatusCode,
        public string $payerClaimId,
        public string $serviceDate,
        public string $patientFirstName,
        public string $patientLastName,
        public string $warnings,
        public int $crossover,
        public array $serviceLines,
    ) {
    }

    /**
     * @param array<mixed> $out the claim array ParseERA::parseERA() passes to its callback
     */
    public static function fromParsedEra(array $out): self
    {
        $serviceLines = [];
        $svcs = $out['svc'] ?? [];
        foreach (is_array($svcs) ? $svcs : [] as $svc) {
            if (!is_array($svc)) {
                continue;
            }
            $adjustments = [];
            $adjs = $svc['adj'] ?? [];
            foreach (is_array($adjs) ? $adjs : [] as $adj) {
                if (!is_array($adj)) {
                    continue;
                }
                $adjustments[] = new Adjustment(
                    self::string($adj, 'group_code'),
                    self::string($adj, 'reason_code'),
                    self::float($adj, 'amount'),
                );
            }
            $allowed = $svc['allowed'] ?? null;
            $remark = $svc['remark'] ?? null;
            $serviceLines[] = new ServiceLine(
                self::string($svc, 'code'),
                self::string($svc, 'mod'),
                self::float($svc, 'chg'),
                self::float($svc, 'paid'),
                is_numeric($allowed) ? (float) $allowed : null,
                is_scalar($remark) ? (string) $remark : null,
                $adjustments,
            );
        }

        $serviceDate = $out['dos'] ?? $out['claim_date'] ?? '';
        $crossover = $out['crossover'] ?? 0;

        return new self(
            checkNumber: self::string($out, 'check_number'),
            checkDate: self::string($out, 'check_date'),
            productionDate: self::string($out, 'production_date'),
            ourClaimId: self::string($out, 'our_claim_id'),
            claimStatusCode: self::string($out, 'claim_status_code'),
            payerClaimId: self::string($out, 'payer_claim_id'),
            serviceDate: is_scalar($serviceDate) ? (string) $serviceDate : '',
            patientFirstName: self::string($out, 'patient_fname'),
            patientLastName: self::string($out, 'patient_lname'),
            warnings: self::string($out, 'warnings'),
            crossover: is_int($crossover) ? $crossover : 0,
            serviceLines: $serviceLines,
        );
    }

    /**
     * @param array<mixed> $values
     */
    private static function string(array $values, string $key): string
    {
        $value = $values[$key] ?? '';
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param array<mixed> $values
     */
    private static function float(array $values, string $key): float
    {
        $value = $values[$key] ?? 0.0;
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
