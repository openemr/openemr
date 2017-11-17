<?php
/**
 * Copyright (C) 2010-2012 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */
use OpenEMR\Core\Header;

require_once ("../globals.php");
require_once ("$srcdir/acl.inc");

// This script can be run either inside the OpenEMR frameset for order catalog
// maintenance, or as a popup window for selecting an item to order. In the
// popup case the GET variables 'popup' (a boolean) and 'order' (an optional
// item ID to select) will be provided, and maintenance may also be permitted.

$popup = empty($_GET['popup']) ? 0 : 1;
$order = isset($_GET['order']) ? $_GET['order'] + 0 : 0;
$labid = isset($_GET['labid']) ? $_GET['labid'] + 0 : 0;

// If Save was clicked, set the result, close the window and exit.
//
if ($popup && $_POST['form_save']) {
    $form_order = isset($_GET['form_order']) ? $_GET['form_order'] + 0 : 0;
    $ptrow = sqlQuery("SELECT name FROM procedure_type WHERE " . "procedure_type_id = '$form_order'");
    $name = addslashes($ptrow['name']);
    ?>
<script type="text/javascript"
    src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<script language="JavaScript">
if (opener.closed || ! opener.set_proc_type) {
 alert('<?php xl('The destination form was closed; I cannot act on your selection.', 'e'); ?>');
}
else {
 opener.set_proc_type(<?php echo "$form_order, '$name'" ?>);
<?php
    // This is to generate the "Questions at Order Entry" for the Procedure Order form.
    // GET parms needed for this are: formid, formseq.
    if (isset($_GET['formid'])) {
        require_once ("qoe.inc.php");
        $qoe_init_javascript = '';
        echo ' opener.set_proc_html("';
        echo generate_qoe_html($form_order, intval($_GET['formid']), 0, intval($_GET['formseq']));
        echo '", "' . $qoe_init_javascript . '");' . "\n";
    }
    ?>
}
window.close(); // comment out for debugging
</script>
<?php
    exit();
}

// end Save logic

?>
<!DOCTYPE html>
<html>

<head>
    <?php Header::setupHeader(['datetime-picker']);?>

<title><?php echo xlt('Configure Orders and Results'); ?></title>

<!--<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>-->

<style type="text/css">
body {
    font-family: sans-serif;
    font-size: 9pt;
    font-weight: normal;
    padding: 5px 3px 5px 3px;
}

#con0 table {
    margin: 0;
    padding: 0;
    width: 100%;
}

#con0 td {
    padding: 0pt;
    font-family: sans-serif;
    font-size: 9pt;
}

.plusminus {
    font-family: monospace;
    font-size: 10pt;
}

.haskids {
    color: #0000dd;
    cursor: pointer;
    cursor: hand;
}

tr.head {
    font-size: 10pt;
    background-color: #cccccc;
    font-weight: bold;
}

tr.evenrow {
    background-color: #ddddff;
}

tr.oddrow {
    background-color: #ffffff;
}

.col1 {
    width: 35%
}

.col2 {
    width: 8%
}

.col3 {
    width: 12%
}

.col4 {
    width: 35%
}

.col5 {
    width: 10%
}
</style>

<script
    src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-2-2/index.js"
    type="text/javascript"></script>
<?php if ($popup) { ?>
<script type="text/javascript" src="../../library/topdialog.js"></script>
<?php } ?>
<script type="text/javascript"
    src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>

<script language="JavaScript">

<?php

if ($popup) {
    require ($GLOBALS['srcdir'] . "/restoreSession.php");
}
?>

<?php
// Create array of IDs to pre-select, leaf to top.
echo "preopen = [";
echo $order > 0 ? $order : 0;
for ($parentid = $order; $parentid > 0;) {
    $row = sqlQuery("SELECT parent FROM procedure_type WHERE procedure_type_id = '$parentid'");
    $parentid = $row['parent'] + 0;
    echo ", $parentid";
}

echo "];\n";
?>

// initiate by loading the top-level nodes
$(document).ready(function(){
 nextOpen();
});

// This is called repeatedly at initialization until all desired nodes
// have been opened.
function nextOpen() {
 if (preopen.length) {
  var thisid = preopen.pop();
  if (thisid == 0 || preopen.length > 0) {
   if (thisid > 0)
    toggle(thisid);
   else
    $.getScript('types_ajax.php?id=' + thisid + '&order=<?php echo $order; ?>' + '&labid=<?php echo $labid; ?>');
  }
  else {
   recolor();
  }
 }
 else {
  recolor();
 }
}

// toggle expansion indicator from + to - or vice versa
function swapsign(td1, from, to) {
 var s = td1.html();
 var i = s.indexOf('>' + from + '<');
 if (i >= 0) td1.html(s.substring(0,i+1) + to + s.substring(i+2));
}

