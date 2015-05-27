<?php
// Copyright (C) 2015 Tony McCormick <tony@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("api/PreBillingIssuesAPI.php");
require_once(dirname(__FILE__) . "/../globals.php");
require_once("$srcdir/formatting.inc.php");

//////////////////////////////////////////////////////////////////////////////////////////////////////////
// main
//////////////////////////////////////////////////////////////////////////////////////////////////////////

$reportData = computeReport();

//////////////////////////////////////////////////////////////////////////////////////////////////////////
// private
//////////////////////////////////////////////////////////////////////////////////////////////////////////

function computeReport() {
    $preBillingIssuesAPI = new PreBillingIssuesAPI();
    $reportData = array();
    $reportData['encountersMissingProvider'] = $preBillingIssuesAPI->findEncountersMissingProvider();
    $reportData['patientInsuranceMissingSubscriberFields'] = $preBillingIssuesAPI->findPatientInsuranceMissingSubscriberFields();
    $reportData['patientInsuranceMissingSubscriberRelationship'] = $preBillingIssuesAPI->findPatientInsuranceMissingSubscriberRelationship();
    $reportData['patientInsuranceMissingInsuranceFields'] = $preBillingIssuesAPI->findPatientInsuranceMissingInsuranceFields();

    return $reportData;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////
// render page - main
//////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
<html>
    <head>
        <?php if (function_exists('html_header_show')) html_header_show(); ?>
        <link rel=stylesheet href="<?php echo $css_header; ?>" type="text/css">
        <title><?php xl('Pre-billing Issues Report', 'e') ?></title>
        <style type="text/css">
            .highlight {
                color: white;
                text-decoration: underline;
                background-color: black;
            }
        </style>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
        <script language="javascript">
            var concurrentLayout = <?php echo $GLOBALS['concurrent_layout'] ? true : false ?>;
            
            function toencounter(pid, pubpid, pname, enc, datestr, dobstr) {
                dobstr = dobstr ? dobstr : '';
                top.restoreSession();
                if ( concurrentLayout ) {
                    var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
                    parent.left_nav.setPatient(pname,pid,pubpid,'',dobstr);
                    parent.left_nav.setEncounter(datestr, enc, othername);
                    parent.left_nav.setRadio(othername, 'enc');
                    parent.frames[othername].location.href =
                    '../patient_file/encounter/encounter_top.php?set_encounter='
                        + enc + '&pid=' + pid;
                } else {
                     location.href = '../patient_file/encounter/patient_encounter.php?set_encounter='
                        + enc + '&pid=' + pid;
                }
            }
            
            function topatient(pid, pubpid, pname, enc, datestr, dobstr) {
                dobstr = dobstr ? dobstr : '';
                top.restoreSession();
                if ( concurrentLayout ) {
                    var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
                    parent.left_nav.setPatient(pname,pid,pubpid,'',dobstr);
                    parent.frames[othername].location.href =
                     '../patient_file/summary/demographics_full.php?pid=' + pid;
                } else {
                      location.href = '../patient_file/summary/demographics_full.php?pid=' + pid;
                }
            }
            
            $(document).ready(function () {
                $(".reportrow").mouseover(function () {
                    $(this).addClass("highlight");
                });
                $(".reportrow").mouseout(function () {
                    $(this).removeClass("highlight");
                });
                $(".encrow").click(function () {
                    toencounter(
                        $(this).attr('pid'),
                        $(this).attr('pubpid'),
                        $(this).attr('pname'),
                        $(this).attr('encid'),
                        $(this).attr('encdate'),
                        $(this).attr('pdob')
                    );
                });
                $(".ptrow").click(function () {
                    topatient(
                        $(this).attr('pid'),
                        $(this).attr('pubpid'),
                        $(this).attr('pname'),
                        $(this).attr('encid'),
                        $(this).attr('encdate'),
                        $(this).attr('pdob')
                    );
                });
                
                $(".reportrow").attr('title', '<?php echo xl('Click through to correct this record', 'e') ?>');
            });

        </script>
    </script>

</head>

<body class="body_top">
    <span class='title'><?php xl('Report', 'e'); ?> - <?php xl('Pre-billing Issues', 'e'); ?></span>
    
    <p>
        <?php xl('Use this report to discover billing errors. You may click through each row to drill into the record that requires an update.', 'e') ?>
    </p>

    <h5><?php xl('Encounters without rendering provider', 'e') ?></h5>
    <div id="report_results">
        <table>
            <thead>
               <th>&nbsp;<?php xl('Patient Name','e')?></th>
               <th>&nbsp;<?php xl('Encounter Date','e')?></th>
            </thead>

            <?php foreach ($reportData['encountersMissingProvider'] as $index => $row) { ?>
                <tr class='encrow reportrow' 
                    bgcolor='<?php echo $index % 2 == 0 ? "#ffdddd" : "#ddddff" ?>'
                    pid='<?php echo $row['Pt ID'] ?>' 
                    pubpid='<?php echo $row['Pub Pt ID'] ?>' 
                    pname='<?php echo $row['LName'] . ', ' . $row['FName'] ?>' 
                    encid='<?php echo htmlspecialchars(oeFormatShortDate($row['Enc ID']), ENT_QUOTES) ?>'
                    encdate='<?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($row['Encounter Date']))), ENT_QUOTES) ?>'
                    pdob='<?php echo $row['Pt DOB'] ?>' 
                >
                    <td class='detail'><?php echo $row['LName'] . ', ' . $row['FName'] ?></td>
                    <td class='detail'><?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($row['Encounter Date']))), ENT_QUOTES) ?></td>
                </tr>
            <?php } ?>
        </table>

        <h5><?php xl('Incomplete patient insurance subscriber fields', 'e') ?></h5>
        <table>
            <thead>
               <th>&nbsp;<?php xl('Patient Name','e')?></th>
               <th>&nbsp;<?php xl('Insurance Type','e')?></th>
               <th>&nbsp;<?php xl('Subscriber Relationship','e')?></th>
               <th>&nbsp;<?php xl('Errors','e')?></th>            
            </thead>
            
            <?php foreach ($reportData['patientInsuranceMissingSubscriberFields'] as $index => $row) { ?>
                <tr class='ptrow reportrow' 
                    bgcolor='<?php echo $index % 2 == 0 ? "#ffdddd" : "#ddddff" ?>'
                    pid='<?php echo $row['Pt ID'] ?>' 
                    pubpid='<?php echo $row['Pub Pt ID'] ?>' 
                    pname='<?php echo $row['LName'] . ', ' . $row['FName'] ?>' 
                    encid='<?php echo htmlspecialchars($row['Enc ID'], ENT_QUOTES) ?>'
                    encdate='<?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($row['Encounter Date']))), ENT_QUOTES) ?>'
                    pdob='<?php echo oeFormatShortDate($row['Pt DOB']) ?>' 
                >
                    <td class='detail'><?php echo $row['LName'] . ', ' . $row['FName'] ?></td>
                    <td class='detail'><?php echo $row['Insurance Type'] ?></td>
                    <td class='detail'><?php echo $row['Subscriber Relationship'] ?></td>
                    <td class='detail'>
                        <?php foreach ($row['decodedErrors'] as $error) { ?>
                            <?php echo xl($error, 'e') ?> <br>
                        <?php } ?>
                    </td>                    
                </tr>
            <?php } ?>
        </table>

        <h5><?php xl('Incomplete patient insurance missing subscriber relationship', 'e') ?></h5>
        <table>
            <thead>
               <th>&nbsp;<?php xl('Patient Name','e')?></th>
               <th>&nbsp;<?php xl('Insurance Type','e')?></th>
            </thead>
            <?php foreach ($reportData['patientInsuranceMissingSubscriberRelationship'] as $index => $row) { ?>
                <tr class='ptrow reportrow' 
                    bgcolor='<?php echo $index % 2 == 0 ? "#ffdddd" : "#ddddff" ?>'
                    pid='<?php echo $row['Pt ID'] ?>' 
                    pubpid='<?php echo $row['Pub Pt ID'] ?>' 
                    pname='<?php echo $row['LName'] . ', ' . $row['FName'] ?>' 
                    encid='<?php echo htmlspecialchars($row['Enc ID'], ENT_QUOTES) ?>'
                    encdate='<?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($row['Encounter Date']))), ENT_QUOTES) ?>'
                    pdob='<?php echo oeFormatShortDate($row['Pt DOB']) ?>' 
                >
                    <td class='detail'><?php echo $row['LName'] . ', ' . $row['FName'] ?></td>
                    <td class='detail'><?php echo $row['Insurance Type'] ?></td>
                </tr>
            <?php } ?>
        </table>

        <h5><?php xl('Incomplete patient insurance', 'e') ?></h5>
        <table>
            <thead>
               <th>&nbsp;<?php xl('Patient Name','e')?></th>
               <th>&nbsp;<?php xl('Insurance Type','e')?></th>
               <th>&nbsp;<?php xl('Errors','e')?></th>
            </thead>            
            <?php foreach ($reportData['patientInsuranceMissingInsuranceFields'] as $index => $row) { ?>
                <tr class='ptrow reportrow' 
                    bgcolor='<?php echo $index % 2 == 0 ? "#ffdddd" : "#ddddff" ?>'
                    pid='<?php echo $row['Pt ID'] ?>' 
                    pubpid='<?php echo $row['Pub Pt ID'] ?>' 
                    pname='<?php echo $row['LName'] . ', ' . $row['FName'] ?>' 
                    encid='<?php echo htmlspecialchars($row['Enc ID'], ENT_QUOTES) ?>'
                    encdate='<?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($row['Encounter Date']))), ENT_QUOTES) ?>'
                    pdob='<?php echo oeFormatShortDate($row['Pt DOB']) ?>' 
                >
                    <td class='detail'><?php echo $row['LName'] . ', ' . $row['FName'] ?></td>
                    <td class='detail'><?php echo $row['Insurance Type'] ?></td>
                    <td class='detail'>
                        <?php foreach ($row['decodedErrors'] as $error) { ?>
                            <?php echo xl($error, 'e') ?> <br>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>