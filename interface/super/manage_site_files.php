<?php

/**
 * This module provides for editing site-specific text files and
 * for uploading site-specific image files.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use GuzzleHttp\Client;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("File management")]);
    exit;
}

$educationdir = "$OE_SITE_DIR/documents/education";

if (!empty($_POST['bn_save'])) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

     // Handle PDF uploads for patient education.
    if (is_uploaded_file($_FILES['form_education']['tmp_name']) && $_FILES['form_education']['size']) {
        $form_dest_filename = $_FILES['form_education']['name'];
        $form_dest_filename = strtolower(basename($form_dest_filename));
        if (substr($form_dest_filename, -4) != '.pdf') {
            die(xlt('Filename must end with ".pdf"'));
        }

        $educationpath = "$educationdir/$form_dest_filename";
        // If the site's education directory does not yet exist, create it.
        if (!is_dir($educationdir)) {
            mkdir($educationdir);
        }

        if (is_file($educationpath)) {
            unlink($educationpath);
        }

        $fileData = file_get_contents($_FILES['form_education']['tmp_name']);
        if ($GLOBALS['drive_encryption']) {
            $fileData = (new Cryptogen())->encryptStandard($fileData, null, 'database');
        }
        if (file_put_contents($educationpath, $fileData) === false) {
            die(text(xl('Unable to create') . " '$educationpath'"));
        }
    }
}

/**
 * Thumbnails generator
 * generating  thumbnail image to all images files from documents table
 */

if (isset($_POST['generate_thumbnails'])) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $thumb_generator = new ThumbnailGenerator();
    $results = $thumb_generator->generate_all();

    $thumbnail_msg = "<p class='text-success'>" . xlt('Generated thumbnail(s)') . " : " . text($results['sum_success']) . "</p>";
    $thumbnail_msg .= "<p class='text-danger'>" . xlt('Failed to generate') . " : " .  text($results['sum_failed']) . "</p>";
    foreach ($results['failed'] as $key => $file) {
        $num = $key + 1;
        $thumbnail_msg .= "<p class='text-danger' style='font-size: 11px'> " . text($num) . ". " . text($file) . "</p>";
    }
} else {
    $count_not_generated = ThumbnailGenerator::count_not_generated();

    $thumbnail_msg = "<p>" .  xlt('Files with empty thumbnail') . ": " . text($count_not_generated) . " </p>";
}


/**
 * White list files.
 * Security feature that enable to upload only file with mime-type from white list.
 * Important to prevention upload of virus script.
 * Dependence - turn on global setting 'secure_upload'
 */

if ($GLOBALS['secure_upload']) {
    $mime_types  = array('image/*', 'text/*', 'audio/*', 'video/*');

    $responseError = false;
    $responseErrorAsString = "";
    try {
        $resp = (new GuzzleHttp\Client())->get('https://cdn.rawgit.com/jshttp/mime-db/master/db.json', [
            'timeout' => 5
        ]);
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $responseErrorAsString = $e->getResponse()->getBody()->getContents();
        $responseError = true;
    }

    if (!$responseError && empty($responseErrorAsString) && !empty($resp) && ($resp->getStatusCode() == 200) && $resp->getBody()) {
        $all_mime_types = json_decode($resp->getBody(), true);
        foreach ($all_mime_types as $name => $value) {
            $mime_types[] = $name;
        }
    } else {
        if (!empty($resp)) {
            $errorStatusCode = $resp->getStatusCode();
        }
        error_log('Get list of mime-type error: "' . errorLogEscape($responseErrorAsString) . '" - Code: ' . errorLogEscape($errorStatusCode ?? 0));
        $mime_types_list = array(
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.oasis.opendocument.spreadsheet',
            'text/plain'
        );
        $mime_types = array_merge($mime_types, $mime_types_list);
    }

    if (isset($_POST['submit_form'])) {
        //verify csrf
        if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
            CsrfUtils::csrfNotVerified();
        }

        $new_white_list = empty($_POST['white_list']) ? array() : $_POST['white_list'];

        // truncate white list from list_options table
        sqlStatement("DELETE FROM `list_options` WHERE `list_id` = 'files_white_list'");
        foreach ($new_white_list as $mimetype) {
            sqlStatement("INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `activity`)  VALUES ('files_white_list', ?, ?, 1)", array($mimetype, $mimetype));
        }

        $white_list = $new_white_list;
    } else {
        $white_list = array();
        $lres = sqlStatement("SELECT option_id FROM list_options WHERE list_id = 'files_white_list' AND activity = 1");
        while ($lrow = sqlFetchArray($lres)) {
            $white_list[] = $lrow['option_id'];
        }
    }
}


