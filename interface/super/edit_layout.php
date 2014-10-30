<?php
/**
 * Copyright (C) 2014 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @link    http://www.open-emr.org
 */

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/log.inc");
require_once("$srcdir/formdata.inc.php");

$layouts = array(
  'DEM' => xl('Demographics'),
  'HIS' => xl('History'),
  'REF' => xl('Referrals'),
  'FACUSR' => xl('Facility Specific User Information')
);
if ($GLOBALS['ippf_specific']) {
  $layouts['GCA'] = xl('Abortion Issues');
  $layouts['CON'] = xl('Contraception Issues');
  // $layouts['SRH'] = xl('SRH Visit Form');
}

// Include Layout Based Encounter Forms.
$lres = sqlStatement("SELECT * FROM list_options " .
  "WHERE list_id = 'lbfnames' ORDER BY seq, title");
while ($lrow = sqlFetchArray($lres)) {
  $layouts[$lrow['option_id']] = $lrow['title'];
}

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
  "21" => xl("Checkbox list"),
  "22" => xl("Textbox list"),
  "23" => xl("Exam results"),
  "24" => xl("Patient allergies"),
  "25" => xl("Checkbox w/text"),
  "26" => xl("List box w/add"),
  "27" => xl("Radio buttons"),
  "28" => xl("Lifestyle status"),
  "31" => xl("Static Text"),
  "32" => xl("Smoking Status"),
  "33" => xl("Race and Ethnicity"),
  "34" => xl("NationNotes"),
  "35" => xl("Facilities"),
  "36" => xl("Multiple Select List")
);

$sources = array(
  'F' => xl('Form'),
  'D' => xl('Patient'),
  'H' => xl('History'),
  'E' => xl('Visit'),
  'V' => xl('VisForm'),
);

function nextGroupOrder($order) {
  if ($order == '9') $order = 'A';
  else if ($order == 'Z') $order = 'a';
  else $order = chr(ord($order) + 1);
  return $order;
}

// Check authorization.
$thisauth = acl_check('admin', 'super');
if (!$thisauth) die(xl('Not authorized'));

// The layout ID identifies the layout to be edited.
$layout_id = empty($_REQUEST['layout_id']) ? '' : $_REQUEST['layout_id'];

// Tag style for stuff to hide if not an LBF layout.
$lbfonly = substr($layout_id,0,3) == 'LBF' ? "" : "style='display:none;'";

// Handle the Form actions

if ($_POST['formaction'] == "save" && $layout_id) {
    // If we are saving, then save.
    $fld = $_POST['fld'];
    for ($lino = 1; isset($fld[$lino]['id']); ++$lino) {
        $iter = $fld[$lino];
        $field_id = formTrim($iter['id']);
        $data_type = formTrim($iter['data_type']);
        $listval = $data_type == 34 ? formTrim($iter['contextName']) : formTrim($iter['list_id']);

        // Skip conditions for the line are stored as a serialized array.
        $condarr = array();
        for ($cix = 0; !empty($iter['condition_id'][$cix]); ++$cix) {
          $andor = empty($iter['condition_andor'][$cix]) ? '' : $iter['condition_andor'][$cix];
          $condarr[$cix] = array(
            'id'       => strip_escape_custom($iter['condition_id'      ][$cix]),
            'itemid'   => strip_escape_custom($iter['condition_itemid'  ][$cix]),
            'operator' => strip_escape_custom($iter['condition_operator'][$cix]),
            'value'    => strip_escape_custom($iter['condition_value'   ][$cix]),
            'andor'    => strip_escape_custom($andor),
          );
        }
        $conditions = empty($condarr) ? '' : serialize($condarr);

        if ($field_id) {
            sqlStatement("UPDATE layout_options SET " .
                "source = '"        . formTrim($iter['source'])    . "', " .
                "title = '"         . formTrim($iter['title'])     . "', " .
                "group_name = '"    . formTrim($iter['group'])     . "', " .
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
                "edit_options = '"  . formTrim($iter['edit_options']) . "', " .
                "default_value = '" . formTrim($iter['default'])   . "', " .
                "description = '"   . formTrim($iter['desc'])      . "', " .
                "conditions = '"    . add_escape_custom($conditions) . "' " .
                "WHERE form_id = '$layout_id' AND field_id = '$field_id'");
        }
    }
}

else if ($_POST['formaction'] == "addfield" && $layout_id) {
    // Add a new field to a specific group
    $data_type = formTrim($_POST['newdatatype']);
    $max_length = $data_type == 3 ? 3 : 255;
    $listval = $data_type == 34 ? formTrim($_POST['contextName']) : formTrim($_POST['newlistid']);
    sqlStatement("INSERT INTO layout_options (" .
      " form_id, source, field_id, title, group_name, seq, uor, fld_length, fld_rows" .
      ", titlecols, datacols, data_type, edit_options, default_value, description" .
      ", max_length, list_id, list_backup_id " .
      ") VALUES ( " .
      "'"  . formTrim($_POST['layout_id']      ) . "'" .
      ",'" . formTrim($_POST['newsource']      ) . "'" .
      ",'" . formTrim($_POST['newid']          ) . "'" .
      ",'" . formTrim($_POST['newtitle']       ) . "'" .
      ",'" . formTrim($_POST['newfieldgroupid']) . "'" .
      ",'" . formTrim($_POST['newseq']         ) . "'" .
      ",'" . formTrim($_POST['newuor']         ) . "'" .
      ",'" . formTrim($_POST['newlengthWidth']      ) . "'" .
      ",'" . formTrim($_POST['newlengthHeight']      ) . "'" .
      ",'" . formTrim($_POST['newtitlecols']   ) . "'" .
      ",'" . formTrim($_POST['newdatacols']    ) . "'" .
      ",'$data_type'"                                  .
      ",'" . formTrim($_POST['newedit_options']) . "'" .
      ",'" . formTrim($_POST['newdefault']     ) . "'" .
      ",'" . formTrim($_POST['newdesc']        ) . "'" .
      ",'"    . formTrim($_POST['newmaxSize'])    . "'"                                 .
      ",'" . $listval . "'" .
      ",'" . formTrim($_POST['newbackuplistid']) . "'" .
      " )");

    if (substr($layout_id,0,3) != 'LBF' && $layout_id != "FACUSR") {
      // Add the field to the table too (this is critical)
      if ($layout_id == "DEM") { $tablename = "patient_data"; }
      else if ($layout_id == "HIS") { $tablename = "history_data"; }
      else if ($layout_id == "REF") { $tablename = "transactions"; }
      else if ($layout_id == "SRH") { $tablename = "lists_ippf_srh"; }
      else if ($layout_id == "CON") { $tablename = "lists_ippf_con"; }
      else if ($layout_id == "GCA") { $tablename = "lists_ippf_gcac"; }
      sqlStatement("ALTER TABLE `" . $tablename . "` ADD ".
                      "`" . formTrim($_POST['newid']) . "`" .
                      " TEXT NOT NULL");
      newEvent("alter_table", $_SESSION['authUser'], $_SESSION['authProvider'], 1,
        $tablename . " ADD " . formTrim($_POST['newid']));
    }
}

