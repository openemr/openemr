<?php
/*
 * Inc file for the 270 / 271 creation and uploading
 *
 * This program creates the segments for the x12 270 eligibility file
 * It also allows the reading and storing of the x12 271 file
 *
 * Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * Copyright (C) 2010 MMF Systems, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-license.php.
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lilysystems.com>
 * @link http://www.open-emr.org
 */

use OpenEMR\Common\Http\oeHttp;

// @TODO global to become private var when this goes to a class.
//
global $X12info;

// SEGMENT FUNCTION START

// ISA Segment  - EDI-270 format

function create_ISA($row, $X12info, $segTer, $compEleSep)
{

    $ISA     =  array();

    $ISA[0] = "ISA";                            // Interchange Control Header Segment ID

    $ISA[1] = "00";                                 // Author Info Qualifier

    $ISA[2] = str_pad("1234567", 10, " ");        // Author Information

    $ISA[3] = "00";                                 //   Security Information Qualifier
                                                //   MEDI-CAL NOTE: For Leased-Line & Dial-Up use '01',
                                                //   for BATCH use '00'.
                                                //   '00' No Security Information Present
                                                //   (No Meaningful Information in I04)

    $ISA[4] = str_pad(" ", 10, " ");         // Security Information

    $ISA[5] = str_pad($X12info['x12_isa05'], 2, " ");              // Interchange ID Qualifier

    $ISA[6] = str_pad($X12info['x12_sender_id'], 15, " ");      // INTERCHANGE SENDER ID

    $ISA[7] = str_pad($X12info['x12_isa07'], 2, " ");              // Interchange ID Qualifier

    $ISA[8] = str_pad($X12info['x12_receiver_id'], 15, " ");      // INTERCHANGE RECEIVER ID

    $ISA[9] = str_pad(date('ymd'), 6, " ");       // Interchange Date (YYMMDD)

    $ISA[10] = str_pad(date('Hi'), 4, " ");       // Interchange Time (HHMM)

    $ISA[11] = "^";                                 // Interchange Control Standards Identifier

    $ISA[12] = str_pad("00501", 5, " ");          // Interchange Control Version Number

    $ISA[13] = str_pad("000000001", 9, " ");      // INTERCHANGE CONTROL NUMBER

    $ISA[14] = str_pad($X12info['x12_isa14'], 1, " ");              // Acknowledgment Request [0= not requested, 1= requested]

    $ISA[15] =  str_pad($X12info['x12_isa15'], 1, " ");                 // Usage Indicator [ P = Production Data, T = Test Data ]

    $ISA['Created'] = implode('*', $ISA);       // Data Element Separator

    $ISA['Created'] = $ISA['Created'] ."*";

    $ISA['Created'] = $ISA ['Created'] . $compEleSep . $segTer;

    return trim($ISA['Created']);
}

// GS Segment  - EDI-270 format

function create_GS($row, $X12info, $segTer, $compEleSep)
{

    $GS    = array();

    $GS[0] = "GS";                      // Functional Group Header Segment ID

    $GS[1] = "HS";                      // Functional ID Code [ HS = Eligibility, Coverage or Benefit Inquiry (270) ]

    $GS[2] =  $X12info['x12_sender_id'];              // Application Sender's ID

    $GS[3] =  $X12info['x12_receiver_id'];              // Application Receiver's ID

    $GS[4] = date('Ymd');               // Date [CCYYMMDD]

    $GS[5] = date('His');               // Time [HHMM] Group Creation Time

    $GS[6] = "2";                       // Group Control Number No zeros for 5010

    $GS[7] = "X";                   // Responsible Agency Code Accredited Standards Committee X12 ]

    $GS[8] = "005010X279A1";            // Version Release / Industry[ Identifier Code Query 005010X279A1

    $GS['Created'] = implode('*', $GS);         // Data Element Separator

    $GS['Created'] = $GS ['Created'] . $segTer;  // change the information in the tag or change the tag

    return trim($GS['Created']);
}

// ST Segment  - EDI-270 format

function create_ST($row, $X12info, $segTer, $compEleSep)
{

    $ST    =    array();

    $ST[0] = "ST";                              // Transaction Set Header Segment ID

    $ST[1] = "270";                                 // Transaction Set Identifier Code (Inquiry Request)

    $ST[2] = "000000003";                       // Transaction Set Control Number - Must match SE's

    $ST[3] = "005010X279A1";                    // Standard 005010X279A1 in $ST[3]

    $ST['Created'] = implode('*', $ST);             // Data Element Separator

    $ST['Created'] = $ST ['Created'] . $segTer;

    return trim($ST['Created']);
}

// BHT Segment  - EDI-270 format

function create_BHT($row, $X12info, $segTer, $compEleSep)
{

    $BHT    =   array();

    $BHT[0] = "BHT";                        // Beginning of Hierarchical Transaction Segment ID

    $BHT[1] = "0022";                       // Subscriber Structure Code

    $BHT[2] = "13";                         // Purpose Code - This is a Request

    $BHT[3] = "PROVTest600";                //  Submitter Transaction Identifier
                                            //This information is required by the information Receiver
                                            //when using Real Time transactions.
                                            //For BATCH this can be used for optional information.

    $BHT[4] = str_pad(date('Ymd'), 8, " ");           // Date Transaction Set Created

    $BHT[5] = str_pad(date('Hi'), 4, " ");            // Time Transaction Set Created no space after and 1300 is plenty

    $BHT['Created'] = implode('*', $BHT);           // Data Element Separator

    $BHT['Created'] = $BHT ['Created'] . $segTer;

    return trim($BHT['Created']);
}

