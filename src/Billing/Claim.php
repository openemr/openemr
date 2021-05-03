<?php

/* Claim Class
 *
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2009-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

use InsuranceCompany;
use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Services\FacilityService;

class Claim
{
    const X12_VERSION = '005010X222A1';
    const NOC_CODES = array('J3301'); // special handling for not otherwise classified HCPCS/CPT, not many so can add more here

    public $pid;               // patient id
    public $encounter_id;      // encounter id
    public $procs;             // array of procedure rows from billing table
    public $diags;             // array of icd9 codes from billing table
    public $diagtype = "ICD10"; // diagnosis code_type; safe to assume ICD10 now
    public $x12_partner;       // row from x12_partners table
    public $encounter;         // row from form_encounter table
    public $facility;          // row from facility table
    public $billing_facility;  // row from facility table
    public $provider;          // row from users table (rendering provider)
    public $referrer;          // row from users table (referring provider)
    public $supervisor;        // row from users table (supervising provider)
    public $insurance_numbers; // row from insurance_numbers table for current payer
    public $supervisor_numbers;// row from insurance_numbers table for current payer
    public $patient_data;      // row from patient_data table
    public $billing_options;   // row from form_misc_billing_options table
    public $invoice;           // result from get_invoice_summary()
    public $payers;            // array of arrays, for all payers
    public $copay;             // total of copays from the ar_activity table
    public $facilityService;   // via matthew.vita orm work :)
    public $pay_to_provider;   // to be implemented in facility ui

    // This enforces the X12 Basic Character Set. Page A2.
    public function x12Clean($str)
    {
        return preg_replace('/[^A-Z0-9!"\\&\'()+,\\-.\\/;?=@ ]/', '', strtoupper($str));
    }

    // X12 likes 9 digit zip codes also moving this from X125010837P to pursue PSR-0 and PSR-4
    public function x12Zip($zip)
    {
        $zip = $this->x12Clean($zip);
        // this will take out dashes and pad with trailing 9s if not 9 digits
        return str_pad(
            preg_replace('/[^0-9]/', '', $zip),
            9,
            9,
            STR_PAD_RIGHT
        );
    }

    // Make sure dates have no formatting and zero filled becomes blank
    // Handles date time stamp formats as well
    public function cleanDate($date_field)
    {
        $cleandate = str_replace('-', '', substr($date_field, 0, 10));

        if (substr_count($cleandate, '0') == 8) {
            $cleandate = '';
        }

        return ($cleandate);
    }

    public function loadPayerInfo(&$billrow)
    {
        global $sl_err;
        $encounter_date = substr($this->encounter['date'], 0, 10);

        // Create the $payers array.  This contains data for all insurances
        // with the current one always at index 0, and the others in payment
        // order starting at index 1.
        //
        $this->payers = array();
        $this->payers[0] = array();
        $query = "SELECT * FROM insurance_data WHERE pid = ? AND (date <= ? OR date IS NULL) ORDER BY type ASC, date DESC";
        $dres = sqlStatement($query, array($this->pid, $encounter_date));
        $prevtype = '';
        while ($drow = sqlFetchArray($dres)) {
            if (strcmp($prevtype, $drow['type']) == 0) {
                continue;
            }

            $prevtype = $drow['type'];
            // Very important to look at entries with a missing provider because
            // they indicate no insurance as of the given date.
            if (empty($drow['provider'])) {
                continue;
            }

            $ins = count($this->payers);
            if ($drow['provider'] == $billrow['payer_id'] && empty($this->payers[0]['data'])) {
                $ins = 0;
            }

            $crow = sqlQuery("SELECT * FROM insurance_companies WHERE id = ?", array($drow['provider']));
            $orow = new InsuranceCompany($drow['provider']);
            $this->payers[$ins] = array();
            $this->payers[$ins]['data']    = $drow;
            $this->payers[$ins]['company'] = $crow;
            $this->payers[$ins]['object']  = $orow;
        }

        // This kludge hands most cases of a rare ambiguous situation, where
        // the primary insurance company is the same as the secondary.  It seems
        // nobody planned for that!
        //
        for ($i = 1; $i < count($this->payers); ++$i) {
            if (
                $billrow['process_date'] &&
                $this->payers[0]['data']['provider'] == $this->payers[$i]['data']['provider']
            ) {
                $tmp = $this->payers[0];
                $this->payers[0] = $this->payers[$i];
                $this->payers[$i] = $tmp;
            }
        }

        $this->using_modifiers = true;

        // Get payment and adjustment details if there are any previous payers.
        //
        $this->invoice = array();
        if ($this->payerSequence() != 'P') {
            $this->invoice = InvoiceSummary::arGetInvoiceSummary($this->pid, $this->encounter_id, true);
            // Secondary claims might not have modifiers in SQL-Ledger data.
            // In that case, note that we should not try to match on them.
            $this->using_modifiers = false;
            foreach ($this->invoice as $key => $trash) {
                if (strpos($key, ':')) {
                    $this->using_modifiers = true;
                }
            }
        }
    }

  // Constructor. Loads relevant database information.
  //
    public function __construct($pid, $encounter_id)
    {
        $this->pid = $pid;
        $this->encounter_id = $encounter_id;
        $this->procs = array();
        $this->diags = array();
        $this->copay = 0;

        $this->facilityService = new FacilityService();
        $this->pay_to_provider = ''; // will populate from facility someday :)


        // We need the encounter date before we can identify the payers.
        $sql = "SELECT * FROM form_encounter WHERE pid = ? AND encounter = ?";
        $this->encounter = sqlQuery($sql, array($this->pid, $this->encounter_id));

        // Sort by procedure timestamp in order to get some consistency.
        $sql = "SELECT b.id, b.date, b.code_type, b.code, b.pid, b.provider_id, " .
        "b.user, b.groupname, b.authorized, b.encounter, b.code_text, b.billed, " .
        "b.activity, b.payer_id, b.bill_process, b.bill_date, b.process_date, " .
        "b.process_file, b.modifier, b.units, b.fee, b.justify, b.target, b.x12_partner_id, " .
        "b.ndc_info, b.notecodes, b.revenue_code, ct.ct_diag " .
        "FROM billing as b " .
        "INNER JOIN code_types as ct ON b.code_type = ct.ct_key " .
        "WHERE ct.ct_claim = '1' AND ct.ct_active = '1' AND b.encounter = ? AND b.pid = ? AND " .
        "b.activity = '1' ORDER BY b.date, b.id";
        $res = sqlStatement($sql, array($this->encounter_id, $this->pid));
        while ($row = sqlFetchArray($res)) {
            // Save all diagnosis codes.
            if ($row['ct_diag'] == '1') {
                $this->diags[$row['code']] = $row['code'];
                continue;
            }

            if (!$row['units']) {
                $row['units'] = 1;
            }

            // Load prior payer data at the first opportunity in order to get
            // the using_modifiers flag that is referenced below.
            if (empty($this->procs)) {
                $this->loadPayerInfo($row);
            }

            // The consolidate duplicate procedures, which was previously here, was removed
            // from codebase on 12/9/15. Reason: Some insurance companies decline consolidated
            // procedures, and this can be left up to the billing coder when they input the items.

            // If there is a row-specific provider then get its details.
            if (!empty($row['provider_id'])) {
                // Get service provider data for this row.
                $sql = "SELECT * FROM users WHERE id = ?";
                $row['provider'] = sqlQuery($sql, array($row['provider_id']));
                // Get insurance numbers for this row's provider.
                $sql = "SELECT * FROM insurance_numbers " .
                "WHERE (insurance_company_id = ? OR insurance_company_id is NULL) AND provider_id = ?" .
                "ORDER BY insurance_company_id DESC LIMIT 1";
                $row['insurance_numbers'] = sqlQuery($sql, array($row['payer_id'], $row['provider_id']));
            }

            $this->procs[] = $row;
        }

        $resMoneyGot = sqlStatement(
            "SELECT pay_amount as PatientPay, session_id as id, " .
            "date(post_time) as date FROM ar_activity WHERE pid = ? AND encounter = ? AND " .
            "deleted IS NULL AND payer_type = 0 AND account_code = 'PCP'",
            array($this->pid, $this->encounter_id)
        );
          //new fees screen copay gives account_code='PCP'
        while ($rowMoneyGot = sqlFetchArray($resMoneyGot)) {
              $PatientPay = $rowMoneyGot['PatientPay'] * -1;
              $this->copay -= $PatientPay;
        }

        $sql = "SELECT * FROM x12_partners WHERE id = ?";
        $this->x12_partner = sqlQuery($sql, array($this->procs[0]['x12_partner_id']));

        $this->facility = $this->facilityService->getById($this->encounter['facility_id']);

        /*****************************************************************
        $provider_id = $this->procs[0]['provider_id'];
        *****************************************************************/
        $provider_id = $this->encounter['provider_id'];
        $sql = "SELECT * FROM users WHERE id = ?";
        $this->provider = sqlQuery($sql, array($provider_id));
        // Selecting the billing facility assigned  to the encounter.  If none,
        // try the first (and hopefully only) facility marked as a billing location.
        if (empty($this->encounter['billing_facility'])) {
              $this->billing_facility = $this->facilityService->getPrimaryBillingLocation();
        } else {
              $this->billing_facility = $this->facilityService->getById($this->encounter['billing_facility']);
        }

        $sql = "SELECT * FROM insurance_numbers " .
        "WHERE (insurance_company_id = ? OR insurance_company_id is NULL) AND provider_id = ? " .
        "ORDER BY insurance_company_id DESC LIMIT 1";
        $this->insurance_numbers = sqlQuery($sql, array($this->procs[0]['payer_id'], $provider_id));

        $sql = "SELECT * FROM patient_data WHERE pid = ? ORDER BY id LIMIT 1";
        $this->patient_data = sqlQuery($sql, array($this->pid));

        $sql = "SELECT fpa.* FROM forms JOIN form_misc_billing_options AS fpa " .
        "ON fpa.id = forms.form_id " .
        "WHERE forms.encounter = ? AND forms.pid = ? AND forms.deleted = 0 AND forms.formdir = 'misc_billing_options' " .
        "ORDER BY forms.date";
        $this->billing_options = sqlQuery($sql, array($this->encounter_id, $this->pid));

        $referrer_id = (empty($GLOBALS['MedicareReferrerIsRenderer']) ||
        $this->insurance_numbers['provider_number_type'] != '1C') ?
          $this->patient_data['ref_providerID'] : $provider_id;
        $sql = "SELECT * FROM users WHERE id = ?";
        $this->referrer = sqlQuery($sql, array($referrer_id));
        if (!$this->referrer) {
            $this->referrer = array();
        }

        $supervisor_id = $this->encounter['supervisor_id'];
        $sql = "SELECT * FROM users WHERE id = ?";
        $this->supervisor = sqlQuery($sql, array($supervisor_id));
        if (!$this->supervisor) {
            $this->supervisor = array();
        }

        $billing_options_id = $this->billing_options['provider_id'] ?? null;
        $sql = "SELECT * FROM users WHERE id = ?";
        $this->billing_prov_id = sqlQuery($sql, array($billing_options_id));
        if (!$this->billing_prov_id) {
            $this->billing_prov_id = array();
        }

        $sql = "SELECT * FROM insurance_numbers WHERE " .
          "(insurance_company_id = ? OR insurance_company_id is NULL) AND provider_id = ? " .
          "ORDER BY insurance_company_id DESC LIMIT 1";
        $this->supervisor_numbers = sqlQuery($sql, array($this->procs[0]['payer_id'], $supervisor_id));
        if (!$this->supervisor_numbers) {
            $this->supervisor_numbers = array();
        }
    }

  // Return an array of adjustments from the designated prior payer for the
  // designated procedure key (might be procedure:modifier), or for the claim
  // level.  For each adjustment give date, group code, reason code, amount.
  // Note this will include "patient responsibility" adjustments which are
  // not adjustments to OUR invoice, but they reduce the amount that the
  // insurance company pays.
  //
    public function payerAdjustments($ins, $code = 'Claim')
    {
        $aadj = array();

        // If we have no modifiers stored in SQL-Ledger for this claim,
        // then we cannot use a modifier passed in with the key.
        $tmp = strpos($code, ':');
        if ($tmp && !$this->using_modifiers) {
            $code = substr($code, 0, $tmp);
        }

        // For payments, source always starts with "Ins" or "Pt".
        // Nonzero adjustment reason examples:
        //   Ins1 adjust code 42 (Charges exceed ... (obsolete))
        //   Ins1 adjust code 45 (Charges exceed your contracted/ legislated fee arrangement)
        //   Ins1 adjust code 97 (Payment is included in the allowance for another service/procedure)
        //   Ins1 adjust code A2 (Contractual adjustment)
        //   Ins adjust Ins1
        //   adjust code 45
        // Zero adjustment reason examples:
        //   Co-pay: 25.00
        //   Coinsurance: 11.46  (code 2)   Note: fix remits to identify insurance
        //   To deductible: 0.22 (code 1)   Note: fix remits to identify insurance
        //   To copay Ins1 (manual entry)
        //   To ded'ble Ins1 (manual entry)

        if (!empty($this->invoice[$code])) {
            $date = '';
            $deductible  = 0;
            $coinsurance = 0;
            $inslabel = ($this->payerSequence($ins) == 'S') ? 'Ins2' : 'Ins1';
            $insnumber = substr($inslabel, 3);

            // Compute this procedure's patient responsibility amount as of this
            // prior payer, which is the original charge minus all insurance
            // payments and "hard" adjustments up to this payer.
            $ptresp = $this->invoice[$code]['chg'] + $this->invoice[$code]['adj'];
            foreach ($this->invoice[$code]['dtl'] as $key => $value) {
                // plv (from ar_activity.payer_type) exists to
                // indicate the payer level.
                if (isset($value['pmt']) && $value['pmt'] != 0) {
                    if ($value['plv'] > 0 && $value['plv'] <= $insnumber) {
                        $ptresp -= $value['pmt'];
                    }
                } elseif (isset($value['chg']) && trim(substr($key, 0, 10))) {
                  // non-blank key indicates this is an adjustment and not a charge
                    if ($value['plv'] > 0 && $value['plv'] <= $insnumber) {
                        $ptresp += $value['chg']; // adjustments are negative charges
                    }
                }

                $msp = isset($value['msp']) ? $value['msp'] : null; // record the reason for adjustment
            }

            if ($ptresp < 0) {
                $ptresp = 0; // we may be insane but try to hide it
            }

            // Main loop, to extract adjustments for this payer and procedure.
            foreach ($this->invoice[$code]['dtl'] as $key => $value) {
                $tmp = str_replace('-', '', trim(substr($key, 0, 10)));
                if ($tmp) {
                    $date = $tmp;
                }

                if ($tmp && $value['pmt'] == 0) { // not original charge and not a payment
                    $rsn = $value['rsn'];
                    $chg = 0 - $value['chg']; // adjustments are negative charges

                    $gcode = 'CO'; // default group code = contractual obligation
                    $rcode = '45'; // default reason code = max fee exceeded (code 42 is obsolete)

                    if (preg_match("/Ins adjust $inslabel/i", $rsn, $tmp)) {
                        // From manual post. Take the defaults.
                    } elseif (preg_match("/To copay $inslabel/i", $rsn, $tmp) && !$chg) {
                        $coinsurance = $ptresp; // from manual post
                        continue;
                    } elseif (preg_match("/To ded'ble $inslabel/i", $rsn, $tmp) && !$chg) {
                        $deductible = $ptresp; // from manual post
                        continue;
                    } elseif (preg_match("/$inslabel copay: (\S+)/i", $rsn, $tmp) && !$chg) {
                        $coinsurance = $tmp[1]; // from 835 as of 6/2007
                        continue;
                    } elseif (preg_match("/$inslabel coins: (\S+)/i", $rsn, $tmp) && !$chg) {
                        $coinsurance = $tmp[1]; // from 835 and manual post as of 6/2007
                        continue;
                    } elseif (preg_match("/$inslabel dedbl: (\S+)/i", $rsn, $tmp) && !$chg) {
                        $deductible = $tmp[1]; // from 835 and manual post as of 6/2007
                        continue;
                    } elseif (preg_match("/$inslabel ptresp: (\S+)/i", $rsn, $tmp) && !$chg) {
                        continue; // from 835 as of 6/2007
                    } elseif (preg_match("/$inslabel adjust code (\S+)/i", $rsn, $tmp)) {
                        $rcode = $tmp[1]; // from 835
                    } elseif (preg_match("/$inslabel/i", $rsn, $tmp)) {
                        // Take the defaults.
                    } elseif (preg_match('/Ins(\d)/i', $rsn, $tmp) && $tmp[1] != $insnumber) {
                        continue; // it's for some other payer
                    } elseif ($insnumber == '1') {
                        if (preg_match("/Adjust code (\S+)/i", $rsn, $tmp)) {
                            $rcode = $tmp[1]; // from 835
                        } elseif ($chg) {
                            // Other adjustments default to Ins1.
                        } elseif (
                            preg_match("/Co-pay: (\S+)/i", $rsn, $tmp) ||
                            preg_match("/Coinsurance: (\S+)/i", $rsn, $tmp)
                        ) {
                            $coinsurance = 0 + $tmp[1]; // from 835 before 6/2007
                            continue;
                        } elseif (preg_match("/To deductible: (\S+)/i", $rsn, $tmp)) {
                            $deductible = 0 + $tmp[1]; // from 835 before 6/2007
                            continue;
                        } else {
                            continue; // there is no adjustment amount
                        }
                    } else {
                        continue; // it's for primary and that's not us
                    }

                    if ($rcode == '42') {
                        $rcode = '45'; // reason 42 is obsolete
                    }

                    $aadj[] = array($date, $gcode, $rcode, sprintf('%.2f', $chg));
                } // end if
            } // end foreach

            // If we really messed it up, at least avoid negative numbers.
            if ($coinsurance > $ptresp) {
                $coinsurance = $ptresp;
            }

            if ($deductible  > $ptresp) {
                $deductible  = $ptresp;
            }

            // Find out if this payer paid anything at all on this claim.  This will
            // help us allocate any unknown patient responsibility amounts.
            $thispaidanything = 0;
            foreach ($this->invoice as $codekey => $codeval) {
                foreach ($codeval['dtl'] as $key => $value) {
                    // plv exists to indicate the payer level.
                    if ($value['plv'] == $insnumber) {
                        $thispaidanything += $value['pmt'];
                    }
                }
            }

            // Allocate any unknown patient responsibility by guessing if the
            // deductible has been satisfied.
            if ($thispaidanything) {
                $coinsurance = $ptresp - $deductible;
            } else {
                $deductible = $ptresp - $coinsurance;
            }

            $deductible  = sprintf('%.2f', $deductible);
            $coinsurance = sprintf('%.2f', $coinsurance);

            if ($date && $deductible != 0) {
                $aadj[] = array($date, 'PR', '1', $deductible, $msp);
            }

            if ($date && $coinsurance != 0) {
                $aadj[] = array($date, 'PR', '2', $coinsurance, $msp);
            }
        } // end if

        return $aadj;
    }

  // Return date, total payments and total "hard" adjustments from the given
  // prior payer. If $code is specified then only that procedure key is
  // selected, otherwise it's for the whole claim.
  //
    public function payerTotals($ins, $code = '')
    {
        // If we have no modifiers stored in SQL-Ledger for this claim,
        // then we cannot use a modifier passed in with the key.
        $tmp = strpos($code, ':');
        if ($tmp && !$this->using_modifiers) {
            $code = substr($code, 0, $tmp);
        }

        $inslabel = ($this->payerSequence($ins) == 'S') ? 'Ins2' : 'Ins1';
        $insnumber = substr($inslabel, 3);
        $paytotal = 0;
        $adjtotal = 0;
        $date = '';
        foreach ($this->invoice as $codekey => $codeval) {
            if ($code && strcmp($codekey, $code) != 0) {
                continue;
            }

            foreach ($codeval['dtl'] as $key => $value) {
                // plv (from ar_activity.payer_type) exists to
                // indicate the payer level.
                if ($value['plv'] == $insnumber) {
                    if (!$date) {
                        $date = str_replace('-', '', trim(substr($key, 0, 10)));
                    }

                    $paytotal += $value['pmt'];
                }
            }

            $aarr = $this->payerAdjustments($ins, $codekey);
            foreach ($aarr as $a) {
                if (strcmp($a[1], 'PR') != 0) {
                    $adjtotal += $a[3];
                }

                if (!$date) {
                    $date = $a[0];
                }
            }
        }

        return array($date, sprintf('%.2f', $paytotal), sprintf('%.2f', $adjtotal));
    }

  // Return the amount already paid by the patient.
  //
    public function patientPaidAmount()
    {
        // For primary claims $this->invoice is not loaded, so get the co-pay
        // from the ar_activity table instead.
        if (empty($this->invoice)) {
            return $this->copay;
        }

        //
        $amount = 0;
        foreach ($this->invoice as $codekey => $codeval) {
            foreach ($codeval['dtl'] as $key => $value) {
                // plv exists to indicate the payer level.

                if (empty($value['plv'])) { // 0 indicates patient
                    $value['pmt'] = $value['pmt'] ?? null;
                    $amount += $value['pmt'];
                }
            }
        }

        return sprintf('%.2f', $amount);
    }

  // Return invoice total, including adjustments but not payments.
  //
    public function invoiceTotal()
    {
        $amount = 0;
        foreach ($this->invoice as $codekey => $codeval) {
            $amount += $codeval['chg'];
        }

        return sprintf('%.2f', $amount);
    }

  // Number of procedures in this claim.
    public function procCount()
    {
        return count($this->procs);
    }

  // Number of payers for this claim. Ranges from 1 to 3.
    public function payerCount()
    {
        return count($this->payers);
    }

    public function x12gsversionstring()
    {
        return Claim::X12_VERSION;
    }

    public function x12gssenderid()
    {
        $tmp = ($this->x12_partner['x12_sender_id'] ?? '');
        while (strlen($tmp) < 15) {
            $tmp .= " ";
        }

        return $tmp;
    }

    public function x12gs03()
    {
       /*
      * GS03: Application Receiver's Code
      * Code Identifying Party Receiving Transmission
      *
      * In most cases, the ISA08 and GS03 are the same. However
      *
      * In some clearing houses ISA08 and GS03 are different
      * Example: https://www.acs-gcro.com/downloads/DOL/DOL_CG_X12N_5010_837_v1_02.pdf - Page 18
      * In this .pdf, the ISA08 is specified to be 100000 while the GS03 is specified to be 77044
      *
      * Therefore if the x12_gs03 segement is explicitly specified we use that value,
      * otherwise we simply use the same receiver ID as specified for ISA03
        */
        if (!empty($this->x12_partner['x12_gs03'])) {
            return $this->x12_partner['x12_gs03'];
        } else {
            return ($this->x12_partner['x12_receiver_id'] ?? '');
        }
    }

    public function x12gsreceiverid()
    {
        $tmp = ($this->x12_partner['x12_receiver_id'] ?? '');
        while (strlen($tmp) < 15) {
            $tmp .= " ";
        }

        return $tmp;
    }

    public function x12gsisa05()
    {
        return ($this->x12_partner['x12_isa05'] ?? '');
    }
