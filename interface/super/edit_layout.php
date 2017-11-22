<?php
/**
 * Edit layouts gui
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/log.inc");

use OpenEMR\Core\Header;

function collectLayoutNames($condition, $mapping = '')
{
    global $layouts;
    $gres = sqlStatement("SELECT grp_form_id, grp_title, grp_mapping " .
      "FROM layout_group_properties WHERE " .
      "grp_group_id = '' AND grp_activity = 1 AND $condition " .
      "ORDER BY grp_mapping, grp_seq, grp_title");
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

// Field modifier objects in script
// array of the data_types of the fields
$datatypes = array(
  "1"  => xl("List box"),
  "2"  => xl("Textbox"),
  "3"  => xl("Textarea"),
  "4"  => xl("Text-date"),
  "10" => xl("Providers"),
  "11" => xl("Providers NPI"),
  "12" => xl("Pharmacies"),
  "13" => xl("Squads"),
  "14" => xl("Organizations"),
  "15" => xl("Billing codes"),
  "16" => xl("Insurances"),
  "18" => xl("Visit Categories"),
  "21" => xl("Checkbox(es)"),
  "22" => xl("Textbox list"),
  "23" => xl("Exam results"),
  "24" => xl("Patient allergies"),
  "25" => xl("Checkboxes w/text"),
  "26" => xl("List box w/add"),
  "27" => xl("Radio buttons"),
  "28" => xl("Lifestyle status"),
  "31" => xl("Static Text"),
  "32" => xl("Smoking Status"),
  "33" => xl("Race/Ethnicity"),
  "34" => xl("NationNotes"),
  "35" => xl("Facilities"),
  "36" => xl("Multiple Select List"),
  "40" => xl("Image canvas"),
);

$sources = array(
  'F' => xl('Form'),
  'D' => xl('Patient'),
  'H' => xl('History'),
  'E' => xl('Visit'),
  'V' => xl('VisForm'),
);

function nextGroupOrder($order)
{
    if ($order == '9') {
        $order = 'A';
    } else if ($order == 'Z') {
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
    $s .= "<option value=''>" . xlt('None') . "</option>";
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
        array($layout_id, "$parent_%")
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
    } elseif ($layout_id == "HIS") {
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

// Call this when adding or removing a layout field.  This will create or drop
// the corresponding table column when appropriate.  Table columns are not
// dropped if they contain any non-empty values.
function addOrDeleteColumn($layout_id, $field_id, $add = true)
{
    $tablename = tableNameFromLayout($layout_id);
    if (!$tablename) {
        return;
    }
    // Check if the column currently exists.
    $tmp = sqlQuery("SHOW COLUMNS FROM `$tablename` LIKE '$field_id'");
    $column_exists = !empty($tmp);

    if ($add && !$column_exists) {
        sqlStatement("ALTER TABLE `$tablename` ADD `$field_id` TEXT");
        newEvent(
            "alter_table",
            $_SESSION['authUser'],
            $_SESSION['authProvider'],
            1,
            "$tablename ADD $field_id"
        );
    } elseif (!$add && $column_exists) {
        // Do not drop a column that has any data.
        $tmp = sqlQuery("SELECT `$field_id` FROM `$tablename` WHERE " .
        "`$field_id` IS NOT NULL AND `$field_id` != '' LIMIT 1");
        if (!isset($tmp['field_id'])) {
            sqlStatement("ALTER TABLE `$tablename` DROP `$field_id`");
            newEvent(
                "alter_table",
                $_SESSION['authUser'],
                $_SESSION['authProvider'],
                1,
                "$tablename DROP $field_id "
            );
        }
    }
}

// Call this before renaming a layout field.
// Renames the table column (if applicable) and returns a result status:
//  -1 = There is no table for this layout (not an error).
//   0 = Rename successful.
//   2 = There is no column having the old name.
//   3 = There is already a column having the new name.
//
function renameColumn($layout_id, $old_field_id, $new_field_id)
{
    $tablename = tableNameFromLayout($layout_id);
    if (!$tablename) {
        return -1; // Indicate rename is not relevant.
    }
    // Make sure old column exists.
    $colarr = sqlQuery("SHOW COLUMNS FROM `$tablename` LIKE ?", array($old_field_id));
    if (empty($colarr)) {
        // Error, old name does not exist.
        return 2;
    }
    // Make sure new column does not exist.
    $tmp = sqlQuery("SHOW COLUMNS FROM `$tablename` LIKE ?", array($new_field_id));
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
        $colstr .= " " . $colarr['Extra'];
    }
    $query = "ALTER TABLE `$tablename` CHANGE `$old_field_id` `$new_field_id` $colstr";
    sqlStatement($query);
    newEvent(
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
$thisauth = acl_check('admin', 'super');
if (!$thisauth) {
    die(xl('Not authorized'));
}

// The layout ID identifies the layout to be edited.
$layout_id = empty($_REQUEST['layout_id']) ? '' : $_REQUEST['layout_id'];

// Tag style for stuff to hide if not an LBF layout. Currently just for the Source column.
$lbfonly = substr($layout_id, 0, 3) == 'LBF' ? "" : "style='display:none;'";

// Handle the Form actions

if ($_POST['formaction'] == "save" && $layout_id) {
    // If we are saving, then save.
    $fld = $_POST['fld'];
    for ($lino = 1; isset($fld[$lino]['id']); ++$lino) {
        $iter = $fld[$lino];
        $field_id = formTrim($iter['id']);
        $field_id_original = formTrim($iter['originalid']);
        $data_type = formTrim($iter['datatype']);
        $listval = $data_type == 34 ? formTrim($iter['contextName']) : formTrim($iter['list_id']);
        $action = $iter['action'];
        if ($action == 'value') {
            $action = 'value=' . $iter['value'];
        }

        // Skip conditions for the line are stored as a serialized array.
        $condarr = array('action' => $action);
        $cix = 0;
        for (; !empty($iter['condition_id'][$cix]); ++$cix) {
            $andor = empty($iter['condition_andor'][$cix]) ? '' : $iter['condition_andor'][$cix];
            $condarr[$cix] = array(
            'id'       => strip_escape_custom($iter['condition_id'      ][$cix]),
            'itemid'   => strip_escape_custom($iter['condition_itemid'  ][$cix]),
            'operator' => strip_escape_custom($iter['condition_operator'][$cix]),
            'value'    => strip_escape_custom($iter['condition_value'   ][$cix]),
            'andor'    => strip_escape_custom($andor),
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
                "field_id = '"      . formDataCore($field_id)      . "', " .
                "source = '"        . formTrim($iter['source'])    . "', " .
                "title = '"         . formDataCore($iter['title'])     . "', " .
                "group_id = '"    . formTrim($iter['group'])     . "', " .
                "seq = '"           . formTrim($iter['seq'])       . "', " .
                "uor = '"           . formTrim($iter['uor'])       . "', " .
                "fld_length = '"    . formTrim($iter['lengthWidth'])    . "', " .
                "fld_rows = '"    . formTrim($iter['lengthHeight'])    . "', " .
                "max_length = '"    . formTrim($iter['maxSize'])    . "', "                             .
                "titlecols = '"     . formTrim($iter['titlecols']) . "', " .
                "datacols = '"      . formTrim($iter['datacols'])  . "', " .
                "data_type= '$data_type', "                                .
                "list_id= '"        . $listval   . "', " .
                "list_backup_id= '"        . formTrim($iter['list_backup_id'])   . "', " .
                "edit_options = '"  . encodeModifier($iter['edit_options']) . "', " .
                "default_value = '" . formTrim($iter['default'])   . "', " .
                "description = '"   . formTrim($iter['desc'])      . "', " .
                "conditions = '"    . add_escape_custom($conditions) . "', " .
                "validation = '"   . formTrim($iter['validation'])   . "' " .
                "WHERE form_id = '$layout_id' AND field_id = '$field_id_original'");
        }
    }
} else if ($_POST['formaction'] == "addfield" && $layout_id) {
    // Add a new field to a specific group
    $data_type = formTrim($_POST['newdatatype']);
    $max_length = $data_type == 3 ? 3 : 255;
    $listval = $data_type == 34 ? formTrim($_POST['contextName']) : formTrim($_POST['newlistid']);
    sqlStatement("INSERT INTO layout_options (" .
      " form_id, source, field_id, title, group_id, seq, uor, fld_length, fld_rows" .
      ", titlecols, datacols, data_type, edit_options, default_value, description" .
      ", max_length, list_id, list_backup_id " .
      ") VALUES ( " .
      "'"  . formTrim($_POST['layout_id']) . "'" .
      ",'" . formTrim($_POST['newsource']) . "'" .
      ",'" . formTrim($_POST['newid']) . "'" .
      ",'" . formDataCore($_POST['newtitle']) . "'" .
      ",'" . formTrim($_POST['newfieldgroupid']) . "'" .
      ",'" . formTrim($_POST['newseq']) . "'" .
      ",'" . formTrim($_POST['newuor']) . "'" .
      ",'" . formTrim($_POST['newlengthWidth']) . "'" .
      ",'" . formTrim($_POST['newlengthHeight']) . "'" .
      ",'" . formTrim($_POST['newtitlecols']) . "'" .
      ",'" . formTrim($_POST['newdatacols']) . "'" .
      ",'$data_type'"                                  .
        ",'" . encodeModifier($_POST['newedit_options']) . "'" .
      ",'" . formTrim($_POST['newdefault']) . "'" .
      ",'" . formTrim($_POST['newdesc']) . "'" .
      ",'"    . formTrim($_POST['newmaxSize'])    . "'"                                 .
      ",'" . $listval . "'" .
      ",'" . formTrim($_POST['newbackuplistid']) . "'" .
      " )");
    addOrDeleteColumn($layout_id, formTrim($_POST['newid']), true);
} else if ($_POST['formaction'] == "movefields" && $layout_id) {
    // Move field(s) to a new group in the layout
    $sqlstmt = "UPDATE layout_options SET ".
                " group_id = '" . $_POST['targetgroup'] . "' " .
                " WHERE ".
                " form_id = '" . $_POST['layout_id'] . "' ".
                " AND field_id IN (";
    $comma = "";
    foreach (explode(" ", $_POST['selectedfields']) as $onefield) {
        $sqlstmt .= $comma."'".$onefield."'";
        $comma = ", ";
    }

    $sqlstmt .= ")";
    //echo $sqlstmt;
    sqlStatement($sqlstmt);
} else if ($_POST['formaction'] == "deletefields" && $layout_id) {
    // Delete a field from a specific group
    $sqlstmt = "DELETE FROM layout_options WHERE ".
                " form_id = '".$_POST['layout_id']."' ".
                " AND field_id IN (";
    $comma = "";
    foreach (explode(" ", $_POST['selectedfields']) as $onefield) {
        $sqlstmt .= $comma."'".$onefield."'";
        $comma = ", ";
    }

    $sqlstmt .= ")";
    sqlStatement($sqlstmt);
    foreach (explode(" ", $_POST['selectedfields']) as $onefield) {
        addOrDeleteColumn($layout_id, $onefield, false);
    }
} else if ($_POST['formaction'] == "addgroup" && $layout_id) {
    // Generate new value for layout_items.group_id.
    $newgroupid = genGroupId($_POST['newgroupparent']);

    sqlStatement(
        "INSERT INTO layout_group_properties SET " .
        "grp_form_id = ?, " .
        "grp_group_id = ?, " .
        "grp_title = ?",
        array($layout_id, $newgroupid, $_POST['newgroupname'])
    );

    $data_type = formTrim($_POST['gnewdatatype']);
    $max_length = $data_type == 3 ? 3 : 255;
    $listval = $data_type == 34 ? formTrim($_POST['gcontextName']) : formTrim($_POST['gnewlistid']);
    // add a new group to the layout, with the defined field
    sqlStatement("INSERT INTO layout_options (" .
      " form_id, source, field_id, title, group_id, seq, uor, fld_length, fld_rows" .
      ", titlecols, datacols, data_type, edit_options, default_value, description" .
      ", max_length, list_id, list_backup_id " .
      ") VALUES ( " .
      "'"  . formTrim($_POST['layout_id']) . "'" .
      ",'" . formTrim($_POST['gnewsource']) . "'" .
      ",'" . formTrim($_POST['gnewid']) . "'" .
      ",'" . formDataCore($_POST['gnewtitle']) . "'" .
      ",'" . formTrim($newgroupid)                . "'" .
      ",'" . formTrim($_POST['gnewseq']) . "'" .
      ",'" . formTrim($_POST['gnewuor']) . "'" .
      ",'" . formTrim($_POST['gnewlengthWidth']) . "'" .
      ",'" . formTrim($_POST['gnewlengthHeight']) . "'" .
      ",'" . formTrim($_POST['gnewtitlecols']) . "'" .
      ",'" . formTrim($_POST['gnewdatacols']) . "'" .
      ",'$data_type'"                                   .
        ",'" . encodeModifier($_POST['gnewedit_options']) . "'" .
      ",'" . formTrim($_POST['gnewdefault']) . "'" .
      ",'" . formTrim($_POST['gnewdesc']) . "'" .
      ",'"    . formTrim($_POST['gnewmaxSize'])    . "'"                                  .
      ",'" . $listval       . "'" .
      ",'" . formTrim($_POST['gnewbackuplistid']) . "'" .
      " )");
    addOrDeleteColumn($layout_id, formTrim($_POST['gnewid']), true);
} /**********************************************************************
else if ($_POST['formaction'] == "deletegroup" && $layout_id) {
    // drop the fields from the related table (this is critical)
    $res = sqlStatement("SELECT field_id FROM layout_options WHERE " .
      "form_id = '" . $_POST['layout_id'] . "' ".
      "AND group_name = '" . $_POST['deletegroupname'] . "'");
    while ($row = sqlFetchArray($res)) {
        addOrDeleteColumn($layout_id, $row['field_id'], false);
    }

    // Delete an entire group from the form
    sqlStatement("DELETE FROM layout_options WHERE ".
                " form_id = '".$_POST['layout_id']."' ".
                " AND group_name = '".$_POST['deletegroupname']."'"
                );
}
**********************************************************************/

