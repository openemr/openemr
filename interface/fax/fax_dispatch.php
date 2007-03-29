<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/pnotes.inc");

 if ($_GET['file']) {
  $mode = 'fax';
  $filename = $_GET['file'];
  $filepath = $GLOBALS['hylafax_basedir'] . '/recvq/' . $filename;
 }
 else if ($_GET['scan']) {
  $mode = 'scan';
  $filename = $_GET['scan'];
  $filepath = $GLOBALS['scanner_output_directory'] . '/' . $filename;
 }
 else {
  die("No filename was given.");
 }

 $ext = substr($filename, strrpos($filename, '.'));
 $filebase = basename("/$filename", $ext);
 $faxcache = "$webserver_root/faxcache/$mode/$filebase";

 $info_msg = "";

 // This function builds an array of document categories recursively.
 // Kittens are the children of cats, you know.  :-)
 //
 function getKittens($catid, $catstring, &$categories) {
  $cres = sqlStatement("SELECT id, name FROM categories " .
   "WHERE parent = $catid ORDER BY name");
  $childcount = 0;
  while ($crow = sqlFetchArray($cres)) {
   ++$childcount;
   getKittens($crow['id'], ($catstring ? "$catstring / " : "") .
    ($catid ? $crow['name'] : ''), $categories);
  }
  // If no kitties, then this is a leaf node and should be listed.
  if (!$childcount) $categories[$catid] = $catstring;
 }

 // This merges the tiff files for the selected pages into one tiff file.
 //
 function mergeTiffs() {
  global $faxcache;
  $msg = '';
  $inames = '';
  $tmp1 = array();
  $tmp2 = 0;
  foreach ($_POST['form_images'] as $inbase) {
   $inames .= ' ' . escapeshellarg("$inbase.tif");
  }
  if (!$inames) die(xl("Internal error - no pages were selected!"));
  $tmp0 = exec("cd '$faxcache'; tiffcp $inames temp.tif", $tmp1, $tmp2);
  if ($tmp2) {
   $msg .= "tiffcp returned $tmp2: $tmp0 ";
  }
  return $msg;
 }

 // If we are submitting...
 //
 if ($_POST['form_save']) {
  $action_taken = false;
  $tmp1 = array();
  $tmp2 = 0;

  if ($_POST['form_cb_copy']) {
   $patient_id = (int) $_POST['form_pid'];
   if (!$patient_id) die(xl('Internal error - patient ID was not provided!'));
   // Compute the name of the target directory and make sure it exists.
   $docdir = "$webserver_root/documents/$patient_id";
   exec("mkdir -p '$docdir'");
   // Compute a target filename that does not yet exist.
   $ffname = trim($_POST['form_filename']);
   $i = strrpos($ffname, '.');
   if ($i) $ffname = trim(substr($ffname, 0, $i));
   if (!$ffname) $ffname = $filebase;
   $ffmod  = '';
   $ffsuff = '.pdf';
   // If the target filename exists, modify it until it doesn't.
   $count = 0;
   while (is_file("$docdir/$ffname$ffmod$ffsuff")) {
    ++$count;
    $ffmod = "_$count";
   }
   $target = "$docdir/$ffname$ffmod$ffsuff";

   // Create the target PDF.
   $info_msg .= mergeTiffs();
   $tmp0 = exec("tiff2pdf -p letter -o '$target' '$faxcache/temp.tif'", $tmp1, $tmp2);
   if ($tmp2) {
    $info_msg .= "tiff2pdf returned $tmp2: $tmp0 ";
   }
   else {
    $newid = generate_id();
    $fsize = filesize($target);
    $catid = (int) $_POST['form_category'];
    // Update the database.
    $query = "INSERT INTO documents ( " .
      "id, type, size, date, url, mimetype, foreign_id" .
      " ) VALUES ( " .
      "'$newid', 'file_url', '$fsize', NOW(), 'file://$target', " .
      "'application/pdf', $patient_id " .
      ")";
    sqlStatement($query);
    $query = "INSERT INTO categories_to_documents ( " .
      "category_id, document_id" .
      " ) VALUES ( " .
      "'$catid', '$newid' " .
      ")";
    sqlStatement($query);

    // If we are posting a note...
    if ($_POST['form_cb_note']) {
     // Build note text in a way that identifies the new document.
     // See pnotes_full.php which uses this to auto-display the document.
     $note = "$ffname$ffmod$ffsuff";
     for ($tmp = $catid; $tmp;) {
      $catrow = sqlQuery("SELECT name, parent FROM categories WHERE id = '$tmp'");
      $note = $catrow['name'] . "/$note";
      $tmp = $catrow['parent'];
     }
     $note = "New scanned document $newid: $note";
     $form_note_message = trim($_POST['form_note_message']);
     if (get_magic_quotes_gpc()) $form_note_message = stripslashes($form_note_message);
     if ($form_note_message) $note .= "\n" . $form_note_message;
     // addPnote() will do its own addslashes().
     addPnote($_POST['form_pid'], $note, $userauthorized, '1',
      $_POST['form_note_type'], $_POST['form_note_to']);
    }

    $action_taken = true;
   }
  }

  if ($_POST['form_cb_forward']) {
   $form_from     = trim($_POST['form_from']);
   $form_to       = trim($_POST['form_to']);
   $form_fax      = trim($_POST['form_fax']);
   $form_message  = trim($_POST['form_message']);
   $form_finemode = $_POST['form_finemode'] ? '-m' : '-l';

   if (get_magic_quotes_gpc()) {
    $form_from    = stripslashes($form_from);
    $form_to      = stripslashes($form_to);
    $form_message = stripslashes($form_message);
   }

   // Generate a cover page using enscript.  This can be a cool thing
   // to do, as enscript is very powerful.
   //
   $tmp1 = array();
   $tmp2 = 0;
   $tmpfn1 = tempnam("/tmp", "fax1");
   $tmpfn2 = tempnam("/tmp", "fax2");
   $tmph = fopen($tmpfn1, "w");
   $cpstring = '';
   $fh = fopen("$webserver_root/custom/faxcover.txt", 'r');
   while (!feof($fh)) $cpstring .= fread($fh, 8192);
   fclose($fh);
   $cpstring = str_replace('{CURRENT_DATE}'  , date('F j, Y'), $cpstring);
   $cpstring = str_replace('{SENDER_NAME}'   , $form_from    , $cpstring);
   $cpstring = str_replace('{RECIPIENT_NAME}', $form_to      , $cpstring);
   $cpstring = str_replace('{RECIPIENT_FAX}' , $form_fax     , $cpstring);
   $cpstring = str_replace('{MESSAGE}'       , $form_message , $cpstring);
   fwrite($tmph, $cpstring);
   fclose($tmph);
   $tmp0 = exec("cd $webserver_root/custom; " . $GLOBALS['hylafax_enscript'] .
    " -o $tmpfn2 $tmpfn1", $tmp1, $tmp2);
   if ($tmp2) {
    $info_msg .= "enscript returned $tmp2: $tmp0 ";
   }
   unlink($tmpfn1);

   // Send the fax as the cover page followed by the selected pages.
   $info_msg .= mergeTiffs();
   $tmp0 = exec("sendfax -A -n $form_finemode -d " .
    escapeshellarg($form_fax) . " $tmpfn2 '$faxcache/temp.tif'",
    $tmp1, $tmp2);
   if ($tmp2) {
    $info_msg .= "sendfax returned $tmp2: $tmp0 ";
   }
   unlink($tmpfn2);

   $action_taken = true;
  }

  if ($_POST['form_cb_delete'] && !$info_msg) {

   // Delete the tiff file, with archiving if desired.
   if ($GLOBALS['hylafax_archdir'] && $mode == 'fax') {
    rename($filepath, $GLOBALS['hylafax_archdir'] . '/' . $filename);
   } else {
    unlink($filepath);
   }

   // Erase its cache.
   if (is_dir($faxcache)) {
    $dh = opendir($faxcache);
    while (($tmp = readdir($dh)) !== false) {
     if (is_file("$faxcache/$tmp")) unlink("$faxcache/$tmp");
    }
    closedir($dh);
    rmdir($faxcache);
   }

   $action_taken = true;
  }

  if (!$action_taken && !$info_msg)
   $info_msg = xl('You did not choose any actions.');

  // Close this window and refresh the fax list.
  echo "<html>\n<body>\n<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " if (!opener.closed && opener.refreshme) opener.refreshme();\n";
  echo " window.close();\n";
  echo "</script>\n</body>\n</html>\n";
  exit();
 }

 // If we get this far then we are displaying the form.

 // If the image cache does not yet exist for this fax, build it.
 // This will contain a .tif image as well as a .jpg image for each page.
 if (! is_dir($faxcache)) {
  $tmp0 = exec("mkdir -p '$faxcache'", $tmp1, $tmp2);
  if ($tmp2) die("mkdir returned $tmp2: $tmp0");
  if (strtolower($ext) != '.tif') {
   // convert's default density for PDF-to-TIFF conversion is 72 dpi which is
   // not very good, so we upgrade it to "fine mode" fax quality.  It's really
   // better and faster if the scanner produces TIFFs instead of PDFs.
   $tmp0 = exec("convert -density 203x196 '$filepath' '$faxcache/deleteme.tif'", $tmp1, $tmp2);
   if ($tmp2) die("convert returned $tmp2: $tmp0");
   $tmp0 = exec("cd '$faxcache'; tiffsplit 'deleteme.tif'; rm -f 'deleteme.tif'", $tmp1, $tmp2);
   if ($tmp2) die("tiffsplit/rm returned $tmp2: $tmp0");
  } else {
   $tmp0 = exec("cd '$faxcache'; tiffsplit '$filepath'", $tmp1, $tmp2);
   if ($tmp2) die("tiffsplit returned $tmp2: $tmp0");
  }
  $tmp0 = exec("cd '$faxcache'; mogrify -resize 750x970 -format jpg *.tif", $tmp1, $tmp2);
  if ($tmp2) die("mogrify returned $tmp2: $tmp0; ext is '$ext'; filepath is '$filepath'");
 }

 // Get the categories list.
 $categories = array();
 getKittens(0, '', $categories);

 // Get the users list.
 $ures = sqlStatement("SELECT username, fname, lname FROM users " .
  "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
  "ORDER BY lname, fname");
