<?php
  // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

function parse_era_2100(&$out, $cb) {
	if ($out['loopid'] == '2110') {
		$cb($out);
	}
}

function parse_era($filename, $cb) {
	$infh = fopen($filename, 'r');
	if (! $infh) return "ERA input file open failed";

	$out = array();
	$out['loopid'] = '';
	$out['st_segment_count'] = 0;
	$buffer = '';
	$segid = '';

	while (true) {
		if (strlen($buffer) < 2048 && ! feof($infh))
			$buffer .= fread($infh, 2048);
		$tpos = strpos($buffer, '~');
		if ($tpos === false) break;
		$inline = substr($buffer, 0, $tpos);
		$buffer = substr($buffer, $tpos + 1);

		// echo $inline . "\n"; // debugging

		$seg = explode('|', $inline);
		$segid = $seg[0];

		if ($segid == 'ISA') {
			if ($out['loopid']) return 'Unexpected ISA segment';
			$out['isa_sender_id']      = trim($seg[6]);
			$out['isa_receiver_id']    = trim($seg[8]);
			$out['isa_control_number'] = trim($seg[13]);
			// TBD: clear some stuff if we allow multiple transmission files.
		}
		else if ($segid == 'GS') {
			if ($out['loopid']) return 'Unexpected GS segment';
			$out['gs_date'] = trim($seg[4]);
			$out['gs_time'] = trim($seg[5]);
			$out['gs_control_number'] = trim($seg[6]);
		}
		else if ($segid == 'ST') {
			parse_era_2100($out, $cb);
			$out['loopid'] = '';
			$out['st_control_number'] = trim($seg[2]);
			$out['st_segment_count'] = 0;
		}
		else if ($segid == 'BPR') {
			if ($out['loopid']) return 'Unexpected BPR segment';
			$out['check_amount'] = trim($seg[2]);
			$out['check_date'] = trim($seg[16]); // yyyymmdd
			// TBD: BPR04 is a payment method code.
		}
		else if ($segid == 'TRN') {
			if ($out['loopid']) return 'Unexpected TRN segment';
			$out['check_number'] = trim($seg[2]);
			$out['payer_tax_id'] = substr($seg[3], 1); // 9 digits
			$out['payer_id'] = trim($seg[4]);
			// Note: TRN04 further qualifies the paying entity within the
			// organization identified by TRN03.
		}
		else if ($segid == 'REF' && $seg[1] == 'EV') {
			if ($out['loopid']) return 'Unexpected REF|EV segment';
		}
		else if ($segid == 'CUR' && ! $out['loopid']) {
			if ($seg[3] && $seg[3] != 1.0) {
				return("We cannot handle foreign currencies!");
			}
		}
		else if ($segid == 'REF' && ! $out['loopid']) {
			// ignore
		}
		else if ($segid == 'DTM' && $seg[1] == '405') {
			if ($out['loopid']) return 'Unexpected DTM|405 segment';
			$out['production_date'] = trim($seg[2]); // yyyymmdd
		}
		//
		// Loop 1000A is Payer Information.
		//
		else if ($segid == 'N1' && $seg[1] == 'PR') {
			if ($out['loopid']) return 'Unexpected N1|PR segment';
			$out['loopid'] = '1000A';
			$out['payer_name'] = trim($seg[2]);
		}
		else if ($segid == 'N3' && $out['loopid'] == '1000A') {
			$out['payer_street'] = trim($seg[1]);
			// TBD: N302 may exist as an additional address line.
		}
		else if ($segid == 'N4' && $out['loopid'] == '1000A') {
			$out['payer_city']  = trim($seg[1]);
			$out['payer_state'] = trim($seg[2]);
			$out['payer_zip']   = trim($seg[3]);
		}
		else if ($segid == 'REF' && $out['loopid'] == '1000A') {
			// Other types of REFs may be given to identify the payer, but we
			// ignore them.
		}
		else if ($segid == 'PER' && $out['loopid'] == '1000A') {
			// TBD: Report payer contact information as a note.
		}
		//
		// Loop 1000B is Payee Identification.
		//
		else if ($segid == 'N1' && $seg[1] == 'PE') {
			if ($out['loopid'] != '1000A') return 'Unexpected N1|PE segment';
			$out['loopid'] = '1000B';
			$out['payee_name']   = trim($seg[2]);
			$out['payee_tax_id'] = trim($seg[4]);
		}
		else if ($segid == 'N3' && $out['loopid'] == '1000B') {
			$out['payee_street'] = trim($seg[1]);
		}
		else if ($segid == 'N4' && $out['loopid'] == '1000B') {
			$out['payee_city']  = trim($seg[1]);
			$out['payee_state'] = trim($seg[2]);
			$out['payee_zip']   = trim($seg[3]);
		}
		else if ($segid == 'REF' && $out['loopid'] == '1000B') {
			// Used to report additional ID numbers.  Ignored.
		}
		//
		// Loop 2000 provides for logical grouping of claim payment information.
		// LX is required if any CLPs are present, but so far we do not care
		// about loop 2000 content.
		//
		else if ($segid == 'LX') {
			if (! $out['loopid']) return 'Unexpected LX segment';
			parse_era_2100($out, $cb);
			$out['loopid'] = '2000';
		}
		else if ($segid == 'TS2' && $out['loopid'] == '2000') {
			// ignore
		}
		else if ($segid == 'TS3' && $out['loopid'] == '2000') {
			// ignore
		}
		//
		// Loop 2100 is Claim Payment Information. The good stuff begins here.
		//
		else if ($segid == 'CLP') {
			if (! $out['loopid']) return 'Unexpected CLP segment';
			parse_era_2100($out, $cb);
			$out['loopid'] = '2100';
			$out['warnings'] = '';
			// Clear some stuff to start the new claim:
			$out['subscriber_lname']     = '';
			$out['subscriber_fname']     = '';
			$out['subscriber_mname']     = '';
			$out['subscriber_member_id'] = '';
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
			$out['our_claim_id']      = trim($seg[1]);
			//
			$out['claim_status_code'] = trim($seg[2]);
			$out['amount_charged']    = trim($seg[3]);
			$out['amount_approved']   = trim($seg[4]);
			$out['amount_patient']    = trim($seg[5]); // pt responsibility, copay + deductible
			$out['payer_claim_id']    = trim($seg[7]); // payer's claim number
		}
		else if ($segid == 'CAS' && $out['loopid'] == '2100') {
			// This is a claim-level adjustment and should be unusual.
			// Handle it by creating a dummy zero-charge service item and then
			// populating the adjustments into it.
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
		// QC = Patient
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
		// 74 = Corrected Insured
		// TT = Crossover Carrier (Transfer To another payer)
		// PR = Corrected Payer
		else if ($segid == 'NM1' && $out['loopid'] == '2100') {
			// $out['warnings'] .= "NM1 segment at claim level ignored.\n";
		}
		else if ($segid == 'MOA' && $out['loopid'] == '2100') {
			$out['warnings'] .= "MOA segment at claim level ignored.\n";
		}
		// REF segments may provide various identifying numbers. REF02 is:
		// 1L = Group or Policy Number
		// 1W = Member Identification Number
		// 9A = Repriced Claim Reference Number
		// 9C = Adjusted Repriced Claim Reference Number
		// A6 = Employee Identification Number
		// BB = Authorization Number
		// CE = Class of Contract Code
		// EA = Medical Record Identification Number
		// F8 = Original Reference Number
		// G1 = Prior Authorization Number
		// G3 = Predetermination of Benefits Identification Number
		// IG = Insurance Policy Number
		// SY = Social Security Number
		// 1A, 1B, 1C, 1D, 1G, 1H, D3, G2 = various rendering provider numbers
		else if ($segid == 'REF' && $seg[1] == '1W' && $out['loopid'] == '2100') {
			$out['claim_comment'] = trim($seg[2]);
		}
		else if ($segid == 'REF' && $out['loopid'] == '2100') {
			// ignore
		}
		else if ($segid == 'DTM' && $seg[1] == '050' && $out['loopid'] == '2100') {
			$out['claim_date'] = trim($seg[2]); // yyyymmdd
		}
		// 036 = expiration date of coverage
		// 050 = date claim received by payer
		// 232 = claim statement period start
		// 233 = claim statement period end
		else if ($segid == 'DTM' && $out['loopid'] == '2100') {
			// ignore?
		}
		else if ($segid == 'PER' && $out['loopid'] == '2100') {
			$out['warnings'] .= 'Claim contact information: ' .
				$seg[4] . "\n";
		}
		// For AMT01 see the Amount Qualifier Codes on pages 135-135 of the
		// Implementation Guide.  AMT is only good for comments and is not
		// part of claim balancing.
		else if ($segid == 'AMT' && $out['loopid'] == '2100') {
			$out['warnings'] .= "AMT segment at claim level ignored.\n";
		}
		// For QTY01 see the Quantity Qualifier Codes on pages 137-138 of the
		// Implementation Guide.  QTY is only good for comments and is not
		// part of claim balancing.
		else if ($segid == 'QTY' && $out['loopid'] == '2100') {
			$out['warnings'] .= "QTY segment at claim level ignored.\n";
		}
		//
		// Loop 2110 is Service Payment Information.
		//
		else if ($segid == 'SVC') {
			if (! $out['loopid']) return 'Unexpected SVC segment';
			$out['loopid'] = '2110';
			$svc = explode('^', $seg[1]);
			if ($svc[0] != 'HC') return 'SVC segment has unexpected qualifier';
			// TBD: Other qualifiers are possible; see IG pages 140-141.
			$i = count($out['svc']);
			$out['svc'][$i] = array();
			$out['svc'][$i]['code'] = $svc[1];
			$out['svc'][$i]['mod']  = $svc[2] ? $svc[2] : '';
			// TBD: There may be up to 4 procedure modifiers in $svc[2] thru [5].
			$out['svc'][$i]['chg']  = $seg[2];
			$out['svc'][$i]['paid'] = $seg[3];
			$out['svc'][$i]['adj']  = array();

			// Note: SVC05, if present, indicates the paid units of service.
			// It defaults to 1.

			// Note: In the case of bundling, SVC06 reports the original procedure
			// code, there are adjustments of the old procedure codes that zero out
			// the original charge amounts, a negative adjustment of the new
			// procedure code(s) reflecting the old charges, and other "normal"
			// adjustments to these charges.

		}
		// DTM01 identifies the type of service date:
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
				$j = count($out['svc'][$i]['adj']);
				$out['svc'][$i]['adj'][$j] = array();
				$out['svc'][$i]['adj'][$j]['group_code']  = $seg[1];
				$out['svc'][$i]['adj'][$j]['reason_code'] = $seg[$k];
				$out['svc'][$i]['adj'][$j]['amount']      = $seg[$k+1];
				// Note: $seg[$k+2] is "quantity".  A value here indicates a change to
				// the number of units of service.  We're ignoring that for now.
			}
		}
		else if ($segid == 'REF' && $out['loopid'] == '2110') {
			// ignore
		}
		else if ($segid == 'AMT' && $seg[1] == 'B6' && $out['loopid'] == '2110') {
			$i = count($out['svc']) - 1;
			$out['svc'][$i]['allowed'] = $seg[2]; // report this amount as a note
		}
		else if ($segid == 'LQ' && $seg[1] == 'HE' && $out['loopid'] == '2110') {
			$i = count($out['svc']) - 1;
			$out['svc'][$i]['remark'] = $seg[2];
		}
		else if ($segid == 'QTY' && $out['loopid'] == '2110') {
			$out['warnings'] .= "QTY segment at service level ignored.\n";
		}
		else if ($segid == 'PLB') {
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
			parse_era_2100($out, $cb);
			$out['loopid'] = '';
			if ($out['st_control_number'] != trim($seg[2])) {
				return 'Ending transaction set control number mismatch';
			}
			if (($out['st_segment_count'] + 1) != trim($seg[1])) {
				return 'Ending transaction set segment count mismatch';
			}
		}
		else if ($segid == 'GE') {
			if ($out['loopid']) return 'Unexpected GE segment';
			if ($out['gs_control_number'] != trim($seg[2])) {
				return 'Ending functional group control number mismatch';
			}
		}
		else if ($segid == 'IEA') {
			if ($out['loopid']) return 'Unexpected IEA segment';
			if ($out['isa_control_number'] != trim($seg[2])) {
				return 'Ending interchange control number mismatch';
			}
		}
		else {
			return "Unknown or unexpected segment ID $segid";
		}

		++$out['st_segment_count'];
	}

	if ($segid != 'IEA') return 'Premature end of ERA file';
	return '';
}
?>
