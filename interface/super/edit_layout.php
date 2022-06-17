<?php

/**
 * Edit layouts gui
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/layout.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Indicates if deactivated layouts are included in the dropdown.
$form_inactive = !empty($_REQUEST['form_inactive']);

function setLayoutTimestamp($layout_id)
{
    $query = "UPDATE layout_group_properties SET grp_last_update = CURRENT_TIMESTAMP " .
        "WHERE grp_form_id = ? AND grp_group_id = ''";
    sqlStatement($query, array($layout_id));
}

function collectLayoutNames($condition, $mapping = '')
{
    global $layouts, $form_inactive;
    $gres = sqlStatement(
        "SELECT grp_form_id, grp_title, grp_mapping " .
        "FROM layout_group_properties WHERE " .
        "grp_group_id = '' " .
        ($form_inactive ? "" : "AND grp_activity = 1 ") .
        "AND $condition " .
        "ORDER BY grp_mapping, grp_seq, grp_title"
    );
    while ($grow = sqlFetchArray($gres)) {
        $tmp = $mapping ? $mapping : $grow['grp_mapping'];
        if (!$tmp) {
            $tmp = '(' . xl('No Name') . ')';
        }
        $layouts[$grow['grp_form_id']] = array($tmp, $grow['grp_title']);
    }
}
$layouts = array();
collectLayoutNames("grp_form_id NOT LIKE 'LBF%' AND grp_form_id NOT LIKE 'LBT%'", xl('Core'));
collectLayoutNames("grp_form_id LIKE 'LBT%'", xl('Transactions'));
collectLayoutNames("grp_form_id LIKE 'LBF%'", '');

// Include predefined Validation Rules from list
$validations = array();
$lres = sqlStatement("SELECT * FROM list_options " .
    "WHERE list_id = 'LBF_Validations' AND activity = 1 ORDER BY seq, title");
while ($lrow = sqlFetchArray($lres)) {
    $validations[$lrow['option_id']] = xl_list_label($lrow['title']);
}

function nextGroupOrder($order)
{
    if ($order == '9') {
        $order = 'A';
    } elseif ($order == 'Z') {
        $order = 'a';
    } else {
        $order = chr(ord($order) + 1);
    }

    return $order;
}

// This returns HTML for a <select> that allows choice of a layout group.
// Included also are parent groups containing only sub-groups.  Groups are listed
// in the same order as they appear in the layout.
//
function genGroupSelector($name, $layout_id, $default = '')
{
    $res = sqlStatement(
        "SELECT grp_group_id, grp_title " .
        "FROM layout_group_properties WHERE " .
        "grp_form_id = ? AND grp_group_id != '' ORDER BY grp_group_id",
        array($layout_id)
    );
    $s  = "<select class='form-control' name='" . xla($name) . "'>";
    $s .= "<option value=''>" . xlt('None{{Group}}') . "</option>";
    $arr = array();
    $arrid = '';
    while ($row = sqlFetchArray($res)) {
        $thisid = $row['grp_group_id'];
        $i = 0;
      // Compute number of initial matching groups.
        while ($i < strlen($thisid) && $i < strlen($arrid) && $thisid[$i] == $arrid[$i]) {
            ++$i;
        }
        $arr = array_slice($arr, 0, $i); // discard the rest
        while ($i < (strlen($arrid) - 1)) {
            $arr[$i++] = '???'; // should not happen
        }
        $arr[$i] = $row['grp_title'];
        $gval = '';
        foreach ($arr as $part) {
            if ($gval) {
                $gval .= ' / ';
            }
            $gval .= $part;
        }
        $s .= "<option value='" . attr($thisid) . "'";
        if ($thisid == $default) {
            $s .= ' selected';
        }
        $s .= ">" . text($gval) . "</option>";
    }
    $s .= "</select>";
    return $s;
}

// Compute a new group ID that will become layout_options.group_id and
// layout_group_properties.grp_group_id.
// $parent is a string of zero or more sequence prefix characters.
// If there is a nonempty $parent then its ID will be the prefix for the
// new ID and the sequence prefix will be computed within the parent.
//
function genGroupId($parent)
{
    global $layout_id;
    $results = sqlStatement(
        "SELECT grp_group_id " .
        "FROM layout_group_properties WHERE " .
        "grp_form_id = ? AND grp_group_id LIKE ?",
        array($layout_id, ($parent ?? '') . "_%")
    );
    $maxnum = '1';
    while ($result = sqlFetchArray($results)) {
        $tmp = substr($result['grp_group_id'], strlen($parent), 1);
        if ($tmp >= $maxnum) {
            $maxnum = nextGroupOrder($tmp);
        }
    }
    return $parent . $maxnum;
}

// Changes a group's ID from and to the specified IDs. This also works for groups
// that have sub-groups, in which case only the appropriate parent portion of
// the ID is changed.
//
function fuzzyRename($from, $to)
{
    global $layout_id;

    $query = "UPDATE layout_options SET group_id = concat(?, substr(group_id, ?)) " .
    "WHERE form_id = ? AND group_id LIKE ?";
    sqlStatement($query, array($to, strlen($from) + 1, $layout_id, "$from%"));

    $query = "UPDATE layout_group_properties SET grp_group_id = concat(?, substr(grp_group_id, ?)) " .
    "WHERE grp_form_id = ? AND grp_group_id LIKE ?";
    sqlStatement($query, array($to, strlen($from) + 1, $layout_id, "$from%"));

    setLayoutTimestamp($layout_id);
}

// Swaps the positions of two groups.  To the degree they have matching parents,
// only the first differing child positions are swapped.
//
function swapGroups($id1, $id2)
{
    $i = 0;
    while ($i < strlen($id1) && $i < strlen($id2) && $id1[$i] == $id2[$i]) {
        ++$i;
    }
  // $i is now the number of matching characters/levels.
    if ($i < strlen($id1) && $i < strlen($id2)) {
        $common = substr($id1, 0, $i);
        $pfx1   = substr($id1, $i, 1);
        $pfx2   = substr($id2, $i, 1);
        $tmpname = $common . '#';
      // To avoid collision use 3 renames.
        fuzzyRename($common . $pfx1, $common . '#');
        fuzzyRename($common . $pfx2, $common . $pfx1);
        fuzzyRename($common . '#', $common . $pfx2);
    }
}

function tableNameFromLayout($layout_id)
{
    // Skip layouts that store data in vertical tables.
    if (substr($layout_id, 0, 3) == 'LBF' || substr($layout_id, 0, 3) == 'LBT' || $layout_id == "FACUSR") {
        return '';
    }
    if ($layout_id == "DEM") {
        $tablename = "patient_data";
    } elseif (substr($layout_id, 0, 3) == "HIS") {
        $tablename = "history_data";
    } elseif ($layout_id == "SRH") {
        $tablename = "lists_ippf_srh";
    } elseif ($layout_id == "CON") {
        $tablename = "lists_ippf_con";
    } elseif ($layout_id == "GCA") {
        $tablename = "lists_ippf_gcac";
    } else {
        die(xlt('Internal error in tableNameFromLayout') . '(' . text($layout_id) . ')');
    }
    return $tablename;
}

// This tells you if a column name is required in code and therefore must not
// be deleted or renamed.
function isColumnReserved($tablename, $field_id)
{
    if ($tablename == 'patient_data') {
        if (
            in_array($field_id, array(
            'id',
            'DOB',
            'title',
            'language',
            'fname',
            'lname',
            'mname',
            'street',
            'postal_code',
            'city',
            'state',
            'ss',
            'phone_home',
            'phone_cell',
            'date',
            'sex',
            'providerID',
            'email',
            'pubpid',
            'pid',
            'squad',
            'home_facility',
            'deceased_date',
            'deceased_reason',
            'allow_patient_portal',
            'soap_import_status',
            'email_direct',
            'dupscore',
            'cmsportal_login',
            'care_team_provider',
            'care_team_status',
            'billing_note',
            'uuid',
            'care_team_facility',
            'name_history',
            'care_team_status',
            'patient_groups',
            'additional_addresses'
            ))
        ) {
            return true;
        }
    } elseif ($tablename == 'history_data') {
        if (
            in_array($field_id, array(
            'id',
            'date',
            'pid',
            ))
        ) {
            return true;
        }
    }
    return false;
}

// Call this when adding or removing a layout field.  This will create or drop
// the corresponding table column when appropriate.  Table columns are not
// dropped if they contain any non-empty values or are required internally.
function addOrDeleteColumn($layout_id, $field_id, $add = true)
{
    $tablename = tableNameFromLayout($layout_id);
    if (!$tablename) {
        return;
    }
    // Check if the column currently exists.
    $tmp = sqlQuery("SHOW COLUMNS FROM `" . escape_table_name($tablename) . "` LIKE ?", array($field_id));
    $column_exists = !empty($tmp);

    if ($add && !$column_exists) {
        sqlStatement("ALTER TABLE `" . escape_table_name($tablename) . "` ADD `" . escape_identifier($field_id, 'a-zA-Z0-9_', true) . "` TEXT");
        EventAuditLogger::instance()->newEvent(
            "alter_table",
            $_SESSION['authUser'],
            $_SESSION['authProvider'],
            1,
            "$tablename ADD $field_id"
        );
    } elseif (!$add && $column_exists) {
        // Do not drop a column that has any data.
        $tmp = sqlQuery(
            "SELECT `" . escape_sql_column_name($field_id, [$tablename]) .
            "` AS field_id FROM `" . escape_table_name($tablename) . "` WHERE " .
            "`" . escape_sql_column_name($field_id, [$tablename]) . "` IS NOT NULL AND `"
            . escape_sql_column_name($field_id, [$tablename]) . "` != '' LIMIT 1"
        );
        if (!isset($tmp['field_id']) && !isColumnReserved($tablename, $field_id)) {
            $lotmp = array();
            // For History layouts do not delete a field name duplicated in another History layout
            // (should not happen, but a bug allowed it).
            if (substr($layout_id, 0, 3) == 'HIS') {
                $lotmp = sqlQuery(
                    "SELECT COUNT(*) AS count FROM layout_options WHERE " .
                    "form_id LIKE 'HIS%' AND form_id != ? AND field_id = ?",
                    array($layout_id, $field_id)
                );
            }
            if (empty($lotmp['count'])) {
                sqlStatement(
                    "ALTER TABLE `" . escape_table_name($tablename) . "` " .
                    "DROP `" . escape_sql_column_name($field_id, [$tablename]) . "`"
                );
                EventAuditLogger::instance()->newEvent(
                    "alter_table",
                    $_SESSION['authUser'],
                    $_SESSION['authProvider'],
                    1,
                    "$tablename DROP $field_id "
                );
            }
        }
    }
}

// Call this before renaming a layout field.
// Renames the table column (if applicable) and returns a result status:
//  -1 = There is no table for this layout (not an error).
//   0 = Rename successful.
//   2 = There is no column having the old name.
//   3 = There is already a column having the new name.
//   4 = Old name is needed internally and cannot be changed.
//
function renameColumn($layout_id, $old_field_id, $new_field_id)
{
    $tablename = tableNameFromLayout($layout_id);
    if (!$tablename) {
        return -1; // Indicate rename is not relevant.
    }
    if (isColumnReserved($tablename, $old_field_id)) {
        return 4;
    }
    // Make sure old column exists.
    $colarr = sqlQuery("SHOW COLUMNS FROM `" . escape_table_name($tablename) . "` LIKE ?", array($old_field_id));
    if (empty($colarr)) {
        // Error, old name does not exist.
        return 2;
    }
    // Make sure new column does not exist.
    $tmp = sqlQuery("SHOW COLUMNS FROM `" . escape_table_name($tablename) . "` LIKE ?", array($new_field_id));
    if (!empty($tmp)) {
        // Error, new name already in use.
        return 3;
    }
    // With MySQL you can't change just the name, you have to specify the column definition too.
    $colstr = $colarr['Type'];
    if ($colarr['Null'] == 'NO') {
        $colstr .= " NOT NULL";
    }
    if ($colarr['Default'] !== null) {
        $colstr .= " DEFAULT '" . add_escape_custom($colarr['Default']) . "'";
    }
    if ($colarr['Extra']) {
        $colstr .= " " . add_escape_custom($colarr['Extra']);
    }
    $query = "ALTER TABLE `" . escape_table_name($tablename) . "` CHANGE `" . escape_sql_column_name($old_field_id, [$tablename]) . "` `" . escape_identifier($new_field_id, 'a-zA-Z0-9_', true) . "` $colstr";
    sqlStatement($query);
    EventAuditLogger::instance()->newEvent(
        "alter_table",
        $_SESSION['authUser'],
        $_SESSION['authProvider'],
        1,
        "$tablename RENAME $old_field_id TO $new_field_id $colstr"
    );
    return 0; // Indicate rename done and successful.
}

// Test options array for save
function encodeModifier($jsonArray)
{
    return $jsonArray !== null ? json_encode($jsonArray) : "";
}

// Check authorization.
$thisauth = AclMain::aclCheckCore('admin', 'super');
if (!$thisauth) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Layout Editor")]);
    exit;
}

// Make a sorted version of the $datatypes array.
$sorted_datatypes = $datatypes;
natsort($sorted_datatypes);

// The layout ID identifies the layout to be edited.
$layout_id = empty($_REQUEST['layout_id']) ? '' : $_REQUEST['layout_id'];
$layout_tbl = !empty($layout_id) ? tableNameFromLayout($layout_id) : '';

// Tag style for stuff to hide if not an LBF layout. Currently just for the Source column.
$lbfonly = substr($layout_id, 0, 3) == 'LBF' ? "" : "style='display:none;'";

// Handle the Form actions

if (!empty($_POST['formaction']) && ($_POST['formaction'] == "save") && $layout_id) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    // If we are saving, then save.
    $fld = $_POST['fld'];
    for ($lino = 1; isset($fld[$lino]['id']); ++$lino) {
        $iter = $fld[$lino];
        $field_id = trim($iter['id']);
        $field_id_original = trim($iter['originalid']);
        $data_type = trim($iter['datatype']);
        $listval = $data_type == 34 ? trim($iter['contextName']) : trim($iter['list_id']);
        $action = $iter['action'];
        if ($action == 'value' || $action == 'hsval') {
            $action .= '=' . $iter['value'];
        }
        // Skip conditions for the line are stored as a serialized array.
        $condarr = array('action' => $action);
        $cix = 0;
        for (; !empty($iter['condition_id'][$cix]); ++$cix) {
            $andor = empty($iter['condition_andor'][$cix]) ? '' : $iter['condition_andor'][$cix];
            $condarr[$cix] = array(
            'id'       => $iter['condition_id'      ][$cix],
            'itemid'   => $iter['condition_itemid'  ][$cix],
            'operator' => $iter['condition_operator'][$cix],
            'value'    => $iter['condition_value'   ][$cix],
            'andor'    => $andor,
            );
        }
        $conditions = $cix ? serialize($condarr) : '';
        if ($field_id) {
            if ($field_id != $field_id_original) {
                if (renameColumn($layout_id, $field_id_original, $field_id) > 0) {
                    // If column rename had an error then don't rename it here.
                    $field_id = $field_id_original;
                }
            }
            sqlStatement("UPDATE layout_options SET " .
                "field_id = '"      . add_escape_custom($field_id)      . "', " .
                "source = '"        . add_escape_custom(trim($iter['source']))    . "', " .
                "title = '"         . add_escape_custom($iter['title'])     . "', " .
                "group_id = '"    . add_escape_custom(trim($iter['group']))     . "', " .
                "seq = '"           . add_escape_custom(trim($iter['seq']))      . "', " .
                "uor = '"           . add_escape_custom(trim($iter['uor']))       . "', " .
                "fld_length = '"    . add_escape_custom(trim($iter['lengthWidth']))    . "', " .
                "fld_rows = '"    . add_escape_custom(trim($iter['lengthHeight']))    . "', " .
                "max_length = '"    . add_escape_custom(trim($iter['maxSize']))    . "', "                             .
                "titlecols = '"     . add_escape_custom(trim($iter['titlecols'])) . "', " .
                "datacols = '"      . add_escape_custom(trim($iter['datacols']))  . "', " .
                "data_type= '" . add_escape_custom($data_type) . "', "                                .
                "list_id= '"        . add_escape_custom($listval)   . "', " .
                "list_backup_id= '"        . add_escape_custom(trim($iter['list_backup_id']))   . "', " .
                "edit_options = '"  . add_escape_custom(encodeModifier($iter['edit_options'] ?? null)) . "', " .
                "default_value = '" . add_escape_custom(trim($iter['default']))   . "', " .
                "description = '"   . add_escape_custom(trim($iter['desc']))      . "', " .
                "codes = '"   . add_escape_custom(trim($iter['codes']))      . "', " .
                "conditions = '"    . add_escape_custom($conditions) . "', " .
                "validation = '"   . add_escape_custom(trim($iter['validation']))   . "' " .
                "WHERE form_id = '" . add_escape_custom($layout_id) . "' AND field_id = '" . add_escape_custom($field_id_original) . "'");

              setLayoutTimestamp($layout_id);
        }
    }
} elseif (!empty($_POST['formaction']) && ($_POST['formaction'] == "addfield") && $layout_id) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    // Add a new field to a specific group
    $data_type = trim($_POST['newdatatype']);
    $max_length = $data_type == 3 ? 3 : 255;
    $listval = $data_type == 34 ? trim($_POST['contextName']) : trim($_POST['newlistid']);
    sqlStatement("INSERT INTO layout_options (" .
      " form_id, source, field_id, title, group_id, seq, uor, fld_length, fld_rows" .
      ", titlecols, datacols, data_type, edit_options, default_value, codes, description" .
      ", max_length, list_id, list_backup_id " .
      ") VALUES ( " .
      "'"  . add_escape_custom(trim($_POST['layout_id'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newsource'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newid'])) . "'" .
      ",'" . add_escape_custom($_POST['newtitle']) . "'" .
      ",'" . add_escape_custom(trim($_POST['newfieldgroupid'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newseq'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newuor'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newlengthWidth'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newlengthHeight'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newtitlecols'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newdatacols'])) . "'" .
      ",'" . add_escape_custom($data_type) . "'"                                  .
        ",'" . add_escape_custom(encodeModifier($_POST['newedit_options'] ?? null)) . "'" .
      ",'" . add_escape_custom(trim($_POST['newdefault'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newcodes'])) . "'" .
      ",'" . add_escape_custom(trim($_POST['newdesc'])) . "'" .
      ",'"    . add_escape_custom(trim($_POST['newmaxSize']))    . "'"  .
      ",'" . add_escape_custom($listval) . "'" .
      ",'" . add_escape_custom(trim($_POST['newbackuplistid'])) . "'" .
      " )");
    addOrDeleteColumn($layout_id, trim($_POST['newid']), true);
    setLayoutTimestamp($layout_id);
} elseif (!empty($_POST['formaction']) && ($_POST['formaction'] == "movefields") && $layout_id) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    // Move field(s) to a new group in the layout
    $sqlstmt = "UPDATE layout_options SET " .
                " group_id = '" . add_escape_custom($_POST['targetgroup']) . "' " .
                " WHERE " .
                " form_id = '" . add_escape_custom($_POST['layout_id']) . "' " .
                " AND field_id IN (";
    $comma = "";
    foreach (explode(" ", $_POST['selectedfields']) as $onefield) {
        $sqlstmt .= $comma . "'" . add_escape_custom($onefield) . "'";
        $comma = ", ";
    }
    $sqlstmt .= ")";
    //echo $sqlstmt;
    sqlStatement($sqlstmt);
    setLayoutTimestamp($layout_id);
} elseif (($_POST['formaction'] ?? '') == "copytolayout" && $layout_id && !empty($_POST['targetlayout'])) {
    // Copy field(s) to the specified group in another layout.
    // It's important to skip any duplicate field names.
    $tlayout = $_POST['targetlayout'];
    $tgroup  = $_POST['targetgroup'];
    foreach (explode(" ", $_POST['selectedfields']) as $onefield) {
        $srow = sqlQuery(
            "SELECT * FROM layout_options WHERE " .
            "form_id = ? AND field_id = ? LIMIT 1",
            array($layout_id, $onefield)
        );
        if (empty($srow)) {
            die("Internal error: Field '" . text($onefield) . "' not found in layout '" . text($layout_id) . "'.");
        }
        $trow = sqlQuery(
            "SELECT * FROM layout_options WHERE " .
            "form_id = ? AND field_id = ? LIMIT 1",
            array($tlayout, $onefield)
        );
        if (!empty($trow)) {
            echo "<!-- Field '" . text($onefield) . "' already exists in layout '" . text($tlayout) . "'. -->\n";
            continue;
        }
        $qstr = "INSERT INTO layout_options SET `form_id` = ?, `field_id` = ?, `group_id` = ?";
        $qarr = array($tlayout, $onefield, $tgroup);
        foreach ($srow as $key => $value) {
            if ($key == 'form_id' || $key == 'field_id' || $key == 'group_id') {
                continue;
            }
            $qstr .= ", `$key` = ?";
            $qarr[] = $value;
        }
        // echo "<!-- $qstr ("; foreach ($qarr as $tmp) echo "'$tmp',"; echo ") -->\n"; // debugging
        sqlStatement($qstr, $qarr);
        addOrDeleteColumn($tlayout, $onefield, true);
        setLayoutTimestamp($tlayout);
    }
} elseif (!empty($_POST['formaction']) && ($_POST['formaction'] == "deletefields") && $layout_id) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    // Delete a field from a specific group
    $sqlstmt = "DELETE FROM layout_options WHERE " .
                " form_id = '" . add_escape_custom($_POST['layout_id']) . "' " .
                " AND field_id IN (";
    $comma = "";
    foreach (explode(" ", $_POST['selectedfields']) as $onefield) {
        $sqlstmt .= $comma . "'" . add_escape_custom($onefield) . "'";
        $comma = ", ";
    }
    $sqlstmt .= ")";
    sqlStatement($sqlstmt);
    foreach (explode(" ", $_POST['selectedfields']) as $onefield) {
        addOrDeleteColumn($layout_id, $onefield, false);
    }
    setLayoutTimestamp($layout_id);
} elseif (!empty($_POST['formaction']) && ($_POST['formaction'] == "addgroup") && $layout_id) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    // Generate new value for layout_items.group_id.
    $newgroupid = genGroupId($_POST['newgroupparent']);
    sqlStatement(
        "INSERT INTO layout_group_properties SET " .
        "grp_form_id = ?, " .
        "grp_group_id = ?, " .
        "grp_title = ?",
        array($layout_id, $newgroupid, $_POST['newgroupname'])
    );
    setLayoutTimestamp($layout_id);
} elseif (!empty($_POST['formaction']) && $_POST['formaction'] == "deletegroup" && $layout_id) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    // drop the fields from the related table (this is critical)
    $res = sqlStatement(
        "SELECT field_id FROM layout_options WHERE " .
        "form_id = ? AND group_id = ?",
        array($_POST['layout_id'], $_POST['deletegroupid'])
    );
    while ($row = sqlFetchArray($res)) {
        addOrDeleteColumn($layout_id, $row['field_id'], false);
    }
    // Delete an entire group from the form
    sqlStatement(
        "DELETE FROM layout_options WHERE " .
        " form_id = ? AND group_id = ?",
        array($_POST['layout_id'], $_POST['deletegroupid'])
    );
    sqlStatement(
        "DELETE FROM layout_group_properties WHERE " .
        "grp_form_id = ? AND grp_group_id = ?",
        array($_POST['layout_id'], $_POST['deletegroupid'])
    );
    setLayoutTimestamp($layout_id);
} elseif (!empty($_POST['formaction']) && ($_POST['formaction'] == "movegroup") && $layout_id) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    // Note that in some cases below the swapGroups() call will do nothing.
    $res = sqlStatement(
        "SELECT DISTINCT group_id " .
        "FROM layout_options WHERE form_id = ? ORDER BY group_id",
        array($layout_id)
    );
    $row = sqlFetchArray($res);
    $id1 = $row['group_id'];
    while ($row = sqlFetchArray($res)) {
        $id2 = $row['group_id'];
        if ($_POST['movedirection'] == 'up') { // moving up
            if ($id2 == $_POST['movegroupname']) {
                swapGroups($id2, $id1);
                break;
            }
        } else { // moving down
            if ($id1 == $_POST['movegroupname']) {
                swapGroups($id1, $id2);
                break;
            }
        }
        $id1 = $id2;
    }
    setLayoutTimestamp($layout_id);
} elseif (!empty($_POST['formaction']) && ($_POST['formaction'] == "renamegroup") && $layout_id) {
    // Renaming a group. This might include moving to a different parent group.
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $newparent = $_POST['renamegroupparent'];  // this is an ID
    $oldid     = $_POST['renameoldgroupname']; // this is an ID
    $oldparent = substr($oldid, 0, -1);
    $newid = $oldid;
    if ($newparent != $oldparent) {
      // Different parent, generate a new child prefix character.
        $newid = genGroupId($newparent);
        sqlStatement(
            "UPDATE layout_options SET group_id = ? " .
            "WHERE form_id = ? AND group_id = ?",
            array($newid, $layout_id, $oldid)
        );
    }
    $query = "UPDATE layout_group_properties SET " .
    "grp_group_id = ?, grp_title = ? " .
    "WHERE grp_form_id = ? AND grp_group_id = ?";
    sqlStatement($query, array($newid, $_POST['renamegroupname'], $layout_id, $oldid));
}

// global counter for field numbers
$fld_line_no = 0;

$extra_html = '';

// This is called to generate a select option list for fields within this form.
// Used for selecting a field for testing in a skip condition.
//
function genFieldOptionList($current = '')
{
    global $layout_id;
    $option_list = "<option value=''>-- " . xlt('Please Select') . " --</option>";
    if ($layout_id) {
        $query = "SELECT field_id FROM layout_options WHERE form_id = ? ORDER BY group_id, seq";
        $res = sqlStatement($query, array($layout_id));
        while ($row = sqlFetchArray($res)) {
            $field_id = $row['field_id'];
            $option_list .= "<option value='" . attr($field_id) . "'";
            if ($field_id == $current) {
                $option_list .= " selected";
            }
            $option_list .= ">" . text($field_id) . "</option>";
        }
    }
    return $option_list;
}

// Write one option line to the form.
//
function writeFieldLine($linedata)
{
    global $fld_line_no, $sources, $lbfonly, $extra_html, $validations, $UOR;
    ++$fld_line_no;
    $checked = $linedata['default_value'] ? " checked" : "";

    //echo " <tr bgcolor='$bgcolor'>\n";
    echo " <tr id='fld[" . attr($fld_line_no) . "]' class='" . ($fld_line_no % 2 ? 'even' : 'odd') . "'>\n";

    echo "  <td class='optcell'>";
    // tuck the group_name INPUT in here
    echo "<input type='hidden' name='fld[" . attr($fld_line_no) . "][group]' value='" .
         attr($linedata['group_id']) . "' class='optin' />";
    // Original field ID.
    echo "<input type='hidden' name='fld[" . attr($fld_line_no) . "][originalid]' value='" .
         attr($linedata['field_id']) . "' />";

    echo "<div class='input-group'><div class='input-group-prepend'><div class='input-group-text'><input type='checkbox' class='selectfield' " .
            "name='"  . attr($linedata['group_id']) . "~" . attr($linedata['field_id']) . "' " .
            "id='"    . attr($linedata['group_id']) . "~" . attr($linedata['field_id']) . "' " .
            "title='" . xla('Select field') . "' /></div></div>";

    echo "<input type='text' name='fld[" . attr($fld_line_no) . "][seq]' id='fld[" . attr($fld_line_no) . "][seq]' value='" .
        attr($linedata['seq']) . "' size='2' maxlength='4' class='form-control optin' />";
    echo "</td></div>\n";

    echo "  <td class='text-center optcell' $lbfonly>";
    echo "<select name='fld[" . attr($fld_line_no) . "][source]' class='form-control optin' $lbfonly>";
    foreach ($sources as $key => $value) {
        echo "<option value='" . attr($key) . "'";
        if ($key == $linedata['source']) {
            echo " selected";
        }

        echo ">" . text($value) . "</option>\n";
    }

    echo "</select>";
    echo "</td>\n";

    echo "  <td class='text-left optcell'>";
    echo "<input type='text' name='fld[" . attr($fld_line_no) . "][id]' value='" .
        attr($linedata['field_id']) . "' size='15' maxlength='31' " .
         "class='form-control optin' onclick='FieldIDClicked(this)' />";
    echo "</td>\n";

    echo "  <td class='text-center optcell'>";
    echo "<input type='text' id='fld[" . attr($fld_line_no) . "][title]' name='fld[" . attr($fld_line_no) . "][title]' value='" .
        attr($linedata['title']) . "' size='15' maxlength='3000' class='form-control optin' />";
    echo "</td>\n";

    // if not english and set to translate layout labels, then show the translation
    if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
        echo "<td class='text-center translation'>" . xlt($linedata['title']) . "</td>\n";
    }

    echo "  <td class='text-center optcell'>";
    echo "<select name='fld[" . attr($fld_line_no) . "][uor]' class='form-control optin'>";
    foreach ($UOR as $key => $value) {
        echo "<option value='" . attr($key) . "'";
        if ($key == $linedata['uor']) {
            echo " selected";
        }
        echo ">" . text($value) . "</option>\n";
    }

    echo "</select>";
    echo "</td>\n";

    echo "  <td class='text-center optcell'>";
    echo "<select class='form-control' name='fld[" . attr($fld_line_no) . "][datatype]' id='fld[" . attr($fld_line_no) . "][datatype]' onchange=NationNotesContext(" . attr_js($fld_line_no) . ",this.value)>";
    echo "<option value=''></option>";
    global $datatypes;
    global $sorted_datatypes;
    foreach ($sorted_datatypes as $key => $value) {
        if ($linedata['data_type'] == $key) {
            echo "<option value='" . attr($key) . "' selected>" . text($value) . "</option>";
        } else {
            echo "<option value='" . attr($key) . "'>" . text($value) . "</option>";
        }
    }

    echo "</select>";
    echo "  </td>";

    echo "  <td class='text-center optcell'>";

    if (
        in_array(
            $linedata['data_type'],
            array(1, 2, 3, 15, 21, 22, 23, 25, 26, 27, 28, 32, 33, 37, 40, 51, 52)
        )
    ) {
        // Show the width field
        echo "<input type='text' name='fld[" . attr($fld_line_no) . "][lengthWidth]' value='" .
        attr($linedata['fld_length']) .
        "' size='2' maxlength='10' class='form-control optin' title='" . xla('Width') . "' />";
        if (in_array($linedata['data_type'], array(3, 40))) {
            // Show the height field
            echo "<input type='text' name='fld[" . attr($fld_line_no) . "][lengthHeight]' value='" .
            attr($linedata['fld_rows']) .
            "' size='2' maxlength='10' class='form-control optin' title='" . xla('Height') . "' />";
        } else {
            // Hide the height field
            echo "<input type='hidden' name='fld[" . attr($fld_line_no) . "][lengthHeight]' value='' />";
        }
    } else {
      // all other data_types (hide both the width and height fields
        echo "<input type='hidden' name='fld[" . attr($fld_line_no) . "][lengthWidth]' value='' />";
        echo "<input type='hidden' name='fld[" . attr($fld_line_no) . "][lengthHeight]' value='' />";
    }

    echo "</td>\n";

    echo "  <td class='text-center optcell'>";
    echo "<input type='text' name='fld[" . attr($fld_line_no) . "][maxSize]' value='" .
      attr($linedata['max_length']) .
      "' size='1' maxlength='10' class='form-control optin' " .
      "title='" . xla('Maximum Size (entering 0 will allow any size)') . "' />";
    echo "</td>\n";

    echo "  <td class='text-center optcell'>";
    if (
        $linedata['data_type'] ==  1 || $linedata['data_type'] == 21 ||
        $linedata['data_type'] == 22 || $linedata['data_type'] == 23 ||
        $linedata['data_type'] == 25 || $linedata['data_type'] == 26 ||
        $linedata['data_type'] == 27 || $linedata['data_type'] == 32 ||
        $linedata['data_type'] == 33 || $linedata['data_type'] == 34 ||
        $linedata['data_type'] == 36 || $linedata['data_type'] == 37 ||
        $linedata['data_type'] == 43 || $linedata['data_type'] == 46
    ) {
        $type = "";
        $disp = "style='display: none'";
        if ($linedata['data_type'] == 34) {
            $type = "style='display: none'";
            $disp = "";
        }

        echo "<input type='text' name='fld[" . attr($fld_line_no) . "][list_id]'  id='fld[" . attr($fld_line_no) . "][list_id]' value='" .
        attr($linedata['list_id']) . "' " . $type .
        " size='6' maxlength='100' class='form-control optin listid' style='cursor: pointer;'" .
        "title='" . xla('Choose list') . "' />";

        echo "<select class='form-control' name='fld[" . attr($fld_line_no) . "][contextName]' id='fld[" . attr($fld_line_no) . "][contextName]' " . $disp . ">";
        $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
        while ($row = sqlFetchArray($res)) {
            $sel = '';
            if ($linedata['list_id'] == $row['cl_list_item_long']) {
                $sel = 'selected';
            }

            echo "<option value='" . attr($row['cl_list_item_long']) . "' " . $sel . ">" . text($row['cl_list_item_long']) . "</option>";
        }

        echo "</select>";
    } else {
      // all other data_types
        echo "<input type='hidden' name='fld[" . attr($fld_line_no) . "][list_id]' value='' />";
    }

    echo "</td>\n";

    //Backup List Begin
    echo "  <td class='text-center optcell'>";
    if (
        $linedata['data_type'] ==  1 || $linedata['data_type'] == 26 ||
        $linedata['data_type'] == 33 || $linedata['data_type'] == 36 ||
        $linedata['data_type'] == 43 || $linedata['data_type'] == 46
    ) {
        echo "<input type='text' name='fld[" . attr($fld_line_no) . "][list_backup_id]' value='" .
            attr($linedata['list_backup_id']) .
            "' size='3' maxlength='100' class='form-control optin listid' style='cursor:pointer;' />";
    } else {
        echo "<input type='hidden' name='fld[" . attr($fld_line_no) . "][list_backup_id]' value='' />";
    }

    echo "</td>\n";
    //Backup List End

    echo "  <td class='text-center optcell'>";
    echo "<input type='text' name='fld[" . attr($fld_line_no) . "][titlecols]' value='" .
         attr($linedata['titlecols']) . "' size='3' maxlength='10' class='form-control optin' />";
    echo "</td>\n";

    echo "  <td class='text-center optcell'>";
    echo "<input type='text' name='fld[" . attr($fld_line_no) . "][datacols]' value='" .
         attr($linedata['datacols']) . "' size='3' maxlength='10' class='form-control optin' />";
    echo "</td>\n";
    /* Below for compatibility with existing string modifiers. */
    if (!str_contains($linedata['edit_options'], ',') && isset($linedata['edit_options'])) {
        $t = json_decode($linedata['edit_options']);
        if (json_last_error() !== JSON_ERROR_NONE || $t === 0) { // hopefully string of characters and 0 handled.
            $t = str_split(trim($linedata['edit_options']));
            $linedata['edit_options'] = json_encode($t); // convert to array select understands.
        }
    }
    echo "  <td class='text-center optcell' title='" . xla("Add modifiers for this field type. You may select more than one.") . "'>";
    echo "<select id='fld[" . attr($fld_line_no) . "][edit_options]' name='fld[" . attr($fld_line_no) . "][edit_options][]' class='typeAddons optin' size='3' multiple data-set='" .
    attr(trim($linedata['edit_options'])) . "' ></select></td>\n";

    if ($linedata['data_type'] == 31) {
        echo "  <td class='text-center optcell'>";
        echo "<textarea name='fld[" . attr($fld_line_no) . "][desc]' rows='3' cols='35' class='form-control optin'>" .
           text($linedata['description']) . "</textarea>";
        echo "<input type='hidden' name='fld[" . attr($fld_line_no) . "][default]' value='" .
         attr($linedata['default_value']) . "' />";
        echo "</td>\n";
    } else {
        echo "  <td class='text-center optcell'>";
        echo "<input type='text' name='fld[" . attr($fld_line_no) . "][desc]' value='" .
        attr($linedata['description']) . "' size='20' class='form-control optin' />";
        echo "<input type='hidden' name='fld[" . attr($fld_line_no) . "][default]' value='" .
        attr($linedata['default_value']) . "' />";
        echo "</td>\n";
      // if not english and showing layout labels, then show the translation of Description
        if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
            echo "<td class='text-center translation'>" . xlt($linedata['description']) . "</td>\n";
        }
    }
    echo "  <td class='text-center optcell'>";
    echo "<input type='text' name='fld[" . attr($fld_line_no) . "][codes]' id='codes_fld[" . attr($fld_line_no) . "][codes]' value='" . attr($linedata['codes']) . "' title='" . xla('Code(s)') . "' onclick='select_clin_term_code(this)' size='10' maxlength='255' class='form-control optin' />";
    echo "</td>\n";

    // The "?" to click on for yet more field attributes.
    echo "  <td class='font-weight-bold' id='querytd_" . attr($fld_line_no) . "' style='cursor:pointer;";
    if (!empty($linedata['conditions']) || !empty($linedata['validation'])) {
        echo "background-color: var(--success);";
    }

    echo "' onclick='extShow(" . attr($fld_line_no) . ", this)' align='center' ";
    echo "title='" . xla('Click here to view/edit more details') . "'>";
    echo "&nbsp;?&nbsp;";
    echo "</td>\n";

    echo " </tr>\n";

    // Create a floating div for the additional attributes of this field.
    $conditions = empty($linedata['conditions']) ?
      array(0 => array('id' => '', 'itemid' => '', 'operator' => '', 'value' => '')) :
        unserialize($linedata['conditions'], ['allowed_classes' => false]);
    $action = empty($conditions['action']) ? 'skip' : $conditions['action'];
    $action_value = '';
    if ($action != 'skip') {
        $action_value = substr($action, 6);
        $action = substr($action, 0, 5); // "value" or "hsval"
    }
    //
    $extra_html .= "<div id='ext_" . attr($fld_line_no) . "' " .
      "style='width: 750px; border: 1px solid var(--black);" .
      "padding: 2px; background-color: var(--gray300); visibility: hidden;" .
      "z-index: 1000; left:-1000px; top:0; font-size: 0.6875rem;' class='position-absolute'>\n" .
      "<table class='w-100'>\n" .
      " <tr>\n" .
      "  <th colspan='3' class='text-left font-weight-bold'>" .
      xlt('For') . " " . text($linedata['field_id']) . " " .
      "<select class='form-control' name='fld[" . attr($fld_line_no) . "][action]' onchange='actionChanged(" . attr_js($fld_line_no) . ")'>" .
      "<option value='skip'  " . ($action == 'skip'  ? 'selected' : '') . ">" . xlt('hide this field') . "</option>" .
      "<option value='value' " . ($action == 'value' ? 'selected' : '') . ">" . xlt('set value to') . "</option>" .
      "<option value='hsval' " . ($action == 'hsval' ? 'selected' : '') . ">" . xlt('hide else set to') . "</option>" .
      "</select>" .
      "<input type='text' class='form-control' name='fld[" . attr($fld_line_no) . "][value]' value='" . attr($action_value) . "' size='15' />" .
      " " . xlt('if') .
      "</th>\n" .
      "  <th colspan='2' class='text-right text'><input class='btn btn-secondary' type='button' " .
      "value='" . xla('Close') . "' onclick='extShow(" . attr_js($fld_line_no) . ", false)' />&nbsp;</th>\n" .
      " </tr>\n" .
      " <tr class='text-left'>\n" .
      "  <th class='font-weight-bold'>" . xlt('Field ID') . "</th>\n" .
      "  <th class='font-weight-bold'>" . xlt('List item ID') . "</th>\n" .
      "  <th class='font-weight-bold'>" . xlt('Operator') . "</th>\n" .
      "  <th class='font-weight-bold'>" . xlt('Value if comparing') . "</th>\n" .
      "  <th class='font-weight-bold'>&nbsp;</th>\n" .
      " </tr>\n";
    // There may be multiple condition lines for each field.
    foreach ($conditions as $i => $condition) {
        if (!is_numeric($i)) {
            continue; // skip if 'action'
        }
        $extra_html .=
        " <tr>\n" .
        "  <td class='text-left'>\n" .
        "   <select class='form-control' name='fld[" . attr($fld_line_no) . "][condition_id][" . attr($i) . "]' onchange='cidChanged(" . attr_js($fld_line_no) . ", " . attr_js($i) . ")'>" .
        genFieldOptionList($condition['id']) . " </select>\n" .
        "  </td>\n" .
        "  <td class='text-left'>\n" .
        // List item choices are populated on the client side but will need the current value,
        // so we insert a temporary option here to hold that value.
        "   <select class='form-control' name='fld[" . attr($fld_line_no) . "][condition_itemid][" . attr($i) . "]'><option value='" .
        attr($condition['itemid']) . "'>...</option></select>\n" .
        "  </td>\n" .
        "  <td class='text-left'>\n" .
        "   <select class='form-control' name='fld[" . attr($fld_line_no) . "][condition_operator][" . attr($i) . "]'>\n";
        foreach (
            array(
            'eq' => xl('Equals'),
            'ne' => xl('Does not equal'),
            'se' => xl('Is selected'),
            'ns' => xl('Is not selected'),
            ) as $key => $value
        ) {
            $extra_html .= "    <option value='" . attr($key) . "'";
            if ($key == $condition['operator']) {
                $extra_html .= " selected";
            }

            $extra_html .= ">" . text($value) . "</option>\n";
        }

        $extra_html .=
        "   </select>\n" .
        "  </td>\n" .
        "  <td class='text-left' title='" . xla('Only for comparisons') . "'>\n" .
        "   <input type='text' class='form-control' name='fld[" . attr($fld_line_no) . "][condition_value][" . attr($i) . "]' value='" .
        attr($condition['value']) . "' size='15' maxlength='63' />\n" .
        "  </td>\n";
        if (!isset($conditions[$i + 1])) {
            $extra_html .=
            "  <td class='text-right' title='" . xla('Add a condition') . "'>\n" .
            "   <input type='button' class='btn btn-primary btn-sm' value='+' onclick='extAddCondition(" . attr_js($fld_line_no) . ",this)' />\n" .
            "  </td>\n";
        } else {
            $extra_html .=
            "  <td class='text-right'>\n" .
            "   <select class='form-control' name='fld[" . attr($fld_line_no) . "][condition_andor][" . attr($i) . "]'>\n";
            foreach (
                array(
                'and' => xl('And'),
                'or'  => xl('Or'),
                ) as $key => $value
            ) {
                $extra_html .= "    <option value='" . attr($key) . "'";
                if ($key == $condition['andor']) {
                    $extra_html .= " selected";
                }

                $extra_html .= ">" . text($value) . "</option>\n";
            }

            $extra_html .=
            "   </select>\n" .
            "  </td>\n";
        }

        $extra_html .=
        " </tr>\n";
    }

    $extra_html .=
      "</table>\n";

    $extra_html .=  "<table class='w-100'>\n" .
    " <tr>\n" .
    "  <td colspan='3' class='text-left font-weight-bold'>\"" . text($linedata['field_id']) . "\" " .
    xlt('will have the following validation rules') . ":</td>\n" .
    " </tr>\n" .
    " <tr>\n" .
    "  <td class='text-left font-weight-bold'>" . xlt('Validation rule') . "  </td>\n" .
    " </tr>\n" .
    " <tr>\n" .
    "  <td class='text-left' title='" . xla('Select a validation rule') . "'>\n" .


    "   <select class='form-control' name='fld[" . attr($fld_line_no) . "][validation]' onchange='valChanged(" . attr_js($fld_line_no) . ")'>\n" .
    "   <option value=''";
    if (empty($linedata['validation'])) {
        $extra_html .= " selected";
    }

        $extra_html .= ">-- " . xlt('Please Select') . " --</option>";
    foreach ($validations as $key => $value) {
        $extra_html .= "    <option value='" . attr($key) . "'";
        if ($key == $linedata['validation']) {
            $extra_html .= " selected";
        }

        $extra_html .= ">" . text($value) . "</option>\n";
    }

    $extra_html .= "</select>\n" .
        "  </td>\n";

     $extra_html .=
         "</table>\n" .
       "</div>\n";
}

