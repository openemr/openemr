<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../../globals.php");

 $info_msg = "";

 // Call this to get the URL for form submission.
 function coding_form_action() {
  return $GLOBALS['rootdir'] . "/patient_file/encounter/coding_popup.php";
 }

 // Call this to generate JavaScript that will close the window.
 //
 function terminate_coding() {
  global $info_msg;
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " window.close();\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  echo "</script></body></html>\n";
  exit();
 }
?>
<html>
<head>
<?php html_header_show();?>
<title>Coding</title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<script language='JavaScript'>
 function docancel() {
  window.close();
 }
</script>
</head>

<body class="body_top">
<?php
 // This has all the interesting stuff.
 include_once("$srcdir/coding.inc.php");
?>
</body>
</html>
