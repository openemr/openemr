<?php

/**
 * DTO for a single claim row returned by the ClaimRev claim-search API.
 *
 * Constructed once at the API boundary via ::fromApi() so that downstream
 * templates and services can access fields with native types instead of
 * mixed json_decode results.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector\Dto;

final readonly class ClaimSearchResult
{
    public function __construct(
        public ?string $statusName = null,
        public ?int $statusId = null,
        public ?int $payerFileStatusId = null,
        public ?string $payerFileStatusName = null,
        public ?int $payerAcceptanceStatusId = null,
        public ?string $payerAcceptanceStatusName = null,
        public ?int $paymentAdviceStatusId = null,
        public ?string $paymentAdviceStatusName = null,
        public ?string $eraClassification = null,
        public ?string $patientControlNumber = null,
        public ?bool $isWorked = null,
        public ?int $objectId = null,
        public ?int $claimTypeId = null,
        public ?string $claimType = null,
        public ?int $errorCount = null,
        public ?string $pFirstName = null,
        public ?string $pLastName = null,
        public ?string $birthDate = null,
        public ?string $payerName = null,
        public ?string $payerNumber = null,
        public ?string $providerFirstName = null,
        public ?string $providerLastName = null,
        public ?string $providerNpi = null,
        public ?string $serviceDate = null,
        public ?string $serviceDateEnd = null,
        public ?string $receivedDate = null,
        public ?float $billedAmount = null,
        public ?float $payerPaidAmount = null,
        public ?string $payerControlNumber = null,
        public ?string $memberNumber = null,
        public ?string $traceNumber = null,
    ) {
    }

    /**
     * Build from a single decoded API result item.
     *
     * Accepts either an associative array (json_decode with assoc=true) or
     * an object/stdClass (json_decode default). Unknown shapes parse to all-null.
     */
    public static function fromApi(mixed $raw): self
    {
        $arr = match (true) {
            is_array($raw) => $raw,
            is_object($raw) => (array) $raw,
            default => [],
        };

        return new self(
            statusName: self::asString($arr, 'statusName'),
            statusId: self::asInt($arr, 'statusId'),
            payerFileStatusId: self::asInt($arr, 'payerFileStatusId'),
            payerFileStatusName: self::asString($arr, 'payerFileStatusName'),
            payerAcceptanceStatusId: self::asInt($arr, 'payerAcceptanceStatusId'),
            payerAcceptanceStatusName: self::asString($arr, 'payerAcceptanceStatusName'),
            paymentAdviceStatusId: self::asInt($arr, 'paymentAdviceStatusId'),
            paymentAdviceStatusName: self::asString($arr, 'paymentAdviceStatusName'),
            eraClassification: self::asString($arr, 'eraClassification'),
            patientControlNumber: self::asString($arr, 'patientControlNumber'),
            isWorked: self::asBool($arr, 'isWorked'),
            objectId: self::asInt($arr, 'objectId'),
            claimTypeId: self::asInt($arr, 'claimTypeId'),
            claimType: self::asString($arr, 'claimType'),
            errorCount: self::asInt($arr, 'errorCount'),
            pFirstName: self::asString($arr, 'pFirstName'),
            pLastName: self::asString($arr, 'pLastName'),
            birthDate: self::asString($arr, 'birthDate'),
            payerName: self::asString($arr, 'payerName'),
            payerNumber: self::asString($arr, 'payerNumber'),
            providerFirstName: self::asString($arr, 'providerFirstName'),
            providerLastName: self::asString($arr, 'providerLastName'),
            providerNpi: self::asString($arr, 'providerNpi'),
            serviceDate: self::asString($arr, 'serviceDate'),
            serviceDateEnd: self::asString($arr, 'serviceDateEnd'),
            receivedDate: self::asString($arr, 'receivedDate'),
            billedAmount: self::asFloat($arr, 'billedAmount'),
            payerPaidAmount: self::asFloat($arr, 'payerPaidAmount'),
            payerControlNumber: self::asString($arr, 'payerControlNumber'),
            memberNumber: self::asString($arr, 'memberNumber'),
            traceNumber: self::asString($arr, 'traceNumber'),
        );
    }

    /**
     * @param array<int|string, mixed> $arr
     */
    private static function asString(array $arr, string $key): ?string
    {
        $v = $arr[$key] ?? null;
        return is_string($v) ? $v : null;
    }

    /**
     * @param array<int|string, mixed> $arr
     */
    private static function asInt(array $arr, string $key): ?int
    {
        $v = $arr[$key] ?? null;
        if (is_int($v)) {
            return $v;
        }
        return is_numeric($v) ? (int) $v : null;
    }

    /**
     * @param array<int|string, mixed> $arr
     */
    private static function asFloat(array $arr, string $key): ?float
    {
        $v = $arr[$key] ?? null;
        if (is_float($v) || is_int($v)) {
            return (float) $v;
        }
        return is_numeric($v) ? (float) $v : null;
    }

    /**
     * @param array<int|string, mixed> $arr
     */
    private static function asBool(array $arr, string $key): ?bool
    {
        $v = $arr[$key] ?? null;
        return is_bool($v) ? $v : null;
    }
}