// HL Segment  - EDI-270 format

function create_HL($row, $nHlCounter, $X12info, $segTer, $compEleSep)
{

    $HL         = array();

    $HL[0]      = "HL";             // Hierarchical Level Segment ID
    $HL_LEN[0]  =  2;

    $HL[1] = $nHlCounter;       // Hierarchical ID No.

    if ($nHlCounter == 1) {
        $HL[2] = "";
        $HL[3] = 20;            // Description: Identifies the payor, maintainer, or source of the information.
        $HL[4] = 1;                 // 1 Additional Subordinate HL Data Segment in This Hierarchical Structure.
    } else if ($nHlCounter == 2) {
        $HL[2] = 1;                 // Hierarchical Parent ID Number
        $HL[3] = 21;            // Hierarchical Level Code. '21' Information Receiver
        $HL[4] = 1;                 // 1 Additional Subordinate HL Data Segment in This Hierarchical Structure.
    } else {
        $HL[2] = 2;
        $HL[3] = 22;            // Hierarchical Level Code.'22' Subscriber
        $HL[4] = 0;                 // 0 no Additional Subordinate in the Hierarchical Structure.
    }

    $HL['Created'] = implode('*', $HL);         // Data Element Separator

    $HL['Created'] = $HL ['Created'] . $segTer;

    return trim($HL['Created']);
}

// NM1 Segment  - EDI-270 format

function create_NM1($row, $nm1Cast, $X12info, $segTer, $compEleSep)
{

    $NM1        = array();

    $NM1[0]         = "NM1";                    // Subscriber Name Segment ID

    if ($nm1Cast == 'PR') {
        $NM1[1] = "PR";                         // Entity ID Code - Payer [PR Payer]
        $NM1[2] = "2";                      // Entity Type - Non-Person
        $NM1[3] = $row["payer_name"];       // Organizational Name
        $NM1[4] = "";                       // Data Element not required.
        $NM1[5] = "";                       // Data Element not required.
        $NM1[6] = "";                       // Data Element not required.
        $NM1[7] = "";                       // Data Element not required.
        $NM1[8] = $X12info['id_number'] ? $X12info['id_number'] : "46";  // 46 - Electronic Transmitter Identification Number (ETIN)
        $NM1[9] = $X12info['x12_receiver_id'];              // Application Sender's ID
    } else if ($nm1Cast == 'FA') {
        $NM1[1] = "FA";                     // Entity ID Code - Facility [FA Facility]
        $NM1[2] = "2";                      // Entity Type - Non-Person
        $NM1[3] = $row['facility_name'];            // Organizational Name
        $NM1[4] = "";           // Data Element not required.
        $NM1[5] = "";           // Data Element not required.
        $NM1[6] = "";                       // Data Element not required.
        $NM1[7] = "";                       // Data Element not required.
        $NM1[8] = "FI";
        $NM1[9] = $row['facility_npi'];
    } else if ($nm1Cast == 'IL') {
        $NM1[1] = "IL";                         // Insured or Subscriber
        $NM1[2] = "1";                      // Entity Type - Person
        $NM1[3] = $row['lname'];                // last Name
        $NM1[4] = $row['fname'];                // first Name
        $NM1[5] = $row['mname'];                // middle Name
        $NM1[6] = "";                       // data element
        $NM1[7] = "";                       // data element
        $NM1[8] = "MI";                         // Identification Code Qualifier
        $NM1[9] = $row['policy_number'];            // Identification Code
    }

    $NM1['Created'] = implode('*', $NM1);               // Data Element Separator

    $NM1['Created'] = $NM1['Created'] . $segTer;

    return trim($NM1['Created']);
}

// REF Segment  - EDI-270 format

function create_REF($row, $ref, $X12info, $segTer, $compEleSep)
{

    $REF    =   array();

    $REF[0] = "REF";                        // Subscriber Additional Identification    does not want this for anything

    if ($ref == '1P') {
        $REF[1] = "4A";                         // Reference Identification Qualifier
        $REF[2] = $row['provider_pin'];                 // Provider Pin.
    } else {
        $REF[1] = "EJ";                         // 'EJ' for Patient Account Number     does not want this for patient
        $REF[2] = $row['pid'];                  // Patient Account No.
    }

    $REF['Created'] = implode('*', $REF);               // Data Element Separator

    $REF['Created'] = $REF['Created'] . $segTer;

    return trim($REF['Created']);
}

// TRN Segment - EDI-270 format

function create_TRN($row, $tracno, $refiden, $X12info, $segTer, $compEleSep)
{

    $TRN    =   array();

    $TRN[0] = "TRN";                        // Subscriber Trace Number Segment ID

    $TRN[1] = "1";                          // Trace Type Code � Current Transaction Trace Numbers

    $TRN[2] = $tracno;                      // Trace Number

    $TRN[3] = "9000000000";                         // Originating Company ID � must be 10 positions in length

    $TRN[4] = $refiden;                         // Additional Entity Identifier (i.e. Subdivision)

    $TRN['Created'] = implode('*', $TRN);               // Data Element Separator

    $TRN['Created'] = $TRN['Created'] . $segTer;

    return trim($TRN['Created']);
}

