<?php

/**
 *
 * AMC_315g_7_Numerator handles the numerator AMC checks for the number of transitions of care and referrals in the
 * denominator where a summary of care record was created using CEHRT and exchanged electronically.  IN order for
 * the summary of care record to count it must include the problem list, current medication list, and current
 * medication allergy list.  The list of these values can be empty if there is no data on record for the patient of
 * these values.
 *
 * The provider also must have reasonable confirmation that the recipient received the summary of care record.  If
 * a recipient (such as an HIE) requested the summary of care record, the provider must have documentation of the enquiry
 * which can be used to satisfy the confirmation request.
 *
 * Copyright (C) 2015 Ensoftek, Inc
 * Copyright (C) 2022 Discover and Change, Inc.

 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright 2022 Discover and Change, Inc.
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class AMC_315g_7_Numerator implements AmcFilterIF, IAmcItemizedReport
{
    const ACTION_LABEL_CCDA = "send_sum_elec";
    const ACTION_LABEL_CONFIRMED = "send_sum_elec_confirmed";
    const ACTION_DETAILS_KEY_CCDA_CREATED = 'ccda_sent';
    const ACTION_DETAILS_KEY_RECEIPT_CONFIRMED = 'receipt_conf';
    const ACTION_DETAILS_KEY_CCDA_NOT_SENT = 'no_ccda_sent';
    const ACTION_DETAILS_KEY_CCDA_INVALID = 'ccda_invalid';

    private $lastTestActionData;

    public function __construct()
    {
        $this->lastTestActionData = new AmcItemizedActionData();
    }

    public function getTitle()
    {
        return "AMC_315g_7 Numerator";
    }

    /**
     * @param AmcPatient $patient
     * @param $beginDate
     * @param $endDate
     * @return bool
     */
    public function test(AmcPatient $patient, $beginDate, $endDate)
    {

        // essentially we need to check for each referral whether a CCD record was sent electronically.
        // next we need to check if the referral marked that the recipient confirmed receipt...
        // Note if the CCDA is sent via Direct Messaging (such as with EMRDirect) we mark this as meeting the reasonable
        // confirmation requirement as the protocol assures confirmation to the Sender that the message was received.

        // However, if the referral is marked as the CCDA was sent electronically in the referral form but was NOT
        // transmitted via Direct (such as the provider downloaded the CCDA and used a different electronic means) of
        // transmitting the CCDA then we will check the referral form flag as well for the 'confirmation' of the summary
        // of care.

        //The number of transitions of care and referrals in the denominator where a summary of care record was
        // electronically transmitted using CEHRT to a recipient.
        //  (so basically both amc elements of send_sum_amc and send_sum_elec_amc needs to exist)

        // The referring clinician must have reasonable certainty of receipt by the receiving clinician to
        //count the action toward the measure. This may include confirmation of receipt or that a query
        //of the summary of care record has occurred in order to count the action in the numerator.
        // this means send_sum_elec_amc_confirmed needs to exist as well
        $amcElementCCDAValid = amcCollect('send_sum_valid_ccda', $patient->id, 'transactions', $patient->object['id']);
        $amcElementElecSent = amcCollect('send_sum_elec_amc', $patient->id, 'transactions', $patient->object['id']);
        $amcElementConfirmed = amcCollect('send_sum_amc_confirmed', $patient->id, 'transactions', $patient->object['id']);

        // nothing sent so no details needed
        if (empty($amcElementElecSent)) {
            if (empty($amcElementCCDAValid)) {
                $this->lastTestActionData->addNumeratorActionData(self::ACTION_LABEL_CCDA, false, ['type' => self::ACTION_DETAILS_KEY_CCDA_NOT_SENT]);
            } else {
                $this->lastTestActionData->addNumeratorActionData(self::ACTION_LABEL_CCDA, false, ['type' => self::ACTION_DETAILS_KEY_CCDA_INVALID
                    , 'date' => $amcElementCCDAValid['date_completed']]);
            }
        } else {
            $this->lastTestActionData->addNumeratorActionData(
                self::ACTION_LABEL_CCDA,
                true,
                ['type' => self::ACTION_DETAILS_KEY_CCDA_CREATED, 'date' => $amcElementElecSent['date_completed']]
            );
        }

        if (empty($amcElementConfirmed)) {
            $this->lastTestActionData->addNumeratorActionData(self::ACTION_LABEL_CONFIRMED, false, '');
        } else {
            $this->lastTestActionData->addNumeratorActionData(
                self::ACTION_LABEL_CONFIRMED,
                true,
                ['type' => self::ACTION_DETAILS_KEY_RECEIPT_CONFIRMED, 'date' => $amcElementConfirmed['date_completed']]
            );
        }
        if (!empty($amcElementElecSent) && !(empty($amcElementConfirmed))) {
            // we check the problems, allergies, and medications pieces at the time we generate the send_sum_elec_amc
            // when OpenEMR is used to generate the CCDA
            // If a provider is marking these items off on their Referral Form (in transactions) and using some OTHER
            // 2015 CEHRT product for generating / transmitting the CCDA (such as a smart app), or downloading the CCDA
            // and emailing it or some other mechanism, then they are responsible for checking that the CCDA had the
            // required elements.
            return true;
        }
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
        $ccdaLabel = xl("Summary of Care Record (including all CMS required information or indication of none) Created and Transmitted / Exchanged Electronically During Calendar Year");
        $confirmedLabel = xl("Receipt of Summary of Care Record Confirmed During Calendar Year");
        $result = new AmcItemizedActionData();
        foreach ($actionData as $key => $data) {
            $details = $this->parseDetailsToString($data['details'] ?? []);
            if ($key == self::ACTION_LABEL_CCDA) {
                $result->addNumeratorActionData($key, $data['value'] ?? false, $details, $ccdaLabel);
            } else if ($key == self::ACTION_LABEL_CONFIRMED) {
                $result->addNumeratorActionData($key, $data['value'] ?? false, $details, $confirmedLabel);
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
        if ($type == self::ACTION_DETAILS_KEY_CCDA_CREATED) {
            $newDetails = xl('Summary of Care Record Created and Transmitted On') . ' ' . $details['date'];
        } else if ($type == self::ACTION_DETAILS_KEY_RECEIPT_CONFIRMED) {
            $newDetails = xl('Receipt Confirmed On') . ' ' . $details['date'];
        } else if ($type == self::ACTION_DETAILS_KEY_CCDA_NOT_SENT) {
            $newDetails = xl("Summary of Care Document not created electronically for referral");
        } else if ($type == self::ACTION_DETAILS_KEY_CCDA_INVALID) {
            $newDetails = xl("Summary of Care Document created for referral had missing required data or missing notation of no current problem, medication, and/or medication allergy");
        }
        return $newDetails;
    }
}
