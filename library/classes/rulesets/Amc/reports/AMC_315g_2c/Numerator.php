<?php

/**
 *
 * AMC_315g_2c_Numerator handles the numerator population calculation for §170.315 (g)(2) Required Test 2c which has
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

use OpenEMR\Common\Database\QueryUtils;

class AMC_315g_2c_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_315g_2c Numerator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        $fhir_api = $GLOBALS['rest_fhir_api'] ?? '0';
        $patient_api = $GLOBALS['rest_portal_api'] ?? '0';

        // if either the fhir api or the patient api is disabled, then we must fail the measure as no patient
        // fhir api access is available.
        if ($fhir_api === '0' || $patient_api === '0') {
            return false;
        }

        // now we need to check whether patient portal is allowed and that patient credentials have been generated
        // which give patient's access to their data.

        // patient_data.prevent_api_access -> IF 'YES' patient opted out and can still be counted for the period
        // patient_onsite_access.date_created -> IF EXISTS AND date_created BETWEEN $beginDate and $endDate then true

        $sql = "SELECT pd.prevent_portal_apps, poa.date_created FROM patient_data pd "
        . " LEFT JOIN patient_access_onsite poa ON pd.pid = poa.pid WHERE pd.pid = ?";

        $numeratorData = QueryUtils::fetchRecords($sql, [$patient->id]);

        if (!empty($numeratorData['date_created'])) {
            return true;
        }
        // we don't worry about casing here
        if (strtolower($numeratorData['prevent_portal_apps']) != "yes") {
            // patient opted out of 3rd party portal access which makes them then eligible for 2c criteria
            // NOTE if we certify (e)(1) then this will no longer be valid and View, Download, Transmit (VDT) will need
            // to be checked alongside this condition.
            return true;
        }
        // no credentials generated, and they are enrolled in api access.
        return false;
    }
}
