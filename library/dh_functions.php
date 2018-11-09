<?php
/**
 * Allow the last name to be followed by a comma and some part of a first name(can
 *   also place middle name after the first name with a space separating them)
 * Allows comma alone followed by some part of a first name(can also place middle name
 *   after the first name with a space separating them).
 * Allows comma alone preceded by some part of a last name.
 * If no comma or space, then will search both last name and first name.
 * If the first letter of either name is capital, searches for name starting
 *   with given substring (the expected behavior). If it is lower case, it
 *   searches for the substring anywhere in the name. This applies to either
 *   last name, first name, and middle name.
 * Also allows first name followed by middle and/or last name when separated by spaces.
 * @param string $term
 * @param string $given
 * @param string $orderby
 * @param string $limit
 * @param string $start
 * @return array
 */
function dh_getPatientLnames($term = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{
    $names = getPatientNameSplit($term);

    foreach ($names as $key => $val) {
        if (!empty($val)) {
            if ((strlen($val) > 1) && ($names[$key][0] != strtoupper($names[$key][0]))) {
                $names[$key] = '%' . $val . '%';
            } else {
                $names[$key] = $val . '%';
            }
        }
    }

    // Debugging section below
    //if(array_key_exists('first',$names)) {
    //    error_log("first name search term :".$names['first']);
    //}
    //if(array_key_exists('middle',$names)) {
    //    error_log("middle name search term :".$names['middle']);
    //}
    //if(array_key_exists('last',$names)) {
    //    error_log("last name search term :".$names['last']);
    //}
    // Debugging section above

    $sqlBindArray = array();
    if (array_key_exists('last', $names) && $names['last'] == '') {
        // Do not search last name
        $where = "(fname LIKE ?) ";
        array_push($sqlBindArray, $names['first']);
        if ($names['middle'] != '') {
            $where .= "AND (mname LIKE ?) ";
            array_push($sqlBindArray, $names['middle']);
        }
    } elseif (array_key_exists('first', $names) && $names['first'] == '') {
        // Do not search first name or middle name
        $where = "(lname LIKE ?) ";
        array_push($sqlBindArray, $names['last']);
    } elseif ($names['first'] == '' && $names['last'] != '') {
        // Search both first name and last name with same term
        $names['first'] = $names['last'];
        $where = "(lname LIKE ? OR fname LIKE ?) ";
        array_push($sqlBindArray, $names['last'], $names['first']);
    } elseif ($names['middle'] != '') {
        $where = "(lname LIKE ? AND fname LIKE ? AND mname LIKE ?) ";
        array_push($sqlBindArray, $names['last'], $names['first'], $names['middle']);
    } else {
        $where = "(lname LIKE ? AND fname LIKE ?) ";
        array_push($sqlBindArray, $names['last'], $names['first']);
    }
    if (!acl_check('patients', 'p_list')) {
        $where .= "AND providerid = '" . $_SESSION['authUserID'] . "' ";
    }
    
    if (!empty($GLOBALS['pt_restrict_field'])) {
        if ($_SESSION{"authUser"} != 'admin' || $GLOBALS['pt_restrict_admin']) {
            $where .= " AND ( patient_data." . add_escape_custom($GLOBALS['pt_restrict_field']) .
                " = ( SELECT facility_id FROM users WHERE username = ?) OR patient_data." .
                add_escape_custom($GLOBALS['pt_restrict_field']) . " = '' ) ";
            array_push($sqlBindArray, $_SESSION{"authUser"});
        }
        
    }

    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }
    //echo $sql;
    $rez = sqlStatement($sql, $sqlBindArray);

    $returnval=array();
    for ($iter=0; $row=sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}






function dh_getPatientPhone($phone = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{
    $phone = preg_replace("/[[:punct:]]/", "", $phone);
    $sqlBindArray = array();
    $where = "REPLACE(REPLACE(phone_home, '-', ''), ' ', '') REGEXP ? ";
    if (!acl_check('patients', 'p_list')) {
        $where .= "AND providerid = '" . $_SESSION['authUserID'] . "' ";
    }
    array_push($sqlBindArray, $phone);
    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }
    echo $sql;
    $rez = sqlStatement($sql, $sqlBindArray);
    for ($iter=0; $row=sqlFetchArray($rez); $iter++) {
        $returnval[$iter]=$row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}



function dh_getPatientId($pid = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{

    $sqlBindArray = array();
    $where = "(pubpid LIKE ?) ";
    array_push($sqlBindArray, $pid."%");
    if (!empty($GLOBALS['pt_restrict_field']) && $GLOBALS['pt_restrict_by_id']) {
        if ($_SESSION{"authUser"} != 'admin' || $GLOBALS['pt_restrict_admin']) {
            $where .= "AND ( patient_data." . add_escape_custom($GLOBALS['pt_restrict_field']) .
                    " = ( SELECT facility_id FROM users WHERE username = ?) OR patient_data." .
                    add_escape_custom($GLOBALS['pt_restrict_field']) . " = '' ) ";
            array_push($sqlBindArray, $_SESSION{"authUser"});
        }
    }

    if (!acl_check('patients', 'p_list')) {
        $where .= "AND providerid = '" . $_SESSION['authUserID'] . "' ";
    }

    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    //echo $sql;
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }
    


    $rez = sqlStatement($sql, $sqlBindArray);
    for ($iter=0; $row=sqlFetchArray($rez); $iter++) {
        $returnval[$iter]=$row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

/* find patient data by DOB */
function dh_getPatientDOB($DOB = "%", $given = "pid, id, lname, fname, mname", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{
    $sqlBindArray = array();
    $where = "(DOB like ?) ";
    array_push($sqlBindArray, $DOB."%");
    if (!empty($GLOBALS['pt_restrict_field'])) {
        if ($_SESSION{"authUser"} != 'admin' || $GLOBALS['pt_restrict_admin']) {
            $where .= "AND ( patient_data." . add_escape_custom($GLOBALS['pt_restrict_field']) .
                    " = ( SELECT facility_id FROM users WHERE username = ?) OR patient_data." .
                    add_escape_custom($GLOBALS['pt_restrict_field']) . " = '' ) ";
            array_push($sqlBindArray, $_SESSION{"authUser"});
        }
    }

    if (!acl_check('patients', 'p_list')) {
        $where .= "AND providerid = '" . $_SESSION['authUserID'] . "' ";
    }

    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";

    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $rez = sqlStatement($sql, $sqlBindArray);
    for ($iter=0; $row=sqlFetchArray($rez); $iter++) {
        $returnval[$iter]=$row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

// $frow is a row from the layout_options table.
// $currvalue is the current value, if any, of the associated item.
// 
// dh 11/8/2018 this version modified by the appointment statuses to block access to normal users
// to Record Complete, Soap Overdue, Attendence Overdue.  Uses ACL check to allow 
// admin users to select these values.

function dh_generate_form_field($frow, $currvalue)
{
    global $rootdir, $date_init, $ISSUE_TYPES, $code_types;

    $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

    $data_type   = $frow['data_type'];
    $field_id    = $frow['field_id'];
    $list_id     = $frow['list_id'];
    $backup_list = $frow['list_backup_id'];

  // escaped variables to use in html
    $field_id_esc= htmlspecialchars($field_id, ENT_QUOTES);
    $list_id_esc = htmlspecialchars($list_id, ENT_QUOTES);

  // Added 5-09 by BM - Translate description if applicable
    $description = (isset($frow['description']) ? htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES) : '');

  // Support edit option T which assigns the (possibly very long) description as
  // the default value.
    if (isOption($frow['edit_options'], 'T') !== false) {
        if (strlen($currescaped) == 0) {
            $currescaped = $description;
        }

        // Description used in this way is not suitable as a title.
        $description = '';
    }

  // added 5-2009 by BM to allow modification of the 'empty' text title field.
  //  Can pass $frow['empty_title'] with this variable, otherwise
  //  will default to 'Unassigned'.
  // modified 6-2009 by BM to allow complete skipping of the 'empty' text title
  //  if make $frow['empty_title'] equal to 'SKIP'
    $showEmpty = true;
    if (isset($frow['empty_title'])) {
        if ($frow['empty_title'] == "SKIP") {
            //do not display an 'empty' choice
            $showEmpty = false;
            $empty_title = "Unassigned";
        } else {
            $empty_title = $frow['empty_title'];
        }
    } else {
        $empty_title = "Unassigned";
    }

    $disabled = isOption($frow['edit_options'], '0') === false ? '' : 'disabled';

    $lbfchange = (
        strpos($frow['form_id'], 'LBF') === 0 ||
        strpos($frow['form_id'], 'LBT') === 0 ||
        $frow['form_id'] == 'DEM'             ||
        $frow['form_id'] == 'HIS'
    ) ? "checkSkipConditions();" : "";
    $lbfonchange = $lbfchange ? "onchange='$lbfchange'" : "";

  // generic single-selection list or Race and Ethnicity.
  // These data types support backup lists.
    if ($data_type == 1 || $data_type == 33) {
        echo dh_generate_select_list(
            "form_$field_id",
            $list_id,
            $currvalue,
            $description,
            ($showEmpty ? $empty_title : ''),
            '',
            $lbfchange,
            '',
            ($disabled ? array('disabled' => 'disabled') : null),
            false,
            $backup_list
        );
    } // simple text field
    elseif ($data_type == 2) {
        $fldlength = htmlspecialchars($frow['fld_length'], ENT_QUOTES);
        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        echo "<input type='text'" .
        " class='form-control'" .
        " name='form_$field_id_esc'" .
        " id='form_$field_id_esc'" .
        " size='$fldlength'" .
        " $string_maxlength" .
        " title='$description'" .
        " value='$currescaped'";
        $tmp = $lbfchange;
        if (isOption($frow['edit_options'], 'C') !== false) {
            $tmp .= "capitalizeMe(this);";
        } elseif (isOption($frow['edit_options'], 'U') !== false) {
            $tmp .= "this.value = this.value.toUpperCase();";
        }

        if ($tmp) {
            echo " onchange='$tmp'";
        }

        $tmp = htmlspecialchars($GLOBALS['gbl_mask_patient_id'], ENT_QUOTES);
        // If mask is for use at save time, treat as no mask.
        if (strpos($tmp, '^') !== false) {
            $tmp = '';
        }
        if ($field_id == 'pubpid' && strlen($tmp) > 0) {
            echo " onkeyup='maskkeyup(this,\"$tmp\")'";
            echo " onblur='maskblur(this,\"$tmp\")'";
        }

        if (isOption($frow['edit_options'], '1') !== false && strlen($currescaped) > 0) {
            echo " readonly";
        }

        if ($disabled) {
            echo ' disabled';
        }

        echo " />";
    } // long or multi-line text field
    elseif ($data_type == 3) {
        $textCols = htmlspecialchars($frow['fld_length'], ENT_QUOTES);
        $textRows = htmlspecialchars($frow['fld_rows'], ENT_QUOTES);
        echo "<textarea" .
        " name='form_$field_id_esc'" .
        " class='form-control'" .
        " id='form_$field_id_esc'" .
        " title='$description'" .
        " cols='$textCols'" .
        " rows='$textRows' $lbfonchange $disabled" .
        ">" . $currescaped . "</textarea>";
    } // date
    elseif ($data_type == 4) {
        $age_asof_date = ''; // optionalAge() sets this
        $age_format = isOption($frow['edit_options'], 'A') === false ? 3 : 0;
        $agestr = optionalAge($frow, $currvalue, $age_asof_date, $description);
        if ($agestr) {
            echo "<table cellpadding='0' cellspacing='0'><tr><td class='text'>";
        }

        $onchange_string = '';
        if (!$disabled && $agestr) {
            $onchange_string = "onchange=\"if (typeof(updateAgeString) == 'function') " .
            "updateAgeString('$field_id','$age_asof_date', $age_format, '$description')\"";
        }
        if ($data_type == 4) {
            $modtmp = isOption($frow['edit_options'], 'F') === false ? 0 : 1;
            if (!$modtmp) {
                $dateValue  = oeFormatShortDate(substr($currescaped, 0, 10));
                echo "<input type='text' size='10' class='datepicker form-control' name='form_$field_id_esc' id='form_$field_id_esc'" .
                " value='" .  attr($dateValue)  ."'";
            } else {
                $dateValue  = oeFormatDateTime(substr($currescaped, 0, 20), 0);
                echo "<input type='text' size='20' class='datetimepicker form-control' name='form_$field_id_esc' id='form_$field_id_esc'" .
                    " value='" . attr($dateValue) . "'";
            }
        }
        if (!$agestr) {
            echo " title='$description'";
        }

        echo " $onchange_string $lbfonchange $disabled />";

        // Optional display of age or gestational age.
        if ($agestr) {
            echo "</td></tr><tr><td id='span_$field_id' class='text'>" . text($agestr) . "</td></tr></table>";
        }
    } // provider list, local providers only
    elseif ($data_type == 10) {
        $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
        "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
        "AND authorized = 1 " .
        "ORDER BY lname, fname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' $lbfonchange $disabled class='form-control'>";
        echo "<option value=''>" . xlt($empty_title) . "</option>";
        $got_selected = false;
        while ($urow = sqlFetchArray($ures)) {
            $uname = text($urow['fname'] . ' ' . $urow['lname']);
            $optionId = attr($urow['id']);
            echo "<option value='$optionId'";
            if ($urow['id'] == $currvalue) {
                echo " selected";
                $got_selected = true;
            }

            echo ">$uname</option>";
        }

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // provider list, including address book entries with an NPI number
    elseif ($data_type == 11) {
        $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
        "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
        "AND ( authorized = 1 OR ( username = '' AND npi != '' ) ) " .
        "ORDER BY lname, fname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control'";
        echo " $lbfonchange $disabled>";
        echo "<option value=''>" . xlt('Unassigned') . "</option>";
        $got_selected = false;
        while ($urow = sqlFetchArray($ures)) {
            $uname = text($urow['fname'] . ' ' . $urow['lname']);
            $optionId = attr($urow['id']);
            echo "<option value='$optionId'";
            if ($urow['id'] == $currvalue) {
                echo " selected";
                $got_selected = true;
            }

            echo ">$uname</option>";
        }

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // pharmacy list
    elseif ($data_type == 12) {
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control'";
        echo " $lbfonchange $disabled>";
        echo "<option value='0'></option>";
        $pres = get_pharmacies();
        $got_selected = false;
        while ($prow = sqlFetchArray($pres)) {
            $key = $prow['id'];
            $optionValue = htmlspecialchars($key, ENT_QUOTES);
            $optionLabel = htmlspecialchars($prow['name'] . ' ' . $prow['area_code'] . '-' .
            $prow['prefix'] . '-' . $prow['number'] . ' / ' .
            $prow['line1'] . ' / ' . $prow['city'], ENT_NOQUOTES);
            echo "<option value='$optionValue'";
            if ($currvalue == $key) {
                  echo " selected";
                  $got_selected = true;
            }

            echo ">$optionLabel</option>";
        }

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // squads
    elseif ($data_type == 13) {
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control'";
        echo " $lbfonchange $disabled>";
        echo "<option value=''>&nbsp;</option>";
        $squads = acl_get_squads();
        if ($squads) {
            foreach ($squads as $key => $value) {
                $optionValue = htmlspecialchars($key, ENT_QUOTES);
                $optionLabel = htmlspecialchars($value[3], ENT_NOQUOTES);
                echo "<option value='$optionValue'";
                if ($currvalue == $key) {
                    echo " selected";
                }

                echo ">$optionLabel</option>\n";
            }
        }

        echo "</select>";
    } // Address book, preferring organization name if it exists and is not in
  // parentheses, and excluding local users who are not providers.
  // Supports "referred to" practitioners and facilities.
  // Alternatively the letter L in edit_options means that abook_type
  // must be "ord_lab", indicating types used with the procedure
  // lab ordering system.
  // Alternatively the letter O in edit_options means that abook_type
  // must begin with "ord_", indicating types used with the procedure
  // ordering system.
  // Alternatively the letter V in edit_options means that abook_type
  // must be "vendor", indicating the Vendor type.
  // Alternatively the letter R in edit_options means that abook_type
  // must be "dist", indicating the Distributor type.
    elseif ($data_type == 14) {
        if (isOption($frow['edit_options'], 'L') !== false) {
            $tmp = "abook_type = 'ord_lab'";
        } elseif (isOption($frow['edit_options'], 'O') !== false) {
            $tmp = "abook_type LIKE 'ord\\_%'";
        } elseif (isOption($frow['edit_options'], 'V') !== false) {
            $tmp = "abook_type LIKE 'vendor%'";
        } elseif (isOption($frow['edit_options'], 'R') !== false) {
            $tmp = "abook_type LIKE 'dist'";
        } else {
            $tmp = "( username = '' OR authorized = 1 )";
        }

        $ures = sqlStatement("SELECT id, fname, lname, organization, username FROM users " .
        "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
        "AND $tmp " .
        "ORDER BY organization, lname, fname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control'";
        echo " $lbfonchange $disabled>";
        echo "<option value=''>" . htmlspecialchars(xl('Unassigned'), ENT_NOQUOTES) . "</option>";
        while ($urow = sqlFetchArray($ures)) {
            $uname = $urow['organization'];
            if (empty($uname) || substr($uname, 0, 1) == '(') {
                $uname = $urow['lname'];
                if ($urow['fname']) {
                    $uname .= ", " . $urow['fname'];
                }
            }

            $optionValue = htmlspecialchars($urow['id'], ENT_QUOTES);
            $optionLabel = htmlspecialchars($uname, ENT_NOQUOTES);
            echo "<option value='$optionValue'";
            // Failure to translate Local and External is not an error here;
            // they are only used as internal flags and must not be translated!
            $title = $urow['username'] ? 'Local' : 'External';
            $optionTitle = htmlspecialchars($title, ENT_QUOTES);
            echo " title='$optionTitle'";
            if ($urow['id'] == $currvalue) {
                echo " selected";
            }

            echo ">$optionLabel</option>";
        }

        echo "</select>";
    } // A billing code. If description matches an existing code type then that type is used.
    elseif ($data_type == 15) {
        $codetype = '';
        if (!empty($frow['description']) && isset($code_types[$frow['description']])) {
            $codetype = $frow['description'];
        }
        $fldlength = htmlspecialchars($frow['fld_length'], ENT_QUOTES);
        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        //
        if (isOption($frow['edit_options'], '2') !== false && substr($frow['form_id'], 0, 3) == 'LBF') {
            // Option "2" generates a hidden input for the codes, and a matching visible field
            // displaying their descriptions. First step is computing the description string.
            $currdescstring = '';
            if (!empty($currvalue)) {
                $relcodes = explode(';', $currvalue);
                foreach ($relcodes as $codestring) {
                    if ($codestring === '') {
                        continue;
                    }

                    $code_text = lookup_code_descriptions($codestring);
                    if ($currdescstring !== '') {
                        $currdescstring .= '; ';
                    }

                    if (!empty($code_text)) {
                        $currdescstring .= $code_text;
                    } else {
                        $currdescstring .= $codestring;
                    }
                }
            }

            $currdescstring = attr($currdescstring);
            //
            echo "<input type='text'" .
            " name='form_$field_id_esc'" .
            " id='form_related_code'" .
            " size='$fldlength'" .
            " value='$currescaped'" .
            " style='display:none'" .
            " $lbfonchange readonly $disabled />";
            // Extra readonly input field for optional display of code description(s).
            echo "<input type='text'" .
            " name='form_$field_id_esc" . "__desc'" .
            " size='$fldlength'" .
            " title='$description'" .
            " value='$currdescstring'";
            if (!$disabled) {
                echo " onclick='sel_related(this,\"$codetype\")'";
            }

            echo "class='form-control'";
            echo " readonly $disabled />";
        } else {
            echo "<input type='text'" .
            " name='form_$field_id_esc'" .
            " id='form_related_code'" .
            " size='$fldlength'" .
            " $string_maxlength" .
            " title='$description'" .
            " value='$currescaped'";
            if (!$disabled) {
                echo " onclick='sel_related(this,\"$codetype\")'";
            }

            echo "class='form-control'";
            echo " $lbfonchange readonly $disabled />";
        }
    } // insurance company list
    elseif ($data_type == 16) {
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' class='form-control' title='$description'>";
        echo "<option value='0'></option>";
        $insprovs = getInsuranceProviders();
        $got_selected = false;
        foreach ($insprovs as $key => $ipname) {
            $optionValue = htmlspecialchars($key, ENT_QUOTES);
            $optionLabel = htmlspecialchars($ipname, ENT_NOQUOTES);
            echo "<option value='$optionValue'";
            if ($currvalue == $key) {
                echo " selected";
                $got_selected = true;
            }

            echo ">$optionLabel</option>";
        }

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // issue types
    elseif ($data_type == 17) {
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' class='form-control' title='$description'>";
        echo "<option value='0'></option>";
        $got_selected = false;
        foreach ($ISSUE_TYPES as $key => $value) {
            $optionValue = htmlspecialchars($key, ENT_QUOTES);
            $optionLabel = htmlspecialchars($value[1], ENT_NOQUOTES);
            echo "<option value='$optionValue'";
            if ($currvalue == $key) {
                echo " selected";
                $got_selected = true;
            }

            echo ">$optionLabel</option>";
        }

        if (!$got_selected && strlen($currvalue) > 0) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // Visit categories.
    elseif ($data_type == 18) {
        $cres = sqlStatement("SELECT pc_catid, pc_catname " .
        "FROM openemr_postcalendar_categories ORDER BY pc_catname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' class='form-control' title='$description'" .
        " $lbfonchange $disabled>";
        echo "<option value=''>" . xlt($empty_title) . "</option>";
        $got_selected = false;
        while ($crow = sqlFetchArray($cres)) {
            $catid = $crow['pc_catid'];
            if (($catid < 9 && $catid != 5) || $catid == 11) {
                continue;
            }

            echo "<option value='" . attr($catid) . "'";
            if ($catid == $currvalue) {
                echo " selected";
                $got_selected = true;
            }

            echo ">" . text(xl_appt_category($crow['pc_catname'])) . "</option>";
        }

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // a set of labeled checkboxes
    elseif ($data_type == 21) {
        // If no list then it's a single checkbox and its value is "Yes" or empty.
        if (!$list_id) {
            echo "<input type='checkbox' name='form_{$field_id_esc}' " .
            "id='form_{$field_id_esc}' value='Yes' $lbfonchange";
            if ($currvalue) {
                echo " checked";
            }
            echo " $disabled />";
        } else {
            // In this special case, fld_length is the number of columns generated.
            $cols = max(1, $frow['fld_length']);
            $avalue = explode('|', $currvalue);
            $lres = sqlStatement("SELECT * FROM list_options " .
            "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
            echo "<table cellpadding='0' cellspacing='0' width='100%' title='".attr($description)."'>";
            $tdpct = (int) (100 / $cols);
            for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
                $option_id = $lrow['option_id'];
                $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
                // if ($count) echo "<br />";
                if ($count % $cols == 0) {
                    if ($count) {
                        echo "</tr>";
                    }
                    echo "<tr>";
                }
                echo "<td width='" . attr($tdpct) . "%' nowrap>";
                echo "<input type='checkbox' name='form_{$field_id_esc}[$option_id_esc]'" .
                "id='form_{$field_id_esc}[$option_id_esc]' class='form-control' value='1' $lbfonchange";
                if (in_array($option_id, $avalue)) {
                    echo " checked";
                }
                // Added 5-09 by BM - Translate label if applicable
                echo " $disabled />" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
                echo "</td>";
            }
            if ($count) {
                echo "</tr>";
                if ($count > $cols) {
                    // Add some space after multiple rows of checkboxes.
                    $cols = htmlspecialchars($cols, ENT_QUOTES);
                    echo "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
                }
            }
            echo "</table>";
        }
    } // a set of labeled text input fields
    elseif ($data_type == 22) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            $maxlength = $frow['max_length'];
            $string_maxlength = "";
            // if max_length is set to zero, then do not set a maxlength
            if ($maxlength) {
                $string_maxlength = "maxlength='".attr($maxlength)."'";
            }

            $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];

            // Added 5-09 by BM - Translate label if applicable
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
            $optionValue = htmlspecialchars($avalue[$option_id], ENT_QUOTES);
            echo "<td><input type='text'" .
            " name='form_{$field_id_esc}[$option_id_esc]'" .
            " id='form_{$field_id_esc}[$option_id_esc]'" .
            " size='$fldlength'" .
            " class='form-control'" .
            " $string_maxlength" .
            " value='$optionValue'";
            echo " $lbfonchange $disabled /></td></tr>";
        }

        echo "</table>";
    } // a set of exam results; 3 radio buttons and a text field:
    elseif ($data_type == 23) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0'>";
        echo "<tr><td>&nbsp;</td><td class='bold'>" .
        htmlspecialchars(xl('N/A'), ENT_NOQUOTES) .
        "&nbsp;</td><td class='bold'>" .
        htmlspecialchars(xl('Nor'), ENT_NOQUOTES) . "&nbsp;</td>" .
        "<td class='bold'>" .
        htmlspecialchars(xl('Abn'), ENT_NOQUOTES) . "&nbsp;</td><td class='bold'>" .
        htmlspecialchars(xl('Date/Notes'), ENT_NOQUOTES) . "</td></tr>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);

            // Added 5-09 by BM - Translate label if applicable
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

            for ($i = 0; $i < 3; ++$i) {
                $inputValue = htmlspecialchars($i, ENT_QUOTES);
                echo "<td><input type='radio'" .
                " name='radio_{$field_id_esc}[$option_id_esc]'" .
                " id='radio_{$field_id_esc}[$option_id_esc]'" .
                " value='$inputValue' $lbfonchange";
                if ($restype === "$i") {
                    echo " checked";
                }

                echo " $disabled /></td>";
            }

            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
            $resnote = htmlspecialchars($resnote, ENT_QUOTES);
            echo "<td><input type='text'" .
            " name='form_{$field_id_esc}[$option_id_esc]'" .
            " id='form_{$field_id_esc}[$option_id_esc]'" .
            " size='$fldlength'" .
            " $string_maxlength" .
            " value='$resnote' $disabled /></td>";
            echo "</tr>";
        }

        echo "</table>";
    } // the list of active allergies for the current patient
  // this is read-only!
    elseif ($data_type == 24) {
        $query = "SELECT title, comments FROM lists WHERE " .
        "pid = ? AND type = 'allergy' AND enddate IS NULL " .
        "ORDER BY begdate";
        // echo "<!-- $query -->\n"; // debugging
        $lres = sqlStatement($query, array($GLOBALS['pid']));
        $count = 0;
        while ($lrow = sqlFetchArray($lres)) {
            if ($count++) {
                echo "<br />";
            }

            echo htmlspecialchars($lrow['title'], ENT_NOQUOTES);
            if ($lrow['comments']) {
                echo ' (' . htmlspecialchars($lrow['comments'], ENT_NOQUOTES) . ')';
            }
        }
    } // a set of labeled checkboxes, each with a text field:
    elseif ($data_type == 25) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);

            // Added 5-09 by BM - Translate label if applicable
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

            $option_id = htmlspecialchars($option_id, ENT_QUOTES);
            echo "<td><input type='checkbox' name='check_{$field_id_esc}[$option_id_esc]'" .
            " id='check_{$field_id_esc}[$option_id_esc]' class='form-control' value='1' $lbfonchange";
            if ($restype) {
                echo " checked";
            }

            echo " $disabled />&nbsp;</td>";
            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
            $resnote = htmlspecialchars($resnote, ENT_QUOTES);
            echo "<td><input type='text'" .
            " name='form_{$field_id_esc}[$option_id_esc]'" .
            " id='form_{$field_id_esc}[$option_id_esc]'" .
            " size='$fldlength'" .
            " class='form-control' " .
            " $string_maxlength" .
            " value='$resnote' $disabled /></td>";
            echo "</tr>";
        }

        echo "</table>";
    } // single-selection list with ability to add to it
    elseif ($data_type == 26) {
        echo generate_select_list(
            "form_$field_id",
            $list_id,
            $currvalue,
            $description,
            ($showEmpty ? $empty_title : ''),
            'addtolistclass_'.$list_id,
            $lbfchange,
            '',
            ($disabled ? array('disabled' => 'disabled') : null),
            false,
            $backup_list
        );
        // show the add button if user has access to correct list
        $inputValue = htmlspecialchars(xl('Add'), ENT_QUOTES);
        $outputAddButton = "<input type='button' id='addtolistid_" . $list_id_esc . "' fieldid='form_" .
        $field_id_esc . "' class='addtolist' value='$inputValue' $disabled />";
        if (aco_exist('lists', $list_id)) {
           // a specific aco exist for this list, so ensure access
            if (acl_check('lists', $list_id)) {
                echo $outputAddButton;
            }
        } else {
           // no specific aco exist for this list, so check for access to 'default' list
            if (acl_check('lists', 'default')) {
                echo $outputAddButton;
            }
        }
    } // a set of labeled radio buttons
    elseif ($data_type == 27) {
        // In this special case, fld_length is the number of columns generated.
        $cols = max(1, $frow['fld_length']);
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0' width='100%'>";
        $tdpct = (int) (100 / $cols);
        $got_selected = false;
        for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            if ($count % $cols == 0) {
                if ($count) {
                    echo "</tr>";
                }

                echo "<tr>";
            }

            echo "<td width='" . attr($tdpct) . "%'>";
            echo "<input type='radio' name='form_{$field_id_esc}' class='form-control' id='form_{$field_id_esc}[$option_id_esc]'" .
            " value='$option_id_esc' $lbfonchange";
            if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
              (strlen($currvalue)  > 0 && $option_id == $currvalue)) {
                echo " checked";
                $got_selected = true;
            }

            echo " $disabled />" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
            echo "</td>";
        }

        if ($count) {
            echo "</tr>";
            if ($count > $cols) {
                // Add some space after multiple rows of radio buttons.
                $cols = htmlspecialchars($cols, ENT_QUOTES);
                echo "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
            }
        }

        echo "</table>";
        if (!$got_selected && strlen($currvalue) > 0) {
            $fontTitle = htmlspecialchars(xl('Please choose a valid selection.'), ENT_QUOTES);
            $fontText = htmlspecialchars(xl('Fix this'), ENT_NOQUOTES);
            echo "$currescaped <font color='red' title='$fontTitle'>$fontText!</font>";
        }
    } // special case for history of lifestyle status; 3 radio buttons and a date text field:
  // VicarePlus :: A selection list box for smoking status:
    elseif ($data_type == 28 || $data_type == 32) {
        $tmp = explode('|', $currvalue);
        switch (count($tmp)) {
            case "4":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = oeFormatShortDate($tmp[2]);
                $reslist = $tmp[3];
                break;
            case "3":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = oeFormatShortDate($tmp[2]);
                break;
            case "2":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = "";
                break;
            case "1":
                $resnote = $tmp[0];
                $resdate = $restype = "";
                break;
            default:
                $restype = $resdate = $resnote = "";
                break;
        }

        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];

        $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
        $resnote = htmlspecialchars($resnote, ENT_QUOTES);
        $resdate = htmlspecialchars($resdate, ENT_QUOTES);
        echo "<table cellpadding='0' cellspacing='0'>";
        echo "<tr>";
        if ($data_type == 28) {
          // input text
            echo "<td><input type='text'" .
            " name='form_$field_id_esc'" .
            " id='form_$field_id_esc'" .
            " size='$fldlength'" .
            " $string_maxlength" .
            " value='$resnote' $disabled />&nbsp;</td>";
            echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
            "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
            htmlspecialchars(xl('Status'), ENT_NOQUOTES).":&nbsp;&nbsp;</td>";
        } elseif ($data_type == 32) {
          // input text
            echo "<tr><td><input type='text'" .
            " name='form_text_$field_id_esc'" .
            " id='form_text_$field_id_esc'" .
            " size='$fldlength'" .
            " class='form-control'" .
            " $string_maxlength" .
            " value='$resnote' $disabled />&nbsp;</td></tr>";
            echo "<td>";
          //Selection list for smoking status
            $onchange = 'radioChange(this.options[this.selectedIndex].value)';//VicarePlus :: The javascript function for selection list.
            echo generate_select_list(
                "form_$field_id",
                $list_id,
                $reslist,
                $description,
                ($showEmpty ? $empty_title : ''),
                '',
                $onchange,
                '',
                ($disabled ? array('disabled' => 'disabled') : null)
            );
            echo "</td>";
            echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . xlt('Status') . ":&nbsp;&nbsp;</td>";
        }

        // current
        echo "<td class='text' ><input type='radio'" .
        " name='radio_{$field_id_esc}'" .
        " id='radio_{$field_id_esc}[current]'" .
        " class='form-control'" .
        " value='current" . $field_id_esc . "' $lbfonchange";
        if ($restype == "current" . $field_id) {
            echo " checked";
        }

        if ($data_type == 32) {
            echo " onClick='smoking_statusClicked(this)'";
        }

        echo " />" . xlt('Current') . "&nbsp;</td>";
        // quit
        echo "<td class='text'><input type='radio'" .
        " name='radio_{$field_id_esc}'" .
        " id='radio_{$field_id_esc}[quit]'" .
        " class='form-control'" .
        " value='quit".$field_id_esc."' $lbfonchange";
        if ($restype == "quit" . $field_id) {
            echo " checked";
        }

        if ($data_type == 32) {
            echo " onClick='smoking_statusClicked(this)'";
        }

        echo " $disabled />" . xlt('Quit') . "&nbsp;</td>";
        // quit date
        echo "<td class='text'><input type='text' size='6' class='datepicker' name='date_$field_id_esc' id='date_$field_id_esc'" .
        " value='$resdate'" .
        " title='$description'" .
        " $disabled />";
        echo "&nbsp;</td>";
        // never
        echo "<td class='text'><input type='radio'" .
        " name='radio_{$field_id_esc}'" .
        " class='form-control'" .
        " id='radio_{$field_id_esc}[never]'" .
        " value='never" . $field_id_esc . "' $lbfonchange";
        if ($restype == "never" . $field_id) {
            echo " checked";
        }

        if ($data_type == 32) {
            echo " onClick='smoking_statusClicked(this)'";
        }

        echo " />" . xlt('Never') . "&nbsp;</td>";
        // Not Applicable
        echo "<td class='text'><input type='radio'" .
        " class='form-control' " .
        " name='radio_{$field_id}'" .
        " id='radio_{$field_id}[not_applicable]'" .
        " value='not_applicable" . $field_id . "' $lbfonchange";
        if ($restype == "not_applicable" . $field_id) {
            echo " checked";
        }

        if ($data_type == 32) {
            echo " onClick='smoking_statusClicked(this)'";
        }

        echo " $disabled />" . xlt('N/A') . "&nbsp;</td>";
        //
        //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
        echo "<td class='text' ><div id='smoke_code'></div></td>";
        echo "</tr>";
        echo "</table>";
    } // static text.  read-only, of course.
    elseif ($data_type == 31) {
        echo parse_static_text($frow);
    } //$data_type == 33
  // Race and Ethnicity. After added support for backup lists, this is now the same as datatype 1; so have migrated it there.
  //$data_type == 33

    elseif ($data_type == 34) {
        $arr = explode("|*|*|*|", $currvalue);
        echo "<a href='../../../library/custom_template/custom_template.php?type=form_{$field_id}&contextName=".htmlspecialchars($list_id_esc, ENT_QUOTES)."' class='iframe_medium' style='text-decoration:none;color:black;'>";
        echo "<div id='form_{$field_id}_div' class='text-area' style='min-width:100pt'>" . $arr[0] . "</div>";
        echo "<div style='display:none'><textarea name='form_{$field_id}' id='form_{$field_id}' class='form-control' style='display:none' $lbfonchange $disabled>" . $currvalue . "</textarea></div>";
        echo "</a>";
    } //facilities drop-down list
    elseif ($data_type == 35) {
        if (empty($currvalue)) {
            $currvalue = 0;
        }

        dropdown_facility(
            $selected = $currvalue,
            $name = "form_$field_id_esc",
            $allow_unspecified = true,
            $allow_allfacilities = false,
            $disabled,
            $lbfchange
        );
    } //multiple select
  // supports backup list
    elseif ($data_type == 36) {
        echo generate_select_list(
            "form_$field_id",
            $list_id,
            $currvalue,
            $description,
            $showEmpty ? $empty_title : '',
            '',
            $lbfchange,
            '',
            null,
            true,
            $backup_list
        );
    } // Canvas and related elements for browser-side image drawing.
  // Note you must invoke lbf_canvas_head() (below) to use this field type in a form.
    elseif ($data_type == 40) {
        // Unlike other field types, width and height are in pixels.
        $canWidth  = intval($frow['fld_length']);
        $canHeight = intval($frow['fld_rows']);
        if (empty($currvalue)) {
            if (preg_match('/\\bimage=([a-zA-Z0-9._-]*)/', $frow['description'], $matches)) {
                // If defined this is the filename of the default starting image.
                $currvalue = $GLOBALS['web_root'] . '/sites/' . $_SESSION['site_id'] . '/images/' . $matches[1];
            }
        }

        $mywidth  = 50 + ($canWidth  > 250 ? $canWidth  : 250);
        $myheight = 31 + ($canHeight > 261 ? $canHeight : 261);
        echo "<div id='form_$field_id_esc' style='width:$mywidth; height:$myheight;'></div>";
        // Hidden form field exists to send updated data to the server at submit time.
        echo "<input type='hidden' name='form_$field_id_esc' value='' />";
        // Hidden image exists to support initialization of the canvas.
        echo "<img src='" . attr($currvalue) . "' id='form_{$field_id_esc}_img' style='display:none'>";
        // $date_init is a misnomer but it's the place for browser-side setup logic.
        $date_init .= " lbfCanvasSetup('form_$field_id_esc', $canWidth, $canHeight);\n";
    }
}












// Function to generate a drop-list.
//
function dh_generate_select_list(
    $tag_name,
    $list_id,
    $currvalue,
    $title,
    $empty_name = ' ',
    $class = '',
    $onchange = '',
    $tag_id = '',
    $custom_attributes = null,
    $multiple = false,
    $backup_list = ''
) {
        $s = '';

    $tag_name_esc = attr($tag_name);

    if ($multiple) {
        $tag_name_esc = $tag_name_esc . "[]";
    }

    $s .= "<select name='$tag_name_esc'";

    if ($multiple) {
        $s .= " multiple='multiple'";
    }

    $tag_id_esc = attr($tag_name);

    if ($tag_id != '') {
        $tag_id_esc = attr($tag_id);
    }

    $s .= " id='$tag_id_esc'";

    if (!empty($class)) {
        $class_esc = attr($class);
        $s .= " class='form-control $class_esc'";
    } else {
        $s .= " class='form-control'";
    }

    if ($onchange) {
        $s .= " onchange='$onchange'";
    }

    if ($custom_attributes != null && is_array($custom_attributes)) {
        foreach ($custom_attributes as $attr => $val) {
            if (isset($custom_attributes [$attr])) {
                $s .= " " . attr($attr) . "='" . attr($val) . "'";
            }
        }
    }

    $selectTitle = attr($title);
    $s .= " title='$selectTitle'>";
    $selectEmptyName = xlt($empty_name);
    if ($empty_name) {
        $s .= "<option value=''>" . $selectEmptyName . "</option>";
    }

        // List order depends on language translation options.
        //  (Note we do not need to worry about the list order in the algorithm
        //   after the below code block since that is where searches for exceptions
        //   are done which include inactive items or items from a backup
        //   list; note these will always be shown at the bottom of the list no matter the
        //   chosen order.)
        $lang_id = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
        // sort by title
    if (($lang_id == '1' && !empty($GLOBALS['skip_english_translation'])) || !$GLOBALS['translate_lists']) {
        // do not translate
        if ($GLOBALS['gb_how_sort_list'] == '0') {
            // order by seq
            $order_by_sql = "seq, title";
        } else { //$GLOBALS['gb_how_sort_list'] == '1'
            // order by title
            $order_by_sql = "title, seq";
        }

        $lres = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity=1 ORDER BY " . $order_by_sql, array($list_id));
    } else {
        // do translate
        if ($GLOBALS['gb_how_sort_list'] == '0') {
            // order by seq
            $order_by_sql = "lo.seq, IF(LENGTH(ld.definition),ld.definition,lo.title)";
        } else { //$GLOBALS['gb_how_sort_list'] == '1'
            // order by title
            $order_by_sql = "IF(LENGTH(ld.definition),ld.definition,lo.title), lo.seq";
        }

        $lres = sqlStatement("SELECT lo.option_id, lo.is_default, " .
        "IF(LENGTH(ld.definition),ld.definition,lo.title) AS title " .
        "FROM list_options AS lo " .
        "LEFT JOIN lang_constants AS lc ON lc.constant_name = lo.title " .
        "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
        "ld.lang_id = ? " .
        "WHERE lo.list_id = ?  AND lo.activity=1 " .
        "ORDER BY " . $order_by_sql, array($lang_id, $list_id));
    }

    $got_selected = false;

    while ($lrow = sqlFetchArray($lres)) {
        $selectedValues = explode("|", $currvalue);

        $optionValue = attr($lrow ['option_id']);
        $s .= "<option value='$optionValue'";

        if ((strlen($currvalue) == 0 && $lrow ['is_default']) || (strlen($currvalue) > 0 && in_array($lrow ['option_id'], $selectedValues))) {
            $s .= " selected";
            $got_selected = true;
        }
    //echo "<script type='text/javascript'>alert('Here');</script>";
        // dh 11/9/2018 added this for the items only allowed to be changed by admin and system          
        if (!acl_check('admin', 'super') && (($lrow ['option_id']=="RC") || ($lrow ['option_id']=="SO") || ($lrow ['option_id']=="AO"))) {
            $s .= ' disabled';
        } 

        // Already has been translated above (if applicable), so do not need to use
        // the xl_list_label() function here
        $optionLabel = text($lrow ['title']);
        $s .= ">$optionLabel</option>\n";
    }

    /*
	  To show the inactive item in the list if the value is saved to database
	  */
    if (!$got_selected && strlen($currvalue) > 0) {
        $lres_inactive = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 0 AND option_id = ? ORDER BY seq, title", array($list_id, $currvalue));
        $lrow_inactive = sqlFetchArray($lres_inactive);
        if ($lrow_inactive['option_id']) {
            $optionValue = htmlspecialchars($lrow_inactive['option_id'], ENT_QUOTES);
            $s .= "<option value='$optionValue' selected>" . htmlspecialchars(xl_list_label($lrow_inactive['title']), ENT_NOQUOTES) . "</option>\n";
            $got_selected = true;
        }
    }

    if (!$got_selected && strlen($currvalue) > 0 && !$multiple) {
        $list_id = $backup_list;
        $lrow = sqlQuery("SELECT title FROM list_options WHERE list_id = ? AND option_id = ?", array($list_id,$currvalue));

        if ($lrow > 0 && !empty($backup_list)) {
            $selected = text(xl_list_label($lrow ['title']));
            $s .= "<option value='$currescaped' selected> $selected </option>";
            $s .= "</select>";
        } else {
            $s .= "<option value='$currescaped' selected>* $currescaped *</option>";
            $s .= "</select>";
            $fontTitle = xlt('Please choose a valid selection from the list.');
            $fontText = xlt('Fix this');
            $s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
        }
    } elseif (!$got_selected && strlen($currvalue) > 0 && $multiple) {
        //if not found in main list, display all selected values that exist in backup list
        $list_id = $backup_list;

        $got_selected_backup = false;
        if (!empty($backup_list)) {
            $lres_backup = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
            while ($lrow_backup = sqlFetchArray($lres_backup)) {
                $selectedValues = explode("|", $currvalue);

                $optionValue = attr($lrow_backup['option_id']);

                if (in_array($lrow_backup ['option_id'], $selectedValues)) {
                    $s .= "<option value='$optionValue'";
                    $s .= " selected";
                    $optionLabel = text(xl_list_label($lrow_backup ['title']));
                    $s .= ">$optionLabel</option>\n";
                    $got_selected_backup = true;
                }
            }
        }

        if (!$got_selected_backup) {
            $selectedValues = explode("|", $currvalue);
            foreach ($selectedValues as $selectedValue) {
                $s .= "<option value='" . attr($selectedValue) . "'";
                $s .= " selected";
                $s .= ">* " . text($selectedValue) . " *</option>\n";
            }

            $s .= "</select>";
            $fontTitle = xlt('Please choose a valid selection from the list.');
            $fontText = xlt('Fix this');
            $s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
        }
    } else {
        $s .= "</select>";
    }

    return $s;
}



?> 