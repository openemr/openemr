<?php

/**
 * This file presents the PMSFH control panel.
 * It uses ajax/javascript to add, delete or edit an issue.
 *
 * Originally culled from /interface/patient_file/summary and adapted...
 *
 * @packageOpenEMR
 * @linkhttp://www.open-emr.org
 * @authorRod Roark <rod@sunsetsystems.com>
 * @authorRay Magauran <magauran@MedFetch.com>
 * @authorBrady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2011 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015-2016 Ray Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @licensehttps://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* TODO: Code cleanup */

$form_folder = "eye_mag";
require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/lists.inc.php');
require_once($GLOBALS['srcdir'] . '/patient.inc.php');
require_once($GLOBALS['srcdir'] . '/options.inc.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once("../../forms/" . $form_folder . "/php/" . $form_folder . "_functions.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

$pid = (int) (empty($_REQUEST['pid']) ? $pid : $_REQUEST['pid']);
$info_msg = "";

// A nonempty thisenc means we are to link the issue to the encounter.
// ie. we are going to use this as a billing issue?
// The Coding Engine does not look at encounters and issue linkage, yet.  It could and perhaps should.
$encounter = 0 + (empty($_REQUEST['encounter']) ? $_SESSION['encounter'] : $_REQUEST['encounter']);

$issue = $_REQUEST['issue'] ?? '';
$deletion = $_REQUEST['deletion'] ?? '';
$form_save = $_REQUEST['form_save'] ?? '';
if (!$pid) {
    $pid = $_SESSION['pid'];
}

$form_id = $_REQUEST['form_id'];
$form_type = $_REQUEST['form_type'];
$uniqueID = $_REQUEST['uniqueID'];

if ($issue && !AclMain::aclCheckCore('patients', 'med', '', 'write')) {
    die(xlt("Edit is not authorized!"));
}

if (
    !AclMain::aclCheckCore('patients', 'med', '', array(
    'write',
    'addonly'
    ))
) {
    die(xlt("Add is not authorized!"));
}

$PMSFH = build_PMSFH($pid);
$patient = getPatientData($pid, "*");
$providerID = findProvider($pid, $encounter);
if (!($_SESSION['providerID'] ?? '') && $providerID) {
    ($_SESSION['providerID'] = $providerID);
}

$irow = array();
if ($issue) {
    $irow = sqlQuery("SELECT * FROM lists WHERE id = ?", array(
        $issue
    ));
} elseif ($thistype ?? '') {
    $irow['type'] = $thistype;
    $irow['subtype'] = $subtype;
}

if (!empty($irow['type'])) {
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($key == $irow['type']) {
            break;
        }
        ++$type_index;
    }
}

$given = "ROSGENERAL,ROSHEENT,ROSCV,ROSPULM,ROSGI,ROSGU,ROSDERM,ROSNEURO,ROSPSYCH,ROSMUSCULO,ROSIMMUNO,ROSENDOCRINE,ROSCOMMENTS";
$query = "SELECT $given from form_eye_ros where id=? and pid=?";
$rres = sqlQuery($query, array(
    $form_id,
    $pid
));
foreach (explode(',', $given) as $item) {
    $$item = $rres[$item];
}
?>
<html>
<head>
    <title><?php echo xlt('Add New Issue'); ?></title>
    <script>
        var aitypes = new Array(); // issue type attributes
        var aopts = new Array(); // Option objects
        <?php
