<?php

/* Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

/*
 *
 * This popup is called when adding/editing a calendar event
 *
 */

require_once('../../globals.php');
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

$info_msg = "";

// If we are searching, search.
// first set $result to empty
$result = "";
if (!empty($_REQUEST['searchby']) && !empty($_REQUEST['searchparm'])) {
    $searchby = $_REQUEST['searchby'];
    $searchparm = trim($_REQUEST['searchparm']);

    if ($searchby == "Last") {
        $result = getPatientLnames("$searchparm", "*");
    } elseif ($searchby == "Phone") {                  //(CHEMED) Search by phone number
        $result = getPatientPhone("$searchparm", "*");
    } elseif ($searchby == "ID") {
        $result = getPatientId("$searchparm", "*");
    } elseif ($searchby == "DOB") {
        $result = getPatientDOB(DateToYYYYMMDD($searchparm), "*");
    } elseif ($searchby == "SSN") {
        $result = getPatientSSN("$searchparm", "*");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['common', 'datetime-picker', 'opener']); ?>
    <title><?php echo text(xl('Patient Finder')); ?></title>

    <style>
      form {
        padding: 0;
        margin: 0;
      }

      #searchCriteria {
        text-align: center;
        width: 100%;
        font-weight: bold;
        padding: 3px;
      }

      #searchResultsHeader {
        width: 100%;
        border-collapse: collapse;
      }

      #searchResults {
        width: 100%;
        border-collapse: collapse;
        background-color: var(--white);
        overflow: auto;
      }

      #searchResults tr {
        cursor: hand;
        cursor: pointer;
      }

      #searchResults td {
        /*font-size: 0.7em;*/
        border-bottom: 1px solid var(--light);
      }

      .billing {
        color: var(--danger);
        font-weight: bold;
      }

      /* for search results or 'searching' notification */
      #searchstatus {
        font-weight: bold;
        font-style: italic;
        color: var(--black);
        text-align: center;
      }

      #searchspinner {
        display: inline;
        visibility: hidden;
      }

      /* highlight for the mouse-over */
      .highlight {
        background-color: #336699;
        color: var(--white);
      }
    </style>

    <!-- ViSolve: Verify the noresult parameter -->
    <?php if (isset($_GET["res"])) {
        echo '<script>
    // Pass the variable to parent hidden type and submit
    opener.document.theform.resname.value = "noresult";
    opener.document.theform.submit();
    // Close the window
    window.self.close();
    </script>';
    } ?>
    <!-- ViSolve: Verify the noresult parameter -->
