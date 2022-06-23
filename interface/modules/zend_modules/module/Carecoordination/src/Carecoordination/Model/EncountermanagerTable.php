<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Riju K P <rijukp@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

// TODO: we need to refactor all of this so it can go into a class for this functionality
require_once($GLOBALS['fileroot'] . '/ccr/transmitCCD.php');
require_once($GLOBALS['fileroot'] . '/library/amc.php');

use Application\Plugin\CommonPlugin;
use CouchDB;
use DOMDocument;
use Dompdf\Dompdf;
use Application\Model\ApplicationTable;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Driver\Pdo\Result;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\DirectMessaging\ErrorConstants;
use OpenEMR\Common\Logging\SystemLogger;
use XSLTProcessor;

class EncountermanagerTable extends AbstractTableGateway
{
    public function getEncounters($data, $getCount = null)
    {
        $query_data = array();
        $query = "SELECT pd.fname, pd.lname, pd.mname, date(fe.date) as date, fe.pid, fe.encounter, pd.date AS 'patient_creation_date',
                        u.fname as doc_fname, u.mname as doc_mname, u.lname as doc_lname, (select count(encounter) from form_encounter where pid=fe.pid) as enc_count,
                        (SELECT DATE(date) FROM form_encounter WHERE pid=fe.pid ORDER BY date DESC LIMIT 1) as last_visit_date,
						(select count(*) from ccda where pid=fe.pid and transfer=1) as ccda_transfer_count,
						(select count(*) from ccda where pid=fe.pid and transfer=1 and status=1) as ccda_successfull_transfer_count
                        FROM form_encounter AS fe
                        JOIN patient_data AS pd ON pd.pid=fe.pid
                        LEFT JOIN users AS u ON u.id=fe.provider_id ";
        if ($data['status']) {
            $query .= " LEFT JOIN combination_form AS cf ON cf.encounter = fe.encounter ";
        }

        $query .= " WHERE 1=1 ";

        if ($data['status'] == "signed") {
            $query .= " AND cf.encounter IS NOT NULL AND cf.encounter !=''";
        }

        if ($data['status'] == "unsigned") {
            $query .= " AND (cf.encounter IS  NULL OR cf.encounter ='')";
        }
        if ($data['from_date'] && $data['to_date']) {
            if ($data['search_type_date'] == 'date_patient_creation') {
                $query .= " AND pd.date BETWEEN ? AND ? ";
            } else {
                // default is encounter date
                $query .= " AND fe.date BETWEEN ? AND ? ";
            }
            $query_data[] = $data['from_date'];
            $query_data[] = $data['to_date'];
        }
        if (!empty($data['provider_id'])) {
            $query .= " AND (`fe`.`provider_id` = ? OR `fe`.`supervisor_id` = ?) ";
            $query_data[] = $data['provider_id'];
            $query_data[] = $data['provider_id'];
        }

        if (!empty($data['billing_facility_id'])) {
            $query .= " AND `fe`.`billing_facility` = ? ";
            $query_data[] = $data['billing_facility_id'];
        }

        if ($data['pid']) {
            $query .= " AND (fe.pid = ? OR pd.fname like ? OR pd.mname like ? OR pd.lname like ? OR CONCAT_WS(' ',pd.fname,pd.lname) like ?) ";
            $query_data[] = $data['pid'];
            $query_data[] = "%" . $data['pid'] . "%";
            $query_data[] = "%" . $data['pid'] . "%";
            $query_data[] = "%" . $data['pid'] . "%";
            $query_data[] = "%" . $data['pid'] . "%";
        }

        if ($data['encounter']) {
            $query .= " AND fe.encounter = ? ";
            $query_data[] = $data['encounter'];
        }

        $query .= " GROUP BY fe.pid ";

        $query .= " ORDER BY fe.pid, fe.date ";

        $appTable = new ApplicationTable();

        if ($getCount) {
            $res = $appTable->zQuery($query, $query_data);
            $resCount = $res->count();
            return $resCount;
        }

        $query .= " LIMIT " . CommonPlugin::escapeLimit($data['limit_start']) . "," . CommonPlugin::escapeLimit($data['results']);
        $resDetails = $appTable->zQuery($query, $query_data);

        return $resDetails;
    }

    public function getStatus($data)
    {
        $pid = '';
        foreach ($data as $row) {
            if (!empty($pid)) {
                $pid .= ',';
            }

            $pid .= ($row['pid'] ?? '');
        }

        if (empty($pid)) {
            $pid = "''";
        }

        $query = "SELECT cc.*, DATE(fe.date) AS dos, CONCAT_WS(' ',u.fname, u.mname, u.lname) AS user_name FROM ccda AS cc
				LEFT JOIN form_encounter AS fe ON fe. pid = cc.pid AND fe.encounter = cc.encounter
				LEFT JOIN users AS u ON u.id = cc.user_id
				WHERE cc.pid in (?) ORDER BY cc.pid, cc.time desc";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($pid));
        return $result;
    }