//This builds the litle quick pick list in this section.
// If the provider has more 2 items already defined in the last month, they are collated
// and ranked by frequency, sort alphabetically and <=10 are listed.
// If not, we use the defaults from list_options/
        $i = '0';

        foreach ($PMSFH[0] as $key => $value) {
            echo " aopts['" . attr($key) . "'] = [];\n";
            $local = '1';
            echo " aitypes['" . attr($key) . "'] = '0';\n";
            if ($key == "PMH") { // "0" = medical_problem_issue_list leave out Dental "4"
                $qry = sqlStatement("SELECT title, title as option_id, diagnosis as codes, count(title) AS freq  FROM `lists` WHERE `type` LIKE ? and subtype = '' and pid in (select pid from form_encounter where provider_id =? and date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()) GROUP BY title order by freq desc limit 20", array(
                    "medical_problem",
                    $_SESSION['providerID']
                ));

                if (sqlNumRows($qry) < '4') { //if they are just starting out, use the list_options for all
                    $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = ? and subtype not like 'eye'", array(
                        "medical_problem_issue_list"
                    ));
                }
            } elseif ($key == "Medication") {
                $qry = sqlStatement("SELECT title, title as option_id, diagnosis as codes, count(title) AS freq  FROM `lists` WHERE `type` LIKE ? and subtype = '' and pid in (select pid from form_encounter where provider_id =? and date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()) GROUP BY title order by freq desc limit 10", array(
                    "medication",
                    $_SESSION['providerID']
                ));
                if (sqlNumRows($qry) < '4') { //if they are just starting out, use the list_options for all
                    $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = ? and subtype not like 'eye'", array(
                        "medication_issue_list"
                    ));
                }
            } elseif ($key == "Surgery") {
                $qry = sqlStatement("SELECT title, title as option_id, diagnosis as codes, count(title) AS freq  FROM `lists` WHERE `type` LIKE ? and
    subtype = '' and pid in (select pid from form_encounter where provider_id =?
    and date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()) GROUP BY title order by freq desc limit 10", array(
                    "surgery",
                    $_SESSION['providerID']
                ));

                if (sqlNumRows($qry) < '4') { //if they are just starting out, use the list_options for all
                    $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = ? and subtype not like 'eye'", array(
                        "surgery_issue_list"
                    ));
                }
            } elseif ($key == "Allergy") {
                $qry = sqlStatement("SELECT title, title as option_id, diagnosis as codes, count(title) AS freq  FROM `lists` WHERE `type` LIKE ? and subtype = '' and pid in (select pid from form_encounter where provider_id =? and date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()) GROUP BY title order by freq desc limit 10", array(
                    "allergy",
                    $_SESSION['providerID']
                ));
                if (sqlNumRows($qry) < '4') { //if they are just starting out, use the list_options for all
                    $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = ? and subtype not like 'eye'", array(
                        "allergy_issue_list"
                    ));
                }
            } elseif ($key == "POH") { // POH medical group
                $query = "SELECT title, title as option_id, diagnosis as codes, count(title) AS freq  FROM `lists` WHERE `type` LIKE 'medical_problem' and subtype = 'eye' and pid in (select pid from form_encounter where provider_id =? and date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()) GROUP BY title order by freq desc limit 10";
                $qry = sqlStatement($query, array(
                    $_SESSION['providerID']
                ));
                if (sqlNumRows($qry) < '4') { //if they are just starting out, use the list_options for all
                    $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = 'medical_problem_issue_list' and subtype = 'eye'");
                }
            } elseif ($key == "POS") { // POS surgery group
                $query = "SELECT title, title as option_id, diagnosis as codes, count(title) AS freq  FROM `lists` WHERE `type` LIKE 'surgery' and subtype = 'eye' and pid in (select pid from form_encounter where provider_id =? and date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()) GROUP BY title order by freq desc limit 10";
                $qry = sqlStatement($query, array(
                    $_SESSION['providerID']
                ));

                if (sqlNumRows($qry) < '4') { //if they are just starting out, use the list_options for all
                    $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = 'surgery_issue_list' and subtype = 'eye'");
                }
            } elseif ($key == "Eye Meds") { // POS surgery group
                $query = "SELECT title, title as option_id, diagnosis as codes, count(title) AS freq FROM `lists` WHERE `type` LIKE 'medication' and subtype = 'eye' and pid in ( select pid from form_encounter where provider_id =? and date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()) GROUP BY title order by freq desc limit 10";
                $qry = sqlStatement($query, array(
                    $_SESSION['providerID']
                ));
                if (sqlNumRows($qry) < '4') { //if they are just starting out, use the list_options for all
                    $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = 'medication_issue_list' and subtype = 'eye'");
                }
            } elseif ($key == "FH") {
                $local = "";
                $qry = "";
            } elseif ($key == "SOCH") {
                $local = "";
                $qry = "";
            } elseif ($key == "ROS") {
                $local = "";
                $qry = "";
            }

            if ($local == "1") { // leave FH/SocHx/ROS for later - done below separately
                while ($res = sqlFetchArray($qry ?? '')) { //Should we take the top 10 and display alphabetically?
                    echo " aopts['" . attr($key) . "'][aopts['" . attr($key) . "'].length] = new Option(" . js_escape(xl_list_label(trim($res['title']))) . ", " . js_escape(trim($res['option_id'])) . ", false, false);\n";
                    if ($res['codes']) {
                        echo " aopts['" . attr($key) . "'][aopts['" . attr($key) . "'].length-1].setAttribute('data-code','" . attr(trim($res['codes'])) . "');\n";
                    }
                }
            }
            ++$i;
        }

        ?>

        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        function newtype(index) {
            var f = document.forms[0];
            var theopts = f.form_titles.options;
            theopts.length = 0;
            if (aopts[index]) {
                var i = 0;
                for (i = 0; i < aopts[index].length; ++i) {
                    theopts[i] = aopts[index][i];
                }
            }

            f.form_type.value = index;
            f.form_occur.options[0].selected = true;

            document.getElementById('row_quick_picks').style.display = i ? '' : 'none'; //select list of things
            document.getElementById('row_title').style.display = '';
            document.getElementById('row_diagnosis').style.display = 'none';
            document.getElementById('row_begindate').style.display = 'none';
            document.getElementById('row_enddate').style.display = 'none';
            document.getElementById('row_reaction').style.display = 'none';
            document.getElementById('row_eye_med').style.display = 'none';
            document.getElementById('row_referredby').style.display = 'none';
            document.getElementById('row_classification').style.display = 'none';
            document.getElementById('row_occurrence').style.display = 'none';
            document.getElementById('row_comments').style.display = 'none';
            document.getElementById('row_outcome').style.display = 'none';
            document.getElementById('row_destination').style.display = 'none';
            document.getElementById('row_social').style.display = 'none';
            document.getElementById('row_FH').style.display = 'none';
            document.getElementById('row_ROS').style.display = 'none';
            document.getElementById('row_PLACEHOLDER').style.display = 'none';
            document.getElementById('cancel_button').style.display = 'none';
            document.getElementById('row_eye_med').style.display = 'none';


            if (index == 'PMH') {
                document.getElementById('title_diagnosis').textContent = "<?php echo xlt('PMH Dx') . ":"; ?>";
                document.getElementById('row_diagnosis').style.display = '';
                document.getElementById('row_begindate').style.display = '';
                document.getElementById('row_enddate').style.display = '';
                document.getElementById('row_occurrence').style.display = '';
                f.form_occur.options[2].selected = true;
                document.getElementById('row_comments').style.display = '';

            } else if (index == 'Allergy') {
                document.getElementById('title_diagnosis').textContent = "<?php echo xlt('Allergic to') . ":"; ?>";
                document.getElementById('row_reaction').style.display = '';
                document.getElementById('row_begindate').style.display = '';
                document.getElementById('row_comments').style.display = '';

            } else if (index == 'Medication') {
                document.getElementById('title_diagnosis').textContent = "<?php echo xlt('Medication') . ":"; ?>";
                document.getElementById('row_begindate').style.display = '';
                document.getElementById('row_enddate').style.display = '';
                document.getElementById('row_comments').style.display = '';
                document.getElementById('form_eye_subtype').checked = false;
                //change Onset to started
                //change resolved to Completed
                document.getElementById('onset').textContent = "<?php echo xlt('Start') . ':'; ?>";
                document.getElementById('resolved').textContent = "<?php echo xlt('Finish') . ':'; ?>";

            } else if (index == 'Eye Meds') {
                document.getElementById('title_diagnosis').textContent = "<?php echo xlt('Medication') . ":"; ?>";
                document.getElementById('row_begindate').style.display = '';
                document.getElementById('row_enddate').style.display = '';
                document.getElementById('row_comments').style.display = '';
                document.getElementById('row_eye_med').style.display = '';
                document.getElementById('form_eye_subtype').checked = true;

                //change Onset to started
                //change resolved to Completed
                document.getElementById('onset').textContent = "<?php echo xlt('Start') . ':'; ?>";
                document.getElementById('resolved').textContent = "<?php echo xlt('Finish') . ':'; ?>";

            } else if ((index == 'Surgery') || (index == 'POS')) {
                document.getElementById('title_diagnosis').textContent = "<?php echo xlt('Procedure') . ':'; ?>";
                document.getElementById('row_diagnosis').style.display = '';

                document.getElementById('row_begindate').style.display = '';
                document.getElementById('row_referredby').style.display = '';
                document.getElementById('form_referredby').title = "<?php echo xla('Name of the Surgeon'); ?>";
                document.getElementById('by_whom').textContent = "<?php echo xlt('Surgeon') . ':'; ?>";
                document.getElementById('onset').textContent = "<?php echo xlt('Date') . ':'; ?>";
                document.getElementById('row_outcome').style.display = '';
                document.getElementById('row_comments').style.display = '';

            } else if (index == 4) { //Dental so skip it
            } else if (index == 'POH') {
                document.getElementById('title_diagnosis').textContent = "<?php echo xlt('Eye Dx{{eye diagnosis}}') . ":"; ?>";
                document.getElementById('row_diagnosis').style.display = '';
                document.getElementById('row_begindate').style.display = '';
                document.getElementById('row_referredby').style.display = '';
                document.getElementById('by_whom').textContent = "<?php echo xlt('Collaborator') . ":"; ?>";
                document.getElementById('form_referredby').title = "<?php echo xla('Co-managing/referring provider'); ?>";
                document.getElementById('onset').textContent = "<?php echo xlt('Date') . ":"; ?>";
                document.getElementById('row_comments').style.display = '';

            } else if (index == 'FH') {
                document.getElementById('row_title').style.display = 'none';
                document.getElementById('row_FH').style.display = '';

            } else if (index == 'SOCH') {
                document.getElementById('row_title').style.display = 'none';
                document.getElementById('row_social').style.display = '';
                document.getElementById('cancel_button').style.display = '';

            } else if (index == 'ROS') {
                document.getElementById('row_title').style.display = 'none';
                document.getElementById('row_ROS').style.display = '';

            } else {
                document.getElementById('title_diagnosis').textContent = "<?php echo xlt('Eye Dx{{eye diagnosis}}') . ":"; ?>";
                document.getElementById('row_diagnosis').style.display = '';
                document.getElementById('row_begindate').style.display = '';
                document.getElementById('row_referredby').style.display = '';
                document.getElementById('form_referredby').title = "<?php echo xla('Referring provider'); ?>";
                document.getElementById('by_whom').textContent = "<?php echo xlt('Collaborator') . ":"; ?>";
                document.getElementById('onset').textContent = "<?php echo xlt('Date') . ":"; ?>";
                document.getElementById('row_comments').style.display = '';
                document.getElementById('row_PLACEHOLDER').style.display = '';
            }
            return false;
        }
        // If a clickoption title is selected, copy it to the title field.
        // We also want to copy any other fields present in obj.PMSFH_options
        // We need to build this object first.  The base install options will need ICD-10 codes attached
        // to make this work.
        // f.form_title.diagnosis = f.form_titles.options[f.form_titles.selectedIndex].text;
        function set_text() {
            var f = document.forms[0];
            f.form_title.value = f.form_titles.options[f.form_titles.selectedIndex].text;
            f.form_diagnosis.value = f.form_titles.options[f.form_titles.selectedIndex].getAttribute('data-code');
            f.form_titles.selectedIndex = -1;
        }

        function refreshIssue() {
            parent.refresh_page();
        }

        function submit_this_form() {
            var url = "../../forms/eye_mag/save.php?PMSFH_save=1&mode=update&form_save=1";
            var formData = $("form#theform").serialize();
            var f = document.forms[0];
            top.restoreSession();
            $.ajax({
                type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                url: url, // the url where we want to POST
                data: formData // our data object
            }).done(function(result) {
                f.form_title.value = '';
                f.form_diagnosis.value = '';
                f.form_begin.value = '';
                f.form_end.value = '';
                f.form_referredby.value = '';
                f.form_reaction.value = '';
                f.form_classification.value = '';
                f.form_comments.value = '';
                f.form_outcome.value = '';
                f.form_destination.value = '';
                f.issue.value = '';
                parent.populate_form(result);
            });
        }
        // Process click on Delete link.
        function deleteme() {
            var url = "../../forms/eye_mag/save.php?PMSFH_save=1&mode=update&form_save=1";
            var f = document.forms[0];
            top.restoreSession();
            $.ajax({
                type: 'POST',
                data: {
                    pid: <?php echo attr($pid); ?>,
                    issue: f.issue.value,
                    deletion: '1',
                    PMSFH: '1'
                },
                url: url
            }).done(function(result) {
                // CLEAR THE FORM TOO...
                f.form_title.value = '';
                f.form_diagnosis.value = '';
                f.form_begin.value = '';
                f.form_end.value = '';
                f.form_referredby.value = '';
                f.form_reaction.value = '';
                f.form_classification.value = '';
                f.form_comments.value = '';
                f.form_occur.options[0].selected = true;
                f.form_outcome.value = '';
                f.form_destination.value = '';
                f.issue.value = '';
                parent.populate_form(result);
            });
        }

        function imdeleted() {
            closeme();
        }

        function clearme() {
            negate_radio('radio_tobacco');
            var f = document.forms[0];

            // f.radio_tobacco.value = '';
            f.form_diagnosis.value = '';
            f.form_begin.value = '';
            f.form_end.value = '';
            f.form_referredby.value = '';
            f.form_reaction.value = '';
            f.form_classification.value = '';
            f.form_comments.value = '';
            f.form_outcome.value = '';
            f.form_destination.value = '';
            f.issue.value = '';
        }
        // Called when the Active checkbox is clicked.  For consistency we
        // use the existence of an end date to indicate inactivity, even
        // though the simple version of the form does not show an end date.
        function resolvedClicked(cb) {
            var f = document.forms[0];
            if (!cb.checked) {
                f.form_end.value = '';
                f.delete_button.classList.remove("nodisplay");
            } else {
                var today = new Date();
                f.form_end.value = '' + (today.getYear() + 1900) + '-' +
                    (today.getMonth() + 1) + '-' + today.getDate();
                f.delete_button.classList.add("nodisplay");
            }
        }

        // Called when resolved outcome is chosen and the end date is entered.
        function outcomeClicked(cb) {
            var f = document.forms[0];
            if (cb.value == '1') {
                var today = new Date();
                f.form_end.value = '' + (today.getYear() + 1900) + '-' +
                    ("0" + (today.getMonth() + 1)).slice(-2) + '-' + ("0" + today.getDate()).slice(-2);
                f.form_end.focus();
            }
        }
        // This is for callback by the find-code popup.
        // Appends to or erases the current list of diagnoses.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            var s = f.form_diagnosis.value;
            var title = f.form_title.value;
            if (code) {
                if (s.length > 0) s += ';';
                s += codetype + ':' + code;
            } else {
                s = '';
            }
            f.form_diagnosis.value = s;
            if (title == '') f.form_title.value = codedesc;
        }

        // This invokes the find-code popup.
        function sel_diagnosis() {
            var f = document.forms[0];
            term = f.form_title.value;
            <?php
            if ((($irow['type'] ?? '') == 'PMH') || (($irow['type'] ?? '') == 'POH')) {
                ?>
            dlgopen('../../patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("medical_problem", "csv")) ?>&search_term=' + term, '_blank', 500, 400);
                <?php
            } else {
                ?>
            dlgopen('../../patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("diagnosis", "csv")) ?>&search_term=' + term, '_blank', 500, 400);
                <?php
            }
            ?>
            f.form_save.focus();
        }

        // Check for errors when the form is submitted.
        function validate() {
            var f = document.forms[0];
            if (f.form_begin.value > f.form_end.value && (f.form_end.value)) {
                alert("<?php echo addslashes(xl('Please Enter End Date greater than Begin Date!')); ?>");
                return false;
            }
            if (f.form_type.value != 'ROS' && f.form_type.value != 'FH' && f.form_type.value != 'SOCH') {
                if (!f.form_title.value) {
                    alert("<?php echo addslashes(xl('Please enter a title!')); ?>");
                    return false;
                }
            }
            return true;
        }

        // Supports customizable forms (currently just for IPPF).
        function divclick(cb, divid) {
            var divstyle = document.getElementById(divid).style;
            if (cb.checked) {
                divstyle.display = 'block';
            } else {
                divstyle.display = 'none';
            }
            return true;
        }
        //function for selecting the smoking status in drop down list based on the selection in radio button.
        function smoking_statusClicked(cb) {
            if (cb.value == 'currenttobacco') {
                document.getElementById('form_tobacco').selectedIndex = 1;
            } else if (cb.value == 'nevertobacco') {
                document.getElementById('form_tobacco').selectedIndex = 4;
            } else if (cb.value == 'quittobacco') {
                document.getElementById('form_tobacco').selectedIndex = 3;
            } else if (cb.value == 'not_applicabletobacco') {
                document.getElementById('form_tobacco').selectedIndex = 6;
            }
            radioChange(document.getElementById('form_tobacco').value);
        }
        //function for selecting the smoking status in radio button based on the selection of drop down list.
        function radioChange(rbutton) {
            if (rbutton == 1 || rbutton == 2 || rbutton == 15 || rbutton == 16) {
                document.getElementById('radio_tobacco[current]').checked = true;
            } else if (rbutton == 3) {
                document.getElementById('radio_tobacco[quit]').checked = true;
            } else if (rbutton == 4) {
                document.getElementById('radio_tobacco[never]').checked = true;
            } else if (rbutton == 5 || rbutton == 9) {
                document.getElementById('radio_tobacco[not_applicable]').checked = true;
            } else if (rbutton == '') {
                var radList = document.getElementsByName('radio_tobacco');
                for (var i = 0; i < radList.length; i++) {
                    if (radList[i].checked) radList[i].checked = false;
                }
            }
            //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
            if (rbutton != "") {
                if (code_options_js[rbutton] != "")
                    $("#smoke_code").html(" ( " + code_options_js[rbutton] + " )");
                else
                    $("#smoke_code").html("");
            } else
                $("#smoke_code").html("");
        }

        function setSelectBoxByText(eid, etxt) {
            var eid = document.getElementById(eid);
            for (var i = 0; i < eid.options.length; ++i) {
                if (eid.options[i].text === etxt)
                    eid.options[i].selected = true;
            }
        }

        function clear_option(section) {
            //click the field, erase the Negative radio and input Y
            var f = document.forms[0];
            var name = 'radio_' + section.name;
            var radio = document.getElementById(name);
            radio.checked = false;
            if (section.value == '') {
                section.value = "Y";
                section.select();
            }
        }

        function negate_radio(section) {
            if (section.checked == true) {
                var rfield = section.name.match(/radio_(.*)/);
                document.getElementById(rfield[1]).value = '';
            }
        }
        //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
        var code_options_js = Array();

        <?php
        $smoke_codes = getSmokeCodes();

        foreach ($smoke_codes as $val => $code) {
            echo "code_options_js" . "['" . attr($val) . "']='" . attr($code) . "';\n";
        }
        ?>

    </script>
    <!-- Add Font stuff for the look and feel.  -->

    <?php Header::setupHeader(['datetime-picker', 'purecss', 'shortcut', 'opener', 'dialog'  ]); ?>

    <link rel="stylesheet" href="<?php echo $GLOBALS['rootdir']; ?>/forms/<?php echo $form_folder; ?>/css/style.css">
    <script src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/js/eye_base.php?enc=<?php echo attr($encounter); ?>&providerID=<?php echo attr($providerID); ?>"></script>
