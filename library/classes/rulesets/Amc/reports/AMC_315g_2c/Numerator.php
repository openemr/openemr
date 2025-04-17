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

class AMC_315g_2c_Numerator implements AmcFilterIF, IAmcItemizedReport
{
    const ACTION_LABEL = "pt_hlth_access";
    const ACTION_DETAILS_KEY_ACCESS_GRANTED = 'access_granted';
    const ACTION_DETAILS_KEY_API_DISABLED = 'api_disabled';
    const ACTION_DETAILS_KEY_PATIENT_OPT_OUT = 'patient_opt_out';
    const ACTION_DETAILS_KEY_MISSING_CREDENTIALS = 'missing_creds';

    private $lastTestActionData;

    public function __construct()
    {
        $this->lastTestActionData = new AmcItemizedActionData();
    }

    public function getTitle()
    {
        return "AMC_315g_2c Numerator";
    }

    public function isValidPatient($date_created, $prevent_portal_access, $beginDate, $endDate)
    {
        if (!empty($date_created)) {
            $creationDate = strtotime($date_created);
            $beginDate = strtotime($beginDate);
            $endDate = strtotime($endDate);
            // creation date for the credentials was within the valid date boundary that we wanted
            if ($creationDate >= $beginDate && $creationDate <= $endDate) {
                $this->lastTestActionData->addNumeratorActionData(
                    self::ACTION_LABEL,
                    true,
                    ['type' => self::ACTION_DETAILS_KEY_ACCESS_GRANTED, 'date' => $date_created]
                );
                return true;
            }
        }
        // we don't worry about casing here
        if (is_string($prevent_portal_access) && strtolower($prevent_portal_access) == "yes") {
            $this->lastTestActionData->addNumeratorActionData(self::ACTION_LABEL, true, ['type' => self::ACTION_DETAILS_KEY_PATIENT_OPT_OUT]);
            // patient opted out of 3rd party portal access which makes them then eligible for 2c criteria
            // NOTE if we certify (e)(1) then this will no longer be valid and View, Download, Transmit (VDT) will need
            // to be checked alongside this condition.
            return true;
        }
        // no details as they just didn't have access
        $this->lastTestActionData->addNumeratorActionData(self::ACTION_LABEL, false, '');
        // no credentials generated, and they are enrolled in api access.
        return false;
    }

    /**
     * Checks if the patient in the given report date had access during the beginDate, endDate
     * @param AmcPatient $patient The patient we are checking for patient access
     * @param $beginDate The report start date (if none is provided this is the patient's DOB).
     * @param $endDate The report end date
     * @return bool True if the test passes, false, if it does not
     */
    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        $fhir_api = $GLOBALS['rest_fhir_api'] ?? '0';
        $patient_api = $GLOBALS['rest_portal_api'] ?? '0';

        // if either the fhir api or the patient api is disabled, then we must fail the measure as no patient
        // fhir api access is available.
        if ($fhir_api === '0' || $patient_api === '0') {
            $this->lastTestActionData->addNumeratorActionData(
                self::ACTION_LABEL,
                false,
                ['type' => self::ACTION_DETAILS_KEY_API_DISABLED, 'fhir' => 0, 'portal' => 0]
            );
            return false;
        }

        // now we need to check whether patient portal is allowed and that patient credentials have been generated
        // which give patient's access to their data.

        // patient_data.prevent_api_access -> IF 'YES' patient opted out and can still be counted for the period
        // patient_onsite_access.date_created -> IF EXISTS AND date_created BETWEEN $beginDate and $endDate then true

        $sql = "SELECT pd.prevent_portal_apps, poa.date_created FROM patient_data pd "
        . " LEFT JOIN patient_access_onsite poa ON pd.pid = poa.pid WHERE pd.pid = ?";

        $numeratorData = QueryUtils::fetchRecords($sql, [$patient->id]);
        if (empty($numeratorData)) {
            $this->lastTestActionData->addNumeratorActionData(
                self::ACTION_LABEL,
                false,
                ['type' => self::ACTION_DETAILS_KEY_MISSING_CREDENTIALS]
            );
            return false;
        }
        return $this->isValidPatient($numeratorData[0]['date_created'], $numeratorData[0]['prevent_portal_apps'], $beginDate, $endDate);
    }

    /**
     * Returns the itemized data results as a hashmap of action_id => 0|1 where 0 is failed and 1 is passed
     * @return array
     */
    public function getItemizedDataForLastTest(): AmcItemizedActionData
    {
        return $this->lastTestActionData;
    }

    /**
     * Returns the hydrated (language translated) data record that came from the itemized data record
     * @return AmcItemizedActionData
     */
    public function hydrateItemizedDataFromRecord($actionData): AmcItemizedActionData
    {
        $label = xl("Patient Health Information is Available to Access via API within 48 hours (Medicaid) or 4 business days (MIPS)");
        $result = new AmcItemizedActionData();
        foreach ($actionData as $key => $data) {
            if ($key == self::ACTION_LABEL) {
                $details = $this->parseDetailsToString($data['details'] ?? []);
                $result->addNumeratorActionData($key, $data['value'] ?? false, $details, $label);
            }
        }
        return $result;
    }
    /*
     * This function lets us have language translation as well as interpreting any specific rule item data that is needed.
     */
    private function parseDetailsToString($details)
    {
        $newDetails = '';
        $type = $details['type'] ?? '';
        if ($type == self::ACTION_DETAILS_KEY_API_DISABLED) {
            $newDetails = xl("Patient API access is disabled");
        } else if ($type == self::ACTION_DETAILS_KEY_ACCESS_GRANTED) {
            $newDetails = xl("Patient has automatic access to patient data since API credentials were generated on") . " "
                . $details['date'];
        } else if ($type == self::ACTION_DETAILS_KEY_PATIENT_OPT_OUT) {
            $newDetails = xl("Patient opted out of 3rd party api access");
        } else if ($type == self::ACTION_DETAILS_KEY_MISSING_CREDENTIALS) {
            $newDetails = xl("API Credentials were not generated");
        }
        return $newDetails;
    }
}