?>
<html>

<head>
<title><?php echo xlt('File management'); ?></title>

    <?php Header::setupHeader(); ?>

<style>
.dehead {
    font-family: sans-serif;
    font-size: 0.8125rem;
    font-weight: bold;
}
 .detail {
     font-family: sans-serif;
     font-size: 0.8125rem;
     font-weight: normal;
}
#generate_thumb {
     width: 95%;
     margin: 50px auto;
     border: 2px solid var(--gray);
}
#file_type_whitelist {
    width: 95%;
    margin: 50px auto;
}
#generate_thumb table {
     font-size: 14px;
     text-align: center;
}
#generate_thumb table td {
     border-right: 1px solid var(--gray);
     padding: 0 15px;
}
</style>

<script>
// This is invoked when a filename selection changes in the drop-list.
// In this case anything else entered into the form is discarded.
function msfFileChanged() {
    top.restoreSession();
    document.forms[0].submit();
}
</script>

</head>

<body class="body_top">
<form method='post' action='manage_site_files.php' enctype='multipart/form-data'
 onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<table class="table table-bordered border-dark">

 <tr class='dehead bg-light'>
  <td colspan='2' align='center'><?php echo text(xl('Upload Patient Education PDF to') . " $educationdir"); ?></td>
 </tr>
 <tr>
  <td valign='top' class='detail' nowrap>
    <?php echo xlt('Source File'); ?>:
   <input type="file" name="form_education" size="40" />&nbsp;
    <?php echo xlt('Name must be like codetype_code_language.pdf, for example icd9_274.11_en.pdf'); ?>
  </td>
 </tr>

</table>

<input type='submit' class="btn btn-primary" name='bn_save' value='<?php echo xla('Save'); ?>' />

</center>

</form>

<div id="generate_thumb">
    <table class="w-100">
        <tr>
            <td class="thumb_title" style="width: 33%">
                <strong><?php echo xlt('Generate Thumbnails')?></strong>
            </td>
            <td class="thumb_msg" style="width: 50%">
                <span><?php echo $thumbnail_msg ?></span>
            </td>
            <td  class="thumb_form" style="width: 17%; border-right: none">
                <form method='post' action='manage_site_files.php#generate_thumb'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <input style="margin-top: 10px" class="btn btn-primary" type="submit" name="generate_thumbnails" value="<?php echo xla('Generate') ?>" />
                </form>
            </td>
        </tr>
    </table>
</div>