</head>

<body>
    <div id="page" style="text-align: justify; text-justify: newspaper;">
        <form method='POST' name='theform' id='theform' action='a_issue.php?pid=<?php echo attr($pid); ?>&encounter=<?php echo attr($encounter); ?>' onsubmit='return validate();'>
            <input type="hidden" name="form_id" id="form_id" value="<?php echo attr($form_id); ?>" />
            <input type="hidden" name="issue" id="issue" value="<?php echo attr($issue); ?>" />
            <input type="hidden" name="uniqueID" id="uniqueID" value="<?php echo attr($uniqueID); ?>" />
            <div class="issues">
                <?php
                $output = '';
                global $counter_header;
                $count_header = '0';
                $output = array();
                foreach ($PMSFH[0] as $key => $value) {
                    $checked = '';
                    if ($key == "POH") {
                        $checked = " checked='checked' ";
                    }

                    $key_short_title = $key;
                    if ($key == "Medication") {
                        $key_short_title = "Meds";
                        $title = "Medications";
                    }

                    if ($key == "Problem") {
                        $key_short_title = "PMH";
                        $title = "Past Medical History";
                    }

                    if ($key == "Surgery") {
                        $key_short_title = "Surg";
                        $title = "Past Surgical History";
                    }

                    if ($key == "SOCH") {
                        $key_short_title = "Soc";
                        $title = "Social History";
                    }
                    if ($key == "Allergy") {
                        $key_short_title = "All";
                        $title = "Allergies";
                    }
                    if ($key == "Eye Meds") {
                        $key_short_title = "EyeM";
                        $title = "Eye Medications";
                    }

                    $HELLO[attr($key) ] = '<input type="radio" name="form_type" id="PMSFH_' . attr($key) . '" value="' . attr($key) . '" ' . $checked . ' onclick="top.restoreSession();newtype(\'' . attr($key) . '\');" /><span>' . '<label class="input-helper input-helper--checkbox" for="PMSFH_' . attr($key) . '" title="' . xla($title ?? '') . '" />' . xlt($key_short_title) . '</label></span>&nbsp;';
                }

