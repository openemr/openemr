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
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 **/


require_once("../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
?>
<html>
<head>
    <title><?php echo xlt("Dictation"); ?></title>

    <?php Header::setupHeader();?>
</head>
<body class="body_top">
<?php
$obj = formFetch("form_dictation", $_GET["id"]);
?>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class="page-header">
                <h2><?php echo xlt("Dictation"); ?></h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <form method=post action="<?php echo $rootdir?>/forms/dictation/save.php?mode=update&id=<?php echo attr($_GET["id"]);?>" name="my_form">
                <fieldset>
                    <legend class=""><?php echo xlt('Dictation')?></legend>
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-1">
                            <textarea name="dictation" class="form-control" cols="80" rows="15" ><?php echo text($obj{"dictation"});?></textarea>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class=""><?php echo xlt('Additional Notes'); ?></legend>
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-1">
                            <textarea name="additional_notes" class="form-control" cols="80" rows="5" ><?php echo text($obj{"additional_notes"});?></textarea>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group clearfix">
                    <div class="col-sm-12 col-sm-offset-1 position-override">
                        <div class="btn-group oe-opt-btn-group-pinch" role="group">
                            <button type='submit' onclick='top.restoreSession()' class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
                            <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
