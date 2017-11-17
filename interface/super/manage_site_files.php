<?php
// Copyright (C) 2010-2016 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This module provides for editing site-specific text files and
// for uploading site-specific image files.
use OpenEMR\Core\Header;

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
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['common']);?>
    <title><?php echo xlt('File management'); ?></title>
    <style type="text/css">
    .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
    .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
    .head{
        background-color:#DDDDDD;
    }
    /*#generate_thumb, #file_type_whitelist{
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
    }*/
    .oe-small{
       font-size:0.8em;
    }
    .tooltp{
        word-wrap: break-word;
        width:100px;
    }
    select[id$="-list"] {
        height:200px ! Important;
    }
    </style>
    
    <script language="JavaScript">
    // This is invoked when a filename selection changes in the drop-list.
    // In this case anything else entered into the form is discarded.
    function msfFileChanged() {
    top.restoreSession();
    console.log("SELECT ACTION VAL " + $('#select_action').val()); 
    $('#select-val').val( $('#select_action').val());
    console.log("SELECT  VAL " + $('#select-val').val());
    document.forms[0].submit();
    }
    </script>
</head>
<body class="body_top">
    <div class='container'>
        <div class="row">
            <div class="col-xs-12">
                 <div class="page-header clearfix">
                   <h2 class="clearfix"><span id='header_text'><?php echo xlt("File Management"); ?></span><a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
                </div>
            </div>
        </div>
        <fieldset>
        <legend>
            
            <span><?php echo xlt('Select Action'); ?></span>
            <div class='pull-right' style='margin:7px 10px 5px 10px'>
                <select id='select_action' class='form-control'>
                    <option value=''><?php echo xlt('Select an action'); ?> ...</option>
                    <option value='edit_files'><?php echo xlt('Edit Files'); ?></option>
                    <option value='upload_images'><?php echo xlt('Upload Images'); ?></option>
                    <option value='upload_pt_ed'><?php echo xlt('Upload Patient Education'); ?></option>
                    <option value='thumbnails'><?php echo xlt('Generate Thumbnails'); ?></option>
                    <?php 
                        $mime = xlt('MIME White List');
                        if ($GLOBALS['secure_upload']) {
                            echo "<option value='mime_type'>$mime</option>";
                        }
                    
                    ?>
                </select>
            </div>
            
        </legend>
        <div class='row'>
                <form action='manage_site_files.php' enctype='multipart/form-data' method=
                'post' onsubmit='return top.restoreSession()'>
                    <div class='col-xs-12' id='edit-files-div' style='display:none'>
                        <div class='col-xs-12'>
                        <fieldset>
                                <h4 class='head clearfix ' style='padding:5px 10px'><?php echo htmlspecialchars(xl('Edit File in') . " $OE_SITE_DIR"); ?>
                                    <div class='pull-right'>
                                        <select name='form_filename' id='form_filename' class='form-control' onchange='msfFileChanged()'>
                                            <option value=''><?php echo xlt('Please select a file to edit'); ?> ...</option>
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
                                    </div>
                                </h4>
                                <textarea name='form_filedata' id='form_filedata' class='form-control' cols='80' rows='25'>
                                    <?php
                                    if ($form_filename) {
                                        echo htmlspecialchars(@file_get_contents($filepath));
                                    }
                                    ?>
                                </textarea>
                        </fieldset>
                        </div>
                    </div>
                    <div class='col-xs-12' id='upload-images-div' style='display:none'>
                        <div class='col-xs-12'>
                        <fieldset>
                            <h4 class='head clearfix ' style='padding:5px 10px'><?php echo xlt('Upload Image'); ?> <i id='upload-image-tooltip' class="fa fa-info-circle text-primary oe-small h5" style='word-wrap:break-word;'aria-hidden="true"></i></h4>
                             <div class='col-xs-6'>
                                <div class="form-group">
                                    <p><strong><?php echo htmlspecialchars(xl('Source File')); ?>:</strong>
                                    <div class="input-group">
                                        <label class="input-group-btn">
                                            <span class="btn btn-default">
                                                <?php echo xlt('Browse'); ?>&hellip;<input type="file" id="form_image" name="form_image" style="display: none;" >
                                                <input name="MAX_FILE_SIZE" type="hidden" value="5000000"> 
                                            </span>
                                        </label>
                                        <input type="text" id="selected-file" class="form-control" placeholder="<?php echo xlt('Click Browse and select one image file'); ?> ..." readonly>
                                    </div>
                                </div>
                            </div>
                            <div class='col-xs-6'>
                                <div class="form-group">
                                <p><strong><?php echo htmlspecialchars(xl('Destination File')); ?>:</strong>
                                <div class="input-group col-xs-12">
                                <select name='form_dest_filename' class='form-control'>
                                    <option value=''>
                                        (<?php echo htmlspecialchars(xl('Use source filename')) ?>)
                                    </option><?php
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
                                </div>
                                </div>
                            </div>
                        </fieldset>
                        </div>
                    </div>
                    <div class='col-xs-12' id='upload-pt-edu-div' style='display:none'>
                        <div class='col-xs-12'>
                            <fieldset>
                                <h4 class='head clearfix ' style='padding:5px 10px'><?php echo xlt('Upload Patient Education Material'); ?>  <i id='upload-pt-edu-tooltip' class="fa fa-info-circle text-primary h5 oe-small" title='' aria-hidden="true"></i></h4>
                                 <div class='col-xs-12'>
                                    <div class="form-group">
                                        <div class="input-group"> 
                                            <label class="input-group-btn">
                                                <span class="btn btn-default">
                                                    <?php echo xlt('Browse'); ?>&hellip;<input type="file" id="form_education" name="form_education" style="display: none;" >
                                                </span>
                                            </label>
                                            <input type="text" id="selected-pt-edu" class="form-control" placeholder="<?php echo xlt('Click Browse and select one patient education file'); ?> ..." readonly>
                                        </div>
                                        <div class='col-xs-11 col-xs-offset-1'><p><strong><em><?php echo xlt('Name must be like codetype_code_language.pdf, for example icd9_274.11_en.pdf'); ?></em></strong></div>
                                    </div>
                                 </div>
                            </fieldset>
                        </div>
                    </div>
                    <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                    <div class="form-group clearfix" id="button-div" style='display:none'>
                        <div class="col-sm-12 text-left position-override">
                            <div class="btn-group btn-group-pinch" role="group">
                                <button type='submit' name='bn_save'  class="btn btn-default btn-save" value='<?php echo xla('Save') ?>'><?php echo xlt('Save') ?></button>
                                <button class="btn btn-default btn-undo" type="reset"><?php echo xlt('Reset');?></button>
                                <button class="btn btn-link btn-cancel btn-separate-left" href="#" onclick="CancelDistribute()"><?php echo xlt('Cancel');?></button>
                                
                            </div>
                        </div>
                    </div>
                    <input type='hidden' name='select-val' id='select-val' value = ''>
                </form>
    </div>
    <div class='row' id="generate_thumb" style='display:none'>
        
        <div class='col-xs-12'>
            <div class='col-xs-12'>
                <form action='manage_site_files.php#generate_thumb' method='post'>
                    <fieldset>
                        <h4 class='head clearfix ' style='padding:5px 10px'><?php echo xlt('Generate Thumbnails'); ?>  <i id='thumbnail-tooltip' class="fa fa-info-circle text-primary h5 oe-small" title='' aria-hidden="true"></i></h4>
                        <div class='col-xs-12'>
                            <p><?php echo xlt('Use this feature to generate thumbnails of images in the images directory');?>
                        </div>
                        <div class='col-xs-12'><span><?php echo $thumbnail_msg ?></span></div>
                        <div class='col-xs-12'>
                            
                            <button name="generate_thumbnails" id="generate_thumbnails" class="btn btn-default btn-add" style="margin-top: 10px" type="submit" value="<?php echo xla('Generate') ?>"><?php echo xlt('Generate') ?></button>
                           
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
    <?php if ($GLOBALS['secure_upload']) { ?>
    <div class='row' id="file_type_whitelist" style="display:none">
        <div class='col-xs-12'>
		<div class='col-xs-12'>
			<form id="whitelist_form" method="post" name="whitelist_form">
				<fieldset>
					<legend><?php echo xlt('Create custom white list'); ?>   <i id='mime-type-tooltip' class="fa fa-info-circle text-primary h5 oe-small" title='' aria-hidden="true"></i></legend>
					<div class="subject-black-list col-lg-offset-1">
						<div class="top-list">
							<h2>
							<?php echo xlt('Black list'); ?></h2><b><?php echo xlt('Filter');?>:</b>
							<input id="filter-black-list" type="text">
						</div><select class="form-control" id='black-list' multiple=
						"multiple">
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
						<input id="btnAllRight" type="button" value="&gt;&gt;"><br><br>
						<input id="btnRight" type="button" value="&gt;"><br><br>
						<input id="btnLeft" type="button" value="&lt;"><br><br>
						<input id="btnAllLeft" type="button" value="&lt;&lt;">
					</div>
					<div class="subject-white-list">
						<div class="top-list">
							<h2>
							<?php echo xlt('White list'); ?></h2><b><?php echo xlt('Add manually');?>:</b>
							<input id="add-manually-input" type="text"> <button id=
							"add-manually" type="button" class="btn btn-default btn-add" value="<?php echo xlt('Add');?>"><?php echo xlt('Add');?></button>
						</div><select class="form-control" id='white-list' multiple=
						"multiple" name="white_list[]">
							<?php
											foreach ($white_list as $type) {
												echo "<option value='" . attr($type) . "'> " . text($type) . "</option>";
											}
											?>
						</select>
					</div>
                    </fieldset>
					<?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 col-sm-offset-1 position-override">
                            <div class="btn-group btn-group-pinch" role="group">
                                <button type="button" class="btn btn-default btn-save" id="submit-whitelist" onclick="top.restoreSession()" value="<?php echo xla('Save'); ?>"><?php echo xlt('Save'); ?></button>
                                <input name="submit_form" type="hidden" value="1">
                            </div>
                        </div>
                    </div>
            </form>
		<div>
	</div>
    </div>
    </fieldset>
    </div><!--End of container div-->
    <div class="row">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content oe-modal-content">
                    <div class="modal-header clearfix"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button></div>
                    <div class="modal-body">
                        <iframe src="" id="targetiframe" style="height:600px; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe>  
                    </div>
                    <div class="modal-footer" style="margin-top:0px;">
                       <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button"><?php echo xlt('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $( document ).ready(function() {
            $('#help-href').click (function(){
                document.getElementById('targetiframe').src ='manage_site_files_help.php';
            })
       
        });
    </script>

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
            $('#white-list').prepend("<option value="+new_type+">"+new_type+"<\/option>")
        })

        $('#submit-whitelist').on('click', function () {
            $('#white-list option').prop('selected', true);
            $('#whitelist_form').submit();
        })

    }(jQuery));

    </script>
    
    <?php } //End of if ($GLOBALS['secure_upload'])?>
    <script>
        $(document).ready(function() {
            $('#select_action').on('change', function() {
                if ($(this).val() == 'edit_files') {
                    $('#edit-files-div').show();
                    $('#upload-images-div').hide();
                    $('#upload-pt-edu-div').hide();
                    $('#generate_thumb').hide();
                    $('#file_type_whitelist').hide();
                    $('#button-div').show();
                    $('#select-val').val('edit_files');
                    $('#form_filename').val('');
                    $('#form_filedata').val('');
                } else if ($(this).val() == 'upload_images') {
                    $('#edit-files-div').hide();
                    $('#upload-images-div').show();
                    $('#upload-pt-edu-div').hide();
                    $('#generate_thumb').hide();
                    $('#file_type_whitelist').hide();
                    $('#button-div').show();
                    $('#select-val').val('upload_images');
                    $('#form_filename').val('');
                    $('#form_filedata').val('');
                } else if ($(this).val() == 'upload_pt_ed') {
                    $('#edit-files-div').hide();
                    $('#upload-images-div').hide();
                    $('#upload-pt-edu-div').show();
                    $('#generate_thumb').hide();
                    $('#file_type_whitelist').hide();
                    $('#button-div').show();
                    $('#select-val').val('upload_pt_ed');
                    $('#form_filename').val('');
                    $('#form_filedata').val('');
                } else if ($(this).val() == 'thumbnails') {
                    $('#edit-files-div').hide();
                    $('#upload-images-div').hide();
                    $('#upload-pt-edu-div').hide();
                    $('#generate_thumb').show();
                    $('#file_type_whitelist').hide();
                    $('#button-div').hide();
                    $('#select-val').val('thumbnails');
                    $('#form_filename').val(''); 
                    $('#form_filedata').val('');
                } else if ($(this).val() == 'mime_type') {
                    $('#edit-files-div').hide();
                    $('#upload-images-div').hide();
                    $('#upload-pt-edu-div').hide();
                    $('#generate_thumb').hide();
                    $('#file_type_whitelist').show();
                    $('#button-div').hide();
                    $('#select-val').val('mime_type');
                    $('#form_filename').val('');
                    $('#form_filedata').val('');
                }
                else if ($(this).val() == '') {
                    $('#edit-files-div').hide();
                    $('#upload-images-div').hide();
                    $('#upload-pt-edu-div').hide();
                    $('#generate_thumb').hide();
                    $('#file_type_whitelist').hide();
                    $('#button-div').hide();
                    $('#form_filename').val('');
                    $('#form_filedata').val('');
                }
                //to empty file select if moving out withour saving
                if ($(this).val() != 'upload_pt_ed') {
                    var $el = $('#form_education');
                    $el.wrap('<form>').closest('form').get(0).reset();
                    $el.unwrap();
                }
                if ($(this).val() != 'upload_images') {
                    var $el = $('#form_image');
                    $el.wrap('<form>').closest('form').get(0).reset();
                    $el.unwrap();
                }
                 if ($(this).val() != 'edit_files') {
                    /* var $el = $('#form_filedata');
                    $el.wrap('<form>').closest('form').get(0).reset();
                    $el.unwrap(); */
                    $('#form_filedata').val('');
                }
            });
            var postSelVal = '<?php echo xla($_POST['select-val']);?>';
            var postThumbnails = '';
            var postMIME = '';
            <?php 
                if(isset($_POST['generate_thumbnails'])) {
                    echo "var postThumbnails = 'thumbnails';";
                }
                if(isset($_POST['white_list'])) {
                    echo "var postMIME = 'mime';";
                }
            ?>
            $('#select_action').val(postSelVal);
                       
            if (postSelVal=='edit_files') {
                $('#edit-files-div').show();
                $('#button-div').show();
               //$('#form_filename').val('postSelVal');
            } else if (postSelVal=='upload_images') {
                $('#upload-images-div').show();
                $('#button-div').show();
            } else if (postSelVal=='upload_pt_ed') {
                $('#upload-pt-edu-div').show();
                $('#button-div').show();
            }
            if (postThumbnails=='thumbnails') {
                $('#generate_thumb').show();
            } 
            if (postMIME=='mime') {
                $('#file_type_whitelist').show();
            }
        });
    </script>
    <script>
        $(function() {
            //https://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3
            // We can attach the `fileselect` event to all file inputs on the page
            $(document).on('change', ':file', function() {
                var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                input.trigger('fileselect', [numFiles, label]);
            });

            // We can watch for our custom `fileselect` event like this
            $(document).ready( function() {
                $(':file').on('fileselect', function(event, numFiles, label) {
                    var input = $(this).parents('.input-group').find(':text'),
                    log = numFiles > 1 ? numFiles + ' files selected' : label;
                    
                    if( input.length ) {
                    input.val(log);
                    } 
                    else {
                    if( log ) alert(log);
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function(){
       
            
           <?php if($GLOBALS[ "generate_doc_thumb"] == 1){ // to generate appropriate message if thumbnail is enabled
                echo "var enableThumbnail = 1;";
            } elseif ($GLOBALS[ "generate_doc_thumb"] == 0){
                echo "var enableThumbnail = 0;";
            }
             $imagesdir = 'default/images';
             $edudir = 'default/documents/education';
            ?>
            if (enableThumbnail==1) {
                $('#thumbnail-tooltip').tooltip({title: '<?php echo xlt('Generate missing thumbnails of image files'); ?>'});
            } else if (enableThumbnail==0) {
               $('#thumbnail-tooltip').tooltip({title:'<?php echo xla("First enable Administration - Globals - Documents - Generate Thumbnail"); ?>'});
               $('#generate_thumbnails').tooltip({title:'<?php echo xla("Please enable feature by selecting Administration - Globals - Documents - Generate Thumbnail"); ?>'});
            }
            
            $('#upload-image-tooltip').tooltip({title: '<?php echo xla("Will upload an image file to the  $imagesdir directory. Application should have write permission to the images directory"); ?>'});
            $('#upload-pt-edu-tooltip').tooltip({title: '<?php echo xla("Will upload a PDF file to the  $edudir directory.  Name must be like codetype_code_language.pdf, for example icd9_274.11_en.pdf"); ?>'});
            $('#mime-type-tooltip').tooltip({title: '<?php echo xla("Create a white list of allowed MIME types that can be loaded into the sites/default/documents directory"); ?>'});
        });
        </script>
</body>
</html>

