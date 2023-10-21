<?php

/**
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2005-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2023 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

class FormatMoney
{
    public static function getFormattedMoney($amount, bool $symbol = false): string
    {
        $s = number_format(
            floatval($amount),
            $GLOBALS['currency_decimals'],
            $GLOBALS['currency_dec_point'],
            $GLOBALS['currency_thousands_sep']
        );
        // If the currency symbol exists and is requested, prepend it.
        if ($symbol && !empty($GLOBALS['gbl_currency_symbol'])) {
            $s = $GLOBALS['gbl_currency_symbol'] . " $s";
        }

        return $s;
    }

    public static function getBucks($amount, $return_zero = false): string
    {
        if ($amount) {
            return self::getFormattedMoney($amount);
        }

        if ($return_zero) {
            return "0.00";
        } else {
            return '';
        }
    }
}
