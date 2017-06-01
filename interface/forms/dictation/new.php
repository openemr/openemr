<!-- Form generated from formsWiz -->
<?php
/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
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
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */

use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: dictation");
$returnurl = 'encounter_top.php';
?>
<html>
<head>
<?php Header::setupHeader(); ?>
	<script type="text/javascript" src="<?php echo $webroot."/library/custom_template/ckeditor/ckeditor.js" ?>"</script>
	<script src="<?php echo $webroot."/library/custom_template/ckeditor/_samples/sample.js" ?>" type="text/javascript"></script>
	<link href="<?php echo $webroot."/library/custom_template/ckeditor/_samples.css"; ?>" rel="stylesheet" type="text/css" />
</head>
<body class="body_top">
<h1><?php echo xlt('Speech Dictation'); ?></h1><br><br>

<div class="container">
<form method=post action="<?php echo $rootdir;?>/forms/dictation/save.php?mode=new" name="my_form">
<div class="form-group">

<label class="text"><?php echo xlt('Dictation: '); ?></label><br><textarea class="form-control ckeditor" cols=80 rows=24 wrap="virtual" name="dictation" ></textarea>
</div>
<div class="form-group">
<label class="text"><?php echo xlt('Additional Notes:'); ?> </label><br><textarea class="form-control ckeditor" cols=80 rows=8 wrap="virtual" name="additional_notes" ></textarea>
</div>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit"><button><?php echo xlt('Save'); ?></button></a>

<a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link"
 onclick="top.restoreSession()"><button><?php echo xlt('Don\'t Save'); ?></button></a>
</form>
</div>
<?php
formFooter();
?>
