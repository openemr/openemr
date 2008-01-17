<?php
 // Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("../../../custom/code_types.inc.php");

 $info_msg = "";
 $codetype = $_REQUEST['codetype'];

 // If we are searching, search.
 //
 if ($_REQUEST['bn_search']) {
  $search_term = $_REQUEST['search_term'];
  $query = "SELECT code, modifier, code_text FROM codes WHERE " .
    "(code_text LIKE '%$search_term%' OR " .
    "code LIKE '%$search_term%') AND " .
    "code_type = '" . $code_types[$codetype]['id'] . "' " .
    "ORDER BY code";
  $res = sqlStatement($query);
  // $numrows = mysql_num_rows($res); // FIXME - not portable!
  echo "\n<!-- $query -->\n"; // debugging
 }
?>
<html>
<head>
<title><?php xl('Code Finder','e'); ?></title>
<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="JavaScript">

 function selcode(code, codedesc) {
  if (opener.closed || ! opener.set_related)
   alert('The destination form was closed; I cannot act on your selection.');
  else
   opener.set_related(code, codedesc);
  window.close();
  return false;
 }

</script>

</head>

<body <?echo $top_bg_line;?>>
<?
?>
<form method='post' name='theform' action='find_code_popup.php?codetype=<?php echo $codetype ?>'>
<center>

<table border='0' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td>
   <b>
 <?php xl('Search for:','e'); ?>
   <input type='text' name='search_term' size='12' value='<? echo $_REQUEST['search_term']; ?>'
    title='<?php xl('Any part of the desired code or its description','e'); ?>'>
   &nbsp;
   <input type='submit' name='bn_search' value='<?php xl('Search','e'); ?>'>
   &nbsp;&nbsp;&nbsp;
   <input type='button' value='<?php xl('Erase','e'); ?>' onclick="selcode('', '')">
   </b>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<?php if ($_REQUEST['bn_search']) { ?>

<table border='0'>
 <tr>
  <td><b><?php xl ('Code','e'); ?></b></td>
  <td><b><?php xl ('Description','e'); ?></b></td>
 </tr>
<?php
  while ($row = sqlFetchArray($res)) {
   $itercode = addslashes($row['code']);
   $itertext = addslashes(trim($row['code_text']));
   $anchor = "<a href='' " .
    "onclick='return selcode(\"$itercode\", \"$itertext\")'>";
   echo " <tr>";
   echo "  <td>$anchor$itercode</a></td>\n";
   echo "  <td>$anchor$itertext</a></td>\n";
   echo " </tr>";
  }
?>
</table>

<? } ?>

</center>
</form>
</body>
</html>