else if ($_POST['formaction'] == "movefields" && $layout_id) {
    // Move field(s) to a new group in the layout
    $sqlstmt = "UPDATE layout_options SET ".
                " group_name='". $_POST['targetgroup']."' ".
                " WHERE ".
                " form_id = '".$_POST['layout_id']."' ".
                " AND field_id IN (";
    $comma = "";
    foreach (explode(" ", $_POST['selectedfields']) as $onefield) {
        $sqlstmt .= $comma."'".$onefield."'";
        $comma = ", ";
    }
    $sqlstmt .= ")";
    //echo $sqlstmt;
    sqlStatement($sqlstmt);
}

else if ($_POST['formaction'] == "deletefields" && $layout_id) {
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

    if (substr($layout_id,0,3) != 'LBF' && $layout_id != "FACUSR") {
        // drop the field from the table too (this is critical) 
        if ($layout_id == "DEM") { $tablename = "patient_data"; }
        else if ($layout_id == "HIS") { $tablename = "history_data"; }
        else if ($layout_id == "REF") { $tablename = "transactions"; }
        else if ($layout_id == "SRH") { $tablename = "lists_ippf_srh"; }
        else if ($layout_id == "CON") { $tablename = "lists_ippf_con"; }
        else if ($layout_id == "GCA") { $tablename = "lists_ippf_gcac"; }
        foreach (explode(" ", $_POST['selectedfields']) as $onefield) {
            sqlStatement("ALTER TABLE `".$tablename."` DROP `".$onefield."`");
            newEvent("alter_table", $_SESSION['authUser'], $_SESSION['authProvider'], 1, $tablename." DROP ".$onefield);
        }
    }
}

else if ($_POST['formaction'] == "addgroup" && $layout_id) {
    // all group names are prefixed with a number indicating their display order
    // this new group is prefixed with the net highest number given the
    // layout_id
    $results = sqlStatement("select distinct(group_name) as gname ".
                        " from layout_options where ".
                        " form_id = '".$_POST['layout_id']."'"
                        );
    $maxnum = '1';
    while ($result = sqlFetchArray($results)) {
      $tmp = substr($result['gname'], 0, 1);
      if ($tmp >= $maxnum) $maxnum = nextGroupOrder($tmp);
    }

    $data_type = formTrim($_POST['gnewdatatype']);
    $max_length = $data_type == 3 ? 3 : 255;
    $listval = $data_type == 34 ? formTrim($_POST['gcontextName']) : formTrim($_POST['gnewlistid']);
    // add a new group to the layout, with the defined field
    sqlStatement("INSERT INTO layout_options (" .
      " form_id, source, field_id, title, group_name, seq, uor, fld_length, fld_rows" .
      ", titlecols, datacols, data_type, edit_options, default_value, description" .
      ", max_length, list_id, list_backup_id " .
      ") VALUES ( " .
      "'"  . formTrim($_POST['layout_id']      ) . "'" .
      ",'" . formTrim($_POST['gnewsource']      ) . "'" .
      ",'" . formTrim($_POST['gnewid']          ) . "'" .
      ",'" . formTrim($_POST['gnewtitle']       ) . "'" .
      ",'" . formTrim($maxnum . $_POST['newgroupname']) . "'" .
      ",'" . formTrim($_POST['gnewseq']         ) . "'" .
      ",'" . formTrim($_POST['gnewuor']         ) . "'" .
      ",'" . formTrim($_POST['gnewlengthWidth']      ) . "'" .
      ",'" . formTrim($_POST['gnewlengthHeight']      ) . "'" .
      ",'" . formTrim($_POST['gnewtitlecols']   ) . "'" .
      ",'" . formTrim($_POST['gnewdatacols']    ) . "'" .
      ",'$data_type'"                                   .
      ",'" . formTrim($_POST['gnewedit_options']) . "'" .
      ",'" . formTrim($_POST['gnewdefault']     ) . "'" .
      ",'" . formTrim($_POST['gnewdesc']        ) . "'" .
      ",'"    . formTrim($_POST['gnewmaxSize'])    . "'"                                  .
      ",'" . $listval       . "'" .
      ",'" . formTrim($_POST['gnewbackuplistid']        ) . "'" .
      " )");

    if (substr($layout_id,0,3) != 'LBF' && $layout_id != "FACUSR") {
      // Add the field to the table too (this is critical)
      if ($layout_id == "DEM") { $tablename = "patient_data"; }
      else if ($layout_id == "HIS") { $tablename = "history_data"; }
      else if ($layout_id == "REF") { $tablename = "transactions"; }
      else if ($layout_id == "SRH") { $tablename = "lists_ippf_srh"; }
      else if ($layout_id == "CON") { $tablename = "lists_ippf_con"; }
      else if ($layout_id == "GCA") { $tablename = "lists_ippf_gcac"; }
      sqlStatement("ALTER TABLE `" . $tablename . "` ADD ".
                      "`" . formTrim($_POST['gnewid']) . "`" .
                      " TEXT NOT NULL");
      newEvent("alter_table", $_SESSION['authUser'], $_SESSION['authProvider'], 1,
        $tablename . " ADD " . formTrim($_POST['gnewid']));
    }
}

else if ($_POST['formaction'] == "deletegroup" && $layout_id) {
    // drop the fields from the related table (this is critical)
    if (substr($layout_id,0,3) != 'LBF' && $layout_id != "FACUSR") {
        $res = sqlStatement("SELECT field_id FROM layout_options WHERE " .
                            " form_id = '".$_POST['layout_id']."' ".
                            " AND group_name = '".$_POST['deletegroupname']."'"
                            );
        while ($row = sqlFetchArray($res)) {
            // drop the field from the table too (this is critical) 
            if ($layout_id == "DEM") { $tablename = "patient_data"; }
            else if ($layout_id == "HIS") { $tablename = "history_data"; }
            else if ($layout_id == "REF") { $tablename = "transactions"; }
            else if ($layout_id == "SRH") { $tablename = "lists_ippf_srh"; }
            else if ($layout_id == "CON") { $tablename = "lists_ippf_con"; }
            else if ($layout_id == "GCA") { $tablename = "lists_ippf_gcac"; }
            sqlStatement("ALTER TABLE `".$tablename."` DROP `".$row['field_id']."`");
            newEvent("alter_table", $_SESSION['authUser'], $_SESSION['authProvider'], 1, $tablename." DROP ".trim($row['field_id']));
        }
    }

    // Delete an entire group from the form
    sqlStatement("DELETE FROM layout_options WHERE ".
                " form_id = '".$_POST['layout_id']."' ".
                " AND group_name = '".$_POST['deletegroupname']."'"
                );
}

