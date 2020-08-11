<?php

/**
 * library/ccr_import_ajax.php Functions related to patient CCR/CCD/CCDA parsing.
 *
 * Functions related to patient CCR/CCD/CCDA parsing and insert/update to corresponding tables.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Ajil P M <ajilpm@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../parse_patient_xml.php");

use OpenEMR\Common\Csrf\CsrfUtils;

//verify csrf
if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if ($_REQUEST["ccr_ajax"] == "yes") {
    $doc_id = $_REQUEST["document_id"];
    $d = new Document($doc_id);
    $url =  $d->get_url();
    $storagemethod = $d->get_storagemethod();
    $couch_docid = $d->get_couch_docid();
    $couch_revid = $d->get_couch_revid();
    if ($storagemethod == 1) {
        $couch = new CouchDB();
        $resp = $couch->retrieve_doc($couch_docid);
        $content = $resp->data;
        if ($content == '' && $GLOBALS['couchdb_log'] == 1) {
            $log_content = date('Y-m-d H:i:s') . " ==> Retrieving document\r\n";
            $log_content = date('Y-m-d H:i:s') . " ==> URL: " . $url . "\r\n";
            $log_content .= date('Y-m-d H:i:s') . " ==> CouchDB Document Id: " . $couch_docid . "\r\n";
            $log_content .= date('Y-m-d H:i:s') . " ==> CouchDB Revision Id: " . $couch_revid . "\r\n";
            $log_content .= date('Y-m-d H:i:s') . " ==> Failed to fetch document content from CouchDB.\r\n";
            $log_content .= date('Y-m-d H:i:s') . " ==> Will try to download file from HardDisk if exists.\r\n\r\n";
            $this->document_upload_download_log($d->get_foreign_id(), $log_content);
            die(xlt("File retrieval from CouchDB failed"));
        }

        $content = base64_decode($content);
    } else {
        $url = preg_replace("|^(.*)://|", "", $url);
        $from_all = explode("/", $url);
        $from_filename = array_pop($from_all);
        $from_pathname_array = array();
        for ($i = 0; $i < $d->get_path_depth(); $i++) {
            $from_pathname_array[] = array_pop($from_all);
        }

        $from_pathname_array = array_reverse($from_pathname_array);
        $from_pathname = implode("/", $from_pathname_array);
        $temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $from_filename;
        if (!file_exists($temp_url)) {
            echo xlt('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it') . '.' . $temp_url;
        } else {
            $content = file_get_contents($temp_url);
        }
    }

  //fields to which the corresponding elements are to be inserted
  //format - level 1 key is the main tag in the XML eg:- //Problems or //Problems/Problem according to the content in the XML.
  //level 2 key is 'table name:field name' and level 2 value is the sub tag under the main tag given in level 1 key
  //eg:- 'Type/Text' if the XML format is '//Problems/Problem/Type/Text' or 'id/@extension' if it is an attribute
  //level 2 key can be 'table name:#some value' for checking whether a particular tag exits in the XML section
    $field_mapping = array(
    '//Problems/Problem' => array(
      'lists1:diagnosis' => 'Description/Code/Value',
      'lists1:comments' => 'CommentID',
      'lists1:activity' => 'Status/Text',
    ),
    '//Alerts/Alert' => array(
      'lists2:type' => 'Type/Text',
      'lists2:diagnosis' => 'Description/Code/Value',
      'lists2:date' => 'Agent/EnvironmentalAgents/EnvironmentalAgent/DateTime/ExactDateTime',
      'lists2:title' => 'Agent/EnvironmentalAgents/EnvironmentalAgent/Description/Text',
      'lists2:reaction' => 'Reaction/Description/Text',
    ),
    '//Medications/Medication' => array(
      'prescriptions:date_added' => 'DateTime/ExactDateTime',
      'prescriptions:active' => 'Status/Text',
      'prescriptions:drug' => 'Product/ProductName/Text',
      'prescriptions:size' => 'Product/Strength/Value',
      'prescriptions:unit' => 'Product/Strength/Units/Unit',
      'prescriptions:form' => 'Product/Form/Text',
      'prescriptions:quantity' => 'Quantity/Value',
      'prescriptions:note' => 'PatientInstructions/Instruction/Text',
      'prescriptions:refills' => 'Refills/Refill/Number',
    ),
    '//Immunizations/Immunization' => array(
      'immunizations:administered_date' => 'DateTime/ExactDateTime',
      'immunizations:note' => 'Directions/Direction/Description/Text',
    ),
    '//Results/Result' => array(
      'procedure_result:date' => 'DateTime/ExactDateTime',
      'procedure_type:name' => 'Test/Description/Text',
      'procedure_result:result' => 'Test/TestResult/Value',
      'procedure_result:range' => 'Test/NormalResult/Normal/Value',
      'procedure_result:abnormal' => 'Test/Flag/Text',
    ),
    '//Actors/Actor' => array(
      'patient_data:fname' => 'Person/Name/CurrentName/Given',
      'patient_data:lname' => 'Person/Name/CurrentName/Family',
      'patient_data:DOB' => 'Person/DateOfBirth/ExactDateTime',
      'patient_data:sex' => 'Person/Gender/Text',
      'patient_data:abname' => 'InformationSystem/Name',
      'patient_data:#Type' => 'InformationSystem/Type',
      'patient_data:pubpid' => 'IDs/ID',
      'patient_data:street' => 'Address/Line1',
      'patient_data:city' => 'Address/City',
      'patient_data:state' => 'Address/State',
      'patient_data:postal_code' => 'Address/PostalCode',
      'patient_data:phone_contact' => 'Telephone/Value',
    ),
    );
    if (!empty($content)) {
        $var = array();
        $res = parseXmlStream($content, $field_mapping);
        $var = array(
        'approval_status' => 1,
        'type' => 11,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        );
        foreach ($res as $sections => $details) {
            foreach ($details as $cnt => $vals) {
                foreach ($vals as $key => $val) {
                    if (array_key_exists('#Type', $res[$sections][$cnt])) {
                        if ($key == 'postal_code') {
                            $var['field_name_value_array']['misc_address_book'][$cnt]['zip'] = $val;
                        } elseif ($key == 'phone_contact') {
                              $var['field_name_value_array']['misc_address_book'][$cnt]['phone'] = $val;
                        } elseif ($key == 'abname') {
                              $values = explode(' ', $val);
                            if ($values[0]) {
                                $var['field_name_value_array']['misc_address_book'][$cnt]['lname'] = $values[0];
                            }

                            if ($values[1]) {
                                $var['field_name_value_array']['misc_address_book'][$cnt]['fname'] = $values[1];
                            }
                        } else {
                              $var['field_name_value_array']['misc_address_book'][$cnt][$key] = $val;
                        }

                        $var['entry_identification_array']['misc_address_book'][$cnt] = $cnt;
                    } else {
                        if ($sections == 'lists1' && $key == 'activity') {
                            if ($val == 'Active') {
                                $val = 1;
                            } else {
                                $val = 0;
                            }
                        }

                        if ($sections == 'lists2' && $key == 'type') {
                            if (strpos($val, "-")) {
                                $vals = explode("-", $val);
                                $val = $vals[0];
                            } else {
                                $val = "";
                            }
                        }

                        if ($sections == 'prescriptions' && $key == 'active') {
                            if ($val == 'Active') {
                                $val = 1;
                            } else {
                                $val = 0;
                            }
                        }

                            $var['field_name_value_array'][$sections][$cnt][$key] = $val;
                            $var['entry_identification_array'][$sections][$cnt] = $cnt;
                    }
                }

                if (array_key_exists('#Type', $var['field_name_value_array']['misc_address_book'][$cnt])) {
                      unset($var['field_name_value_array']['misc_address_book'][$cnt]['#Type']);
                }
            }
        }

        $var['field_name_value_array']['documents'][0]['id'] = $doc_id;
        insert_ccr_into_audit_data($var);
        $d->update_imported($doc_id);
        echo xlt('Successfully Imported the details. Please approve the patient from the Pending Approval Screen') . '.';
    } else {
        exit(xlt('Could not read the file'));
    }

    exit;
}
