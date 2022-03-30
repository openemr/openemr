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

class AMC_315g_7_Numerator implements AmcFilterIF
{
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
        $amcElementElecSent = amcCollect('send_sum_elec_amc', $patient->id, 'transactions', $patient->object['id']);
        $amcElementConfirmed = amcCollect('send_sum_amc_confirmed', $patient->id, 'transactions', $patient->object['id']);

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
}
