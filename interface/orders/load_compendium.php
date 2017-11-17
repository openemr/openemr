<?php
/**
 * Administrative loader for lab compendium data.
 *
 * Supports loading of lab order codes and related order entry questions from CSV
 * format into the procedure_order and procedure_questions tables, respectively.
 *
 * Copyright (C) 2012-2013 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package   OpenEMR
 * @author    Rod Roark <rod@sunsetsystems.com>
 */
set_time_limit(0);
use OpenEMR\Core\Header;

require_once ("../globals.php");
require_once ("$srcdir/acl.inc");

// This array is an important reference for the supported labs and their NPI
// numbers as known to this program. The clinic must define at least one
// procedure provider entry for a lab that has a supported NPI number.
//
$lab_npi = array(
    '1235138868' => 'Diagnostic Pathology Medical Group',
    '1235186800' => 'Pathgroup Labs LLC',
    '1598760985' => 'Yosemite Pathology Medical Group'
);

/**
 * Get lab's ID from the users table given its NPI.
 * If none return 0.
 *
 * @param string $npi
 *            The lab's NPI number as known to the system
 * @return integer The numeric value of the lab's address book entry
 */
function getLabID($npi)
{
    $lrow = sqlQuery("SELECT ppid FROM procedure_providers WHERE " . "npi = ? ORDER BY ppid LIMIT 1", array(
        $npi
    ));
    if (empty($lrow['ppid'])) {
        return 0;
    }
    
    return intval($lrow['ppid']);
}

if (! acl_check('admin', 'super')) {
    die(xlt('Not authorized', '', '', '!'));
}

$form_step = isset($_POST['form_step']) ? trim($_POST['form_step']) : '0';
$form_status = isset($_POST['form_status']) ? trim($_POST['form_status']) : '';

if (! empty($_POST['form_import'])) {
    $form_step = 1;
}

// When true the current form will submit itself after a brief pause.
$auto_continue = false;

// Set up main paths.
$EXPORT_FILE = $GLOBALS['temporary_files_dir'] . "/openemr_config.sql";
?>
<!DOCTYPE html>
<html>

<head>
    <?php Header::setupHeader();?>
<title><?php echo xlt('Load Lab Configuration'); ?></title>
<style>
    @media only screen and (max-width: 1024px) {
        [class*="col-"] {
          width: 100%;
          text-align:left!Important;
        }
        .page-header > h2{
            width:90%;
        }
    }
    .label-div > a {
    display:none;
    }
    .label-div:hover > a {
       display:inline-block; 
    }
    div[id$="_info"] {
        background: #F7FAB3;
        padding: 20px;
        margin: 10px 15px 0px 15px;
    }
    div[id$="_info"] > a {
        margin-left:10px;
    }
</style>
</head>

