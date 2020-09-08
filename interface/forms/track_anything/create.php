<?php

/**
 * Encounter form to track any clinical parameter.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Joe Slam <trackanything@produnis.de>
 * @copyright Copyright (c) 2014 Joe Slam <trackanything@produnis.de>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Acl\AclMain;

use OpenEMR\Core\Header;

formHeader("Form: Track anything");


?>
<head>
    <?php Header::setupHeader('track-anything'); ?>
<?php
echo "<div id='ta_type'>";


// **** DB ACTION ******
$dbaction = isset($_POST['dbaction']) ? trim($_POST['dbaction']) : '';

// save new item to a track
//-----------------------------
if ($dbaction == 'add') {
        $the_name   = $_POST['name'];
        $the_descr  = $_POST['description'];
        $the_pos    = $_POST['position'];
        $the_parent = $_POST['parentid'];
        $the_type   = $_POST['the_type'];

    if ($the_name != null) {
        $insertspell  = "INSERT INTO form_track_anything_type ";
        $insertspell .= "(name, description, position, parent, active) VALUES (?,?,?,?,?)";
        $save_into_db = sqlInsert($insertspell, array($the_name, $the_descr, $the_pos, $the_parent,1));
    } else {
        if ($the_type == 'add') {
            echo "<br /><span class='failure'>\n";
            echo xlt('Adding item to track failed') . ". ";
            echo xlt("Please enter at least the item's name") . ".";
            echo "</span><br /><br />\n";
        }

        if ($the_type == 'create') {
            echo "<br /><span class='failure'>\n";
            echo xlt('Creating new track failed') . ". ";
            echo xlt("Please enter at least the track's name") . ".";
            echo "</span><br /><br />\n";
        }
    }
}

// end save new item to track -----------------------------


// edit existing track/items
//-----------------------------
if ($dbaction == 'edit') {
        $the_name   = $_POST['name'];
        $the_descr  = $_POST['description'];
        $the_pos    = $_POST['position'];
        $the_item = $_POST['itemid'];

    if ($the_name != null) {
        $updatespell  = "UPDATE form_track_anything_type ";
        $updatespell .= "SET name = ?, description = ?, position = ? ";
        $updatespell .= "WHERE track_anything_type_id = ? ";
        sqlStatement($updatespell, array($the_name, $the_descr, $the_pos, $the_item));
    } else {
        echo "<br /><span class='failure'>\n";
        echo xlt('Editing failed') . ". ";
        echo xlt("Field 'name' cannot be NULL") . ".";
        echo "</span><br /><br />\n";
    }
}

// end edit -----------------------------

//-----------------------------
if ($dbaction == 'delete' && AclMain::aclCheckCore('admin', 'super')) {
        $the_item   = $_POST['itemid'];
        $deletespell  = "DELETE FROM form_track_anything_type ";
        $deletespell .= "WHERE track_anything_type_id = ? ";
        sqlStatement($deletespell, array($the_item));
}

// end edit -----------------------------

// *** END DB ACTIONS


// Create a new track
$create_track = isset($_POST['create_track']) ? trim($_POST['create_track']) : '';
if ($create_track) {
    echo "<table class='create'><tr><td>\n";
    echo "<b>" . xlt('Create a new track')  . " </b><br />&nbsp;";
    echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>\n";
    echo "<table>\n";
    echo "<tr>\n";
    echo "<th class='add'>" . xlt('Name') . "</th>\n";
    echo "<td class='add'><input type='text' size='12' name='name'></td>\n";
    echo "</tr><tr>\n";
    echo "<th class='add'>" . xlt('Description') . "</th>\n";
    echo "<td class='add'><input type='text' size='12' name='description'></td>\n";
    echo "</tr><tr>\n";
    echo "<th class='edit'>" . xlt('Position') . "</th>\n";
    echo "<td class='edit'><input type='text' size='12' name='position'></td>\n";
    echo "</tr>\n";
    echo "</table>\n";
    echo "<input type='hidden' name='parentid' value='0'>\n";
    echo "<input type='hidden' name='the_type' value='create'>\n";
    echo "<input type='hidden' name='dbaction' value='add'>\n";
    echo "<input type='submit' name='addsave' value='" . xla('Save') . "'>\n";
    echo "<input type='button' name='stop' value='" . xla('Back') . "' ";
    ?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/create.php'"<?php
    echo " />\n";
    echo "</form>\n";
    echo "</td></tr></table>\n";
} // end create new track

// user clicked some buttons...
$the_item = isset($_POST['typeid']) ? trim($_POST['typeid']) : '';
if ($the_item) {
    $add        = $_POST['add'];
    $edit       = $_POST['edit'];
    $delete     = $_POST['delete'];
    $deactivate = $_POST['deact'];
    $activate   = $_POST['act'];

    // add a new item to track
    //------------------------
    if ($add) {
        // add item to parent
        echo "<table class='add'><tr><td>";
        $spell  = "SELECT name FROM form_track_anything_type ";
        $spell .= "WHERE track_anything_type_id = ?";
        $myrow = sqlQuery($spell, array($the_item));
        echo "<br />&nbsp;&nbsp;";
        echo xlt('Add item to track')  . " <b>" . text($myrow['name']) . "</b><br />&nbsp;\n";
        echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>\n";
        echo "<table>\n";
        echo "<tr>\n";
        echo "<th class='add'>" . xlt('Name') . "</th>\n";
        echo "<td class='add'><input type='text' size='12' name='name'></td>\n";
        echo "</tr><tr>\n";
        echo "<th class='add'>" . xlt('Description') . "</th>\n";
        echo "<td class='add'><input type='text' size='12' name='description'></td>\n";
        echo "</tr><tr>\n";
        echo "<th class='edit'>" . xlt('Position') . "</th>\n";
        echo "<td class='edit'><input type='text' size='12' name='position'></td>\n";

        echo "</tr>\n";
        echo "</table>\n";
        echo "<input type='hidden' name='parentid' value='" . attr($the_item) . "'>\n";
        echo "<input type='hidden' name='dbaction' value='add'>\n";
        echo "<input type='hidden' name='the_type' value='add'>\n";
        echo "<input type='submit' name='addsave' value='" . xla('Save') . "'>\n";
        echo "<input type='button' name='stop' value='" . xla('Back') . "' ";
        ?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/create.php'"<?php
        echo " />\n";
        echo "</form>\n";
        echo "</td></tr></table>\n";
    }// end add item------------------


    if ($edit) {
        echo "<table class='edit'><tr><td>";
        $spell  = "SELECT name, description, position FROM form_track_anything_type ";
        $spell .= "WHERE track_anything_type_id = ?";
        $myrow = sqlQuery($spell, array($the_item));
        $the_name   = $myrow['name'];
        $the_descr  = $myrow['description'];
        $the_pos    = $myrow['position'];
        echo "<br />&nbsp;&nbsp;";
        echo xlt('Edit')  . " <b>" . text($the_name) . "</b><br />&nbsp;";
        echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>\n";
        echo "<table>\n";
        echo "<tr>\n";
        echo "<th class='edit'>" . xlt('Name') . "</th>\n";
        echo "<td class='edit'><input type='text' size='12' name='name' value='" . attr($the_name) . "'></td>\n";
        echo "</tr><tr>\n";
        echo "<th class='edit'>" . xlt('Description') . "</th>\n";
        echo "<td class='edit'><input type='text' size='12' name='description' value='" . attr($the_descr) . "'></td>\n";
        echo "</tr><tr>\n";
        echo "<th class='edit'>" . xlt('Position') . "</th>\n";
        echo "<td class='edit'><input type='text' size='12' name='position' value='" . attr($the_pos) . "'></td>\n";
        echo "</tr>\n";
        echo "</table>\n";
        echo "<input type='hidden' name='itemid' value='" . attr($the_item) . "'>\n";
        echo "<input type='hidden' name='dbaction' value='edit'>\n";
        echo "<input type='submit' name='addsave' value='" . xla('Save') . "'>\n";
        echo "<input type='button' name='stop' value='" . xla('Back') . "' ";
        ?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/create.php'"<?php
        echo " />\n";
        echo "</form>\n";
        echo "</td></tr></table>\n";
    }

    if ($delete) {
        echo "<table class='del'><tr><td>\n";
        $spell  = "SELECT name FROM form_track_anything_type ";
        $spell .= "WHERE track_anything_type_id = ?";
        $myrow = sqlQuery($spell, array($the_item));
        $the_name   = $myrow['name'];

        echo "<br />&nbsp;&nbsp;<span class='failure'>\n";
        echo xlt('Are you sure you want to delete') . " <b>" . text($the_name) . "</b>?</span><br />\n";
        echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>\n";
        echo "<input type='hidden' name='itemid' value='" . attr($the_item) . "'>\n";
        echo "<input type='hidden' name='dbaction' value='delete'>\n";
        echo "&nbsp;&nbsp;<input type='submit' class='delete_button' name='addsave' value='" . xla('Delete') . "'>\n";
        echo "&nbsp;&nbsp;<input type='button' class='nodelete_button' name='stop' value='" . xla('Back') . "' ";
        ?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/create.php'"<?php
        echo " />\n";
        echo "</form><br /><br />\n";
        echo "</td></tr></table>\n";
    }


    if ($deactivate) {
    // deactive the item/track
        $updatespell  = "UPDATE form_track_anything_type ";
        $updatespell .= "SET active = '0' ";
        $updatespell .= "WHERE track_anything_type_id = ? ";
        sqlStatement($updatespell, array($the_item));
    }

    if ($activate) {
    // activate the item/track
        $updatespell  = "UPDATE form_track_anything_type ";
        $updatespell .= "SET active = '1' ";
        $updatespell .= "WHERE track_anything_type_id = ? ";
        sqlStatement($updatespell, array($the_item));
    }
} //end user clicked button



// ================================================================0
// Here comes the page...

echo "<br />&nbsp;&nbsp;<b>\n";
echo xlt('Create and modify tracks');
echo "</b><br /><br />\n";
echo "<table width='100%'>\n";
 echo "<tr>\n";
  echo "<th width='30%'>" . xlt('Name') . "</th>\n";
  echo "<th width='45%'>" . xlt('Description') . "</th>\n";
  echo "<th width='5%'>" . xlt('Pos{{Abbreviation for Position}}') . ".</th>\n";
  echo "<th width='20%'>&nbsp; </th>\n";
 echo "</tr>\n";
// get all track-setups
$spell  = "SELECT * FROM form_track_anything_type ";
$spell .= "WHERE parent = 0 ";
$spell .= "ORDER BY position ASC, active DESC, name ASC";
$result = sqlStatement($spell);
while ($myrow = sqlFetchArray($result)) {
    $type_id        = $myrow['track_anything_type_id'];
    $type_name      = $myrow['name'];
    $type_pos       = $myrow['position'];
    $type_descr     = $myrow['description'];
    $type_active    = $myrow['active'];
    echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>\n";

    echo "<tr>\n";
    if ($type_active == '1') {
        echo "<td class='parent'>&nbsp;&nbsp;" . text($type_name) . "</td>\n";
        echo "<td class='parent'>&nbsp;&nbsp;" . text($type_descr) . "</td>\n";
        echo "<td class='parent'>&nbsp;&nbsp;" . text($type_pos) . "</td>\n";
    } elseif ($type_active == '0') {
        echo "<td class='deactive'>&nbsp;&nbsp;" . text($type_name) . "</td>\n";
        echo "<td class='deactive'>&nbsp;&nbsp;" . text($type_descr) . "</td>\n";
        echo "<td class='deactive'>&nbsp;&nbsp;" . text($type_pos) . "</td>\n";
    }

    echo "<td class='op'>";
    echo "<input type='submit' class='ta_button' name='add' value='" . xla('Add') . "'>\n";
    echo "<input type='submit' class='ta_button' name='edit' value='" . xla('Edit') . "'>\n";
    if ($type_active == '1') {
        echo "<input type='submit' class='ta_button' name='deact' value='" . xla('Disable') . "'>\n";
    } elseif ($type_active == '0') {
        echo "<input type='submit' class='ta_button' name='act' value='" . xla('Enable') . "'>\n";
    }

    if (AclMain::aclCheckCore('admin', 'super')) {
        echo "<input type='submit' class='delete_button' name='delete' value='" . xla('Delete') . "'>\n";
    }

    echo "<input type='hidden' name='typeid' value='" . attr($type_id) . "'>";
    echo "</td></tr>\n";
    echo "</form>\n";
    $spell2  = "SELECT * FROM form_track_anything_type ";
    $spell2 .= "WHERE parent = ? ";
    $spell2 .= "ORDER BY position ASC, active DESC, name ASC";
    $result2 = sqlStatement($spell2, array($type_id));
    while ($myrow2 = sqlFetchArray($result2)) {
        $item_id        = $myrow2['track_anything_type_id'];
        $item_name      = $myrow2['name'];
        $item_pos       = $myrow2['position'];
        $item_descr     = $myrow2['description'];
        $item_active    = $myrow2['active'];
        echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>\n";
        echo "<tr>\n";
        if ($item_active == '1') {
            echo "<td class='child'>&nbsp;&nbsp;&nbsp;&nbsp; | " . text($item_name) . "</td>\n";
            echo "<td class='child'>&nbsp;&nbsp;&nbsp;&nbsp; | " . text($item_descr) . "</td>\n";
            echo "<td class='child'>&nbsp;&nbsp;&nbsp;&nbsp; | " . text($item_pos) . "</td>\n";
        } elseif ($item_active == '0') {
            echo "<td class='deactive'>&nbsp;&nbsp;&nbsp;&nbsp; | " . text($item_name) . "</td>\n";
            echo "<td class='deactive'>&nbsp;&nbsp;&nbsp;&nbsp; | " . text($item_descr) . "</td>\n";
            echo "<td class='deactive'>&nbsp;&nbsp;&nbsp;&nbsp; | " . text($item_pos) . "</td>\n";
        }

        echo "<td class='op'>";
        echo "<input type='submit' class='ta_button' name='edit' value='" . xla('Edit') . "'>\n";
        if ($item_active == '1') {
            echo "<input type='submit' class='ta_button' name='deact' value='" . xla('Disable') . "'>\n";
        } elseif ($item_active == '0') {
            echo "<input type='submit' class='ta_button' name='act' value='" . xla('Enable') . "'>\n";
        }

        if (AclMain::aclCheckCore('admin', 'super')) {
            echo "<input type='submit' class='delete_button' name='delete' value='" . xla('Delete') . "'>\n";
        }

        echo "<input type='hidden' name='typeid' value='" . attr($item_id) . "'>\n";
        echo "</td></tr>\n";
        echo "</form>\n";
    } // end while $myrow2
    echo "</tr>\n";
} // end while $myrow
echo "</table>\n";

echo "<p align='center'>\n";
echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>\n";
echo "<input type='submit' name='create_track' value='" . xla('Create new Track') . "' >\n";
echo "<input type='button' name='stop' value='" . xla('Back') . "' ";
// if in an encounter, go back to "select track"
if ($encounter) {
    ?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/new.php'"<?php
// if not in an encounter, go back to "demographics"
} elseif (!$encounter and $pid) {
    ?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/patient_file/summary/demographics.php'"<?php
} elseif (!$encounter and !$pid) {
    ?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/new/new.php'"<?php
}

echo " />\n";
echo "</p>\n";
echo "</form>\n";
echo "</div>\n";
?>
