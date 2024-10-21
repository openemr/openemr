<?php

/**
 *
 * @package   OpenEMR
 * @link      http: // www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https: // github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Dorn;

use OpenEMR\Common\Logging\EventAuditLogger;

class DornGenHl7Order extends GenHl7OrderBase
{
    public function __construct()
    {
    }

    public static function isDornLab($ppid)
    {
        $sql = "SHOW TABLES LIKE 'mod_dorn_routes'";
        $result = sqlQuery($sql);
        if ($result === false) {
            return false;
        }

        $sql = "SELECT 1 FROM mod_dorn_routes WHERE ppid = ?";
        $dornRecord = sqlQuery($sql, [$ppid]);
        if ($dornRecord !== false) {
            return true;
        }
        return false;
    }

    /**
     * Generate HL7 for the specified procedure order.
     *
     * @param integer  $orderid Procedure order ID.
     * @param string  &$out     Container for target HL7 text.
     * @return string            Error text, or empty if no errors.
     */
    public function genHl7Order($orderid, &$out)
    {
        // Delimiters
        $d0 = "\r";
        $d1 = '|';
        $d2 = '^';
        $today = time();
        $out = '';
        $porow = ProcedureSqlStatements::getProcedureOrder($orderid);
        if (empty($porow)) {
            return "Procedure order, ordering provider or lab is missing for order ID '$orderid'";
        }

        $pcres = ProcedureSqlStatements::getProcedureCode($orderid);
        $pdres = ProcedureSqlStatements::getProcedureCode($orderid);
        // why was this the exact same query? not sure but it was.
        $vitals = ProcedureSqlStatements::getVitals($porow['pid'], $porow['encounter']);
        $bill_type = strtoupper(substr($porow['billing_type'], 0, 1));
        $out .= $this->createMsh($porow['send_app_id'], $porow['send_fac_id'], $porow['recv_app_id'], $porow['recv_fac_id'], date('YmdHisO', $today), "", $orderid, "T", "", "", "AL", "NE", "", "", "", "");
        $out .= $this->createPid("1", "", $porow['pid'], "", $porow['fname'], $porow['lname'], $porow['mname'], "", $porow['DOB'], $porow['sex'], "", $porow['race'], $porow['street'], "", $porow['city'], $porow['state'], $porow['postal_code'], "", $porow['phone_home'], "", "", "", "", "", "", "", "", "");
        $out .= $this->createPv1("U", $bill_type);
        // Insurance stuff.
        $payers = $this->loadPayerInfo($porow['pid'], $porow['date_ordered']);
        $setid = 0;
        if ($bill_type == 'T') {
            // only send primary and secondary insurance
            foreach ($payers as $payer) {
                $payer_object = $payer['object'];
                $payer_address = $payer_object->get_address();
                $full_address = $payer_address->get_line1();
                $payer_address1 = $payer_address->get_line1();
                $payer_address2 = $payer_address->get_line2();
                $payer_addressCity = $payer_address->get_city();
                $payer_addressState = $payer_address->get_state();
                $payer_addressZip = $payer_address->get_zip();
                $payer_addressPhone = $payer_object->get_phone();
                if (!empty($payer_address->get_line2())) {
                    $full_address .= "," . $payer_address->get_line2();
                }
                $setid = $setid + 1;
                $out .= $this->createIn1(
                    $setid,
                    $payer['company']['cms_id'],  // this is a guess
                    $payer['company']['cms_id'],
                    $payer['company']['name'],
                    $payer_address1,
                    $payer_address2,
                    $payer_addressCity,
                    $payer_addressState,
                    $payer_addressZip,
                    $payer_addressPhone,
                    $payer['data']['group_number'],
                    "",
                    "",
                    $payer['data']['subscriber_fname'],
                    $payer['data']['subscriber_lname'],
                    $payer['data']['subscriber_mname'],
                    $payer['data']['subscriber_relationship'],
                    $this->formatDate($payer['data']['subscriber_DOB']),
                    $payer['data']['subscriber_street'],
                    "",
                    $payer['data']['subscriber_city'],
                    $payer['data']['subscriber_state'],
                    $payer['data']['subscriber_postal_code'],
                    $payer['data']['policy_number']
                );
            }
            if ($setid === 0) {
                return "\nInsurance is being billed but patient does not have any payers on record!";
            }
        }

        if ($bill_type == "T") {
            $guarantors = $this->loadGuarantorInfo($porow['pid'], $porow['date_ordered']);
            // this is returning an array but in the query we have a limit 1!
            foreach ($guarantors as $guarantor) {
                $out .= $this->createGt1("1", $guarantor['data']['subscriber_fname'], $guarantor['data']['subscriber_lname'], $guarantor['data']['subscriber_mname'], $guarantor['data']['subscriber_street'], "", $guarantor['data']['subscriber_city'], $guarantor['data']['subscriber_state'], $guarantor['data']['subscriber_postal_code'], "P", $guarantor['data']['subscriber_relationship']);
            }
        } elseif ($bill_type == "P") {
            // need to figure out what data to pull here, I feel like this should pull patient info. rather than
            // insurance info
        } elseif ($bill_type == "C") {
            // need to figure out what data to pull here
        }
        $setid2 = 0;
        $vvalue = strtoupper($_REQUEST['form_specimen_fasting']) == 'YES' ? "Y" : "N";
        $isFasting = strtoupper($_REQUEST['form_specimen_fasting']) == 'YES' ? "Y" : "N";
        //         $ht = str_pad(round($vitals['height']), 3, "0", STR_PAD_LEFT);
        $lb = floor((float)$vitals['weight']);
        $lb = str_pad($lb, 3, "0", STR_PAD_LEFT);
        $setid = 0;
        while ($pcrow = sqlFetchArray($pcres)) {
            $out .= $this->createOrc("NW", $orderid, $orderid, $porow['docnpi'], $porow['docfname'], $porow['doclname'], "", "", "", "", "", "", "");
            if ($this->hl7Priority($porow['order_priority']) == "S") {
                $out .= $this->createTq1("", "");
            }

            // Observation Request.
            // this was origionally used $porow['clinical_hx'] and I want to look at what
            // is there in teh database, I this note is a reminder to me to look.

            $specprocedure = ProcedureSqlStatements::getSpecimen($pcrow['procedure_code']);
            $out .= $this->createObr(
                ++$setid,
                $orderid,
                $pcrow['procedure_code'],
                $pcrow['procedure_name'],
                $porow['date_collected'],
                "",
                "L",
                $isFasting,  // $porow['clinical_hx'],
                "",
                "",
                "RO",
                "",
                ""
            );
            // this is where an NTE segment should be placed.

            // now from each test order list
            $hasDiagnosisSegment = false;
            while ($pdrow = sqlFetchArray($pdres)) {
                if (!empty($pdrow['diagnoses'])) {
                    $relcodes = explode(';', $pdrow['diagnoses']);
                    foreach ($relcodes as $codestring) {
                        if ($codestring === '') {
                            continue;
                        }
                        list($codetype, $code) = explode(':', $codestring);
                        $desc = lookup_code_descriptions($codestring);
                        $out .= $this->createDg1(++$setid2, $code, $desc, $codetype);
                        $hasDiagnosisSegment = true;
                        if ($setid2 < 9) {
                            $D[1] .= $code . '^';
                        }
                    }
                }
            }
            if (!$hasDiagnosisSegment) {
                return "No diagnosis present";
            }

            // Order entry questions and answers.
            $qres = ProcedureSqlStatements::getProcedureAnswers($porow['ppid'], $pcrow['procedure_code'], $orderid, $pcrow['procedure_order_seq']);
            $setid2 = 0;
            $fastflag = false;
            while ($qrow = sqlFetchArray($qres)) {
                // Formatting of these answer values may be lab-specific and we'll figure
                // out how to deal with that as more labs are supported.
                $answer = trim($qrow['answer']);
                $qcode = trim($qrow['question_code']);
                $fldtype = $qrow['fldtype'];
                $datatype = 'ST';
                if ($qcode == 'FASTIN') {
                    $fastflag = true;
                }
                if ($fldtype == 'N') {
                    $datatype = "NM";
                } elseif ($fldtype == 'D') {
                    $answer = $this->hl7Date($answer);
                } elseif ($fldtype == 'G') {
                    $weeks = intval($answer / 7);
                    $days = $answer % 7;
                    $answer = $weeks . 'wks ' . $days . 'days';
                }
                $out .= $this->createObx(++$setid2, $datatype, $qrow['tips'], $answer, "", "", "F", "", "", "");
            }

            $vvalue = strtoupper($_REQUEST['form_specimen_fasting']) === 'YES' ? "Y" : "N";
            $C[24] = $vvalue === "Y" ? ($vvalue . '12') : $vvalue;
            $T[$setid] = $this->hl7Text($pcrow['procedure_code']);
            if ($vvalue === "Y" && $fastflag === false) {
                // TODO -sjp- for now patch out the fasting default auto question. Dorn autolab doesn't allow but maybe others will!
                //$out .= $this->createObx(++$setid2, "ST", "FASTIN^FASTING^L", $vvalue, "", "", "F", "", "", "");
            }
        }
        return '';
    }


    /**
     * Generate HL7 for the specified procedure order.
     *
     * @param integer  $orderid Procedure order ID.
     * @param string  &$out     Container for target HL7 text.
     * @param string  &$reqStr
     * @return string            Error text, or empty if no errors.
     */
    public function genHl7OrderBarCode($orderid, &$reqStr)
    {
        $today = time();
        // init 2d barcode req record arrays
        for ($i = 0; $i < 98; $i++) {
            if ($i < 6) {
                $H[$i] = '';
            }
            if ($i < 9) {
                $G[$i] = '';
            }
            if ($i < 27) {
                $C[$i] = '';
            }
            if ($i < 41) {
                $A[$i] = '';
                $T[$i] = '';
            }
            $P[$i] = '';
        }
        $H[0] = 'H';
        $C[0] = 'C';
        $C[19] = '^';
        $A[0] = 'A';
        $M[0] = 'M';
        $T[0] = 'T';
        $O[0] = 'O';
        $S[0] = 'S';
        $G[0] = 'G';
        $D[0] = 'D';
        $L[0] = 'L';
        $E[0] = 'E';
        $A[21] = "^^";
        $A[22] = "^";
        $A[23] = "^";
        $A[29] = "^";
        $A[30] = "^^^^^";
        $A[33] = "^^^";
        $G[1] = "^";
        $S[1] = "^^^^^^";
        $P[0] = 'P';
        $P[36] = "^";
        $P[45] = "^";
        $P[54] = "^^^^^^^^^^^^^^";
        $P[55] = "^^^^^^^";
        $P[72] = "^^";
        $P[73] = "^^";
        $P[74] = "^^";
        $P[75] = "^^";
        $P[79] = "^";
        $P[85] = "^";
        $P[86] = "^^^^";
        $P[89] = "^";
        $P[94] = "^";
        $P[95] = "^^";
        $B = "B|||||||||||||||||||||";
        $K = "K|^|||||||||||||||^^^^||||||";
        $I = "I|^^|^^|^^|^^|^^|^^|^^|^^|";
        $porow = ProcedureSqlStatements::getProcedureOrder($orderid);
        if (empty($porow)) {
            return "Procedure order, ordering provider or lab is missing for order ID '$orderid'";
        }

        $pcres = ProcedureSqlStatements::getProcedureCode($orderid);
        $pdres = ProcedureSqlStatements::getProcedureCode($orderid);
        // why was this the exact same query? not sure but it was.
        $vitals = ProcedureSqlStatements::getVitals($porow['pid'], $porow['encounter']);
        $P[68] = $vitals['weight'];
        $P[70] = $vitals['height'];
        $P[88] = $vitals['bps'] . '^' . $vitals['bpd'];
        $P[89] = $vitals['waist_circ'];
        $C[17] = parent::hl7Date(date("Ymd", strtotime($porow['date_collected'])));
        $bill_type = strtoupper(substr($porow['billing_type'], 0, 1));
        $H[1] = $porow['send_app_id'];
        $H[2] = date('Ymd', $today);
        $P[1] = $porow['pid'];
        $P[7] = $porow['recv_fac_id'];
        $P[9] = $this->hl7Text($porow['lname']) . '^' . $this->hl7Text($porow['fname']) . '^' . $this->hl7Text($porow['mname']);
        $P[10] = $this->hl7Date($porow['DOB']);
        $P[11] = $this->hl7Sex($porow['sex']);
        $P[12] = $this->hl7SSN($porow['ss']);
        $P[13] = $this->hl7Text($porow['street']);
        $P[14] = $this->hl7Text($porow['city']);
        $P[15] = $this->hl7Text($porow['state']);
        $P[16] = $this->hl7Zip($porow['postal_code']);
        $P[17] = $this->hl7Phone($porow['phone_home']);
        $P[57] = $orderid;
        $P[58] = $porow['pid'];
        if ($bill_type == 'T') {
            $P[18] = "XI";
        } elseif ($bill_type == 'P') {
            $P[18] = "03";
        } else {
            $P[18] = "04";
        }

        $P[29] = $this->hl7Text($porow['doclname']) . "^" . $this->hl7Text($porow['docfname']);
        $P[30] = $this->hl7Text($porow['docnpi']);
        $P[71] = $this->hl7Text($porow['docnpi']);
        // Insurance stuff.
        $payers = $this->loadPayerInfo($porow['pid'], $porow['date_ordered']);
        $setid = 0;
        if ($bill_type == 'T') {
            // only send primary and secondary insurance
            foreach ($payers as $payer) {
                $payer_object = $payer['object'];
                $payer_address = $payer_object->get_address();
                $full_address = $payer_address->get_line1();
                $setid = $setid + 1;
                if (!empty($payer_address->get_line2())) {
                    $full_address .= "," . $payer_address->get_line2();
                }

                if ($payer_object->get_ins_type_code() === '2') {
                    // medicare
                    $P[19] = $this->hl7Text($payer['data']['policy_number']);
                } elseif ($payer_object->get_ins_type_code() === '3') {
                    // medicaid
                    $P[53] = $this->hl7Text($payer['data']['policy_number']);
                } else {
                    $P[40] = $this->hl7Text($payer['data']['policy_number']);
                }
                if ($setid === 2) {
                    $P[43] = $this->hl7Text($payer['company']['cms_id']);
                    $P[44] = $this->hl7Text($payer['company']['name']);
                    $P[45] = $this->hl7Text($full_address);
                    $P[46] = $this->hl7Text($payer_address->get_city());
                    $P[47] = $this->hl7Text($payer_address->get_state());
                    $P[48] = $this->hl7Zip($payer_address->get_zip());
                    $P[41] = $this->hl7Text($payer['data']['group_number']);
                    $P[52] = $this->hl7Workman($payer['data']['policy_type']);
                    break;
                }
                $P[34] = $this->hl7Text($payer['company']['cms_id']);
                $P[35] = $this->hl7Text($payer['company']['name']);
                $P[36] = $this->hl7Text($full_address);
                $P[37] = $this->hl7Text($payer_address->get_city());
                $P[38] = $this->hl7Text($payer_address->get_state());
                $P[39] = $this->hl7Zip($payer_address->get_zip());
                $P[41] = $this->hl7Text($payer['data']['group_number']);
                $P[52] = $this->hl7Workman($payer['data']['policy_type']);
            }
            if ($setid === 0) {
                return "\nInsurance is being billed but patient does not have any payers on record!";
            }
        }

        /*
        bill_type is T = Third Party
                     P = Self Pay
                     C = Clinic
        */
        if ($bill_type == "T") {
            $guarantors = $this->loadGuarantorInfo($porow['pid'], $porow['date_ordered']);
            // this is returning an array but in the query we have a limit 1!
            foreach ($guarantors as $guarantor) {
                $P[20] = $this->hl7Text($guarantor['data']['subscriber_lname']) . '^' . $this->hl7Text($guarantor['data']['subscriber_fname']) . '^';
                $P[21] = $this->hl7Date($guarantor['data']['subscriber_ss']);
                $P[22] = $this->hl7Text($guarantor['data']['subscriber_street']);
                $P[23] = $this->hl7Text($guarantor['data']['subscriber_city']);
                $P[24] = $this->hl7Text($guarantor['data']['subscriber_state']);
                $P[25] = $this->hl7Zip($guarantor['data']['subscriber_postal_code']);
                // $P[26] =  // employer;
                $P[27] = $this->hl7Relation($guarantor['data']['subscriber_relationship']);
                $P[56] = $this->hl7Phone($guarantor['data']['subscriber_phone']);
            }
        } elseif ($bill_type == "P") {
            // need to figure out what data to pull here, I feel like this should pull patient info. rather than
            // insurance info
        } elseif ($bill_type == "C") {
            // need to figure out what data to pull here
        }


        $setid2 = 0;
        $D[1] = substr($D[1], 0, strlen($D[1]) - 1);
        $vvalue = strtoupper($_REQUEST['form_specimen_fasting']) == 'YES' ? "Y" : "N";
        $isFasting = strtoupper($_REQUEST['form_specimen_fasting']) == 'YES' ? "Y" : "N";
        //         $ht = str_pad(round($vitals['height']), 3, "0", STR_PAD_LEFT);
        $lb = floor((float)$vitals['weight']);
        $lb = str_pad($lb, 3, "0", STR_PAD_LEFT);
        $setid = 0;
        while ($pcrow = sqlFetchArray($pcres)) {
            // this is where an NTE segment should be placed.

            // now from each test order list
            $hasDiagnosisSegment = false;
            while ($pdrow = sqlFetchArray($pdres)) {
                if (!empty($pdrow['diagnoses'])) {
                    $relcodes = explode(';', $pdrow['diagnoses']);
                    foreach ($relcodes as $codestring) {
                        if ($codestring === '') {
                            continue;
                        }
                        list($codetype, $code) = explode(':', $codestring);
                        $desc = lookup_code_descriptions($codestring);
                        $out .= $this->createDg1(++$setid2, $code, $desc, $codetype);
                        $hasDiagnosisSegment = true;
                        if ($setid2 < 9) {
                            $D[1] .= $code . '^';
                        }
                    }
                }
            }
            if (!$hasDiagnosisSegment) {
                return "No diagnosis present";
            }

            // Order entry questions and answers.
            $qres = ProcedureSqlStatements::getProcedureAnswers($porow['ppid'], $pcrow['procedure_code'], $orderid, $pcrow['procedure_order_seq']);
            $setid2 = 0;
            $fastflag = false;
            while ($qrow = sqlFetchArray($qres)) {
                // Formatting of these answer values may be lab-specific and we'll figure
                // out how to deal with that as more labs are supported.
                $answer = trim($qrow['answer']);
                $qcode = trim($qrow['question_code']);
                $fldtype = $qrow['fldtype'];
                $datatype = 'ST';
                if ($qcode == 'FASTIN') {
                    $fastflag = true;
                }
                if ($fldtype == 'N') {
                    $datatype = "NM";
                } elseif ($fldtype == 'D') {
                    $answer = $this->hl7Date($answer);
                } elseif ($fldtype == 'G') {
                    $weeks = intval($answer / 7);
                    $days = $answer % 7;
                    $answer = $weeks . 'wks ' . $days . 'days';
                }
            }

            $vvalue = strtoupper($_REQUEST['form_specimen_fasting']) === 'YES' ? "Y" : "N";
            $C[24] = $vvalue === "Y" ? ($vvalue . '12') : $vvalue;
            $T[$setid] = $this->hl7Text($pcrow['procedure_code']);
            if ($vvalue === "Y" && $fastflag === false) {
                $out .= $this->createObx(++$setid2, "ST", "FASTIN^FASTING^L", $vvalue, "", "", "F", "", "", "");
            }
        }

        $reqStr = "";
        for ($i = 0; $i < 6; $i++) {
            $reqStr .= $H[$i] . '|';
        }
        $reqStr .= "\x0D";
        for ($i = 0; $i < 98; $i++) {
            $reqStr .= $P[$i] . '|';
        }
        $reqStr .= "\x0D";
        for ($i = 0; $i < 27; $i++) {
            $reqStr .= $C[$i] . '|';
        }
        $reqStr .= "\x0D";
        for ($i = 0; $i < 41; $i++) {
            $reqStr .= $A[$i] . '|';
        }
        $reqStr .= "\x0D";
        for ($i = 0; $i < 41; $i++) {
            $reqStr .= $T[$i] . '|';
        }
        $reqStr .= "\x0D";
        for ($i = 0; $i < 6; $i++) {
            $reqStr .= $M[$i] . '|';
        }
        $reqStr .= "\x0D";
        $reqStr .= $D[0] . '|' . $D[1] . '||' . "\x0D";
        $l = strlen($reqStr);
        $reqStr .= "L|$l|\x0D";
        $reqStr .= 'E|0|' . "\x0D";
        $reqStr = strtoupper($reqStr);
        return '';
    }

    /*
    The OBX segment is conditional and only required if/when AOE response information
    or AUC data is available. Each AOE response or AUC determination will be included as
    an individual OBX segment nested beneath the OBR segment of the corresponding
    ordered test or imaging service.
    */
    private function createObx($setId, $valueType, $observationIdent, $observationValue, $units, $interpretationCodes, $observationResultStatus, $producersReference, $observationType, $observationValueAbsentReason)
    {
        $fields = [
            $this->buildHL7Field($setId),
            $this->buildHL7Field($valueType),  // 2
            $this->buildHL7Field($observationIdent),  // 3
            "",  // 4
            $this->buildHL7Field($observationValue),  // 5
            $this->buildHL7Field($units),  // 6
            "",  // 7
            $this->buildHL7Field($interpretationCodes),  // 8
            "",  // 9
            "", // 10
            $this->buildHL7Field($observationResultStatus), // 11
            "", // 12
            "",  // 13
            "",  // 14
            $this->buildHL7Field($producersReference),  // 15
            "",  // 16
            "",  // 17
            "",  // 18
            "",  // 19
            "", // 20
            "", // 21
            "", // 22
            "", // 23
            "",  // 24
            "",  // 25
            "",  // 26
            "",  // 27
            "",  // 28
            $this->buildHL7Field($observationType),  // 29
            "", // 30
            "", // 31
            $this->buildHL7Field($observationValueAbsentReason), // 32
            "", // 33
        ];
        $segment = $this->buildHl7Segment("OBX", $fields);
        return $segment;
    }


    /*
        The DG1 segment is used to communicate one or more diagnoses associated with an
        ordered observation (test). Each diagnosis will be included as an individual DG1
        segment nested beneath the OBR segment of the corresponding ordered test.
        The DG1 segment is required and may appear one or more times for each OBR
        segment.
    */
    private function createDg1($setId, $diagCode, $diagDesc, $diagType)
    {
        $diagDesc = $this->replaceNewLine($diagDesc);
        $fields = [
            $this->buildHL7Field($setId), // 1
            "", // 2
            $this->buildHL7Field([$diagCode, $diagDesc, "I10c"]),  // 3
            "",  // 4
            "",  // 5
            $this->buildHL7Field($diagType)  // 6
        ];
        $segment = $this->buildHl7Segment("DG1", $fields);
        error_log(text($segment));
        return $segment;
    }

    /*
    The OBR segment is used to transmit information about an order for a diagnostic study
    or observation, physical exam, or assessment. Among other things it specifies details
    such as order test identifier(s).
    An OBR segment will appear once for each test placed in an individual order message.
    An ORC segment will accompany each OBR segment in a message.
    */
    private function createObr($setId, $placerOrderNumber, $procedureCode, $procedureName, $observationStartDateTime, $observationEndDateTime, $specimenActionCode, $fastingStatus, $placerField1, $placerField2, $fillerField1, $resultsCopiesTo, $scheduledDateTime): string
    {
        $fields = [
            $this->buildHL7Field($setId),
            $this->buildHL7Field($placerOrderNumber),  // 2
            "",  // 3
            $this->buildHL7Field([$procedureCode, $procedureName]),  // 4
            "",  // 5
            "",  // 6
            $this->hl7DateTime($observationStartDateTime),  // 7
            $this->hl7DateTime($observationEndDateTime),  // 8
            "",  // 9
            "", // 10
            $this->buildHL7Field($specimenActionCode), // 11
            "", // 12
            $this->buildHL7Field($fastingStatus),  // 13
            "",  // 14
            "",  // 15
            "",  // 16
            "",  // 17
            $this->buildHL7Field($placerField1),  // 18
            $this->buildHL7Field($placerField2),  // 19
            $this->buildHL7Field($fillerField1), // 20
            "", // 21
            "", // 22
            "", // 23
            "",  // 24
            "",  // 25
            "",  // 26
            "",  // 27
            $this->buildHL7Field($resultsCopiesTo),  // 28
            "",  // 29
            "", // 30
            "", // 31
            "", // 32
            "", // 33
            "",  // 34
            "",  // 35
            $this->buildHL7Field($scheduledDateTime),  // 36
        ];
        $segment = $this->buildHl7Segment("OBR", $fields);
        return $segment;
    }

    /*
        The TQ1 segment is used only to indicate when the order is a STAT order or a future
        order.
        The TQ1 segment is conditional and omitted if the order is not a STAT or a future order.
    */
    private function createTq1($startDateTime, $endDateTime): string
    {
        $fields = [
            "1",  // 1
            "",  // 2
            "",  // 3
            "",  // 4
            "",  // 5
            "",  // 6
            $this->buildHL7Field($startDateTime),  // 7
            $this->buildHL7Field($endDateTime),  // 8
            "S",  // 9
        ];
        $segment = $this->buildHl7Segment("TQ1", $fields);
        return $segment;
    }

    /**
     * Order Common (ORC)
     * The ORC segment contains data and information common to all the tests contained in
     * the order message. One ORC segment will accompany each OBR segment contained
     * in a message.
     * An ORC segment will appear once for each test placed in an individual order message.
     */
    private function createOrc(
        $orderControl,
        $placerOrderNumber,
        $placerGroupNumber,
        $orderingProviderNpi,
        $orderingProviderFirstName,
        $orderingProviderLastName,
        $orderingProviderMiddle,
        $callBackPhoneNumber,
        $orderingProviderAddress1,
        $orderingProviderAddress2,
        $orderingProviderCity,
        $orderingProviderState,
        $orderingProviderZip,
    ): string {
        $fields = [
            $this->buildHL7Field($orderControl),  // 1
            $this->buildHL7Field($placerOrderNumber),  // 2
            "",  // 3
            $this->buildHL7Field($placerGroupNumber),  // 4
            "",  // 5
            "",  // 6
            "",  // 7
            "",  // 8
            "",  // 9
            "", // 10
            "", // 11
            $this->buildHL7Field([$orderingProviderNpi, $orderingProviderLastName, $orderingProviderFirstName, $orderingProviderMiddle, "", "", "", "", "", "", "", "", "NPI"]), // 12
            "",  // 13
            $this->buildHL7Field($callBackPhoneNumber),  // 14
            "",  // 15
            "",  // 16
            "",  // 17
            "",  // 18
            "",  // 19
            "", // 20
            "", // 21
            "", // 22
            "", // 23
            $this->buildHL7Field([$orderingProviderAddress1, $orderingProviderAddress2, $orderingProviderCity, $orderingProviderState, $orderingProviderZip]),  // 24
        ];
        $segment = $this->buildHl7Segment("ORC", $fields);
        return $segment;
    }

    /*
    The GT1 segment contains information about the person with financial responsibility for
    payment of services.
    The GT1 segment is required and may only appear once
    */
    private function createGt1($setId, $subscriberFirstName, $subscriberLastName, $subscriberMiddleName, $subscriberAddress1, $subscriberAddress2, $subscriberCity, $subscriberState, $subscriberZip, $subscriberType, $relationship): string
    {
        $fields = [
            $this->buildHL7Field($setId),
            "",
            $this->buildHL7Field([$subscriberLastName, $subscriberFirstName, $subscriberMiddleName]),  // 3
            "",
            $this->buildHL7Field([$subscriberAddress1, $subscriberAddress2, $subscriberCity, $subscriberState, $subscriberZip]),  // 5
            "",  // 6
            "",  // 7
            "",  // 8
            "",  // 9
            $this->buildHL7Field($subscriberType), // 10
            $this->hl7Relation($relationship)];
        $segment = $this->buildHl7Segment("GT1", $fields);
        return $segment;
    }

    /*
    The IN1 segment is used to communicate insurance policy coverage information to the
    Order Filler when such information is relevant for a requisition.
    The IN1 segment is conditional and should only be present if the value of PV1-20 is "T",
    i.e. for third-party billing. Up to two IN1 segments may be included.
    */
    private function createIn1(
        $setId,
        $insPlanId,
        $insCompanyId,
        $insCompanyName,
        $insAddress1,
        $insAddress2,
        $insCity,
        $insState,
        $insZip,
        $insPhone,
        $groupNumber,
        $insuredGroupEmpName,
        $planExpDate,
        $subscriberFirstName,
        $subscriberLastName,
        $subscriberMiddleName,
        $relationship,
        $subscriberDob,
        $subscriberAddress1,
        $subscriberAddress2,
        $subscriberCity,
        $subscriberState,
        $subscriberZip,
        $policyNumber
    ): string {
        $fields = [
            $this->buildHL7Field($setId),
            $this->buildHL7Field($insPlanId),
            $this->buildHL7Field($insCompanyId),
            $this->buildHL7Field($insCompanyName),
            $this->buildHL7Field([$insAddress1, $insAddress2, $insCity, $insState, $insZip]),  // 5
            "",
            $this->buildHL7Field($insPhone),
            $this->buildHL7Field($groupNumber),
            "",
            "", // 10
            $this->buildHL7Field($insuredGroupEmpName),
            $this->buildHL7Field($planExpDate),
            "",
            "",
            "", // 15
            $this->buildHL7Field([$subscriberLastName, $subscriberFirstName, $subscriberMiddleName]),
            $this->hl7Relation($relationship),
            $this->buildHL7Field($subscriberDob),
            $this->buildHL7Field([$subscriberAddress1, $subscriberAddress2, $subscriberCity, $subscriberState, $subscriberZip]), // 19
            "", // 20
            "", // 21
            "", // 22
            "",
            "",
            "", // 25
            "",
            "",
            "",
            "",
            "", // 30
            "",
            "",
            "",
            "",
            "", // 35
            $this->buildHL7Field($policyNumber),

        ];
        $segment = $this->buildHl7Segment("IN1", $fields);
        return $segment;
    }

    private function createPv1($patientClass, $financialClass): string
    {
        $fields = [
            "1", // 1
            $this->buildHL7Field($patientClass),
            "", // 3
            "", // 4
            "", // 5
            "",
            "", // 7
            "",
            "", // 9
            "", // 10
            "",
            "", // 12
            "",
            "", // 14
            "",
            "", // 16
            "",
            "", // 18
            "",
            $this->buildHL7Field($financialClass)  // 20
        ];
        $segment = $this->buildHl7Segment("PV1", $fields);
        return $segment;
    }

    private function createPid(
        $setPid,
        $pid,
        $patientIdentList,
        $altPid,
        $patientFirstName,
        $patientLastName,
        $patientMiddleName,
        $mothersMaidenName,
        $dob,
        $adminSex,
        $patAlias,
        $race,
        $patAddressStreet,
        $patAddressStreet2,
        $patAddressCity,
        $patAddressState,
        $patAddressZip,
        $countryCode,
        $phoneHome,
        $phoneBus,
        $primaryLanguage,
        $maritalStatus,
        $religion,
        $patAccNumber,
        $patSsn,
        $patDriversLicense,
        $mothersId,
        $ethnicGroup,
    ): string {

        $fields = [
            $this->buildHL7Field($setPid),
            $this->buildHL7Field($pid),
            $this->buildHL7Field([$patientIdentList, "", "", "", "AN"]),
            $this->buildHL7Field($altPid),
            $this->buildHL7Field([$patientLastName, $patientFirstName, $patientMiddleName]),
            $this->buildHL7Field($mothersMaidenName),
            $this->formatDate($dob),
            $this->hl7Sex($adminSex),
            $this->buildHL7Field($patAlias),
            $this->hl7Race($race),
            $this->buildHL7Field([$patAddressStreet, $patAddressStreet2, $patAddressCity, $patAddressState, $patAddressZip]),
            $this->buildHL7Field($countryCode),
            $this->buildHL7Field($phoneHome),
            $this->buildHL7Field($phoneBus),
            $this->buildHL7Field($primaryLanguage),
            $this->buildHL7Field($maritalStatus),
            $this->buildHL7Field($religion),
            $this->buildHL7Field($patAccNumber),
            $this->buildHL7Field($patSsn),
            $this->buildHL7Field($patDriversLicense),
            $this->buildHL7Field($mothersId),
            $this->buildHL7Field($ethnicGroup),
        ];
        $segment = $this->buildHl7Segment("PID", $fields);
        return $segment;
    }

    private function createMsh(
        $sendingApplication,
        $sendingFacility,
        $receivingApplication,
        $receivingFacility,
        $msgDateTime,
        $security,
        $msgCtrlId,
        $processingId,
        $sequenceNumber,
        $continuationPointer,
        $acceptAckType,
        $applicationAckType,
        $countryCode,
        $characterSet,
        $principleLangMsg,
        $altCharScheme
    ): string {

        // Combine encoding characters
        $encodingCharacters = $this->componentSeparator .
            $this->repetitionSeparator .
            $this->escapeSeparator .
            $this->subComponentSeparator;
        $fields = [
            $encodingCharacters,  // POS 1 & 2
            $this->buildHL7Field($sendingApplication), // POS 3
            $this->buildHL7Field($sendingFacility), // POS 4 - per dorn this should be the account number
            $this->buildHL7Field($receivingApplication), // POS 5
            $this->buildHL7Field($receivingFacility), // POS 6
            $this->buildHL7Field($msgDateTime), // POS 7
            $this->buildHL7Field($security), // POS 8
            $this->buildHL7Field(["OML", "O21", "OML_O21"]), // POS 9
            $this->buildHL7Field($msgCtrlId), // POS 10
            $this->buildHL7Field($processingId), // POS 11
            $this->buildHL7Field("2.5.1"), // POS 12
            $this->buildHL7Field($sequenceNumber), // POS 13
            $this->buildHL7Field($continuationPointer), // POS 14
            $this->buildHL7Field($acceptAckType), // POS 15
            $this->buildHL7Field($applicationAckType), // POS 16
            $this->buildHL7Field($countryCode), // POS 17
            $this->buildHL7Field($characterSet), // POS 18
            $this->buildHL7Field($principleLangMsg), // POS 19
            $this->buildHL7Field($altCharScheme), // POS 20
            $this->buildHL7Field("ELINCS_MT-OML-1_1.0"), // POS 21
        ];
        foreach ($fields as $field) {
            $segment .= $this->fieldSeparator . $field;
        }
        $segment = "MSH" . $segment . $this->lineBreakChar;
        return $segment;
    }

    /**
     * Transmit HL7 for the specified lab.
     *
     * @param integer $ppid Procedure provider ID.
     * @param string  $out  The HL7 text to be sent.
     * @return string         Error text, or empty if no errors.
     */
    public function sendHl7Order($ppid, $orderId, $out)
    {
        $responseMessage = "";
        global $srcdir;
        $pid = null;
        $porow = sqlQuery("SELECT " .
            "po.date_collected, po.date_ordered, po.order_priority,po.billing_type,po.clinical_hx,po.account,po.order_diagnosis, " .
            "pp.*, " .
            "pd.pid, pd.pubpid, pd.fname, pd.lname, pd.mname, pd.DOB, pd.ss, pd.race, " .
            "pd.phone_home, pd.phone_biz, pd.sex, pd.street, pd.city, pd.state, pd.postal_code, " .
            "f.encounter, u.fname AS docfname, u.lname AS doclname, u.npi AS docnpi, u.id as user_id " .
            "FROM procedure_order AS po, procedure_providers AS pp, " .
            "forms AS f, patient_data AS pd, users AS u " .
            "WHERE " .
            "po.procedure_order_id = ? AND " .
            "pp.ppid = po.lab_id AND " .
            "f.formdir = 'procedure_order' AND " .
            "f.form_id = po.procedure_order_id AND " .
            "pd.pid = f.pid AND " .
            "u.id = po.provider_id", array($orderId));
        if (!empty($porow)) {
            $pid = $porow['pid'];
        }

        $d0 = "\r";
        $ppSql = "SELECT * FROM procedure_providers AS pp
            INNER JOIN mod_dorn_routes AS mdr ON
                pp.ppid = mdr.ppid
            WHERE pp.ppid = ?";
        $pprow = sqlQuery($ppSql, array($ppid));
        if (empty($pprow)) {
            return xl('Procedure provider') . " $ppid " . xl('not found');
        }

        $labGuid = $pprow['lab_guid'];
        $labAccountNumber = $pprow['lab_account_number'];
        $protocol = $pprow['protocol'];
        // Extract MSH-10 which is the message control ID.
        $segmsh = explode(substr($out, 3, 1), substr($out, 0, strpos($out, $d0)));
        $msgid = $segmsh[9];
        if (empty($msgid)) {
            return xl('Internal error: Cannot find MSH-10');
        }


        if ($protocol == 'DL') {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=order_$msgid.hl7");
            header("Content-Description: File Transfer");
            echo $out;
            exit;
        } else {
            $response = ConnectorApi::sendOrder($labGuid, $labAccountNumber, $orderId, $pid, $out);
            if (!$response->isSuccess) {
                $responseMessage = $response->responseMessage;
            }
        }

        // Falling through to here indicates success.
        EventAuditLogger::instance()->newEvent("proc_order_xmit", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "ID: $msgid Protocol: $protocol Host: DORN");
        return $responseMessage;
    }
}
