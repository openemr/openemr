<?php

/*
 *  package   OpenEMR
 *  link      http://www.open-emr.org
 *  author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  copyright Copyright (c )2020.. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

use Exception;

class AutoBilling
{
    public function generateBilling($event_date, $bInfo, $pid, $userid)
    {
        /**
         *  Aggregate information to fill billing table
         */
        $event_date = $event_date . " " . date("H:i:s"); // append current time to event date
        $billingData = explode("-", $bInfo);  //separate up billing information into code and text
        $code = trim($billingData[0]);  // code that is in the calendar description
        $text = trim($billingData[1]);  // text that is after the code in the calendar description

        //see if there is a diagnosis in the patient chart
        //returns an array
        $icd10 = self::getDiagnosis($pid);

        //if not empty get the description
        /**
         * TODO: account for multiple diagnosis codes
         */
        if (!empty($icd10)) {
            $desc = $icd10['title'];
            $icd10 = $icd10['diagnosis'];
            $icd10 = substr($icd10, 6);
        } else {
            error_log('No diagnosis code found in chart');
            die;  //stop processing if no Diagnosis is found.
        }

        /**
         * Grab Fee
         * @returns int
        */
        $fees = self::getCodeFee($code);

        /**
         * Grab the encounter to post charges using the event date
         * @returns int
        */
       $enc = self::getEventEncounter($pid, $event_date);

        /**
         * Grab the provider for the encounter from the event table
         */
        $provider = self::getEventProvider($pid, $event_date);

        /**
         * find out if there are any entries in the billing table for event date for this patient
         * moved to calendar inc
         *
         */
        $res = self::findEventBilling($pid, $event_date);

        /**
         * Gets the modifier assigned to the
         */
        $modifier = self::getModifier($code);

        /**
         * Is documentation done
         */
        $docs = self::checkDocumenation($pid, $enc);
        if ($docs == 1) {
            echo "<br><br><h4 style='color: red'>" . text("Please add documentation to complete this visit. Your pay depends on it") . "</h4>";
            die;
        }
        //insert billing if there is none
        if (empty($res['encounter'])) {
            try {
                $codetype = "CPT4";
                self::insertCPTBilling($event_date, $code, $pid, $provider, $userid, $enc, $text, $fees, $icd10, $codetype, $modifier);

                //Enter ICD10 if it exist
                if (!empty($icd10) && empty($res)) {
                    self::insertICDBilling($event_date, $icd10, $pid, $provider, $userid, $enc, $desc);
                }
            } catch (Exception $e) {
                error_log('Autobilling failed ' . $e);
            }
        }
    }// End of billing entries

    /**
     * @param $event_date
     * @param $code
     * @param $pid
     * @param $provider
     * @param $userid
     * @param $enc
     * @param $text
     * @param $fees
     * @param $icd
     * @param $codetype
     * @param $modifer
     */
    private function insertCPTBilling($event_date,$code,$pid,$provider,$userid,$enc,$text,$fees,$icd10,$codetype,$modifer)
    {
        $sql = "REPLACE INTO billing SET " .
            "date = ? , " .
            "code_type = ? , " .
            "code = ? , " .
            "pid = ? , " .
            "provider_id = ? , " .
            "user = ? , " .
            "groupname = 'default', " .
            "authorized = '1', " .
            "encounter = ? , " .
            "code_text = ? , " .
            "activity = '1', " .
            "modifier = ? , " .
            "units = '1', " .
            "fee = ? , " .
            "justify = ? , " .
            "pricelevel = 'standard'";

        try {
               sqlStatement($sql, [$event_date, $codetype, $code, $pid, $provider, $userid, $enc, $text, $modifer, $fees, $icd10]);
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * @param $enDate
     * @param $icd10
     * @param $pid
     * @param $provider
     * @param $userid
     * @param $enc
     * @param $desc
     */
    private function insertICDBilling($enDate,$icd10,$pid,$provider,$userid,$enc,$desc)
    {
        $sql = "REPLACE INTO billing SET "
            . "date = ? ,"
            . "code_type = ? ,"
            . "code = ? ,"
            . "pid = ? ,"
            . "provider_id = ? ,"
            . "user = ? ,"
            . "groupname = 'default',"
            . "authorized = '1' ,"
            . "encounter = ? ,"
            . "code_text = ? ,"
            . "activity = '1',"
            . "units = '1' , "
            . "fee = '0.00' ";

        try {
            sqlStatement($sql, [$enDate, $icd10, $pid, $provider, $userid, $enc, $desc]);
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * @param $pid
     * @return mixed
     */
    public function getDiagnosis($pid)
    {
        /**
         * get the most recent diagnosis
         */
        $sql = "select title, diagnosis, type from lists where pid = ? ORDER BY id DESC LIMIT 1";
        $res = sqlQuery($sql, [$pid]);
        return $res;
    }

    /**
     * @param $code
     * @return string
     */
    private function getCodeFee($code)
    {
        $getFee = "SELECT b.pr_price FROM `codes` AS a, prices AS b WHERE a.code = ? AND a.id = b.pr_id ";
        $fee = sqlQuery($getFee, array($code));
        if (empty($fee['pr_price'])) {
            $fees = '0.00';
        } else {
            $fees = $fee['pr_price'];
        }
        return $fees;
    }

    /**
     * @param $pid
     * @param $enDate
     * @return mixed
     */
    private function getEventEncounter($pid,$enDate)
    {
        $d = substr($enDate, 0,-8);
        $de = $d." 00:00:00";
        $sql = "SELECT encounter FROM form_encounter WHERE pid = ? AND date = ?";
        $query_e = sqlQuery($sql, [$pid, $de]);
        return $query_e['encounter'];

    }

    /**
     * @param $pid
     * @param $enDate
     * @return mixed
     * Check if billing has been done for this event date
     */
    private function findEventBilling($pid, $enDate)
    {
        $setdate = substr($enDate, 0, -8);
        $sql = "select encounter from billing where pid = ? and date like ? ORDER by id limit 1";
        $findbilling = sqlQuery($sql, [$pid, $setdate . "%"]);
        return $findbilling;

    }

    //find modifier for code if one

    /**
     * @param $code
     * @return mixed
     */
    private function getModifier($code) {
        $sql = "select modifier from codes where code = ?";
        $isModified = sqlQuery($sql, [$code]);
        return $isModified['modifier'];

    }

    //get the provider for the encounter
    /**
     * @param $pid
     * @param $event_date
     * @return mixed
     */
    private function getEventProvider($pid, $event_date)
    {
        $ev = substr($event_date, 0,-8);
        $sql = "select pc_aid from openemr_postcalendar_events where pc_eventDate = ? AND pc_pid = ? ";
        $provider = sqlQuery($sql, [$ev, $pid]);
        return $provider['pc_aid'];
    }

    /**
     * @param $pid,
     * @param $enc
     * @return mixed
     *  check that any documentation has been started/saved
     */
    private function checkDocumenation($pid, $enc)
    {
        $sql = "select count(formdir) as c from forms where pid = ? and encounter = ?";
        $formcount = sqlQuery($sql, [$pid, $enc]);
        return $formcount['c'];
    }
} // end of class
