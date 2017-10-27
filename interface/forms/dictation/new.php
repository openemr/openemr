<?php
/**
- * Generated DocBlock
 - *
 - * @package OpenEMR
 - * @link    http://www.open-emr.org
 - * @author  cfapress <cfapress>
 - * @author  bradymiller <bradymiller@users.sourceforge.net>
 - * @author  Robert Down <robertdown@live.com>
 - * @author  Brady Miller <brady.g.miller@gmail.com>
 - * @author  Brady Miller <brady.g.miller@gmail.com>
 - * @copyright Copyright (c) 2008 cfapress <cfapress>
 - * @copyright Copyright (c) 2013 bradymiller <bradymiller@users.sourceforge.net>
 - * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 - * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 - * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 - * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 - **/
use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: dictation");
$returnurl = 'encounter_top.php';
?>
<html>
<head>
<?php Header::setupHeader();?>
</head>
<body class="body_top">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="page-header">
                    <h2><?php echo xlt("Speech Dictation"); ?></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <form  name="my_form" method=post action="<?php echo $rootdir;?>/forms/dictation/save.php?mode=new" onsubmit="return top.restoreSession()">
                    <fieldset>
                            <legend class=""><?php echo xlt('Dictation')?></legend>
                                <div class="form-group">
                                    <div class="col-sm-10 col-sm-offset-1">
                                        <textarea name="dictation"    class="form-control" cols="80" rows="15" ></textarea>
                                    </div>
                                </div>
                    </fieldset>
                    <fieldset>
                            <legend class=""><?php echo xlt('Additional Notes'); ?></legend>
                                <div class="form-group">
                                    <div class="col-sm-10 col-sm-offset-1">
                                        <textarea name="additional_notes"    class="form-control" cols="80" rows="5" ></textarea>
                                    </div>
                                </div>
                    </fieldset>
                    <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 text-left position-override">
                            <div class="btn-group btn-pinch" role="group">
                                <button type='submit' onclick='top.restoreSession()' class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
                                <button type="button" class="btn btn-link btn-cancel btn-separate-left"onclick="top.restoreSession(); location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>';"><?php echo xlt('Cancel');?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
formFooter();
?>