//put them in the desired display order
                echo $HELLO['POH'] . $HELLO['POS'] . $HELLO['Eye Meds'] . $HELLO['PMH'] . $HELLO['Medication'] . $HELLO['Surgery'] . $HELLO['Allergy'] . $HELLO['FH'] . $HELLO['SOCH'] . $HELLO['ROS'];
                ?>
            </div>
            <div class="borderShadow text-left issues">
                <style>
                    input[type="text"] {
                        display: inline-block;
                        height: 22px;
                        padding: 0.2em 0.4em;
                        width: 150px;
                        text-align:left;
                        padding-left:5px;
                    }

                    textarea {
                        max-width: 95%;
                        min-width: 95%;
                        overflow: auto;
                        border: 1px solid #ccc;
                        border-radius: 4px;
                        box-shadow: 0 1px 3px #ddd inset;
                        box-sizing: border-box;
                        display: inline-block;
                    }

                </style>
                <table class='border-0 w-100 small'>
                    <tr id='row_quick_picks'>
                        <td class="text-nowrap">&nbsp;</td>
                        <td class="align-top" colspan="3">
                            <select size="7" name='form_titles' onchange='top.restoreSession();set_text();'>
                            </select>
                        </td>
                        <td>
                    </tr>
                    <tr id="row_title">
                        <td class="right font-weight-bold text-nowrap" id='title_diagnosis' style="vertical-align:middle;">
                           <?php echo xlt('Title'); ?>:</td>
                        <td colspan="3">
                            <input type='text' name='form_title' id='form_title' value='<?php echo attr($irow['title'] ?? '') ?>' />
                        </td>
                    </tr>
                    <tr id="row_diagnosis">
                        <td class="right font-weight-bold text-nowrap" style="vertical-align:middle;"><strong><?php echo xlt('Code'); ?>:</strong></td>
                        <td colspan="3">
                            <input type='text' name='form_diagnosis' id='form_diagnosis' value='<?php echo attr($irow['diagnosis'] ?? '') ?>' onclick='top.restoreSession();sel_diagnosis();' title='<?php echo xla('Click to select or change diagnoses'); ?>' />
                        </td>
                    </tr>
                    <tr id='row_begindate'>
                        <td class="right text-nowrap"><strong id="onset"><?php echo xlt('Onset'); ?>:</strong></td>
                        <td>
                            <input type='text' class='datepicker' name='form_begin' id='form_begin' style="max-width: 100px;" value='<?php echo attr(oeFormatShortDate($irow['begdate'] ?? '')); ?>' ¸  title='<?php echo xla('Date of onset, surgery or start of medication'); ?>' />

                        </td>
                        <td class="text-nowrap" id='row_enddate'>
                            <input type='checkbox' name='form_active' id='form_active' value='1' <?php echo attr($irow['enddate'] ?? '') ? " checked" : ""; ?> onclick='top.restoreSession();resolvedClicked(this);' title='<?php echo xla('Indicates if this issue is currently active'); ?>' />
                            <strong id="resolved"><?php echo xlt('Resolved'); ?>:</strong>&nbsp;<input type='text' class='datepicker' name='form_end' id='form_end' style="max-width: 100px;" value='<?php echo attr(oeFormatShortDate($irow['enddate'] ?? '')); ?>' title='<?php echo xla('Date of recovery or end of medication'); ?>' />
                        </td>
                    </tr>

                    <tr id='row_occurrence'>
                        <td class="right text-nowrap align-top"><strong><?php echo xlt('Course'); ?>:</strong></td>
                        <td colspan="2">
                            <?php