// Generates <optgroup> and <option> tags for all layouts.
//
function genLayoutOptions($title = '?', $default = '')
{
    global $layouts;
    $s = "  <option value=''>" . text($title) . "</option>\n";
    $lastgroup = '';
    foreach ($layouts as $key => $value) {
        if ($value[0] != $lastgroup) {
            if ($lastgroup) {
                $s .= " </optgroup>\n";
            }
            $s .= " <optgroup label='" . attr($value[0]) . "'>\n";
            $lastgroup = $value[0];
        }
        $s .= "  <option value='" . attr($key) . "'";
        if ($key == $default) {
            $s .= " selected";
        }
        $s .= ">" . text($value[1]) . "</option>\n";
    }
    if ($lastgroup) {
        $s .= " </optgroup>\n";
    }
    return $s;
}

?>
<!DOCTYPE HTML>
<html>
<head>
  <?php Header::setupHeader(['select2']); ?>
  <title><?php echo xlt('Layout Editor'); ?></title>
  <style>
      .sticky-top {
          top: 80px;
          z-index: 999;
      }

    .orgTable tr.head {
        font-size: 0.6875rem;
        background-color: var(--gray400);
    }

    .orgTable tr.detail {
        font-size: 0.6875rem;
    }

    .orgTable td {
        font-size: 0.6875rem;
    }

    .orgTable input {
        font-size: 0.6875rem;
    }

    .orgTable select {
        font-size: 0.6875rem;
    }

    a,
    a:visited,
    a:hover {
        color: var(--primary);
    }

    .optin {
        background: transparent;
    }

    .group {
        margin: 0 0 11px 0;
        padding: 0;
        width: 100%;
    }

    .group table {
        border-collapse: collapse;
        width: 100%;

    }

    .orgTable .odd td {
        background-color: var(--gray300);
        padding: 3px 0px 3px 0px;
    }

    .orgTable .even td {
        background-color: var(--light);
        padding: 3px 0px 3px 0px;
    }

    .help {
        cursor: help;
    }

    .translation {
        color: var(--success);
        font-size: 0.6875rem;
    }

    .highlight * {
        border: 2px solid var(--primary);
        background-color: var(--yellow);
        color: var(--black);
    }

    .select2-container--default .select2-selection--multiple {
        cursor: pointer;
    }

    .select2-search__field {
        cursor: pointer;
        width: 0 !important;
    }

    /* Can't be responsive here cause of select2 */
    .select2-selection__choice {
        font-size: 0.75rem;
    }

    .select2-container {
        cursor: pointer;
        opacity: 0.99 !important;
    }

    .select2-dropdown {
        opacity: 0.99 !important;
    }

    .tips {
        display: none;
    }

    .select2-container--default .select2-selection--multiple {
        background-color: var(--white) !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: var(--light) !important;
    }
    .optin {
        color: var(--black) !important;
    }
  </style>
