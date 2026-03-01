<?php

/**
 * Interface for HL7 order generation strategies.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Orders;

interface Hl7OrderGeneratorInterface
{
    /**
     * Generate an HL7 order message for the given procedure order.
     *
     * @param  int $orderId Procedure order ID
     * @return Hl7OrderResult Result containing HL7 text and optional requisition data
     * @throws Hl7OrderGenerationException On generation failure
     */
    public function generate(int $orderId): Hl7OrderResult;

    /**
     * Send the generated HL7 order to the procedure provider.
     *
     * @param  int    $providerId Procedure provider ID (ppid)
     * @param  string $hl7        The HL7 message text
     * @return string Error message, or empty string on success
     */
    public function send(int $providerId, string $hl7): string;

    /**
     * Load payer/insurance information for a patient.
     *
     * @param  int    $patientId Patient ID
     * @param  string $date      Optional date (YYYY-MM-DD), defaults to today
     * @return array<mixed>  Array of payer information
     */
    public function loadPayerInfo(int $patientId, string $date = ''): array;
}
