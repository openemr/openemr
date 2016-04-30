<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");


if (!$_REQUEST['target']) {
    //we are printing something.
      $table_name = "form_eye_mag";
/***************************************/

/** 
 * forms/eye_mag/report.php 
 * 
 * Central report form for the eye_mag form.  Here is where all new data for display
 * is created.  New reports are created via new.php and then this script is displayed.
 * Edit are performed in view.php.  Nothing is editable here, but it is scrollable 
 * across time...
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
 *   
 *   * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  The HTML5 Sketch plugin stuff:
 *    Copyright (C) 2011 by Michael Bleigh and Intridea, Inc.
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software 
 *  and associated documentation files (the "Software"), to deal in the Software without restriction, 
 *  including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,  
 *  and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,  
 *  subject to the following conditions:
 *   
 *  The above copyright notice and this permission notice shall be included in all copies or substantial  
 *  portions of the Software.
 *   * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;
error_reporting(E_ALL & ~E_NOTICE);

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");


$form_name = "eye_mag";
$form_folder = "eye_mag";

include_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");

@extract($_REQUEST); 
@extract($_SESSION);

// Get users preferences, for this user 
// (and if not the default where a fresh install begins from, or someone else's) 
$query  = "SELECT * FROM form_eye_mag_prefs where PEZONE='PREFS' AND id=? ORDER BY ZONE_ORDER,ordering";
$result = sqlStatement($query,array($_SESSION['authUserID']));
while ($prefs= sqlFetchArray($result))   {    
    @extract($prefs);    
    $$LOCATION = $VALUE; 
}

// get pat_data and user_data
$query = "SELECT * FROM patient_data where pid=?";
$pat_data =  sqlQuery($query,array($pid));
@extract($pat_data);

$query = "SELECT * FROM users where id = ?";
$prov_data =  sqlQuery($query,array($_SESSION['authUserID']));
$providerID = $prov_data['fname']." ".$prov_data['lname'];

/** openEMR note:  eye_mag Index is id, 
  * linked to encounter in form_encounter 
  * whose encounter is linked to id in forms.
  * Would VIEW be a better way to access this data?
  * If it matters we can create the VIEW right here in eye_mag
  */ 
$query="select form_encounter.date as encounter_date,form_eye_mag.* 
                    from form_eye_mag ,forms,form_encounter 
                    where 
                    form_encounter.encounter =? and 
                    form_encounter.encounter = forms.encounter and 
                    form_eye_mag.id=forms.form_id and
                    forms.pid =form_eye_mag.pid and 
                    form_eye_mag.pid=? ";        
                   
$objQuery =sqlQuery($query,array($encounter,$pid));
@extract($objQuery);

$dated = new DateTime($encounter_date);
$visit_date = $dated->format('m/d/Y'); 
/*
There is a global setting for displaying dates...
If this form only uses visit_date for display purposes then use the global preference above instead.
*/
formHeader("Chart: ".$pat_data['fname']." ".$pat_data['lname']." ".$visit_date);

?>
<html><head>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>

<!-- Add Font stuff for the look and feel.  -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/font-awesome-4.2.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css">    

</head>

<body>

    <?php 
        /**  Time to decide what to display.
          *  Suggestions for this time:
          *  1. Dictation style report with printed data
          *  2. If drawing is all they want
          *  3. Legal document.
          *  4. Word processor to edit.  Stored as unique document.
          *  5. Create a new, additional report.
          */
        //  see save.php
        $side="OU";
        $zone = array("HPI","PMH","VISION","NEURO","EXT","ANTSEG","RETINA","IMPPLAN");
        //  for ($i = 0; $i < count($zone); ++$i) {
        //  show only 2 for now
        for ($i = 0; $i < '2'; ++$i) {
            $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter."/".$side."_".$zone[$i]."_VIEW.png";
            $sql = "SELECT * from documents where url='file://".$file_location."'";
            $doc = sqlQuery($sql);
            if (file_exists($file_location) && ($doc['id'] > '0')) {
                $filetoshow = $GLOBALS['web_root']."/controller.php?document&retrieve&patient_id=$pid&document_id=$doc[id]&as_file=false";
            } else {
                $filetoshow = "../../forms/".$form_folder."/images/".$side."_".$zone[$i]."_BASE.png?".rand();
            }
            
            /*$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/".$form_folder."/".$pid."/".$encounter."/".$side."_".$zone[$i]."_VIEW.png";
            if (file_exists($file_location)) {
                $filetoshow = $GLOBALS['web_root']."/sites/".$_SESSION['site_id']."/".$form_folder."/".$pid."/".$encounter."/".$side."_".$zone[$i]."_VIEW.png?".rand();
            } else {
                $filetoshow = "../../forms/".$form_folder."/images/".$side."_".$zone[$i]."_BASE.png?".rand();
            }*/
            ?>
            <div class='bordershadow' style='position:relative;float:left;width:310px;height:205px;'>
                <img src='<?php echo $filetoshow; ?>' width=300 heght=200>
                
            </div>

            <?php
        }
        exit;
    ?>
        <div class="bordershadow">
            <?php display_draw_section ("VISION",$encounter,$pid); ?>
        </div>
        <div class="bordershadow">
            <br />
            <?php display_draw_section ("NEURO",$encounter,$pid); ?>
        </div>
        <div class="bordershadow">
            <br />
            <?php display_draw_section ("EXT",$encounter,$pid); ?>
        </div>
        <div class="bordershadow">
            <br />
            <?php display_draw_section ("ANTSEG",$encounter,$pid); ?>
        </div>
        <div class="bordershadow">
            <br />
            <?php display_draw_section ("RETINA",$encounter,$pid); ?>
        </div>
        <div class="bordershadow">
            <br />
            <?php display_draw_section ("IMPPLAN",$encounter,$pid); ?>
        </div>
        
</body>
</html>

<?

exit;




/***************************************/
    $count = 0;
    $data = formFetch($table_name, $id);
   
    if ($data) {
 
        foreach($data as $key => $value) {
            $$key=$value;
        }
    }
    if ($target =="W") {
        //we are printing the current RX
       
?>
 <table id="SpectacleRx">
                                                <th colspan="9"><?=$fname?><?=$lname?></th>
                                                <tr style="font-style:bold;">
                                                    <td></td>
                                                    <td></td>
                                                    <td>sph</td>
                                                    <td>cyl</td>
                                                    <td>axis</td>
                                                    <td>Prism</td>
                                                    <td>Acuity</td>
                                                    <td rowspan="7" class="right">
                                                        <b style="font-weight:bold;text-decoration:none;">Rx Type</b><br />
                                                        <b id="SingleVision_span">Single<input type=radio value="0" id="RX1" name="RX" class="input-helper--radio input-helper--radio" check="checked" /></b><br />
                                                        <b id="Bifocal_span">Bifocal<input type=radio value="1" id="RX1" name="RX" /></b><br />
                                                        <b id="Trifocal_span" name="Trifocal_span">Trifocal
                                                            <input type=radio value="2" id="RX1" name="RX" /></b><br />
                                                        <b id="Progressive_span">Prog.<input type=radio value="3" id="RX1" name="RX" /></b><br />

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td rowspan="2">Distance</td>    
                                                    <td><b>OD</b></td>
                                                    <td><input type=text id="ODSph" name=="ODSph" /></td>
                                                    <td><input type=text id="ODCyl" name="ODCyl" /></td>
                                                    <td><input type=text id="ODAxis" name="ODAxis" /></td>
                                                    <td><input type=text id="ODPrism" name="ODPrism" /></td>
                                                    <td><input type=text id="ODVA" name="ODVA" /></td>
                                                </tr>
                                                <tr>
                                                    <td><b>OS</b></td>
                                                    <td><input type=text id="OSSph" name="OSSph" /></td>
                                                    <td><input type=text id="OSCyl" name="OSCyl" /></td>
                                                    <td><input type=text id="OSAxis" name="OSAxis" /></td>
                                                    <td><input type=text id="OSPrism" name="OSPrism" /></td>
                                                    <td><input type=text id="OSVA" name="OSVA" /></td>
                                                </tr>
                                                <tr class="NEAR">
                                                    <td rowspan=2><span style="text-decoration:none;">Mid/<br />Near</span></td>    
                                                    <td><b>OD</b></td>
                                                    <td class="Mid nodisplay"><input type=text id="ODADD1" name="ODADD1" value=""></td>
                                                    <td class="Add2"><input type=text id="ODADD2" name="ODADD2" value=""></td>
                                                    <td class="HIDECYL"><input type=text id="ODCYLNEAR" name="ODCYLNEAR" value=""></td>
                                                    <td><input type=text id="ODAXISNEAR" name="ODAXISNEAR" value=""></td>
                                                    <td><input type=text id="ODPRISMNEAR" name="ODPRISMNEAR" value=""></td>
                                                    <td><input type=text id="ODVANear" name="ODVANear" value=""></td>
                                                </tr>
                                                <tr class="NEAR">
                                                    <td><b>OS</b></td>
                                                    <td class="Mid nodisplay"><input type=text id="OSADD1" name="OSADD1" value=""></td>
                                                    <td class="Add2"><input type=text id="OSADD2" name="OSADD2" value=""></td>
                                                    <td class="HIDECYL"><input type=text id="OSCYLNEAR" name"OSCYLNEAR" value=""></td>
                                                    <td><input type=text id="OSAXISNEAR" name="OSAXISNEAR" value=""></td>
                                                    <td><input type=text id="OSPRISMNEAR" name="OSPRISMNEAR" value=""></td>
                                                    <td><input type=text id="OSVANear" name="OSVANear" value=""></td>

                                                </tr>
                                                <tr style="">
                                                    <td colspan="2" class="up" style="text-align:right;vertical-align:top;top:0px;"><b>Comments:</b>
                                                    </td>
                                                    <td colspan="5" class="up" style="text-align:left;vertical-align:middle;top:0px;">
                                                        <textarea style="idth:100%;height:2.1em;" id="COMMENTS" name="COMMENTS"></textarea>     
                                                    </td>
                                                    <td> 
                                                        <span class="ui-icon ui-icon-clock" >&nbsp; </span>
                                                        <span href="print.php?target=W" class="ui-icon ui-icon-cancel" onclick="indow.print(); return false;" style="display:inline-block"></span><span>Print</span> 
                                                    </td>
                                                </tr>
                                            </table>


<?
        }
}
/** CHANGE THIS, the name of the function is significant and  **
 **              must be changed to match the folder name     **/
function eye_mag_report( $pid, $encounter, $cols, $id) {
    
    /** CHANGE THIS - name of the database table associated with this form **/
    $table_name = "form_eye_mag";

    $count = 0;
    $data = formFetch($table_name, $id);
   
    if ($data) {
 
        print "<table><tr>";
       
        foreach($data as $key => $value) {
            if ($key == "id" || $key == "pid" || $key == "user" || 
                $key == "groupname" || $key == "authorized" || 
                $key == "activity" || $key == "date" || 
                $value == "" || $value == "0000-00-00 00:00:00" || 
                $value == "n") 
            {
                // skip certain fields and blank data
            continue;
            }

            $key=ucwords(str_replace("_"," ",$key));
            print("<tr>\n");  
            print("<tr>\n");  
            print "<td><span class=bold>$key: </span><span class=text>$value</span></td>";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }
    print "</tr></table>";
}

?> 
