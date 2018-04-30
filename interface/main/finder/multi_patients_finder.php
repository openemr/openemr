<?php

include_once('../../globals.php');
include_once("$srcdir/patient.inc");
use OpenEMR\Core\Header;

//echo "<pre>"; print_r($twentyFirstPid);die;

?>

<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['jquery', 'bootstrap', 'select2', 'opener']); ?>
    <title><?php echo htmlspecialchars(xl('Patient Finder'), ENT_NOQUOTES); ?></title>

    <style>
        #submitbtn{
            float: none !important;
        }
        #searchCriteria {
            text-align: center;
            width: 100%;
            /*font-size: 0.8em;*/
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
            display: inline-block;
            margin-left: 22px;
            vertical-align: middle;
        }
        .remove-patient{
            color: red;
            pointer-events: auto;
        }
        #searchResultsHeader {
            width: 100%;
            /*background-color: #fff;*/
            border-collapse: collapse;
        }
        #searchResultsHeader th {
            /*font-size: 0.7em;*/
        }
        #searchResults {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            overflow: auto;
        }

        #searchResults tr {
            cursor: hand;
            cursor: pointer;
        }
        #searchResults td {
            /*font-size: 0.7em;*/
            border-bottom: 1px solid #eee;
        }
        .oneResult { }
        .billing { color: red; font-weight: bold; }

        /* for search results or 'searching' notification */
        #searchstatus {
            font-size: 0.8em;
            font-weight: bold;
            padding: 1px 1px 10px 1px;
            font-style: italic;
            color: black;
            text-align: center;
        }
        .noResults { background-color: #ccc; }
        .tooManyResults { background-color: #fc0; }
        .howManyResults { background-color: #9f6; }
        #searchspinner {
            display: inline;
            visibility: hidden;
        }

        /* highlight for the mouse-over */
        .highlight {
            background-color: #336699;
            color: white;
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
                    <option value=""><?php echo xlt('Type name'); ?></option>
                </select>
                <label><?php echo xlt('Patient ID'); ?></label>
                <select id="by-id" class="input-sm">
                    <option value=""><?php echo xlt('Type ID'); ?></option>
                </select>
            </div>
            <div class="buttons-box">
                <button id="add-to-list"><?php echo xlt('Add to list'); ?></button>
                <button id=""><?php echo  xlt('OK'); ?></button>
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

        </tbody>
    </table>

</div>

<script>

var currentResult;
var patientsList = [];

//Initial select2 library for auto completing using ajax
$('#by-id, #by-name').select2({
    ajax: {
        url: 'multi_patients_finder_ajax.php',
        data:function (params) {
            var query = {
                search: params.term,
                type: $(this).attr('id')
            }
            return query;
        },
        dataType: 'json'
    }
});

//get all the data of selected patient
$('#by-id').on('change', function () {
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


$('#add-to-list').on('click', function (e) {
    e.preventDefault();

    if($('#by-name').val() == '')return;

    if(patientsList.length === 0){
        $('#results-table').show();
    }

    // return if patient already exist in the list
    if(patientsList.indexOf(currentResult) > -1)return;

    // add to array
    patientsList.push(currentResult);
    var lastIndex = patientsList.length-1;

    $('#searchResults').append('<tr id="row'+lastIndex+'">' +
        '<td>'+ currentResult.lname + ', ' + currentResult.fname + '</td>' +
        '<td>' + currentResult.phone_home + '</td>' +
        '<td>' + currentResult.ss + '</td>' +
        '<td>' + currentResult.DOB + '</td>' +
        '<td>' + currentResult.pubpid + '</td>' +
        '<td><i class="fa fa-remove remove-patient" onclick="removePatient('+lastIndex+')"></i></td>' +
    '<tr>');

});

// remove patient from list
function removePatient(index) {
    patientsList.splice(index,1);
    $('#row'+index).remove();
}


</script>

</body>