// DMG Segment - EDI-270 format

function create_DMG($row, $X12info, $segTer, $compEleSep)
{

    $DMG    =   array();

    $DMG[0] = "DMG";                            // Date or Time or Period Segment ID

    $DMG[1] = "D8";                                 // Date Format Qualifier - (D8 means CCYYMMDD)

    $DMG[2] = $row['dob'];                      // Subscriber's Birth date

    $DMG[3] = strtoupper($row['sex'][0]);

    $DMG['Created'] = implode('*', $DMG);       // Data Element Separator

    $DMG['Created'] = $DMG['Created'] .  $segTer;

    return trim($DMG['Created']);
}

// DTP Segment - EDI-270 format

function create_DTP($row, $qual, $X12info, $segTer, $compEleSep)
{

    $DTP    =   array();

    $DTP[0] = "DTP";                        // Date or Time or Period Segment ID

    $DTP[1] = $qual;                        // Qualifier - Date of Service

    $DTP[2] = "D8";                             // Date Format Qualifier - (D8 means CCYYMMDD)

    if ($qual == '102') {
        $DTP[3] = $row['date'];                 // Date
    } else {
        $DTP[3] = $row['pc_eventDate'];         // Date of Service
    }

    $DTP['Created'] = implode('*', $DTP);   // Data Element Separator

    $DTP['Created'] = $DTP['Created'] .  $segTer;

    return trim($DTP['Created']);
}

// EQ Segment - EDI-270 format

function create_EQ($row, $X12info, $segTer, $compEleSep)
{

    $EQ         =   array();

    $EQ[0]  = "EQ";                                     // Subscriber Eligibility or Benefit Inquiry Information

    $EQ[1]  = "30";                                     // Service Type Code

    $EQ['Created'] = implode('*', $EQ);                 // Data Element Separator

    $EQ['Created'] = $EQ['Created'] . $segTer;

    return trim($EQ['Created']);
}

// SE Segment - EDI-270 format

function create_SE($row, $segmentcount, $X12info, $segTer, $compEleSep)
{

    $SE     =   array();

    $SE[0] = "SE";                              // Transaction Set Trailer Segment ID

    $SE[1] = $segmentcount;                         // Segment Count

    $SE[2] = "000000003";                       // Transaction Set Control Number - Must match ST's

    $SE['Created'] = implode('*', $SE);             // Data Element Separator

    $SE['Created'] = $SE['Created'] . $segTer;

    return trim($SE['Created']);
}

// GE Segment - EDI-270 format

function create_GE($row, $X12info, $segTer, $compEleSep)
{

    $GE     =   array();

    $GE[0]  = "GE";                             // Functional Group Trailer Segment ID

    $GE[1]  = "1";                          // Number of included Transaction Sets

    $GE[2]  = "2";                          // Group Control Number

    $GE['Created'] = implode('*', $GE);                 // Data Element Separator

    $GE['Created'] = $GE['Created'] . $segTer;

    return trim($GE['Created']);
}

// IEA Segment - EDI-270 format

function create_IEA($row, $X12info, $segTer, $compEleSep)
{

    $IEA    =   array();

    $IEA[0] = "IEA";                        // Interchange Control Trailer Segment ID

    $IEA[1] = "1";                          // Number of included Functional Groups

    $IEA[2] = "000000001";                      // Interchange Control Number

    $IEA['Created'] = implode('*', $IEA);

    $IEA['Created'] = $IEA['Created'] .  $segTer;

    return trim($IEA['Created']);
}

function translate_relationship($relationship)
{
    switch ($relationship) {
        case "spouse":
            return "01";
            break;
        case "child":
            return "19";
            break;
        case "self":
        default:
            return "S";
    }
}

// EDI-270 Batch file Generation

function print_elig($res, $X12info, $segTer, $compEleSep, $eFlag = false)
{

    $i=1;

    $PATEDI    = "";

    // For Header Segment

    $nHlCounter = 1;
    $rowCount   = 0;
    $trcNo      = 1234501;
    $refiden    = 5432101;

    while ($row = sqlFetchArray($res)) {
        if ($nHlCounter == 1) {
            // create ISA
            $PATEDI    = create_ISA($row, $X12info, $segTer, $compEleSep);

            // create GS
            $PATEDI   .= create_GS($row, $X12info, $segTer, $compEleSep);

            // create ST
            $PATEDI   .= create_ST($row, $X12info, $segTer, $compEleSep);

            // create BHT
            $PATEDI   .= create_BHT($row, $X12info, $segTer, $compEleSep);

            // For Payer Segment

            $PATEDI  .= create_HL($row, 1, $X12info, $segTer, $compEleSep);
            $PATEDI  .= create_NM1($row, 'PR', $X12info, $segTer, $compEleSep);

            // For Provider Segment

            $PATEDI  .= create_HL($row, 2, $X12info, $segTer, $compEleSep);
            $PATEDI  .= create_NM1($row, 'FA', $X12info, $segTer, $compEleSep);

            $nHlCounter = $nHlCounter + 2;
            $segmentcount = 6; // segement counts - start from ST
        }

        // For Subscriber Segment

        $PATEDI  .= create_HL($row, $nHlCounter, $X12info, $segTer, $compEleSep);
        $PATEDI  .= create_NM1($row, 'IL', $X12info, $segTer, $compEleSep);
        $PATEDI  .= create_DMG($row, $X12info, $segTer, $compEleSep);

        $PATEDI  .= create_DTP($row, '291', $X12info, $segTer, $compEleSep);
        $PATEDI  .= create_EQ($row, $X12info, $segTer, $compEleSep);

        $segmentcount   = $segmentcount + 5;
        $nHlCounter     = $nHlCounter + 1;
        $rowCount   = $rowCount + 1;
        $trcNo      = $trcNo + 1;
        $refiden    = $refiden + 1;

        if ($rowCount == sqlNumRows($res)) {
            $segmentcount = $segmentcount + 1;
            $PATEDI   .= create_SE($row, $segmentcount, $X12info, $segTer, $compEleSep);
            $PATEDI   .= create_GE($row, $X12info, $segTer, $compEleSep);
            $PATEDI   .= create_IEA($row, $X12info, $segTer, $compEleSep);
        }
    }
    if (!$eFlag) {
        echo $PATEDI;
    } else {
        return $PATEDI;
    }
}
// EDI-270 Batch file Generation

