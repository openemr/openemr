<?php

/**
 * fax dispatch
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../globals.php';

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\RequestTerminator;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FormService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

$globalsBag = OEGlobalsBag::getInstance();
$srcdir = $globalsBag->getSrcDir();

require_once "$srcdir/patient.inc.php";
require_once "$srcdir/pnotes.inc.php";
require_once "$srcdir/forms.inc.php";
require_once "$srcdir/options.inc.php";
require_once "$srcdir/gprelations.inc.php";

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = Request::createFromGlobals();
$filesystem = new Filesystem();
$userauthorized = $globalsBag->getInt('userauthorized');

// Error paths send a real HTTP status instead of die(). The default
// terminator exits nonzero for error statuses; tests can inject a
// throwing one.
$terminator = new RequestTerminator();

$site_id = $session->get('site_id');
if (!is_string($site_id) || $site_id === '') {
    $terminator->error(Response::HTTP_UNAUTHORIZED, "Site ID is missing from the session.");
}

$fileParam = $request->query->getString('file');
$scanParam = $request->query->getString('scan');

if ($fileParam !== '') {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

    $mode = 'fax';
    $filename = $fileParam;

    // ensure the file variable has no illegal characters
    check_file_dir_name($filename);

    $filepath = $globalsBag->getString('hylafax_basedir') . '/recvq/' . $filename;
} elseif ($scanParam !== '') {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

    $mode = 'scan';
    $filename = $scanParam;

    // ensure the file variable has no illegal characters
    check_file_dir_name($filename);

    $filepath = $globalsBag->getString('scanner_output_directory') . '/' . $filename;
} else {
    $terminator->error(Response::HTTP_BAD_REQUEST, "No filename was given.");
}

$dotPos = strrpos($filename, '.');
$ext = $dotPos === false ? '' : substr($filename, $dotPos);
$filebase = basename("/$filename", $ext);
$faxcache = $globalsBag->getString('OE_SITE_DIR') . "/faxcache/$mode/$filebase";

$info_msg = "";

// Run an external command with Symfony Process so arguments are escaped
// safely, returning the finished Process for exit-code and output checks.
/** @param list<string> $command */
$runProcess = static function (array $command, ?string $cwd = null): Process {
    $process = new Process($command, $cwd);
    $process->run();
    return $process;
};

// Format a failed Process as "exitcode: output" for error messages.
$processError = (static fn(Process $process): string => (string) $process->getExitCode() . ': ' . trim($process->getOutput() . ' ' . $process->getErrorOutput()));

// This merges the tiff files for the selected pages into one tiff file.
// $images holds the form_images checkboxes to the right of the images.
//
$mergeTiffs = static function (array $images) use ($faxcache, $filesystem, $runProcess, $processError): string {
    $tiffNames = [];
    foreach ($images as $name) {
        if (is_string($name)) {
            $tiffNames[] = "$name.tif";
        }
    }

    if (count($tiffNames) === 0) {
        return xl('Internal error - no pages were selected!') . ' ';
    }

    // Remove any merge output from a previous run so a failed tiffcp cannot
    // leave stale pages behind for the dependent tiff2pdf/sendfax steps.
    $filesystem->remove($faxcache . '/temp.tif');
    $process = $runProcess(array_merge(['tiffcp'], $tiffNames, ['temp.tif']), $faxcache);
    if (!$process->isSuccessful()) {
        return 'tiffcp returned ' . $processError($process) . ' ';
    }

    return '';
};

