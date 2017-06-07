<?php
/** 
 *  Dictation Form
 * 
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @copyright Copyright (c) 2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * 
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
<div class="container">
   <div class="page-header">
      <h1><?php echo xlt('Speech Dictation'); ?></h1><br><br>
   </div>

  <form method=post action="<?php echo $rootdir;?>/forms/dictation/save.php?mode=new" name="my_form">

     <div class="form-group">
       <label for="dictation"><?php echo xlt('Dictation: '); ?></label><br><textarea class="form-control ckeditor" cols=80 rows=24 wrap="virtual" name="dictation" ></textarea>
    </div>
    <div class="form-group">
       <label for="additional_notes"><?php echo xlt('Additional Notes:'); ?> </label><br><textarea class="form-control ckeditor" cols=80 rows=8 wrap="virtual" name="additional_notes" ></textarea>
    </div>

     <button type="submit" class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>

     <a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="btn btn-cancel btn-link" onclick="top.restoreSession()"><?php echo xlt('Cancel'); ?></a>
  </form>
</div>

<?php
formFooter();
?>
