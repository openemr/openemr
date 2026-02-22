<?php

/**
 * Result object returned by gen_hl7_order functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Orders;

/**
 * Immutable result object containing HL7 order data and optional lab-specific requisition data.
 */
class Hl7OrderResult
{
    /**
     * @param string $hl7 The generated HL7 message
     * @param string $requisitionData Lab-specific requisition data (e.g., LabCorp 2D barcode data), empty string for most labs
     */
    public function __construct(
        public readonly string $hl7,
        public readonly string $requisitionData = ''
    ) {
    }
}
