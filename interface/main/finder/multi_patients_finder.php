<?php

/**
 * Multi select patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2017 Amiel Elboim <amielel@matrix.co.il
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once("$srcdir/patient.inc");
use OpenEMR\Core\Header;

// for editing selected patients
if (isset($_GET['patients'])) {
    $patients = rtrim($_GET['patients'], ";");
    $patients = explode(';', $patients);
    $results = array();
    foreach ($patients as $patient) {
        $result=getPatientData($patient, 'id, pid, lname, fname, mname, pubpid, ss, DOB, phone_home');
        $results[] = $result;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['select2', 'opener']); ?>
    <title><?php echo htmlspecialchars(xl('Patient Finder'), ENT_NOQUOTES); ?></title>

    <style>
        #searchCriteria {
            text-align: center;
            width: 100%;
            background-color: #ddddff;
            font-weight: bold;
            padding: 7px;
        }
        .select-box{
            display: inline-block;
        }
        #by-id{
            width: 90px !important;
        }
        #by-name{
            width: 120px !important;
        }
        .buttons-box{
            margin-left: 10px;
            margin-right: 10px;
            display: inline-block;
            vertical-align: middle;
        }
        .inline-box{
            display: inline-block;
            vertical-align: middle;
        }
        .remove-patient{
            color: red;
            pointer-events: auto;
        }
        #searchResultsHeader {
            width: 100%;
            border-collapse: collapse;
        }
        #searchResults {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            overflow: auto;
        }

        #searchResults .remove-patient {
            cursor: hand;
            cursor: pointer;
        }
        #searchResults td {
            /*font-size: 0.7em;*/
            border-bottom: 1px solid #eee;
        }
    </style>

</head>

<body class="body_top">
<div class="container-responsive">
    <div id="searchCriteria">
        <form class="form-inline">
            <div class="select-box">
                <label><?php echo xlt('Patient name') .':'; ?></label>
                <select id="by-name" class="input-sm">
                    <option value=""><?php echo xlt('Enter name'); ?></option>
                </select>
                <label><?php echo xlt('Patient ID'); ?></label>
                <select id="by-id" class="input-sm">
                    <option value=""><?php echo xlt('Enter ID'); ?></option>
                </select>
            </div>
            <div class="buttons-box">
                <div class="inline-box">
                    <button id="add-to-list"><?php echo xlt('Add to list'); ?></button>
                </div>
                <div class="inline-box">
                    <button id="send-patients" onclick="selPatients()"><?php echo  xlt('OK'); ?></button>
                </div>
            </div>
        </form>
    </div>

    <table id="results-table" class="table table-condensed">
        <thead id="searchResultsHeader" class="head">
        <tr>
            <th class="srName"><?php echo  xlt('Name'); ?></th>
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
                        '<td><i class="fa fa-remove remove-patient" onclick="removePatient('.attr($result['pid']).')"></i></td>' .
                    '<tr>';
            }
        } ?>
        </tbody>
    </table>

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
    ajax: {
        beforeSend: top.restoreSession,
        url: 'multi_patients_finder_ajax.php',
        data:function (params) {
            var query = {
                search: params.term,
                type: $(this).attr('id')
            }
            return query;
        },
        dataType: 'json',
    }
});

//get all the data of selected patient
$('#by-id').on('change', function () {
    top.restoreSession();
    $.ajax({
        url: 'multi_patients_finder_ajax.php',
        data:{
            type:'patient-by-id',
            search:$('#by-id').val()
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
            search:$('#by-name').val()
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
        '<td><i class="fa fa-remove remove-patient" onclick="removePatient('+currentResult.pid+')"></i></td>' +
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
