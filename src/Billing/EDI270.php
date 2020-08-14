<?php

/**
 * Inc file for the 270 / 271 creation and uploading
 *
 * This program creates the segments for the x12 270 eligibility file
 * It also allows the reading and storing of the x12 271 file
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2010 MMF Systems, Inc
 * @copyright Copyright (c) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

require_once(dirname(__FILE__) . "/../../library/edihistory/codes/edih_271_code_class.php");

use edih_271_codes;
use OpenEMR\Common\Http\oeHttp;
use OpenEMR\Common\Utils\RandomGenUtils;

// @TODO global to become private var when this goes to a class.
//
class EDI270
{
// SEGMENT FUNCTION START
// ISA Segment  - EDI-270 format

    // @sjpadgett's recommendation to try singleton class, see _rest_config.php
    // basically prevents external construction
    private function __construct()
    {
    }

    public static function createISA($row, $X12info, $segTer, $compEleSep)
    {
        $ISA = array();
        $ISA[0] = "ISA"; // Interchange Control Header Segment ID
        $ISA[1] = "00";  // Author Info Qualifier
        $ISA[2] = str_pad("0000000", 10, " ");        // Author Information
        $ISA[3] = "00"; //   Security Information Qualifier
        //   MEDI-CAL NOTE: For Leased-Line & Dial-Up use '01',
        //   for BATCH use '00'.
        //   '00' No Security Information Present
        //   (No Meaningful Information in I04)
        $ISA[4] = str_pad("0000000000", 10, " ");         // Security Information
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
        $ISA[15] = str_pad($X12info['x12_isa15'], 1, " ");                 // Usage Indicator [ P = Production Data, T = Test Data ]
        $ISA['Created'] = implode('*', $ISA);       // Data Element Separator
        $ISA['Created'] = $ISA['Created'] . "*";
        $ISA['Created'] = $ISA ['Created'] . $compEleSep . $segTer;

        return trim($ISA['Created']);
    }

// GS Segment  - EDI-270 format
    public static function createGS($row, $X12info, $segTer, $compEleSep)
    {
        $GS = array();
        $GS[0] = "GS";                      // Functional Group Header Segment ID
        $GS[1] = "HS";                      // Functional ID Code [ HS = Eligibility, Coverage or Benefit Inquiry (270) ]
        $GS[2] = $X12info['x12_sender_id'];              // Application Sender's ID
        $GS[3] = $X12info['x12_receiver_id'];              // Application Receiver's ID
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
    public static function createST($row, $X12info, $segTer, $compEleSep)
    {
        $ST = array();
        $ST[0] = "ST";                              // Transaction Set Header Segment ID
        $ST[1] = "270";                                 // Transaction Set Identifier Code (Inquiry Request)
        $ST[2] = "000000003";                       // Transaction Set Control Number - Must match SE's
        $ST[3] = "005010X279A1";                    // Standard 005010X279A1 in $ST[3]
        $ST['Created'] = implode('*', $ST);             // Data Element Separator
        $ST['Created'] = $ST ['Created'] . $segTer;
        return trim($ST['Created']);
    }

// BHT Segment  - EDI-270 format
    public static function createBHT($row, $X12info, $segTer, $compEleSep)
    {
        $BHT = array();
        $BHT[0] = "BHT";                        // Beginning of Hierarchical Transaction Segment ID
        $BHT[1] = "0022";                       // Subscriber Structure Code
        $BHT[2] = "13";                         // Purpose Code - This is a Request
        $BHT[3] = "PROVTest600";                //  Submitter Transaction Identifier
        // This information is required by the information Receiver when using Real Time transactions.
        // For BATCH this can be used for optional information.
        $BHT[4] = str_pad(date('Ymd'), 8, " ");           // Date Transaction Set Created
        $BHT[5] = str_pad(date('Hi'), 4, " ");            // Time Transaction Set Created no space after and 1300 is plenty
        $BHT['Created'] = implode('*', $BHT);           // Data Element Separator
        $BHT['Created'] = $BHT ['Created'] . $segTer;
        return trim($BHT['Created']);
    }

// HL Segment  - EDI-270 format
    public static function createHL($row, $nHlCounter, $X12info, $segTer, $compEleSep)
    {
        $HL = array();
        $HL[0] = "HL";             // Hierarchical Level Segment ID
        $HL_LEN[0] = 2;
        $HL[1] = $nHlCounter;       // Hierarchical ID No.
        if ($nHlCounter == 1) {
            $HL[2] = "";
            $HL[3] = 20;            // Description: Identifies the payor, maintainer, or source of the information.
            $HL[4] = 1;                 // 1 Additional Subordinate HL Data Segment in This Hierarchical Structure.
        } elseif ($nHlCounter == 2) {
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
    public static function createNM1($row, $nm1Cast, $X12info, $segTer, $compEleSep)
    {
        $NM1 = array();
        $NM1[0] = "NM1";                    // Subscriber Name Segment ID
        if ($nm1Cast == 'PR') {
            $NM1[1] = "PR";                         // Entity ID Code - Payer [PR Payer]
            $NM1[2] = "2";                      // Entity Type - Non-Person
            $NM1[3] = $row["payer_name"];       // Organizational Name
            $NM1[4] = "";                       // Data Element not required.
            $NM1[5] = "";                       // Data Element not required.
            $NM1[6] = "";                       // Data Element not required.
            $NM1[7] = "";                       // Data Element not required.
            $NM1[8] = "PI";                     // 5010 no longer uses "46"
            if ($GLOBALS['enable_oa']) {
                $payerId = $row['eligibility_id'];
            } else {
                $payerId = $row['cms_id'];
            }
            $NM1[9] = $payerId; // Application Sender's ID
        } elseif ($nm1Cast == 'FA') {
            $NM1[1] = "FA";                     // Entity ID Code - Facility [FA Facility]
            $NM1[2] = "2";                      // Entity Type - Non-Person
            $NM1[3] = $row['facility_name'];            // Organizational Name
            $NM1[4] = "";           // Data Element not required.
            $NM1[5] = "";           // Data Element not required.
            $NM1[6] = "";                       // Data Element not required.
            $NM1[7] = "";                       // Data Element not required.
            $NM1[8] = "FI";
            $NM1[9] = $row['facility_npi'];
        } elseif ($nm1Cast == '1P') {
            $NM1[1] = "1P";                     // Entity ID Code - Provider
            $NM1[2] = "2";                      // Entity Type - Non-Person
            $NM1[3] = $row['facility_name'];   // Organizational Name
            $NM1[4] = "";   // Data Element not required.
            $NM1[5] = "";                       // Data Element not required.
            $NM1[6] = "";                       // Data Element not required.
            $NM1[7] = "";                       // Data Element not required.
            $NM1[8] = "XX";
            $NM1[9] = $row['provider_npi'];
        } elseif ($nm1Cast == 'IL') {
            $NM1[1] = "IL";                         // Insured or Subscriber
            $NM1[2] = "1";                      // Entity Type - Person
            $NM1[3] = $row['lname'];                // last Name
            $NM1[4] = $row['fname'];                // first Name
            $NM1[5] = $row['mname'];                // middle Name
            $NM1[6] = "";                       // data element
            $NM1[7] = "";                       // data element
            $NM1[8] = "MI";                     // Identification Code Qualifier
            $NM1[9] = $row['policy_number'];    // Identification Code
        }
        $NM1['Created'] = implode('*', $NM1);               // Data Element Separator
        $NM1['Created'] = $NM1['Created'] . $segTer;
        return trim($NM1['Created']);
    }

// REF Segment  - EDI-270 format
    public static function createREF($row, $ref, $X12info, $segTer, $compEleSep)
    {
        $REF = array();
        $REF[0] = "REF";                            // Subscriber Additional Identification    does not want this for anything
        if ($ref == '1P') {
            $REF[1] = "4A";                         // Reference Identification Qualifier
            $REF[2] = $row['provider_pin'];         // Provider Pin.
        } else {
            $REF[1] = "EJ";                         // 'EJ' for Patient Account Number     does not want this for patient
            $REF[2] = $row['pid'];                  // Patient Account No.
        }
        $REF['Created'] = implode('*', $REF);  // Data Element Separator
        $REF['Created'] = $REF['Created'] . $segTer;
        return trim($REF['Created']);
    }

// TRN Segment - EDI-270 format
    public function createTRN($row, $tracno, $refiden, $X12info, $segTer, $compEleSep)
    {
        $TRN = array();
        $TRN[0] = "TRN";                        // Subscriber Trace Number Segment ID
        $TRN[1] = "1";                          // Trace Type Code � Current Transaction Trace Numbers
        $TRN[2] = $tracno;                      // Trace Number
        $TRN[3] = "9000000000";                 // Originating Company ID � must be 10 positions in length
        $TRN[4] = $refiden;                     // Additional Entity Identifier (i.e. Subdivision)
        $TRN['Created'] = implode('*', $TRN); // Data Element Separator
        $TRN['Created'] = $TRN['Created'] . $segTer;
        return trim($TRN['Created']);
    }

// DMG Segment - EDI-270 format
    public static function createDMG($row, $X12info, $segTer, $compEleSep)
    {
        $DMG = array();
        $DMG[0] = "DMG";                            // Date or Time or Period Segment ID
        $DMG[1] = "D8";                             // Date Format Qualifier - (D8 means CCYYMMDD)
        $DMG[2] = $row['dob'];                      // Subscriber's Birth date
        $DMG[3] = strtoupper($row['sex'][0]);
        $DMG['Created'] = implode('*', $DMG);  // Data Element Separator
        $DMG['Created'] = $DMG['Created'] . $segTer;
        return trim($DMG['Created']);
    }

// DTP Segment - EDI-270 format
    public static function createDTP($row, $qual, $X12info, $segTer, $compEleSep)
    {
        $DTP = array();
        $DTP[0] = "DTP";                            // Date or Time or Period Segment ID
        $DTP[1] = $qual;                            // Qualifier - Date of Service
        $DTP[2] = "D8";                             // Date Format Qualifier - (D8 means CCYYMMDD)
        if ($qual == '102') {
            $DTP[3] = $row['date'];                 // Ins effective Date
        } else {
            switch ($X12info['x12_dtp03']) {
                case 'A':
                    $dtp_date = !empty($row['pc_eventDate']) && $row['pc_eventDate'] > '20010101' ? $row['pc_eventDate'] : date("Ymd");
                    break;
                case 'E':
                    $dtp_date = !empty($row['date']) && $row['date'] > '20010101' ? $row['date'] : date("Ymd");
                    break;
                default:
                    $dtp_date = date("Ymd");
            }
            $DTP[3] = $dtp_date;  // Date of Service
        }
        $DTP['Created'] = implode('*', $DTP);   // Data Element Separator
        $DTP['Created'] = $DTP['Created'] . $segTer;
        return trim($DTP['Created']);
    }

// EQ Segment - EDI-270 format
    public static function createEQ($row, $X12info, $segTer, $compEleSep)
    {
        $EQ = array();
        $EQ[0] = "EQ";                                     // Subscriber Eligibility or Benefit Inquiry Information
        $EQ[1] = "30";                                     // Service Type Code
        $EQ['Created'] = implode('*', $EQ);                 // Data Element Separator
        $EQ['Created'] = $EQ['Created'] . $segTer;
        return trim($EQ['Created']);
    }

// SE Segment - EDI-270 format
    public static function createSE($row, $segmentcount, $X12info, $segTer, $compEleSep)
    {
        $SE = array();
        $SE[0] = "SE";                              // Transaction Set Trailer Segment ID
        $SE[1] = $segmentcount;                     // Segment Count
        $SE[2] = "000000003";                       // Transaction Set Control Number - Must match ST's
        $SE['Created'] = implode('*', $SE);    // Data Element Separator
        $SE['Created'] = $SE['Created'] . $segTer;
        return trim($SE['Created']);
    }

// GE Segment - EDI-270 format
    public static function createGE($row, $X12info, $segTer, $compEleSep)
    {
        $GE = array();
        $GE[0] = "GE";                          // Functional Group Trailer Segment ID
        $GE[1] = "1";                           // Number of included Transaction Sets
        $GE[2] = "2";                           // Group Control Number
        $GE['Created'] = implode('*', $GE); // Data Element Separator
        $GE['Created'] = $GE['Created'] . $segTer;
        return trim($GE['Created']);
    }

// IEA Segment - EDI-270 format
    public static function createIEA($row, $X12info, $segTer, $compEleSep)
    {
        $IEA = array();
        $IEA[0] = "IEA";                        // Interchange Control Trailer Segment ID
        $IEA[1] = "1";                          // Number of included Functional Groups
        $IEA[2] = "000000001";                  // Interchange Control Number
        $IEA['Created'] = implode('*', $IEA);
        $IEA['Created'] = $IEA['Created'] . $segTer;
        return trim($IEA['Created']);
    }

    public static function translateRelationship($relationship)
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
    public static function printElig($res, $X12info, $segTer, $compEleSep)
    {
        $i = 1;
        $PATEDI = "";
        // For Header Segment
        $nHlCounter = 1;
        $rowCount = 0;
        $trcNo = 1234501;
        $refiden = 5432101;
        foreach ($res as $row) {
            if ($nHlCounter == 1) {
                // create ISA
                $PATEDI = self::createISA($row, $X12info, $segTer, $compEleSep);
                // create GS
                $PATEDI .= self::createGS($row, $X12info, $segTer, $compEleSep);
                // create ST
                $PATEDI .= self::createST($row, $X12info, $segTer, $compEleSep);
                // create BHT
                $PATEDI .= self::createBHT($row, $X12info, $segTer, $compEleSep);
                // For Payer Segment
                $PATEDI .= self::createHL($row, 1, $X12info, $segTer, $compEleSep);
                $PATEDI .= self::createNM1($row, 'PR', $X12info, $segTer, $compEleSep);
                // For Provider Segment
                $PATEDI .= self::createHL($row, 2, $X12info, $segTer, $compEleSep);
                $PATEDI .= self::createNM1($row, '1P', $X12info, $segTer, $compEleSep);  // 5010 no longer uses FA
                $nHlCounter = $nHlCounter + 2;
                $segmentcount = 6; // segment counts - start from ST
            }
            // For Subscriber Segment
            $PATEDI .= self::createHL($row, $nHlCounter, $X12info, $segTer, $compEleSep);
            $PATEDI .= self::createNM1($row, 'IL', $X12info, $segTer, $compEleSep);
            // send pid so we get it back in 271
            $PATEDI .= self::createREF($row, 'EJ', '', $segTer, '');
            $PATEDI .= self::createDMG($row, $X12info, $segTer, $compEleSep);
            $PATEDI .= self::createDTP($row, '291', $X12info, $segTer, $compEleSep);
            $PATEDI .= self::createEQ($row, $X12info, $segTer, $compEleSep);
            $segmentcount = $segmentcount + 6;
            $nHlCounter = $nHlCounter + 1;
            $rowCount = $rowCount + 1;
            $trcNo = $trcNo + 1;
            $refiden = $refiden + 1;
            if ($rowCount == count($res)) {
                $segmentcount = $segmentcount + 1;
                $PATEDI .= self::createSE($row, $segmentcount, $X12info, $segTer, $compEleSep);
                $PATEDI .= self::createGE($row, $X12info, $segTer, $compEleSep);
                $PATEDI .= self::createIEA($row, $X12info, $segTer, $compEleSep);
            }
        }
        echo $PATEDI;
    }

    public static function requestEligibleTransaction($pid = 0, $eFlag = false)
    {
        $query = "SELECT
            d.facility_id,
            p.lname,
            p.fname,
            p.mname,
            DATE_FORMAT(p.dob, '%Y%m%d') as dob,
            p.ss,
            p.sex,
            p.pid,
            p.pubpid,
            p.providerID,
            i.subscriber_ss,
            i.policy_number,
            i.provider as payer_id,
            i.subscriber_relationship,
            i.subscriber_lname,
            i.subscriber_fname,
            i.subscriber_mname,
            DATE_FORMAT(i.subscriber_DOB, '%Y%m%d') as subscriber_dob,
            i.policy_number,
            i.subscriber_sex,
            DATE_FORMAT(i.date, '%Y%m%d') as date,
            d.lname as provider_lname,
            d.fname as provider_fname,
            d.npi as provider_npi,
            d.upin as provider_pin,
            f.federal_ein as federal_ein,
            f.facility_npi as facility_npi,
            f.name as facility_name,
            c.cms_id as cms_id,
            c.eligibility_id as eligibility_id,
            c.x12_default_eligibility_id as partner,
            c.name as payer_name
        FROM patient_data AS p
        LEFT JOIN users AS d on (p.providerID = d.id)
        LEFT JOIN facility AS f on (f.id = d.facility_id)
        LEFT JOIN insurance_data AS i ON (i.id =(SELECT id FROM insurance_data AS i WHERE pid = p.pid AND type = 'primary' ORDER BY date DESC LIMIT 1))
        LEFT JOIN insurance_companies as c ON (c.id = i.provider)
        WHERE p.pid = ?";
        $res = sqlStatement($query, array($pid));

        $details = self::requestRealTimeEligible($res, '', "~", ':', true);
        $isError = strpos($details, "Error:");
        $isError = $isError !== false ? $isError : strpos($details, "AAA");
        if ($isError !== false) {
            $details = substr($details, $isError);
            return "<div>" . nl2br(text($details)) . "</div>";
        }

        return true;
    }

// EDI-270 RealTime Request & Response
// RealTime requires one transaction per request.
//
    public static function requestRealTimeEligible($res, $X12info, $segTer, $compEleSep, $eFlag = false)
    {
        $rowCount = 0;
        $totalCount = count($res);
        $down_accum = $log = $error_accum = '';
        foreach ($res as $row) {
            if (!$X12info) {
                $X12info = self::getX12Partner($row['partner']);
            }
            if ($row['providerID'] === 0 || !$row['provider_npi']) {
                $error_accum .= xlt("Error") . ": " . xlt("Provider Missing Add one in Choices") . "\n";
            }
            if (!$row['eligibility_id']) {
                $error_accum .= xlt("Error") . ": " . xlt("Missing Insurance Payer Id") . "\n";
            }
            if (!$row['policy_number'] || !$row['subscriber_dob']) {
                $error_accum .= xlt("Error") . ": " . xlt("Missing Subscriber Policy Number or DOB") . "\n";
            }
            if (!empty($error_accum)) {
                return $error_accum;
            }
            // create ISA
            $PATEDI = self::createISA($row, $X12info, $segTer, $compEleSep);
            // create GS
            $PATEDI .= self::createGS($row, $X12info, $segTer, $compEleSep);
            // create ST
            $PATEDI .= self::createST($row, $X12info, $segTer, $compEleSep);
            // create BHT
            $PATEDI .= self::createBHT($row, $X12info, $segTer, $compEleSep);
            // For Payer Segment
            $PATEDI .= self::createHL($row, 1, $X12info, $segTer, $compEleSep);
            $PATEDI .= self::createNM1($row, 'PR', $X12info, $segTer, $compEleSep);
            // For Provider Segment
            $PATEDI .= self::createHL($row, 2, $X12info, $segTer, $compEleSep);
            // unsure but 'FA' may have to be an option vs '1P'
            $PATEDI .= self::createNM1($row, '1P', $X12info, $segTer, $compEleSep);
            // For Subscriber Segment
            $PATEDI .= self::createHL($row, 3, $X12info, $segTer, $compEleSep);
            $PATEDI .= self::createNM1($row, 'IL', $X12info, $segTer, $compEleSep);
            // send pid so we get it back in 271
            $PATEDI .= self::createREF($row, 'EJ', '', $segTer, '');
            $PATEDI .= self::createDMG($row, $X12info, $segTer, $compEleSep);
            // 2110
            $PATEDI .= self::createDTP($row, '291', $X12info, $segTer, $compEleSep);
            $PATEDI .= self::createEQ($row, $X12info, $segTer, $compEleSep);
            // the end
            $PATEDI .= self::createSE($row, 13, $X12info, $segTer, $compEleSep);
            $PATEDI .= self::createGE($row, $X12info, $segTer, $compEleSep);
            $PATEDI .= self::createIEA($row, $X12info, $segTer, $compEleSep);
            // make request
            $result = self::requestEligibility($X12info['id'], $PATEDI);
            $rowCount++;
            $e = strpos($result, "Error:");
            if ($e !== false) {
                $error_accum = $result;
            } else {
                $down_accum .= $result . "\n"; // delimit for next new request
            }

            $log .= "*** 270 " . xlt("Request Message") . " $rowCount of $totalCount\n" . $PATEDI . "\n" . $error_accum . "\n"; // keep a log.
            $error_accum = '';
        }
        // parse the 271 responses from 270 requests sent.
        $process = self::parseEdi271($down_accum);
        $log = xlt("List of ") . $rowCount . " " . xlt("Requests Sent") . ":\n" . $log . "\n" . $process;
        if ($eFlag) {
            return $log;
        }

        return $rowCount;
    }

// Report Generation

    public static function showElig($res, $X12info, $segTer, $compEleSep)
    {

        $i = 0;
        echo "	<div id='report_results'>
    <table class='table table-striped table-hover'>
        <thead>
            <th>" . text(xl('Facility Name')) . "</th>
            <th>" . text(xl('Facility NPI')) . "</th>
            <th>" . text(xl('Insurance Comp')) . "</th>
            <th>" . text(xl('Appt Date')) . "</th>
            <th>" . text(xl('Policy No')) . "</th>
            <th>" . text(xl('Patient Name')) . "</th>
            <th>" . text(xl('DOB')) . "</th>
            <th>" . text(xl('Gender')) . "</th>
            <th>" . text(xl('SSN')) . "</th>
            <th>	&nbsp;			  </th>
        </thead>
        <tbody>";

        foreach ($res as $row) {
            $i = $i + 1;
            // what the heck is below for... looks abandoned.
            $elig = array();
            $elig[0] = $row['facility_name'];              // Inquiring Provider Name  calendadr
            $elig[1] = $row['facility_npi'];               // Inquiring Provider NPI
            $elig[2] = $row['payer_name'];                     // Payer Name  our insurance co name
            $elig[3] = $row['policy_number'];              // Subscriber ID
            $elig[4] = $row['subscriber_lname'];               // Subscriber Last Name
            $elig[5] = $row['subscriber_fname'];               // Subscriber First Name
            $elig[6] = $row['subscriber_mname'];               // Subscriber Middle Initial
            $elig[7] = $row['subscriber_dob'];                 // Subscriber Date of Birth
            $elig[8] = substr($row['subscriber_sex'], 0, 1);       // Subscriber Sex
            $elig[9] = $row['subscriber_ss'];              // Subscriber SSN
            $elig[10] = self::translateRelationship($row['subscriber_relationship']);    // Pt Relationship to insured
            $elig[11] = $row['lname'];                  // Dependent Last Name
            $elig[12] = $row['fname'];                  // Dependent First Name
            $elig[13] = $row['mname'];                  // Dependent Middle Initial
            $elig[14] = $row['dob'];                    // Dependent Date of Birth
            $elig[15] = substr($row['sex'], 0, 1);              // Dependent Sex
            $elig[16] = $row['pc_eventDate'];               // Date of service
            $elig[17] = "30";                       // Service Type
            $elig[18] = $row['pubpid'];                     // Patient Account Number pubpid

            echo "	<tr id='PR" . $i . "_" . text($row['policy_number']) . "'>
				<td class ='detail'>" . text($row['facility_name']) . "</td>
				<td class ='detail'>" . text($row['facility_npi']) . "</td>
				<td class ='detail'>" . text($row['payer_name']) . "</td>
				<td class ='detail'>" . text(date("m/d/Y", strtotime($row['pc_eventDate']))) . "</td>
				<td class ='detail'>" . text($row['policy_number']) . "</td>
				<td class ='detail'>" . text($row['subscriber_lname'] . " " . $row['subscriber_fname']) . "</td>
				<td class ='detail'>" . text($row['subscriber_dob']) . "</td>
				<td class ='detail'>" . text($row['subscriber_sex']) . "</td>
				<td class ='detail'>" . text($row['subscriber_ss']) . "</td>
				<td class ='detail'>
				<img src=\"" . $GLOBALS['images_static_relative'] . "/deleteBtn.png\" title=" . text(xl('Delete Row')) . " style='cursor:pointer;cursor:hand;' onclick='deletetherow(\"" . $i . "_" . text($row['policy_number']) . "\")'>
				</td>
			</tr>
		";
            unset($elig); // see ..
        }

        if ($i == 0) {
            echo "	<tr>
				<td class='norecord' colspan=9>
					<div style='padding:5px;font-family:arial;font-size:13px;text-align:center;'>" . text(xl('No records found')) . "</div>
				</td>
			</tr>	";
        }
        echo "	</tbody>
			</table>";
    }

// To Show Eligibility Verification data
    public static function showEligibilityInformation($pid, $flag = false)
    {
        $query =
            "SELECT eligr.*, eligv.insurance_id, eligv.copay, insd.pid, insc.name, " .
            "Date_Format(eligv.eligibility_check_date, '%W %M %e, %Y %h:%i %p') AS verificationDate " .
            "FROM eligibility_verification eligv " .
            "INNER JOIN benefit_eligibility eligr ON eligr.verification_id = eligv.verification_id " .
            "INNER JOIN insurance_data insd ON insd.id = eligv.insurance_id " .
            "INNER JOIN insurance_companies insc ON insc.id = insd.provider " .
            "WHERE insd.pid = ? AND eligv.eligibility_check_date = " .
            "(SELECT Max(eligibility_verification.eligibility_check_date) FROM eligibility_verification " .
            "WHERE eligibility_verification.insurance_id = eligv.insurance_id)";
        $result = sqlStatement($query, array($pid));

        $showString = "<div class='row'>";
        $col = 1;
        $title = 1;
        while ($benefit = sqlFetchArray($result)) {
            if ($title) {
                $title = 0;
                $showString .= "<div class='col col-sm-12'>\n";
                $showString .= "<b>" . xlt('Insurance Provider') . ":</b> " . (!empty($benefit['name']) ? text($benefit['name']) : xlt('n/a')) . "<br/>\n";
                $showString .= "<b>" . xlt('Verified On') . ":</b> " . text($benefit['verificationDate']) . "<br/><br/>\n";
                $showString .= "</div><br/>\n";
            }
            $benefit['start_date'] = strpos($benefit['start_date'], "0000") === false ? $benefit['start_date'] : '';
            $benefit['end_date'] = strpos($benefit['end_date'], "0000") === false ? $benefit['end_date'] : '';
            $color = "";
            switch ($benefit['type']) {
                case '1':
                    $color = "darkred";
                    break;
                case 'A':
                    $color = "blue";
                    break;
                case 'B':
                    $color = "red";
                    break;
                case 'C':
                    $color = "green";
                    break;
                case 'F':
                    $color = "darkgreen";
                    break;
            }
            $showString .= "\n<div class='col col-sm-6' >\n";
            $showString .= !empty($benefit['benefit_type']) ? "<b style='color: $color'>" . xlt('Benefit Type') . ": " . text($benefit['benefit_type']) . "</b><br />\n" : '';
            $showString .= !empty($benefit['start_date']) ? "<b>" . xlt('Start Date') . ":</b> " . text(date("m/d/Y", strtotime($benefit['start_date']))) . "<br />\n" : '';
            $showString .= !empty($benefit['end_date']) ? "<b>" . xlt('End Date') . ":</b> " . text(date("m/d/Y", strtotime($benefit['end_date']))) . "<br />\n" : '';
            $showString .= !empty($benefit['coverage_level']) ? "<b>" . xlt('Coverage Level') . ":</b> " . text($benefit['coverage_level']) . "<br />\n" : '';
            $showString .= !empty($benefit['coverage_type']) ? "<b>" . xlt('Coverage Type') . ":</b> " . text($benefit['coverage_type']) . "<br />\n" : '';
            $showString .= !empty($benefit['plan_type']) ? "<b>" . xlt('Plan Type') . ":</b> " . text($benefit['plan_type']) . "<br />\n" : '';
            $showString .= !empty($benefit['plan_desc']) ? "<b>" . xlt('Plan Description') . ":</b> " . text(text($benefit['plan_desc'])) . "<br />\n" : '';
            $showString .= !empty($benefit['coverage_period']) ? "<b>" . xlt('Coverage Period') . ":</b> " . text($benefit['coverage_period']) . "<br />\n" : '';
            $showString .= !empty($benefit['amount']) ? "<b>" . xlt('Amount') . ":</b> " . text($benefit['amount']) . "<br />\n" : '';
            $showString .= !empty($benefit['percent']) ? "<b>" . xlt('Percentage') . ":</b> " . text($benefit['percent']) . "<br />\n" : '';
            $showString .= !empty($benefit['network_ind']) ? "<b>" . xlt('Network Indicator') . ":</b> " . text($benefit['network_ind']) . "<br />\n" : '';
            $showString .= !empty($benefit['message']) ? "<b>" . xlt('Message') . ":</b> " . text($benefit['message']) . "<br />\n" : '';
            $showString .= "</div>";

            if ($col === 2) {
                $showString .= "</div>\n<br/><div class='row'>\n";
                $col = 0;
            }
            $col++;
        }
        if ($col === 2) {
            $showString .= "</div>";
        }
        if ($title === 1) {
            $showString = "<br /><span><b>" . xlt("Nothing To Report") . "</b></span><br />";
        }
        $showString .= "</div>\n";
        echo $showString;
    }

// For EDI 271
// Function to save the values in eligibility_verification table
    public static function eligibilityVerificationSave($subscriber = [])
    {
        $verification_id = 0;
        $insurance_id = 0;
        $partner_id = $subscriber['isa_sender_id'];
        $patient_id = $subscriber['pid'];

        $query = "SELECT id, copay FROM insurance_data WHERE type = 'primary' and pid = ?";
        $insId = sqlQuery($query, array($patient_id));
        if ($insId !== false) {
            $insurance_id = $insId['id'];
            $copay = $insId['copay'];
        }

        $query = "SELECT verification_id FROM eligibility_verification WHERE insurance_id = ?";
        $resId = sqlQuery($query, array($insurance_id));
        if ($resId !== false) {
            $verification_id = $resId['verification_id'];
        }

        if (!empty($insurance_id)) {
            $sqlBindArray = array();
            $query = "REPLACE INTO eligibility_verification SET verification_id = ?, insurance_id = ?, response_id = ?, eligibility_check_date = now(), create_date = now()";
            array_push($sqlBindArray, $verification_id, $insurance_id, $partner_id);

            $res = sqlInsert($query, $sqlBindArray);
            if (!$verification_id) {
                $verification_id = $res;
            }
        }

        $query = "DELETE FROM `benefit_eligibility` WHERE `benefit_eligibility`.`verification_id` = ?";
        $res = sqlStatement($query, array($verification_id));

        foreach ($subscriber['benefits'] as $benefit) {
            $bind = array(
                $verification_id,
                "A",
                date('Y/m/d H:i'),
                date('Y/m/d H:i'),
                $benefit['type'],
                $benefit['benefit_type'],
                $benefit['start_date'],
                $benefit['end_date'],
                $benefit['coverage_level'],
                $benefit['coverage_type'],
                $benefit['plan_type'],
                $benefit['plan_desc'],
                $benefit['coverage_period'],
                $benefit['amount'],
                $benefit['percent'],
                $benefit['network_ind'],
                $benefit['message']
            );

            $query = "INSERT INTO `benefit_eligibility` " .
                "(`verification_id`, `response_status`, `response_create_date`, `response_modify_date`, `type`, `benefit_type`, `start_date`, " .
                "`end_date`, `coverage_level`, `coverage_type`, `plan_type`, `plan_description`, `coverage_period`, `amount`, `percent`, `network_ind`, `message`) " .
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $res = sqlStatement($query, $bind);
        }
    }

// return array of X12 partners
// if id return just that id
    public static function getX12Partner($id = 0)
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
    public static function getUsernames()
    {
        $rez = sqlStatement("select distinct username, lname, fname,id from users " .
            "where authorized = 1 and username != ''");
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            $returnval[$iter] = $row;
        }

        return $returnval;
    }

// return formated array

    public static function arrFormated(&$item, $key)
    {
        $item = strstr($item, '_');
        $item = substr($item, 1, strlen($item) - 1);
        $item = "'" . $item;
    }

    public static function requestEligibility($partner = '', $x12_270 = '')
    {
        global $X12info;
        if (((int)$X12info['id'] !== (int)$partner) && (int)$partner > 0) {
            $X12info = self::getX12Partner($partner);
        }

        $payloadId = "3b8c13f5-11e2-43bf-bc47-737cca04f3fe"; // a default fallback
        if (function_exists('openssl_random_pseudo_bytes') === true) {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            $payloadId = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        $boundary = RandomGenUtils::createUniqueToken(12);
        $rt_passwrd = $X12info['x12_isa04'];
        $rt_user = $X12info['x12_isa02'];
        $sender_id = $X12info['x12_sender_id'];
        $receiver_id = $X12info['x12_receiver_id'];
        $now_date = date("Y-m-d\TH:i:s\Z");
        $headers = array(
            'Content-Type' => "multipart/form-data; boundary=$boundary",
            'Host' => ' wsd.officeally.com'
        );

// IMPORTANT: Do not change the format of $mime_body below.
// HTTP MIME Multipart is non-normative. LFs matter...
//
        $mime_body = <<<MIMEBODY
--$boundary
Content-Disposition: form-data; name="ProcessingMode"

RealTime
--$boundary
Content-Disposition: form-data; name="TimeStamp"

$now_date
--$boundary
Content-Disposition: form-data; name="PayloadID"

$payloadId
--$boundary
Content-Disposition: form-data; name="CORERuleVersion"

2.2.0
--$boundary
Content-Disposition: form-data; name="ReceiverID"

$receiver_id
--$boundary
Content-Disposition: form-data; name="SenderID"

$sender_id
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
MIMEBODY;
        // send the request
        $response = oeHttp::bodyFormat('body')
            //->setDebug('5000')/* @todo uncomment and set proxy port to debug eg Fiddler */
            ->usingHeaders($headers)
            ->post('https://wsd.officeally.com/TransactionSite/rtx.aspx', $mime_body); // @TODO put request urls in x12 partner's for versatility.

        $formBody = $response->body();
        $contentType = $response->header('Content-Type')[0];
        $hContentLength = (int)$response->header('Content-Length')[0];
        $cksum = ($hContentLength - strlen($formBody)) === 0 ? true : false; // validate content size
        $formData = self::mimeParse($formBody, $contentType);

        $errors = '';
        if (!$cksum) {
            $errors .= "Error:" . xlt("Request Content Fails Integrity Test");
        }
        if ($response->status() !== 200) {
            $errors .= "\nError:" . xlt("Http Error") . ": " . $response->getReasonPhrase() . " : " . $response->status();
        }
        if ($formData['ErrorCode'] != "Success") {
            $errors .= "\nError:" . $formData['ErrorCode'] . "\n" . $formData['ErrorMessage'];
        }
        if ($errors) {
            $errors .= $formData['Payload'] ? "\nError:" . $formData['Payload'] : '';
            return $errors;
        }

        $x12_271 = $formData['Payload'];

        return $x12_271;
    }

    public static function mimeParse(string $formBody = null, $contentType)
    {
        $mimeBody = preg_replace('~\r\n?~', "\r", $formBody);
        list($contentType, $bound, $cs) = explode(";", trim($contentType)); // $contentType & $cs are throwaways
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

    public static function getPatientMatch($fn, $ln, $sex, $dob)
    {
        $fn = "%" . $fn . "%";
        $ln = "%" . $ln . "%";
        $sex = "%" . $sex . "%";
        $dob = date("Y-m-d", strtotime($dob));
        $sql = "SELECT pid FROM patient_data WHERE fname LIKE ? && lname LIKE ? && sex LIKE ? && DOB LIKE ?";
        $rtn = sqlQuery($sql, array($fn, $ln, $sex, $dob));

        return $rtn['pid'] ? $rtn['pid'] : 0;
    }

    public static function parseEdi271($content)
    {

        $codes = new edih_271_codes('*', '^');
        $target = $GLOBALS['edi_271_file_path'];
        $log = "";
        // not sure if want to save yet
        $target = $target . time();

        $responses = explode("\n", $content);
        if (empty($responses)) {
            $responses = $content;
        }

// Loop through each 271. '\n' delims records in batch.
        foreach ($responses as $new) {
            if (empty($new)) {
                continue;
            }
            $AAA = array();
            $subscribers = array();
            $benefits = array();
            $in = array();
            $in['pid'] = 0;
            $loop = array();
            $loop['id'] = 0;
            $loop['parent'] = 0;
            $loop['error'] = 1;
            $trace = -1;

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
                // sanitize
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
                            $in['payer_org'] = $elements[3];
                            $in['payer_member_id'] = $elements[9];
                        } elseif ($loop['id'] === 2) { //"1P" or "FA"
                            $in['provider_org'] = $elements[3];
                            $in['provider_member_id'] = $elements[9];
                        } elseif ($elements[1] == "IL" || $loop['context'] == "TRN") { //"IL"
                            $in['trace'] = $trace;
                            $in['subscriber_lname'] = $elements[3];
                            $in['subscriber_fname'] = $elements[4];
                            $in['subscriber_mname'] = $elements[5];
                            $in['subscriber_member_id'] = $elements[9];
                            $in['verify_date'] = date('Y/m/d H:i:s');
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
                        if (!(int)$in['pid']) {
                            $in['pid'] = (int)self::getPatientMatch(
                                $in['subscriber_fname'],
                                $in['subscriber_lname'],
                                $in['subscriber_sex'],
                                $in['subscriber_dob']
                            );
                        }
                        break;

                    case 'TRN':
                        if ($trace === -1) {
                            $trace++;
                            break; // subscriber not set yet
                        }
                        $trace++;
                        if ($in['pid']) {
                            $in['benefits'] = $benefits ? $benefits : [];
                            array_push($subscribers, $in);
                            $loop['context'] = $elements[0];
                            $benefits = [];
                        }
                        break;

                    case 'REF':
                        if ($elements[1] == "EJ") {
                            $in['pid'] = (int)$elements[2];
                            if (!$in['pid']) {
                                $in['pid'] = (int)self::getPatientMatch(
                                    $in['subscriber_fname'],
                                    $in['subscriber_lname'],
                                    $in['subscriber_sex'],
                                    $in['subscriber_dob']
                                );
                            }
                        }
                        break;

                    case 'DTP':
                        if ($elements[2] == "D8") {
                            $loop['start_date'] = $elements[3] ? date("Y-m-d", strtotime($elements[3])) : '';
                            $loop['end_date'] = '';
                        } elseif ($elements[2] == "RD8") {
                            $tmp = explode('-', $elements[3]);
                            $loop['start_date'] = $tmp[0] ? date("Y-m-d", strtotime($tmp[0])) : '';
                            $loop['end_date'] = !empty($tmp[1]) ? date("Y-m-d", strtotime($tmp[1])) : '';
                        }
                        if ($loop['context'] == "EB") {
                            $bcnt = count($benefits) - 1;
                            $benefits[$bcnt]['start_date'] = $loop['start_date'];
                            $benefits[$bcnt]['end_date'] = $loop['end_date'];
                        }
                        break;

                    case 'EB':
                        $eb = array(
                            'type' => $elements[1],
                            'benefit_type' => $codes->get_271_code("EB01", $elements[1]) ? $codes->get_271_code("EB01", $elements[1]) : $elements[1],
                            'start_date' => '',
                            'end_date' => '',
                            'coverage_level' => $elements[2] ? $codes->get_271_code("EB02", $elements[2]) : $elements[2],
                            'coverage_type' => $elements[3] ? $codes->get_271_code("EB03", $elements[3]) : $elements[3],
                            'plan_type' => $elements[4] ? $codes->get_271_code("EB04", $elements[4]) : $elements[4],
                            'plan_description' => $elements[5],
                            'coverage_period' => $elements[6] ? $codes->get_271_code("EB06", $elements[6]) : $elements[6],
                            'amount' => $elements[7] ? number_format($elements[7], 2, '.', '') : '',
                            'percent' => $elements[8],
                            'network_ind' => $elements[12] ? $codes->get_271_code("EB12", $elements[12]) : $elements[12],
                            'message' => '' // any MSG segments that may be assoc with this EB.
                        );
                        $loop['context'] = "EB";
                        array_push($benefits, $eb);
                        break;

                    case 'AAA':
                        $error = array(
                            'request_ind' => $elements[1],
                            'reason_code' => $elements[3] . " : " . $codes->get_271_code("AAA03", $elements[3]),
                            'follow_up' => $elements[4] . " : " . $codes->get_271_code("AAA04", $elements[4])
                        );
                        array_push($AAA, $error);
                        break;

                    case 'MSG':
                        $bcnt = count($benefits) - 1;
                        if ($bcnt > -1) {
                            $benefits[$bcnt]['message'] = $benefits[$bcnt]['message'] ? $benefits[$bcnt]['message'] . ":" : '';
                            $benefits[$bcnt]['message'] .= $elements[1];
                        }
                        break;

                    case 'SE':
                        $in['benefits'] = $benefits ? $benefits : [];
                        if ($in['pid']) {
                            array_push($subscribers, $in);
                        }
                        $loop['context'] = $elements[0];
                        $benefits = [];
                        break;

                    case 'IEA':
                        // save
                        $elog = '';
                        if (count($subscribers) < 1) {
                            $elog = xlt("Error") . ": " . xlt("Unknown Transaction Error Maybe Subscriber Effective or DOB Dates");
                        }
                        foreach ($subscribers as $subscriber) {
                            self::eligibilityVerificationSave($subscriber);
                        }
                        break;
                }
            }
            // some debug logging
            if (!$GLOBALS['disable_eligibility_log']) {
                $log .= "*------------------- " . xlt("271 Returned") . " --------------------*\n" . $new . "\n" . (isset($AAA[0]) ? (xlt("AAA Segments") . ":\n" . print_r($AAA, true)) : "\n") . $elog;
                $log .= self::makeEligibilityReport($subscribers);
            }
        }


        return $log;
    }

    public static function makeEligibilityReport($subscribers = [])
    {
        $binfo = '';
        foreach ($subscribers as $subscriber) {
            $binfo .=
                xlt("Subscriber Member") . ": " . $subscriber['subscriber_fname'] . " " . $subscriber['subscriber_lname'] . " " . $subscriber['subscriber_mname'] .
                " " . xlt("Member Id") . ": " . $subscriber['subscriber_member_id'] . " ---*\n";
            $cnt = count($subscriber['benefits']);
            if ($cnt < 1) {
                $binfo .= "*** " . xlt("Nothing returned to report") . " ***\n";
            }
            foreach ($subscriber['benefits'] as $key => $benefits) {
                foreach ($benefits as $key => $benefit) {
                    if ($key == 'type') {
                        $binfo .= "\n";
                    }
                    if (empty($benefit) || $key == 'type') {
                        continue;
                    }
                    $binfo .= "\t" . ucwords(str_replace('_', ' ', $key)) . ": " . $benefit . "\n";
                }
            }
        }

        return $binfo;
    }
}
