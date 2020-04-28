<?php

/* Hcfa1500 Class
 *
 * This program creates the HCFA 1500 claim form.
 *
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2011 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (C) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

use OpenEMR\Billing\Claim;
use OpenEMR\Billing\HCFAInfo;

class Hcfa1500
{
    protected $hcfa_curr_line;
    protected $hcfa_curr_col;
    protected $hcfa_data;
    protected $hcfa_proc_index;
    /**
     * Hcfa1500 constructor.
     * @param int $hcfa_curr_line
     * @param int $hcfa_curr_col
     * @param type $hcfa_data
     * @param int $hcfa_proc_index
     */
    public function __construct()
    {
        $this->hcfa_curr_line = 1;
        $this->hcfa_curr_col = 1;
        $this->hcfa_data = '';
        $this->hcfa_proc_index = 0;
    }

    /**
     * take the data element and place it at the correct coordinates on the page
     *
     * @global int $hcfa_curr_line
     * @global int $hcfa_curr_col
     * @global type $hcfa_data
     * @param type $line
     * @param type $col
     * @param type $maxlen
     * @param type $data
     * @param type $strip   regular expression for what to strip from the data. period and has are the defaults
     *                      02/12 version needs to include periods in the diagnoses hence the need to override
     */
    private function putHcfa($line, $col, $maxlen, $data, $strip = '/[.#]/')
    {
        if ($line < $this->hcfa_curr_line) {
            die("Data item at ($line, $col) precedes current line $this->hcfa_curr_line and $data");
        }

        while ($this->hcfa_curr_line < $line) {
            $this->hcfa_data .= "\n";
            ++$this->hcfa_curr_line;
            $this->hcfa_curr_col = 1;
        }

        if ($col < $this->hcfa_curr_col) {
            die("Data item at ($line, $col) precedes current column.");
        }

        while ($this->hcfa_curr_col < $col) {
            $this->hcfa_data .= " ";
            ++$this->hcfa_curr_col;
        }

        $data = preg_replace($strip, '', strtoupper($data));
        $len = min(strlen($data), $maxlen);
        $this->hcfa_data .= substr($data, 0, $len);
        $this->hcfa_curr_col += $len;
    }

    /**
     * Process the diagnoses for a given claim. log any errors
     *
     * @param type $claim
     * @param string $log
     */
    private function processDiagnoses0212($claim, &$log)
    {
        $hcfa_entries = array();
        $diags = $claim->diagArray(false);
        if ($claim->diagtype == 'ICD10') {
            $icd_indicator = '0';
        } else {
            $icd_indicator = '9';
        }

        $hcfa_entries[] = new HCFAInfo(37, 42, 1, $icd_indicator);

        // Box 22. Medicaid Resubmission Code and Original Ref. No.
        $hcfa_entries[] = new HCFAInfo(38, 50, 10, $claim->medicaidResubmissionCode());
        $hcfa_entries[] = new HCFAInfo(38, 62, 15, $claim->medicaidOriginalReference());

        // Box 23. Prior Authorization Number
        $hcfa_entries[] = new HCFAInfo(40, 50, 28, $claim->priorAuth());

        $diag_count = 0;
        foreach ($diags as $diag) {
            if ($diag_count < 12) {
                $this->addDiagnosis($hcfa_entries, $diag_count, $diag);
            } else {
                $log .= "***Too many diagnoses " . ($diag_count + 1) . ":" . $diag;
            }

            $diag_count++;
        }
        // Sort the entries to put them in the page base sequence.
        usort($hcfa_entries, array('OpenEMR\Billing\HCFAInfo', 'cmpHcfaInfo'));

        foreach ($hcfa_entries as $hcfa_entry) {
            $this->putHcfa(
                $hcfa_entry->getRow(),
                $hcfa_entry->getColumn(),
                $hcfa_entry->getWidth(),
                $hcfa_entry->getInfo(),
                '/#/'
            );
        }
    }
    /**
     * calculate where on the form a given diagnosis belongs and add it to the entries
     *
     * @param array $hcfa_entries
     * @param type $number
     * @param type $diag
     */
    private function addDiagnosis(&$hcfa_entries, $number, $diag)
    {
        /*
         * The diagnoses go across the page.
         * Positioned
         *  A B C D
         *  E F G H
         *  I J K L
         */
        $column_num = ($number % 4);
        $row_num = (int)($number / 4);

        // First column is at location 3, each column is 13 wide
        $col_pos = 3 + 13 * $column_num;

        // First diagnosis row is 38
        $strip = '/[.#]/';
        $diag = preg_replace($strip, '', strtoupper($diag));
        $row_pos = 38 + $row_num;
        $hcfa_entries[] = new HCFAInfo($row_pos, $col_pos, 8, $diag);
    }

    public function genHcfa1500($pid, $encounter, &$log)
    {
        $this->hcfa_data = '';
        $this->hcfa_proc_index = 0;

        $today = time();

        $claim = new Claim($pid, $encounter);

        $log .= "Generating HCFA claim $pid-$encounter for " .
            $claim->patientFirstName() . ' ' .
            $claim->patientMiddleName() . ' ' .
            $claim->patientLastName() . ' on ' .
            date('Y-m-d H:i', $today) . ".\n";

        while ($this->hcfa_proc_index < $claim->procCount()) {
            if ($this->hcfa_proc_index) {
                $this->hcfa_data .= "\014"; // append form feed for new page
            }

            $this->genHcfa1500Page($pid, $encounter, $log, $claim);
        }

        $log .= "\n";
        return $this->hcfa_data;
    }

    private function genHcfa1500Page($pid, $encounter, &$log, $claim)
    {

        $this->hcfa_curr_line = 1;
        $this->hcfa_curr_col = 1;

        // According to:
        // https://www.ngsmedicare.com/NGSMedicare/PartB/EducationandSupport/ToolsandMaterials/CMS_ClaimFormInst.aspx
        // Medicare interprets sections 9 and 11 of the claim form in its own
        // special way.  This flag tells us to do that.  However I'm not 100%
        // sure that it applies nationwide, and if you find that it is not right
        // for you then set it to false.  -- Rod 2009-03-26
        $new_medicare_logic = $claim->claimType() == 'MB';

        // Payer name, attn, street.
        $this->putHcfa(2, 41, 31, $claim->payerName());
        $this->putHcfa(3, 41, 31, $claim->payerAttn());
        $this->putHcfa(4, 41, 31, $claim->payerStreet());

        // Payer city, state, zip.
        $tmp = $claim->payerCity() ? ($claim->payerCity() . ', ') : '';
        $this->putHcfa(5, 41, 31, $tmp . $claim->payerState() . ' ' . $claim->payerZip());

        // Box 1. Insurance Type
        // claimTypeRaw() gets the integer value from insurance_companies.ins_type_code.
        // Previous version of this code called claimType() which maps ins_type_code to
        // a 2-character code and that was not specific enough.
        $ct = $claim->claimTypeRaw();
        $tmpcol = 45;                    // Other
        if ($ct == 2) {
            $tmpcol = 1; // Medicare
        } elseif ($ct == 3) {
            $tmpcol = 8; // Medicaid
        } elseif ($ct == 5) {
            $tmpcol = 15; // TriCare (formerly CHAMPUS)
        } elseif ($ct == 4) {
            $tmpcol = 24; // Champus VA
        } elseif ($ct == 6) {
            $tmpcol = 31; // Group Health Plan (only BCBS?)
        } elseif ($ct == 7) {
            $tmpcol = 39; // FECA
        }

        $this->putHcfa(8, $tmpcol, 1, 'X');

        // Box 1a. Insured's ID Number
        $this->putHcfa(8, 50, 17, $claim->policyNumber());

        // Box 2. Patient's Name
        $tmp = $claim->patientLastName() . ', ' . $claim->patientFirstName();
        if ($claim->patientMiddleName()) {
            $tmp .= ', ' . substr($claim->patientMiddleName(), 0, 1);
        }

        $this->putHcfa(10, 1, 28, $tmp);

        // Box 3. Patient's Birth Date and Sex
        $tmp = $claim->patientDOB();
        $this->putHcfa(10, 31, 2, substr($tmp, 4, 2));
        $this->putHcfa(10, 34, 2, substr($tmp, 6, 2));
        $this->putHcfa(10, 37, 4, substr($tmp, 0, 4));
        $this->putHcfa(10, $claim->patientSex() == 'M' ? 42 : 47, 1, 'X');

        // Box 4. Insured's Name
        $tmp = $claim->insuredLastName() . ', ' . $claim->insuredFirstName();
        if ($claim->insuredMiddleName()) {
            $tmp .= ', ' . substr($claim->insuredMiddleName(), 0, 1);
        }

        $this->putHcfa(10, 50, 28, $tmp);

        // Box 5. Patient's Address
        $this->putHcfa(12, 1, 28, $claim->patientStreet());

        // Box 6. Patient Relationship to Insured
        $tmp = $claim->insuredRelationship();
        $tmpcol = 47;                         // Other
        if ($tmp === '18') {
            $tmpcol = 33; // self
        } elseif ($tmp === '01') {
            $tmpcol = 38; // spouse
        } elseif ($tmp === '19') {
            $tmpcol = 42; // child
        }

        $this->putHcfa(12, $tmpcol, 1, 'X');

        // Box 7. Insured's Address
        $this->putHcfa(12, 50, 28, $claim->insuredStreet());

        // Box 5 continued. Patient's City and State
        $this->putHcfa(14, 1, 20, $claim->patientCity());
        $this->putHcfa(14, 26, 2, $claim->patientState());

        // Box 8. Reserved for NUCC Use in 02/12

        // Box 7 continued. Insured's City and State
        $this->putHcfa(14, 50, 20, $claim->insuredCity());
        $this->putHcfa(14, 74, 2, $claim->insuredState());

        // Box 5 continued. Patient's Zip Code and Telephone
        $this->putHcfa(16, 1, 10, $claim->patientZip());
        $tmp = $claim->patientPhone();
        $this->putHcfa(16, 15, 3, substr($tmp, 0, 3));
        $this->putHcfa(16, 19, 7, substr($tmp, 3));

        // Box 7 continued. Insured's Zip Code and Telephone
        $this->putHcfa(16, 50, 10, $claim->insuredZip());
        $tmp = $claim->insuredPhone();
        $this->putHcfa(16, 65, 3, substr($tmp, 0, 3));
        $this->putHcfa(16, 69, 7, substr($tmp, 3));

        // Box 9. Other Insured's Name
        if ($new_medicare_logic) {
            // TBD: Medigap stuff? How do we know if this is a Medigap transfer?
        } else {
            if ($claim->payerCount() > 1) {
                $tmp = $claim->insuredLastName(1) . ', ' . $claim->insuredFirstName(1);
                if ($claim->insuredMiddleName(1)) {
                    $tmp .= ', ' . substr($claim->insuredMiddleName(1), 0, 1);
                }

                $this->putHcfa(18, 1, 28, $tmp);
            }
        }

        // Box 11. Insured's Group Number
        if ($new_medicare_logic) {
            // If this is Medicare secondary then we need the primary's policy number
            // here, otherwise the word "NONE".
            $tmp = $claim->payerSequence() == 'P' ? 'NONE' : $claim->policyNumber(1);
        } else {
            $tmp = $claim->groupNumber();
        }

        $this->putHcfa(18, 50, 30, $tmp);

        // Box 9a. Other Insured's Policy or Group Number
        if ($new_medicare_logic) {
            // TBD: Medigap stuff?
        } else {
            if ($claim->payerCount() > 1) {
                $this->putHcfa(20, 1, 28, $claim->policyNumber(1));
            }
        }

        // Box 10a. Employment Related
        $this->putHcfa(20, $claim->isRelatedEmployment() ? 35 : 41, 1, 'X');

        // Box 11a. Insured's Birth Date and Sex
        if ($new_medicare_logic) {
            $tmpdob = $tmpsex = '';
            if ($claim->payerSequence() != 'P') {
                $tmpdob = $claim->insuredDOB(1);
                $tmpsex = $claim->insuredSex(1);
            }
        } else {
            $tmpdob = $claim->insuredDOB();
            $tmpsex = $claim->insuredSex();
        }

        if ($tmpdob) {
            $this->putHcfa(20, 53, 2, substr($tmpdob, 4, 2));
            $this->putHcfa(20, 56, 2, substr($tmpdob, 6, 2));
            $this->putHcfa(20, 59, 4, substr($tmpdob, 0, 4));
        }

        if ($tmpsex) {
            $this->putHcfa(20, $tmpsex == 'M' ? 68 : 75, 1, 'X');
        }

        // Box 9b. Reserved for NUCC Use in 02/12

        // Box 10b. Auto Accident
        $this->putHcfa(22, $claim->isRelatedAuto() ? 35 : 41, 1, 'X');
        if ($claim->isRelatedAuto()) {
            $this->putHcfa(22, 45, 2, $claim->autoAccidentState());
        }

        // Box 11b. Insured's Employer/School Name
        if ($new_medicare_logic) {
            $tmp = $claim->payerSequence() == 'P' ? '' : $claim->groupName(1);
        } else {
            $tmp = $claim->groupName();
        }

        $this->putHcfa(22, 50, 30, $tmp);

        // Box 9c. Reserved for NUCC Use in 02/12

        // Box 10c. Other Accident
        $this->putHcfa(24, $claim->isRelatedOther() ? 35 : 41, 1, 'X');

        // Box 11c. Insurance Plan Name or Program Name
        if ($new_medicare_logic) {
            $tmp = '';
            if ($claim->payerSequence() != 'P') {
                $tmp = $claim->planName(1);
                if (!$tmp) {
                    $tmp = $claim->payerName(1);
                }
            }
        } else {
            $tmp = $claim->planName();
        }

        $this->putHcfa(24, 50, 30, $tmp);

        // Box 9d. Other Insurance Plan Name or Program Name
        if ($new_medicare_logic) {
            // TBD: Medigap stuff?
        } else {
            if ($claim->payerCount() > 1) {
                $this->putHcfa(26, 1, 28, $claim->planName(1));
            }
        }

        // Box 10d. Claim Codes  medicaid_referral_code

        if ($claim->epsdtFlag()) {
            $this->putHcfa(26, 34, 2, $claim->medicaidReferralCode());
        }

        // Box 11d. Is There Another Health Benefit Plan

        if (!$new_medicare_logic) {
            $this->putHcfa(26, $claim->payerCount() > 1 ? 52 : 57, 1, 'X');
        }

        // Box 12. Patient's or Authorized Person's Signature
        $this->putHcfa(29, 7, 17, 'Signature on File');
        // Note: Date does not apply unless the person physically signs the form.

        // Box 13. Insured's or Authorized Person's Signature
        $this->putHcfa(29, 55, 17, 'Signature on File');

        // Box 14. Date of Current Illness/Injury/Pregnancy
        // this will cause onsetDate in Encounter summary to override misc billing so not perfect yet but fine for now
        $tmp = ($claim->onsetDate()) ? $claim->onsetDate() : $claim->miscOnsetDate();
        if (!empty($tmp)) {
            $this->putHcfa(32, 2, 2, substr($tmp, 4, 2));
            $this->putHcfa(32, 5, 2, substr($tmp, 6, 2));
            $this->putHcfa(32, 8, 4, substr($tmp, 0, 4));
            // Include Box 14 Qualifier
            $this->putHcfa(32, 16, 3, $claim->box14Qualifier());
        }

        // Box 15. First Date of Same or Similar Illness, if applicable
        $tmp = $claim->dateInitialTreatment();
        if (!empty($tmp)) {
            // Only include the Box 15 qualifier if using version 02/12 and there is a Box 15 date.
            $this->putHcfa(32, 31, 3, $claim->box15Qualifier());
        }

        $this->putHcfa(32, 37, 2, substr($tmp, 4, 2));
        $this->putHcfa(32, 40, 2, substr($tmp, 6, 2));
        $this->putHcfa(32, 43, 4, substr($tmp, 0, 4));

        // Box 16. Dates Patient Unable to Work in Current Occupation
        if ($claim->isUnableToWork()) {
            $tmp = $claim->offWorkFrom();
            $this->putHcfa(32, 54, 2, substr($tmp, 4, 2));
            $this->putHcfa(32, 57, 2, substr($tmp, 6, 2));
            $this->putHcfa(32, 60, 4, substr($tmp, 0, 4));
            $tmp = $claim->offWorkTo();
            $this->putHcfa(32, 68, 2, substr($tmp, 4, 2));
            $this->putHcfa(32, 71, 2, substr($tmp, 6, 2));
            $this->putHcfa(32, 74, 4, substr($tmp, 0, 4));
        }

        // Referring provider stuff.  Reports are that for primary care providers,
        // Medicare forbids an entry here and other payers require one.
        // There is still confusion over this.
        if (
            $claim->referrerLastName() || $claim->billingProviderLastName() &&
            (empty($GLOBALS['MedicareReferrerIsRenderer']) || $claim->claimType() != 'MB')
        ) {
            // Box 17a. Referring Provider Alternate Identifier
            // Commented this out because UPINs are obsolete, leaving the code as an
            // example in case some other identifier needs to be supported.
            /*****************************************************************
             * if ($claim->referrerUPIN() && $claim->claimType() != 'MB') {
             * $this->putHcfa(33, 30,  2, '1G');
             * $this->putHcfa(33, 33, 15, $claim->referrerUPIN());
             * }
             *****************************************************************/
            if ($claim->claimType() == 'MC') {
                $this->putHcfa(33, 30, 2, 'ZZ');
                $this->putHcfa(33, 33, 14, $claim->referrerTaxonomy());
            }

            // Box 17. Name of Referring Provider or Other Source
            if (strlen($claim->billingProviderLastName()) != 0) {
                $tmp2 = $claim->billingProviderLastName() . ', ' . $claim->billingProviderFirstName();
                if ($claim->billingProviderMiddleName()) {
                    $tmp2 .= ', ' . substr($claim->billingProviderMiddleName(), 0, 1);
                }

                $this->putHcfa(34, 1, 3, $claim->billing_options['provider_qualifier_code']);
                $this->putHcfa(34, 4, 25, $tmp2);
                if ($claim->billingProviderNPI()) {
                    $this->putHcfa(34, 33, 15, $claim->billingProviderNPI());
                }
            } else {
                $tmp = $claim->referrerLastName() . ', ' . $claim->referrerFirstName();
                if ($claim->referrerMiddleName()) {
                    $tmp .= ', ' . substr($claim->referrerMiddleName(), 0, 1);
                }

                $this->putHcfa(34, 1, 3, 'DN');
                $this->putHcfa(34, 4, 25, $tmp);
                if ($claim->referrerNPI()) {
                    $this->putHcfa(34, 33, 15, $claim->referrerNPI());
                }
            }
        }

        // Box 18. Hospitalization Dates Related to Current Services
        if ($claim->isHospitalized()) {
            $tmp = $claim->hospitalizedFrom();
            $this->putHcfa(34, 54, 2, substr($tmp, 4, 2));
            $this->putHcfa(34, 57, 2, substr($tmp, 6, 2));
            $this->putHcfa(34, 60, 4, substr($tmp, 0, 4));
            $tmp = $claim->hospitalizedTo();
            $this->putHcfa(34, 68, 2, substr($tmp, 4, 2));
            $this->putHcfa(34, 71, 2, substr($tmp, 6, 2));
            $this->putHcfa(34, 74, 4, substr($tmp, 0, 4));
        }

        // Box 19. Reserved for Local Use
        $this->putHcfa(36, 1, 48, $claim->additionalNotes());

        // Box 20. Outside Lab
        $this->putHcfa(36, $claim->isOutsideLab() ? 52 : 57, 1, 'X');
        if ($claim->isOutsideLab()) {
            // Note here that $this->putHcfa strips the decimal point, as required.
            // We right-justify this amount (ending in col. 69).
            $this->putHcfa(36, 63, 8, sprintf('%8s', $claim->outsideLabAmount()));
        }

        // Box 21. Diagnoses
        $this->processDiagnoses0212($claim, $log);

        $proccount = $claim->procCount(); // number of procedures

        // Charges, adjustments and payments are accumulated by line item so that
        // each page of a multi-page claim will stand alone.  Payments include the
        // co-pay for the first page only.
        $clm_total_charges = 0;
        $clm_amount_adjusted = 0;
        $clm_amount_paid = $this->hcfa_proc_index ? 0 : $claim->patientPaidAmount();

        // Procedure loop starts here.
        for ($svccount = 0; $svccount < 6 && $this->hcfa_proc_index < $proccount; ++$this->hcfa_proc_index) {
            $dia = $claim->diagIndexArray($this->hcfa_proc_index);

            if (!$claim->cptCharges($this->hcfa_proc_index)) {
                $log .= "*** Procedure '" . $claim->cptKey($this->hcfa_proc_index) .
                    "' has no charges!\n";
            }

            if (empty($dia)) {
                $log .= "*** Procedure '" . $claim->cptKey($this->hcfa_proc_index) .
                    "' is not justified!\n";
            }

            $clm_total_charges += floatval($claim->cptCharges($this->hcfa_proc_index));

            // Compute prior payments and "hard" adjustments.
            for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {
                if ($claim->payerSequence($ins) > $claim->payerSequence()) {
                    continue; // skip future payers
                }

                $payerpaid = $claim->payerTotals($ins, $claim->cptKey($this->hcfa_proc_index));
                $clm_amount_paid += $payerpaid[1];
                $clm_amount_adjusted += $payerpaid[2];
            }

            ++$svccount;
            $lino = $svccount * 2 + 41;

            // Drug Information. Medicaid insurers want this with HCPCS codes.
            //
            $ndc = $claim->cptNDCID($this->hcfa_proc_index);
            if ($ndc) {
                if (preg_match('/^(\d\d\d\d\d)-(\d\d\d\d)-(\d\d)$/', $ndc, $tmp)) {
                    $ndc = $tmp[1] . $tmp[2] . $tmp[3];
                } elseif (preg_match('/^\d{11}$/', $ndc)) {
                } else {
                    $log .= "*** NDC code '$ndc' has invalid format!\n";
                }

                $this->putHcfa($lino, 1, 50, "N4$ndc   " . $claim->cptNDCUOM($this->hcfa_proc_index) .
                    $claim->cptNDCQuantity($this->hcfa_proc_index));
            }

            //Note Codes.
            $this->putHcfa($lino, 25, 7, $claim->cptNotecodes($this->hcfa_proc_index));

            // 24i and 24j Top. ID Qualifier and Rendering Provider ID
            if ($claim->supervisorNumber()) {
                // If there is a supervising provider and that person has a
                // payer-specific provider number, then we assume that the SP
                // must be identified on the claim and this is how we do it
                // (but the NPI of the actual rendering provider appears below).
                // BCBS of TN indicated they want it this way.  YMMV.  -- Rod
                $this->putHcfa($lino, 65, 2, $claim->supervisorNumberType());
                $this->putHcfa($lino, 68, 10, $claim->supervisorNumber());
            } elseif ($claim->providerNumber($this->hcfa_proc_index)) {
                $this->putHcfa($lino, 65, 2, $claim->providerNumberType($this->hcfa_proc_index));
                $this->putHcfa($lino, 68, 10, $claim->providerNumber($this->hcfa_proc_index));
            } elseif ($claim->claimType() == 'MC') {
                $this->putHcfa($lino, 65, 2, 'ZZ');
                $this->putHcfa($lino, 68, 14, $claim->providerTaxonomy());
            }

            ++$lino;

            // 24a. Date of Service
            $tmp = $claim->serviceDate();
            $this->putHcfa($lino, 1, 2, substr($tmp, 4, 2));
            $this->putHcfa($lino, 4, 2, substr($tmp, 6, 2));
            $this->putHcfa($lino, 7, 2, substr($tmp, 2, 2));
            $this->putHcfa($lino, 10, 2, substr($tmp, 4, 2));
            $this->putHcfa($lino, 13, 2, substr($tmp, 6, 2));
            $this->putHcfa($lino, 16, 2, substr($tmp, 2, 2));

            // 24b. Place of Service
            $this->putHcfa($lino, 19, 2, $claim->facilityPOS());

            // 24c. EMG
            // Not currently supported.

            // 24d. Procedures, Services or Supplies
            $this->putHcfa($lino, 25, 7, $claim->cptCode($this->hcfa_proc_index));
            // replace colon with space for printing
            $this->putHcfa($lino, 33, 12, str_replace(':', ' ', $claim->cptModifier($this->hcfa_proc_index)));

            // 24e. Diagnosis Pointer
            $tmp = '';
            foreach ($claim->diagIndexArray($this->hcfa_proc_index) as $value) {
                $value = chr($value + 64);
                $tmp .= $value;
            }

            $this->putHcfa($lino, 45, 4, $tmp);

            // 24f. Charges
            $this->putHcfa($lino, 50, 8, str_replace(
                '.',
                ' ',
                sprintf('%8.2f', $claim->cptCharges($this->hcfa_proc_index))
            ));

            // 24g. Days or Units
            $this->putHcfa($lino, 59, 3, $claim->cptUnits($this->hcfa_proc_index));

            // 24h. EPSDT Family Plan
            //
            if ($claim->epsdtFlag()) {
                $this->putHcfa($lino, 63, 2, '03');
            }

            // 24j. Rendering Provider NPI
            $this->putHcfa($lino, 68, 10, $claim->providerNPI($this->hcfa_proc_index));
        }

        // 25. Federal Tax ID Number
        $this->putHcfa(56, 1, 15, $claim->billingFacilityETIN());
        if ($claim->federalIdType() == 'SY') {
            $this->putHcfa(56, 17, 1, 'X'); // The SSN checkbox
        } else {
            $this->putHcfa(56, 19, 1, 'X'); // The EIN checkbox
        }

        // 26. Patient's Account No.
        // Instructions say hyphens are not allowed.
        $this->putHcfa(56, 23, 15, "$pid-$encounter");

        // 27. Accept Assignment
        $this->putHcfa(56, $claim->billingFacilityAssignment() ? 38 : 43, 1, 'X');

        // 28. Total Charge
        $this->putHcfa(56, 52, 8, str_replace('.', ' ', sprintf('%8.2f', $clm_total_charges)));
        if (!$clm_total_charges) {
            $log .= "*** This claim has no charges!\n";
        }

        // 29. Amount Paid
        $this->putHcfa(56, 62, 8, str_replace('.', ' ', sprintf('%8.2f', $clm_amount_paid)));

        // 30. Reserved for NUCC use.

        // 33. Billing Provider: Phone Number
        $tmp = $claim->billingContactPhone();
        $this->putHcfa(57, 66, 3, substr($tmp, 0, 3));
        $this->putHcfa(57, 70, 3, substr($tmp, 3)); // slight adjustment for better look smw 030315
        $this->putHcfa(57, 73, 1, '-');
        $this->putHcfa(57, 74, 4, substr($tmp, 6));

        // 32. Service Facility Location Information: Name
        $this->putHcfa(58, 23, 25, $claim->facilityName());

        // 33. Billing Provider: Name
        if ($claim->federalIdType() == "SY") { // check entity type for NM*102 1 == person, 2 == non-person entity
            $firstName = $claim->providerFirstName();
            $lastName = $claim->providerLastName();
            $middleName = $claim->providerMiddleName();
            $suffixName = $claim->providerSuffixName();
            $billingProviderName = $lastName . ", " . $firstName . ", " . $middleName . ", " . $suffixName;
            $this->putHcfa(58, 50, 25, $billingProviderName);
        } else {
            $this->putHcfa(58, 50, 25, $claim->billingFacilityName());
        }

        // 32. Service Facility Location Information: Street
        $this->putHcfa(59, 23, 25, $claim->facilityStreet());

        // 33. Billing Provider: Name
        $this->putHcfa(59, 50, 25, $claim->billingFacilityStreet());

        // 31. Signature of Physician or Supplier

        if ($GLOBALS['cms_1500_box_31_format'] == 0) {
            $this->putHcfa(60, 1, 20, 'Signature on File');
        } elseif ($GLOBALS['cms_1500_box_31_format'] == 1) {
            $this->putHcfa(60, 1, 22, $claim->providerFirstName() . " " . $claim->providerLastName());
        }

        // 32. Service Facility Location Information: City State Zip
        $tmp = $claim->facilityCity() ? ($claim->facilityCity() . ' ') : '';
        $this->putHcfa(60, 23, 27, $tmp . $claim->facilityState() . ' ' .
            $claim->facilityZip());

        // 33. Billing Provider: City State Zip
        $tmp = $claim->billingFacilityCity() ? ($claim->billingFacilityCity() . ' ') : '';
        $this->putHcfa(60, 50, 27, $tmp . $claim->billingFacilityState() . ' ' .
            $claim->billingFacilityZip());

        // 31. Signature of Physician or Supplier: Date
        if ($GLOBALS['cms_1500_box_31_date'] > 0) {
            if ($GLOBALS['cms_1500_box_31_date'] == 1) {
                $date_of_service = $claim->serviceDate();
                $MDY = substr($date_of_service, 4, 2) .
                    " " . substr($date_of_service, 6, 2) .
                    " " . substr($date_of_service, 2, 2);
            } elseif ($GLOBALS['cms_1500_box_31_date'] == 2) {
                $MDY = date("m/d/y");
            }

            $this->putHcfa(61, 6, 10, $MDY);
        }

        // 32a. Service Facility NPI
        $this->putHcfa(61, 23, 10, $claim->facilityNPI());

        // 32b. Service Facility Other ID
        // Note that Medicare does NOT want this any more.
        if ($claim->providerGroupNumber()) {
            $this->putHcfa(61, 36, 2, $claim->providerNumberType());
            $this->putHcfa(61, 38, 11, $claim->providerGroupNumber());
        }

        // 33a. Billing Facility NPI
        $this->putHcfa(61, 50, 10, $claim->billingFacilityNPI());

        // 33b. Billing Facility Other ID
        // Note that Medicare does NOT want this any more.
        if ($claim->claimType() == 'MC') {
            $this->putHcfa(61, 63, 2, 'ZZ');
            $this->putHcfa(61, 65, 14, $claim->providerTaxonomy());
        } elseif ($claim->providerGroupNumber() && $claim->claimType() != 'MB') {
            $this->putHcfa(61, 63, 2, $claim->providerNumberType());
            $this->putHcfa(61, 65, 14, $claim->providerGroupNumber());
        }

        // Put an extra line here for compatibility with old hcfa text generated form
        $this->putHcfa(62, 1, 1, ' ');
        // put a couple more in so that multiple claims correctly print through the text file download
        $this->putHcfa(63, 1, 1, ' ');
        $this->putHcfa(64, 1, 1, ' ');
        return;
    }
}
