<?php

/**
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @author Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2005-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2023 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class FormatMoney
{
    /**
     * Format a monetary amount according to currency settings.
     *
     * @param mixed $amount The amount to format
     * @param bool $symbol Whether to prepend the currency symbol
     * @param ?ParameterBag $globals Optional globals bag for testing (defaults to OEGlobalsBag)
     */
    public static function getFormattedMoney($amount, bool $symbol = false, ?ParameterBag $globals = null): string
    {
        $globals ??= OEGlobalsBag::getInstance();

        $s = number_format(
            floatval($amount),
            $globals->getInt('currency_decimals', 2),
            $globals->getString('currency_dec_point', '.'),
            $globals->getString('currency_thousands_sep', ',')
        );

        // If the currency symbol exists and is requested, prepend it.
        if ($symbol) {
            $currencySymbol = $globals->getString('gbl_currency_symbol', '');
            if ($currencySymbol !== '') {
                $s = $currencySymbol . " $s";
            }
        }

        return $s;
    }

    /**
     * Format amount or return empty string for zero/falsy values.
     *
     * @param mixed $amount The amount to format
     * @param ?ParameterBag $globals Optional globals bag for testing (defaults to OEGlobalsBag)
     */
    public static function getBucks($amount, ?ParameterBag $globals = null): string
    {
        return $amount ? self::getFormattedMoney($amount, false, $globals) : '';
    }
}
