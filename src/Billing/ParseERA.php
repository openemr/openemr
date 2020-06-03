<?php

/*
 * The functions of this class support the billing process like the script billing_process.php.
 *
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

class ParseERA
{
    public static function parseERA2100(&$out, $cb)
    {
        if ($out['loopid'] == '2110' || $out['loopid'] == '2100') {
            // Production date is posted with adjustments, so make sure it exists.
            if (!$out['production_date']) {
                $out['production_date'] = $out['check_date'];
            }

            // Force the sum of service payments to equal the claim payment
            // amount, and the sum of service adjustments to equal the CLP's
            // (charged amount - paid amount - patient responsibility amount).
            // This may result from claim-level adjustments, and in this case the
            // first SVC item that we stored was a 'Claim' type.  It also may result
            // from poorly reported payment reversals, in which case we may need to
            // create the 'Claim' service type here.
            //
            $paytotal = $out['amount_approved'];
            $adjtotal = $out['amount_charged'] - $out['amount_approved'] - $out['amount_patient'];
            foreach ($out['svc'] as $svc) {
                $paytotal -= $svc['paid'];
                foreach ($svc['adj'] as $adj) {
                    if ($adj['group_code'] != 'PR') {
                        $adjtotal -= $adj['amount'];
                    }
                }
            }

            $paytotal = round($paytotal, 2);
            $adjtotal = round($adjtotal, 2);
            if ($paytotal != 0 || $adjtotal != 0) {
                if ($out['svc'][0]['code'] != 'Claim') {
                    array_unshift($out['svc'], array());
                    $out['svc'][0]['code'] = 'Claim';
                    $out['svc'][0]['mod'] = '';
                    $out['svc'][0]['chg'] = '0';
                    $out['svc'][0]['paid'] = '0';
                    $out['svc'][0]['adj'] = array();
                    $out['warnings'] .= "Procedure 'Claim' is inserted artificially to " .
                        "force claim balancing.\n";
                }

                $out['svc'][0]['paid'] += $paytotal;
                if ($adjtotal) {
                    $j = count($out['svc'][0]['adj']);
                    $out['svc'][0]['adj'][$j] = array();
                    $out['svc'][0]['adj'][$j]['group_code'] = 'CR'; // presuming a correction or reversal
                    $out['svc'][0]['adj'][$j]['reason_code'] = 'Balancing';
                    $out['svc'][0]['adj'][$j]['amount'] = $adjtotal;
                }

                // if ($out['svc'][0]['code'] != 'Claim') {
                //   $out['warnings'] .= "First service item payment amount " .
                //   "adjusted by $paytotal due to payment imbalance. " .
                //   "This should not happen!\n";
                // }
            }

            $cb($out);
        }
    }

    public static function parseERA($filename, $cb)
    {
        $delimiter1 = '~';
        $delimiter2 = '|';
        $delimiter3 = '^';

        $infh = fopen($filename, 'r');
        if (!$infh) {
            return "ERA input file open failed";
        }

        $out = array();
        $out['loopid'] = '';
        $out['st_segment_count'] = 0;
        $buffer = '';
        $segid = '';

        while (true) {
            if (strlen($buffer) < 2048 && !feof($infh)) {
                $buffer .= fread($infh, 2048);
            }

            $tpos = strpos($buffer, $delimiter1);
            if ($tpos === false) {
                break;
            }

            $inline = substr($buffer, 0, $tpos);
            $buffer = substr($buffer, $tpos + 1);
            // remove carriage returns and new lines that some payers send
            $buffer = str_replace(array("\n", "\r"), '', $buffer);

            // If this is the ISA segment then figure out what the delimiters are.
            if ($segid === '' && substr($inline, 0, 3) === 'ISA') {
                $delimiter2 = substr($inline, 3, 1);
                $delimiter3 = substr($inline, -1);
            }

            $seg = explode($delimiter2, $inline);
            $segid = $seg[0];

            if ($segid == 'ISA') {
                if ($out['loopid']) {
                    return 'Unexpected ISA segment';
                }

                $out['isa_sender_id'] = trim($seg[6]);
                $out['isa_receiver_id'] = trim($seg[8]);
                $out['isa_control_number'] = trim($seg[13]);
                // TBD: clear some stuff if we allow multiple transmission files.
            } elseif ($segid == 'GS') {
                if ($out['loopid']) {
                    return 'Unexpected GS segment';
                }

                $out['gs_date'] = trim($seg[4]);
                $out['gs_time'] = trim($seg[5]);
                $out['gs_control_number'] = trim($seg[6]);
            } elseif ($segid == 'ST') {
                self::parseERA2100($out, $cb);
                $out['loopid'] = '';
                $out['st_control_number'] = trim($seg[2]);
                $out['st_segment_count'] = 0;
            } elseif ($segid == 'BPR') {
                if ($out['loopid']) {
                    return 'Unexpected BPR segment';
                }

                $out['check_amount'] = trim($seg[2]);
                $out['check_date'] = trim($seg[16]); // yyyymmdd
                // TBD: BPR04 is a payment method code.
            } elseif ($segid == 'TRN') {
                if ($out['loopid']) {
                    return 'Unexpected TRN segment';
                }

                $out['check_number'] = trim($seg[2]);
                $out['payer_tax_id'] = substr($seg[3], 1); // 9 digits
            //    $out['payer_id'] = trim($seg[4]); no payer id in TRN04 for 5010
                // Note: TRN04 further qualifies the paying entity within the
                // organization identified by TRN03.
            } elseif ($segid == 'REF' && $seg[1] == 'EV') {
                if ($out['loopid']) {
                    return 'Unexpected REF|EV segment';
                }
            } elseif ($segid == 'CUR' && !$out['loopid']) {
                if ($seg[3] && $seg[3] != 1.0) {
                    return ("We cannot handle foreign currencies!");
                }
            } elseif ($segid == 'REF' && !$out['loopid']) {
                // ignore
            } elseif ($segid == 'DTM' && $seg[1] == '405') {
                if ($out['loopid']) {
                    return 'Unexpected DTM|405 segment';
                }

                $out['production_date'] = trim($seg[2]); // yyyymmdd
            } elseif ($segid == 'N1' && $seg[1] == 'PR') { // Loop 1000A is Payer Information.
                if ($out['loopid']) {
                    return 'Unexpected N1|PR segment';
                }
                $out['loopid'] = '1000A';
                $out['payer_name'] = trim($seg[2]);
                $out['payer_id'] = trim($seg[4]); // will be overwritten if in REF*2U below
            } elseif ($segid == 'N3' && $out['loopid'] == '1000A') {
                $out['payer_street'] = trim($seg[1]);
                // TBD: N302 may exist as an additional address line.
            } elseif ($segid == 'N4' && $out['loopid'] == '1000A') {
                $out['payer_city'] = trim($seg[1]);
                $out['payer_state'] = trim($seg[2]);
                $out['payer_zip'] = trim($seg[3]);
            } elseif ($segid == 'REF' && $out['loopid'] == '1000A') {
                // Other types of REFs may be given to identify the payer, but we
                // ignore them.
                if (trim($seg[1] == '2U')) {
                    $out['payer_id'] = trim($seg[2]);
                }
            } elseif ($segid == 'PER' && $out['loopid'] == '1000A') {
                // TBD: Report payer contact information as a note.
            } elseif ($segid == 'N1' && $seg[1] == 'PE') { // Loop 1000B is Payee Identification.
                if ($out['loopid'] != '1000A') {
                    return 'Unexpected N1|PE segment';
                }
                $out['loopid'] = '1000B';
                $out['payee_name'] = trim($seg[2]);
                $out['payee_tax_id'] = trim($seg[4]);
            } elseif ($segid == 'N3' && $out['loopid'] == '1000B') {
                $out['payee_street'] = trim($seg[1]);
            } elseif ($segid == 'N4' && $out['loopid'] == '1000B') {
                $out['payee_city'] = trim($seg[1]);
                $out['payee_state'] = trim($seg[2]);
                $out['payee_zip'] = trim($seg[3]);
            } elseif ($segid == 'REF' && $out['loopid'] == '1000B') {
                // Used to report additional ID numbers.  Ignored.
            } elseif ($segid == 'LX') {
                //
                // Loop 2000 provides for logical grouping of claim payment information.
                // LX is required if any CLPs are present, but so far we do not care
                // about loop 2000 content.
                //
                if (!$out['loopid']) {
                    return 'Unexpected LX segment';
                }

                self::parseERA2100($out, $cb);
                $out['loopid'] = '2000';
            } elseif ($segid == 'TS2' && $out['loopid'] == '2000') {
                // ignore
            } elseif ($segid == 'TS3' && $out['loopid'] == '2000') {
                // ignore
            } elseif ($segid == 'CLP') {
                // Loop 2100 is Claim Payment Information. The good stuff begins here.
                if (!$out['loopid']) {
                    return 'Unexpected CLP segment';
                }

                self::parseERA2100($out, $cb);
                $out['loopid'] = '2100';
                $out['warnings'] = '';
                // Clear some stuff to start the new claim:
                $out['subscriber_lname'] = '';
                $out['subscriber_fname'] = '';
                $out['subscriber_mname'] = '';
                $out['subscriber_member_id'] = '';
                $out['crossover'] = 0;
                $out['corrected'] = 0;
                $out['svc'] = array();
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
                $out['our_claim_id'] = trim($seg[1]);
                //
                $out['claim_status_code'] = trim($seg[2]);
                $out['amount_charged'] = trim($seg[3]);
                $out['amount_approved'] = trim($seg[4]);
                $out['amount_patient'] = trim($seg[5]); // pt responsibility, copay + deductible
                $out['payer_claim_id'] = trim($seg[7]); // payer's claim number
            } elseif ($segid == 'CAS' && $out['loopid'] == '2100') {
                // This is a claim-level adjustment and should be unusual.
                // Handle it by creating a dummy zero-charge service item and
                // then populating the adjustments into it.  See also code in
                // self::parseERA2100() which will later plug in a payment reversal
                // amount that offsets these adjustments.
                $i = 0; // if present, the dummy service item will be first.
                if (!$out['svc'][$i]) {
                    $out['svc'][$i] = array();
                    $out['svc'][$i]['code'] = 'Claim';
                    $out['svc'][$i]['mod'] = '';
                    $out['svc'][$i]['chg'] = '0';
                    $out['svc'][$i]['paid'] = '0';
                    $out['svc'][$i]['adj'] = array();
                }

                for ($k = 2; $k < 20; $k += 3) {
                    if (!$seg[$k]) {
                        break;
                    }

                    $j = count($out['svc'][$i]['adj']);
                    $out['svc'][$i]['adj'][$j] = array();
                    $out['svc'][$i]['adj'][$j]['group_code'] = $seg[1];
                    $out['svc'][$i]['adj'][$j]['reason_code'] = $seg[$k];
                    $out['svc'][$i]['adj'][$j]['amount'] = $seg[$k + 1];
                }
            } elseif ($segid == 'NM1' && $seg[1] == 'QC' && $out['loopid'] == '2100') { // QC = Patient
                $out['patient_lname'] = trim($seg[3]);
                $out['patient_fname'] = trim($seg[4]);
                $out['patient_mname'] = trim($seg[5]);
                $out['patient_member_id'] = trim($seg[9]);
            } elseif ($segid == 'NM1' && $seg[1] == 'IL' && $out['loopid'] == '2100') { // IL = Insured or Subscriber
                $out['subscriber_lname'] = trim($seg[3]);
                $out['subscriber_fname'] = trim($seg[4]);
                $out['subscriber_mname'] = trim($seg[5]);
                $out['subscriber_member_id'] = trim($seg[9]);
            } elseif ($segid == 'NM1' && $seg[1] == '82' && $out['loopid'] == '2100') { // 82 = Rendering Provider
                $out['provider_lname'] = trim($seg[3]);
                $out['provider_fname'] = trim($seg[4]);
                $out['provider_mname'] = trim($seg[5]);
                $out['provider_member_id'] = trim($seg[9]);
            } elseif ($segid == 'NM1' && $seg[1] == 'TT' && $out['loopid'] == '2100') { // TT = Crossover Carrier (Transfer To another payer)
                $out['crossover'] = 1; //Claim automatic forward case.
            } elseif ($segid == 'NM1' && $seg[1] == '74' && $out['loopid'] == '2100') { // 74 = Corrected Insured
                $out['corrected'] = 1; // Updated policy number case.
                $out['corrected_mbi'] = trim($seg[9]); // Usually MBI from Medicare
            } elseif ($segid == 'NM1' && $out['loopid'] == '2100') { // PR = Corrected Payer
                // $out['warnings'] .= "NM1 segment at claim level ignored.\n";
            } elseif ($segid == 'MOA' && $out['loopid'] == '2100') {
                $out['warnings'] .= "MOA segment at claim level ignored.\n";
            } elseif ($segid == 'REF' && $seg[1] == '1W' && $out['loopid'] == '2100') {
                // REF segments may provide various identifying numbers, where REF02
                // indicates the type of number.
                $out['claim_comment'] = trim($seg[2]);
            } elseif ($segid == 'REF' && $out['loopid'] == '2100') {
                // ignore
            } elseif ($segid == 'DTM' && $seg[1] == '050' && $out['loopid'] == '2100') {
                $out['claim_date'] = trim($seg[2]); // yyyymmdd
            } elseif ($segid == 'DTM' && $out['loopid'] == '2100') { // 036 = expiration date of coverage
                // 050 = date claim received by payer
                // 232 = claim statement period start
                // 233 = claim statement period end
                // ignore?
            } elseif ($segid == 'PER' && $out['loopid'] == '2100') {
                $out['payer_insurance'] = trim($seg[2]);
                $out['warnings'] .= 'Claim contact information: ' .
                    $seg[4] . "\n";
            } elseif ($segid == 'AMT' && $out['loopid'] == '2100') { // For AMT01 see the Amount Qualifier Codes on
                // pages 135-135 of the Implementation Guide.
                // AMT is only good for comments and is not part of claim balancing.
                $out['warnings'] .= "AMT segment at claim level ignored.\n";
            } elseif ($segid == 'QTY' && $out['loopid'] == '2100') { // For QTY01 see the Quantity Qualifier Codes on
                // pages 137-138 of the Implementation Guide.
                // QTY is only good for comments and is not part of claim balancing.
                $out['warnings'] .= "QTY segment at claim level ignored.\n";
            } elseif ($segid == 'SVC') { // Loop 2110 is Service Payment Information.
                if (!$out['loopid']) {
                    return 'Unexpected SVC segment';
                }

                $out['loopid'] = '2110';
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

                if ($svc[0] != 'HC') {
                    return 'SVC segment has unexpected qualifier';
                }

                // TBD: Other qualifiers are possible; see IG pages 140-141.
                $i = count($out['svc']);
                $out['svc'][$i] = array();
                // It seems some payers append the modifier with no separator!
                if (strlen($svc[1]) == 7 && empty($svc[2])) {
                    $out['svc'][$i]['code'] = substr($svc[1], 0, 5);
                    $out['svc'][$i]['mod'] = substr($svc[1], 5);
                } else {
                    $out['svc'][$i]['code'] = $svc[1];
                    $out['svc'][$i]['mod'] = $svc[2] ? $svc[2] . ':' : '';
                    $out['svc'][$i]['mod'] .= $svc[3] ? $svc[3] . ':' : '';
                    $out['svc'][$i]['mod'] .= $svc[4] ? $svc[4] . ':' : '';
                    $out['svc'][$i]['mod'] .= $svc[5] ? $svc[5] . ':' : '';
                    $out['svc'][$i]['mod'] = preg_replace('/:$/', '', $out['svc'][$i]['mod']);
                }

                $out['svc'][$i]['chg'] = $seg[2];
                $out['svc'][$i]['paid'] = $seg[3];
                $out['svc'][$i]['adj'] = array();
                // Note: SVC05, if present, indicates the paid units of service.
                // It defaults to 1.
            } elseif ($segid == 'DTM' && $out['loopid'] == '2110') { // DTM01 identifies the type of service date:
                // 472 = a single date of service
                // 150 = service period start
                // 151 = service period end
                $out['dos'] = trim($seg[2]); // yyyymmdd
            } elseif ($segid == 'CAS' && $out['loopid'] == '2110') {
                $i = count($out['svc']) - 1;
                for ($k = 2; $k < 20; $k += 3) {
                    if (!$seg[$k]) {
                        break;
                    }

                    // removing inversion for CO*144 MIPS incentive adjustment to prevent claim balancing
                    if ($seg[1] == 'CO' && $seg[$k + 1] < 0 && $seg[$k] !== '144') {
                        $out['warnings'] .= "Negative Contractual Obligation adjustment " .
                            "seems wrong. Inverting, but should be checked!\n";
                        $seg[$k + 1] = 0 - $seg[$k + 1];
                    }

                    $j = count($out['svc'][$i]['adj']);
                    $out['svc'][$i]['adj'][$j] = array();
                    $out['svc'][$i]['adj'][$j]['group_code'] = $seg[1];
                    $out['svc'][$i]['adj'][$j]['reason_code'] = $seg[$k];
                    $out['svc'][$i]['adj'][$j]['amount'] = $seg[$k + 1];
                    // Note: $seg[$k+2] is "quantity".  A value here indicates a change to
                    // the number of units of service.  We're ignoring that for now.
                }
            } elseif ($segid == 'REF' && $out['loopid'] == '2110') {
                // ignore
            } elseif ($segid == 'AMT' && $seg[1] == 'B6' && $out['loopid'] == '2110') {
                $i = count($out['svc']) - 1;
                $out['svc'][$i]['allowed'] = $seg[2]; // report this amount as a note
            } elseif ($segid == 'AMT' && $out['loopid'] == '2110') {
                $out['warnings'] .= "$inline at service level ignored.\n";
            } elseif ($segid == 'LQ' && $seg[1] == 'HE' && $out['loopid'] == '2110') {
                $i = count($out['svc']) - 1;
                $out['svc'][$i]['remark'] = $seg[2];
            } elseif ($segid == 'QTY' && $out['loopid'] == '2110') {
                $out['warnings'] .= "QTY segment at service level ignored.\n";
            } elseif ($segid == 'PLB') {
                // Provider-level adjustments are a General Ledger thing and should not
                // alter the A/R for the claim, so we just report them as notes.
                for ($k = 3; $k < 15; $k += 2) {
                    if (!$seg[$k]) {
                        break;
                    }

                    $out['warnings'] .= 'PROVIDER LEVEL ADJUSTMENT (not claim-specific): $' .
                        sprintf('%.2f', $seg[$k + 1]) . " with reason code " . $seg[$k] . "\n";
                    // Note: For PLB adjustment reason codes see IG pages 165-170.
                }
            } elseif ($segid == 'SE') {
                self::parseERA2100($out, $cb);
                $out['loopid'] = '';
                if ($out['st_control_number'] != trim($seg[2])) {
                    return 'Ending transaction set control number mismatch';
                }

                if (($out['st_segment_count'] + 1) != trim($seg[1])) {
                    return 'Ending transaction set segment count mismatch';
                }
            } elseif ($segid == 'GE') {
                if ($out['loopid']) {
                    return 'Unexpected GE segment';
                }

                if ($out['gs_control_number'] != trim($seg[2])) {
                    return 'Ending functional group control number mismatch';
                }
            } elseif ($segid == 'IEA') {
                if ($out['loopid']) {
                    return 'Unexpected IEA segment';
                }

                if ($out['isa_control_number'] != trim($seg[2])) {
                    return 'Ending interchange control number mismatch';
                }
            } else {
                return "Unknown or unexpected segment ID $segid";
            }

            ++$out['st_segment_count'];
        }

        if ($segid != 'IEA') {
            return 'Premature end of ERA file';
        }

        return '';
    }

    //for getting the check details and provider details
    public static function parseERAForCheck($filename)
    {
        $delimiter1 = '~';
        $delimiter2 = '|';
        $delimiter3 = '^';

        $infh = fopen($filename, 'r');
        if (!$infh) {
            return "ERA input file open failed";
        }

        $out = array();
        $out['loopid'] = '';
        $out['st_segment_count'] = 0;
        $buffer = '';
        $segid = '';
        $check_count = 0;
        while (true) {
            if (strlen($buffer) < 2048 && !feof($infh)) {
                $buffer .= fread($infh, 2048);
            }

            $tpos = strpos($buffer, $delimiter1);
            if ($tpos === false) {
                break;
            }

            $inline = substr($buffer, 0, $tpos);
            $buffer = substr($buffer, $tpos + 1);
            // remove carriage returns and new lines that some payers send
            $buffer = str_replace(array("\n", "\r"), '', $buffer);

            // If this is the ISA segment then figure out what the delimiters are.
            if ($segid === '' && substr($inline, 0, 3) === 'ISA') {
                $delimiter2 = substr($inline, 3, 1);
                $delimiter3 = substr($inline, -1);
            }

            $seg = explode($delimiter2, $inline);
            $segid = $seg[0];

            if ($segid == 'ISA') {
            } elseif ($segid == 'BPR') {
                ++$check_count;
                //if ($out['loopid']) return 'Unexpected BPR segment';
                $out['check_amount' . $check_count] = trim($seg[2]);
                $out['check_date' . $check_count] = trim($seg[16]); // yyyymmdd
                // TBD: BPR04 is a payment method code.
            } elseif ($segid == 'N1' && $seg[1] == 'PE') {
                //if ($out['loopid'] != '1000A') return 'Unexpected N1|PE segment';
                $out['loopid'] = '1000B';
                $out['payee_name' . $check_count] = trim($seg[2]);
                $out['payee_tax_id' . $check_count] = trim($seg[4]);
            } elseif ($segid == 'TRN') {
                //if ($out['loopid']) return 'Unexpected TRN segment';
                $out['check_number' . $check_count] = trim($seg[2]);
                $out['payer_tax_id' . $check_count] = substr($seg[3], 1); // 9 digits
                $out['payer_id' . $check_count] = trim($seg[4]);
                // Note: TRN04 further qualifies the paying entity within the
                // organization identified by TRN03.
            }


            ++$out['st_segment_count'];
        }

        $out['check_count'] = $check_count;
        era_callback_check($out);

        if ($segid != 'IEA') {
            return 'Premature end of ERA file';
        }

        return '';
    }
}
