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
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("File management")]);
    exit;
}

$imagedir     = "$OE_SITE_DIR/images";
$educationdir = "$OE_SITE_DIR/documents/education";

if (!empty($_POST['bn_save'])) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    /** This is a feature that allows editing of configuration files. Uncomment this
        at your own risk, since it is considered a critical security vulnerability if
        OpenEMR is not configured correctly.
    // Prepare array of names of editable files, relative to the site directory.
    $my_files = array(
    'config.php',
    'faxcover.txt',
    'faxtitle.eps',
    'referral_template.html',
    'statement.inc.php',
    'letter_templates/custom_pdf.php',
    );
    // Append LBF plugin filenames to the array.
    $lres = sqlStatement('SELECT grp_form_id FROM layout_group_properties ' .
    "WHERE grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_activity = 1 ORDER BY grp_seq, grp_title");
    while ($lrow = sqlFetchArray($lres)) {
    $option_id = $lrow['grp_form_id']; // should start with LBF
    $my_files[] = "LBF/$option_id.plugin.php";
    }
    $form_filename = $_REQUEST['form_filename'];
    // Sanity check to prevent evildoing.
    if (!in_array($form_filename, $my_files)) {
    $form_filename = '';
    }
    $filepath = "$OE_SITE_DIR/$form_filename";
    if ($form_filename) {
        // Textareas, at least in Firefox, return a \r\n at the end of each line
        // even though only \n was originally there.  For consistency with
        // normal OpenEMR usage we translate those back.
        file_put_contents($filepath, str_replace(
            "\r\n",
            "\n",
            $_POST['form_filedata']
        ));
        $form_filename = '';
    }
    */

    // Handle image uploads.
    if (is_uploaded_file($_FILES['form_image']['tmp_name']) && $_FILES['form_image']['size']) {
        $form_dest_filename = $_POST['form_dest_filename'];
        if ($form_dest_filename == '') {
            $form_dest_filename = $_FILES['form_image']['name'];
        }

        $form_dest_filename = basename($form_dest_filename);
        if ($form_dest_filename == '') {
            die(xlt('Cannot find a destination filename'));
        }

        $path_parts = pathinfo($form_dest_filename);
        if (!in_array(strtolower($path_parts['extension']), array('gif','jpg','jpe','jpeg','png','svg'))) {
            die(xlt('Only images files are accepted'));
        }

        $imagepath = "$imagedir/$form_dest_filename";
        // If the site's image directory does not yet exist, create it.
        if (!is_dir($imagedir)) {
            mkdir($imagedir);
        }

        if (is_file($imagepath)) {
            unlink($imagepath);
        }

        $tmp_name = $_FILES['form_image']['tmp_name'];
        if (!move_uploaded_file($_FILES['form_image']['tmp_name'], $imagepath)) {
            die(xlt('Unable to create') . " '" . text($imagepath) . "'");
        }
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

        $tmp_name = $_FILES['form_education']['tmp_name'];
        if (!move_uploaded_file($tmp_name, $educationpath)) {
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

    // Get cURL resource
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://cdn.rawgit.com/jshttp/mime-db/master/db.json',
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5
    ));
   // Send the request & save response to $resp
    $resp = curl_exec($curl);
    $httpinfo = curl_getinfo($curl);
    if ($resp && $httpinfo['http_code'] == 200 && $httpinfo['content_type'] == 'application/json;charset=utf-8') {
        $all_mime_types = json_decode($resp, true);
        foreach ($all_mime_types as $name => $value) {
            $mime_types[] = $name;
        }
    } else {
        error_log('Get list of mime-type error: "' . errorLogEscape(curl_error($curl)) . '" - Code: ' . errorLogEscape(curl_errno($curl)));
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

    curl_close($curl);

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

<?php /** This is a feature that allows editing of configuration files. Uncomment this
at your own risk, since it is considered a critical security vulnerability if
OpenEMR is not configured correctly. ?>
 <tr class='bg-light dehead'>
  <td colspan='2' align='center'><?php echo xlt('Edit File in') . " " . text($OE_SITE_DIR); ?></td>
 </tr>
 <tr>
  <td valign='top' class='detail' nowrap>
   <select name='form_filename' onchange='msfFileChanged()' class="form-control">
    <option value=''></option>
<?php
foreach ($my_files as $filename) {
    echo "    <option value='" . attr($filename) . "'";
    if ($filename == $form_filename) {
        echo " selected";
    }
    echo ">" . text($filename) . "</option>\n";
}
?>
   </select>
   <br />
   <textarea name='form_filedata' rows='25' class="w-100 form-control"><?php
    if ($form_filename) {
        echo text(@file_get_contents($filepath));
    }
?></textarea>
  </td>
 </tr>
<?php */ ?>

 <tr class='dehead bg-light'>
  <td colspan='2' class='text-center'><?php echo text(xl('Upload Image to') . " $imagedir"); ?></td>
 </tr>

 <tr>
  <td valign='top' class='detail' nowrap>
    <?php echo xlt('Source File'); ?>:
   <input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
   <input type="file" name="form_image" size="40" />&nbsp;
    <?php echo xlt('Destination Filename'); ?>:
   <select name='form_dest_filename' class='form-control'>
    <option value=''>(<?php echo xlt('Use source filename'); ?>)</option>
<?php
  // Generate an <option> for each file already in the images directory.
  $dh = opendir($imagedir);
if (!$dh) {
    die(text(xl('Cannot read directory') . " '$imagedir'"));
}

  $imagesslist = array();
while (false !== ($sfname = readdir($dh))) {
    if (substr($sfname, 0, 1) == '.') {
        continue;
    }

    if ($sfname == 'CVS') {
        continue;
    }

    $imageslist[$sfname] = $sfname;
}

  closedir($dh);
  ksort($imageslist);
foreach ($imageslist as $sfname) {
    echo "    <option value='" . attr($sfname) . "'";
    echo ">" . text($sfname) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

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
    <h2><?php echo xlt('Create custom white list of MIME content type of a files to secure your documents system');?></h2>
    <form id="whitelist_form" method="post">
        <div class="subject-black-list">
            <div class="top-list">
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
            <select multiple="multiple" id='black-list' class="form-control">
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

        <div class="subject-white-list">
            <div class="top-list">
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
            <select name="white_list[]" multiple="multiple" id='white-list' class="form-control">
                <?php
                foreach ($white_list as $type) {
                    echo "<option value='" . attr($type) . "'> " . text($type) . "</option>";
                }
                ?>
            </select>
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
            $('#white-list').prepend("<option value="+new_type+">"+new_type+"</option>")
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