// If we are submitting...
//
if ($request->request->getString('form_save') !== '') {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

    $action_taken = false;

    // form_images are the checkboxes to the right of the images.
    $selectedImages = [];
    foreach ($request->request->all('form_images') as $image) {
        if (!is_string($image)) {
            continue;
        }

        check_file_dir_name($image);
        $selectedImages[] = $image;
    }

    if ($request->request->getBoolean('form_cb_copy')) {
        $patient_id = $request->request->getInt('form_pid');
        if ($patient_id === 0) {
            $terminator->error(Response::HTTP_BAD_REQUEST, xlt('Internal error - patient ID was not provided!'));
        }

        // If copying to patient documents...
        //
        if ($request->request->getInt('form_cb_copy_type') === 1) {
            // Compute the document's display filename.
            $ffname = trim($request->request->getString('form_filename'));
            check_file_dir_name($ffname);
            $i = strrpos($ffname, '.');
            if ($i !== false && $i > 0) {
                $ffname = trim(substr($ffname, 0, $i));
            }

            if ($ffname === '') {
                $ffname = $filebase;
            }

            $docname = "$ffname.pdf";
            $docdate = fixDate($request->request->getString('form_docdate'));
            $catid = $request->request->getInt('form_category');
            $newid = null;

            // Create the target PDF in a temporary file.  Note that we are
            // relying on the .tif files for the individual pages to already
            // exist in the faxcache directory.
            //
            $mergeMsg = $mergeTiffs($selectedImages);
            $info_msg .= $mergeMsg;
            $tmppdf = $mergeMsg === '' ? tempnam($globalsBag->getString('temporary_files_dir'), 'fax') : false;

            if ($mergeMsg === '' && $tmppdf === false) {
                $info_msg .= xl('Unable to create a temporary file for the document') . ' ';
            } elseif ($tmppdf !== false) {
                // The -j option here requires that libtiff is configured with libjpeg.
                // It could be omitted, but the output PDFs would then be quite large.
                $process = $runProcess(['tiff2pdf', '-j', '-p', 'letter', '-o', $tmppdf, $faxcache . '/temp.tif']);

                if (!$process->isSuccessful()) {
                    $info_msg .= 'tiff2pdf returned ' . $processError($process) . ' ';
                } else {
                    // Storing through the Document model keeps drive encryption,
                    // the storage path, UUID, hash and category linkage consistent
                    // with every other way a document enters the system.
                    $data = file_get_contents($tmppdf);
                    if ($data === false) {
                        $info_msg .= xl('Failed to read the generated document') . ' ';
                    } else {
                        $document = new Document();
                        $document->set_docdate($docdate);
                        $store_msg = $document->createDocument(
                            (string) $patient_id,
                            $catid,
                            $docname,
                            'application/pdf',
                            $data,
                            tmpfile: $tmppdf,
                        );
                        $rawDocId = $document->get_id();
                        $newid = is_numeric($rawDocId) ? (int) $rawDocId : null;
                        if ($newid === null) {
                            $info_msg .= ($store_msg ?: xl('Failed to store the document')) . ' ';
                        }
                    }
                }

                if (is_file($tmppdf)) {
                    unlink($tmppdf);
                }
            } // end temporary file created

            // If we are posting a note...
            if ($request->request->getBoolean('form_cb_note') && !$info_msg) {
                // Build note text in a way that identifies the new document.
                // See pnotes_full.php which uses this to auto-display the document.
                $note = $docname;
                for ($ancestorId = $catid; $ancestorId !== 0;) {
                    $catrow = QueryUtils::fetchRecords('SELECT name, parent FROM categories WHERE id = ?', [$ancestorId])[0] ?? null;
                    if ($catrow === null) {
                        break;
                    }

                    $catName = $catrow['name'];
                    $note = (is_string($catName) ? $catName : '') . "/$note";
                    $catParent = $catrow['parent'];
                    $ancestorId = is_numeric($catParent) ? (int) $catParent : 0;
                }

                $note = "New scanned document $newid: $note";
                $form_note_message = trim($request->request->getString('form_note_message'));
                if ($form_note_message) {
                    $note .= "\n" . $form_note_message;
                }

                $noteid = addPnote(
                    $patient_id,
                    $note,
                    $userauthorized,
                    1,
                    $request->request->getString('form_note_type'),
                    $request->request->getString('form_note_to')
                );
                // Link the new patient note to the document.
                setGpRelation(1, $newid, 6, $noteid);
            } // end post patient note
        } else { // end copy to documents
            // Otherwise creating a scanned encounter note...
            // Get desired $encounter_id.
            $encounter_id = $request->request->getInt('form_copy_sn_visit');
            if ($encounter_id === 0) {
                $info_msg .= "This patient has no visits! ";
            }

            $tmp_name = "$faxcache/temp.tif";
            if (!$info_msg) {
                // Merge the selected pages.
                $info_msg .= $mergeTiffs($selectedImages);
            }

            if (!$info_msg) {
                // The following is cloned from contrib/forms/scanned_notes/new.php:
                //
                $query = "INSERT INTO form_scanned_notes ( notes ) VALUES ( ? )";
                $formid = QueryUtils::sqlInsert($query, [$request->request->getString('form_copy_sn_comments')]);
                (new FormService())->addForm(
                    $encounter_id,
                    "Scanned Notes",
                    $formid,
                    "scanned_notes",
                    $patient_id,
                    $userauthorized
                );
                //
                $imagedir = $globalsBag->getString('OE_SITE_DIR') . "/documents/$patient_id/encounters";
                $imagepath = "$imagedir/{$encounter_id}_$formid.jpg";
                if (! is_dir($imagedir)) {
                    $filesystem->mkdir($imagedir);
                    $filesystem->touch($imagedir . "/index.html");
                }

                if (is_file($imagepath)) {
                    unlink($imagepath);
                }

                // TBD: There may be a faster way to create this file, given that
                // we already have a jpeg for each page in faxcache.
                $process = $runProcess(['convert', '-resize', '800', '-density', '96', $tmp_name, '-append', $imagepath]);
                if (!$process->isSuccessful()) {
                    $terminator->error(Response::HTTP_INTERNAL_SERVER_ERROR, "convert returned " . text($processError($process)));
                }
            }

            // If we are posting a patient note...
            if ($request->request->getBoolean('form_cb_note') && !$info_msg) {
                $visitDate = '';
                $erow = QueryUtils::fetchRecords(
                    'SELECT date FROM form_encounter WHERE encounter = ? AND pid = ?',
                    [$encounter_id, $patient_id]
                )[0] ?? null;
                if ($erow !== null) {
                    $encDate = $erow['date'] ?? null;
                    if (is_string($encDate)) {
                        $visitDate = substr($encDate, 0, 10);
                    }
                }

                $note = "New scanned encounter note for visit on " . $visitDate;
                $form_note_message = trim($request->request->getString('form_note_message'));
                if ($form_note_message) {
                    $note .= "\n" . $form_note_message;
                }

                addPnote(
                    $patient_id,
                    $note,
                    $userauthorized,
                    1,
                    $request->request->getString('form_note_type'),
                    $request->request->getString('form_note_to')
                );
            } // end post patient note
        }

        $action_taken = true;
    } // end copy to chart

    if ($request->request->getBoolean('form_cb_forward')) {
        $form_from     = trim($request->request->getString('form_from'));
        $form_to       = trim($request->request->getString('form_to'));
        $form_fax      = trim($request->request->getString('form_fax'));
        $form_message  = trim($request->request->getString('form_message'));
        $form_finemode = $request->request->getBoolean('form_finemode') ? '-m' : '-l';

        // Generate a cover page using enscript.  This can be a cool thing
        // to do, as enscript is very powerful.
        //
        $tmpfn1 = $filesystem->tempnam('/tmp', 'fax1');
        $tmpfn2 = $filesystem->tempnam('/tmp', 'fax2');
        $cpstring = $filesystem->readFile($globalsBag->getString('OE_SITE_DIR') . '/faxcover.txt');
        $cpstring = str_replace('{CURRENT_DATE}', date('F j, Y'), $cpstring);
        $cpstring = str_replace('{SENDER_NAME}', $form_from, $cpstring);
        $cpstring = str_replace('{RECIPIENT_NAME}', $form_to, $cpstring);
        $cpstring = str_replace('{RECIPIENT_FAX}', $form_fax, $cpstring);
        $cpstring = str_replace('{MESSAGE}', $form_message, $cpstring);
        $filesystem->dumpFile($tmpfn1, $cpstring);
        $process = $runProcess(
            array_merge(explode(' ', OPENEMR_HYLAFAX_ENSCRIPT), ['-o', $tmpfn2, $tmpfn1]),
            $globalsBag->getString('webserver_root') . '/custom'
        );
        $coverMsg = '';
        if (!$process->isSuccessful()) {
            $coverMsg = 'enscript returned ' . $processError($process) . ' ';
            $info_msg .= $coverMsg;
        }

        unlink($tmpfn1);

        // Send the fax as the cover page followed by the selected pages,
        // but only if both the cover page and the merge succeeded — faxing
        // after a failed merge could send stale pages from an earlier run.
        $mergeMsg = $mergeTiffs($selectedImages);
        $info_msg .= $mergeMsg;
        if ($coverMsg === '' && $mergeMsg === '') {
            $process = $runProcess(
                ['sendfax', '-A', '-n', $form_finemode, '-d', $form_fax, $tmpfn2, $faxcache . '/temp.tif']
            );
            if (!$process->isSuccessful()) {
                $info_msg .= 'sendfax returned ' . $processError($process) . ' ';
            }
        }

        unlink($tmpfn2);

        $action_taken = true;
    } // end forward

    $form_cb_delete = $request->request->getString('form_cb_delete');

  // If deleting selected, do it and then check if any are left.
    if ($form_cb_delete == '1' && !$info_msg) {
        foreach ($selectedImages as $inbase) {
            unlink($faxcache . "/" . $inbase . ".jpg");
            $action_taken = true;
        }

        // Check if any .jpg files remain... if not we'll clean up.
        if ($action_taken) {
            $dh = opendir($faxcache);
            if (! $dh) {
                $terminator->error(Response::HTTP_INTERNAL_SERVER_ERROR, "Cannot read " . text($faxcache));
            }

            $form_cb_delete = '2';
            while (false !== ($jfname = readdir($dh))) {
                if (strtolower(pathinfo($jfname, PATHINFO_EXTENSION)) === 'jpg') {
                    $form_cb_delete = '1';
                }
            }

            closedir($dh);
        }
    } // end delete 1

    if ($form_cb_delete == '2' && !$info_msg) {
        // Delete the tiff file, with archiving if desired. The throwing
        // Filesystem calls guarantee the cache erase below cannot run when
        // the source file was not actually archived or deleted.
        $archdir = $globalsBag->getString('hylafax_archdir');
        if ($archdir !== '' && $mode == 'fax') {
            $filesystem->rename($filepath, $archdir . '/' . $filename, true);
        } else {
            $filesystem->remove($filepath);
        }

        // Erase its cache.
        if (is_dir($faxcache)) {
            $dh = opendir($faxcache);
            if (! $dh) {
                $terminator->error(Response::HTTP_INTERNAL_SERVER_ERROR, "Cannot read " . text($faxcache));
            }

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
            echo " alert(" . js_escape($info_msg) . ");\n";
        }

        echo " if (!opener.closed && opener.refreshme) opener.refreshme();\n";
        echo " window.close();\n";
        echo "</script>\n</body>\n</html>\n";
        return;
    }
} // end submit logic

