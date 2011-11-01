<?php
// Copyright (C) 2007-2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("Claim.class.php");

function gen_x12_837($pid, $encounter, &$log, $encounter_claim=false) {

  $today = time();
  $out = '';
  $claim = new Claim($pid, $encounter);
  $edicount = 0;

  // This is true for the 5010 standard, false for 4010.
  // x12gsversionstring() should be "005010X222A1" or "004010X098A1".
  $CMS_5010 = strpos($claim->x12gsversionstring(), '5010') !== false;

  $log .= "Generating claim $pid-$encounter for " .
    $claim->patientFirstName()  . ' ' .
    $claim->patientMiddleName() . ' ' .
    $claim->patientLastName()   . ' on ' .
    date('Y-m-d H:i', $today) . ".\n";

  $out .= "ISA" .
    "*00" .
    "*          " .
    "*00" .
    "*          " .
    "*" . $claim->x12gsisa05() .
    "*" . $claim->x12gssenderid() .
    "*" . $claim->x12gsisa07() .
    "*" . $claim->x12gsreceiverid() .
    "*030911" .
    "*1630" .
    "*" . ($CMS_5010 ? "^"     : "U"    ) .
    "*" . ($CMS_5010 ? "00501" : "00401") .
    "*000000001" .
    "*" . $claim->x12gsisa14() .
    "*" . $claim->x12gsisa15() .
    "*:" .
    "~\n";

  $out .= "GS" .
    "*HC" .
    "*" . $claim->x12gsgs02() .
    "*" . trim($claim->x12gsreceiverid()) .
    "*" . date('Ymd', $today) .
    "*" . date('Hi', $today) .
    "*1" .
    "*X" .
    "*" . $claim->x12gsversionstring() .
    "~\n";

  ++$edicount;
  $out .= "ST" .
    "*837" .
    "*0021" .
    // Spec says the following is optional, so should be able to leave it out.
    ($CMS_5010 ? ("*" . $claim->x12gsversionstring()) : "") .
    "~\n";

  ++$edicount;
  $out .= "BHT" .
    "*0019" .                             // 0019 is required here
    "*00" .                               // 00 = original transmission
    "*0123" .                             // reference identification
    "*" . date('Ymd', $today) .           // transaction creation date
    "*" . date('Hi', $today) .            // transaction creation time
    ($encounter_claim ? "*RP" : "*CH") .  // RP = reporting, CH = chargeable
    "~\n";

  if (!$CMS_5010) {
    // This segment was deleted for 5010.
    ++$edicount;
    $out .= "REF" .
      "*87" .
      "*" . $claim->x12gsversionstring() .
      "~\n";
  }

  ++$edicount;
  //Field length is limited to 35. See nucc dataset page 63 www.nucc.org
  $billingFacilityName = substr($claim->billingFacilityName(), 0, $CMS_5010 ? 60 : 35);
  $out .= "NM1" .       // Loop 1000A Submitter
    "*41" .
    "*2" .
    "*" . $billingFacilityName .
    "*" .
    "*" .
    "*" .
    "*" .
    "*46";
   if (trim($claim->x12gsreceiverid()) == '470819582') { // if ECLAIMS EDI
    $out  .=  "*" . $claim->clearingHouseETIN();
   } else {
    $out  .=  "*" . $claim->billingFacilityETIN();
   }
    $out .= "~\n";

  ++$edicount;
  $out .= "PER" .
    "*IC" .
    "*" . $claim->billingContactName() .
    "*TE" .
    "*" . $claim->billingContactPhone();
  if ($claim->x12gsper06()) {
    $out .= "*ED*" . $claim->x12gsper06();
  }
  $out .= "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 1000B Receiver
    "*40" .
    "*2" .
    "*" . $claim->clearingHouseName() .
    "*" .
    "*" .
    "*" .
    "*" .
    "*46" .
    "*" . $claim->clearingHouseETIN() .
    "~\n";

  $HLcount = 1;

  ++$edicount;
  $out .= "HL" .        // Loop 2000A Billing/Pay-To Provider HL Loop
    "*$HLcount" .
    "*" .
    "*20" .
    "*1" .              // 1 indicates there are child segments
    "~\n";

  $HLBillingPayToProvider = $HLcount++;

  // Situational PRV segment (for provider taxonomy code) omitted here.
  // Situational CUR segment (foreign currency information) omitted here.

  ++$edicount;
  //Field length is limited to 35. See nucc dataset page 63 www.nucc.org
  $billingFacilityName = substr($claim->billingFacilityName(), 0, $CMS_5010 ? 60 : 35);
  $out .= "NM1" .       // Loop 2010AA Billing Provider
    "*85" .
    "*2" .
    "*" . $billingFacilityName .
    "*" .
    "*" .
    "*" .
    "*";
  if ($claim->billingFacilityNPI()) {
    $out .= "*XX*" . $claim->billingFacilityNPI();
  }
  else {
    $log .= "*** Billing facility has no NPI.\n";
    if ($CMS_5010) {
      $out .= "*XX*";
    }
    else {
      $out .= "*24*" . $claim->billingFacilityETIN();
    }
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
    "*" . $claim->billingFacilityZip() .
    "~\n";

  if ($CMS_5010 || ($claim->billingFacilityNPI() && $claim->billingFacilityETIN())) {
    ++$edicount;
    $out .= "REF" ;
    if($claim->federalIdType()){
      $out .= "*" . $claim->federalIdType();
    }
    else{
      $out .= "*EI"; // For dealing with the situation before adding TaxId type In facility.
    }
    $out .=  "*" . $claim->billingFacilityETIN() .
      "~\n";
  }

  if ($claim->providerNumberType() && $claim->providerNumber() &&
      !($CMS_5010 && $claim->billingFacilityNPI()))
  {
    ++$edicount;
    $out .= "REF" .
      "*" . $claim->providerNumberType() .
      "*" . $claim->providerNumber() .
      "~\n";
  }
  else if ($claim->providerNumber() && !$claim->providerNumberType()) {
    $log .= "*** Payer-specific provider insurance number is present but has no type assigned.\n";
  }

  // Situational PER*1C segment omitted.

  // Pay-To Address defaults to billing provider and is no longer required in 5010.
  if (!$CMS_5010) {
    ++$edicount;
    // Field length is limited to 35. See nucc dataset page 63 www.nucc.org
    $billingFacilityName = substr($claim->billingFacilityName(), 0, $CMS_5010 ? 60 : 35);
    $out .= "NM1" .       // Loop 2010AB Pay-To Provider
      "*87" .
      "*2" .
      "*" . $billingFacilityName .
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
      "*" . $claim->billingFacilityStreet() .
      "~\n";

    ++$edicount;
    $out .= "N4" .
      "*" . $claim->billingFacilityCity() .
      "*" . $claim->billingFacilityState() .
      "*" . $claim->billingFacilityZip() .
      "~\n";

    if ($claim->billingFacilityNPI() && $claim->billingFacilityETIN()) {
      ++$edicount;
      $out .= "REF" .
        "*EI" .
        "*" . $claim->billingFacilityETIN() .
        "~\n";
    }
  }

  // Loop 2010AC Pay-To Plan Name omitted.  Includes:
  // NM1*PE, N3, N4, REF*2U, REF*EI

  $PatientHL = $claim->isSelfOfInsured() ? 0 : 1;
  $HLSubscriber = $HLcount++;

  ++$edicount;
  $out .= "HL" .        // Loop 2000B Subscriber HL Loop
    "*$HLSubscriber" .
    "*$HLBillingPayToProvider" .
    "*22" .
    "*$PatientHL" .
    "~\n";

  if (!$claim->payerSequence()) {
    $log .= "*** Error: Insurance information is missing!\n";
  }

  ++$edicount;
  $out .= "SBR" .       // Subscriber Information
    "*" . $claim->payerSequence() .
    "*" . ($claim->isSelfOfInsured() ? '18' : '') .
    "*" . $claim->groupNumber() .
    "*" . (($CMS_5010 && $claim->groupNumber()) ? '' : $claim->groupName()) .
    "*" . $claim->insuredTypeCode() . // applies for secondary medicare
    "*" .
    "*" .
    "*" .
    "*" . $claim->claimType() . // Zirmed replaces this
    "~\n";

  // Segment PAT omitted.

  ++$edicount;
  $out .= "NM1" .       // Loop 2010BA Subscriber
    "*IL" .
    "*1" . // 1 = person, 2 = non-person
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
  if (!$CMS_5010 || $claim->isSelfOfInsured()) {
    ++$edicount;
    $out .= "N3" .
      "*" . $claim->insuredStreet() .
      "~\n";

    ++$edicount;
    $out .= "N4" .
      "*" . $claim->insuredCity() .
      "*" . $claim->insuredState() .
      "*" . $claim->insuredZip() .
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
  //Field length is limited to 35. See nucc dataset page 81 www.nucc.org
  $payerName = substr($claim->payerName(), 0, $CMS_5010 ? 60 : 35);
  $out .= "NM1" .       // Loop 2010BB Payer
    "*PR" .
    "*2" .
    "*" . $payerName .
    "*" .
    "*" .
    "*" .
    "*" .
    // The 5010 spec says:
    // "On or after the mandated implementation date for the HIPAA
    // National Plan Identifier (National Plan ID), XV must be sent.
    // Prior to the mandated implementation date and prior to any phase-
    // in period identified by Federal regulation, PI must be sent."
    // *************** Anybody know what that date is? ***************
    "*PI" .
    // Zirmed ignores this if using payer name matching:
    "*" . ($encounter_claim ? $claim->payerAltID() : $claim->payerID()) .
    "~\n";

  // if (!$claim->payerID()) {
  //   $log .= "*** CMS ID is missing for payer '" . $claim->payerName() . "'.\n";
  // }

  if (!$CMS_5010) {
    // The 5010 spec says:
    // "Required when the payer address is available and the submitter intends
    // for the claim to be printed on paper at the next EDI location (for example, a
    // clearinghouse). If not required by this implementation guide, do not send."

    ++$edicount;
    $out .= "N3" .
      "*" . $claim->payerStreet() .
      "~\n";

    ++$edicount;
    $out .= "N4" .
      "*" . $claim->payerCity() .
      "*" . $claim->payerState() .
      "*" . $claim->payerZip() .
      "~\n";
  }

  // Segment REF (Payer Secondary Identification) omitted.
  // Segment REF (Billing Provider Secondary Identification) omitted.

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
      "*" . $claim->patientLastName() .
      "*" . $claim->patientFirstName() .
      "*" . $claim->patientMiddleName() .
      "~\n";

    ++$edicount;
    $out .= "N3" .
      "*" . $claim->patientStreet() .
      "~\n";

    ++$edicount;
    $out .= "N4" .
      "*" . $claim->patientCity() .
      "*" . $claim->patientState() .
      "*" . $claim->patientZip() .
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
    "*$pid-$encounter" .
    "*"  . sprintf("%.2f",$clm_total_charges) . // Zirmed computes and replaces this
    "*"  .
    "*"  .
    "*"  . sprintf('%02d', $claim->facilityPOS()) . ":" .
           ($CMS_5010 ? "B" : "") . ":" .
           $claim->frequencyTypeCode() . // Changed to correct single digit output
    "*Y" .
    "*A" .
    "*"  . ($claim->billingFacilityAssignment() ? 'Y' : 'N') .
    "*Y" .
    ($CMS_5010 ? "" : "*C") .
    "~\n"; 

  if ($claim->onsetDate()) {
    ++$edicount;
    $out .= "DTP" .       // Date of Onset
      "*431" .
      "*D8" .
      "*" . $claim->onsetDate() .
      "~\n";
  }

  if ($claim->dateInitialTreatment()) {
    ++$edicount;
    $out .= "DTP" .       // Date of Initial Treatment
      "*454" .
      "*D8" .
      "*" . $claim->dateInitialTreatment() .
      "~\n";
  }

  // Segment DTP*304 (Last Seen Date) omitted.
  // Segment DTP*453 (Acute Manifestation Date) omitted.
  // Segment DTP*439 (Accident Date) omitted.
  // Segment DTP*484 (Last Menstrual Period Date) omitted.
  // Segment DTP*455 (Last X-Ray Date) omitted.
  // Segment DTP*471 (Hearing and Vision Prescription Date) omitted.
  // Segments DTP (Disability Dates) omitted.
  // Segment DTP*297 (Last Worked Date) omitted.
  // Segment DTP*296 (Authorized Return to Work Date) omitted.

  if (strcmp($claim->facilityPOS(),'21') == 0) {
    ++$edicount;
    $out .= "DTP" .     // Date of Hospitalization
      "*435" .
      "*D8" .
      "*" . $claim->onsetDate() .
      "~\n";
  }

  // Segment DTP*096 (Discharge Date) omitted.
  // Segments DTP (Assumed and Relinquished Care Dates) omitted.
  // Segment DTP*444 (Property and Casualty Date of First Contact) omitted.
  // Segment DTP*050 (Repricer Received Date) omitted.
  // Segment PWK (Claim Supplemental Information) omitted.
  // Segment CN1 (Contract Information) omitted.

  $patientpaid = $claim->patientPaidAmount();
  if ($patientpaid != 0) {
    ++$edicount;
    $out .= "AMT" .     // Patient paid amount. Page 190/220.
      "*F5" .
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
      "*G1" .
      "*" . $claim->priorAuth() .
      "~\n";
  }

  // Segment REF*F8 (Payer Claim Control Number) omitted.

  if ($claim->cliaCode() && ($CMS_5010 || $claim->claimType() === 'MB')) {
    // Required by Medicare when in-house labs are done.
    ++$edicount;
    $out .= "REF" .     // Clinical Laboratory Improvement Amendment Number
      "*X4" .
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
      "*" . ($CMS_5010 ? "ADD" : "") .
      "*" . $claim->additionalNotes() .
      "~\n";
  }

  // Segment CR1 (Ambulance Transport Information) omitted.
  // Segment CR2 (Spinal Manipulation Service Information) omitted.
  // Segment CRC (Ambulance Certification) omitted.
  // Segment CRC (Patient Condition Information: Vision) omitted.
  // Segment CRC (Homebound Indicator) omitted.
  // Segment CRC (EPSDT Referral) omitted.

  // Diagnoses, up to $max_per_seg per HI segment.
  $max_per_seg = $CMS_5010 ? 12 : 8;
  $da = $claim->diagArray();
  $diag_type_code = 'BK';
  $tmp = 0;
  foreach ($da as $diag) {
    if ($tmp % $max_per_seg == 0) {
      if ($tmp) $out .= "~\n";
      ++$edicount;
      $out .= "HI";         // Health Diagnosis Codes
    }
    $out .= "*$diag_type_code:" . $diag;
    $diag_type_code = 'BF';
    ++$tmp;
  }
  if ($tmp) $out .= "~\n";

  // Segment HI*BP (Anesthesia Related Procedure) omitted.
  // Segment HI*BG (Condition Information) omitted.
  // Segment HCP (Claim Pricing/Repricing Information) omitted.

  if ($claim->referrerLastName()) {
    // Medicare requires referring provider's name and UPIN.
    ++$edicount;
    $out .= "NM1" .     // Loop 2310A Referring Provider
      "*DN" .
      "*1" .
      "*" . $claim->referrerLastName() .
      "*" . $claim->referrerFirstName() .
      "*" . $claim->referrerMiddleName() .
      "*" .
      "*";
    if ($CMS_5010 || $claim->referrerNPI()) { $out .=
      "*XX" .
      "*" . $claim->referrerNPI();
    } else { $out .=
      "*34" .                           // not allowed for 5010
      "*" . $claim->referrerSSN();
    }
    $out .= "~\n";

    if (!$CMS_5010 && $claim->referrerTaxonomy()) {
      ++$edicount;
      $out .= "PRV" .
        "*RF" . // ReFerring provider
        "*ZZ" .
        "*" . $claim->referrerTaxonomy() .
        "~\n";
    }

    if ($claim->referrerUPIN()) {
      ++$edicount;
      $out .= "REF" .   // Referring Provider Secondary Identification
        "*1G" .
        "*" . $claim->referrerUPIN() .
        "~\n";
    }
  }

  ++$edicount;
  $out .= "NM1" .       // Loop 2310B Rendering Provider
    "*82" .
    "*1" .
    "*" . $claim->providerLastName() .
    "*" . $claim->providerFirstName() .
    "*" . $claim->providerMiddleName() .
    "*" .
    "*";
  if ($CMS_5010 || $claim->providerNPI()) { $out .=
    "*XX" .
    "*" . $claim->providerNPI();
  } else { $out .=
    "*34" .                             // not allowed for 5010
    "*" . $claim->providerSSN();
    $log .= "*** Rendering provider has no NPI.\n";
  }
  $out .= "~\n";

  if ($claim->providerTaxonomy()) {
    ++$edicount;
    $out .= "PRV" .
      "*PE" . // PErforming provider
      "*" . ($CMS_5010 ? "PXC" : "ZZ") .
      "*" . $claim->providerTaxonomy() .
      "~\n";
  }

  // REF*1C is required here for the Medicare provider number if NPI was
  // specified in NM109.  Not sure if other payers require anything here.
  // --- apparently ECLAIMS, INC wants the data in 2010 but NOT in 2310B - tony@mi-squared.com
  //
  // Loop 2010AA does not normally provide this for 5010, so assuming the
  // eclaims exception no longer applies here.  -- Rod 2011-10-30
  //
  if ($CMS_5010 || trim($claim->x12gsreceiverid()) != '470819582') { // if NOT ECLAIMS EDI
    if ($claim->providerNumber()) {
      ++$edicount;
      $out .= "REF" .
        "*" . $claim->providerNumberType() .
        "*" . $claim->providerNumber() .
        "~\n";
    }
  }

  // Loop 2310D is omitted in the case of home visits (POS=12).
  if ($claim->facilityPOS() != 12 &&
      (!$CMS_5010 || $claim->facilityNPI() != $claim->billingFacilityNPI()))
    {
    ++$edicount;
    $out .= "NM1" .       // Loop 2310D Service Location
      "*77" .
      "*2";
    //Field length is limited to 35. See nucc dataset page 77 www.nucc.org
    $facilityName = substr($claim->facilityName(), 0, $CMS_5010 ? 60 : 35);
    if ($claim->facilityName() || $claim->facilityNPI() || $claim->facilityETIN()) { $out .=
      "*" . $facilityName;
    }
    if ($claim->facilityNPI() || $claim->facilityETIN()) { $out .=
      "*" .
      "*" .
      "*" .
      "*";
      if ($CMS_5010 || $claim->facilityNPI()) { $out .=
        "*XX*" . $claim->facilityNPI();
      } else { $out .=
        "*24*" . $claim->facilityETIN();
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
        "*" . $claim->facilityZip() .
        "~\n";
    }
  }

  // Segment REF (Service Facility Location Secondary Identification) omitted.
  // Segment PER (Service Facility Contact Information) omitted.

  // Loop 2310E, Supervising Provider
  //
  if ($claim->supervisorLastName()) {
    ++$edicount;
    $out .= "NM1" .
      "*DQ" . // Supervising Physician
      "*1" .  // Person
      "*" . $claim->supervisorLastName() .
      "*" . $claim->supervisorFirstName() .
      "*" . $claim->supervisorMiddleName() .
      "*" .   // NM106 not used
      "*";    // Name Suffix
    if ($CMS_5010 || $claim->supervisorNPI()) { $out .=
      "*XX" .
      "*" . $claim->supervisorNPI();
    } else { $out .=
      "*34" .
      "*" . $claim->supervisorSSN();
    }
    if (!$claim->supervisorNPI()) {
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
  }

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
    if ($tmp1 === 'CI') $tmp2 = 'C1';
    if ($tmp1 === 'AM') $tmp2 = 'AP';
    if ($tmp1 === 'HM') $tmp2 = 'HM';
    if ($tmp1 === 'MB') $tmp2 = 'MB';
    if ($tmp1 === 'MC') $tmp2 = 'MC';
    if ($tmp1 === '09') $tmp2 = 'PP';
    ++$edicount;
    $out .= "SBR" . // Loop 2320, Subscriber Information - page 297/318
      "*" . $claim->payerSequence($ins) .
      "*" . $claim->insuredRelationship($ins) .
      "*" . $claim->groupNumber($ins) .
      "*" . (($CMS_5010 && $claim->groupNumber($ins)) ? '' : $claim->groupName($ins)) .
      "*" . ($CMS_5010 ? $claim->insuredTypeCode($ins) : $tmp2) .
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
          "*" . $a[1] .
          "*" . $a[2] .
          "*" . $a[3] .
          "~\n";
      }

      $payerpaid = $claim->payerTotals($ins);
      ++$edicount;
      $out .= "AMT" . // Previous payer's paid amount. Page 307/332.
        "*D" .
        "*" . $payerpaid[1] .
        "~\n";

      // Segment AMT*A8 (COB Total Non-Covered Amount) omitted.
      // Segment AMT*EAF (Remaining Patient Liability) omitted.

      if (!$CMS_5010) {
        // Patient responsibility amount as of this previous payer.
        $prev_pt_resp -= $payerpaid[1]; // reduce by payments
        $prev_pt_resp -= $payerpaid[2]; // reduce by adjustments

        ++$edicount;
        $out .= "AMT" . // Allowed amount per previous payer. Page 334.
          "*B6" .
          "*" . sprintf('%.2f', $payerpaid[1] + $prev_pt_resp) .
          "~\n";

        ++$edicount;
        $out .= "AMT" . // Patient responsibility amount per previous payer. Page 335.
          "*F2" .
          "*" . sprintf('%.2f', $prev_pt_resp) .
          "~\n";
      }
    } // End of things that apply only to previous payers.

    if (!$CMS_5010) {
      ++$edicount;
      $out .= "DMG" . // Other subscriber demographic information. Page 342.
        "*D8" .
        "*" . $claim->insuredDOB($ins) .
        "*" . $claim->insuredSex($ins) .
        "~\n";
    }

    ++$edicount;
    $out .= "OI" .  // Other Insurance Coverage Information. Page 310/344.
      "*" .
      "*" .
      "*" . ($claim->billingFacilityAssignment($ins) ? 'Y' : 'N') .
      // For this next item, the 5010 example in the spec does not match its
      // description.  So this might be wrong.
      "*" . ($CMS_5010 ? '' : 'B') .
      "*" .
      "*Y" .
      "~\n";

    // Segment MOA (Medicare Outpatient Adjudication) omitted.

    ++$edicount;
    $out .= "NM1" . // Loop 2330A Subscriber info for other insco. Page 315/350.
      "*IL" .
      "*1" .
      "*" . $claim->insuredLastName($ins) .
      "*" . $claim->insuredFirstName($ins) .
      "*" . $claim->insuredMiddleName($ins) .
      "*" .
      "*" .
      "*MI" .
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
      "*" . $claim->insuredZip($ins) .
      "~\n";

    // Segment REF (Other Subscriber Secondary Identification) omitted.

    ++$edicount;
    //Field length is limited to 35. See nucc dataset page 81 www.nucc.org
    $payerName = substr($claim->payerName($ins), 0, $CMS_5010 ? 60 : 35);
    $out .= "NM1" . // Loop 2330B Payer info for other insco. Page 322/359.
      "*PR" .
      "*2" .
      "*" . $payerName .
      "*" .
      "*" .
      "*" .
      "*" .
      "*PI" .
      "*" . $claim->payerID($ins) .
      "~\n";

    // if (!$claim->payerID($ins)) {
    //   $log .= "*** CMS ID is missing for payer '" . $claim->payerName($ins) . "'.\n";
    // }

    // Payer address (N3 and N4) are added below so that Gateway EDI can
    // auto-generate secondary claims.  These do NOT appear in my copy of
    // the spec!  -- Rod 2008-06-12

    if ($CMS_5010 || trim($claim->x12gsreceiverid()) == '431420764') { // if Gateway EDI
      ++$edicount;
      $out .= "N3" .
        "*" . $claim->payerStreet($ins) .
        "~\n";
      //
      ++$edicount;
      $out .= "N4" .
        "*" . $claim->payerCity($ins) .
        "*" . $claim->payerState($ins) .
        "*" . $claim->payerZip($ins) .
        "~\n";
    } // end Gateway EDI

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
      "*HC:" . $claim->cptKey($prockey) .
      "*" . sprintf('%.2f', $claim->cptCharges($prockey)) .
      "*UN" .
      "*" . $claim->cptUnits($prockey) .
      "*" .
      "*" .
      "*";
    $dia = $claim->diagIndexArray($prockey);
    $i = 0;
    foreach ($dia as $dindex) {
      if ($i) $out .= ':';
      $out .= $dindex;
      if (++$i >= 4) break;
    }
    $out .= "~\n";

    if (!$claim->cptCharges($prockey)) {
      $log .= "*** Procedure '" . $claim->cptKey($prockey) . "' has no charges!\n";
    }

    if (empty($dia)) {
      $log .= "*** Procedure '" . $claim->cptKey($prockey) . "' is not justified!\n";
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
    $out .= "DTP" .     // Date of Service. Page 435.
      "*472" .
      "*D8" .
      "*" . $claim->serviceDate() .
      "~\n";

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

    if (!$CMS_5010) {
      // This segment was deleted for 5010.
      //
      // AMT*AAE segment for Approved Amount from previous payer.
      // Medicare secondaries seem to require this.
      //
      for ($ins = $claim->payerCount() - 1; $ins > 0; --$ins) {
        if ($claim->payerSequence($ins) > $claim->payerSequence())
          continue; // payer is future, not previous
        $payerpaid = $claim->payerTotals($ins, $claim->cptKey($prockey));
        ++$edicount;
        $out .= "AMT" . // Approved amount per previous payer. Page 485.
          "*AAE" .
          "*" . sprintf('%.2f', $claim->cptCharges($prockey) - $payerpaid[2]) .
          "~\n";
        break;
      }
    }

    // Loop 2410, Drug Information. Medicaid insurers seem to want this
    // with HCPCS codes.
    //
    $ndc = $claim->cptNDCID($prockey);
    if ($ndc) {
      ++$edicount;
      $out .= "LIN" . // Drug Identification. Page 500+ (Addendum pg 71).
        "*" .         // Per addendum, LIN01 is not used.
        "*N4" .
        "*" . $ndc .
        "~\n";

      if (!preg_match('/^\d\d\d\d\d-\d\d\d\d-\d\d$/', $ndc, $tmp) && !preg_match('/^\d{11}$/', $ndc)) {
        $log .= "*** NDC code '$ndc' has invalid format!\n";
      }

      ++$edicount;
      $tmpunits = $claim->cptNDCQuantity($prockey) * $claim->cptUnits($prockey);
      if (!$tmpunits) $tmpunits = 1;
      $out .= "CTP" . // Drug Pricing. Page 500+ (Addendum pg 74).
        "*" .
        "*" .
        "*" . ($CMS_5010 ? '' : sprintf('%.2f', $claim->cptCharges($prockey) / $tmpunits)) .
        "*" . $claim->cptNDCQuantity($prockey) .
        "*" . $claim->cptNDCUOM($prockey) .
        // Note: 5010 documents "ME" (Milligrams) as an additional unit of measure.
        "~\n";
    }

    // Segment REF (Prescription or Compound Drug Association Number) omitted.

    // Loop 2420A, Rendering Provider (service-specific).
    // Used if the rendering provider for this service line is different
    // from that in loop 2310B.
    //
    if ($claim->providerNPI() != $claim->providerNPI($prockey)) {
      ++$edicount;
      $out .= "NM1" .       // Loop 2310B Rendering Provider
        "*82" .
        "*1" .
        "*" . $claim->providerLastName($prockey) .
        "*" . $claim->providerFirstName($prockey) .
        "*" . $claim->providerMiddleName($prockey) .
        "*" .
        "*";
      if ($CMS_5010 || $claim->providerNPI($prockey)) { $out .=
        "*XX" .
        "*" . $claim->providerNPI($prockey);
      } else { $out .=
        "*34" .                         // Not allowed for 5010
        "*" . $claim->providerSSN($prockey);
      }
      if (!$claim->providerNPI($prockey)) {
        $log .= "*** Rendering provider has no NPI.\n";
      }
      $out .= "~\n";

      if ($claim->providerTaxonomy($prockey)) {
        ++$edicount;
        $out .= "PRV" .
          "*PE" . // PErforming provider
          "*" . ($CMS_5010 ? "PXC" : "ZZ") .
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
      // specified in NM109.  Not sure if other payers require anything here.
      if (!$CMS_5010 && $claim->providerNumber($prockey)) {
        ++$edicount;
        $out .= "REF" .
          "*" . $claim->providerNumberType($prockey) .
          // Note: 5010 documents that type 1D (Medicaid) is changed to G2.
          "*" . $claim->providerNumber($prockey) .
          "~\n";
      }
    }

    // Loop 2430, adjudication by previous payers.
    //
    for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {
      if ($claim->payerSequence($ins) > $claim->payerSequence())
        continue; // payer is future, not previous

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
        "*HC:" . $claim->cptKey($prockey) .
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
        if (!$tmpdate) $tmpdate = $a[0];

        // WTH is this??
        /*************************************************************
        if ( isset($a[4]) &&
        	$a[4] != null ) {
        	$out .= "CAS02" . // Previous payer's adjustment reason
	          "*" . $a[4] .
	          "~\n";	
        }
        *************************************************************/
      }

      if ($tmpdate) {
        ++$edicount;
        $out .= "DTP" . // Previous payer's line adjustment date. Page 493/566.
          "*573" .
          "*D8" .
          "*$tmpdate" .
          "~\n";
      }

      // Segment AMT*EAF (Remaining Patient Liability) omitted.
      // Segment LQ (Form Identification Code) omitted.
      // Segment FRM (Supporting Documentation) omitted.

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

  $log .= "\n";
  return $out;
}
?>
