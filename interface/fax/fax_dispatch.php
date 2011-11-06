<?php
// Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/gprelations.inc.php");

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
$faxcache = $GLOBALS['OE_SITE_DIR'] . "/faxcache/$mode/$filebase";

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
  // form_images are the checkboxes to the right of the images.
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
    $docdir = $GLOBALS['OE_SITE_DIR'] . "/documents/$patient_id";
    exec("mkdir -p '$docdir'");

    // If copying to patient documents...
    //
    if ($_POST['form_cb_copy_type'] == 1) {
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
      $docdate = fixDate($_POST['form_docdate']);

      // Create the target PDF.  Note that we are relying on the .tif files for
      // the individual pages to already exist in the faxcache directory.
      //
      $info_msg .= mergeTiffs();
      // The -j option here requires that libtiff is configured with libjpeg.
      // It could be omitted, but the output PDFs would then be quite large.
      $tmp0 = exec("tiff2pdf -j -p letter -o '$target' '$faxcache/temp.tif'", $tmp1, $tmp2);

      if ($tmp2) {
        $info_msg .= "tiff2pdf returned $tmp2: $tmp0 ";
      }
      else {
        $newid = generate_id();
        $fsize = filesize($target);
        $catid = (int) $_POST['form_category'];
        // Update the database.
        $query = "INSERT INTO documents ( " .
          "id, type, size, date, url, mimetype, foreign_id, docdate" .
          " ) VALUES ( " .
          "'$newid', 'file_url', '$fsize', NOW(), 'file://$target', " .
          "'application/pdf', $patient_id, '$docdate' " .
          ")";
        sqlStatement($query);
        $query = "INSERT INTO categories_to_documents ( " .
          "category_id, document_id" .
          " ) VALUES ( " .
          "'$catid', '$newid' " .
          ")";
        sqlStatement($query);
      } // end not error

      // If we are posting a note...
      if ($_POST['form_cb_note'] && !$info_msg) {
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
        $noteid = addPnote($_POST['form_pid'], $note, $userauthorized, '1',
          $_POST['form_note_type'], $_POST['form_note_to']);
        // Link the new patient note to the document.
        setGpRelation(1, $newid, 6, $noteid);
      } // end post patient note
    } // end copy to documents

    // Otherwise creating a scanned encounter note...
    //
    else {

      // Get desired $encounter_id.
      $encounter_id = 0;
      if (empty($_POST['form_copy_sn_visit'])) {
        $info_msg .= "This patient has no visits! ";
      } else {
        $encounter_id = 0 + $_POST['form_copy_sn_visit'];
      }

      if (!$info_msg) {
        // Merge the selected pages.
        $info_msg .= mergeTiffs();
        $tmp_name = "$faxcache/temp.tif";
      }

      if (!$info_msg) {
        // The following is cloned from contrib/forms/scanned_notes/new.php:
        //
        $query = "INSERT INTO form_scanned_notes ( " .
          "notes " .
          ") VALUES ( " .
          "'" . $_POST['form_copy_sn_comments'] . "' " .
          ")";
        $formid = sqlInsert($query);
        addForm($encounter_id, "Scanned Notes", $formid, "scanned_notes",
          $patient_id, $userauthorized);
        //
        $imagedir = $GLOBALS['OE_SITE_DIR'] . "/documents/$patient_id/encounters";
        $imagepath = "$imagedir/${encounter_id}_$formid.jpg";
        echo $imagedir;
		die;
        if (! is_dir($imagedir)) {
		  $tmp0 = exec('mkdir -p "'.$imagedir.'"', $tmp1, $tmp2);
          if ($tmp2) die("mkdir returned $tmp2: $tmp0");
          exec("touch '$imagedir/index.html'");
        }
        if (is_file($imagepath)) unlink($imagepath);
        // TBD: There may be a faster way to create this file, given that
        // we already have a jpeg for each page in faxcache.
        $cmd = "convert -resize 800 -density 96 '$tmp_name' -append '$imagepath'";
        $tmp0 = exec($cmd, $tmp1, $tmp2);
        if ($tmp2) die("\"$cmd\" returned $tmp2: $tmp0");
      }

      // If we are posting a patient note...
      if ($_POST['form_cb_note'] && !$info_msg) {
        $note = "New scanned encounter note for visit on " . substr($erow['date'], 0, 10);
        $form_note_message = trim($_POST['form_note_message']);
        if (get_magic_quotes_gpc()) $form_note_message = stripslashes($form_note_message);
        if ($form_note_message) $note .= "\n" . $form_note_message;
        // addPnote() will do its own addslashes().
        addPnote($patient_id, $note, $userauthorized, '1',
          $_POST['form_note_type'], $_POST['form_note_to']);
      } // end post patient note
    }

    $action_taken = true;

  } // end copy to chart

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
    $fh = fopen($GLOBALS['OE_SITE_DIR'] . "/faxcover.txt", 'r');
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
  } // end forward

  $form_cb_delete = $_POST['form_cb_delete'];

  // If deleting selected, do it and then check if any are left.
  if ($form_cb_delete == '1' && !$info_msg) {
    foreach ($_POST['form_images'] as $inbase) {
      unlink("$faxcache/$inbase.jpg");
      $action_taken = true;
    }
    // Check if any .jpg files remain... if not we'll clean up.
    if ($action_taken) {
      $dh = opendir($faxcache);
      if (! $dh) die("Cannot read $faxcache");
      $form_cb_delete = '2';
      while (false !== ($jfname = readdir($dh))) {
        if (preg_match('/\.jpg$/', $jfname)) $form_cb_delete = '1';
      }
      closedir($dh);
    }
  } // end delete 1

  if ($form_cb_delete == '2' && !$info_msg) {
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
  } // end delete 2

  if (!$action_taken && !$info_msg)
    $info_msg = xl('You did not choose any actions.');

  if ($info_msg || $form_cb_delete != '1') {
    // Close this window and refresh the fax list.
    echo "<html>\n<body>\n<script language='JavaScript'>\n";
    if ($info_msg) echo " alert('$info_msg');\n";
    echo " if (!opener.closed && opener.refreshme) opener.refreshme();\n";
    echo " window.close();\n";
    echo "</script>\n</body>\n</html>\n";
    exit();
  }
} // end submit logic

