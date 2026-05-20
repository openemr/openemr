<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

class ValueMapping
{
    public static function mapPayerResponsibility(string $payerResponsibility): string
    {
        return match (strtolower($payerResponsibility)) {
            'primary'   => 'p',
            'secondary' => 's',
            'tertiary'  => 't',
            default     => substr($payerResponsibility, 0, 1),
        };
    }
}
