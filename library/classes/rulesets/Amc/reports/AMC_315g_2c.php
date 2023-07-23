<?php

/**
 *
 * AMC_315g_2c handles the population calculation for §170.315 (g)(2) Required Test 2c which has
 * the following requirement from MIPS for Required Test 2:
 *
 * For at least one unique patient seen by the MIPS eligible clinician: (1) The
 * patient (or the patient-authorized representative) is provided timely access to
 * view online, download, and transmit his or her health information; and (2) The
 * MIPS eligible clinician ensures the patient's health information is available for
 * the patient (or patient-authorized representative) to access using any
 * application of their choice that is configured to meet the technical specifications
 * of the Application Programming Interface (API) in the MIPS eligible clinician's
 * certified electronic health record technology (CEHRT).
 *
 * We currently only support the ONC Required Test 2c version of this requirement as we do not support
 * the View, Download, Transmit (VDT) of §170.315 (e)(1) from the patient portal.  Only clinicians can VDT on behalf
 * of a patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright 2022 Discover and Change, Inc.
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class AMC_315g_2c extends AbstractAmcReport
{
    public function getTitle()
    {
        return "AMC_315g_2c";
    }

    public function getObjectToCount()
    {
        return "patients";
    }

    public function createDenominator()
    {
        return new AMC_315g_2c_Denominator($this->_billingFacilityId, $this->_providerId);
    }

    public function createNumerator()
    {
        return new AMC_315g_2c_Numerator();
    }
}
