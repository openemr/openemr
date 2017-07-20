<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Jacob Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+
use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

formHeader("Form:Clinical Instructions Form");
$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
$check_res = $formid ? formFetch("form_clinical_instructions", $formid) : array();
?>
<html>
    <head>
        <?php Header::setupHeader('bootstrap'); ?>
        
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
                
                    <div class="page-header">
                        <h2><?php echo xlt('Clinical Instructions Form'); ?></h2>
                    </div>
                
            </div>
            <div class="row">
                    <?php echo "<form method='post' name='my_form' " . "action='$rootdir/forms/clinical_instructions/save.php?id=" . attr($formid) . "'>\n"; ?>
                        <fieldset>
                        <legend class=""><?php echo xlt('Instructions'); ?></legend>
                            <div class="form-group">
                                <div class="col-sm-10 col-sm-offset-1">
                                    <textarea name="instruction" id ="instruction"  class="form-control" cols="80" rows="5" ><?php echo text($check_res['instruction']); ?></textarea>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <div class="col-sm-12 text-center">
                                <div class="btn-group" role="group">
                                    <a href="javascript:top.restoreSession();document.my_form.submit();" class="btn btn-default btn-save"><?php echo xlt('Save'); ?></a>
                                    <a href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>' class="btn btn-default btn-cancel" onclick="top.restoreSession()"><?php echo xlt('Don\'t Save'); ?></a>
                                </div>
                            </div>
                        </div>
                        
                    </form>
            </div>
        </div>
            
    <?php
    formFooter();
    ?>
