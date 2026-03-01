<?php

/**
 * Default HL7 order generator — delegates to default_*() procedural functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Orders;

class DefaultHl7OrderGenerator implements Hl7OrderGeneratorInterface
{
    public function generate(int $orderId): Hl7OrderResult
    {
        return default_gen_hl7_order($orderId);
    }

    public function send(int $providerId, string $hl7): string
    {
        return default_send_hl7_order($providerId, $hl7);
    }

    /** @return array<mixed> */
    public function loadPayerInfo(int $patientId, string $date = ''): array
    {
        return default_loadPayerInfo($patientId, $date);
    }
}
