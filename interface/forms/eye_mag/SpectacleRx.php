<?php

/** 
* forms/eye_mag/SpectacleRx.php 
* 
* Functions for printing a glasses prescription
* 
* Copyright (C) 2014 Raymond Magauran <magauran@MedFetch.com> 
* 
* LICENSE: This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 3 
* of the License, or (at your option) any later version. 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details. 
* You should have received a copy of the GNU General Public License 
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;. 
* 
* @package OpenEMR 
* @author Ray Magauran <magauran@MedFetch.com> 
* @link http://www.open-emr.org 
*/

$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");

if ($_REQUEST['mode'] =="update") {  //store any changed fields
// in dispense table
    $table_name = "form_eye_mag_dispense";
    $query = "show columns from ".$table_name;
    $dispense_fields = sqlStatement($query);
    $fields = array();
      
    if (sqlNumRows($dispense_fields) > 0) {
        while ($row = sqlFetchArray($dispense_fields)) {
      //exclude critical columns/fields, define below as needed
      if ($row['Field'] == 'id' or 
         $row['Field'] == 'pid' or 
         $row['Field'] == 'user' or 
         $row['Field'] == 'groupname' or 
         $row['Field'] == 'authorized' or 
         $row['Field'] == 'activity' //or 
        // $row['Field'] == 'REFDATE' 
         ) 
        continue;
            if (isset($_POST[$row['Field']])) $fields[$row['Field']] = $_POST[$row['Field']];
        }
        $fields['REFTYPE']=$target;
      //  $fields['REFDATE'] = $data['date'];

        $insert_this_id = formUpdate($table_name, $fields, $_POST['id'], $_SESSION['userauthorized']);
        echo "insert_this_id = ".$insert_this_id;
    }
   // $query ="UPDATE form_eye_mag_dispense set REFTYPE=? where id=?";
   // echo $query;
   // sqlStatement($query,array($_REQUEST['REFTYPE'],$_REQUEST['id']));
    exit;
} elseif ($_REQUEST['REFTYPE']) {  //store any changed fields
$query ="UPDATE form_eye_mag_dispense set REFTYPE=? where id=?";
   echo $query;
   sqlStatement($query,array($_REQUEST['REFTYPE'],$_REQUEST['id']));
    exit;
}

