<?php
// Copyright (C) 2010-2016 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This module provides for editing site-specific text files and
// for uploading site-specific image files.

require_once('../globals.php');
require_once($GLOBALS['srcdir'].'/acl.inc');
/* for formData() */

if (!acl_check('admin', 'super')) {
    die(htmlspecialchars(xl('Not authorized')));
}

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

$form_filename = strip_escape_custom($_REQUEST['form_filename']);
// Sanity check to prevent evildoing.
if (!in_array($form_filename, $my_files)) {
    $form_filename = '';
}

$filepath = "$OE_SITE_DIR/$form_filename";

$imagedir     = "$OE_SITE_DIR/images";
$educationdir = "$OE_SITE_DIR/documents/education";

if (!empty($_POST['bn_save'])) {
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

  // Handle image uploads.
    if (is_uploaded_file($_FILES['form_image']['tmp_name']) && $_FILES['form_image']['size']) {
        $form_dest_filename = $_POST['form_dest_filename'];
        if ($form_dest_filename == '') {
            $form_dest_filename = $_FILES['form_image']['name'];
        }

        $form_dest_filename = basename($form_dest_filename);
        if ($form_dest_filename == '') {
            die(htmlspecialchars(xl('Cannot find a destination filename')));
        }

        $path_parts = pathinfo($form_dest_filename);
        if (!in_array(strtolower($path_parts['extension']), array('gif','jpg','jpe','jpeg','png','svg'))) {
            die(xl('Only images files are accepted'));
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
            die(htmlspecialchars(xl('Unable to create') . " '$imagepath'"));
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
    $thumb_generator = new ThumbnailGenerator();
    $results = $thumb_generator->generate_all();

    $thumbnail_msg = "<p style='color: green'>" . xlt('Generated thumbnail(s)') . " : " . text($results['sum_success']) . "</p>";
    $thumbnail_msg .= "<p style='color: red'>" . xlt('Failed to generate') . " : " .  text($results['sum_failed']) . "</p>";
    foreach ($results['failed'] as $key => $file) {
        $num = $key +1;
        $thumbnail_msg .= "<p style='color: red; font-size: 11px'> " .text($num) . ". " . text($file) . "</p>";
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
        error_log('Get list of mime-type error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
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
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style type="text/css">
 .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
 #generate_thumb, #file_type_whitelist{
     width: 95%;
     margin: 50px auto;
     border: 2px solid dimgrey;
 }
 #generate_thumb table{
     font-size: 14px;
     text-align: center;
 }
 #generate_thumb table td{
     border-right: 1px solid dimgrey;
     padding: 0 15px;
 }
</style>

<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-min-3-1-1/index.js"></script>

<script language="JavaScript">
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

<center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd' class='dehead'>
  <td colspan='2' align='center'><?php echo htmlspecialchars(xl('Edit File in') . " $OE_SITE_DIR"); ?></td>
 </tr>

 <tr>
  <td valign='top' class='detail' nowrap>
   <select name='form_filename' onchange='msfFileChanged()'>
    <option value=''></option>
<?php
foreach ($my_files as $filename) {
    echo "    <option value='" . htmlspecialchars($filename, ENT_QUOTES) . "'";
    if ($filename == $form_filename) {
        echo " selected";
    }

    echo ">" . htmlspecialchars($filename) . "</option>\n";
}
?>
   </select>
   <br />
   <textarea name='form_filedata' rows='25' style='width:100%'><?php
    if ($form_filename) {
        echo htmlspecialchars(@file_get_contents($filepath));
    }
?></textarea>
  </td>
 </tr>

 <tr bgcolor='#dddddd' class='dehead'>
  <td colspan='2' align='center'><?php echo htmlspecialchars(xl('Upload Image to') . " $imagedir"); ?></td>
 </tr>

 <tr>
  <td valign='top' class='detail' nowrap>
    <?php echo htmlspecialchars(xl('Source File')); ?>:
   <input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
   <input type="file" name="form_image" size="40" />&nbsp;
    <?php echo htmlspecialchars(xl('Destination Filename')) ?>:
   <select name='form_dest_filename'>
    <option value=''>(<?php echo htmlspecialchars(xl('Use source filename')) ?>)</option>
<?php
  // Generate an <option> for each file already in the images directory.
  $dh = opendir($imagedir);
if (!$dh) {
    die(htmlspecialchars(xl('Cannot read directory') . " '$imagedir'"));
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
    echo "    <option value='" . htmlspecialchars($sfname, ENT_QUOTES) . "'";
    echo ">" . htmlspecialchars($sfname) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

 <tr bgcolor='#dddddd' class='dehead'>
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

<p>
<input type='submit' name='bn_save' value='<?php echo htmlspecialchars(xl('Save')) ?>' />
</p>

</center>

</form>

<div id="generate_thumb">
    <table style="width: 100%">
        <tr>
            <td class="thumb_title" style="width: 33%">
                <b><?php echo xlt('Generate Thumbnails')?></b>
            </td>
            <td class="thumb_msg" style="width: 50%">
                <span><?php echo $thumbnail_msg ?></span>
            </td>
            <td  class="thumb_form" style="width:17%;border-right:none">
                <form method='post' action='manage_site_files.php#generate_thumb'>
                    <input style="margin-top: 10px" type="submit" name="generate_thumbnails" value="<?php echo xla('Generate') ?>">
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
               <h2><?php echo xlt('Black list'); ?></h2>
               <b><?php echo xlt('Filter');?>:</b> <input type="text" id="filter-black-list" >
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
            <input type="button" id="btnAllRight" value=">>" /><br />
            <input type="button" id="btnRight" value=">" /><br />
            <input type="button" id="btnLeft" value="<" /><br />
            <input type="button" id="btnAllLeft" value="<<" />
        </div>

        <div class="subject-white-list">
            <div class="top-list">
                <h2><?php echo xlt('White list'); ?></h2>
                <b><?php echo xlt('Add manually');?>:</b> <input type="text" id="add-manually-input"> <input type="button" id="add-manually" value="+">
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
            <input type="button" id="submit-whitelist" value="<?php echo xlt('Save'); ?>" />
            <input type="hidden" name="submit_form" value="1" />
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

