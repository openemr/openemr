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
require_once("../../../public/assets/htmlpurifier-4.9.2/library/HTMLPurifier.auto.php");

$returnurl = 'encounter_top.php';
?>
<html>
<head>
<title>Review</title>
   <?php Header::setupHeader(); ?>
   <script type="text/javascript" src="<?php echo $webroot."/library/custom_template/ckeditor/ckeditor.js" ?>"</script>
   <script src="<?php echo $webroot."/library/custom_template/ckeditor/_samples/sample.js" ?>" type="text/javascript"></script>
   <link href="<?php echo $webroot."/library/custom_template/ckeditor/_samples.css"; ?>" rel="stylesheet" type="text/css" />
</head>
<body class="body_top">
<div class="container">
<?php
include_once("$srcdir/api.inc");
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$obj = formFetch("form_dictation", $id);
?>
   <form method=post action="<?php echo $rootdir?>/forms/dictation/save.php?mode=update&id=<?php echo attr($id);?>" name="my_form">
     <div class="page-header">
      <h1><?php echo xlt('Speech Dictation Review/Edit'); ?></h1>
     </div>
     <div class="form-group">
       <label for="dictation"><?php echo xlt('Dictation: '); ?></label><br><textarea class="form-control ckeditor" cols=80 rows=24 wrap="virtual" name="dictation" ><?php 
              $config = HTMLPurifier_Config::createDefault();
              $purifier = new HTMLPurifier($config);
              $clean_html = $purifier->purify($obj{"dictation"});
              echo trim($clean_html); ?></textarea>
     </div>
     <div class="form-group">
       <label for="additional_notes"><?php echo xlt('Additional Notes: '); ?></span><br><textarea class="form-control ckeditor"cols=80 rows=8 wrap="virtual" name="additional_notes" ><?php
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