?>
<html>
<head>
<title><?php xl('Dispatch Received Document','e'); ?></title>
<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>

<style>

body, td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

body {
 padding: 0.2em 1em 1em 1em;
}

.itemtitle {
 font-weight: bold;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin-left: 2em;
 padding: 1em;
}

</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>

<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function divclick(cb, divid) {
  var divstyle = document.getElementById(divid).style;
  if (cb.checked) {
   divstyle.display = 'block';
  } else {
   divstyle.display = 'none';
  }
  return true;
 }

 // This is for callback by the find-patient popup.
 function setpatient(pid, lname, fname, dob) {
  var f = document.forms[0];
  f.form_patient.value = lname + ', ' + fname;
  f.form_pid.value = pid;
 }

 // This invokes the find-patient popup.
 function sel_patient() {
  dlgopen('../main/calendar/find_patient_popup.php', '_blank', 500, 400);
 }

 // Check for errors when the form is submitted.
 function validate() {
  var f = document.forms[0];

  if (f.form_cb_copy.checked) {
   if (! f.form_pid.value) {
    alert('You have not selected a patient!');
    return false;
   }
  }

  if (f.form_cb_forward.checked) {
   var s = f.form_fax.value;
   if (! s) {
    alert('A fax number is required!');
    return false;
   }
   var digcount = 0;
   for (var i = 0; i < s.length; ++i) {
    var c = s.charAt(i);
    if (c >= '0' && c <= '9') {
     ++digcount;
    }
    else if (digcount == 0 || c != '-') {
     alert('Invalid character(s) in fax number!');
     return false;
    }
   }
   if (digcount == 7) {
    if (s.charAt(0) < '2') {
     alert('Local phone number starts with an invalid digit!');
     return false;
    }
   }
   else if (digcount == 11) {
    if (s.charAt(0) != '1') {
     alert('11-digit number must begin with 1!');
     return false;
    }
   }
   else if (digcount == 10) {
    if (s.charAt(0) < '2') {
     alert('10-digit number starts with an invalid digit!');
     return false;
    }
    f.form_fax.value = '1' + s;
   }
   else {
    alert('Invalid number of digits in fax telephone number!');
    return false;
   }
  }

  if (f.form_cb_copy.checked || f.form_cb_forward.checked) {
   var check_count = 0;
   for (var i = 0; i < f.elements.length; ++i) {
    if (f.elements[i].name == 'form_images[]' && f.elements[i].checked)
     ++check_count;
   }
   if (check_count == 0) {
    alert('No pages have been selected!');
    return false;
   }
  }

  return true;
 }