<script>

// Called when the "Include inactive" checkbox is clicked.
// This reloads the page, any edits are lost.
function inactiveClicked() {
  top.restoreSession();
  location.href = 'edit_layout.php?form_inactive=<?php echo $form_inactive ? "0" : "1"; ?>';
}

// Helper functions for positioning the floating divs.
function extGetX(elem) {
 var x = 0;
 while(elem != null) {
  x += elem.offsetLeft;
  elem = elem.offsetParent;
 }
 return x;
}
function extGetY(elem) {
 var y = 0;
 while(elem != null) {
  y += elem.offsetTop;
  elem = elem.offsetParent;
 }
 return y;
}

// Show or hide the "extras" div for a row.
var extdiv = null;
function extShow(lino, show) {
 var thisdiv = document.getElementById("ext_" + lino);
 if (extdiv) {
  extdiv.style.visibility = 'hidden';
  extdiv.style.left = '-1000px';
  extdiv.style.top = '0';
 }
 if (show && thisdiv != extdiv) {
  extdiv = thisdiv;
  var dw = window.innerWidth ? window.innerWidth - 20 : document.body.clientWidth;
  x = dw - extdiv.offsetWidth;
  if (x < 0) x = 0;
  var y = extGetY(show) + show.offsetHeight;
  extdiv.style.left = x + 'px';
  extdiv.style.top  = y + 'px';
  extdiv.style.visibility = 'visible';
 }
 else {
  extdiv = null;
 }
}

