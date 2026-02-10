<?php

/**
 * DateMode - Enum for date mode selection in AR Activity processing
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Enums;

enum DateMode: int
{
    case PAYMENT = 0;
    case SERVICE = 1;
    case ENTRY = 2;
}