// Modified 6/2009 by BM to incorporate the occurrence items into the list_options listings
                            generate_form_field(array(
                            'data_type' => 1,
                            'field_id' => 'occur',
                            'list_id' => 'occurrence',
                            'empty_title' => 'SKIP'
                            ), $irow['occurrence'] ?? '');
                            ?>
                        </td>
                        <td class="indent20">
                            <a class="text-body" href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=occurrence" target="RTop" title="<?php echo xla('Click here to Edit the Course/Occurrence List'); ?>"><i class="fa fa-pencil-alt fa-fw"></i></a>
                        </td>
                    </tr>

                    <tr id='row_classification'>
                        <td class="right text-nowrap align-top"><strong><?php echo xlt('Classification'); ?>:</strong></td>
                        <td colspan="3">
                            <select name='form_classification' id='form_classification'>
                                <?php
                                foreach ($ISSUE_CLASSIFICATIONS as $key => $value) {
                                    echo "<option value='" . attr($key) . "'";
                                    if ($key == ($irow['classification'] ?? '')) {
                                        echo " selected";
                                    }

                                    echo ">" . text($value) . "\n";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr id='row_reaction'>
                        <td class="right text-nowrap align-top"><strong><?php echo xlt('Reaction'); ?>:</strong></td>
                        <td colspan="3">
                            <input type='text'  name='form_reaction' id='form_reaction' value='<?php echo attr($irow['reaction'] ?? '') ?>' title='<?php echo xla('Allergy Reaction'); ?>' />
                        </td>
                    </tr>
                    <tr id='row_referredby'>
                        <td class="right text-nowrap"><strong id="by_whom"><?php echo xlt('Referred by'); ?>:</strong></td>
                        <td colspan="3">
                            <input type='text' name='form_referredby' id='form_referredby' value='<?php echo attr($irow['referredby'] ?? '') ?>' title='<?php echo xla('Referring physician and practice'); ?>' />
                        </td>
                    </tr>
                    <tr id='row_eye_med'>
                        <td class="right text-nowrap"><strong id="by_whom"><?php echo xlt('Eye Med'); ?>:</strong></td>
                        <td colspan="3"><?php echo $irow['subtype'] ?? ''; ?>
                            <input type='checkbox' name='form_eye_subtype' id='form_eye_subtype' value='1' <?php
                            if ($irow['subtype'] ?? '' == 'eye') {
                                echo " checked";
                            }
                            ?> style="margin:3px 3px 3px 5px;" title='<?php echo xla('Indicates if this issue is an ophthalmic-specific medication'); ?>' />
                        </td>
                    </tr>

                    <tr id='row_comments'>
                        <td class="right align-self-center text-nowrap align-top"><strong><?php echo xlt('Comments'); ?>:</strong></td>
                        <td colspan="3">
                            <textarea name='form_comments' id='form_comments' cols='40' wrap='virtual'><?php echo text($irow['comments'] ?? '') ?></textarea>
                        </td>
                    </tr>
                    <tr id="row_outcome">
                        <td class="right align-self-center text-nowrap"><strong><?php echo xlt('Outcome'); ?>:</strong></td>
                        <td>
                            <?php
                            echo generate_select_list('form_outcome', 'outcome', ($irow['outcome'] ?? ''), '', '', '', 'outcomeClicked(this);');
                            ?>
                        </td>
                    </tr>
                    <tr id="row_destination">
                        <td class="right text-nowrap align-top"><strong><?php echo xlt('Destination'); ?>:</strong></td>
                        <td colspan="3">
                            <?php if (true) { ?>
                            <input type='text' name='form_destination' value='<?php echo attr($irow['destination'] ?? '') ?>' title='GP, Secondary care specialist, etc.' />
                                <?php
                            } else { // leave this here for now, please -- Rod
                                ?>
                                <?php echo rbinput('form_destination', '1', 'GP', 'destination') ?>&nbsp;
                                <?php echo rbinput('form_destination', '2', 'Secondary care spec', 'destination') ?>&nbsp;
                                <?php echo rbinput('form_destination', '3', 'GP via physio', 'destination') ?>&nbsp;
                                <?php echo rbinput('form_destination', '4', 'GP via podiatry', 'destination') ?>
                                <?php
                            } ?>
                        </td>
                    </tr>
                </table>
                <table id="row_social" class="w-100 small">
                    <?php
                    $given = "*";
                    $dateStart = $_POST['dateState'] ?? '';
                    $dateEnd = $_POST['dateEnd'] ?? '';
                    if ($dateStart && $dateEnd) {
                        $result1 = sqlQuery("select $given from history_data where pid = ? and date >= ? and date <= ? order by date DESC limit 0,1", array(
                            $pid,
                            $dateStart,
                            $dateEnd
                        ));
                    } elseif ($dateStart && !$dateEnd) {
                        $result1 = sqlQuery("select $given from history_data where pid = ? and date >= ? order by date DESC limit 0,1", array(
                            $pid,
                            $dateStart
                        ));
                    } elseif (!$dateStart && $dateEnd) {
                        $result1 = sqlQuery("select $given from history_data where pid = ? and date <= ? order by date DESC limit 0,1", array(
                            $pid,
                            $dateEnd
                        ));
                    } else {
                        $result1 = sqlQuery("select $given from history_data where pid=? order by date DESC limit 0,1", array(
                            $pid
                        ));
                    }

                    $group_fields_query = sqlStatement("SELECT * FROM layout_options " . "WHERE form_id = 'HIS' AND group_id = '4' AND uor > 0 " . "ORDER BY seq");
                    while ($group_fields = sqlFetchArray($group_fields_query)) {
                        $titlecols = $group_fields['titlecols'];
                        $datacols = $group_fields['datacols'];
                        $data_type = $group_fields['data_type'];
                        $field_id = $group_fields['field_id'];
                        $list_id = $group_fields['list_id'];
                        $currvalue = '';
                        if (isset($result1[$field_id])) {
                            $currvalue = $result1[$field_id];
                        }

                        if ($data_type == 28 || $data_type == 32) {
                            $tmp = explode('|', $currvalue);
                            switch (count($tmp)) {
                                case "4":
                                    $result2[$field_id]['resnote'] = $tmp[0];
                                    $result2[$field_id]['restype'] = $tmp[1];
                                    $result2[$field_id]['resdate'] = $tmp[2];
                                    $result2[$field_id]['reslist'] = $tmp[3];
                                    break;
                                case "3":
                                    $result2[$field_id]['resnote'] = $tmp[0];
                                    $result2[$field_id]['restype'] = $tmp[1];
                                    $result2[$field_id]['resdate'] = $tmp[2];
                                    break;
                                case "2":
                                    $result2[$field_id]['resnote'] = $tmp[0];
                                    $result2[$field_id]['restype'] = $tmp[1];
                                    $result2[$field_id]['resdate'] = "";
                                    break;
                                case "1":
                                    $result2[$field_id]['resnote'] = $tmp[0];
                                    $result2[$field_id]['resdate'] = $result2[$field_id]['restype'] = "";
                                    break;
                                default:
                                    $result2[$field_id]['restype'] = $result2[$field_id]['resdate'] = $result2[$field_id]['resnote'] = "";
                                    break;
                            }

                            $fldlength = empty($frow['fld_length']) ? 20 : $frow['fld_length'];
                            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
                            $result2[$field_id]['resnote'] = htmlspecialchars($result2[$field_id]['resnote'], ENT_QUOTES);
                            $result2[$field_id]['resdate'] = htmlspecialchars($result2[$field_id]['resdate'], ENT_QUOTES);
                        } elseif ($data_type == 2) {
                            $result2[$field_id]['resnote'] = nl2br(htmlspecialchars($currvalue, ENT_NOQUOTES));
                        }
                    }
                    ?>
                    <style>
                        .data td {
                            font-size: 10px;
                            min-width: 40px;
                            padding: 0px 3px;
                        }

                        .data input[type="text"] {
                            width: 90px;
                        }

                        #form_box {
                            width: 90px;
                        }

                        #theform table table {
                            margin: 0px;
                            border: none;
                        }

                    </style>

                    <tbody>
                        <tr>
                            <td class="right text-nowrap"><?php echo xlt('Marital'); ?>:</td>
                            <td colspan="3"><input type="text" style="width:75px;" name="marital_status" id="marital_status" value="<?php echo attr($patient['status']); ?>" />
                                &nbsp;<?php echo xlt('Occupation'); ?>:&nbsp;<input type="text" style="width:175px;" name="occupation" id="occupation" value="<?php echo attr($patient['occupation']); ?>" /></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <select name="form_tobacco" id="form_tobacco" onchange="radioChange(this.options[this.selectedIndex].value)" title="<?php xla('Tobacco use'); ?>">
                                    <option value="" <?php if (($result2['tobacco']['reslist'] ?? '') == '') {
                                        echo "selected";
                                                     } ?>><?php echo xlt('Unassigned'); ?></option>
                                    <option value="1" <?php if (($result2['tobacco']['reslist'] ?? '') == '1') {
                                        echo "selected";
                                                      } ?>><?php echo xlt('Current every day smoker'); ?></option>
                                    <option value="2" <?php if (($result2['tobacco']['reslist'] ?? '') == '2') {
                                        echo "selected";
                                                      } ?>><?php echo xlt('Current some day smoker'); ?></option>
                                    <option value="3" <?php if (($result2['tobacco']['reslist'] ?? '') == '3') {
                                        echo "selected";
                                                      } ?>><?php echo xlt('Former smoker'); ?></option>
                                    <option value="4" <?php if (($result2['tobacco']['reslist'] ?? '') == '4') {
                                        echo "selected";
                                                      } ?>><?php echo xlt('Never smoker'); ?></option>
                                    <option value="5" <?php if (($result2['tobacco']['reslist'] ?? '') == '5') {
                                        echo "selected";
                                                      } ?>><?php echo xlt('Smoker, current status unknown'); ?></option>
                                    <option value="9" <?php if (($result2['tobacco']['reslist'] ?? '') == '9') {
                                        echo "selected";
                                                      } ?>><?php echo xlt('Unknown if ever smoked'); ?></option>
                                    <option value="15" <?php if (($result2['tobacco']['reslist'] ?? '') == '15') {
                                        echo "selected";
                                                       } ?>><?php echo xlt('Heavy tobacco smoker'); ?></option>
                                    <option value="16" <?php if (($result2['tobacco']['reslist'] ?? '') == '16') {
                                        echo "selected";
                                                       } ?>><?php echo xlt('Light tobacco smoker'); ?></option>
                                </select>
                            </td>
                            <td class="text-nowrap"><span style="text-decoration:underline;">Never</span></td>
                            <td>
                                <span style="text-decoration:underline;"><?php echo xlt('N/A{{not applicable}}'); ?></span></td>
                        </tr>

                        <tr>
                            <td class="right text-nowrap"><?php echo xlt('Tobacco'); ?>:</td>
                            <td class="text data" colspan="4">
                                <table>
                                    <tr>
                                        <td><input type="text" name="form_text_tobacco" id="form_box" size="20" value="<?php echo attr($PMSFH[0]['SOCH']['tobacco']['resnote'] ?? ''); ?>" />&nbsp;</td>

                                        <td class="text">
                                            <input type="radio" name="radio_tobacco" id="radio_tobacco[current]" value="currenttobacco" onclick="smoking_statusClicked(this) " <?php if ($result2['tobacco']['restype'] == 'currenttobacco') {
                                                echo " checked";
                                                                                                                                                                               } ?> /><?php echo xlt('Current'); ?>&nbsp;</td>
                                        <td class="text"><input type="radio" name="radio_tobacco" id="radio_tobacco[quit]" value="quittobacco" onclick="smoking_statusClicked(this) " <?php if ($result2['tobacco']['restype'] == 'quittobacco') {
                                            echo " checked";
                                                                                                                                                                                      } ?> /><?php echo xlt('Quit'); ?>&nbsp;</td>
                                        <td class="text" onclick='top.restoreSession();resolvedClicked(this);'>
                                            <input class="datepicker" size="6" name="date_tobacco" id="date_tobacco" value="<?php echo attr(oeFormatShortDate($result2['tobacco']['resdate'])); ?>" title="<?php echo xla('Tobacco use'); ?>" type="text" />&nbsp;</td>
                                        <td class="text-center">
                                            <input type="radio" name="radio_tobacco" id="radio_tobacco[never]" value="nevertobacco" onclick="smoking_statusClicked(this) " <?php if ($result2['tobacco']['restype'] == 'nevertobacco') {
                                                echo " checked";
                                                                                                                                                                           } ?> />
                                        </td>
                                        <td class="text-center"><input name="radio_tobacco" type="radio" id="radio_tobacco[not_applicable]" <?php if (($PMSFH[0]['SOCH']['tobacco']['restype'] ?? '') == 'not_applicable') {
                                            echo " checked";
                                                                                                                                            } ?> value="not_applicabletobacco" />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="right text-nowrap"><?php echo xlt('Coffee'); ?>:</td>
                            <td class="text data" colspan="4">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="form_coffee" id="form_box" size="20" value="<?php echo attr($result2['coffee']['resnote']); ?>" />&nbsp;</td>

                                            <td class="text"><input type="radio" name="radio_coffee" id="radio_coffee[current]" value="currentcoffee" <?php if (($PMSFH[0]['SOCH']['coffee']['restype'] ?? '') == 'currentcoffee') {
                                                echo " checked";
                                                                                                                                                      } ?> /><?php echo xlt('Current'); ?>&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_coffee" id="radio_coffee[quit]" value="quitcoffee" <?php if (($PMSFH[0]['SOCH']['coffee']['restype'] ?? '') == 'quitcoffee') {
                                                echo " checked";
                                                                                                                                                } ?> /><?php echo xlt('Quit'); ?>&nbsp;</td>
                                            <td class="text"><input type="text" class="datepicker" size="6" name="date_coffee" id="date_coffee" value="" title="<?php echo xla('Caffeine consumption'); ?>" />&nbsp;</td>
                                            <td class="text-center"><input type="radio" name="radio_coffee" id="radio_coffee[never]" value="nevercoffee" <?php if (($PMSFH[0]['SOCH']['coffee']['restype'] ?? '') == 'nevercoffee') {
                                                echo " checked";
                                                                                                                                                         } ?> /></td>
                                            <td class="text-center"><input name="radio_coffee" type="radio" id="radio_coffee[not_applicable]" <?php if (($PMSFH[0]['SOCH']['coffee']['restype'] ?? '') == 'not_applicable') {
                                                echo " checked";
                                                                                                                                              } ?> value="not_applicablecoffee" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="right text-nowrap"><?php echo xlt('Alcohol'); ?>:</td>
                            <td class="text data" colspan="4">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="form_alcohol" id="form_box" size="20" value="<?php echo attr($result2['alcohol']['resnote']); ?>" />&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_alcohol" id="radio_alcohol[current]" value="currentalcohol" <?php if (($PMSFH[0]['SOCH']['alcohol']['restype'] ?? '') == 'currentalcohol') {
                                                echo " checked";
                                                                                                                                                         } ?> /><?php echo xlt('Current'); ?>&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_alcohol" id="radio_alcohol[quit]" value="quitalcohol" <?php if (($PMSFH[0]['SOCH']['alcohol']['restype'] ?? '') == 'quitalcohol') {
                                                echo " checked";
                                                                                                                                                   } ?> /><?php echo xlt('Quit'); ?>&nbsp;</td>
                                            <td class="text"><input type="text" size="6" class="datepicker" name="date_alcohol" id="date_alcohol" value="" title="<?php echo xla('Alcohol consumption'); ?>" />&nbsp;</td>
                                            <td class="text-center"><input type="radio" name="radio_alcohol" id="radio_alcohol[never]" value="neveralcohol" <?php if (($PMSFH[0]['SOCH']['alcohol']['restype'] ?? '') == 'neveralcohol') {
                                                echo " checked";
                                                                                                                                                            } ?> /></td>
                                            <td class="text-center"><input name="radio_alcohol" type="radio" id="radio_alcohol[not_applicable]" value="not_applicablealcohol" <?php if (($PMSFH[0]['SOCH']['alcohol']['restype'] ?? '') == 'not_applicable') {
                                                echo " checked";
                                                                                                                                                                              } ?> />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="right text-nowrap"><?php echo xlt('Drugs'); ?>:</td>
                            <td class="text data" colspan="4">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="form_recreational_drugs" id="form_box" size="20" value="<?php echo attr($result2['recreational_drugs']['resnote']); ?>" />&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_recreational_drugs" id="radio_recreational_drugs[current]" value="currentrecreational_drugs" <?php if (($PMSFH[0]['SOCH']['recreational_drugs']['restype'] ?? '') == 'currentrecreational_drugs') {
                                                echo " checked";
                                                                                                                                                                                          } ?> /><?php echo xlt('Current'); ?>&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_recreational_drugs" id="radio_recreational_drugs[quit]" value="quitrecreational_drugs" <?php if (($PMSFH[0]['SOCH']['recreational_drugs']['restype'] ?? '') == 'quitrecreational_drugs') {
                                                echo " checked";
                                                                                                                                                                                    } ?> /><?php echo xlt('Quit'); ?>&nbsp;</td>
                                            <td class="text"><input type="text" size="6" class="datepicker" name="date_recreational_drugs" id="date_recreational_drugs" value="" title="<?php echo xla('Recreational drug use'); ?>" />&nbsp;</td>
                                            <td class="text-center"><input type="radio" name="radio_recreational_drugs" id="radio_recreational_drugs[never]" value="neverrecreational_drugs" <?php if (($PMSFH[0]['SOCH']['recreational_drugs']['restype'] ?? '') == 'neverrecreational_drugs') {
                                                echo " checked";
                                                                                                                                                                                             } ?>></td>
                                            <td class="text-center"><input name="radio_recreational_drugs" type="radio" id="radio_recreational_drugs[not_applicable]" <?php if (($PMSFH[0]['SOCH']['recreational_drugs']['restype'] ?? '') == 'not_applicable') {
                                                echo " checked";
                                                                                                                                                                      } ?> value="not_applicablerecreational_drugs" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="right text-nowrap"><?php echo xlt('Counseling'); ?>:</td>
                            <td class="text data" colspan="4">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="form_counseling" id="form_box" size="20" value="<?php echo attr($result2['counseling']['resnote']); ?>" />&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_counseling" id="radio_counseling[current]" value="currentcounseling" <?php if (($PMSFH[0]['SOCH']['counseling']['restype'] ?? '') == 'currentcounseling') {
                                                echo " checked";
                                                                                                                                                                  } ?> /><?php echo xlt('Current'); ?>&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_counseling" id="radio_counseling[quit]" value="quitcounseling" <?php if (($PMSFH[0]['SOCH']['counseling']['restype'] ?? '') == 'quitcounseling') {
                                                echo " checked";
                                                                                                                                                            } ?> /><?php echo xlt('Quit'); ?>&nbsp;</td>
                                            <td class="text"><input type="text" size="6" class="datepicker" name="date_counseling" id="date_counseling" value="" title="<?php echo xla('Counseling activities') ?>" />&nbsp;</td>
                                            <td class="text-center"><input type="radio" name="radio_counseling" id="radio_counseling[never]" value="nevercounseling" <?php if (($PMSFH[0]['SOCH']['counseling']['restype'] ?? '') == 'nevercounseling') {
                                                echo " checked";
                                                                                                                                                                     } ?> /></td>
                                            <td class="text-center"><input name="radio_counseling" type="radio" id="radio_counseling[not_applicable]" value="not_applicablecounseling" <?php if (($PMSFH[0]['SOCH']['counseling']['restype'] ?? '') == 'not_applicable') {
                                                echo " checked";
                                                                                                                                                                                       } ?> />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="right text-nowrap">
                                <?php echo xlt('Exercise'); ?>:</td>
                            <td class="text data" colspan="4">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="form_exercise_patterns" id="form_box" size="20" value="<?php echo attr($result2['exercise_patterns']['resnote']); ?>" />&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_exercise_patterns" id="radio_exercise_patterns[current]" value="currentexercise_patterns" <?php if (($PMSFH[0]['SOCH']['exercise_patterns']['restype'] ?? '') == 'currentexercise_patterns') {
                                                echo " checked";
                                                                                                                                                                                       } ?> /><?php echo xlt('Current'); ?>&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_exercise_patterns" id="radio_exercise_patterns[quit]" value="quitexercise_patterns" <?php if (($PMSFH[0]['SOCH']['exercise_patterns']['restype'] ?? '') == 'quitexercise_patterns') {
                                                echo " checked";
                                                                                                                                                                                 } ?> /><?php echo xlt('Quit') ?>&nbsp;</td>
                                            <td class="text"><input type="text" size="6" class="datepicker" name="date_exercise_patterns" id="date_exercise_patterns" value="" title="<?php echo xla('Exercise patterns') ?>" />&nbsp;</td>
                                            <td class="text-center"><input type="radio" name="radio_exercise_patterns" id="radio_exercise_patterns[never]" value="neverexercise_patterns" <?php if (($PMSFH[0]['SOCH']['exercise_patterns']['restype'] ?? '') == 'neverexercise_patterns') {
                                                echo " checked";
                                                                                                                                                                                          } ?> /></td>
                                            <td class="text-center"><input name="radio_exercise_patterns" type="radio" id="radio_exercise_patterns[not_applicable]" <?php if (($PMSFH[0]['SOCH']['exercise_patterns']['restype'] ?? '') == 'not_applicable') {
                                                echo " checked";
                                                                                                                                                                    } ?> value="not_applicableexercise_patterns" /></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="right text-nowrap"><?php echo xlt('Risky Beh.'); ?>:</td>
                            <td class="text data" colspan="4">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="form_hazardous_activities" id="form_box" size="20" value="<?php echo attr($result2['hazardous_activities']['resnote']); ?>" />&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_hazardous_activities" id="radio_hazardous_activities[current]" value="currenthazardous_activities" <?php if (($PMSFH[0]['SOCH']['hazardous_activities']['restype'] ?? '') == 'currenthazardous_activities') {
                                                echo " checked";
                                                                                                                                                                                                } ?> /><?php echo xlt('Current'); ?>&nbsp;</td>
                                            <td class="text"><input type="radio" name="radio_hazardous_activities" id="radio_hazardous_activities[quit]" value="quithazardous_activities" <?php if (($PMSFH[0]['SOCH']['hazardous_activities']['restype'] ?? '') == 'quithazardous_activities') {
                                                echo " checked";
                                                                                                                                                                                          } ?> /><?php echo xlt('Quit') ?>&nbsp;</td>
                                            <td class="text"><input type="text" size="6" class="datepicker" name="date_hazardous_activities" id="date_hazardous_activities" value="" title="<?php echo xla('Hazardous activities') ?>" />&nbsp;</td>
                                            <td class="text-center"><input type="radio" name="radio_hazardous_activities" id="radio_hazardous_activities[never]" value="neverhazardous_activities" <?php if (($PMSFH[0]['SOCH']['hazardous_activities']['restype'] ?? '') == 'neverhazardous_activities') {
                                                echo " checked"; } ?> /></td>
                                            <td class="text-center"><input name="radio_hazardous_activities" type="radio" id="radio_hazardous_activities[not_applicable]" <?php if (($PMSFH[0]['SOCH']['hazardous_activities']['restype'] ?? '') == 'not_applicable') {
                                                echo " checked"; } ?> value="not_applicablehazardous_activities" onclick="hazardous_activities_statusClicked(this)" /></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="right text-nowrap"><?php echo xlt('Sleep'); ?>:</td>
                            <td class="text data" colspan="4">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="form_sleep_patterns" id="form_box" size="20" title="<?php echo xla('Sleep patterns'); ?>" value="<?php echo attr($result2['sleep_patterns']['resnote']); ?>" /></td>
                                            <td></td>
                            <td class="left text-nowrap"><?php echo xlt('Seatbelt'); ?>:
                            </td>
                            <td><input type="text" name="form_seatbelt_use" id="form_box" size="20" title="<?php echo xla('Seatbelt use'); ?>" value="<?php echo attr($result2['seatbelt_use']['resnote']); ?>" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                </td>
                </tr>
                </tbody>
                </table>
                <table id="row_FH" class="small" name="row_FH" width="90%">
                    <tr>
                        <td class="right text-nowrap"><?php echo xlt('Glaucoma'); ?>:</td>
                        <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext11" name="radio_usertext11" <?php if (!$result1['usertext11']) {
                            echo " checked='checked'";
                                                                                                                                              } ?> />
                            <input type="text" name="usertext11" id="usertext11" onclick='clear_option(this)' value="<?php echo attr($result1['usertext11']); ?>" /></td>
                        <td class="right text-nowrap"><?php echo xlt('Cataract'); ?>:</td>
                        <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext12" name="radio_usertext12" <?php if (!$result1['usertext12']) {
                            echo " checked='checked'";
                                                                                                                                              } ?> />
                            <input type="text" name="usertext12" id="usertext12" onclick='clear_option(this)' value="<?php echo attr($result1['usertext12']); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><?php echo xlt('AMD{{age related macular degeneration}}'); ?>:</td>
                        <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext13" name="radio_usertext13" <?php if (!$result1['usertext13']) {
                            echo " checked='checked'";
                                                                                                                                              } ?> />
                            <input type="text" name="usertext13" id="usertext13" onclick='clear_option(this)' value="<?php echo attr($result1['usertext13']); ?>" /></td>
                        <td class="right text-nowrap"><?php echo xlt('RD{{retinal detachment}}'); ?>:</td>
                        <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext14" name="radio_usertext14" <?php if (!$result1['usertext14']) {
                            echo " checked='checked'";
                                                                                                                                              } ?> />
                            <input type="text" name="usertext14" id="usertext14" onclick='clear_option(this)' value="<?php echo attr($result1['usertext14']); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><?php echo xlt('Blindness'); ?>:</td>
                        <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext15" name="radio_usertext15" <?php if (!$result1['usertext15']) {
                            echo " checked='checked'";
                                                                                                                                              } ?> />
                            <input type="text" name="usertext15" id="usertext15" onclick='clear_option(this)' value="<?php echo attr($result1['usertext15']); ?>" /></td>
                        <td class="right text-nowrap"><?php echo xlt('Amblyopia'); ?>:</td>
                        <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext16" name="radio_usertext16" <?php if (!$result1['usertext16']) {
                            echo " checked='checked'";
                                                                                                                                              } ?> />
                            <input type="text" name="usertext16" id="usertext16" onclick='clear_option(this)' value="<?php echo attr($result1['usertext16']); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><?php echo xlt('Strabismus'); ?>:</td>
                        <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext17" name="radio_usertext17" <?php if (!$result1['usertext17']) {
                            echo " checked='checked'";
                                                                                                                                              } ?> />
                            <input type="text" name="usertext17" id="usertext17" onclick='clear_option(this)' value="<?php echo attr($result1['usertext17']); ?>" /></td>
                        <td class="right text-nowrap"><?php echo xlt('Epilepsy'); ?>:</td>
                        <td class="text data">
                            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_epilepsy" name="radio_relatives_epilepsy" <?php if (!$result1['relatives_epilepsy']) {
                                echo " checked='checked'";
                                                                                                                                            } ?> />
                            <input type="text" name="relatives_epilepsy" id="relatives_epilepsy" onclick='clear_option(this)' value="<?php echo attr($result1['relatives_epilepsy']); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><?php echo xlt('Cancer'); ?>:</td>
                        <td class="text data">
                            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_cancer" name="radio_relatives_cancer" <?php if (!$result1['relatives_cancer']) {
                                echo " checked='checked'";
                                                                                                                                        } ?> />
                            <input type="text" name="relatives_cancer" id="relatives_cancer" onclick='clear_option(this)' value="<?php echo attr($result1['relatives_cancer']); ?>" /></td>
                        <td class="right text-nowrap"><?php echo xlt('Diabetes'); ?>:</td>
                        <td class="text data">
                            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_diabetes" name="radio_relatives_diabetes" <?php if (!$result1['relatives_diabetes']) {
                                echo " checked='checked'";
                                                                                                                                            } ?> />
                            <input type="text" name="relatives_diabetes" id="relatives_diabetes" onclick='clear_option(this)' value="<?php echo attr($result1['relatives_diabetes']); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><?php echo xlt('HTN{{hypertension}}'); ?>:</td>
                        <td class="text data">
                            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_high_blood_pressure" name="radio_relatives_high_blood_pressure" <?php if (!$result1['relatives_high_blood_pressure']) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="relatives_high_blood_pressure" id="relatives_high_blood_pressure" onclick='clear_option(this)' value="<?php echo attr($result1['relatives_high_blood_pressure']); ?>" /></td>
                        <td class="right text-nowrap"><?php echo xlt('Cardiac'); ?>:</td>
                        <td class="text data">
                            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_heart_problems" name="radio_relatives_heart_problems" <?php if (!$result1['relatives_heart_problems']) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="relatives_heart_problems" id="relatives_heart_problems" onclick='clear_option(this)' value="<?php echo attr($result1['relatives_heart_problems']); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><?php echo xlt('Stroke'); ?>:</td>
                        <td class="text data">
                            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_stroke" name="radio_relatives_stroke" <?php if (!$result1['relatives_stroke']) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="relatives_stroke" id="relatives_stroke" onclick='clear_option(this)' value="<?php echo attr($result1['relatives_stroke']); ?>" /></td>
                        <td class="right text-nowrap"><?php echo xlt('Other'); ?>:</td>
                        <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext18" name="radio_usertext18" <?php if (!$result1['usertext18']) {
                            echo " checked='checked'"; } ?> />
                            <input type="text" name="usertext18" id="usertext18" onclick='clear_option(this)' value="<?php echo attr($result1['usertext18']); ?>" /></td>
                    </tr>
                </table>
                <table id="row_ROS" name="row_ROS" class="ROS_class">
                    <tr>
                        <td></td>
                        <td>
                            <span class="underline"><?php echo xlt('Neg{{negative}}'); ?></span><span class="underline" style="margin:30px;"><?php echo xlt('Positive'); ?></span>
                        </td>
                        <td></td>
                        <td><span class="underline"><?php echo xlt('Neg{{negative}}'); ?></span><span class="underline" style="margin:30px;"><?php echo xlt('Positive'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><label for="ROSGENERAL" class="input-helper input-helper--checkbox"><?php echo xlt('General'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSGENERAL" name="radio_ROSGENERAL" <?php if (!$ROSGENERAL) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSGENERAL" id="ROSGENERAL" onclick='clear_option(this)' value="<?php echo attr($ROSGENERAL); ?>" /></td>
                        <td class="right text-nowrap"><label for="ROSHEENT" class="input-helper input-helper--checkbox"><?php echo xlt('HEENT'); ?>:</td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSHEENT" name="radio_ROSHEENT" <?php if (!$ROSHEENT) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSHEENT" id="ROSHEENT" onclick='clear_option(this)' value="<?php echo attr($ROSHEENT); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><label for="ROSCV" class="input-helper input-helper--checkbox"><?php echo xlt('CV{{Cardiovascular}}'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSCV" name="radio_ROSCV" <?php if (!$ROSCV) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSCV" id="ROSCV" onclick='clear_option(this)' value="<?php echo attr($ROSCV); ?>" /></td>
                        <td class="right text-nowrap"><label for="ROSPULM" class="input-helper input-helper--checkbox"><?php echo xlt('Pulmonary'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSPULM" name="radio_ROSPULM" <?php if (!$ROSPULM) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSPULM" id="ROSPULM" onclick='clear_option(this)' value="<?php echo attr($ROSPULM); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><label for="ROSGI" class="input-helper input-helper--checkbox"><?php echo xlt('GI{{Gastrointestinal}}'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSGI" name="radio_ROSGI" <?php if (!$ROSGI) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSGI" id="ROSGI" onclick='clear_option(this)' value="<?php echo attr($ROSGI); ?>" /></td>
                        <td class="right text-nowrap"><label for="ROSGU" class="input-helper input-helper--checkbox"><?php echo xlt('GU{{Genitourinary}}'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSGU" name="radio_ROSGU" <?php if (!$ROSGU) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSGU" id="ROSGU" onclick='clear_option(this)' value="<?php echo attr($ROSGU); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><label for="ROSDERM" class="input-helper input-helper--checkbox"><?php echo xlt('Derm{{dermatologic}}'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSDERM" name="radio_ROSDERM" <?php if (!$ROSDERM) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSDERM" id="ROSDERM" onclick='clear_option(this)' value="<?php echo attr($ROSDERM); ?>" /></td>
                        <td class="right text-nowrap"><label for="ROSNEURO" class="input-helper input-helper--checkbox"><?php echo xlt('Neuro{{neurologic}}'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSNEURO" name="radio_ROSNEURO" <?php if (!$ROSNEURO) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSNEURO" id="ROSNEURO" onclick='clear_option(this)' value="<?php echo attr($ROSNEURO); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><label for="ROSPSYCH" class="input-helper input-helper--checkbox"><?php echo xlt('Psych{{psychiatric}}'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSPSYCH" name="radio_ROSPSYCH" <?php if (!$ROSPSYCH) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSPSYCH" id="ROSPSYCH" onclick='clear_option(this)' value="<?php echo attr($ROSPSYCH); ?>" /></td>
                        <td class="right text-nowrap"><label for="ROSMUSCULO" class="input-helper input-helper--checkbox"><?php echo xlt('Musculo{{musculoskeletal}}'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSMUSCULO" name="radio_ROSMUSCULO" <?php if (!$ROSMUSCULO) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSMUSCULO" id="ROSMUSCULO" onclick='clear_option(this)' value="<?php echo attr($ROSMUSCULO); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="right text-nowrap"><label for="ROSIMMUNO" class="input-helper input-helper--checkbox"><?php echo xlt('Immuno{{immunologic}}'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSIMMUNO" name="radio_ROSIMMUNO" <?php if (!$ROSIMMUNO) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSIMMUNO" id="ROSIMMUNO" onclick='clear_option(this)' value="<?php echo attr($ROSIMMUNO); ?>" /></td>
                        <td class="right text-nowrap"><label for="ROSENDOCRINE" class="input-helper input-helper--checkbox"><?php echo xlt('Endocrine'); ?>:</label></td>
                        <td>
                            <input type="radio" onclick='negate_radio(this);' id="radio_ROSENDOCRINE" name="radio_ROSENDOCRINE" <?php if (!$ROSENDOCRINE) {
                                echo " checked='checked'"; } ?> />
                            <input type="text" name="ROSENDOCRINE" id="ROSENDOCRINE" onclick='clear_option(this)' value="<?php echo attr($ROSENDOCRINE); ?>" /></td>
                    </tr>
                    <tr>
                        <td colspan="4"><label>Comments:</label><br />
                            <textarea name="ROSCOMMENTS" id="ROSCOMMENTS"><?php echo text($ROSCOMMENTS); ?></textarea>
                        </td>
                    </tr>
                </table>
                <table id="row_PLACEHOLDER" name="row_PLACEHOLDER" width="90%">
                    <tr>
                        <td>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="text-center w-100 mt-3">
                <input type="hidden" id="issue_js" name="issue_js" value="test" />
                <input type="hidden" id="pid" name="pid" value="<?php echo attr($pid); ?>" />
                <button type='button' id='form_save' name='form_save' class="btn btn-primary btn-save" onclick='top.restoreSession();submit_this_form();'><?php echo xla('Save'); ?></button>
                <?php $display_delete = "nodisplay"; ?>
                &nbsp;
                <button type='button' name='delete_button' id='delete_button' class="btn btn-secondary btn-delete <?php echo $display_delete; ?>" onclick='top.restoreSession();deleteme();'><?php echo xla('Delete'); ?></button>
                &nbsp;
                <button type='button' name='cancel_button' id='cancel_button' class="btn btn-secondary btn-cancel" onclick='clearme();'><?php echo xla('Cancel'); ?></button>
            </div>
        </form>
    </div>
<script>
    newtype('<?php if (!($form_index ?? '')) {
        echo "POH";
             } else {
                 echo $type_index;
             } ?>');
    newtype('Eye Meds');
    $(function () {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php $datetimepicker_minDate = false; ?>
            <?php $datetimepicker_maxDate = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
        $('.datepicker-past').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php $datetimepicker_minDate = false; ?>
            <?php $datetimepicker_maxDate = '+1970/01/01'; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
        $('.datepicker-future').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php $datetimepicker_minDate = '-1970/01/01'; ?>
            <?php $datetimepicker_maxDate = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });

    $('[title]').tooltip();

</script>
</body>
</html>
