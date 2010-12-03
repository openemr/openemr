<?php
// Copyright (C) 2007-2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__) . "/classes/Address.class.php");
require_once(dirname(__FILE__) . "/classes/InsuranceCompany.class.php");
require_once(dirname(__FILE__) . "/sql-ledger.inc");
require_once(dirname(__FILE__) . "/invoice_summary.inc.php");

// This enforces the X12 Basic Character Set. Page A2.
//
function x12clean($str) {
  return preg_replace('/[^A-Z0-9!"\\&\'()+,\\-.\\/;?= ]/', '', strtoupper($str));
}

class Claim {

  var $pid;               // patient id
  var $encounter_id;      // encounter id
  var $procs;             // array of procedure rows from billing table
  var $diags;             // array of icd9 codes from billing table
  var $x12_partner;       // row from x12_partners table
  var $encounter;         // row from form_encounter table
  var $facility;          // row from facility table
  var $billing_facility;  // row from facility table
  var $provider;          // row from users table (rendering provider)
  var $referrer;          // row from users table (referring provider)
  var $supervisor;        // row from users table (supervising provider)
  var $insurance_numbers; // row from insurance_numbers table for current payer
  var $supervisor_numbers; // row from insurance_numbers table for current payer
  var $patient_data;      // row from patient_data table
  var $billing_options;   // row from form_misc_billing_options table
  var $invoice;           // result from get_invoice_summary()
  var $payers;            // array of arrays, for all payers
  var $copay;             // total of copays from the billing table

  function loadPayerInfo(&$billrow) {
    global $sl_err;
    $encounter_date = substr($this->encounter['date'], 0, 10);

    // Create the $payers array.  This contains data for all insurances
    // with the current one always at index 0, and the others in payment
    // order starting at index 1.
    //
    $this->payers = array();
    $this->payers[0] = array();
    $query = "SELECT * FROM insurance_data WHERE " .
      "pid = '{$this->pid}' AND " .
      "date <= '$encounter_date' " .
      "ORDER BY type ASC, date DESC";
    $dres = sqlStatement($query);
    $prevtype = '';
    while ($drow = sqlFetchArray($dres)) {
      if (strcmp($prevtype, $drow['type']) == 0) continue;
      $prevtype = $drow['type'];
      // Very important to look at entries with a missing provider because
      // they indicate no insurance as of the given date.
      if (empty($drow['provider'])) continue;
      $ins = count($this->payers);
      if ($drow['provider'] == $billrow['payer_id'] && empty($this->payers[0]['data'])) $ins = 0;
      $crow = sqlQuery("SELECT * FROM insurance_companies WHERE " .
        "id = '" . $drow['provider'] . "'");
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
      if ($billrow['process_date'] &&
        $this->payers[0]['data']['provider'] == $this->payers[$i]['data']['provider'])
      {
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
      if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) {
        $this->invoice = ar_get_invoice_summary($this->pid, $this->encounter_id, true);
      }
      else if ($GLOBALS['oer_config']['ws_accounting']['enabled']) {
        SLConnect();
        $arres = SLQuery("select id from ar where invnumber = " .
          "'{$this->pid}.{$this->encounter_id}'");
        if ($sl_err) die($sl_err);
        $arrow = SLGetRow($arres, 0);
        if ($arrow) {
          $this->invoice = get_invoice_summary($arrow['id'], true);
        }
        SLClose();
      }
      // Secondary claims might not have modifiers in SQL-Ledger data.
      // In that case, note that we should not try to match on them.
      $this->using_modifiers = false;
      foreach ($this->invoice as $key => $trash) {
        if (strpos($key, ':')) $this->using_modifiers = true;
      }
    }
  }

