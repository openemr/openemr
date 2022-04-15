<?php

/**
 *
 * AMC_315g_2c_Denominator handles the denominator population calculation for §170.315 (g)(2) Required Test 2c which is
 * all of the patients seen by a Eligible Clinician (EC) or Eligible Clinician Group (ECG) during the
 * report period.  Note to be seen means that the provider/billing facility had to have had some kind of encounter with the patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright 2022 Discover and Change, Inc.
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class AMC_315g_2c_Denominator implements AmcFilterIF, IAmcItemizedReport
{
    const ACTION_LABEL = "dn_pat_seen";
    const ACTION_DETAILS_KEY_SEEN = 'seen';
    const ACTION_DETAILS_KEY_NOT_SEEN = 'not_seen';

    private $lastTestActionData;

    private $billingFacilityId;
    private $providerId;

    public function __construct($billingFacilityId, $providerId)
    {
        $this->billingFacilityId = $billingFacilityId;
        $this->providerId = $providerId;
        $this->lastTestActionData = new AmcItemizedActionData();
    }

    public function getTitle()
    {
        return "AMC_315g_2c Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // dates are already filtered so we don't have to worry about that, but we do need to filter by our denom
        // criteria
        $bind = [$patient->id, $beginDate, $endDate];
        $sql = "SELECT COUNT(*) AS total_cnt FROM form_encounter WHERE pid=? AND form_encounter.date BETWEEN ? AND ?";
        if (!empty($this->providerId) || !empty($this->billingFacilityId)) {
            if (!empty($this->billingFacilityId)) {
                $sql .= " AND billing_facility = ? ";
                $bind[] = $this->billingFacilityId;
            }

            if (!empty($this->providerId)) {
                $sql .= " AND (provider_id = ? OR supervisor_id = ?)";
                $bind[] = $this->providerId;
                $bind[] = $this->providerId;
            }
        }
        $rez = sqlStatementCdrEngine($sql, $bind);
        $result = sqlFetchArray($rez);
        if (!empty($result) && $result['total_cnt'] > 0) {
            $this->lastTestActionData->addDenominatorActionData(
                self::ACTION_LABEL,
                true,
                ['type' => self::ACTION_DETAILS_KEY_SEEN, 'enc' => $result['total_cnt']]
            );
            return true;
        } else {
            $this->lastTestActionData->addDenominatorActionData(
                self::ACTION_LABEL,
                false,
                ['type' => self::ACTION_DETAILS_KEY_NOT_SEEN, 'enc' => 0]
            );
            return false;
        }
    }

    /**
     * Returns our action data that we wish to store in the database
     * @return AmcItemizedActionData
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
        $label = xl("Patient Seen During EHR Reporting Period");
        $result = new AmcItemizedActionData();
        foreach ($actionData as $key => $data) {
            if ($key == self::ACTION_LABEL) {
                $details = $this->parseDetailsToString($data['details'] ?? []);
                $result->addDenominatorActionData($key, $data['value'] ?? false, $details, $label);
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
        if ($type == self::ACTION_DETAILS_KEY_SEEN) {
            $newDetails = xl("Encounters during period") . ':' . $details['enc'];
        } else if ($type == self::ACTION_DETAILS_KEY_NOT_SEEN) {
            $newDetails = xl("Not seen");
        }
        return $newDetails;
    }
}
