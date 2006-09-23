<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");

 // Get the recvq entries, parse and sort by filename.
 //
 $statlines = array();
 exec("faxstat -r -l -h " . $GLOBALS['hylafax_server'], $statlines);
 $mlines = array();
 foreach ($statlines as $line) {
  // This gets pagecount, sender, time, filename.
  if (preg_match('/^-\S+\s+(\d+)\s+\S+\s+(.+)\s+(\S+)\s+(\S+)\s*$/', $line, $matches)) {
   $mlines[$matches[4]] = $matches;
  }
 }
 ksort($mlines);

 // echo "<!--\n"; print_r($statlines); echo "-->\n"; // debugging

?>
<html>

<head>

<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>
<title><? xl('Received Faxes','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; font-weight: bold; }
tr.detail { font-size:10pt; }
td        { padding-left: 4px; padding-right: 4px; }
a, a:visited, a:hover { color:#0000cc; }
</style>

<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

// callback from popups to refresh this display.
function refreshme() {
 location.reload();
}

// Process click on filename to view.
function dodclick(ffname) {
 cascwin('fax_view.php?file=' + ffname, '_blank', 600, 475,
  "resizable=1,scrollbars=1");
 return false;
}

// Process click on More to pop up the dispatch window.
function domclick(ffname) {
 dlgopen('fax_dispatch.php?file=' + ffname, '_blank', 700, 475);
}

</script>

</head>

<body <?echo $top_bg_line;?>>
<form method='post' action='faxq.php'>

<table width='100%' cellpadding='1' cellspacing='2'>
 <tr class='head'>
  <td colspan='2' title='Click to view'><? xl('Document','e'); ?></td>
  <td><? xl('Received','e'); ?></td>
  <td><? xl('From','e'); ?></td>
  <td align='right'><? xl('Pages','e'); ?></td>
 </tr>
<?
 $encount = 0;
 foreach ($mlines as $matches) {
  ++$encount;
  $ffname = $matches[4];
  $ffbase = basename("/$ffname", '.tif');
  $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
  echo " <tr class='detail' bgcolor='$bgcolor'>\n";
  echo "  <td onclick='dodclick(\"$ffname\")'>";
  echo "<a href='fax_view.php?file=$ffname' onclick='return false'>$ffbase</a></td>\n";
  // echo "<a href='fax_view.php?file=$ffname'>$ffname</a></td>\n";
  echo "  <td onclick='domclick(\"$ffname\")'>";
  echo "<a href='fax_dispatch.php?file=$ffname' onclick='return false'>Dispatch</a></td>\n";
  echo "  <td>" . htmlentities($matches[3]) . "</td>\n";
  echo "  <td>" . htmlentities($matches[2]) . "</td>\n";
  echo "  <td align='right'>" . htmlentities($matches[1]) . "</td>\n";
  echo " </tr>\n";
 }
?>
</table>

</form>
</body>
</html>
