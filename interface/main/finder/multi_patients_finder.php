<?php

/**
 * Multi select patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2017 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// for editing selected patients
if (isset($_GET['patients'])) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $patients = rtrim($_GET['patients'], ";");
    $patients = explode(';', $patients);
    $results = array();
    foreach ($patients as $patient) {
        $result = getPatientData($patient, 'id, pid, lname, fname, mname, pubpid, ss, DOB, phone_home');
        $results[] = $result;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['select2', 'opener']); ?>
    <title><?php echo xlt('Patient Finder'); ?></title>

    <style>
        #searchCriteria {
            text-align: center;
            width: 100%;
            background-color: var(--gray300);
            font-weight: bold;
            padding: 7px;
        }

        .select-box {
            display: inline-block;
        }

        #by-id {
            width: 90px !important;
        }

        #by-name {
            width: 120px !important;
        }

        .inline-box {
            display: inline-block;
            vertical-align: middle;
        }
        .remove-patient {
            color: var(--danger);
            pointer-events: auto;
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

        #searchResults .remove-patient {
            cursor: hand;
            cursor: pointer;
        }

        #searchResults td {
            border-bottom: 1px solid var(--gray200);
        }
    </style>

</head>

<body>
<div class="container-fluid">
    <div id="searchCriteria">
        <form>
            <div class="row align-items-center">
                <div class="col-4">
                    <div class="select-box form-inline">
                        <label for="by-name"><?php echo xlt('Patient name') . ':'; ?></label>
                        <select id="by-name" name="by-name" class="input-sm">
                            <option value=""><?php echo xlt('Enter name'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="select-box form-inline">
                        <label for="by-id"><?php echo xlt('Patient ID'); ?>:</label>
                        <select id="by-id" name="by-id" class="input-sm">
                            <option value=""><?php echo xlt('Enter ID'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="btn-group" role="group" aria-label="Form Buttons">
                        <button id="add-to-list" type="button" class="btn btn-primary btn-add btn-sm"><?php echo xlt('Add to list'); ?></button>
                        <button id="send-patients" type="button" class="btn btn-primary btn-save btn-sm" onclick="selPatients()"><?php echo xlt('OK'); ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table id="results-table" class="table table-sm">
            <thead id="searchResultsHeader" class="head">
            <tr>
                <th class="srName"><?php echo xlt('Name'); ?></th>
                <th class="srPhone"><?php echo xlt('Phone'); ?></th>
                <th class="srSS"><?php echo xlt('SS'); ?></th>
                <th class="srDOB"><?php echo xlt('DOB'); ?></th>
                <th class="srID"><?php echo xlt('ID'); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody id="searchResults">
            <?php
            if (isset($_GET['patients'])) {
                foreach ($results as $index => $result) {
                    echo '<tr id="row' . attr($result['pid']) . '">' .
                            '<td>' . text($result['lname']) . ', ' . text($result['fname']) . '</td>' .
                            '<td>' . text($result['phone_home']) . '</td>' .
                            '<td>' . text($result['ss']) . '</td>' .
                            '<td>' . text(oeFormatShortDate($result['DOB'])) . '</td>' .
                            '<td>' . text($result['pubpid']) . '</td>' .
                            '<td><i class="fas fa-trash-alt remove-patient" onclick="removePatient(' . attr(addslashes($result['pid'])) . ')"></i></td>' .
                        '<tr>';
                }
            } ?>
            </tbody>
        </table>
    </div>
</div>

<script>

var currentResult;

<?php if (isset($_GET['patients'])) { ?>
var patientsList = <?php echo json_encode($results); ?>;
<?php } else { ?>
var patientsList = [];
$('#results-table').hide();
<?php } ?>

//Initial select2 library for auto completing using ajax
$('#by-id, #by-name').select2({
    theme: "bootstrap4",
    ajax: {
        beforeSend: top.restoreSession,
        url: 'multi_patients_finder_ajax.php',
        data:function (params) {
            var query = {
                search: params.term,
                type: $(this).attr('id'),
                csrf_token_form: "<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"
            }
            return query;
        },
        dataType: 'json',
    },
    <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
});

//get all the data of selected patient
$('#by-id').on('change', function () {
    top.restoreSession();
    $.ajax({
        url: 'multi_patients_finder_ajax.php',
        data:{
            type:'patient-by-id',
            search:$('#by-id').val(),
            csrf_token_form: "<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"
        },
        dataType: 'json'
    }).done(function(data){
        currentResult=data.results;
        //change patient name to selected patient
        $('#by-name').val(null);
        var newOption = "<option value='" +currentResult.pid+ "' selected>"+currentResult.lname + ', ' + currentResult.fname+"</option>";
        $('#by-name').append(newOption);
    })
});

//get all the data of selected patient
$('#by-name').on('change', function () {
    top.restoreSession();
    $.ajax({
        url: 'multi_patients_finder_ajax.php',
        data:{
            type:'patient-by-id',
            search:$('#by-name').val(),
            csrf_token_form: "<?php echo attr(CsrfUtils::collectCsrfToken()); ?>"
        },
        dataType: 'json'
    }).done(function(data){
        currentResult=data.results;
        //change patient pubpid to selected patient
        $('#by-id').val(null);
        var newOption = "<option value='" +currentResult.pid+ "' selected>"+ currentResult.pubpid +"</option>";
        $('#by-id').append(newOption);
    })
});

//add new patient to list
$('#add-to-list').on('click', function (e) {
    e.preventDefault();

    if($('#by-name').val() == '')return;

    if(patientsList.length === 0){
        $('#results-table').show();
    }

    // return if patient already exist in the list
    var exist
    $.each(patientsList, function (key, patient) {
        if (patient.pid == currentResult.pid) exist = true;
    })
    if(exist)return;


    // add to array
    patientsList.push(currentResult);

    $('#searchResults').append('<tr id="row'+currentResult.pid +'">' +
        '<td>'+ currentResult.lname + ', ' + currentResult.fname + '</td>' +
        '<td>' + currentResult.phone_home + '</td>' +
        '<td>' + currentResult.ss + '</td>' +
        '<td>' + currentResult.DOB + '</td>' +
        '<td>' + currentResult.pubpid + '</td>' +
        '<td><i class="fas fa-trash-alt remove-patient" onclick="removePatient('+currentResult.pid+')"></i></td>' +
    '<tr>');

});

// remove patient from list
function removePatient(pid) {

    $.each(patientsList, function (index, patient) {
        if (typeof patient !== 'undefined' && patient.pid == pid) {
            patientsList.splice(index,1);
        }
    });

    $('#row'+pid).remove();
}

//send array of patients to function 'setMultiPatients' of the opener
function selPatients() {
    if (opener.closed || ! opener.setMultiPatients)
        alert("<?php echo xls('The destination form was closed; I cannot act on your selection.'); ?>");
    else
        opener.setMultiPatients(patientsList);
    dlgclose();
    return false;
}


</script>

</body>