else if ($_POST['formaction'] == "movegroup" && $layout_id) {
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
} // Renaming a group. This might include moving to a different parent group.
else if ($_POST['formaction'] == "renamegroup" && $layout_id) {
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

// Get the selected form's elements.
if ($layout_id) {
    $res = sqlStatement("SELECT * FROM layout_options WHERE " .
    "form_id = '$layout_id' ORDER BY group_id, seq");
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
    global $fld_line_no, $sources, $lbfonly, $extra_html,$validations;
    ++$fld_line_no;
    $checked = $linedata['default_value'] ? " checked" : "";

    //echo " <tr bgcolor='$bgcolor'>\n";
    echo " <tr id='fld[$fld_line_no]' class='".($fld_line_no % 2 ? 'even' : 'odd')."'>\n";

    echo "  <td class='optcell' nowrap>";
    // tuck the group_name INPUT in here
    echo "<input type='hidden' name='fld[$fld_line_no][group]' value='" .
         htmlspecialchars($linedata['group_id'], ENT_QUOTES) . "' class='optin' />";
    // Original field ID.
    echo "<input type='hidden' name='fld[$fld_line_no][originalid]' value='" .
         attr($linedata['field_id']) . "' />";

    echo "<input type='checkbox' class='selectfield' ".
            "name='"  . $linedata['group_id'] . "~" . $linedata['field_id'] . "' " .
            "id='"    . $linedata['group_id'] . "~" . $linedata['field_id'] . "' " .
            "title='" . xla('Select field') . "' />";

    echo "<input type='text' name='fld[$fld_line_no][seq]' id='fld[$fld_line_no][seq]' value='" .
      htmlspecialchars($linedata['seq'], ENT_QUOTES) . "' size='2' maxlength='4' " .
      "class='optin' style='width:32pt' />";
    echo "</td>\n";

    echo "  <td align='center' class='optcell' $lbfonly>";
    echo "<select class='form-control' name='fld[$fld_line_no][source]' class='optin' $lbfonly>";
    foreach ($sources as $key => $value) {
        echo "<option value='" . attr($key) . "'";
        if ($key == $linedata['source']) {
            echo " selected";
        }

        echo ">" . text($value) . "</option>\n";
    }

    echo "</select>";
    echo "</td>\n";

    echo "  <td align='left' class='optcell'>";
    echo "<input type='text' name='fld[$fld_line_no][id]' value='" .
         htmlspecialchars($linedata['field_id'], ENT_QUOTES) . "' size='15' maxlength='63' " .
         "class='optin' style='width:100%' onclick='FieldIDClicked(this)' />";
    echo "</td>\n";

    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' id='fld[$fld_line_no][title]' name='fld[$fld_line_no][title]' value='" .
         htmlspecialchars($linedata['title'], ENT_QUOTES) . "' size='15' maxlength='63' class='optin' style='width:100%' />";
    echo "</td>\n";

    // if not english and set to translate layout labels, then show the translation
    if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
        echo "<td align='center' class='translation' >" . htmlspecialchars(xl($linedata['title']), ENT_QUOTES) . "</td>\n";
    }

    echo "  <td align='center' class='optcell'>";
    echo "<select class='form-control' name='fld[$fld_line_no][uor]' class='optin'>";
    foreach (array(0 =>xl('Unused'), 1 =>xl('Optional'), 2 =>xl('Required')) as $key => $value) {
        echo "<option value='$key'";
        if ($key == $linedata['uor']) {
            echo " selected";
        }

        echo ">$value</option>\n";
    }

    echo "</select>";
    echo "</td>\n";

    echo "  <td align='center' class='optcell'>";
    echo "<select class='form-control' name='fld[$fld_line_no][datatype]' id='fld[$fld_line_no][datatype]' onchange=NationNotesContext('".$fld_line_no."',this.value)>";
    echo "<option value=''></option>";
    global $datatypes;
    foreach ($datatypes as $key => $value) {
        if ($linedata['data_type'] == $key) {
            echo "<option value='$key' selected>$value</option>";
        } else {
            echo "<option value='$key'>$value</option>";
        }
    }

    echo "</select>";
    echo "  </td>";

    echo "  <td align='center' class='optcell'>";
    if ($linedata['data_type'] == 2 || $linedata['data_type'] == 3 ||
      $linedata['data_type'] == 21 || $linedata['data_type'] == 22 ||
      $linedata['data_type'] == 23 || $linedata['data_type'] == 25 ||
      $linedata['data_type'] == 27 || $linedata['data_type'] == 28 ||
      $linedata['data_type'] == 32 || $linedata['data_type'] == 15 ||
      $linedata['data_type'] == 40
    ) {
      // Show the width field
        echo "<input type='text' name='fld[$fld_line_no][lengthWidth]' value='" .
        htmlspecialchars($linedata['fld_length'], ENT_QUOTES) .
        "' size='2' maxlength='10' class='optin' title='" . xla('Width') . "' />";
        if ($linedata['data_type'] == 3 || $linedata['data_type'] == 40) {
            // Show the height field
            echo "<input type='text' name='fld[$fld_line_no][lengthHeight]' value='" .
            htmlspecialchars($linedata['fld_rows'], ENT_QUOTES) .
            "' size='2' maxlength='10' class='optin' title='" . xla('Height') . "' />";
        } else {
            // Hide the height field
            echo "<input type='hidden' name='fld[$fld_line_no][lengthHeight]' value=''>";
        }
    } else {
      // all other data_types (hide both the width and height fields
        echo "<input type='hidden' name='fld[$fld_line_no][lengthWidth]' value=''>";
        echo "<input type='hidden' name='fld[$fld_line_no][lengthHeight]' value=''>";
    }

    echo "</td>\n";

    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='fld[$fld_line_no][maxSize]' value='" .
      htmlspecialchars($linedata['max_length'], ENT_QUOTES) .
      "' size='1' maxlength='10' class='optin' style='width:100%' " .
      "title='" . xla('Maximum Size (entering 0 will allow any size)') . "' />";
    echo "</td>\n";

    echo "  <td align='center' class='optcell'>";
    if ($linedata['data_type'] ==  1 || $linedata['data_type'] == 21 ||
      $linedata['data_type'] == 22 || $linedata['data_type'] == 23 ||
      $linedata['data_type'] == 25 || $linedata['data_type'] == 26 ||
      $linedata['data_type'] == 27 || $linedata['data_type'] == 32 ||
      $linedata['data_type'] == 33 || $linedata['data_type'] == 34 ||
      $linedata['data_type'] == 36) {
        $type = "";
        $disp = "style='display:none'";
        if ($linedata['data_type'] == 34) {
            $type = "style='display:none'";
            $disp = "";
        }

        echo "<input type='text' name='fld[$fld_line_no][list_id]'  id='fld[$fld_line_no][list_id]' value='" .
        htmlspecialchars($linedata['list_id'], ENT_QUOTES) . "'".$type.
        " size='6' maxlength='100' class='optin listid' style='width:100%;cursor:pointer'".
        "title='". xl('Choose list') . "' />";

        echo "<select class='form-control' name='fld[$fld_line_no][contextName]' id='fld[$fld_line_no][contextName]' ".$disp.">";
        $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
        while ($row = sqlFetchArray($res)) {
            $sel = '';
            if ($linedata['list_id'] == $row['cl_list_item_long']) {
                $sel = 'selected';
            }

            echo "<option value='".htmlspecialchars($row['cl_list_item_long'], ENT_QUOTES)."' ".$sel.">".htmlspecialchars($row['cl_list_item_long'], ENT_QUOTES)."</option>";
        }

        echo "</select>";
    } else {
      // all other data_types
        echo "<input type='hidden' name='fld[$fld_line_no][list_id]' value=''>";
    }

    echo "</td>\n";

    //Backup List Begin
    echo "  <td align='center' class='optcell'>";
    if ($linedata['data_type'] ==  1 || $linedata['data_type'] == 26 ||
        $linedata['data_type'] == 33 || $linedata['data_type'] == 36) {
        echo "<input type='text' name='fld[$fld_line_no][list_backup_id]' value='" .
            htmlspecialchars($linedata['list_backup_id'], ENT_QUOTES) .
            "' size='3' maxlength='100' class='optin listid' style='cursor:pointer; width:100%' />";
    } else {
        echo "<input type='hidden' name='fld[$fld_line_no][list_backup_id]' value=''>";
    }

    echo "</td>\n";
    //Backup List End

    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='fld[$fld_line_no][titlecols]' value='" .
         htmlspecialchars($linedata['titlecols'], ENT_QUOTES) . "' size='3' maxlength='10' class='optin' style='width:100%' />";
    echo "</td>\n";

    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='fld[$fld_line_no][datacols]' value='" .
         htmlspecialchars($linedata['datacols'], ENT_QUOTES) . "' size='3' maxlength='10' class='optin' style='width:100%' />";
    echo "</td>\n";
    /* Below for compatabilty with existing string modifiers. */
    if (strpos($linedata['edit_options'], ',') === false && isset($linedata['edit_options'])) {
        $t = json_decode($linedata['edit_options']);
        if (json_last_error() !== JSON_ERROR_NONE || $t === 0) { // hopefully string of characters and 0 handled.
            $t = str_split(trim($linedata['edit_options']));
            $linedata['edit_options'] = json_encode($t); // convert to array select understands.
        }
    }
    echo "  <td align='center' class='optcell' title='" . xla("Add modifiers for this field type. You may select more than one.") . "'>";
    echo "<select id='fld[$fld_line_no][edit_options]' name='fld[$fld_line_no][edit_options][]' class='typeAddons optin' size=3 multiple data-set='" .
    trim($linedata['edit_options']) . "' ></select></td>\n";

    if ($linedata['data_type'] == 31) {
        echo "  <td align='center' class='optcell'>";
        echo "<textarea name='fld[$fld_line_no][desc]' rows='3' cols='35' class='optin' style='width:100%'>" .
           $linedata['description'] . "</textarea>";
        echo "<input type='hidden' name='fld[$fld_line_no][default]' value='" .
         htmlspecialchars($linedata['default_value'], ENT_QUOTES) . "' />";
        echo "</td>\n";
    } else {
        echo "  <td align='center' class='optcell' >";
        echo "<input type='text' name='fld[$fld_line_no][desc]' value='" .
        htmlspecialchars($linedata['description'], ENT_QUOTES) .
        "' size='30' class='optin' style='width:100%' />";
        echo "<input type='hidden' name='fld[$fld_line_no][default]' value='" .
        htmlspecialchars($linedata['default_value'], ENT_QUOTES) . "' />";
        echo "</td>\n";
      // if not english and showing layout labels, then show the translation of Description
        if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
            echo "<td align='center' class='translation'>" .
            htmlspecialchars(xl($linedata['description']), ENT_QUOTES) . "</td>\n";
        }
    }

    // The "?" to click on for yet more field attributes.
    echo "  <td class='bold' id='querytd_$fld_line_no' style='cursor:pointer;";
    if (!empty($linedata['conditions']) || !empty($linedata['validation'])) {
        echo "background-color:#77ff77;";
    }

    echo "' onclick='extShow($fld_line_no, this)' align='center' ";
    echo "title='" . xla('Click here to view/edit more details') . "'>";
    echo "&nbsp;?&nbsp;";
    echo "</td>\n";

    echo " </tr>\n";

    // Create a floating div for the additional attributes of this field.
    $conditions = empty($linedata['conditions']) ?
      array(0 => array('id' => '', 'itemid' => '', 'operator' => '', 'value' => '')) :
      unserialize($linedata['conditions']);
    $action = empty($conditions['action']) ? 'skip' : $conditions['action'];
    $action_value = $action == 'skip' ? '' : substr($action, 6);
    //
    $extra_html .= "<div id='ext_$fld_line_no' " .
      "style='position:absolute;width:750px;border:1px solid black;" .
      "padding:2px;background-color:#cccccc;visibility:hidden;" .
      "z-index:1000;left:-1000px;top:0px;font-size:8pt;'>\n" .
      "<table width='100%'>\n" .
      " <tr>\n" .
      "  <th colspan='3' align='left' class='bold'>" .
      xlt('For') . " " . text($linedata['field_id']) . " " .
      "<select name='fld[$fld_line_no][action]' onchange='actionChanged($fld_line_no)'>" .
      "<option value='skip'  " . ($action == 'skip' ? 'selected' : '') . ">" . xlt('hide this field') . "</option>" .
      "<option value='value' " . ($action != 'skip' ? 'selected' : '') . ">" . xlt('set value to') . "</option>" .
      "</select>" .
      "<input type='text' name='fld[$fld_line_no][value]' value='" . attr($action_value) . "' size='15' />" .
      " " . xlt('if') .
      "</th>\n" .
      "  <th colspan='2' align='right' class='text'><input type='button' " .
      "value='" . xla('Close') . "' onclick='extShow($fld_line_no, false)' />&nbsp;</th>\n" .
      " </tr>\n" .
      " <tr>\n" .
      "  <th align='left' class='bold'>" . xlt('Field ID') . "</th>\n" .
      "  <th align='left' class='bold'>" . xlt('List item ID') . "</th>\n" .
      "  <th align='left' class='bold'>" . xlt('Operator') . "</th>\n" .
      "  <th align='left' class='bold'>" . xlt('Value if comparing') . "</th>\n" .
      "  <th align='left' class='bold'>&nbsp;</th>\n" .
      " </tr>\n";
    // There may be multiple condition lines for each field.
    foreach ($conditions as $i => $condition) {
        if (!is_numeric($i)) {
            continue; // skip if 'action'
        }
        $extra_html .=
        " <tr>\n" .
        "  <td align='left'>\n" .
        "   <select name='fld[$fld_line_no][condition_id][$i]' onchange='cidChanged($fld_line_no, $i)'>" .
        genFieldOptionList($condition['id']) . " </select>\n" .
        "  </td>\n" .
        "  <td align='left'>\n" .
        // List item choices are populated on the client side but will need the current value,
        // so we insert a temporary option here to hold that value.
        "   <select name='fld[$fld_line_no][condition_itemid][$i]'><option value='" .
        attr($condition['itemid']) . "'>...</option></select>\n" .
        "  </td>\n" .
        "  <td align='left'>\n" .
        "   <select name='fld[$fld_line_no][condition_operator][$i]'>\n";
        foreach (array(
        'eq' => xl('Equals'),
        'ne' => xl('Does not equal'),
        'se' => xl('Is selected'),
        'ns' => xl('Is not selected'),
        ) as $key => $value) {
            $extra_html .= "    <option value='$key'";
            if ($key == $condition['operator']) {
                $extra_html .= " selected";
            }

            $extra_html .= ">" . text($value) . "</option>\n";
        }

        $extra_html .=
        "   </select>\n" .
        "  </td>\n" .
        "  <td align='left' title='" . xla('Only for comparisons') . "'>\n" .
        "   <input type='text' name='fld[$fld_line_no][condition_value][$i]' value='" .
        attr($condition['value']) . "' size='15' maxlength='63' />\n" .
        "  </td>\n";
        if (!isset($conditions[$i + 1])) {
            $extra_html .=
            "  <td align='right' title='" . xla('Add a condition') . "'>\n" .
            "   <input type='button' value='+' onclick='extAddCondition($fld_line_no,this)' />\n" .
            "  </td>\n";
        } else {
            $extra_html .=
            "  <td align='right'>\n" .
            "   <select name='fld[$fld_line_no][condition_andor][$i]'>\n";
            foreach (array(
            'and' => xl('And'),
            'or'  => xl('Or'),
            ) as $key => $value) {
                $extra_html .= "    <option value='$key'";
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

    $extra_html .=  "<table width='100%'>\n" .
    " <tr>\n" .
    "  <td colspan='3' align='left' class='bold'>\"" . text($linedata['field_id']) . "\" " .
    xlt('will have the following validation rules') . ":</td>\n" .
    " </tr>\n" .
    " <tr>\n" .
    "  <td align='left' class='bold'>" . xlt('Validation rule') . "  </td>\n" .
    " </tr>\n".
    " <tr>\n" .
    "  <td align='left' title='" . xla('Select a validation rule') . "'>\n" .


    "   <select name='fld[$fld_line_no][validation]' onchange='valChanged($fld_line_no)'>\n" .
    "   <option value=''";
    if (empty($linedata['validation'])) {
        $extra_html .= " selected";
    }

        $extra_html .= ">-- " . xlt('Please Select') . " --</option>";
    foreach ($validations as $key => $value) {
        $extra_html .= "    <option value='$key'";
        if ($key == $linedata['validation']) {
            $extra_html .= " selected";
        }

        $extra_html .= ">" . text($value) . "</option>\n";
    }

    $extra_html .="</select>\n" .
        "  </td>\n";

     $extra_html .=
         "</table>\n" .
       "</div>\n";
}
?>
<html>

<head>

<?php Header::setupHeader(['select2']); ?>

<title><?php  xl('Layout Editor', 'e'); ?></title>

<style>
.orgTable tr.head   { font-size:8pt; background-color:#cccccc; }
.orgTable tr.detail { font-size:8pt; }
.orgTable td        { font-size:8pt; }
.orgTable input     { font-size:8pt; }
.orgTable select    { font-size:8pt; }
a, a:visited, a:hover { color:#0000cc; }
.optcell  { }
.optin    { background: transparent; }
.group {
    margin: 0pt 0pt 8pt 0pt;
    padding :0pt;
    width: 100%;
}

.group table {
    border-collapse: collapse;
    width: 100%;

}
.orgTable .odd td {
    background-color: #ddddff;
    padding: 3px 0px 3px 0px;
}
.orgTable .even td {
    background-color: #ffdddd;
    padding: 3px 0px 3px 0px;
}
.help { cursor: help; }
.layouts_title { font-size: 110%; }
.translation {
    color: green;
    font-size:8pt;
}
.highlight * {
    border: 2px solid blue;
    background-color: yellow;
    color: black;
}
.select2-container--default .select2-selection--multiple {
    cursor: pointer;
}
.select2-search__field {
    cursor: pointer;
    width: 0 !important;
}
.select2-selection__choice {
    font-size: 12px;
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

</style>

<script>
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
  extdiv.style.top = '0px';
 }
 if (show && thisdiv != extdiv) {
  extdiv = thisdiv;
  var dw = window.innerWidth ? window.innerWidth - 20 : document.body.clientWidth;
  x = dw - extdiv.offsetWidth;
  if (x < 0) x = 0;
  var y = extGetY(show) + show.offsetHeight;
  extdiv.style.left = x;
  extdiv.style.top  = y;
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
  if (i == 0) alert('f["fld[' + lino + '][condition_id][' + i + ']"] <?php echo xls('not found') ?>');

  // Get containing <td>, <tr> and <table> nodes of the "+" button.
  var tdplus = btnelem.parentNode;
  var trelem = tdplus.parentNode;
  var telem  = trelem.parentNode;

  // Replace contents of the tdplus cell.
  tdplus.innerHTML =
    "<select name='fld[" + lino + "][condition_andor][" + i + "]'>" +
    "<option value='and'><?php echo xls('And') ?></option>" +
    "<option value='or' ><?php echo xls('Or') ?></option>" +
    "</select>";

  // Add the new row.
  var newtrelem = telem.insertRow(i+2);
  newtrelem.innerHTML =
    "<td align='left'>" +
    "<select name='fld[" + lino + "][condition_id][" + i + "]' onchange='cidChanged(" + lino + "," + i + ")'>" +
    "<?php echo addslashes(genFieldOptionList()) ?>" +
    "</select>" +
    "</td>" +
    "<td align='left'>" +
    "<select name='fld[" + lino + "][condition_itemid][" + i + "]' style='display:none' />" +
    "</td>" +
    "<td align='left'>" +
    "<select name='fld[" + lino + "][condition_operator][" + i + "]'>" +
    "<option value='eq'><?php echo xls('Equals') ?></option>" +
    "<option value='ne'><?php echo xls('Does not equal') ?></option>" +
    "<option value='se'><?php echo xls('Is selected') ?></option>" +
    "<option value='ns'><?php echo xls('Is not selected') ?></option>" +
    "</select>" +
    "</td>" +
    "<td align='left'>" +
    "<input type='text' name='fld[" + lino + "][condition_value][" + i + "]' value='' size='15' maxlength='63' />" +
    "</td>" +
    "<td align='right'>" +
    "<input type='button' value='+' onclick='extAddCondition(" + lino + ",this)' />" +
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
      alert('<?php echo xls('Condition field not found') ?>: ' + field_id);
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
  if (data_type != '21' && data_type != '22' && data_type != '23' && data_type != '25') {
    f[target].style.display = 'none';
    return;
  }
  // OK, list item IDs do apply so go get 'em.
  // This happens asynchronously so the generated code needs to stand alone.
  f[target].style.display = '';
  $.getScript('layout_listitems_ajax.php' +
    '?listid='  + encodeURIComponent(list_id) +
    '&target='  + encodeURIComponent(target)  +
    '&current=' + encodeURIComponent(current));
}

// This is called whenever a condition's field ID selection is changed.
function cidChanged(lino, seq) {
  changeColor(lino);
  setListItemOptions(lino, seq, false);
}

// This invokes the popup to edit layout properties or add a new layout.
function edit_layout_props(groupid) {
 var title = "<?php echo xlt('Layout Properties');?>";
 dlgopen('edit_layout_props.php?layout_id=<?php echo attr($layout_id); ?>&group_id=' + groupid,
  '_blank', 775, 550, "", title);
}

// callback from edit_layout_props.php:
function refreshme(layout_id) {
 location.href = 'edit_layout.php?layout_id=' + layout_id;
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
        thistd.style.backgroundColor = '#77ff77';
    }else{
        thistd.style.backgroundColor ='';
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
    if (!confirm('<?php echo xls('You have unsaved changes. Abandon them?'); ?>')) {
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
<div class="container-responsive">
<form method='post' name='theform' id='theform' action='edit_layout.php'>
<input type="hidden" name="formaction" id="formaction" value="">
<!-- elements used to identify a field to delete -->
<input type="hidden" name="deletefieldid" id="deletefieldid" value="">
<input type="hidden" name="deletefieldgroup" id="deletefieldgroup" value="">
<!-- elements used to identify a group to delete -->
<!--
<input type="hidden" name="deletegroupname" id="deletegroupname" value="">
-->
<!-- elements used to change the group order -->
<input type="hidden" name="movegroupname" id="movegroupname" value="">
<input type="hidden" name="movedirection" id="movedirection" value="">
<!-- elements used to select more than one field -->
<input type="hidden" name="selectedfields" id="selectedfields" value="">
<input type="hidden" id="targetgroup" name="targetgroup" value="">

<div class="menubar" style='padding:5px 0;'>

<b><?php xl('Edit layout', 'e'); ?>:</b>&nbsp;
<select name='layout_id' id='layout_id' class='form-control' style='display:inline-block;margin-bottom:5px;width:20%;'>
 <option value=''>-- <?php echo xl('Select') ?> --</option>
<?php
$lastgroup = '';
foreach ($layouts as $key => $value) {
    if ($value[0] != $lastgroup) {
        if ($lastgroup) {
            echo " </optgroup>\n";
        }
        echo " <optgroup label='" . attr($value[0]) . "'>\n";
        $lastgroup = $value[0];
    }
    echo "  <option value='" . attr($key) . "'";
    if ($key == $layout_id) {
        echo " selected";
    }
    echo ">" . text($value[1]) . "</option>\n";
}
if ($lastgroup) {
    echo " </optgroup>\n";
}
?>
</select>

<?php if ($layout_id) { ?>
<input type='button' value='<?php echo xla('Layout Properties'); ?>' onclick='edit_layout_props("")' />&nbsp;
<input type='button' class='addgroup'  id='addgroup'  value='<?php echo xla('Add Group'); ?>' />
<span style="font-size:90%"> &nbsp;
<input type='button' class="btn btn-danger" name='save' id='save' value='<?php xl('Save Changes', 'e'); ?>' /></span> &nbsp;&nbsp;
<?php xl('With selected:', 'e');?>
<input type='button' name='deletefields' id='deletefields' value='<?php xl('Delete', 'e'); ?>' style="font-size:90%" disabled="disabled" />
<input type='button' name='movefields' id='movefields' value='<?php xl('Move to...', 'e'); ?>' style="font-size:90%" disabled="disabled" />
<input type='button' value='<?php echo xla('Tips'); ?>' onclick='$("#tips").toggle();' />&nbsp;
<input type='button' value='<?php echo xla('Encounter Preview'); ?>' onclick='layoutLook();' />

<?php } else { ?>
<input type='button' value='<?php echo xla('New Layout'); ?>' onclick='edit_layout_props("")' />&nbsp;
<?php } ?>

<div id="tips" class="container tips"><section class="panel panel-primary">
  <header class="panel-heading">
   <h3 class="panel-title"><?php echo xlt('Usage Tips') ?></h3>
  </header>
  <div class="panel-body">
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
   <button class='btn btn-xs btn-success pull-right' onclick='$("#tips").toggle();return false;'><?php echo xlt('Dismiss')?></button>
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

while ($row = sqlFetchArray($res)) {
    $group_id = $row['group_id'];
    if ($group_id != $prevgroup) {
        if ($firstgroup == false) {
            echo "</tbody></table></div>\n";
            echo "<div id='" . $group_id . "' class='group'>";
        } else { // making first group flag useful for maintaining top fixed nav bar.
            echo "<div id='" . $group_id . "' class='group' style='padding-top:40px'>";
        }
        echo "<div class='text bold layouts_title' style='position:relative; background-color: #c9dbf2;'>";

        // Get the fully qualified descriptive name of this group (i.e. including ancestor names).
        $gdispname = '';
        for ($i = 1; $i <= strlen($group_id); ++$i) {
            if ($gdispname) {
                $gdispname .= ' / ';
            }
            $gdispname .= $grparr[substr($group_id, 0, $i)]['grp_title'];
        }
        $gmyname = $grparr[$group_id]['grp_title'];

        echo text($gdispname);

        // if not english and set to translate layout labels, then show the translation of group name
        if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
            echo "<span class='translation'&gt;&gt;&gt;&nbsp; " . xlt($gdispname) . "</span>";
            echo "&nbsp; ";
        }

        echo "&nbsp; ";
        echo " <input type='button' class='addfield' id='addto~$group_id' value='" . xla('Add Field') . "'/>";
        echo "&nbsp; &nbsp; ";
        echo " <input type='button' class='renamegroup' id='$group_id~$gmyname' value='" . xla('Rename Group') . "'/>";
        /******************************************************************
        echo "&nbsp; &nbsp; ";
        echo " <input type='button' class='deletegroup' id='$group_id' value='" . xl('Delete Group') . "'/>";
        ******************************************************************/
        echo "&nbsp; &nbsp; ";
        echo " <input type='button' class='movegroup' id='$group_id~up' value='" . xl('Move Up') . "'/>";
        echo "&nbsp; &nbsp; ";
        echo " <input type='button' class='movegroup' id='$group_id~down' value='" . xl('Move Down') . "'/>";
        echo "&nbsp; &nbsp; ";
        echo "<input type='button' value='" . xla('Group Properties') . "' onclick='edit_layout_props(\"$group_id\")' />";
        echo "</div>";
        $firstgroup = false;
    ?>
  <table class='table table-condensed table-striped'>
  <thead>
   <tr class='head'>
    <th style='width:1%'><?php xl('Order', 'e'); ?></th>
    <th <?php echo " $lbfonly"; ?>style='width:5%'><?php xl('Source', 'e'); ?></th>
    <th style='width:5%'><?php xl('ID', 'e'); ?>&nbsp;<span class="help" title=<?php xl('A unique value to identify this field, not visible to the user', 'e', '\'', '\''); ?> >(?)</span></th>
    <th style='width:10%'><?php xl('Label', 'e'); ?>&nbsp;<span class="help" title=<?php xl('The label that appears to the user on the form', 'e', '\'', '\''); ?> >(?)</span></th>
    <?php // if not english and showing layout label translations, then show translation header for title
    if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
        echo "<th>" . xl('Translation')."<span class='help' title='" . xl('The translated label that will appear on the form in current language') . "'>&nbsp;(?)</span></th>";
    } ?>
      <th style='width:6%'><?php xl('UOR', 'e'); ?></th>
      <th style='width:10%'><?php xl('Data Type', 'e'); ?></th>
      <th style='width:1%'><?php xl('Size', 'e'); ?></th>
      <th style='width:3%'><?php xl('Max Size', 'e'); ?></th>
      <th style='width:10%'><?php xl('List', 'e'); ?></th>
      <th style='width:10%'><?php xl('Backup List', 'e'); ?></th>
      <th style='width:1%'><?php xl('Label Cols', 'e'); ?></th>
      <th style='width:1%'><?php xl('Data Cols', 'e'); ?></th>
      <th style='width:10%'><?php xl('Options', 'e'); ?></th>
      <th style='width:20%'><?php xl('Description', 'e'); ?></th>
        <?php // if not english and showing layout label translations, then show translation header for description
        if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
            echo "<th>" . xl('Translation')."<span class='help' title='" . xl('The translation of description in current language')."'>&nbsp;(?)</span></th>";
        } ?>
      <th style='width:1%'><?php echo xlt('?'); ?></th>
   </tr>
  </thead>
  <tbody>

    <?php
    } // end if-group_name

    writeFieldLine($row);
    $prevgroup = $group_id;
} // end while loop

?>
</tbody>
</table>

<?php echo $extra_html; ?>

</form>

<!-- template DIV that appears when user chooses to rename an existing group -->
<div id="renamegroupdetail"
 style="border: 1px solid black; padding: 3px; display: none; visibility: hidden; background-color: lightgrey;">
<input type="hidden" name="renameoldgroupname" id="renameoldgroupname" value="" />
<?php echo xlt('Group Name'); ?>:
<input type="text" size="20" maxlength="30" name="renamegroupname" id="renamegroupname" />
&nbsp;&nbsp;
<?php echo xlt('Parent'); ?>:
<?php echo genGroupSelector('renamegroupparent', $layout_id); ?>
<br>
<input type="button" class="saverenamegroup .btn-save" value="<?php echo xla('Rename Group'); ?>" />
<input type="button" class="cancelrenamegroup" value="<?php echo xla('Cancel'); ?>" />
</div>

<!-- template DIV that appears when user chooses to add a new group -->
<div id="groupdetail"
 style="border: 1px solid black; padding: 3px; display: none; visibility: hidden; background-color: lightgrey;">
<span class='bold'>
<?php echo xlt('Group Name'); ?>:
<input type="text" size="20" maxlength="30" name="newgroupname" id="newgroupname" />
&nbsp;&nbsp;
<?php echo xlt('Parent'); ?>:
<?php echo genGroupSelector('newgroupparent', $layout_id); ?>
<br>
<table class='table table-condensed table-striped' style="border-collapse: collapse; margin-top: 5px;">
<thead>
 <tr class='head'>
  <th style='width:1%'><?php xl('Order', 'e'); ?></th>
  <th <?php echo " $lbfonly"; ?>style='width:5%'><?php xl('Source', 'e'); ?></th>
  <th style='width:5%'><?php xl('ID', 'e'); ?>&nbsp;<span class="help" title=<?php xl('A unique value to identify this field, not visible to the user', 'e', '\'', '\''); ?> >(?)</span></th>
  <th style='width:10%'><?php xl('Label', 'e'); ?>&nbsp;<span class="help" title=<?php xl('The label that appears to the user on the form', 'e', '\'', '\''); ?> >(?)</span></th>
  <th style='width:6%'><?php xl('UOR', 'e'); ?></th>
  <th style='width:10%'><?php xl('Data Type', 'e'); ?></th>
  <th style='width:1%'><?php xl('Size', 'e'); ?></th>
  <th style='width:1%'><?php xl('Max Size', 'e'); ?></th>
  <th style='width:10%'><?php xl('List', 'e'); ?></th>
  <th style='width:10%'><?php xl('Backup List', 'e'); ?></th>
  <th style='width:1%'><?php xl('Label Cols', 'e'); ?></th>
  <th style='width:1%'><?php xl('Data Cols', 'e'); ?></th>
  <th style='width:10%'><?php xl('Options', 'e'); ?></th>
  <th style='width:20%'><?php xl('Description', 'e'); ?></th>
 </tr>
</thead>
<tbody>
<tr class='center'>
<td ><input type="text" name="gnewseq" id="gnewseq" value="" size="2" maxlength="4"> </td>
<td<?php echo " $lbfonly"; ?>>
<select class='form-control' name='gnewsource' id='gnewsource'>
<?php
foreach ($sources as $key => $value) {
    echo "<option value='" . attr($key) . "'>" . text($value) . "</option>\n";
}
?>
</select>
</td>
<td><input type="text" name="gnewid" id="gnewid" value="" size="10" maxlength="20"
     onclick='FieldIDClicked(this)'> </td>
<td><input type="text" name="gnewtitle" id="gnewtitle" value="" size="20" maxlength="63"> </td>
<td>
<select class='form-control' name="gnewuor" id="gnewuor">
<option value="0"><?php xl('Unused', 'e'); ?></option>
<option value="1" selected><?php xl('Optional', 'e'); ?></option>
<option value="2"><?php xl('Required', 'e'); ?></option>
</select>
</td>
<td align='center'>
<select class='form-control' name='gnewdatatype' id='gnewdatatype'>
<option value=''></option>
<?php
global $datatypes;
foreach ($datatypes as $key => $value) {
    echo "<option value='$key'>$value</option>";
}
?>
</select>
</td>
<td><input type="text" name="gnewlengthWidth" id="gnewlengthWidth" value="" size="1" maxlength="3" title="<?php echo xla('Width'); ?>">
    <input type="text" name="gnewlengthHeight" id="gnewlengthHeight" value="" size="1" maxlength="3" title="<?php echo xla('Height'); ?>"></td>
<td><input type="text" name="gnewmaxSize" id="gnewmaxSize" value="" size="1" maxlength="3" title="<?php echo xla('Maximum Size (entering 0 will allow any size)'); ?>"></td>
<td><input type="text" name="gnewlistid" id="gnewlistid" value="" size="8" maxlength="100" class="listid">
    <select class='form-control' name='gcontextName' id='gcontextName' style='display:none'>
        <?php
        $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
        while ($row = sqlFetchArray($res)) {
            echo "<option value='".htmlspecialchars($row['cl_list_item_long'], ENT_QUOTES)."'>".htmlspecialchars($row['cl_list_item_long'], ENT_QUOTES)."</option>";
        }
        ?>
    </select>
</td>
<td><input type="text" name="gnewbackuplistid" id="gnewbackuplistid" value="" size="8" maxlength="100" class="listid"></td>
<td><input type="text" name="gnewtitlecols" id="gnewtitlecols" value="" size="3" maxlength="3"> </td>
<td><input type="text" name="gnewdatacols" id="gnewdatacols" value="" size="3" maxlength="3"> </td>
<td><select name="gnewedit_options[]" id="gnewedit_options" class="typeAddons" multiple style='width:100%' value="" size="3"></select>
    <input type="hidden"  name="gnewdefault" id="gnewdefault" value="" /> </td>
<td><input type="text" name="gnewdesc" id="gnewdesc" value="" size="30"> </td>
</tr>
</tbody>
</table>
<br>
<input type="button" class="savenewgroup" value=<?php xl('Save New Group', 'e', '\'', '\''); ?>>
<input type="button" class="cancelnewgroup" value=<?php xl('Cancel', 'e', '\'', '\''); ?>>
</span>
</div>

<!-- template DIV that appears when user chooses to add a new field to a group -->
<div id="fielddetail" class="fielddetail" style="display: none; visibility: hidden">
<input type="hidden" name="newfieldgroupid" id="newfieldgroupid" value="">
<table class="table table-condensed" style="border-collapse: collapse;">
 <thead>
  <tr class='head'>
   <th style='width:1%'><?php xl('Order', 'e'); ?></th>
   <th <?php echo " $lbfonly"; ?>style='width:5%'><?php xl('Source', 'e'); ?></th>
   <th style='width:5%'><?php xl('ID', 'e'); ?>&nbsp;<span class="help" title=<?php xl('A unique value to identify this field, not visible to the user', 'e', '\'', '\''); ?> >(?)</span></th>
   <th style='width:10%'><?php xl('Label', 'e'); ?>&nbsp;<span class="help" title=<?php xl('The label that appears to the user on the form', 'e', '\'', '\''); ?> >(?)</span></th>
   <th style='width:6%'><?php xl('UOR', 'e'); ?></th>
   <th style='width:10%'><?php xl('Data Type', 'e'); ?></th>
   <th style='width:1%'><?php xl('Size', 'e'); ?></th>
   <th style='width:1%'><?php xl('Max Size', 'e'); ?></th>
   <th style='width:10%'><?php xl('List', 'e'); ?></th>
   <th style='width:10%'><?php xl('Backup List', 'e'); ?></th>
   <th style='width:1%'><?php xl('Label Cols', 'e'); ?></th>
   <th style='width:1%'><?php xl('Data Cols', 'e'); ?></th>
   <th style='width:10%'><?php xl('Options', 'e'); ?></th>
   <th style='width:20%'><?php xl('Description', 'e'); ?></th>
  </tr>
 </thead>
 <tbody>
  <tr class='center'>
   <td ><input type="text" name="newseq" id="newseq" value="" size="2" maxlength="4"> </td>
   <td<?php echo " $lbfonly"; ?>>
    <select class='form-control' name='newsource' id='newsource'>
<?php
foreach ($sources as $key => $value) {
    echo "    <option value='" . attr($key) . "'>" . text($value) . "</option>\n";
}
?>
    </select>
   </td>
   <td ><input type="text" name="newid" id="newid" value="" size="10" maxlength="20"
         onclick='FieldIDClicked(this)'> </td>
   <td><input type="text" name="newtitle" id="newtitle" value="" size="20" maxlength="63"> </td>
   <td>
    <select class='form-control' name="newuor" id="newuor">
     <option value="0"><?php xl('Unused', 'e'); ?></option>
     <option value="1" selected><?php xl('Optional', 'e'); ?></option>
     <option value="2"><?php xl('Required', 'e'); ?></option>
    </select>
   </td>
   <td align='center'>
    <select class='form-control' name='newdatatype' id='newdatatype'>
     <option value=''></option>
<?php
global $datatypes;
foreach ($datatypes as $key => $value) {
    echo "     <option value='$key'>$value</option>\n";
}
?>
    </select>
   </td>
   <td><input type="text" name="newlengthWidth" id="newlengthWidth" value="" size="1" maxlength="3" title="<?php echo xla('Width'); ?>">
       <input type="text" name="newlengthHeight" id="newlengthHeight" value="" size="1" maxlength="3" title="<?php echo xla('Height'); ?>"></td>
   <td><input type="text" name="newmaxSize" id="newmaxSize" value="" size="1" maxlength="3" title="<?php echo xla('Maximum Size (entering 0 will allow any size)'); ?>"></td>
   <td><input type="text" name="newlistid" id="newlistid" value="" size="8" maxlength="31" class="listid">
       <select class='form-control' name='contextName' id='contextName' style='display:none'>
        <?php
        $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
        while ($row = sqlFetchArray($res)) {
            echo "<option value='".htmlspecialchars($row['cl_list_item_long'], ENT_QUOTES)."'>".htmlspecialchars($row['cl_list_item_long'], ENT_QUOTES)."</option>";
        }
        ?>
       </select>
   </td>
   <td><input type="text" name="newbackuplistid" id="newbackuplistid" value="" size="8" maxlength="31" class="listid"></td>
   <td><input type="text" name="newtitlecols" id="newtitlecols" value="" size="3" maxlength="3"> </td>
   <td><input type="text" name="newdatacols" id="newdatacols" value="" size="3" maxlength="3"> </td>
   <td><select name="newedit_options[]" id="newedit_options"  multiple class='typeAddons'  style='width:100%'></select>
       <input type="hidden"  name="newdefault" id="newdefault" value="" /> </td>
   <td><input type="text" name="newdesc" id="newdesc" value="" size="30"> </td>
  </tr>
  <tr>
   <td colspan="9">
    <input type="button" class="savenewfield" value=<?php xl('Save New Field', 'e', '\'', '\''); ?>>
    <input type="button" class="cancelnewfield" value=<?php xl('Cancel', 'e', '\'', '\''); ?>>
   </td>
  </tr>
 </tbody>
</table>
</div>
</div>
</body>

<script>
/* Field modifier objects - heading towards context based.
    Used by Select2 so rtl may be enabled*/
<?php echo "var fldOptions = [
	{id: 'A',text:'" . xla('Age') . "',ctx:['4'],ctxExcp:['0']},
	{id: 'B',text:'" . xla('Gestational Age') . "',ctx:['4'],ctxExcp:['0']},
	{id: 'F',text:'" . xla('Add Time to Date') . "',ctx:['4'],ctxExcp:['0']},
	{id: 'C',text:'" . xla('Capitalize') . "',ctx:['0'],ctxExcp:['4','15','40']},
	{id: 'D',text:'" . xla('Dup Check') . "'},
	{id: 'E',text:'" . xla('Dup Check on only Edit') . "'},
	{id: 'W',text:'" . xla('Dup Check on only New') . "'},
	{id: 'G',text:'" . xla('Graphable') . "'},
	{id: 'I',text:'" . xla('Initially Open Group') . "'},
	{id: 'L',text:'" . xla('Lab Order') . "'},
	{id: 'N',text:'" . xla('New Patient Form') . "'},
	{id: 'O',text:'" . xla('Order Processor') . "'},
	{id: 'P',text:'" . xla('Default to previous value') . "'},
	{id: 'R',text:'" . xla('Distributor') . "'},
	{id: 'T',text:'" . xla('Description is default text') . "'},
	{id: 'U',text:'" . xla('Capitalize all') . "'},
	{id: 'V',text:'" . xla('Vendor') . "'},
	{id: 'X',text:'" . xla('Do Not Print') . "'},
    {id:'grp',text:'" . xla('Stylings') . "',children:[
        {id: 'RS',text:'" . xla('Add Bottom Border Row') . "'},
        {id: 'RO',text:'" . xla('Outline Entire Row') . "'},
        {id: 'DS',text:'" . xla('Add Data Bottom Border') . "'},
        {id: 'DO',text:'" . xla('Outline Data Col') . "'},
        {id: 'SP',text:'" . xla('Span Entire Row') . "'}
    ]},
    {id: '0',text:'" . xla('Read Only') . "'},
	{id: '1',text:'" . xla('Write Once') . "'},
	{id: '2',text:'" . xla('Billing Code Descriptions') . "'}];\n";

// Language direction for select2
echo 'var langDirection = "' . $_SESSION['language_direction'] . '";';
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

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){

    $(function () {
        $('.typeAddons').select2({
            data: fldOptions,
            theme: 'default',
            multiple: true,
            closeOnSelect: false,
            width:'100%',
            minimumResultsForSearch: 'Infinity',
            containerCssClass: ':all:',
            dir: langDirection,
            allowClear: false
        });
    });
      // Populate field option selects
    $(function() {
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
        $("#layout_id").val("<?php echo $layout_id; ?>");
        return;
      }
      mySubmit();
    });
    $(".addgroup").click(function() { AddGroup(this); });
    $(".savenewgroup").click(function() { SaveNewGroup(this); });
    /******************************************************************
    $(".deletegroup").click(function() { DeleteGroup(this); });
    ******************************************************************/
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
        $(".selectfield").each(function(i) {
            // if any field is selected, enable the delete-move buttons
            if ($(this).prop("checked") == true) {
                $("#deletefields").removeAttr("disabled");
                $("#movefields").removeAttr("disabled");

            }
        });
    });
    $("#movefields").click(function() { ShowGroups(this); });
    $(".savenewfield").click(function() { SaveNewField(this); });
    $(".cancelnewfield").click(function() { CancelNewField(this); });
    $("#newtitle").blur(function() { if ($("#newid").val() == "") $("#newid").val($("#newtitle").val()); });
    $("#newdatatype").change(function() { ChangeList(this.value);});
    $("#gnewdatatype").change(function() { ChangeListg(this.value);});
    $(".listid").click(function() { ShowLists(this); });

    // special class that skips the element
    $(".noselect").focus(function() { $(this).blur(); });

    // Save the changes made to the form
    var SaveChanges = function () {
      var f = document.forms[0];
      for (var i = 1; f['fld['+i+'][id]']; ++i) {
        var ival = f['fld['+i+'][id]'].value;
        for (var j = i + 1; f['fld['+j+'][id]']; ++j) {
          if (ival == f['fld['+j+'][id]'].value || ival == f['fld['+j+'][originalid]'].value) {
            alert('<?php echo xls('Error: Duplicated field ID'); ?>: ' + ival);
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
        // Assign a sensible default sequence number.
        $('#gnewseq').val(10);
    };

    // save the new group to the form
    var SaveNewGroup = function(btnObj) {
        // the group name field can only have letters, numbers, spaces and underscores
        // AND it cannot start with a number
        if ($("#newgroupname").val() == "") {
            alert("<?php xl('Group names cannot be blank', 'e'); ?>");
            return false;
        }
        if ($("#newgroupname").val().match(/^(\d+|\s+)/)) {
            alert("<?php xl('Group names cannot start with numbers or spaces.', 'e'); ?>");
            return false;
        }
        var validname = $("#newgroupname").val().replace(/[^A-za-z0-9 ]/g, "_"); // match any non-word characters and replace them
        $("#newgroupname").val(validname);

        // now, check the first group field values

        // seq must be numeric and <= 9999
        if (! IsNumeric($("#gnewseq").val(), 0, 9999)) {
            alert("<?php xl('Order must be a number between 1 and 9999', 'e'); ?>");
            return false;
        }
        // length must be numeric and less than 999
        if (! IsNumeric($("#gnewlengthWidth").val(), 0, 999)) {
            alert("<?php xl('Size must be a number between 1 and 999', 'e'); ?>");
            return false;
        }
        // titlecols must be numeric and less than 100
        if (! IsNumeric($("#gnewtitlecols").val(), 0, 999)) {
            alert("<?php xl('LabelCols must be a number between 1 and 999', 'e'); ?>");
            return false;
        }
        // datacols must be numeric and less than 100
        if (! IsNumeric($("#gnewdatacols").val(), 0, 999)) {
            alert("<?php xl('DataCols must be a number between 1 and 999', 'e'); ?>");
            return false;
        }
        // the id field can only have letters, numbers and underscores
        if ($("#gnewid").val() == "") {
            alert("<?php xl('ID cannot be blank', 'e'); ?>");
            return false;
        }
        var validid = $("#gnewid").val().replace(/(\s|\W)/g, "_"); // match any non-word characters and replace them
        $("#gnewid").val(validid);
        // similarly with the listid field
        validid = $("#gnewlistid").val().replace(/(\s|\W)/g, "_");
        $("#gnewlistid").val(validid);
        // similarly with the backuplistid field
        validid = $("#gnewbackuplistid").val().replace(/(\s|\W)/g, "_");
        $("#gnewbackuplistid").val(validid);


        // submit the form to add a new field to a specific group
        $("#formaction").val("addgroup");
        mySubmit();
    }

    /******************************************************************
    // actually delete an entire group from the database
    var DeleteGroup = function(btnObj) {
        var parts = $(btnObj).attr("id");
        var groupname = parts.replace(/^\d+/, "");
        if (confirm("<?php xl('WARNING', 'e', '', ' - ') . xl('This action cannot be undone.', 'e', '', '\n') . xl('Are you sure you wish to delete the entire group named', 'e', '', ' '); ?>'"+groupname+"'?")) {
            // submit the form to add a new field to a specific group
            $("#formaction").val("deletegroup");
            $("#deletegroupname").val(parts);
            $("#theform").submit();
        }
    };
    ******************************************************************/

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
            alert("<?php xl('Group names cannot start with numbers.', 'e'); ?>");
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
        if (confirm("<?php xl('WARNING', 'e', '', ' - ') . xl('This action cannot be undone.', 'e', '', '\n') . xl('Are you sure you wish to delete the selected fields?', 'e'); ?>")) {
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

        // seq must be numeric and <= 9999
        if (! IsNumeric($("#newseq").val(), 0, 9999)) {
            alert("<?php xl('Order must be a number between 1 and 9999', 'e'); ?>");
            return false;
        }
        // length must be numeric and less than 999
        if (! IsNumeric($("#newlengthWidth").val(), 0, 999)) {
            alert("<?php xl('Size must be a number between 1 and 999', 'e'); ?>");
            return false;
        }
        // titlecols must be numeric and less than 100
        if (! IsNumeric($("#newtitlecols").val(), 0, 999)) {
            alert("<?php xl('LabelCols must be a number between 1 and 999', 'e'); ?>");
            return false;
        }
        // datacols must be numeric and less than 100
        if (! IsNumeric($("#newdatacols").val(), 0, 999)) {
            alert("<?php xl('DataCols must be a number between 1 and 999', 'e'); ?>");
            return false;
        }
        // the id field can only have letters, numbers and underscores
        var validid = $("#newid").val().replace(/(\s|\W)/g, "_"); // match any non-word characters and replace them
        $("#newid").val(validid);
        // similarly with the listid field
        validid = $("#newlistid").val().replace(/(\s|\W)/g, "_");
        $("#newlistid").val(validid);
        // similarly with the backuplistid field
        validid = $("#newbackuplistid").val().replace(/(\s|\W)/g, "_");
        $("#newbackuplistid").val(validid);

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
        var title = "<?php echo xla('Select List');?>";
        dlgopen('../patient_file/encounter/find_code_dynamic.php?what=lists',"_blank", 850, 750, "", title);
        selectedfield = btnObj;
    };

    // show the popup choice of groups
    var ShowGroups = function(btnObj) {
        if (!myChangeCheck()) return;
        var title = "<?php echo xlt('Select Group');?>";
        dlgopen('../patient_file/encounter/find_code_dynamic.php?what=groups&layout_id=<?php echo addslashes($layout_id); ?>',
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
    var ChangeListg = function(btnObj){
      if(btnObj==34){
        $('#gnewlistid').hide();
        $('#gcontextName').show();
      }
      else{
        $('#gnewlistid').show();
        $('#gcontextName').hide();
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
      var msg = "<?php echo xls('You have unsaved changes.'); ?>";
      e.returnValue = msg;     // Gecko, Trident, Chrome 34+
      return msg;              // Gecko, WebKit, Chrome <34
    }
  });

}); /* Ready Done */

function layoutLook(){
    var form = "<?php echo attr($layout_id);?>";
    var btnName = "<?php echo xla('Back To Editor');?>";
    var url = "../patient_file/encounter/view_form.php?isShow&id=0&formname=" + form;
    var title = "<?php echo xlt('LBF Encounter Form Preview');?>";
    dlgopen(url, '_blank', 1250, 800, "", title);
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
        document.getElementById("fld[" + lineitem + "][list_id]").value = '';
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
  var title = "<?php echo xlt('Select Field');?>";
  dlgopen('../patient_file/encounter/find_code_dynamic.php?what=fields&source='
    + srcval, "_blank", 700, 600, "", title);
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
  elemFromPart('lengthHeight').value = fld_rows;
}

//////////////////////////////////////////////////////////////////////
// End code for field ID selection pop-up.
//////////////////////////////////////////////////////////////////////

/* this is called after the user chooses a new group from the popup window
 * it will submit the page so the selected fields can be moved into
 * the target group
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
    $("#formaction").val("movefields");
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

/****************************************************/
/****************************************************/
/****************************************************/

// tell if num is an Integer
function IsN(num) { return !/\D/.test(num); }

</script>

</html>
