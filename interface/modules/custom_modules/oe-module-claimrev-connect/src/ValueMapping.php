<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

class ValueMapping
{
    public static function mapPayerResponsibility($payerResponsibility)
    {
        if (strtolower($payerResponsibility) == "primary") {
            return "p";
        } elseif (strtolower($payerResponsibility) == "secondary") {
            return "s";
        } elseif (strtolower($payerResponsibility) == "tertiary") {
            return"t";
        } else {
            return substr($payerResponsibility, 0, 1);
        }
    }
}