else if ($_POST['formaction'] == "movegroup" && $layout_id) {
  $results = sqlStatement("SELECT DISTINCT(group_name) AS gname " .
    "FROM layout_options WHERE form_id = '$layout_id' " .
    "ORDER BY gname");
  $garray = array();
  $i = 0;
  while ($result = sqlFetchArray($results)) {
    if ($result['gname'] == $_POST['movegroupname']) {
      if ($_POST['movedirection'] == 'up') { // moving up
        if ($i > 0) {
          $garray[$i] = $garray[$i - 1];
          $garray[$i - 1] = $result['gname'];
          $i++;
        }
        else {
          $garray[$i++] = $result['gname'];
        }
      }
      else { // moving down
        $garray[$i++] = '';
        $garray[$i++] = $result['gname'];
      }
    }
    else if ($i > 1 && $garray[$i - 2] == '') {
      $garray[$i - 2] = $result['gname'];
    }
    else {
      $garray[$i++] = $result['gname'];
    }
  }
  $nextord = '1';
  foreach ($garray as $value) {
    if ($value === '') continue;
    $newname = $nextord . substr($value, 1);
    sqlStatement("UPDATE layout_options SET " .
      "group_name = '$newname' WHERE " .
      "form_id = '$layout_id' AND " .
      "group_name = '$value'");
    $nextord = nextGroupOrder($nextord);
  }
}

else if ($_POST['formaction'] == "renamegroup" && $layout_id) {
  $currpos = substr($_POST['renameoldgroupname'], 0, 1);
  // update the database rows 
  sqlStatement("UPDATE layout_options SET " .
    "group_name = '" . $currpos . $_POST['renamegroupname'] . "' ".
    "WHERE form_id = '$layout_id' AND ".
    "group_name = '" . $_POST['renameoldgroupname'] . "'");
}

// Get the selected form's elements.
if ($layout_id) {
  $res = sqlStatement("SELECT * FROM layout_options WHERE " .
    "form_id = '$layout_id' ORDER BY group_name, seq");
}

// global counter for field numbers
$fld_line_no = 0;

$extra_html = '';

// This is called to generate a select option list for fields within this form.
// Used for selecting a field for testing in a skip condition.
//
function genFieldOptionList($current='') {
  global $layout_id;
  $option_list = "<option value=''>-- " . xlt('Please Select') . " --</option>";
  if ($layout_id) {
    $query = "SELECT field_id FROM layout_options WHERE form_id = ? ORDER BY group_name, seq";
    $res = sqlStatement($query, array($layout_id));
    while ($row = sqlFetchArray($res)) {
      $field_id = $row['field_id'];
      $option_list .= "<option value='" . attr($field_id) . "'";
      if ($field_id == $current) $option_list .= " selected";
      $option_list .= ">" . text($field_id) . "</option>";
    }
  }
  return $option_list;
}

