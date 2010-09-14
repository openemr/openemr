<?php
// Copyright (C) 2009-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");

$patientid = $_REQUEST["patientid"];
$what      = $_REQUEST["file"];

$patientdir = $GLOBALS['OE_SITE_DIR'] . "/documents/$patientid";
$imagedir   = "$patientdir/demographics";
?>
<html>
<head>
<title>Upload Image</title>
<link rel="stylesheet" href="<?php echo xl($css_header,'e');?>" type="text/css">
</head>
<body>

<?php
  $errmsg = '';

  if ($_POST["form_submit"] || $_POST["form_delete"]) {
    if (!file_exists($patientdir)) mkdir($patientdir);
    if (!file_exists($imagedir  )) mkdir($imagedir  );
    $filename = "$imagedir/$what.jpg";

    if ($_POST["form_delete"]) {
      unlink($filename);
    }
    else {
      // Check if the upload worked.
      //
      if (! $errmsg) {
        if (! is_uploaded_file($_FILES['userfile']['tmp_name']))
          $errmsg = "Upload failed!  Make sure the path/filename is valid " .
            "and the file is less than 4,000,000 bytes.";
      }

      // Copy the image to its destination.
      //
      if (! $errmsg) {

        /***************************************************************
        $tmp = exec("/usr/bin/convert -resize 150x150 " .
          ($_POST["form_normalize"] ? "-equalize " : "") .
          $_FILES['userfile']['tmp_name'] .
          " $filename 2>&1");
        if ($tmp)
          $errmsg = "This is not a valid image, or its format is unsupported.";
        ***************************************************************/

        if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)) {
          $errmsg = "Internal error accessing uploaded file!";
        }
      }
    }

    // Write JavaScript for final disposition by the browser.
    //
    echo "<script LANGUAGE=\"JavaScript\">\n";
    if ($errmsg) {
      $errmsg = strtr($errmsg, "\r\n'", "   ");
      echo "window.alert('$errmsg')\n";
      echo "window.back()\n";
    } else {
      echo "opener.location.reload()\n";
      echo "window.close()\n";
    }
    echo "</script>\n</body>\n</html>\n";

    exit;
  }
?>

<center>

<p><b>Upload Image File</b></p>

</center>

<form method="post" name="main" action="upload_dialog.php?patientid=<?php echo $patientid ?>&file=<?php echo $what ?>" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="4000000">

<center>

<!-- Table required so input field does not start on a new line -->
<table border="0">
 <tr>
  <td style="font-size:11pt">
   Send this file:
  </td>
  <td>
   <input type="file" name="userfile" />
  </td>
 </tr>
</table>

<p>
<input type="submit" name="form_submit" value="Upload" />
<input type="button" value="Cancel" onclick="window.close()" />
<input type="submit" name="form_delete" value="Delete" />

</center>

</form>

</body>
</html>
