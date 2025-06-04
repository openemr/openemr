<?php

/*
 * edih_835_code_class.php
 *
 * Copyright 2016 Kevin McCormick <kevin@kt61p>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 */

/**
 * Codes that are unique to the 835 edi x12 type
 *
 * The element component separator and repetition seperator are optional
 *  ( see edih_x12_file class property delimiters[ds] [dr] )
 * The array code835 is created with keys for code types
 * Access the values with $text = $obj->get_835_code(key, code)
 * List the array keys with $obj->get_keys()
 *
 * @param string  $component_separator (optional)
 * @param string  $repetition_separator (optional)
 *
 * @return object
 */

use OpenEMR\Billing\BillingUtilities;

class edih_835_codes
{
//
//public $code835 = array();
    private $code835 = array();
    private $ds = '';
    private $dr = '';
// the key_match array is a concept of matching code lists to
// segment elements when diferent segments are looking for the same
// code or reference lists
//  -- a very tedious project and immediately put on hold
//public $key_match = array('HCR04'=>array('CRC02');
//
    function __construct($component_separator = '', $repetition_separator = '')
    {
        //
        //
        // these seperators are not necessarily used and composite
        // elements can (should) be separated out before submitting codes
        $this->ds = $component_separator;
        $this->dr = $repetition_separator;
        //
        // BPR Transaction Handling Code
        $this->code835['BPR01'] = array(
        "C" => "Pmt Accompanies RA",
        "D" => "Make Pmt Only",
        "H" => "Notification Only",
        "I" => "RA Information Only",
        "P" => "Pre-notification of Future Transfers",
        "U" => "Split Pmt and RA",
        "X" => "Handling Party's Option To Split Pmt and RA",
        );

        // CAS segment
        $this->code835['CAS_GROUP'] = array(
        "CO" => "Contractual Obligations",
        "CR" => "Corrections and Reversals",
        "OA" => "Other Adjustments",
        "PI" => "Payor Initiated Reductions",
        "PR" => "Patient Responsibility"
        );


        // Claim Status
        // loop 2100", Claim Payment Information
        $this->code835['CLAIM_STATUS'] = array (
        "1" => "Primary",
        "2" => "Secondary",
        "3" => "Tertiary",
        "4" => "Denied",
        "5" => "Pended",
        "10" => "Received, but not in process",
        "13" => "Suspended",
        "15" => "Suspended - investigation with field",
        "17" => "Suspended - review pending",
        "19" => "Primary, Frwd to Addtl Payer(s)",
        "20" => "Secondary, Frwd to Addtl Payer(s)",
        "21" => "Tertiary,  Frwd to Addtl Payer(s)",
        "22" => "Reversal of Previous Payment",
        "23" => "Not Our Claim, Frwd to Addtl Payer(s)",
        "25" => "Predetermination Pricing Only - No Payment",
        "27" => "Reviewed"
        );

        // Claim Transaction Code
        // loop 2100", Claim Payment Information
        $this->code835['CLP06'] = array (
        "12" => "Preferred Provider Org (PPO)",
        "13" => "Point of Service",
        "14" => "Exclusive Provider Org (EPO)",
        "15" => "Indemnity Insurance",
        "16" => "HMO Medicare Risk",
        "17" => "Dental Maintenance Org",
        "AM" => "Automobile Medical",
        "CS" => "Champus",
        "DH" => "Disability",
        "HM" => "Health Maintenance Org",
        "LA" => "Liability Medical",
        "MA" => "Medicare Part A",
        "MB" => "Medicare Part B",
        "MC" => "Medicaid",
        "OF" => "Other Federal Program",
        "TV" => "Title V",
        "VA" => "Veterans Affairs Program",
        "WC" => "Worker's Compensation",
        "ZZ" => "Mutually Defined",
        );


        // loop 2100 Claim Supplemantal Information
        // loop 2110 Service Supplemental Amount
        $this->code835['AMT'] = array(
        "AU" => "Coverage Amount",
        "B6" => "Allowed - Actual",
        "CA" => "Covered - Actual Days",
        "CD" => "Co-insured - Actual",
        "DY" => "Per Day Limit",
        "F5" => "Patient Amount Paid",
        "I" => "Interest",
        "KH" => "Deduction Amount Late Filing Reduction",
        "LA" => "Life-time Reserve - Actual",
        "LE" => "Life-time Reserve - Estimated",
        "NE" => "Non-Covered - Estimated",
        "NL" => "Net Billed",
        "NL" => "Negative Ledger Balance",
        "NR" => "Not Replaced Blood Units",
        "OU" => "Outlier Days",
        "PS" => "Prescription",
        "T" => "Tax",
        "T2" => "Total Claim Before Taxes",
        "VS" => "Visits",
        "ZK" => "Fed Mcr or Mcd Pmt Mandate - Cat 1",
        "ZL" => "Fed Mcr or Mcd Pmt Mandate - Cat 2",
        "ZM" => "Fed Mcr or Mcd Pmt Mandate - Cat 3",
        "ZN" => "Fed Mcr or Mcd Pmt Mandate - Cat 4",
        "ZO" => "Fed Mcr or Mcd Pmt Mandate - Cat 5",
        "ZZ" => "Mutually Defined"
        );



        // Provider Level Adjustment Codes
        $this->code835['PLB'] = array(
        "50" => "Late Charge",
        "51" => "Interest Penalty Charge",
        "72" => "Authorized Rtrn Refunds--Manual Inv",
        "90" => "Early Payment Allowance",
        "AA" => "Receivable",
        "AH" => "Origination Fee",
        "AM" => "Applied to Borrower's Acct",
        "AP" => "Acceleration of Benefits",
        "AW" => "Accelerated Pmt Wthhld",
        "B2" => "Rebate",
        "B3" => "Recovery Allowance",
        "BD" => "Bad Debt Adjustment",
        "BF" => "Balance Moved Forward",
        "BN" => "Bonus",
        "CA" => "Manual Claim Adjustment",
        "C5" => "Temporary Allowance",
        "CO" => "From Previous Balance",
        "CR" => "Capitation Int/Pmt/Adjustment",
        "CS" => "Adjustment",
        "CT" => "Capitation Payment",
        "CV" => "Capital Passthrough",
        "CW" => "Certified RN Anesth Passthru",
        "DM" => "Direct Med Edu Passthru",
        "E3" => "Withholding",
        "FB" => "Forwardng Balance",
        "FC" => "Fund Allocation",
        "GO" => "Graduate Med Edu Passthru",
        "HM" => "Hemo.",
        "IN" => "Interest on Claims in this Remit",
        "IR" => "IRS Withholding",
        "IP" => "Incentive Premium Payment",
        "IS" => "Interim Settlement",
        "J1" => "Non-Reimbursable",
        "L3" => "Penalty",
        "L6" => "Interest Owed",
        "LE" => "Levy",
        "LR" => "Medicare Late Cost Rpt Penalty",
        "LS" => "Lump Sum",
        "OA" => "Organ Acquisition",
        "OB" => "Offset for Affiliated",
        "OS" => "Outside Recovery Adjustment",
        "PI" => "Periodic Interim Payment",
        "PL" => "Payment Final",
        "PW" => "Penalty Withhold",
        "RE" => "Return on Equity",
        "RI" => "Reissued Check Amount",
        "RF" => "Refund Adjustment",
        "RS" => "Penalty Release",
        "SL" => "Student Loan Repayment",
        "SW" => "Settlement Withhold Amount",
        "TL" => "Third Party Liability",
        "WO" => "Overpayment Recovery",
        "WU" => "Unspecified Recovery",
        "ZZ" => "Unknown"
        );


        // Claim Adjustment Reason Codes
        // CAS01 CAS05 CAS08 ...
        $this->code835['CARC'] = BillingUtilities::CLAIM_ADJUSTMENT_REASON_CODES;

        // Remittance Advice Remark Codes
        $this->code835['RARC'] = BillingUtilities::REMITTANCE_ADVICE_REMARK_CODES;
    }
    // edih_835_codes
    public function classname()
    {
        return get_class($this);
    }
    //
    public function get_835_code($elem, $code)
    {
        //
        $e = (string)$elem;
        $val = '';
        if (($this->ds && strpos($code, $this->ds) !== false) || ($this->dr && strpos($code, $this->dr) !== false)) {
            if ($this->ds && strpos($code, $this->ds) !== false) {
                $cdar = explode($this->ds, $code);
                foreach ($cdar as $cd) {
                    if ($this->dr && strpos($code, $this->dr) !== false) {
                        $cdar2 = explode($this->dr, $code);
                        foreach ($cdar2 as $cd2) {
                            if (isset($this->code835[$e][$cd2])) {
                                $val .= $this->code835[$e][$cd2] . '; ';
                            } else {
                                $val .= "code $cd2 unknown ";
                            }
                        }
                    } else {
                        $val .= (isset($this->code835[$e][$cd]) ) ? $this->code835[$e][$cd] . ' ' : "code $cd unknown";
                    }
                }
            } elseif ($this->dr && strpos($code, $this->dr) != false) {
                $cdar = explode($this->dr, $code);
                foreach ($cdar as $cd) {
                    $val .= (isset($this->code835[$e][$cd]) ) ? $this->code835[$e][$cd] . '; ' : "code $cd unknown";
                }
            }
        } elseif (array_key_exists($e, $this->code835)) {
            $val = (isset($this->code835[$e][$code]) ) ? $this->code835[$e][$code] : "$e code $code unknown";
        } else {
            $val = "$e codes not available ($code)";
        }

        //
        return $val;
    }
    //
    public function get_keys()
    {
        return array_keys($this->code835);
    }
}
