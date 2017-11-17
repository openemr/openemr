<?php
/** * The address book entry editor. * Available from
Administration->Addr Book in the concurrent layout. * * Copyright (C)
2006-2010, 2016 Rod Roark
<rod @sunsetsystems.com> * * This program is free software; you can
redistribute it and/or * modify it under the terms of the GNU General
Public License * as published by the Free Software Foundation; either
version 2 * of the License, or (at your option) any later version. * *
Improved slightly by tony@mi-squared.com 2011, added organization to
view * and search * * @package OpenEMR * @author Rod Roark <rod
@sunsetsystems.com> * @link http://open-emr.org */

use OpenEMR\Core\Header;

require_once("../globals.php"); require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php"); 

$popup = empty($_GET['popup']) ? 0 : 1;

$form_fname = trim($_POST['form_fname']);
$form_lname = trim($_POST['form_lname']);
$form_specialty = trim($_POST['form_specialty']);
$form_organization = trim($_POST['form_organization']);
$form_abook_type = trim($_REQUEST['form_abook_type']);
$form_external = $_POST['form_external'] ? 1 : 0;

$sqlBindArray = array();
$query = "SELECT u.*, lo.option_id AS ab_name, lo.option_value as ab_option FROM users AS u " .
  "LEFT JOIN list_options AS lo ON " .
  "list_id = 'abook_type' AND option_id = u.abook_type AND activity = 1 " .
  "WHERE u.active = 1 AND ( u.authorized = 1 OR u.username = '' ) ";
if ($form_organization) {
    $query .= "AND u.organization LIKE ? ";
    array_push($sqlBindArray, $form_organization."%");
}

if ($form_lname) {
    $query .= "AND u.lname LIKE ? ";
    array_push($sqlBindArray, $form_lname."%");
}

if ($form_fname) {
    $query .= "AND u.fname LIKE ? ";
    array_push($sqlBindArray, $form_fname."%");
}

if ($form_specialty) {
    $query .= "AND u.specialty LIKE ? ";
    array_push($sqlBindArray, "%".$form_specialty."%");
}

if ($form_abook_type) {
    $query .= "AND u.abook_type LIKE ? ";
    array_push($sqlBindArray, $form_abook_type);
}

if ($form_external) {
    $query .= "AND u.username = '' ";
}

if ($form_lname) {
    $query .= "ORDER BY u.lname, u.fname, u.mname";
} else if ($form_organization) {
    $query .= "ORDER BY u.organization";
} else {
    $query .= "ORDER BY u.organization, u.lname, u.fname";
}

$query .= " LIMIT 500";
$res = sqlStatement($query, $sqlBindArray);
?>
<!DOCTYPE html >
<html>

<head>
    <?php Header::setupHeader(['common']);?>
    <title><?php echo xlt('Address Book'); ?></title>
    <style>
        @media only screen and (max-width: 1004px) {
               [class*="col-"] {
               width: 100%;
               text-align:left!Important;
                }
            }
        .table>tbody>tr>td, .table>tbody>tr>th, 
        .table>tfoot>tr>td, .table>tfoot>tr>th,
        .table>thead>tr>td, .table>thead>tr>th {
        border: 1px solid #ddd ! Important;
        }
        .table{
           min-width: 1600px; !Important
           
        }
        a {
            color:black;
        }
        .input-group-addon {
           text-align: left;
           background-color: transparent;
           border: 0px;
        }
    </style>
</head>