  // Constructor. Loads relevant database information.
  //
  function Claim($pid, $encounter_id) {
    $this->pid = $pid;
    $this->encounter_id = $encounter_id;
    $this->procs = array();
    $this->diags = array();
    $this->copay = 0;

    // We need the encounter date before we can identify the payers.
    $sql = "SELECT * FROM form_encounter WHERE " .
      "pid = '{$this->pid}' AND " .
      "encounter = '{$this->encounter_id}'";
    $this->encounter = sqlQuery($sql);

    // Sort by procedure timestamp in order to get some consistency.
    $sql = "SELECT * FROM billing WHERE " .
      "encounter = '{$this->encounter_id}' AND pid = '{$this->pid}' AND " .
      "(code_type = 'CPT4' OR code_type = 'HCPCS' OR code_type = 'COPAY' OR code_type = 'ICD9') AND " .
      "activity = '1' ORDER BY date, id";
    $res = sqlStatement($sql);
    while ($row = sqlFetchArray($res)) {
      if ($row['code_type'] == 'COPAY') {
        $this->copay -= $row['fee'];
        continue;
      }
      // Save all diagnosis codes.
      if ($row['code_type'] == 'ICD9') {
        $this->diags[$row['code']] = $row['code'];
        continue;
      }
      if (!$row['units']) $row['units'] = 1;
      // Load prior payer data at the first opportunity in order to get
      // the using_modifiers flag that is referenced below.
      if (empty($this->procs)) $this->loadPayerInfo($row);
      // Consolidate duplicate procedures.
      foreach ($this->procs as $key => $trash) {
        if (strcmp($this->procs[$key]['code'],$row['code']) == 0 &&
            (strcmp($this->procs[$key]['modifier'],$row['modifier']) == 0 ||
             !$this->using_modifiers))
        {
          $this->procs[$key]['units'] += $row['units'];
          $this->procs[$key]['fee']   += $row['fee'];
          continue 2; // skip to next table row
        }
      }

      // If there is a row-specific provider then get its details.
      if (!empty($row['provider_id'])) {
        // Get service provider data for this row.
        $sql = "SELECT * FROM users WHERE id = '" . $row['provider_id'] . "'";
        $row['provider'] = sqlQuery($sql);
        // Get insurance numbers for this row's provider.
        $sql = "SELECT * FROM insurance_numbers WHERE " .
          "(insurance_company_id = '" . $row['payer_id'] .
          "' OR insurance_company_id is NULL) AND " .
          "provider_id = '" . $row['provider_id'] . "' " .
          "ORDER BY insurance_company_id DESC LIMIT 1";
        $row['insurance_numbers'] = sqlQuery($sql);
      }

      $this->procs[] = $row;
    }

    $sql = "SELECT * FROM x12_partners WHERE " .
      "id = '" . $this->procs[0]['x12_partner_id'] . "'";
    $this->x12_partner = sqlQuery($sql);

    $sql = "SELECT * FROM facility WHERE " .
      "id = '" . addslashes($this->encounter['facility_id']) . "' " .
      "LIMIT 1";
    $this->facility = sqlQuery($sql);

    /*****************************************************************
    $provider_id = $this->procs[0]['provider_id'];
    *****************************************************************/
    $provider_id = $this->encounter['provider_id'];
    $sql = "SELECT * FROM users WHERE id = '$provider_id'";
    $this->provider = sqlQuery($sql);

    $sql = "SELECT * FROM facility " .
      "ORDER BY billing_location DESC, id ASC LIMIT 1";
    $this->billing_facility = sqlQuery($sql);

    $sql = "SELECT * FROM insurance_numbers WHERE " .
      "(insurance_company_id = '" . $this->procs[0]['payer_id'] .
      "' OR insurance_company_id is NULL) AND " .
      "provider_id = '$provider_id' " .
      "ORDER BY insurance_company_id DESC LIMIT 1";
    $this->insurance_numbers = sqlQuery($sql);

    $sql = "SELECT * FROM patient_data WHERE " .
      "pid = '{$this->pid}' " .
      "ORDER BY id LIMIT 1";
    $this->patient_data = sqlQuery($sql);

    $sql = "SELECT fpa.* FROM forms JOIN form_misc_billing_options AS fpa " .
      "ON fpa.id = forms.form_id WHERE " .
      "forms.encounter = '{$this->encounter_id}' AND " .
      "forms.pid = '{$this->pid}' AND " .
      "forms.formdir = 'misc_billing_options' " .
      "ORDER BY forms.date";
    $this->billing_options = sqlQuery($sql);

    $referrer_id = (empty($GLOBALS['MedicareReferrerIsRenderer']) ||
      $this->insurance_numbers['provider_number_type'] != '1C') ?
      $this->patient_data['providerID'] : $provider_id;
    $sql = "SELECT * FROM users WHERE id = '$referrer_id'";
    $this->referrer = sqlQuery($sql);
    if (!$this->referrer) $this->referrer = array();

    $supervisor_id = $this->encounter['supervisor_id'];
    $sql = "SELECT * FROM users WHERE id = '$supervisor_id'";
    $this->supervisor = sqlQuery($sql);
    if (!$this->supervisor) $this->supervisor = array();

    $sql = "SELECT * FROM insurance_numbers WHERE " .
      "(insurance_company_id = '" . $this->procs[0]['payer_id'] .
      "' OR insurance_company_id is NULL) AND " .
      "provider_id = '$supervisor_id' " .
      "ORDER BY insurance_company_id DESC LIMIT 1";
    $this->supervisor_numbers = sqlQuery($sql);
    if (!$this->supervisor_numbers) $this->supervisor_numbers = array();

  } // end constructor