<body class="body_top">
    <div class="container">
        
        <div class="row">
            <div class="page-header clearfix">
                <h2 class="clearfix"><?php echo xlt('Load Lab Configuration'); ?><a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
            </div>
        </div>
        <div class="row">
           
                <form method='post' action='load_compendium.php'enctype='multipart/form-data'>
                    
                        <fieldset>
                        <div class="row">
                            <div class="col-xs-12 oe-custom-line">
                                <legend name="form_legend" id="form_legend"><?php echo xlt('Select Compendium'); ?></legend>
                            </div>
                            <?php
                                if ($form_step == 0) {
                                ?>
                                    <div class="col-xs-12 oe-custom-line">
                                        <div class="forms col-xs-4 label-div">
                                            <label class="control-label" for="form-vendor"><?php echo xlt('Vendor'); ?>:</label><a href="#vendor_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                                            <?php 
                                            echo "<select name='vendor' name='form-vendor id='form-vendor' class='form-control'>";
                                            foreach ($lab_npi as $key => $value) {
                                                echo "<option value='" . attr($key) . "'";
                                                if (! getLabID($key)) {
                                                    // Entries with no matching address book entry will be disabled.
                                                    echo " disabled";
                                                }
                                                
                                                echo ">" . text($key) . ": " . text($value) . "</option>";
                                            }
                                            echo "</select>";
                                            
                                            ?>
                                        </div>
                                        <div class="forms col-xs-4 label-div">
                                            <label class="control-label" for="action"><?php echo xlt('Action'); ?>:</label><a href="#action_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a> 
                                            <?php 
                                            echo "<select name='action' id='action' class='form-control'>";
                                                echo "<option value='1'>" . xlt('Load Order Definitions') . "</option>";
                                                echo "<option value='2'>" . xlt('Load Order Entry Questions') . "</option>";
                                                echo "<option value='3'>" . xlt('Load OE Question Options') . "</option>";
                                            echo "</select>";
                                            ?>
                                        </div>
                                        <div class="forms col-xs-4 label-div">
                                            <label class="control-label" for="group"><?php echo xlt('Container Group Name'); ?>:</label><a href="#container_group_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                                            <?php echo "<select name='group' id='group' class='form-control'>";
                                            $gres = sqlStatement("SELECT procedure_type_id, name FROM procedure_type " . "WHERE procedure_type = 'grp' ORDER BY name, procedure_type_id");
                                            while ($grow = sqlFetchArray($gres)) {
                                                echo "<option value='" . attr($grow['procedure_type_id']) . "'>" . text($grow['name']) . "</option>";
                                            };
                                            echo "</select>";
                                            ?>
                                        </div>
                                        <?php //The next three divs are the info divs for the above Select boxes ?>
                                        <div class="col-xs-4 col-lg-4">
                                            <div id="vendor_info" class="collapse">
                                                <a href="#vendor_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                                                <p><?php echo xlt("Vendor");?>:</p>
                                                <p><?php echo xlt("Name of one of the three labs or entity that have a compendium that can be uploaded into openEMR");?></p>
                                                <p><?php echo xlt("To be able to select one option an entry for that lab should exist in the address book.");?></p>
                                                <p><?php echo xlt("Currently only Diagnosic Pathology Medical Group, Yosemite Pathology Medical Group and Pathgroup Labs LLC are supported.");?></p>
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-lg-4">
                                            <div id="action_info" class="collapse">
                                                <a href="#action_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                                                <p><?php echo xlt("Action");?>:</p>
                                                <p><?php echo xlt("Load Order Definitions.");?></p>
                                                <p><?php echo xlt("Load Order Entry Questions.");?></p>
                                                <p><?php echo xlt("Load OE Question Options.");?></p>
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-lg-4">
                                            <div id="container_group_info" class="collapse">
                                                <a href="#container_group_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                                                <p><?php echo xlt("Container Group Name");?>:</p>
                                                <p><?php echo xlt("Fill in the blanks.");?></p>
                                                <p><?php echo xlt("Fill in the blanks.");?></p>
                                                <p><?php echo xlt("Fill in the blanks.");?></p>
                                            </div>
                                        </div>
                                    </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 oe-custom-line">
                                <div class="form-group col-xs9 oe-file-div label-div">
                                    <div class="input-group"> 
                                        <label class="input-group-btn">
                                            <span class="btn btn-default">
                                                <?php echo xlt('Browse'); ?>&hellip;<input type="file" id="userfile" name="userfile" style="display: none;" >
                                                <input name="MAX_FILE_SIZE" type="hidden" value="5000000"> 
                                            </span>
                                        </label>
                                        <input type="text" class="form-control" placeholder="<?php echo xlt('Click Browse and select one compendium file...'); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </fieldset>
                        <div class="row">
                            <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                            <div class="form-group clearfix">
                                <div class="col-sm-12 text-left position-override">
                                    <div class="btn-group" role="group">
                                        <!--<input type='submit' value='" . xla('Submit') . "' />-->
                                         <button type="submit" class="btn btn-default btn-save" name='form_save' value='<?php echo xlt('Submit'); ?>'><?php echo xlt('Submit');?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <?php
                            }
                            ?>
                        <div class="row">
                            <div class='col-xs-12'>
                                <?php
                                if ($form_step == 1) {
                                        // Process uploaded config file.
                                        if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                                            $form_vendor = $_POST['vendor'];
                                            $form_action = intval($_POST['action']);
                                            $form_group = intval($_POST['group']);
                                            $lab_id = getLabID($form_vendor);
                                            
                                            $form_status .= xlt('Applying') . "...<br />";
                                            echo nl2br(text($form_status));
                                            
                                            $fhcsv = fopen($_FILES['userfile']['tmp_name'], "r");
                                            
                                            if ($fhcsv) {
                                                // Vendor = Pathgroup
                                                //
                                                if ($form_vendor == '1235186800') {
                                                    if ($form_action == 1) { // load compendium
                                                                             // Mark all "ord" rows having the indicated parent as inactive.
                                                        sqlStatement("UPDATE procedure_type SET activity = 0 WHERE " . "parent = ? AND procedure_type = 'ord'", array(
                                                            $form_group
                                                        ));
                                                        
                                                        // What should be uploaded is the "Compendium" spreadsheet provided by
                                                        // PathGroup, saved in "Text CSV" format from OpenOffice, using its
                                                        // default settings. Values for each row are:
                                                        // 0: Order Code : mapped to procedure_code of order type
                                                        // 1: Order Name : mapped to name of order type
                                                        // 2: Result Code : mapped to procedure_code of result type
                                                        // 3: Result Name : mapped to name of result type
                                                        //
                                                        while (! feof($fhcsv)) {
                                                            $acsv = fgetcsv($fhcsv, 4096);
                                                            if (count($acsv) < 4 || $acsv[0] == "Order Code") {
                                                                continue;
                                                            }
                                                            
                                                            $standard_code = empty($acsv[2]) ? '' : ('CPT4:' . $acsv[2]);
                                                            
                                                            // Update or insert the order row, if not already done.
                                                            $trow = sqlQuery("SELECT * FROM procedure_type WHERE " . "parent = ? AND procedure_code = ? AND procedure_type = 'ord' " . "ORDER BY procedure_type_id DESC LIMIT 1", array(
                                                                $form_group,
                                                                $acsv[0]
                                                            ));
                                                            if (empty($trow['procedure_type_id']) || $trow['activity'] == 0) {
                                                                if (empty($trow['procedure_type_id'])) {
                                                                    $ptid = sqlInsert("INSERT INTO procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?", array(
                                                                        $form_group,
                                                                        $acsv[1],
                                                                        $lab_id,
                                                                        $acsv[0],
                                                                        'ord'
                                                                    ));
                                                                    } else {
                                                                    $ptid = $trow['procedure_type_id'];
                                                                    sqlStatement("UPDATE procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " . "activity = 1 WHERE procedure_type_id = ?", array(
                                                                        $form_group,
                                                                        $acsv[1],
                                                                        $lab_id,
                                                                        $acsv[0],
                                                                        'ord',
                                                                        $ptid
                                                                    ));
                                                                }
                                                                
                                                                sqlStatement("UPDATE procedure_type SET activity = 0 WHERE " . "parent = ? AND procedure_type = 'res'", array(
                                                                    $ptid
                                                                ));
                                                            }
                                                            
                                                            // Update or insert the result row.
                                                            // Not sure we need this, but what the hell.
                                                            $trow = sqlQuery("SELECT * FROM procedure_type WHERE " . "parent = ? AND procedure_code = ? AND procedure_type = 'res' " . "ORDER BY procedure_type_id DESC LIMIT 1", array(
                                                                $ptid,
                                                                $acsv[2]
                                                            ));
                                                            // The following should always be true, otherwise duplicate input row.
                                                            if (empty($trow['procedure_type_id']) || $trow['activity'] == 0) {
                                                                if (empty($trow['procedure_type_id'])) {
                                                                    sqlInsert("INSERT INTO procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?", array(
                                                                        $ptid,
                                                                        $acsv[3],
                                                                        $lab_id,
                                                                        $acsv[2],
                                                                        'res'
                                                                    ));
                                                                } else {
                                                                    $resid = $trow['procedure_type_id'];
                                                                    sqlStatement("UPDATE procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " . "activity = 1 WHERE procedure_type_id = ?", array(
                                                                        $ptid,
                                                                        $acsv[3],
                                                                        $lab_id,
                                                                        $acsv[2],
                                                                        'res',
                                                                        $resid
                                                                    ));
                                                                }
                                                            } // end if
                                                        } // end while
                                                    } // end load compendium

                                                    else if ($form_action == 2) { // load questions
                                                                                  // Delete the vendor's current questions.
                                                        sqlStatement("DELETE FROM procedure_questions WHERE lab_id = ?", array(
                                                            $lab_id
                                                        ));
                                                        
                                                        // What should be uploaded is the "AOE Questions" spreadsheet provided by
                                                        // PathGroup, saved in "Text CSV" format from OpenOffice, using its
                                                        // default settings. Values for each row are:
                                                        // 0: OBRCode (order code)
                                                        // 1: Question Code
                                                        // 2: Question
                                                        // 3: "Tips"
                                                        // 4: Required (0 = No, 1 = Yes)
                                                        // 5: Maxchar (integer length)
                                                        // 6: FieldType (FT = free text, DD = dropdown, ST = string)
                                                        //
                                                        $seq = 0;
                                                        $last_code = '';
                                                        while (! feof($fhcsv)) {
                                                            $acsv = fgetcsv($fhcsv, 4096);
                                                            if (count($acsv) < 7 || $acsv[4] == "Required") {
                                                                continue;
                                                            }
                                                            
                                                            $code = trim($acsv[0]);
                                                            if (empty($code)) {
                                                                continue;
                                                            }
                                                            
                                                            if ($code != $last_code) {
                                                                $seq = 0;
                                                                $last_code = $code;
                                                            }
                                                            
                                                            ++ $seq;
                                                            
                                                            $required = 0 + $acsv[4];
                                                            $maxsize = 0 + $acsv[5];
                                                            $fldtype = 'T';
                                                            
                                                            // Figure out field type.
                                                            if ($acsv[6] == 'DD') {
                                                                $fldtype = 'S';
                                                            } else if (stristr($acsv[3], 'mm/dd/yy') !== false) {
                                                                $fldtype = 'D';
                                                            } else if (stristr($acsv[3], 'wks_days') !== false) {
                                                                $fldtype = 'G';
                                                            } else if ($acsv[6] == 'FT') {
                                                                $fldtype = 'T';
                                                            } else {
                                                                $fldtype = 'N';
                                                            }
                                                            
                                                            $qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array(
                                                                $lab_id,
                                                                $code,
                                                                $acsv[1]
                                                            ));
                                                            
                                                            if (empty($qrow['question_code'])) {
                                                                sqlStatement("INSERT INTO procedure_questions SET " . "lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " . "required = ?, maxsize = ?, fldtype = ?, options = '', tips = ?,
                                                    activity = 1, seq = ?", array(
                                                                    $lab_id,
                                                                    $code,
                                                                    $acsv[1],
                                                                    $acsv[2],
                                                                    $required,
                                                                    $maxsize,
                                                                    $fldtype,
                                                                    $acsv[3],
                                                                    $seq
                                                                ));
                                                            } else {
                                                                sqlStatement("UPDATE procedure_questions SET " . "question_text = ?, required = ?, maxsize = ?, fldtype = ?, " . "options = '', tips = ?, activity = 1, seq = ? WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array(
                                                                    $acsv[2],
                                                                    $required,
                                                                    $maxsize,
                                                                    $fldtype,
                                                                    $acsv[3],
                                                                    $seq,
                                                                    $lab_id,
                                                                    $code,
                                                                    $acsv[1]
                                                                ));
                                                            }
                                                        } // end while
                                                    } // end load questions

                                                    else if ($form_action == 3) { // load question options
                                                                                  // What should be uploaded is the "AOE Options" spreadsheet provided
                                                                                  // by YPMG, saved in "Text CSV" format from OpenOffice, using its
                                                                                  // default settings. Values for each row are:
                                                                                  // 0: OBXCode (question code)
                                                                                  // 1: OBRCode (procedure code)
                                                                                  // 2: Option1 (option text)
                                                                                  // 3: Optioncode (the row is duplicated for each possible value)
                                                                                  //
                                                        while (! feof($fhcsv)) {
                                                            $acsv = fgetcsv($fhcsv, 4096);
                                                            if (count($acsv) < 4 || ($acsv[0] == "OBXCode")) {
                                                                continue;
                                                            }
                                                            
                                                            $pcode = trim($acsv[1]);
                                                            $qcode = trim($acsv[0]);
                                                            $options = trim($acsv[2]) . ':' . trim($acsv[3]);
                                                            if (empty($pcode) || empty($qcode)) {
                                                                continue;
                                                            }
                                                            
                                                            $qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array(
                                                                $lab_id,
                                                                $pcode,
                                                                $qcode
                                                            ));
                                                            if (empty($qrow['procedure_code'])) {
                                                                continue; // should not happen
                                                            } else {
                                                                if ($qrow['activity'] == '1' && $qrow['options'] !== '') {
                                                                    $options = $qrow['options'] . ';' . $options;
                                                                }
                                                                
                                                                sqlStatement("UPDATE procedure_questions SET " . "options = ? WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array(
                                                                    $options,
                                                                    $lab_id,
                                                                    $pcode,
                                                                    $qcode
                                                                ));
                                                            }
                                                        } // end while
                                                    } // end load questions
                                                } // End Pathgroup
                                                  
                                                // Vendor = YPMG or DPMG
                                                  //
                                                if ($form_vendor == '1598760985' || $form_vendor == '1235138868') {
                                                    if ($form_action == 1) { // load compendium
                                                                             // Mark all "ord" rows having the indicated parent as inactive.
                                                        sqlStatement("UPDATE procedure_type SET activity = 0 WHERE " . "parent = ? AND procedure_type = 'ord'", array(
                                                            $form_group
                                                        ));
                                                        // What should be uploaded is the Order Compendium spreadsheet provided
                                                        // by YPMG, saved in "Text CSV" format from OpenOffice, using its
                                                        // default settings. Values for each row are:
                                                        // 0: Order code : mapped to procedure_code
                                                        // 1: Order Name : mapped to name
                                                        // 2: Result Code : ignored (will cause multiple occurrences of the same order code)
                                                        // 3: Result Name : ignored
                                                        //
                                                        while (! feof($fhcsv)) {
                                                            $acsv = fgetcsv($fhcsv, 4096);
                                                            $ordercode = trim($acsv[0]);
                                                            if (count($acsv) < 2 || $ordercode == "Order Code") {
                                                                continue;
                                                            }
                                                            
                                                            $trow = sqlQuery("SELECT * FROM procedure_type WHERE " . "parent = ? AND procedure_code = ? AND procedure_type = 'ord' " . "ORDER BY procedure_type_id DESC LIMIT 1", array(
                                                                $form_group,
                                                                $ordercode
                                                            ));
                                                            
                                                            if (empty($trow['procedure_type_id'])) {
                                                                sqlStatement("INSERT INTO procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " . "activity = 1", array(
                                                                    $form_group,
                                                                    trim($acsv[1]),
                                                                    $lab_id,
                                                                    $ordercode,
                                                                    'ord'
                                                                ));
                                                            } else {
                                                                sqlStatement("UPDATE procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " . "activity = 1 " . "WHERE procedure_type_id = ?", array(
                                                                    $form_group,
                                                                    trim($acsv[1]),
                                                                    $lab_id,
                                                                    $ordercode,
                                                                    'ord',
                                                                    $trow['procedure_type_id']
                                                                ));
                                                            }
                                                        }
                                                    } else if ($form_action == 2) { // load questions
                                                                                    // Mark the vendor's current questions inactive.
                                                        sqlStatement("DELETE FROM procedure_questions WHERE lab_id = ?", array(
                                                            $lab_id
                                                        ));
                                                        
                                                        // What should be uploaded is the "AOE Questions" spreadsheet provided
                                                        // by YPMG, saved in "Text CSV" format from OpenOffice, using its
                                                        // default settings. Values for each row are:
                                                        // 0: Order Code
                                                        // 1: Question Code
                                                        // 2: Question
                                                        // 3: Is Required (always "false")
                                                        // 4: Field Type ("Free Text", "Pre-Defined Text" or "Drop Down";
                                                        // "Drop Down" was previously "Multiselect Pre-Defined Text" and
                                                        // indicates that more than one choice is allowed)
                                                        // 5: Response (just one; the row is duplicated for each possible value)
                                                        //
                                                        $seq = 0;
                                                        $last_code = '';
                                                        while (! feof($fhcsv)) {
                                                            $acsv = fgetcsv($fhcsv, 4096);
                                                            if (count($acsv) < 5 || ($acsv[3] !== "false" && $acsv[3] !== "true")) {
                                                                continue;
                                                            }
                                                            
                                                            $pcode = trim($acsv[0]);
                                                            $qcode = trim($acsv[1]);
                                                            $required = strtolower(substr($acsv[3], 0, 1)) == 't' ? 1 : 0;
                                                            $options = trim($acsv[5]);
                                                            if (empty($pcode) || empty($qcode)) {
                                                                continue;
                                                            }
                                                            
                                                            if ($pcode != $last_code) {
                                                                $seq = 0;
                                                                $last_code = $pcode;
                                                            }
                                                            
                                                            ++ $seq;
                                                            
                                                            // Figure out field type.
                                                            $fldtype = 'T';
                                                            if (strpos($acsv[4], 'Drop') !== false) {
                                                                $fldtype = 'S';
                                                            } else if (strpos($acsv[4], 'Multiselect') !== false) {
                                                                $fldtype = 'S';
                                                            }
                                                            
                                                            $qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array(
                                                                $lab_id,
                                                                $pcode,
                                                                $qcode
                                                            ));
                                                            
                                                            // If this is the first option value and it's a multi-select list,
                                                            // then prepend '+;' here to indicate that. YPMG does not use those
                                                            // but keep this note here for future reference.
                                                            
                                                            if (empty($qrow['procedure_code'])) {
                                                                sqlStatement("INSERT INTO procedure_questions SET " . "lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " . "fldtype = ?, required = ?, options = ?, seq = ?, activity = 1", array(
                                                                    $lab_id,
                                                                    $pcode,
                                                                    $qcode,
                                                                    trim($acsv[2]),
                                                                    $fldtype,
                                                                    $required,
                                                                    $options,
                                                                    $seq
                                                                ));
                                                            } else {
                                                                if ($qrow['activity'] == '1' && $qrow['options'] !== '' && $options !== '') {
                                                                    $options = $qrow['options'] . ';' . $options;
                                                                }
                                                                
                                                                sqlStatement("UPDATE procedure_questions SET " . "question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1 WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array(
                                                                    trim($acsv[2]),
                                                                    $fldtype,
                                                                    $required,
                                                                    $options,
                                                                    $lab_id,
                                                                    $pcode,
                                                                    $qcode
                                                                ));
                                                            }
                                                        } // end while
                                                    } // end load questions
                                                } // End YPMG
                                                
                                                fclose($fhcsv);
                                            } else {
                                                echo xlt('Internal error accessing uploaded file!');
                                                $form_step = - 1;
                                            }
                                        } else {
                                            echo xlt('Upload failed!');
                                            $form_step = - 1;
                                        }
                                        
                                        $auto_continue = true;
                                    }

                                    if ($form_step == 2) {
                                        $form_status .= xlt('Done') . ".";
                                        echo nl2br(text($form_status));
                                    }

                                    ++ $form_step
                                ?>
                            </div>
                        </div>
                            <input type='hidden' name='form_step' value='<?php echo attr($form_step); ?>' /> 
                            <input type='hidden' name='form_status' value='<?php echo attr($form_status); ?>' />
                    
                    
                </form>
            
        </div>
    </div><!--end of Container Div-->
    <div class="row">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content oe-modal-content">
                    <div class="modal-header clearfix"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button></div>
                    <div class="modal-body">
                        <iframe src="" id="targetiframe" style="height:600px; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe>  
                    </div>
                    <div class="modal-footer" style="margin-top:0px;">
                       <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button"><?php echo xlt('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $( document ).ready(function() {
            $('#help-href').click (function(){
                document.getElementById('targetiframe').src ='load_compendium_help.php';
            })
        });
    </script>
    <script>
        $(function() {
            //https://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3
            // We can attach the `fileselect` event to all file inputs on the page
            $(document).on('change', ':file', function() {
                var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                input.trigger('fileselect', [numFiles, label]);
            });

            // We can watch for our custom `fileselect` event like this
            $(document).ready( function() {
                $(':file').on('fileselect', function(event, numFiles, label) {
                    var input = $(this).parents('.input-group').find(':text'),
                    log = numFiles > 1 ? numFiles + ' files selected' : label;
                    
                    if( input.length ) {
                    input.val(log);
                    } 
                    else {
                    if( log ) alert(log);
                    }
                });
            });

            });
    </script>
    

<?php
ob_flush();
flush();
?>



<?php if ($auto_continue) { ?>
<script language="JavaScript">
 setTimeout("document.forms[0].submit();", 500);
</script>
<?php } ?>

</body>
</html>

