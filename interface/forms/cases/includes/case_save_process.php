<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtcase.class.php');

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";

if($frmn == "form_cases") {

    if($frmn == "form_cases" && $mode == "updatenotes") {
        // $field_prefix = '';

        // $form_lb_date = isset($_POST['tmp_lb_date']) && !empty($_POST['tmp_lb_date']) ? date("Y-m-d",strtotime(trim($_POST['tmp_lb_date'])))  : NULL;
        // $form_lb_notes = isset($_POST['tmp_lb_notes']) ? trim($_POST['tmp_lb_notes']) : "";
        // $form_lb_list_interim = "";
        // //$form_lb_list_interim = isset($_POST['tmp_lb_list_interim']) ? trim($_POST['tmp_lb_list_interim']) : "";

        // if(isset($_POST['tmp_lb_list_interim']) && !empty($_POST['tmp_lb_list_interim'])) {
        //  $nq_filter = ' AND option_id = "'.$_POST['tmp_lb_list_interim'].'"';
        //  $listOptions = LoadList('Case_Billing_Notes', 'active', 'seq', '', $nq_filter);

        //  if(!empty($listOptions)) {
        //      $form_lb_list_interim = $listOptions[0] && isset($listOptions[0]['title']) ? $listOptions[0]['title'] : "";
        //  }
        // }

        // if(!empty($form_lb_list_interim)) {
        //  if(!empty($form_lb_notes)) {
        //      $form_lb_notes = $form_lb_list_interim . " - ".$form_lb_notes;
        //  } else {
        //      $form_lb_notes = $form_lb_list_interim;
        //  }
        // }
        
        // if(!empty($form_lb_date) && !empty($form_lb_notes)) {
        //  $sql = "INSERT INTO `case_form_value_logs` ( case_id, delivery_date, notes, user ) VALUES (?, ?, ?, ?) ";
        //  sqlInsert($sql, array(
        //      $id,
        //      $form_lb_date,
        //      $form_lb_notes,
        //      $_SESSION['authUserID']
        //  ));
        // }
    }

    if($frmn == "form_cases" && ($mode == "save" || $mode == "updatenotes")) {
        foreach($modules as $module) {
            if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
            $field_prefix = $chp_options[1];

            if($module['option_id'] == "case_header") {
                $sc_referring_id_tmp = isset($_REQUEST['tmp_' . $field_prefix . 'sc_referring_id']) ? $_REQUEST['tmp_' . $field_prefix . 'sc_referring_id'] : array();
                $sc_filter_referring_id = array();

                foreach($sc_referring_id_tmp as $key => $val) {
                    if(!empty($val)) {
                        $sc_filter_referring_id[] = $val;
                    }
                }
                $sc_referring_id = implode("|",$sc_filter_referring_id);
                wmtCase::addScRcData($id, $sc_referring_id);
            }
        }
    }


    if($frmn == "form_cases" && ($mode == "save" || $mode == "updatenotes")) {
        $bc_date_value = isset($_POST['bc_date']) ? $_POST['bc_date'] : "";
        $bc_notes_value = isset($_POST['bc_notes']) ? $_POST['bc_notes'] : "";
        $bc_notes_dsc_value = isset($_POST['bc_notes_dsc']) ? $_POST['bc_notes_dsc'] : "";
        
        $bc_old_value = isset($_POST['tmp_old_bc_value']) ? $_POST['tmp_old_bc_value'] : "";
        $bc_new_value = $bc_date_value . $bc_notes_value . $bc_notes_dsc_value;

        if($bc_old_value !== $bc_new_value) {
            $form_lb_date = !empty($bc_date_value) ? date("Y-m-d",strtotime(trim($bc_date_value))) : NULL;
            $form_lb_list_interim = "";
            $form_lb_notes = $bc_notes_dsc_value;

            if(!empty($bc_notes_value)) {
                $nq_filter = ' AND option_id = "'.$bc_notes_value.'"';
                $listOptions = LoadList('Case_Billing_Notes', 'active', 'seq', '', $nq_filter);

                if(!empty($listOptions)) {
                    $form_lb_list_interim = $listOptions[0] && isset($listOptions[0]['title']) ? $listOptions[0]['title'] : "";
                }
            }

            if(!empty($form_lb_list_interim)) {
                if(!empty($form_lb_notes)) {
                    $form_lb_notes = $form_lb_list_interim . " - " . $form_lb_notes;
                } else {
                    $form_lb_notes = $form_lb_list_interim;
                }
            }
            
            if(!empty($form_lb_date) || !empty($form_lb_notes)) {
                $sql = "INSERT INTO `case_form_value_logs` ( case_id, delivery_date, notes, user ) VALUES (?, ?, ?, ?) ";
                $sId = sqlInsert($sql, array(
                    $id,
                    $form_lb_date,
                    $form_lb_notes,
                    $_SESSION['authUserID']
                ));

                if(!empty($id)) {
                    wmtCase::updateRecentDate($id);
                }
            }
        }
    }

    if($frmn == "form_cases" && ($mode == "save" || $mode == "updatenotes")) {
        if(!empty($id)) {
            $fieldList = array('case_manager', 'rehab_field_1', 'rehab_field_2');
            foreach($modules as $module) {
                if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
                $field_prefix = $chp_options[1];

                if($module['option_id'] == "case_header") {
                    $data = array();
                    $casemanager_hidden_sec = isset($_REQUEST['tmp_' . $field_prefix . 'casemanager_hidden_sec']) ? $_REQUEST['tmp_' . $field_prefix . 'casemanager_hidden_sec'] : 0;

                    foreach ($fieldList as $fk => $fItem) {
                        $data[$fItem] = isset($_REQUEST['tmp_' . $field_prefix . $fItem]) ? $_REQUEST['tmp_' . $field_prefix . $fItem] : "";
                    }

                    if($casemanager_hidden_sec === "1") {
                        //Save PI Case Values
                        $isNeedToUpdate = wmtCase::generateRehabLog($id, $data, $field_prefix);
                        wmtCase::savePICaseManagmentDetails($id, $data);

                        if($isNeedToUpdate !== false) {
                            wmtCase::logFormFieldValues(array(
                                'field_id' => 'rehab_field',
                                'form_name' => $frmn,
                                'form_id' => $id,
                                'new_value' => $isNeedToUpdate['new_value'],
                                'old_value' => $isNeedToUpdate['old_value'],
                                'pid' => isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '',
                                'username' => $_SESSION['authUserID']
                            ));
                        }
                    } else {
                        wmtCase::savePICaseManagmentDetails($id, $data);
                    }

                    //Handle Lawyer/Paralegal Contacts
                    $lpc_data = array();
                    $lpc_fieldList = array('lp_contact');
                    foreach ($lpc_fieldList as $lpc_k => $lpcItem) {
                        $lpc_data[$lpcItem] = isset($_REQUEST['tmp_' . $field_prefix . $lpcItem]) ? $_REQUEST['tmp_' . $field_prefix . $lpcItem] : "";
                    }
                    if(!empty($lpc_data) && !empty($id)) {
                        $c_notes = isset($_REQUEST[$field_prefix . 'notes']) ? $_REQUEST[$field_prefix . 'notes'] : "";
                        $c_emails = array_filter(explode(",",$c_notes));
                        $c_emails = array_map('trim',$c_emails);

                        $t_emails = $c_emails;

                        $lpContactData = wmtCase::getPICaseManagerData($id, 'lp_contact');
                        $lpList1 = array();
                        $lpList2 = array();

                        foreach ($lpContactData as $lpck => $lpcItem) {
                            if(isset($lpcItem['field_value']) && !empty($lpcItem['field_value'])) {
                                $lpList1[] = $lpcItem['field_value'];
                            }
                        }

                        if(isset($lpc_data['lp_contact']) && !empty($lpc_data['lp_contact'])) {
                            $lpList2 = $lpc_data['lp_contact'];
                        }

                        $diff1 = wmtCase::getArrayValDeff($lpList1, $lpList2);
                        $diff2 = wmtCase::getArrayValDeff($lpList2, $lpList1);
                        
                        $diffa1 = wmtCase::getAbookData($diff1);
                        $diffa2 = wmtCase::getAbookData($diff2);

                        if(!empty($diff1) || !empty($diff2)) {
                            foreach ($diff1 as $dak1 => $daI1) {
                                if(isset($diffa1['id_'.$daI1]) && !empty($diffa1['id_'.$daI1])) {
                                    $daItem1 = $diffa1['id_'.$daI1];

                                    if(isset($daItem1['email']) && !empty($daItem1['email'])) {
                                        if (($ky1 = array_search($daItem1['email'], $t_emails)) !== false) {
                                            unset($t_emails[$ky1]);
                                        }
                                    }
                                }
                            }

                            foreach ($diff2 as $dak2 => $daI2) {
                                if(isset($diffa2['id_'.$daI2]) && !empty($diffa2['id_'.$daI2])) {
                                    $daItem2 = $diffa2['id_'.$daI2];

                                    if(isset($daItem2['email']) && !empty($daItem2['email'])) {
                                        $t_emails[] = $daItem2['email'];
                                    }
                                }
                            }

                            if(isset($t_emails) && !empty($id)) {
                                $t_emails_str = implode(", ", $t_emails);
                                sqlStatement("UPDATE form_cases SET `notes` = ? WHERE `id` = ?", array($t_emails_str, $id));
                            }
                        }

                        //Save PI Case Managment Data
                        wmtCase::savePICaseManagmentDetails($id, $lpc_data);
                    }
                }
            }
        }
    }
}