</script>

</head>

<body <?php echo $top_bg_line;?> onunload='imclosing()'>

<center><h2><?php xl('Dispatch Received Document','e'); ?></h2></center>

<form method='post' name='theform'
 action='fax_dispatch.php?<?php echo ($mode == 'fax') ? 'file' : 'scan'; ?>=<?php echo $filename ?>'
 onsubmit='return validate()'>

<p><input type='checkbox' name='form_cb_copy' value='1'
 onclick='return divclick(this,"div_copy");' />
<b><?php xl('Copy Pages to Patient Chart','e'); ?></b></p>

<div id='div_copy' class='section' style='display:none;'>
 <table>
  <tr>
   <td class='itemtitle' width='1%' nowrap><?php xl('Patient','e'); ?></td>
   <td>
    <input type='text' size='10' name='form_patient' style='width:100%'
     value=' (<?php xl('Click to select'); ?>)' onclick='sel_patient()'
     title='Click to select patient' readonly />
    <input type='hidden' name='form_pid' value='0' />
   </td>
  </tr>
  <tr>
   <td class='itemtitle' nowrap><?php xl('Category','e'); ?></td>
   <td>
    <select name='form_category' style='width:100%'>
<?php
 foreach ($categories as $catkey => $catname) {
  echo "     <option value='$catkey'";
  echo ">$catname</option>\n";
 }