// Write one option line to the form.
//
function writeFieldLine($linedata) {
    global $fld_line_no, $sources, $lbfonly, $extra_html;
    ++$fld_line_no;
    $checked = $linedata['default_value'] ? " checked" : "";
  
    //echo " <tr bgcolor='$bgcolor'>\n";
    echo " <tr id='fld[$fld_line_no]' class='".($fld_line_no % 2 ? 'even' : 'odd')."'>\n";
  
    echo "  <td class='optcell' style='width:4%' nowrap>";
    // tuck the group_name INPUT in here
    echo "<input type='hidden' name='fld[$fld_line_no][group]' value='" .
         htmlspecialchars($linedata['group_name'], ENT_QUOTES) . "' class='optin' />";

    echo "<input type='checkbox' class='selectfield' ".
            "name='".$linedata['group_name']."~".$linedata['field_id']."' ".
            "id='".$linedata['group_name']."~".$linedata['field_id']."' ".
            "title='".htmlspecialchars(xl('Select field', ENT_QUOTES))."'>";

    echo "<input type='text' name='fld[$fld_line_no][seq]' id='fld[$fld_line_no][seq]' value='" .
      htmlspecialchars($linedata['seq'], ENT_QUOTES) . "' size='2' maxlength='3' " .
      "class='optin' style='width:36pt' />";
    echo "</td>\n";

    echo "  <td align='center' class='optcell' $lbfonly style='width:3%'>";
    echo "<select name='fld[$fld_line_no][source]' class='optin noselect' $lbfonly>";
    foreach ($sources as $key => $value) {
        echo "<option value='$key'";
        if ($key == $linedata['source']) echo " selected";
        echo ">$value</option>\n";
    }
    echo "</select>";
    echo "</td>\n";

    echo "  <td align='left' class='optcell' style='width:10%'>";
    echo "<input type='text' name='fld[$fld_line_no][id]' value='" .
         htmlspecialchars($linedata['field_id'], ENT_QUOTES) . "' size='15' maxlength='63'
         class='optin noselect' style='width:100%' />";
         // class='optin noselect' onclick='FieldIDClicked(this)' />";
    /*
    echo "<input type='hidden' name='fld[$fld_line_no][id]' value='" .
         htmlspecialchars($linedata['field_id'], ENT_QUOTES) . "' />";
    echo htmlspecialchars($linedata['field_id'], ENT_QUOTES);
    */
    echo "</td>\n";
  
    echo "  <td align='center' class='optcell' style='width:12%'>";
    echo "<input type='text' id='fld[$fld_line_no][title]' name='fld[$fld_line_no][title]' value='" .
         htmlspecialchars($linedata['title'], ENT_QUOTES) . "' size='15' maxlength='63' class='optin' style='width:100%' />";
    echo "</td>\n";

    // if not english and set to translate layout labels, then show the translation
    if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
        echo "<td align='center' class='translation' style='width:10%'>" . htmlspecialchars(xl($linedata['title']), ENT_QUOTES) . "</td>\n";
    }
	
    echo "  <td align='center' class='optcell' style='width:4%'>";
    echo "<select name='fld[$fld_line_no][uor]' class='optin'>";
    foreach (array(0 =>xl('Unused'), 1 =>xl('Optional'), 2 =>xl('Required')) as $key => $value) {
        echo "<option value='$key'";
        if ($key == $linedata['uor']) echo " selected";
        echo ">$value</option>\n";
    }
    echo "</select>";
    echo "</td>\n";
  
    echo "  <td align='center' class='optcell' style='width:8%'>";
    echo "<select name='fld[$fld_line_no][data_type]' id='fld[$fld_line_no][data_type]' onchange=NationNotesContext('".$fld_line_no."',this.value)>";
    echo "<option value=''></option>";
    GLOBAL $datatypes;
    foreach ($datatypes as $key=>$value) {
        if ($linedata['data_type'] == $key)
            echo "<option value='$key' selected>$value</option>";
        else
            echo "<option value='$key'>$value</option>";
    }
    echo "</select>";
    echo "  </td>";

    echo "  <td align='center' class='optcell' style='width:4%'>";
    if ($linedata['data_type'] == 2 || $linedata['data_type'] == 3 ||
      $linedata['data_type'] == 21 || $linedata['data_type'] == 22 ||
      $linedata['data_type'] == 23 || $linedata['data_type'] == 25 ||
      $linedata['data_type'] == 27 || $linedata['data_type'] == 28 ||
      $linedata['data_type'] == 32)
    {
      // Show the width field
      echo "<input type='text' name='fld[$fld_line_no][lengthWidth]' value='" .
        htmlspecialchars($linedata['fld_length'], ENT_QUOTES) .
        "' size='1' maxlength='10' class='optin' title='" . xla('Width') . "' />";
      if ($linedata['data_type'] == 3) {
        // Show the height field
        echo "<input type='text' name='fld[$fld_line_no][lengthHeight]' value='" .
          htmlspecialchars($linedata['fld_rows'], ENT_QUOTES) .
          "' size='1' maxlength='10' class='optin' title='" . xla('Height') . "' />";
      }
      else {
        // Hide the height field
        echo "<input type='hidden' name='fld[$fld_line_no][lengthHeight]' value=''>";
      }
    }
    else {
      // all other data_types (hide both the width and height fields
      echo "<input type='hidden' name='fld[$fld_line_no][lengthWidth]' value=''>";
      echo "<input type='hidden' name='fld[$fld_line_no][lengthHeight]' value=''>";
    }
    echo "</td>\n";

    echo "  <td align='center' class='optcell' style='width:4%'>";
    echo "<input type='text' name='fld[$fld_line_no][maxSize]' value='" .
      htmlspecialchars($linedata['max_length'], ENT_QUOTES) .
      "' size='1' maxlength='10' class='optin' style='width:100%' " .
      "title='" . xla('Maximum Size (entering 0 will allow any size)') . "' />";
    echo "</td>\n";

    echo "  <td align='center' class='optcell' style='width:8%'>";
    if ($linedata['data_type'] ==  1 || $linedata['data_type'] == 21 ||
      $linedata['data_type'] == 22 || $linedata['data_type'] == 23 ||
      $linedata['data_type'] == 25 || $linedata['data_type'] == 26 ||
      $linedata['data_type'] == 27 || $linedata['data_type'] == 32 ||
      $linedata['data_type'] == 33 || $linedata['data_type'] == 34 ||
      $linedata['data_type'] == 36)
    {
      $type = "";
      $disp = "style='display:none'";
      if($linedata['data_type'] == 34){
        $type = "style='display:none'";
        $disp = "";
      }
      echo "<input type='text' name='fld[$fld_line_no][list_id]'  id='fld[$fld_line_no][list_id]' value='" .
        htmlspecialchars($linedata['list_id'], ENT_QUOTES) . "'".$type.
        " size='6' maxlength='30' class='optin listid' style='width:100%;cursor:pointer'".
        "title='". xl('Choose list') . "' />";
    
      echo "<select name='fld[$fld_line_no][contextName]' id='fld[$fld_line_no][contextName]' ".$disp.">";
        $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
        while($row = sqlFetchArray($res)){
          $sel = '';
          if ($linedata['list_id'] == $row['cl_list_item_long'])
          $sel = 'selected';
          echo "<option value='".htmlspecialchars($row['cl_list_item_long'],ENT_QUOTES)."' ".$sel.">".htmlspecialchars($row['cl_list_item_long'],ENT_QUOTES)."</option>";
        }
      echo "</select>";
    }
    else {
      // all other data_types
      echo "<input type='hidden' name='fld[$fld_line_no][list_id]' value=''>";
    }
    echo "</td>\n";

    //Backup List Begin
    echo "  <td align='center' class='optcell' style='width:4%'>";
    if ($linedata['data_type'] ==  1 || $linedata['data_type'] == 26 ||
        $linedata['data_type'] == 33 || $linedata['data_type'] == 36)
    {
        echo "<input type='text' name='fld[$fld_line_no][list_backup_id]' value='" .
    	    htmlspecialchars($linedata['list_backup_id'], ENT_QUOTES) .
    	    "' size='3' maxlength='10' class='optin listid' style='cursor:pointer; width:100%' />";
    }
    else {
        echo "<input type='hidden' name='fld[$fld_line_no][list_backup_id]' value=''>";
    }
    echo "</td>\n";
    //Backup List End
    
    echo "  <td align='center' class='optcell' style='width:4%'>";
    echo "<input type='text' name='fld[$fld_line_no][titlecols]' value='" .
         htmlspecialchars($linedata['titlecols'], ENT_QUOTES) . "' size='3' maxlength='10' class='optin' style='width:100%' />";
    echo "</td>\n";
  
    echo "  <td align='center' class='optcell' style='width:4%'>";
    echo "<input type='text' name='fld[$fld_line_no][datacols]' value='" .
         htmlspecialchars($linedata['datacols'], ENT_QUOTES) . "' size='3' maxlength='10' class='optin' style='width:100%' />";
    echo "</td>\n";
  
    echo "  <td align='center' class='optcell' style='width:5%' title='" .
          "A = " . xla('Age') .
        ", B = " . xla('Gestational Age') .
        ", C = " . xla('Capitalize') .
        ", D = " . xla('Dup Check') .
        ", G = " . xla('Graphable') .
        ", L = " . xla('Lab Order') .
        ", N = " . xla('New Patient Form') .
        ", O = " . xla('Order Processor') .
        ", P = " . xla('Default to previous value') .
        ", R = " . xla('Distributor') .
        ", T = " . xla('Description is default text') .
        ", U = " . xla('Capitalize all') .
        ", V = " . xla('Vendor') .
        ", 0 = " . xla('Read Only') .
        ", 1 = " . xla('Write Once') . 
        ", 2 = " . xla('Billing Code Descriptions') . 
        "'>";
    echo "<input type='text' name='fld[$fld_line_no][edit_options]' value='" .
      htmlspecialchars($linedata['edit_options'], ENT_QUOTES) . "' size='3' " .
      "maxlength='36' class='optin' style='width:100%' />";
    echo "</td>\n";
 
    /*****************************************************************
    echo "  <td align='center' class='optcell'>";
    if ($linedata['data_type'] == 2) {
      echo "<input type='text' name='fld[$fld_line_no][default]' value='" .
           htmlspecialchars($linedata['default_value'], ENT_QUOTES) . "' size='10' maxlength='63' class='optin' />";
    } else {
      echo "&nbsp;";
    }
    echo "</td>\n";
  
    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='fld[$fld_line_no][desc]' value='" .
         htmlspecialchars($linedata['description'], ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
    echo "</td>\n";

    // if not english and showing layout labels, then show the translation of Description
    if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
     echo "<td align='center' class='translation'>" . htmlspecialchars(xl($linedata['description']), ENT_QUOTES) . "</td>\n";
    }
    *****************************************************************/

    if ($linedata['data_type'] == 31) {
      echo "  <td align='center' class='optcell' style='width:24%'>";
      echo "<textarea name='fld[$fld_line_no][desc]' rows='3' cols='35' class='optin' style='width:100%'>" .
           $linedata['description'] . "</textarea>";
      echo "<input type='hidden' name='fld[$fld_line_no][default]' value='" .
         htmlspecialchars($linedata['default_value'], ENT_QUOTES) . "' />";
      echo "</td>\n";
    }
    else {
      echo "  <td align='center' class='optcell' style='width:24%'>";
      echo "<input type='text' name='fld[$fld_line_no][desc]' value='" .
        htmlspecialchars($linedata['description'], ENT_QUOTES) .
        "' size='30' maxlength='63' class='optin' style='width:100%' />";
      echo "<input type='hidden' name='fld[$fld_line_no][default]' value='" .
        htmlspecialchars($linedata['default_value'], ENT_QUOTES) . "' />";
      echo "</td>\n";
      // if not english and showing layout labels, then show the translation of Description
      if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
        echo "<td align='center' class='translation' style='width:10%'>" .
        htmlspecialchars(xl($linedata['description']), ENT_QUOTES) . "</td>\n";
      }
    }

    // The "?" to click on for yet more field attributes.
    echo "  <td class='bold' id='querytd_$fld_line_no' style='cursor:pointer;";
    if (!empty($linedata['conditions'])) echo "background-color:#77ff77;";
    echo "' onclick='extShow($fld_line_no, this)' align='center' ";
    echo "title='" . xla('Click here to view/edit more details') . "'>";
    echo "&nbsp;?&nbsp;";
    echo "</td>\n";

    echo " </tr>\n";

    // Create a floating div for the additional attributes of this field.
    $conditions = empty($linedata['conditions']) ?
      array(0 => array('id' => '', 'itemid' => '', 'operator' => '', 'value' => '')) :
      unserialize($linedata['conditions']);
    //
    $extra_html .= "<div id='ext_$fld_line_no' " .
      "style='position:absolute;width:750px;border:1px solid black;" .
      "padding:2px;background-color:#cccccc;visibility:hidden;" .
      "z-index:1000;left:-1000px;top:0px;font-size:9pt;'>\n" .
      "<table width='100%'>\n" .
      " <tr>\n" .
      "  <th colspan='3' align='left' class='bold'>\"" . text($linedata['field_id']) . "\" " .
      xlt('will be hidden if') . ":</th>\n" .
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
        'eq' => xl('Equals'         ),
        'ne' => xl('Does not equal' ),
        'se' => xl('Is selected'    ),
        'ns' => xl('Is not selected'),
      ) as $key => $value) {
        $extra_html .= "    <option value='$key'";
        if ($key == $condition['operator']) $extra_html .= " selected";
        $extra_html .= ">" . text($value) . "</option>\n";
      }
      $extra_html .=
        "   </select>\n" .
        "  </td>\n" .
        "  <td align='left' title='" . xla('Only for comparisons') . "'>\n" .
        "   <input type='text' name='fld[$fld_line_no][condition_value][$i]' value='" .
        attr($condition['value']) . "' size='15' maxlength='63' />\n" .
        "  </td>\n";
      if (count($conditions) == $i + 1) {
        $extra_html .=
          "  <td align='right' title='" . xla('Add a condition') . "'>\n" .
          "   <input type='button' value='+' onclick='extAddCondition($fld_line_no,this)' />\n" .
          "  </td>\n";
      }
      else {
        $extra_html .=
          "  <td align='right'>\n" .
          "   <select name='fld[$fld_line_no][condition_andor][$i]'>\n";
        foreach (array(
          'and' => xl('And'),
          'or'  => xl('Or' ),
        ) as $key => $value) {
          $extra_html .= "    <option value='$key'";
          if ($key == $condition['andor']) $extra_html .= " selected";
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
      "</table>\n" .
      "</div>\n";
}
?>
<html>

<head>
<?php html_header_show();?>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>

<title><?php  xl('Layout Editor','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; }
tr.detail { font-size:10pt; }
td        { font-size:10pt; }
input     { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }
.optcell  { }
.optin    { background: transparent; }
.group {
    margin: 0pt 0pt 8pt 0pt;
    padding: 0;
    width: 100%;
}
.group table {
    border-collapse: collapse;
    width: 100%;
}
.odd td {
    background-color: #ddddff;
    padding: 3px 0px 3px 0px;
}
.even td {
    background-color: #ffdddd;
    padding: 3px 0px 3px 0px;
}
.help { cursor: help; }
.layouts_title { font-size: 110%; }
.translation {
    color: green;
    font-size:10pt;
}
.highlight * {
    border: 2px solid blue;
    background-color: yellow;
    color: black;
}
</style>

<script language="JavaScript">

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
    "<option value='or' ><?php echo xls('Or' ) ?></option>" +
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
    "<option value='eq'><?php echo xls('Equals'         ) ?></option>" +
    "<option value='ne'><?php echo xls('Does not equal' ) ?></option>" +
    "<option value='se'><?php echo xls('Is selected'    ) ?></option>" +
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
  var data_type = f['fld[' + i + '][data_type]'].value;
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
  var thisid = document.forms[0]['fld[' + lino + '][condition_id][0]'].value;
  var thistd = document.getElementById("querytd_" + lino);
  thistd.style.backgroundColor = thisid ? '#77ff77' : '';
  setListItemOptions(lino, seq, false);
}

</script>

</head>

<body class="body_top">

<form method='post' name='theform' id='theform' action='edit_layout.php'>
<input type="hidden" name="formaction" id="formaction" value="">
<!-- elements used to identify a field to delete -->
<input type="hidden" name="deletefieldid" id="deletefieldid" value="">
<input type="hidden" name="deletefieldgroup" id="deletefieldgroup" value="">
<!-- elements used to identify a group to delete -->
<input type="hidden" name="deletegroupname" id="deletegroupname" value="">
<!-- elements used to change the group order -->
<input type="hidden" name="movegroupname" id="movegroupname" value="">
<input type="hidden" name="movedirection" id="movedirection" value="">
<!-- elements used to select more than one field -->
<input type="hidden" name="selectedfields" id="selectedfields" value="">
<input type="hidden" id="targetgroup" name="targetgroup" value="">

<p><b><?php xl('Edit layout','e'); ?>:</b>&nbsp;
<select name='layout_id' id='layout_id'>
 <option value=''>-- <?php echo xl('Select') ?> --</option>
<?php
foreach ($layouts as $key => $value) {
  echo " <option value='$key'";
  if ($key == $layout_id) echo " selected";
  echo ">$value</option>\n";
}
?>
</select></p>

<?php if ($layout_id) { ?>
<div style='margin: 0 0 8pt 0;'>
<input type='button' class='addgroup' id='addgroup' value=<?php xl('Add Group','e','\'','\''); ?>/>
</div>
<?php } ?>

<?php 
$prevgroup = "!@#asdf1234"; // an unlikely group name
$firstgroup = true; // flag indicates it's the first group to be displayed
while ($row = sqlFetchArray($res)) {
  if ($row['group_name'] != $prevgroup) {
    if ($firstgroup == false) { echo "</tbody></table></div>\n"; }
    echo "<div id='".$row['group_name']."' class='group'>";
    echo "<div class='text bold layouts_title' style='position:relative; background-color: #eef'>";
    // echo preg_replace("/^\d+/", "", $row['group_name']);
    echo substr($row['group_name'], 1);
    echo "&nbsp; ";
    // if not english and set to translate layout labels, then show the translation of group name
    if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
      // echo "<span class='translation'>>>&nbsp; " . xl(preg_replace("/^\d+/", "", $row['group_name'])) . "</span>";
      echo "<span class='translation'>>>&nbsp; " . xl(substr($row['group_name'], 1)) . "</span>";
      echo "&nbsp; ";	
    }
    echo "&nbsp; ";
    echo " <input type='button' class='addfield' id='addto~".$row['group_name']."' value='" . xl('Add Field') . "'/>";
    echo "&nbsp; &nbsp; ";
    echo " <input type='button' class='renamegroup' id='".$row['group_name']."' value='" . xl('Rename Group') . "'/>";
    echo "&nbsp; &nbsp; ";
    echo " <input type='button' class='deletegroup' id='".$row['group_name']."' value='" . xl('Delete Group') . "'/>";
    echo "&nbsp; &nbsp; ";
    echo " <input type='button' class='movegroup' id='".$row['group_name']."~up' value='" . xl('Move Up') . "'/>";
    echo "&nbsp; &nbsp; ";
    echo " <input type='button' class='movegroup' id='".$row['group_name']."~down' value='" . xl('Move Down') . "'/>";
    echo "</div>";
    $firstgroup = false;
?>

<table>
<thead>
 <tr class='head'>
  <th><?php xl('Order','e'); ?></th>
  <th<?php echo " $lbfonly"; ?>><?php xl('Source','e'); ?></th>
  <th><?php xl('ID','e'); ?>&nbsp;<span class="help" title=<?php xl('A unique value to identify this field, not visible to the user','e','\'','\''); ?> >(?)</span></th>
  <th><?php xl('Label','e'); ?>&nbsp;<span class="help" title=<?php xl('The label that appears to the user on the form','e','\'','\''); ?> >(?)</span></th>
  <?php // if not english and showing layout label translations, then show translation header for title
  if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
   echo "<th>" . xl('Translation')."<span class='help' title='" . xl('The translated label that will appear on the form in current language') . "'>&nbsp;(?)</span></th>";	
  } ?>		  
  <th><?php xl('UOR','e'); ?></th>
  <th><?php xl('Data Type','e'); ?></th>
  <th><?php xl('Size','e'); ?></th>
  <th><?php xl('Max Size','e'); ?></th>
  <th><?php xl('List','e'); ?></th>
  <th><?php xl('Backup List','e'); ?></th>
  <th><?php xl('Label Cols','e'); ?></th>
  <th><?php xl('Data Cols','e'); ?></th>
  <th><?php xl('Options','e'); ?></th>
  <th><?php xl('Description','e'); ?></th>
  <?php // if not english and showing layout label translations, then show translation header for description
  if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
   echo "<th>" . xl('Translation')."<span class='help' title='" . xl('The translation of description in current language')."'>&nbsp;(?)</span></th>";
  } ?>
  <th><?php echo xlt('?'); ?></th>
 </tr>