// If we get this far then we are displaying the form.

// Find out if the scanned_notes form is installed and active.
//
$scannedNotesCount = QueryUtils::fetchSingleValue(
    <<<'SQL'
    SELECT COUNT(*) AS count FROM registry
    WHERE directory LIKE 'scanned_notes' AND state = 1 AND sql_run = 1
    SQL,
    'count'
);
$using_scanned_notes = is_numeric($scannedNotesCount) && (int) $scannedNotesCount > 0;

// If the image cache does not yet exist for this fax, build it.
// This will contain a .tif image as well as a .jpg image for each page.
//
// Build the page images for the fax under $faxcache: a .tif and a .jpg per
// page. Returns '' on success. On failure, removes the partial cache — so a
// later request rebuilds it instead of silently serving an incomplete set of
// pages — and returns the error message.
$buildFaxcache = static function () use ($ext, $filepath, $faxcache, $filesystem, $runProcess, $processError): string {
    $filesystem->mkdir($faxcache);

    if (strtolower($ext) != '.tif') {
        // convert's default density for PDF-to-TIFF conversion is 72 dpi which is
        // not very good, so we upgrade it to "fine mode" fax quality.  It's really
        // better and faster if the scanner produces TIFFs instead of PDFs.
        $process = $runProcess(['convert', '-density', '203x196', $filepath, $faxcache . '/deleteme.tif']);
        if (!$process->isSuccessful()) {
            $filesystem->remove($faxcache);
            return "convert returned " . text($processError($process));
        }

        $process = $runProcess(['tiffsplit', 'deleteme.tif'], $faxcache);
        if (!$process->isSuccessful()) {
            $filesystem->remove($faxcache);
            return "tiffsplit returned " . text($processError($process));
        }

        $filesystem->remove($faxcache . '/deleteme.tif');
    } else {
        $process = $runProcess(['tiffsplit', $filepath], $faxcache);
        if (!$process->isSuccessful()) {
            $filesystem->remove($faxcache);
            return "tiffsplit returned " . text($processError($process));
        }
    }

    $pageTiffs = [];
    foreach (new FilesystemIterator($faxcache) as $pageFile) {
        if ($pageFile instanceof SplFileInfo && strtolower($pageFile->getExtension()) === 'tif') {
            $pageTiffs[] = $pageFile->getFilename();
        }
    }

    sort($pageTiffs);
    if (count($pageTiffs) > 0) {
        $process = $runProcess(array_merge(['mogrify', '-resize', '750x970', '-format', 'jpg'], $pageTiffs), $faxcache);
        if (!$process->isSuccessful()) {
            $filesystem->remove($faxcache);
            return "mogrify returned " . text($processError($process)) . "; ext is '" . text($ext) . "'; filepath is '" . text($filepath) . "'";
        }
    }

    return '';
};