  // Return an array of adjustments from the designated prior payer for the
  // designated procedure key (might be procedure:modifier), or for the claim
  // level.  For each adjustment give date, group code, reason code, amount.
  // Note this will include "patient responsibility" adjustments which are
  // not adjustments to OUR invoice, but they reduce the amount that the
  // insurance company pays.
  //
  function payerAdjustments($ins, $code='Claim') {
    $aadj = array();

    // If we have no modifiers stored in SQL-Ledger for this claim,
    // then we cannot use a modifier passed in with the key.
    $tmp = strpos($code, ':');
    if ($tmp && !$this->using_modifiers) $code = substr($code, 0, $tmp);

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
        if (isset($value['plv'])) {
          // New method; plv (from ar_activity.payer_type) exists to
          // indicate the payer level.
          if (isset($value['pmt']) && $value['pmt'] != 0) {
            if ($value['plv'] > 0 && $value['plv'] <= $insnumber)
              $ptresp -= $value['pmt'];
          }
          else if (isset($value['chg']) && trim(substr($key, 0, 10))) {
            // non-blank key indicates this is an adjustment and not a charge
            if ($value['plv'] > 0 && $value['plv'] <= $insnumber)
              $ptresp += $value['chg']; // adjustments are negative charges
          }
        }
        else {
          // Old method: With SQL-Ledger payer level was stored in the memo.
          if (preg_match("/^Ins(\d)/i", $value['src'], $tmp)) {
            if ($tmp[1] <= $insnumber) $ptresp -= $value['pmt'];
          }
          else if (trim(substr($key, 0, 10))) { // not an adjustment if no date
            if (!preg_match("/Ins(\d)/i", $value['rsn'], $tmp) || $tmp[1] <= $insnumber)
              $ptresp += $value['chg']; // adjustments are negative charges
          }
        }
      }
      if ($ptresp < 0) $ptresp = 0; // we may be insane but try to hide it

      // Main loop, to extract adjustments for this payer and procedure.
      foreach ($this->invoice[$code]['dtl'] as $key => $value) {
        $tmp = str_replace('-', '', trim(substr($key, 0, 10)));
        if ($tmp) $date = $tmp;
        if ($tmp && $value['pmt'] == 0) { // not original charge and not a payment
          $rsn = $value['rsn'];
          $chg = 0 - $value['chg']; // adjustments are negative charges

          $gcode = 'CO'; // default group code = contractual obligation
          $rcode = '45'; // default reason code = max fee exceeded (code 42 is obsolete)

          if (preg_match("/Ins adjust $inslabel/i", $rsn, $tmp)) {
            // From manual post. Take the defaults.
          }
          else if (preg_match("/To copay $inslabel/i", $rsn, $tmp) && !$chg) {
            $coinsurance = $ptresp; // from manual post
            continue;
          }
          else if (preg_match("/To ded'ble $inslabel/i", $rsn, $tmp) && !$chg) {
            $deductible = $ptresp; // from manual post
            continue;
          }
          else if (preg_match("/$inslabel copay: (\S+)/i", $rsn, $tmp) && !$chg) {
            $coinsurance = $tmp[1]; // from 835 as of 6/2007
            continue;
          }
          else if (preg_match("/$inslabel coins: (\S+)/i", $rsn, $tmp) && !$chg) {
            $coinsurance = $tmp[1]; // from 835 and manual post as of 6/2007
            continue;
          }
          else if (preg_match("/$inslabel dedbl: (\S+)/i", $rsn, $tmp) && !$chg) {
            $deductible = $tmp[1]; // from 835 and manual post as of 6/2007
            continue;
          }
          else if (preg_match("/$inslabel ptresp: (\S+)/i", $rsn, $tmp) && !$chg) {
            continue; // from 835 as of 6/2007
          }
          else if (preg_match("/$inslabel adjust code (\S+)/i", $rsn, $tmp)) {
            $rcode = $tmp[1]; // from 835
          }
          else if (preg_match("/$inslabel/i", $rsn, $tmp)) {
            // Take the defaults.
          }
          else if (preg_match('/Ins(\d)/i', $rsn, $tmp) && $tmp[1] != $insnumber) {
            continue; // it's for some other payer
          }
          else if ($insnumber == '1') {
            if (preg_match("/\$\s*adjust code (\S+)/i", $rsn, $tmp)) {
              $rcode = $tmp[1]; // from 835
            }
            else if ($chg) {
              // Other adjustments default to Ins1.
            }
            else if (preg_match("/Co-pay: (\S+)/i", $rsn, $tmp) ||
              preg_match("/Coinsurance: (\S+)/i", $rsn, $tmp)) {
              $coinsurance = 0 + $tmp[1]; // from 835 before 6/2007
              continue;
            }
            else if (preg_match("/To deductible: (\S+)/i", $rsn, $tmp)) {
              $deductible = 0 + $tmp[1]; // from 835 before 6/2007
              continue;
            }
            else {
              continue; // there is no adjustment amount
            }
          }
          else {
            continue; // it's for primary and that's not us
          }

          if ($rcode == '42') $rcode= '45'; // reason 42 is obsolete
          $aadj[] = array($date, $gcode, $rcode, sprintf('%.2f', $chg));

        } // end if
      } // end foreach

