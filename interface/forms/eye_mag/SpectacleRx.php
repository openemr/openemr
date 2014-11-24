<?php

/** 
* forms/eye_mag/SpectacleRx.php 
* 
* Functions for printing a glasses prescription
* 
* Copyright (C) 14 Raymond Magauran <magauran@MedFetch.com> 
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

@extract($_REQUEST);

if ($_GET['target']) {
    $table_name = "form_eye_mag";
    if (!$encounter) $encounter = $_SESSION['encounter'];
    $query = "SELECT * FROM form_eye_mag JOIN forms on forms.form_id = form_eye_mag.id where forms.encounter=?";
    $data =  sqlQuery($query, array($encounter) );
    @extract($data);
    if ($target =="W") {
        $ODSPH = $WODSPH;
        $ODAXIS = $WODAXIS;
        $ODCYL = $WODCYL;
        $ODPRISM = $WODPRISM;
        $OSSPH = $WOSSPH;
        $OSCYL = $WOSCYL;
        $OSAXIS = $WOSAXIS;
        $OSPRISM = $WOSPRISM;
        $COMMENTS = $WCOMMENTS; 
        $ODADD1 = $WODADD1;
        $ODADD2 = $WODADD2;
        $OSADD1 = $WODADD1;
        $OSADD2 = $WODADD2;
        if ($ODADD1) {
            $trifocal ='checked="checked"';
        } else if ($ODADD2){
            $bifocal ='checked="checked"';
        } else {
            $single='checked="checked"';
        }
    } else if ($target =="AR") {
            $ODSPH = $ARODSPH;
            $ODAXIS = $ARODAXIS;
            $ODCYL = $ARODCYL;
            $ODPRISM = $ARODPRISM;
            $OSSPH = $AROSSPH;
            $OSCYL = $AROSCYL;
            $OSAXIS = $AROSAXIS;
            $OSPRISM = $AROSPRISM;
            $COMMENTS = $CRCOMMENTS; 
            $ODADD1 = $ARODADD;
            $OSADD1 = $AROSADD;
    } else if ($target =="MR") {
            $ODSPH = $MRODSPH;
            $ODAXIS = $MRODAXIS;
            $ODCYL = $MRODCYL;
            $ODPRISM = $MRODPRISM;
            $OSSPH = $MROSSPH;
            $OSCYL = $MROSCYL;
            $OSAXIS = $MROSAXIS;
            $OSPRISM = $MROSPRISM;
            $COMMENTS = $CRCOMMENTS; 
            $ODADD1 = $MRODADD;
            $OSADD1 = $MROSADD;
    } else if ($target =="CR") {
            $ODSPH = $CRODSPH;
            $ODAXIS = $CRODAXIS;
            $ODCYL = $CRODCYL;
            $ODPRISM = $CRODPRISM;
            $OSSPH = $CROSSPH;
            $OSCYL = $CROSCYL;
            $OSAXIS = $CROSAXIS;
            $OSPRISM = $CROSPRISM;
            $COMMENTS = $CRCOMMENTS; 
    }
}

$form_name = "eye_mag";
$form_folder = "eye_mag";
formHeader("Rx Vision: ".$form_name);

$query = "SELECT * FROM patient_data where pid=?";
$pat_data =  sqlQuery($query,array($pid));

$query = "SELECT * FROM users where id = ?";
$prov_data =  sqlQuery($query,array($_SESSION['authUserID']));

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
        <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">

    </head>
    <body>
      <table class="title">
            <tr>
                <th colspan="3">
                    <br>
                    <?php echo text($prov_data[facility]); ?><br />
                    <?php echo text($prov_data[street]); ?><br />
                    <?php echo text($prov_data[city]); ?>, <?php echo text($prov_data[state]); ?> &nbsp;&nbsp;<?php echo text($prov_data[zip]); ?><br />
                    <?php echo xlt("Phone"); ?>: <?php echo text($prov_data[phone]); ?><br />
                    <?php echo xlt("Fax"); ?>: <?php echo text($prov_data[fax]); ?><br />
                </th>
            </tr>
            <tr>
                <td class="right"><?php echo xlt('Name'); ?>:
                </td>   
                <td class="left"><?php echo text($pat_data['fname']); ?> <?php echo text($pat_data['lname']); ?>
                </td>
                <td class="right"><?php echo xlt('Date'); ?>: <u><?php echo xlt(date('F jS\,  Y')); ?></u>
                </td>
            </tr>
            <tr>
                <td class="right">
                    <?php echo xlt('Address'); ?>:
                </td>
                <td colspan="2"> <?php echo text($pat_data['street']); ?><br /> <?php echo text($pat_data['city']); ?>, <?php echo text($pat_data['state']); ?>
                </td>
            </tr>
            <tr>
                <td colspan="8">
                    <table id="SpectacleRx" name="SpectacleRx" class="refraction">
                        <tr style="font-style:bold;">
                            <td></td>
                            <td></td>
                            <td><?php echo xlt('Sph'); ?></td>
                            <td><?php echo xlt('Cyl'); ?></td>
                            <td><?php echo xlt('Axis'); ?></td>
                            <td><?php echo xlt('Prism') ?></td>
                            <td rowspan="5" class="right" style="width:200px;">
                                <b style="font-weight:bold;text-decoration:underline;"><?php echo xlt('Rx Type'); ?></b><br /><br />
                                <b id="SingleVision_span" name="SingleVision_span"><?php echo xlt('Single'); ?>
                                    <input type="radio" value="0" id="RX1" name="RX1" class="input-helper--radio input-helper--radio" <?php echo attr($single); ?>></b><br />
                                <b id="Bifocal_span" name="Bifocal_span"><?php echo xlt('Bifocal'); ?>
                                    <input type="radio" value="1" id="RX1" name="RX1" <?php echo attr($bifocal); ?>></b><br />
                                <b id="Trifocal_span" name="Trifocal_span"><?php echo xlt('Trifocal'); ?>
                                    <input type=radio value="2" id="RX1" name="RX1" <?php echo attr($trifocal); ?>></b><br />
                                <b id="Progressive_span"><?php echo xlt('Prog.'); ?>
                                    <input type="radio" value="3" id="RX1" name="RX1" <?php echo attr($progressive); ?>></b><br />
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
                            <td rowspan=2><span style="text-decoration:none;"><?php echo xlt("Mid"); ?>/<br /><?php echo xlt("Near"); ?></span></td>    
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
                            <td> 
                                <span class="ui-icon ui-icon-clock">&nbsp; </span>
                                <span href="print.php?target=W" class="ui-icon ui-icon-cancel" onclick="top.restoreSession(); window.print(); return false;" style="display:inline-block"></span>
                                <span><?php echo xlt("Print"); ?></span> 
                            </td>
                        </tr>
                    </table>&nbsp;<br /><br /><br />
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border-top:2pt solid black;margin:25px auto;padding:20px;text-align:center;">
                    <?php echo xlt('Provider'); ?>: <?php echo text($prov_data['fname']); ?> <?php echo text($prov_data['lname']); ?>, <?php echo text($prov_data['title']); ?></br>
                    <small><?php echo xlt('e-signed'); ?> <input type="checkbox" checked="checked">
                </td>
            </tr>
        </table>  
    <? 
exit;
?> 
