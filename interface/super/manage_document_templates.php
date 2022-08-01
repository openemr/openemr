<?php

/**
 * Document Template Management Module.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2013-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Document Template Management")]);
    exit;
}

// Set up crypto object
$cryptoGen = new CryptoGen();

$form_filename = convert_safe_file_dir_name($_REQUEST['form_filename'] ?? '');

$templatedir = "$OE_SITE_DIR/documents/doctemplates";

// If downloading a file, do the download and nothing else.
// Thus the current browser page should remain displayed.
//
if (!empty($_POST['bn_download'])) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $templatepath = "$templatedir/$form_filename";

    // Place file in variable
    $fileData = file_get_contents($templatepath);

    // Decrypt file, if applicable
    if ($cryptoGen->cryptCheckStandard($fileData)) {
        $fileData = $cryptoGen->decryptStandard($fileData, null, 'database');
    }

    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    // attachment, not inline
    header("Content-Disposition: attachment; filename=\"$form_filename\"");
    // Note we avoid providing a mime type that suggests opening the file.
    header("Content-Type: application/octet-stream");
    header("Content-Length: " . strlen($fileData));
    echo $fileData;
    exit;
}

if (!empty($_POST['bn_delete'])) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $templatepath = "$templatedir/$form_filename";
    if (is_file($templatepath)) {
        unlink($templatepath);
    }
}

if (!empty($_POST['bn_upload'])) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    // Handle uploads.
    $tmp_name = $_FILES['form_file']['tmp_name'];
    if (is_uploaded_file($tmp_name) && $_FILES['form_file']['size']) {
        // Choose the destination path/filename.
        $form_dest_filename = $_POST['form_dest_filename'];
        if ($form_dest_filename == '') {
            $form_dest_filename = $_FILES['form_file']['name'];
        }

        $form_dest_filename = convert_safe_file_dir_name(basename($form_dest_filename));
        if ($form_dest_filename == '') {
            die(xlt('Cannot determine a destination filename'));
        }
        $path_parts = pathinfo($form_dest_filename);
        if (!in_array(strtolower($path_parts['extension']), array('odt', 'txt', 'docx', 'zip'))) {
            die(text(strtolower($path_parts['extension'])) . ' ' . xlt('filetype is not accepted'));
        }

        $templatepath = "$templatedir/$form_dest_filename";
        // If the site's template directory does not yet exist, create it.
        if (!is_dir($templatedir)) {
            mkdir($templatedir);
        }

        // If the target file already exists, delete it.
        if (is_file($templatepath)) {
            unlink($templatepath);
        }

        // Place uploaded file in variable.
        $fileData = file_get_contents($tmp_name);

        // Encrypt uploaded file, if applicable.
        if ($GLOBALS['drive_encryption']) {
            $storedData = $cryptoGen->encryptStandard($fileData, null, 'database');
        } else {
            $storedData = $fileData;
        }

        // Store the uploaded file.
        if (file_put_contents($templatepath, $storedData) === false) {
            die(xlt('Unable to create') . " '" . text($templatepath) . "'");
        }
    }
}

?>
<html>
   <head>
      <title><?php echo xlt('Document Template Management'); ?></title>
      <?php Header::setupHeader(); ?>
      <style>
         .dehead {
           color: var(--black);
           font-family: sans-serif;
           font-size: 0.8125rem;
           font-weight:bold;
         }
         .detail {
           color: var(--black);
           font-family: sans-serif;
           font-size: 0.8125rem;
           font-weight: normal;
         }
      </style>
   </head>
   <body class="body_top">
   <div class="container">
      <form method='post' action='manage_document_templates.php' enctype='multipart/form-data'
         onsubmit='return top.restoreSession()'>
         <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <h2 class="text-center"><?php echo xlt('Document Template Management'); ?></h2>
            <div class="row">
            <div class="col-6">
               <div class="mx-auto mt-3">
                  <div class="card">
                     <h5 class="card-header"><?php echo xlt('Upload a Template'); ?></h5>
                     <div class="card-body">
                        <div class="custom-file">
                           <input type="hidden" name="MAX_FILE_SIZE" value="250000000" />
                           <input type="file" name="form_file" size="40" class="custom-file-input" id="customFile" />
                           <label class="custom-file-label" for="customFile"><?php echo xlt('Choose file'); ?></label>
                        </div>
                        <div class="input-group mt-3">
                          <label for="form_dest_filename"><?php echo xlt('Destination Filename'); ?>:</label>
                          <input type='text' class="form-control" name='form_dest_filename' id='form_dest_filename' size='30' />
                          <div class="input-group-append">
                            <input type='submit' class="btn btn-primary" name='bn_upload' value='<?php echo xla('Upload') ?>' />
                          </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-6">
               <div class="mx-auto mt-3">
                  <div class="card">
                     <h5 class="card-header"><?php echo xlt('Download or Delete a Template'); ?></h5>
                     <div class="card-body">
                        <select class="form-control" name='form_filename'>
                        <?php
                        // Generate an <option> for each existing file.
                        if (file_exists($templatedir)) {
                            $dh = opendir($templatedir);
                        } else {
                            $dh = false;
                        }
                        if ($dh) {
                            $templateslist = array();
                            while (false !== ($sfname = readdir($dh))) {
                                if (substr($sfname, 0, 1) == '.') {
                                    continue;
                                }

                                $templateslist[$sfname] = $sfname;
                            }

                            closedir($dh);
                            ksort($templateslist);
                            foreach ($templateslist as $sfname) {
                                echo "    <option value='" . attr($sfname) . "'";
                                echo ">" . text($sfname) . "</option>\n";
                            }
                        }
                        ?>
                        </select>
                        <div class="mt-3">
                           <input type='submit' class="btn btn-success" name='bn_download' value='<?php echo xla('Download') ?>' />
                           <input type='submit' class="btn btn-danger" name='bn_delete' value='<?php echo xla('Delete') ?>' />
                        </div>
                     </div>
                  </div>
               </div>
            </div>
          </div>
      </form>
      </div>
      <script>
      //display file name
        $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(jsText(fileName));
        });
        </script>
   </body>
</html>