function requestRealTimeEligibily($res, $X12info, $segTer, $compEleSep, $eFlag = false)
{
    $rowCount = 0;
    $totalCount = sqlNumRows($res);
    $down_accum = $log = '';
    while ($row = sqlFetchArray($res)) {
        $nHlCounter = 1;

        // create ISA
        $PATEDI = create_ISA($row, $X12info, $segTer, $compEleSep);
        // create GS
        $PATEDI .= create_GS($row, $X12info, $segTer, $compEleSep);
        // create ST
        $PATEDI .= create_ST($row, $X12info, $segTer, $compEleSep);
        // create BHT
        $PATEDI .= create_BHT($row, $X12info, $segTer, $compEleSep);
        // For Payer Segment
        $PATEDI .= create_HL($row, 1, $X12info, $segTer, $compEleSep);
        $PATEDI .= create_NM1($row, 'PR', $X12info, $segTer, $compEleSep);
        // For Provider Segment
        $PATEDI .= create_HL($row, 2, $X12info, $segTer, $compEleSep);
        $PATEDI .= create_NM1($row, 'FA', $X12info, $segTer, $compEleSep);

        $nHlCounter = $nHlCounter + 2;
        $segmentcount = 6; // segement counts - start from ST
        // For Subscriber Segment
        $PATEDI .= create_HL($row, $nHlCounter, $X12info, $segTer, $compEleSep);
        $PATEDI .= create_NM1($row, 'IL', $X12info, $segTer, $compEleSep);
        $PATEDI .= create_DMG($row, $X12info, $segTer, $compEleSep);
        $PATEDI .= create_DTP($row, '291', $X12info, $segTer, $compEleSep);
        $PATEDI .= create_EQ($row, $X12info, $segTer, $compEleSep);

        $segmentcount = $segmentcount + 5;
        //$nHlCounter = $nHlCounter + 1;
        $rowCount = $rowCount + 1;
        $segmentcount = $segmentcount + 1;

        $PATEDI .= create_SE($row, $segmentcount, $X12info, $segTer, $compEleSep);
        $PATEDI .= create_GE($row, $X12info, $segTer, $compEleSep);
        $PATEDI .= create_IEA($row, $X12info, $segTer, $compEleSep);

        $result = requestEligibility($X12info['id'], $PATEDI);
        $down_accum .= $result . "\n"; // delimit next new request
        $log .= $PATEDI . "\n";
    }

    $process = parseEdi271($down_accum);
    $log = "270 Requests Sent:\n" . $log . "\n" . $process;
    /*if ($eFlag) {
        $fn = sprintf(
            'elig-271_%s_%s.txt',
            strtolower(str_replace(' ', '', $X12info['name'])),
            date("Y-m-d:H:i:s")
        );
        $log = str_replace('~', "~\r", $log);
        @x12Download($fn, $log);
    }*/

    return $log;
}
// Report Generation