?>
    </select>
   </td>
  </tr>
  <tr>
   <td class='itemtitle' nowrap><?php xl('Filename','e'); ?></td>
   <td>
    <input type='text' size='10' name='form_filename' style='width:100%'
     value='<?php  echo "$filebase.pdf" ?>'
     title='Name for this document in the patient chart' />
   </td>
  </tr>
  <tr>
   <td colspan='2' style='padding-top:0.5em;'>
    <input type='checkbox' name='form_cb_note' value='1'
     onclick='return divclick(this,"div_note");' />
    <b><?php xl('Create Patient Note','e'); ?></b>
    <div id='div_note' class='section' style='display:none;margin-top:0.5em;'>
     <table>
      <tr>
       <td class='itemtitle' width='1%' nowrap><?php xl('Type','e'); ?></td>
       <td>
        <select name='form_note_type' style='width:100%'>
<?php
 foreach ($patient_note_types as $value) {
  echo "    <option value='$value'";
  echo ">$value</option>\n";
 }
?>
        </select>
       </td>
      </tr>
      <tr>
       <td class='itemtitle' width='1%' nowrap>To</td>
       <td>
        <select name='form_note_to' style='width:100%'>
<?php
 while ($urow = sqlFetchArray($ures)) {
  echo "         <option value='" . $urow['username'] . "'";
  echo ">" . $urow['lname'];
  if ($urow['fname']) echo ", " . $urow['fname'];
  echo "</option>\n";
 }