if ($_REQUEST['target']) {
    $target = $_REQUEST['target'];
    $id = $_REQUEST['id'];
    $table_name = "form_eye_mag";
    if (!$_REQUEST['encounter']) { 
    $encounter = $_SESSION['encounter']; } else { $encounter = $_REQUEST['encounter']; }
    $query = "SELECT * FROM form_eye_mag JOIN forms on forms.form_id = form_eye_mag.id where form_eye_mag.pid =? and forms.encounter=? and forms.deleted !='1'";
 
    $data =  sqlQuery($query, array($id,$encounter) );
   
    if ($target =="W") {
        $ODSPH = $data['WODSPH'];
        $ODAXIS = $data['WODAXIS'];
        $ODCYL = $data['WODCYL'];
        $ODPRISM = $data['WODPRISM'];
        $OSSPH = $data['WOSSPH'];
        $OSCYL = $data['WOSCYL'];
        $OSAXIS = $data['WOSAXIS'];
        $OSPRISM = $data['WOSPRISM'];
        $COMMENTS = $data['WCOMMENTS']; 
        $ODADD1 = $data['WODADD1'];
        $ODADD2 = $data['WODADD2'];
        $OSADD1 = $data['WODADD1'];
        $OSADD2 = $data['WODADD2'];
        if ($data['ODADD1']) {
            $trifocal ='checked="checked"';
        } elseif ($ODADD2){
            $bifocal ='checked="checked"';
        } else {
            $single='checked="checked"';
        }
    } elseif ($target =="AR") {
            $ODSPH = $data['ARODSPH'];
            $ODAXIS = $data['ARODAXIS'];
            $ODCYL = $data['ARODCYL'];
            $ODPRISM = $data['ARODPRISM'];
            $OSSPH = $data['AROSSPH'];
            $OSCYL = $data['AROSCYL'];
            $OSAXIS = $data['AROSAXIS'];
            $OSPRISM = $data['AROSPRISM'];
            $COMMENTS = $data['CRCOMMENTS']; 
            $ODADD1 = $data['ARODADD'];
            $OSADD1 = $data['AROSADD'];
    } elseif ($target =="MR") {
            $ODSPH = $data['MRODSPH'];
            $ODAXIS = $data['MRODAXIS'];
            $ODCYL = $data['MRODCYL'];
            $ODPRISM = $data['MRODPRISM'];
            $OSSPH = $data['MROSSPH'];
            $OSCYL = $data['MROSCYL'];
            $OSAXIS = $data['MROSAXIS'];
            $OSPRISM = $data['MROSPRISM'];
            $COMMENTS = $data['CRCOMMENTS']; 
            $ODADD1 = $data['MRODADD'];
            $OSADD1 = $data['MROSADD'];
    } elseif ($target =="CR") {
            $ODSPH = $data['CRODSPH'];
            $ODAXIS = $data['CRODAXIS'];
            $ODCYL = $data['CRODCYL'];
            $ODPRISM = $data['CRODPRISM'];
            $OSSPH = $data['CROSSPH'];
            $OSCYL = $data['CROSCYL'];
            $OSAXIS = $data['CROSAXIS'];
            $OSPRISM = $data['CROSPRISM'];
            $COMMENTS = $data['CRCOMMENTS']; 
    } elseif ($target=="CTL") {
            $ODSPH = $data['CTLODSPH'];
            $ODAXIS = $data['CTLODAXIS'];
            $ODCYL = $data['CTLODCYL'];
            $ODPRISM = $data['CTLODPRISM'];
            
            $OSSPH = $data['CTLOSSPH'];
            $OSCYL = $data['CTLOSCYL'];
            $OSAXIS = $data['CTLOSAXIS'];
            $OSPRISM = $data['CTLOSPRISM'];
            
            $CTLODBC = $data['CTLODBC'];
            $CTLODDIAM = $data['CTLODDIAM'];
            $CTLODADD = $data['CTLODADD'];
            $CTLODVA = $data['CTLODVA'];

            $CTLODBC = $data['CTLOSBC'];
            $CTLODDIAM = $data['CTLOSDIAM'];
            $CTLODADD = $data['CTLOSADD'];
            $CTLODVA = $data['CTLOSVA'];

            $COMMENTS = $data['CTLCOMMENTS']; 
            $CTLMANUFACTUREROD = $data['CTLMANUFACTUREROD'];
            $CTLMANUFACTUREROS = $data['CTLMANUFACTUREROS'];
            $CTLSUPPLIEROD = $data['CTLSUPPLIEROD'];
            $CTLSUPPLIEROS = $data['CTLSUPPLIEROS'];
            $CTLBRANDOD = $data['CTLBRANDOD'];
            $CTLBRANDOS = $data['CTLBRANDOS'];
    }
    $form_name = "eye_mag";
    $form_folder = "eye_mag";
    formHeader("Rx Vision: ".$form_name);

    $query = "SELECT * FROM patient_data where pid=?";
    $pat_data =  sqlQuery($query,array($data['pid']));

    $query = "SELECT * FROM users where id = ?";
    $prov_data =  sqlQuery($query,array($_SESSION['authUserID']));
    $query = "SELECT * FROM facility WHERE primary_business_entity='1'";
    $practice_data = sqlQuery($query); 

    $table_name = "form_eye_mag_dispense";
    $query = "show columns from ".$table_name;
    $dispense_fields = sqlStatement($query);
    $fields = array();
      
    if (sqlNumRows($dispense_fields) > 0) {
        while ($row = sqlFetchArray($dispense_fields)) {
      //exclude critical columns/fields, define below as needed
      if ($row['Field'] == 'id' or 
         $row['Field'] == 'pid' or 
         $row['Field'] == 'user' or 
         $row['Field'] == 'groupname' or 
         $row['Field'] == 'authorized' or 
         $row['Field'] == 'activity' or 
         $row['Field'] == 'REFDATE' 
         ) 
        continue;
            if (isset(${$row['Field']})) $fields[$row['Field']] = ${$row['Field']};
        }
        $fields['REFTYPE']=$target;
        $fields['REFDATE'] = $data['date'];
        $insert_this_id = formSubmit($table_name, $fields, $form_id, $_SESSION['userauthorized']);
    }
}
if ($_REQUEST['dispensed']) {
    $query = "SELECT * FROM patient_data where pid=?";
    $pat_data =  sqlQuery($query,array($_REQUEST['pid']));
    
    $query = "SELECT * from form_eye_mag_dispense where pid =? ORDER BY date DESC";
    $dispensed = sqlStatement($query,array($_REQUEST['pid']));
    ?><html><title>Rx Dispensed History</title>
    <head>
        <style>
            .refraction {
                top:1in;
                float:left;
                min-height:1.8in;
                border: 1.00pt solid #000000; 
                padding: 25; 
                box-shadow: 10px 10px 5px #888888;
                border-radius: 8px;
                margin: 5 auto;
                margin-right: 4px; 
                width:5.5in;
            }
            .refraction td {
                text-align:center;
                font-size:8pt;
                padding:5;
                width:0.35in;
                vertical-align: text-middle;
                text-decoration: none;
            }
            table {
                color:white;
                font-size: 0.8em;
                padding: 2px;
                color: black;
                width:5.5in;
                vertical-align: text-top;
            }

            input[type=text] {
                text-align: right;
                width:80px;
            }
            .refraction  b{
                text-decoration:bold;
            }
            .refraction td.right {
                text-align: right;
                text-decoration: none;
                width:0.7in;
                vertical-align: text-top;
            }

            .right {
                text-align:right;
                vertical-align: text-top;
                width:10%;
            }
            .left {
                vertical-align: text-top;
            }
            .title {
                font-size: 0.9em;
                font-weight:normal;
            }
        </style>
        <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/font-awesome-4.2.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/pure-min.css">
        <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/bootstrap-3-2-0.min.css">
        <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/css/bootstrap-responsive.min.css">
        <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css">    
      
    </head>
    <body>
        <table> 
            <tr>
                <td class="right"><?php echo xlt('Name'); ?>:
                </td>   
                <td class="left" ><?php echo text($pat_data['fname']); ?> <?php echo text($pat_data['lname']); ?>
                </td>
            </tr>
            <tr>
                <td class="right">
                    <?php echo xlt('Address'); ?>:
                </td>
                <td > <?php echo text($pat_data['street']); ?><br /> <?php echo text($pat_data['city']); ?>, <?php echo text($pat_data['state']); ?>
                </td>
            </tr>
            <?php
            if (sqlNumRows($dispensed) == 0) {
                echo "<tr><td> No previous Rx written</td></tr>";
            }
            echo "</table>";
    while ($row = sqlFetchArray($dispensed)) {
        $i++;
        $Single ='';$Bifocal='';$Trifocal='';$Progressive='';
        if ($row['REFTYPE'] == "Single") $Single = 'checked="checked"';
        if ($row['REFTYPE'] == "Bifocal") $Bifocal = 'checked="checked"';
        if ($row['REFTYPE'] == "Trifocal") $Trifocal = 'checked="checked"';
        if ($row['REFTYPE'] == "Progressive") $Progressive = 'checked="checked"';
        ?>
        <table>
            <tr>
                <td colspan="5">
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="right bold" width="50%"><b><?php echo xlt('Date'); ?>: </b>
                </td><td>&nbsp;&nbsp;<?php echo xlt($row['date']); ?>
                </td>
            </tr>
           
            <tr>
                <td colspan="8">
                    <?php echo "target is ".$target; if ($target != "CTL") { ?>
                        <table id="SpectacleRx" name="SpectacleRx" class="refraction">
                        <tr style="font-style:bold;">
                            <td></td>
                            <td></td>
                            <td><?php echo xlt('Sph{{Sphere setting in a glasses prescription}}'); ?></td>
                            <td><?php echo xlt('Cyl{{Cylinder setting in a glasses prescription}}'); ?></td>
                            <td><?php echo xlt('Axis{{Axis setting in a glasses prescription}}'); ?></td>
                            <td><?php echo xlt('Prism{{Prism setting in a glasses prescription}}') ?></td>
                            <td rowspan="5" class="right" style="width:200px;">
                                <b style="font-weight:bold;text-decoration:underline;"><?php echo xlt('Rx Type'); ?></b><br /><br />
                                <b id="SingleVision_span" name="SingleVision_span"><?php echo xlt('Single'); ?>
                                    <input type="radio" value="0" id="RX$i" name="RX<?php echo $i; ?>" class="input-helper--radio input-helper--radio" <?php echo $Single; ?>></b><br />
                                <b id="Bifocal_span" name="Bifocal_span"><?php echo xlt('Bifocal'); ?>
                                    <input type="radio" value="1" id="RX$i" name="RX<?php echo $i; ?>" <?php echo $Bifocal; ?>></b><br />
                                <b id="Trifocal_span" name="Trifocal_span"><?php echo xlt('Trifocal'); ?>
                                    <input type="radio" value="2" id="RX$i" name="RX<?php echo $i; ?>" <?php echo $Trifocal; ?>></b><br />
                                <b id="Progressive_span"><?php echo xlt('Prog.{{Progressive lenses}}'); ?>
                                    <input type="radio" value="3" id="RX$i" name="RX<?php echo $i; ?>" <?php echo $Progressive; ?>></b><br />
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2"><?php echo xlt('Distance'); ?></td>    
                            <td><b><?php echo xlt('OD'); ?></b></td>
                            <td><input type=text id="ODSPH" name="ODSPH" value="<?php echo attr($row['ODSPH']); ?>"></td>
                            <td><input type=text id="ODCYL" name="ODCYL" value="<?php echo attr($row['ODCYL']); ?>"></td>
                            <td><input type=text id="ODAXIS" name="ODAXIS" value="<?php echo attr($row['ODAXIS']); ?>"></td>
                            <td><input type=text id="ODPRISM" name="ODPRISM" value="<?php echo attr($row['ODPRISM']); ?>"></td>
                        </tr>
                        <tr>
                            <td><b><?php echo xlt('OS'); ?></b></td>
                            <td><input type=text id="OSSPH" name=="OSSPH" value="<?php echo attr($row['OSSPH']); ?>"></td>
                            <td><input type=text id="OSCYL" name="OSCYL" value="<?php echo attr($row['OSCYL']); ?>"></td>
                            <td><input type=text id="OSAXIS" name="OSAXIS" value="<?php echo attr($row['OSAXIS']); ?>"></td>
                            <td><input type=text id="OSPRISM" name="OSPRISM" value="<?php echo attr($row['OSPRISM']); ?>"></td>
                        </tr>
                        <tr class="NEAR">
                            <td rowspan=2><span style="text-decoration:none;"><?php echo xlt("Mid{{Middle segment in a trifocal glasses prescription}}"); ?>/<br /><?php echo xlt("Near"); ?></span></td>    
                            <td><b><?php echo xlt('OD'); ?></b></td>
                            <td class="WMid nodisplay"><input type="text" id="ODADD1" name="ODADD1" value="<?php echo attr($row['ODADD1']); ?>"></td>
                            <td class="WAdd2"><input type="text" id="ODADD2" name="ODADD2" value="<?php echo attr($row['ODADD2']); ?>"></td>
                        </tr>
                        <tr class="NEAR">
                            <td><b><?php echo xlt('OS'); ?></b></td>
                            <td class="WMid nodisplay"><input type="text" id="OSADD1" name="OSADD1" value="<?php echo attr($row['OSADD1']); ?>"></td>
                            <td class="WAdd2"><input type="text" id="OSADD2" name="OSADD2" value="<?php echo attr($row['OSADD2']); ?>"></td>
                        </tr>
                        <tr style="">
                            <td colspan="2" class="up" style="text-align:right;vertical-align:top;top:0px;"><b><?php echo xlt('Comments'); ?>:</b>
                            </td>
                            <td colspan="4" class="up" style="text-align:left;vertical-align:middle;top:0px;">
                                <textarea style="width:100%;height:2.1em;" id="COMMENTS" name="COMMENTS"><?php echo text($row['COMMENTS']); ?></textarea>     
                            </td>
                            <!-- <td> 
                                <span class="ui-icon ui-icon-clock">&nbsp; </span>
                                <span href="print.php?target=W" class="ui-icon ui-icon-cancel" onclick= "window.print();" Xonclick="top.restoreSession(); window.print(); return false;" style="display:inline-block"><?php echo xlt("Print"); ?></span> 
                            </td> -->
                        </tr>
                        </table>
                    <?php } else { ?>
                        <table id="CTLRx" name="CTLRx">
                            <tr>
                                <td><?php echo attr($CTLODSPH); ?></td>
                                        <td><?php echo attr($CTLODCYL); ?></td>
                                        <td><?php echo attr($CTLODAXIS); ?></td>
                                        <td><?php echo attr($CTLODBC); ?></td>
                                        <td><?php echo attr($CTLODDIAM); ?></td>
                                        <td><?php echo attr($CTLODADD); ?></td>
                                        <td><?php echo attr($CTLODVA); ?></td>
                            <tr>
                                <td colspan=3 class="right"><?php echo xlt('Manufacturer'); ?></td>
                                <td colspan=3><?php echo xlt($ODCTLMANUFACTURER); ?></td>
                            </tr>
                            <tr>
                                <td colspan=3 class="right"><?php echo xlt('Brand'); ?></td>
                                <td colspan=3><?php echo xlt($ODCTLBRAND); ?></td>
                            </tr>
                            <tr>
                                <td><?php echo attr($CTLODSPH); ?></td>
                                        <td><?php echo attr($CTLODCYL); ?></td>
                                        <td><?php echo attr($CTLODAXIS); ?></td>
                                        <td><?php echo attr($CTLODBC); ?></td>
                                        <td><?php echo attr($CTLODDIAM); ?></td>
                                        <td><?php echo attr($CTLODADD); ?></td>
                                        <td><?php echo attr($CTLODVA); ?></td>
                            <tr>
                                <td colspan=3 class="right"><?php echo xlt('Manufacturer'); ?></td>
                                <td colspan=3><?php echo xlt($ODCTLMANUFACTURER); ?></td>
                            </tr>
                            <tr>
                                <td colspan=3 class="right"><?php echo xlt('Brand'); ?></td>
                                <td colspan=3><?php echo xlt($ODCTLBRAND); ?></td>
                            </tr>
                            <tr>
                            </tr>
                        </table>
                    <?php } ?>
                </td>
            </tr>
        </table>
    
        <?php 

    }
    ?>
    </body>
    </html>
    <?php 
    exit;


}
?><html>
    <head>
        <style>
            .refraction {
                top:1in;
                float:left;
                min-height:1.8in;
                border: 1.00pt solid #000000; 
                padding: 25; 
                box-shadow: 10px 10px 5px #888888;
                border-radius: 8px;
                margin: 5 auto;
                margin-right: 4px; 
                width:5.5in;
            }
            .refraction td {
                text-align:center;
                font-size:8pt;
                padding:5;
                width:0.35in;
                vertical-align: text-middle;
                text-decoration: none;
            }
            table {
                color:white;
                font-size: 0.8em;
                padding: 2px;
                color: black;
                width:5.5in;
                vertical-align: text-top;
            }

            input[type=text] {
                text-align: right;
                width:80px;
            }
            .refraction  b{
                text-decoration:bold;
            }
            .refraction td.right {
                text-align: right;
                text-decoration: none;
                width:0.7in;
                vertical-align: text-top;
            }

            .right {
                text-align:right;
                vertical-align: text-top;
                width:10%;
            }
            .left {
                vertical-align: text-top;
            }
            .title {
                font-size: 0.9em;
                font-weight:normal;
            }
        </style>
        <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/font-awesome-4.2.0/css/font-awesome.min.css">
             <!-- jQuery library -->
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.min.js"></script>
    <!-- Latest compiled JavaScript -->
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/bootstrap.min.js"></script>  
   
        <script>
            function pick_refType(reftype,id) {
                //update reftype
                //alert(id+' is the id');
                var url = "../../forms/eye_mag/SpectacleRx.php";
                var formData = {
                    'REFTYPE'     : reftype,
                    'id'          : id
                };
            $.ajax({
               type         : 'POST',
               url          : url,
               data         : formData
               }).done(function(o) {
                    console.log('data is '+formData);
                       });
            }
            function submit_form(){
                var url = "../../forms/eye_mag/SpectacleRx.php?mode=update";
                //$("#UNDO_ID").val(parseInt($("#UNDO_ID").val()) + 1);
                //client side variable with all fields incremented with these new save values
                formData = $("form#Spectacle").serialize();
                // formFields.push = serializeArray;
                $.ajax({
                   type     : 'POST',   // define the type of HTTP verb we want to use (POST for our form)
                   url      : url,      // the url where we want to POST
                   data     : formData // our data object
                   }).done(function(o) {
                    console.log('data is '+formData);
                });
            }
        </script>

    </head>
    <body>
        <form method="post" action="<?php echo $rootdir;?>/forms/<?php echo $form_folder; ?>/SpectacleRx.php?mode=update" id="Spectacle" class="eye_mag pure-form" name="Spectacle">
      <!-- start container for the main body of the form -->
      <input type="hidden" name="REFDATE" id="REFDATE" value="<?php echo date('F jS\,  Y'); ?>">
      <input type="hidden" name="pid" id="pid" value="<?php echo $pid; ?>">
      <input type="hidden" name="id" id="id" value="<?php echo $insert_this_id; ?>">

        <table class="title">
            <tr>
                <th colspan="3">
                    <br>
                    <?php echo text($prov_data[facility]); ?><br />
                    <?php echo text($practice_data[street]); ?><br />
                    <?php echo text($practice_data[city]); ?>, <?php echo text($practice_data[state]); ?> &nbsp;&nbsp;<?php echo text($practice_data[zip]); ?><br />
                    <?php echo xlt("Phone"); ?>: <?php echo text($practice_data[phone]); ?><br />
                    <?php echo xlt("Fax"); ?>: <?php echo text($practice_data[fax]); ?><br />
                </th>
            </tr>
             <tr>
                <td class="right" width="50%"><?php echo xlt('Date'); ?>: 
                </td><td><u><?php echo xlt(date('F jS\,  Y')); ?></u>
                </td>
            </tr>
            <tr>
                <td class="right"><?php echo xlt('Name'); ?>:
                </td>   
                <td class="left" ><?php echo text($pat_data['fname']); ?> <?php echo text($pat_data['lname']); ?>
                </td>
            </tr>
            <tr>
                <td class="right">
                    <?php echo xlt('Address'); ?>:
                </td>
                <td > <?php echo text($pat_data['street']); ?><br /> <?php echo text($pat_data['city']); ?>, <?php echo text($pat_data['state']); ?>
                </td>
            </tr>
            <tr>
                <td colspan="8">
                    <?php if ($target !="CTL") { ?>
                    <table id="SpectacleRx" name="SpectacleRx" class="refraction">
                        <tr style="font-style:bold;">
                            <td></td>
                            <td></td>
                            <td><?php echo xlt('Sph{{Sphere setting in a glasses prescription}}'); ?></td>
                            <td><?php echo xlt('Cyl{{Cylinder setting in a glasses prescription}}'); ?></td>
                            <td><?php echo xlt('Axis{{Axis setting in a glasses prescription}}'); ?></td>
                            <td><?php echo xlt('Prism{{Prism setting in a glasses prescription}}') ?></td>
                            <td rowspan="5" class="right" style="width:200px;">
                                <b style="font-weight:bold;text-decoration:underline;"><?php echo xlt('Rx Type'); ?></b><br /><br />
                                <b id="SingleVision_span" name="SingleVision_span"><?php echo xlt('Single'); ?>
                                    <input type="radio" onclick="pick_refType('Single','<?php echo $insert_this_id; ?>');" value="Single" id="REFTYPE" name="REFTYPE" class="input-helper--radio input-helper--radio" <?php echo attr($single); ?>></b><br />
                                <b id="Bifocal_span" name="Bifocal_span"><?php echo xlt('Bifocal'); ?>
                                    <input type="radio" onclick="pick_refType('Bifocal','<?php echo $insert_this_id; ?>');" value="Bifocal" id="REFTYPE" name="REFTYPE" <?php echo attr($bifocal); ?>></b><br />
                                <b id="Trifocal_span" name="Trifocal_span"><?php echo xlt('Trifocal'); ?>
                                    <input type="radio" onclick="pick_refType('Trifocal','<?php echo $insert_this_id; ?>');" value="Trifocal" id="REFTYPE" name="REFTYPE" <?php echo attr($trifocal); ?>></b><br />
                                <b id="Progressive_span"><?php echo xlt('Prog.{{Progressive lenses}}'); ?>
                                    <input type="radio" onclick="pick_refType('Progressive','<?php echo $insert_this_id; ?>');" value="Progressive" id="REFTYPE" name="REFTYPE" <?php echo attr($progressive); ?>></b><br />
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2"><?php echo xlt('Distance'); ?></td>    
                            <td><b><?php echo xlt('OD'); ?></b></td>
                            <td><input type=text id="ODSPH" name="ODSPH" value="<?php echo attr($ODSPH); ?>"></td>
                            <td><input type=text id="ODCYL" name="ODCYL" value="<?php echo attr($ODCYL); ?>"></td>
                            <td><input type=text id="ODAXIS" name="ODAXIS" value="<?php echo attr($ODAXIS); ?>"></td>
                            <td><input type=text id="ODPRISM" name="ODPRISM" value="<?php echo attr($ODPRISM); ?>"></td>
                        </tr>
                        <tr>
                            <td><b><?php echo xlt('OS'); ?></b></td>
                            <td><input type=text id="OSSPH" name=="OSSPH" value="<?php echo attr($OSSPH); ?>"></td>
                            <td><input type=text id="OSCYL" name="OSCYL" value="<?php echo attr($OSCYL); ?>"></td>
                            <td><input type=text id="OSAXIS" name="OSAXIS" value="<?php echo attr($OSAXIS); ?>"></td>
                            <td><input type=text id="OSPRISM" name="OSPRISM" value="<?php echo attr($OSPRISM); ?>"></td>
                        </tr>
                        <tr class="NEAR">
                            <td rowspan=2><span style="text-decoration:none;"><?php echo xlt("Mid{{Middle segment in a trifocal glasses prescription}}"); ?>/<br /><?php echo xlt("Near"); ?></span></td>    
                            <td><b><?php echo xlt('OD'); ?></b></td>
                            <td class="WMid nodisplay"><input type="text" id="ODADD1" name="ODADD1" value="<?php echo attr($ODADD1); ?>"></td>
                            <td class="WAdd2"><input type="text" id="ODADD2" name="ODADD2" value="<?php echo attr($ODADD2); ?>"></td>
                        </tr>
                        <tr class="NEAR">
                            <td><b><?php echo xlt('OS'); ?></b></td>
                            <td class="WMid nodisplay"><input type="text" id="OSADD1" name="OSADD1" value="<?php echo attr($OSADD1); ?>"></td>
                            <td class="WAdd2"><input type="text" id="OSADD2" name="OSADD2" value="<?php echo attr($OSADD2); ?>"></td>
                        </tr>
                        <tr style="">
                            <td colspan="2" class="up" style="text-align:right;vertical-align:top;top:0px;"><b><?php echo xlt('Comments'); ?>:</b>
                            </td>
                            <td colspan="4" class="up" style="text-align:left;vertical-align:middle;top:0px;">
                                <textarea style="width:100%;height:2.1em;" id="COMMENTS" name="COMMENTS"><?php echo text($COMMENTS); ?></textarea>     
                            </td>
                         <!--   <td> 
                                <span class="ui-icon ui-icon-clock">&nbsp; </span>
                                <span href="print.php?target=W" class="ui-icon ui-icon-cancel" onclick= "window.print();" Xonclick="top.restoreSession(); window.print(); return false;" style="display:inline-block"><?php echo xlt("Print"); ?></span> 
                            </td>
                        -->
                        </tr>
                    </table>&nbsp;<br /><br /><br />
                    <?php } else { ?>
                     <table id="CTLRx" name="CTLRx" class="refraction2">
                            <tr>
                                <td><?php echo attr($CTLODSPH); ?></td>
                                        <td><?php echo attr($CTLODCYL); ?></td>
                                        <td><?php echo attr($CTLODAXIS); ?></td>
                                        <td><?php echo attr($CTLODBC); ?></td>
                                        <td><?php echo attr($CTLODDIAM); ?></td>
                                        <td><?php echo attr($CTLODADD); ?></td>
                                        <td><?php echo attr($CTLODVA); ?></td>
                            <tr>
                                <td colspan=3 class="right"><?php echo xlt('Manufacturer'); ?></td>
                                <td colspan=3><?php echo xlt($ODCTLMANUFACTURER); ?></td>
                            </tr>
                            <tr>
                                <td colspan=3 class="right"><?php echo xlt('Brand'); ?></td>
                                <td colspan=3><?php echo xlt($ODCTLBRAND); ?></td>
                            </tr>
                            <tr>
                                <td><input type=text id="CTLODSPH" name="CTLODSPH" value="<?php echo attr($ODSPH); ?>"></td>
                                <td><input type=text id="CTLODCYL" name="CTLODCYL" value="<?php echo attr($ODCYL); ?>"></td>
                                <td><input type=text id="CTLODAXIS" name="CTLODAXIS" value="<?php echo attr($ODAXIS); ?>"></td>
                                <td><input type=text id="CTLODBC" name="CTLODBC" value="<?php echo attr($ODBC); ?>"></td>
                                <td><input type=text id="CTLODDIAM" name="CTLODDIAM" value="<?php echo attr($ODDIAM); ?>"></td>
                                <td><input type=text id="CTLODADD" name="CTLODADD" value="<?php echo attr($ODADD); ?>"></td>
                                <td><input type=text id="CTLODVA" name="CTLODVA" value="<?php echo attr($ODVA); ?>"></td>
                            <tr>
                                <td colspan=3 class="right"><?php echo xlt('Manufacturer'); ?></td>
                                <td colspan=3><?php echo xlt($ODCTLMANUFACTURER); ?></td>
                            </tr>
                            <tr>
                                <td colspan=3 class="right"><?php echo xlt('Brand'); ?></td>
                                <td colspan=3><?php echo xlt($ODCTLBRAND); ?></td>
                            </tr>
                            <tr>
                            </tr>
                        </table>
                    <? } ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="margin:25px auto;margin:50px;text-align:center;">
               <?php
                    $signature = $GLOBALS["webserver_root"]."/interface/forms/eye_mag/images/sign_".$_SESSION['authUserID'].".jpg";
                    if (file_exists($signature)) {
                        ?><center>
                        <div style="position:relative;left:0.in;padding-left:40px;border-bottom:2pt solid black;width:50%;">
                            <img src="/openemr/interface/forms/eye_mag/images/sign_<?php echo $_SESSION['authUserID']; ?>.jpg" style="width:240px;height:85px;bottom:1px;" /> 
                        </div>
                    </center>
                        <?php } ?>

            <?php echo xlt('Provider'); ?>: <?php echo text($prov_data['fname']); ?> <?php echo text($prov_data['lname']); ?> <?php echo text($prov_data['title']); ?></br>
                    <small><?php echo xlt('e-signed'); ?> <input type="checkbox" checked="checked">
                </td>
            </tr>
        </table>  
    </form>
        <script>
            $(document).ready(function() {
                $("input[type='text']").blur(function() {
                    //alert('blur')
                    submit_form();
                    });
            });
        </script>
    </body>
    </html>

    <? 
exit;
?> 
