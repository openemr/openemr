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
        <?php Header::setupHeader(); ?>
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
                        <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                        <div class="form-group clearfix">
                            <div class="col-sm-12 text-left position-override">
                                <div class="btn-group btn-group-pinch" role="group">
                                    <button type='submit' onclick='top.restoreSession()' class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
                                    <button type="button" class="btn btn-link btn-cancel btn-separate-left"onclick="top.restoreSession(); location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>';"><?php echo xlt('Cancel');?></button>
                                </div>
                            </div>
                        </div>
                        
                    </form>
            </div>
        </div>
            
    <?php
    formFooter();
    ?>