<?php if ($GLOBALS['secure_upload']) { ?>
<div id="file_type_whitelist">
    <h3 class='text-center'><?php echo xlt('White list files by MIME content type');?></h3>
    <form id="whitelist_form" method="post">
        <div class="form-row">
            <div class="subject-black-list col">
                <div class="form-group">
                    <h2 class="text-center"><?php echo xlt('Black list'); ?></h2>
                    <div class="form-row align-items-center">
                        <div class="col-2">
                            <label for="filter-black-list" class="font-weight-bold"><?php echo xlt('Filter');?>:</label>
                        </div>
                        <div class="col">
                            <input type="text" id="filter-black-list" class="form-control" />
                        </div>
                    </div>
                </div>
                <select multiple="multiple" id='black-list' class="form-control w-100">
                    <?php
                    foreach ($mime_types as $type) {
                        if (!in_array($type, $white_list)) {
                            echo "<option value='" . attr($type) . "'> " . text($type) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="subject-info-arrows">
                <input type="button" id="btnAllRight" value=">>" class="btn btn-secondary btn-sm" /><br />
                <input type="button" id="btnRight" value=">" class="btn btn-secondary btn-sm" /><br />
                <input type="button" id="btnLeft" value="<" class="btn btn-secondary btn-sm" /><br />
                <input type="button" id="btnAllLeft" value="<<" class="btn btn-secondary btn-sm" />
            </div>

            <div class="subject-white-list col">
                <div class="form-group">
                    <h2 class="text-center"><?php echo xlt('White list'); ?></h2>
                    <div class="form-row">
                        <div class="col-2">
                            <label><?php echo xlt('Add manually');?>:</label>
                        </div>
                        <div class="col">
                            <input type="text" id="add-manually-input" class="form-control" />
                        </div>
                        <div class="col">
                            <input type="button" class="btn btn-primary" id="add-manually" value="+" />
                        </div>
                    </div>
                </div>
                <select name="white_list[]" multiple="multiple" id='white-list' class="form-control w-100">
                    <?php
                    foreach ($white_list as $type) {
                        echo "<option value='" . attr($type) . "'> " . text($type) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="subject-info-save">
            <input type="button" id="submit-whitelist" class="btn btn-primary" value="<?php echo xla('Save'); ?>" />
            <input type="hidden" name="submit_form" value="1" />
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        </div>
    </form>

</div>

<script>

    (function () {
        $('#btnRight').click(function (e) {
            var selectedOpts = $('#black-list option:selected');
            if (selectedOpts.length == 0) {
                e.preventDefault();
            }

            $('#white-list').append($(selectedOpts).clone());
            $(selectedOpts).remove();
            e.preventDefault();
        });

        $('#btnAllRight').click(function (e) {
            var selectedOpts = $('#black-list option');
            if (selectedOpts.length == 0) {
                e.preventDefault();
            }

            $('#white-list').append($(selectedOpts).clone());
            $(selectedOpts).remove();
            e.preventDefault();
        });

        $('#btnLeft').click(function (e) {
            var selectedOpts = $('#white-list option:selected');
            if (selectedOpts.length == 0) {
                e.preventDefault();
            }

            $('#black-list').append($(selectedOpts).clone());
            $(selectedOpts).remove();
            e.preventDefault();
        });

        $('#btnAllLeft').click(function (e) {
            var selectedOpts = $('#white-list option');
            if (selectedOpts.length == 0) {
                e.preventDefault();
            }

            $('#black-list').append($(selectedOpts).clone());
            $(selectedOpts).remove();
            e.preventDefault();
        });

        var storeElements = [];

        $('#filter-black-list').on('keyup', function() {
            var val = this.value.toLowerCase();

            $('#black-list  option').each(function(){

                if(this.value.toLowerCase().indexOf( val ) == -1){
                    if(storeElements.indexOf(this) == -1){
                        storeElements.unshift(this)
                    }
                    $(this).remove();
                }
            });

            $(storeElements).each(function(key, element){

                if(element.value.toLowerCase().indexOf( val ) > -1){

                    $('#black-list').prepend(element);
                    storeElements.splice(key, 1)
                }

            });

        });

        $('#add-manually').on('click', function () {
            var new_type = $("#add-manually-input").val();
            if(new_type.length < 1)return;
            $('#white-list').prepend("<option value='" + jsAttr(new_type) + "'>" + jsText(new_type) + "</option>")
        })

        $('#submit-whitelist').on('click', function () {
            $('#white-list option').prop('selected', true);
            $('#whitelist_form').submit();
        })

    }(jQuery));

</script>


<?php } ?>

</body>
</html>
