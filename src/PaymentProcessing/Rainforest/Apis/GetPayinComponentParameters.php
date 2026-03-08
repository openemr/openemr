<?php

/**
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright (c) 2026 OpenCoreEMR, Inc
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
     * @return array{
     *   payin_config_id: string,
     *   session_key: string,
     * }
     */
    public static function parseRawRequest(ServerRequestInterface $request, OEGlobalsBag $bag): array
    {
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

        $encounters = array_map(fn(array $row) => new Rainforest\EncounterData(
            id: $row['id'],
            code: $row['code'],
            codeType: $row['codeType'],
            amount: $parser->parse($row['value'], $usd),
        ), $postBody['encounters']);

        $rf = Rainforest\Api::makeFromGlobals($bag);
        return $rf->getPaymentComponentParameters(
            amount: $money,
            patientId: $postBody['patientId'],
            encounters: $encounters,
        );
    }
}