// If we get this far then we are displaying the form.

// Find out if the scanned_notes form is installed and active.
//
$tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE " .
  "directory LIKE 'scanned_notes' AND state = 1 AND sql_run = 1");
$using_scanned_notes = $tmp['count'];

// If the image cache does not yet exist for this fax, build it.
// This will contain a .tif image as well as a .jpg image for each page.
//
echo $faxcache;

if (! is_dir($faxcache)) {
  $tmp0 = exec('mkdir -p "'.$faxcache.'"', $tmp1, $tmp2);
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
<?php if (function_exists(html_header_show)) html_header_show(); ?>
<title><?php xl('Dispatch Received Document','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>

td, input, select, textarea {
 font-size: 10pt;
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

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/js/jquery-1.2.2.min.js"></script>

<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 function divclick(cb, divid) {
  var divstyle = document.getElementById(divid).style;
  if (cb.checked) {
   if (divid == 'div_copy_doc') {
    document.getElementById('div_copy_sn').style.display = 'none';
   }
   else if (divid == 'div_copy_sn') {
    document.getElementById('div_copy_doc').style.display = 'none';
   }
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
<?php if ($using_scanned_notes) { ?>
  // This loads the patient's list of recent encounters:
  f.form_copy_sn_visit.options.length = 0;
  f.form_copy_sn_visit.options[0] = new Option('Loading...', '0');
  $.getScript("fax_dispatch_newpid.php?p=" + pid);
<?php } ?>
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

  top.restoreSession();
  return true;
 }

 function allCheckboxes(issel) {
  var f = document.forms[0];
  for (var i = 0; i < f.elements.length; ++i) {
   if (f.elements[i].name == 'form_images[]') f.elements[i].checked = issel;
  }
 }

</script>

</head>

<body class="body_top" onunload='imclosing()'>

<center><h2><?php xl('Dispatch Received Document','e'); ?></h2></center>

<form method='post' name='theform'
 action='fax_dispatch.php?<?php echo ($mode == 'fax') ? 'file' : 'scan'; ?>=<?php echo $filename ?>' onsubmit='return validate()'>

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
   <td colspan='2' style='padding-top:0.5em;'>
    <input type='radio' name='form_cb_copy_type' value='1'
     onclick='return divclick(this,"div_copy_doc");' checked />
    <b><?php xl('Patient Document','e'); ?></b>&nbsp;
<?php if ($using_scanned_notes) { ?>
    <input type='radio' name='form_cb_copy_type' value='2'
     onclick='return divclick(this,"div_copy_sn");' />
    <b><?php xl('Scanned Encounter Note','e'); ?></b>
<?php } ?>
    <div id='div_copy_doc' class='section' style='margin-top:0.5em;'>
     <table width='100%'>
      <tr>
       <td class='itemtitle' nowrap><?php xl('Category','e'); ?></td>
       <td>
        <select name='form_category' style='width:100%'>
<?php
foreach ($categories as $catkey => $catname) {
  echo "         <option value='$catkey'";
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
       <td class='itemtitle' nowrap><?php xl('Document Date','e'); ?></td>
       <td>
        <input type='text' size='10' name='form_docdate' id='form_docdate'
        value='<?php echo date('Y-m-d'); ?>'
        title='<?php xl('yyyy-mm-dd date associated with this document','e'); ?>'
        onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
        <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
        id='img_docdate' border='0' alt='[?]' style='cursor:pointer'
        title='<?php xl('Click here to choose a date','e'); ?>' />
       </td>
      </tr>
     </table>
    </div><!-- end div_copy_doc -->
    <div id='div_copy_sn' class='section' style='display:none;margin-top:0.5em;'>
     <table width='100%'>
      <tr>
       <td class='itemtitle' width='1%' nowrap><?php xl('Visit Date','e'); ?></td>
       <td>
        <select name='form_copy_sn_visit' style='width:100%'>
        </select>
       </td>
      </tr>
      <tr>
       <td class='itemtitle' width='1%' nowrap><?php xl('Comments','e'); ?></td>
       <td>
        <textarea name='form_copy_sn_comments' rows='3' cols='30' style='width:100%'
         title='Comments associated with this scanned note'
         /></textarea>
       </td>
      </tr>
     </table>
    </div><!-- end div_copy_sn -->
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
        <?php
         // Added 6/2009 by BM to incorporate the patient notes into the list_options listings
         generate_form_field(array('data_type'=>1,'field_id'=>'note_type','list_id'=>'note_type','empty_title'=>'SKIP'), '');
        ?>
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

<p><b><?php xl('Delete Pages','e'); ?>:</b>&nbsp;
<input type='radio' name='form_cb_delete' value='2' />All&nbsp;
<input type='radio' name='form_cb_delete' value='1' checked />Selected&nbsp;
<input type='radio' name='form_cb_delete' value='0' />None
</p>

<center>
<p>
<input type='submit' name='form_save' value='<?php xl('OK','e'); ?>' />
&nbsp; &nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick='window.close()' />
&nbsp; &nbsp;
<input type='button' value='<?php xl('Select All','e'); ?>' onclick='allCheckboxes(true)' />
&nbsp; &nbsp;
<input type='button' value='<?php xl('Clear All','e'); ?>' onclick='allCheckboxes(false)' />
</p>

<p><br /><b><?php xl('Please select the desired pages to copy or forward:','e'); ?></b></p>
<table>

<?php
$dh = opendir($faxcache);
if (! $dh) die("Cannot read $faxcache");
$jpgarray = array();
while (false !== ($jfname = readdir($dh))) {
  if (preg_match("/^(.*)\.jpg/", $jfname, $matches)) {
    $jpgarray[$matches[1]] = $jfname;
  }
}
closedir($dh);
// readdir does not read in any particular order, we must therefore sort
// by filename so the display order matches the original document.
ksort($jpgarray);
$page = 0;
foreach ($jpgarray as $jfnamebase => $jfname) {
  ++$page;
  echo " <tr>\n";
  echo "  <td valign='top'>\n";
  echo "   <img src='../../sites/" . $_SESSION['site_id'] . "/faxcache/$mode/$filebase/$jfname' />\n";
  echo "  </td>\n";
  echo "  <td align='center' valign='top'>\n";
  echo "   <input type='checkbox' name='form_images[]' value='$jfnamebase' checked />\n";
  echo "   <br />$page\n";
  echo "  </td>\n";
  echo " </tr>\n";
}
?>

</table>
</center>
</form>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_docdate", ifFormat:"%Y-%m-%d", button:"img_docdate"});
</script>

</body>
</html>
