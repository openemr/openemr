<?php

namespace OpenEMR\OemrAd;

@include_once(__DIR__ . "/../interface/globals.php");
@include_once($GLOBALS['srcdir']."/patient.inc");
@include_once($GLOBALS['srcdir']."/wmt-v2/wmtstandard.inc");
@include_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
@include_once($GLOBALS['srcdir']."/OemrAD/oemrad.globals.php");

use Mpdf\Mpdf;
use OpenEMR\OemrAd\MessagesLib;


/**
 * Apis Class
 */
class Attachment {

    public static function getFormEncountersById($pid, $encounter = '') {
        $results = array();

        $sql = '';
        if(!empty($encounter)) {
            $sql = ' AND forms.encounter = '. $encounter . ' ';
        }

        $res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, " .
                    "forms.formdir, forms.date AS fdate, form_encounter.date " .
                    ",form_encounter.reason, u.lname, u.fname, ".
            "CONCAT(fname, ' ', lname) AS drname ".
            "FROM forms, form_encounter LEFT JOIN users AS u ON ".
            "(form_encounter.provider_id = u.id) WHERE " .
                    "forms.pid = '$pid' AND form_encounter.pid = '$pid' AND " .
                    "form_encounter.encounter = forms.encounter " .
                    " AND forms.deleted=0 ". $sql .
                    "ORDER BY form_encounter.date $pat_rpt_order, form_encounter.encounter $pat_rpt_order, fdate ASC");

