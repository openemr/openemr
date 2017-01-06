<?php
/**
* Maintenance for the list of procedure providers.
*
* Copyright (C) 2012 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/htmlspecialchars.inc.php");

$popup = empty($_GET['popup']) ? 0 : 1;

$form_name = trim($_POST['form_name']);

$query = "SELECT pp.* FROM procedure_providers AS pp";
$query .= " ORDER BY pp.name";
$res = sqlStatement($query);

$adm_acl = acl_check('admin', 'practice' );
?>
<html>

<head>

<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<link rel="stylesheet" href='<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css' type='text/css'>
<link rel="stylesheet" href='<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css' type='text/css'>
<style type="text/css">
    table tr:hover{cursor:pointer;} /* Since not part of standard, set this for all tables */
</style>
<title><?php echo xlt('Procedure Providers'); ?></title>

<script language="JavaScript">

<?php if ($popup) require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// Callback from popups to refresh this display.
function refreshme() {
 // location.reload();
 document.forms[0].submit();
}
</script>

</head>

<body class="body_top">
<div class="container" style='top: 0;left:0; width: 100%; margin:0; padding: 0;'>
    <div class="row">
        <div id="left4" class="col-md-4"></div>
        <?php if ($adm_acl) {?>
        <div id="right8" class="col-md-8 embed-responsive embed-responsive-16by9">
            <iframe id='ppeditor' class="embed-responsive-item"></iframe>
        </div>
        <?php }?>
    </div>
</div>
<div id="addressbook_list">
<form method='post' action='procedure_provider_list.php'>

<table>
 <tr class='search'> <!-- bgcolor='#ddddff' -->
  <td>
   <input type='submit' class='button' name='form_search' value='<?php echo xla("Refresh")?>' />
   <input type='button' class='button' value='<?php echo xla("Add New"); ?>' onclick='edit_ppid(0)' />
  </td>
 </tr>
</table>

<table class='table table-striped table-hover table-bordered'>
 <tr class='head'>
  <td title='<?php echo xla('Click to view or edit'); ?>'><?php echo xlt('Name'); ?></td>
  <td><?php echo xlt('NPI'); ?></td>
  <td><?php echo xlt('Protocol'); ?></td>
 </tr>

<?php
 $encount = 0;
 while ($row = sqlFetchArray($res)) {
     printf('<tr class="clickable-row" data-p1="%s">
                <td>%s</td><td>%s</td><td>%s,%s</td>
             </tr>', 
        $row['ppid'], text($row['name']), text($row['npi']), text($row['protocol']), 
        text($row['direction']));
 }
?>
</table>
</form>
</div>
<script type="text/JavaScript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-2-2-0/index.js"></script>
<script type="text/javascript">
var adm_acl = <?php echo ($adm_acl ? 'true':'false'); ?>;
var xl_icons = {'DL':'download', 'SFTP':'bolt', 'FS':'folder', 'R':'download', 'B':'exchange'};
$(document).ready(function() {
    $('#addressbook_list').detach().appendTo('#left4');
    if (adm_acl) {
        $('.clickable-row').click(function() {
            $(this).addClass('alert alert-warning active').siblings().removeClass('alert alert-warning active');
            edit_ppid ($(this).data('p1')); 
        });
    }
    $('.clickable-row td:nth-child(3)').each(function() {
        var p = $(this).html().split(',');
        $(this).html('');
        for (ix = 0; ix < p.length; ++ix) {
            $(this).append('<i class="fa fa-' + xl_icons[p[ix]] + '" aria-hidden="true"></i>&nbsp;');
        }
    });
});
function edit_ppid(ppid) {
    top.restoreSession();
    $('#ppeditor').attr('src', 'procedure_provider_edit.php?ppid='+ppid);
}
</script>
</body>
</html>