//adding in public functions for isa 01 - isa 04

    public function x12gsisa01()
    {
        return ($this->x12_partner['x12_isa01'] ?? '');
    }

    public function x12gsisa02()
    {
        return ($this->x12_partner['x12_isa02'] ?? '');
    }

    public function x12gsisa03()
    {
        return ($this->x12_partner['x12_isa03'] ?? '');
    }
    public function x12gsisa04()
    {
        return ($this->x12_partner['x12_isa04'] ?? '');
    }

/////////
    public function x12gsisa07()
    {
        return ($this->x12_partner['x12_isa07'] ?? '');
    }

    public function x12gsisa14()
    {
        return ($this->x12_partner['x12_isa14'] ?? '');
    }

    public function x12gsisa15()
    {
        return ($this->x12_partner['x12_isa15'] ?? '');
    }

    public function x12gsgs02()
    {
        $tmp = ($this->x12_partner['x12_gs02'] ?? '');
        if ($tmp === '') {
            $tmp = ($this->x12_partner['x12_sender_id'] ?? '');
        }

        return $tmp;
    }

    public function x12gsper06()
    {
        return $this->x12_partner['x12_per06'];
    }

    public function cliaCode()
    {
        return $this->x12Clean(trim($this->facility['domain_identifier']));
    }

    public function billingFacilityName()
    {
        return $this->x12Clean(trim($this->billing_facility['name']));
    }

    public function billingFacilityStreet()
    {
        return $this->x12Clean(trim($this->billing_facility['street']));
    }

    public function billingFacilityCity()
    {
        return $this->x12Clean(trim($this->billing_facility['city']));
    }

    public function billingFacilityState()
    {
        return $this->x12Clean(trim($this->billing_facility['state']));
    }

    public function billingFacilityZip()
    {
        return $this->x12Clean(trim($this->billing_facility['postal_code']));
    }

    public function billingFacilityETIN()
    {
        return $this->x12Clean(trim(str_replace('-', '', $this->billing_facility['federal_ein'])));
    }

    public function billingFacilityNPI()
    {
        return $this->x12Clean(trim($this->billing_facility['facility_npi']));
    }

    public function federalIdType()
    {
        if ($this->billing_facility['tax_id_type']) {
            return $this->billing_facility['tax_id_type'];
        } else {
            return null;
        }
    }

  # The billing facility and the patient must both accept for this to return true.
    public function billingFacilityAssignment($ins = 0)
    {
        $tmp = strtoupper($this->payers[$ins]['data']['accept_assignment'] ?? '');
        if (strcmp($tmp, 'FALSE') == 0) {
            return '0';
        }

        return !empty($this->billing_facility['accepts_assignment']);
    }

    public function billingContactName()
    {
        return $this->x12Clean(trim($this->billing_facility['attn']));
    }

    public function billingContactPhone()
    {
        if (
            preg_match(
                "/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",
                $this->billing_facility['phone'],
                $tmp
            )
        ) {
            return $tmp[1] . $tmp[2] . $tmp[3];
        }

        return '';
    }

    public function billingContactEmail()
    {
        return $this->x12Clean(trim($this->billing_facility['email']));
    }

    public function facilityName()
    {
        return $this->x12Clean(trim($this->facility['name']));
    }

    public function facilityStreet()
    {
        return $this->x12Clean(trim($this->facility['street']));
    }

    public function facilityCity()
    {
        return $this->x12Clean(trim($this->facility['city']));
    }

    public function facilityState()
    {
        return $this->x12Clean(trim($this->facility['state']));
    }

    public function facilityZip()
    {
        return $this->x12Clean(trim($this->facility['postal_code']));
    }

    public function facilityETIN()
    {
        return $this->x12Clean(trim(str_replace('-', '', $this->facility['federal_ein'])));
    }

    public function facilityNPI()
    {
        return $this->x12Clean(trim($this->facility['facility_npi']));
    }

    public function facilityPOS()
    {
        if ($this->encounter['pos_code']) {
            return sprintf('%02d', trim($this->encounter['pos_code']));
        } else {
            return sprintf('%02d', trim($this->facility['pos_code']));
        }
    }

    public function facilityTaxonomy()
    {
        return $this->x12Clean(trim($this->facility['facility_taxonomy']));
    }

    public function clearingHouseName()
    {
        return $this->x12Clean(trim($this->x12_partner['name'] ?? ''));
    }

    public function clearingHouseETIN()
    {
        return $this->x12Clean(trim(str_replace('-', '', ($this->x12_partner['id_number'] ?? ''))));
    }

    public function providerNumberType($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->insurance_numbers : $this->procs[$prockey]['insurance_numbers'];
        return ($tmp['provider_number_type'] ?? '');
    }

    public function providerNumber($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->insurance_numbers : $this->procs[$prockey]['insurance_numbers'];
        return $this->x12Clean(trim(str_replace('-', '', ($tmp['provider_number'] ?? ''))));
    }

    public function providerGroupNumber($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->insurance_numbers : $this->procs[$prockey]['insurance_numbers'];
        return $this->x12Clean(trim(str_replace('-', '', ($tmp['group_number'] ?? ''))));
    }

  // Returns 'P', 'S' or 'T'.
  //
    public function payerSequence($ins = 0)
    {
        return strtoupper(substr(($this->payers[$ins]['data']['type'] ?? ''), 0, 1));
    }

  // Returns the HIPAA code of the patient-to-subscriber relationship.
  //
    public function insuredRelationship($ins = 0)
    {
        $tmp = strtolower(($this->payers[$ins]['data']['subscriber_relationship'] ?? ''));
        if (strcmp($tmp, 'self') == 0) {
            return '18';
        }

        if (strcmp($tmp, 'spouse') == 0) {
            return '01';
        }

        if (strcmp($tmp, 'child') == 0) {
            return '19';
        }

        if (strcmp($tmp, 'other') == 0) {
            return 'G8';
        }

        return $tmp; // should not happen
    }

    public function insuredTypeCode($ins = 0)
    {
        if (strcmp($this->claimType($ins), 'MB') == 0 && $this->payerSequence($ins) != 'P') {
            return $this->payers[$ins]['data']['policy_type'];
        } else {
            return '';
        }
    }

  // Is the patient also the subscriber?
  //
    public function isSelfOfInsured($ins = 0)
    {
        $tmp = strtolower($this->payers[$ins]['data']['subscriber_relationship'] ?? '');
        return (strcmp($tmp, 'self') == 0);
    }

    public function planName($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['plan_name'] ?? ''));
    }

    public function policyNumber($ins = 0)
    {
 // "ID"
        return $this->x12Clean(trim($this->payers[$ins]['data']['policy_number'] ?? ''));
    }

    public function groupNumber($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['group_number'] ?? ''));
    }

    public function groupName($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['subscriber_employer'] ?? ''));
    }

  // Claim types are:
  // 16 Other HCFA
  // MB Medicare Part B
  // MC Medicaid
  // CH ChampUSVA
  // CH ChampUS
  // BL Blue Cross Blue Shield
  // 16 FECA
  // 09 Self Pay
  // 10 Central Certification
  // 11 Other Non-Federal Programs
  // 12 Preferred Provider Organization (PPO)
  // 13 Point of Service (POS)
  // 14 Exclusive Provider Organization (EPO)
  // 15 Indemnity Insurance
  // 16 Health Maintenance Organization (HMO) Medicare Risk
  // AM Automobile Medical
  // CI Commercial Insurance Co.
  // DS Disability
  // HM Health Maintenance Organization
  // LI Liability
  // LM Liability Medical
  // OF Other Federal Program
  // TV Title V
  // VA Veterans Administration Plan
  // WC Workers Compensation Health Plan
  // ZZ Mutually Defined
  //
    public function claimType($ins = 0)
    {
        if (empty($this->payers[$ins]['object'])) {
            return '';
        }

        return $this->payers[$ins]['object']->get_ins_claim_type();
    }

    public function claimTypeRaw($ins = 0)
    {
        if (empty($this->payers[$ins]['object'])) {
            return 0;
        }

        return $this->payers[$ins]['object']->get_ins_type_code();
    }

    public function insuredLastName($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['subscriber_lname'] ?? ''));
    }

    public function insuredFirstName($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['subscriber_fname'] ?? ''));
    }

    public function insuredMiddleName($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['subscriber_mname'] ?? ''));
    }

    public function insuredStreet($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['subscriber_street'] ?? ''));
    }

    public function insuredCity($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['subscriber_city'] ?? ''));
    }

    public function insuredState($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['subscriber_state'] ?? ''));
    }

    public function insuredZip($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['data']['subscriber_postal_code'] ?? ''));
    }

    public function insuredPhone($ins = 0)
    {
        if (
            preg_match(
                "/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",
                ($this->payers[$ins]['data']['subscriber_phone'] ?? ''),
                $tmp
            )
        ) {
            return $tmp[1] . $tmp[2] . $tmp[3];
        }

        return '';
    }

    public function insuredDOB($ins = 0)
    {
        return str_replace('-', '', ($this->payers[$ins]['data']['subscriber_DOB'] ?? ''));
    }

    public function insuredSex($ins = 0)
    {
        return strtoupper(substr(($this->payers[$ins]['data']['subscriber_sex'] ?? ''), 0, 1));
    }

    public function payerName($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['company']['name'] ?? ''));
    }

    public function payerAttn($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['company']['attn'] ?? ''));
    }

    public function payerStreet($ins = 0)
    {
        if (empty($this->payers[$ins]['object'])) {
            return '';
        }

        $tmp = $this->payers[$ins]['object'];
        $tmp = $tmp->get_address();
        return $this->x12Clean(trim($tmp->get_line1()));
    }

    public function payerCity($ins = 0)
    {
        if (empty($this->payers[$ins]['object'])) {
            return '';
        }

        $tmp = $this->payers[$ins]['object'];
        $tmp = $tmp->get_address();
        return $this->x12Clean(trim($tmp->get_city()));
    }

    public function payerState($ins = 0)
    {
        if (empty($this->payers[$ins]['object'])) {
            return '';
        }

        $tmp = $this->payers[$ins]['object'];
        $tmp = $tmp->get_address();
        return $this->x12Clean(trim($tmp->get_state()));
    }

    public function payerZip($ins = 0)
    {
        if (empty($this->payers[$ins]['object'])) {
            return '';
        }

        $tmp = $this->payers[$ins]['object'];
        $tmp = $tmp->get_address();
        return $this->x12Clean(trim($tmp->get_zip()));
    }

    public function payerID($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['company']['cms_id'] ?? ''));
    }

    public function payerAltID($ins = 0)
    {
        return $this->x12Clean(trim($this->payers[$ins]['company']['alt_cms_id']));
    }

    public function patientLastName()
    {
        return $this->x12Clean(trim($this->patient_data['lname']));
    }

    public function patientFirstName()
    {
        return $this->x12Clean(trim($this->patient_data['fname']));
    }

    public function patientMiddleName()
    {
        return $this->x12Clean(trim($this->patient_data['mname']));
    }

    public function patientStreet()
    {
        return $this->x12Clean(trim($this->patient_data['street']));
    }

    public function patientCity()
    {
        return $this->x12Clean(trim($this->patient_data['city']));
    }

    public function patientState()
    {
        return $this->x12Clean(trim($this->patient_data['state']));
    }

    public function patientZip()
    {
        return $this->x12Clean(trim($this->patient_data['postal_code']));
    }

    public function patientPhone()
    {
        $ptphone = $this->patient_data['phone_home'];
        if (!$ptphone) {
            $ptphone = $this->patient_data['phone_biz'];
        }

        if (!$ptphone) {
            $ptphone = $this->patient_data['phone_cell'];
        }

        if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/", $ptphone, $tmp)) {
            return $tmp[1] . $tmp[2] . $tmp[3];
        }

        return '';
    }

    public function patientDOB()
    {
        return str_replace('-', '', $this->patient_data['DOB']);
    }

    public function patientSex()
    {
        return strtoupper(substr($this->patient_data['sex'], 0, 1));
    }

  // Patient Marital Status: M = Married, S = Single, or something else.
    public function patientStatus()
    {
        return strtoupper(substr($this->patient_data['status'], 0, 1));
    }

  // This should be UNEMPLOYED, STUDENT, PT STUDENT, or anything else to
  // indicate employed.
    public function patientOccupation()
    {
        return strtoupper($this->x12Clean(trim($this->patient_data['occupation'])));
    }

    public function cptCode($prockey)
    {
        return $this->x12Clean(trim($this->procs[$prockey]['code']));
    }

    public function cptModifier($prockey)
    {
        // Split on the colon or space and clean each modifier
        $mods = array();
        $cln_mods = array();
        $mods = preg_split("/[: ]/", trim($this->procs[$prockey]['modifier']));
        foreach ($mods as $mod) {
            array_push($cln_mods, $this->x12Clean($mod));
        }

        return (implode(':', $cln_mods));
    }

    public function cptNotecodes($prockey)
    {
        return $this->x12Clean(trim($this->procs[$prockey]['notecodes']));
    }

  // Returns the procedure code, followed by ":modifier" if there is one.
    public function cptKey($prockey)
    {
        $tmp = $this->cptModifier($prockey);
        return $this->cptCode($prockey) . ($tmp ? ":$tmp" : "");
    }

    public function cptCharges($prockey)
    {
        return $this->x12Clean(trim($this->procs[$prockey]['fee']));
    }

    public function cptUnits($prockey)
    {
        if (empty($this->procs[$prockey]['units'])) {
            return '1';
        }

        return $this->x12Clean(trim($this->procs[$prockey]['units']));
    }

  // NDC drug ID.
    public function cptNDCID($prockey)
    {
        $ndcinfo = $this->procs[$prockey]['ndc_info'];
        if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $ndcinfo, $tmp)) {
            $ndc = $tmp[1];
            if (preg_match('/^(\d+)-(\d+)-(\d+)$/', $ndc, $tmp)) {
                return sprintf('%05d%04d%02d', $tmp[1], $tmp[2], $tmp[3]);
            }

            return $this->x12Clean($ndc); // format is bad but return it anyway
        }

        return '';
    }

  // NDC drug unit of measure code.
    public function cptNDCUOM($prockey)
    {
        $ndcinfo = $this->procs[$prockey]['ndc_info'];
        if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $ndcinfo, $tmp)) {
            return $this->x12Clean($tmp[2]);
        }

        return '';
    }

  // NDC drug number of units.
    public function cptNDCQuantity($prockey)
    {
        $ndcinfo = $this->procs[$prockey]['ndc_info'];
        if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $ndcinfo, $tmp)) {
            return $this->x12Clean(ltrim($tmp[3], '0'));
        }

        return '';
    }

    // Not Otherwise Classified codes require a description on the SV1 line after the modifiers
    public function cptNOC($prockey)
    {
        return in_array($this->cptCode($prockey), Claim::NOC_CODES);
    }

    public function cptDescription($prockey)
    {
        return $this->x12Clean($this->procs[$prockey]['code_text']);
    }

    public function onsetDate()
    {
        return $this->cleanDate($this->encounter['onset_date']);
    }

    public function onsetDateValid()
    {
        return $this->onsetDate() !== '';
    }

    public function serviceDate()
    {
        return str_replace('-', '', substr($this->encounter['date'], 0, 10));
    }

    public function priorAuth()
    {
        return $this->x12Clean(trim($this->billing_options['prior_auth_number'] ?? ''));
    }

    public function isRelatedEmployment()
    {
        return !empty($this->billing_options['employment_related']);
    }

    public function isRelatedAuto()
    {
        return !empty($this->billing_options['auto_accident']);
    }

    public function isRelatedOther()
    {
        return !empty($this->billing_options['other_accident']);
    }

    public function autoAccidentState()
    {
        return $this->x12Clean(trim($this->billing_options['accident_state']));
    }

    public function isUnableToWork()
    {
        return !empty($this->billing_options['is_unable_to_work']);
    }

    public function offWorkFrom()
    {
        return $this->cleanDate($this->billing_options['off_work_from']);
    }

    public function offWorkTo()
    {
        return $this->cleanDate($this->billing_options['off_work_to']);
    }

    public function isHospitalized()
    {
        return !empty($this->billing_options['is_hospitalized']);
    }

    public function hospitalizedFrom()
    {
        return $this->cleanDate($this->billing_options['hospitalization_date_from']);
    }

    public function hospitalizedFromDateValid()
    {
        return $this->hospitalizedFrom() !== '';
    }

    public function hospitalizedTo()
    {
        return $this->cleanDate($this->billing_options['hospitalization_date_to']);
    }
    public function hospitalizedToDateValid()
    {
        return $this->hospitalizedTo() !== '';
    }

    public function isOutsideLab()
    {
        return !empty($this->billing_options['outside_lab']);
    }

    public function outsideLabAmount()
    {
        return sprintf('%.2f', 0 + $this->billing_options['lab_amount']);
    }

    public function medicaidReferralCode()
    {
        return $this->x12Clean(trim($this->billing_options['medicaid_referral_code']));
    }

    public function epsdtFlag()
    {
        return $this->x12Clean(trim($this->billing_options['epsdt_flag'] ?? ''));
    }

    public function medicaidResubmissionCode()
    {
        return $this->x12Clean(trim($this->billing_options['medicaid_resubmission_code'] ?? ''));
    }

    public function medicaidOriginalReference()
    {
        return $this->x12Clean(trim($this->billing_options['medicaid_original_reference'] ?? ''));
    }

    public function frequencyTypeCode()
    {
        return (!empty($this->billing_options['replacement_claim']) && ($this->billing_options['replacement_claim'] == 1)) ? '7' : '1';
    }

    public function icnResubmissionNumber()
    {
        return $this->x12Clean($this->billing_options['icn_resubmission_number']);
    }

    public function additionalNotes()
    {
        return $this->x12Clean(trim($this->billing_options['comments'] ?? ''));
    }

    public function miscOnsetDate()
    {
        return $this->cleanDate($this->billing_options['onset_date'] ?? '');
    }

    public function miscOnsetDateValid()
    {
        return $this->miscOnsetDate() !== '';
    }

    public function dateInitialTreatment()
    {
        return $this->cleanDate($this->billing_options['date_initial_treatment'] ?? '');
    }

    public function dateInitialTreatmentValid()
    {
        return $this->dateInitialTreatment() !== '';
    }

    public function box14Qualifier()
    {
        // If no box qualifier specified use "431" indicating Onset
        return empty($this->billing_options['box_14_date_qual']) ? '431' :
            $this->billing_options['box_14_date_qual'];
    }

    public function box15Qualifier()
    {
        // If no box qualifier specified use "454" indicating Initial Treatment
        return empty($this->billing_options['box_15_date_qual']) ? '454' :
            $this->billing_options['box_15_date_qual'];
    }

    public function box17Qualifier()
    {
        //If no box qualifier specified use "DK" for ordering provider
        //someday might make mbo form the place to set referring instead of demographics under choices
        return empty($this->billing_options['provider_qualifier_code']) ? '' :
            $this->billing_options['provider_qualifier_code'];
    }

  // Returns an array of unique diagnoses.  Periods are stripped by default
  // Option to keep periods is to support HCFA 1500 02/12 version
    public function diagArray($strip_periods = true)
    {
        $da = array();
        foreach ($this->procs as $row) {
            $atmp = explode(':', $row['justify']);
            foreach ($atmp as $tmp) {
                if (!empty($tmp)) {
                    $code_data = explode('|', $tmp);

                    // If there was a | in the code data, the the first part of the array is the type, and the second is the identifier
                    if (!empty($code_data[1])) {
                        // This is the simplest way to determine if the claim is using ICD9 or ICD10 codes
                        // a mix of code types is generally not allowed as there is only one specifier for all diagnoses on HCFA-1500 form
                        // and there would be ambiguity with E and V codes
                        $this->diagtype = $code_data[0];

                        //code is in the second part of the $code_data array.
                        if ($strip_periods == true) {
                                $diag = str_replace('.', '', $code_data[1]);
                        } else {
                            $diag = $code_data[1];
                        }
                    } else {
                        //No prepended code type label
                        if ($strip_periods) {
                            $diag = str_replace('.', '', $code_data[0]);
                        } else {
                            $diag = $code_data[0];
                        }
                    }

                    $da[$diag] = $diag;
                }
            }
        }

        return $da;
    }

  // Compute one 1-relative index in diagArray for the given procedure.
  // This function is obsolete, use diagIndexArray() instead.
    public function diagIndex($prockey)
    {
        $da = $this->diagArray();
        $tmp = explode(':', $this->procs[$prockey]['justify']);
        if (empty($tmp)) {
            return '';
        }

        $diag = str_replace('.', '', $tmp[0]);
        $i = 0;
        foreach ($da as $value) {
            ++$i;
            if (strcmp($value, $diag) == 0) {
                return $i;
            }
        }

        return '';
    }

  // Compute array of 1-relative diagArray indices for the given procedure.
    public function diagIndexArray($prockey)
    {
        $dia = array();
        $da = $this->diagArray();
        $atmp = explode(':', $this->procs[$prockey]['justify']);
        foreach ($atmp as $tmp) {
            if (!empty($tmp)) {
                $code_data = explode('|', $tmp);
                if (!empty($code_data[1])) {
                    //Strip the prepended code type label
                    $diag = str_replace('.', '', $code_data[1]);
                } else {
                    //No prepended code type label
                    $diag = str_replace('.', '', $code_data[0]);
                }

                $i = 0;
                foreach ($da as $value) {
                    ++$i;
                    if (strcmp($value, $diag) == 0) {
                        $dia[] = $i;
                    }
                }
            }
        }

        return $dia;
    }

    public function providerLastName($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->provider : $this->procs[$prockey]['provider'];
        return $this->x12Clean(trim($tmp['lname']));
    }

    public function providerFirstName($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->provider : $this->procs[$prockey]['provider'];
        return $this->x12Clean(trim($tmp['fname']));
    }

    public function providerMiddleName($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->provider : $this->procs[$prockey]['provider'];
        return $this->x12Clean(trim($tmp['mname']));
    }

    public function providerSuffixName($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
            $this->provider : $this->procs[$prockey]['provider'];
        return $this->x12Clean(trim($tmp['suffix']));
    }

    public function providerNPI($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->provider : $this->procs[$prockey]['provider'];
        return $this->x12Clean(trim($tmp['npi']));
    }

    public function NPIValid($npi)
    {
        // A NPI MUST be a 10 digit number
        if ($npi === '') {
            return false;
        }

        if (strlen($npi) != 10) {
            return false;
        }

        if (!preg_match("/[0-9]*/", $npi)) {
            return false;
        }

        return true;
    }
    public function providerNPIValid($prockey = -1)
    {
        return $this->NPIValid($this->providerNPI($prockey));
    }

    public function providerUPIN($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->provider : $this->procs[$prockey]['provider'];
        return $this->x12Clean(trim($tmp['upin']));
    }

    public function providerSSN($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->provider : $this->procs[$prockey]['provider'];
        return $this->x12Clean(trim(str_replace('-', '', $tmp['federaltaxid'])));
    }

    public function providerTaxonomy($prockey = -1)
    {
        $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
        $this->provider : $this->procs[$prockey]['provider'];
        if (empty($tmp['taxonomy'])) {
            return '207Q00000X';
        }

        return $this->x12Clean(trim($tmp['taxonomy']));
    }

    public function referrerLastName()
    {
        return $this->x12Clean(trim($this->referrer['lname'] ?? ''));
    }

    public function referrerFirstName()
    {
        return $this->x12Clean(trim($this->referrer['fname']));
    }

    public function referrerMiddleName()
    {
        return $this->x12Clean(trim($this->referrer['mname']));
    }

    public function referrerNPI()
    {
        return $this->x12Clean(trim($this->referrer['npi']));
    }

    public function referrerUPIN()
    {
        return $this->x12Clean(trim($this->referrer['upin']));
    }

    public function referrerSSN()
    {
        return $this->x12Clean(trim(str_replace('-', '', $this->referrer['federaltaxid'])));
    }

    public function referrerTaxonomy()
    {
        if (empty($this->referrer['taxonomy'])) {
            return '207Q00000X';
        }

        return $this->x12Clean(trim($this->referrer['taxonomy']));
    }

    public function supervisorLastName()
    {
        return $this->x12Clean(trim($this->supervisor['lname'] ?? ''));
    }

    public function supervisorFirstName()
    {
        return $this->x12Clean(trim($this->supervisor['fname']));
    }

    public function supervisorMiddleName()
    {
        return $this->x12Clean(trim($this->supervisor['mname']));
    }

    public function supervisorNPI()
    {
        return $this->x12Clean(trim($this->supervisor['npi']));
    }

    public function supervisorUPIN()
    {
        return $this->x12Clean(trim($this->supervisor['upin']));
    }

    public function supervisorSSN()
    {
        return $this->x12Clean(trim(str_replace('-', '', $this->supervisor['federaltaxid'])));
    }

    public function supervisorTaxonomy()
    {
        if (empty($this->supervisor['taxonomy'])) {
            return '207Q00000X';
        }

        return $this->x12Clean(trim($this->supervisor['taxonomy']));
    }

    public function supervisorNumberType()
    {
        return $this->supervisor_numbers['provider_number_type'];
    }

    public function supervisorNumber()
    {
        return $this->x12Clean(trim(str_replace('-', '', ($this->supervisor_numbers['provider_number'] ?? ''))));
    }

    public function billingProviderLastName()
    {
        return $this->x12Clean(trim($this->billing_prov_id['lname'] ?? ''));
    }

    public function billingProviderFirstName()
    {
        return $this->x12Clean(trim($this->billing_prov_id['fname']));
    }

    public function billingProviderMiddleName()
    {
        return $this->x12Clean(trim($this->billing_prov_id['mname']));
    }

    public function billingProviderNPI()
    {
        return $this->x12Clean(trim($this->billing_prov_id['npi']));
    }

    public function billingProviderUPIN()
    {
        return $this->x12Clean(trim($this->billing_prov_id['upin']));
    }

    public function billingProviderSSN()
    {
        return $this->x12Clean(trim(str_replace('-', '', $this->billing_prov_id['federaltaxid'])));
    }

    public function billingProviderTaxonomy()
    {
        if (empty($this->billing_prov_id['taxonomy'])) {
            return '207Q00000X';
        }
        return $this->x12Clean(trim($this->billing_prov_id['taxonomy']));
    }

    public function billingProviderStreet()
    {
        return $this->x12Clean(trim($this->billing_prov_id['street']));
    }

    public function billingProviderStreetB()
    {
        return $this->x12Clean(trim($this->billing_prov_id['streetb']));
    }

    public function billingProviderCity()
    {
        return $this->x12Clean(trim($this->billing_prov_id['city']));
    }

    public function billingProviderState()
    {
        return $this->x12Clean(trim($this->billing_prov_id['state']));
    }

    public function billingProviderZip()
    {
        return $this->x12Clean(trim($this->billing_prov_id['zip']));
    }
}
