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
        if (strtolower((string) $payerResponsibility) == "primary") {
            return "p";
        } elseif (strtolower((string) $payerResponsibility) == "secondary") {
            return "s";
        } elseif (strtolower((string) $payerResponsibility) == "tertiary") {
            return"t";
        } else {
            return substr((string) $payerResponsibility, 0, 1);
        }
    }
}
