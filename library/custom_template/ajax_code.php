<?php

// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Jacob T Paul <jacob@zhservices.com>
//           Jerry Padgett <sjpadgett@gmail.com>
// +------------------------------------------------------------------------------+


require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;

$templateid = $_REQUEST['templateid'] ?? '';
$Source = $_REQUEST['source'] ?? '';
$list_id = $_REQUEST['list_id'] ?? '';
$item = $_REQUEST['item'] ?? '';
$multi = $_REQUEST['multi'] ?? '';
$content = $_REQUEST['content'] ?? '';

if ($Source == "add_template") {
    $arr = explode("|", $multi);

    for ($i = 0; $i < count($arr) - 1; $i++) {
        $sql = sqlStatement("SELECT * FROM customlists AS cl LEFT OUTER JOIN template_users AS tu ON cl.cl_list_slno=tu.tu_template_id
                        WHERE cl_list_item_long=? AND cl_list_type=3 AND cl_deleted=0 AND cl_list_id=? AND tu.tu_user_id=?", array($templateid, $arr[$i], $_SESSION['authUserID']));
        $cnt = sqlNumRows($sql);
        if ($cnt == 0) {
            $newid = sqlInsert("INSERT INTO customlists (cl_list_id,cl_list_type,cl_list_item_long,cl_creator) VALUES (?,?,?,?)", array($arr[$i], 3, $templateid, $_SESSION['authUserID']));
            sqlStatement("INSERT INTO template_users (tu_user_id,tu_template_id) VALUES (?,?)", array($_SESSION['authUserID'], $newid));
        }
        echo "<select name='template' id='template' onchange='TemplateSentence(this.value)' style='width:180px'>";
        echo "<option value=''>" . xlt('Select category') . "</option>";
        $resTemplates = sqlStatement("SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno WHERE
                                     tu.tu_user_id=? AND c.cl_list_type=3 AND cl_list_id=? AND cl_deleted=0 ORDER BY tu.tu_template_order,
                                     c.cl_list_item_long", array($_SESSION['authUserID'], $list_id));
        while ($rowTemplates = sqlFetchArray($resTemplates)) {
            echo "<option value='" . attr($rowTemplates['cl_list_slno']) . "'>" . text($rowTemplates['cl_list_item_long']) . "</option>";
        }
        echo "</select>";
    }
} elseif ($Source == "save_provider") {
    $arr = explode("|", $multi);
    for ($i = 0; $i < count($arr) - 1; $i++) {
        $cnt = sqlNumRows(sqlStatement("SELECT * FROM template_users WHERE tu_user_id=? AND tu_template_id=?", array($arr[$i], $list_id)));
        if (!$cnt) {
            sqlStatement("INSERT INTO template_users (tu_user_id,tu_template_id) VALUES (?,?)", array($arr[$i], $list_id));
        }
    }
} elseif ($Source == "add_item") {
    $row = sqlQuery("SELECT max(cl_order)+1 as order1 FROM customlists WHERE cl_list_id=?", array($templateid));
    $order = $row['order1'];
    $newid = sqlInsert("INSERT INTO customlists (cl_list_id,cl_list_type,cl_list_item_long,cl_order,cl_creator) VALUES (?,?,?,?,?)", array($templateid, 4, $item, $order, $_SESSION['authUserID']));
    sqlStatement("INSERT INTO template_users (tu_user_id,tu_template_id,tu_template_order) VALUES (?,?,?)", array($_SESSION['authUserID'], $newid, $order));
} elseif ($Source == "delete_item") {
    sqlStatement("DELETE FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($item, $_SESSION['authUserID']));
} elseif ($Source == "update_item") {
    $row = sqlQuery("SELECT max(cl_order)+1 as order1 FROM customlists WHERE cl_list_id=?", array($templateid));
    $order = $row['order1'];
    $newid = sqlInsert("INSERT INTO customlists (cl_list_id,cl_list_type,cl_list_item_long,cl_order,cl_creator) VALUES (?,?,?,?,?)", array($templateid, 4, $content, $order, $_SESSION['authUserID']));
    sqlStatement("UPDATE template_users SET tu_template_id=? WHERE tu_template_id=? AND tu_user_id=?", array($newid, $item, $_SESSION['authUserID']));
} elseif ($Source == 'item_show') {
    $sql = "SELECT * FROM customlists WHERE cl_list_id=? AND cl_list_type=4 AND cl_deleted=0";
    $res = sqlStatement($sql, array($list_id));
    $selcat = sqlQuery("SELECT * FROM customlists WHERE cl_list_slno=? AND cl_list_type=3 AND cl_deleted=0", array($list_id));
    $selcont = sqlQuery("SELECT * FROM customlists WHERE cl_list_slno=? AND cl_list_type=2 AND cl_deleted=0", array($selcat['cl_list_id']));
    $cnt = sqlNumRows($res);
    if ($cnt) {
        echo "<table class='table table-dark table-striped table-sm'>";
        echo "<tr class='text bg-dark text-light'><th colspan=2 class='text bg-dark text-light'>" . text(xl('Preview of') . " " . $selcat['cl_list_item_long'] . "(" . $selcont['cl_list_item_long'] . ")") . "</th></tr>";
        $i = 0;
        while ($row = sqlFetchArray($res)) {
            $i++;
            echo "<tr class='text'><td class='bg-dark text-light'>" . text($i) . "</td><td>" . text($row['cl_list_item_long']) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<table width='100%'>";
        echo "<tr class='text bg-dark text-light'><th colspan=2>" . xlt('No items under selected category') . "</th></tr>";
        echo "</table>";
    }
    $Source = "add_template";
} elseif ($Source == 'check_item') {
    $sql = sqlStatement("SELECT * FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($item, $list_id));
    $cnt = sqlNumRows($sql);
    if ($cnt) {
        echo xlt("OK");
    } else {
        echo xlt("FAIL");
    }
    $Source = "add_template";
} elseif ($Source == 'display_item') {
    $multi = preg_replace('/\|$/', '', $multi);
    $val = str_replace("|", ",", $multi);
    echo "<select multiple name='topersonalizeditem[]' id='topersonalizeditem' size='6' style='width:220px' onchange='display_item()'>";
    $resTemplates = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=4 AND cl_deleted=0 AND cl_list_id IN (?) ORDER BY cl_list_item_long", [$val]);
    while ($rowTemplates = sqlFetchArray($resTemplates)) {
        echo "<option value='" . attr($rowTemplates['cl_list_slno']) . "'>" . text($rowTemplates['cl_list_item_long']) . "</option>";
    }
    echo "</select>";
    $Source = "add_template";
} elseif ($Source == 'delete_category') {
    $res = sqlStatement("SELECT * FROM template_users AS tu LEFT OUTER JOIN users AS u ON tu.tu_user_id=u.id WHERE tu_template_id=? AND tu.tu_user_id!=?", array($templateid, $_SESSION['authUserID']));
    $users = '';
    $i = 0;
    while ($row = sqlFetchArray($res)) {
        $i++;
        $users .= $i . ")" . $row['fname'] . " " . $row['lname'] . "\n";
    }
    echo text($users);
    $Source = "add_template";
} elseif ($Source == 'delete_full_category') {
    sqlStatement("UPDATE customlists SET cl_deleted=? WHERE cl_list_slno=?", array(1, $templateid));
    sqlStatement("DELETE template_users WHERE tu_template_id=?", array($templateid));
    $res = sqlStatement("SELECT * FROM customlists AS cl WHERE cl_list_id=?", array($templateid));
    while ($row = sqlFetchArray($res)) {
        sqlStatement("UPDATE customlists SET cl_deleted=1 WHERE cl_list_slno=?", array($row['cl_list_slno']));
        sqlStatement("DELETE template_users WHERE tu_template_id=?", array($row['cl_list_slno']));
    }

    $Source = "add_template";
} elseif ($Source == 'checkcontext') {
    $res = sqlStatement("SELECT * FROM customlists WHERE cl_deleted=0 AND cl_list_type=3 AND cl_list_id=?", array($list_id));
    if (sqlNumRows($res)) {
        echo "1";
    } else {
        echo "0";
    }
    $Source = "add_template";
}
if ($Source != "add_template") {
    $res = sqlStatement(
        "SELECT * FROM customlists AS cl LEFT  OUTER JOIN template_users AS tu ON cl.cl_list_slno=tu.tu_template_id
                        WHERE cl_list_type=4 AND cl_list_id=? AND cl_deleted=0 AND tu.tu_user_id=? ORDER BY tu.tu_template_order",
        array($templateid, $_SESSION['authUserID'])
    );
    $i = 0;
    while ($row = sqlFetchArray($res)) {
        $i++;
        echo "<li class='bg-dark text-light' id='clorder_" . attr($row['cl_list_slno']) . "' style='cursor:pointer'><span class='bg-dark text-light'>";
        if (AclMain::aclCheckCore('nationnotes', 'nn_configure')) {
            echo "<img src='" . $GLOBALS['images_static_relative'] . "/b_edit.png' onclick='update_item_div(" . attr_js($row['cl_list_slno']) . ")'>";
        }
        echo "<div style='display:inline' id='" . attr($row['cl_list_slno']) . "' onclick='moveOptions_11(" . attr_js($row['cl_list_slno']) . ", \"textarea1\")'>" . text($row['cl_list_item_long']) . "</div>";
        if (AclMain::aclCheckCore('nationnotes', 'nn_configure')) {
            echo "<img src='" . $GLOBALS['images_static_relative'] . "/deleteBtn.png' onclick='delete_item(" . attr_js($row['cl_list_slno']) . ")'>";
            echo "<div id='update_item" . attr($row['cl_list_slno']) . "' style='display:none'><textarea name='update_item_txt" . attr($row['cl_list_slno']) . "' id='update_item_txt" . attr($row['cl_list_slno']) . "' class='w-100'>" . text($row['cl_list_item_long']) . "</textarea><br />";
            echo "<input type='button' name='update' onclick='update_item(" . attr_js($row['cl_list_slno']) . ")' value='" . xla('Update') . "'><input type='button' name='cancel' value='" . xla('Cancel') . "' onclick='cancel_item(" . attr_js($row['cl_list_slno']) . ")'></div>";
        }
        echo "</span></li>";
    }
    if (AclMain::aclCheckCore('nationnotes', 'nn_configure') && $templateid) {
        echo "<li class='bg-dark text-light' style='cursor:pointer'><span class='bg-dark text-light' onclick='add_item()'>" . xlt('Click to add new components');
        echo "</span><div id='new_item' style='display:none' class='w-100'>";
        echo "<textarea name='item' id='item' class='w-100 bg-dark text-light'></textarea><br />";
        echo "<input type='button' name='save' value='" . xla('Save') . "' onclick='save_item()'><input type='button' name='cancel' value='" . xla('Cancel') . "' onclick='cancel_item(" . attr_js($row['cl_list_slno'] ?? '') . ")'></div></li>";
    }
}