// onclick handler to expand or collapse a node
function toggle(id) {
 var td1 = $('#td' + id);
 if (!td1.hasClass('haskids')) return;
 if (td1.hasClass('isExpanded')) {
  $('#con' + id).remove();
  td1.removeClass('isExpanded');
  swapsign(td1, '-', '+');
  recolor();
 }
 else {
  td1.parent().after('<tr class="outertr"><td colspan="5" id="con' + id + '" style="padding:0">Loading...</td></tr>');
  td1.addClass('isExpanded');
  swapsign(td1, '+', '-');
  $.getScript('types_ajax.php?id=' + id + '&order=<?php echo $order; ?>' + '&labid=<?php echo $labid; ?>');
 }
}

// Called by the edit window to refresh a given node's children
function refreshFamily(id, haskids) {
 if (id) { // id == 0 means top level
  var td1 = $('#td' + id);
  if (td1.hasClass('isExpanded')) {
   $('#con' + id).remove();
   td1.removeClass('isExpanded');
   swapsign(td1, '-', '+');
  }
  if (td1.hasClass('haskids') && !haskids) {
   td1.removeClass('haskids');
   // swapsign(td1, '+', '.');
   swapsign(td1, '+', '|');
   return;
  }
  if (!td1.hasClass('haskids') && haskids) {
   td1.addClass('haskids');
   // swapsign(td1, '.', '+');
   swapsign(td1, '|', '+');
  }
  if (haskids) {
   td1.parent().after('<tr class="outertr"><td colspan="5" id="con' + id + '" style="padding:0">Loading...</td></tr>');
   td1.addClass('isExpanded');
   swapsign(td1, '+', '-');
  }
 }
 if (haskids)
  $.getScript('types_ajax.php?id=' + id + '&order=<?php echo $order; ?>' + '&labid=<?php echo $labid; ?>');
 else
  recolor();
}

// edit a node
function enode(id) {
 dlgopen('types_edit.php?parent=0&typeid=' + id, '_blank', 700, 550);
 //$("#targetiframe").attr("src", 'types_edit.php?typeid=0&parent=' + id);
}

// add a node
function anode(id) {
 dlgopen('types_edit.php?typeid=0&parent=' + id, '_blank', 700, 550);
 //$("#targetiframe").attr("src", 'http://google.com');
}

// call this to alternate row colors when anything changes the number of rows
function recolor() {
 var i = 0;
 $('#con0 tr').each(function(index) {
  // skip any row that contains other rows
  if ($(this).hasClass('outertr')) return;
  this.className = (i++ & 1) ? "evenrow" : "oddrow";
 });
}

</script>

</head>

<body class="body_nav">
    <div class="container">
        <div class="row">
            <div class="page-header clearfix">
                <h2 class="clearfix"><?php echo xlt("Configure Orders and Results") ; ?> <a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
            </div>
        </div>
        <div class="row">
            <form method='post' name='theform'
                action='types.php?popup=<?php echo $popup ?>&order=<?php
                echo $order;
                if (isset($_GET['formid'])) {
                    echo '&formid=' . $_GET['formid'];
                }

                if (isset($_GET['formseq'])) {
                    echo '&formseq=' . $_GET['formseq'];
                }
                ?>'>
                <div class = "table-responsive">
                    <table class="table">
                        <thead>
                            <tr class='head'>
                                <td class='col1' align='left'>&nbsp;&nbsp;<?php xl('Name', 'e') ?></td>
                                <td class='col2' align='left'><?php xl('Order', 'e') ?></td>
                                <td class='col3' align='left'><?php xl('Code', 'e') ?></td>
                                <td class='col4' align='left'><?php xl('Description', 'e') ?></td>
                                <td class='col5' align='left'>&nbsp;</td>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="con0"></div>
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                <div class="form-group clearfix">
                    <div class="col-sm-12 text-left position-override">
                        <div class="btn-group btn-group-pinch" role="group">
                            <?php if ($popup) { ?>
                                <button type="submit" class="btn btn-default btn-save" name='form_save' value='<?php echo xlt('Save'); ?>'><?php echo xlt('Save');?></button>
                                <button class="btn btn-link btn-cancel btn-separate-left" onclick="CancelDistribute()"><?php echo xlt('Cancel');?></button>
                            <?php } ?>
                            <br><br> 
                                <button class="btn btn-default btn-add" name='add_node_btn' id='add_node_button'  onclick='anode(0)'><?php echo xlt('Add Top Level');?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div><!--End of Container div-->
    <div class="row">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog oe-modal-dialog modal-lg">
                <div class="modal-content oe-modal-content">
                    <div class="modal-header clearfix"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button></div>
                    <div class="modal-body">
                        <iframe src="" id="targetiframe" style="height:650px; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe>  
                    </div>
                    <div class="modal-footer" style="margin-top:0px;">
                       <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button"><?php echo xlt('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#help-href').click (function(){
                <?php
                if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443){
                    echo "alert ('". xlt('Your main page was loaded over HTTPS but you are requesting an insecure resource over HTTP. This request has been blocked, the content must be served over HTTPS.')."');";
                    echo "return;";
                } else {
                    echo "document.getElementById('targetiframe').src = 'http://www.open-emr.org/wiki/index.php/Procedures_Module_Configuration_for_Manual_Result_Entry';";
                }
                ?>
            })
        }); 
          
    </script>
</body>
</html>

