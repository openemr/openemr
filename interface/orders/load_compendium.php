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

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// This array is an important reference for the supported labs and their NPI
// numbers as known to this program.  The clinic must define at least one
// procedure provider entry for a lab that has a supported NPI number.
//
$lab_npi = array(
  '1235138868' => 'Diagnostic Pathology Medical Group',
  '1235186800' => 'Pathgroup Labs LLC',
  '1598760985' => 'Yosemite Pathology Medical Group',
);

/**
 * Get lab's ID from the users table given its NPI.  If none return 0.
 *
 * @param  string  $npi           The lab's NPI number as known to the system
 * @return integer                The numeric value of the lab's address book entry
 */
function getLabID($npi)
{
    $lrow = sqlQuery(
        "SELECT ppid FROM procedure_providers WHERE " .
        "npi = ? ORDER BY ppid LIMIT 1",
        array($npi)
    );
    if (empty($lrow['ppid'])) {
        return 0;
    }

    return intval($lrow['ppid']);
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Load Compendium")]);
    exit;
}

$form_step   = isset($_POST['form_step']) ? trim($_POST['form_step']) : '0';
$form_status = isset($_POST['form_status' ]) ? trim($_POST['form_status' ]) : '';

if (!empty($_POST['form_import'])) {
    $form_step = 1;
}

// When true the current form will submit itself after a brief pause.
$auto_continue = false;

// Set up main paths.
$EXPORT_FILE = $GLOBALS['temporary_files_dir'] . "/openemr_config.sql";
?>
<html>

<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Load Compendium'); ?></title>
</head>