if (! is_dir($faxcache)) {
    $buildError = $buildFaxcache();
    if ($buildError !== '') {
        $terminator->error(Response::HTTP_INTERNAL_SERVER_ERROR, $buildError);
    }
}

// Get the categories list: the leaf nodes of the document category tree,
// labeled with their ancestry. Kittens are the children of cats, you know. :-)
// Depth-first via an explicit stack; children are pushed in reverse so they
// are visited in name order, matching the original recursive listing.
$categories = [];
$catStack = [[0, '']];
while (count($catStack) > 0) {
    [$catid, $catstring] = array_pop($catStack);
    if (!is_int($catid) || !is_string($catstring)) {
        continue;
    }

    $crows = QueryUtils::fetchRecords('SELECT id, name FROM categories WHERE parent = ? ORDER BY name', [$catid]);
    $children = [];
    foreach ($crows as $crow) {
        $childId = $crow['id'];
        $childName = $crow['name'];
        if (!is_numeric($childId) || !is_string($childName)) {
            continue;
        }

        $children[] = [
            (int) $childId,
            ($catstring !== '' ? "$catstring / " : '') . ($catid !== 0 ? $childName : ''),
        ];
    }

    // If no kitties, then this is a leaf node and should be listed.
    if (count($children) === 0) {
        $categories[$catid] = $catstring;
        continue;
    }

    foreach (array_reverse($children) as $child) {
        $catStack[] = $child;
    }
}

