<?php

/**
 * @author    Eric Stern <erics@opencoreemr.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @link      https://www.open-emr.org
 * @package   OpenEMR
 */

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Apis;

use Money\{
    Currency,
    Currencies\ISOCurrencies,
    Parser\DecimalMoneyParser,
};
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

/**
 * API endpoint logic to get the RainforestPay parameters to power their
 * <rainforest-payment> HTML component.
 *
 * The structure here isn't ideal, but it's somewhat reusable until additional
 * upstream refactoring can occur.
 */
class GetPayinComponentParameters
{
    /**
     * Request handler to set up a payment. Expects a POST request with a JSON
     * body in the following format:
     *
     * {
     *   dollars: numeric-string,
     *   patientId: '12346',
     *   encounters: array{
     *     id: string,
     *     code: string.
     *     codeType: string,
     *     value: numeric-string,
     *   }[],
     * }
     *
     * The numeric strings are dollars-formatted money amounts, e.g. '1.23' for
     * $1.23. Assumes USD.
     *
     * @param ?string $trustedPatientId When set, this value overrides the
     *     `patientId` field from the JSON body. Callers that have already
     *     authenticated a patient (e.g. the portal endpoint) MUST pass the
     *     session-bound pid here; the body's `patientId` cannot be relied on
     *     because it is signed into the Rainforest `payin_config` metadata
     *     and later written to `ar_activity` by the webhook processor, so
     *     accepting a client-supplied value permits credit attribution to
     *     another patient's account.
     *
     * @return array{
     *   payin_config_id: string,
     *   session_key: string,
     * }
     */
    public static function parseRawRequest(
        ServerRequestInterface $request,
        OEGlobalsBag $bag,
        ?string $trustedPatientId = null,
    ): array {
        if ($request->getMethod() !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit(1);
        }

        if (!str_starts_with($request->getHeaderLine('Content-Type'), 'application/json')) {
            header('HTTP/1.1 406 Not Acceptable');
            header('Accept: application/json');
            exit(1);
        }

        $rawJson = (string) $request->getBody();
        /**
         * @var array{
         *   dollars: string,
         *   patientId: string,
         *   encounters: array<array{id: string, code: string, codeType: string, value: string}>,
         * }
         */
        $postBody = json_decode($rawJson, true, flags: JSON_THROW_ON_ERROR);

        $currencies = new ISOCurrencies();
        $parser = new DecimalMoneyParser($currencies);

        $usd = new Currency('USD');

        $money = $parser->parse($postBody['dollars'], $usd);

        if (!$money->isPositive()) {
            throw new UnexpectedValueException('Payment amount must be positive');
        }

        $patientId = self::selectPatientId($trustedPatientId, $postBody['patientId']);

        $validBillingKeys = self::loadValidBillingKeys($patientId);

        $encounters = array_map(function (array $row) use ($validBillingKeys, $parser, $usd): Rainforest\EncounterData {
            $key = $row['id'] . '|' . $row['codeType'] . '|' . $row['code'];
            if (!isset($validBillingKeys[$key])) {
                throw new UnexpectedValueException('Encounter line does not belong to patient');
            }
            $amount = $parser->parse($row['value'], $usd);
            if (!$amount->isPositive()) {
                throw new UnexpectedValueException('Encounter amount must be positive');
            }
            return new Rainforest\EncounterData(
                id: $row['id'],
                code: $row['code'],
                codeType: $row['codeType'],
                amount: $amount,
            );
        }, $postBody['encounters']);

        $rf = Rainforest\Api::makeFromGlobals($bag);
        return $rf->getPaymentComponentParameters(
            amount: $money,
            patientId: $patientId,
            encounters: $encounters,
        );
    }

    /**
     * Build the set of legitimate (encounter, code_type, code) composite
     * keys for the bound patient's active billing rows.
     *
     * The composite key is what scopes credit attribution to the correct
     * patient: submitting an encounter id alone would let a caller attribute
     * a payment to any encounter number, but the (encounter, code_type, code)
     * triple only matches a row that (a) exists, (b) is active, and
     * (c) belongs to the session-bound patient. `activity = 1` excludes
     * reversed/voided lines so a stale billing row cannot be replayed against
     * a fresh authorization.
     *
     * The keys are returned as a string-keyed associative array so the
     * per-encounter check is O(1) and does not linearly scan billing rows
     * inside the array_map closure.
     *
     * @return array<string, true>
     */
    private static function loadValidBillingKeys(string $patientId): array
    {
        $keys = [];
        foreach (
            QueryUtils::fetchRecords(
                'SELECT encounter, code_type, code FROM billing WHERE pid = ? AND activity = 1',
                [$patientId],
            ) as $line
        ) {
            $encounter = $line['encounter'];
            $codeType = $line['code_type'];
            $code = $line['code'];
            if (!is_int($encounter) && !is_string($encounter)) {
                throw new UnexpectedValueException('Unexpected billing.encounter type');
            }
            if (!is_string($codeType) || !is_string($code)) {
                throw new UnexpectedValueException('Unexpected billing.code_type or billing.code type');
            }
            $keys[$encounter . '|' . $codeType . '|' . $code] = true;
        }
        return $keys;
    }

    /**
     * Choose which patient id to sign into the Rainforest `payin_config`
     * metadata.
     *
     * Prefer the caller-provided trusted patient id (session-bound) over
     * anything the body claims. When no trusted value is supplied fall back
     * to the body — the only current caller is the portal endpoint which
     * passes the session pid, but the argument stays optional so downstream
     * refactors can keep using the class shape.
     *
     * Split out as a static helper so the trust-boundary decision is
     * testable in isolation without touching the network-facing
     * `Rainforest\Api::makeFromGlobals` code path.
     */
    public static function selectPatientId(
        ?string $trustedPatientId,
        ?string $bodyPatientId,
    ): string {
        if ($trustedPatientId !== null && $trustedPatientId !== '') {
            return $trustedPatientId;
        }
        if ($bodyPatientId === null || $bodyPatientId === '') {
            throw new UnexpectedValueException(
                'patientId must be supplied by either the trusted caller or the request body',
            );
        }
        return $bodyPatientId;
    }
}
