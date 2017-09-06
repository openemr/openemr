<!-- Form generated from formsWiz -->
<?php

/**
 * Edit Dictation
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
$fake_register_globals=false;
$sanitize_all_escapes=true;

use OpenEMR\Core\Header;
require_once("../globals.php");
require_once("$srcdir/api.inc");
formHeader("Form: dictation");
//$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
$dic = filter_input(INPUT_GET, 'id');
echo $dic;
$sql = "SELECT dictation, additional_notes FROM form_dictation WHERE id = ". $dic;
$res = sqlQuery($sql);
//var_dump($res);

?>
<html><head>
    <?php html_header_show(); ?>
    <title><?php xl('Edit Dictation','e'); ?></title>
    <?php Header::setupHeader(); ?>

</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir;?>/forms/dictation/save.php?mode=review&id=<?php echo $dic; ?>" name="my_form">
    <span class="title"><?php echo xlt('Speech Dictation'); ?></span><br><br>
    <span class=text><?php echo xlt('Dictation: '); ?></span><br><textarea cols=120 rows=24 wrap=virtual name="dictation" >
<?php print $res['dictation']; ?>
</textarea><br>
    <span class=text><?php echo xlt('Additional Notes:'); ?> </span><br><textarea cols=120 rows=8 wrap=virtual name="additional_notes" ><?php print $res['additional_notes']; ?>
</textarea><br>
    <br>
    <a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
    <br>
    <a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link"
       onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save'); ?>]</a>
</form>
<?php
formFooter();
?>
