<?php
/** 
 *  Dictation Form Edit Saved Data
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

$returnurl = 'encounter_top.php';
?>
<html>
<head>
<title><?php echo xlt('Dictation'); ?></title>
   <?php Header::setupHeader(); ?>
   <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/ckeditor-4-7-0/ckeditor.js"</script>
   <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/ckeditor-4-7-0/js/samples/sample.js" type="text/javascript"></script>
   <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/ckeditor-4-7-0/css/samples/samples.css" rel="stylesheet" type="text/css" />
</head>
<body class="body_top">
<div class="container">
<?php
include_once("$srcdir/api.inc");
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$obj = formFetch("form_dictation", $id);
?>
   <form method=post action="<?php echo $rootdir?>/forms/dictation/save.php?mode=update&id=<?php echo attr($id);?>" name="my_form" onclick="top.restoreSession()">
     <div class="page-header">
      <h1><?php echo xlt('Speech Dictation'); ?></h1>
     </div>
     <div class="form-group">
       <label for="dictation"><?php echo xlt('Dictation: '); ?></label><br><textarea class="form-control ckeditor" name="dictation" ><?php 
              $config = HTMLPurifier_Config::createDefault();
              $purifier = new HTMLPurifier($config);
              $clean_html = $purifier->purify($obj{"dictation"});
              echo trim($clean_html); ?></textarea>
     </div>
     <div class="form-group">
       <label for="additional_notes"><?php echo xlt('Additional Notes: '); ?></label><br><textarea class="form-control ckeditor" name="additional_notes" ><?php
              $config = HTMLPurifier_Config::createDefault();
              $purifier = new HTMLPurifier($config);
              $clean_html = $purifier->purify($obj{"additional_notes"});
              echo trim($clean_html); ?></textarea>
     </div>

       <button type="submit" class="btn btn-default btn-save"><?php echo xlt('Update'); ?></button>

       <a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="btn btn-cancel btn-link" onclick="top.restoreSession()"><?php echo xlt('Cancel'); ?></a>
     </form>
</div>

<?php
formFooter();
?>
