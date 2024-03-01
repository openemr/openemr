<?php

/**
 * Dictation form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    cfapress <cfapress>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2013-2017 bradymiller <bradymiller@users.sourceforge.net>
 * @copyright Copyright (c) 2017-2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 **/

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';

if ($mode == 'new') {
    $save_action = "save.php?mode=new";
} else {
    $save_action = "save.php?mode=update&id=" . $id;
    $obj = formFetch("form_dictation", $_GET["id"]);
}
?>
<html>
<head>
    <title><?php echo xlt("Dictation"); ?></title>
    <?php Header::setupHeader(['ckeditor', 'angular', 'angular-sanitize']);?>
</head>
<body>
<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <h2><?php echo xlt("Dictation"); ?></h2>
        </div>
    </div>
    <div class="row">
        <div class="col-12" id="toptext">
            <form name="my_form" method=post action="<?php echo $rootdir;?>/forms/dictation/<?php echo $save_action ?>" onsubmit="return top.restoreSession()">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <fieldset>
                    <legend><?php echo xlt('Dictation')?></legend>
                    <div class="container">
                        <div class="form-group">
                            <textarea name="dictation" id="editor1" class="form-control" cols="80" rows="15"><?php if ($mode = 'update') { echo text($obj["dictation"]); }?></textarea>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend><?php echo xlt('Additional Notes'); ?></legend>
                    <div class="container">
                        <div class="form-group">
                            <textarea name="additional_notes" id="editor2" class="form-control" cols="80" rows="5"><?php if ($mode = 'update') { echo text($obj["additional_notes"]); } ?></textarea>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <div class="btn-group" role="group">
                        <button type='submit' onclick='top.restoreSession()' class="btn btn-primary btn-save"><?php echo xlt('Save'); ?></button>
                        <button type="button" class="btn btn-secondary btn-cancel" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    CKEDITOR.replace('editor1');
</script>
</body>
</html>
