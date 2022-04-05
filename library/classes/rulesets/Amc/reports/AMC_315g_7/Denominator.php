<?php

/**
 *
 * AMC_315g_7_Denominator handles the denominator population calculation for §170.315 (g)(2) Required Test 7 which is
 * all of the referrals or transitions of care records created by an Eligible Clinician (EC) or Eligible Clinician
 * Group (ECG) during the report period.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright 2022 Discover and Change, Inc.
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class AMC_315g_7_Denominator implements AmcFilterIF, IAmcItemizedReport
{
    const ACTION_LABEL = 'referral_within_period';
    private $actionData;
    public function __construct()
    {
        $this->actionData = new AmcItemizedActionData();
    }

    public function getTitle()
    {
        return "AMC_315g_7 Denominator";
    }

    public function test(AmcPatient $patient, $beginDate, $endDate)
    {
        // this filtering is done in AbstractAmcReport::collectObjects().  See the transitions-out option

        //  (basically needs a referral within the report dates,
        //   which are already filtered for, so all the objects are a positive)
        if (!empty($patient->object)) {
            $createdDate = $patient->object['date'] ?? null;
            if (!empty($patient->object['date'])) {
                $this->actionData->addDenominatorActionData(self::ACTION_LABEL, true, ['created' => $createdDate]);
            } else {
                $this->actionData->addDenominatorActionData(self::ACTION_LABEL, true, '');
            }
        }
        return true;
    }

    /**
     * Returns our action data that we wish to store in the database
     * @return AmcItemizedActionData
     */
    public function getItemizedDataForLastTest(): AmcItemizedActionData
    {
        return $this->actionData;
    }

    /**
     * Returns the hydrated (language translated) data record that came from the itemized data record
     * @return AmcItemizedActionData
     */
    public function hydrateItemizedDataFromRecord($actionData): AmcItemizedActionData
    {
        $label = xl("Transition of Care or Referral Within Reporting/Performance Period");
        $result = new AmcItemizedActionData();
        foreach ($actionData as $key => $data) {
            if ($key == self::ACTION_LABEL) {
                if (!empty($data['created'])) {
                    $details = xl('Referral Date') . ':' . $data['created'];
                } else {
                    $details = '';
                }
                $result->addDenominatorActionData($key, $data['value'] ?? false, $details, $label);
            }
        }
        return $result;
    }
}