<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Load Lab Compendium'); ?></h2>
            </div>
        </div>
        <form class="jumbotron py-4" method='post' action='load_compendium.php' enctype='multipart/form-data'>
            <table class="table table-borderless">
                <?php if ($form_step == 0) { ?>
                    <tr>
                        <td class="text-nowrap">
                            <?php echo xlt('Vendor'); ?>
                        </td>
                        <td>
                            <select class='form-control' name='vendor'>
                                <?php foreach ($lab_npi as $key => $value) {
                                    echo "<option value='" . attr($key) . "'";
                                    if (!getLabID($key)) {
                                        // Entries with no matching address book entry will be disabled.
                                        echo " disabled";
                                    }
                                    echo ">" . text($key) . ": " . text($value) . "</option>";
                                } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-nowrap">
                            <?php echo xlt('Action'); ?>
                        </td>
                        <td>
                            <select class='form-control' name='action'>
                                <option value='1'><?php echo xlt('Load Order Definitions'); ?></option>
                                <option value='2'><?php echo xlt('Load Order Entry Questions'); ?></option>
                                <option value='3'><?php echo xlt('Load OE Question Options'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-nowrap">
                            <?php echo xlt('Container Group Name'); ?>
                        </td>
                        <td>
                            <select class='form-control' name='group'>
                                <?php
                                $gres = sqlStatement("SELECT procedure_type_id, name FROM procedure_type " .
                                "WHERE procedure_type = 'grp' ORDER BY name, procedure_type_id");
                                while ($grow = sqlFetchArray($gres)) {
                                    echo "<option value='" . attr($grow['procedure_type_id']) . "'>" .
                                    text($grow['name']) . "</option>";
                                }?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-nowrap">
                            <?php echo xlt('File to Upload'); ?>
                        </td>
                        <td>
                            <div class="custom-file">
                                <label class="custom-file-label" for="userfile"><?php echo xlt('Choose file'); ?></label>
                                <input type='hidden' class="custom-file-input" name='MAX_FILE_SIZE' value='4000000' />
                                <input class='form-control' type='file' name='userfile' id='userfile' />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="submit" class="btn btn-primary btn-save" value='<?php echo xla('Submit'); ?>'>
                                <?php echo xlt('Submit'); ?>
                            </button>
                        </td>
                    </tr>
                <?php }

                echo " <tr>\n";
                echo "  <td colspan='2'>\n";

                if ($form_step == 1) {
                    // Process uploaded config file.
                    if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                        $form_vendor = $_POST['vendor'];
                        $form_action = intval($_POST['action']);
                        $form_group  = intval($_POST['group']);
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
                                    sqlStatement(
                                        "UPDATE procedure_type SET activity = 0 WHERE " .
                                        "parent = ? AND procedure_type = 'ord'",
                                        array($form_group)
                                    );

                                    // What should be uploaded is the "Compendium" spreadsheet provided by
                                    // PathGroup, saved in "Text CSV" format from OpenOffice, using its
                                    // default settings.  Values for each row are:
                                    //   0: Order Code  : mapped to procedure_code of order type
                                    //   1: Order Name  : mapped to name of order type
                                    //   2: Result Code : mapped to procedure_code of result type
                                    //   3: Result Name : mapped to name of result type
                                    //
                                    while (!feof($fhcsv)) {
                                            $acsv = fgetcsv($fhcsv, 4096);
                                        if (count($acsv) < 4 || $acsv[0] == "Order Code") {
                                            continue;
                                        }

                                            $standard_code = empty($acsv[2]) ? '' : ('CPT4:' . $acsv[2]);

                                            // Update or insert the order row, if not already done.
                                            $trow = sqlQuery(
                                                "SELECT * FROM procedure_type WHERE " .
                                                "parent = ? AND procedure_code = ? AND procedure_type = 'ord' " .
                                                "ORDER BY procedure_type_id DESC LIMIT 1",
                                                array($form_group, $acsv[0])
                                            );
                                        if (empty($trow['procedure_type_id']) || $trow['activity'] == 0) {
                                            if (empty($trow['procedure_type_id'])) {
                                                $ptid = sqlInsert(
                                                    "INSERT INTO procedure_type SET " .
                                                    "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?",
                                                    array($form_group, $acsv[1], $lab_id, $acsv[0], 'ord')
                                                );
                                            } else {
                                                $ptid = $trow['procedure_type_id'];
                                                sqlStatement(
                                                    "UPDATE procedure_type SET " .
                                                    "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " .
                                                    "activity = 1 WHERE procedure_type_id = ?",
                                                    array($form_group, $acsv[1], $lab_id, $acsv[0], 'ord', $ptid)
                                                );
                                            }

                                                                sqlStatement(
                                                                    "UPDATE procedure_type SET activity = 0 WHERE " .
                                                                    "parent = ? AND procedure_type = 'res'",
                                                                    array($ptid)
                                                                );
                                        }

                                            // Update or insert the result row.
                                            // Not sure we need this, but what the hell.
                                            $trow = sqlQuery(
                                                "SELECT * FROM procedure_type WHERE " .
                                                "parent = ? AND procedure_code = ? AND procedure_type = 'res' " .
                                                "ORDER BY procedure_type_id DESC LIMIT 1",
                                                array($ptid, $acsv[2])
                                            );
                                            // The following should always be true, otherwise duplicate input row.
                                        if (empty($trow['procedure_type_id']) || $trow['activity'] == 0) {
                                            if (empty($trow['procedure_type_id'])) {
                                                sqlStatement(
                                                    "INSERT INTO procedure_type SET " .
                                                    "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?",
                                                    array($ptid, $acsv[3], $lab_id, $acsv[2], 'res')
                                                );
                                            } else {
                                                $resid = $trow['procedure_type_id'];
                                                sqlStatement(
                                                    "UPDATE procedure_type SET " .
                                                    "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " .
                                                    "activity = 1 WHERE procedure_type_id = ?",
                                                    array($ptid, $acsv[3], $lab_id, $acsv[2], 'res', $resid)
                                                );
                                            }
                                        } // end if
                                    } // end while
                                    // end SFTP
                                } elseif ($form_action == 2) { // load questions
                                    // Delete the vendor's current questions.
                                    sqlStatement(
                                        "DELETE FROM procedure_questions WHERE lab_id = ?",
                                        array($lab_id)
                                    );

                                    // What should be uploaded is the "AOE Questions" spreadsheet provided by
                                    // PathGroup, saved in "Text CSV" format from OpenOffice, using its
                                    // default settings.  Values for each row are:
                                    //   0: OBRCode (order code)
                                    //   1: Question Code
                                    //   2: Question
                                    //   3: "Tips"
                                    //   4: Required (0 = No, 1 = Yes)
                                    //   5: Maxchar (integer length)
                                    //   6: FieldType (FT = free text, DD = dropdown, ST = string)
                                    //
                                    $seq = 0;
                                    $last_code = '';
                                    while (!feof($fhcsv)) {
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

                                        ++$seq;

                                        $required = 0 + $acsv[4];
                                        $maxsize = 0 + $acsv[5];
                                        $fldtype = 'T';

                                        // Figure out field type.
                                        if ($acsv[6] == 'DD') {
                                            $fldtype = 'S';
                                        } elseif (stristr($acsv[3], 'mm/dd/yy') !== false) {
                                            $fldtype = 'D';
                                        } elseif (stristr($acsv[3], 'wks_days') !== false) {
                                            $fldtype = 'G';
                                        } elseif ($acsv[6] == 'FT') {
                                            $fldtype = 'T';
                                        } else {
                                            $fldtype = 'N';
                                        }

                                        $qrow = sqlQuery(
                                            "SELECT * FROM procedure_questions WHERE " .
                                            "lab_id = ? AND procedure_code = ? AND question_code = ?",
                                            array($lab_id, $code, $acsv[1])
                                        );

                                        if (empty($qrow['question_code'])) {
                                                    sqlStatement(
                                                        "INSERT INTO procedure_questions SET " .
                                                        "lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " .
                                                        "required = ?, maxsize = ?, fldtype = ?, options = '', tips = ?,
                                activity = 1, seq = ?",
                                                        array($lab_id, $code, $acsv[1], $acsv[2], $required, $maxsize, $fldtype, $acsv[3], $seq)
                                                    );
                                        } else {
                                                    sqlStatement(
                                                        "UPDATE procedure_questions SET " .
                                                        "question_text = ?, required = ?, maxsize = ?, fldtype = ?, " .
                                                        "options = '', tips = ?, activity = 1, seq = ? WHERE " .
                                                        "lab_id = ? AND procedure_code = ? AND question_code = ?",
                                                        array($acsv[2], $required, $maxsize, $fldtype, $acsv[3], $seq,
                                                        $lab_id,
                                                        $code,
                                                        $acsv[1])
                                                    );
                                        }
                                    } // end while
                                    // end load questions
                                } elseif ($form_action == 3) { // load question options
                                    // What should be uploaded is the "AOE Options" spreadsheet provided
                                    // by YPMG, saved in "Text CSV" format from OpenOffice, using its
                                    // default settings.  Values for each row are:
                                    //   0: OBXCode (question code)
                                    //   1: OBRCode (procedure code)
                                    //   2: Option1 (option text)
                                    //   3: Optioncode (the row is duplicated for each possible value)
                                    //
                                    while (!feof($fhcsv)) {
                                        $acsv = fgetcsv($fhcsv, 4096);
                                        if (count($acsv) < 4 || ($acsv[0] == "OBXCode")) {
                                            continue;
                                        }

                                        $pcode   = trim($acsv[1]);
                                        $qcode   = trim($acsv[0]);
                                        $options = trim($acsv[2]) . ':' . trim($acsv[3]);
                                        if (empty($pcode) || empty($qcode)) {
                                            continue;
                                        }

                                        $qrow = sqlQuery(
                                            "SELECT * FROM procedure_questions WHERE " .
                                            "lab_id = ? AND procedure_code = ? AND question_code = ?",
                                            array($lab_id, $pcode, $qcode)
                                        );
                                        if (empty($qrow['procedure_code'])) {
                                                    continue; // should not happen
                                        } else {
                                            if ($qrow['activity'] == '1' && $qrow['options'] !== '') {
                                                $options = $qrow['options'] . ';' . $options;
                                            }

                                                    sqlStatement(
                                                        "UPDATE procedure_questions SET " .
                                                        "options = ? WHERE " .
                                                        "lab_id = ? AND procedure_code = ? AND question_code = ?",
                                                        array($options, $lab_id, $pcode, $qcode)
                                                    );
                                        }
                                    } // end while
                                } // end load questions
                            } // End Pathgroup

                            // Vendor = YPMG or DPMG
                            //
                            if ($form_vendor == '1598760985' || $form_vendor == '1235138868') {
                                if ($form_action == 1) { // load compendium
                                    // Mark all "ord" rows having the indicated parent as inactive.
                                    sqlStatement(
                                        "UPDATE procedure_type SET activity = 0 WHERE " .
                                        "parent = ? AND procedure_type = 'ord'",
                                        array($form_group)
                                    );
                                    // What should be uploaded is the Order Compendium spreadsheet provided
                                    // by YPMG, saved in "Text CSV" format from OpenOffice, using its
                                    // default settings.  Values for each row are:
                                    //   0: Order code    : mapped to procedure_code
                                    //   1: Order Name    : mapped to name
                                    //   2: Result Code   : ignored (will cause multiple occurrences of the same order code)
                                    //   3: Result Name   : ignored
                                    //
                                    while (!feof($fhcsv)) {
                                            $acsv = fgetcsv($fhcsv, 4096);
                                            $ordercode = trim($acsv[0]);
                                        if (count($acsv) < 2 || $ordercode == "Order Code") {
                                            continue;
                                        }

                                            $trow = sqlQuery(
                                                "SELECT * FROM procedure_type WHERE " .
                                                "parent = ? AND procedure_code = ? AND procedure_type = 'ord' " .
                                                "ORDER BY procedure_type_id DESC LIMIT 1",
                                                array($form_group, $ordercode)
                                            );

                                        if (empty($trow['procedure_type_id'])) {
                                                            sqlStatement(
                                                                "INSERT INTO procedure_type SET " .
                                                                "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " .
                                                                "activity = 1",
                                                                array($form_group, trim($acsv[1]), $lab_id, $ordercode, 'ord')
                                                            );
                                        } else {
                                                                sqlStatement(
                                                                    "UPDATE procedure_type SET " .
                                                                    "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " .
                                                                    "activity = 1 " .
                                                                    "WHERE procedure_type_id = ?",
                                                                    array($form_group, trim($acsv[1]), $lab_id, $ordercode, 'ord',
                                                                    $trow['procedure_type_id'])
                                                                );
                                        }
                                    }
                                } elseif ($form_action == 2) { // load questions
                                    // Mark the vendor's current questions inactive.
                                    sqlStatement(
                                        "DELETE FROM procedure_questions WHERE lab_id = ?",
                                        array($lab_id)
                                    );

                                    // What should be uploaded is the "AOE Questions" spreadsheet provided
                                    // by YPMG, saved in "Text CSV" format from OpenOffice, using its
                                    // default settings.  Values for each row are:
                                    //   0: Order Code
                                    //   1: Question Code
                                    //   2: Question
                                    //   3: Is Required (always "false")
                                    //   4: Field Type ("Free Text", "Pre-Defined Text" or "Drop Down";
                                    //      "Drop Down" was previously "Multiselect Pre-Defined Text" and
                                    //      indicates that more than one choice is allowed)
                                    //   5: Response (just one; the row is duplicated for each possible value)
                                    //
                                    $seq = 0;
                                    $last_code = '';
                                    while (!feof($fhcsv)) {
                                        $acsv = fgetcsv($fhcsv, 4096);
                                        if (count($acsv) < 5 || ($acsv[3] !== "false" && $acsv[3] !== "true")) {
                                            continue;
                                        }

                                        $pcode   = trim($acsv[0]);
                                        $qcode   = trim($acsv[1]);
                                        $required = strtolower(substr($acsv[3], 0, 1)) == 't' ? 1 : 0;
                                        $options = trim($acsv[5]);
                                        if (empty($pcode) || empty($qcode)) {
                                            continue;
                                        }

                                        if ($pcode != $last_code) {
                                            $seq = 0;
                                            $last_code = $pcode;
                                        }

                                        ++$seq;

                                        // Figure out field type.
                                        $fldtype = 'T';
                                        if (strpos($acsv[4], 'Drop') !== false) {
                                            $fldtype = 'S';
                                        } elseif (strpos($acsv[4], 'Multiselect') !== false) {
                                            $fldtype = 'S';
                                        }

                                        $qrow = sqlQuery(
                                            "SELECT * FROM procedure_questions WHERE " .
                                            "lab_id = ? AND procedure_code = ? AND question_code = ?",
                                            array($lab_id, $pcode, $qcode)
                                        );

                                        // If this is the first option value and it's a multi-select list,
                                        // then prepend '+;' here to indicate that.  YPMG does not use those
                                        // but keep this note here for future reference.

                                        if (empty($qrow['procedure_code'])) {
                                                    sqlStatement(
                                                        "INSERT INTO procedure_questions SET " .
                                                        "lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " .
                                                        "fldtype = ?, required = ?, options = ?, seq = ?, activity = 1",
                                                        array($lab_id, $pcode, $qcode, trim($acsv[2]), $fldtype, $required, $options, $seq)
                                                    );
                                        } else {
                                            if ($qrow['activity'] == '1' && $qrow['options'] !== '' && $options !== '') {
                                                $options = $qrow['options'] . ';' . $options;
                                            }

                                                    sqlStatement(
                                                        "UPDATE procedure_questions SET " .
                                                        "question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1 WHERE " .
                                                        "lab_id = ? AND procedure_code = ? AND question_code = ?",
                                                        array(trim($acsv[2]), $fldtype, $required, $options, $lab_id, $pcode, $qcode)
                                                    );
                                        }
                                    } // end while
                                } // end load questions
                            } // End YPMG

                            fclose($fhcsv);
                        } else {
                            echo "<p class='text-danger'>" . xlt('Internal error accessing uploaded file!') . "</p>";
                            $form_step = -1;
                        }
                    } else {
                        echo "<p class='text-danger'>" . xlt('Upload failed!') . "</p>";
                        $form_step = -1;
                    }

                    $auto_continue = true;
                }

                if ($form_step == 2) {
                    $form_status .= xlt('Done') . ".";
                    echo nl2br(text($form_status));
                }

                ++$form_step;
                ?>
            </td>
            </tr>
        </table>
    <input type='hidden' name='form_step' value='<?php echo attr($form_step); ?>' />
    <input type='hidden' name='form_status' value='<?php echo attr($form_status); ?>' />
</form>

<?php
ob_flush();
flush();
?>

<?php if ($auto_continue) { ?>
<script>
    setTimeout("document.forms[0].submit();", 500);
</script>
<?php } ?>
</div>

</body>
</html>