// Get the users list.
$userRows = QueryUtils::fetchRecords(
    <<<'SQL'
    SELECT username, fname, lname FROM users
    WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' )
    ORDER BY lname, fname
    SQL
);
?>
<html>
<head>

    <?php Header::setupHeader(['opener', 'datetime-picker']);?>
    <title><?php echo xlt('Dispatch Received Document'); ?></title>

<script>

    <?php require(OEGlobalsBag::getInstance()->getSrcDir() . "/restoreSession.php"); ?>

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
  $.getScript("fax_dispatch_newpid.php?p=" + encodeURIComponent(pid) + "&csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken(session: $session)); ?>);
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
            <?php require(OEGlobalsBag::getInstance()->getSrcDir() . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
</script>

</head>

<body class="body_top">
<h2 class="text-center"><?php echo xlt('Dispatch Received Document'); ?></h2>

<form method='post' name='theform'
 action='fax_dispatch.php?<?php echo ($mode == 'fax') ? 'file' : 'scan'; ?>=<?php echo attr_url($filename); ?>&csrf_token_form=<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>' onsubmit='return validate()'>
<input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />

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
                                echo "         <option value='" . attr((string) $catkey) . "'";
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
                        generate_form_field(['data_type' => 1,'field_id' => 'note_type','list_id' => 'note_type','empty_title' => 'SKIP'], '');
                        ?>
                    </div>
                </div>
                <!-- To Section -->
                <div class="form-row mt-2">
                    <label class="col-2 col-form-label font-weight-bold"><?php echo xlt('To'); ?></label>
                    <div class="col-10">
                    <select name='form_note_to' class='form-control'>
                        <?php
                        foreach ($userRows as $urow) {
                            $username = $urow['username'];
                            $lname = $urow['lname'];
                            $fname = $urow['fname'];
                            if (!is_string($username) || !is_string($lname)) {
                                continue;
                            }

                            echo "         <option value='" . attr($username) . "'";
                            echo ">" . text($lname);
                            if (is_string($fname) && $fname !== '') {
                                echo ", " . text($fname);
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
    $terminator->error(Response::HTTP_INTERNAL_SERVER_ERROR, "Cannot read " . text($faxcache));
}

$jpgarray = [];
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
    echo "   <img src='../../sites/" . attr($site_id) . "/faxcache/" . attr($mode) . "/" . attr($filebase) . "/" . attr($jfname) . "' />\n";
    echo "  </td>\n";
    echo "  <td align='center' valign='top'>\n";
    echo "   <input type='checkbox' name='form_images[]' value='" . attr($jfnamebase) . "' checked />\n";
    echo "   <br />" . text((string) $page) . "\n";
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