<body class="body_top">
    <div class="container-fluid">
         <div class="row">
            <div class="col-xs-12">
                 <div class="page-header clearfix">
                   <h2 class="clearfix"><span id='header_text'><?php echo xlt("Address Book"); ?></span> &nbsp;&nbsp;<a href="#addressbook_list" data-toggle="collapse"><i class="fa fa-search-plus fa-2x small" aria-hidden="true" title="<?php echo xla('Show/Hide Search Address Book'); ?>"></i></a></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
            <div id="addressbook_list" class="collapse">
                <form method='post' action='addrbook_list.php'
                    onsubmit='return top.restoreSession()'>
                    <div class="row">
                        <div class="col-xs-12">
                             <div class="form-group clearfix">
                                <div class="text-left">
                                    <div class="btn-group" role="group">
                                        <button type = 'submit' class='btn btn-default btn-search' title='<?php echo xla("Use % alone in a field to just sort on that column") ?>'  value='<?php echo xla("Search")?>'><?php echo xlt("Search")?></button>
                                        <button type='button'  class='btn btn-default btn-add' id='addbutton' onclick='doedclick_add(document.forms[0].form_abook_type.value)' value=<?php echo xlt("Add New"); ?>><?php echo xlt("Add New"); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <fieldset>
                        <div class="row" style="padding-top:10px">
                            
                            <div class="col-xs-6 oe-custom-line">
                                <div class="col-xs-4 label-div">
                                    <label class="control-label" for="form-fname"><?php echo xlt('First Name'); ?>:</label>
                                    <input type='text' name='form_fname' id='form_fname'
                                    value='<?php echo attr($_POST['form_fname']); ?>'
                                    class='form-control'
                                    title='<?php echo xla("All or part of the first name") ?>' />
                                </div>
                                <div class="col-xs-4 label-div">
                                    <label class="control-label" for="form-lname"><?php echo xlt('Last Name'); ?>:</label>
                                    <input type='text' name='form_lname' id='form_lname'
                                    value='<?php echo attr($_POST['form_lname']); ?>'
                                    class='form-control'
                                    title='<?php echo xla("All or part of the last name") ?>' />
                                </div>
                                <div class="col-xs-4 label-div">
                                    <label class="control-label" for="form-specialty"><?php echo xlt('Specialty'); ?>:</label>
                                    <input type='text' name='form_specialty' id='form_specialty'
                                    value='<?php echo attr($_POST['form_specialty']); ?>'
                                    class='form-control'
                                    title='<?php echo xla("Any part of the desired specialty") ?>' />
                                </div> 
                            </div>
                             <div class="col-xs-6 oe-custom-line">
                                <div class="col-xs-4 label-div">
                                    <label class="control-label" for="form_organization"><?php echo xlt('Organization'); ?>:</label>
                                    <input type='text' name='form_organization' ID='form_organization'
                                    value='<?php echo attr($_POST['form_organization']); ?>'
                                    class='form-control'
                                    title='<?php echo xla("All or part of the organization") ?>' />
                                </div>
                                <div class="col-xs-4 label-div">
                                    <label class="control-label" for="form-specialty"><?php echo xlt('Type'); ?>:</label>
                                    <?php
                                     // Generates a select list named form_abook_type:
                                    echo generate_select_list("form_abook_type", "abook_type", $_REQUEST['form_abook_type'], '', 'All');
                                    ?>
                                </div>
                                <div class="col-xs-4 label-div clearfix">
                                    <label class="control-label" for="form_external">Select:</label>
                                    <span class="input-group-addon">
                                        <input type='checkbox' name='form_external' id='form_external' value='1'
                                            <?php
                                                if ($form_external) {
                                                echo ' checked';
                                                }
                                            ?>
                                            title='<?php echo xla("Omit internal users?") ?>' /><span class="oe-ckbox-label"><?php echo xlt('External Only')?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <fieldset>
                    <div class= "table-responsive">
                        <table class= "table table-striped">
                            <thead>
                                <tr class='head'>
                                    <th title='<?php echo xla('Click to view or edit'); ?>'><?php echo xlt('Organization'); ?></th>
                                    <th><?php echo xlt('Name'); ?></th>
                                    <th><?php echo xlt('Local'); ?></th>
                                    <!-- empty for external -->
                                    <th><?php echo xlt('Type'); ?></th>
                                    <th><?php echo xlt('Specialty'); ?></th>
                                    <th><?php echo xlt('Phone'); ?></th>
                                    <th><?php echo xlt('Mobile'); ?></th>
                                    <th><?php echo xlt('Fax'); ?></th>
                                    <th><?php echo xlt('Email'); ?></th>
                                    <th><?php echo xlt('Street'); ?></th>
                                    <th><?php echo xlt('City'); ?></th>
                                    <th><?php echo xlt('State'); ?></th>
                                    <th><?php echo xlt('Postal'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $encount = 0;
                            while ($row = sqlFetchArray($res)) {
                                ++ $encount;
                                // $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
                                $bgclass = (($encount & 1) ? "evenrow" : "oddrow");
                                $username = $row['username'];
                                if (! $row['active']) {
                                    $username = '--';
                                }
                                
                                $displayName = $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']; // Person Name
                                if ($row['suffix'] > '') {
                                    $displayName .= ", " . $row['suffix'];
                                }
                                
                                if (acl_check('admin', 'practice') || (empty($username) && empty($row['ab_name']))) {
                                    // Allow edit, since have access or (no item type and not a local user)
                                    $trTitle = xl('Edit') . ' ' . $displayName;
                                   echo " <tr class='address_names detail' style='cursor:pointer' " .
                                   "onclick='doedclick_edit(" . $row['id'] . ")' title='" . attr($trTitle) . "'>\n";
                                } else {
                                    // Do not allow edit, since no access and (item is a type or is a local user)
                                    $trTitle = $displayName . " (" . xl("Not Allowed to Edit") . ")";
                                    echo " <tr class='address_names detail' title='".attr($trTitle)."'>\n";
                                }
                                
                                echo "  <td>" . text($row['organization']) . "</td>\n";
                                echo "  <td>" . text($displayName) . "</td>\n";
                                echo "  <td>" . ($username ? '*' : '') . "</td>\n";
                                echo "  <td>" . generate_display_field(array(
                                    'data_type' => '1',
                                    'list_id' => 'abook_type'
                                ), $row['ab_name']) . "</td>\n";
                                echo "  <td>" . text($row['specialty']) . "</td>\n";
                                echo "  <td>" . text($row['phonew1']) . "</td>\n";
                                echo "  <td>" . text($row['phonecell']) . "</td>\n";
                                echo "  <td>" . text($row['fax']) . "</td>\n";
                                echo "  <td>" . text($row['email']) . "</td>\n";
                                echo "  <td>" . text($row['street']) . "</td>\n";
                                echo "  <td>" . text($row['city']) . "</td>\n";
                                echo "  <td>" . text($row['state']) . "</td>\n";
                                echo "  <td>" . text($row['zip']) . "</td>\n";
                                echo " </tr>\n";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </fieldset>
            </div>
        </div>
        <!--<div style="display: none;">
            <a class="iframe addrbookedit_modal"></a>
        </div>-->
    </div><!--end of container div -->
<script>
   $('#show_hide').click(function() {
        var elementTitle = $('#show_hide').prop('title');
        var hideTitle = '<?php echo xla('Click to Hide'); ?>';
        var showTitle = '<?php echo xla('Click to Show'); ?>';
        $('.hideaway').toggle('1000');
        $(this).toggleClass('fa-eye-slash fa-eye');
        if (elementTitle == hideTitle) {
            elementTitle = showTitle;
        } else if (elementTitle == showTitle) {
            elementTitle = hideTitle;
        }
        $('#show_hide').prop('title', elementTitle);
    });
</script>       
<?php if ($popup) { ?>
<script type="text/javascript" src="../../library/topdialog.js"></script>
<?php } ?>
<script type="text/javascript"
    src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>">
</script>

<script language="JavaScript">
    <?php
    if ($popup) {
        require ($GLOBALS['srcdir'] . "/restoreSession.php");
    }
    ?>

    // Callback from popups to refresh this display.
    function refreshme() {
     // location.reload();
     document.forms[0].submit();
    }

    // Process click to pop up the add window.
    function doedclick_add(type) {
     top.restoreSession();
     dlgopen('addrbook_edit.php?type=' + type, '_blank', 650, (screen.availHeight * 75/100));
    }

    // Process click to pop up the edit window.
    function doedclick_edit(userid) {
     top.restoreSession();
     dlgopen('addrbook_edit.php?userid=' + userid, '_blank', 650, (screen.availHeight * 75/100));
    }

    // $(document).ready(function(){
      // // initialise fancy box
      // enable_modals();

      // // initialise a link
      // $(".addrbookedit_modal").fancybox( {
        // 'overlayOpacity' : 0.0,
        // 'showCloseButton' : true,
        // 'frameHeight' : 550,
        // 'frameWidth' : 700
      // });
    // });
</script>

</body>
</html>