        while ($result = sqlFetchArray($res)) {
            $results[] = $result;
        }
        return $results;
    }

    // Get Encounter Form data for attachment selection
    public static function getEncounterFormDataForSelection($opts = array()) {
        $pid = isset($opts['pid']) ? $opts['pid'] : "";
        $formid = isset($opts['formid']) ? $opts['formid'] : array();

        $whereStr = array();

        if(!empty($pid)) {
            $whereStr[] = "forms.pid = '$pid' AND form_encounter.pid = '$pid'";
        }

        if(!empty($formid)) {
            $whereStr[] = "forms.id IN ('" . implode("','", $formid) ."')";
        }
        
        if(!empty($whereStr)) {
            $whereStr = implode(' AND ', $whereStr) . " AND ";
        } else {
            $whereStr = "";
        }

        $res = sqlStatement("SELECT forms.id, forms.encounter, forms.form_id, forms.form_name, forms.formdir, forms.date AS fdate, form_encounter.date ,form_encounter.reason, u.lname, u.fname, CONCAT(fname, ' ', lname) AS drname FROM forms, form_encounter LEFT JOIN users AS u ON (form_encounter.provider_id = u.id) WHERE $whereStr form_encounter.encounter = forms.encounter AND forms.deleted=0 ORDER BY form_encounter.date desc, form_encounter.encounter desc, fdate ASC");
        $enc = Utility::prepareEncounterReportListData($res);

        $res2 = sqlStatement("SELECT name FROM registry ORDER BY priority");
        $registry_form_name = array();
        while ($result2 = sqlFetchArray($res2)) {
            array_push($registry_form_name, trim($result2['name']));
        }


        $isfirst = 1;
        $prepared_data = array();
        $child_data = array();
        $current_formid = "";
        foreach ($enc as $key => $result) {
            if ($result{"form_name"} == "New Patient Encounter") {
                if ($isfirst == 0) {

                    //Prepare Data
                    foreach ($registry_form_name as $var) {
                        if ($toprint = $child_data[$var]) {
                            $prepared_data[$current_formid]['childs'][$var] = $toprint;
                        }
                    }

                    $child_data = array();
                    $current_formid = "";
                }

                $result['raw_text'] = $result{"reason"}. "(" . date("Y-m-d", strtotime($result{"date"})) . ") ". $result['drname'];

                $isfirst = 0;

                //PrepareData
                $current_formid = $result{"formdir"} . "_" .  $result{"form_id"};
                $prepared_data[$current_formid] = $result;
                $prepared_data[$current_formid]['childs'] = array();
            } else {
                $form_name = trim($result{"form_name"});
                //if form name is not in registry, look for the closest match by
                // finding a registry name which is  at the start of the form name.
                //this is to allow for forms to put additional helpful information
                //in the database in the same string as their form name after the name
                $form_name_found_flag = 0;
                foreach ($registry_form_name as $var) {
                    if ($var == $form_name) {
                        $form_name_found_flag = 1;
                    }
                }

                // if the form does not match precisely with any names in the registry, now see if any front partial matches
                // and change $form_name appropriately so it will print above in $toprint = $html_strings[$var]
                if (!$form_name_found_flag) {
                    foreach ($registry_form_name as $var) {
                        if (strpos($form_name, $var) == 0) {
                            $form_name = $var;
                        }
                    }
                }

                if (!is_array($child_data[$form_name])) {
                    $child_data[$form_name] = array();
                }
                array_push($child_data[$form_name], $result);
            }
        }

        //Prepare Data
        foreach ($registry_form_name as $var) {
            if ($toprint = $child_data[$var]) {
                if(!isset($prepared_data[$current_formid]['childs'][$var])) {
                    $prepared_data[$current_formid]['childs'][$var] = array();
                }
                $prepared_data[$current_formid]['childs'][$var] = $toprint;
            }
        }

        $jsonList = array();
        $attachmentList = array();
        foreach ($prepared_data as $pKey => $pData) {
            $prefix = "encf_";
            $parentId = $pData['id'];

            if(empty($formid) || in_array($parentId, $formid)) {
                //Prepare data
                $jsonList[$prefix . $parentId] = array(
                    "id" => $prefix . $parentId,
                    "pid" => $pid,
                    "text_title" => xl_form_title($pData{"raw_text"}),
                    "data" => array(
                        "formid" => $parentId ? $parentId : ""
                    )
                ); 

                $attachmentList[$pData["formdir"] ."_". $pData["form_id"]] = $pData["encounter"];
            }

            if(isset($pData['childs'])) {
                foreach ($pData['childs'] as $cKey => $cData) {
                    foreach ($cData as $c1Key => $c1Data) {
                        $childId = $c1Data['id'];

                        if(empty($formid) || in_array($childId, $formid)) {
                            if(!in_array($parentId, $formid)) {
                                //Prepare data
                                $jsonList[$prefix . $parentId] = array(
                                    "id" => $prefix . $parentId,
                                    "pid" => $pid,
                                    "text_title" => xl_form_title($pData{"raw_text"}),
                                    "data" => array(
                                        "formid" => $parentId ? $parentId : ""
                                    )
                                ); 

                                $attachmentList[$pData["formdir"] ."_". $pData["form_id"]] = $pData["encounter"];
                            }


                            $jsonList[$prefix . $childId] = array(
                                "id" => $prefix . $childId,
                                "pid" => $pid,
                                "parentId" => $parentId ? $prefix . $parentId : "",
                                "text_title" => xl_form_title($c1Data{"form_name"}),
                                "data" => array(
                                    "formid" => $childId ? $childId : "",
                                    "parentId" => $parentId ? $parentId : ""
                                )
                            );

                            $attachmentList[$c1Data["formdir"] ."_". $c1Data["form_id"]] = $c1Data["encounter"]; 
                        }
                    }
                }
            }
        }
        
        return array(
            "items" => $prepared_data,
            "json_items" => $jsonList,
            "attachment_list" => $attachmentList
        );
    }

    // Get Encounter data for attachment selection
    public static function getEncounterDataForSelection($opts = array()) {
        $pids = explode(";", $opts['pid']);
        $enc_id = isset($opts['enc_id']) ? $opts['enc_id'] : "";

        $binds = array();
        $whereStr  = array();

        if(!empty($pids)) {
            $wherePidStr  = "";
            if(is_array($pids)) {
                foreach ($pids as $value) {
                    if(!empty($value)) {
                        if(!empty($wherePidStr)) {
                            $wherePidStr .= "OR ";
                        }

                        $wherePidStr .= "fe.pid = ? ";
                        $binds[] = $value;
                    }
                }

                if(!empty($wherePidStr)) {
                    $wherePidStr = ' ('.$wherePidStr.') ';
                }
            } else {
                $wherePidStr  = "fe.pid = ? ";
                $binds[] = $pids;
            }

            if(!empty($wherePidStr)) {
                $whereStr[] = $wherePidStr;
            }
        }

        if(!empty($enc_id)) {
            $whereStr[] = "fe.encounter IN ('" . implode("','", $enc_id) ."')";
        }
        
        if(!empty($whereStr)) {
            $whereStr = implode(' AND ', $whereStr);
        } else {
            $whereStr = "";
        }

        $result4 = sqlStatement("SELECT fe.id, fe.encounter, fe.pid, fe.date, openemr_postcalendar_categories.pc_catname, us.fname, us.mname, us.lname FROM form_encounter AS fe left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid left join users AS us on fe.provider_id = us.id  WHERE ".$whereStr." order by fe.date desc", $binds);

        $list = array();
        $jsonList = array();

        while ($rowresult4 = sqlFetchArray($result4)) {
            $encounter = isset($rowresult4['encounter']) ? $rowresult4['encounter'] : '';
            $eData = false;

            if(!empty($encounter)) {
                $eData = sqlQuery("SELECT FE.encounter, E.id, E.tid, E.table, E.uid, U.fname, U.lname, E.datetime, E.is_lock, E.amendment, E.hash, E.signature_hash FROM form_encounter FE LEFT JOIN esign_signatures E ON (case when E.`table` ='form_encounter' then FE.encounter = E.tid else  FE.id = E.tid END) LEFT JOIN users U ON E.uid = U.id WHERE FE.encounter = ? ORDER BY E.datetime ASC", array($encounter));
            }

            if($eData !== false && isset($eData['is_lock']) && $eData['is_lock'] == '1') {
                $rowresult4['signed'] = true;
            } else {
                $rowresult4['signed'] = false;
            }

            $cCat = isset($rowresult4['pc_catname']) ? $rowresult4['pc_catname'] : '';
            $pName = trim($rowresult4['fname'].' '.$rowresult4['mname'].' '.$rowresult4['lname']);
            if(!empty($pName)) {
                $pName = ' - '.$pName;
            }

            $signed = $rowresult4['signed'] === true ? 'Signed' : 'Unsigned';
            if(!empty($signed)) {
                $signed = ' - '.$signed.'';
            }

            $dateFormat = MessagesLib::getCurrentDateFormat();

            $rowresult4['enc_date'] = isset($rowresult4['date']) ? date($dateFormat, strtotime($rowresult4['date'])) : '';
            $rowresult4['encounter_title'] = trim($cCat.$pName.$signed);

            $list[] = $rowresult4;

            //Prepare data
            $jsonList['enc_' . $rowresult4['id']] = array(
                "id" => "enc_" . $rowresult4['id'],
                "pid" => $rowresult4['pid'],
                "text_title" => $rowresult4['enc_date'] . " " . $rowresult4['encounter_title'],
                "data" => array(
                    "encounter_id" => $rowresult4['encounter'] ? $rowresult4['encounter'] : ""
                )
            );
        }

        return array(
            "items" => $list,
            "json_items" => $jsonList
        );
    }

    // Get Document data for attachment selection
    public static function getDocumentDataForSelection($opts = array()) {
        $pid = explode(";", $opts['pid']);
        $doc_id = $opts['doc_id'];
    
        $binds = array();
        $whereStr  = array();
    
        if(!empty($pid)) {
            $wherePidStr  = "";
    
            if(is_array($pid)) {
                foreach ($pid as $value) {
                    if(!empty($value)) {
                        if(!empty($wherePidStr)) {
                            $wherePidStr .= "OR ";
                        }
    
                        $wherePidStr .= "d.foreign_id = ? ";
                        $binds[] = $value;
                    }
                }
    
                if(!empty($wherePidStr)) {
                    $wherePidStr = ' ('.$wherePidStr.') ';
                }
            } else {
                $wherePidStr  = "d.foreign_id = ? ";
                $binds[] = $pid;
            }
    
            if(!empty($wherePidStr)) {
                $whereStr[] = $wherePidStr;
            }
        }
    
        if(!empty($doc_id)) {
            $whereStr[] = "d.id IN ('" . implode("','", $doc_id) ."')";
        }
        
        if(!empty($whereStr)) {
            $whereStr = implode(' AND ', $whereStr);
        } else {
            $whereStr = "";
        }
    
        $query = "SELECT d.id, d.type, d.size, d.url, d.docdate, d.list_id, d.encounter_id, d.foreign_id, c.name FROM documents AS d, categories_to_documents AS cd, categories AS c WHERE " . $whereStr ." AND cd.document_id = d.id AND c.id = cd.category_id ";
        $query .= "ORDER BY d.docdate DESC, d.id DESC";
        $dres = sqlStatement($query, $binds);
    
        $list = array();
        $jsonList = array();
        while ($drow = sqlFetchArray($dres)) {
            $drow['baseName'] = basename($drow['url']) . ' (' . xl_document_category($drow['name']). ')';
            $drow['baseFileName'] = '(' . xl_document_category($drow['name']). ')' . basename($drow['url']);
            $drow['issue'] = MessagesLib::getAssociatedIssue($drow['list_id']);
            $drow['pid'] = $drow['foreign_id'];
            $list[] = $drow;
    
            //Prepare data
            $jsonList['doc_' . $drow['id']] = array(
                "id" => "doc_" . $drow['id'],
                "pid" => $drow['pid'],
                "text_title" => $drow['baseName'] ? $drow['baseName'] : "",
                "data" => array(
                    "doc_id" => $drow['id'] ? $drow['id'] : ""
                )
            ); 
        }
    
        return array(
            "items" => $list,
            "json_items" => $jsonList
        );
    }

    // Get Order data for attachment selection
    public static function getOrderDataForSelection($opts = array(), $selectCol = '*', $columnName = '', $columnSortOrder = '', $limit = '', $rowperpage = '') {
        $recordsList = self::getRawOrderDataForSelection($opts, $selectCol, $columnName, $columnSortOrder, $limit, $rowperpage);

        $list = array();
        $jsonList = array();
        foreach ($recordsList as $i => $ritem) {
            $itemTitle = ListLook($ritem['rto_action'],'RTO_Action');
            if(!empty($ritem['rto_status'])) {
                $itemTitle .= ' - '.ListLook($ritem['rto_status'],'RTO_Status');
            }
          
            $patient_id = $ritem['pid'];
            $patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
            $patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));
                $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

            $ritem['patient_name'] = $patientName;
            $ritem['patient_DOB'] = $patientDOB;
            $ritem['pubpid'] = $patientData['pubpid'];

            $ritem['title'] = $itemTitle;       
            $ritem['rto_action'] = ListLook($ritem['rto_action'],'RTO_Action');
            $ritem['rto_ordered_by'] = UserNameFromName($ritem['rto_ordered_by']);
            $ritem['rto_status'] = ListLook($ritem['rto_status'],'RTO_Status');
            $ritem['rto_resp_user'] = !empty($ritem['rto_resp_user']) ? UserNameFromName($ritem['rto_resp_user']) : '';

            $ritem['row_select'] = $ritem['id'];
            $ritem['row_select'] = '<input type="checkbox" class="checkboxes itemCheck" data-title="'.addslashes($itemTitle).'" id="order_'.$ritem['id'].'" data-pid="'.$ritem['pid'].'" data-patientdob="'.$ritem['patient_DOB'].'" data-patientname="'.$ritem['patient_name'].'" data-pubpid="'.$ritem['pubpid'].'" value="'.$ritem['id'].'">';

            $list[] = $ritem;
            $jsonList['order_' . $ritem['id']] = array(
                    "id" => "order_" . $ritem['id'],
                    "pid" => $patient_id,
                    "text_title" => $itemTitle,
                    "data" => array(
                        "order_id" => $ritem['id'] ? $ritem['id'] : ""
                    )
                );
        }

        return array(
            "items" => $list,
            "json_items" => $jsonList
        );
    }

    // Get Order data for attachment selection
    public static function getRawOrderDataForSelection($opts = array(), $selectCol = '*', $columnName = '', $columnSortOrder = '', $limit = '', $rowperpage = '') {
        $pid = explode(";", $opts['pid']);
        $order_id = $opts['order_id'];

        $binds = array();
        $whereStr  = array();

        if(empty($pid)) {
            $wherePidStr  = "";

            if(is_array($pid)) {
                foreach ($pid as $value) {
                    if(!empty($value)) {
                        if(!empty($wherePidStr)) {
                            $wherePidStr .= "OR ";
                        }

                        $wherePidStr .= "fr.pid = ? ";
                        $binds[] = $value;
                    }
                }

                if(!empty($wherePidStr)) {
                    $wherePidStr = ' ('.$wherePidStr.') ';
                }
            } else {
                $wherePidStr  = "fr.pid = ? ";
                $binds[] = $pid;
            }

            if(!empty($wherePidStr)) {
                $whereStr[] = $wherePidStr;
            }
        }

        if(!empty($order_id)) {
            $whereStr[] = "fr.id IN ('" . implode("','", $order_id) ."')";
        }
        
        if(!empty($whereStr)) {
            $whereStr = "WHERE " . implode(' AND ', $whereStr);
        } else {
            $whereStr = "";
        }

        $query = "SELECT ".$selectCol." FROM form_rto as fr " . $whereStr;

        if(!empty($columnName) && !empty($columnSortOrder)) {
            $query .= " ORDER BY ".$columnName." ".$columnSortOrder;
        }

        if((!empty($limit) || $limit >= 0) && !empty($rowperpage)) {
            $query .= " LIMIT ".$limit." , ".$rowperpage;
        }

        $dres = sqlStatement($query, $binds);

        $list = array();
        while ($drow = sqlFetchArray($dres)) {
            $list[] = $drow;
        }

        return $list;
    }

    // Get Message data for attachment selection
    public static function getMessageDataForSelection($opts = array(), $selectCol = '*', $columnName = '', $columnSortOrder = '', $limit = '', $rowperpage = '') {
        $recordsList = self::getRawMessageDataForSelection($opts, $selectCol, $columnName, $columnSortOrder, $limit, $rowperpage);

        $list = array();
        $jsonList = array();
        foreach ($recordsList as $i => $row) {
            $patient_id = $row['pid'];
            $patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");

            $patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));
            $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

            $row['patient_name'] = $patientName;
            $row['patient_DOB'] = $patientDOB;
            $row['pubpid'] = $patientData['pubpid'];
            $row['pid'] = $patient_id;

            $name = $row['user'];
            $name = $row['users_lname'];
            if ($row['users_fname']) {
                $name .= ", " . $row['users_fname'];
            }
            if(empty($name)) $name = $row['user'];
                        $msg_to = $row['msg_to_lname'];
            if ($row['msg_to_fname']) {
                $msg_to .= ", " . $row['msg_to_fname'];
            }

            $patient = $row['pid'];
            if ($patient > 0) {
                $patient = $row['patient_data_lname'];
                if ($row['patient_data_fname']) {
                    $patient .= ", " . $row['patient_data_fname'];
                }
            } else {
                $patient = "* " . xlt('Patient must be set manually') . " *";
            }

            $row['user_fullname'] = $name;
            $row['msg_to'] = $msg_to;
            $row['patient_fullname'] = $patient;
            $row['link_title'] = '('.$row['id'].') '.$row['user_fullname'].' - '.$row['msg_to'].' - '.$row['patient_fullname'].' - '.$row['message_status'].' - '.text(oeFormatShortDate(substr($row['date'], 0, strpos($row['date'], " "))));
            $row['date'] = text(oeFormatShortDate(substr($row['date'], 0, strpos($row['date'], " "))));

            $row['row_select'] = $row['id'];
            //$row['row_select'] = '<input type="checkbox" class="checkboxes itemCheck" data-title="'.addslashes($row['link_title']).'" data-pid="'.$row['pid'].'" data-patientdob="'.$row['patient_DOB'].'" data-patientname="'.$row['patient_name'].'" data-pubpid="'.$row['pubpid'].'"  id="order_'.$row['id'].'" value="'.$row['id'].'">';

            $list[] = $row;
            $jsonList['message_' . $row['id']] = array(
                    "id" => "message_" . $row['id'],
                    "pid" => $patient_id,
                    "text_title" => $row['link_title'],
                    "data" => array(
                        "message_id" => $row['id'] ? $row['id'] : ""
                    )
                );
        }

        return array(
            "items" => $list,
            "json_items" => $jsonList
        );
    }

    // Get Message data for attachment selection
    public static function getRawMessageDataForSelection($opts = array(), $selectCol = '*', $columnName = '', $columnSortOrder = '', $limit = '', $rowperpage = '') {
        $pid = explode(";", $opts['pid']);
        $user = $opts['user'];
        $message_id = $opts['message_id'];


        $binds = array(1);
        $whereStr  = array();

        $wherePidStr  = "";
        if(!empty($pid)) {
            if(is_array($pid)) {
                foreach ($pid as $value) {
                    if(!empty($value)) {
                        if(!empty($wherePidStr)) {
                            $wherePidStr .= "OR ";
                        }

                        $wherePidStr .= "pnotes.pid = ? ";
                        $binds[] = $value;
                    }
                }

                if(!empty($wherePidStr)) {
                    $wherePidStr = ' ('.$wherePidStr.') ';
                }
            } else {
                $wherePidStr  = "pnotes.pid = ? ";
                $binds[] = $pid;
            }
        }

        if(!empty($user)) {
            $wherePidStr .= " AND pnotes.user = ? ";
            $binds[] = $user;
        }

        if(!empty($wherePidStr)) {
            $whereStr[] = $wherePidStr;
        }

        if(!empty($message_id)) {
            $whereStr[] = "pnotes.id IN ('" . implode("','", $message_id) ."')";
        }
        
        if(!empty($whereStr)) {
            $whereStr = implode(' AND ', $whereStr);
        } else {
            $whereStr = "";
        }

        $query = 'SELECT '.$selectCol.' FROM pnotes LEFT JOIN users AS u ON pnotes.user = u.username LEFT JOIN users AS msg_to ON msg_to.username = pnotes.assigned_to AND pnotes.assigned_to != "" LEFT JOIN list_options ON (SUBSTRING(pnotes.assigned_to,5) = list_options.option_id AND list_options.list_id = "Messaging_Groups") LEFT JOIN patient_data ON pnotes.pid = patient_data.pid WHERE pnotes.deleted != ? AND '.$whereStr.'';

        //$query = "SELECT ".$selectCol." FROM form_rto as fr WHERE ".$wherePidStr;

        if(!empty($columnName) && !empty($columnSortOrder)) {
            $query .= " ORDER BY ".$columnName." ".$columnSortOrder;
        }

        if((!empty($limit) || $limit >= 0) && !empty($rowperpage)) {
            $query .= " LIMIT ".$limit." , ".$rowperpage;
        }

        $result = sqlStatement($query, $binds);

        $messageList = array();
        while ($row = sqlFetchArray($result)) {
            $messageList[] = $row;
        }

        return $messageList;
    }

    /*Get Demos Ins list by pid*/
    public static function getDemosInsDataForSelection($opts = array()) {
        $pid = isset($opts['pid']) ? $opts['pid'] : "";
        $i_id = isset($opts['id']) ? $opts['id'] : "";

        $whereStr = array();
        $binds = array($pid);
        
        if(!empty($pid)) {
            $whereStr[] = "form_cases.pid = ?";
            $binds[] = $pid;
        }

        if(!empty($i_id)) {
            $whereStr[] = "form_cases.id IN ('" . implode("','", $i_id) ."')";
        }

        if(!empty($whereStr)) {
            $whereStr = "AND " . implode(' AND ', $whereStr);
        } else {
            $whereStr = "";
        }

        $sql = 'SELECT form_cases.*, (SELECT COUNT(*) FROM case_appointment_link AS ca LEFT JOIN openemr_postcalendar_events AS oe ON (ca.pc_eid = oe.pc_eid) WHERE pid = ? AND oe.pc_case = form_cases.id) AS enc_count FROM form_cases WHERE ';
        if($type == 'active') $sql .= 'closed = 0 AND ';
        $sql .= 'activity > 0 '. $whereStr .' ORDER BY id DESC';

        $res = sqlStatement($sql, $binds);

        $list = array();
        $jsonList = array();
        while($row = sqlFetchArray($res)) {
            $jsonIns = array();
            $insData = array();

            //Prepare data
            $jsonList['di_' . $row['id']] = array(
                "id" => "di_" . $row['id'],
                "pid" => $row['pid'],
                "text_title" => "Case: ". $row['id'],
                "data" => array(
                    "id" => $row['id'] ? $row['id'] : ""
                )
            );

            for($i=1; $i<=3; $i++) {
                if(isset($row['ins_data_id'.$i]) && !empty($row['ins_data_id'.$i])) {
                    if(empty($row['ins_data_id'.$i])) {
                        continue;
                    }

                    $insObj = self::getInsuranceDataById($row['pid'], $row['ins_data_id'.$i]);
                    
                    //Please unset 'uuid'
                    unset($insObj[0]['uuid']);
                    
                    $row['ins_data'][] = isset($insObj) ? $insObj[0] : array();
                    

                    $jsonList['di_' . $row['id'] . '_' . $insObj[0]['id'] . '_' . $i] = array(
                        "id" => 'di_' . $row['id'] . '_' . $insObj[0]['id'] . '_' . $i,
                        "pid" => $row['pid'],
                        "text_title" => $insObj[0]['name'],
                        "data" => array(
                            "id" => $insObj[0]['id'] ? $insObj[0]['id'] : ""
                        )
                    );
                }
            }

            $list[] = $row; 
        }

        return array(
            "items" => $list,
            "json_items" => $jsonList
        );
    }

    /* Get Insurance related data from datatable by passing different parameters */
    public static function getInsuranceDataById($pid, $ins_id, $provider_id = '', $order_by = '`date` DESC', $type = '') {
            if(!$pid || !$ins_id) {
                return false;
            }
            $binds = array();
            $query = 'SELECT ins.*, ic.`id` AS ic_id, ic.`name`, ic.`attn`, ic.`cms_id`, ic.`alt_cms_id`, ic.`ins_type_code`, ad.`line1`, ad.`line2`,  '.
                'ad.`city`, ad.`state`, ad.`zip`, ad.`plus_four`, ph.`area_code`, '.
                'ph.`prefix`, ph.`number`';
            if($provider_id) {
                $query .= ', us.`id` AS pr_id, us.`fname` AS pr_fname, us.`lname` AS pr_lname, us.`federaltaxid` AS pr_federaltaxid, us.`upin` AS pr_upin, us.`npi` AS pr_npi, us.`facility_id` AS pr_facility_id';
            }   

            $query .= ' FROM insurance_data AS ins '.
                'LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` '.
                'LEFT JOIN phone_numbers AS ph ON '.
                '(ic.`id` = ph.`foreign_id` AND ph.`type` = 2) '.
                'LEFT JOIN addresses AS ad ON (ic.`id` = ad.`foreign_id`) ';
            
            if($provider_id) {
                $query .= 'LEFT JOIN users AS us ON us.`id` = ? ';
                $binds[] = $provider_id;
            }

            $query .= ' WHERE ins.`id` = ? AND ins.`pid` = ? '; 
            $binds[] = $ins_id;
            $binds[] = $pid;
            if($type) {
                $query .= ' AND ins.`type` = ? ';
                $binds[] = $type;
            }
            $query .= 'AND ins.`provider` IS NOT NULL AND ins.`provider` > 0 '.
                'AND ins.`date` IS NOT NULL AND ins.`date` != "0000-00-00" '.
                'AND ins.`date` != "" ';
            $query .= 'ORDER BY ' . $order_by;
            
            
            $fres = sqlStatement($query, $binds);
            $data = array();
            while($row = sqlFetchArray($fres)) {
                $data[] = $row;
            }
            return $data;       
    }

    // Prepare message attachment data.
    public static function prepareMessageAttachment($items) {
        global $webserver_root;

        // Reparepare data for old message attachment data.
        $items = self::prepareOldMessageAttachmentData($items);

        foreach ($items as $iType => $iItem) {
            if($iType === "documents") {
                $docIds = array();
                foreach ($iItem as $dKey => $dItem) {
                    if(isset($dItem['doc_id']) && !in_array($dItem['doc_id'], $docIds)) {
                        $docIds[] = $dItem['doc_id'];
                    }
                }

                if(empty($docIds)) continue;

                $pData = self::getDocumentDataForSelection(array('doc_id' => $docIds));
                $items[$iType] = isset($pData['json_items']) ? array_values($pData['json_items']) : array();
            } else if($iType === "encounter_forms") {
                $formIds = array();
                foreach ($iItem as $efKey => $efItem) {
                    if(isset($efItem['formid']) && !in_array($efItem['formid'], $formIds)) {
                        $formIds[] = $efItem['formid'];
                    }
                }

                if(empty($formIds)) continue;

                $pData = self::getEncounterFormDataForSelection(array('formid' => $formIds));
                $items[$iType] = isset($pData['json_items']) ? array_values($pData['json_items']) : array();
            } else if($iType === "encounters") {
                $encIds = array();
                foreach ($iItem as $eKey => $eItem) {
                    if(isset($eItem['encounter_id']) && !in_array($eItem['encounter_id'], $encIds)) {
                        $encIds[] = $eItem['encounter_id'];
                    }
                }

                if(empty($encIds)) continue;

                $pData = array();
                if(!empty($encIds)) {
                    $pData = self::getEncounterDataForSelection(array('enc_id' => $encIds));
                }

                $items[$iType] = isset($pData['json_items']) ? array_values($pData['json_items']) : array();
            } else if($iType === "orders") {
                $orderIds = array();
                foreach ($iItem as $oKey => $oItem) {
                    if(isset($oItem['order_id']) && !in_array($oItem['order_id'], $orderIds)) {
                        $orderIds[] = $oItem['order_id'];
                    }
                }

                if(empty($orderIds)) continue;

                $pData = array();
                if(!empty($orderIds)) {
                    $pData = self::getOrderDataForSelection(array('order_id' => $orderIds), 'fr.*');
                }

                $items[$iType] = isset($pData['json_items']) ? array_values($pData['json_items']) : array();
            } else if($iType === "messages") {
                $msgIds = array();
                foreach ($iItem as $mKey => $mItem) {
                    if(isset($mItem['message_id']) && !in_array($mItem['message_id'], $msgIds)) {
                        $msgIds[] = $mItem['message_id'];
                    }
                }

                if(empty($msgIds)) continue;

                $pData = array();
                if(!empty($msgIds)) {
                    $pData = self::getMessageDataForSelection(array('message_id' => $msgIds), 'pnotes.id, pnotes.user, pnotes.pid, pnotes.title, pnotes.date, pnotes.message_status, pnotes.assigned_to, list_options.option_id, IF(pnotes.user != pnotes.pid, u.fname, patient_data.fname) AS users_fname, IF(pnotes.user != pnotes.pid, u.lname, patient_data.lname) AS users_lname, IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.title, msg_to.lname), patient_data.lname) AS msg_to_lname, IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.notes, msg_to.fname), patient_data.fname) AS msg_to_fname, patient_data.fname AS patient_data_fname, patient_data.lname AS patient_data_lname, CONCAT( patient_data.lname, " ", patient_data.fname ) AS patient_fullname, CONCAT( IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.title, msg_to.lname), patient_data.lname), " ", IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", list_options.notes, msg_to.fname), patient_data.fname) ) AS msg_to, CONCAT( IF(pnotes.user != pnotes.pid, u.lname, patient_data.lname), " ", IF(pnotes.user != pnotes.pid, u.fname, patient_data.fname) ) AS user_fullname ');
                }

                $items[$iType] = isset($pData['json_items']) ? array_values($pData['json_items']) : array();
            } else if($iType === "local_files") {
                $lFiles =array();
                foreach ($iItem as $lfKey => $lfItem) {
                    $file_path = isset($lfItem['file_path']) ? $lfItem['file_path'] : "";
                    $file_full_path = $webserver_root . $file_path;

                    $tItems = array(
                        "file_full_path" => $file_full_path,
                        "data" => $lfItem
                    );
                    $lFiles[] = $tItems;
                }
                
                $items[$iType] = $lFiles;
            } else if($iType === "demos_insurances") {
                $diItems =array();
                $caseIds = array();

                foreach ($iItem as $lfKey => $lfItem) {
                    $caseIds[] = $lfItem['id'];
                }

                if(empty($caseIds)) continue;

                $pData = array();
                if(!empty($caseIds)) {
                    $pData = self::getDemosInsDataForSelection(array('id' => $caseIds));
                    $jsonData = isset($pData['json_items']) ? $pData['json_items'] : array();

                    foreach ($iItem as $lfKey => $lfItem) {
                        $iKey = "di_" . $lfItem['id'];
                        if(!isset($jsonData[$iKey])) {
                            continue;
                        }
                        $diItems[$iKey] = $jsonData[$iKey];
                        $diItems[$iKey]['childs'] = array();
                        $diItems[$iKey]['data']['childs'] = array();
                        $iChildData = isset($lfItem['childs']) ? $lfItem['childs'] : array();

                        for($i=1; $i<=3;$i++) {
                            $nKey = 'cnt'.$i;

                            if(isset($iChildData[$nKey]) && isset($iChildData[$nKey]['id'])) {
                                if(empty($iChildData[$nKey]['id'])) {
                                    continue;
                                }

                                $nChildKey = "di_" . $lfItem['id'] . "_" . $iChildData[$nKey]['id'] . "_" . $i;
                                
                                if(!isset($jsonData[$nChildKey])) {
                                    continue;
                                }

                                $diItems[$iKey]['childs'][$nKey] = $jsonData[$nChildKey];
                                $diItems[$iKey]['data']['childs'][$nKey] = $iChildData[$nKey];                              
                            }
                        }

                        if(count($diItems[$iKey]['childs']) <= 0) {
                            unset($diItems[$iKey]);
                        }
                        
                    }
                }

                $items[$iType] = array_values($diItems);
            }
            
        }

        return $items;
    }

    // Prepare message attachment data.
    public static function prepareOldMessageAttachmentData($items) {
        $preparedItems = array();
        $typeList = array(
            "selectedEncounterList" => "encounter_forms",
            "selectedDocuments" => "documents",
            "selectedOrder" => "orders",
            "selectedNotes" => "notes",
            "selectedEncounterIns" => "demos_insurances",
            "uploadFileList" => "local_files",
            "checkEncounterInsDemo" => "demoins_inc_demographic"
        );

        foreach ($items as $iType => $iItem) {
            if (isset($typeList[$iType])) {
                // Get new type
                $nType = $typeList[$iType];
                
                if($iType == "selectedEncounterList") {
                    // Prepare Encounter & Form Data
                    $encfData = array();
                    $formIdMaping = array();

                    //Prepare new data.
                    foreach ($iItem as $dataKey => $dataItem) {
                        $dataKeyArray = explode("_", $dataKey);
                        $form_id = end($dataKeyArray) != "" ? end($dataKeyArray) : "";
                        array_pop($dataKeyArray);
                        $formdir = implode("_", $dataKeyArray);
                        $encounter = isset($dataItem['value']) ? $dataItem['value'] : "";

                        if(empty($encounter) || empty($form_id)) {
                            continue;
                        }

                        $whereStr = " forms.form_id = '$form_id' AND forms.formdir = '$formdir' AND ";
                        if($form_id != $encounter) {
                            $whereStr .= "forms.encounter = '$encounter' AND ";
                        } 

                        $res = sqlQuery("SELECT forms.id, forms.encounter, forms.form_id, forms.form_name, forms.formdir, forms.date AS fdate, form_encounter.date ,form_encounter.reason, u.lname, u.fname, CONCAT(fname, ' ', lname) AS drname FROM forms, form_encounter LEFT JOIN users AS u ON (form_encounter.provider_id = u.id) WHERE " . $whereStr . " form_encounter.encounter = forms.encounter AND forms.deleted=0 ORDER BY form_encounter.date desc, form_encounter.encounter desc, fdate ASC");

                        if(empty($res['id'])) {
                            continue;
                        }

                        $formIdMaping[$dataKey] = $res['id'];

                        $encfItem = array(
                            'formid' => $res['id']
                        );

                        if(isset($dataItem['parentId'])) $encfItem['parentId'] = $dataItem['parentId'];

                        $encfData[] = $encfItem;
                    }

                    // Correct parent id of item.
                    foreach ($encfData as $encfk => $encfi) {
                        if(isset($encfData[$encfk]['parentId'])) {
                            $encfData[$encfk]['parentId'] = $formIdMaping[$encfData[$encfk]['parentId']];
                        }
                    }

                    //Set new data.
                    $preparedItems[$nType] = $encfData;

                } else if($iType == "selectedDocuments") {
                    //Prepare Document data
                    $docData = array();

                    //Prepare new data.
                    foreach ($iItem as $dataKey => $dataItem) {
                        $docData[] = array(
                            'doc_id' => $dataItem['id']
                        );
                    }

                    //Set new data.
                    $preparedItems[$nType] = $docData;
                } else if($iType == "selectedOrder") {
                    //Prepare Order data
                    $orderData = array();

                    //Prepare new data.
                    foreach ($iItem as $dataKey => $dataItem) {
                        $orderData[] = array(
                            'order_id' => $dataItem['id']
                        );
                    }

                    //Set new data.
                    $preparedItems[$nType] = $orderData;
                } else if($iType == "uploadFileList") {
                    //Prepare Document data
                    $filesData = array();

                    //Prepare new data.
                    foreach ($iItem as $dataKey => $dataItem) {
                        $filesData[] = array(
                            'type' => $dataItem['action'],
                            'file_path' => $dataItem['url'],
                            'file_name' => trim($dataItem['file_name']),
                        );
                    }

                    //Set new data.
                    $preparedItems[$nType] = $filesData;
                } else if($iType == "selectedEncounterIns") {
                    //Prepare Document data
                    $diData = array();

                    //Prepare new data.
                    foreach ($iItem as $dataKey => $dataItem) {
                        $nData = array('id' => $dataItem['id'], 'childs' => array());
                        for($i=1; $i<=3;$i++) {
                            $nKey = 'cnt'.$i;
                            if(isset($dataItem[$nKey])) {
                                $nData['childs'][$nKey] = array('id' => $dataItem[$nKey]['id']);
                            }
                        }

                        $diData[] = $nData;
                    }

                    //Set new data.
                    $preparedItems[$nType] = $diData;
                } else if($iType == "checkEncounterInsDemo") {
                    //Set new data.
                    $preparedItems[$nType] = $iItem;
                }

            } else {
                $preparedItems[$iType] = $iItem;
            }
        }

        return $preparedItems;
    }

    // Assign msg to user
    public static function assignUserToMSG($msgId) {
        if(!empty($msgId)) {
            sqlStatementNoLog("UPDATE `message_log` SET assigned = userid WHERE id = ?", array($msgId));
        }
    }

    // Write attached document log
    public function writeMessageDocumentLog($id, $type, $file_name, $url, $attachid = '') {
        // Create log entry
        $binds = array();
        $binds[] = $id;
        $binds[] = $type;
        $binds[] = $file_name;
        $binds[] = $url;

        // Store log record
        $sql = "INSERT INTO `message_attachments` SET ";
        $sql .= "message_id=?, type=?,  file_name=?, url=? ";

        if(!empty($attachid)) {
            $sql .= ", doc_id=? ";
            $binds[] = $attachid;
        }

        return sqlInsert($sql, $binds);
    }

    // Save email attachment file on to server
    public static function saveAttachmentFile($attachmentList, $move = true) {
        $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";

        if (!file_exists($file_location)) {
            mkdir($file_location, 0777, true);
        }

        foreach ($attachmentList as $key => $attachment) {
            $filename = $attachment['name'];
            $fullfilepath = $file_location . strtotime(date('Y-m-d H:i:s')) . "_" . self::cleanStr($filename);
            
            if(isset($attachment['attachment'])) {
                if(!file_exists($fullfilepath)) {
                        $fp = fopen($fullfilepath, "w+");
                        fwrite($fp, $attachment['attachment']);
                        fclose($fp);

                        $attachmentList[] = array(
                            'type' => 'file_url',
                            'path' => $fullfilepath,
                            'name' => $filename
                        );
                }
            }
        }

        return $attachmentList;
    }

    public static function updateRequest($attachmentList, $request_data) {

        $tLocalFiles = array();
        foreach ($attachmentList as $ak => $aItem) {
            if($aItem['action'] == 'upload') {

                $sUrl = str_replace($GLOBALS["webserver_root"], "", $aItem['path']);
                $file_url = substr($sUrl, strpos($sUrl, "/sites/"));

                $tLocalFiles[] = array(
                    'type' => 'upload',
                    'file_path' => $file_url,
                    'file_name'=> $aItem['name']
                );
            }
        }

        if(isset($request_data['local_files'])) {
            $tmpl = array_merge(json_decode($request_data['local_files'], true) , $tLocalFiles);
            $request_data['local_files'] = json_encode($tmpl);
        }

        return $request_data;
    }

    // Prepare Attachment file and notes for email content (Attachment)
    public static function prepareAttachment($pid, $request, $files) {
        try {
            $arrayTypeList = array("local_files", "documents", "notes", "orders", "encounter_forms", "demos_insurances");
            $attachmentList = array();
            $attachList = array();

            // Files
            if(isset($files['files'])) {
                $attachList['files_length'] = $request['files_length'];
                $attachList['files'] = $files['files'];
            }

            foreach ($arrayTypeList as $typeKey => $typeItem) {
                if(isset($request[$typeItem])) {
                    $attachList[$typeItem] = json_decode($request[$typeItem], true);
                }
            }

            if(isset($attachList['demos_insurances']) && !empty($attachList['demos_insurances'])) {
                $caseInsHtmlContent = self::generateCaseHTML($attachList['demos_insurances']);
                $demoHtmlContent = self::incDemographicsAttachment($pid, array(), $request['demoins_inc_demographic']);
                
                if($demoHtmlContent != "" && $request['demoins_inc_demographic'] !== "true") {
                    $bodyHtml = self::mergerHTMLContent($demoHtmlContent, $caseInsHtmlContent);
                } else {
                    $bodyHtml = $demoHtmlContent;
                }

                $bodyHtml = self::setHeaderFooterOfHtml('H', $bodyHtml, self::getPdfHeaderContent($pid));
                $bodyHtml = self::setHeaderFooterOfHtml('F', $bodyHtml, '');

                $demosInsurancesPDF = self::getAndSavePDF(array(
                    'filename' => 'demos_and_ins',
                    'content' => $bodyHtml,
                    'pdf_top_margin' => 3,
                    'pdf_bottom_margin' => 3
                ));

                //$email->AddAttachment($demosInsurancesPDF['path'], $demosInsurancesPDF['name']);
                $attachmentList[] = array(
                    'action' => 'stay',
                    'type' => 'demos_insurances',
                    'path' => $demosInsurancesPDF['path'],
                    'name' => $demosInsurancesPDF['name'],
                    'page_count' => isset($demosInsurancesPDF['page_count']) ? $demosInsurancesPDF['page_count'] : 0
                );
            }

            if(isset($attachList['encounter_forms']) && !empty($attachList['encounter_forms'])) {
                $formIds = array();
                foreach ($attachList['encounter_forms'] as $efKey => $efItem) {
                    if(isset($efItem['formid']) && !in_array($efItem['formid'], $formIds)) {
                        $formIds[] = $efItem['formid'];
                    }
                }

                $encFormData = self::getEncounterFormDataForSelection(array('formid' => $formIds));
                $demoHtmlContent = self::incDemographicsAttachment($pid, $encFormData['attachment_list'], $request['encform_inc_demographic']);

                //$bodyHtml = self::mergerHTMLContent($demoHtmlContent, $encFormData);
                $bodyHtml = $demoHtmlContent;
                $bodyHtml = self::setHeaderFooterOfHtml('H', $bodyHtml, self::getPdfHeaderContent($pid));
                $bodyHtml = self::setHeaderFooterOfHtml('F', $bodyHtml, '');

                $encounterFormsPDF = self::getAndSavePDF(array(
                    'pid' => $pid,
                    'filename' => 'encounters_and_forms',
                    'content' => $bodyHtml,
                    'pdf_top_margin' => 3,
                    'pdf_bottom_margin' => 3
                ));

                //$email->AddAttachment($encounterFormsPDF['path'], $encounterFormsPDF['name']);
                $attachmentList[] = array(
                    'action' => 'stay',
                    'type' => 'encounter_forms',
                    'path' => $encounterFormsPDF['path'],
                    'name' => $encounterFormsPDF['name'],
                    'page_count' => isset($encounterFormsPDF['page_count']) ? $encounterFormsPDF['page_count'] : 0
                );
            }

            if(isset($attachList['orders']) && !empty($attachList['orders'])) {
                $orderHtmlContent = self::generateOrderDataAttachment($attachList['orders']);

                $bodyHtml = $orderHtmlContent;
                $bodyHtml = self::setHeaderFooterOfHtml('H', $bodyHtml, self::getPdfHeaderContent($pid));
                $bodyHtml = self::setHeaderFooterOfHtml('F', $bodyHtml, '');

                $orderPDF = self::getAndSavePDF(array(
                    'pid' => $pid,
                    'filename' => 'orders',
                    'content' => $bodyHtml,
                    'pdf_top_margin' => 2.5,
                    'pdf_bottom_margin' => 2.5
                ));

                //$email->AddAttachment($orderPDF['path'], $orderPDF['name']);
                $attachmentList[] = array(
                    'action' => 'stay',
                    'type' => 'orders',
                    'path' => $orderPDF['path'],
                    'name' => $orderPDF['name'],
                    'page_count' => isset($orderPDF['page_count']) ? $orderPDF['page_count'] : 0
                );
            }

            if(isset($attachList['local_files'])) {
                foreach ($attachList['local_files'] as $key => $fileItem) {
                    if($fileItem['type'] == "file_url" || $fileItem['type'] == "upload") {
                        $filePath = str_replace("file://","",$fileItem['file_path']);
                        //$email->AddAttachment($filePath, $fileItem['file_name']);
                        
                        $fileItem['ignore'] = true;
                        $fileItem['type'] = 'local_files';
                        $fileItem['path'] = $GLOBALS['fileroot'] . $filePath;
                        $fileItem['name'] = $fileItem['file_name'];
                        $fileItem['page_count'] = (end(explode('.', $fileItem['file_name'])) == "pdf") ? self::getPdf($fileItem['path']) : 1;

                        $attachmentList[] = $fileItem;
                    }
                }
            }

            if(isset($attachList['files']) && isset($attachList['files_length'])) {
                for ($i=0; $i < $attachList['files_length'] ; $i++) {
                    //$email->AddAttachment($attachList['files']['tmp_name'][$i], $attachList['files']['name'][$i]);
                    $fullfilepath = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/" . strtotime(date('Y-m-d H:i:s')) . "_" . self::cleanStr($attachList['files']['name'][$i]);

                    // move upload file
                    move_uploaded_file($attachList['files']['tmp_name'][$i], $fullfilepath);

                    $attachmentList[] = array(
                        'action' => 'upload',
                        'type' => 'local_files',
                        'path' => $fullfilepath,
                        'name' => $attachList['files']['name'][$i],
                        'page_count' => (end(explode('.', $attachList['files']['name'][$i])) == "pdf") ? self::getPdf($fullfilepath) : 1
                    );
                }
            }


            if(isset($attachList['documents']) && isset($attachList['documents'])) {
                $docIds = array();
                foreach ($attachList['documents'] as $dKey=> $dItem) {
                    if(isset($dItem['doc_id']) && !in_array($dItem['doc_id'], $docIds)) {
                        $docIds[] = $dItem['doc_id'];
                    }
                }

                $pData = self::getDocumentDataForSelection(array('doc_id' => $docIds));

                foreach ($pData['items'] as $pikey => $docObj) {
                    if($docObj['type'] == "file_url") {
                        $filePath = str_replace("file://","",$docObj['url']);
                        //$email->AddAttachment($filePath, $docObj['baseFileName']);

                        $attachmentList[] = array(
                            'action' => 'stay',
                            'type' => 'documents',
                            'path' => $filePath,
                            'name' => $docObj['baseFileName'],
                            'id' => isset($docObj['id']) ? $docObj['id'] : '',
                            'page_count' => 1
                        );
                    }
                }
            }

            if(isset($attachList['attachment_files']) && is_array($attachList['attachment_files'])) {
                foreach ($attachList['attachment_files'] as $key => $doc) {
                    //$email->AddAttachment($doc['url'], $doc['name']);
                    $doc['type'] = 'attachment_files';
                    $doc['page_count'] = (end(explode('.', $doc['name'])) == "pdf") ? self::getPdf($doc['path']) : 1;

                    $attachmentList[] = $doc;
                }
            }

            // generate mime boundry
            $outer_boundary = md5(time());
            $inner_boundary = md5(time()+100);

            if(isset($attachList['notes']) && !empty($attachList['notes'])) {
                $noteStr = "";
                $noteStr .= '<h1>Internal Notes</h1><ul style="padding-left:15px; font-size:16px;">';
                $nCounter = 1;

                foreach ($attachList['notes'] as $key => $note) {
                    $noteObj = (array)$note;
                    $noteStr .= "<li>".preg_replace("/[\r\n]/", "\n   ", strip_tags($noteObj['note_id']))."</li>";
                    $nCounter++;
                }

                $notesPDF = self::generateAttachmentPDF($noteStr, 'internal_notes', true);
                //$email->AddAttachment($notesPDF['path'], $notesPDF['name']);

                $attachmentList[] = array(
                    'action' => 'stay',
                    'type' => 'notes',
                    'path' => $notesPDF['path'],
                    'name' => $notesPDF['name'],
                    'page_count' => isset($notesPDF['page_count']) ? $notesPDF['page_count'] : 0
                );
            }



        } catch(\Throwable $e) {
            throw new \Exception($e->getMessage()); 
        }

        return $attachmentList;
    }

    // Clear attachment file (Attachment).
    public static function clearAttachmentFile($attachmentList = array()) {
        try {
            $typeList = array("demos_insurances", "encounter_forms", "orders", "files", "fax");
            foreach ($attachmentList as $aKey => $aItem) {
                if(isset($aItem['type']) && !empty($aItem['type'])) {
                    if (in_array($aItem['type'], $typeList)) {
                        if (file_exists($aItem['path'])) {
                            unlink($aItem['path']);
                                unset($attachmentList[$aKey]);
                        }
                    }
                }
            }
        } catch(\Throwable $e) {
            throw new \Exception($e->getMessage()); 
        }

        return $attachmentList;
    }

    // GenerateCase HTML (Attachment)
    public static function generateCaseHTML($data = array()) {
        $htmlStr = "<style>.ins_datatable{border:1px solid #000!important;border-collapse: collapse; margin-right:5px;} .insheadercell, .inscell {padding:8px;border:1px solid #000!important;} .childTable{width:100%} .insheadercell, .cinsheadercell{text-align: left;} .cinscell, .cinsheadercell { padding:5px;} .cinsheadercell {border-bottom:1px solid #fff;text-align:left;}.headerRow{background-color:#FFFBEB;}.insRow{background-color:#E0E0E0;}.cinscell{text-align:left;}.insurance{margin-top:20px;}.insuranceData, .subscriberData{padding-right:10px;}</style>";

        if(!empty($data)) {
            $htmlStr .= "<div class='text insurance'>";
            $htmlStr .= "<h4>".xl('Insurance Data').":</h4>";
            $htmlStr .= "<table class='ins_datatable' style='width:100%;'>";
            
            foreach ($data as $dKey => $dItem) {
                $case_id = isset($dItem['id']) ? $dItem['id'] : '';

                if(empty($case_id)) {
                    continue;
                }

                $insData = self::getCaseListById($case_id);
                if(isset($insData)) {
                    if(isset($insData[0]['cash']) && $insData[0]['cash'] == '1') {
                        $htmlStr .= "<tr>";
                        $htmlStr .= '<td colspan="7" class="cinscell" style="border: 1px solid #000;">';
                        $htmlStr .= '<b>SelfPay</b>';
                        $htmlStr .= '</td>';
                        $htmlStr .= "</tr>";
                        continue;
                    }

                    $insObj = array();
                    $dItemChilds = isset($dItem['childs']) ? $dItem['childs'] : array();

                    for($i =1;$i<=3;$i++) {
                        if(isset($dItem['childs'])) {
                            $cntObj = $dItemChilds['cnt'.$i];
                            $cnt_id = $cntObj['id'];
                            foreach ((array)$insData[0]['ins_data'] as $ki => $ins_data) {
                                if($ins_data['id'] == $cnt_id) {
                                    $insObj[] = $ins_data;
                                    break;
                                }
                            }
                        }
                    }

                    if(!empty($insObj)) {
                        foreach ($insObj as $key => $obj) {
                            $subName = $obj['subscriber_fname']." ".$obj['subscriber_lname'];
                            if(!empty($obj['subscriber_relationship'])) {
                                $subName .= " (".$obj['subscriber_relationship'].")";
                            }

                            $sub_address = array();
                            if(!empty($obj['subscriber_city'])) {
                                $sub_address[] = $obj['subscriber_city'];
                            }

                            if(!empty($obj['subscriber_state'])) {
                                $sub_address[] = $obj['subscriber_state'];
                            }

                            if(!empty($obj['subscriber_country'])) {
                                $sub_address[] = $obj['subscriber_country'].' '.$obj['subscriber_postal_code'];
                            }

                            $htmlStr .= "<tr>";
                            $htmlStr .= '<td colspan="7" class="cinscell" style="border: 1px solid #000;">';
                            $htmlStr .= '<table class="insContainer">';
                            $htmlStr .= '<tr>';
                                $htmlStr .= '<td class="insuranceData" valign="top" width="250">';
                                    $htmlStr .= '<div><h4>Patient Data:</h4></div>';
                                    $htmlStr .= '<span>'.$obj['name'].'</span><br/>';
                                    $htmlStr .= '<span>Policy Number: '.$obj['policy_number'].'</span><br/>';
                                    $htmlStr .= '<span>Plan Name: '.$obj['plan_name'].'</span><br/>';
                                    $htmlStr .= '<span>Group Number: '.$obj['group_number'].'</span><br/>';
                                    $htmlStr .= '<span>Effective Date: '.$obj['date'].'</span><br/>';
                                $htmlStr .= '</td>';
                                $htmlStr .= '<td class="subscriberData" valign="top" width="180">';
                                    $htmlStr .= '<div><b>Subscriber</b></div>';
                                    $htmlStr .= '<span>'.trim($subName).'</span><br/>';
                                    $htmlStr .= '<span>S.S.: '.$obj['subscriber_ss'].'</span><br/>';
                                    $htmlStr .= '<span>D.O.B.: '.$obj['subscriber_DOB'].'</span><br/>';
                                    $htmlStr .= '<span>Phone: '.$obj['subscriber_phone'].'</span><br/>';
                                $htmlStr .= '</td>';
                                $htmlStr .= '<td class="subscriberAddrData" valign="top">';
                                    $htmlStr .= '<div><b>Subscriber Address</b></div>';
                                    $htmlStr .= '<span>'.$obj['subscriber_street'].'</span><br/>';
                                    $htmlStr .= '<span>'.implode(", ", $sub_address).'</span><br/>';
                                $htmlStr .= '</td>';
                            $htmlStr .= '</tr>';
                            $htmlStr .= '</table>';
                            $htmlStr .= '</td>';
                            $htmlStr .= "</tr>";
                        }
                    }
                }
            }

            $htmlStr .= "</table>";
            $htmlStr .= "</div>";
        }

        return $htmlStr;
    }

    // Get Case list by pid (Attachment)
    public static function getCaseListById($id = '') {
        $cases = array();
        $sql = 'SELECT form_cases.*, (SELECT COUNT(*) FROM case_appointment_link AS ca LEFT JOIN openemr_postcalendar_events AS oe ON (ca.pc_eid = oe.pc_eid) WHERE pid = form_cases.pid AND oe.pc_case = form_cases.id) AS enc_count FROM form_cases WHERE ';
        if($type == 'active') $sql .= 'closed = 0 AND ';

        if(!empty($id)) {
            $sql .= "id = $id AND ";
        }

        $sql .= 'activity > 0 ORDER BY id DESC';
        $res = sqlStatement($sql, array());

        while($row = sqlFetchArray($res)) {
            for($i=1; $i<=3; $i++) {
                if(isset($row['ins_data_id'.$i]) && !empty($row['ins_data_id'.$i])) {
                    // Fetch Insurance data.
                    $binds = array();
                    $insquery = 'SELECT ins.*, ic.`id` AS ic_id, ic.`name`, ic.`attn`, ic.`cms_id`, ic.`alt_cms_id`, ic.`ins_type_code`, ad.`line1`, ad.`line2`, ad.`city`, ad.`state`, ad.`zip`, ad.`plus_four`, ph.`area_code`, ph.`prefix`, ph.`number` FROM insurance_data AS ins LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` LEFT JOIN phone_numbers AS ph ON (ic.`id` = ph.`foreign_id` AND ph.`type` = 2) LEFT JOIN addresses AS ad ON (ic.`id` = ad.`foreign_id`) ';
                    $insquery .= '  WHERE ins.`id` = ? ';
                    $insquery .= 'AND ins.`provider` IS NOT NULL AND ins.`provider` > 0 AND ins.`date` IS NOT NULL AND ins.`date` != "0000-00-00" AND ins.`date` != "" ORDER BY `date` DESC';

                    $binds[] = $row['ins_data_id'.$i];
                    $fres = sqlStatement($insquery, $binds);
                    $insObj = array();
                    while($insrow = sqlFetchArray($fres)) {
                        $insObj[] = $insrow;
                    }

                    $row['ins_data'][] = isset($insObj) ? $insObj[0] : array();
                }
            }

            $cases[] = $row;
        }

        return $cases;
    }

    // Get And Save Encounter PDF (Attachment)
    public static function getAndSavePDF($opt = array()) {
        $pid = isset($opt['pid']) ? $opt['pid'] : "";
        $content = isset($opt['content']) ? $opt['content'] : "";
        $filename = isset($opt['filename']) ? $opt['filename'] : "";
        $pData = (isset($pid) && !empty($pid)) ? self::getPatientDataAttachment($pid) : array();

        $pdf_top_margin = isset($opt['pdf_top_margin']) ? $opt['pdf_top_margin'] : 1.5;
        $pdf_bottom_margin = isset($opt['pdf_bottom_margin']) ? $opt['pdf_bottom_margin'] : 1.5;

        // We want to overwrite so only one PDF is stored per form/encounter
        $config_mpdf = array(
            'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
            'mode' => $GLOBALS['pdf_language'],
            'format' => $GLOBALS['pdf_size'],
            'default_font_size' => '9',
            'default_font' => 'dejavusans',
            'margin_left' => $GLOBALS['pdf_left_margin'],
            'margin_right' => $GLOBALS['pdf_right_margin'],
            'margin_top' => $GLOBALS['pdf_top_margin'] * $pdf_top_margin,
            'margin_bottom' => $GLOBALS['pdf_bottom_margin'] * $pdf_bottom_margin,
            'margin_header' => $GLOBALS['pdf_top_margin'],
            'margin_footer' => $GLOBALS['pdf_bottom_margin'],
            'orientation' => $GLOBALS['pdf_layout'],
            'shrink_tables_to_fit' => 1,
            'use_kwt' => true,
            'keep_table_proportions' => true
        );

        $pdfE = new mPDF($config_mpdf);
        //$pdfE->shrink_tables_to_fit = 1;
        //$keep_table_proportions = true;
        //$pdfE->use_kwt = true;
        //$pdfE->setDefaultFont('dejavusans');
        //$pdfE->autoScriptToLang = true;
        //$pdfE->setAutoTopMargin = "stretch";
        //$pdfE->setAutoBottomMargin = "stretch";

        $col = $GLOBALS['wmt::use_email_direct'] ? 'email_direct' : 'email';
        
        if(!empty($pData)) {
            $pdfE->SetHTMLHeader('<table style="width: 100%;"><tr><td style="text-align:left;">Patient ID: '.$pData['pubpid'].'</td><td style="text-align:center;">DOB: '.$pData['DOB'].'</td><td style="text-align:right;">Name: '.($pData['fname'].' '.$pData['lname']).'</td></tr></table>');
        }

        $tmpc = $content;
        //$tmpc = self::replaceHTMLTags($tmpc, Array("html","head","body"));

        /*Added CSS File*/
        //$tmpc .= '<link rel="stylesheet" href="'.$GLOBALS['webroot'].'/interface/themes/style_pdf.css">';
        //$tmpc .= '<link rel="stylesheet" type="text/css" href="'.$GLOBALS['webroot'].'/library/ESign/css/esign_report.css" />';

        /*Save File*/
        $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
        $fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_". $filename . ".pdf";

        $pdfE->writeHTML($tmpc);

        $pageCount = $pdfE->page;
        $content_pdf = $pdfE->Output($fullfilename, 'F');

        return array(
            'path' => $fullfilename,
            'name' => $filename . ".pdf",
            'page_count' => $pageCount
        );
    }

    public static function generateCustomReport($data = array()) {
        global $web_root;
        $siteUrl = sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            $web_root
        );

        $data['SESSION_DATA'] = $_SESSION;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $siteUrl . '/interface/main/messages/ajax/req_custom_report.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RESOLVE, [ 'localhost:80:127.0.0.1']);

        $error_msg = curl_error($ch);
        $server_output = curl_exec($ch);

        curl_close($ch);

        return $server_output;
    }

    // Get Demographics content (Attachment)
    public static function incDemographicsAttachment($pid, $queryData, $includeDemo = false) {
        global $srcdir, $web_root, $css_header, $doNotPrintField;

        //$temp_pdf_output = $GLOBALS['pdf_output'];

        $temp_post = $_POST;
        unset($_POST);

        $temp_get = $_GET;
        unset($_GET);

        $_POST['pdf'] = "1";

        if($includeDemo == "true") {
            $_POST['include_demographics'] = "demographics";
        } else {
            $_POST['include_demographics'] = "demographics";

            /*foreach ($options as $oi => $option) {
                $_POST[$oi] = $option;
            }*/

            foreach ($queryData as $key => $value) {
                $_POST[$key] = $value;
            }
        }

        ob_start();
        echo self::generateCustomReport($_POST);
        $cReportData = ob_get_clean();

        /*
        $GLOBALS['pdf_output'] = "S";

        //Change Dir
        $currentDir = getcwd();
        chdir($GLOBALS['fileroot'].'/interface/patient_file/report/');

        $doNotPrintField = true;

        ob_start();
        include $GLOBALS['fileroot'].'/interface/patient_file/report/custom_report.php' ;
        $f = ob_get_clean();

        $doNotPrintField = false;

        //Set Original Dir
        chdir($currentDir);
        */
        
        $_POST = $temp_post;
        $_GET = $temp_get;

        //$GLOBALS['pdf_output'] = $temp_pdf_output;

        //return $content;
        return $cReportData;
    }

    // Generate order data attachment
    public static function generateOrderDataAttachment($orders) {
        global $doNotPrintField;

        $orderIds = array();

        foreach ($orders as $ok => $orderItem) {
            if(isset($orderItem['order_id']) && !empty($orderItem['order_id'])) {
                $orderIds[] = $orderItem['order_id'];
            }
        }

        $rtos = self::getAllRTOAttachment($orderIds);

        ob_start();
        ?>
        <style type="text/css">
            .orderTable {
                border:1px solid #000!important;
                border-collapse: collapse;
                width: 100%;
            }

            .orderTable .cellHeader,
            .orderTable .cell,
            .orderTable .cell1 {
                padding:8px;
                text-align: left;
                border:1px solid #000!important;
            }

            .headerRow{
                background-color:#FFFBEB;
            }
            .insRow{
                background-color:#E0E0E0;
            }

        </style>
        <center><h1 style='font-size:15px;'><?php echo xl('Order Fulfillment') ?></h1></center>
        <table class="orderTable">
            <!-- <tr class="headerRow">
                <th class="cellHeader">Order</th>
                <th class="cellHeader">Order By</th>
                <th class="cellHeader">Status</th>
                <th class="cellHeader">Assigned To</th>
                <th class="cellHeader">Date</th>
            </tr> -->
            <?php
                foreach ($rtos as $key => $rto) {
                    $rtoData = getRtoLayoutFormData($pid, $rto['id']);
                    $layoutData = getLayoutForm($rto['rto_action']);
                    ?>
                    <!-- <tr class="insRow">
                        <td class="cell"><?php //echo ListLook($rto['rto_action'],'RTO_Action'); ?></td>
                        <td class="cell"><?php //echo UserNameFromName($rto['rto_ordered_by']); ?></td>
                        <td class="cell"><?php //echo ListLook($rto['rto_status'],'RTO_Status'); ?></td>
                        <td class="cell"><?php //echo !empty($rto['rto_resp_user']) ? UserNameFromName($rto['rto_resp_user']) : ''; ?></td>
                        <td class="cell"><?php //echo $rto['date']; ?></td>
                    </tr> -->
                    <?php
                    if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
                        $formname = $layoutData['grp_form_id'];
                        $form_id = $rtoData['form_id'];

                        ?>
                        <tr class="insRow1" >
                            <td colspan="5" class="cell1">
                            <?php self::generateOrderItemDetails($rto); ?>
                            <span><b>Summary:</b></span>
                            <div class="lbfFormDetails">
                            <?php 
                                if (substr($formname, 0, 3) == 'LBF') {
                                    $doNotPrintField = true;
                                    include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

                                    call_user_func("lbf_report", $pid, '', 2, $form_id, $formname, true);
                                    $doNotPrintField = false;
                                } else {
                                    include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
                                    call_user_func($formname . "_report", $pid, '', 2, $form_id);
                                }
                            ?>
                            </div>
                            </td>
                        </tr>
                    <?php
                    } else {
                    ?>
                        <tr class="insRow1" >
                            <td colspan="5" class="cell1">
                                <?php self::generateOrderItemDetails($rto); ?>
                                <span><b>Notes:</b></span>
                                <div><?php echo !empty($rto['rto_notes']) ?  htmlspecialchars($rto['rto_notes'], ENT_QUOTES, '', FALSE) : '---'; ?></div>
                            </td>
                        </tr>
                    <?php
                    }
                }
            ?>
        </table>
        <?php
        $htmlStr = ob_get_clean(); 
        return $htmlStr;
        return '';
    }

    // Generate order items details
    public static function generateOrderItemDetails($rto) {
        if(!empty($rto)) {
        ?>
        <div>
            <div>
                    <span><b>Ordered Type: </b></span>
                    <span><?php echo ListLook($rto['rto_action'],'RTO_Action'); ?></span>
            </div>
            <div>
                    <span><b>Ordered By: </b></span>
                    <span><?php echo UserNameFromName($rto['rto_ordered_by']); ?></span>
            </div>
            <div>
                    <span><b>Ordered Time: </b></span>
                    <span><?php echo $rto['date']; ?></span>
            </div>
        </div>
        <br/>
        <?php
        }
    }

    // Get all RTO data. (Attachment).
    public static function getAllRTOAttachment($id = '') {
        $binds = array();
        $whereIdsStr  = "";

        if(is_array($id)) {
            foreach ($id as $value) {
                if(!empty($value)) {
                    if(!empty($whereIdsStr)) {
                        $whereIdsStr .= "OR ";
                    }

                    $whereIdsStr .= "id = ? ";
                    $binds[] = $value;
                }
            }

            if(!empty($whereIdsStr)) {
                $whereIdsStr = ' ('.$whereIdsStr.') ';
            }
        }

        $sql = "SELECT * FROM form_rto WHERE ".$whereIdsStr." ORDER BY rto_target_date, rto_status DESC";
        $all=array();
        $res = sqlStatement($sql, $binds);
        for($iter =0;$row = sqlFetchArray($res);$iter++) { 
            $links = self::LoadLinkedTriggersAttachment($row{'id'});
            if($links) {
                $settings = explode('|', $links);
                foreach($settings as $test) {
                    $tmp = explode('^',$test);
                    $key = $tmp[0];
                    $val = $tmp[1];
                    $row[$key] = $val;
                }
            }
            $all[] = $row;
        }
      return $all;
    }

    // Load linked trigger data (Attachment).
    public static function LoadLinkedTriggersAttachment($thisId){
        // THIS FUNCTION CREATES KEYS FOR ANY JAVASCRIPT CHECKS THAT NEED
        // TO HAPPEN FROM THE RTO SCREEN
        $sql = "SHOW TABLES LIKE 'wmt_rto_links'";
        $tres = sqlStatement($sql);
        $trow = sqlFetchArray($tres);
        $frm = '';
        if(is_array($trow)) {
            if(count($trow)) $frm = array_shift($trow);
        }
        if($frm != 'wmt_rto_links') return false;
        $key = false;
        $sql = "SELECT * FROM wmt_rto_links WHERE rto_id=?";
        $lres = sqlStatement($sql, array($thisId));
        while($lrow = sqlFetchArray($lres)) {
            if($lrow{'form_name'} == 'surg1') {
                $tres = sqlStatement("SELECT id, pid, sc1_surg_date FROM form_surg1 ".
                    "WHERE id=? ",array($lrow{'form_id'})); 
                $trow = sqlFetchArray($tres);
                if($trow{'id'} == $lrow{'form_id'}) {
                    if($key) $key .= '|';
                    if($trow{'sc1_surg_date'}) $key = 'test_target_dt^'.$trow{'sc1_surg_date'};
                }
            }
        }
        return($key);
    }

    // Generate AttachmentPDF based on content.
    public static function generateAttachmentPDF($content, $filename, $isFile = true) {
        $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
        $fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_" . $filename . ".pdf";

        // We want to overwrite so only one PDF is stored per form/encounter
        $config_mpdf = array(
            'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
            'mode' => $GLOBALS['pdf_language'],
            'format' => $GLOBALS['pdf_size'],
            'default_font_size' => '9',
            'default_font' => 'dejavusans',
            'margin_left' => $GLOBALS['pdf_left_margin'],
            'margin_right' => $GLOBALS['pdf_right_margin'],
            'margin_top' => $GLOBALS['pdf_top_margin'] * 1.5,
            'margin_bottom' => $GLOBALS['pdf_bottom_margin'] * 1.5,
            'margin_header' => $GLOBALS['pdf_top_margin'],
            'margin_footer' => $GLOBALS['pdf_bottom_margin'],
            'orientation' => $GLOBALS['pdf_layout'],
            'shrink_tables_to_fit' => 1,
            'use_kwt' => true,
            'keep_table_proportions' => true
        );

        $pdf = new mPDF($config_mpdf);
        $pdf->autoScriptToLang = true;

        $pdf->writeHTML($content);
        
        if($isFile == true) {
            $content_pdf = $pdf->Output($fullfilename, 'F');
            return array(
                'path' => $fullfilename,
                'name' => $filename . ".pdf",
                'page_count' => $pdf->page,
            );
        } else {
            $content_pdf = $pdf->Output($fullfilename, 'S');
            return array(
                'base64_content' => base64_encode($content_pdf),
                'name' => $filename . ".pdf",
                'page_count' => $pdf->page,
            );
        }
    }

    // Get Patient Data (Attachment)
    public static function getPatientDataAttachment($pid, $given = "*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS"){
        $sql = "select $given from patient_data where pid=? order by date DESC limit 0,1";
        return sqlQuery($sql, array($pid));
    }

    // Get Patient Data (Attachment)
    public static function replaceHTMLTags($string, $tags) {
        $tags_to_strip = $tags;
        foreach ($tags_to_strip as $tag){
            $string = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/","",$string);
        }

        return $string;
    }

    public static function getPDF($path) {
        $pdftext = file_get_contents($path);
        $num = preg_match_all("/\/Page\W/", $pdftext, $dummy);
        return $num;
    }

    public static function cleanStr($str) {
        // remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        $str = strip_tags($str); 
        $str = str_replace('&', 'and', $str);
        $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
        $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
        //$str = strtolower($str);
        $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
        $str = htmlentities($str, ENT_QUOTES, "utf-8");
        $str = preg_replace("/([a-z])([a-z]+;)/i", '$2', $str);
        $str = str_replace(' ', '_', $str);
        $str = rawurlencode($str);
        $str = str_replace('%', '_', $str);
        return $str;
    }

    // Get website url
    public static function getWebsiteURL() {
        global $web_root, $webserver_root;
        $prefix = 'http://';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $prefix = "https://";
        }
        return $prefix . $_SERVER['HTTP_HOST'] . $web_root;
    }

    // Help to get save url
    public static function getSaveURL($url) {
        global $webserver_root;
        $site_url = self::getWebsiteURL();
        $sUrl = str_replace($webserver_root, "", $url);
        $sUrl = substr($sUrl, strpos($sUrl, "/sites/"));
        return $sUrl;
    }

    // Set PDF Header Footer
    public static function setHeaderFooterOfHtml($type = '', $htmlStr = '', $newStr = '') {
        if(empty($htmlStr)) {
            return $htmlStr;
        }

        if(empty($type)) {
            return $htmlStr;
        }
        
        $hdoc = new \DOMDocument();
        $hdoc->loadHTML($htmlStr);
        
        if($type === 'H') {
            if (empty($newStr)) {
                $htmlStr = preg_replace("/<htmlpageheader.*?<\/htmlpageheader>/", '', $htmlStr);
                return preg_replace("/<sethtmlpageheader.*?<\/sethtmlpageheader>/", '', $htmlStr);
            }


            $eBox = $hdoc->getElementsByTagName('htmlpageheader')->item(0);

            if(!empty($eBox)) {
                $appended = $hdoc->createDocumentFragment();
                $appended->appendXML($newStr);
                $eBox->insertBefore($appended, $eBox->childNodes->item(0));
            } else {
                $bBox = $hdoc->getElementsByTagName('body')->item(0);

                $appended = $hdoc->createDocumentFragment();
                $appended->appendXML('<htmlpageheader name="PageHeader1">'.$newStr.'</htmlpageheader><sethtmlpageheader name="PageHeader1" page="ALL" value="ON" />');

                if(!empty($bBox)) {
                    $bBox->insertBefore($appended, $bBox->childNodes->item(0));
                } else {
                    $rBox = $hdoc->documentElement;
                    $rBox->insertBefore($appended, $rBox->childNodes->item(0));
                }
            }
        } else if($type === 'F') {
            if (empty($newStr)) {
                $htmlStr = preg_replace("/<htmlpagefooter.*?<\/htmlpagefooter>/", '', $htmlStr);
                return preg_replace("/<sethtmlpagefooter.*?<\/sethtmlpagefooter>/", '', $htmlStr);
            }

            $eBox = $hdoc->getElementsByTagName('htmlpagefooter')->item(0);

            if(!empty($eBox)) {
                $appended = $hdoc->createDocumentFragment();
                $appended->appendXML($newStr);
                $eBox->insertBefore($appended, $eBox->childNodes->item(0));
            } else {
                $bBox = $hdoc->getElementsByTagName('body')->item(0);

                $appended = $hdoc->createDocumentFragment();
                $appended->appendXML('<htmlpagefooter name="PageFooter1">'.$newStr.'</htmlpagefooter><sethtmlpagefooter name="PageFooter1" page="ALL" value="ON" />');

                if(!empty($bBox)) {
                    $bBox->insertBefore($appended, $bBox->childNodes->item(0));
                } else {
                    $rBox = $hdoc->documentElement;
                    $rBox->insertBefore($appended, $rBox->childNodes->item(0));
                }
            }
        }

        $hHtml = $hdoc->saveHTML();

        return $hHtml;
    }

    public static function mergerHTMLContent($content1 = '', $content2= '') {
        $doc = new \DOMDocument(); 
        $doc->loadHTML($content1);
        //get the element you want to append to
        $bodyBox = $doc->getElementsByTagName('body')->item(0);
        //create the element to append to #element1
        $appended = $doc->createDocumentFragment();
        $appended->appendXML('<div>'.$content2.'</div>');
        //actually append the element
        $bodyBox->appendChild($appended);
        $bodyHtml = $doc->saveHTML();

        return $bodyHtml;
    }

    public static function getPdfHeaderContent($pid = '') {
        if(empty($pid)) {
            return '';
        }

        $pData = (isset($pid) && !empty($pid)) ? self::getPatientDataAttachment($pid) : array();

        return '<div><table style="width: 100%;"><tr><td style="text-align:left;">Patient ID: '.$pData['pubpid'].'</td><td style="text-align:center;">DOB: '.$pData['DOB'].'</td><td style="text-align:right;">Name: '.($pData['fname'].' '.$pData['lname']).'</td></tr></table></div>';
    }


    /*Get Email Attachments*/
    public static function getAttachmentList($id) {
        $bind = array($id);
        $query = "SELECT * " .
        "FROM message_attachments AS ma WHERE " .
        "ma.`message_id` = ? ";

        $query .= "ORDER BY ma.`date` DESC";
        $dres = sqlStatement($query, $bind);

        $list = array();
        while ($drow = sqlFetchArray($dres)) {
            $list[] = $drow;
        }
        return $list;
    }

    /*Email Attachment doc*/
    public static function generateFinalDoc($type, $data, $file_name, $pid, $category_id, $doc_date = '') {
        global $webserver_root;

        try {

            // We want to overwrite so only one PDF is stored per form/encounter
            $config_mpdf = array(
                'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
                'mode' => $GLOBALS['pdf_language'],
                'format' => 'A4',
                'default_font_size' => '9',
                'default_font' => 'dejavusans',
                'margin_left' => '12.6mm',
                'margin_right' => '12.6mm',
                'margin_top' => '13.5mm',
                'margin_bottom' => '13.5mm',
                'orientation' => $GLOBALS['pdf_layout'],
                'shrink_tables_to_fit' => 1,
                'use_kwt' => true,
                'keep_table_proportions' => true
            );
            $pdf = new mPDF($config_mpdf);

            $pdf->allow_charset_conversion=true;
            $pdf->charset_in='UTF-8';

            $allowedImage = array('jpg','jpeg','jpe','png');
            $allowedFile = array('pdf');

            foreach($data as $dE => $data_item) {
                if($dE > 0) {
                    $pdf->AddPage();
                }

                $imageFiles = array();
                $dataFiles = array();

                $emailHtml = self::generateMsgHTML($type, $data_item);
                $pdf->writeHTML(utf8_decode($emailHtml));    

                foreach($data_item['attachments'] as $item) {
                    $fileExt = end(explode(".", $item['url']));
                    if(in_array($fileExt, $allowedImage) == true) {
                        $imageFiles[] = array(
                            'file_name' => $item['file_name'],
                            'path' => $webserver_root . $item['url']
                        );
                    } else if(in_array($fileExt, $allowedFile) == true) {
                        $dataFiles[] = $webserver_root . $item['url'];
                    } else {
                        $messages[] = "Skipped file becuase of UnSupported File Type: ".$item['filename'];
                    }
                }

                foreach($dataFiles as $file){
                    $fileExt = end(explode(".", $file));
                        //$pdf->SetImportUse();
                        $rsponceFile = self::checkFPDI($file);
                        if($rsponceFile != false && !empty($rsponceFile)) {
                            $pagecount = $pdf->setSourceFile($rsponceFile);
                            for ($i=1; $i<=($pagecount); $i++) {
                                $pdf->AddPage();
                                $import_page = $pdf->importPage($i);
                                $pdf->useTemplate($import_page);
                            }
                        }
                }

                if(!empty($imageFiles)) {
                    $pdf->AddPage();
                    foreach($imageFiles as $file){
                        $htmlStr = '<div><img src="'.$file['path'].'"/><div style="margin-top:5px;margin-bottom:25px;"><span><b>'.$file['file_name'].'</b></span></div></div>';
                        $pdf->writeHTML($htmlStr);
                    }
                }
            }

            $pagecount = $pdf->page;
            
            $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
            $fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_adddpc.pdf";
            
            $content_pdf = $pdf->Output($fullfilename, 'S');

            //Generate tmp file of documents
            $tmpfname = tempnam(sys_get_temp_dir(), 'POST');
            rename($tmpfname, $tmpfname .= '.tmp');
            file_put_contents($tmpfname, $content_pdf);

            $docResponce = self::doAddToDocument($tmpfname, $file_name, $pid, $category_id, $doc_date);

            return array(
                'status' => true,
                'error' => isset($docResponce['error']) ? $docResponce['error'] : "",
                'message' => isset($docResponce['message']) ? $docResponce['message'] : "",
                'page_count' => $pagecount 
            );

        } catch (Exception $e) {

            $status = $e->getMessage();

            return array(
                'status' => false,
                'error' => $status
            );
        
        }
    }

    public static function generateMsgHTML($type, $data) {
        $html = '';

        if(!empty($data)) {
            $field_to_label = 'To';
            $field_to_val = '';

            $raw_data = array();
            if(isset($data) && !empty($data['raw_data'])) {
                $raw_data = json_decode($data['raw_data'], true);
            }
            
            if($type == "email") {
                if(isset($data['direction']) && $data['direction'] == 'out') {
                    $field_to_label = 'Email To:';
                    $field_to_val =  $data['msg_to'];

                    $field_to_label_1 = 'Subject:';
                    $field_to_val_1 = isset($raw_data['subject']) ? $raw_data['subject'] : '';
                } else if(isset($data['direction']) && $data['direction'] == 'in') {
                    $field_to_label = 'Email From:';
                    $field_to_val =  $data['msg_from'];

                    $field_to_label_1 = 'Subject:';
                    $field_to_val_1 = $data['message_subject'];
                }
            } else if($type == "sms") {
                if(isset($data['direction']) && $data['direction'] == 'out') {
                    $field_to_label = 'SMS To:';
                    $field_to_val =  $data['msg_to'];
                } else if(isset($data['direction']) && $data['direction'] == 'in') {
                    $field_to_label = 'SMS From:';
                    $field_to_val =  $data['msg_from'];
                }

                $field_to_label_1 = 'Message Time:';
                $field_to_val_1 = $data['msg_time'];
            } else if($type == "fax") {
                if(isset($data['direction']) && $data['direction'] == 'out') {
                    $field_to_label = 'Fax To:';
                    $field_to_val =  $data['msg_to'];
                } else if(isset($data['direction']) && $data['direction'] == 'in') {
                    $field_to_label = 'Fax From:';
                    $field_to_val =  $data['msg_from'];
                }
            } else if($type == "postal_letter") {
                if(isset($data['direction']) && $data['direction'] == 'out') {
                    $field_to_label = 'Address To:';
                    $field_to_val =  $data['msg_to'];
                } else if(isset($data['direction']) && $data['direction'] == 'in') {
                    $field_to_label = 'Address From:';
                    $field_to_val =  $data['msg_from'];
                }
            }

            $field_message = isset($data['message']) ? MessagesLib::displayMessageContent($data['message'], false, true) : "";

            ob_start();
            ?>
                <table style="overflow:wrap;">
                    <tr>
                        <td width="150"><b><?php echo $field_to_label; ?></b></td>
                        <td><?php echo $field_to_val; ?></td>
                    </tr>
                    <?php if(isset($field_to_label_1) && !empty($field_to_label_1)) {?>
                    <tr>
                        <td width="100"><b><?php echo $field_to_label_1; ?></b></td>
                        <td><?php echo $field_to_val_1; ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="2"><b>Message:</b></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $field_message; ?></td>
                    </tr>
                </table>
            <?php
            $html = ob_get_clean();
        }
        return $html;
    }

    public static function checkFPDI($file) {
        $filepdf = fopen($file,"r");
        if ($filepdf) {
        $line_first = fgets($filepdf);
            fclose($filepdf);
        } else{
            return false;
            //echo "error opening the file.";
        }

        // extract number such as 1.4 ,1.5 from first read line of pdf file
        preg_match_all('!\d+!', $line_first, $matches); 
        // save that number in a variable
        $pdfversion = implode('.', $matches[0]);

        $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
        $fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_temdoc.pdf";

        $final_file = $file;

        if($pdfversion > "1.4"){
            shell_exec('gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile="'.$fullfilename.'" "'.$file.'"'); 
            $final_file = $fullfilename;
        }
        else{
            $final_file = $file;
        }

        return $final_file;
    }

    public static function doAddToDocument($file_path, $file_name, $pid, $category_id, $doc_date = '') {
        $fname = $file_name.'.pdf';
        $filesize = filesize($file_path);
        $tmpfile = fopen($file_path, "r");
        $filetext = fread($tmpfile, $filesize);
        fclose($tmpfile);

        // set mime, test for single DICOM and assign extension if missing.
        $mimetype = mime_content_type($file_path);

        if (strpos($filetext, 'DICM') !== false) {
            $mimetype = 'application/dicom';
            $parts = pathinfo($fname);
            if (!$parts['extension']) {
                $fname .= '.dcm';
            }
        }

        $d = new \Document();

        if(!empty($doc_date)) {
            $d->set_docdate($doc_date);
        }

        $rc = $d->createDocument(
            $pid,
            $category_id,
            $fname,
            $mimetype,
            $filetext,
            '',
            1,
            0,
            $file_path
        );
        if ($rc) {
            $error = $rc;
        } else {
            $message = "Success";
        }

        return array(
            'status' => false,
            'error' => isset($error) ? $error : "",
            'message' => isset($message) ? $message : ""
        );
    }
}