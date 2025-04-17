<?php

/*
 * test_edih_835_accounting.php
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


// comment out below exit when need to use this script
exit;
use OpenEMR\Billing\ParseERA;

function edih_835_accounting($segments, $delimiters)
{
    // accounting information is in
    // BPR TRN CLP SVC PLB
    /*****
     *
     *
     *$out['check_number'] = trim($seg[2]); TRN
     *$out['payer_tax_id'] = substr($seg[3], 1); // 9 digits
     *$out['payer_id'] = trim($seg[4]);
     *$out['production_date'] = trim($seg[2]); DTM 405
     *$out['payer_name'] = trim($seg[2]);  N1 loop 1000A
     * $out['payer_street'] = trim($seg[1]); N3
     * $out['payer_city']  = trim($seg[1]);  N4
     * $out['payer_state'] = trim($seg[2]);
     * $out['payer_zip']   = trim($seg[3]);
     * $out['payee_name']   = trim($seg[2]);  N1 loop 1000B
     *$out['payee_street'] = trim($seg[1]);
     * $out['payee_city']  = trim($seg[1]);
     * $out['payee_state'] = trim($seg[2]);
     *$out['payee_zip']   = trim($seg[3]);
     * CLP segment
     * // Clear some stuff to start the new claim:
            $out['subscriber_lname']     = '';
            $out['subscriber_fname']     = '';
            $out['subscriber_mname']     = '';
            $out['subscriber_member_id'] = '';
            $out['crossover']=0;
            $out['svc'] = array();
     * $out['our_claim_id']      = trim($seg[1]);
     *$out['claim_status_code'] = trim($seg[2]);
     *$out['amount_charged']    = trim($seg[3]);
     * $out['amount_approved']   = trim($seg[4]);
     * $out['amount_patient']    = trim($seg[5]); // pt responsibility, copay + deductible
     * $out['payer_claim_id']    = trim($seg[7]); // payer's claim number
     *
     * else if ($segid == 'CAS' && $out['loopid'] == '2100') {
            // This is a claim-level adjustment and should be unusual.
            // Handle it by creating a dummy zero-charge service item and
            // then populating the adjustments into it.  See also code in
            // ParseERA::parseERA2100() which will later plug in a payment reversal
            // amount that offsets these adjustments.
            $i = 0; // if present, the dummy service item will be first.
            if (!$out['svc'][$i]) {
                $out['svc'][$i] = array();
                $out['svc'][$i]['code'] = 'Claim';
                $out['svc'][$i]['mod']  = '';
                $out['svc'][$i]['chg']  = '0';
                $out['svc'][$i]['paid'] = '0';
                $out['svc'][$i]['adj']  = array();
            }
            for ($k = 2; $k < 20; $k += 3) {
                if (!$seg[$k]) break;
                $j = count($out['svc'][$i]['adj']);
                $out['svc'][$i]['adj'][$j] = array();
                $out['svc'][$i]['adj'][$j]['group_code']  = $seg[1];
                $out['svc'][$i]['adj'][$j]['reason_code'] = $seg[$k];
                $out['svc'][$i]['adj'][$j]['amount']      = $seg[$k+1];
            }
        }
     *
     * // QC = Patient
        else if ($segid == 'NM1' && $seg[1] == 'QC' && $out['loopid'] == '2100') {
            $out['patient_lname']     = trim($seg[3]);
            $out['patient_fname']     = trim($seg[4]);
            $out['patient_mname']     = trim($seg[5]);
            $out['patient_member_id'] = trim($seg[9]);
        }
        // IL = Insured or Subscriber
        else if ($segid == 'NM1' && $seg[1] == 'IL' && $out['loopid'] == '2100') {
            $out['subscriber_lname']     = trim($seg[3]);
            $out['subscriber_fname']     = trim($seg[4]);
            $out['subscriber_mname']     = trim($seg[5]);
            $out['subscriber_member_id'] = trim($seg[9]);
        }
        // 82 = Rendering Provider
        else if ($segid == 'NM1' && $seg[1] == '82' && $out['loopid'] == '2100') {
            $out['provider_lname']     = trim($seg[3]);
            $out['provider_fname']     = trim($seg[4]);
            $out['provider_mname']     = trim($seg[5]);
            $out['provider_member_id'] = trim($seg[9]);
        }
        else if ($segid == 'NM1' && $seg[1] == 'TT' && $out['loopid'] == '2100') {
            $out['crossover']     = 1;//Claim automatic forward case.

        }
     *
     * else if ($segid == 'REF' && $seg[1] == '1W' && $out['loopid'] == '2100') {
            $out['claim_comment'] = trim($seg[2]);
        }
     *
     *  else if ($segid == 'DTM' && $seg[1] == '050' && $out['loopid'] == '2100') {
            $out['claim_date'] = trim($seg[2]); // yyyymmdd
        }
     *
     * else if ($segid == 'PER' && $out['loopid'] == '2100') {

            $out['payer_insurance']  = trim($seg[2]);
            $out['warnings'] .= 'Claim contact information: ' .
                $seg[4] . "\n";
        }
     *
     * else if ($segid == 'SVC') {
            if (! $out['loopid']) return 'Unexpected SVC segment';
     *      $out['loopid'] = '2110';
            if ($seg[6]) {
                // SVC06 if present is our original procedure code that they are changing.
                // We will not put their crap in our invoice, but rather log a note and
                // treat it as adjustments to our originally submitted coding.
                $svc = explode($delimiter3, $seg[6]);
                $tmp = explode($delimiter3, $seg[1]);
                $out['warnings'] .= "Payer is restating our procedure " . $svc[1] .
                    " as " . $tmp[1] . ".\n";
            } else {
                $svc = explode($delimiter3, $seg[1]);
            }
            if ($svc[0] != 'HC') return 'SVC segment has unexpected qualifier';
            // TBD: Other qualifiers are possible; see IG pages 140-141.
            $i = count($out['svc']);
            $out['svc'][$i] = array();
     *
     * // It seems some payers append the modifier with no separator!
      if (strlen($svc[1]) == 7 && empty($svc[2])) {
        $out['svc'][$i]['code'] = substr($svc[1], 0, 5);
        $out['svc'][$i]['mod']  = substr($svc[1], 5);
      } else {
        $out['svc'][$i]['code'] = $svc[1];
        $out['svc'][$i]['mod']  = $svc[2] ? $svc[2] . ':' : '';
        $out['svc'][$i]['mod']  .= $svc[3] ? $svc[3] . ':' : '';
        $out['svc'][$i]['mod']  .= $svc[4] ? $svc[4] . ':' : '';
        $out['svc'][$i]['mod']  .= $svc[5] ? $svc[5] . ':' : '';
        $out['svc'][$i]['mod'] = preg_replace('/:$/','',$out['svc'][$i]['mod']);
      }
            $out['svc'][$i]['chg']  = $seg[2];
            $out['svc'][$i]['paid'] = $seg[3];
            $out['svc'][$i]['adj']  = array();
            // Note: SVC05, if present, indicates the paid units of service.
            // It defaults to 1.
        }
     *        // DTM01 identifies the type of service date:
        // 472 = a single date of service
        // 150 = service period start
        // 151 = service period end
        else if ($segid == 'DTM' && $out['loopid'] == '2110') {
            $out['dos'] = trim($seg[2]); // yyyymmdd
        }
        else if ($segid == 'CAS' && $out['loopid'] == '2110') {
            $i = count($out['svc']) - 1;
            for ($k = 2; $k < 20; $k += 3) {
                if (!$seg[$k]) break;
        if ($seg[1] == 'CO' && $seg[$k+1] < 0) {
          $out['warnings'] .= "Negative Contractual Obligation adjustment " .
            "seems wrong. Inverting, but should be checked!\n";
          $seg[$k+1] = 0 - $seg[$k+1];
        }
                $j = count($out['svc'][$i]['adj']);
                $out['svc'][$i]['adj'][$j] = array();
                $out['svc'][$i]['adj'][$j]['group_code']  = $seg[1];
                $out['svc'][$i]['adj'][$j]['reason_code'] = $seg[$k];
                $out['svc'][$i]['adj'][$j]['amount']      = $seg[$k+1];
                // Note: $seg[$k+2] is "quantity".  A value here indicates a change to
                // the number of units of service.  We're ignoring that for now.
            }
        }
     *else if ($segid == 'LQ' && $seg[1] == 'HE' && $out['loopid'] == '2110') {
            $i = count($out['svc']) - 1;
            $out['svc'][$i]['remark'] = $seg[2];
        }
     *
     *
     * else if ($segid == 'PLB') {
            // Provider-level adjustments are a General Ledger thing and should not
            // alter the A/R for the claim, so we just report them as notes.
            for ($k = 3; $k < 15; $k += 2) {
                if (!$seg[$k]) break;
                $out['warnings'] .= 'PROVIDER LEVEL ADJUSTMENT (not claim-specific): $' .
                    sprintf('%.2f', $seg[$k+1]) . " with reason code " . $seg[$k] . "\n";
                // Note: For PLB adjustment reason codes see IG pages 165-170.
            }
        }
        else if ($segid == 'SE') {
            ParseERA::parseERA2100($out, $cb);
            $out['loopid'] = '';
            if ($out['st_control_number'] != trim($seg[2])) {
                return 'Ending transaction set control number mismatch';
            }
            if (($out['st_segment_count'] + 1) != trim($seg[1])) {
                return 'Ending transaction set segment count mismatch';
            }
        }
     *
     *
     *
     *
     */

    if (is_array($segments) && count($segments)) {
        $acct = array();
    } else {
        csv_edihist_log("edih_835_accounting: invalid segments argument");
        return "835 accounting: invalid segments argument";
    }

    foreach ($segments as $seg) {
        if (strncmp('GS' . $de, $seg, 3) === 0) {
            $sar = explode($de, $seg);
            $gs_date = (isset($sar[4]) && $sar[4]) ? trim($sar[4]) : '';
        }

        if (strncmp('BPR' . $de, $seg, 4) === 0) {
            $sar = explode($de, $seg);
            $check_amount = (isset($sar[2]) && $sar[2]) ? trim($sar[2]) : '';
            $check_date = (isset($sar[16]) && $sar[16]) ? trim($sar[16]) : '';
        }

        if (strncmp('TRN' . $de, $seg, 4) === 0) {
            $sar = explode($de, $seg);
            $ck = (isset($sar[2]) && $sar[2]) ? trim($sar[2]) : count($out);
            $out[$ck]['gs_date'] = $gs_date;
            $out[$ck]['check_amount'] = $check_amount;
            $out[$ck]['check_date'] = $check_date;
            $out[$ck]['check_number'] = (isset($sar[2]) && $sar[2]) ? trim($sar[2]) : '';
        }

        if (strncmp('LX' . $de, $seg, 3) === 0) {
        }

        if (strncmp('CLP' . $de, $seg, 4) === 0) {
            $sar = explode($de, $seg);
            $loopid = '2100';
            //
            $i = (isset($out[$ck]['clp'])) ? count($out[$ck]['clp']) : 0;
            //
            $out[$ck]['loopid'] = '2100';
            $out[$ck]['warnings'] = '';
            // Clear some stuff to start the new claim:
            $out[$ck]['clp'][$i]['subscriber_lname'] = '';
            $out[$ck]['clp'][$i]['subscriber_fname'] = '';
            $out[$ck]['clp'][$i]['subscriber_mname'] = '';
            $out[$ck]['clp'][$i]['subscriber_member_id'] = '';
            $out[$ck]['clp'][$i]['crossover'] = 0;
            $out[$ck]['clp'][$i]['svc'] = array();
            //
            // This is the poorly-named "Patient Account Number".  For 837p
            // it comes from CLM01 which we populated as pid-diagid-procid,
            // where diagid and procid are id values from the billing table.
            // For HCFA 1500 claims it comes from field 26 which we
            // populated with our familiar pid-encounter billing key.
            //
            // The 835 spec calls this the "provider-assigned claim control
            // number" and notes that it is specifically intended for
            // identifying the claim in the provider's database.
            $out[$ck]['clp'][$i]['our_claim_id'] = (isset($sar[1]) && $sar[1]) ? trim($sar[1]) : "";
            //
            $out[$ck]['clp'][$i]['claim_status_code'] = (isset($sar[2]) && $sar[2]) ? trim($sar[2]) : "";
            $out[$ck]['clp'][$i]['amount_charged']  = (isset($sar[3]) && $sar[3]) ? trim($sar[3]) : "";
            $out[$ck]['clp'][$i]['amount_approved'] = (isset($sar[4]) && $sar[4]) ? trim($sar[4]) : "";
            $out[$ck]['clp'][$i]['amount_patient']  = (isset($sar[5]) && $sar[5]) ? trim($sar[5]) : ""; // pt responsibility, copay + deductible
            $out[$ck]['clp'][$i]['payer_claim_id']  = (isset($sar[7]) && $sar[7]) ? trim($sar[7]) : ""; // payer's claim number
        }

        if (strncmp('CAS' . $de, $seg, 4) === 0) {
            $sar = explode($de, $seg);
            if ($loop == '2100') {
                //
                // This is a claim-level adjustment and should be unusual.
                // Handle it by creating a dummy zero-charge service item and
                // then populating the adjustments into it.  See also code in
                // ParseERA::parseERA2100() which will later plug in a payment reversal
                // amount that offsets these adjustments.
                $j = 0; // if present, the dummy service item will be first.
                if (!$out['svc'][$j]) {
                    $out[$ck]['clp'][$i]['svc'][$j] = array();
                    $out[$ck]['clp'][$i]['svc'][$j]['code'] = 'Claim';
                    $out[$ck]['clp'][$i]['svc'][$j]['mod']  = '';
                    $out[$ck]['clp'][$i]['svc'][$j]['chg']  = '0';
                    $out[$ck]['clp'][$i]['svc'][$j]['paid'] = '0';
                    $out[$ck]['clp'][$i]['svc'][$j]['adj']  = array();
                }

                for ($k = 2; $k < 20; $k += 3) {
                    if (!isset($sar[$k])) {
                        break;
                    }

                    $k = count($out['svc'][$j]['adj']);
                    $out[$ck]['clp'][$i]['svc'][$j]['adj'][$k] = array();
                    $out[$ck]['clp'][$i]['svc'][$j]['adj'][$k]['group_code']  = $sar[1];
                    $out[$ck]['clp'][$i]['svc'][$j]['adj'][$k]['reason_code'] = $sar[$k];
                    $out[$ck]['clp'][$i]['svc'][$j]['adj'][$k]['amount']      = $sar[$k + 1];
                }
            } elseif ($loopid == '2110') {
                $sar = explode($de, $seg);
                $j = count($out[$ck]['clp'][$i]['svc']);
                $out[$ck]['clp'][$i]['svc'][$j] = array();
                if (! $out['loopid']) {
                    return 'Unexpected SVC segment';
                }

                //
                if (isset($sar[6]) && $sar[6]) {
                    // SVC06 if present is our original procedure code that they are changing.
                    // We will not put their crap in our invoice, but rather log a note and
                    // treat it as adjustments to our originally submitted coding.
                    $svc = explode($ds, $sar[6]);
                    $tmp = (isset($sar[1]) && $sar[1]) ? explode($ds, $sar[1]) : "";
                    $out[$ck]['clp'][$i]['warnings'] .= "Submitted procedure modified " . $svc[1] .
                        " as " . $tmp[1] . ".\n";
                } else {
                    $svc = explode($delimiter3, $seg[1]);
                }

                if ($svc[0] != 'HC') {
                    return 'SVC segment has unexpected qualifier';
                }

                // TBD: Other qualifiers are possible; see IG pages 140-141.
                $j = count($out[$ck]['clp'][$i]['svc']);
                $out['svc'][$j] = array();
                // It seems some payers append the modifier with no separator!
                if (strlen($svc[1]) == 7 && empty($svc[2])) {
                    $out['svc'][$j]['code'] = substr($svc[1], 0, 5);
                    $out['svc'][$j]['mod']  = substr($svc[1], 5);
                } else {
                    $out['svc'][$j]['code'] = $svc[1];
                    $out['svc'][$j]['mod']  = $svc[2] ? $svc[2] . ':' : '';
                    $out['svc'][$j]['mod']  .= $svc[3] ? $svc[3] . ':' : '';
                    $out['svc'][$j]['mod']  .= $svc[4] ? $svc[4] . ':' : '';
                    $out['svc'][$j]['mod']  .= $svc[5] ? $svc[5] . ':' : '';
                    $out['svc'][$j]['mod'] = preg_replace('/:$/', '', $out['svc'][$j]['mod']);
                }

                    $out['svc'][$j]['chg']  = $seg[2];
                    $out['svc'][$j]['paid'] = $seg[3];
                    $out['svc'][$j]['adj']  = array();
                    // Note: SVC05, if present, indicates the paid units of service.
                    // It defaults to 1.
            }
        } elseif (strncmp('NM1' . $de, $seg, 4) === 0) {
            $sar = explode($de, $seg);
            $id = (isset($sar[1]) && $sar[1]) ? trim($sar[1]) : "";
            if ($id == 'QC') {
                // QC Patient
                $out[$ck]['clp'][$i]['patient_lname'] = (isset($sar[3]) && $sar[3]) ? trim($sar[3]) : "";
                $out[$ck]['clp'][$i]['patient_fname'] = (isset($sar[4]) && $sar[4]) ? trim($sar[4]) : "";
                $out[$ck]['clp'][$i]['patient_mname'] = (isset($sar[5]) && $sar[5]) ? trim($sar[5]) : "";
                $out[$ck]['clp'][$i]['patient_member_id'] = (isset($sar[9]) && $sar[9]) ? trim($sar[9]) : "";
            } elseif ($id == 'IL') {
                // IL = Insured or Subscriber
                $out[$ck]['clp'][$i]['subscriber_lname'] = (isset($sar[3]) && $sar[3]) ? trim($sar[3]) : "";
                $out[$ck]['clp'][$i]['subscriber_fname'] = (isset($sar[4]) && $sar[4]) ? trim($sar[4]) : "";
                $out[$ck]['clp'][$i]['subscriber_mname'] = (isset($sar[5]) && $sar[5]) ? trim($sar[5]) : "";
                $out[$ck]['clp'][$i]['subscriber_member_id'] = (isset($sar[9]) && $sar[9]) ? trim($sar[9]) : "";
            } elseif ($id == '82') {
                // 82 = Rendering Provider
                $out[$ck]['clp'][$i]['provider_lname'] = (isset($sar[3]) && $sar[3]) ? trim($sar[3]) : "";
                $out[$ck]['clp'][$i]['provider_fname'] = (isset($sar[4]) && $sar[4]) ? trim($sar[4]) : "";
                $out[$ck]['clp'][$i]['provider_mname'] = (isset($sar[5]) && $sar[5]) ? trim($sar[5]) : "";
                $out[$ck]['clp'][$i]['provider_member_id'] = (isset($sar[9]) && $sar[9]) ? trim($sar[9]) : "";
            } elseif ($id == 'TT') {
                //Claim automatic forward case.
                $out[$ck]['clp'][$i]['crossover'] = 1;
            }
        } elseif ((strncmp('PER' . $de, $seg, 4) === 0 ) && ($segid == 'PER' && $out['loopid'] == '2100')) {
              $sar = explode($de, $seg);
            $out['payer_insurance']  = trim($seg[2]);
            $out['warnings'] .= 'Claim contact information: ' . $seg[4];
        } elseif (strncmp('PLB' . $de, $seg, 4) === 0) {
            $sar = explode($de, $seg);
            $p = (isset($out[$ck]['plb'])) ? count($out[$ck]['plb']) : 0;
            $q = 0;
            //
            $out[$ck]['plb'][$p] = array();
            $out[$ck]['plb']['adj'] = array();
            $out[$ck]['plb'][$p]['provider'] = (isset($sar[1]) && $sar[1]) ? trim($sar[1]) : "";
            $out[$ck]['plb'][$p]['fye'] = (isset($sar[2]) && $sar[2]) ? trim($sar[2]) : "";
            // PLB02 is provider fiscal year end or CCYY1231
            //
            $plbar = array_slice($sar, 3);
            foreach ($plbar as $ky => $plb) {
                switch ($ky % 2) {
                    // PLB04 06 08 ...
                    case 0:
                        $out[$ck]['clp'][$p]['adj'][$q]['amt'] = $plb;
                        break;
                    // PLB03 05 07 ...
                    case 1:
                        if (strpos($plb, $ds)) {
                            $plb02 = explode($ds, $plb);
                            $out[$ck]['per'][$p]['adj'][$q]['code']  = $plb02[0];
                            $out[$ck]['per'][$p]['adj'][$q]['ref']  = $plb02[1];
                        } else {
                            $out[$ck]['per'][$p]['adj'][$q]['code']  = $plb;
                            $out[$ck]['per'][$p]['adj'][$q]['ref'] = '';
                        }

                        $q++;
                        break;
                }
            }
        }

             $plbar .= (isset($sar[3]) && $sar[3]) ? trim($sar[3]) : '0';
        // I am not sure that the assignment is corrent here, but based on the flow, I frame it.
    }
}
if (strncmp('SVC' . $de, $seg, 4) === 0) {
    $loopid = '2110';
}

    $acctng['lx'][$lx01] = array('ts3amt' => 0, 'fee' => 0, 'clmpmt' => 0, 'clmadj' => 0, 'prvadj' => 0, 'ptrsp' => 0);
if ($chk) {
    $acctng['pmt'] = $bpr02;
}

    // try a little accounting
if ($chk) {
    if ($acctng['pmt'] == ($acctng['clmpmt'] + $acctng['prvadj'])) {
        $bal = 'Balanced';
    } else {
        $bal = 'Not Balanced';
    }

    $pmt_html .= "<tr class='pmt'><td colspan=4>Accounting " . text($bal) . "</td></tr>" . PHP_EOL;
    $pmt_html .= "<tr class='pmt'><td>Fee " . text($acctng['fee']) . "</td><td>Adj " . text($acctng['clmadj']) . "</td><td>PtRsp " . text($acctng['ptrsp']) . "</td></tr>" . PHP_EOL;
    $pmt_html .= "<tr class='pmt'><td>PMT " . text($acctng['pmt']) . "</td><td>CLP " . text($acctng['clmpmt']) . "</td><td>PLB " . text($acctng['prvadj']) . "</td></tr>" . PHP_EOL;
}