// Show or hide the value field for a "Set value to" condition.
function actionChanged(lino) {
  var f = document.forms[0];
  var eaction = f['fld[' + lino + '][action]'];
  var evalue  = f['fld[' + lino + '][value]'];
  evalue.style.display = eaction.value == 'skip' ? 'none' : '';
}

// Add an extra condition line for the given row.
function extAddCondition(lino, btnelem) {
  var f = document.forms[0];
  var i = 0;

  // Get index of next condition line.
  while (f['fld[' + lino + '][condition_id][' + i + ']']) ++i;
  if (i == 0) alert('f["fld[' + lino + '][condition_id][' + i + ']"]' + <?php echo xlj('not found') ?>);

  // Get containing <td>, <tr> and <table> nodes of the "+" button.
  var tdplus = btnelem.parentNode;
  var trelem = tdplus.parentNode;
  var telem  = trelem.parentNode;

  // Replace contents of the tdplus cell.
  tdplus.innerHTML =
    "<select class='form-control' name='fld[" + lino + "][condition_andor][" + (i-1) + "]'>" +
    "<option value='and'>" + jsText(<?php echo xlj('And') ?>) + "</option>" +
    "<option value='or' >" + jsText(<?php echo xlj('Or') ?>) + "</option>" +
    "</select>";

  // Add the new row.
  var newtrelem = telem.insertRow(i+2);
  newtrelem.innerHTML =
    "<td class='text-left'>" +
    "<select class='form-control' name='fld[" + lino + "][condition_id][" + i + "]' onchange='cidChanged(" + lino + "," + i + ")'>" +
    <?php echo js_escape(genFieldOptionList()) ?> +
    "</select>" +
    "</td>" +
    "<td class='text-left'>" +
    "<select class='form-control' name='fld[" + lino + "][condition_itemid][" + i + "]' style='display:none' />" +
    "</td>" +
    "<td class='text-left'>" +
    "<select class='form-control' name='fld[" + lino + "][condition_operator][" + i + "]'>" +
    "<option value='eq'>" + jsText(<?php echo xlj('Equals') ?>) + "</option>" +
    "<option value='ne'>" + jsText(<?php echo xlj('Does not equal') ?>) + "</option>" +
    "<option value='se'>" + jsText(<?php echo xlj('Is selected') ?>) + "</option>" +
    "<option value='ns'>" + jsText(<?php echo xlj('Is not selected') ?>) + "</option>" +
    "</select>" +
    "</td>" +
    "<td class='text-left'>" +
    "<input type='text' class='form-control' name='fld[" + lino + "][condition_value][" + i + "]' value='' size='15' maxlength='63' />" +
    "</td>" +
    "<td class='text-right'>" +
    "<input type='button' class='btn btn-primary btn-sm' value='+' onclick='extAddCondition(" + lino + ",this)' />" +
    "</td>";
}

