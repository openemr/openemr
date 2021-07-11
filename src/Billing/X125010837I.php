<?php

/**
 * X12 837I
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

use OpenEMR\Billing\Claim;

class X125010837I
{
    public function x12Date($frmdate)
    {
        return ('20' . substr($frmdate, 4, 2) . substr($frmdate, 0, 2) . substr($frmdate, 2, 2));
    }

    public $ub04id = array();

    public function generateX12837I($pid, $encounter, &$log, $ub04id)
    {
        $today = time();
        $out = '';
        $claim = new Claim($pid, $encounter);
        $edicount = 0;

        $log .= "Generating 837I claim $pid-$encounter for " .
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
            "*" . $claim->x12_sender_id() .
            "*" . $claim->x12gsisa07() .
            "*" . $claim->x12gsreceiverid() .
            // date of transmission "*030911" .
            "*" . date('Ymd', $today) .
            //Time of transmission "*1630" .
            "*" . date('Hi', $today) .
            "*" . "^" .
            "*" . "00501" .
            "*" . "000000001" .
            "*" . $claim->x12gsisa14() .
            "*" . $claim->x12gsisa15() .
            "*:" .
            "~\n";
        $out .= "GS" .
            "*HC" .
            "*" . $claim->x12gsgs02() .
            "*" . trim($claim->x12gs03()) .
            "*" . date('Ymd', $today) .
            "*" . date('Hi', $today) .
            "*1" .
            "*X" .
            // "*" . $claim->x12gsversionstring() .
            "*" . "005010X223A2" .
            "~\n";
        ++$edicount;
        $out .= "ST" .
            "*" . "837" .
            "*" . "0021" .
            // "*" . $claim->x12gsversionstring() .
            "*" . "005010X223A2" .
            "~\n";
        ++$edicount;
        $out .= "BHT" .
            "*" . "0019" .                             // 0019 is required here
            "*" . "00" .                               // 00 = original transmission
            "*" . "0123" .                             // reference identification
            "*" . date('Ymd', $today) .           // transaction creation date
            "*" . date('Hi', $today) .            // transaction creation time
            ($encounter_claim ? "*RP" : "*CH") .  // RP = reporting, CH = chargeable
            "~\n";

        ++$edicount;
        //Field length is limited to 35. See nucc dataset page 63 www.nucc.org
        $billingFacilityName = substr($claim->billingFacilityName(), 0, 60);
        if ($billingFacilityName == '') {
            $log .= "*** billing facility name in 1000A loop is empty\n";
        }
        $out .= "NM1" .       // Loop 1000A Submitter stays in the 837I
            "*" . "41" .
            "*" . "2" .
            "*" . $billingFacilityName .
            "*" .
            "*" .
            "*" .
            "*" .
            "*" . "46";

        $out .= "*" . $claim->billingFacilityETIN();
        $out .= "~\n";
        ++$edicount;

        $out .= "PER" .
            "*IC" .
            "*" . $claim->billingContactName() .
            "*TE" .
            "*" . $claim->billingContactPhone();
        $out .= "~\n";

        ++$edicount;

        $out .= "NM1" .       // Loop 1000B Receiver stays in the 837I
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
        $HLcount = 1;
        ++$edicount;

        $out .= "HL" .        // Loop 2000A Billing/Pay-To Provider HL Loop
            "*" . "$HLcount" .
            "*" .
            "*" . "20" .
            "*" . "1" .              // 1 indicates there are child segments
            "~\n";
        $HLBillingPayToProvider = $HLcount++;
        // Situational PRV segment (for provider taxonomy code) omitted here.
        // Situational CUR segment (foreign currency information) omitted here.
        ++$edicount;
        //Field length is limited to 35. See nucc dataset page 63 www.nucc.org
        $billingFacilityName = substr($claim->billingFacilityName(), 0, 60);
        $out .= "NM1" .       // Loop 2010AA Billing Provider stays in the 837I
            "*" . "85" .
            "*" . "2" .
            "*" . $billingFacilityName .
            "*" .
            "*" .
            "*" .
            "*";
        if ($claim->billingFacilityNPI()) {
            $out .= "*XX*" . $claim->billingFacilityNPI();
        } else {
            $log .= "*** Billing facility has no NPI.\n";
            $out .= "*XX*";
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
            "*" . $claim->x12Zip($claim->billingFacilityZip()) .
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
        } elseif ($claim->providerNumber() && !$claim->providerNumberType()) {
            $log .= "*** Payer-specific provider insurance number is present but has no type assigned.\n";
        }
        // Situational PER*1C segment.
        ++$edicount;
        $out .= "PER" .
            "*" . "IC" .
            "*" . $claim->billingContactName() .
            "*" . "TE" .
            "*" . $claim->billingContactPhone();
        $out .= "~\n";

        // This is also excluded in the 837I
        // Loop 2010AC Pay-To Plan Name omitted.  Includes:
        // NM1*PE, N3, N4, REF*2U, REF*EI
        $PatientHL = $claim->isSelfOfInsured() ? 0 : 1;
        $HLSubscriber = $HLcount++;
        ++$edicount;

        // loop 2000B
        $out .= "HL" .        // Loop 2000B Subscriber HL Loop
            "*$HLSubscriber" .
            "*$HLBillingPayToProvider" .
            "*" . "22" .
            "*$PatientHL" .
            "~\n";
        if (!$claim->payerSequence()) {
            $log .= "*** Error: Insurance information is missing!\n";
        }
        ++$edicount;

        //SBR01 is either a P or S    SBR02 for care is always 18 "patient"  SBR09 is always MA
        $out .= "SBR" .       // Subscriber Information
            "*" . $claim->payerSequence() .
            "*" . ($claim->isSelfOfInsured() ? '18' : '') .
            "*" . $claim->groupNumber() .
            "*" . (($claim->groupNumber()) ? '' : $claim->groupName()) .
            "*" . $claim->insuredTypeCode() . // applies for secondary medicare
            "*" .
            "*" .
            "*" .
            "*" . $claim->claimType() . // Zirmed replaces this
            "~\n";
        // 2000C Segment PAT omitted.
        ++$edicount;
        $out .= "NM1" .       // Loop 2010BA Subscriber  same in 837I
            "*IL" .
            "*" . "1" . // 1 = person, 2 = non-person
            "*" . $claim->insuredLastName() .
            "*" . $claim->insuredFirstName() .
            "*" . $claim->insuredMiddleName() .
            "*" .
            "*" . // Name Suffix
            "*MI" .
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
                "*" . $claim->x12Zip($claim->insuredZip()) .
                "~\n";
            ++$edicount;
            $out .= "DMG" .
                "*D8" .
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
            "*PR" .
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
            $log .= "*** Payer ID is missing for payer '" . $claim->payerName() . "'.\n";
        }

        ++$edicount;
        $out .= "N3" .
            "*" . $claim->payerStreet() .
            "~\n";
        ++$edicount;
        $out .= "N4" .
            "*" . $claim->payerCity() .
            "*" . $claim->payerState() .
            "*" . $claim->x12Zip($claim->payerZip()) .
            "~\n";

        // Segment REF (Payer Secondary Identification) omitted.
        // Segment REF (Billing Provider Secondary Identification) omitted.


        if (!$claim->isSelfOfInsured()) {
            ++$edicount;
            $out .= "HL" .        // Loop 2000C Patient Information
                "*" . "$HLcount" .
                "*$HLSubscriber" .
                "*23" .
                "*0" .
                "~\n";
            $HLcount++;
            ++$edicount;
            $out .= "PAT" .
                "*" . $claim->insuredRelationship() .
                "~\n";
            ++$edicount;
            $out .= "NM1" .       // Loop 2010CA Patient may need this elsed in to the loop 2000C
                "*QC" .
                "*1" .
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
                "*" . $claim->x12Zip($claim->patientZip()) .
                "~\n";
            ++$edicount;
            $out .= "DMG" .
                "*D8" .
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
        $out .= "CLM" .       // Loop 2300 Claim
            "*" . $pid . "-" . $encounter .
            "*" . sprintf("%.2f", $clm_total_charges) . // Zirmed computes and replaces this
            "*" .
            "*";
        // Service location this need to be bill type from ub form type_of_bill
        if (strlen($ub04id[7]) >= 3) {
            $out .= "*" . substr($ub04id[7], 1, 1) . ":" . substr($ub04id[7], 2, 1) . ":" . substr($ub04id[7], 3, 1);
        }

        $out .= "*" .
            "*" . "A" .
            "*" . ($claim->billingFacilityAssignment() ? 'Y' : 'N') .
            "*" . "Y" .
            "~\n";
        // discharge hour
        if ($ub04id[29]) {
            ++$edicount;
            $out .= "DTP" . // Loop 2300
                "*" . "096" .
                "*" . "TM" .
                "*" . $ub04id[29] .
                "~\n";
        }

        // Statment Dates
        // DTP 434 RD8 (Statment from OR to date)

        if ($ub04id[13]) {
            ++$edicount;

            $tmp = self::x12Date($ub04id[13]);
            $tmp1 = self::x12Date($ub04id[14]);
            $out .= "DTP" . // Loop 2300
                "*434" . "*" . "RD8" . "*" . $tmp . '-' . $tmp1 . "~\n";
        }

        if ($ub04id[13]) {
            ++$edicount;
            $tmp = self::x12Date($ub04id[25]);
            $out .= "DTP" . // Loop 2300
                "*435" . "*" . "DT" . "*" . $tmp . $ub04id[26] . "~\n";
        }

        if (strlen(trim($ub04id[13])) == 0) {
            $log .= "*** Error: No Admission Date Entered!\n";
        }

        // Repricer Received Date
        // DTP 050 D8 (Admission Date and Hour from form)

        // Institutional Claim Code
        // CL1 (Admission Type Code) (Admission Source Code) (Patient Status Code)

        if ($ub04id[27] != "014X") { // Type of bill
            ++$edicount;
            $out .= "CL1" . // Loop 2300
                "*" . $ub04id[27] . "*" . $ub04id[28] . "*" . $ub04id[30] . "~\n";
        }

        // Segment PWK (Claim Supplemental Information) omitted.

        // Segment CN1 (Contract Information) omitted.

        // Patient Estimated Amount Due
        // Check logic

        // $patientpaid = $claim->patientPaidAmount();
        // if ($patientpaid != 0) {
        // ++$edicount;
        // $out .= "AMT" . // Patient paid amount. Page 190/220.
        // "*F5" .
        // "*" . $patientpaid .
        // "~\n";
        // }

        // Segment REF*4N (Service Authorization Exception Code) omitted.
        // Segment REF*9F (Referral Number) omitted.

        // Prior Authorization
        //
        if ($claim->priorAuth()) {
            ++$edicount;
            $out .= "REF" . // Prior Authorization Number
                "*G1" . "*" . $claim->priorAuth() . "~\n";
        }

        // Segment REF*F8 (Payer Claim Control Number) omitted.

        // This may be needed for the UB04 Claim if so change the 'MB' to 'MA'
        // if ($claim->cliaCode() && ($CMS_5010 || $claim->claimType() === 'MB')) {
        // Required by Medicare when in-house labs are done.
        // ++$edicount;
        // $out .= "REF" . // Clinical Laboratory Improvement Amendment Number
        // "*X4" .
        // "*" . $claim->cliaCode() .
        // "~\n";
        // }

        // Segment REF*9A (Repriced Claim Number) omitted.
        // Segment REF*9C (Adjusted Repriced Claim Number) omitted.
        // Segment REF*LX (Investigational Device Exemption Number) omitted.
        // Segment REF*S9 (Claim Identifier for Transmission Intermediaries) omitted.
        // Segment REF*LU (Auto Accident State) omitted.
        // Segment REF*EA (Medical Record Number) omitted.
        // Segment REF*P4 (Demonstration Project Identifier) omitted.
        // Segment REF*G4 (Peer Review Organization PRO Approval Number) omitted.
        // Segment K3 (File Information) omitted.

        if ($claim->additionalNotes()) {
            // Claim Note
            // Has a list of valid codes. Required when PROVIDER deems necessary

            // Billing note.
            // Check to verify I am getting this information on the ub04 form

            ++$edicount;
            $out .= "NTE" . // comments box 19
                "*" . "ADD" . "*" . $claim->additionalNotes() . "~\n";
        }

        // Segment CRC (EPSDT Referral) omitted.
        // Diagnoses, up to $max_per_seg per HI segment. Check this
        $max_per_seg = 18;
        $da = $claim->diagArray();
        $diag_type_code = 'ABK'; // ICD10
        $tmp = 0;
        foreach ($da as $diag) {
            if ($tmp == 1) {
                continue;
            }
            if ($tmp % $max_per_seg == 0) {
                if ($tmp) {
                    $out .= "~\n";
                }
                ++$edicount;
                $out .= "HI"; // Health Diagnosis Codes
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

        // Segment HI*BI (Occurrence Span Information).
        // HI BI (Occurrence Span Code 1) RD8 (Occurrence Span Code Associated Date)
        if ($ub04id[52]) {
            $max_per_seg = 4;
            $diag_type_code = 'BI';
            $tmp = 0;
            $os = 52;
            for ($i = 0; $i <= 3;) {
                if ($tmp % $max_per_seg == 0) {
                    if ($tmp) {
                        $out .= "~\n";
                    }
                    ++$edicount;
                    $out .= "HI"; // Health Diagnosis Codes
                }
                if ($ub04id[$os]) {
                    $out .= "*" . $diag_type_code . ":" . $ub04id[$os++] . ":" . self::x12Date($ub04id[$os++]) . ":" . self::x12Date($ub04id[$os++]);
                    $diag_type_code = 'BI';
                }
                if ($os >= 57) {
                    $os = 67;
                }
                ++$tmp;
                ++$i;
            }
            if ($tmp) {
                $out .= "~\n";
            }
        }

        // Segment HI*BH (Occurrence Information).
        // HI BH (Occurrence Code 1) D8 (Occurrence Code Associated Date)

        if ($ub04id[44]) {
            $max_per_seg = 8;
            $diag_type_code = 'BH';
            $tmp = 0;
            $os = 44;
            for ($i = 0; $i <= 7;) {
                if ($tmp % $max_per_seg == 0) {
                    if ($tmp) {
                        $out .= "~\n";
                    }
                    ++$edicount;
                    $out .= "HI"; // Health Diagnosis Codes
                }
                if ($ub04id[$os]) {
                    $out .= "*" . $diag_type_code . ":" . $ub04id[$os] . ":D8" . ":" . self::x12Date($ub04id[$os++]);
                    $diag_type_code = 'BH';
                }
                if ($os >= 51) {
                    $os = 59;
                }
                ++$tmp;
                ++$i;
            }
            if ($tmp) {
                $out .= "~\n";
            }
        }

        // Segment HI*BE (Value Information).
        // HI BE (Value Code 1) *.* (Value Code Amount)

        if ($ub04id[74]) {
            $max_per_seg = 12;
            $diag_type_code = 'BE';
            $os = 74;
            $tmp = 0;
            for ($i = 0; $i <= 11;) {
                if ($tmp % $max_per_seg == 0) {
                    if ($tmp) {
                        $out .= "~\n";
                    }
                    ++$edicount;
                    $out .= "HI"; // Health Diagnosis Codes
                }
                if ($ub04id[$os]) {
                    // if ($i=1) {
                    $out .= "*" . $diag_type_code . ":" . $ub04id[$os++] . ":" . ":" . self::x12Date($ub04id[$os++]);
                    $diag_type_code = 'BE';
                    // }
                }
                ++$tmp;
                ++$i;
            }
            if ($tmp) {
                $out .= "~\n";
            }
        }

        // Segment HI*BG (Condition Information).
        // HI BG (Condition Code 1)

        if ($ub04id[31]) {
            $max_per_seg = 11;
            $diag_type_code = 'BG';
            $os = 31;
            $tmp = 0;
            for ($i = 0; $i <= 10;) {
                if ($tmp % $max_per_seg == 0) {
                    if ($tmp) {
                        $out .= "~\n";
                    }
                    ++$edicount;
                    $out .= "HI"; // Health Diagnosis Codes
                }
                if ($ub04id[$os]) {
                    // if ($i=1) {
                    $out .= "*" . $diag_type_code . ":" . $ub04id[$os++];
                    $diag_type_code = 'BG';
                    // }
                }

                ++$tmp;
                ++$i;
            }
            if ($tmp) {
                $out .= "~\n";
            }
        }

        // Segment HI*TC (Treatment Code Information).
        // HI TC (Treatment Code 1)
        /* 63a. TREATMENT AUTHORIZATION CODES - PRIMARY PLAN */
        if ($ub04id[319]) {
            $max_per_seg = 3;
            $diag_type_code = 'TC';
            $tmp = 0;

            for ($i = 0; $i <= 2;) {
                if ($tmp % $max_per_seg == 0) {
                    if ($tmp) {
                        $out .= "~\n";
                    }
                    ++$edicount;
                    $out .= "HI"; // Health Diagnosis Codes
                }

                if ($ub04id[319]) {
                    $out .= "*" . $diag_type_code . ":" . $ub04id[319];
                    $diag_type_code = 'TC';
                }
                if ($i = 1) {
                    if ($ub04id[322]) {
                        $out .= "*" . $diag_type_code . ":" . $ub04id[322];
                        $diag_type_code = 'TC';
                    }
                }
                if ($i = 2) {
                    if ($ub04id[325]) {
                        $out .= "*" . $diag_type_code . ":" . $ub04id[325];
                        $diag_type_code = 'TC';
                    }
                }

                ++$tmp;
                ++$i;
            }
            if ($tmp) {
                $out .= "~\n";
            }
        }

        // Segment HCP (Claim Pricing/Repricing Information) omitted.

        // This needs to allow Attending Physician 2310A, Operating Physician Name 2310B, Other Operating Physician Name 2310C
        // and Rendering Provider Name (Rendering Provider Name is futher down)

        if ($ub04id[388]) {
            ++$edicount;
            // Loop 2310A Attending Physician
            $out .= "NM1" . "*71" . "*1" . "*" . $ub04id[388] . "*" . $ub04id[389] . "*" . "*";
            if ($ub04id[379]) { // NPI
                $out .= "*" . "XX" . "*" . $ub04id[379];
            } else {
                $out .= "*" . "*";
                $log .= "*** Attending Physician has no NPI.\n";
            }
            $out .= "~\n";

            if ($ub04id[380]) {
                ++$edicount;
                $out .= "REF" . // Attending Physician Secondary Identification
                    "*" . $ub04id[380] . "*" . $ub04id[381] . "~\n";
            }
        }

        // 2310B

        if ($ub04id[400]) {
            ++$edicount;

            $out .= "NM1" . // Loop 2310B operating Physician
                "*72" . "*1" . "*" . $ub04id[400] . "*" . $ub04id[400] . "*" . "*";
            if ($ub04id[390]) {
                $out .= "*" . "XX" . "*" . $ub04id[390];
            } else {
                $out .= "*" . "*";
                $log .= "*** Operating Physician has no NPI qualifier.\n";
            }
            $out .= "~\n";

            if ($ub04id[391]) {
                ++$edicount;
                $out .= "REF" . // operating Physician Secondary Identification
                    "*" . $claim->$ub04id[391] . "*" . $ub04id[392] . "~\n";
            }
        }

        // 2310C

        if ($ub04id[413]) {
            ++$edicount;

            $out .= "NM1" . // Loop 2310C other operating Physician
                "*73" . "*1" . "*" . $ub04id[413] . "*" . $ub04id[414] . "*" . "*";
            if ($ub04id[405]) {
                $out .= "*" . $ub04id[405] . "*" . $ub04id[406];
            } else {
                $out .= "*" . "*";
                $log .= "*** Other Operating Physician has no NPI.\n";
            }
            $out .= "~\n";

            if ($ub04id[407]) {
                ++$edicount;
                $out .= "REF" . // other operating Physician Secondary Identification
                    "*" . $ub04id[407] . "*" . $ub04id[408] . "~\n";
            }
        }
        if ($ub04id[427]) {
            ++$edicount;

            $out .= "NM1" . // Loop 2310C other operating Physician
                "*73" . "*1" . "*" . $ub04id[427] . "*" . $ub04id[428] . "*" . "*";
            if ($ub04id[420]) {
                $out .= "*" . $ub04id[419] . "*" . $ub04id[420];
            } else {
                $out .= "*" . "*";
                $log .= "*** Other Operating Physician has no NPI.\n";
            }
            $out .= "~\n";

            if ($ub04id[422]) {
                ++$edicount;
                $out .= "REF" . // other operating Physician Secondary Identification
                    "*" . $ub04id[422] . "*" . $ub04id[421] . "~\n";
            }
        }
        /*
         * Per the implementation guide lines, only include this information if it is different
         * than the Loop 2010AA information
         */
        if ($claim->providerNPIValid() && $claim->billingFacilityNPI() !== $claim->providerNPI()) {
            ++$edicount;
            $out .= "NM1" . // Loop 2310D Rendering Provider
                "*82" . "*1" . "*" . $claim->providerLastName() . "*" . $claim->providerFirstName() . "*" . $claim->providerMiddleName() . "*" . "*";
            if ($claim->providerNPI()) {
                $out .= "*XX" . "*" . $claim->providerNPI();
            } else {
                $log .= "*** Rendering provider has no NPI.\n";
            }
            $out .= "~\n";

            // End of Loop 2310D
        } else {
            // This loop can only get skipped if we are generating a 5010 claim
            if (!($claim->providerNPIValid())) {
                //If the loop was skipped because the provider NPI was invalid, generate a warning for the log.
                $log .= "*** Skipping 2310B because " . $claim->providerLastName() . "," . $claim->providerFirstName() . " has invalid NPI.\n";
            }
            /*
             * Skipping this segment because the providerNPI and the billingFacilityNPI are identical
             * is a normal condition, so no need to warn.
             */
        }

        // 5010 spec says nothing here if NPI was specified.
        //
        if (!$claim->providerNPI() && in_array($claim->providerNumberType(), array('0B', '1G', 'G2', 'LU'))) {
            if ($claim->providerNumber()) {
                ++$edicount;
                $out .= "REF" . "*" . $claim->providerNumberType() . "*" . $claim->providerNumber() . "~\n";
            }
        }

        // Loop 2310D is omitted in the case of home visits (POS=12).
        if ($claim->facilityPOS() != 12 && ($claim->facilityNPI() != $claim->billingFacilityNPI())) {
            ++$edicount;

            // Service Facility Name

            $out .= "NM1" . // Loop 2310E Service Location
                "*77" . "*2";
            $facilityName = substr($claim->facilityName(), 0, 60);
            if ($claim->facilityName() || $claim->facilityNPI() || $claim->facilityETIN()) {
                $out .= "*" . $facilityName;
            }
            if ($claim->facilityNPI() || $claim->facilityETIN()) {
                $out .= "*" . "*" . "*" . "*";
                if ($claim->facilityNPI()) {
                    $out .= "*XX*" . $claim->facilityNPI();
                } else {
                    $log .= "*** Service location has no NPI.\n";
                }
            }
            $out .= "~\n";
            if ($claim->facilityStreet()) {
                ++$edicount;
                $out .= "N3" . "*" . $claim->facilityStreet() . "~\n";
            }
            if ($claim->facilityState()) {
                ++$edicount;
                $out .= "N4" . "*" . $claim->facilityCity() . "*" . $claim->facilityState() . "*" . $claim->x12Zip($claim->facilityZip()) . "~\n";
            }
        }

        // Segment REF (Service Facility Location Secondary Identification) omitted.
        // Segment PER (Service Facility Contact Information) omitted.

        // Loop 2310F Referring Provider

        if ($claim->referrerLastName()) {
            // Medicare requires referring provider's name and UPIN.
            ++$edicount;

            $out .= "NM1" . // Loop 2310F Referring Provider this needs to change position
                "*DN" . "*1" . "*" . $claim->referrerLastName() . "*" . $claim->referrerFirstName() . "*" . $claim->referrerMiddleName() . "*" . "*";
            if ($claim->referrerNPI()) {
                $out .= "*XX" . "*" . $claim->referrerNPI();
            } else {
                $log .= "*** Referrer has no NPI.\n";
            }
            $out .= "~\n";
        }

        // Loop 2310E, Supervising Provider
        // Omitted

        // Segments NM1*PW, N3, N4 (Ambulance Pick-Up Location) omitted.
        // Segments NM1*45, N3, N4 (Ambulance Drop-Off Location) omitted.

        $prev_pt_resp = $clm_total_charges; // for computation below

        // Loops 2320 and 2330*, other subscriber/payer information.
        // Remember that insurance index 0 is always for the payer being billed
        // by this claim, and 1 and above are always for the "other" payers.
        //
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
            if ($tmp1 === 'MA') {
                $tmp2 = 'MA';
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
                "*" . (($claim->groupNumber($ins)) ? '' : $claim->groupName($ins)) .
                "*" . ($claim->insuredTypeCode($ins) ? $claim->insuredTypeCode($ins) : $tmp2) .
                "*" .
                "*" .
                "*" .
                "*" . $claim->claimType($ins) .
                "~\n";

            // Things that apply only to previous payers, not future payers.
            //
            if ($claim->payerSequence($ins) < $claim->payerSequence()) {
                // Generate claim-level adjustments.
                $aarr = $claim->payerAdjustments($ins);
                foreach ($aarr as $a) {
                    ++$edicount;
                    $out .= "CAS" . // Previous payer's claim-level adjustments. Page 301/323.
                        "*" . $a[1] . "*" . $a[2] . "*" . $a[3] . "~\n";
                }

                $payerpaid = $claim->payerTotals($ins);
                ++$edicount;
                $out .= "AMT" . // Previous payer's paid amount. Page 307/332.
                    "*D" . "*" . $payerpaid[1] . "~\n";

                // Segment AMT*A8 (COB Total Non-Covered Amount) omitted.
                // Segment AMT*EAF (Remaining Patient Liability) omitted.
            } // End of things that apply only to previous payers.

            ++$edicount;
            $out .= "OI" . // Other Insurance Coverage Information. Page 310/344.
                "*" . "*" . "*" . ($claim->billingFacilityAssignment($ins) ? 'Y' : 'N') .
                // For this next item, the 5010 example in the spec does not match its
                // description. So this might be wrong.
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
                "*" . $claim->x12Zip($claim->insuredZip($ins)) .
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
                $log .= "*** Payer ID is missing for payer '" . $claim->payerName($ins) . "'.\n";
            }

            ++$edicount;
            $out .= "N3" .
                "*" . $claim->payerStreet($ins) .
                "~\n";

            ++$edicount;
            $out .= "N4" .
                "*" . $claim->payerCity($ins) .
                "*" . $claim->payerState($ins) .
                "*" . $claim->x12Zip($claim->payerZip($ins)) .
                "~\n";

            // Segment DTP*573 (Claim Check or Remittance Date) omitted.
            // Segment REF (Other Payer Secondary Identifier) omitted.
            // Segment REF*G1 (Other Payer Prior Authorization Number) omitted.
            // Segment REF*9F (Other Payer Referral Number) omitted.
            // Segment REF*T4 (Other Payer Claim Adjustment Indicator) omitted.
            // Segment REF*F8 (Other Payer Claim Control Number) omitted.
            // 2330C-I loops Omitted
        } // End loops 2320/2330*.

        $loopcount = 0;

        // Procedure loop starts here.
        //

        for ($tlh = 0; $tlh < $proccount; ++$tlh) {
            $tmp = $claim->procs[$tlh][code_text];

            if ($claim->procs[$tlh][code_type] == 'HCPCS') {
                $tmpcode = '3';
            } else {
                $tmpcode = '1';
            }
            $getrevcd = $claim->cptCode($tlh);
            $sql = "SELECT * FROM codes WHERE code_type = ? and code = ? ORDER BY revenue_code DESC";
            $revcode[$tlh] = sqlQuery($sql, array(
                $tmpcode,
                $getrevcd
            ));
        }


        for ($prockey = 0; $prockey < $proccount; ++$prockey) {
            $os = 99 + ($loopcount * 8); // Form revenue code offset
            $dosos = 102 + ($loopcount * 8); // Procedure date of service form start offset-add 8 for loop
            ++$loopcount;

            ++$edicount;
            $out .= "LX" . // Loop 2400 LX Service Line. Page 398.
                "*" . "$loopcount" . "~\n";

            ++$edicount;

            // Revenue code from form
            //
            $tmp = $ub04id[$os]; //$revcode[$prockey][revenue_code];
            if (empty($tmp)) {
                $log .= "*** Error: Missing Revenue Code for " . $claim->cptKey($prockey) . "!\n";
            }
            // Institutional Service Line.
            //
            $out .= "SV2" . "*" . $tmp . // revenue code

                "*" . "HC:" . $claim->cptKey($prockey) . "*" . sprintf('%.2f', $claim->cptCharges($prockey)) . "*UN" . "*" . $claim->cptUnits($prockey) . "*" . "*" . "*";

            $out .= "~\n";

            if (!$claim->cptCharges($prockey)) {
                $log .= "*** Procedure '" . $claim->cptKey($prockey) . "' has no charges!\n";
            }

            // Segment SV5 (Durable Medical Equipment Service) omitted.
            // Segment PWK (Line Supplemental Information) omitted.
            // Segment PWK (Durable Medical Equipment Certificate of Medical Necessity Indicator) omitted.
            // Segment CR1 (Ambulance Transport Information) omitted.
            // Segment CR3 (Durable Medical Equipment Certification) omitted.
            // Segment CRC (Ambulance Certification) omitted.
            // Segment CRC (Hospice Employee Indicator) omitted.
            // Segment CRC (Condition Indicator / Durable Medical Equipment) omitted.

            ++$edicount;

            $out .= "DTP" . // Date of Service. Needs to be when service preformed.
                "*" . "472" . "*" . "D8" . "*" . $ub04id[$dosos] . "~\n"; //$claim->serviceDate()

            $testnote = rtrim($claim->cptNotecodes($prockey));
            if (!empty($testnote)) {
                ++$edicount;
                $out .= "NTE" . // Explain Unusual Circumstances.
                    "*ADD" . "*" . $claim->cptNotecodes($prockey) . "~\n";
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
            // (Really oughta have this for robust 835 posting!)
            // Segment REF*EW (Mammography Certification Number) omitted.
            // Segment REF*X4 (CLIA Number) omitted.
            // Segment REF*F4 (Referring CLIA Facility Identification) omitted.
            // Segment REF*BT (Immunization Batch Number) omitted.
            // Segment REF*9F (Referral Number) omitted.
            // Segment AMT*GT (Sales Tax Amount) omitted.
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
            }

            // Segment REF (Prescription or Compound Drug Association Number) omitted.

            // Loop 2420A, Rendering Provider (service-specific). (Operating Physician Name for 837I)
            // Used if the rendering provider for this service line is different
            // from that in loop 2310B.
            //
            if ($claim->providerNPI() != $claim->providerNPI($prockey)) {
                ++$edicount;
                $out .= "NM1" .       // Loop 2310B Rendering Provider
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

                if ($claim->providerTaxonomy($prockey)) {
                    ++$edicount;
                    $out .= "PRV" .
                        "*" . "PE" . // PErforming provider
                        "*" . "PXC" .
                        "*" . $claim->providerTaxonomy($prockey) .
                        "~\n";
                }

                // Segment PRV*PE (Rendering Provider Specialty Information) omitted.
                // Segment REF (Rendering Provider Secondary Identification) omitted.
                // Segment NM1 (Purchased Service Provider Name) omitted.
                // Segment REF (Purchased Service Provider Secondary Identification) omitted.
                // Segment NM1,N3,N4 (Service Facility Location) omitted.
                // Segment REF (Service Facility Location Secondary Identification) omitted.
                // Segment NM1 (Supervising Provider Name) omitted.
                // Segment REF (Supervising Provider Secondary Identification) omitted.
                // Segment NM1,N3,N4 (Ordering Provider) omitted.
                // Segment REF (Ordering Provider Secondary Identification) omitted.
                // Segment PER (Ordering Provider Contact Information) omitted.
                // Segment NM1 (Referring Provider Name) omitted.
                // Segment REF (Referring Provider Secondary Identification) omitted.
                // Segments NM1*PW, N3, N4 (Ambulance Pick-Up Location) omitted.
                // Segments NM1*45, N3, N4 (Ambulance Drop-Off Location) omitted.

                // REF*1C is required here for the Medicare provider number if NPI was
                // specified in NM109. Not sure if other payers require anything here.
                if ($claim->providerNumberType($prockey) == "G2") {
                    ++$edicount;
                    $out .= "REF" . "*" . $claim->providerNumberType($prockey) .
                        "*" . $claim->providerNumber($prockey) . "~\n";
                }
            } // provider exception

            // Loop 2430, adjudication by previous payers.
            //
            for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {
                if ($claim->payerSequence($ins) > $claim->payerSequence()) {
                    continue; // payer is future, not previous
                }

                $payerpaid = $claim->payerTotals($ins, $claim->cptKey($prockey));
                $aarr = $claim->payerAdjustments($ins, $claim->cptKey($prockey));

                if ($payerpaid[1] == 0 && !count($aarr)) {
                    $log .= "*** Procedure '" . $claim->cptKey($prockey) . "' has no payments or adjustments from previous payer!\n";
                    continue;
                }

                ++$edicount;
                $out .= "SVD" . // Service line adjudication. Page 554.
                    "*" . $claim->payerID($ins) . "*" . $payerpaid[1] . "*HC:" . $claim->cptKey($prockey) . "*" . "*" . $claim->cptUnits($prockey) . "~\n";

                $tmpdate = $payerpaid[0];
                foreach ($aarr as $a) {
                    ++$edicount;
                    $out .= "CAS" . // Previous payer's line level adjustments. Page 558.
                        "*" . $a[1] . "*" . $a[2] . "*" . $a[3] . "~\n";
                    if (!$tmpdate) {
                        $tmpdate = $a[0];
                    }
                }

                if ($tmpdate) {
                    ++$edicount;
                    $out .= "DTP" . // Previous payer's line adjustment date. Page 493/566.
                        "*573" . "*D8" . "*$tmpdate" . "~\n";
                }

                // Segment AMT*EAF (Remaining Patient Liability) omitted.
                // Segment LQ (Form Identification Code) omitted.
                // Segment FRM (Supporting Documentation) omitted.
            } // end loop 2430
        } // end this procedure

        ++$edicount;
        $out .= "SE" . // SE Trailer
            "*$edicount" . "*0021" . "~\n";

        $out .= "GE" . // GE Trailer
            "*1" . "*1" . "~\n";

        $out .= "IEA" . // IEA Trailer
            "*1" . "*000000001" . "~\n";

        // Remove any trailing empty fields (delimiters) from each segment.
        $out = preg_replace('/\*+~/', '~', $out);

        $log .= "\n";
        return $out;
    }
}