</thead>
<tbody>

<?php
    } // end if-group_name

    writeFieldLine($row);
    $prevgroup = $row['group_name'];

} // end while loop

?>
</tbody>
</table></div>

<?php echo $extra_html; ?>

<?php if ($layout_id) { ?>
<span style="font-size:90%">
<?php xl('With selected:', 'e');?>
<input type='button' name='deletefields' id='deletefields' value='<?php xl('Delete','e'); ?>' style="font-size:90%" disabled="disabled" />
<input type='button' name='movefields' id='movefields' value='<?php xl('Move to...','e'); ?>' style="font-size:90%" disabled="disabled" />
</span>
<p>
<input type='button' name='save' id='save' value='<?php xl('Save Changes','e'); ?>' />
</p>
<?php } ?>

</form>

<!-- template DIV that appears when user chooses to rename an existing group -->
<div id="renamegroupdetail" style="border: 1px solid black; padding: 3px; display: none; visibility: hidden; background-color: lightgrey;">
<input type="hidden" name="renameoldgroupname" id="renameoldgroupname" value="">
<?php xl('Group Name','e'); ?>:	<input type="textbox" size="20" maxlength="30" name="renamegroupname" id="renamegroupname">
<br>
<input type="button" class="saverenamegroup" value=<?php xl('Rename Group','e','\'','\''); ?>>
<input type="button" class="cancelrenamegroup" value=<?php xl('Cancel','e','\'','\''); ?>>
</div>