// This is called when a field ID is chosen for testing within a skip condition.
// It checks to see if a corresponding list item must also be chosen for the test, and
// if so then inserts the dropdown for selecting an item from the appropriate list.
function setListItemOptions(lino, seq, init) {
  var f = document.forms[0];
  var target = 'fld[' + lino + '][condition_itemid][' + seq + ']';
  // field_id is the ID of the field that the condition will test.
  var field_id = f['fld[' + lino + '][condition_id][' + seq + ']'].value;
  if (!field_id) {
    f[target].options.length = 0;
    f[target].style.display = 'none';
    return;
  }
  // Find the occurrence of that field in the layout.
  var i = 1;
  while (true) {
    var idname = 'fld[' + i + '][id]';
    if (!f[idname]) {
      alert(<?php echo xlj('Condition field not found') ?> + ': ' + field_id);
      return;
    }
    if (f[idname].value == field_id) break;
    ++i;
  }
  // If this is startup initialization then preserve the current value.
  var current = init ? f[target].value : '';
  f[target].options.length = 0;
  // Get the corresponding data type and list ID.
  var data_type = f['fld[' + i + '][datatype]'].value;
  var list_id   = f['fld[' + i + '][list_id]'].value;
  // WARNING: If new data types are defined the following test may need enhancing.
  // We're getting out if the type does not generate multiple fields with different names.
  if (data_type != '21' && data_type != '22' && data_type != '23' && data_type != '25' && data_type != '37') {
    f[target].style.display = 'none';
    return;
  }
  // OK, list item IDs do apply so go get 'em.
  // This happens asynchronously so the generated code needs to stand alone.
  f[target].style.display = '';
  $.getScript('layout_listitems_ajax.php' +
    '?listid='  + encodeURIComponent(list_id) +
    '&target='  + encodeURIComponent(target)  +
    '&current=' + encodeURIComponent(current) +
    '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>);
}

// This is called whenever a condition's field ID selection is changed.
function cidChanged(lino, seq) {
  changeColor(lino);
  setListItemOptions(lino, seq, false);
}

// This invokes the popup to edit layout properties or add a new layout.
function edit_layout_props(groupid) {
 var title = <?php echo xlj('Layout Properties');?>;
 dlgopen('edit_layout_props.php?layout_id=' + <?php echo js_url($layout_id); ?> + '&group_id=' + encodeURIComponent(groupid),
  '_blank', 775, 550, "", title);
}

// callback from edit_layout_props.php:
function refreshme(layout_id) {
 location.href = 'edit_layout.php?layout_id=' + encodeURIComponent(layout_id);
}

// This is called whenever a validation rule field ID selection is changed.
function valChanged(lino) {
    changeColor(lino);
}

function changeColor(lino){
    var thisid = document.forms[0]['fld[' + lino + '][condition_id][0]'].value;
    var thisValId = document.forms[0]['fld[' + lino + '][validation]'].value;
    var thistd = document.getElementById("querytd_" + lino);
    if(thisid !='' || thisValId!='') {
        thistd.style.backgroundColor = 'var(--success)';
    }else{
        thistd.style.backgroundColor = '';
    }
}

// Call this to disable the warning about unsaved changes and submit the form.
function mySubmit() {
 somethingChanged = false;
 top.restoreSession();
 document.forms[0].submit();
}

// User is about to do something that would discard any unsaved changes.
// Return true if that is OK.
function myChangeCheck() {
  if (somethingChanged) {
    if (!confirm(<?php echo xlj('You have unsaved changes. Abandon them?'); ?>)) {
      return false;
    }
    // Do not set somethingChanged to false here because if they cancel the
    // action then the previously changed values will still be of interest.
  }
  return true;
}

</script>

</head>

<body class="body_top admin-layout">
<form method='post' name='theform' id='theform' action='edit_layout.php'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type="hidden" name="formaction" id="formaction" value="" />
<!-- elements used to identify a field to delete -->
<input type="hidden" name="deletefieldid" id="deletefieldid" value="" />
<input type="hidden" name="deletefieldgroup" id="deletefieldgroup" value="" />
<!-- elements used to identify a group to delete -->
<input type="hidden" name="deletegroupid" id="deletegroupid" value="">
<!-- elements used to change the group order -->
<input type="hidden" name="movegroupname" id="movegroupname" value="" />
<input type="hidden" name="movedirection" id="movedirection" value="" />
<!-- elements used to select more than one field -->
<input type="hidden" name="selectedfields" id="selectedfields" value="" />
<input type="hidden" id="targetgroup" name="targetgroup" value="" />
<input type="hidden" id="targetlayout" name="targetlayout" value="" />

<div class="fixed-top py-2 px-1 bg-light text-dark">
<strong><?php echo xlt('Edit layout'); ?>:</strong>&nbsp;
<select name='layout_id' id='layout_id' class='form-control form-control-sm d-inline-block' style='margin-bottom:5px; width:20%;'>
<?php echo genLayoutOptions('-- ' . xl('Select') . ' --', $layout_id); ?>
</select>

&nbsp;
<label><input type='checkbox' name='form_inactive' value='1'
 title='<?php echo xla('This will abandon any edits!'); ?>'
 onclick='inactiveClicked()' <?php if ($form_inactive) {
        echo 'checked';} ?> />
<?php echo xlt('Include inactive'); ?></label>

<?php if ($layout_id) { ?>
<div class="btn-group ml-auto">
    <button type='button' class='btn btn-secondary btn-sm' onclick='edit_layout_props("")'><?php echo xla('Layout Properties'); ?></button>
    <button type='button' class='btn btn-secondary btn-sm addgroup' id='addgroup'><?php echo xla('Add Group'); ?></button>
    <button type='button' class="btn btn-primary btn-save btn-sm" name='save' id='save'><?php echo xla('Save Changes'); ?></button>
</div>
<br>
    <?php echo xlt('With selected');?>:&nbsp;
<input type='button' class='btn btn-secondary btn-sm' name='deletefields' id='deletefields' value='<?php echo xla('Delete'); ?>' disabled="disabled" />
<input type='button' class='btn btn-secondary btn-sm' name='movefields' id='movefields' value='<?php echo xla('Move to...'); ?>' disabled="disabled" />
<select id='copytolayout' class='form-control form-control-sm d-inline-block'
 style='width:20%;' disabled="disabled" onchange="CopyToLayout(this)">
    <?php echo genLayoutOptions(xl('Copy to Layout...')); ?>
</select>
&nbsp;&nbsp;&nbsp;
<input type='button' class='btn btn-secondary btn-sm' value='<?php echo xla('Tips'); ?>' onclick='$("#tips").toggle();' />&nbsp;
<input type='button' class='btn btn-secondary btn-sm' value='<?php echo xla('Encounter Preview'); ?>' onclick='layoutLook();' />
<?php } else { ?>
<button type='button' class='btn btn-primary btn-sm btn-add btn-new' onclick='edit_layout_props("")'><?php echo xla('New Layout'); ?></button>
<?php } ?>

<div id="tips" class="container tips">
  <section class="card bg-light p-3">
  <header class="card-heading">
   <h3 class="card-title"><?php echo xlt('Usage Tips') ?></h3>
  </header>
  <div class="card-body">
   <ul>
<?php
    echo "<li>" . xlt("Clicking Options will present a multiselection drop menu to add behaviors to the selected data type. Typing after pull down activates allows search in options.") . "</li>";
    echo "<li>" . xlt("The option Span Entire Row is useful when using Static Text in allowing text to wrap and span entire row regardless of column settings. Another use could be to create an empty row as spacer or add additional option Add Bottom Border to create a line break.Only Bottom Border Row is useful here.") . "</li>";
    echo "<li>" . xlt("The options for Outline and Border will either wrap a row in thin border or add a border to the bottom of an item.") . "</li>";
    echo "<li>" . xlt("If a field's Label Col = 0 the label will immediately follow the previous data field in the Order sequence, on the same line as the Data field.") . "</li>";
    echo "<li>" . xlt("If a field's Data Col = 0 the data field will immediately follow its label field on the same line") . "</li>";
    echo "<li>" . xlt("If a field's Label Col = 1 the label field will go to a new line unless the previous field's total column values (Label + Data) is less than number of Layout columns from Group Properties or Layout Properties.") . "</li>";
    echo "<li>" . xlt("Generally, the first field in a group should be Label Cols = 1 Data Cols = number of Layout columns from Group Properties.") . "</li>";
    echo "<li>" . xlt("Make subsequent fields in the same row, Label = 0 Data = 0 and ensure enough columns are available from previous items to allow space for this new item. Otherwise result could be unpredictable") . "</li>";
    echo "<li>" . xlt("The Encounter Preview button is useful for showing encounter type layout forms as seen when using form in an encounter. Note, this feature is only useful for showing encounter forms and won't display system forms like Demographics") . "</li>";
    //echo "<li>" . xlt("") . "</li>";
    echo "<li>" . xlt("Please see http://www.open-emr.org/wiki/index.php/LBV_Forms for more on this topic") . "</li>";
?>
   </ul>
   <button class='btn btn-success btn-sm float-right' onclick='$("#tips").toggle();return false;'><?php echo xlt('Dismiss')?></button>
  </div>
</section></div></div>
<?php
// Load array of properties for this layout and its groups.
$grparr = array();
$gres = sqlStatement("SELECT * FROM layout_group_properties WHERE grp_form_id = ? " .
  "ORDER BY grp_group_id", array($layout_id));
while ($grow = sqlFetchArray($gres)) {
    $grparr[$grow['grp_group_id']] = $grow;
}

$prevgroup = "!@#asdf1234"; // an unlikely group ID
$firstgroup = true; // flag indicates it's the first group to be displayed

// Get the selected form's elements.
if ($layout_id) {
    $res = sqlStatement(
        "SELECT p.grp_group_id, l.* FROM layout_group_properties AS p " .
        "LEFT JOIN layout_options AS l ON l.form_id = p.grp_form_id AND l.group_id = p.grp_group_id " .
        "WHERE p.grp_form_id = ? " .
        "ORDER BY p.grp_group_id, l.seq, l.field_id",
        array($layout_id)
    );
    while ($row = sqlFetchArray($res)) {
        $group_id = $row['grp_group_id'];
        // Skip if this is the top level layout and (as expected) it has no fields.
        if ($group_id === '' && empty($row['form_id'])) {
            continue;
        }
        if ($group_id != $prevgroup) {
            if ($firstgroup == false) {
                echo "</tbody></table></div>\n";
                echo "<div id='" . attr($group_id) . "' class='group'>";
            } else {
                // making first group flag useful for maintaining top fixed nav bar.
                echo "<div id='" . attr($group_id) . "' class='group' style='padding-top:80px'>";
            }

            // echo "<div id='" . $group_id . "' class='group'>";
            echo "<div class='text bold layouts_title'>";

            // Get the fully qualified descriptive name of this group (i.e. including ancestor names).
            $gdispname = '';
            for ($i = 1; $i <= strlen($group_id); ++$i) {
                if ($gdispname) {
                    $gdispname .= ' / ';
                }
                $gdispname .= $grparr[substr($group_id, 0, $i)]['grp_title'];
            }
            $gmyname = $grparr[$group_id]['grp_title'];

            $group_id_attr = attr($group_id);
            $group_id_attr_js = attr_js($group_id);
            $t_vars = [
                "xla_add_field" => xla("Add Field"),
                "xla_rename_group" => xla("Rename Group"),
                "xla_delete_group" => xla("Delete Group"),
                "xla_move_up" => xla("Move Up"),
                "xla_move_down" => xla("Move Down"),
                "xla_group_props" => xla("Group Properties"),
                'text_group_name' => text($gdispname),
                'translate_layout' => ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) ? xlt($gdispname) : "",
                'attr_gmyname' => attr($gmyname),
            ];
            echo <<<HTML
            <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
                <span class="navbar-brand">{$t_vars['translate_layout']}&nbsp;{$t_vars['text_group_name']}</span>
                <div class="btn-toolbar" role="toolbar" aria-label="Group Toolbar">
                    <div class="btn-group mr-2" role="group" aria-label="Field Group">
                        <button type="button" class="addfield btn btn-secondary btn-add btn-sm" id="addto~{$group_id_attr}">{$t_vars['xla_add_field']}</button>
                    </div>
                    <div class="btn-group ml-2 mr-2" role="group" aria-label="Move Group">
                        <button type="button" class="movegroup btn btn-secondary btn-sm" id="{$group_id_attr}~up"><i class="fa fa-angle-up"></i>&nbsp;{$t_vars['xla_move_up']}</button>
                        <button type="button" class="movegroup btn btn-secondary btn-sm" id="{$group_id_attr}~down"><i class="fa fa-angle-down"></i>&nbsp;{$t_vars['xla_move_down']}</button>
                    </div>
                    <div class="btn-group mr-2" role="group" aria-label="Group Options">
                        <button type="button" class="renamegroup btn btn-secondary btn-sm" id="{$group_id_attr}~{$t_vars['attr_gmyname']}">{$t_vars['xla_rename_group']}</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="edit_layout_props({$group_id_attr_js})">{$t_vars['xla_group_props']}</button>
                        <button type="button" class="deletegroup btn btn-secondary text-danger btn-sm" id="{$group_id_attr}">{$t_vars['xla_delete_group']}</button>
                    </div>
                </div>

            </nav>
            HTML;
            $firstgroup = false;
            if (!empty($row['form_id'])) { // if this is not an empty group
                ?>

      <div class="table-responsive">
      <table class='table table-sm table-striped'>
      <thead>
       <tr class='head'>
        <th><?php echo xlt('Order{{Sequence}}'); ?></th>
        <th <?php echo " $lbfonly"; ?>><?php echo xlt('Source'); ?></th>
        <th><?php echo xlt('ID'); ?>&nbsp;<span class="help" title='<?php echo xla('A unique value to identify this field, not visible to the user'); ?>' >(?)</span></th>
        <th><?php echo xlt('Label'); ?>&nbsp;<span class="help" title='<?php echo xla('The label that appears to the user on the form'); ?>' >(?)</span></th>
                <?php // if not english and showing layout label translations, then show translation header for title
                if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
                    echo "<th>" . xlt('Translation') . "<span class='help' title='" . xla('The translated label that will appear on the form in current language') . "'>&nbsp;(?)</span></th>";
                } ?>
          <th><?php echo xlt('UOR'); ?></th>
          <th><?php echo xlt('Data Type'); ?></th>
          <th><?php echo xlt('Size'); ?></th>
          <th><?php echo xlt('Max Size'); ?></th>
          <th><?php echo xlt('List'); ?></th>
          <th><?php echo xlt('Backup List'); ?></th>
          <th ><?php echo xlt('Label Cols'); ?></th>
          <th><?php echo xlt('Data Cols'); ?></th>
          <th><?php echo xlt('Options'); ?></th>
          <th><?php echo xlt('Description'); ?></th>
                <?php // if not english and showing layout label translations, then show translation header for description
                if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
                    echo "<th>" . xlt('Translation') . "<span class='help' title='" . xla('The translation of description in current language') . "'>&nbsp;(?)</span></th>";
                } ?>
          <th><?php echo xlt('Code(s)'); ?></th>
          <th style='width:1%'><?php echo xlt('?'); ?></th>
       </tr>
      </thead>
      <tbody>

                <?php
            } // end not empty group
        } // end new group

        if (!empty($row['form_id'])) {
            writeFieldLine($row);
        }
        $prevgroup = $group_id;
    } // end while loop
} // end if $layout_id

