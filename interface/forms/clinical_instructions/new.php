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
//SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

formHeader("Form:Clinical Instructions Form");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
$check_res = $formid ? formFetch("form_clinical_instructions", $formid) : array();
?>
<html>
    <head>
        <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
    </head>
    <body class="body_top">
        <p><span class="forms-title"><?php echo xlt('Clinical Instructions Form'); ?></span></p>
        </br>
        <?php echo "<form method='post' name='my_form' " . "action='$rootdir/forms/clinical_instructions/save.php?id=" . attr($formid) . "'>\n"; ?>
        <table style="">
            <tr id="">
                <td>
                    <label>
                        <?php echo xlt('Instructions').':'; ?>
                    </label>
                </td>
                <td>
                    <textarea name="instruction" id ="instruction" style="width:500px;height:100px;"><?php echo text($check_res['instruction']); ?></textarea>
                </td>
                <td>
                    <input type='submit'  value='<?php echo xla('Save'); ?>' class="button-css">&nbsp; 
                </td>
            </tr>
        </table>
    </form>    
    <?php
    formFooter();
    ?>