<!-- template DIV that appears when user chooses to add a new group -->
<div id="groupdetail" style="border: 1px solid black; padding: 3px; display: none; visibility: hidden; background-color: lightgrey;">
<span class='bold'>
<?php xl('Group Name','e'); ?>:	<input type="textbox" size="20" maxlength="30" name="newgroupname" id="newgroupname">
<br>
<table style="border-collapse: collapse; margin-top: 5px;">
<thead>
 <tr class='head'>
  <th><?php xl('Order','e'); ?></th>
  <th<?php echo " $lbfonly"; ?>><?php xl('Source','e'); ?></th>
  <th><?php xl('ID','e'); ?>&nbsp;<span class="help" title=<?php xl('A unique value to identify this field, not visible to the user','e','\'','\''); ?> >(?)</span></th>
  <th><?php xl('Label','e'); ?>&nbsp;<span class="help" title=<?php xl('The label that appears to the user on the form','e','\'','\''); ?> >(?)</span></th>
  <th><?php xl('UOR','e'); ?></th>
  <th><?php xl('Data Type','e'); ?></th>
  <th><?php xl('Size','e'); ?></th>
  <th><?php xl('Max Size','e'); ?></th>
  <th><?php xl('List','e'); ?></th>
  <th><?php xl('Backup List','e'); ?></th>
  <th><?php xl('Label Cols','e'); ?></th>
  <th><?php xl('Data Cols','e'); ?></th>
  <th><?php xl('Options','e'); ?></th>
  <th><?php xl('Description','e'); ?></th>
 </tr>
</thead>
<tbody>
<tr class='center'>
<td ><input type="textbox" name="gnewseq" id="gnewseq" value="" size="2" maxlength="3"> </td>
<td<?php echo " $lbfonly"; ?>>
<select name='gnewsource' id='gnewsource'>
<?php
foreach ($sources as $key => $value) {
  echo "<option value='$key'>" . text($value) . "</option>\n";
}
?>
</select>
</td>
<td><input type="textbox" name="gnewid" id="gnewid" value="" size="10" maxlength="20"
     onclick='FieldIDClicked(this)'> </td>
<td><input type="textbox" name="gnewtitle" id="gnewtitle" value="" size="20" maxlength="63"> </td>
<td>
<select name="gnewuor" id="gnewuor">
<option value="0"><?php xl('Unused','e'); ?></option>
<option value="1" selected><?php xl('Optional','e'); ?></option>
<option value="2"><?php xl('Required','e'); ?></option>
</select>
</td>
<td align='center'>
<select name='gnewdatatype' id='gnewdatatype'>
<option value=''></option>
<?php
global $datatypes;
foreach ($datatypes as $key=>$value) {
    echo "<option value='$key'>$value</option>";
}
?>
</select>
</td>
<td><input type="textbox" name="gnewlengthWidth" id="gnewlengthWidth" value="" size="1" maxlength="3" title="<?php echo xla('Width'); ?>">
    <input type="textbox" name="gnewlengthHeight" id="gnewlengthHeight" value="" size="1" maxlength="3" title="<?php echo xla('Height'); ?>"></td>
<td><input type="textbox" name="gnewmaxSize" id="gnewmaxSize" value="" size="1" maxlength="3" title="<?php echo xla('Maximum Size (entering 0 will allow any size)'); ?>"></td>
<td><input type="textbox" name="gnewlistid" id="gnewlistid" value="" size="8" maxlength="31" class="listid">
    <select name='gcontextName' id='gcontextName' style='display:none'>
        <?php
        $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
        while($row = sqlFetchArray($res)){
          echo "<option value='".htmlspecialchars($row['cl_list_item_long'],ENT_QUOTES)."'>".htmlspecialchars($row['cl_list_item_long'],ENT_QUOTES)."</option>";
        }
        ?>
    </select>
