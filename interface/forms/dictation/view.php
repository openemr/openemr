<?php
/**
 *  Dictation Form Edit
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");
use OpenEMR\Core\Header;

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$obj = formFetch("form_dictation", $id);

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

$clean_html_dictation = trim($purifier->purify($obj{"dictation"}));
$clean_html_additional_notes = trim($purifier->purify($obj{"additional_notes"}));

?>
<html>
<head>
    <title><?php echo xlt('Dictation'); ?></title>
    <?php Header::setupHeader('ckeditor'); ?>
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/custom/ckeditor_config.js?v=<?php echo $v_js_includes; ?>" type="text/javascript"></script>
    <?php require $srcdir."/js/xl/ckeditor.js.php" ?>
</head>
<body class="body_top">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="page-header">
                    <h1><?php echo xlt('Speech Dictation'); ?></h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <form method="post" action="<?php echo $rootdir?>/forms/dictation/save.php?mode=update&id=<?php echo attr($id);?>" name="my_form" onsubmit="return top.restoreSession()">
                    <div class="form-group">
                        <label for="dictation"><?php echo xlt('Dictation: '); ?></label>
                        <textarea class="form-control ckeditor" id="dictation" name="dictation" ><?php echo $clean_html_dictation; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="additional_notes"><?php echo xlt('Additional Notes:'); ?> </label>
                        <textarea class="form-control ckeditor" id="additional_notes" name="additional_notes" ><?php echo $clean_html_additional_notes; ?></textarea>
                    </div>
                    <div class="form-group">
                        <div class="btn-group" role="group">
                            <button type="submit" class="btn btn-default btn-save">
                                <?php echo xlt('Save'); ?>
                            </button>
                            <a href="<?php echo "$rootdir/patient_file/encounter/encounter_top.php";?>" class="btn btn-cancel btn-link" onclick="top.restoreSession()">
                                <?php echo xlt('Cancel'); ?>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>