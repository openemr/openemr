<?php

/**
 * fax dispatch
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/gprelations.inc.php");

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if ($_GET['file']) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $mode = 'fax';
    $filename = $_GET['file'];

    // ensure the file variable has no illegal characters
    check_file_dir_name($filename);

    $filepath = $GLOBALS['hylafax_basedir'] . '/recvq/' . $filename;
} elseif ($_GET['scan']) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $mode = 'scan';
    $filename = $_GET['scan'];

    // ensure the file variable has no illegal characters
    check_file_dir_name($filename);

    $filepath = $GLOBALS['scanner_output_directory'] . '/' . $filename;
} else {
    die("No filename was given.");
}

$ext = substr($filename, strrpos($filename, '.'));
$filebase = basename("/$filename", $ext);
$faxcache = $GLOBALS['OE_SITE_DIR'] . "/faxcache/$mode/$filebase";

$info_msg = "";

// This function builds an array of document categories recursively.
// Kittens are the children of cats, you know.  :-)getKittens
//
function getKittens($catid, $catstring, &$categories)
{
    $cres = sqlStatement("SELECT id, name FROM categories " .
    "WHERE parent = ? ORDER BY name", array($catid));
    $childcount = 0;
    while ($crow = sqlFetchArray($cres)) {
        ++$childcount;
        getKittens($crow['id'], ($catstring ? "$catstring / " : "") .
        ($catid ? $crow['name'] : ''), $categories);
    }

  // If no kitties, then this is a leaf node and should be listed.
    if (!$childcount) {
        $categories[$catid] = $catstring;
    }
}

// This merges the tiff files for the selected pages into one tiff file.
//
function mergeTiffs()
{
    global $faxcache;
    $msg = '';
    $inames = '';
    $tmp1 = array();
    $tmp2 = 0;
  // form_images are the checkboxes to the right of the images.
    foreach ($_POST['form_images'] as $inbase) {
        check_file_dir_name($inbase);
        $inames .= ' ' . escapeshellarg("$inbase.tif");
    }

    if (!$inames) {
        die(xlt("Internal error - no pages were selected!"));
    }

    $tmp0 = exec("cd " . escapeshellarg($faxcache) . "; tiffcp $inames temp.tif", $tmp1, $tmp2);
    if ($tmp2) {
        $msg .= "tiffcp returned $tmp2: $tmp0 ";
    }

    return $msg;
}

// If we are submitting...
//
if ($_POST['form_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $action_taken = false;
    $tmp1 = array();
    $tmp2 = 0;

    if ($_POST['form_cb_copy']) {
        $patient_id = (int) $_POST['form_pid'];
        if (!$patient_id) {
            die(xlt('Internal error - patient ID was not provided!'));
        }

        // Compute the name of the target directory and make sure it exists.
        $docdir = $GLOBALS['OE_SITE_DIR'] . "/documents/" . check_file_dir_name($patient_id);
        exec("mkdir -p " . escapeshellarg($docdir));

        // If copying to patient documents...
        //
        if ($_POST['form_cb_copy_type'] == 1) {
            // Compute a target filename that does not yet exist.
            $ffname = check_file_dir_name(trim($_POST['form_filename']));
            $i = strrpos($ffname, '.');
            if ($i) {
                $ffname = trim(substr($ffname, 0, $i));
            }

            if (!$ffname) {
                $ffname = $filebase;
            }

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
            $tmp0 = exec("tiff2pdf -j -p letter -o " . escapeshellarg($target) . " " . escapeshellarg($faxcache . '/temp.tif'), $tmp1, $tmp2);

            if ($tmp2) {
                $info_msg .= "tiff2pdf returned $tmp2: $tmp0 ";
            } else {
                $newid = generate_id();
                $fsize = filesize($target);
                $catid = (int) $_POST['form_category'];
                // Update the database.
                $query = "INSERT INTO documents ( " .
                "id, type, size, date, url, mimetype, foreign_id, docdate" .
                " ) VALUES ( " .
                "?, 'file_url', ?, NOW(), ?, " .
                "'application/pdf', ?, ? " .
                ")";
                sqlStatement($query, array($newid, $fsize, 'file://' . $target, $patient_id, $docdate));
                $query = "INSERT INTO categories_to_documents ( " .
                "category_id, document_id" .
                " ) VALUES ( " .
                "?, ? " .
                ")";
                sqlStatement($query, array($catid, $newid));
            } // end not error

            // If we are posting a note...
            if ($_POST['form_cb_note'] && !$info_msg) {
                // Build note text in a way that identifies the new document.
                // See pnotes_full.php which uses this to auto-display the document.
                $note = "$ffname$ffmod$ffsuff";
                for ($tmp = $catid; $tmp;) {
                    $catrow = sqlQuery("SELECT name, parent FROM categories WHERE id = ?", array($tmp));
                    $note = $catrow['name'] . "/$note";
                    $tmp = $catrow['parent'];
                }

                $note = "New scanned document $newid: $note";
                $form_note_message = trim($_POST['form_note_message']);
                if ($form_note_message) {
                    $note .= "\n" . $form_note_message;
                }

                $noteid = addPnote(
                    $_POST['form_pid'],
                    $note,
                    $userauthorized,
                    '1',
                    $_POST['form_note_type'],
                    $_POST['form_note_to']
                );
                // Link the new patient note to the document.
                setGpRelation(1, $newid, 6, $noteid);
            } // end post patient note
        } else { // end copy to documents
            // Otherwise creating a scanned encounter note...
            // Get desired $encounter_id.
            $encounter_id = 0;
            if (empty($_POST['form_copy_sn_visit'])) {
                $info_msg .= "This patient has no visits! ";
            } else {
                $encounter_id = (int) $_POST['form_copy_sn_visit'];
            }

            if (!$info_msg) {
                // Merge the selected pages.
                $info_msg .= mergeTiffs();
                $tmp_name = "$faxcache/temp.tif";
            }

            if (!$info_msg) {
                // The following is cloned from contrib/forms/scanned_notes/new.php:
                //
                $query = "INSERT INTO form_scanned_notes ( notes ) VALUES ( ? )";
                $formid = sqlInsert($query, array($_POST['form_copy_sn_comments']));
                addForm(
                    $encounter_id,
                    "Scanned Notes",
                    $formid,
                    "scanned_notes",
                    $patient_id,
                    $userauthorized
                );
                //
                $imagedir = $GLOBALS['OE_SITE_DIR'] . "/documents/" . check_file_dir_name($patient_id) . "/encounters";
                $imagepath = "$imagedir/" . check_file_dir_name($encounter_id) . "_" . check_file_dir_name($formid) . ".jpg";
                if (! is_dir($imagedir)) {
                        $tmp0 = exec('mkdir -p ' . escapeshellarg($imagedir), $tmp1, $tmp2);
                    if ($tmp2) {
                        die("mkdir returned " . text($tmp2) . ": " . text($tmp0));
                    }

                        exec("touch " . escapeshellarg($imagedir . "/index.html"));
                }

                if (is_file($imagepath)) {
                    unlink($imagepath);
                }

                // TBD: There may be a faster way to create this file, given that
                // we already have a jpeg for each page in faxcache.
                $cmd = "convert -resize 800 -density 96 " . escapeshellarg($tmp_name) . " -append " . escapeshellarg($imagepath);
                $tmp0 = exec($cmd, $tmp1, $tmp2);
                if ($tmp2) {
                    die("\"" . text($cmd) . "\" returned " . text($tmp2) . ": " . text($tmp0));
                }
            }

            // If we are posting a patient note...
            if ($_POST['form_cb_note'] && !$info_msg) {
                $note = "New scanned encounter note for visit on " . substr($erow['date'], 0, 10);
                $form_note_message = trim($_POST['form_note_message']);
                if ($form_note_message) {
                    $note .= "\n" . $form_note_message;
                }

                addPnote(
                    $patient_id,
                    $note,
                    $userauthorized,
                    '1',
                    $_POST['form_note_type'],
                    $_POST['form_note_to']
                );
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
        while (!feof($fh)) {
            $cpstring .= fread($fh, 8192);
        }

        fclose($fh);
        $cpstring = str_replace('{CURRENT_DATE}', date('F j, Y'), $cpstring);
        $cpstring = str_replace('{SENDER_NAME}', $form_from, $cpstring);
        $cpstring = str_replace('{RECIPIENT_NAME}', $form_to, $cpstring);
        $cpstring = str_replace('{RECIPIENT_FAX}', $form_fax, $cpstring);
        $cpstring = str_replace('{MESSAGE}', $form_message, $cpstring);
        fwrite($tmph, $cpstring);
        fclose($tmph);
        $tmp0 = exec("cd " . escapeshellarg($webserver_root . '/custom') . "; " . escapeshellcmd((new CryptoGen())->decryptStandard($GLOBALS['more_secure']['hylafax_enscript'])) .
        " -o " . escapeshellarg($tmpfn2) . " " . escapeshellarg($tmpfn1), $tmp1, $tmp2);
        if ($tmp2) {
              $info_msg .= "enscript returned $tmp2: $tmp0 ";
        }

        unlink($tmpfn1);

        // Send the fax as the cover page followed by the selected pages.
        $info_msg .= mergeTiffs();
        $tmp0 = exec(
            "sendfax -A -n " . escapeshellarg($form_finemode) . " -d " .
            escapeshellarg($form_fax) . " " . escapeshellarg($tmpfn2) . " " . escapeshellarg($faxcache . '/temp.tif'),
            $tmp1,
            $tmp2
        );
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
            check_file_dir_name($inbase);
            unlink($faxcache . "/" . $inbase . ".jpg");
            $action_taken = true;
        }

        // Check if any .jpg files remain... if not we'll clean up.
        if ($action_taken) {
            $dh = opendir($faxcache);
            if (! $dh) {
                die("Cannot read " . text($faxcache));
            }

            $form_cb_delete = '2';
            while (false !== ($jfname = readdir($dh))) {
                if (preg_match('/\.jpg$/', $jfname)) {
                    $form_cb_delete = '1';
                }
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
                if (is_file("$faxcache/$tmp")) {
                    unlink("$faxcache/$tmp");
                }
            }

            closedir($dh);
            rmdir($faxcache);
        }

        $action_taken = true;
    } // end delete 2

    if (!$action_taken && !$info_msg) {
        $info_msg = xl('You did not choose any actions.');
    }

    if ($info_msg || $form_cb_delete != '1') {
        // Close this window and refresh the fax list.
        echo "<html>\n<body>\n<script>\n";
        if ($info_msg) {
            echo " alert('" . addslashes($info_msg) . "');\n";
        }

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
if (! is_dir($faxcache)) {
    $tmp0 = exec('mkdir -p ' . escapeshellarg($faxcache), $tmp1, $tmp2);
    if ($tmp2) {
        die("mkdir returned " . text($tmp2) . ": " . text($tmp0));
    }

    if (strtolower($ext) != '.tif') {
        // convert's default density for PDF-to-TIFF conversion is 72 dpi which is
        // not very good, so we upgrade it to "fine mode" fax quality.  It's really
        // better and faster if the scanner produces TIFFs instead of PDFs.
        $tmp0 = exec("convert -density 203x196 " . escapeshellarg($filepath) . " " . escapeshellarg($faxcache . '/deleteme.tif'), $tmp1, $tmp2);
        if ($tmp2) {
            die("convert returned " . text($tmp2) . ": " . text($tmp0));
        }

        $tmp0 = exec("cd " . escapeshellarg($faxcache) . "; tiffsplit 'deleteme.tif'; rm -f 'deleteme.tif'", $tmp1, $tmp2);
        if ($tmp2) {
            die("tiffsplit/rm returned " . text($tmp2) . ": " . text($tmp0));
        }
    } else {
        $tmp0 = exec("cd " . escapeshellarg($faxcache) . "; tiffsplit " . escapeshellarg($filepath), $tmp1, $tmp2);
        if ($tmp2) {
            die("tiffsplit returned " . text($tmp2) . ": " . text($tmp0));
        }
    }

    $tmp0 = exec("cd " . escapeshellarg($faxcache) . "; mogrify -resize 750x970 -format jpg *.tif", $tmp1, $tmp2);
    if ($tmp2) {
        die("mogrify returned " . text($tmp2) . ": " . text($tmp0) . "; ext is '" . text($ext) . "'; filepath is '" . text($filepath) . "'");
    }
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

    <?php Header::setupHeader(['opener', 'datetime-picker']);?>
    <title><?php echo xlt('Dispatch Received Document'); ?></title>

<script>

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
  $.getScript("fax_dispatch_newpid.php?p=" + encodeURIComponent(pid) + "&csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>);
<?php } ?>
 }

 // This invokes the find-patient popup.
 function sel_patient() {
  dlgopen('../main/calendar/find_patient_popup.php', '_blank', 750, 550, false, 'Select Patient');
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

    $(function () {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
</script>

</head>

<body class="body_top">
<h2 class="text-center"><?php echo xlt('Dispatch Received Document'); ?></h2>

<form method='post' name='theform'
 action='fax_dispatch.php?<?php echo ($mode == 'fax') ? 'file' : 'scan'; ?>=<?php echo attr_url($filename); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>' onsubmit='return validate()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<p><input type='checkbox' name='form_cb_copy' value='1'
 onclick='return divclick(this,"div_copy");' />
<span class="font-weight-bold"><?php echo xlt('Copy Pages to Patient Chart'); ?></span></p>

<!-- Copy Pages to Patient Chart Section -->
<div id='div_copy' class='jumbotron' style='display:none;'>
    <!-- Patient Section -->
    <div class="form-row mt-2">
        <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Patient'); ?></label>
        <div class="col-10">
            <input type='text' size='10' name='form_patient' class='form-control bg-light'
                value=' (<?php echo xla('Click to select'); ?>)' onclick='sel_patient()'
                data-toggle='tooltip' data-placement='top'
                title='<?php echo xla('Click to select patient'); ?>' readonly />
            <input type='hidden' name='form_pid' value='0' />
        </div>
    </div>
    <!-- Patient Document Section -->
    <div class="form-row mt-2">
        <div class="col-12 col-form-label">
            <input type='radio' name='form_cb_copy_type' value='1'
                onclick='return divclick(this,"div_copy_doc");' checked />
            <label class="font-weight-bold"><?php echo xlt('Patient Document'); ?></label>
            <?php if ($using_scanned_notes) { ?>
                <input type='radio' name='form_cb_copy_type' value='2'
                    onclick='return divclick(this,"div_copy_sn");' />
                <label class="font-weight-bold"><?php echo xlt('Scanned Encounter Note'); ?></label>
            <?php } ?>
            <!-- div_copy_doc Section -->
            <div id='div_copy_doc' class='bg-secondary border rounded p-2'>
                <!-- Category Section -->
                <div class="form-row mt-2">
                    <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Category'); ?></label>
                    <div class="col-10">
                        <select name='form_category' class='form-control'>
                            <?php
                            foreach ($categories as $catkey => $catname) {
                                echo "         <option value='" . attr($catkey) . "'";
                                echo ">" . text($catname) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <!-- Filename Section -->
                <div class="form-row mt-2">
                    <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Filename'); ?></label>
                    <div class="col-10">
                        <input type='text' size='10' name='form_filename' class='form-control'
                            value='<?php echo attr($filebase) . ".pdf" ?>'
                            data-toggle='tooltip' data-placement='top'
                            title='Name for this document in the patient chart' />
                    </div>
                </div>
                <!-- Document Date Section -->
                <div class="form-row mt-2">
                    <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Document Date'); ?></label>
                    <div class="col-10">
                        <input type='text' class='datepicker form-control' size='10' name='form_docdate' id='form_docdate'
                            value='<?php echo date('Y-m-d'); ?>'
                            data-toggle='tooltip' data-placement='top'
                            title='<?php echo xla('yyyy-mm-dd date associated with this document'); ?>' />
                    </div>
                </div>
            </div>
            <!-- div_copy_sn Section -->
            <div id='div_copy_sn' class='bg-secondary border rounded p-2' style='display:none;margin-top:0.5em;'>
                <!-- Visit Date Section -->
                <div class="form-row mt-2">
                    <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Visit Date'); ?></label>
                    <div class="col-10">
                        <select name='form_copy_sn_visit' class='form-control'>
                        </select>
                    </div>
                </div>
                <!-- Comments Section -->
                <div class="form-row mt-2">
                    <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Comments'); ?></label>
                    <div class="col-10">
                        <textarea name='form_copy_sn_comments' rows='3' cols='30' class='form-control'
                            data-toggle='tooltip' data-placement='top'
                            title='Comments associated with this scanned note'>
                        </textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Create Patient Note Section -->
    <div class="form-gruop row">
        <div class="col-12 col-form-label">
            <input type='checkbox' name='form_cb_note' value='1'
                onclick='return divclick(this,"div_note");' />
            <label class="font-weight-bold"><?php echo xlt('Create Patient Note'); ?></label>
            <!-- div_note Section -->
            <div id='div_note' class='bg-secondary border rounded p-2' style='display:none;'>
                <!-- Type Section -->
                <div class="form-row mt-2">
                    <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Type'); ?></label>
                    <div class="col-10">
                        <?php
                        // Added 6/2009 by BM to incorporate the patient notes into the list_options listings
                        generate_form_field(array('data_type' => 1,'field_id' => 'note_type','list_id' => 'note_type','empty_title' => 'SKIP'), '');
                        ?>
                    </div>
                </div>
                <!-- To Section -->
                <div class="form-row mt-2">
                    <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('To'); ?></label>
                    <div class="col-10">
                    <select name='form_note_to' class='form-control'>
                        <?php
                        while ($urow = sqlFetchArray($ures)) {
                            echo "         <option value='" . attr($urow['username']) . "'";
                            echo ">" . text($urow['lname']);
                            if ($urow['fname']) {
                                echo ", " . text($urow['fname']);
                            }

                            echo "</option>\n";
                        }
                        ?>
                        <option value=''>** <?php echo xlt('Close'); ?> **</option>
                    </select>
                    </div>
                </div>
                <!-- Message Section -->
                <div class="form-row mt-2">
                    <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Message'); ?></label>
                    <div class="col-10">
                        <textarea name='form_note_message' rows='3' cols='30' class='form-control'
                            data-toggle='tooltip' data-placement='top'
                            title='Your comments'>
                        </textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<p><input type='checkbox' name='form_cb_forward' value='1'
 onclick='return divclick(this,"div_forward");' />
<span class="font-weight-bold"><?php echo xlt('Forward Pages via Fax'); ?></span></p>

<!-- Forward Pages via Fax Section -->
<div id='div_forward' class='jumbotron' style='display:none;'>
    <!-- From Section -->
    <div class="form-row mt-2">
        <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('From'); ?></label>
        <div class="col-10">
            <input type='text' size='10' name='form_from' class='form-control' data-toggle='tooltip' data-placement='top' title='Type your name here'>
        </div>
    </div>
    <!-- To Section -->
    <div class="form-row mt-2">
        <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('To{{Destination}}'); ?></label>
        <div class="col-10">
            <input type='text' size='10' name='form_to' class='form-control' data-toggle='tooltip' data-placement='top' title='Type the recipient name here'>
        </div>
    </div>
    <!-- Fax Section -->
    <div class="form-row mt-2">
        <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Fax'); ?></label>
        <div class="col-10">
            <input type='text' size='10' name='form_fax' class='form-control' data-toggle='tooltip' data-placement='top' title='The fax phone number to send this to'>
        </div>
    </div>
    <!-- Message Section -->
    <div class="form-row mt-2">
        <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Message'); ?></label>
        <div class="col-10">
            <textarea name='form_message' rows='3' cols='30' class='form-control'
                data-toggle='tooltip' data-placement='top'
                title='Your comments to include with this message'>
            </textarea>
        </div>
    </div>
    <!-- Quality Section -->
    <div class="form-row mt-2">
        <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('Quality'); ?></label>
        <div class="col-10">
            <div class="form-check form-check-inline">
                <input type='radio' class='form-check-input' name='form_finemode' value=''>
                <label class="form-check-label"><?php echo xlt('Normal'); ?></label>
            </div>
            <div class="form-check form-check-inline">
                <input type='radio' class='form-check-input' name='form_finemode' value='1' checked>
                <label class="form-check-label"><?php echo xlt('Fine'); ?></label>
            </div>
        </div>
    </div>
</div>

<div class="form-group form-inline">
    <label class="font-weight-bold"><?php echo xlt('Delete Pages'); ?>:</label>
    <div class="form-check form-check-inline">
        <input type='radio' class='form-check-input' name='form_cb_delete' value='2' />
        <label class="form-check-label">All</label>
    </div>
    <div class="form-check form-check-inline">
        <input type='radio' class='form-check-input' name='form_cb_delete' value='1' checked />
        <label class="form-check-label">Selected</label>
    </div>
    <div class="form-check form-check-inline">
        <input type='radio' class='form-check-input' name='form_cb_delete' value='0' />
        <label class="form-check-label">None</label>
    </div>
</div>

<div class="btn-group">
    <button type='submit' class='btn btn-primary btn-save' name='form_save' value='<?php echo xla('OK'); ?>'><?php echo xla('OK'); ?></button>
    <button type='button' class='btn btn-secondary btn-cancel' value='<?php echo xla('Cancel'); ?>' onclick='window.close()'><?php echo xla('Cancel'); ?></button>
    <button type='button' class='btn btn-secondary' value='<?php echo xla('Select All'); ?>' onclick='allCheckboxes(true)'><?php echo xla('Select All'); ?></button>
    <button type='button' class='btn btn-secondary' value='<?php echo xla('Clear All'); ?>' onclick='allCheckboxes(false)'><?php echo xla('Clear All'); ?></button>
</div>

<p class="mt-2 font-weight-bold"><?php echo xlt('Please select the desired pages to copy or forward:'); ?></p>
<table>

<?php
$dh = opendir($faxcache);
if (! $dh) {
    die("Cannot read " . text($faxcache));
}

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
    echo "   <img src='../../sites/" . attr($_SESSION['site_id']) . "/faxcache/" . attr($mode) . "/" . attr($filebase) . "/" . attr($jfname) . "' />\n";
    echo "  </td>\n";
    echo "  <td align='center' valign='top'>\n";
    echo "   <input type='checkbox' name='form_images[]' value='" . attr($jfnamebase) . "' checked />\n";
    echo "   <br />" . text($page) . "\n";
    echo "  </td>\n";
    echo " </tr>\n";
}
?>

</table>
</form>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
</body>
</html>
