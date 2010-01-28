<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
?>
<html>

<head>

<title><?php xl('Order and Result Types','e'); ?></title>

<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style type="text/css">
body {
 font-family:sans-serif;
 font-size:9pt;
 font-weight:normal;
 padding: 5px 3px 5px 3px;
}
#con0 table {
 margin:0;
 padding:0;
 width:100%;
}
#con0 td {
 padding:0pt;
 font-family:sans-serif;
 font-size:9pt;
}
.plusminus {
 font-family:monospace;
 font-size:10pt;
}
.haskids {
 color:#0000dd;
 cursor:pointer;
 cursor:hand;
}
tr.head {
font-size:10pt;
background-color:#cccccc;
font-weight:bold;
}
tr.evenrow {
 background-color:#ddddff;
}
tr.oddrow {
 background-color:#ffffff;
}

.col1 {width:35%}
.col2 {width:8%}
.col3 {width:12%}
.col4 {width:35%}
.col5 {width:10%}
</style>

<script src="../../library/js/jquery-1.2.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

// initiate by loading the top-level nodes
$(document).ready(function(){
 $.getScript('types_ajax.php?id=0');
});

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
  $.getScript('types_ajax.php?id=' + id);
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
  $.getScript('types_ajax.php?id=' + id);
 else
  recolor();
}

// edit a node
function enode(id) {
 dlgopen('types_edit.php?parent=0&typeid=' + id, '_blank', 700, 550);
}

// add a node
function anode(id) {
 dlgopen('types_edit.php?typeid=0&parent=' + id, '_blank', 700, 550);
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
<center>

<h3 style='margin-top:0'><?php xl('Types of Orders and Results','e') ?></h3>

<table width='100%' cellspacing='0' cellpadding='0' border='0'>
 <tr class='head'>
  <th class='col1' align='left'>&nbsp;&nbsp;<?php xl('Name','e') ?></th>
  <th class='col2' align='left'><?php xl('Order','e') ?></th>
  <th class='col3' align='left'><?php xl('Code','e') ?></th>
  <th class='col4' align='left'><?php xl('Description','e') ?></th>
  <th class='col5' align='left'>&nbsp;</th>
 </tr>
</table>

<div id="con0">
</div>

<p><span onclick='anode(0)' class='haskids'>[<?php xl('Add Top Level','e') ?>]</span></p>

</center>
</body>
</html>

