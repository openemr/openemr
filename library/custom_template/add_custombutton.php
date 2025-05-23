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
//
// +------------------------------------------------------------------------------+


require_once("../../interface/globals.php");

use OpenEMR\Core\Header;

if ((isset($_POST['form_save']) && $_POST['form_save'] == 'Save') || (isset($_POST['form_delete']) && $_POST['form_delete'] == 'Delete')) {
    $count = $_POST['count'];
    $k = 1;
    $sta = $_POST['start'];
    $end = $st + $count;
    for ($cnt = $sta; $cnt <= $end; $cnt++) {
        if ($_POST['hidid' . $cnt]) {
            if (trim(formData('inshort' . $cnt)) == '' && trim(formdata('designation' . $cnt)) == '') {
                sqlStatement("UPDATE customlists SET cl_deleted=1 WHERE cl_list_slno=?", array($_POST['hidid' . $cnt]));
                sqlStatement("DELETE FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($_POST['hidid' . $cnt], $_SESSION['authUserID']));
            } else {
                $sql = "UPDATE customlists SET cl_list_item_short=?,cl_list_item_long=?,cl_order=? WHERE cl_list_slno=?";
                sqlStatement($sql, array($_POST['inshort' . $cnt], $_POST['designation' . $cnt], $_POST['level' . $cnt], $_POST['hidid' . $cnt]));
            }
        } else {
            if (trim(formData('inshort' . $cnt)) != '' || trim(formdata('designation' . $cnt)) != '') {
                $rowID = sqlQuery("SELECT MAX(cl_list_item_id)+1 as maxID FROM customlists WHERE cl_list_type=6");
                $itemID = $rowID['maxID'] ? $rowID['maxID'] : 1;
                $sql = "INSERT INTO customlists (cl_list_item_id,cl_list_type,cl_list_item_short,cl_list_item_long,cl_order) VALUES(?,?,?,?,?)";
                $newid = sqlInsert($sql, array($itemID, 6, $_POST['inshort' . $cnt], $_POST['designation' . $cnt], $_POST['level' . $cnt]));
                sqlStatement("INSERT INTO template_users (tu_user_id,tu_template_id) VALUES (?,?)", array($_SESSION['authUserID'], $newid));
            }
        }
        if ($_POST['form_delete'] == 'Delete') {
            if ($_POST['chk' . $cnt]) {
                sqlStatement("UPDATE customlists SET cl_deleted=1 WHERE cl_list_slno=?", array($_POST['chk' . $cnt]));
                sqlStatement("DELETE FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($_POST['chk' . $cnt], $_SESSION['authUserID']));
            }
        }
    }
    unset($_POST['form_save']);
    unset($_POST['form_delete']);
}
?>
<html>
<head>
    <?php Header::setupHeader('opener'); ?>
    <style>
        .bottom {
            border-bottom: 1px solid var(--black);
        }

        .top {
            border-top: 1px solid var(--black);
        }

        .left {
            border-left: 1px solid var(--black);
        }

        .right {
            border-right: 1px solid var(--black);
        }

        .class1 {
            background-color: #7dc1db;
        }

        .class2 {
            background-color: #ef2983;
        }
    </style>
</head>
<body class="body_top">
<form name="designation_managment" action="" method="post" onsubmit="top.restoreSession();">
  <h3 class="text-center"><?php echo htmlspecialchars(xl('Add Custom Button'), ENT_QUOTES); ?></h3>
  <div class="table-responsive">
    <table class="table table-borderless">
        <tr class="text-center">
            <td class="title_bar_top">#</td>
            <td class="title_bar_top"><?php echo htmlspecialchars(xl('Value'), ENT_QUOTES); ?></td>
            <td class="title_bar_top"><?php echo htmlspecialchars(xl('Display Name'), ENT_QUOTES); ?></td>
            <td class="title_bar_top"><?php echo htmlspecialchars(xl('Order'), ENT_QUOTES); ?></td>
            <td class="title_bar_top">&nbsp;</td>
        </tr>
        <?php
        $i = 1;
        $res = sqlStatement("SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS cl ON cl.cl_list_slno=tu.tu_template_id
                           WHERE tu.tu_user_id = ? AND cl.cl_list_type = 6 AND cl.cl_deleted = 0 ORDER BY cl.cl_order", array($_SESSION['authUserID']));
        $sl = 1;
        $start = 1;
        while ($row = sqlFetchArray($res)) {
            if ($sl == 1) {
                $start = $row['cl_list_slno'];
            }
            $i = $row['cl_list_slno'];
            $class = 'class1';
            ?>
            <tr class='<?php echo htmlspecialchars($class, ENT_QUOTES); ?> text-center'>
              <input type='hidden' name='<?php echo htmlspecialchars("hidid" . $i, ENT_QUOTES); ?>' value='<?php echo htmlspecialchars($row['cl_list_slno'], ENT_QUOTES); ?>' />
                <td><input type='text' class="form-control" name="<?php echo htmlspecialchars("sl" . $i, ENT_QUOTES); ?>" value="<?php echo htmlspecialchars($sl, ENT_QUOTES); ?>" readonly="" style="width:25px; background-color:#C9C9C9" /></td>
                <td><input type='text' class="form-control" name="<?php echo htmlspecialchars("inshort" . $i, ENT_QUOTES); ?>" size="10" value="<?php echo htmlspecialchars($row['cl_list_item_short'], ENT_QUOTES); ?>"/>
                </td>
                <td><input type='text' class="form-control" name="<?php echo htmlspecialchars("designation" . $i, ENT_QUOTES); ?>" value="<?php echo htmlspecialchars($row['cl_list_item_long'], ENT_QUOTES); ?>"/>
                </td>
                <td><input type='text' class="form-control" name='<?php echo htmlspecialchars("level" . $i, ENT_QUOTES); ?>' value="<?php echo htmlspecialchars($row['cl_order'], ENT_QUOTES); ?>" size='1'>
                </td>
                <td><input type='checkbox' name='<?php echo htmlspecialchars("chk" . $i, ENT_QUOTES); ?>' value='<?php echo htmlspecialchars($row['cl_list_slno'], ENT_QUOTES); ?>'>
                </td>
            </tr>
            <?php
            $i++;
            $sl++;
        }
        ?>
        <tr class="text-center">
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('sl' . $i, ENT_QUOTES); ?>" value="<?php echo htmlspecialchars($sl, ENT_QUOTES); ?>" readonly="" style="width:50px; background-color:#C9C9C9" /></td>
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('inshort' . $i, ENT_QUOTES); ?>" size="10" value="" /></td>
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('designation' . $i, ENT_QUOTES); ?>" value="" /></td>
            <td><input type='text' class="form-control" name='<?php echo htmlspecialchars("level" . $i, ENT_QUOTES); ?>' size='1' /></td>
        </tr>
        <tr class="text-center">
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('sl' . ($i + 1), ENT_QUOTES); ?>" value="<?php echo htmlspecialchars(($sl + 1), ENT_QUOTES); ?>" readonly="" style="width:50px; background-color:#C9C9C9" /></td>
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('inshort' . ($i + 1), ENT_QUOTES); ?>" size="10" value="" /></td>
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('designation' . ($i + 1), ENT_QUOTES); ?>" value="" /></td>
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('level' . ($i + 1), ENT_QUOTES); ?>" size='1' /></td>
        </tr>
        <tr class="text-center">
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('sl' . ($i + 2), ENT_QUOTES); ?>" value="<?php echo htmlspecialchars(($sl + 2), ENT_QUOTES); ?>" readonly="" style="width:50px; background-color:#C9C9C9" /></td>
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('inshort' . ($i + 2), ENT_QUOTES); ?>" size="10" value="" /></td>
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('designation' . ($i + 2), ENT_QUOTES); ?>" value="" /></td>
            <td><input type='text' class="form-control" name="<?php echo htmlspecialchars('level' . ($i + 2), ENT_QUOTES); ?>" size='1' /></td>
        </tr>
        <input type="hidden" name="count" value="<?php echo htmlspecialchars(($i + 2), ENT_QUOTES); ?>" />
        <tr class="text">
            <td class="text-center" colspan="5">
                <input type='submit' class="btn btn-primary" name='form_save' id='form_save'
                       value="<?php echo htmlspecialchars(xl('Save'), ENT_QUOTES); ?>"/>
                <input type='submit' class="btn btn-secondary" name='form_delete' id='form_delete'
                       value="<?php echo htmlspecialchars(xl('Delete'), ENT_QUOTES); ?>"
                       title='<?php echo htmlspecialchars(xl('Select corresponding checkboxes to delete'), ENT_QUOTES); ?>'/>
            </td>
        </tr>
    </table>
  </div>
</form>
</body>
</html>
