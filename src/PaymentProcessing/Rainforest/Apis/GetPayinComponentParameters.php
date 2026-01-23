<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Apis;

use Money\{
    Currency,
    Currencies\ISOCurrencies,
    Parser\DecimalMoneyParser,
};
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest;
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
    public static function parseRawRequest(OEGlobalsBag $bag): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit(1);
        }

        if (!str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            header('HTTP/1.1 406 Not Acceptable');
            header('Accept: application/json');
            exit(1);
        }

        $rawJson = file_get_contents('php://input');
        $postBody = json_decode($rawJson, true, flags: JSON_THROW_ON_ERROR);

        // Future scope: proper JSON API (e.g. PSR-7 resources)

        $currencies = new ISOCurrencies();
        $parser = new DecimalMoneyParser($currencies);

        $usd = new Currency('USD');

        $money = $parser->parse($postBody['dollars'], $usd);

        if (!$money->isPositive()) {
            throw new UnexpectedValueException('Payment amount must be positive');
        }

        $encounters = array_map(function ($row) use ($parser, $usd) {
            return new Rainforest\EncounterData(
                id: $row['id'],
                code: $row['code'],
                codeType: $row['codeType'],
                amount: $parser->parse($row['value'], $usd),
            );
        }, $postBody['encounters']);

        $rf = Rainforest::makeFromGlobals($bag);
        return $rf->getPaymentComponentParameters(
            amount: $money,
            patientId: $postBody['patientId'],
            encounters: $encounters,
        );
    }
}