?>
</tbody>
</table>
</div>

<?php echo $extra_html; ?>

</form>

<!-- template DIV that appears when user chooses to rename an existing group -->
<div id="renamegroupdetail" class="bg-light p-3" style="border: 1px solid black; display: none; visibility: hidden;">
<input type="hidden" name="renameoldgroupname" id="renameoldgroupname" value="" />
<div class="form-group">
    <label for="renamegroupname"><?php echo xlt('Group Name'); ?>:</label>
    <input type="text" class="form-control" size="20" maxlength="30" name="renamegroupname" id="renamegroupname" />
</div>
<div class="form-group">
    <label for="renamegroupparent"><?php echo xlt('Parent'); ?>:</label>
    <?php echo genGroupSelector('renamegroupparent', $layout_id); ?>
</div>
<div class="btn-group">
    <input type="button" class="btn btn-primary btn-sm saverenamegroup btn-save" value="<?php echo xla('Rename Group'); ?>" />
    <input type="button" class="btn btn-secondary btn-sm cancelrenamegroup" value="<?php echo xla('Cancel'); ?>" />
</div>
</div>

<!-- template DIV that appears when user chooses to add a new group -->
<div id="groupdetail" style="border: 1px solid black; padding: 3px; display: none; visibility: hidden; background-color: var(--gray);">
<span class='font-weight-bold'>
<?php echo xlt('Group Name'); ?>:
<input type="text" size="20" maxlength="30" name="newgroupname" id="newgroupname" />
&nbsp;&nbsp;
<?php echo xlt('Parent'); ?>:
<?php echo genGroupSelector('newgroupparent', $layout_id); ?>
<br />

<input type="button" class="btn btn-primary btn-sm savenewgroup" value='<?php echo xla('Save New Group'); ?>' />
<input type="button" class="btn btn-secondary btn-sm cancelnewgroup" value='<?php echo xla('Cancel'); ?>' />
</span>
</div>

<!-- template DIV that appears when user chooses to add a new field to a group -->
<div id="fielddetail" class="fielddetail" style="display: none; visibility: hidden">
<input type="hidden" name="newfieldgroupid" id="newfieldgroupid" value="" />
<div class="table-responsive">
<table class="table table-sm" style="border-collapse: collapse;">
 <thead>
  <tr class='head'>
   <th><?php echo xlt('Order{{Sequence}}'); ?></th>
   <th <?php echo " $lbfonly"; ?>><?php echo xlt('Source'); ?></th>
   <th><?php echo xlt('ID'); ?>&nbsp;<span class="help" title='<?php echo xla('A unique value to identify this field, not visible to the user'); ?>' >(?)</span></th>
   <th><?php echo xlt('Label'); ?>&nbsp;<span class="help" title='<?php echo xla('The label that appears to the user on the form'); ?>' >(?)</span></th>
   <th><?php echo xlt('UOR'); ?></th>
   <th><?php echo xlt('Data Type'); ?></th>
   <th><?php echo xlt('Size Width'); ?></th>
   <th><?php echo xlt('Size Height'); ?></th>
   <th><?php echo xlt('Max Size'); ?></th>
   <th><?php echo xlt('List'); ?></th>
   <th><?php echo xlt('Backup List'); ?></th>
   <th><?php echo xlt('Label Cols'); ?></th>
   <th><?php echo xlt('Data Cols'); ?></th>
   <th><?php echo xlt('Options'); ?></th>
   <th><?php echo xlt('Description'); ?></th>
   <th><?php echo xlt('Code(s)'); ?></th>
  </tr>
 </thead>
 <tbody>
  <tr class='text-center'>
   <td><input type="text" class="form-control" name="newseq" id="newseq" value="" size="2" maxlength="4" /> </td>
   <td<?php echo " $lbfonly"; ?>>
    <select class='form-control' name='newsource' id='newsource'>
<?php
foreach ($sources as $key => $value) {
    echo "    <option value='" . attr($key) . "'>" . text($value) . "</option>\n";
}
?>
    </select>
   </td>
   <td ><input type="text" class="form-control" name="newid" id="newid" value="" size="10" maxlength="31" onclick='FieldIDClicked(this)' /> </td>
   <td><input type="text" class="form-control" name="newtitle" id="newtitle" value="" size="20" maxlength="63" /> </td>
   <td>
    <select class='form-control' name="newuor" id="newuor">
     <option value="0"><?php echo xlt('Unused'); ?></option>
     <option value="1" selected><?php echo xlt('Optional'); ?></option>
     <option value="2"><?php echo xlt('Required'); ?></option>
    </select>
   </td>
   <td align='center'>
    <select class='form-control' name='newdatatype' id='newdatatype'>
     <option value=''></option>
<?php
global $sorted_datatypes;
foreach ($sorted_datatypes as $key => $value) {
    echo "     <option value='" . attr($key) . "'>" . text($value) . "</option>\n";
}
?>
    </select>
   </td>
   <td><input class='form-control' type="text" name="newlengthWidth" id="newlengthWidth" value="" size="1" maxlength="3" title="<?php echo xla('Width'); ?>" /></td>
   <td><input class='form-control' type="text" name="newlengthHeight" id="newlengthHeight" value="" size="1" maxlength="3" title="<?php echo xla('Height'); ?>" /></td>
   <td><input class='form-control' type="text" name="newmaxSize" id="newmaxSize" value="" size="1" maxlength="3" title="<?php echo xla('Maximum Size (entering 0 will allow any size)'); ?>" /></td>
   <td><input type="text" name="newlistid" id="newlistid" value="" size="8" maxlength="31" class="form-control listid" />
       <select class='form-control' name='contextName' id='contextName' style='display:none'>
        <?php
        $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
        while ($row = sqlFetchArray($res)) {
            echo "<option value='" . attr($row['cl_list_item_long']) . "'>" . text($row['cl_list_item_long']) . "</option>";
        }
        ?>
       </select>
   </td>
   <td><input type="text" name="newbackuplistid" id="newbackuplistid" value="" size="8" maxlength="31" class="form-control listid" /></td>
   <td><input class='form-control' type="text" name="newtitlecols" id="newtitlecols" value="" size="3" maxlength="3" /> </td>
   <td><input class='form-control' type="text" name="newdatacols" id="newdatacols" value="" size="3" maxlength="3" /> </td>
   <td><select name="newedit_options[]" id="newedit_options"  multiple class='form-control typeAddons'></select>
       <input type="hidden"  name="newdefault" id="newdefault" value="" /> </td>
   <td><input type="text" class='form-control' name="newdesc" id="newdesc" value="" size="20" /> </td>
   <td><input type='text' class='form-control' name="newcodes" id="newcodes" value="" onclick='select_clin_term_code(this)' size='10' maxlength='255' /> </td>
  </tr>
  <tr>
   <td colspan="9">
    <input type="button" class="btn btn-primary btn-sm savenewfield" value='<?php echo xla('Save New Field'); ?>' />
    <input type="button" class="btn btn-secondary btn-sm cancelnewfield" value='<?php echo xla('Cancel'); ?>' />
   </td>
  </tr>
 </tbody>
