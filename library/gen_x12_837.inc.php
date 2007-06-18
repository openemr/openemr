<?php
// Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


// TBD: Write log messages for the "view log" screen.


require_once("Claim.class.php");

function gen_x12_837($pid, $encounter) {

  $today = time();
  $out = '';
  $claim = new Claim($pid, $encounter);
  $edicount = 0;

  $out .= "ISA" .
    "*00" .
    "*          " .
    "*00" .
    "*          " .
    "*ZZ" .
    "*" . strtoupper($claim->x12gssenderid()) .
    "*ZZ" .
    "*" . strtoupper($claim->x12gsreceiverid()) .
    "*030911" .
    "*1630" .
    "*U" .
    "*00401" .
    "*000000001" .
    "*0" .
    "*T" .
    "*:" .
    "~\n";

  $out .= "GS" .
    "*HC" .
    "*" . strtoupper($claim->x12gssenderid()) .
    "*" . strtoupper($claim->x12gsreceiverid()) .
    "*" . date('Ymd', $today) .
    "*" . date('Hi', $today) .
    "*1" .
    "*X" .
    "*" . strtoupper($claim->x12gsversionstring()) .
    "~\n";

  ++$edicount;
  $out .= "ST" .
    "*837" .
    "*0021" .
    "~\n";

  ++$edicount;
  $out .= "BHT" .
    "*0019" .
    "*00" .
    "*0123" .
    "*" . date('Ymd', $today) .
    "*1023" .
    "*CH" .
    "~\n";

  ++$edicount;
  $out .= "REF" .
    "*87" .
    "*" . strtoupper($claim->x12gsversionstring()) .
    "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 1000A Submitter
    "*41" .
    "*2" .
    "*" . strtoupper($claim->billingFacilityName()) .
    "*" .
    "*" .
    "*" .
    "*" .
    "*46" .
    "*" . strtoupper($claim->billingFacilityETIN()) .
    "~\n";

  ++$edicount;
  $out .= "PER" .
    "*IC" .
    "*" . strtoupper($claim->billingContactName()) .
    "*TE" .
    "*" . $claim->billingContactPhone() .
    "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 1000B Receiver
    "*40" .
    "*2" .
    "*" . strtoupper($claim->clearingHouseName()) .
    "*" .
    "*" .
    "*" .
    "*" .
    "*46" .
    "*" . strtoupper($claim->clearingHouseETIN()) .
    "~\n";

  $HLcount = 1;

  ++$edicount;
  $out .= "HL" .        // Loop 2000A Billing/Pay-To Provider HL Loop
    "*$HLcount" .
    "*" .
    "*20" .
    "*1" .
    "~\n";

  $HLBillingPayToProvider = $HLcount++;

  ++$edicount;
  $out .= "NM1" .       // Loop 2010AA Billing Provider
    "*85" .
    "*2" .
    "*" . strtoupper($claim->billingFacilityName()) .
    "*" .
    "*" .
    "*" .
    "*";
  if ($claim->billingFacilityNPI())
    $out .= "*XX*" . $claim->billingFacilityNPI();
  else
    $out .= "*24*" . $claim->billingFacilityETIN();
  $out .= "~\n";

  ++$edicount;
  $out .= "N3" .
    "*" . strtoupper($claim->billingFacilityStreet()) .
    "~\n";

  ++$edicount;
  $out .= "N4" .
    "*" . strtoupper($claim->billingFacilityCity()) .
    "*" . strtoupper($claim->billingFacilityState()) .
    "*" . strtoupper($claim->billingFacilityZip()) .
    "~\n";

  // Add a REF*EI*<ein> segment if NPI was specified in the NM1 above.

  if ($claim->billingFacilityNPI() && $claim->billingFacilityETIN()) {
    ++$edicount;
    $out .= "REF" .
      "*EI" .
      "*" . $claim->billingFacilityETIN() .
      "~\n";
  }

  ++$edicount;
  $out .= "REF" .
    "*" . $claim->providerNumberType() .
    "*" . $claim->providerNumber() .
    "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 2010AB Pay-To Provider
    "*87" .
    "*2" .
    "*" . strtoupper($claim->billingFacilityName()) .
    "*" .
    "*" .
    "*" .
    "*";
  if ($claim->billingFacilityNPI())
    $out .= "*XX*" . $claim->billingFacilityNPI();
  else
    $out .= "*24*" . $claim->billingFacilityETIN();
  $out .= "~\n";

  ++$edicount;
  $out .= "N3" .
    "*" . strtoupper($claim->billingFacilityStreet()) .
    "~\n";

  ++$edicount;
  $out .= "N4" .
    "*" . strtoupper($claim->billingFacilityCity()) .
    "*" . strtoupper($claim->billingFacilityState()) .
    "*" . strtoupper($claim->billingFacilityZip()) .
    "~\n";

  if ($claim->billingFacilityNPI() && $claim->billingFacilityETIN()) {
    ++$edicount;
    $out .= "REF" .
      "*EI" .
      "*" . $claim->billingFacilityETIN() .
      "~\n";
  }

  $PatientHL = 0;

  ++$edicount;
  $out .= "HL" .        // Loop 2000B Subscriber HL Loop
    "*$HLcount" .
    "*$HLBillingPayToProvider" .
    "*22" .
    "*$PatientHL" .
    "~\n";

  $HLSubscriber = $HLcount++;

  ++$edicount;
  $out .= "SBR" .       // Subscriber Information
    "*" . strtoupper($claim->payerSequence()) .
    "*" . strtoupper($claim->insuredRelationship()) .
    "*" . strtoupper($claim->groupNumber()) .
    "*" . strtoupper($claim->groupName()) .
    "*" .
    "*" .
    "*" .
    "*" .
    "*" . $claim->claimType() .
    "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 2010BA Subscriber
    "*IL" .
    "*1" .
    "*" . strtoupper($claim->insuredLastName()) .
    "*" . strtoupper($claim->insuredFirstName()) .
    "*" . strtoupper($claim->insuredMiddleName()) .
    "*" .
    "*" .
    "*MI" .
    "*" . $claim->policyNumber() .
    "~\n";

  ++$edicount;
  $out .= "N3" .
    "*" . strtoupper($claim->insuredStreet()) .
    "~\n";

  ++$edicount;
  $out .= "N4" .
    "*" . strtoupper($claim->insuredCity()) .
    "*" . strtoupper($claim->insuredState()) .
    "*" . strtoupper($claim->insuredZip()) .
    "~\n";

  ++$edicount;
  $out .= "DMG" .
    "*D8" .
    "*" . $claim->insuredDOB() .
    "*" . $claim->insuredSex() .
    "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 2010BB Payer
    "*PR" .
    "*2" .
    "*" . strtoupper($claim->payerName()) .
    "*" .
    "*" .
    "*" .
    "*" .
    "*PI" .
    "*" . $claim->payerID() .
    "~\n";

  ++$edicount;
  $out .= "N3" .
    "*" . strtoupper($claim->payerStreet()) .
    "~\n";

  ++$edicount;
  $out .= "N4" .
    "*" . strtoupper($claim->payerCity()) .
    "*" . strtoupper($claim->payerState()) .
    "*" . strtoupper($claim->payerZip()) .
    "~\n";

  if (! $claim->isSelfOfInsured()) {
    ++$edicount;
    $out .= "HL" .        // Loop 2000C Patient Information
      "*$HLcount" .
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
    $out .= "NM1" .       // Loop 2010CA Patient
      "*QC" .
      "*1" .
      "*" . strtoupper($claim->patientLastName()) .
      "*" . strtoupper($claim->patientFirstName()) .
      "*" . strtoupper($claim->patientMiddleName()) .
      "~\n";

    ++$edicount;
    $out .= "N3" .
      "*" . strtoupper($claim->patientStreet()) .
      "~\n";

    ++$edicount;
    $out .= "N4" .
      "*" . strtoupper($claim->patientCity()) .
      "*" . strtoupper($claim->patientState()) .
      "*" . strtoupper($claim->patientZip()) .
      "~\n";

    ++$edicount;
    $out .= "DMG" .
      "*D8" .
      "*" . $claim->patientDOB() .
      "*" . $claim->patientSex() .
      "~\n";
  } // end of patient different from insured

  $proccount = $claim->procCount();

  $clm_total_charges = 0;
  for ($prockey = 0; $prockey < $proccount; ++$prockey) {
    $clm_total_charges += $claim->cptCharges($prockey);
  }

  ++$edicount;
  $out .= "CLM" .       // Loop 2300 Claim
    "*$pid-$encounter" .
    "*" . sprintf("%.2f",$clm_total_charges) .
    "*" .
    "*" .
    "*" . $claim->facilityPOS() . "::1" .
    "*Y" .
    "*A" .
    "*Y" .
    "*Y" .
    "*C" .
    "~\n";

  ++$edicount;
  $out .= "DTP" .       // Date of Onset
    "*431" .
    "*D8" .
    "*" . $claim->onsetDate() .
    "~\n";

  if ($claim->facilityPOS() == '21') {
    ++$edicount;
    $out .= "DTP" .     // Date of Hospitalization
      "*435" .
      "*D8" .
      "*" . $claim->onsetDate() .
      "~\n";
  }

  $patientpaid = $claim->patientPaidAmount();
  if ($patientpaid != 0) {
    ++$edicount;
    $out .= "AMT" .     // Patient paid amount. Page 220.
      "*F5" .
      "*" . $patientpaid .
      "~\n";
  }

  if ($claim->priorAuth()) {
    ++$edicount;
    $out .= "REF" .     // Prior Authorization Number
      "*G1" .
      "*" . $claim->priorAuth() .
      "~\n";
  }

  if ($claim->cliaCode()) {
    // Required by Medicare when in-house labs are done.
    ++$edicount;
    $out .= "REF" .     // Clinical Laboratory Improvement Amendment Number
      "*X4" .
      "*" . $claim->cliaCode() .
      "~\n";
  }

  $da = $claim->diagArray();
  ++$edicount;
  $out .= "HI";         // Health Diagnosis Codes
  $diag_type_code = 'BK';
  foreach ($da as $diag) {
    $out .= "*$diag_type_code:" . $diag;
    $diag_type_code = 'BF';
  }
  $out .= "~\n";

  if ($claim->referrerLastName()) {
    // Medicare requires referring provider's name and UPIN.
    ++$edicount;
    $out .= "NM1" .     // Loop 2310A Referring Provider
      "*DN" .
      "*1" .
      "*" . strtoupper($claim->referrerLastName()) .
      "*" . strtoupper($claim->referrerFirstName()) .
      "*" . strtoupper($claim->referrerMiddleName()) .
      "*" .
      "*";
    if ($claim->referrerNPI()) { $out .=
      "*XX" .
      "*" . $claim->referrerNPI();
    } else { $out .=
      "*34" .
      "*" . $claim->referrerSSN();
    }
    $out .= "~\n";

    ++$edicount;
    $out .= "REF" .     // Referring Provider Secondary Identification
      "*1G" .
      "*" . strtoupper($claim->referrerUPIN()) .
      "~\n";
  }

  ++$edicount;
  $out .= "NM1" .       // Loop 2310B Rendering Provider
    "*82" .
    "*1" .
    "*" . strtoupper($claim->providerLastName()) .
    "*" . strtoupper($claim->providerFirstName()) .
    "*" . strtoupper($claim->providerMiddleName()) .
    "*" .
    "*";
  if ($claim->providerNPI()) { $out .=
    "*XX" .
    "*" . $claim->providerNPI();
  } else { $out .=
    "*34" .
    "*" . $claim->providerSSN();
  }
  $out .= "~\n";

  ++$edicount;
  $out .= "PRV" .       // Rendering Provider Information
    "*PE" .
    "*ZZ" .
    "*207Q00000X" .
    "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 2310B Service Location
    "*77" .
    "*2" .
    "*" . strtoupper($claim->facilityName()) .
    "*" .
    "*" .
    "*" .
    "*";
  if ($claim->facilityNPI()) { $out .=
    "*XX*" . $claim->facilityNPI();
  } else { $out .=
    "*24*" . $claim->facilityETIN();
  }
  $out .= "~\n";

  ++$edicount;
  $out .= "N3" .
    "*" . strtoupper($claim->facilityStreet()) .
    "~\n";

  ++$edicount;
  $out .= "N4" .
    "*" . strtoupper($claim->facilityCity()) .
    "*" . strtoupper($claim->facilityState()) .
    "*" . strtoupper($claim->facilityZip()) .
    "~\n";

  // Loops 2320 and 2330*, other subscriber/payer information.
  //
  for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {

    ++$edicount;
    $out .= "SBR" . // Loop 2320, Subscriber Information - page 318
      "*" . strtoupper($claim->payerSequence($ins)) .
      "*" . strtoupper($claim->insuredRelationship($ins)) .
      "*" . strtoupper($claim->groupNumber($ins)) .
      "*" . strtoupper($claim->groupName($ins)) .
      "*" .
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
        $out .= "CAS" . // Previous payer's claim-level adjustments. Page 323.
          "*" . $a[1] .
          "*" . $a[2] .
          "*" . $a[3] .
          "~\n";
      }

      $payerpaid = $claim->payerPaidAmount($ins);
      ++$edicount;
      $out .= "AMT" . // Previous payer's paid amount. Page 332.
        "*D" .
        "*" . $payerpaid .
        "~\n";

      ++$edicount;
      $out .= "AMT" . // Patient responsibility amount per previous payer. Page 335.
        "*F2" .
        "*" . sprintf('%.2f', $claim->invoiceTotal() - $payerpaid) .
        "~\n";

    } // End of things that apply only to previous payers.

    ++$edicount;
    $out .= "OI" .  // Other Insurance Coverage Information. Page 344.
      "*" .
      "*" .
      "*Y" .
      "*B" .
      "*" .
      "*Y" .
      "~\n";

    ++$edicount;
    $out .= "NM1" . // Loop 2330A Subscriber info for other insco. Page 350.
      "*IL" .
      "*1" .
      "*" . strtoupper($claim->insuredLastName($ins)) .
      "*" . strtoupper($claim->insuredFirstName($ins)) .
      "*" . strtoupper($claim->insuredMiddleName($ins)) .
      "*" .
      "*" .
      "*MI" .
      "*" . $claim->policyNumber($ins) .
      "~\n";

    ++$edicount;
    $out .= "NM1" . // Loop 2330B Payer info for other insco. Page 359.
      "*PR" .
      "*2" .
      "*" . strtoupper($claim->payerName($ins)) .
      "*" .
      "*" .
      "*" .
      "*" .
      "*PI" .
      "*" . $claim->payerID($ins) .
      "~\n";

  } // End loops 2320/2330*.

  $loopcount = 0;

  // Procedure loop starts here.
  //
  for ($prockey = 0; $prockey < $proccount; ++$prockey) {
    ++$loopcount;

    ++$edicount;
    $out .= "LX" .      // Loop 2400 LX Service Line. Page 398.
      "*$loopcount" .
      "~\n";

    ++$edicount;
    $out .= "SV1" .     // Professional Service. Page 400.
      "*HC:" . strtoupper($claim->cptCode($prockey));
    if ($claim->cptModifier($prockey)) { $out .=
      ":" . strtoupper($claim->cptModifier($prockey));
    }
    $out .=
      "*" . sprintf('%.2f', $claim->cptCharges($prockey)) .
      "*UN" .
      "*" . $claim->cptUnits($prockey) .
      "*" .
      "*" .
      "*" . $claim->diagIndex($prockey) .
      "~\n";

    ++$edicount;
    $out .= "DTP" .     // Date of Service. Page 435.
      "*472" .
      "*D8" .
      "*" . $claim->serviceDate() .
      "~\n";

    // Loop 2410, Drug Information. Medicaid insurers seem to want this
    // with HCPCS codes.
    //
    if ($claim->cptNDCID($prockey))
      ++$edicount;
      $out .= "LIN" . // Drug Identification. Page 500+ (Addendum pg 71).
        "*4" .
        "*N4" .
        "*" . $claim->cptNDCID($prockey) .
        "~\n";

      ++$edicount;
      $out .= "CTP" . // Drug Pricing. Page 500+ (Addendum pg 74).
        "*" .
        "*" .
        "*0" . // dummy price, required by HIPAA
        "*" . $claim->cptNDCQuantity($prockey) .
        "*" . $claim->cptNDCUOM($prockey) .
        "~\n";
    }

    // Loop 2430, adjudication by previous payers.
    //
    for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {
      if ($claim->payerSequence($ins) > $claim->payerSequence())
        continue; // payer is future, not previous

      ++$edicount;
      $out .= "SVD" . // Service line adjudication. Page 554.
        "*" . $claim->payerID($ins) .
        "*" . $claim->payerPaidAmount($ins, $claim->cptCode($prockey)) .
        "*HC:" . strtoupper($claim->cptCode($prockey));
      if ($claim->cptModifier($prockey)) $out .=
        ":" . strtoupper($claim->cptModifier($prockey));
      $out .=
        "*" .
        "*" . $claim->cptUnits($prockey) .
        "~\n";

      $aarr = $claim->payerAdjustments($ins, $claim->cptCode($prockey));
      $tmpdate = '';
      foreach ($aarr as $a) {
        ++$edicount;
        $out .= "CAS" . // Previous payer's line level adjustments. Page 558.
          "*" . $a[1] .
          "*" . $a[2] .
          "*" . $a[3] .
          "~\n";
        $tmpdate = $a[0];
      }

      if ($tmpdate) {
        ++$edicount;
        $out .= "DTP" . // Previous payer's line adjustment date. Page 566.
          "*573" .
          "*D8" .
          "*$tmpdate" .
          "~\n";
      }
    } // end loop 2430
  } // end this procedure

  ++$edicount;
  $out .= "SE" .        // SE Trailer
    "*$edicount" .
    "*0021" .
    "~\n";

  $out .= "GE" .        // GE Trailer
    "*1" .
    "*1" .
    "~\n";

  $out .= "IEA" .       // IEA Trailer
    "*1" .
    "*000000001" .
    "~\n";

  return $out;
}
?>
