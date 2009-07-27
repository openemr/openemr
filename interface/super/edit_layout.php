<?php
// Copyright (C) 2007-2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/log.inc");
require_once("$srcdir/formdata.inc.php");

$layouts = array(
  'DEM' => xl('Demographics'),
  'HIS' => xl('History'),
  'REF' => xl('Referrals'),
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
$datatypes = array("1"=>xl("list box"), 
                    "2"=>xl("textbox"),
                    "3"=>xl("textarea"),
                    "4"=>xl("text-date"),
                    "10"=>xl("Providers"),
                    "11"=>xl("Providers NPI"),
                    "12"=>xl("Pharmacies"),
                    "13"=>xl("Squads"),
                    "14"=>xl("Organizations"),
                    "15"=>xl("Billing codes"),
                    "21"=>xl("checkbox list"),
                    "22"=>xl("textbox list"),
                    "23"=>xl("Exam results"),
                    "24"=>xl("Patient allergies"),
                    "25"=>xl("checkbox w/ text"),
                    "26"=>xl("list box w/ add")
                    );

// Check authorization.
$thisauth = acl_check('admin', 'super');
if (!$thisauth) die(xl('Not authorized'));

// the layout ID defaults to DEM (demographics)
$layout_id = empty($_REQUEST['layout_id']) ? 'DEM' : $_REQUEST['layout_id'];

// Handle the Form actions

if ($_POST['formaction'] == "save" && $layout_id) {
    // If we are saving, then save.
    $fld = $_POST['fld'];
    for ($lino = 1; isset($fld[$lino]['id']); ++$lino) {
        $iter = $fld[$lino];
        $field_id = formTrim($iter['id']);
        if ($field_id) {
            sqlStatement("UPDATE layout_options SET " .
                "title = '"         . formTrim($iter['title'])     . "', " .
                "group_name = '"    . formTrim($iter['group'])     . "', " .
                "seq = '"           . formTrim($iter['seq'])       . "', " .
                "uor = '"           . formTrim($iter['uor'])       . "', " .
                "fld_length = '"    . formTrim($iter['length'])    . "', " .
                "titlecols = '"     . formTrim($iter['titlecols']) . "', " .
                "datacols = '"      . formTrim($iter['datacols'])  . "', " .
                "data_type= '"      . formTrim($iter['data_type']) . "', " .
                "list_id= '"        . formTrim($iter['list_id'])   . "', " .
                "default_value = '" . formTrim($iter['default'])   . "', " .
                "description = '"   . formTrim($iter['desc'])      . "' " .
                "WHERE form_id = '$layout_id' AND field_id = '$field_id'");
        }
    }
}

else if ($_POST['formaction'] == "addfield" && $layout_id) {
    // Add a new field to a specific group
    sqlStatement("INSERT INTO layout_options (" .
      " form_id, field_id, title, group_name, seq, uor, fld_length" .
      ", titlecols, datacols, data_type, default_value, description" .
      ", max_length " .
      ") VALUES ( " .
      "'"  . formTrim($_POST['layout_id']      ) . "'" .
      ",'" . formTrim($_POST['newid']          ) . "'" .
      ",'" . formTrim($_POST['newtitle']       ) . "'" .
      ",'" . formTrim($_POST['newfieldgroupid']) . "'" .
      ",'" . formTrim($_POST['newseq']         ) . "'" .
      ",'" . formTrim($_POST['newuor']         ) . "'" .
      ",'" . formTrim($_POST['newlength']      ) . "'" .
      ",'" . formTrim($_POST['newtitlecols']   ) . "'" .
      ",'" . formTrim($_POST['newdatacols']    ) . "'" .
      ",'" . formTrim($_POST['newdatatype']    ) . "'" .
      ",'" . formTrim($_POST['newdefault']     ) . "'" .
      ",'" . formTrim($_POST['newdesc']        ) . "'" .
      ",'" . formTrim($_POST['newlength']      ) . "'" . // maxlength = length
      " )");

    if (substr($layout_id,0,3) != 'LBF') {
      // Add the field to the table too (this is critical)
      if ($layout_id == "DEM") { $tablename = "patient_data"; }
      else if ($layout_id == "HIS") { $tablename = "history_data"; }
      else if ($layout_id == "REF") { $tablename = "transactions"; }
      else if ($layout_id == "SRH") { $tablename = "lists_ippf_srh"; }
      else if ($layout_id == "CON") { $tablename = "lists_ippf_con"; }
      else if ($layout_id == "GCA") { $tablename = "lists_ippf_gcac"; }
      sqlStatement("ALTER TABLE `" . $tablename . "` ADD ".
                      "`" . formTrim($_POST['newid']) . "`" .
                      " VARCHAR( 255 )");
      newEvent("alter_table", $_SESSION['authUser'], $_SESSION['authProvider'],
        $tablename . " ADD " . formTrim($_POST['newid']));
    }
}

else if ($_POST['formaction'] == "deletefield" && $layout_id) {
    // Delete a field from a specific group
    sqlStatement("DELETE FROM layout_options WHERE ".
                " form_id = '".$_POST['layout_id']."' ".
                " AND field_id = '".$_POST['deletefieldid']."'".
                " AND group_name = '".$_POST['deletefieldgroup']."'"
                );

    if (substr($layout_id,0,3) != 'LBF') {
      // drop the field from the table too (this is critical) 
      if ($layout_id == "DEM") { $tablename = "patient_data"; }
      else if ($layout_id == "HIS") { $tablename = "history_data"; }
      else if ($layout_id == "REF") { $tablename = "transactions"; }
      else if ($layout_id == "SRH") { $tablename = "lists_ippf_srh"; }
      else if ($layout_id == "CON") { $tablename = "lists_ippf_con"; }
      else if ($layout_id == "GCA") { $tablename = "lists_ippf_gcac"; }
      sqlStatement("ALTER TABLE `".$tablename."` DROP `".$_POST['deletefieldid']."`");
      newEvent("alter_table", $_SESSION['authUser'], $_SESSION['authProvider'], $tablename." DROP ".trim($_POST['deletefieldid']));
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
    $maxnum = 0;
    while ($result = sqlFetchArray($results)) {
        // split the number from the group name
        $parts = preg_split("/([A-Z]|[a-z])/", $result['gname']);
        if ($parts[0] >= $maxnum) { $maxnum = $parts[0] + 1; }
    }

    // add a new group to the layout, with the defined field
    sqlStatement("INSERT INTO layout_options (" .
      " form_id, field_id, title, group_name, seq, uor, fld_length" .
      ", titlecols, datacols, data_type, default_value, description" .
      ", max_length " .
      ") VALUES ( " .
      "'"  . formTrim($_POST['layout_id']      ) . "'" .
      ",'" . formTrim($_POST['gnewid']          ) . "'" .
      ",'" . formTrim($_POST['gnewtitle']       ) . "'" .
      ",'" . formTrim($maxnum . $_POST['newgroupname']) . "'" .
      ",'" . formTrim($_POST['gnewseq']         ) . "'" .
      ",'" . formTrim($_POST['gnewuor']         ) . "'" .
      ",'" . formTrim($_POST['gnewlength']      ) . "'" .
      ",'" . formTrim($_POST['gnewtitlecols']   ) . "'" .
      ",'" . formTrim($_POST['gnewdatacols']    ) . "'" .
      ",'" . formTrim($_POST['gnewdatatype']    ) . "'" .
      ",'" . formTrim($_POST['gnewdefault']     ) . "'" .
      ",'" . formTrim($_POST['gnewdesc']        ) . "'" .
      ",'" . formTrim($_POST['gnewlength']      ) . "'" . // maxlength = length
      " )");

    if (substr($layout_id,0,3) != 'LBF') {
      // Add the field to the table too (this is critical)
      if ($layout_id == "DEM") { $tablename = "patient_data"; }
      else if ($layout_id == "HIS") { $tablename = "history_data"; }
      else if ($layout_id == "REF") { $tablename = "transactions"; }
      else if ($layout_id == "SRH") { $tablename = "lists_ippf_srh"; }
      else if ($layout_id == "CON") { $tablename = "lists_ippf_con"; }
      else if ($layout_id == "GCA") { $tablename = "lists_ippf_gcac"; }
      sqlStatement("ALTER TABLE `" . $tablename . "` ADD ".
                      "`" . formTrim($_POST['gnewid']) . "`" .
                      " VARCHAR( 255 )");
      newEvent("alter_table", $_SESSION['authUser'], $_SESSION['authProvider'],
        $tablename . " ADD " . formTrim($_POST['gnewid']));
    }
}

else if ($_POST['formaction'] == "deletegroup" && $layout_id) {
    // drop the fields from the related table (this is critical)
    if (substr($layout_id,0,3) != 'LBF') {
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
            newEvent("alter_table", $_SESSION['authUser'], $_SESSION['authProvider'], $tablename." DROP ".trim($row['field_id']));
        }
    }

    // Delete an entire group from the form
    sqlStatement("DELETE FROM layout_options WHERE ".
                " form_id = '".$_POST['layout_id']."' ".
                " AND group_name = '".$_POST['deletegroupname']."'"
                );
    //newEvent("alter_table", $_SESSION['authUser'], $_SESSION['authProvider'], $tablename." DROP ".trim($row['field_id']));
}

else if ($_POST['formaction'] == "movegroup" && $layout_id) {
    
    // split the numeric order out of the group name
    $parts = preg_split("/(^\d)/", $_POST['movegroupname'], -1, PREG_SPLIT_DELIM_CAPTURE);
    $currpos = $newpos = $parts[1];
    $groupname = $parts[2];

    // inc/dec the order number
    if ($_POST['movedirection'] == 'up') {
        $newpos--;
        if ($newpos < 0) { $newpos = 0; }
    }
    else if ($_POST['movedirection'] == 'down') {
        $newpos++;
    }
    
    // if we can't determine a position, then assign it a zero
    if ($newpos == "") $newpos = "0";

    // update the database rows 
    sqlStatement("UPDATE layout_options SET ".
                "group_name='".$newpos.$groupname."'".
                " WHERE ".
                "group_name='".$currpos.$groupname."'"
                );
}

else if ($_POST['formaction']=="renamegroup" && $layout_id) {
    
    // split the numeric order out of the group name
    $parts = preg_split("/(^\d)/", $_POST['renameoldgroupname'], -1, PREG_SPLIT_DELIM_CAPTURE);
    $currpos = $parts[1];

    // if we can't determine a position, then assign it a zero
    if ($currpos == "") $currpos = "0";

    // update the database rows 
    sqlStatement("UPDATE layout_options SET ".
                "group_name='".$currpos.$_POST['renamegroupname']."'".
                " WHERE ".
                "group_name='".$_POST['renameoldgroupname']."'"
                );
}


// Get the selected form's elements.
if ($layout_id) {
  $res = sqlStatement("SELECT * FROM layout_options WHERE " .
                        "form_id = '$layout_id' ORDER BY group_name, seq");
}


// global counter for field numbers
$fld_line_no = 0;

// Write one option line to the form.
//
function writeFieldLine($linedata) {
    global $fld_line_no;
    ++$fld_line_no;
    $checked = $linedata['default_value'] ? " checked" : "";
  
    //echo " <tr bgcolor='$bgcolor'>\n";
    echo " <tr id='fld[$fld_line_no]' class='".($fld_line_no % 2 ? 'even' : 'odd')."'>\n";
  
    echo "  <td align='center' class='optcell' nowrap>";
    // tuck the group_name INPUT in here
    echo "<input type='hidden' name='fld[$fld_line_no][group]' value='" .
         htmlspecialchars($linedata['group_name'], ENT_QUOTES) . "' class='optin' />";
    echo "<input type='button' class='deletefield' ".
            "name='".$linedata['group_name']."~".$linedata['field_id']."' ".
            "id='".$linedata['group_name']."~".$linedata['field_id']."' value='X' title='Delete field'>";
    echo "<input type='text' name='fld[$fld_line_no][seq]' id='fld[$fld_line_no][seq]' value='" .
         htmlspecialchars($linedata['seq'], ENT_QUOTES) . "' size='2' maxlength='3' class='optin' />";
    echo "</td>\n";
  
    echo "  <td align='left' class='optcell'>";
    echo "<input type='text' name='fld[$fld_line_no][id]' value='" .
         htmlspecialchars($linedata['field_id'], ENT_QUOTES) . "' size='15' maxlength='63' class='optin noselect' />";
    /*
    echo "<input type='hidden' name='fld[$fld_line_no][id]' value='" .
         htmlspecialchars($linedata['field_id'], ENT_QUOTES) . "' />";
    echo htmlspecialchars($linedata['field_id'], ENT_QUOTES);
    */
    echo "</td>\n";
  
    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' id='fld[$fld_line_no][title]' name='fld[$fld_line_no][title]' value='" .
         htmlspecialchars($linedata['title'], ENT_QUOTES) . "' size='15' maxlength='63' class='optin' />";
    echo "</td>\n";

    // if not english and set to translate layout labels, then show the translation
    if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
        echo "<td align='center' class='translation'>" . htmlspecialchars(xl($linedata['title']), ENT_QUOTES) . "</td>\n";
    }
	
    echo "  <td align='center' class='optcell'>";
    echo "<select name='fld[$fld_line_no][uor]' class='optin'>";
    foreach (array(0 =>xl('Unused'), 1 =>xl('Optional'), 2 =>xl('Required')) as $key => $value) {
        echo "<option value='$key'";
        if ($key == $linedata['uor']) echo " selected";
        echo ">$value</option>\n";
    }
    echo "</select>";
    echo "</td>\n";
  
    echo "  <td align='center' class='optcell'>";
    echo "<select name='fld[$fld_line_no][data_type]' id='fld[$fld_line_no][data_type]'>";
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

    echo "  <td align='center' class='optcell'>";
    if ($linedata['data_type'] == 2 || $linedata['data_type'] == 3)
    {
        // textbox or textarea
        echo "<input type='hidden' name='fld[$fld_line_no][list_id]' value=''>";
        echo "<input type='text' name='fld[$fld_line_no][length]' value='" .
            htmlspecialchars($linedata['fld_length'], ENT_QUOTES) . "' size='1' maxlength='10' class='optin' />";
    }
    else if ($linedata['data_type'] == 4) {
        // text-date
        echo "<input type='hidden' name='fld[$fld_line_no][length]' value=''>";
        echo "<span>(date)</span>";
    }
    else if ($linedata['data_type'] ==  1 || $linedata['data_type'] == 21 ||
             $linedata['data_type'] == 22 || $linedata['data_type'] == 23 ||
             $linedata['data_type'] == 25 || $linedata['data_type'] == 26)
    {
        // select, checkbox, textbox list, or checkbox list w/ text
        echo "<input type='hidden' name='fld[$fld_line_no][length]' value=''>";
        echo "<input type='text' name='fld[$fld_line_no][list_id]' value='" .
            htmlspecialchars($linedata['list_id'], ENT_QUOTES) . "'".
            "size='6' maxlength='30' class='optin listid' style='cursor: pointer'".
            "title='Choose list' />";
    }
    else {
        // all other data_types
        echo "<input type='hidden' name='fld[$fld_line_no][length]' value=''>";
        if ($linedata['list_id'] != "") {
            echo "<span title='".$linedata['list_id']."'>(list)</span>";
        }
    }
    echo "</td>\n";
  
    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='fld[$fld_line_no][titlecols]' value='" .
         htmlspecialchars($linedata['titlecols'], ENT_QUOTES) . "' size='3' maxlength='10' class='optin' />";
    echo "</td>\n";
  
    echo "  <td align='center' class='optcell'>";
    echo "<input type='text' name='fld[$fld_line_no][datacols]' value='" .
         htmlspecialchars($linedata['datacols'], ENT_QUOTES) . "' size='3' maxlength='10' class='optin' />";
    echo "</td>\n";
 
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
    
    echo " </tr>\n";
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
    margin: 10px;
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
.moveup {
    cursor: pointer;
}
.movedown {
    cursor: pointer;
}
.help {
    cursor: help;
}
.layouts_title {
    font-size: 110%;
}
.translation {
    color: green;
    font-size:10pt;
 }
</style>


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

<p><b><?php xl('Edit layout','e'); ?>:</b>&nbsp;
<select name='layout_id' id='layout_id'>
<?php
foreach ($layouts as $key => $value) {
  echo "<option value='$key'";
  if ($key == $layout_id) echo " selected";
  echo ">$value</option>\n";
}
?>
</select></p>

<div>
<input type='button' class='addgroup' id='addgroup' value=<?php xl('Add Group','e','\'','\''); ?>/>
</div>

<?php 
$prevgroup = "!@#asdf1234"; // an unlikely group name
$firstgroup = true; // flag indicates it's the first group to be displayed
while ($row = sqlFetchArray($res)) {
?>

<?php 
if ($row['group_name'] != $prevgroup) {
    if ($firstgroup == false) { echo "</tbody></table></div>\n"; }
    echo "<div id='".$row['group_name']."' class='group'>";
    echo "<div class='text bold layouts_title' style='position:relative; background-color: #eef'>";
    echo preg_replace("/^\d+/", "", $row['group_name']);
    echo "&nbsp; ";
    // if not english and set to translate layout labels, then show the translation of group name
    if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
	echo "<span class='translation'>>>&nbsp; " . xl(preg_replace("/^\d+/", "", $row['group_name'])) . "</span>";
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
  <th><?php xl('ID','e'); ?> <span class="help" title=<?php xl('A unique value to identify this field, not visible to the user','e','\'','\''); ?> >(?)</span></th>
  <th><?php xl('Label','e'); ?> <span class="help" title=<?php xl('The label that appears to the user on the form','e','\'','\''); ?> >(?)</span></th>
  <?php // if not english and showing layout label translations, then show translation header for title
  if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
   echo "<th>" . xl('Translation')."<span class='help' title='" . xl('The translated label that will appear on the form in current language') . "'> (?)</span></th>";	
  } ?>		  
  <th><?php xl('UOR','e'); ?></th>
  <th><?php xl('Data Type','e'); ?></th>
  <th><?php xl('Size/List','e'); ?></th>
  <th><?php xl('Label Cols','e'); ?></th>
  <th><?php xl('Data Cols','e'); ?></th>
  <th><?php xl('Default Value','e'); ?></th>
  <th><?php xl('Description','e'); ?></th>
  <?php // if not english and showing layout label translations, then show translation header for description
  if ($GLOBALS['translate_layout'] && $_SESSION['language_choice'] > 1) {
   echo "<th>" . xl('Translation')."<span class='help' title='" . xl('The translation of description in current language')."'> (?)</span></th>";
  } ?>
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

<input type='button' name='save' id='save' value='<?php xl('Save Changes','e'); ?>' />

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
<?php xl('Group Name','e'); ?>:	<input type="textbox" size="20" maxlength="30" name="newgroupname" id="newgroupname">
<br>
<table style="border-collapse: collapse; margin-top: 5px;">
<thead>
 <tr class='head'>
  <th><?php xl('Order','e'); ?></th>
  <th><?php xl('ID','e'); ?> <span class="help" title=<?php xl('A unique value to identify this field, not visible to the user','e','\'','\''); ?> >(?)</span></th>
  <th><?php xl('Label','e'); ?> <span class="help" title=<?php xl('The label that appears to the user on the form','e','\'','\''); ?> >(?)</span></th>
  <th><?php xl('UOR','e'); ?></th>
  <th><?php xl('Data Type','e'); ?></th>
  <th><?php xl('Size/List','e'); ?></th>
  <th><?php xl('Label Cols','e'); ?></th>
  <th><?php xl('Data Cols','e'); ?></th>
  <th><?php xl('Default Value','e'); ?></th>
  <th><?php xl('Description','e'); ?></th>
 </tr>
</thead>
<tbody>
<tr class='center'>
<td ><input type="textbox" name="gnewseq" id="gnewseq" value="" size="2" maxlength="3"> </td>
<td ><input type="textbox" name="gnewid" id="gnewid" value="" size="10" maxlength="20"> </td>
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
<td><input type="textbox" name="gnewlength" id="gnewlength" value="" size="1" maxlength="3"> </td>
<td><input type="textbox" name="gnewtitlecols" id="gnewtitlecols" value="" size="3" maxlength="3"> </td>
<td><input type="textbox" name="gnewdatacols" id="gnewdatacols" value="" size="3" maxlength="3"> </td>
<td><input type="textbox" name="gnewdefault" id="gnewdefault" value="" size="20" maxlength="63"> </td>
<td><input type="textbox" name="gnewdesc" id="gnewdesc" value="" size="20" maxlength="63"> </td>
</tr>
</tbody>
</table>
<br>
<input type="button" class="savenewgroup" value=<?php xl('Save New Group','e','\'','\''); ?>>
<input type="button" class="cancelnewgroup" value=<?php xl('Cancel','e','\'','\''); ?>>
</div>



<!-- template DIV that appears when user chooses to add a new field to a group -->
<div id="fielddetail" class="fielddetail" style="display: none; visibility: hidden">
<input type="hidden" name="newfieldgroupid" id="newfieldgroupid" value="">
<table style="border-collapse: collapse;">
<thead>
 <tr class='head'>
  <th><?php xl('Order','e'); ?></th>
  <th><?php xl('ID','e'); ?> <span class="help" title=<?php xl('A unique value to identify this field, not visible to the user','e','\'','\''); ?> >(?)</span></th>
  <th><?php xl('Label','e'); ?> <span class="help" title=<?php xl('The label that appears to the user on the form','e','\'','\''); ?> >(?)</span></th>
  <th><?php xl('UOR','e'); ?></th>
  <th><?php xl('Data Type','e'); ?></th>
  <th><?php xl('Size/List','e'); ?></th>
  <th><?php xl('Label Cols','e'); ?></th>
  <th><?php xl('Data Cols','e'); ?></th>
  <th><?php xl('Default Value','e'); ?></th>
  <th><?php xl('Description','e'); ?></th>
 </tr>
</thead>
<tbody>
<tr class='center'>
<td ><input type="textbox" name="newseq" id="newseq" value="" size="2" maxlength="3"> </td>
<td ><input type="textbox" name="newid" id="newid" value="" size="10" maxlength="20"> </td>
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
    echo "<option value='$key'>$value</option>";
}
?>
</select>
</td>
<td><input type="textbox" name="newlength" id="newlength" value="" size="1" maxlength="3"> </td>
<td><input type="textbox" name="newtitlecols" id="newtitlecols" value="" size="3" maxlength="3"> </td>
<td><input type="textbox" name="newdatacols" id="newdatacols" value="" size="3" maxlength="3"> </td>
<td><input type="textbox" name="newdefault" id="newdefault" value="" size="20" maxlength="63"> </td>
<td><input type="textbox" name="newdesc" id="newdesc" value="" size="20" maxlength="63"> </td>
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

var selectedfield;

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
    $(".deletefield").click(function() { DeleteField(this); });
    $(".savenewfield").click(function() { SaveNewField(this); });
    $(".cancelnewfield").click(function() { CancelNewField(this); });
    $("#newtitle").blur(function() { if ($("#newid").val() == "") $("#newid").val($("#newtitle").val()); });
    
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
        $('#groupdetail > #newgropuname').focus();
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
        if (! IsNumeric($("#gnewlength").val(), 0, 999)) {
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
    };
    
    var DeleteField = function(btnObj) {
        var parts = $(btnObj).attr("id").split("~");
        var groupname = parts[0].replace(/^\d+/, "");
        if (confirm("<?php xl('WARNING','e','',' - ') . xl('This action cannot be undone.','e','','\n') . xl('Are you sure you wish to delete the field in','e'); ?> '"+groupname+"' <?php xl('identified as','e'); ?> '"+parts[1]+"'?")) {
            // submit the form to add a new field to a specific group
            $("#formaction").val("deletefield");
            $("#deletefieldgroup").val(parts[0]);
            $("#deletefieldid").val(parts[1]);
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
        if (! IsNumeric($("#newlength").val(), 0, 999)) {
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

});

function SetList(listid) {
    $(selectedfield).val(listid);
}

// set the new-field values to a default state
function ResetNewFieldValues () {
    $("#newseq").val("");
    $("#newid").val("");
    $("#newtitle").val("");
    $("#newuor").val(1);
    $("#newlength").val("");
    $("#newdatatype").val("");
    $("#newlistid").val("");
    $("#newtitlecols").val("");
    $("#newdatacols").val("");
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