</td>
<td><input type="textbox" name="gnewbackuplistid" id="gnewbackuplistid" value="" size="8" maxlength="31" class="listid"></td>
<td><input type="textbox" name="gnewtitlecols" id="gnewtitlecols" value="" size="3" maxlength="3"> </td>
<td><input type="textbox" name="gnewdatacols" id="gnewdatacols" value="" size="3" maxlength="3"> </td>
<td><input type="textbox" name="gnewedit_options" id="gnewedit_options" value="" size="3" maxlength="36">
    <input type="hidden"  name="gnewdefault" id="gnewdefault" value="" /> </td>
<td><input type="textbox" name="gnewdesc" id="gnewdesc" value="" size="30" maxlength="63"> </td>
</tr>
</tbody>
</table>
<br>
<input type="button" class="savenewgroup" value=<?php xl('Save New Group','e','\'','\''); ?>>
<input type="button" class="cancelnewgroup" value=<?php xl('Cancel','e','\'','\''); ?>>
</span>
</div>

<!-- template DIV that appears when user chooses to add a new field to a group -->
<div id="fielddetail" class="fielddetail" style="display: none; visibility: hidden">
<input type="hidden" name="newfieldgroupid" id="newfieldgroupid" value="">
<table style="border-collapse: collapse;">
 <thead>
  <tr class='head'>
   <th><?php xl('Order','e'); ?></th>
   <th<?php echo " $lbfonly"; ?>><?php xl('Source','e'); ?></th>
   <th><?php xl('ID','e'); ?>&nbsp;<span class="help" title=<?php xl('A unique value to identify this field, not visible to the user','e','\'','\''); ?> >(?)</span></th>
   <th><?php xl('Label','e'); ?>&nbsp;<span class="help" title=<?php xl('The label that appears to the user on the form','e','\'','\''); ?> >(?)</span></th>
   <th><?php xl('UOR','e'); ?></th>
   <th><?php xl('Data Type','e'); ?></th>
   <th><?php xl('Size','e'); ?></th>
   <th><?php xl('Max Size','e'); ?></th>
   <th><?php xl('List','e'); ?></th>
   <th><?php xl('Backup List','e'); ?></th>
   <th><?php xl('Label Cols','e'); ?></th>
   <th><?php xl('Data Cols','e'); ?></th>
   <th><?php xl('Options','e'); ?></th>
   <th><?php xl('Description','e'); ?></th>
  </tr>
 </thead>
 <tbody>
  <tr class='center'>
   <td ><input type="textbox" name="newseq" id="newseq" value="" size="2" maxlength="3"> </td>
   <td<?php echo " $lbfonly"; ?>>
    <select name='newsource' id='newsource'>
<?php
foreach ($sources as $key => $value) {
  echo "    <option value='$key'>" . text($value) . "</option>\n";
}
?>
    </select>
   </td>
   <td ><input type="textbox" name="newid" id="newid" value="" size="10" maxlength="20"
         onclick='FieldIDClicked(this)'> </td>
   <td><input type="textbox" name="newtitle" id="newtitle" value="" size="20" maxlength="63"> </td>
   <td>
    <select name="newuor" id="newuor">
     <option value="0"><?php xl('Unused','e'); ?></option>
     <option value="1" selected><?php xl('Optional','e'); ?></option>
     <option value="2"><?php xl('Required','e'); ?></option>
    </select>
   </td>
   <td align='center'>
    <select name='newdatatype' id='newdatatype'>
     <option value=''></option>
<?php
global $datatypes;
foreach ($datatypes as $key=>$value) {
    echo "     <option value='$key'>$value</option>\n";
}
?>
    </select>
   </td>
   <td><input type="textbox" name="newlengthWidth" id="newlengthWidth" value="" size="1" maxlength="3" title="<?php echo xla('Width'); ?>">
       <input type="textbox" name="newlengthHeight" id="newlengthHeight" value="" size="1" maxlength="3" title="<?php echo xla('Height'); ?>"></td>
   <td><input type="textbox" name="newmaxSize" id="newmaxSize" value="" size="1" maxlength="3" title="<?php echo xla('Maximum Size (entering 0 will allow any size)'); ?>"></td>
   <td><input type="textbox" name="newlistid" id="newlistid" value="" size="8" maxlength="31" class="listid">
       <select name='contextName' id='contextName' style='display:none'>
        <?php
        $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
        while($row = sqlFetchArray($res)){
          echo "<option value='".htmlspecialchars($row['cl_list_item_long'],ENT_QUOTES)."'>".htmlspecialchars($row['cl_list_item_long'],ENT_QUOTES)."</option>";
        }
        ?>
       </select>
   </td>
   <td><input type="textbox" name="newbackuplistid" id="newbackuplistid" value="" size="8" maxlength="31" class="listid"></td>
   <td><input type="textbox" name="newtitlecols" id="newtitlecols" value="" size="3" maxlength="3"> </td>
   <td><input type="textbox" name="newdatacols" id="newdatacols" value="" size="3" maxlength="3"> </td>
   <td><input type="textbox" name="newedit_options" id="newedit_options" value="" size="3" maxlength="36">
       <input type="hidden"  name="newdefault" id="newdefault" value="" /> </td>
   <td><input type="textbox" name="newdesc" id="newdesc" value="" size="30" maxlength="63"> </td>
  </tr>
  <tr>
   <td colspan="9">
    <input type="button" class="savenewfield" value=<?php xl('Save New Field','e','\'','\''); ?>>
    <input type="button" class="cancelnewfield" value=<?php xl('Cancel','e','\'','\''); ?>>
   </td>
  </tr>
 </tbody>
</table>
</div>

</body>

<script language="javascript">

