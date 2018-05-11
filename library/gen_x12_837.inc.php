<?php
/*
 * This program creates a 5010 837P file
 *
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2009 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/invoice_summary.inc.php");

use OpenEMR\Billing\Claim;

function stripZipCode($zip)
{
    $temp = preg_replace('/[-\s]*/', '', $zip);
    if (strlen($temp) == 5) {
        return $temp . "9999";
    } else {
        return $temp;
    }
}

function gen_x12_837($pid, $encounter, &$log, $encounter_claim = false)
{
    $today = time();
    $out = '';
    $claim = new Claim($pid, $encounter);
    $edicount = 0;
    $HLcount = 0;

    $log .= "Generating claim $pid" . "-" . $encounter . " for " .
    $claim->patientFirstName() . ' ' .
    $claim->patientMiddleName() . ' ' .
    $claim->patientLastName() . ' on ' .
    date('Y-m-d H:i', $today) . ".\n";

    $out .= "ISA" .
    "*" . $claim->x12gsisa01() .
    "*" . $claim->x12gsisa02() .
    "*" . $claim->x12gsisa03() .
    "*" . $claim->x12gsisa04() .
    "*" . $claim->x12gsisa05() .
    "*" . $claim->x12gssenderid() .
    "*" . $claim->x12gsisa07() .
    "*" . $claim->x12gsreceiverid() .
    "*" . "030911" .  // dummy data replace by billing_process.php
    "*" . "1630" . // ditto
    "*" . "^" .
    "*" . "00501" .
    "*" . "000000001" .
    "*" . $claim->x12gsisa14() .
    "*" . $claim->x12gsisa15() .
    "*:" .
    "~\n";

    $out .= "GS" .
    "*" . "HC" .
    "*" . $claim->x12gsgs02() .
    "*" . trim($claim->x12gs03()) .
    "*" . date('Ymd', $today) .
    "*" . date('Hi', $today) .
    "*" . "1" .
    "*" . "X" .
    "*" . $claim->x12gsversionstring() .
    "~\n";

    ++$edicount;
    $out .= "ST" .
    "*" . "837" .
    "*" . "0021" .
    "*" . $claim->x12gsversionstring() .
    "~\n";

    ++$edicount;
    $out .= "BHT" .
    "*" . "0019" .                             // 0019 is required here
    "*" . "00" .                               // 00 = original transmission
    "*" . "0123" .                             // reference identification
    "*" . date('Ymd', $today) .           // transaction creation date
    "*" . date('Hi', $today) .            // transaction creation time
    "*" . ($encounter_claim ? "RP" : "CH") .  // RP = reporting, CH = chargeable
    "~\n";

    ++$edicount;
    if ($claim->federalIdType() == "SY") { // check entity type for NM*102 1 == person, 2 == non-person entity
        $firstName = $claim->providerFirstName();
        $lastName = $claim->providerLastName();
        $middleName = $claim->providerMiddleName();
        $suffixName = $claim->providerSuffixName();
        $out .= "NM1" . // Loop 1000A Submitter
        "*" . "41" .
        "*" . "1" .
        "*" . $lastName .
        "*" . $firstName .
        "*" . $middleName .
        "*" . // Name Prefix not used
        "*" . $suffixName .
        "*" . "46";
    } else {
        $billingFacilityName = substr($claim->billingFacilityName(), 0, 60);
        if ($billingFacilityName == '') {
            $log .= "*** billing facility name in 1000A loop is empty\n";
        }
        $out .= "NM1" .
        "*" . "41" .
        "*" . "2" .
        "*" . $billingFacilityName .
        "*" .
        "*" .
        "*" .
        "*" .
        "*" . "46";
    }
    $out .= "*" . $claim->billingFacilityETIN();
    $out .= "~\n";

    ++$edicount;
    $out .= "PER" . // Loop 1000A, Submitter EDI contact information
    "*" . "IC" .
    "*" . $claim->billingContactName() .
    "*" . "TE" .
    "*" . $claim->billingContactPhone() .
    "*" . "EM" .
    "*" . $claim->billingContactEmail();
    $out .= "~\n";

    ++$edicount;
    $out .= "NM1" . // Loop 1000B Receiver
    "*" . "40" .
    "*" . "2" .
    "*" . $claim->clearingHouseName() .
    "*" .
    "*" .
    "*" .
    "*" .
    "*" . "46" .
    "*" . $claim->clearingHouseETIN() .
    "~\n";

    ++$HLcount;
    ++$edicount;
    $out .= "HL" . // Loop 2000A Billing/Pay-To Provider HL Loop
    "*" . $HLcount .
    "*" .
    "*" . "20" .
    "*" . "1" . // 1 indicates there are child segments
    "~\n";

    $HLBillingPayToProvider = $HLcount++;

    // Situational PRV segment for provider taxonomy.
    if ($claim->facilityTaxonomy()) {
        ++$edicount;
        $out .= "PRV" .
        "*" . "BI" .
        "*" . "PXC" .
        "*" . $claim->facilityTaxonomy() .
        "~\n";
    }

    // Situational CUR segment (foreign currency information) omitted here.
    ++$edicount;
    if ($claim->federalIdType() == "SY") { // check for entity type like in 1000A
        $firstName = $claim->providerFirstName();
        $lastName = $claim->providerLastName();
        $middleName = $claim->providerMiddleName();
        $out .= "NM1" .
        "*" . "85" .
        "*" . "1" .
        "*" . $lastName .
        "*" . $firstName .
        "*" . $middleName .
        "*" . // Name Prefix not used
        "*";
    } else {
        $billingFacilityName = substr($claim->billingFacilityName(), 0, 60);
        if ($billingFacilityName == '') {
            $log .= "*** billing facility name in 2010A loop is empty\n";
        }
        $out .= "NM1" . // Loop 2010AA Billing Provider
        "*" . "85" .
        "*" . "2" .
        "*" . $billingFacilityName .
        "*" .
        "*" .
        "*" .
        "*";
    }
    if ($claim->billingFacilityNPI()) {
        $out .= "*XX*" . $claim->billingFacilityNPI();
    } else {
        $log .= "*** Billing facility has no NPI.\n";
    }
    $out .= "~\n";

    ++$edicount;
    $out .= "N3" .
    "*" . $claim->billingFacilityStreet() .
    "~\n";

    ++$edicount;
    $out .= "N4" .
    "*" . $claim->billingFacilityCity() .
    "*" . $claim->billingFacilityState() .
    "*" . stripZipCode($claim->billingFacilityZip()) .
    "~\n";

    if ($claim->billingFacilityNPI() && $claim->billingFacilityETIN()) {
        ++$edicount;
        $out .= "REF";
        if ($claim->federalIdType()) {
            $out .= "*" . $claim->federalIdType();
        } else {
            $out .= "*EI"; // For dealing with the situation before adding TaxId type In facility.
        }
        $out .= "*" . $claim->billingFacilityETIN() . "~\n";
    } else {
        $log .= "*** No billing facility NPI and/or ETIN.\n";
    }
    if ($claim->providerNumberType() && $claim->providerNumber() && !$claim->billingFacilityNPI()) {
        ++$edicount;
        $out .= "REF" .
            "*" . $claim->providerNumberType() .
            "*" . $claim->providerNumber() .
            "~\n";
    } else if ($claim->providerNumber() && !$claim->providerNumberType()) {
        $log .= "*** Payer-specific provider insurance number is present but has no type assigned.\n";
    }

    // Situational PER*1C segment omitted.

    // Pay-To Address defaults to billing provider and is no longer required in 5010 but may be useful
    if ($claim->facilityStreet() != $claim->billingFacilityStreet()) {
        ++$edicount;
        $billingFacilityName = substr($claim->billingFacilityName(), 0, 60);
        $out .= "NM1" .       // Loop 2010AB Pay-To Provider
            "*" . "87" .
            "*" . "2" .
            "*" . $billingFacilityName .
            "*" .
            "*" .
            "*" .
            "*";
        if ($claim->billingFacilityNPI()) {
            $out .= "*XX*" . $claim->billingFacilityNPI();
        }
        $out .= "~\n";

        ++$edicount;
        $out .= "N3" .
            "*" . $claim->billingFacilityStreet() .
            "~\n";

        ++$edicount;
        $out .= "N4" .
            "*" . $claim->billingFacilityCity() .
            "*" . $claim->billingFacilityState() .
            "*" . stripZipCode($claim->billingFacilityZip()) .
            "~\n";
    }

    // Loop 2010AC Pay-To Plan Name omitted.  Includes:
    // NM1*PE, N3, N4, REF*2U, REF*EI

    $PatientHL = $claim->isSelfOfInsured() ? 0 : 1;
    $HLSubscriber = $HLcount++;

    ++$edicount;
    $out .= "HL" .        // Loop 2000B Subscriber HL Loop
        "*" . $HLSubscriber .
        "*" . $HLBillingPayToProvider .
        "*" . "22" .
        "*" . $PatientHL .
        "~\n";

    if (!$claim->payerSequence()) {
        $log .= "*** Error: Insurance information is missing!\n";
    }

    ++$edicount;
    $out .= "SBR" .    // Subscriber Information
        "*" . $claim->payerSequence() .
        "*" . ($claim->isSelfOfInsured() ? '18' : '') .
        "*" . $claim->groupNumber() .
        "*" . $claim->groupName() .
        "*" . $claim->insuredTypeCode() . // applies for secondary medicare
        "*" .
        "*" .
        "*" .
        "*" . $claim->claimType() .
        "~\n";

    // Segment PAT omitted.

    ++$edicount;
    $out .= "NM1" .       // Loop 2010BA Subscriber
        "*" . "IL" .
        "*" . "1" . // 1 = person, 2 = non-person
        "*" . $claim->insuredLastName() .
        "*" . $claim->insuredFirstName() .
        "*" . $claim->insuredMiddleName() .
        "*" .
        "*" . // Name Suffix not used
        "*" . "MI" .
        // "MI" = Member Identification Number
        // "II" = Standard Unique Health Identifier, "Required if the
        //        HIPAA Individual Patient Identifier is mandated use."
        //        Here we presume that is not true yet.
        "*" . $claim->policyNumber() .
        "~\n";

    // For 5010, further subscriber info is sent only if they are the patient.
    if ($claim->isSelfOfInsured()) {
        ++$edicount;
        $out .= "N3" .
            "*" . $claim->insuredStreet() .
            "~\n";

        ++$edicount;
        $out .= "N4" .
            "*" . $claim->insuredCity() .
            "*" . $claim->insuredState() .
            "*" . stripZipCode($claim->insuredZip()) .
            "~\n";

        ++$edicount;
        $out .= "DMG" .
            "*" . "D8" .
            "*" . $claim->insuredDOB() .
            "*" . $claim->insuredSex() .
            "~\n";
    }

    // Segment REF*SY (Subscriber Secondary Identification) omitted.
    // Segment REF*Y4 (Property and Casualty Claim Number) omitted.
    // Segment PER*IC (Property and Casualty Subscriber Contact Information) omitted.

    ++$edicount;
    $payerName = substr($claim->payerName(), 0, 60);
    $out .= "NM1" .       // Loop 2010BB Payer
        "*" . "PR" .
        "*" . "2" .
        "*" . $payerName .
        "*" .
        "*" .
        "*" .
        "*" .
        "*" . "PI" .
        "*" . ($encounter_claim ? $claim->payerAltID() : $claim->payerID()) .
        "~\n";
    if (!$claim->payerID()) {
        $log .= "*** CMS ID is missing for payer '" . $claim->payerName() . "'.\n";
    }

    ++$edicount;
    $out .= "N3" .
        "*" . $claim->payerStreet() .
        "~\n";

    ++$edicount;
    $out .= "N4" .
        "*" . $claim->payerCity() .
        "*" . $claim->payerState() .
        "*" . stripZipCode($claim->payerZip()) .
        "~\n";

    // Segment REF (Payer Secondary Identification) omitted.
    // Segment REF (Billing Provider Secondary Identification) omitted.

    if (!$claim->isSelfOfInsured()) {
        ++$edicount;
        $out .= "HL" .        // Loop 2000C Patient Information
            "*" . $HLcount .
            "*" . $HLSubscriber .
            "*" . "23" .
            "*" . "0" .
            "~\n";

        $HLcount++;
        ++$edicount;
        $out .= "PAT" .
            "*" . $claim->insuredRelationship() .
            "~\n";

        ++$edicount;
        $out .= "NM1" .       // Loop 2010CA Patient
            "*" . "QC" .
            "*" . "1" .
            "*" . $claim->patientLastName() .
            "*" . $claim->patientFirstName();

        if ($claim->patientMiddleName() !== '') {
            $out .= "*" . $claim->patientMiddleName();
        }

        $out .= "~\n";

        ++$edicount;
        $out .= "N3" .
            "*" . $claim->patientStreet() .
            "~\n";

        ++$edicount;
        $out .= "N4" .
            "*" . $claim->patientCity() .
            "*" . $claim->patientState() .
            "*" . stripZipCode($claim->patientZip()) .
            "~\n";

        ++$edicount;
        $out .= "DMG" .
            "*" . "D8" .
            "*" . $claim->patientDOB() .
            "*" . $claim->patientSex() .
            "~\n";

        // Segment REF*Y4 (Property and Casualty Claim Number) omitted.
        // Segment REF (Property and Casualty Patient Identifier) omitted.
        // Segment PER (Property and Casualty Patient Contact Information) omitted.
    } // end of patient different from insured

    $proccount = $claim->procCount();
    $clm_total_charges = 0;
    for ($prockey = 0; $prockey < $proccount; ++$prockey) {
        $clm_total_charges += $claim->cptCharges($prockey);
    }
    if (!$clm_total_charges) {
        $log .= "*** This claim has no charges!\n";
    }

    ++$edicount;
    $out .= "CLM" .    // Loop 2300 Claim
        "*" . $pid . "-" . $encounter .
        "*" . sprintf("%.2f", $clm_total_charges) .
        "*" .
        "*" .
        "*" . sprintf('%02d', $claim->facilityPOS()) . ":" . "B" . ":" . $claim->frequencyTypeCode() .
        "*" . "Y" .
        "*" . "A" .
        "*" . ($claim->billingFacilityAssignment() ? 'Y' : 'N') .
        "*" . "Y" .
        "~\n";

    if ($claim->onsetDate() && ($claim->onsetDate() !== $claim->serviceDate()) && ($claim->onsetDateValid())) {
        ++$edicount;
        $out .= "DTP" .       // Date of Onset
            "*" . "431" .
            "*" . "D8" .
            "*" . $claim->onsetDate() .
            "~\n";
    }

    // above is for historical use of encounter onset date, now in misc_billing_options
    // Segment DTP*431 (Onset of Current Symptoms or Illness)
    // Segment DTP*484 (Last Menstrual Period Date)

    if ($claim->miscOnsetDate() && ($claim->box14Qualifier()) && ($claim->miscOnsetDateValid())) {
        ++$edicount;
        $out .= "DTP" .
            "*" . $claim->box14Qualifier() .
            "*" . "D8" .
            "*" . $claim->miscOnsetDate() .
            "~\n";
    }

    // Segment DTP*304 (Last Seen Date)
    // Segment DTP*453 (Acute Manifestation Date)
    // Segment DTP*439 (Accident Date)
    // Segment DTP*455 (Last X-Ray Date)
    // Segment DTP*471 (Hearing and Vision Prescription Date)
    // Segment DTP*314 (Disability) omitted.
    // Segment DTP*360 (Initial Disability Period Start) omitted.
    // Segment DTP*361 (Initial Disability Period End) omitted.
    // Segment DTP*297 (Last Worked Date)
    // Segment DTP*296 (Authorized Return to Work Date)

    // Segment DTP*454 (Initial Treatment Date)

    if ($claim->dateInitialTreatment() && ($claim->box15Qualifier()) && ($claim->dateInitialTreatmentValid())) {
        ++$edicount;
        $out .= "DTP" .       // Date Last Seen
        "*" . $claim->box15Qualifier() .
        "*" . "D8" .
        "*" . $claim->dateInitialTreatment() .
        "~\n";
    }

    if (strcmp($claim->facilityPOS(), '21') == 0 && $claim->onsetDateValid()) {
        ++$edicount;
        $out .= "DTP" .     // Date of Hospitalization
        "*" . "435" .
        "*" . "D8" .
        "*" . $claim->onsetDate() .
        "~\n";
    }

    // above is for historical use of encounter onset date, now in misc_billing_options
    if (strcmp($claim->facilityPOS(), '21') == 0 && $claim->hospitalizedFromDateValid()) {
        ++$edicount;
        $out .= "DTP" .     // Date of Admission
        "*" . "435" .
        "*" . "D8" .
        "*" . $claim->hospitalizedFrom() .
        "~\n";
    }

    // Segment DTP*096 (Discharge Date)
    if (strcmp($claim->facilityPOS(), '21') == 0 && $claim->hospitalizedToDateValid()) {
        ++$edicount;
        $out .= "DTP" .     // Date of Discharge
        "*" . "96" .
        "*" . "D8" .
        "*" . $claim->hospitalizedTo() .
        "~\n";
    }

    // Segments DTP (Assumed and Relinquished Care Dates) omitted.
    // Segment DTP*444 (Property and Casualty Date of First Contact) omitted.
    // Segment DTP*050 (Repricer Received Date) omitted.
    // Segment PWK (Claim Supplemental Information) omitted.
    // Segment CN1 (Contract Information) omitted.

    $patientpaid = $claim->patientPaidAmount();
    if ($patientpaid != 0) {
        ++$edicount;
        $out .= "AMT" .     // Patient paid amount. Page 190/220.
        "*" . "F5" .
        "*" . $patientpaid .
        "~\n";
    }

    // Segment REF*4N (Service Authorization Exception Code) omitted.
    // Segment REF*F5 (Mandatory Medicare Crossover Indicator) omitted.
    // Segment REF*EW (Mammography Certification Number) omitted.
    // Segment REF*9F (Referral Number) omitted.

    if ($claim->priorAuth()) {
        ++$edicount;
        $out .= "REF" .     // Prior Authorization Number
        "*" . "G1" .
        "*" . $claim->priorAuth() .
        "~\n";
    }

    // Segment REF*F8 Payer Claim Control Number for claim re-submission.icn_resubmission_number
    if (trim($claim->billing_options['icn_resubmission_number']) > 3) {
        ++$edicount;
        error_log("Method 1: " . $claim->billing_options['icn_resubmission_number'], 0);
        $out .= "REF" .
        "*" . "F8" .
        "*" . $claim->icnResubmissionNumber() .
        "~\n";
    }

    if ($claim->cliaCode() && ($claim->claimType() === 'MB')) {
        // Required by Medicare when in-house labs are done.
        ++$edicount;
        $out .= "REF" .     // Clinical Laboratory Improvement Amendment Number
        "*" . "X4" .
        "*" . $claim->cliaCode() .
        "~\n";
    }

    // Segment REF*9A (Repriced Claim Number) omitted.
    // Segment REF*9C (Adjusted Repriced Claim Number) omitted.
    // Segment REF*LX (Investigational Device Exemption Number) omitted.
    // Segment REF*D9 (Claim Identifier for Transmission Intermediaries) omitted.
    // Segment REF*EA (Medical Record Number) omitted.
    // Segment REF*P4 (Demonstration Project Identifier) omitted.
    // Segment REF*1J (Care Plan Oversight) omitted.
    // Segment K3 (File Information) omitted.
    if ($claim->additionalNotes()) {
        // Claim note.
        ++$edicount;
        $out .= "NTE" .     // comments box 19
        "*" . "ADD" .
        "*" . $claim->additionalNotes() .
        "~\n";
    }

    // Segment CR1 (Ambulance Transport Information) omitted.
    // Segment CR2 (Spinal Manipulation Service Information) omitted.
    // Segment CRC (Ambulance Certification) omitted.
    // Segment CRC (Patient Condition Information: Vision) omitted.
    // Segment CRC (Homebound Indicator) omitted.
    // Segment CRC (EPSDT Referral).
    if ($claim->epsdtFlag()) {
        ++$edicount;
        $out .= "CRC" .
        "*" . "ZZ" .
        "*" . "Y" .
        "*" . $claim->medicaidReferralCode() .
        "~\n";
    }

    // Diagnoses, up to $max_per_seg per HI segment.
    $max_per_seg = 12;
    $da = $claim->diagArray();
    if ($claim->diagtype == "ICD9") {
        $diag_type_code = 'BK';
    } else {
        $diag_type_code = 'ABK';
    }
    $tmp = 0;
    foreach ($da as $diag) {
        if ($tmp % $max_per_seg == 0) {
            if ($tmp) {
                $out .= "~\n";
            }
            ++$edicount;
            $out .= "HI";         // Health Diagnosis Codes
        }
        $out .= "*" . $diag_type_code . ":" . $diag;
        if ($claim->diagtype == "ICD9") {
            $diag_type_code = 'BF';
        } else {
            $diag_type_code = 'ABF';
        }
        ++$tmp;
    }

    if ($tmp) {
        $out .= "~\n";
    }

    // Segment HI*BP (Anesthesia Related Procedure) omitted.
    // Segment HI*BG (Condition Information) omitted.
    // Segment HCP (Claim Pricing/Repricing Information) omitted.
    if ($claim->referrerLastName()) {
        // Medicare requires referring provider's name and UPIN.
        ++$edicount;
        $out .= "NM1" .     // Loop 2310A Referring Provider
        "*" . "DN" .
        "*" . "1" .
        "*" . $claim->referrerLastName() .
        "*" . $claim->referrerFirstName() .
        "*" . $claim->referrerMiddleName() .
        "*" .
        "*";
        if ($claim->referrerNPI()) {
            $out .=
            "*" . "XX" .
            "*" . $claim->referrerNPI();
        } else {
            $log .= "*** Referring provider has no NPI.\n";
        }
        $out .= "~\n";
    }

    // Per the implementation guide lines, only include this information if it is different
    // than the Loop 2010AA information
    if ($claim->providerNPIValid() && ($claim->billingFacilityNPI() !== $claim->providerNPI())) {
        ++$edicount;
        $out .= "NM1" .       // Loop 2310B Rendering Provider
        "*" . "82" .
        "*" . "1" .
        "*" . $claim->providerLastName() .
        "*" . $claim->providerFirstName() .
        "*" . $claim->providerMiddleName() .
        "*" .
        "*";
        if ($claim->providerNPI()) {
            $out .=
            "*" . "XX" .
            "*" . $claim->providerNPI();
        } else {
            $log .= "*** Rendering provider has no NPI.\n";
        }
        $out .= "~\n";

        if ($claim->providerTaxonomy()) {
            ++$edicount;
            $out .= "PRV" .
            "*" . "PE" . // Performing provider
            "*" . "PXC" .
            "*" . $claim->providerTaxonomy() .
            "~\n";
        } else {
            $log .= "*** Performing provider has no taxonomy code.\n";
        }
    } else {
        $log .= "*** Rendering provider is billing under a group.\n";
    }
    if (!$claim->providerNPIValid()) {
        // If the loop was skipped because the provider NPI was invalid, generate a warning for the log.
        $log .= "*** Skipping 2310B because " . $claim->providerLastName() . "," . $claim->providerFirstName() . " has invalid NPI.\n";
    }

    if (!$claim->providerNPI() && in_array($claim->providerNumberType(), array('0B', '1G', 'G2', 'LU'))) {
        if ($claim->providerNumber()) {
            ++$edicount;
            $out .= "REF" .
            "*" . $claim->providerNumberType() .
            "*" . $claim->providerNumber() .
            "~\n";
        }
    }
    // End of Loop 2310B

    // Loop 2310C is omitted in the case of home visits (POS=12).
    if ($claim->facilityPOS() != 12 && ($claim->facilityNPI() != $claim->billingFacilityNPI())) {
        ++$edicount;
        $out .= "NM1" .       // Loop 2310C Service Location
        "*" . "77" .
        "*" . "2";
        $facilityName = substr($claim->facilityName(), 0, 60);
        if ($claim->facilityName() || $claim->facilityNPI() || $claim->facilityETIN()) {
            $out .=
            "*" . $facilityName;
        } else {
            $log .= "*** Check for invalid facility name, NPI, and/or tax id.\n";
        }
        if ($claim->facilityNPI() || $claim->facilityETIN()) {
            $out .=
            "*" .
            "*" .
            "*" .
            "*";
            if ($claim->facilityNPI()) {
                $out .=
                "*" . "XX" . "*" . $claim->facilityNPI();
            } else {
                $out .=
                "*" . "24" . "*" . $claim->facilityETIN();
            }
            if (!$claim->facilityNPI()) {
                $log .= "*** Service location has no NPI.\n";
            }
        }

        $out .= "~\n";
        if ($claim->facilityStreet()) {
            ++$edicount;
            $out .= "N3" .
            "*" . $claim->facilityStreet() .
            "~\n";
        }

        if ($claim->facilityState()) {
            ++$edicount;
            $out .= "N4" .
            "*" . $claim->facilityCity() .
            "*" . $claim->facilityState() .
            "*" . stripZipCode($claim->facilityZip()) .
            "~\n";
        }
    }
    // Segment REF (Service Facility Location Secondary Identification) omitted.
    // Segment PER (Service Facility Contact Information) omitted.

    // Loop 2310D, Supervising Provider
    if (! empty($claim->supervisorLastName())) {
        ++$edicount;
        $out .= "NM1" .
        "*" . "DQ" . // Supervising Physician
        "*" . "1" .  // Person
        "*" . $claim->supervisorLastName() .
        "*" . $claim->supervisorFirstName() .
        "*" . $claim->supervisorMiddleName() .
        "*" .   // NM106 not used
        "*";    // Name Suffix not used
        if ($claim->supervisorNPI()) {
            $out .=
            "*" . "XX" .
            "*" . $claim->supervisorNPI();
        } else {
            $log .= "*** Supervising Provider has no NPI.\n";
        }
        $out .= "~\n";

        if ($claim->supervisorNumber()) {
            ++$edicount;
            $out .= "REF" .
            "*" . $claim->supervisorNumberType() .
            "*" . $claim->supervisorNumber() .
            "~\n";
        }
    } else {
        $log .= "*** Supervising provider is empty.\n";
    }

    // Segments NM1*PW, N3, N4 (Ambulance Pick-Up Location) omitted.
    // Segments NM1*45, N3, N4 (Ambulance Drop-Off Location) omitted.

    // Loops 2320 and 2330, other subscriber/payer information.
    // Remember that insurance index 0 is always for the payer being billed
    // by this claim, and 1 and above are always for the "other" payers.

    for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {
        $tmp1 = $claim->claimType($ins);
        $tmp2 = 'C1'; // Here a kludge. See page 321.
        if ($tmp1 === 'CI') {
            $tmp2 = 'C1';
        }
        if ($tmp1 === 'AM') {
            $tmp2 = 'AP';
        }
        if ($tmp1 === 'HM') {
            $tmp2 = 'HM';
        }
        if ($tmp1 === 'MB') {
            $tmp2 = 'MB';
        }
        if ($tmp1 === 'MC') {
            $tmp2 = 'MC';
        }
        if ($tmp1 === '09') {
            $tmp2 = 'PP';
        }

        ++$edicount;
        $out .= "SBR" . // Loop 2320, Subscriber Information - page 297/318
        "*" . $claim->payerSequence($ins) .
        "*" . $claim->insuredRelationship($ins) .
        "*" . $claim->groupNumber($ins) .
        "*" . $claim->groupName($ins) .
        "*" . $claim->insuredTypeCode($ins) .
        "*" .
        "*" .
        "*" .
        "*" . $claim->claimType($ins) .
        "~\n";

        // Things that apply only to previous payers, not future payers.
        if ($claim->payerSequence($ins) < $claim->payerSequence()) {
            // Generate claim-level adjustments.
            $aarr = $claim->payerAdjustments($ins);
            foreach ($aarr as $a) {
                ++$edicount;
                $out .= "CAS" . // Previous payer's claim-level adjustments. Page 301/323.
                "*" . $a[1] .
                "*" . $a[2] .
                "*" . $a[3] .
                "~\n";
            }

            $payerpaid = $claim->payerTotals($ins);
            ++$edicount;
            $out .= "AMT" . // Previous payer's paid amount. Page 307/332.
            "*" . "D" .
            "*" . $payerpaid[1] .
            "~\n";
            // Segment AMT*A8 (COB Total Non-Covered Amount) omitted.
            // Segment AMT*EAF (Remaining Patient Liability) omitted.
        }   // End of things that apply only to previous payers.

        ++$edicount;
        $out .= "OI" .  // Other Insurance Coverage Information. Page 310/344.
        "*" .
        "*" .
        "*" . ($claim->billingFacilityAssignment($ins) ? 'Y' : 'N') .
        // For this next item, the 5010 example in the spec does not match its
        // description.  So this might be wrong.
        "*" .
        "*" .
        "*" .
        "Y" .
        "~\n";

        // Segment MOA (Medicare Outpatient Adjudication) omitted.
        ++$edicount;
        $out .= "NM1" . // Loop 2330A Subscriber info for other insco. Page 315/350.
        "*" . "IL" .
        "*" . "1" .
        "*" . $claim->insuredLastName($ins) .
        "*" . $claim->insuredFirstName($ins) .
        "*" . $claim->insuredMiddleName($ins) .
        "*" .
        "*" .
        "*" . "MI" .
        "*" . $claim->policyNumber($ins) .
        "~\n";

        ++$edicount;
        $out .= "N3" .
        "*" . $claim->insuredStreet($ins) .
        "~\n";

        ++$edicount;
        $out .= "N4" .
        "*" . $claim->insuredCity($ins) .
        "*" . $claim->insuredState($ins) .
        "*" . stripZipCode($claim->insuredZip($ins)) .
        "~\n";

        // Segment REF (Other Subscriber Secondary Identification) omitted.
        ++$edicount;
        $payerName = substr($claim->payerName($ins), 0, 60);
        $out .= "NM1" . // Loop 2330B Payer info for other insco. Page 322/359.
        "*" . "PR" .
        "*" . "2" .
        "*" . $payerName .
        "*" .
        "*" .
        "*" .
        "*" .
        "*" . "PI" .
        "*" . $claim->payerID($ins) .
        "~\n";

        if (!$claim->payerID($ins)) {
            $log .= "*** CMS ID is missing for payer '" . $claim->payerName($ins) . "'.\n";
        }

        ++$edicount;
        $out .= "N3" .
        "*" . $claim->payerStreet($ins) .
        "~\n";

        ++$edicount;
        $out .= "N4" .
        "*" . $claim->payerCity($ins) .
        "*" . $claim->payerState($ins) .
        "*" . stripZipCode($claim->payerZip($ins)) .
        "~\n";
        // Segment DTP*573 (Claim Check or Remittance Date) omitted.
        // Segment REF (Other Payer Secondary Identifier) omitted.
        // Segment REF*G1 (Other Payer Prior Authorization Number) omitted.
        // Segment REF*9F (Other Payer Referral Number) omitted.
        // Segment REF*T4 (Other Payer Claim Adjustment Indicator) omitted.
        // Segment REF*F8 (Other Payer Claim Control Number) omitted.
        // Segment NM1 (Other Payer Referring Provider) omitted.
        // Segment REF (Other Payer Referring Provider Secondary Identification) omitted.
        // Segment NM1 (Other Payer Rendering Provider) omitted.
        // Segment REF (Other Payer Rendering Provider Secondary Identification) omitted.
        // Segment NM1 (Other Payer Service Facility Location) omitted.
        // Segment REF (Other Payer Service Facility Location Secondary Identification) omitted.
        // Segment NM1 (Other Payer Supervising Provider) omitted.
        // Segment REF (Other Payer Supervising Provider Secondary Identification) omitted.
        // Segment NM1 (Other Payer Billing Provider) omitted.
        // Segment REF (Other Payer Billing Provider Secondary Identification) omitted.
    } // End loops 2320/2330*.

    $loopcount = 0;

    // Loop 2400 Procedure Loop.
    //

    for ($prockey = 0; $prockey < $proccount; ++$prockey) {
        ++$loopcount;
        ++$edicount;
        $out .= "LX" .      // Segment LX, Service Line. Page 398.
        "*" . $loopcount .
        "~\n";

        ++$edicount;
        $out .= "SV1" .     // Segment SV1, Professional Service. Page 400.
        "*" . "HC:" . $claim->cptKey($prockey) .
        "*" . sprintf('%.2f', $claim->cptCharges($prockey)) .
        "*" . "UN" .
        "*" . $claim->cptUnits($prockey) .
        "*" .
        "*" .
        "*";
        $dia = $claim->diagIndexArray($prockey);
        $i = 0;
        foreach ($dia as $dindex) {
            if ($i) {
                $out .= ':';
            }

            $out .= $dindex;
            if (++$i >= 4) {
                break;
            }
        }

        # needed for epstd
        if ($claim->epsdtFlag()) {
            $out .= "*" .
            "*" .
            "*" .
            "*" . "Y" .
            "~\n";
        } else {
            $out .= "~\n";
        }

        if (!$claim->cptCharges($prockey)) {
            $log .= "*** Procedure '" . $claim->cptKey($prockey) . "' has no charges!\n";
        }

        if (empty($dia)) {
            $log .= "*** Procedure '" . $claim->cptKey($prockey) . "' is not justified!\n";
        }

        // Segment SV5 (Durable Medical Equipment Service) omitted.
        // Segment PWK01 (Line Supplemental Information) omitted.
        // Segment CR1 (Ambulance Transport Information) omitted.
        // Segment CR3 (Durable Medical Equipment Certification) omitted.
        // Segment CRC (Ambulance Certification) omitted.
        // Segment CRC (Hospice Employee Indicator) omitted.
        // Segment CRC (Condition Indicator / Durable Medical Equipment) omitted.

        ++$edicount;
        $out .= "DTP" .     // Date of Service. Page 435.
        "*" . "472" .
        "*" . "D8" .
        "*" . $claim->serviceDate() .
        "~\n";

        $testnote = rtrim($claim->cptNotecodes($prockey));
        if (!empty($testnote)) {
            ++$edicount;
            $out .= "NTE" .     // Explain Unusual Circumstances.
            "*" . "ADD" .
            "*" . $claim->cptNotecodes($prockey) .
            "~\n";
        }

        // Segment DTP*471 (Prescription Date) omitted.
        // Segment DTP*607 (Revision/Recertification Date) omitted.
        // Segment DTP*463 (Begin Therapy Date) omitted.
        // Segment DTP*461 (Last Certification Date) omitted.
        // Segment DTP*304 (Last Seen Date) omitted.
        // Segment DTP (Test Date) omitted.
        // Segment DTP*011 (Shipped Date) omitted.
        // Segment DTP*455 (Last X-Ray Date) omitted.
        // Segment DTP*454 (Initial Treatment Date) omitted.
        // Segment QTY (Ambulance Patient Count) omitted.
        // Segment QTY (Obstetric Anesthesia Additional Units) omitted.
        // Segment MEA (Test Result) omitted.
        // Segment CN1 (Contract Information) omitted.
        // Segment REF*9B (Repriced Line Item Reference Number) omitted.
        // Segment REF*9D (Adjusted Repriced Line Item Reference Number) omitted.
        // Segment REF*G1 (Prior Authorization) omitted.
        // Segment REF*6R (Line Item Control Number) omitted.
        //   (Really oughta have this for robust 835 posting!)
        // Segment REF*EW (Mammography Certification Number) omitted.
        // Segment REF*X4 (CLIA Number) omitted.
        // Segment REF*F4 (Referring CLIA Facility Identification) omitted.
        // Segment REF*BT (Immunization Batch Number) omitted.
        // Segment REF*9F (Referral Number) omitted.
        // Segment AMT*T (Sales Tax Amount) omitted.
        // Segment AMT*F4 (Postage Claimed Amount) omitted.
        // Segment K3 (File Information) omitted.
        // Segment NTE (Line Note) omitted.
        // Segment NTE (Third Party Organization Notes) omitted.
        // Segment PS1 (Purchased Service Information) omitted.
        // Segment HCP (Line Pricing/Repricing Information) omitted.

    // Loop 2410, Drug Information. Medicaid insurers seem to want this
    // with HCPCS codes.
    //
        $ndc = $claim->cptNDCID($prockey);

        if ($ndc) {
            ++$edicount;
            $out .= "LIN" . // Drug Identification. Page 500+ (Addendum pg 71).
            "*" .         // Per addendum, LIN01 is not used.
            "*" . "N4" .
            "*" . $ndc .
            "~\n";

            if (!preg_match('/^\d\d\d\d\d-\d\d\d\d-\d\d$/', $ndc, $tmp) && !preg_match('/^\d{11}$/', $ndc)) {
                $log .= "*** NDC code '$ndc' has invalid format!\n";
            }

            ++$edicount;
            $out .= "CTP" . // Drug Pricing. Page 500+ (Addendum pg 74).
            "*" .
            "*" .
            "*" .
            "*" . $claim->cptNDCQuantity($prockey) .
            "*" . $claim->cptNDCUOM($prockey) .
            // Note: 5010 documents "ME" (Milligrams) as an additional unit of measure.
            "~\n";

            // Segment REF (Prescription or Compound Drug Association Number) omitted.
        }


    // Loop 2420A, Rendering Provider (service-specific).
    // Used if the rendering provider for this service line is different
    // from that in loop 2310B.

        if ($claim->providerNPI() != $claim->providerNPI($prockey)) {
            ++$edicount;
            $out .= "NM1" .       // Loop 2420A Rendering Provider
            "*" . "82" .
            "*" . "1" .
            "*" . $claim->providerLastName($prockey) .
            "*" . $claim->providerFirstName($prockey) .
            "*" . $claim->providerMiddleName($prockey) .
            "*" .
            "*";
            if ($claim->providerNPI($prockey)) {
                $out .=
                "*" . "XX" .
                "*" . $claim->providerNPI($prockey);
            } else {
                $log .= "*** Rendering provider has no NPI.\n";
            }
            $out .= "~\n";

            // Segment PRV*PE (Rendering Provider Specialty Information) .

            if ($claim->providerTaxonomy($prockey)) {
                ++$edicount;
                $out .= "PRV" .
                "*" . "PE" . // PErforming provider
                "*" . "PXC" .
                "*" . $claim->providerTaxonomy($prockey) .
                "~\n";
            }

            // Segment REF (Rendering Provider Secondary Identification).
            // REF*1C is required here for the Medicare provider number if NPI was
            // specified in NM109.  Not sure if other payers require anything here.

            if ($claim->providerNumberType($prockey) == "G2") {
                ++$edicount; $out .= "REF" . "*" . $claim->providerNumberType($prockey) .
                "*" . $claim->providerNumber($prockey) . "~\n";
            }
        } // end provider exception

        // Segment NM1 (Loop 2420B Purchased Service Provider Name) omitted.
        // Segment REF (Loop 2420B Purchased Service Provider Secondary Identification) omitted.
        // Segment NM1,N3,N4 (Loop 2420C Service Facility Location) omitted.
        // Segment REF (Loop 2420C Service Facility Location Secondary Identification) omitted.
        // Segment NM1 (Loop 2420D Supervising Provider Name) omitted.
        // Segment REF (Loop 2420D Supervising Provider Secondary Identification) omitted.

        // Loop 2420E, Ordering Provider.
        // for Medicare DME claims esp @joe on chat.open-emr.org :)

        if ($claim->Box17Qualifier() == "DK" && ($claim->claimType() === 'MB')) {
            ++$edicount;
            $out .= "NM1" .
                "*" . $claim->Box17Qualifier() .
                "*" . "1" .
                "*" . $claim->billingProviderLastName() .
                "*" . $claim->billingProviderFirstName() .
                "*" . $claim->billingProviderMiddleName() .
                "*" .
                "*";
            if ($claim->billingProviderNPI()) {
                $out .=
                    "*" . "XX" .
                    "*" . $claim->billingProviderNPI();
            } else {
                $log .= "*** Ordering provider has no NPI.\n";
            }
            $out .= "~\n";

            ++$edicount;
            $out .= "N3" .
                "*" . $claim->billingProviderStreet() .
                "*" . $claim->billingProviderStreetB() .
                "~\n";

            ++$edicount;
            $out .= "N4" .
                "*" . $claim->billingProviderCity() .
                "*" . $claim->billingProviderState() .
                "*" . stripZipCode($claim->billingProviderZip()) .
                "~\n";
            // Segment REF (Ordering Provider Secondary Identification) omitted.
            // Segment PER (Ordering Provider Contact Information) omitted.
        }


        // Segment NM1 (Referring Provider Name) omitted.
        // Segment REF (Referring Provider Secondary Identification) omitted.
        // Segments NM1*PW, N3, N4 (Ambulance Pick-Up Location) omitted.
        // Segments NM1*45, N3, N4 (Ambulance Drop-Off Location) omitted.

    // Loop 2430, adjudication by previous payers.
    //

        for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {
            if ($claim->payerSequence($ins) > $claim->payerSequence()) {
                continue; // payer is future, not previous
            }

            $payerpaid = $claim->payerTotals($ins, $claim->cptKey($prockey));
            $aarr = $claim->payerAdjustments($ins, $claim->cptKey($prockey));

            if ($payerpaid[1] == 0 && !count($aarr)) {
                $log .= "*** Procedure '" . $claim->cptKey($prockey) .
                    "' has no payments or adjustments from previous payer!\n";
                continue;
            }

            ++$edicount;
            $out .= "SVD" . // Service line adjudication. Page 554.
            "*" . $claim->payerID($ins) .
            "*" . $payerpaid[1] .
            "*" . "HC:" . $claim->cptKey($prockey) .
            "*" .
            "*" . $claim->cptUnits($prockey) .
            "~\n";

            $tmpdate = $payerpaid[0];
            foreach ($aarr as $a) {
                ++$edicount;
                $out .= "CAS" . // Previous payer's line level adjustments. Page 558.
                "*" . $a[1] .
                "*" . $a[2] .
                "*" . $a[3] .
                "~\n";
                if (!$tmpdate) {
                    $tmpdate = $a[0];
                }
            }

            if ($tmpdate) {
                ++$edicount;
                $out .= "DTP" . // Previous payer's line adjustment date. Page 493/566.
                "*" . "573" .
                "*" . "D8" .
                "*" . $tmpdate .
                "~\n";
            }

            // Segment AMT*EAF (Remaining Patient Liability) omitted.
            // Segment LQ (Form Identification Code) omitted.
            // Segment FRM (Supporting Documentation) omitted.
        } // end loop 2430
    } // end this procedure

    ++$edicount;
    $out .= "SE" .        // SE Trailer
    "*" . $edicount .
    "*" . "0021" .
    "~\n";

    $out .= "GE" .        // GE Trailer
    "*" . "1" .
    "*" . "1" .
    "~\n";

    $out .= "IEA" .       // IEA Trailer
    "*" . "1" .
    "*" . "000000001" .
    "~\n";

    // Remove any trailing empty fields (delimiters) from each segment.
    $out = preg_replace('/\*+~/', '~', $out);

    $log .= "\n";
    return $out;
}