?>
         <option value=''>** <?php  xl('Close','e'); ?> **</option>
        </select>
       </td>
      </tr>
      <tr>
       <td class='itemtitle' nowrap><?php xl('Message','e'); ?></td>
       <td>
        <textarea name='form_note_message' rows='3' cols='30' style='width:100%'
         title='Your comments' /></textarea>
       </td>
      </tr>
     </table>
    </div><!-- end div_note -->
   </td>
  </tr>
 </table>
</div><!-- end div_copy -->

<p><input type='checkbox' name='form_cb_forward' value='1'
 onclick='return divclick(this,"div_forward");' />
<b><?php xl('Forward Pages via Fax','e'); ?></b></p>

<div id='div_forward' class='section' style='display:none;'>
 <table>
  <tr>
   <td class='itemtitle' width='1%' nowrap><?php xl('From','e'); ?></td>
   <td>
    <input type='text' size='10' name='form_from' style='width:100%'
     title='Type your name here' />
   </td>
  </tr>
  <tr>
   <td class='itemtitle' nowrap><?php xl('To','e'); ?></td>
   <td>
    <input type='text' size='10' name='form_to' style='width:100%'
     title='Type the recipient name here' />
   </td>
  </tr>
  <tr>
   <td class='itemtitle' nowrap><?php xl('Fax','e'); ?></td>
   <td>
    <input type='text' size='10' name='form_fax' style='width:100%'
     title='The fax phone number to send this to' />
   </td>
  </tr>
  <tr>
   <td class='itemtitle' nowrap><?php xl('Message','e'); ?></td>
   <td>
    <textarea name='form_message' rows='3' cols='30' style='width:100%'
     title='Your comments to include with this message' /></textarea>
   </td>
  </tr>
  <tr>
   <td class='itemtitle' nowrap><?php xl('Quality','e'); ?></td>
   <td>
    <input type='radio' name='form_finemode' value='' /><?php xl('Normal','e'); ?> &nbsp;
    <input type='radio' name='form_finemode' value='1' checked /><?php xl('Fine','e'); ?> &nbsp;
   </td>
  </tr>
 </table>
</div><!-- end div_forward -->

<p><input type='checkbox' name='form_cb_delete' value='1' />
<b><?php xl('Delete Document from Queue','e'); ?></b></p>

<center>
<p>
<input type='submit' name='form_save' value='<?php xl('OK'); ?>' />
&nbsp; &nbsp;
<input type='button' value='<?php xl('Cancel'); ?>' onclick='window.close()' />
</p>

<p><br /><b><?php xl('Please select the desired pages to copy or forward:','e'); ?></b></p>
<table>

<?php
 $dh = opendir($faxcache);
 if (! $dh) die("Cannot read $faxcache");
 $page = 0;
 while (false !== ($jfname = readdir($dh))) {
  if (preg_match("/^(.*)\.jpg/", $jfname, $matches)) {
   ++$page;
   $jfnamebase = $matches[1];
   echo " <tr>\n";
   echo "  <td valign='top'>\n";
   echo "   <img src='../../faxcache/$mode/$filebase/$jfname' />\n";
   echo "  </td>\n";
   echo "  <td align='center' valign='top'>\n";
   echo "   <input type='checkbox' name='form_images[]' value='$jfnamebase' checked />\n";
   echo "   <br />$page\n";
   echo "  </td>\n";
   echo " </tr>\n";
  }
 }
 closedir($dh);
?>

</table>
</center>
</form>

</body>
</html>
