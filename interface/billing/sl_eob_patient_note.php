<?php
/**
 * This allos entry and editing of a "billing note" for the patient.
 * 
 * Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/forms.inc");

  $info_msg = "";
?>
<html>
<head>
<?php html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<title><?php xl('EOB Posting - Patient Note','e')?></title>
</head>
<body>
<?php 
  $patient_id = $_GET['patient_id'];
  if (! $patient_id) die(xl("You cannot access this page directly."));

  if ($_POST['form_save']) {
    $thevalue = trim($_POST['form_note']);

    sqlStatement("UPDATE patient_data SET " .
      "billing_note = ? " .
      "WHERE pid = ? ", array($thevalue, $patient_id));

    echo "<script language='JavaScript'>\n";
    if ($info_msg) echo " alert('$info_msg');\n";
    echo " window.close();\n";
    echo "</script></body></html>\n";
    exit();
  }

  $row = sqlQuery("select fname, lname, billing_note " .
    "from patient_data where pid = '$patient_id' limit 1");
?>
<center>

<h2><?php echo xl('Billing Note for '). $row['fname'] . " " . $row['lname'] ?></h2>
<p>&nbsp;</p>

<form method='post' action='sl_eob_patient_note.php?patient_id=<?php  echo $patient_id ?>'>

<p>
<input type='text' name='form_note' size='60' maxlength='255'
 value='<?php  echo addslashes($row['billing_note']) ?>' />
</p>

<p>&nbsp;</p>
<input type='submit' name='form_save' value='<?php xl("Save","e")?>'>
&nbsp;
<input type='button' value='<?php xl("Cancel","e")?>' onclick='window.close()'>

</form>
</center>

</body>
</html>