</table>
</div>
</div>

<script>
/* Field modifier objects - heading towards context based.
    Used by Select2 so rtl may be enabled*/
<?php echo "var fldOptions = [";
echo "{id: 'EP',text:" . xlj('Exclude in Portal') . "},
    {id: 'A',text:" . xlj('Age') . ",ctx:['4'],ctxExcp:['0']},
    {id: 'B',text:" . xlj('Gestational Age') . ",ctx:['4'],ctxExcp:['0']},
    {id: 'F',text:" . xlj('Add Time to Date') . ",ctx:['4'],ctxExcp:['0']},
    {id: 'C',text:" . xlj('Capitalize') . ",ctx:['0'],ctxExcp:['4','15','40']},
    {id: 'D',text:" . xlj('Dup Check') . "},
    {id: 'E',text:" . xlj('Dup Check on only Edit, or Extra billing codes OK') . "},
    {id: 'W',text:" . xlj('Dup Check on only New') . "},
    {id: 'G',text:" . xlj('Graphable') . "},
    {id: 'J',text:" . xlj('Jump to Next Row') . "},
    {id: 'K',text:" . xlj('Prepend Blank Row') . "},
    {id: 'L',text:" . xlj('Lab Order') . "},
    {id: 'M',text:" . xlj('Radio Group Master') . "},
    {id: 'm',text:" . xlj('Radio Group Member') . "},
    {id: 'N',text:" . xlj('New Patient Form') . "},
    {id: 'O',text:" . xlj('Order Processor') . "},
    {id: 'P',text:" . xlj('Default to previous value') . "},
    {id: 'R',text:" . xlj('Distributor') . "},
    {id: 'T',text:" . xlj('Description is default text') . "},
    {id: 'DAP',text:" . xlj('Description is Placeholder') . "},
    {id: 'U',text:" . xlj('Capitalize all') . "},
    {id: 'V',text:" . xlj('Vendor') . "},
    {id: 'X',text:" . xlj('Do Not Print') . "},
    {id:'grp',text:" . xlj('Stylings') . ",children:[
        {id: 'RS',text:" . xlj('Add Bottom Border Row') . "},
        {id: 'RO',text:" . xlj('Outline Entire Row') . "},
        {id: 'DS',text:" . xlj('Add Data Bottom Border') . "},
        {id: 'DO',text:" . xlj('Outline Data Col') . "},
        {id: 'SP',text:" . xlj('Span Entire Row') . "}
    ]},
    {id: '0',text:" . xlj('Read Only') . "},
    {id: '1',text:" . xlj('Write Once') . "},
    {id: '2',text:" . xlj('Billing Code Descriptions') . "}];\n";

// Language direction for select2
echo 'var langDirection = ' . js_escape($_SESSION['language_direction']) . ';';
?>

// used when selecting a list-name for a field
var selectedfield;

// Support for beforeunload handler.
var somethingChanged = false;

// Get the next logical sequence number for a field in the specified group.
// Note it guesses and uses the existing increment value.
function getNextSeq(group) {
  var f = document.forms[0];
  var seq = 0;
  var delta = 10;
  for (var i = 1; true; ++i) {
    var gelem = f['fld[' + i + '][group]'];
    if (!gelem) break;
    if (gelem.value != group) continue;
    var tmp = parseInt(f['fld[' + i + '][seq]'].value);
    if (isNaN(tmp)) continue;
    if (tmp <= seq) continue;
    delta = tmp - seq;
    seq = tmp;
  }
  return seq + delta;
}

// Helper function for validating new fields.
function validateNewField(idpfx) {
  var f = document.forms[0];
  var pfx = '#' + idpfx;
  var newid = $(pfx + "id").val();

  // seq must be numeric and <= 9999
  if (! IsNumeric($(pfx + "seq").val(), 0, 9999)) {
      alert(<?php echo xlj('Order must be a number between 1 and 9999'); ?>);
      return false;
  }
  // length must be numeric and less than 999
  if (! IsNumeric($(pfx + "lengthWidth").val(), 0, 999)) {
      alert(<?php echo xlj('Size must be a number between 1 and 999'); ?>);
      return false;
  }
  // titlecols must be numeric and less than 100
  if (! IsNumeric($(pfx + "titlecols").val(), 0, 999)) {
      alert(<?php echo xlj('LabelCols must be a number between 1 and 999'); ?>);
      return false;
  }
  // datacols must be numeric and less than 100
  if (! IsNumeric($(pfx + "datacols").val(), 0, 999)) {
      alert(<?php echo xlj('DataCols must be a number between 1 and 999'); ?>);
      return false;
  }
  // the id field can only have letters, numbers and underscores
  if ($(pfx + "id").val() == "") {
      alert(<?php echo xlj('ID cannot be blank'); ?>);
      return false;
  }

  // Make sure the field ID is not duplicated.
  for (var j = 1; f['fld[' + j + '][id]']; ++j) {
    if (newid.toLowerCase() == f['fld[' + j + '][id]'].value.toLowerCase() ||
      newid.toLowerCase() == f['fld[' + j + '][originalid]'].value.toLowerCase())
    {
      alert(<?php echo xlj('Error: Duplicated field ID'); ?> + ': ' + newid);
      return false;
    }
  }

  // the id field can only have letters, numbers and underscores
  var validid = $(pfx + "id").val().replace(/(\s|\W)/g, "_"); // match any non-word characters and replace them
  $(pfx + "id").val(validid);
  // similarly with the listid field
  validid = $(pfx + "listid").val().replace(/(\s|\W)/g, "_");
  $(pfx + "listid").val(validid);
  // similarly with the backuplistid field
  validid = $(pfx + "backuplistid").val().replace(/(\s|\W)/g, "_");
  $(pfx + "backuplistid").val(validid);

  return true;
}

// jQuery stuff to make the page a little easier to use

$(function () {

    $(function () {
        $('.typeAddons').select2({
            data: fldOptions,
            theme: 'default',
            multiple: true,
            closeOnSelect: false,
            width:'100%',
            minimumResultsForSearch: 'Infinity',
            containerCssClass: ':all:',
            allowClear: false,
            <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
        });
    });
      // Populate field option selects
    $(function () {
      $('.typeAddons').each(function(i, obj) {
          var v = $(this).data('set')
          if(typeof v !== 'undefined' && v > ""){
            $(this).val(v).trigger("change")
          }
      });
      somethingChanged = false;
    });

    $("#save").click(function() { SaveChanges(); });
    $("#layout_id").change(function() {
      if (!myChangeCheck()) {
        $("#layout_id").val(<?php echo js_escape($layout_id); ?>);
        return;
      }
      mySubmit();
    });
    $(".addgroup").click(function() { AddGroup(this); });
    $(".savenewgroup").click(function() { SaveNewGroup(this); });
    $(".deletegroup").click(function() { DeleteGroup(this); });
    $(".cancelnewgroup").click(function() { CancelNewGroup(this); });
    $(".movegroup").click(function() { MoveGroup(this); });
    $(".renamegroup").click(function() { RenameGroup(this); });
    $(".saverenamegroup").click(function() { SaveRenameGroup(this); });
    $(".cancelrenamegroup").click(function() { CancelRenameGroup(this); });
    $(".addfield").click(function() { AddField(this); });
    $("#deletefields").click(function() { DeleteFields(this); });
    $(".selectfield").click(function() {
        var TRparent = $(this).parent().parent();
        $(TRparent).children("td").toggleClass("highlight");
        // disable the delete-move buttons
        $("#deletefields").attr("disabled", "disabled");
        $("#movefields").attr("disabled", "disabled");
        $("#copytolayout").attr("disabled", "disabled");
        $(".selectfield").each(function(i) {
            // if any field is selected, enable the delete-move buttons
            if ($(this).prop("checked") == true) {
                $("#deletefields").removeAttr("disabled");
                $("#movefields").removeAttr("disabled");
                $("#copytolayout").removeAttr("disabled");
            }
        });
    });
    $("#movefields").click(function() { ShowGroups(this); });
    $(".savenewfield").click(function() { SaveNewField(this); });
    $(".cancelnewfield").click(function() { CancelNewField(this); });
    $("#newtitle").blur(function() { if ($("#newid").val() == "") $("#newid").val($("#newtitle").val()); });
    $("#newdatatype").change(function() { ChangeList(this.value);});
    $(".listid").click(function() { ShowLists(this); });

    // special class that skips the element
    $(".noselect").focus(function() { $(this).blur(); });

    // Save the changes made to the form
    var SaveChanges = function () {
      var f = document.forms[0];
      for (var i = 1; f['fld['+i+'][id]']; ++i) {
        var ival = f['fld['+i+'][id]'].value;
        for (var j = i + 1; f['fld['+j+'][id]']; ++j) {
          if (ival.toLowerCase() == f['fld['+j+'][id]'].value.toLowerCase() ||
            ival.toLowerCase() == f['fld['+j+'][originalid]'].value.toLowerCase())
          {
            alert(<?php echo xlj('Error: Duplicated field ID'); ?> + ': ' + ival);
            return;
          }
        }
      }
      $("#formaction").val("save");
      mySubmit();
    }

    /****************************************************/
    /************ Group functions ***********************/
    /****************************************************/

    // display the 'new group' DIV
    var AddGroup = function(btnObj) {
        if (!myChangeCheck()) return;
        $("#save").attr("disabled", true);
        // show the field details DIV
        $('#groupdetail').css('visibility', 'visible');
        $('#groupdetail').css('display', 'block');
        $('#groupdetail').css('margin-top', '85px');
        $(btnObj).parent().after($("#groupdetail"));
        $("html, body").animate({ scrollTop: 0 }, "slow");
        $('#groupdetail > #newgroupname').focus();
    };

    // save the new group to the form
    var SaveNewGroup = function(btnObj) {
        // the group name field can only have letters, numbers, spaces and underscores
        // AND it cannot start with a number
        if ($("#newgroupname").val() == "") {
            alert(<?php echo xlj('Group names cannot be blank'); ?>);
            return false;
        }
        if ($("#newgroupname").val().match(/^(\d+|\s+)/)) {
            alert(<?php echo xlj('Group names cannot start with numbers or spaces.'); ?>);
            return false;
        }
        var validname = $("#newgroupname").val().replace(/[^A-za-z0-9 ]/g, "_"); // match any non-word characters and replace them
        $("#newgroupname").val(validname);
        $("#formaction").val("addgroup");
        mySubmit();
    }

    // actually delete an entire group from the database
    var DeleteGroup = function(btnObj) {
        var parts = $(btnObj).attr("id");
        if (confirm(<?php echo xlj('WARNING') ?> + " - " +
            <?php echo xlj('This action cannot be undone.') ?> + "\n" +
            <?php echo xlj('Are you sure you wish to delete this entire group?'); ?>)
        ) {
            // submit the form to add a new field to a specific group
            $("#formaction").val("deletegroup");
            $("#deletegroupid").val(parts);
            $("#theform").submit();
        }
    };

    // just hide the new field DIV
    var CancelNewGroup = function(btnObj) {
        // hide the field details DIV
        $('#groupdetail').css('visibility', 'hidden');
        $('#groupdetail').css('display', 'none');
        // reset the new group values to a default
        $('#groupdetail > #newgroupname').val("");
        $('#groupdetail > #newgroupparent').val("");
        $("#save").attr("disabled", false);
    };

    // display the 'new field' DIV
    var MoveGroup = function(btnObj) {
        if (!myChangeCheck()) return;
        var btnid = $(btnObj).attr("id");
        var parts = btnid.split("~");
        var groupid = parts[0];
        var direction = parts[1];
        // submit the form to change group order
        $("#formaction").val("movegroup");
        $("#movegroupname").val(groupid);
        $("#movedirection").val(direction);
        mySubmit();
    }

    // show the rename group DIV
    var RenameGroup = function(btnObj) {
        if (!myChangeCheck()) return;
        $("#save").attr("disabled", true);
        $('#renamegroupdetail').css('visibility', 'visible');
        $('#renamegroupdetail').css('display', 'block');
        $(btnObj).parent().append($("#renamegroupdetail"));
        var parts = $(btnObj).attr("id").split("~");
        $('#renameoldgroupname').val(parts[0]); // this is actually the existing group ID
        $('#renamegroupname').val(parts[1]);    // the textual name of just this group
        var i = parts[0].length;
        $('[name=renamegroupparent]').val(i > 0 ? parts[0].substr(0, i-1) : ''); // parent ID
    }

    // save the new group to the form
    var SaveRenameGroup = function(btnObj) {
        // the group name field can only have letters, numbers, spaces and underscores
        // AND it cannot start with a number
        if ($("#renamegroupname").val().match(/^\d+/)) {
            alert(<?php echo xlj('Group names cannot start with numbers.'); ?>);
            return false;
        }
        var validname = $("#renamegroupname").val().replace(/[^A-za-z0-9 ]/g, "_"); // match any non-word characters and replace them
        $("#renamegroupname").val(validname);

        // submit the form to add a new field to a specific group
        $("#formaction").val("renamegroup");
        mySubmit();
    }

    // just hide the new field DIV
    var CancelRenameGroup = function(btnObj) {
        // hide the field details DIV
        $('#renamegroupdetail').css('visibility', 'hidden');
        $('#renamegroupdetail').css('display', 'none');
        // reset the rename group values to a default
        $('#renameoldgroupname').val("");
        $('#renamegroupname').val("");
        $('#renamegroupparent').val("");
    };

    /****************************************************/
    /************ Field functions ***********************/
    /****************************************************/

    // display the 'new field' DIV
    var AddField = function(btnObj) {
        if (!myChangeCheck()) return;
        $("#save").attr("disabled", true);
        // update the fieldgroup value to be the groupid
        var btnid = $(btnObj).attr("id");
        var parts = btnid.split("~");
        var groupid = parts[1];
        $('#fielddetail > #newfieldgroupid').attr('value', groupid);
        // show the field details DIV
        $('#fielddetail').css('visibility', 'visible');
        $('#fielddetail').css('display', 'block');
        $(btnObj).parent().append($("#fielddetail"));
        // Assign a sensible default sequence number.
        $('#newseq').val(getNextSeq(groupid));
    };

    var DeleteFields = function(btnObj) {
        if (!myChangeCheck()) return;
        if (confirm(<?php echo xlj('WARNING'); ?> + " - " + <?php echo xlj('This action cannot be undone.'); ?> + '\n' + <?php echo xlj('Are you sure you wish to delete the selected fields?'); ?>)) {
            var delim = "";
            $(".selectfield").each(function(i) {
                // build a list of selected field names to be moved
                if ($(this).prop("checked") == true) {
                    var parts = this.id.split("~");
                    var currval = $("#selectedfields").val();
                    $("#selectedfields").val(currval+delim+parts[1]);
                    delim = " ";
                }
            });
            // submit the form to delete the field(s)
            $("#formaction").val("deletefields");
            mySubmit();
        }
    };

    // save the new field to the form
    var SaveNewField = function(btnObj) {
        // check the new field values for correct formatting
        if (!validateNewField('new')) return false;

        // submit the form to add a new field to a specific group
        $("#formaction").val("addfield");
        mySubmit();
    };

    // just hide the new field DIV
    var CancelNewField = function(btnObj) {
        // hide the field details DIV
        $('#fielddetail').css('visibility', 'hidden');
        $('#fielddetail').css('display', 'none');
        // reset the new field values to a default
        ResetNewFieldValues();
        $("#save").attr("disabled", false);
    };

    // show the popup choice of lists
    var ShowLists = function(btnObj) {
        var title = <?php echo xlj('Select List');?>;
        dlgopen('../patient_file/encounter/find_code_dynamic.php?what=lists',"_blank", 850, 750, "", title);
        selectedfield = btnObj;
    };

    // show the popup choice of groups
    var ShowGroups = function(btnObj) {
        if (!myChangeCheck()) return;
        $("#targetlayout").val("");
        var title = <?php echo xlj('Select Group');?>;
        dlgopen('../patient_file/encounter/find_code_dynamic.php?what=groups&layout_id=' + <?php echo js_url($layout_id); ?>,
            "_blank",850, 600,"", title);
    };

    // Show context DD for NationNotes
    var ChangeList = function(btnObj){
      if(btnObj==34){
        $('#newlistid').hide();
        $('#contextName').show();
      }
      else{
        $('#newlistid').show();
        $('#contextName').hide();
      }
    };

    // Initialize list item selectors and value field visibilities in skip conditions.
    var f = document.forms[0];
    for (var lino = 1; f['fld[' + lino + '][id]']; ++lino) {
      for (var seq = 0; f['fld[' + lino + '][condition_itemid][' + seq + ']']; ++seq) {
        setListItemOptions(lino, seq, true);
      }
      actionChanged(lino);
    }

  // Support for beforeunload handler.
  $('tbody input, tbody select, tbody textarea').not('.selectfield').change(function() {
    somethingChanged = true;
  });
  window.addEventListener("beforeunload", function (e) {
    if (somethingChanged && !top.timed_out) {
      var msg = <?php echo xlj('You have unsaved changes.'); ?>;
      e.returnValue = msg;     // Gecko, Trident, Chrome 34+
      return msg;              // Gecko, WebKit, Chrome <34
    }
  });

}); /* Ready Done */