function show_elig($res, $X12info, $segTer, $compEleSep)
{

    $i=0;
    echo "	<div id='report_results'>
			<table>
				<thead>

					<th style='width:12%;'>	". htmlspecialchars(xl('Facility Name'), ENT_NOQUOTES) ."</th>
					<th style='width:9%;' >	". htmlspecialchars(xl('Facility NPI'), ENT_NOQUOTES) ."</th>
					<th style='width:15%;'>	". htmlspecialchars(xl('Insurance Comp'), ENT_NOQUOTES) ."</th>
					<th style='width:8%;' >	". htmlspecialchars(xl('Policy No'), ENT_NOQUOTES) ."</th>
					<th style='width:16%;'>	". htmlspecialchars(xl('Patient Name'), ENT_NOQUOTES) ."</th>
					<th style='width:7%;' >	". htmlspecialchars(xl('DOB'), ENT_NOQUOTES) ."</th>
					<th style='width:6%;' >	". htmlspecialchars(xl('Gender'), ENT_NOQUOTES) ."</th>
					<th style='width:9%;' >	". htmlspecialchars(xl('SSN'), ENT_NOQUOTES) ."</th>
					<th style='width:2%;' >	&nbsp;			  </th>
				</thead>

				<tbody>

		";

    while ($row = sqlFetchArray($res)) {
        $i= $i+1;

        if ($i%2 == 0) {
            $background = '#FFF';
        } else {
            $background = '#FFF';
        }

        $elig     = array();
        $elig[0]  = $row['facility_name'];              // Inquiring Provider Name  calendadr
        $elig[1]  = $row['facility_npi'];               // Inquiring Provider NPI
        $elig[2]  = $row['payer_name'];                     // Payer Name  our insurance co name
        $elig[3]  = $row['policy_number'];              // Subscriber ID
        $elig[4]  = $row['subscriber_lname'];               // Subscriber Last Name
        $elig[5]  = $row['subscriber_fname'];               // Subscriber First Name
        $elig[6]  = $row['subscriber_mname'];               // Subscriber Middle Initial
        $elig[7]  = $row['subscriber_dob'];                 // Subscriber Date of Birth
        $elig[8]  = substr($row['subscriber_sex'], 0, 1);       // Subscriber Sex
        $elig[9]  = $row['subscriber_ss'];              // Subscriber SSN
        $elig[10] = translate_relationship($row['subscriber_relationship']);    // Pt Relationship to insured
        $elig[11] = $row['lname'];                  // Dependent Last Name
        $elig[12] = $row['fname'];                  // Dependent First Name
        $elig[13] = $row['mname'];                  // Dependent Middle Initial
        $elig[14] = $row['dob'];                    // Dependent Date of Birth
        $elig[15] = substr($row['sex'], 0, 1);              // Dependent Sex
        $elig[16] = $row['pc_eventDate'];               // Date of service
        $elig[17] = "30";                       // Service Type
        $elig[18] = $row['pubpid'];                     // Patient Account Number pubpid

        echo "	<tr id='PR".$i."_". htmlspecialchars($row['policy_number'], ENT_QUOTES)."'>
				<td class ='detail' style='width:12%;'>". htmlspecialchars($row['facility_name'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:9%;'>".  htmlspecialchars($row['facility_npi'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:15%;'>". htmlspecialchars($row['payer_name'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:8%;'>".  htmlspecialchars($row['policy_number'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:16%;'>". htmlspecialchars($row['subscriber_lname']." ".$row['subscriber_fname'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:7%;'>".  htmlspecialchars($row['subscriber_dob'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:6%;'>".  htmlspecialchars($row['subscriber_sex'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:9%;'>".  htmlspecialchars($row['subscriber_ss'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:2%;'>
				<img src='../../images/deleteBtn.png' title=' .htmlspecialchars( xl('Delete Row'), ENT_QUOTES) . ' style='cursor:pointer;cursor:hand;' onclick='deletetherow(\"" . $i."_". htmlspecialchars($row['policy_number'], ENT_QUOTES) . "\")'>
				</td>
			</tr>
		";


        unset($elig);
    }

    if ($i==0) {
        echo "	<tr>
				<td class='norecord' colspan=9>
					<div style='padding:5px;font-family:arial;font-size:13px;text-align:center;'>". htmlspecialchars(xl('No records found'), ENT_NOQUOTES) . "</div>
				</td>
			</tr>	";
    }

        echo "	</tbody>
			</table>";
}

// To Show Eligibility Verification data

function show_eligibility_information($pid)
{

    $query = "SELECT eligr.response_description as ResponseMessage,
        DATE_FORMAT(eligv.eligibility_check_date, '%d %M %Y') as VerificationDate,
        eligv.copay, eligv.deductible, eligv.deductiblemet,
        if(eligr.response_status = 'A','Active','Inactive') as Status,
        insd.pid, insc.name
        FROM 		eligibility_verification eligv
        INNER JOIN	eligibility_response eligr on eligr.response_id = eligv.response_id
        INNER JOIN	insurance_data insd on insd.id = eligv.insurance_id
        INNER JOIN 	insurance_companies insc on insc.id = insd.provider
        WHERE 		insd.pid = ?
        AND			eligr.response_status = 'A'
        AND	 		eligv.eligibility_check_date = (SELECT max(eligibility_check_date)
        FROM eligibility_verification
        WHERE	insurance_id = eligv.insurance_id)";
    $result         = sqlStatement($query, array($pid));

    $row = sqlFetchArray($result);

    $showString="";
    $showString .=  "<br><div class='text'>" .
        "<b>" .
        xlt('Insurance Provider') . ":</b> " .
        (!empty($row['name']) ? text($row['name']) : xlt('n/a')) .
        "<br>\n" .
        "<b>" .
        xlt('Status') . ":</b> " .
        (!empty($row['ResponseMessage']) ? text($row['ResponseMessage']) : xlt('n/a')) .
        "<br>\n" .
        "<b>" .
        xlt('Last Verified On') . ":</b> " .
        (!empty($row['VerificationDate']) ? text($row['VerificationDate']) : xlt('n/a')) .
        "<br>" .
        "<b>" . xlt('Copay') . ":</b> " .
        (!empty($row['copay']) ? text($row['copay']) : xlt('n/a')) .
        "<br><b>" . xlt('Deductible') . ":</b> " .
        (!empty($row['deductible']) ? text($row['deductible']) : xlt('n/a')) .
        "<br><b>" . xlt('Deductible Met') . ":</b> " .
        (!empty($row['deductiblemet']) ? ($row['deductiblemet'] == 'Y' ? xlt('Yes') : xlt('No')) : xlt('n/a')) .
        "</div>";

    echo $showString;
}

// For EDI 271


// Function to save the values in eligibility_response table

function eligibility_response_save($segmentVal, $vendorID)
{

    $resCount = 0;

    $query = "SELECT count(*) as RecordsCount FROM eligibility_response WHERE response_description = ? and response_vendor_id = ?";
    $resCount = sqlStatement($query, array($segmentVal, $vendorID));

    if (isset($resCount)) {
        $row = sqlFetchArray($resCount);
        $resCount = $row['RecordsCount'];
    }

    if ($resCount == 0) {
        $query = "INSERT into eligibility_response SET	response_description =?,
            response_vendor_id	 = ?,
            response_create_date = now(),
            response_modify_date = now()";
        $res    = sqlStatement($query, array($segmentVal, $vendorID));
    }
}

// Function to save the values in eligibility_verification table

function eligibility_verification_save($segmentVal, $x12PartnerId, $patientId)
{

    $resCount = 0;

    // For fetching the response Id

    $query = "  SELECT	 response_id
				FROM	 eligibility_response
				WHERE	 response_description = ? and
						 response_vendor_id	  = ?";

    $resId  = sqlStatement($query, array($segmentVal, $x12PartnerId));

    // For fetching the insuarace data Id

    $query = "  SELECT	 id,copay
				FROM	 insurance_data
				WHERE	 type = 'primary' and
						 pid = ?";

    $insId  = sqlStatement($query, array($patientId));

    if (isset($resId)) {
        $row        = sqlFetchArray($resId);

        $responseId     = $row['response_id'];
    }

    if (isset($insId)) {
        $row        = sqlFetchArray($insId);

        $insuranceId    = $row['id'];
        $copay      = $row['copay'];
    }

    if ($resCount == 0) {
        if (isset($insuranceId) && !empty($insuranceId)) {
            //Set up the sql variable binding array (this prevents sql-injection attacks)
            $sqlBindArray = array();
            $query = "INSERT into eligibility_verification SET response_id	= ?, insurance_id	= ?";
            array_push($sqlBindArray, $responseId, $insuranceId);

            if (!empty($copay)) {
                $query .= ",copay = ?";
                array_push($sqlBindArray, $copay);
            }

            $query .= ",eligibility_check_date = now(), create_date = now()";
            $res = sqlStatement($query, $sqlBindArray);
        }
    }
}

// Function to fetch the Patient information - eligibility

function eligibility_information($insuranceId)
{
    $insuranceId = 1;

    $query = " SELECT * FROM eligibility_verification WHERE insuranceid = ?";

    $result = sqlStatement($query, array($insuranceId));
    $row = sqlFetchArray($result);
    return $row;
}

// return array of X12 partners
// if id return just that id
function getX12Partner($id = 0)
{
    // @TODO move to class
    global $X12info;
    $id = (int)$id;
    $returnval = [];

    if ((int)$id > 0) {
        $returnval = sqlQuery("select * from x12_partners WHERE id = ?", array($id));
        $X12info = $returnval;
    } else {
        $rez = sqlStatement("select * from x12_partners");
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            $returnval[$iter] = $row;
        }
    }

    return $returnval;
}

// return array of provider usernames
function getUsernames()
{
    $rez = sqlStatement("select distinct username, lname, fname,id from users " .
        "where authorized = 1 and username != ''");
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    return $returnval;
}

// return formated array

function arrFormated(&$item, $key)
{
    $item = strstr($item, '_');
    $item = substr($item, 1, strlen($item) - 1);
    $item = "'" . $item;
}

function requestEligibility($partner = '', $x12_270 = '')
{
    global $X12info;
    if (((int)$X12info['id'] !== (int)$partner) && (int)$partner > 0) {
        $X12info = getX12Partner($partner);
    }

    $payloadId = "3b8c13f5-11e2-43bf-bc47-737cca04f3fe"; // a default fallback
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        $payloadId = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    $boundary = createUniqueToken(8);
    $rt_passwrd = $X12info['x12_isa04'];
    $rt_user = $X12info['x12_isa02'];
    $nowDate = date("Y-m-d\TH:i:s\Z");
    $headers = array(
        'Content-Type' => "multipart/form-data; boundary=$boundary",
        'Host' => ' wsd.officeally.com'
    );

// IMPORTANT: Do not change the format of $mime_body below.
// HTTP MIME Multipart is non-normative. LF/Return's matter...
//
    $mime_body = <<<STRING
--$boundary
Content-Disposition: form-data; name="ProcessingMode"

RealTime
--$boundary
Content-Disposition: form-data; name="TimeStamp"

$nowDate
--$boundary
Content-Disposition: form-data; name="PayloadID"

$payloadId
--$boundary
Content-Disposition: form-data; name="CORERuleVersion"

2.2.0
--$boundary
Content-Disposition: form-data; name="ReceiverID"

OffAlly
--$boundary
Content-Disposition: form-data; name="SenderID"

155214
--$boundary
Content-Disposition: form-data; name="PayloadType"

X12_270_Request_005010X279A1
--$boundary
Content-Disposition: form-data; name="UserName"

$rt_user
--$boundary
Content-Disposition: form-data; name="Password"

$rt_passwrd
--$boundary
Content-Disposition: form-data; name="Payload"

$x12_270
--$boundary--
STRING;
    // send the request
    $response = oeHttp::bodyFormat('body')
        //->setDebug('5000')/* @todo uncomment and set proxy port to debug eg Fiddler */
        ->usingHeaders($headers)
        ->post('https://wsd.officeally.com/TransactionSite/rtx.aspx', $mime_body); // @TODO put urls in partner's for versatility ie Availability coming

    $formBody = $response->body();
    $contentType = $response->header('Content-Type')[0];
    $hContentLength = (int)$response->header('Content-Length')[0];
    $cksum = ($hContentLength - strlen($formBody)) === 0 ? true : false; // validate content size
    $formData = mimeParse($formBody, $contentType);
    $x12_271 = '';
    if ($formData['ErrorCode'] != "Success") {
        $x12_271 = $formData['ErrorCode'] . "\n" . $formData['ErrorMessage'];
    }
    $x12_271 .= $formData['Payload'];
/*$x12_271 = <<<test
ISA*00* *00* *ZZ*571132733 *ZZ*571132733 *130312*1140*^*00501*499997000*0*T*>~GS*HB*7GWZZL2SCS*7GWZZL2SCS*20130312*114005*6354*X*005010X279A1~ST*271*000000010*005010X279A1~BHT*0022*11*292001*20130312*11395812~HL*1**20*1~NM1*PR*2*TRICARE*****PI*571132733~HL*2*1*21*1~NM1*1P*2*MEMORIAL HOSPITAL*****XX*1044010401~HL*3*2*22*0~TRN*1*201211270000000038U*0571132733~NM1*IL*1*JONES*RAYMOND*S***MI*796347724~DMG*D8*19800925*M~DTP*346*D8*20100422~EB*1**47**PLAN 122 CHCBP~EB*C*IND*30***22*50~MSG*$50 MINIMUM COPAY~EB*C*IND*30***29*50~EB*C*FAM*30***22*100~MSG*$100 MINIMUM COPAY~EB*C*FAM*30***29*100~EB*G*IND*30***22*1000~EB*G*FAM*30***22*1000~EB*B**47***7*17.35*****W~MSG*$25 MINIMUM COPAY~EB*A**47*****0****W~SE*22*000000010~GE*1*6354~IEA*1*499997000~
test;*/

    return $x12_271;
}

function mimeParse($formBody = '', $contentType)
{
    $mimeBody = preg_replace('~\r\n?~', "\r", $formBody);
    list($contentType, $bound, $cs) = explode(";", trim($contentType));
    $bound = explode("=", trim($bound, ' '))[1];
    $mimeFields = preg_split("/-+$bound/", $mimeBody);
    array_pop($mimeFields);
    $hold = $isMatches = [];
    foreach ($mimeFields as $id => $field) {
        if (empty($field)) {
            continue;
        }
        preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $field, $isMatches);
        if (preg_match('/^(.*)\[\]$/i', $isMatches[1], $hold)) {
            $mimeData[$hold[1]][] = $isMatches[2];
        } else {
            $mimeData[$isMatches[1]] = $isMatches[2];
        }
    }
    return $mimeData;
}
// @todo may be issue on linux???
function x12Download($filename = '', $content = '')
{
    while (@ob_end_flush()) {
    }
    header('Content-Type: text/plain');
    header("Content-Length: " . strlen($content));
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    ob_start();
    echo $content;
    @ob_end_flush();
}

function getPatientMatch($fn, $ln, $sex, $dob)
{
    $fn = "%" . text($fn) . "%";
    $ln = "%" . text($ln) . "%";
    $sex = "%" . text($sex) . "%";
    $dob = date("Y-m-d", strtotime($dob));
    $sql = "SELECT pid FROM patient_data WHERE fname LIKE ? && lname LIKE ? && sex LIKE ? && DOB LIKE ?";
    $rtn = sqlQuery($sql, array($fn, $ln, $sex, $dob));

    return $rtn['pid'] ? $rtn['pid'] : 0;
}

function parseEdi271(&$content)
{
    $aaaSegErrors = [
        "42" => "Unable to Respond at Current Time",
        "43" => "Invalid Provider Status",
        "57" => "Invalid/Missing Date(s) of Service",
        "58" => "Invalid/Missing Date-of-Birth",
        "60" => "Date of Birth Follows Date Of Service",
        "63" => "Date of Service in Future",
        "64" => "Invalid/Missing Patient ID",
        "65" => "Invalid/Missing Patient Name",
        "67" => "Patient Not Found",
        "68" => "Duplicate Patient Id Number",
        "71" => "Patient Birth Date Does Not Match That for the Patient on the Database",
        "79" => "270 2100A NM103 or NM109 Source Invalid"];

    $target = $GLOBALS['edi_271_file_path'];
    $message = '';

    $target = $target . time() . basename($_FILES['uploaded']['name']);

    if (empty($content)) {
        $message .= xlt('Sorry, there was a problem uploading your file') . "<br><br>";
    }

    //$parsed = preg_replace('~\r\n?~', "", $content);
    $response = explode("\n", $content);
    $message = xlt('The following EDI file has been uploaded');

// @todo get delim's from ISA
    $patientId = "";
    $log = "";

// Loop through each 271. '\n' delims records in batch.
    foreach ($response as $new) {
        if (empty($new)) {
            continue;
        }
        $in = array();
        $in['pid'] = 0;
        $AAA = array();
        $loop = array();
        $loop['id'] = 0;
        $loop['parent'] = 0;
        $loop['error'] = 1; // if 0 expect an AAA error
        $benefits = array();

        $segments = explode("~", $new);

        if (count($segments) < 6) {
            if (file_exists($target)) {
                unlink($target);
            }
            continue;
        }

        foreach ($segments as $segment) {
            $elements = explode("*", $segment);
            $ecnt = count($elements);
            for ($i = 0; $i < $ecnt; $i++) {
                $elements[$i] = text(trim($elements[$i]));
            }
            // Switch Case for Segment
            switch ($elements[0]) {
                case 'ISA':
                    $loop[1] = $elements[2];
                    $in['isa_sender_id'] = $elements[6];
                    $in['isa_receiver_id'] = $elements[8];
                    $in['isa_control_number'] = $elements[13];
                    break;

                case 'HL':
                    $loop['id'] = (int)$elements[1];
                    $loop['parent'] = (int)$elements[2];
                    $loop['error'] = (int)$elements[4];
                    break;

                case 'NM1':
                    if ($loop['id'] === 1) {//"PR" payer
                        $in['payer_lname'] = $elements[3];
                        $in['payer_fname'] = $elements[4];
                        $in['payer_mname'] = $elements[5];
                        $in['payer_member_id'] = $elements[9];
                    } elseif ($loop['id'] === 2) { //"1P" or "FA"
                        $in['provider_lname'] = $elements[3];
                        $in['provider_fname'] = $elements[4];
                        $in['provider_mname'] = $elements[5];
                        $in['provider_member_id'] = $elements[9];
                    } elseif ($loop['id'] === 3) { //"IL"
                        $in['subscriber_lname'] = $elements[3];
                        $in['subscriber_fname'] = $elements[4];
                        $in['subscriber_mname'] = $elements[5];
                        $in['subscriber_member_id'] = $elements[9];
                    }
                    $loop['context'] = $elements[0];
                    break;

                case 'DMG':
                    if ($elements[1] == "D8") {
                        $in['subscriber_dob'] = $elements[2];
                    }
                    $in['subscriber_sex'] = $elements[3];
                    $loop['context'] = $elements[0];
                    // 2100A-C should be done so get our patient id.
                    $in['pid'] = (int)getPatientMatch(
                        $in['subscriber_fname'],
                        $in['subscriber_lname'],
                        $in['subscriber_sex'],
                        $in['subscriber_dob']
                    );
                    
                    break;

                case 'DTP':
                    // part of 2110C loop ?
                    if ($elements[1] == "291" || $elements[1] == "346") {
                        if ($elements[2] == "D8") {
                            $loop['plan_begin'] = $elements[3];
                            $loop['plan_end'] = '';
                        } elseif ($elements[2] == "RD8") {
                            $tmp = explode('-', $elements[3]);
                            $loop['plan_begin'] = $tmp[0];
                            $loop['plan_end'] = $tmp[1] ? $tmp[1] : '';
                        }
                        $loop['id'] = $loop['id'] + 1;
                        $loop['parent'] = $loop['id'];
                        $loop['error'] = 1;
                        $loop['context'] = $elements[0];
                    }

                    break;

                case 'EB': // lets log important eligibility benefits for later
                    // A = co-ins B = Co-Pay C = Deductibles G = ? 1 = Active F = Limitations
                    $bcode = $elements[1];
                    if ($bcode != "1" && $bcode != "C" && $bcode != "A" &&
                        $bcode != "G" && $bcode != "B") {
                        break;
                    }
                    if ($elements[3] != "30" && $elements[3] != "47") {
                        // @todo config 270 for other service types.
                        // 47 Hospital 30 General health
                        // currently we select 30 service type in 270.
                        break;
                    }
                    $eb = array(
                        'active' => $elements[1],
                        'plan_begin' => $loop['plan_begin'], // from last DTP
                        'plan_end' => $loop['plan_end'],
                        'coverage_level' => $elements[2],
                        'service_type' => $elements[3],
                        'ins_type' => $elements[4],
                        'plan_desc' => $elements[5],
                        'time_qualifier' => $elements[6],
                        'amount' => $elements[7],
                        'percent' => $elements[8],
                        'network_ind' => $elements[12],
                        'message' => '' // any MSG segments that may be assoc with this EB.
                    );
                    $loop['context'] = $elements[0];
                    array_push($benefits, $eb);
                    break;

                case 'AAA': // @todo may need 999/T1A seg. unclear if get 999's you get AAA's too.
                    $error = array(
                        'request_ind' => $elements[1],
                        'reason_code' => $elements[3],
                        'follow_up' => $elements[4],
                        'reason_text' => $elements[3] . " : " . $aaaSegErrors[$elements[3]]
                    );
                    array_push($AAA, $error);
                    break;

                case 'MSG':
                    $bcnt = count($benefits) - 1;
                    if ($bcnt > -1) {
                        $benefits[$bcnt]['message'] = $benefits[$bcnt]['message'] ? $benefits[$bcnt]['message'] . " : " : '';
                        $benefits[$bcnt]['message'] .= $elements[1];
                    }

                    break;

                case 'SE':
                        //response_save($segmentVal, $x12PartnerId);
                        //eligibility_verification_save($segmentVal, $x12PartnerId, $patientId);
                    break;

                case 'IEA':
                    // done
                    break;
            }
        }
        // some debug logging
        $log .= "Patient : " . $in['pid'] . "\n" . "271 X12:\n" . $new . "\n";
        $log .= "2100 Loops" . print_r($in, true) . "\nEB Segments:\n" . print_r($benefits, true) . "\nAAA Segments:\n" . print_r($AAA, true);
    }
    return $log;
}