// used when selecting a list-name for a field
var selectedfield;

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
    $("#save").click(function() { SaveChanges(); });
    $("#layout_id").change(function() { $('#theform').submit(); });

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
        $(".selectfield").each(function(i) {
            // if any field is selected, enable the delete-move buttons
            if ($(this).attr("checked") == true) {
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
        $("#formaction").val("save");
        $("#theform").submit();
    }

    /****************************************************/
    /************ Group functions ***********************/
    /****************************************************/

    // display the 'new group' DIV
    var AddGroup = function(btnObj) {
        // show the field details DIV
        $('#groupdetail').css('visibility', 'visible');
        $('#groupdetail').css('display', 'block');
        $(btnObj).parent().append($("#groupdetail"));
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
            alert("<?php xl('Group names cannot start with numbers or spaces.','e'); ?>");
            return false;
        }
        var validname = $("#newgroupname").val().replace(/[^A-za-z0-9 ]/g, "_"); // match any non-word characters and replace them
        $("#newgroupname").val(validname);

        // now, check the first group field values
        
        // seq must be numeric and less than 999
        if (! IsNumeric($("#gnewseq").val(), 0, 999)) {
            alert("<?php xl('Order must be a number between 1 and 999','e'); ?>");
            return false;
        }
        // length must be numeric and less than 999
        if (! IsNumeric($("#gnewlengthWidth").val(), 0, 999)) {
            alert("<?php xl('Size must be a number between 1 and 999','e'); ?>");
            return false;
        }
        // titlecols must be numeric and less than 100
        if (! IsNumeric($("#gnewtitlecols").val(), 0, 999)) {
            alert("<?php xl('LabelCols must be a number between 1 and 999','e'); ?>");
            return false;
        }
        // datacols must be numeric and less than 100
        if (! IsNumeric($("#gnewdatacols").val(), 0, 999)) {
            alert("<?php xl('DataCols must be a number between 1 and 999','e'); ?>");
            return false;
        }
        // some fields cannot be blank
        if ($("#gnewtitle").val() == "") {
            alert("<?php xl('Label cannot be blank','e'); ?>");
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
        $("#theform").submit();
    }

    // actually delete an entire group from the database
    var DeleteGroup = function(btnObj) {
        var parts = $(btnObj).attr("id");
        var groupname = parts.replace(/^\d+/, "");
        if (confirm("<?php xl('WARNING','e','',' - ') . xl('This action cannot be undone.','e','','\n') . xl('Are you sure you wish to delete the entire group named','e','',' '); ?>'"+groupname+"'?")) {
            // submit the form to add a new field to a specific group
            $("#formaction").val("deletegroup");
            $("#deletegroupname").val(parts);
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
    };

    // display the 'new field' DIV
    var MoveGroup = function(btnObj) {
        var btnid = $(btnObj).attr("id");
        var parts = btnid.split("~");
        var groupid = parts[0];
        var direction = parts[1];

        // submit the form to change group order
        $("#formaction").val("movegroup");
        $("#movegroupname").val(groupid);
        $("#movedirection").val(direction);
        $("#theform").submit();
    }

    // show the rename group DIV
    var RenameGroup = function(btnObj) {
        $('#renamegroupdetail').css('visibility', 'visible');
        $('#renamegroupdetail').css('display', 'block');
        $(btnObj).parent().append($("#renamegroupdetail"));
        $('#renameoldgroupname').val($(btnObj).attr("id"));
        $('#renamegroupname').val($(btnObj).attr("id").replace(/^\d+/, ""));
    }

    // save the new group to the form
    var SaveRenameGroup = function(btnObj) {
        // the group name field can only have letters, numbers, spaces and underscores
        // AND it cannot start with a number
        if ($("#renamegroupname").val().match(/^\d+/)) {
            alert("<?php xl('Group names cannot start with numbers.','e'); ?>");
            return false;
        }
        var validname = $("#renamegroupname").val().replace(/[^A-za-z0-9 ]/g, "_"); // match any non-word characters and replace them
        $("#renamegroupname").val(validname);

        // submit the form to add a new field to a specific group
        $("#formaction").val("renamegroup");
        $("#theform").submit();
    }

    // just hide the new field DIV
    var CancelRenameGroup = function(btnObj) {
        // hide the field details DIV
        $('#renamegroupdetail').css('visibility', 'hidden');
        $('#renamegroupdetail').css('display', 'none');
        // reset the rename group values to a default
        $('#renameoldgroupname').val("");
        $('#renamegroupname').val("");
    };

    /****************************************************/
    /************ Field functions ***********************/
    /****************************************************/

    // display the 'new field' DIV
    var AddField = function(btnObj) {
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
        if (confirm("<?php xl('WARNING','e','',' - ') . xl('This action cannot be undone.','e','','\n') . xl('Are you sure you wish to delete the selected fields?','e'); ?>")) {
            var delim = "";
            $(".selectfield").each(function(i) {
                // build a list of selected field names to be moved
                if ($(this).attr("checked") == true) {
                    var parts = this.id.split("~");
                    var currval = $("#selectedfields").val();
                    $("#selectedfields").val(currval+delim+parts[1]);
                    delim = " ";
                }
            });
            // submit the form to delete the field(s)
            $("#formaction").val("deletefields");
            $("#theform").submit();
        }
    };
    
    // save the new field to the form
    var SaveNewField = function(btnObj) {
        // check the new field values for correct formatting
    
        // seq must be numeric and less than 999
        if (! IsNumeric($("#newseq").val(), 0, 999)) {
            alert("<?php xl('Order must be a number between 1 and 999','e'); ?>");
            return false;
        }
        // length must be numeric and less than 999
        if (! IsNumeric($("#newlengthWidth").val(), 0, 999)) {
            alert("<?php xl('Size must be a number between 1 and 999','e'); ?>");
            return false;
        }
        // titlecols must be numeric and less than 100
        if (! IsNumeric($("#newtitlecols").val(), 0, 999)) {
            alert("<?php xl('LabelCols must be a number between 1 and 999','e'); ?>");
            return false;
        }
        // datacols must be numeric and less than 100
        if (! IsNumeric($("#newdatacols").val(), 0, 999)) {
            alert("<?php xl('DataCols must be a number between 1 and 999','e'); ?>");
            return false;
        }
        // some fields cannot be blank
        if ($("#newtitle").val() == "") {
            alert("<?php xl('Label cannot be blank','e'); ?>");
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
        $("#theform").submit();
    };
    
    // just hide the new field DIV
    var CancelNewField = function(btnObj) {
        // hide the field details DIV
        $('#fielddetail').css('visibility', 'hidden');
        $('#fielddetail').css('display', 'none');
        // reset the new field values to a default
        ResetNewFieldValues();
    };

    // show the popup choice of lists
    var ShowLists = function(btnObj) {
        window.open("./show_lists_popup.php", "lists", "width=300,height=500,scrollbars=yes");
        selectedfield = btnObj;
    };
    
    // show the popup choice of groups
    var ShowGroups = function(btnObj) {
        window.open("./show_groups_popup.php?layout_id=<?php echo $layout_id;?>", "groups", "width=300,height=300,scrollbars=yes");
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

    // Initialize the list item selectors in skip conditions.
    var f = document.forms[0];
    for (var lino = 1; f['fld[' + lino + '][id]']; ++lino) {
      for (var seq = 0; f['fld[' + lino + '][condition_itemid][' + seq + ']']; ++seq) {
        setListItemOptions(lino, seq, true);
      }
    }

});

function NationNotesContext(lineitem,val){
  if(val==34){
    document.getElementById("fld["+lineitem+"][contextName]").style.display='';
    document.getElementById("fld["+lineitem+"][list_id]").style.display='none';
    document.getElementById("fld["+lineitem+"][list_id]").value='';
  }
  else{
    document.getElementById("fld["+lineitem+"][list_id]").style.display='';
    document.getElementById("fld["+lineitem+"][contextName]").style.display='none';
    document.getElementById("fld["+lineitem+"][list_id]").value='';
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
<?php if (substr($layout_id,0,3) == 'LBF') { ?>
  fieldselectfield = elem;
  var srcval = elemFromPart('source').value;
  // If the field ID is for the local form, allow direct entry.
  if (srcval == 'F') return;
  // Otherwise pop up the selection window.
  window.open('./field_id_popup.php?source=' + srcval, 'fields',
    'width=600,height=600,scrollbars=yes');
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
  elemFromPart('listid'      ).value = list_id;
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
        if ($(this).attr("checked") == true) {
            var parts = this.id.split("~");
            var currval = $("#selectedfields").val();
            $("#selectedfields").val(currval+delim+parts[1]);
            delim = " ";
        }
    });
    $("#formaction").val("movefields");
    $("#theform").submit();
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