function layoutLook(){
    var form = <?php echo js_escape($layout_id);?>;
    var btnName = <?php echo xlj('Back To Editor');?>;
    var url = "../patient_file/encounter/view_form.php?isShow&id=0&formname=" + encodeURIComponent(form);
    var title = <?php echo xlj('LBF Encounter Form Preview');?>;
    dlgopen(url, '_blank', 1250, 800, "", title);
    return false;
}

// show the popup choice of groups
// TBD: Make this less redundant to ShowGroups which is used for moving fields.
function CopyToLayout(selObj) {
    if (!selObj.value || !myChangeCheck()) return;
    $("#targetlayout").val(selObj.value);
    var title = <?php echo xlj('Select Group');?>;
    dlgopen('../patient_file/encounter/find_code_dynamic.php?what=groups&layout_id=' + selObj.value,
        "_blank",850, 600,"", title);
};

function typeUsesList(datatype) {
<?php
foreach ($typesUsingList as $mytype) {
    // echo "  if (datatype == '$mytype') return true;\n";
    echo "  if (datatype == " . js_escape($mytype) . ") return true;\n";
}
?>
    return false;
}

function NationNotesContext(lineitem, val) {
    // Check if function is needed.
    if (!document.getElementById("fld[" + lineitem + "][contextName]") || !document.getElementById("fld[" + lineitem + "][list_id]")) {
        return false; // these elements don't exist yet so do nothing.
    }
    if (val == 34) {
        document.getElementById("fld[" + lineitem + "][contextName]").style.display = '';
        document.getElementById("fld[" + lineitem + "][list_id]").style.display = 'none';
        document.getElementById("fld[" + lineitem + "][list_id]").value = '';
    }
    else {
        document.getElementById("fld[" + lineitem + "][list_id]").style.display = '';
        document.getElementById("fld[" + lineitem + "][contextName]").style.display = 'none';
        if (!typeUsesList(val)) {
            document.getElementById("fld["+lineitem+"][listid]").value='';
        }
    }
}

function SetList(listid) {
    $(selectedfield).val(listid);
}

//////////////////////////////////////////////////////////////////////
// The following supports the field ID selection pop-up.
//////////////////////////////////////////////////////////////////////

var fieldselectfield;

function elemFromPart(part) {
  var ename = fieldselectfield.name;
  // ename is like one of the following:
  //   fld[$fld_line_no][id]
  //   gnewid
  //   newid
  // and "part" is what we substitute for the "id" part.
  var i = ename.lastIndexOf('id');
  ename = ename.substr(0, i) + part + ename.substr(i+2);
  return document.forms[0][ename];
}

function FieldIDClicked(elem) {
<?php if (substr($layout_id, 0, 3) == 'LBF') { ?>
  fieldselectfield = elem;
  var srcval = elemFromPart('source').value;
  // If the field ID is for the local form, allow direct entry.
  if (srcval == 'F') return;
  // Otherwise pop up the selection window.
  var title = <?php echo xlj('Select Field');?>;
  dlgopen('../patient_file/encounter/find_code_dynamic.php?what=fields&source='
    + encodeURIComponent(srcval), "_blank", 700, 600, "", title);
<?php } ?>
}

function SetField(field_id, title, data_type, uor, fld_length, max_length,
  list_id, titlecols, datacols, edit_options, description, fld_rows)
{
  fieldselectfield.value             = field_id;
  elemFromPart('title'       ).value = title;
  elemFromPart('datatype'    ).value = data_type;
  elemFromPart('uor'         ).value = uor;
  elemFromPart('lengthWidth' ).value = fld_length;
  elemFromPart('maxSize'     ).value = max_length;
  elemFromPart('list_id'     ).value = list_id;
  elemFromPart('titlecols'   ).value = titlecols;
  elemFromPart('datacols'    ).value = datacols;
  elemFromPart('edit_options').value = edit_options;
  elemFromPart('desc'        ).value = description;
  elemFromPart('codes'        ).value = codes;
  elemFromPart('lengthHeight').value = fld_rows;
}

//////////////////////////////////////////////////////////////////////
// End code for field ID selection pop-up.
//////////////////////////////////////////////////////////////////////

/* This is called after the user chooses a new group from the popup window.
 * It will submit the page so the selected fields can be moved into
 * the target group or copied to the target layout.
 */
function MoveFields(targetgroup) {
    $("#targetgroup").val(targetgroup);
    var delim = "";
    $(".selectfield").each(function(i) {
        // build a list of selected field names to be moved
        if ($(this).prop("checked") == true) {
            var parts = this.id.split("~");
            var currval = $("#selectedfields").val();
            $("#selectedfields").val(currval+delim+parts[1]);
            delim = " ";
        }
    });
    if ($("#targetlayout").val()) {
        $("#formaction").val("copytolayout");
    }
    else {
        $("#formaction").val("movefields");
    }
    mySubmit();
};

// set the new-field values to a default state
function ResetNewFieldValues () {
    $("#newseq").val("");
    $("#newsource").val("");
    $("#newid").val("");
    $("#newtitle").val("");
    $("#newuor").val(1);
    $("#newlengthWidth").val("");
    $("#newlengthHeight").val("");
    $("#newmaxSize").val("");
    $("#newdatatype").val("");
    $("#newlistid").val("");
    $("#newbackuplistid").val("");
    $("#newtitlecols").val("");
    $("#newdatacols").val("");
    $("#newedit_options").val("");
    $("#newdefault").val("");
    $("#newdesc").val("");
}

// is value an integer and between min and max
function IsNumeric(value, min, max) {
    if (value == "" || value == null) return false;
    if (! IsN(value) ||
        parseInt(value) < min ||
        parseInt(value) > max)
        return false;

    return true;
}

// This invokes the find-code popup.
function select_clin_term_code(e) {
    current_sel_name = '';
    current_sel_clin_term = e.id;
    dlgopen('../patient_file/encounter/find_code_dynamic.php', '_blank', 900, 600);
}

// This is for callback by the find-code popup.
function set_related(codetype, code, selector, codedesc) {
    // Coming from the Clinical Terms Code(s) edit
    var e =  document.getElementById(current_sel_clin_term);
    var s = e.value;
    if (code) {
        if (s.length > 0) s += ';';
        s += codetype + ':' + code;
    }
    else {
        s = '';
    }
    e.value = s;
}

// This is for callback by the find-code popup.
// Deletes the specified codetype:code from the currently selected list.
function del_related(s) {
    my_del_related(s, document.getElementById(current_sel_clin_term), false);
}

// This is for callback by the find-code popup.
// Returns the array of currently selected codes with each element in codetype:code format.
function get_related() {
    return document.getElementById(current_sel_clin_term).value.split(';');
}

/****************************************************/
/****************************************************/
/****************************************************/

// tell if num is an Integer
function IsN(num) { return !/\D/.test(num); }

</script>
</body>
</html>