      // If we really messed it up, at least avoid negative numbers.
      if ($coinsurance > $ptresp) $coinsurance = $ptresp;
      if ($deductible  > $ptresp) $deductible  = $ptresp;

      // Find out if this payer paid anything at all on this claim.  This will
      // help us allocate any unknown patient responsibility amounts.
      $thispaidanything = 0;
      foreach($this->invoice as $codekey => $codeval) {
        foreach ($codeval['dtl'] as $key => $value) {
          if (preg_match("/$inslabel/i", $value['src'], $tmp)) {
            $thispaidanything += $value['pmt'];
          }
        }
      }

      // Allocate any unknown patient responsibility by guessing if the
      // deductible has been satisfied.
      if ($thispaidanything)
        $coinsurance = $ptresp - $deductible;
      else
        $deductible = $ptresp - $coinsurance;

      if ($date && $deductible != 0)
        $aadj[] = array($date, 'PR', '1', sprintf('%.2f', $deductible));
      if ($date && $coinsurance != 0)
        $aadj[] = array($date, 'PR', '2', sprintf('%.2f', $coinsurance));

    } // end if

    return $aadj;
  }

  // Return date, total payments and total "hard" adjustments from the given
  // prior payer. If $code is specified then only that procedure key is
  // selected, otherwise it's for the whole claim.
  //
  function payerTotals($ins, $code='') {
    // If we have no modifiers stored in SQL-Ledger for this claim,
    // then we cannot use a modifier passed in with the key.
    $tmp = strpos($code, ':');
    if ($tmp && !$this->using_modifiers) $code = substr($code, 0, $tmp);

    $inslabel = ($this->payerSequence($ins) == 'S') ? 'Ins2' : 'Ins1';
    $insnumber = substr($inslabel, 3);
    $paytotal = 0;
    $adjtotal = 0;
    $date = '';
    foreach($this->invoice as $codekey => $codeval) {
      if ($code && strcmp($codekey,$code) != 0) continue;
      foreach ($codeval['dtl'] as $key => $value) {
        if (isset($value['plv'])) {
          // New method; plv (from ar_activity.payer_type) exists to
          // indicate the payer level.
          if ($value['plv'] == $insnumber) {
            if (!$date) $date = str_replace('-', '', trim(substr($key, 0, 10)));
            $paytotal += $value['pmt'];
          }
        }
        else {
          // Old method: With SQL-Ledger payer level was stored in the memo.
          if (preg_match("/$inslabel/i", $value['src'], $tmp)) {
            if (!$date) $date = str_replace('-', '', trim(substr($key, 0, 10)));
            $paytotal += $value['pmt'];
          }
        }
      }
      $aarr = $this->payerAdjustments($ins, $codekey);
      foreach ($aarr as $a) {
        if (strcmp($a[1],'PR') != 0) $adjtotal += $a[3];
        if (!$date) $date = $a[0];
      }
    }
    return array($date, sprintf('%.2f', $paytotal), sprintf('%.2f', $adjtotal));
  }

  // Return the amount already paid by the patient.
  //
  function patientPaidAmount() {
    // For primary claims $this->invoice is not loaded, so get the co-pay
    // from the billing table instead.
    if (empty($this->invoice)) return $this->copay;
    //
    $amount = 0;
    foreach($this->invoice as $codekey => $codeval) {
      foreach ($codeval['dtl'] as $key => $value) {
        if (!preg_match("/Ins/i", $value['src'], $tmp)) {
          $amount += $value['pmt'];
        }
      }
    }
    return sprintf('%.2f', $amount);
  }

  // Return invoice total, including adjustments but not payments.
  //
  function invoiceTotal() {
    $amount = 0;
    foreach($this->invoice as $codekey => $codeval) {
      $amount += $codeval['chg'];
    }
    return sprintf('%.2f', $amount);
  }

  // Number of procedures in this claim.
  function procCount() {
    return count($this->procs);
  }

  // Number of payers for this claim. Ranges from 1 to 3.
  function payerCount() {
    return count($this->payers);
  }

  function x12gsversionstring() {
    return x12clean(trim($this->x12_partner['x12_version']));
  }

  function x12gssenderid() {
    $tmp = $this->x12_partner['x12_sender_id'];
    while (strlen($tmp) < 15) $tmp .= " ";
    return $tmp;
  }

  function x12gsreceiverid() {
    $tmp = $this->x12_partner['x12_receiver_id'];
    while (strlen($tmp) < 15) $tmp .= " ";
    return $tmp;
  }

  function x12gsisa05() {
    return $this->x12_partner['x12_isa05'];
  }

  function x12gsisa07() {
    return $this->x12_partner['x12_isa07'];
  }

  function x12gsisa14() {
    return $this->x12_partner['x12_isa14'];
  }

  function x12gsisa15() {
    return $this->x12_partner['x12_isa15'];
  }

  function x12gsgs02() {
    $tmp = $this->x12_partner['x12_gs02'];
    if ($tmp === '') $tmp = $this->x12_partner['x12_sender_id'];
    return $tmp;
  }

  function x12gsper06() {
    return $this->x12_partner['x12_per06'];
  }

  function cliaCode() {
    return x12clean(trim($this->facility['domain_identifier']));
  }

  function billingFacilityName() {
    return x12clean(trim($this->billing_facility['name']));
  }

  function billingFacilityStreet() {
    return x12clean(trim($this->billing_facility['street']));
  }

  function billingFacilityCity() {
    return x12clean(trim($this->billing_facility['city']));
  }

  function billingFacilityState() {
    return x12clean(trim($this->billing_facility['state']));
  }

  function billingFacilityZip() {
    return x12clean(trim($this->billing_facility['postal_code']));
  }

  function billingFacilityETIN() {
    return x12clean(trim(str_replace('-', '', $this->billing_facility['federal_ein'])));
  }

  function billingFacilityNPI() {
    return x12clean(trim($this->billing_facility['facility_npi']));
  }
  
  function federalIdType() {
	if ($this->billing_facility['tax_id_type'])
	{
	return $this->billing_facility['tax_id_type'];
	}
	else{
	return null;
	}
  }

  # The billing facility and the patient must both accept for this to return true.
  function billingFacilityAssignment($ins=0) {
    $tmp = strtoupper($this->payers[$ins]['data']['accept_assignment']);
    if (strcmp($tmp,'FALSE') == 0) return '0';
    return !empty($this->billing_facility['accepts_assignment']);
  }

  function billingContactName() {
    return x12clean(trim($this->billing_facility['attn']));
  }

  function billingContactPhone() {
    if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",
      $this->billing_facility['phone'], $tmp))
    {
      return $tmp[1] . $tmp[2] . $tmp[3];
    }
    return '';
  }

  function facilityName() {
    return x12clean(trim($this->facility['name']));
  }

  function facilityStreet() {
    return x12clean(trim($this->facility['street']));
  }

  function facilityCity() {
    return x12clean(trim($this->facility['city']));
  }

  function facilityState() {
    return x12clean(trim($this->facility['state']));
  }

  function facilityZip() {
    return x12clean(trim($this->facility['postal_code']));
  }

  function facilityETIN() {
    return x12clean(trim(str_replace('-', '', $this->facility['federal_ein'])));
  }

  function facilityNPI() {
    return x12clean(trim($this->facility['facility_npi']));
  }

  function facilityPOS() {
    return x12clean(trim($this->facility['pos_code']));
  }

  function clearingHouseName() {
    return x12clean(trim($this->x12_partner['name']));
  }

  function clearingHouseETIN() {
    return x12clean(trim(str_replace('-', '', $this->x12_partner['id_number'])));
  }

  function providerNumberType($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->insurance_numbers : $this->procs[$prockey]['insurance_numbers'];
    return $tmp['provider_number_type'];
  }

  function providerNumber($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->insurance_numbers : $this->procs[$prockey]['insurance_numbers'];
    return x12clean(trim(str_replace('-', '', $tmp['provider_number'])));
  }

  function providerGroupNumber($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->insurance_numbers : $this->procs[$prockey]['insurance_numbers'];
    return x12clean(trim(str_replace('-', '', $tmp['group_number'])));
  }

  // Returns 'P', 'S' or 'T'.
  //
  function payerSequence($ins=0) {
    return strtoupper(substr($this->payers[$ins]['data']['type'], 0, 1));
  }

  // Returns the HIPAA code of the patient-to-subscriber relationship.
  //
  function insuredRelationship($ins=0) {
    $tmp = strtolower($this->payers[$ins]['data']['subscriber_relationship']);
    if (strcmp($tmp,'self'  ) == 0) return '18';
    if (strcmp($tmp,'spouse') == 0) return '01';
    if (strcmp($tmp,'child' ) == 0) return '19';
    if (strcmp($tmp,'other' ) == 0) return 'G8';
    return $tmp; // should not happen
  }

  function insuredTypeCode($ins=0) {
    if (strcmp($this->claimType($ins),'MB') == 0 && $this->payerSequence($ins) != 'P')
      return '12'; // medicare secondary working aged beneficiary or
                   // spouse with employer group health plan
    return '';
  }

  // Is the patient also the subscriber?
  //
  function isSelfOfInsured($ins=0) {
    $tmp = strtolower($this->payers[$ins]['data']['subscriber_relationship']);
    return (strcmp($tmp,'self') == 0);
  }

  function planName($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['plan_name']));
  }

  function policyNumber($ins=0) { // "ID"
    return x12clean(trim($this->payers[$ins]['data']['policy_number']));
  }

  function groupNumber($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['group_number']));
  }

  function groupName($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['subscriber_employer']));
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
  function claimType($ins=0) {
    if (empty($this->payers[$ins]['object'])) return '';
    return $this->payers[$ins]['object']->get_freeb_claim_type();
  }

  function insuredLastName($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['subscriber_lname']));
  }

  function insuredFirstName($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['subscriber_fname']));
  }

  function insuredMiddleName($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['subscriber_mname']));
  }

  function insuredStreet($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['subscriber_street']));
  }

  function insuredCity($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['subscriber_city']));
  }

  function insuredState($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['subscriber_state']));
  }

  function insuredZip($ins=0) {
    return x12clean(trim($this->payers[$ins]['data']['subscriber_postal_code']));
  }

  function insuredPhone($ins=0) {
    if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",
      $this->payers[$ins]['data']['subscriber_phone'], $tmp))
      return $tmp[1] . $tmp[2] . $tmp[3];
    return '';
  }

  function insuredDOB($ins=0) {
    return str_replace('-', '', $this->payers[$ins]['data']['subscriber_DOB']);
  }

  function insuredSex($ins=0) {
    return strtoupper(substr($this->payers[$ins]['data']['subscriber_sex'], 0, 1));
  }

  function payerName($ins=0) {
    return x12clean(trim($this->payers[$ins]['company']['name']));
  }

  function payerAttn($ins=0) {
    return x12clean(trim($this->payers[$ins]['company']['attn']));
  }

  function payerStreet($ins=0) {
    if (empty($this->payers[$ins]['object'])) return '';
    $tmp = $this->payers[$ins]['object'];
    $tmp = $tmp->get_address();
    return x12clean(trim($tmp->get_line1()));
  }

  function payerCity($ins=0) {
    if (empty($this->payers[$ins]['object'])) return '';
    $tmp = $this->payers[$ins]['object'];
    $tmp = $tmp->get_address();
    return x12clean(trim($tmp->get_city()));
  }

  function payerState($ins=0) {
    if (empty($this->payers[$ins]['object'])) return '';
    $tmp = $this->payers[$ins]['object'];
    $tmp = $tmp->get_address();
    return x12clean(trim($tmp->get_state()));
  }

  function payerZip($ins=0) {
    if (empty($this->payers[$ins]['object'])) return '';
    $tmp = $this->payers[$ins]['object'];
    $tmp = $tmp->get_address();
    return x12clean(trim($tmp->get_zip()));
  }

  function payerID($ins=0) {
    return x12clean(trim($this->payers[$ins]['company']['cms_id']));
  }

  function payerAltID($ins=0) {
    return x12clean(trim($this->payers[$ins]['company']['alt_cms_id']));
  }

  function patientLastName() {
    return x12clean(trim($this->patient_data['lname']));
  }

  function patientFirstName() {
    return x12clean(trim($this->patient_data['fname']));
  }

  function patientMiddleName() {
    return x12clean(trim($this->patient_data['mname']));
  }

  function patientStreet() {
    return x12clean(trim($this->patient_data['street']));
  }

  function patientCity() {
    return x12clean(trim($this->patient_data['city']));
  }

  function patientState() {
    return x12clean(trim($this->patient_data['state']));
  }

  function patientZip() {
    return x12clean(trim($this->patient_data['postal_code']));
  }

  function patientPhone() {
    $ptphone = $this->patient_data['phone_home'];
    if (!$ptphone) $ptphone = $this->patient_data['phone_biz'];
    if (!$ptphone) $ptphone = $this->patient_data['phone_cell'];
    if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/", $ptphone, $tmp))
      return $tmp[1] . $tmp[2] . $tmp[3];
    return '';
  }

  function patientDOB() {
    return str_replace('-', '', $this->patient_data['DOB']);
  }

  function patientSex() {
    return strtoupper(substr($this->patient_data['sex'], 0, 1));
  }

  // Patient Marital Status: M = Married, S = Single, or something else.
  function patientStatus() {
    return strtoupper(substr($this->patient_data['status'], 0, 1));
  }

  // This should be UNEMPLOYED, STUDENT, PT STUDENT, or anything else to
  // indicate employed.
  function patientOccupation() {
    return strtoupper(x12clean(trim($this->patient_data['occupation'])));
  }

  function cptCode($prockey) {
    return x12clean(trim($this->procs[$prockey]['code']));
  }

  function cptModifier($prockey) {
    return x12clean(trim($this->procs[$prockey]['modifier']));
  }

  // Returns the procedure code, followed by ":modifier" if there is one.
  function cptKey($prockey) {
    $tmp = $this->cptModifier($prockey);
    return $this->cptCode($prockey) . ($tmp ? ":$tmp" : "");
  }

  function cptCharges($prockey) {
    return x12clean(trim($this->procs[$prockey]['fee']));
  }

  function cptUnits($prockey) {
    if (empty($this->procs[$prockey]['units'])) return '1';
    return x12clean(trim($this->procs[$prockey]['units']));
  }

  // NDC drug ID.
  function cptNDCID($prockey) {
    $ndcinfo = $this->procs[$prockey]['ndc_info'];
    if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $ndcinfo, $tmp)) {
      $ndc = $tmp[1];
      if (preg_match('/^(\d+)-(\d+)-(\d+)$/', $ndc, $tmp)) {
        return sprintf('%05d-%04d-%02d', $tmp[1], $tmp[2], $tmp[3]);
      }
      return x12clean($ndc); // format is bad but return it anyway
    }
    return '';
  }

  // NDC drug unit of measure code.
  function cptNDCUOM($prockey) {
    $ndcinfo = $this->procs[$prockey]['ndc_info'];
    if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $ndcinfo, $tmp))
      return x12clean($tmp[2]);
    return '';
  }

  // NDC drug number of units.
  function cptNDCQuantity($prockey) {
    $ndcinfo = $this->procs[$prockey]['ndc_info'];
    if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $ndcinfo, $tmp)) {
      return x12clean(ltrim($tmp[3], '0'));
    }
    return '';
  }

  function onsetDate() {
    return str_replace('-', '', substr($this->encounter['onset_date'], 0, 10));
  }

  function serviceDate() {
    return str_replace('-', '', substr($this->encounter['date'], 0, 10));
  }

  function priorAuth() {
    return x12clean(trim($this->billing_options['prior_auth_number']));
  }

  function isRelatedEmployment() {
    return !empty($this->billing_options['employment_related']);
  }

  function isRelatedAuto() {
    return !empty($this->billing_options['auto_accident']);
  }

  function isRelatedOther() {
    return !empty($this->billing_options['other_accident']);
  }

  function autoAccidentState() {
    return x12clean(trim($this->billing_options['accident_state']));
  }

  function isUnableToWork() {
    return !empty($this->billing_options['is_unable_to_work']);
  }

  function offWorkFrom() {
    return str_replace('-', '', substr($this->billing_options['off_work_from'], 0, 10));
  }

  function offWorkTo() {
    return str_replace('-', '', substr($this->billing_options['off_work_to'], 0, 10));
  }

  function isHospitalized() {
    return !empty($this->billing_options['is_hospitalized']);
  }

  function hospitalizedFrom() {
    return str_replace('-', '', substr($this->billing_options['hospitalization_date_from'], 0, 10));
  }

  function hospitalizedTo() {
    return str_replace('-', '', substr($this->billing_options['hospitalization_date_to'], 0, 10));
  }

  function isOutsideLab() {
    return !empty($this->billing_options['outside_lab']);
  }

  function outsideLabAmount() {
    return sprintf('%.2f', 0 + $this->billing_options['lab_amount']);
  }

  function medicaidResubmissionCode() {
    return x12clean(trim($this->billing_options['medicaid_resubmission_code']));
  }

  function medicaidOriginalReference() {
    return x12clean(trim($this->billing_options['medicaid_original_reference']));
  }

  function frequencyTypeCode() {
    return empty($this->billing_options['replacement_claim']) ? '1' : '7';
  }

  function additionalNotes() {
    return x12clean(trim($this->billing_options['comments']));
  }

  // Returns an array of unique diagnoses.  Periods are stripped.
  function diagArray() {
    $da = array();
    foreach ($this->procs as $row) {
      $atmp = explode(':', $row['justify']);
      foreach ($atmp as $tmp) {
        if (!empty($tmp)) {
          $diag = str_replace('.', '', $tmp);
          $da[$diag] = $diag;
        }
      }
    }
    // The above got all the diagnoses used for justification, in the order
    // used for justification.  Next we go through all diagnoses, justified
    // or not, to make sure they all get into the claim.  We do it this way
    // so that the more important diagnoses appear first.
    foreach ($this->diags as $diag) {
      $diag = str_replace('.', '', $diag);
      $da[$diag] = $diag;
    }
    return $da;
  }

  // Compute one 1-relative index in diagArray for the given procedure.
  // This function is obsolete, use diagIndexArray() instead.
  function diagIndex($prockey) {
    $da = $this->diagArray();
    $tmp = explode(':', $this->procs[$prockey]['justify']);
    if (empty($tmp)) return '';
    $diag = str_replace('.', '', $tmp[0]);
    $i = 0;
    foreach ($da as $value) {
      ++$i;
      if (strcmp($value,$diag) == 0) return $i;
    }
    return '';
  }

  // Compute array of 1-relative diagArray indices for the given procedure.
  function diagIndexArray($prockey) {
    $dia = array();
    $da = $this->diagArray();
    $atmp = explode(':', $this->procs[$prockey]['justify']);
    foreach ($atmp as $tmp) {
      if (!empty($tmp)) {
        $diag = str_replace('.', '', $tmp);
        $i = 0;
        foreach ($da as $value) {
          ++$i;
          if (strcmp($value,$diag) == 0) $dia[] = $i;
        }
      }
    }
    return $dia;
  }

  function providerLastName($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->provider : $this->procs[$prockey]['provider'];
    return x12clean(trim($tmp['lname']));
  }

  function providerFirstName($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->provider : $this->procs[$prockey]['provider'];
    return x12clean(trim($tmp['fname']));
  }

  function providerMiddleName($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->provider : $this->procs[$prockey]['provider'];
    return x12clean(trim($tmp['mname']));
  }

  function providerNPI($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->provider : $this->procs[$prockey]['provider'];
    return x12clean(trim($tmp['npi']));
  }

  function providerUPIN($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->provider : $this->procs[$prockey]['provider'];
    return x12clean(trim($tmp['upin']));
  }

  function providerSSN($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->provider : $this->procs[$prockey]['provider'];
    return x12clean(trim(str_replace('-', '', $tmp['federaltaxid'])));
  }

  function providerTaxonomy($prockey=-1) {
    $tmp = ($prockey < 0 || empty($this->procs[$prockey]['provider_id'])) ?
      $this->provider : $this->procs[$prockey]['provider'];
    if (empty($tmp['taxonomy'])) return '207Q00000X';
    return x12clean(trim($tmp['taxonomy']));
  }

  function referrerLastName() {
    return x12clean(trim($this->referrer['lname']));
  }

  function referrerFirstName() {
    return x12clean(trim($this->referrer['fname']));
  }

  function referrerMiddleName() {
    return x12clean(trim($this->referrer['mname']));
  }

  function referrerNPI() {
    return x12clean(trim($this->referrer['npi']));
  }

  function referrerUPIN() {
    return x12clean(trim($this->referrer['upin']));
  }

  function referrerSSN() {
    return x12clean(trim(str_replace('-', '', $this->referrer['federaltaxid'])));
  }

  function referrerTaxonomy() {
    if (empty($this->referrer['taxonomy'])) return '207Q00000X';
    return x12clean(trim($this->referrer['taxonomy']));
  }

  function supervisorLastName() {
    return x12clean(trim($this->supervisor['lname']));
  }

  function supervisorFirstName() {
    return x12clean(trim($this->supervisor['fname']));
  }

  function supervisorMiddleName() {
    return x12clean(trim($this->supervisor['mname']));
  }

  function supervisorNPI() {
    return x12clean(trim($this->supervisor['npi']));
  }

  function supervisorUPIN() {
    return x12clean(trim($this->supervisor['upin']));
  }

  function supervisorSSN() {
    return x12clean(trim(str_replace('-', '', $this->supervisor['federaltaxid'])));
  }

  function supervisorTaxonomy() {
    if (empty($this->supervisor['taxonomy'])) return '207Q00000X';
    return x12clean(trim($this->supervisor['taxonomy']));
  }

  function supervisorNumberType() {
    return $this->supervisor_numbers['provider_number_type'];
  }

  function supervisorNumber() {
    return x12clean(trim(str_replace('-', '', $this->supervisor_numbers['provider_number'])));
  }

}
?>
