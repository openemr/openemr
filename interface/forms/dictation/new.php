<!-- Form generated from formsWiz -->
<?php
use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: dictation");
$returnurl = 'encounter_top.php';
?>
<html>
<head>
<?php Header::setupHeader('bootstrap');?>

<style type="text/css" title="mystyles" media="all">
 
    .form-group{
        margin-bottom: 5px;
        
    }
    legend{
        border-bottom: 2px solid #E5E5E5;
        background:#E5E5E5;
        padding-left:10px;
    }
    .form-horizontal .control-label {
        padding-top: 2px;
    }
    fieldset{
        background-color: #F2F2F2;
        margin-bottom:10px;
        padding-bottom:15px;
    }
</style>
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
                                        <textarea name="dictation"  class="form-control" cols="80" rows="15" ></textarea>
                                    </div>
                                </div>
                    </fieldset>
                    <fieldset>
                            <legend class=""><?php echo xlt('Additional Notes'); ?></legend>
                                <div class="form-group">
                                    <div class="col-sm-10 col-sm-offset-1">
                                        <textarea name="additional_notes"   class="form-control" cols="80" rows="5" ></textarea>
                                    </div>
                                </div>
                    </fieldset>
                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <div class="btn-group" role="group">
                                <a href="javascript:top.restoreSession();document.my_form.submit();" class="btn btn-default btn-save"><?php echo xlt('Save'); ?></a>
                                <a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="btn btn-default btn-cancel" onclick="top.restoreSession()"><?php echo xlt('Don\'t Save'); ?></a>
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