    public function convert_to_yyyymmdd($date)
    {
        $date = str_replace('/', '-', $date);
        $arr = explode('-', $date);
        $formatted_date = $arr[2] . "-" . $arr[0] . "-" . $arr[1];
        return $formatted_date;
    }

    /*
    * Convert date from database format to required format
    *
    * @param    String      $date       Date from database (format: YYYY-MM-DD)
    * @param    String      $format     Required date format
    *
    * @return   String      $formatted_date New formatted date
    */
    public function date_format($date, $format)
    {
        if (!$date) {
            return;
        }

        $format = $format ? $format : 'm/d/y';
        $temp = explode(' ', $date); //split using space and consider the first portion, incase of date with time
        $date = $temp[0];
        $date = str_replace('/', '-', $date);
        $arr = explode('-', $date);

        if ($format == 'm/d/y') {
            $formatted_date = $arr[1] . "/" . $arr[2] . "/" . $arr[0];
        }

        $formatted_date = $temp[1] ? $formatted_date . " " . $temp[1] : $formatted_date; //append the time, if exists, with the new formatted date
        return $formatted_date;
    }

    public function getFile($id)
    {
        $query = "select couch_docid, couch_revid, ccda_data, encrypted from ccda where id=?";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($id));
        foreach ($result as $row) {
            if ($row['couch_docid'] != '') {
                $couch = new CouchDB();
                $resp = $couch->retrieve_doc($row['couch_docid']);
                if ($row['encrypted']) {
                    $cryptoGen = new CryptoGen();
                    $content = $cryptoGen->decryptStandard($resp->data, null, 'database');
                } else {
                    $content = base64_decode($resp->data);
                }
            } elseif (!$row['couch_docid']) {
                if (!filesize($row['ccda_data'])) {
                    continue;
                }
                $fccda = fopen($row['ccda_data'], "r");
                if ($row['encrypted']) {
                    $cryptoGen = new CryptoGen();
                    $content = $cryptoGen->decryptStandard(fread($fccda, filesize($row['ccda_data'])), null, 'database');
                } else {
                    $content = fread($fccda, filesize($row['ccda_data']));
                }
                fclose($fccda);
            } else {
                $content = $row['ccda_data'];
            }

            return $content;
        }
    }

    private function getCcdaAsPdf($ccda)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->getCcdaAsHTML($ccda));
        $dompdf->render();
        return $dompdf->output();
    }

    public function getCcdaAsHTML($ccda)
    {
        $xml = simplexml_load_string($ccda);
        $xsl = new DOMDocument();
        // cda.xsl is self contained with bootstrap and jquery.
        // cda-web.xsl is used when referencing styles from internet.
        $xsl->load(__DIR__ . '/../../../../../public/xsl/cda.xsl');
        $proc = new XSLTProcessor();
        if (!$proc->importStyleSheet($xsl)) { // attach the xsl rules
            throw new \RuntimeException("CDA Stylesheet could not be found");
        }
        $outputFile = sys_get_temp_dir() . '/out_' . time() . '.html';
        $proc->transformToURI($xml, $outputFile);

        return file_get_contents($outputFile);
    }

    /*
     * Connect to a phiMail Direct Messaging server and transmit
     * a CCDA document to the specified recipient. If the message is accepted by the
     * server, the script will return "SUCCESS", otherwise it will return an error msg.
     * @param DOMDocument ccd the xml data to transmit, a CCDA document is assumed
     * @param string recipient the Direct Address of the recipient
     * @param string requested_by user | patient
     * @return string result of operation
     */
    public function transmitCcdToRecipients($data = array())
    {
        $appTable = new ApplicationTable();
        $ccda_combination = $data['ccda_combination'];
        $recipients = $data['recipients'];
        $xml_type = strtolower($data['xml_type'] ?? '');
        $rec_arr = explode(";", $recipients);
        $d_Address = '';
        // no point in continuing if we are not setup here
        $config_err = xl(ErrorConstants::MESSAGING_DISABLED) . " " . ErrorConstants::ERROR_CODE_ABBREVIATION . ":";
        if ($GLOBALS['phimail_enable'] == false) {
            return ("$config_err " . ErrorConstants::ERROR_CODE_MESSAGING_DISABLED);
        }

        if ($GLOBALS['phimail_verifyrecipientreceived_enable'] == '1') {
            $verifyMessageReceivedChecked = true;
        } else {
            $verifyMessageReceivedChecked = false;
        }

        try {
            foreach ($rec_arr as $recipient) {
                $elec_sent = array();
                $arr = explode('|', $ccda_combination);
                foreach ($arr as $value) {
                    $query = "SELECT id,transaction_id FROM  ccda WHERE pid = ? ORDER BY id DESC LIMIT 1";
                    $result = $appTable->zQuery($query, array($value));
                    // wierd foreach loop considering the limit 1 up above?
                    foreach ($result as $val) {
                        $ccda_id = $val['id'];
                        // gets connected at the time the ccda is created
                        $trans_id = $val['transaction_id'];
                    }

                    $elec_sent[] = array('pid' => $value, 'map_id' => $trans_id);

                    $documents = \Document::getDocumentsForForeignReferenceId('ccda', $ccda_id);
                    if (empty($documents[0])) {
                        throw new \RuntimeException("Cannot send document as document was not generated for ccda with ccda id " . $ccda_id);
                    }
                    $document = $documents[0];
                    $ccda = $document->get_data();
                    // use the filename that exists in the document for what is sent
                    $fileName = $document->get_name();
                    if (empty($ccda) || empty($fileName)) {
                        throw new \RuntimeException("Cannot send document as document data was empty or filename was empty for document with id "
                            . $document->get_id());
                    }

                    if ($xml_type == 'html') {
                        $ccda_file = $this->getCcdaAsHTML($ccda);
                    } elseif ($xml_type == 'pdf') {
                        $ccda_file = $this->getCcdaAsPdf($ccda);
                    } elseif ($xml_type == 'xml') {
                        $xml = simplexml_load_string($ccda);
                        $ccda_file = $xml->saveXML();
                    }
                    $replaceExt = "." . $xml_type;
                    $extpos = strrpos($fileName, ".xml");
                    if ($extpos !== false) {
                        $fileName = substr_replace($fileName, $replaceExt, $extpos, strlen($replaceExt));
                    }

                    // there is no way currently to specify this came from the patient so we force to clinician.
                    // Default xml type is CCD  (ie Continuity of Care Document)
                    $result = transmitCCD($value, $ccda_file, $recipient, 'clinician', "CCD", $xml_type, '', $fileName, $verifyMessageReceivedChecked);
                    if ($result !== "SUCCESS") {
                        $d_Address .= ' ' . $recipient . "(" . $result . ")";
                    }
                }
            }
        } catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['data' => $data]);
            return ("Delivery failed to send");
        }

        if ($d_Address == '') {
            foreach ($elec_sent as $elec) {
                // check to make sure its a valid ccda
                $collect = amcCollect('send_sum_valid_ccda', $elec['pid'], 'transactions', $elec['map_id']);
                // if the ccda is invalid we are not going to mark this as complete at all.
                if (!empty($collect)) {
                    amcAdd('send_sum_amc', true, $elec['pid'], 'transactions', $elec['map_id']);
                    amcAdd('send_sum_elec_amc', true, $elec['pid'], 'transactions', $elec['map_id']);
                    // when we use EMR Direct it ensures deliverability to the recipient so we automatically mark the ccda summary of care as confirmed
                    amcAdd('send_sum_elec_amc_confirmed', true, $elec['pid'], 'transactions', $elec['map_id']);
                }
            }

            return ("Successfully Sent");
        } else {
            return ("Delivery failed to send or was not allowed to:" . $d_Address);
        }
    }

    public function getFileID($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT cc.id, pd.fname, pd.lname, pd.pid FROM ccda AS cc
		    LEFT JOIN patient_data AS pd ON pd.pid=cc.pid
		    WHERE cc.pid = ?
		    ORDER BY cc.id DESC LIMIT 1";
        $res = $appTable->zQuery($query, array($pid));
        $res_cur = $res->current();
        return $res_cur;
    }

    /*
    * Save new user with abook type emr_direct
    *
    * @param    String      first name
    * @param    String      last name
    * @param    String      direct address
    *
    */
    public function AddNewUSer($data = array())
    {
        $fname = $data['fname'];
        $lname = $data['lname'];
        $direct_address = $data['direct_address'];
        $appTable = new ApplicationTable();
        $query = "INSERT INTO users SET username = ? ,password = ? ,authorized = ?,fname = ?,lname = ?,email = ?,active = ?,abook_type = ?";
        $appTable->zQuery($query, array('', '', 0, $fname, $lname, $direct_address, 1, 'emr_direct'));
    }
}