</head>
<body class="body_top">
    <div class="table-responsive-sm">
        <div id="searchCriteria" class="bg-light p-2 pt-3">
            <form method='post' name='theform' id="theform" action='find_patient_popup.php?<?php if (isset($_GET['pflag'])) {
                echo "pflag=0";
                                                                                           } ?>'>
                <div class="form-row">
                    <label for="searchby" class="col-form-label col-form-label-sm col"><?php echo text(xl('Search by:')); ?></label>
                    <select name='searchby' id='searchby' class="form-control form-control-sm col">
                        <option value="Last"><?php echo text(xl('Name')); ?></option>
                        <!-- (CHEMED) Search by phone number -->
                        <option value="Phone"<?php if (!empty($searchby) && ($searchby == 'Phone')) {
                            echo ' selected';
                                             } ?>><?php echo xlt('Phone'); ?></option>
                        <option value="ID"<?php if (!empty($searchby) && ($searchby == 'ID')) {
                            echo ' selected';
                                          } ?>><?php echo xlt('ID'); ?></option>
                        <option value="SSN"<?php if (!empty($searchby) && ($searchby == 'SSN')) {
                            echo ' selected';
                                           } ?>><?php echo xlt('SSN'); ?></option>
                        <option value="DOB"<?php if (!empty($searchby) && ($searchby == 'DOB')) {
                            echo ' selected';
                                           } ?>><?php echo xlt('DOB'); ?></option>
                    </select>
                    <label for="searchparm" class="col-form-label col-form-label-sm col"><?php echo text(xl('for:')); ?></label>
                    <input type='text' class="form-control form-control-sm col" id='searchparm' name='searchparm' size='12' value='<?php echo attr($_REQUEST['searchparm'] ?? ''); ?>' title='<?php echo xla('If name, any part of lastname or lastname,firstname'); ?>' />
                    <div class="col">
                        <input class='btn btn-primary btn-sm' type='submit' id="submitbtn" value='<?php echo xla('Search'); ?>' />
                        <div id="searchspinner"><img src="<?php echo $GLOBALS['webroot'] ?>/interface/pic/ajax-loader.gif" /></div>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!isset($_REQUEST['searchparm'])) : ?>
            <div id="searchstatus"><?php echo text(xl('Enter your search criteria above')); ?></div>
        <?php elseif (!is_countable($result)) : ?>
            <div id="searchstatus" class="alert alert-danger rounded-0"><?php echo text(xl('No records found. Please expand your search criteria.')); ?>
                <br />
                <!--VicarePlus :: If pflag is set the new patient create link will not be displayed -->
                <a class="noresult" href='find_patient_popup.php?res=noresult'
                    <?php
                    if (isset($_GET['pflag']) || (!AclMain::aclCheckCore('patients', 'demo', '', array('write', 'addonly')))) {
                        ?> style="display: none;"
                        <?php
                    }
                    ?> >
                    <?php echo text(xl('Click Here to add a new patient.')); ?>
                </a>
            </div>
        <?php elseif (count($result) >= 100) : ?>
            <div id="searchstatus" class="alert alert-danger rounded-0"><?php echo text(xl('More than 100 records found. Please narrow your search criteria.')); ?></div>
        <?php elseif (count($result) < 100) : ?>
            <div id="searchstatus" class="alert alert-success rounded-0"><?php echo text(count($result ?? [])) . ' '; ?><?php echo text(xl('records found.')); ?></div>
        <?php endif; ?>

        <?php if (isset($result)) : ?>
            <table class="table table-sm">
                <thead id="searchResultsHeader" class="head">
                <tr>
                    <th class="srName"><?php echo xlt('Name'); ?></th>
                    <th class="srPhone"><?php echo xlt('Home Phone'); ?></th> <!-- (CHEMED) Search by phone number -->
                    <th class="srCellPhone"><?php echo xlt('Cell Phone'); ?></th>
                    <th class="srEmail"><?php echo xlt('Email'); ?></th>
                    <th class="srSS"><?php echo xlt('SS'); ?></th>
                    <th class="srDOB"><?php echo xlt('DOB'); ?></th>
                    <th class="srID"><?php echo xlt('ID'); ?></th>
                </tr>
                </thead>
                <tbody id="searchResults">
                <?php
                if (is_countable($result)) {
                    foreach ($result as $iter) {
                        $iterpid = $iter['pid'];
                        $iterlname = $iter['lname'];
                        $iterfname = $iter['fname'];
                        $itermname = $iter['mname'];
                        $iterdob = $iter['DOB'];
                        // If billing note exists, then it gets special coloring and an extra line of output
                        // in the 'name' column.
                        $trClass = "oneresult";
                        if (!empty($iter['billing_note'])) {
                            $trClass .= " billing";
                        }

                        echo " <tr class='" . $trClass . "' id='" .
                            attr($iterpid . "~" . $iterlname . "~" . $iterfname . "~" . $iterdob) . "'>";
                        echo "  <td class='srName'>" . text($iterlname . ", " . $iterfname . " " . $itermname);
                        if (!empty($iter['billing_note'])) {
                            echo "<br />" . text($iter['billing_note']);
                        }
                        echo "</td>\n";
                        echo "  <td class='srPhone'>" . text($iter['phone_home']) . "</td>\n";
                        echo "  <td class='srCellPhone'>" . text($iter['phone_cell']) . "</td>\n";
                        echo "  <td class='srEmail'>" . text($iter['email']) . "</td>\n";
                        echo "  <td class='srSS'>" . text($iter['ss']) . "</td>\n";
                        echo "  <td class='srDOB'>" . text($iter['DOB']) . "</td>\n";
                        echo "  <td class='srID'>" . text($iter['pubpid']) . "</td>\n";
                        echo " </tr>";
                    }
                }
                ?>
                </tbody>
            </table>

        <?php endif; ?>

        <script>

            // jQuery stuff to make the page a little easier to use

            $(function () {
                $("#searchparm").focus();
                $(".oneresult").mouseover(function () {
                    $(this).toggleClass("highlight");
                });
                $(".oneresult").mouseout(function () {
                    $(this).toggleClass("highlight");
                });
                $(".oneresult").click(function () {
                    SelectPatient(this);
                });
                //ViSolve
                $(".noresult").click(function () {
                    SubmitForm(this);
                });

                //$(".event").dblclick(function() { EditEvent(this); });
                $("#theform").submit(function () {
                    SubmitForm(this);
                });

                $('select[name="searchby"]').on('change', function () {
                    if ($(this).val() === 'DOB') {
                        $('#searchparm').datetimepicker({
                            <?php $datetimepicker_timepicker = false; ?>
                            <?php $datetimepicker_showseconds = false; ?>
                            <?php $datetimepicker_formatInput = true; ?>
                            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                        });
                    } else {
                        $('#searchparm').datetimepicker("destroy");
                    }
                });
            });

            function selpid(pid, lname, fname, dob) {
                if (opener.closed || !opener.setpatient)
                    alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
                else
                    opener.setpatient(pid, lname, fname, dob);
                dlgclose();
                return false;
            }

            // show the 'searching...' status and submit the form
            var SubmitForm = function (eObj) {
                $("#submitbtn").css("disabled", "true");
                $("#searchspinner").css("visibility", "visible");
                return true;
            }


            // another way to select a patient from the list of results
            // parts[] ==>  0=PID, 1=LName, 2=FName, 3=DOB
            var SelectPatient = function (eObj) {
                objID = eObj.id;
                var parts = objID.split("~");
                return selpid(parts[0], parts[1], parts[2], parts[3]);
            }

        </script>

    </div>
</body>
</html>
