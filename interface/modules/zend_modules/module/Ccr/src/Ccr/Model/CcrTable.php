<?php

/**
 * interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Ccr\Model;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Application\Model\ApplicationTable;
use Laminas\Db\Adapter\Driver\Pdo\Result;
use Laminas\XmlRpc\Generator;
use DOMDocument;
use DOMXpath;
use Document;
use CouchDB;

require_once(dirname(__FILE__) . "/../../../../../../../../library/patient.inc.php");

class CcrTable extends AbstractTableGateway
{
    public function __construct()
    {
    }
  /*
  * Fetch the Catagory ID from categories table
  *
  * @param    title    Text    Name of the category(eg: CCR)
  * @return   records  Array   ID of the Category
  */
    public function fetch_cat_id($title)
    {
        $appTable   = new ApplicationTable();
        $query      = "select * from categories where name = ?";
        $result     = $appTable->zQuery($query, array($title));
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * Fetch the documents uploaded by a user
  *
  * @param  user          Integer   Uploaded user ID
  * @param  time_start    Date      Uploaded start time
  * @param  time_end      Date      Uploaded end time
  *
  * @return records       Array     List of documents uploaded by the user during a particular time
  */
    public function fetch_uploaded_documents($data)
    {
        $query = "SELECT * FROM categories_to_documents AS cat_doc
            JOIN documents AS doc ON doc.id = cat_doc.document_id AND doc.owner = ? AND doc.date BETWEEN ? AND ?";
        $appTable   = new ApplicationTable();
        $result     = $appTable->zQuery($query, array($data['user'], $data['time_start'], $data['time_end']));
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * List the documents uploaded by the user alogn with the matched data
  *
  * @param    cat_title   Text    Category Name
  * @return   records     Array   List of CCR imported to the system, pending approval
  */
    public function document_fetch($data)
    {
        $query      = "SELECT am.id as amid, cat.name, u.fname, u.lname, d.imported, d.size, d.date, d.couch_docid, d.couch_revid, d.url AS file_url, d.id AS document_id, ad.field_value, ad1.field_value, ad2.field_value, pd.pid, CONCAT(ad.field_value,' ',ad1.field_value) as pat_name, DATE(ad2.field_value) as dob, CONCAT_WS(' ',pd.lname, pd.fname) as matched_patient
                FROM documents AS d
                JOIN categories AS cat ON cat.name = 'CCR'
                JOIN categories_to_documents AS cd ON cd.document_id = d.id AND cd.category_id = cat.id
                LEFT JOIN audit_master AS am ON am.type = '11' AND am.approval_status = '1' AND d.audit_master_id = am.id
                LEFT JOIN audit_details ad ON ad.audit_master_id = am.id AND ad.table_name = 'patient_data' AND ad.field_name = 'lname'
                LEFT JOIN audit_details ad1 ON ad1.audit_master_id = am.id AND ad1.table_name = 'patient_data' AND ad1.field_name = 'fname'
                LEFT JOIN audit_details ad2 ON ad2.audit_master_id = am.id AND ad2.table_name = 'patient_data' AND ad2.field_name = 'DOB'
                LEFT JOIN patient_data pd ON pd.lname = ad.field_value AND pd.fname = ad1.field_value AND pd.DOB = DATE(ad2.field_value)
                LEFT JOIN users AS u ON u.id = d.owner
                WHERE d.audit_master_approval_status = 1
                ORDER BY date DESC";
        $appTable   = new ApplicationTable();
        $result     = $appTable->zQuery($query);
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * Update the audit mater ID to documents table for future reference
  *
  * @param    audit_master_id   Integer   ID from audit_master table
  * @param    doc_id            Integer   ID from documents table
  */
    public function update_document($doc_id, $audit_master_id)
    {
        $appTable   = new ApplicationTable();
        $query = "UPDATE documents SET audit_master_id = ? WHERE id = ?";
        $appTable->zQuery($query, array($audit_master_id, $doc_id));
    }

  /*
  * Insert the imprted data to audit master table
  *
  * @param    var   Array   Details parsed from the CCR xml file
  * @return   audit_master_id   Integer   ID from audit_master table
  */
    public function insert_ccr_into_audit_data($var)
    {
        $appTable   = new ApplicationTable();
        $audit_master_id_to_delete  = $var['audit_master_id_to_delete'];
        $approval_status = $var['approval_status'];
        $type       = $var['type'];
        $ip_address = $var['ip_address'];
        $field_name_value_array     = $var['field_name_value_array'];
        $entry_identification_array = $var['entry_identification_array'];

        if ($audit_master_id_to_delete) {
            $qry  = "DELETE from audit_details WHERE audit_master_id=?";
            $appTable->zQuery($qry, array($audit_master_id_to_delete));

            $qry  = "DELETE from audit_master WHERE id=?";
            $appTable->zQuery($qry, array($audit_master_id_to_delete));
        }

        $master_query = "INSERT INTO audit_master SET pid = ?,approval_status = ?,ip_address = ?,type = ?";
        $result       = $appTable->zQuery($master_query, array(0,$approval_status,$ip_address,$type));
        $audit_master_id    = $result->getGeneratedValue();
        $detail_query = "INSERT INTO `audit_details` (`table_name`, `field_name`, `field_value`, `audit_master_id`, `entry_identification`) VALUES ";
        $detail_query_array = array();
        foreach ($field_name_value_array as $key => $val) {
            foreach ($field_name_value_array[$key] as $cnt => $field_details) {
                foreach ($field_details as $field_name => $field_value) {
                    $detail_query         .= "(? ,? ,? ,? ,?),";
                    $detail_query_array[] = $key;
                    $detail_query_array[] = trim($field_name);
                    $detail_query_array[] = trim($field_value);
                    $detail_query_array[] = $audit_master_id;
                    $detail_query_array[] = trim($entry_identification_array[$key][$cnt]);
                }
            }
        }

        $detail_query = substr($detail_query, 0, -1);
        $detail_query = $detail_query . ';';
        $appTable->zQuery($detail_query, $detail_query_array);
        return $audit_master_id;
    }

  /*
  * Library function to parse the CCR xml
  *
  * @param    content         XML     content from the CCR xml
  * @param    field_mapping   Array   fields to be fetched from xml
  */
    public function parseXmlStream($content, $field_mapping)
    {
        $res    = array();
        $xml    = new DOMDocument();
        $xml->loadXML($content);
        $xpath  = new DOMXpath($xml);
        $rootNamespace = $xml->lookupNamespaceUri($xml->namespaceURI);
        $xpath->registerNamespace('x', $rootNamespace);
        foreach ($field_mapping as $skey => $sval) {
            $path     = preg_replace("/\/([a-zA-Z])/", "/x:$1", $skey);
            $elements = $xpath->query($path);
            if (!is_null($elements)) {
                $ele_cnt = 1;
                foreach ($elements as $element) {
                    foreach ($sval as $field => $innerpath) {
                        $ipath  = preg_replace(array("/^([a-zA-Z])/","/\/([a-zA-Z])/"), array("x:$1","/x:$1"), $innerpath);
                        $val    = $xpath->query($ipath, $element)->item(0)->textContent;
                        if ($val) {
                            $field_details  = explode(':', $field);
                            $res[$field_details[0]][$ele_cnt][$field_details[1]] = $val;
                        }
                    }

                    $ele_cnt++;
                }
            }
        }

        return $res;
    }

  /*
  * Fetch the data from audit tables
  *
  * @param    am_id         integer     audit master ID
  * @param    table_name    string      identifier inserted for each table (eg: prescriptions, list1 ...)
  */
    public function createAuditArray($am_id, $table_name)
    {
        $appTable     = new ApplicationTable();
        if (strpos($table_name, ',')) {
            $tables     = explode(',', $table_name);
            $arr        = array($am_id);
            $table_qry  = "";
            for ($i = 0; $i < count($tables); $i++) {
                $table_qry .= "?,";
                array_unshift($arr, $tables[$i]);
            }

            $table_qry  = substr($table_qry, 0, -1);
            $query      = "SELECT * FROM audit_master am LEFT JOIN audit_details ad ON ad.audit_master_id = am.id AND ad.table_name IN ($table_qry)
                    WHERE am.id = ? AND am.type = 11 AND am.approval_status = 1 ORDER BY ad.entry_identification,ad.field_name";
            $result     = $appTable->zQuery($query, $arr);
        } else {
            $query      = "SELECT * FROM audit_master am LEFT JOIN audit_details ad ON ad.audit_master_id = am.id AND ad.table_name = ?
                    WHERE am.id = ? AND am.type = 11 AND am.approval_status = 1 ORDER BY ad.entry_identification,ad.field_name";
            $result     = $appTable->zQuery($query, array($table_name, $am_id));
        }

        $records = array();
        foreach ($result as $res) {
            $records[$table_name][$res['entry_identification']][$res['field_name']] = $res['field_value'];
        }

        return $records;
    }

  /*
  * Fetch the demographics data from audit tables
  *
  * @param    audit_master_id   Integer   ID from audit master table
  * @return   records           Array     Demographics data
  */
    public function getDemographics($data)
    {
        $appTable   = new ApplicationTable();
        $query      = "SELECT ad.id as adid, table_name, field_name, field_value FROM audit_master am JOIN audit_details ad ON ad.audit_master_id = am.id
                  WHERE am.id = ? AND ad.table_name = 'patient_data' ORDER BY ad.id";
        $result     = $appTable->zQuery($query, array($data['audit_master_id']));
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * Fetch the current demographics data of a patient from patient_data table
  *
  * @param    pid       Integer   Patient ID
  * @return   records   Array     current patient data
  */
    public function getDemographicsOld($data)
    {
        $appTable   = new ApplicationTable();
        $query      = "SELECT * FROM patient_data WHERE pid = ?";
        $result     = $appTable->zQuery($query, array($data['pid']));
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * Fetch the current Problems of a patient from lists table
  *
  * @param    pid       Integer     patient id
  * @return   records   Array       list of problems
  */
    public function getProblems($data)
    {
        $appTable   = new ApplicationTable();
        $query      = "SELECT * FROM lists WHERE pid = ? AND TYPE = 'medical_problem'";
        $result     = $appTable->zQuery($query, array($data['pid']));
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * Fetch the current Allergies of a patient from lists table
  *
  * @param    pid       Integer     patient id
  * @return   records   Array       list of allergies
  */
    public function getAllergies($data)
    {
        $appTable   = new ApplicationTable();
        $query      = "SELECT * FROM lists WHERE pid = ? AND TYPE = 'allergy'";
        $result     = $appTable->zQuery($query, array($data['pid']));
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * Fetch the current Medications of a patient from prescriptions table
  *
  * @param    pid       Integer     patient id
  * @return   records   Array       list of medications
  */
    public function getMedications($data)
    {
        $appTable   = new ApplicationTable();
        $query      = "SELECT * FROM prescriptions WHERE patient_id = ?";
        $result     = $appTable->zQuery($query, array($data['pid']));
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * Fetch the current Immunizations of a patient from immunizations table
  *
  * @param    pid       Integer     patient id
  * @return   records   Array       list of immunizations
  */
    public function getImmunizations($data)
    {
        $appTable   = new ApplicationTable();
        $query      = "SELECT * FROM immunizations WHERE patient_id = ?";//removed the field 'added_erroneously' from where condition
        $result     = $appTable->zQuery($query, array($data['pid']));
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * Fetch the currect Lab Results of a patient
  *
  * @param    pid       Integer     patient id
  * @return   records   Array       list of lab results
  */
    public function getLabResults($data)
    {
        $appTable   = new ApplicationTable();
        $query      = "SELECT * FROM procedure_order AS po LEFT JOIN procedure_order_code AS poc
                  ON poc.procedure_order_id = po.procedure_order_id LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id
                  LEFT JOIN procedure_result AS prs ON prs.procedure_report_id = pr.procedure_report_id WHERE patient_id = ?";
        $result     = $appTable->zQuery($query, array($data['pid']));
        $records    = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

  /*
  * Insert the CCR data to the respective tables after approval
  *
  * @param    data    Array     POST values from the approval screen which contains the patient id
  *                             and other details like demographics, problems etc..
  */
    public function insertApprovedData($data)
    {
        $appTable   = new ApplicationTable();
        $patient_data_fields = '';
        $patient_data_values = array();
        foreach ($data as $key => $val) {
            if (substr($key, -4) == '-sel') {
                if (is_array($val)) {
                    for ($i = 0; $i < count($val); $i++) {
                        if ($val[$i] == 'insert') {
                            if (substr($key, 0, -4) == 'lists1') {
                                if ($data['lists1-activity'][$i] == 'Active') {
                                    $activity = 1;
                                } elseif ($data['lists1-activity'][$i] == 'Inactive') {
                                    $activity = 0;
                                }

                                $query = "INSERT INTO lists (pid, diagnosis, activity, title, date, type) VALUES (?,?,?,?,?,?)";
                                $appTable->zQuery($query, array($data['pid'], $data['lists1-diagnosis'][$i], $activity, $data['lists1-title'][$i], \Application\Model\ApplicationTable::fixDate($data['lists1-date'][$i], 'yyyy-mm-dd', $GLOBALS['date_display_format']), 'medical_problem'));
                            } elseif (substr($key, 0, -4) == 'lists2') {
                                $query = "INSERT INTO lists (pid, date, type, title, diagnosis, reaction) VALUES (?,?,?,?,?,?)";
                                $appTable->zQuery($query, array($data['pid'], \Application\Model\ApplicationTable::fixDate($data['lists2-date'][$i], 'yyyy-mm-dd', $GLOBALS['date_display_format']), $data['lists2-type'][$i], $data['lists2-title'][$i], $data['lists2-diagnosis'][$i], $data['lists2-reaction'][$i]));
                            } elseif (substr($key, 0, -4) == 'prescriptions') {
                                if ($data['prescriptions-active'][$i] == 'Active') {
                                    $active = 1;
                                } elseif ($data['prescriptions-active'][$i] == 'Inactive') {
                                    $active = 0;
                                }

                                $query = "INSERT INTO prescriptions (patient_id, date_added, active, drug, size, form, quantity) VALUES (?,?,?,?,?,?,?)";
                                $appTable->zQuery($query, array($data['pid'], \Application\Model\ApplicationTable::fixDate($data['prescriptions-date_added'][$i], 'yyyy-mm-dd', $GLOBALS['date_display_format']),$active, $data['prescriptions-drug'][$i], $data['prescriptions-size'][$i], $data['prescriptions-form'][$i],$data['prescriptions-quantity'][$i]));
                            } elseif (substr($key, 0, -4) == 'immunizations') {
                                $query = "INSERT INTO immunizations (patient_id, administered_date, note) VALUES (?,?,?)";
                                $appTable->zQuery($query, array($data['pid'], \Application\Model\ApplicationTable::fixDate($data['immunizations-administered_date'][$i], 'yyyy-mm-dd', $GLOBALS['date_display_format']), $data['immunizations-note'][$i]));
                            }
                        } elseif ($val[$i] == 'update') {
                            if (substr($key, 0, -4) == 'lists1') {
                                if ($data['lists1-activity'][$i] == 'Active') {
                                    $activity = 1;
                                } elseif ($data['lists1-activity'][$i] == 'Inactive') {
                                    $activity = 0;
                                }

                                $query = "UPDATE lists SET diagnosis=?,activity=? WHERE pid=? AND diagnosis=?";
                                $appTable->zQuery($query, array($data['lists1-diagnosis'][$i], $activity, $data['pid'], $data['lists1-old-diagnosis'][$i]));
                            }
                        }
                    }
                } else {
                    if (substr($key, 0, 12) == 'patient_data') {
                        if ($val == 'update') {
                            $var_name = substr($key, 0, -4);
                            $field_name = substr($var_name, 13);
                            $patient_data_fields .= $field_name . '=?,';
                            array_push($patient_data_values, $data[$var_name]);
                        }
                    }
                }
            }
        }

        if (count($patient_data_values) > 0) {
            array_push($patient_data_values, $data['pid']);
            $patient_data_fields = substr($patient_data_fields, 0, -1);
            $query = "UPDATE patient_data SET $patient_data_fields WHERE pid=?";
            $appTable->zQuery($query, $patient_data_values);
        }

        $appTable->zQuery("UPDATE documents SET foreign_id = ? WHERE id =? ", array($data['pid'], $data['document_id']));
        $appTable->zQuery("UPDATE audit_master SET approval_status = '2' WHERE id=?", array($data['amid']));
        $appTable->zQuery("UPDATE documents SET audit_master_approval_status=2 WHERE audit_master_id=?", array($data['amid']));
    }

  /*
  * Reject the data obtained from the CCR xml
  *
  * @param    audit_master_id     Integer     id from audit master table
  */
    public function discardCCRData($data)
    {
        $appTable   = new ApplicationTable();
        $query = "UPDATE audit_master SET approval_status = '3' WHERE id=?";
        $appTable->zQuery($query, array($data['audit_master_id']));
        $appTable->zQuery("UPDATE documents SET audit_master_approval_status=2 WHERE audit_master_id=?", array($data['audit_master_id']));
    }

  /*
  * Fetch the patient data from audit tables and update to patient_data table
  *
  * @param    audit_master_id     Integer     id from audit master table
  */
    public function insert_patient($audit_master_id)
    {
        $pid = 0;
        $appTable   = new ApplicationTable();
        $pres       = $appTable->zQuery("SELECT IFNULL(MAX(pid)+1,1) AS pid FROM patient_data", array());
        foreach ($pres as $prow) {
            $pid      = $prow['pid'];
        }

        $res        = $appTable->zQuery("SELECT DISTINCT ad.table_name,entry_identification FROM audit_master as am,audit_details as ad WHERE am.id=ad.audit_master_id AND am.approval_status = '1' AND am.id=? AND am.type=11 ORDER BY ad.id", array($audit_master_id));
        $tablecnt   = $res->count();
        foreach ($res as $row) {
            $resfield = $appTable->zQuery("SELECT * FROM audit_details WHERE audit_master_id=? AND table_name=? AND entry_identification=?", array($audit_master_id,$row['table_name'],$row['entry_identification']));
            $table    = $row['table_name'];
            $newdata  = array();
            foreach ($resfield as $rowfield) {
                if ($table == 'patient_data') {
                    if ($rowfield['field_name'] == 'DOB') {
                        $newdata['patient_data'][$rowfield['field_name']] = substr($rowfield['field_value'], 0, 10);
                    } else {
                        $newdata['patient_data'][$rowfield['field_name']] = $rowfield['field_value'];
                    }
                } elseif ($table == 'lists1') {
                    $newdata['lists1'][$rowfield['field_name']]         = $rowfield['field_value'];
                } elseif ($table == 'lists2') {
                    $newdata['lists2'][$rowfield['field_name']]         = $rowfield['field_value'];
                } elseif ($table == 'prescriptions') {
                    $newdata['prescriptions'][$rowfield['field_name']]  = $rowfield['field_value'];
                } elseif ($table == 'immunizations') {
                    $newdata['immunizations'][$rowfield['field_name']]  = $rowfield['field_value'];
                } elseif ($table == 'procedure_result') {
                    $newdata['procedure_result'][$rowfield['field_name']]   = $rowfield['field_value'];
                } elseif ($table == 'procedure_type') {
                    $newdata['procedure_type'][$rowfield['field_name']]     = $rowfield['field_value'];
                } elseif ($table == 'misc_address_book') {
                    $newdata['misc_address_book'][$rowfield['field_name']]  = $rowfield['field_value'];
                } elseif ($table == 'documents') {
                    $newdata['documents'][$rowfield['field_name']]          = $rowfield['field_value'];
                }
            }

            if ($table == 'patient_data') {
                updatePatientData($pid, $newdata['patient_data'], true);
            } elseif ($table == 'lists1') {
                $query_insert = "INSERT INTO lists(pid, diagnosis, activity, title, type, date) VALUES (?,?,?,?,?,?)";
                $appTable->zQuery($query_insert, array($pid, $newdata['lists1']['diagnosis'], $newdata['lists1']['activity'], $newdata['lists1']['title'], 'medical_problem', $newdata['lists1']['date']));
            } elseif ($table == 'lists2' && $newdata['lists2']['diagnosis'] != '') {
                $query_insert = "INSERT INTO lists(pid,date,type,title,diagnosis,reaction) VALUES (?,?,?,?,?,?)";
                $appTable->zQuery($query_insert, array($pid, $newdata['lists2']['date'], $newdata['lists2']['type'], $newdata['lists2']['title'], $newdata['lists2']['diagnosis'], $newdata['lists2']['reaction']));
            } elseif ($table == 'prescriptions' && $newdata['prescriptions']['drug'] != '') {
                $query_insert = "INSERT INTO prescriptions(patient_id,date_added,active,drug,size,form,quantity) VALUES (?,?,?,?,?,?,?)";
                $appTable->zQuery($query_insert, array($pid, $newdata['prescriptions']['date_added'], $newdata['prescriptions']['active'], $newdata['prescriptions']['drug'], $newdata['prescriptions']['size'], $newdata['prescriptions']['form'], $newdata['prescriptions']['quantity']));
            } elseif ($table == 'immunizations') {
                $query_insert = "INSERT INTO immunizations(patient_id,administered_date,note) VALUES (?,?,?)";
                $appTable->zQuery($query_insert, array($pid, $newdata['immunizations']['administered_date'], $newdata['immunizations']['note']));
            } elseif ($table == 'documents') {
                $appTable->zQuery("UPDATE documents SET foreign_id = ? WHERE id =? ", array($pid, $newdata['documents']['id']));
            }
        }

        $appTable->zQuery("UPDATE audit_master SET approval_status=2 WHERE id=?", array($audit_master_id));
        $appTable->zQuery("UPDATE documents SET audit_master_approval_status=2 WHERE audit_master_id=?", array($audit_master_id));
    }

  /*
  * Fetch a document from the database
  *
  * @param  $document_id        Integer     Document ID
  * @return $content            String      File content
  */
    public function getDocument($document_id)
    {
        $content = \Documents\Plugin\Documents::getDocument($document_id);
        return $content;
    }

  /*
  * Update the status of a document after importing
  *
  * @param    $document_id    Interger    Document ID
  */
    public function update_imported($document_id)
    {
        $appTable   = new ApplicationTable();
        $appTable->zQuery("UPDATE documents SET imported = 1 WHERE id = ?", array($document_id));
    }
}
