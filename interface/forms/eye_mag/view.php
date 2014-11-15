<?php
/** 
* forms/eye_mag/view.php 
* 
* Central view for the eye_mag form.  Here is where all new data is entered
* New forms are created via new.php and then this script is displayed.
* Edit requsts come here too...
* 
* Copyright (C) 2010-14 Raymond Magauran <magauran@MedFetch.com> 
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

include_once("../../globals.php");
include_once("$srcdir/api.inc");

$form_name = "eye_mag";
$form_folder = "eye_mag";
formHeader("View Record from ".$form_name);

include_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");


$escapedGet = array_map('mysql_real_escape_string', $_REQUEST); @extract($escapedGet);
$escapedGet = array_map('mysql_real_escape_string', $_SESSION); @extract($escapedGet);

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

// Let's find the users preferences for this user (and if not the default where a fresh install begins from, or someone else's) Norm_eye_mag world
$query  = "SELECT * FROM dbSelectFindings where PEZONE='PREFS' AND id='".$_SESSION['authUserID']."' ORDER BY ZONE_ORDER,ordering";
$result = sqlStatement($query);
while ($prefs= mysql_fetch_array($result))   {    
    @extract($prefs);    
    $$LOCATION = $VALUE; 
}
// OK we have the prefs now...

// get pat_data and user_data
$query = "SELECT * FROM patient_data where pid='$pid'";
$pat_data =  sqlQuery($query);
@extract($pat_data);

$query = "SELECT * FROM users where id = '".$_SESSION['authUserID']."'";
$prov_data =  sqlQuery($query);
$providerID = $prov_data['fname']." ".$prov_data['lname'];

/*
// OK let's get the data from this eye_mag visit
$query = "SELECT fm.* FROM form_eye_mag as fm, forms where forms.form_id = fm.id and forms.encounter='".$encounter."'";
$data =  sqlQuery($query);
@extract($data);
//echo "<PRE>";
//echo "QUERY = ".$query;
//var_dump($data);
$form_id = $id;

//$objIns = formFetch("form_eye_mag", $_GET['id']);  //#Use the formFetch function from api.inc to get values for existing form. 
//@extract($objIns);
$query = "select form_encounter.*,forms.*, form_eye_mag.*,form_encounter.date as encounter_date from form_encounter
                    join forms on form_encounter.pid=forms.pid 
                    join form_eye_mag on forms.form_id=form_eye_mag.id 
                    where 
                    form_encounter.pid = '$pid' and 
                    form_encounter.encounter=forms.encounter and 
                    forms.form_name = 'eye_mag' and 
                    form_eye_mag.id='$_GET[id]' and 
                    forms.deleted ='0' ORDER BY form_encounter.date DESC";
             //      
*/
//I have a problem with the above getting things out of the DB but this works:
$query="select form_encounter.date as encounter_date,form_eye_mag.* from form_eye_mag ,forms,form_encounter 
                    where 
                    form_encounter.encounter ='$encounter' and 
                    form_encounter.encounter = forms.encounter and 
                    form_eye_mag.id=forms.form_id and
                    forms.pid ='".$pid."' ";        
$objQuery =sqlQuery($query);
@extract($objQuery);

$dated = new DateTime($encounter_date);
$visit_date = $dated->format('m/d/Y'); 
/*
Is there a global setting for displaying dates?
If this form only uses visit_date for display purposes then use the global preference instead.
*/

?><html><head>
<?php 
html_header_show();  //why use this at all?
?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<!-- Add jQuery library -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>

<!-- Add HTML5 Draw program (for now not working) library -->
<script type="text/javascript" src="../../forms/<?php echo $form_folder; ?>/js/sketch.min.js"></script>

<!-- Add eye_mag js library -->
<script type="text/javascript" src="../../forms/<?php echo $form_folder; ?>/js/my_js_base.js"></script>

<? 
/*
I USED THIS CODE SOMEWHERE BUT I FORGET WHERE, PERHAPS IN THE SPECTACLERX.PHP.  NOT SURE IF IT IS NEEDED HERE SO PUT IT ASIDE FOR NOW
<script type="text/javascript" src="https://raw.githubusercontent.com/erikzaadi/jQueryPlugins/master/jQuery.printElement/jquery.printElement.min.js"></script>
*/
?>
<!-- Add Font stuff for the look and feel.  What a difference these make! -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/css/font-awesome-4.2.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css">    

<? /*
//not using this yet but it will be when incorporating the HPI/PMH formdata from other modules here
<!-- Add mousewheel plugin (this is optional) not sure what it does though... -->
<script type="text/javascript" src="/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>
<!-- Add fancyBox ( -->
<link rel="stylesheet" href="/fancybox/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
<script type="text/javascript" src="/fancybox/source/jquery.fancybox.pack.js?v=2.1.5"></script>

<!-- Optionally add helpers - button, thumbnail and/or media -->
<link rel="stylesheet" href="/fancybox/source/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen" />
<script type="text/javascript" src="/fancybox/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
<script type="text/javascript" src="/fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>

<link rel="stylesheet" href="/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen" />
<script type="text/javascript" src="/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
*/
?>

</head>

<body>
    <form method="post" action="<?php echo $rootdir;?>/forms/<?php echo $form_folder; ?>/save.php?mode=new" id="eye_mag" class="eye_mag pure-form" name="eye_mag">
        <!-- start container for the main body of the form -->
        <div class="body_top" id="form_container" name="form_container">
                <input type="hidden" name="id" id="id" value="<?=$id?>">
                <input type="hidden" name="encounter" id="encounter" value="<?=$encounter?>">
                <input type="hidden" name="PREFS_VA" id="PREFS_VA" value="<?=$VA?>">
                <input type="hidden" name="PREFS_W" id="PREFS_W" value="<?=$W?>">
                <input type="hidden" name="PREFS_MR" id="PREFS_MR" value="<?=$MR?>">
                <input type="hidden" name="PREFS_CR" id="PREFS_CR" value="<?=$CR?>">
                <input type="hidden" name="PREFS_CTL" id="PREFS_CTL" value="<?=$CTL?>">
                <input type="hidden" name="PREFS_ADDITIONAL" id="PREFS_ADDITIONAL" value="<?=$ADDITIONAL?>">
                <input type="hidden" name="PREFS_CLINICAL" id="PREFS_CLINICAL" value="<?=$CLINICAL?>">
                <input type="hidden" name="PREFS_EXAM" id="PREFS_EXAM" value="<?=$EXAM?>">
                <input type="hidden" name="PREFS_CYL" id="PREFS_CYL" value="<?=$CYLINDER?>">
                <input type="hidden" name="PREFS_EXT_VIEW"  id="PREFS_EXT_VIEW" value="<?=$EXT_VIEW?>">
                <input type="hidden" name="PREFS_ANTSEG_VIEW"  id="PREFS_ANTSEG_VIEW" value="<?=$ANTSEG_VIEW?>">
                <input type="hidden" name="PREFS_RETINA_VIEW"  id="PREFS_RETINA_VIEW" value="<?=$RETINA_VIEW?>">
                <input type="hidden" name="PREFS_NEURO_VIEW"  id="PREFS_NEURO_VIEW" value="<?=$NEURO_VIEW?>">
            <!-- start of general box -->
            <div id="general" style="position:relative; padding: 0.04in;">
                <div ID="Lyr1" STYLE="position: relative; width: auto;border: 1.00pt solid #c0c0c0; padding: 0.04in; text-align:center;">
                    <? 

                     /* 
                     There is no need for a Save Button as it is always saved via ajax and of leaving the form...  
                     Or is there a good reason to include it?
                            <span class="title" style="text-align:left;padding-right:0.5in;">
                                <!-- Save/Cancel buttons -->
                                <input type="button" class="save" value="<?php xl('Save NEW','e'); ?>"> &nbsp; 
                                <input type="button" class="dontsave" value="<?php xl('Don\'t Save','e'); ?>">
                            </span> 
                    */
                     ?>
                    
                    <span class="title" id="pat_name" style="text-align:center;padding-right:0.2in;"><?php echo $fname." ".$lname." (".$pid.")"; ?>
                    </span>

                    <span style="text-align:right;"><b><?php xl('EXAM DATE','e'); ?>:</b> &nbsp;<? echo $visit_date; ?>&nbsp;</span>
                </div>
            </div>          
            <!-- //end of the general BOX -->
            <a id="construction" name="construction" style="font-size:0.6em;">Toggle construction zones</a>
            <div id="accordion" class="text_clinical" style="position:absolute;">
               <div class="CONSTRUCTION_ZONE nodisplay" name="CONSTRUCTION_1" id="CONSTRUCTION_1">
                    <!-- OK this is the CC/HPI/PMFSH area that can be developed in this format or the other modules drawn in to complete the intake?  -->
                    <div id="Lyr2" class="borderShadow" style="position: relative;width:400px;float:left;" >
                        <b><?php xl('Chief Complaint','e'); ?>: </b>
                        <input name="CC" id="CC" size="54" value="<?=$CC?>" tabindex="4" type="text">
                        <br>
                        <br><b><?php xl('History of Present Illness','e'); ?> </b><font size="1">(<?php xl('please include mechanism of injury','e'); ?>):</font><br />
                        <textarea style="left: 0.45in; width: 80%; height: 0.79in; overflow:visible; " name="HPI" rows="3" cols="113" id="HPI" tabindex="5"><?=$HPI?></textarea>
                        <br />
                        QUALITY     TIMING  DURATION    CONTEXT     SEVERITY    MODIFY  ASSOCIATED  LOCATION
                    </div>
                
                    
                    <div class="borderShadow" style="height:1in;width:20%;border:1pt solid black;float:left;">PMH:<br />
                        <textarea rows=4 style="height:0.7in;width:90%;border:1pt solid black;" name="PMH" id="PMH"></textarea></div>
                    <div class="borderShadow" style="height:1in;width:20%;border:1pt solid black;float:left;">PSurgHx:<br />
                        <textarea rows=4 style="height:0.7in;width:90%;border:1pt solid black;" name="PMH" id="PMH"></textarea></div>
                    <div class="borderShadow" style="height:1in;width:20%;border:1pt solid black;float:left;">FH:<br />
                        <textarea rows=4 style="height:0.7in;width:90%;border:1pt solid black;" name="PMH" id="PMH"></textarea></div>
                    <div class="borderShadow" style="height:1in;width:20%;border:1pt solid black;float:left;">Meds:<br />
                        <textarea rows=4 style="height:0.7in;width:90%;border:1pt solid black;" name="PMH" id="PMH"></textarea></div>
                    <div class="borderShadow" style="height:1in;width:20%;border:1pt solid black;float:left;">SocHx:<br />
                        <textarea rows=4 style="height:0.7in;width:90%;border:1pt solid black;" name="PMH" id="PMH"></textarea></div>
                    <div class="borderShadow" style="height:1in;width:20%;border:1pt solid black;float:left;">Allergies:<br />
                        <textarea rows=4 style="height:0.7in;width:90%;border:1pt solid black;" name="PMH" id="PMH"></textarea></div>
                    <div class="borderShadow" style="clear:both;height:1in;width:90%;border:1pt solid black;">ROS:<br />
                        <textarea name="PMH" id="PMH"></textarea></div>
                    <div id="Lyr2.2" style="clear:both;border:1pt solid black;">
                    </div>
               </div>
                <div class="CONSTRUCTION_ZONE nodisplay" name="CONSTRUCTION_2" id="CONSTRUCTION_2" style="position:relative;border:1pt black solid;">
                    <br />
               
                        <span class="text_clinical">
                            
                                <b><?php xl('Mood/Affect','e'); ?>:</b>
                                <label for="alert" class="input-helper input-helper--checkbox"><?php xl('Alert','e'); ?></label>
                                <input id="alert" name="alert" type="checkbox"  <? if ($alert ==="1") { echo "checked='checked'"; } ?>">
                                <label for="oriented" class="input-helper input-helper--checkbox"><?php xl('Oriented','e'); ?></label>
                                <input id="oriented" name="oriented" type="checkbox" value="<?=$oriented?>" <? if ($oriented ==="1") { echo "checked='checked'"; } ?>">
                                <label for="confused" class="input-helper input-helper--checkbox"><?php xl('Confused','e'); ?></label>
                                <input id="confused" name="confused" type="checkbox" value="off" <? 
                                if ($confused =="1") { 
                                    echo "checked='checked'"; 
                                } 
                                ?>
                                >
                             
                        </span><br />
                </div>
                    <br />
                
                <!-- start of the clinical BOX -->
                <div>    
                    <div id="LayerClinical" class="section" style="min-height:1.3in;width:100%;vertical-align:text-top;position:relative;text-align:left;">

                        <!-- start of the VISION BOX -->                  
                        <div id="LayerVision" class="vitals" style="width: 1.75in; min-height: 1.05in;padding: 0.02in; border: 1.00pt solid #000000;">
                            <div id="Lyr3.0" class="top_left ">
                                    <th class="text_clinical"><b id="vision_tab"><?php xl('Vision','e'); ?>:</b>
                                    </th>
                            </div>
                             <? 
                                                    //if the prefs show a field, ie visible, the highlight the zone.
                             if ($W == '1') $button_W = "buttonRefraction_selected";
                             if ($MR == '1')  $button_MR = "buttonRefraction_selected";
                             if ($CR == '1')  $button_CR = "buttonRefraction_selected";
                             if ($CTL == '1')  $button_CTL = "buttonRefraction_selected";
                             if ($ADDITIONAL == '1')  $button_ADDITIONAL = "buttonRefraction_selected";

                             ?>
                             <div class="top_right">
                                <span id="tabs">  
                                    <ul>
                                        <li id="LayerVision_W_lightswitch" class="<?=$button_W?>" value="Current">W</li> | 
                                        <li  id="LayerVision_MR_lightswitch" class="<?=$button_MR?>" value="Auto">MR</li> | 
                                        <li  id="LayerVision_CR_lightswitch" class="<?=$button_CR?>" value="Cyclo">CR</li> | 
                                        <li  id="LayerVision_CTL_lightswitch" class="<?=$button_CTL?>" value="Contact Lens">CTL</li> | 
                                        <li  id="LayerVision_ADDITIONAL_lightswitch" class="<?=$button_ADDITIONAL?>" value="Additional"><?php xl('More','e'); ?></li>
                                    </ul>
                                </span>
                            </div>    

                            <div id="Lyr3.1" style="position: absolute; top: 0.30in; left: 0.1in; width: 0.4in;height: 0.3in; border: none; padding: 0in; " dir="LTR">
                                <font style="font-face:'San Serif'; font-size:3.5em;">V</font>
                                <font style="font-face:arial; font-size:0.9em;"></font>

                            </div>
                            <div id="Visions_A" name="Visions_A" class="" style="position: absolute; top: 0.35in; text-align:right;right:0.1in; height: 0.72in;  padding: 0in;" >
                                <b>OD </b>
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.19in; font-family: 'Times New Roman';" tabindex="6" size="6" id="SCODVA" name="SCODVA" value="<?=$SCODVA?>">
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.19in; font-family: 'Times New Roman';" tabindex="6" size="6"  id="WODVA_copy" name="WODVA_copy" value="<?=$WODVA?>">
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.19in; font-family: 'Times New Roman';" tabindex="6" size="6"  id="PHODVA_copy" name="PHODVA_copy" value="<?=$PHODVA?>">
                                <br />                            
                                <b>OS </b>
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.18in; font-family: 'Times New Roman'" tabindex="7" size="6"  id="SCOSVA" name="SCOSVA" value="<?=$SCOSVA?>">
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.18in; font-family: 'Times New Roman'" tabindex="7" size="6" id="WOSVA_copy" name="WOSVA_copy" value="<?=$WOSVA?>">
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.18in; font-family: 'Times New Roman'" tabindex="7" size="6" id="PHOSVA_copy" name="PHOSVA_copy" value="<?=$PHOSVA?>">
                                <br />
                                <span id="more_visions_1" name="more_visions_1" style="position: absolute;top:0.44in;left:-0.37in;font-size: 0.9em;pading-right:4px;"><b><?php xl('Acuity','e'); ?></b> </span>
                                <span style="position: absolute;top:0.44in;left:0.24in;font-size: 0.8em;"><b><?php xl('SC','e'); ?></b></span>
                                <span style="position: absolute;top:0.44in;left:0.59in;font-size: 0.8em;"><b><?php xl('CC','e'); ?></b></span>
                                <span style="position: absolute;top:0.44in;left:0.91in;font-size: 0.8em;"><b><?php xl('PH','e'); ?></b></span><br /><br /><br />
                            </div>
                            <div id="Visions_B" name="Visions_B" class="nodisplay" style="position: absolute; top: 0.35in; text-align:right;right:0.1in; height: 0.72in;  padding: 0in;" >
                                <b><?php xl('OD','e'); ?> </b>
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.19in; font-family: 'Times New Roman';" tabindex="6" size="6" id="ARODVA_copy" name="ARODVA_copy" value="<?=$ARODVA?>">
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.19in; font-family: 'Times New Roman';" tabindex="6" size="6" id="MRODVA_copy" name="MRODVA_copy" value="<?=$MRODVA?>">
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.19in; font-family: 'Times New Roman';" tabindex="6" size="6" id="CRODVA_copy" name="CRODVA_copy" value="<?=$CRODVA?>">
                                <br />                            
                                <b><?php xl('OS','e'); ?> </b>
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.18in; font-family: 'Times New Roman'" tabindex="7" size="6" id="AROSVA_copy" name="AROSVA_copy" value="<?=$AROSVA?>">
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.18in; font-family: 'Times New Roman'" tabindex="7" size="6" id="MROSVA_copy" name="MROSVA_copy" value="<?=$MROSVA?>">
                                <input type="TEXT" style="left: 0.5in; width: 0.3in; height: 0.18in; font-family: 'Times New Roman'" tabindex="7" size="6" id="CROSVA_copy" name="CROSVA_copy" value="<?=$CROSVA?>">
                                <br />
                                <span id="more_visions_2" name="more_visions_2" style="position: absolute;top:0.44in;left:-0.37in;font-size: 0.9em;pading-right:4px;"><b><?php xl('Acuity','e'); ?></b> </span>
                                <span style="position: absolute;top:0.44in;left:0.24in;font-size: 0.8em;"><b><?php xl('AR','e'); ?></b></span>
                                <span style="position: absolute;top:0.44in;left:0.59in;font-size: 0.8em;"><b><?php xl('MR','e'); ?></b></span>
                                <span style="position: absolute;top:0.44in;left:0.91in;font-size: 0.8em;"><b><?php xl('CR','e'); ?></b></span>
                            </div>       
                        </div>
                        <!-- end of the VISION BOX -->

                        <!-- START OF THE PRESSURE BOX -->
                        <div id="LayerTension" class="vitals" style="width: 1.5in; height: 1.05in;padding: 0.02in; border: 1.00pt solid #000000;">
                            <span title="This will display a graph of IOPs over time in a pop-up window" class="closeButton fa  fa-line-chart" id="IOP_Graph" name="IOP_Graph"></span>
                            <div id="Lyr4.0" style="position:absolute; left:0.05in; width: 1.4in; top:0.0in; padding: 0in; " dir="LTR">
                                <span class="top_left">
                                    <b id="tension_tab"><?php xl('Tension','e'); ?>:</b> 
                                    <div style="position:absolute;background-color:#ffffff;text-align:left;width:50px; top:0.7in;font-size:0.9em;left:0.02in;">
                                        <? 
                                        if ($IOPTIME == '') {
                                            $IOPTIME =  date('g:i a'); 
                                        }
                                        ?>
                                        <input type="text" name="IOPTIME" id="IOPTIME" style="background-color:#ffffff;font-size:0.7em;border:none;" value="<?=$IOPTIME?>">

                                    </div>    
                                </span>
                            </div>
                            <div id="Lyr4.1" style="position: absolute; top: 0.3in; left: 0.12in; width: 0.37in;height: 0.45in; border: none; padding: 0in;">
                                <font style="font-face:arial; font-size:3.5em;">T</font>
                                <font style="font-face:arial; font-size: 0.9em;"></font>
                            </div>
                            <div id="Lyr4.2" style="position: absolute; top: 0.35in; text-align:right;right:0.1in; height: 0.72in;  padding: 0in; border: 1pt black;">
                                <b><?php xl('OD','e'); ?></b>
                                <input type="text" style="left: 0.5in; width: 0.2in; height: 0.18in; font-family: 'Times New Roman';" tabindex="6" size="6" name="ODIOPAP" value="<?=$ODIOPAP?>">
                                <input type="text" style="left: 0.5in; width: 0.2in; height: 0.18in; font-family: 'Times New Roman';" tabindex="6" size="6" name="ODIOPTPN" value="<?=$ODIOPTPN?>">
                                <input type="text" style="left: 0.5in; width: 0.2in; height: 0.18in; font-family: 'Times New Roman';" tabindex="6" size="6" name="ODIOPFTN" value="<?=$ODIOPTPN?>">
                                <br />
                                <b><?php xl('OS','e'); ?> </b>
                                <input type="text" style="left: 0.5in; width: 0.2in; height: 0.18in; font-family: 'Times New Roman';" tabindex="7" size="6" name="OSIOPAP" value="<?=$OSIOPAP?>">
                                <input type="text" style="left: 0.5in; width: 0.2in; height: 0.18in; font-family: 'Times New Roman';" tabindex="7" size="6" name="OSIOPTPN" value="<?=$OSIOPTPN?>">
                                <input type="text" style="left: 0.5in; width: 0.2in; height: 0.18in; font-family: 'Times New Roman';" tabindex="7" size="6" name="OSIOPFTN" value="<?=$OSIOPFTN?>">
                                <br /><br />
                                <span style="position: absolute;top:0.44in;left:0.22in;font-size: 0.8em;"><b><?php xl('AP','e'); ?></b></span>
                                <span style="position: absolute;top:0.44in;left:0.47in;font-size: 0.8em;"><b><?php xl('TP','e'); ?></b></span>
                                <span style="position: absolute;top:0.44in;left:0.7in;font-size: 0.8em;"><b><?php xl('FT','e'); ?></b></span>
                            </div>
                        </div>
                        <!-- END OF THE PRESSURE BOX -->

                        <!-- start of the Amsler box -->
                        <div id="LayerAmsler" class="vitals" style="width: 1.5in; height: 1.05in;padding: 0.02in; border: 1.00pt solid #000000;">
                            <div  id="Lyr5.0" style="position:absolute;  left:0.05in; width: 1.4in; top:0in; padding: 0in;">
                                <span class="top_left">
                                    <b><?php xl('Amsler','e'); ?>:</b>
                                </span>
                            </div>
                            <? 
                                if (!$AMSLEROD) $AMSLEROD= "0";
                                if (!$AMSLEROS) $AMSLEROS= "0";
                                if ($AMSLEROD || $AMSLEROS) {
                                    $checked = 'value="0"'; 
                                } else {
                                    $checked = 'value="1" checked';
                                }
                                
                            ?>
                            <input type="hidden" id="AMSLEROD" name="AMSLEROD" value='<?=$AMSLEROD?>'>
                            <input type="hidden" id="AMSLEROS" name="AMSLEROS" value='<?=$AMSLEROS?>'>
                            
                            <div style="position:absolute;text-align:right; top:0.03in;font-size:0.8em;right:0.1in;">
                                <label for="Amsler-Normal" class="input-helper input-helper--checkbox"><?php xl('Normal','e'); ?></label>
                                <input id="Amsler-Normal" type="checkbox" <?=$checked?>>
                            </div>     
                            <div id="Lyr5.1" style="position: absolute; top: 0.2in; left: 0.12in; display:inline-block;border: none; padding: 0.0in;">
                                <table cellpadding=0 cellspacing=0 style="padding:0px;margin:auto;width:90%;align:auto;font-size:0.8em;text-align:center;">
                                    <tr>
                                        <td colspan=3 style="text-align:center;"><b><?php xl('OD','e'); ?></b>
                                        </td>
                                        <td></td>
                                        <td colspan=3 style="text-align:center;"><b><?php xl('OS','e'); ?></b>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan=3>
                                            <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?=$AMSLEROD?>.jpg" id="AmslerOD" style="padding:0.05in;height:0.5in;width:0.5in;" /></td>
                                        <td></td>
                                        <td colspan=3>
                                            <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?=$AMSLEROS?>.jpg" id="AmslerOS" style="padding:0.05in;height:0.5in;width:0.5in;" /></td>
                                        </tr>
                                        <tr>
                                             <td colspan=3 style="text-align:center;">
                                                <div class="AmslerValueOD" style="font-size:0.8em;text-decoration:italics;">
                                                    <span id="AmslerODvalue"><?=$AMSLEROD?></span>/5
                                                </div>
                                            </td>
                                            <td></td>
                                            <td colspan=3 style="text-align:center;">
                                                <div class="AmslerValueOS" style="font-size:0.8em;text-decoration:italics;">
                                                    <span id="AmslerOSvalue"><?=$AMSLEROS?></span>/5
                                                </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <!-- end of the Amsler box -->

                        <!-- start of the Fields box -->
                        <div id="LayerFields" class="vitals" style="width: 1.42in;height:1.05in;padding: 0.02in; border: 1.00pt solid #000000;">
                            <div  id="Lyr6.0" style="position:absolute;  left:0.05in; width: 1.4in; top:0.0in; padding: 2px; " dir="LTR">
                                <span class="top_left">
                                    <b id="fields"><?php xl('Fields','e'); ?>:</b>
                                           
                                </span>
                            </div> 
                                <? 
                                        //if the VF zone is checked, display it
                                    //if ODVF1 = 1 (true boolean) the value="0" checked="true"
                                    for ($z=1; $z <5; $z++) {
                                        $ODzone = "ODVF".$z;
                                       // echo $ODzone ." is ".$$ODzone."<br />";
                                        if ($$ODzone =='1') {
                                            $ODVF[$z] = 'checked value="true"';
                                            $bad++;
                                        } else {
                                            $ODVF[$z] = 'value="false"';
                                        }
                                        $OSzone = "OSVF".$z;
                                        if ($$OSzone =="1") {
                                            $OSVF[$z] = 'checked value="1"';
                                            $bad++;
                                        } else {
                                            $OSVF[$z] = 'value="0"';
                                        }
                                    }
                                    if (!$bad)  $VFFTCF = "checked";
                                ?>
                             <div style="position:relative;text-align:right; top:0.03in;font-size:0.8em;right:0.1in;">
                                        <label for="FieldsNormal" class="input-helper input-helper--checkbox"><?php xl('FTCF','e'); ?></label>
                                        <input id="FieldsNormal" type="checkbox" value="1" <?=$VFFTCF?>>
                            </div>   
                            <div id="Lyr5.1" style="position: relative; top: 0.08in; left: 0.0in; border: none; padding: 0.05in; background: white">
                                <table cellpadding='1' cellspacing="1" style="font-size: 0.8em;text-align:center;padding:0px;margin:auto;"> 
                                    <tr>    
                                        <td style="width:0.4in;" colspan="2"><b><?php xl('OD','e'); ?></b><br /></td>

                                        <td style="width:0.05in;"> </td>
                                        <td style="width:0.4in;" colspan="2"><b><?php xl('OS','e'); ?></b></td>
                                    </tr> 
                                    <tr>    
                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                            <input name="ODVF1" id="ODVF1" type="checkbox" <?=$ODVF['1']?> class="hidden"> 
                                            <label for="ODVF1" class="input-helper input-helper--checkbox boxed"></label>
                                        </td>
                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;">
                                            <input name="ODVF2" id="ODVF2" type="checkbox" <?=$ODVF['2']?> class="hidden"> 
                                            <label for="ODVF2" class="input-helper input-helper--checkbox boxed"></label>
                                        </td>
                                        <td></td>
                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                            <input name="OSVF1" id="OSVF1" type="checkbox" <?=$OSVF['1']?> class="hidden" >
                                            <label for="OSVF1" class="input-helper input-helper--checkbox boxed"></label>
                                        </td>
                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;">
                                            <input name="OSVF2" id="OSVF2" type="checkbox" <?=$OSVF['2']?> class="hidden">                                                         
                                            <label for="OSVF2" class="input-helper input-helper--checkbox boxed"> </label>
                                        </td>
                                    </tr>       
                                    <tr>    
                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                            <input name="ODVF3" id="ODVF3" type="checkbox"  class="hidden" <?=$ODVF['3']?>> 
                                            <label for="ODVF3" class="input-helper input-helper--checkbox boxed"></label>
                                        </td>
                                        <td style="border-left:1pt solid black;border-top:1pt solid black;">
                                            <input  name="ODVF4" id="ODVF4" type="checkbox"  class="hidden" <?=$ODVF['4']?>>
                                            <label for="ODVF4" class="input-helper input-helper--checkbox boxed"></label>  
                                        </td>
                                        <td></td>
                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                            <input name="OSVF3" id="OSVF3" type="checkbox"  class="hidden" <?=$OSVF['3']?>>
                                            <label for="OSVF3" class="input-helper input-helper--checkbox boxed"></label>
                                        </td>
                                        <td style="border-left:1pt solid black;border-top:1pt solid black;">
                                            <input name="OSVF4" id="OSVF4" type="checkbox"  class="hidden" <?=$OSVF['4']?>>
                                            <label for="OSVF4" class="input-helper input-helper--checkbox boxed"></label>
                                        </td>                    
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <!-- end of the Fields box -->

                        <!-- start of the Pupils box -->
                        <div id="LayerPupils" class="vitals" style="width: 1.85in; height: 1.05in; padding: 0.02in; border: 1.00pt solid #000000; ">  
                            <span class="top_left"><b id="pupils"><?php xl('Pupils','e'); ?>:</b> </span>
                            <div style="position:absolute;text-align:right; top:0.03in;font-size:0.8em;right:0.1in;">
                                        <label for="Pupil_normal" class="input-helper input-helper--checkbox"><?php xl('Normal','e'); ?></label>
                                        <input id="Pupil_normal" type="checkbox" value="1" checked="checked">
                            </div>
                            <div id="Lyr7.0" style="position: absolute; top: 0.3in; left: 0.1in; border: none;padding: auto;">
                                <table cellpadding=2 cellspacing=0 style="font-size: 0.9em;"> 
                                    <tr>    
                                        <th style="width:0.1in;"> 
                                        </th>
                                        <th style="width:0.7in;padding: 0;"><?php xl('size','e'); ?> (<?php xl('mm','e'); ?>)
                                        </th>
                                        <th style="width:0.2in;padding: 0;"><?php xl('react','e'); ?> 
                                        </th>
                                        <th style="width:0.2in;padding: 0;"><?php xl('APD','e'); ?>
                                        </th>
                                    </tr>
                                    <tr>    
                                        <td><b><?php xl('OD','e'); ?></b>
                                        </td>
                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;">
                                            <input type="text" size=1 id ="ODPUPILSIZE1" name="ODPUPILSIZE1" style="width:0.25in;height:0.2in;" value="<?=$ODPUPILSIZE1?>"><font>&#8594;</font><input type="text" id ="ODPUPILSIZE2" size="1" name="ODPUPILSIZE2" style="width:0.25in;height:0.2in;" value="<?=$ODPUPILSIZE2?>">
                                        </td>
                                        <td style="border-left:1pt solid black;border-right:1pt solid black;border-bottom:1pt solid black;">
                                            <input type="text" style="width:0.3in;height:0.2in;" name='ODPUPILREACTIVITY' id='ODPUPILREACTIVITY' value='<?=$ODPUPILREACTIVITY?>'>
                                        </td>
                                        <td style="border-bottom:1pt solid black;">
                                            <input type="text" style="width:0.20in;height:0.2in;" name="ODAPD" id='ODAPD' value='<?=$ODAPD?>'>
                                        </td>
                                    </tr>
                                    <tr>    
                                        <td><b><?php xl('OS','e'); ?></b>
                                        </td>
                                        <td style="border-right:1pt solid black;border-top:1pt solid black;">
                                            <input type="text" size=1 name='OSPUPILSIZE1' id='OSPUPILSIZE1' style="width:0.25in;height:0.2in;" value="<?=$OSPUPILSIZE1?>"><font>&#8594;</font><input type="text" size="1" name="OSPUPILSIZE2" id="OSPUPILSIZE2" style="width:0.25in;height:0.2in;" value="<?=$OSPUPILSIZE2?>">
                                        </td>
                                        <td style="border-left:1pt solid black;border-right:1pt solid black;border-top:1pt solid black;">
                                            <input type=text style="width:0.3in;height:0.2in;" name='OSPUPILREACTIVITY' id='OSPUPILREACTIVITY' value="<?=$OSPUPILREACTIVITY?>">
                                        </td>
                                        <td style="border-top:1pt solid black;">
                                            <input type="text" style="width:0.20in;height:0.2in;" name="OSAPD" id="OSAPD" value='<?=$OSAPD?>'>
                                        </td>
                                    </tr>
                                </table>
                            </div>  
                        </div>
                        <!-- end of the Pupils box -->
                        <!-- start of slide down pupils_panel --> 
                        <? ($DIMODPUPILSIZE != '') ? ($display_dim_pupils_panel = "display") : ($display_dim_pupils_panel = "nodisplay"); ?>
                        <div id="dim_pupils_panel" class="vitals <?=$display_dim_pupils_panel?>" style="position:relative;float:left;height: 1.05in; width:2.3in;padding: 0.02in; border: 1.00pt solid #000000; ">                     
                            <span class="top_left"><b id="pupils_DIM" style="width:100px;"><?php xl('Pupils','e') ?>: <?php xl('Dim','e'); ?></b> </span>
                            <div id="Lyr7.1" style="position: absolute; top: 0.3in; left: 0.1in; border: none;padding: auto;">
                                <table cellpadding="2" cellpadding="0" style="font-size: 0.9em;"> 
                                    <tr>    
                                        <th></th>
                                        <th style="width:0.7in;padding: 0;"><?php xl('size','e'); ?> (<?php xl('mm','e'); ?>)
                                        </th>
                                    </tr>
                                    <tr>    
                                        <td><b><?php xl('OD','e'); ?></b>
                                        </td>
                                        <td style="border-bottom:1pt solid black;">
                                            <input type="text" size=1 id ="DIMODPUPILSIZE1" name="DIMODPUPILSIZE1" style="width:0.25in;height:0.2in;" value='<?=$DIMODPUPILSIZE1?>'><font style="font-size:1.0em;">&#8594;</font><input type="text" id ="DIMODPUPILSIZE2" size=1 name="DIMODPUPILSIZE2" style="width:0.25in;height:0.2in;" value='<?=$DIMODPUPILSIZE2?>'>
                                        </td>
                                    </tr>
                                    <tr>    
                                        <td ><b><?php xl('OS','e'); ?></b>
                                        </td>
                                        <td style="border-top:1pt solid black;">
                                            <input type="text" size=1 name="DIMOSPUPILSIZE1" id="DIMOSPUPILSIZE1" style="width:0.25in;height:0.2in;" value="<?=$DIMOSPUPILSIZE1?>"><font style="font-size:1.0em;">&#8594;</font><input type='text' size=1 name='DIMOSPUPILSIZE2' id='DIMOSPUPILSIZE2' style="width:0.25in;height:0.2in;" value='<?=$DIMOSPUPILSIZE2?>'>
                                        </td>
                                    </tr>
                                </table>
                            </div>   
                            <div style="position:absolute;  top: 0.2in; left: 1.1in; border: none;padding: auto;">
                                <b><?php xl('Comments','e'); ?>:</b><br />
                                <textarea style="height:0.60in;width:95px;font-size:0.8em;" id="PUPIL_COMMENTS" name="PUPIL_COMMENTS"><?=$PUPIL_COMMENTS?></textarea>
                            </div>
                        </div> 
                        <!-- end of slide down pupils_panel --> 
                    </div>
                </div>
                <!-- end of the CLINICAL BOX -->
            
                <!-- start of the refraction box -->
                <div style="position:relative;text-align:center;">
                    <div id="LayerVision2" style="text-align:center;" class="section" >
                        <table id="refraction_width" name="refraction_width" style="text-align:center;margin: 0 0;">
                            <tr>
                                <td style="text-align:center;">
                                    <? ($W ==1) ? ($display_W = "display") : ($display_W = "nodisplay"); ?>
                                    <div id="LayerVision_W" class="refraction borderShadow <? echo $display_W; ?>">
                                        <span class="closeButton fa fa-close" id="Close_W" name="Close_W"></span>
                                        <a class="closeButton2 fa fa-print" href="../../forms/<?php echo $form_folder; ?>/SpectacleRx.php?target=W&id=<?=$pid?>"></a>
                                        <table id="wearing" >
                                            <tr>
                                                <th colspan="9" id="wearing_title"><?php xl('Current Glasses','e'); ?>
                                                    
                                                </th>
                                            </tr>
                                            <tr style="font-weight:400;">
                                                <td ></td>
                                                <td></td>
                                                <td><?php xl('Sph','e'); ?></td>
                                                <td><?php xl('Cyl','e'); ?></td>
                                                <td><?php xl('Axis','e'); ?></td>
                                                <td><?php xl('Prism','e'); ?></td>
                                                <td><?php xl('Acuity','e'); ?></td>
                                                <td rowspan="7" class="right" style="width:150px;padding:10 0 10 0;">
                                                    <b style="font-weight:600;text-decoration:underline;">Rx Type</b><br />
                                                    <span style="padding:10 auto;" id="SingleVision_span">Single <input type=radio value="0" id="RX1" name="RX" class="input-helper--radio input-helper--radio" check="checked" /></span><br /><br />
                                                    <span style="padding:10 auto;" id="Bifocal_span">Bifocal <input type=radio value="1" id="RX1" name="RX" class="input-helper--radio input-helper--radio" /></span><br /><br />
                                                    <span style="padding:10 auto;" id="Trifocal_span" name="Trifocal_span">Trifocal <input type=radio value="2" id="RX1" name="RX" class="input-helper--radio input-helper--radio" /></span><br /><br />
                                                    <span style="padding:10 auto;" id="Progressive_span">Prog. <input type=radio value="3" id="RX1" name="RX" class="input-helper--radio input-helper--radio" /></span><br />

                                                </td>
                                            </tr>
                                            <tr>
                                                <td rowspan="2">Distance</td>    
                                                <td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td><input type=text id="WODSPH" name="WODSPH"  value="<?=$WODSPH?>"></td>
                                                <td><input type=text id="WODCYL" name="WODCYL"  value="<?=$WODCYL?>"></td>
                                                <td><input type=text id="WODAXIS" name="WODAXIS" value="<?=$WODAXIS?>"></td>
                                                <td><input type=text id="WODPRISM" name="WODPRISM" value="<?=$WODPRISM?>"></td>
                                                <td><input type=text id="WODVA" name="WODVA" value="<?=$WODVA?>"></td>
                                            </tr>
                                            <tr>
                                                <td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td><input type=text id="WOSSPH" name="WOSSPH" value="<?=$WOSSPH?>"></td>
                                                <td><input type=text id="WOSCYL" name="WOSCYL" value="<?=$WOSCYL?>"></td>
                                                <td><input type=text id="WOSAXIS" name="WOSAXIS" value="<?=$WOSAXIS?>"></td>
                                                <td><input type=text id="WOSPRISM" name="WOSPRISM" value="<?=$WOSPRISM?>"></td>
                                                <td><input type=text id="WOSVA" name="WOSVA" value="<?=$WOSVA?>"></td>
                                            </tr>
                                            <tr class="WNEAR">
                                                <td rowspan=2><span style="text-decoration:none;">Mid/<br />Near</span></td>    
                                                <td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td class="WMid nodisplay"><input type=text id="WODADD1" name="WODADD1" value="<?=$WODADD1?>"></td>
                                                <td class="WAdd2"><input type=text id="WODADD2" name="WODADD2" value="<?=$WODADD2?>"></td>
                                                <td class="WHIDECYL"><input type=text id="WNEARODCYL" name="WNEARODCYL" value="<?=$WNEARODCYL?>"></td>
                                                <td><input type=text id="WNEARODAXIS" name="WNEARODAXIS" value="<?=$WNEARODAXIS?>"></td>
                                                <td><input type=text id="WNEARODPRISM" name="WODPRISMNEAR" value="<?=$WNEARODPRISM?>"></td>
                                                <td><input type=text id="WNEARODVA" name="WNEARODVA" value="<?=$WNEARODVA?>"></td>
                                            </tr>
                                            <tr class="WNEAR">
                                                <td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td class="WMid nodisplay"><input type=text id="WOSADD1" name="WOSADD1" value="<?=$WOSADD1?>"></td>
                                                <td class="WAdd2"><input type=text id="WOSADD2" name="WOSADD2" value="<?=$WOSADD2?>"></td>
                                                <td class="WHIDECYL"><input type=text id="WNEAROSCYL" name="WNEAROSCYL" value="<?=$WNEAROSCYL?>"></td>
                                                <td><input type=text id="WNEAROSAXIS" name="WNEAROSAXIS" value="<?=$WNEAROSAXIS?>"></td>
                                                <td><input type=text id="WNEAROSPRISM" name="WNEAROSPRISM" value="<?=$WNEAROSPRISM?>"></td>
                                                <td><input type=text id="WNEAROSVA" name="WNEAROSVA" value="<?=$WNEAROSVA?>"></td>
                                            </tr>
                                            <tr style="">
                                                <td colspan="2" class="up" style="text-align:right;vertical-align:top;top:0px;"><b><?php xl('Comments','e'); ?>:</b>
                                                </td>
                                                <td colspan="4" class="up" style="text-align:left;vertical-align:middle;top:0px;">
                                                    <textarea style="width:100%;height:3.0em;" id="WCOMMENTS" name="WCOMMENTS"><?=$WCOMMENTS?></textarea>     
                                                </td>
                                                <td colspan="2"> 
                                                    
                                                </td>
                                            </tr>
                                            <tr id="signature_W" class="nodisplay">
                                                <td colspan="5">
                                                    <span style="font-size:0.7em;font-weight:bold;">e-signature:</span> <i><?=$providerID?></i>
                                                </td>
                                                <td colspan="3" style="text-align:right;text-decoration:underline;font-size:0.8em;font-weight:bold;">DATE: <? echo $date; ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <? ($MR==1) ? ($display_AR = "display") : ($display_AR = "nodisplay");?>
                                    <div id="LayerVision_MR" class="refraction borderShadow <? echo $display_AR; ?>">
                                        <span class="closeButton fa  fa-close" id="Close_MR" name="Close_MR"></span>
                                        <a class="closeButton2 fa fa-print" href="../../forms/<?php echo $form_folder; ?>/SpectacleRx.php?target=AR&id=<?=$pid?>"></a>
                                        <table id="autorefraction">
                                            <th colspan=9>Autorefraction Refraction</th>
                                            <tr>
                                                <td></td>
                                                <td><?php xl('Sph','e'); ?></td>
                                                <td><?php xl('Cyl','e'); ?></td>
                                                <td><?php xl('Axis','e'); ?></td>
                                                <td><?php xl('Acuity','e'); ?></td>
                                                <td><?php xl('ADD','e'); ?></td>
                                                <td><?php xl('Jaeger','e'); ?></td>
                                                <td><?php xl('Prism','e'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td><input type=text id="ARODSPH" name="ARODSPH" value="<?=$ARODSPH?>"></td>
                                                <td><input type=text id="ARODCYL" name="ARODCYL" value="<?=$ARODCYL?>"></td>
                                                <td><input type=text id="ARODAXIS" name="ARODAXIS" value="<?=$ARODAXIS?>"></td>
                                                <td><input type=text id="ARODVA" name="ARODVA" value="<?=$ARODVA?>"></td>
                                                <td><input type=text id="ARODADD" name="ARODADD" value="<?=$ARODADD?>"></td>
                                                <td><input type=text id="ARNEARODVA" name="ARNEARODVA" value="<?=$ARNEARODVA?>"></td>
                                                <td><input type=text id="ARODPRISM" name="ARODPRISM" value="<?=$ARODPRISM?>"></td>
                                            </tr>
                                             <tr>
                                                <td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td><input type=text id="AROSSPH" name="AROSSPH" value="<?=$AROSSPH?>"></td>
                                                <td><input type=text id="AROSCYL" name="AROSCYL" value="<?=$AROSCYL?>"></td>
                                                <td><input type=text id="AROSAXIS" name="AROSAXIS" value="<?=$AROSAXIS?>"></td>
                                                <td><input type=text id="AROSVA" name="AROSVA" value="<?=$AROSVA?>"></td>
                                                <td><input type=text id="AROSADD" name="AROSADD" value="<?=$AROSADD?>"></td>
                                                <td><input type=text id="ARNEAROSVA" name="ARNEAROSVA" value="<?=$ARNEAROSVA?>"></td>
                                                <td><input type=text id="AROSPRISM" name="AROSPRISM" value="<?=$AROSPRISM?>"></td>
                                            </tr>
                                            <th colspan="7">Manifest (Dry) Refraction</th>
                                            <th colspan="2" style="text-align:right;"><a class="fa fa-print" style="margin:0 7;" href="../../forms/<?php echo $form_folder; ?>/SpectacleRx.php?target=MR&id=<?=$pid?>"></a></th>
                                            <tr>
                                                <td></td>
                                                <td><?php xl('Sph','e'); ?></td>
                                                <td><?php xl('Cyl','e'); ?></td>
                                                <td><?php xl('Axis','e'); ?></td>
                                                <td><?php xl('Acuity','e'); ?></td>
                                                <td><?php xl('ADD','e'); ?></td>
                                                <td><?php xl('Jaeger','e'); ?></td>
                                                <td><?php xl('Prism','e'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td><input type=text id="MRODSPH" name="MRODSPH" value="<?=$MRODSPH?>"></td>
                                                <td><input type=text id="MRODCYL" name="MRODCYL" value="<?=$MRODCYL?>"></td>
                                                <td><input type=text id="MRODAXIS"  name="MRODAXIS" value="<?=$MRODAXIS?>"></td>
                                                <td><input type=text id="MRODVA"  name="MRODVA" value="<?=$MRODVA?>"></td>
                                                <td><input type=text id="MRODADD"  name="MRODADD" value="<?=$MRODADD?>"></td>
                                                <td><input type=text id="MRNEARODVA"  name="MRNEARODVA" value="<?=$MRNEARODVA?>"></td>
                                                <td><input type=text id="MRODPRISM"  name="MRODPRISM" value="<?=$MRODPRISM?>"></td>
                                            </tr>
                                            <tr>
                                                <td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td><input type=text id="MROSSPH" name="MROSSPH" value="<?=$MROSSPH?>"></td>
                                                <td><input type=text id="MROSCYL" name="MROSCYL" value="<?=$MROSCYL?>"></td>
                                                <td><input type=text id="MROSAXIS"  name="MROSAXIS" value="<?=$MROSAXIS?>"></td>
                                                <td><input type=text id="MROSVA"  name="MROSVA" value="<?=$MROSVA?>"></td>
                                                <td><input type=text id="MROSADD"  name="MROSADD" value="<?=$MROSADD?>"></td>
                                                <td><input type=text id="MRNEAROSVA"  name="MRNEAROSVA" value="<?=$MRNEAROSVA?>"></td>
                                                <td><input type=text id="MROSPRISM"  name="MROSPRISM" value="<?=$MROSPRISM?>"></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <? ($CR==1)  ? ($display_Cyclo = "display") : ($display_Cyclo = "nodisplay"); ?>
                                    <div id="LayerVision_CR" class="refraction borderShadow <? echo $display_Cyclo; ?>">
                                        <span class="closeButton fa  fa-close" id="Close_CR" name="Close_CR"></span>
                                        <a class="closeButton2 fa fa-print" href="../../forms/<?php echo $form_folder; ?>/SpectacleRx.php?target=CR&id=<?=$pid?>"></a>
                                        <table id="cycloplegia">
                                            <th colspan=9><?php xl('Cycloplegic (Wet) Refraction','e'); ?></th>
                                            <tr>
                                                <td></td>
                                                <td><?php xl('Sph','e'); ?></td>
                                                <td><?php xl('Cyl','e'); ?></td>
                                                <td><?php xl('Axis','e'); ?></td>
                                                <td><?php xl('Acuity','e'); ?></td>

                                                <td colspan="1" style="text-align:left;width:60px;">
                                                    <input type="radio" name="wetType" id="Flash" value="Flash" />
                                                    <label for="Flash" class="input-helper input-helper--checkbox"><?php xl('Flash','e'); ?></label>
                                                </td>
                                                <td colspan="2" rowspan="4" style="text-align:left;width:75px;font-size:0.6em;"><b style="text-align:center;width:70px;text-decoration:underline;"><?php xl('Dilated with','e'); ?>:</b><br />
                                                    <input type="checkbox" id="CycloMydril" name="CycloMydril" value="cyclopentolate hydrochloride 0.2% and phenylephrine hydochloride 1%" checked="checked">
                                                    <label for="CycloMydril" class="input-helper input-helper--checkbox">CycloMydril</label>
                                                    <br />
                                                    <input type="checkbox" id="Tropicamide" name="Cyclogyl" value="Tropicamide 2.5%"/>
                                                    <label for="Tropicamide" class="input-helper input-helper--checkbox">Tropic 2.5%</label>
                                                    </br>
                                                    <input type="checkbox" id="Neo25" name="Neo25" value="Neosynephrine 2.5%"/>
                                                    <label for="Neo25" class="input-helper input-helper--checkbox">Neo 2.5%</label>
                                                    <br />
                                                    <input type="checkbox" id="Cyclogyl" name="Cyclogyl" value="Cyclopentolate 1%" />
                                                    <label for="Cyclogyl" class="input-helper input-helper--checkbox">Cyclo 1%</label>
                                                    </br>
                                                    <input type="checkbox" id="Atropine" name="Atropine" value="Atropine 1%" />
                                                    <label for="Atropine" class="input-helper input-helper--checkbox">Atropine 1%</label>
                                                    </br>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td><input type=text id="CRODSPH" name="CRODSPH" value="<?=$CRODSPH?>"></td>
                                                <td><input type=text id="CRODCYL" name="CRODCYL" value="<?=$CRODCYL?>"></td>
                                                <td><input type=text id="CRODAXIS" name="CRODAXIS" value="<?=$CRODAXIS?>"></td>
                                                <td><input type=text id="CRODVA" name="CRODVA"  value="<?=$CRODVA?>"></td>
                                                <td colspan="1" style="text-align:left;">
                                                    <input type="radio" name="wetType" id="Auto" value="Auto">
                                                    <label for="Auto" class="input-helper input-helper--checkbox"><?php xl('Auto','e'); ?></label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td><input type=text id="CROSSPH" name="CROSSPH" value="<?=$CROSSPH?>"></td>
                                                <td><input type=text id="CROSCL" name="CROSCYL" value="<?=$CROSCYL?>"></td>
                                                <td><input type=text id="CROSAXIS" name="CROSAXIS" value="<?=$CROSAXIS?>"></td>
                                                <td><input type=text id="CROSVA" name="CROSVA" value="<?=$CROSVA?>"></td>
                                                <td colspan="1" style="text-align:left;">
                                                    <input type="radio" name="wetType" id="Manual" value="Manual">
                                                    <label for="Manual" class="input-helper input-helper--checkbox"><?php xl('Manual','e'); ?></label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" style="vertical-align:text-top;">
                                                    <input type="checkbox" id="DIL_RISKS" name="DIL_RISKS" value="on" <?php if ($DIL_RISKS =='on') echo "checked"; ?>>
                                                    <label for="DIL_RISKS" class="input-helper input-helper--checkbox"><?php xl('Dilation risks reviewed','e'); ?></label>
                                                </td>
                                                <td colspan="1" style="text-align:left;">
                                                    <input type="checkbox" name="wetType" id="Balanced" value="Balanced">
                                                    <label for="Balanced" class="input-helper input-helper--checkbox"><?php xl('Balanced','e'); ?></label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="vertical-align:bottom;"><b><?php xl('Comments','e'); ?>:</b></td>
                                                <td colspan="4"></td>

                                            </tr>
                                            <tr>
                                                <td colspan="9" style="text-align:center;"><textarea id="CRCOMMENTS" name="CRCOMMENTS" style="width:98%;height:3.5em;"><?=$CRCOMMENTS?></textarea>
                                                </tD>
                                            </tr>
                                        </table>
                                    </div>

                                    <? ($CTL==1) ? ($display_CTL = "display") : ($display_CTL = "nodisplay"); ?>
                                    <div id="LayerVision_CTL" class="refraction borderShadow <? echo $display_CTL; ?>">
                                        <span class="closeButton fa  fa-close" id="Close_CTL" name="Close_CTL"></span>
                                        <a class="closeButton2 fa fa-print" href="../../forms/<?php echo $form_folder; ?>/SpectacleRx.php?target=CTL&id=<?=$pid?>"></a>
                                        <table id="CTL" style="width:100%;">
                                            <th colspan="9"><?php xl('Contact Lens Refraction','e'); ?></th>
                                            <tr>
                                                <td style="text-align:center;">
                                                    <div style="box-shadow: 1px 1px 2px #888888;border-radius: 8px; margin: 5 auto; position:inline-block; padding: 0.02in; border: 1.00pt solid #000000; ">
                                                        <table>
                                                            <tr>
                                                                <td></td>
                                                                <td>Manufacturer</td>
                                                                <td>Supplier</td>
                                                                <td>Brand</td>
                                                            </tr>
                                                            <tr>
                                                                <td><b><?php xl('OD','e'); ?>:</b></td>
                                                                <td>
                                                                    <!--  these will need to be pulled from a CTL specific table probably -->
                                                                    <select id="CTLMANUFACTUREROD" name="CTLMANUFACTUREROD">
                                                                        <option></option>
                                                                        <option value="BL">Bausch and Lomb</option>
                                                                        <option value="JNJ">JNJ</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select id="CTLSUPPLIEROD" name="CTLMANUFACTUREROD">
                                                                        <option></option>
                                                                        <option value="ABB">ABB</option>
                                                                        <option value="JNJ">JNJ</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select id="CTLBRANDOD" name="CTLBRANDOD">
                                                                        <option></option>
                                                                        <option value="Accuvue">Accuvue</option>
                                                                        <option value="ExtremeH2O">Extreme H2O</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr >
                                                                <td><b><?php xl('OS','e'); ?>:</b></td>
                                                                <td>
                                                                    <select id="CTLMANUFACTUREROS" name="CTLMANUFACTUREROS">
                                                                        <option></option>
                                                                        <option value="BL">Bausch and Lomb</option>
                                                                        <option value="JNJ">JNJ</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select id="CTLSUPPLIEROS" name="CTLSUPPLIEROS">
                                                                        <option></option>
                                                                        <option value="ABB">ABB</option>
                                                                        <option value="JNJ">JNJ</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select id="CTLBRANDOS" name="CTLBRANDOS">
                                                                        <option></option>
                                                                        <option value="Accuvue">Accuvue</option>
                                                                        <option value="ExtremeH2O">Extreme H2O</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <table>
                                            <tr>
                                                <td></td>
                                                <td><?php xl('Sph','e'); ?></td>
                                                <td><?php xl('Cyl','e'); ?></td>
                                                <td><?php xl('Axis','e'); ?></td>
                                                <td><?php xl('BC','e'); ?></td>
                                                <td><?php xl('Diam','e'); ?></td>
                                                <td><?php xl('ADD','e'); ?></td>
                                                <td><?php xl('Acuity','e'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td><input type=text id="CTLODSPH" name="CTLODSPH" value="<?=$CTLODSPH?>"></td>
                                                <td><input type=text id="CTLODCYL" name="CTLODCYL" value="<?=$CTLODCYL?>"></td>
                                                <td><input type=text id="CTLODAXIS" name="CTLODAXIS" value="<?=$CTLODAXIS?>"></td>
                                                <td><input type=text id="CTLODBC" name="CTLODBC" value="<?=$CTLODBC?>"></td>
                                                <td><input type=text id="CTLODDIAM" name="CTLODDIAM" value="<?=$CTLODDIAM?>"></td>
                                                <td><input type=text id="CTLODADD" name="CTLODADD" value="<?=$CTLODADD?>"></td>
                                                <td><input type=text id="CTLODVA" name="CTLODVA" value="<?=$CTLODVA?>"></td>
                                            </tr>
                                            <tr >
                                                <td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td><input type=text id="CTLOSSPH" name="CTLOSSPH" value="<?=$CTLOSSPH?>"></td>
                                                <td><input type=text id="CTLOSCYL" name="CTLOSCYL" value="<?=$CTLOSCYL?>"></td>
                                                <td><input type=text id="CTLOSAXIS" name="CTLOSAXIS" value="<?=$CTLOSAXIS?>"></td>
                                                <td><input type=text id="CTLOSBC" name="CTLOSBC" value="<?=$CTLOSBC?>"></td>
                                                <td><input type=text id="CTLOSDIAM" name="CTLOSDIAM" value="<?=$CTLOSDIAM?>"></td>
                                                <td><input type=text id="CTLOSADD" name="CTLOSADD" value="<?=$CTLOSADD?>"></td>
                                                <td><input type=text id="CTLOSVA" name="CTLOSVA" value="<?=$CTLOSVA?>"></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <? ($ADDITIONAL==1) ? ($display_Add = "display") : ($display_Add = "nodisplay"); ?>
                                    <div id="LayerVision_ADDITIONAL" class="refraction borderShadow <? echo $display_Add; ?>">
                                        <span class="closeButton fa  fa-close" id="Close_ADDITIONAL" name="Close_ADDITIONAL"></span>

                                        <table id="Additional">
                                            <th colspan=9><?php xl('Additional Data Points','e'); ?></th>
                                            <tr><td></td>
                                                <td><?php xl('PH','e'); ?></td>
                                                <td><?php xl('PAM','e'); ?></td>
                                                <td><?php xl('BAT','e'); ?></td>
                                                <td><?php xl('K1','e'); ?></td>
                                                <td><?php xl('K2','e'); ?></td>
                                                <td><?php xl('Axis','e'); ?></td>
                                                <td><?php xl('pend','e'); ?></td>
                                            </tr>
                                            <tr><td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td><input type=text id="PHODVA" name="PHODVA" value="<?=$PHODVA?>"></td>
                                                <td><input type=text id="PAMODVA" name="PAMODVA" value="<?=$PAMODVA?>"></td>
                                                <td><input type=text id="GLAREODVA" name="GLAREODVA" value="<?=$GLAREODVA?>"></td>
                                                <td><input type=text id="ODK1" name="ODK1" value="<?=$ODK1?>"></td>
                                                <td><input type=text id="ODK2" name="ODK2" value="<?=$ODK2?>"></td>
                                                <td><input type=text id="ODK2AXIS" name="ODK2AXIS" value="<?=$ODK2AXIS?>"></td>
                                                <td><input type=text id="pend" name="pend" value="<?=$pend?>"></td>
                                            </tr>
                                            <tr>
                                                <td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td><input type=text id="PHOSVA" name="PHOSVA" value="<?=$PHOSVA?>"></td>
                                                <td><input type=text id="PAMOSVA" name="PAMOSVA" value="<?=$PAMOSVA?>"></td>
                                                <td><input type=text id="GLAREOSVA" name="GLAREOSVA" value="<?=$GLAREOSVA?>"></td>
                                                <td><input type=text id="OSK1" name="OSK1" value="<?=$OSK1?>"></td>
                                                <td><input type=text id="OSK2" name="OSK2" value="<?=$OSK2?>"></td>
                                                <td><input type=text id="OSK2AXIS" name="OSK2AXIS" value="<?=$OSK2AXIS?>"></td>
                                                <td><input type=text id="pend" name="pend" value="<?=$pend?>"></td>
                                            </tr>
                                            <tr><td>&nbsp;</td></tr>
                                            <tr>
                                                <td></td>
                                                <td><?php xl('AxLength','e'); ?></td>
                                                <td><?php xl('ACD','e'); ?></td>
                                                <td><?php xl('PD','e'); ?></td>
                                                <td><?php xl('LT','e'); ?></td>
                                                <td><?php xl('W2W','e'); ?></td>
                                                <td><?php xl('pend','e'); ?></td>
                                                <td><?php xl('pend','e'); ?></td>
                                            </tr>
                                            <tr><td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td><input type=text id="ODAXIALLENGTH" name="ODAXIALLENGTH"  value="<?=$ODAXIALLENGTH?>"></td>
                                                <td><input type=text id="ODACD" name="ODACD"  value="<?=$ODACD?>"></td>
                                                <td><input type=text id="ODPDMeasured" name="ODPDMeasured"  value="<?=$ODPDMeasured?>"></td>
                                                <td><input type=text id="ODLT" name="ODLT"  value="<?=$ODLT?>"></td>
                                                <td><input type=text id="ODW2W" name="ODW2W"  value="<?=$ODW2W?>"></td>
                                                <td><input type=text id="pend" name="pend"  value="<?=$pend?>"></td>
                                                <td><input type=text id="pend" name="pend"  value="<?=$pend?>"></td>
                                            </tr>
                                            <tr>
                                                <td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td><input type=text id="OSAXIALLENGTH" name="OSAXIALLENGTH" value="<?=$OSAXIALLENGTH?>"></td>
                                                <td><input type=text id="OSACD" name="OSACD" value="<?=$OSACD?>"></td>
                                                <td><input type=text id="OSPDMeasured" name="OSPDMeasured" value="<?=$OSPDMeasured?>"></td>
                                                    <td><input type=text id="OSLT" name="OSLT" value="<?=$OSLT?>"></td>
                                                    <td><input type=text id="OSW2W" name="OSW2W" value="<?=$OSW2W?>"></td>
                                                    <td><input type=text id="pend" name="pend" value="<?=$pend?>"></td>
                                                    <td><input type=text id="pend" name="pend" value="<?=$pend?>"></td>
                                                </tr>
                                            </table>
                                    </div>  

                                    <? ($ADDITIONAL_VISION==1 or ($ADDITIONAL==1)) ? ($display_Add = "display") : ($display_Add = "nodisplay"); ?>
                                    <div id="LayerVision_ADDITIONAL_VISION" class="refraction borderShadow <? echo $display_Add; ?>">
                                        <span class="closeButton fa  fa-close" id="Close_ADDITIONAL_VISION" name="Close_ADDITIONAL_VISION"></span> 
                                        <table id="Additional">
                                            <th colspan="9"><?php xl('Visual Acuity','e'); ?></th>
                                            <tr><td></td>
                                                <td><?php xl('SC','e'); ?></td>
                                                <td><?php xl('W Rx','e'); ?></td>
                                                <td><?php xl('AR','e'); ?></td>
                                                <td><?php xl('MR','e'); ?></td>
                                                <td><?php xl('CR','e'); ?></td>
                                                <td><?php xl('PH','e'); ?></td>
                                                <td><?php xl('CTL','e'); ?></td>
                                                
                                            </tr>
                                            <tr><td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td><input type=text id="SCODVA_copy_brd" name="SCODVA_copy_brd" value="<?=$SCODVA?>"></td>
                                                <td><input type=text id="WODVA_copy_brd" name="WODVA_copy_brd" value="<?=$WODVA?>"></td>
                                                <td><input type=text id="ARODVA_copy_brd" name="ARODVA_copy_brd" value="<?=$ARODVA?>"></td>
                                                <td><input type=text id="MRODVA_copy_brd" name="MRODVA_copy_brd" value="<?=$MRODVA?>"></td>
                                                <td><input type=text id="CRODVA_copy_brd" name="CRODVA_copy_brd" value="<?=$CRODVA?>"></td>
                                                <td><input type=text id="PHODVA_copy_brd" name="PHODVA_copy_brd" value="<?=$PHODVA?>"></td>
                                                <td><input type=text id="CTLODVA_copy_brd" name="CTLODVA_copy_brd" value="<?=$CTLODVA?>"></td>
                                                </tr>
                                             <tr><td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td><input type=text id="SCOSVA_copy" name="SCOSVA_copy" value="<?=$SCOSVA?>"></td>
                                                <td><input type=text id="WOSVA_copy_brd" name="WOSVA_copy_brd" value="<?=$WOSVA?>"></td>
                                                <td><input type=text id="AROSVA_copy_brd" name="AROSVA_copy_brd" value="<?=$AROSVA?>"></td>
                                                <td><input type=text id="MROSVA_copy_brd" name="MROSVA_copy_brd" value="<?=$MROSVA?>"></td>
                                                <td><input type=text id="CROSVA_copy_brd" name="CROSVA_copy_brd" value="<?=$CROSVA?>"></td>
                                                <td><input type=text id="PHOSVA_copy_brd" name="PHOSVA_copy_brd" value="<?=$PHOSVA?>"></td>
                                                <td><input type=text id="CTLOSVA_copy_brd" name="CTLOSVA_copy_brd" value="<?=$CTLOSVA?>"></td>
                                            </tr>
                                            <tr><td>&nbsp;</td></tr>
                                            <tr>
                                                <td></td>
                                                <td><?php xl('scNnear','e'); ?></td>
                                                <td><?php xl('ccNear','e'); ?></td>
                                                <td><?php xl('ARNear','e'); ?></td>
                                                <td><?php xl('MRNear','e'); ?></td>
                                                <td><?php xl('PAM','e'); ?></td>
                                                <td><?php xl('Glare','e'); ?></td>
                                                <td><?php xl('Contrast','e'); ?></td>
                                            </tr>
                                             <tr><td><b><?php xl('OD','e'); ?>:</b></td>
                                                <td><input type=text id="SCNEARODVA" name="SCNEARODVA" value="<?=$SCNEARODVA?>"></td>
                                                <td><input type=text id="WNEARODVA_copy_brd" name="WNEARODVA_copy_brd" value="<?=$WNEARODVA?>"></td>
                                                <td><input type=text id="ARNEARODVA_copy_brd" name="ARNEARODVA_copy_brd" value="<?=$ARNEARODVA?>"></td>
                                                <td><input type=text id="MRNEARODVA_copy_brd" name="MRNEARODVA_copy_brd" value="<?=$MRNEARODVA?>"></td>
                                                <td><input type=text id="PAMODVA_copy_brd" name="PAMODVA_copy_brd" value="<?=$PAMODVA?>"></td>
                                                <td><input type=text id="GLAREODVA_copy_brd" name="GLAREODVA_copy_brd" value="<?=$GLAREODVA?>"></td>
                                                <td><input type=text id="CONTRASTODVA_copy_brd" name="CONTRASTODVA_copy_brd" value="<?=$CONTRASTODVA?>"></td>
                                            </tr>
                                            <tr><td><b><?php xl('OS','e'); ?>:</b></td>
                                                <td><input type=text id="SCNEAROSVA" name="SCNEAROSVA" value="<?=$SCNEAROSVA?>"></td>
                                                <td><input type=text id="WNEAROSVA_copy_brd" name="WNEAROSVA_copy_brd" value="<?=$WNEAROSVA?>"></td>
                                                <td><input type=text id="ARNEAROSVA_copy" name="ARNEAROSVA_copy" value="<?=$ARNEAROSVA?>"></td>
                                                <td><input type=text id="MRNEAROSVA_copy" name="MRNEAROSVA_copy" value="<?=$MRNEAROSVA?>"></td>
                                                <td><input type=text id="PAMOSVA_copy_brd" name="PAMOSVA_copy_brd" value="<?=$PAMOSVA?>"></td>
                                                <td><input type=text id="GLAREOSVA_copy_brd" name="GLAREOSVA_copy_brd" value="<?=$GLAREOSVA?>"></td>
                                                <td><input type=text id="CONTRASTOSVA" name="CONTRASTOSVA" value="<?=$CONTRASTOSVA?>"></td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>   
                <!-- end of the refraction box -->

                <!-- my reporting div for development only remove the "X" to see output from save.php-->
                <div id="tellmeX" name="tellmeX"></div><br />
                <!-- end reporting div -->

                <!-- Start of the exam selection row -->
                <div class="section" style="text-align:center;width:100%">
                    <span id="EXAM_settings" name="EXAM_settings" class="btn btn-default btn-sm bordershadow" href="#"><i class="fa fa-cog"></i>&nbsp;<?php xl('Settings','e'); ?></span>
                    <span id="EXAM_defaults" name="EXAM_defaults" value="Defaults" class="btn btn-default btn-sm bordershadow"><i class="fa fa-newspaper-o"></i>&nbsp;<?php xl('Defaults','e'); ?></span> 
                    <span id="EXAM_CLINICAL" name="EXAM_CLINICAL" value="TEXT" class="pbtn btn-default btn-sm bordershadow"><i class="fa fa-hospital-o"></i>&nbsp;<?php xl('Text','e'); ?></span>
                    <span id="EXAM_DRAW" name="EXAM_DRAW" value="DRAW" class="btn btn-default btn-sm bordershadow"><i class="fa fa-paint-brush fa-sm"> </i>&nbsp;<?php xl('Draw','e'); ?></span>
                    <span id="EXAM_QP" name="EXAM_QP" value="QP" class="btn btn-default btn-sm bordershadow"><i class="fa fa-shopping-cart fa-sm"> </i>&nbsp;<?php xl('Quick Picks','e'); ?></span>
                    <span id="PRIORS_ALL_left_text" name="PRIORS_ALL_left_text" 
                          class="PRIORS btn btn-default btn-sm bordershadow nodisplay" style="vertical-align:bottom;">
                            <i class="fa fa-spinner"></i>
                        </span>
                        <? 
                        $output = priors_select("ALL",$date,$pid);
                        if ($output !='') { ?>
                    <span id="ALL_right" name="ALL_right" value="pullForward" class="PRIORS btn btn-default btn-sm bordershadow" style="vertical-align:bottom;">
                        <? echo priors_select("ALL",$date,$pid); ?>
                    </span>     <? } ?>
                    <br /><br />
                </div>
                <!-- end of the exam selection row -->

                <!-- Start of the exam section -->
                <div style="margin: 0 auto;width:100%;text-align: center;font-size:1.0em;">   
                    <!-- start External Exam -->
                    <? ($CLINICAL=='100') ? ($display_Add = "size100") : ($display_Add = "size50"); ?>
                    <? ($CLINICAL=='50') ? ($display_Visibility = "display") : ($display_Visibility = "nodisplay"); ?>
                    <div id="EXT_1" name="EXT_1" class="<?=$display_Add?>">
                        <div id="EXT_left" class="exam_section_left borderShadow" >
                            <div id="EXT_left_text" style="height: 2.5in;text-align:left;">
                                <b><?php xl('External Exam','e'); ?>:</b><br />
                                <div style="position:relative;float:right;top:0.2in;">
                                    <table style="text-align:center;font-weight:600;font-size:0.8em;">
                                        <tr>
                                            <td></td><td><?php xl('OD','e'); ?></td><td><?php xl('OS','e'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php xl('Lev Fn','e'); ?></td>
                                            <td><input  type="text"  name="RLF" id="RLF" value="<?=$RLF?>"></td>
                                            <td><input  type="text"  name="LLF" id="LLF" value="<?=$LLF?>"></td>
                                        </tr>
                                        <tr>
                                            <td><?php xl('MRD','e'); ?></td>
                                            <td><input type="text" size="1" name="RMRD" id="RMRD" value="<?=$RMRD?>"></td>
                                            <td><input type="text" size="1" name="LMRD" id="LMRD" value="<?=$LMRD?>"></td>
                                        </tr>
                                        <tr>
                                            <td><?php xl('Vert Fissure','e'); ?></td>
                                            <td><input type="text" size="1" name="RVFISSURE" id="RVFISSURE" value="<?=$RVFISSURE?>"></td>
                                            <td><input type="text" size="1" name="LVFISSURE" id="LVFISSURE" value="<?=$LVFISSURE?>"></td>
                                        </tr>
                                        <tr><th colspan="3"><u style="padding-top:0.15in;"><br /><?php xl('Hertel Exophthalmometry','e'); ?></u></th></tr>
                                        <tr style="text-align:center;">
                                            <td>
                                                <input type="text" size="1" id="ODHERTEL" name="ODHERTEL" value="<?=$ODHERTEL?>">
                                                <span style="width:40px;-moz-text-decoration-line: line-through;text-align:center;"> &nbsp;&nbsp;&nbsp;&nbsp; </span>
                                            </td>
                                            <td>
                                                <input type=text size=3  id="HERTELBASE" name="HERTELBASE" value="<?=$HERTELBASE?>">
                                                <span style="width:400px;-moz-text-decoration-line: line-through;"> &nbsp;&nbsp;&nbsp;&nbsp; </span>
                                            </td>
                                            <td>
                                                <input type=text size=1  id="OSHERTEL" name="OSHERTEL" value="<?=$OSHERTEL?>">
                                            </td>
                                        </tr>
                                    </table>
                                    
                                </div>

                                <? ($EXT_VIEW ==1) ? ($display_EXT_view = "wide_textarea") : ($display_EXT_view= "narrow_textarea");?>                                 
                                <? ($display_EXT_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                                <div id="EXT_text_list" name="EXT_text_list" class="borderShadow  <?=$display_EXT_view?>">
                                    <span class="top_right fa <?=$marker?>" name="EXT_text_view" id="EXT_text_view"></span>
                                    <table cellspacing="0" cellpadding="0">
                                        <tr>
                                            <th><?php xl('Right','e'); ?></th><td style="width:100px;"></td><th><?php xl('Left','e'); ?></th>
                                        </tr>
                                        <tr>
                                            <td><textarea name="RBROW" id="RBROW" class="right "><?=$RBROW?></textarea></td>
                                            <td style="text-align:center;font-size:0.9em;"><?php xl('Brow<','e'); ?>/td>
                                            <td><textarea name="LBROW" id="LBROW" class=""><?=$LBROW?></textarea></td>
                                        </tr> 
                                        <tr>
                                            <td><textarea name="RUL" id="RUL" class="right"><?=$RUL?></textarea></td>
                                            <td style="text-align:center;font-size:0.9em;"><?php xl('Upper Lids','e'); ?></td>
                                            <td><textarea name="LUL" id="LUL" class=""><?=$LUL?></textarea></td>
                                        </tr> 
                                        <tr>
                                            <td><textarea name="RLL" id="RLL" class="right"><?=$RLL?></textarea></td>
                                            <td style="text-align:center;font-size:0.9em;"><?php xl('Lower Lids','e'); ?></td>
                                            <td><textarea name="LLL" id="LLL" class=""><?=$LLL?></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><textarea name="RMCT" id="RMCT" class="right"><?=$RMCT?></textarea></td>
                                            <td style="text-align:center;font-size:0.9em;"><?php xl('Medial Canthi','e'); ?></td>
                                            <td><textarea name="LMCT" id="LMCT" class=""><?=$LMCT?></textarea></td>
                                        </tr>
                                         <tr>
                                            <td><textarea name="RMAX" id="RMAX" class="right"><?=$Adnexa?></textarea></td>
                                            <td style="text-align:center;font-size:0.9em;"><?php xl('Adnexa','e'); ?></td>
                                            <td><textarea name="LMAX" id="LMAX" class=""><?=$Adnexa?></textarea></td>
                                        </tr>
                                    </table>
                                </div>  <br />
                                <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> <b><?php xl('Comments','e'); ?>:</b><br />
                                    <textarea id="EXT_COMMENTS" name="EXT_COMMENTS" style="width:4.0in;height:3em;"><?=$EXT_COMMENTS?></textarea>
                                </div>       
                            </div>  
                        </div>
                        <div id="PRIORS_EXT_left_text" style="height: 2.5in;text-align:left;" 
                                 name="PRIORS_EXT_left_text" 
                                 class="text_clinical PRIORS_color PRIORS exam_section_left exam_section_right borderShadow nodisplay">

                                            
                                            <i class="fa fa-spinner"></i>
                        </div>
                        <div id="EXT_right" name="EXT_right" class="exam_section_right borderShadow text_clinical canvas <?=$display_Visibility?>">
                            <div id="DrawExt" name="DrawExt" class="text_clinical canvas nodisplay" style="text-align:center;height: 2.5in;">
                                <br />
                                <div class="tools">

                                    <a style="background: red;" data-color="#f00" href="#SketchExternal"></a>

                                    <a style="width: 15px; background: yellow;" data-color="#ff0" href="#SketchExternal">  &nbsp;&nbsp;</a>

                                    <a style="width: 15px; background: lime;" data-color="#0f0" href="#SketchExternal">  &nbsp;&nbsp;</a>

                                    <a style="width: 15px; background: aqua;" data-color="#0ff" href="#SketchExternal">  &nbsp;&nbsp;</a>

                                    <a style="width: 15px; background: blue;" data-color="#00f" href="#SketchExternal">  &nbsp;&nbsp;</a>

                                    <a style="width: 15px; background: fuchsia;" data-color="#f0f" href="#SketchExternal">  &nbsp;&nbsp;</a>

                                    <a style="width: 15px; background: black;" data-color="#000" href="#SketchExternal">  &nbsp;&nbsp;</a>

                                    <a style="width: 15px; background: white;" data-color="#fff" href="#SketchExternal"> &nbsp;&nbsp;</a>

                                    <a style="background: #CCC" data-size="1" href="#SketchExternal">1</a>
                                    <a style="background: #CCC" data-size="3" href="#SketchExternal">3</a>

                                    <a style="background: #CCC" data-size="5" href="#SketchExternal">5</a>
                                    <a style="background: #CCC" data-size="10" href="#SketchExternal">10</a>

                                    <a style="background: #CCC" data-size="15" href="#SketchExternal">15</a>  
                                </div>
                                                <!-- Draw: External Exam
                                                    <span id="EXTOD" value="2" class="AntSegSpan"><?php xl('OD','e'); ?></span>
                                                    <span id="EXTOU" value="1" class="AntSegSpan button_selected"><?php xl('OU','e'); ?></span>
                                                    <span id="EXTOS" value="3" class="AntSegSpan"><?php xl('OS','e'); ?></span>
                                                    <br />
                                                -->
                                                <canvas id="SketchExternal" class="borderShadow2 text_clinical" style="background: url(../../forms/<?php echo $form_folder; ?>/images/external_OU.png) no-repeat center center;background-size: 100% 100%;height:1.5in;width:4.5in;padding:0in;margin: 0.1in 0 auto;"></canvas>
                                                <script type="text/javascript">
                                                $(function() {

                                                    $('#SketchExternal').sketch();

                                                });  
                                                </script>
                                                <br />
                            </div>
          
                            <div id="QPExt" name="QPExt" style="text-align:left;max-height: 2.5in;">
                                        <input type="hidden" id="EXT_prefix" name="EXT_prefix" value="<?=$EXT_prefix?>">
                                        <div style="position:relative;top:0.0in;left:0.00in;">
                                            <span class="eye_button eye_button_selected" id="EXT_prefix_off" name="EXT_prefix_off"  onclick="$('#EXT_prefix').val('').trigger('change');;"><?php xl('Off','e'); ?></span>
                                            <span class="eye_button" id="EXT_defaults" name="EXT_defaults"><?php xl('Defaults','e'); ?></span>  
                                            <span class="eye_button" id="EXT_prefix_no" name="EXT_prefix_no" onclick="$('#EXT_prefix').val('no').trigger('change');"> <?php xl('no','e'); ?> </span>  
                                            <span class="eye_button" id="EXT_prefix_trace" name="EXT_prefix_trace"  onclick="$('#EXT_prefix').val('trace').trigger('change');"> <?php xl('tr','e'); ?> </span>  
                                            <span class="eye_button" id="EXT_prefix_1" name="EXT_prefix_1"  onclick="$('#EXT_prefix').val('+1').trigger('change');"> <?php xl('+1','e'); ?> </span>  
                                            <span class="eye_button" id="EXT_prefix_2" name="EXT_prefix_2"  onclick="$('#EXT_prefix').val('+2').trigger('change');"> <?php xl('+2','e'); ?> </span>  
                                            <span class="eye_button" id="EXT_prefix_3" name="EXT_prefix_3"  onclick="$('#EXT_prefix').val('+3').trigger('change');"> <?php xl('+3','e'); ?> </span>  
  

                                            <? echo priors_select("EXT",$date,$pid); ?>
                        
                                        </div>
                                         <div style="float:left;width:40px;text-align:left;">
                                            <span class="eye_button" id="EXT_prefix_1mm" name="EXT_prefix_1mm"  onclick="$('#EXT_prefix').val('1mm').trigger('change');"> <?php xl('1mm','e'); ?> </span>  <br />
                                            <span class="eye_button" id="EXT_prefix_2mm" name="EXT_prefix_2mm"  onclick="$('#EXT_prefix').val('2mm').trigger('change');"> <?php xl('2mm','e'); ?> </span>  <br />
                                            <span class="eye_button" id="EXT_prefix_3mm" name="EXT_prefix_3mm"  onclick="$('#EXT_prefix').val('3mm').trigger('change');"> <?php xl('3mm','e'); ?> </span>  <br />
                                            <span class="eye_button" id="EXT_prefix_4mm" name="EXT_prefix_4mm"  onclick="$('#EXT_prefix').val('4mm').trigger('change');"> <?php xl('4mm','e'); ?> </span>  <br />
                                            <span class="eye_button" id="EXT_prefix_5mm" name="EXT_prefix_5mm"  onclick="$('#EXT_prefix').val('5mm').trigger('change');"> <?php xl('5mm','e'); ?> </span>  <br />
                                            <span class="eye_button" id="EXT_prefix_medial" name="EXT_prefix_medial"  onclick="$('#EXT_prefix').val('medial').trigger('change');"><?php xl('med','e'); ?></span>   
                                            <span class="eye_button" id="EXT_prefix_lateral" name="EXT_prefix_lateral"  onclick="$('#EXT_prefix').val('lateral').trigger('change');"><?php xl('lat','e'); ?></span>  
                                            <span class="eye_button" id="EXT_prefix_superior" name="EXT_prefix_superior"  onclick="$('#EXT_prefix').val('superior').trigger('change');"><?php xl('sup','e'); ?></span>  
                                            <span class="eye_button" id="EXT_prefix_inferior" name="EXT_prefix_inferior"  onclick="$('#EXT_prefix').val('inferior').trigger('change');"><?php xl('inf','e'); ?></span> 
                                            <span class="eye_button" id="EXT_prefix_anterior" name="EXT_prefix_anterior"  onclick="$('#EXT_prefix').val('anterior').trigger('change');"><?php xl('ant','e'); ?></span>  <br /> 
                                            <span class="eye_button" id="EXT_prefix_mid" name="EXT_prefix_mid"  onclick="$('#EXT_prefix').val('mid').trigger('change');"><?php xl('mid','e'); ?></span>  <br />
                                            <span class="eye_button" id="EXT_prefix_posterior" name="EXT_prefix_posterior"  onclick="$('#EXT_prefix').val('posterior').trigger('change');"><?php xl('post','e'); ?></span>  <br />
                                            <span class="eye_button" id="EXT_prefix_deep" name="EXT_prefix_deep"  onclick="$('#EXT_prefix').val('deep').trigger('change');"><?php xl('deep','e'); ?></span> 
                                        </div>   
                                             
                                        <div id="EXT_QP_block1" name="EXT_QP_block1" class="QP_block borderShadow text_clinical" >

                                             <?
                                             $query = "SELECT * FROM dbSelectFindings where id = '3' AND PEZONE = 'EXT' ORDER BY ZONE_ORDER,ordering";

                                             $result = mysql_query($query);
                                             $number_rows=0;
                                            while ($Select_data= mysql_fetch_array($result))   {

                                                $number_rows++;             
                                                $string = $Select_data['selection'] ;
                                                $string = (strlen($string) > 14) ? substr($string,0,12).'...' : $string;         

                                                ?>
                                                <a class="underline QP" onclick="fill_QP_field('EXT','R','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('R','e'); ?></a> | 
                                                <a class="underline QP" onclick="fill_QP_field('EXT','L','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('L','e'); ?></a> | 
                                                <a class="underline QP" onclick="fill_QP_field('EXT','R','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',2);fill_QP_field('EXT','L','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('B','e'); ?></a> </span>
                                                &nbsp;    <?=$Select_data['LOCATION']?>: <?=$string?>
                                                <br />
                                                <?
                                                if ($number_rows==15) {
                                                    ?>
                                                     </div>
                                                     <div id="EXT_QP_block2" name="EXT_QP_block2" class="QP_block_outer  borderShadow text_clinical" >
                                                        <?
                                                }
                                                if ($number_rows==30) break;
                                            }
                                                ?>   
                                        </div>                
                            </div>
                        </div>
                    </div>
                    <!-- end External Exam -->

                    <!-- start Anterior Segment -->
                    <div id="ANTSEG_1" class="<?=$display_Add?> clear_both"> 
                        <div id="ANTSEG_left" class="exam_section_left borderShadow">
                            <span class="closeButton fa fa-plus-square-o" id="MAX_ANTSEG" name="MAX_ANTSEG"></span>
                            <div id="ANTSEG_left_text" style="height: 2.5in;text-align:left;">
                                <b><?php xl('Anterior Segment','e'); ?>:</b><br />
                                <div class="text_clinical" style="position:relative;float:right;top:0.2in;">
                                    <table style="text-align:center;font-size:0.8em;font-weight:bold;"> 
                                        <tr >
                                            <td></td><td><?php xl('OD','e'); ?></td><td><?php xl('OS','e'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php xl('Gonioscopy','e'); ?></td>
                                            <td><input  type="text" class="" name="ODGONIO" id="ODGONIO" value="<?=$ODGONIO?>"></td>
                                            <td><input  type="text" size="2" name="OSGONIO" id="OSGONIO" value="<?=$OSGONIO?>"></td>
                                        </tr>
                                        <tr>
                                            <td><?php xl('Pachymetry','e'); ?></td>
                                            <td><input type="text" size="1" name="ODKTHICKNESS" id="ODKTHICKNESS" value="<?=$ODKTHICKNESS?>"></td>
                                            <td><input type="text" size="1" name="OSKTHICKNESS" id="OSKTHICKNESS" value="<?=$OSKTHICKNESS?>"></td>
                                        </tr>
                                    </table>
                                </div>

                                <? ($ANTSEG_VIEW !='1') ? ($display_ANTSEG_view = "wide_textarea") : ($display_ANTSEG_view= "narrow_textarea");?>
                                <? ($display_ANTSEG_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                                <div id="ANTSEG_text_list"  name="ANTSEG_text_list" class="borderShadow <?=$display_ANTSEG_view?>" >
                                        <span class="top_right fa <?=$marker?>" name="ANTSEG_text_view" id="ANTSEG_text_view"></span>
                                        <table class="" style="" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <th><?php xl('OD','e'); ?></th><td style="width:100px;"></td><th><?php xl('OS','e'); ?></th></td>
                                            </tr>
                                            <tr>
                                                <td><textarea name="ODCONJ" id="ODCONJ" class=" right"><?=$ODCONJ?></textarea></td>
                                                <td style="text-align:center;font-size:0.9em;"><?php xl('Conj','e'); ?> / <?php xl('Sclera','e'); ?></td>
                                                <td><textarea name="OSCONJ" id="OSCONJ" class=""><?=$OSCONJ?></textarea></td>
                                            </tr> 
                                            <tr>
                                                <td><textarea name="ODCORNEA" id="ODCORNEA" class=" right"><?=$ODCORNEA?></textarea></td>
                                                <td style="text-align:center;font-size:0.9em;"><?php xl('Cornea','e'); ?></td>
                                                <td><textarea name="OSCORNEA" id="OSCORNEA" class=""><?=$OSCORNEA?></textarea></td>
                                            </tr> 
                                            <tr>
                                                <td><textarea name="ODAC" id="ODAC" class=" right"><?=$ODAC?></textarea></td>
                                                <td style="text-align:center;font-size:0.9em;"><?php xl('A/C','e'); ?></td>
                                                <td><textarea name="OSAC" id="OSAC" class=""><?=$OSAC?></textarea></td>
                                            </tr>
                                            <tr>
                                                <td><textarea name="ODLENS" id="ODLENS" class=" right"><?=$ODLENS?></textarea></td>
                                                <td style="text-align:center;font-size:0.9em;font-size:0.9em;" class="dropShadow"><?php xl('Lens','e'); ?></td>
                                                <td><textarea name="OSLENS" id="OSLENS" class=""><?=$OSLENS?></textarea></td>
                                            </tr>
                                            <tr>
                                                <td><textarea name="ODIRIS" id="ODIRIS" class="right"><?=$ODIRIS?></textarea></td>
                                                <td style="text-align:center;"><?php xl('Iris','e'); ?></td>
                                                <td><textarea name="OSIRIS" id="OSIRIS" class=""><?=$OSIRIS?></textarea></td>
                                            </tr>
                                        </table>
                                </div>  <br />
                                <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> <b><?php xl('Comments','e'); ?>:</b><br />
                                    <textarea id="ANTSEG_COMMENTS" name="ANTSEG_COMMENTS" style="width:4.0in;height:3.0em;"><?=$ANTSEG_COMMENTS?></textarea>
                                </div>   
                            </div>  
                        </div>
                        <div id="PRIORS_ANTSEG_left_text" style="height: 2.5in;text-align:left;" 
                                 name="PRIORS_ANTSEG_left_text" 
                                 class="text_clinical PRIORS_color PRIORS exam_section_left exam_section_right borderShadow nodisplay">                                     
                                            <i class="fa fa-spinner"></i>
                        </div>
                        <div id="ANTSEG_right" class="exam_section_right borderShadow text_clinical canvas <?=$display_Visibility?>">
                            <div id="DrawAntSeg" dir="LTR" style="display:none;text-align:center;height: 2.5in;">
                                <div class="tools" style="text-align:center;left:0.02in;width:100%;">

                                    <a href="#SketchAntSeg" data-color="#f00" > &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: yellow;" data-color="#ff0" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: lime;" data-color="#0f0" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: aqua;" data-color="#0ff" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: blue;" data-color="#00f" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: fuchsia;" data-color="#f0f" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: black;" data-color="#000" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: white;" data-color="#fff" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="background: #CCC" data-size="3" href="#SketchAntSeg">3</a>

                                    <a style="background: #CCC" data-size="5" href="#SketchAntSeg">5</a>
                                    <a style="background: #CCC" data-size="10" href="#SketchAntSeg">10</a>

                                    <a style="background: #CCC" data-size="15" href="#SketchAntSeg">15</a>  
                                </div>
                                        <!-- Draw: Anterior Segment 
                                        <span id="AntSegOD" value="2" class="AntSegSpan"><?php xl('OD','e'); ?></span>
                                        <span id="AntSegOU" value="1" class="AntSegSpan button_selected"><?php xl('OU','e'); ?></span>
                                        <span id="AntSegOS" value="3" class="AntSegSpan"><?php xl('OS','e'); ?></span>

                                        <br /> -->
                                        <canvas id="SketchAntSeg" class="borderShadow2" style="background: url(../../forms/<?php echo $form_folder; ?>/images/antseg_OU.png) no-repeat center center;background-size: 100% 100%;height:1.8in;width:4.5in;padding:0.1in;margin:0.1in;"></canvas>
                                        <script type="text/javascript">
                                        $(function() {
                                            $('#SketchAntSeg').sketch();
                                        });
                                        </script>
                                        <br />
                            </div>

                            <div id="QPAntSeg" class="" style="text-align:left;height: 2.5in;">
                                <input type="hidden" id="ANTSEG_prefix" name="ANTSEG_prefix" value="">
                                <div style="position:relative;top:0.0in;left:0.00in;margin: auto;">
                                    <span  class="eye_button eye_button_selected" id="ANTSEG_prefix_off" name="ANTSEG_prefix_off"  onclick="$('#ANTSEG_prefix').val('').trigger('change');">Off</span> 
                                    <span  class="eye_button" id="ANTSEG_defaults" name="ANTSEG_defaults"><?php xl('Defaults','e'); ?></span>  
                                    <span  class="eye_button" id="ANTSEG_prefix_no" name="ANTSEG_prefix_no" onclick="$('#ANTSEG_prefix').val('no').trigger('change');"> <?php xl('no','e'); ?> </span>  
                                    <span  class="eye_button" id="ANTSEG_prefix_trace" name="ANTSEG_prefix_trace"  onclick="$('#ANTSEG_prefix').val('trace').trigger('change');"> tr </span>  
                                    <span  class="eye_button" id="ANTSEG_prefix_1" name="ANTSEG_prefix_1"  onclick="$('#ANTSEG_prefix').val('+1').trigger('change');"> <?php xl('+1','e'); ?> </span>  
                                    <span  class="eye_button" id="ANTSEG_prefix_2" name="ANTSEG_prefix_2"  onclick="$('#ANTSEG_prefix').val('+2').trigger('change');"> <?php xl('+2','e'); ?> </span>  
                                    <span  class="eye_button" id="ANTSEG_prefix_3" name="ANTSEG_prefix_3"  onclick="$('#ANTSEG_prefix').val('+3').trigger('change');"> <?php xl('+3','e'); ?> </span>  
                                    <? echo priors_select("ANTSEG",$date,$pid); ?>
                                </div>
                                <div style="float:left;width:40px;text-align:left;">

                                    <span  class="eye_button" id="ANTSEG_prefix_1mm" name="ANTSEG_prefix_1mm"  onclick="$('#ANTSEG_prefix').val('1mm').trigger('change');"> <?php xl('1mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="ANTSEG_prefix_2mm" name="ANTSEG_prefix_2mm"  onclick="$('#ANTSEG_prefix').val('2mm').trigger('change');"> <?php xl('2mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="ANTSEG_prefix_3mm" name="ANTSEG_prefix_3mm"  onclick="$('#ANTSEG_prefix').val('3mm').trigger('change');"> <?php xl('3mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="ANTSEG_prefix_4mm" name="ANTSEG_prefix_4mm"  onclick="$('#ANTSEG_prefix').val('4mm').trigger('change');"> <?php xl('4mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="ANTSEG_prefix_5mm" name="ANTSEG_prefix_5mm"  onclick="$('#ANTSEG_prefix').val('5mm').trigger('change');"> <?php xl('5mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="ANTSEG_prefix_medial" name="ANTSEG_prefix_medial"  onclick="$('#ANTSEG_prefix').val('medial').trigger('change');"><?php xl('med','e'); ?></span>   
                                    <span  class="eye_button" id="ANTSEG_prefix_lateral" name="ANTSEG_prefix_lateral"  onclick="$('#ANTSEG_prefix').val('lateral').trigger('change');"><?php xl('lat','e'); ?></span>  
                                    <span  class="eye_button" id="ANTSEG_prefix_superior" name="ANTSEG_prefix_superior"  onclick="$('#ANTSEG_prefix').val('superior').trigger('change');"><?php xl('sup','e'); ?></span>  
                                    <span  class="eye_button" id="ANTSEG_prefix_inferior" name="ANTSEG_prefix_inferior"  onclick="$('#ANTSEG_prefix').val('inferior').trigger('change');"><?php xl('inf','e'); ?></span> 
                                    <span  class="eye_button" id="ANTSEG_prefix_anterior" name="ANTSEG_prefix_anterior"  onclick="$('#ANTSEG_prefix').val('anterior').trigger('change');"><?php xl('ant','e'); ?></span>  <br /> 
                                    <span  class="eye_button" id="ANTSEG_prefix_mid" name="ANTSEG_prefix_mid"  onclick="$('#ANTSEG_prefix').val('mid').trigger('change');"><?php xl('mid','e'); ?></span>  <br />
                                    <span  class="eye_button" id="ANTSEG_prefix_posterior" name="ANTSEG_prefix_posterior"  onclick="$('#ANTSEG_prefix').val('posterior').trigger('change');"><?php xl('post','e'); ?></span>  <br />
                                    <span  class="eye_button" id="ANTSEG_prefix_deep" name="ANTSEG_prefix_deep"  onclick="$('#ANTSEG_prefix').val('deep').trigger('change');"><?php xl('deep','e'); ?></span> 
                                </div>         
                                <div class="QP_block borderShadow text_clinical " >
                                    <?
                                    $query = "SELECT * FROM dbSelectFindings where id = '3' AND PEZONE = 'ANTSEG' ORDER BY ZONE_ORDER,ordering";

                                    $result = mysql_query($query);
                                    $number_rows=0;
                                    while ($Select_data= mysql_fetch_array($result))   {
                                        $number_rows++;
                                        $string = $Select_data['selection'] ;
                                        $string = (strlen($string) > 12) ? substr($string,0,10).'...' : $string;   
                                        ?> <span>
                                        <a class="underline QP" onclick="fill_QP_field('ANTSEG','OD','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('OD','e'); ?></a> | 
                                        <a class="underline QP" onclick="fill_QP_field('ANTSEG','OS','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('OS','e'); ?></a> | 
                                        <a class="underline QP" onclick="fill_QP_field('ANTSEG','OD','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',2);fill_QP_field('ANTSEG','OS','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('OU','e'); ?></a> </span>
                                        &nbsp;    <?=$Select_data['LOCATION']?>: <?=$string?>

                                        <br />
                                        <? if ($number_rows==15) {  ?>
                                            </div>
                                            <div class="QP_block_outer  borderShadow text_clinical" ><?  
                                            }  if ($number_rows == 30) break;
                                        } 
                                            ?>      
                                </div>  
                            </div>
                        </div>
                    </div>
                    <!-- end Ant Seg -->

                    <!-- start Retina --> 
                    <div id="RETINA_1" class="<?=$display_Add?> clear_both" > 
                        <div id="RETINA_left" class="exam_section_left borderShadow">
                            <span class="closeButton fa fa-plus-square-o" id="MAX_RETINA" name="MAX_RETINA"></span>
                            <div id="RETINA_left_text" style="height: 2.5in;text-align:left;"> 
                                <b><?php xl('Retina','e'); ?>:</b><br />
                                <div style="position:relative;float:right;top:0.2in;">
                                    <table style="float:right;text-align:right;font-size:0.8em;font-weight:bold;padding:10px 0px 5px 10px;">
                                        <tr>
                                            <td>
                                                <?php xl('OCT Report','e'); ?>:
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_file.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_multi.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/jpg.png" class="little_image">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <?php xl('FA/ICG','e'); ?>:
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_file.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_multi.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/jpg.png" class="little_image">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <?php xl('Imaging','e'); ?>:
                                                </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_file.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_multi.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/jpg.png" class="little_image">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <?php xl('Electrophysiology','e'); ?>:
                                                </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_file.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_multi.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/jpg.png" class="little_image">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <?php xl('Extended ophthal','e'); ?>:</td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_file.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/upload_multi.png" class="little_image">
                                            </td>
                                            <td>
                                                <img src="../../forms/<?php echo $form_folder; ?>/images/jpg.png" class="little_image">
                                            </td>
                                        </tr>
                                    </table>
                                    <br />
                                    <table style="width:50%;text-align:right;font-size:0.8em;font-weight:bold;padding:10px;">
                                        <tr style="text-align:center;">
                                            <td></td>
                                            <td> <?php xl('OD','e'); ?></td><td> <?php xl('OS','e'); ?></td></tr>
                                            <td>
                                                <span id="CMT" name="CMT" title="Central Macular Thickness"><?php xl('CMT','e'); ?>:</span>
                                            </td>
                                            <td>
                                                <input name="ODCMT" size="4" id="ODCMT" value="<?=$ODCMT?>">
                                            </td>
                                            <td>
                                                <input name="OSCMT" size="4" id="ODCMT" value="<?=$OSCMT?>">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
      
                                <? ($RETINA_VIEW ==1) ? ($display_RETINA_view = "wide_textarea") : ($display_RETINA_view= "narrow_textarea");?>
                                <? ($display_RETINA_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                                <div>
                                    <div id="RETINA_text_list" name="RETINA_text_list" class="borderShadow  <?=$display_RETINA_view?>">
                                        <span class="top_right fa <?=$marker?>" name="RETINA_text_view" id="RETINA_text_view"></span>
                                        <table  cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <th><?php xl('OD','e'); ?></th><td style="width:100px;"></td><th><?php xl('OS','e'); ?></th></td>
                                                </tr>
                                                <tr>
                                                    <td><textarea name="ODDISC" id="ODDISC" class="right"><?=$ODDISC?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;"><?php xl('Disc','e'); ?></td>
                                                    <td><textarea name="OSDISC" id="OSDISC" class=""><?=$OSDISC?></textarea></td>
                                                </tr> 
                                                <tr>
                                                    <td><textarea name="ODCUP" id="ODCUP" class="right"><?=$ODCUP?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;"><?php xl('Cup','e'); ?></td>
                                                    <td><textarea name="OSCUP" id="OSCUP" class=""><?=$OSCUP?></textarea></td>
                                                </tr> 
                                                <tr>
                                                    <td><textarea name="ODMACULA" id="ODMACULA" class="right"><?=$ODMACULA?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;"><?php xl('Macula','e'); ?></td>
                                                    <td><textarea name="OSMACULA" id="OSMACULA" class=""><?=$OSMACULA?></textarea></td>
                                                </tr>
                                                <tr>
                                                    <td><textarea name="ODVESSELS" id="ODVESSELS" class="right"><?=$ODVESSELS?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;" class=""><?php xl('Vessels','e'); ?></td>
                                                    <td><textarea name="OSVESSELS" id="OSVESSELS" class=""><?=$OSVESSELS?></textarea></td>
                                                </tr>
                                                <tr>
                                                    <td><textarea name="ODPERIPH" id="ODPERIPH" class="right"><?=$ODPERIPH?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;" class=""><?php xl('Periph','e'); ?></td>
                                                    <td><textarea name="OSPERIPH" id="OSPERIPH" class=""><?=$OSPERIPH?></textarea></td>
                                                </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <br />
                            <br />
                            <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> 
                                <b><?php xl('Comments','e'); ?>:</b><br />
                                <textarea id="RETINA_COMMENTS" name="RETINA_COMMENTS" style="width:4.0in;height:3.0em;"><?=$RETINA_COMMENTS?></textarea>
                            </div>  
                        </div>
                        <div id="PRIORS_RETINA_left_text" style="height: 2.5in;text-align:left;" 
                             name="PRIORS_RETINA_left_text" 
                             class="text_clinical PRIORS_color PRIORS exam_section_left exam_section_right borderShadow nodisplay"><i class="fa fa-spinner"></i>
                        </div>
                        <div id="RETINA_right"class="exam_section_right borderShadow text_clinical canvas <?=$display_Visibility?>">
                            <div id="DrawRetina" style="display:none;text-align:center;margin-bottom: 0.2in;height: 2.5in;">
                                <div class="tools" style="text-align:center;left:0.02in;width:100%;">

                                    <a style="width: 5px; background: red;" data-color="#f00" href="#SketchRetina"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: yellow;" data-color="#ff0" href="#SketchRetina"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: lime;" data-color="#0f0" href="#SketchRetina"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: aqua;" data-color="#0ff" href="#SketchRetina"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: blue;" data-color="#00f" href="#SketchRetina"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: fuchsia;" data-color="#f0f" href="#SketchRetina"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: black;" data-color="#000" href="#SketchRetina"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: white;" data-color="#fff" href="#SketchRetina"> &nbsp;&nbsp;</a>

                                    <a style="background: #CCC" data-size="3" href="#SketchRetina">3</a>

                                    <a style="background: #CCC" data-size="5" href="#SketchRetina">5</a>
                                    <a style="background: #CCC" data-size="10" href="#SketchRetina">10</a>

                                    <a style="background: #CCC" data-size="15" href="#SketchRetina">15</a>  
                                </div>
                                <!--
                                     <a href="#SketchRetina" data-download="png" style="float: right; width: 100px;"><?php xl('Download','e'); ?></a>
                                    </div>
                                     <span id="RETINAOD" value="2" class="AntSegSpan"><?php xl('OD','e'); ?></span>
                                    <span id="RETINAOU" value="1" class="AntSegSpan button_selected"><?php xl('OU','e'); ?></span>
                                    <span id="RETINAOS" value="3" class="AntSegSpan"><?php xl('OS','e'); ?></span>
                                    <br />
                                -->
                                <canvas id="SketchRetina" class="borderShadow2" style="background: url(../../forms/<?php echo $form_folder; ?>/images/retina_OU.png) no-repeat center center;background-size: 100% 100%;height:1.8in;width:4.5in;padding:0.1in;margin:0.1in;"></canvas>
                                <script type="text/javascript">
                                $(function() {


                                    $('#SketchRetina').sketch();
                                });
                                </script>
                            </div>
                            
                            <div id="QPRetina" class="" style="text-align:left;height: 2.5in;">
                                <input type="hidden" id="RETINA_prefix" name="RETINA_prefix" value="" />
                                <div style="position:relative;top:0.0in;left:0.00in;margin: auto;">
                                     <span  class="eye_button  eye_button_selected" id="RETINA_prefix_off" name="RETINA_prefix_off"  onclick="$('#RETINA_prefix').val('').trigger('change');"><?php xl('Off','e'); ?></span> 
                                     <span  class="eye_button" id="RETINA_defaults" name="RETINA_defaults"><?php xl('Defaults','e'); ?></span>  
                                     <span  class="eye_button" id="RETINA_prefix_no" name="RETINA_prefix_no" onclick="$('#RETINA_prefix').val('no').trigger('change');"> <?php xl('no','e'); ?> </span>  
                                     <span  class="eye_button" id="RETINA_prefix_trace" name="RETINA_prefix_trace"  onclick="$('#RETINA_prefix').val('trace').trigger('change');"> <?php xl('tr','e'); ?> </span>  
                                     <span  class="eye_button" id="RETINA_prefix_1" name="RETINA_prefix_1"  onclick="$('#RETINA_prefix').val('+1').trigger('change');"> <?php xl('+1','e'); ?> </span>  
                                     <span  class="eye_button" id="RETINA_prefix_2" name="RETINA_prefix_2"  onclick="$('#RETINA_prefix').val('+2').trigger('change');"> <?php xl('+2','e'); ?> </span>  
                                     <span  class="eye_button" id="RETINA_prefix_3" name="RETINA_prefix_3"  onclick="$('#RETINA_prefix').val('+3').trigger('change');"> <?php xl('+3','e'); ?> </span>  
                                     <? echo priors_select("RETINA",$date,$pid); ?>
                                </div>
                                <div style="float:left;width:40px;text-align:left;">

                                    <span  class="eye_button" id="RETINA_prefix_1mm" name="RETINA_prefix_1mm"  onclick="$('#RETINA_prefix').val('1mm').trigger('change');"> <?php xl('1mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="RETINA_prefix_2mm" name="RETINA_prefix_2mm"  onclick="$('#RETINA_prefix').val('2mm').trigger('change');"> <?php xl('2mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="RETINA_prefix_3mm" name="RETINA_prefix_3mm"  onclick="$('#RETINA_prefix').val('3mm').trigger('change');"> <?php xl('3mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="RETINA_prefix_4mm" name="RETINA_prefix_4mm"  onclick="$('#RETINA_prefix').val('4mm').trigger('change');"> <?php xl('4mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="RETINA_prefix_5mm" name="RETINA_prefix_5mm"  onclick="$('#RETINA_prefix').val('5mm').trigger('change');"> <?php xl('5mm','e'); ?> </span>  <br />
                                    <span  class="eye_button" id="RETINA_prefix_medial" name="RETINA_prefix_medial"  onclick="$('#RETINA_prefix').val('medial').trigger('change');"><?php xl('med','e'); ?></span>   
                                    <span  class="eye_button" id="RETINA_prefix_lateral" name="RETINA_prefix_lateral"  onclick="$('#RETINA_prefix').val('lateral').trigger('change');"><?php xl('lat','e'); ?></span>  
                                    <span  class="eye_button" id="RETINA_prefix_superior" name="RETINA_prefix_superior"  onclick="$('#RETINA_prefix').val('superior').trigger('change');"><?php xl('sup','e'); ?></span>  
                                    <span  class="eye_button" id="RETINA_prefix_inferior" name="RETINA_prefix_inferior"  onclick="$('#RETINA_prefix').val('inferior').trigger('change');"><?php xl('inf','e'); ?></span> 
                                    <span  class="eye_button" id="RETINA_prefix_anterior" name="RETINA_prefix_anterior"  onclick="$('#RETINA_prefix').val('anterior').trigger('change');"><?php xl('ant','e'); ?></span>  <br /> 
                                    <span  class="eye_button" id="RETINA_prefix_mid" name="RETINA_prefix_mid"  onclick="$('#RETINA_prefix').val('mid').trigger('change');"><?php xl('mid','e'); ?></span>  <br />
                                    <span  class="eye_button" id="RETINA_prefix_posterior" name="RETINA_prefix_posterior"  onclick="$('#RETINA_prefix').val('posterior').trigger('change');"><?php xl('post','e'); ?></span>  <br />
                                    <span  class="eye_button" id="RETINA_prefix_deep" name="RETINA_prefix_deep"  onclick="$('#RETINA_prefix').val('deep').trigger('change');"><?php xl('deep','e'); ?></span> 
                                </div>         
                                <div class="QP_block borderShadow text_clinical" ><?
                                    $query = "SELECT * FROM dbSelectFindings where id = '3' AND PEZONE = 'RETINA' ORDER BY ZONE_ORDER,ordering";

                                    $result = mysql_query($query);
                                    $number_rows=0;
                                    while ($Select_data= mysql_fetch_array($result))   {

                                        $number_rows++; 
                                        $string = $Select_data['selection'] ;
                                        $string = (strlen($string) > 12) ? substr($string,0,12).'...' : $string;   ?>
                                    <span>
                                        <a class="underline QP" onclick="fill_QP_field('RETINA','OD','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('OD','e'); ?></a>&nbsp;|&nbsp;
                                        <a class="underline QP" onclick="fill_QP_field('RETINA','OS','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('OS','e'); ?></a>&nbsp;|&nbsp;
                                        <a class="underline QP" onclick="fill_QP_field('RETINA','OD','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',2);fill_QP_field('RETINA','OS','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('OU','e'); ?></a>
                                        &nbsp;    <?=$Select_data['LOCATION']?>: <?=$string?>

                                        <br />
                                        <?
                                        if ($number_rows=='15') {
                                            ?>
                                        </div>
                                        <div class="QP_block_outer  borderShadow text_clinical" ><? 
                                    }
                                    if ($number_rows == 30) break;

                                    } ?>     
                                         <br /><br />
                                </div>    
                            </div>
                        </div>
                    </div>
                    <!-- end Retina -->

                    <!-- start Neuro -->
                    <div id="NEURO_1" class="<?=$display_Add?> clear_both"> 
                        <div id="NEURO_left" class="exam_section_left borderShadow">
                            <span class="closeButton fa fa-plus-square-o" id="MAX_NEURO" name="MAX_NEURO"></span>
                            <div id="NEURO_left_text" style="margin:auto 5;min-height: 2.5in;text-align:left;">
                                <b><?php xl('Neuro','e'); ?>:</b><br />
                                <div style="float:left;font-size:0.9em;">
                                    <div id="NEURO_text_list" class="borderShadow" style="border:1pt solid black;float:left;width:165px;text-align:center;margin:2 auto;font-weight:bold;">
                                        <table style="font-size:1.1em;font-weight:600;">
                                            <tr>
                                                <td></td><td style="text-align:center;"><?php xl('OD','e'); ?></td><td style="text-align:center;"><?php xl('OS','e'); ?></td></tr>
                                            <tr>
                                                <td class="right">
                                                    <?php xl('Color','e'); ?>: 
                                                </td>
                                                <td>
                                                    <input type="text"  name="ODCOLOR" id="ODCOLOR" value="<? if ($ODCOLOR) { echo  $ODCOLOR; } else { echo "   /  "; } ?>"/>
                                                </td>
                                                <td>
                                                    <input type="text" name="OSCOLOR" id="OSCOLOR" value="<? if ($OSCOLOR) { echo  $OSCOLOR; } else { echo "   /  "; } ?>"/>
                                                </td>
                                                <td><!-- //Normals may be 11/11 or 15/15.  Need to make a preference here for the user.
                                                    //or just take the normal they use and incorporate that ongoing?
                                                -->
                                                   <span title="<?php xl('Insert normals','e'); ?> - 11/11" class="fa fa-share-square-o fa-flip-horizontal" id="NEURO_COLOR" name="NEURO_COLOR"></span>
                                                &nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td class="right" style="white-space: nowrap;font-size:0.9em;">
                                                    <span title="Variation in red color discrimination between the eyes (eg. OD=100, OS=75)"><?php xl('Red Desat','e'); ?>:</span>
                                                </td>
                                                <td>
                                                    <input type="text" size="6" name="ODREDDESAT" id="ODREDDESAT" value="<?=$ODREDDESAT?>"/> 
                                                </td>
                                                <td>
                                                    <input type="text" size="6" name="OSREDDESAT" id="OSREDDESAT" value="<?=$OSREDDESAT?>"/>
                                                </td>
                                                <td>
                                                   <span title="Insert normals - 100/100" class="fa fa-share-square-o fa-flip-horizontal" id="NEURO_REDDESAT" name="NEURO_REDDESAT"></span>
                                                &nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td class="right" style="white-space: nowrap;">
                                                    <span title="<?php xl('Variation in white (muscle) light brightness discrimination between the eyes (eg. OD=$1.00, OS=$0.75)','e'); ?>"><?php xl('Coins','e'); ?>:</span>
                                                </td>
                                                <td>
                                                    <input type="text" size="6" name="ODCOINS" id="ODCOINS" value="<?=$ODCOINS?>"/> 
                                                </td>
                                                <td>
                                                    <input type="text" size="6" name="OSCOINS" id="OSCOINS" value="<?=$OSCOINS?>"/>
                                                </td>
                                                <td>
                                                   <span title="<?php xl('Insert normals - 100/100'); ?>" class="fa fa-share-square-o fa-flip-horizontal" id="NEURO_COINS" name="NEURO_COINS"></span>
                                                &nbsp;</td>
                                            </tr>
                                           
                                        </table>
                                    </div>
                                   
                                    <div class="borderShadow" style="position:relative;float:right;text-align:center;width:230px;">
                                        <span class="closeButton fa-stack fa-lg">
                                          <i class="fa fa-th fa-fw" id="Close_ACTMAIN" style="right:0.2em;" name="Close_ACTMAIN"></i>
                                        </span>
                                        <table style="position:relative;float:left;font-size:1.2em;width:210px;font-weight:600;"> 
                                            <tr style="text-align:left;height:26px;vertical-align:middle;width:180px;">
                                                <td >
                                                    <span id="ACTTRIGGER" name="ACTTRIGGER"><?php xl('Alternate Cover Test','e'); ?>:</span>
                                                </td>
                                                <td>
                                                    <span id="ACTNORMAL_CHECK" name="ACTNORMAL_CHECK">
                                                    <label for="ACT" class="input-helper input-helper--checkbox"><?php xl('Ortho','e'); ?></label>
                                                    <input type="checkbox" name="ACT" id="ACT" checked="<? if ($ACT =='1') echo "checked"; ?>"></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="text-align:center;"> 
                                                    <div id="ACTMAIN" name="ACTMAIN" class="nodisplay ACT_TEXT" style="position:relative;z-index:1;margin 10 auto;">
                                                       <br /> 

                                                       <table cellpadding="0" style="position:relative;text-align:center;font-size:0.9em;margin: 7 5 19 5;border-collapse: separate;">
                                                            <tr>
                                                                <td id="ACT_tab_SCDIST" name="ACT_tab_SCDIST" class="ACT_selected"> <?php xl('scDist','e'); ?> </td>
                                                                <td id="ACT_tab_CCDIST" name="ACT_tab_CCDIST" class="ACT_deselected"> <?php xl('ccDist','e'); ?> </td>
                                                                <td id="ACT_tab_SCNEAR" name="ACT_tab_SCNEAR" class="ACT_deselected"> <?php xl('scNear','e'); ?> </td>
                                                                <td id="ACT_tab_CCNEAR" name="ACT_tab_CCNEAR" class="ACT_deselected"> <?php xl('ccNear','e'); ?> </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="4" style="text-align:center;font-size:0.8em;"><div id="ACT_SCDIST" name="ACT_SCDIST" class="ACT_box">
                                                                    <br />
                                                                    <table> 
                                                                            <tr> 
                                                                                <td style="text-align:center;"><?php xl('R','e'); ?></td>   
                                                                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                                <textarea id="ACT1SCDIST" name="ACT1SCDIST" class="ACT"><?=$ACT1SCDIST?></textarea></td>
                                                                                <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                                <textarea id="ACT2SCDIST"  name="ACT2SCDIST"class="ACT"><?=$ACT2SCDIST?></textarea></td>
                                                                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                                <textarea id="ACT3SCDIST"  name="ACT3SCDIST" class="ACT"><?=$ACT3SCDIST?></textarea></td>
                                                                                <td style="text-align:center;"><?php xl('L','e'); ?></td> 
                                                                            </tr>
                                                                            <tr>    
                                                                                <td><i class="fa fa-reply rotate-left"></i></td> 
                                                                                <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                                <textarea id="ACT4SCDIST" name="ACT4SCDIST" class="ACT"><?=$ACT4SCDIST?></textarea></td>
                                                                                <td style="border:1pt solid black;text-align:center;">
                                                                                <textarea id="ACTPRIMSCDIST" name="ACTPRIMSCDIST" class="ACT"><?=$ACTPRIMSCDIST?></textarea></td>
                                                                                <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                                <textarea id="ACT6SCDIST" name="ACT6SCDIST" class="ACT"><?=$ACT6SCDIST?></textarea></td>
                                                                                <td><i class="fa fa-share rotate-right"></i></td> 
                                                                            </tr> 
                                                                            <tr> 
                                                                                <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                                                    <textarea id="ACTRTILTSCDIST" name="ACTRTILTSCDIST" class="ACT"><?=$ACTRTILTSCDIST?></textarea></td>
                                                                                <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                                                    <textarea id="ACT7SCDIST" name="ACT7SCDIST" class="ACT"><?=$ACT7SCDIST?></textarea></td>
                                                                                <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                                                    <textarea id="ACT8SCDIST" name="ACT8SCDIST" class="ACT"><?=$ACT8SCDIST?></textarea></td>
                                                                                <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                                                    <textarea id="ACT9SCDIST" name="ACT9SCDIST" class="ACT"><?=$ACT9SCDIST?></textarea></td>
                                                                                <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                                                    <textarea id="ACTLTILTSCDIST" name="ACTLTILTSCDIST" class="ACT"><?=$ACTLTILTSCDIST?></textarea>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                        <br />
                                                                    </div>
                                                                    <div id="ACT_CCDIST" name="ACT_CCDIST" class="nodisplay ACT_box">
                                                                        <br />
                                                                        <table> 
                                                                           <tr> 
                                                                                <td style="text-align:center;"><?php xl('R','e'); ?></td>   
                                                                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                                <textarea id="ACT1CCDIST" name="ACT1CCDIST" class="ACT"><?=$ACT1CCDIST?></textarea></td>
                                                                                <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                                <textarea id="ACT2CCDIST"  name="ACT2CCDIST"class="ACT"><?=$ACT2CCDIST?></textarea></td>
                                                                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                                <textarea id="ACT3CCDIST"  name="ACT3CCDIST" class="ACT"><?=$ACT3CCDIST?></textarea></td>
                                                                                <td style="text-align:center;"><?php xl('L','e'); ?></td> 
                                                                            </tr>
                                                                            <tr>    <td><i class="fa fa-reply rotate-left"></i></td> 
                                                                                <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                                <textarea id="ACT4CCDIST" name="ACT4CCDIST" class="ACT"><?=$ACT4CCDIST?></textarea></td>
                                                                                <td style="border:1pt solid black;text-align:center;">
                                                                                <textarea id="ACTPRIMCCDIST" name="ACTPRIMCCDIST" class="ACT"><?=$ACTPRIMCCDIST?></textarea></td>
                                                                                <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                                <textarea id="ACT6CCDIST" name="ACT6CCDIST" class="ACT"><?=$ACT6CCDIST?></textarea></td>
                                                                                <td><i class="fa fa-share rotate-right"></i></td> 
                                                                            </tr> 
                                                                            <tr> 
                                                                                <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                                                    <textarea id="ACTRTILTCCDIST" name="ACTRTILTCCDIST" class="ACT"><?=$ACTRTILTCCDIST?></textarea></td>
                                                                                <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                                                    <textarea id="ACT7CCDIST" name="ACT7CCDIST" class="ACT"><?=$ACT7CCDIST?></textarea></td>
                                                                                <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                                                    <textarea id="ACT8CCDIST" name="ACT8CCDIST" class="ACT"><?=$ACT8CCDIST?></textarea></td>
                                                                                <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                                                    <textarea id="ACT9CCDIST" name="ACT9CCDIST" class="ACT"><?=$ACT9CCDIST?></textarea></td>
                                                                                <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                                                    <textarea id="ACTLTILTCCDIST" name="ACTLTILTCCDIST" class="ACT"><?=$ACTLTILTCCDIST?></textarea>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                        <br />
                                                                    </div>
                                                                    <div id="ACT_SCNEAR" name="ACT_SCNEAR" class="nodisplay ACT_box">
                                                                        <br />
                                                                        <table> 
                                                                            <tr> 
                                                                                <td style="text-align:center;"><?php xl('R','e'); ?></td>    
                                                                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                                <textarea id="ACT1SCNEAR" name="ACT1SCNEAR" class="ACT"><?=$ACT1SCNEAR?></textarea></td>
                                                                                <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                                <textarea id="ACT2SCNEAR"  name="ACT2SCNEAR"class="ACT"><?=$ACT2SCNEAR?></textarea></td>
                                                                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                                <textarea id="ACT3SCNEAR"  name="ACT3SCNEAR" class="ACT"><?=$ACT3SCNEAR?></textarea></td>
                                                                                <td style="text-align:center;"><?php xl('L','e'); ?></td> 
                                                                            </tr>
                                                                            <tr>    <td><i class="fa fa-reply rotate-left"></i></td> 
                                                                                <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                                <textarea id="ACT4SCNEAR" name="ACT4SCNEAR" class="ACT"><?=$ACT4SCNEAR?></textarea></td>
                                                                                <td style="border:1pt solid black;text-align:center;">
                                                                                <textarea id="ACTPRIMSCNEAR" name="ACTPRIMSCNEAR" class="ACT"><?=$ACTPRIMSCNEAR?></textarea></td>
                                                                                <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                                <textarea id="ACT6SCNEAR" name="ACT6SCNEAR" class="ACT"><?=$ACT6SCNEAR?></textarea></td>
                                                                                <td><i class="fa fa-share rotate-right"></i></td> 
                                                                            </tr> 
                                                                            <tr> 
                                                                                <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                                                    <textarea id="ACTRTILTSCNEAR" name="ACTRTILTSCNEAR" class="ACT"><?=$ACTRTILTSCNEAR?></textarea></td>
                                                                                <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                                                    <textarea id="ACT7SCNEAR" name="ACT7SCNEAR" class="ACT"><?=$ACT7SCNEAR?></textarea></td>
                                                                                <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                                                    <textarea id="ACT8SCNEAR" name="ACT8SCNEAR" class="ACT"><?=$ACT8SCNEAR?></textarea></td>
                                                                                <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                                                    <textarea id="ACT9SCNEAR" name="ACT9SCNEAR" class="ACT"><?=$ACT9SCNEAR?></textarea></td>
                                                                                <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                                                    <textarea id="ACTLTILTSCNEAR" name="ACTLTILTSCNEAR" class="ACT"><?=$ACTLTILTSCNEAR?></textarea>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                        <br />
                                                                    </div>
                                                                    <div id="ACT_CCNEAR" name="ACT_CCNEAR" class="nodisplay ACT_box">
                                                                        <br />
                                                                        <table> 
                                                                            <tr> 
                                                                                <td style="text-align:center;"><?php xl('R','e'); ?></td>    
                                                                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                                <textarea id="ACT1CCNEAR" name="ACT1CCNEAR" class="ACT"><?=$ACT1CCNEAR?></textarea></td>
                                                                                <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                                <textarea id="ACT2CCNEAR"  name="ACT2CCNEAR"class="ACT"><?=$ACT2CCNEAR?></textarea></td>
                                                                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                                <textarea id="ACT3CCNEAR"  name="ACT3CCNEAR" class="ACT"><?=$ACT3CCNEAR?></textarea></td>
                                                                                <td style="text-align:center;"><?php xl('L','e'); ?></td>
                                                                            </tr>
                                                                            <tr>    
                                                                                <td><i class="fa fa-reply rotate-left"></i></td> 
                                                                                <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                                <textarea id="ACT4CCNEAR" name="ACT4CCNEAR" class="ACT"><?=$ACT4CCNEAR?></textarea></td>
                                                                                <td style="border:1pt solid black;text-align:center;">
                                                                                <textarea id="ACTPRIMCCNEAR" name="ACTPRIMCCNEAR" class="ACT"><?=$ACTPRIMCCNEAR?></textarea></td>
                                                                                <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                                <textarea id="ACT6CCNEAR" name="ACT6CCNEAR" class="ACT"><?=$ACT6CCNEAR?></textarea></td><td><i class="fa fa-share rotate-right"></i></td> 
                                                                            </tr> 
                                                                            <tr> 
                                                                                <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                                                    <textarea id="ACTRTILTCCNEAR" name="ACTRTILTCCNEAR" class="ACT"><?=$ACTRTILTCCNEAR?></textarea></td>
                                                                                <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                                                    <textarea id="ACT7CCNEAR" name="ACT7CCNEAR" class="ACT"><?=$ACT7CCNEAR?></textarea></td>
                                                                                <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                                                    <textarea id="ACT8CCNEAR" name="ACT8CCNEAR" class="ACT"><?=$ACT8CCNEAR?></textarea></td>
                                                                                <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                                                    <textarea id="ACT9CCNEAR" name="ACT9CCNEAR" class="ACT"><?=$ACT9CCNEAR?></textarea></td>
                                                                                <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                                                    <textarea id="ACTLTILTCCNEAR" name="ACTLTILTCCNEAR" class="ACT"><?=$ACTLTILTCCNEAR?></textarea>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                       <br />
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <div id="NPCNPA" name="NPCNPA">
                                            <table style="position:relative;float:left;text-align:center;margin: 4 2;width:100%;font-weight:bold;font-size:1.1em;padding:4px;">
                                                <tr style=""><td style="width:50%;"></td><td><?php xl('OD','e'); ?></td><td><?php xl('OS','e'); ?></td></tr>
                                                <tr>
                                                    <td class="right"><span title="Near Point of Accomodation"><?php xl('NPA','e'); ?>:</span></td>
                                                    <td><input type="text" id="ODNPA" style="width:80%;" name="ODNPA" value="<?=$ODNPA?>"></td>
                                                    <td><input type="text" id="OSNPA" style="width:80%;" name="OSNPA" value="<?=$OSNPA?>"></td>
                                                </tr>
                                                <tr>
                                                    <td class="right"><span title="Near Point of Convergence"><?php xl('NPC','e'); ?>:</span></td>
                                                    <td colspan="2" ><input type="text" style="width:85%;" id="NPC" name="NPC" value="<?=$NPC?>">
                                                    </td>
                                                </tr>
                                                 <tr>
                                                    <td class="right">
                                                        <?php xl('Stereopsis','e'); ?>:
                                                    </td>
                                                    <td colspan="2">
                                                        <input type="text" style="width:85%;" name="STEREOPSIS" id="STEREOPSIS" value="<?=$STEREOPSIS?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="right">
                                                        <?php xl('Vertical Fusional Amps','e') ?>:
                                                    </td>
                                                    <td colspan="2">
                                                        <input type="text" style="width:85%;" name="VERTFUSAMPS" id="VERTFUSAMPS" value="<?=$VERTFUSAMPS?>">
                                                        <br />
                                                    </td>
                                                </tr>


                                                <tr><td colspan="3"><br /><u><?php xl('Convergence Amplitudes','e'); ?></u><br /><span style="font-size:0.8em;font-weight:400;">(Breakdown/Recovery in PD)</span></td></tr>
                                                <tr><td ></td><td ><?php xl('Distance','e'); ?></td><td><?php xl('Near','e'); ?></td></tr>
                                                <tr>
                                                    <td style="text-align:right;"><?php xl('w/o correction','e'); ?></td>
                                                    <td><input type="text" id="CASCDIST" name="CASCDIST" value="<?=$CASCDIST?>"></td>
                                                    <td><input type="text" id="CASCNEAR" name="CASCNEAR" value="<?=$CASCNEAR?>"></td></tr>
                                                <tr>
                                                    <td style="text-align:right;"><?php xl('w/ correction','e'); ?></td>
                                                    <td><input type="text" id="CACCDIST" name="CACCDIST" value="<?=$CACCDIST?>"></td>
                                                    <td><input type="text" id="CACCNEAR" name="CACCNEAR" value="<?=$CACCNEAR?>"></td></tr>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div id="NEURO_MOTILITY" class="text_clinical borderShadow" style="float:left;font-size:1.0em;margin:3 auto;font-weight:bold;height:120px;width:165px;">
                                        <div>
                                            <table style="width:100%;margin:0 0 10 0;">
                                                <tr>
                                                    <td style="width:40%;font-size:0.9em;margin:0 auto;font-weight:bold;"><?php xl('Motility','e'); ?>:</td>
                                                    <td style="font-size:0.9em;vertical-align:top;text-align:right;top:0.0in;right:0.1in;height:0px;">
                                                        <label for="MOTILITYNORMAL" class="input-helper input-helper--checkbox"><?php xl('Normal','e'); ?></label>
                                                        <input id="MOTILITYNORMAL" name="MOTILITYNORMAL" type="checkbox" value="1" checked>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <input type="hidden" name="MOTILITY_RS"  id="MOTILITY_RS" value="<?=$MOTILITY_RS?>">
                                        <input type="hidden" name="MOTILITY_RI"  id="MOTILITY_RI" value="<?=$MOTILITY_RI?>">
                                        <input type="hidden" name="MOTILITY_RR"  id="MOTILITY_RR" value="<?=$MOTILITY_RR?>">
                                        <input type="hidden" name="MOTILITY_RL"  id="MOTILITY_RL" value="<?=$MOTILITY_RL?>">
                                        <input type="hidden" name="MOTILITY_LS"  id="MOTILITY_LS" value="<?=$MOTILITY_LS?>">
                                        <input type="hidden" name="MOTILITY_LI"  id="MOTILITY_LI" value="<?=$MOTILITY_LI?>">
                                        <input type="hidden" name="MOTILITY_LR"  id="MOTILITY_LR" value="<?=$MOTILITY_LR?>">
                                        <input type="hidden" name="MOTILITY_LL"  id="MOTILITY_LL" value="<?=$MOTILITY_LL?>">
                                        
                                        <div style="float:left;left:0.4in;text-decoration:underline;"><?php xl('OD','e'); ?></div>
                                        <div style="float:right;right:0.4in;text-decoration:underline;"><?php xl('OS','e'); ?></div><br />
                                        <div class="divTable" style="left:-0.1in;background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 90% 90%;height:0.7in;width:0.7in;padding:1px;margin:6 1 0 0;">
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_4_3" id="MOTILITY_RS_4_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_4_1" id="MOTILITY_RS_4_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_4" id="MOTILITY_RS_4" value="<?=$MOTILITY_RS?>">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_4_2" id="MOTILITY_RS_4_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_4_4" id="MOTILITY_RS_4_4">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_3_1" id="MOTILITY_RS_3_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_3" id="MOTILITY_RS_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_3_2" id="MOTILITY_RS_3_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_2_1" id="MOTILITY_RS_2_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_2" id="MOTILITY_RS_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_2_2" id="MOTILITY_RS_2_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_1_1" id="MOTILITY_RS_1_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_1" id="MOTILITY_RS_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_1_2" id="MOTILITY_RS_1_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_0_1" id="MOTILITY_RS_0_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_0" id="MOTILITY_RS_0">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RS_0_1" id="MOTILITY_RS_0_1">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divMiddleRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RR_4" id="MOTILITY_RR_4" value="<?=$MOTILITY_RR?>">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RR_3" id="MOTILITY_RR_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RR_2" id="MOTILITY_RR_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RR_1" id="MOTILITY_RR_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RR_0" id="MOTILITY_RR_0">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_R0" id="MOTILITY_R0">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RL_0" id="MOTILITY_RL_0">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RL_1" id="MOTILITY_RL_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RL_2" id="MOTILITY_RL_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RL_3" id="MOTILITY_RL_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RL_4" id="MOTILITY_RL_4" value="<?=$MOTILITY_RL?>">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_0_1" id="MOTILITY_RI_0_1">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_RI_0" name="MOTILITY_RI_0">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_0_2" id="MOTILITY_RI_0_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_1_1" id="MOTILITY_RI_1_1">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_RI_1" name="MOTILITY_RI_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_1_2" id="MOTILITY_RI_1_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_2_1" id="MOTILITY_RI_2_1">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_RI_2" name="MOTILITY_RI_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_2_2" id="MOTILITY_RI_2_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_3_5" id="MOTILITY_RI_3_5">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_3_3" id="MOTILITY_RI_3_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_3_1" id="MOTILITY_RI_3_1">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_RI_3" name="MOTILITY_RI_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_3_2" id="MOTILITY_RI_3_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_3_4" id="MOTILITY_RI_3_4">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_3_6" id="MOTILITY_RI_3_6">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_4_5" id="MOTILITY_RI_4_5">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_4_3" id="MOTILITY_RI_4_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_4_1" id="MOTILITY_RI_4_1">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_RI_4" name="MOTILITY_RI_4" value="<?=$MOTILITY_RI?>">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_4_2" id="MOTILITY_RI_4_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_4_4" id="MOTILITY_RI_4_4">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RI_4_6" id="MOTILITY_RI_4_6">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>   
                                            <div class="divRow"><div class="divCell">&nbsp;</div>
                                            </div>
                                        </div> 
                                        <div class="divTable" style="left:-0.1in;background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 90% 90%;height:0.7in;width:0.7in;padding:1px;margin:6 1 0 0;">
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_4_3" id="MOTILITY_LS_4_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_4_1" id="MOTILITY_LS_4_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_4" id="MOTILITY_LS_4" value="<?=$MOTILITY_LS?>">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_4_2" id="MOTILITY_LS_4_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_4_4" id="MOTILITY_LS_4_4">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_3_1" id="MOTILITY_LS_3_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_3" id="MOTILITY_LS_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_3_2" id="MOTILITY_LS_3_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_2_1" id="MOTILITY_LS_2_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_2" id="MOTILITY_LS_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_2_2" id="MOTILITY_LS_2_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_1_1" id="MOTILITY_LS_1_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_1" id="MOTILITY_LS_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_1_2" id="MOTILITY_LS_1_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_0_1" id="MOTILITY_LS_0_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_0" id="MOTILITY_LS_0">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LS_0_1" id="MOTILITY_LS_0_1">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divMiddleRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_4" id="MOTILITY_LR_4" value="<?=$MOTILITY_LR?>">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_3" id="MOTILITY_LR_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_2" id="MOTILITY_LR_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_1" id="MOTILITY_LR_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_0" id="MOTILITY_LR_0">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_L0" id="MOTILITY_L0">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_0" id="MOTILITY_LL_0">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_1" id="MOTILITY_LL_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_2" id="MOTILITY_LL_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_3" id="MOTILITY_LL_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_4" id="MOTILITY_LL_4" value="<?=$MOTILITY_LL?>">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_4_1" id="MOTILITY_LR_4_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_3_1" id="MOTILITY_LR_3_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_2_1" id="MOTILITY_LR_2_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RO_I_1" id="MOTILITY_RO_I_1">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_LI_0" name="MOTILITY_LI_0">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LO_I_1" id="MOTILITY_LO_I_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_2_2" id="MOTILITY_LL_2_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_3_2" id="MOTILITY_LL_3_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_4_2" id="MOTILITY_LL_4_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                             <div class="divRow">
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_4_3" id="MOTILITY_LR_4_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LR_3_3" id="MOTILITY_LR_3_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RO_I_2" id="MOTILITY_RO_I_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_LI_1" name="MOTILITY_LI_1">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LO_I_2" id="MOTILITY_LO_I_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_3_4" id="MOTILITY_LL_3_4">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LL_4_4" id="MOTILITY_LL_4_4">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell" name="MOTILITY_RO_I_3_1" id="MOTILITY_RO_I_3_1">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_RO_I_3" id="MOTILITY_RO_I_3">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_2_1" id="MOTILITY_LI_2_1">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_LI_2" name="MOTILITY_LI_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_2_2" id="MOTILITY_LI_2_2">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LO_I_2" id="MOTILITY_RO_I_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LO_I_3_1" id="MOTILITY_LO_I_3_1">&nbsp;</div>
                                                </div>
                                            <div class="divRow">
                                                <div class="divCell" name="MOTILITY_LO_I_3" id="MOTILITY_RO_I_3">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_3_5" id="MOTILITY_LI_3_5">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_3_3" id="MOTILITY_LI_3_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_3_1" id="MOTILITY_LI_3_1">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_LI_3" name="MOTILITY_LI_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_3_2" id="MOTILITY_LI_3_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_3_4" id="MOTILITY_LI_3_4">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_3_6" id="MOTILITY_LI_3_6">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LO_I_3" id="MOTILITY_LO_I_3">&nbsp;</div>
                                                
                                            </div>
                                            <div class="divRow">
                                                <div class="divCell" name="MOTILITY_RO_I_4" id="MOTILITY_RO_I_4">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_4_5" id="MOTILITY_LI_4_5">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_4_3" id="MOTILITY_LI_4_3">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_4_1" id="MOTILITY_LI_4_1">&nbsp;</div>
                                                <div class="divCell" id="MOTILITY_LI_4" name="MOTILITY_LI_4"  value="<?=$MOTILITY_LI?>">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_4_2" id="MOTILITY_LI_4_2">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_4_4" id="MOTILITY_LI_4_4">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LI_4_6" id="MOTILITY_LI_4_6">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell">&nbsp;</div>
                                                <div class="divCell" name="MOTILITY_LO_I_4" id="MOTILITY_LO_I_4">&nbsp;</div>
                                            </div>   
                                            <div class="divRow"><div class="divCell">&nbsp;</div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            <br />
                            <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> 
                                <b><?php xl('Comments','e'); ?>:</b><br />
                                <textarea id="NEURO_COMMENTS" name="NEURO_COMMENTS" style="width:4.0in;height:3.0em;"><?=$NEURO_COMMENTS?></textarea>
                            </div>     
                        </div>
                        <div id="PRIORS_NEURO_left_text" style="height: 2.5in;text-align:left;" 
                             name="PRIORS_NEURO_left_text" 
                             class="text_clinical PRIORS_color PRIORS exam_section_left exam_section_right borderShadow nodisplay">
                            <i class="fa fa-spinner"></i>
                        </div>
                        <div id="NEURO_right" class="exam_section_right borderShadow  text_clinical canvas <?=$display_Visibility?>">
                            <div id="DrawNeuro" style="display:none;text-align:center;margin-bottom: 0.2in;height: 2.5in;">
                                <div class="tools" style="text-align:center;left:0.02in;width:100%;">

                                    <a style="width: 5px; background: red;" data-color="#f00" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: yellow;" data-color="#ff0" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: lime;" data-color="#0f0" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: aqua;" data-color="#0ff" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: blue;" data-color="#00f" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: fuchsia;" data-color="#f0f" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: black;" data-color="#000" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="width: 5px; background: white;" data-color="#fff" href="#SketchAntSeg"> &nbsp;&nbsp;</a>

                                    <a style="background: #CCC" data-size="3" href="#SketchAntSeg">3</a>

                                    <a style="background: #CCC" data-size="5" href="#SketchAntSeg">5</a>
                                    <a style="background: #CCC" data-size="10" href="#SketchAntSeg">10</a>

                                    <a style="background: #CCC" data-size="15" href="#SketchAntSeg">15</a>  
                                </div>
                                <canvas id="SketchAntSeg" class="borderShadow2" style="background: url(../../forms/<?php echo $form_folder; ?>/images/antseg_OU.png) no-repeat center center;background-size: 100% 100%;height:1.8in;width:4.5in;padding:0.1in;margin:0.1in;"></canvas>
                                <script type="text/javascript">
                                    $(function() {
                                    $('#SketchAntSeg').sketch();
                                });
                                </script>
                                <br />
                            </div>
                            <div id="QPNeuro" style="text-align:left;height: 2.5in;">
                                <input type="hidden" id="NEURO_prefix" name="NEURO_prefix" value="">
                                <div style="position:relative;top:0.0in;left:0.00in;margin: auto;">
                                 <span class="eye_button eye_button_selected" id="NEURO_prefix_off" name="NEURO_prefix_off"  onclick="$('#NEURO_prefix').val('').trigger('change');"><?php xl('Off','e'); ?></span>
                                 <span class="eye_button" id="NEURO_defaults" name="NEURO_defaults"><?php xl('Defaults','e'); ?></span> 
                                 <span class="eye_button" id="NEURO_prefix_no" name="NEURO_prefix_no" onclick="$('#NEURO_prefix').val('no').trigger('change');"> <?php xl('no','e'); ?> </span> 
                                 <span class="eye_button" id="NEURO_prefix_trace" name="NEURO_prefix_trace"  onclick="$('#NEURO_prefix').val('trace').trigger('change');"> <?php xl('tr','e'); ?> </span> 
                                 <span class="eye_button" id="NEURO_prefix_1" name="NEURO_prefix_1"  onclick="$('#NEURO_prefix').val('+1').trigger('change');"> <?php xl('+1','e'); ?> </span> 
                                 <span class="eye_button" id="NEURO_prefix_2" name="NEURO_prefix_2"  onclick="$('#NEURO_prefix').val('+2').trigger('change');"> <?php xl('+2','e'); ?> </span> 
                                 <span class="eye_button" id="NEURO_prefix_3" name="NEURO_prefix_3"  onclick="$('#NEURO_prefix').val('+3').trigger('change');"> <?php xl('+3','e'); ?> </span> 
                                <? echo priors_select("NEURO",$date,$pid); ?>
                             </div>
                            <div style="float:left;width:40px;text-align:left;">

                                <span class="eye_button" id="NEURO_prefix_1mm" name="NEURO_prefix_1mm"  onclick="$('#NEURO_prefix').val('1mm').trigger('change');"> <?php xl('1mm','e'); ?> </span> <br />
                                <span class="eye_button" id="NEURO_prefix_2mm" name="NEURO_prefix_2mm"  onclick="$('#NEURO_prefix').val('2mm').trigger('change');"> <?php xl('2mm','e'); ?> </span> <br />
                                <span class="eye_button" id="NEURO_prefix_3mm" name="NEURO_prefix_3mm"  onclick="$('#NEURO_prefix').val('3mm').trigger('change');"> <?php xl('3mm','e'); ?> </span> <br />
                                <span class="eye_button" id="NEURO_prefix_4mm" name="NEURO_prefix_4mm"  onclick="$('#NEURO_prefix').val('4mm').trigger('change');"> <?php xl('4mm','e'); ?> </span> <br />
                                <span class="eye_button" id="NEURO_prefix_5mm" name="NEURO_prefix_5mm"  onclick="$('#NEURO_prefix').val('5mm').trigger('change');"> <?php xl('5mm','e'); ?> </span> <br />
                                <span class="eye_button" id="NEURO_prefix_medial" name="NEURO_prefix_medial"  onclick="$('#NEURO_prefix').val('medial').trigger('change');"><?php xl('med','e'); ?></span>  
                                <span class="eye_button" id="NEURO_prefix_lateral" name="NEURO_prefix_lateral"  onclick="$('#NEURO_prefix').val('lateral').trigger('change');"><?php xl('lat','e'); ?></span> 
                                <span class="eye_button" id="NEURO_prefix_superior" name="NEURO_prefix_superior"  onclick="$('#NEURO_prefix').val('superior').trigger('change');"><?php xl('sup','e'); ?></span> 
                                <span class="eye_button" id="NEURO_prefix_inferior" name="NEURO_prefix_inferior"  onclick="$('#NEURO_prefix').val('inferior').trigger('change');"><?php xl('inf','e'); ?></span>
                                <span class="eye_button" id="NEURO_prefix_anterior" name="NEURO_prefix_anterior"  onclick="$('#NEURO_prefix').val('anterior').trigger('change');"><?php xl('ant','e'); ?></span>  
                                <span class="eye_button" id="NEURO_prefix_mid" name="NEURO_prefix_mid"  onclick="$('#NEURO_prefix').val('mid').trigger('change');"><?php xl('mid','e'); ?></span> 
                                <span class="eye_button" id="NEURO_prefix_posterior" name="NEURO_prefix_posterior"  onclick="$('#NEURO_prefix').val('posterior').trigger('change');"><?php xl('post','e'); ?></span> 
                                <span class="eye_button" id="NEURO_prefix_deep" name="NEURO_prefix_deep"  onclick="$('#NEURO_prefix').val('deep').trigger('change');"><?php xl('deep','e'); ?></span>
                            </div>         
                            <div id="NEURO_QP_block" name="NEURO_QP_block" class="QP_block borderShadow text_clinical" ><?
                                    $query = "SELECT * FROM dbSelectFindings where id = '3' AND PEZONE = 'NEURO' ORDER BY ZONE_ORDER,ordering";

                                    $result = mysql_query($query);
                                    $number_rows=0;
                                    while ($Select_data= mysql_fetch_array($result))   {

                                        $number_rows++; 
                                        $string = $Select_data['selection'] ;
                                        $string = (strlen($string) > 12) ? substr($string,0,12).'...' : $string;   ?>
                                        <span >
                                            <a class="underline QP" onclick="fill_QP_field('RETINA','OD','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('OD','e'); ?></a>&nbsp;|&nbsp;
                                            <a class="underline QP" onclick="fill_QP_field('RETINA','OS','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('OS','e'); ?></a>&nbsp;|&nbsp;
                                            <a class="underline QP" onclick="fill_QP_field('RETINA','OD','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',2);fill_QP_field('RETINA','OS','<?=$Select_data['LOCATION_text']?>','<?=$Select_data['selection']?>',1);"><?php xl('OU','e'); ?></a>
                                            &nbsp;|&nbsp;
                                        </span>
                                        &nbsp;    <?=$string?>

                                        <br />
                                        <?
                                        if ($number_rows==15) {
                                            ?>
                                            </div>
                                            <div id="NEURO_QP_block2" name="NEURO_QP_block2" class="QP_block_outer  borderShadow text_clinical" ><?
                                             }
                                         if ($number_rows== 30) break;
                                    } ?>     
                                     <br /><br />
                            </div>    
                        </div>
                        </div>   
                    </div>
                    <!-- end Neuro -->
                    <!-- start IMP/PLAN -->    
                    <br />              
                    <div id="LayerClinical" class="section borderShadow" style="border:1pt solid black;vertical-align:text-top;position:relative;float:middle;">
                        <!-- this needs work to integrate it to auto populate with CPT/ICD codes based on form inputs above -->
                        <div id="LyrExamPanel" class="section" >
                            <div id="LayerClinical" class="section" style="height:1.3in;width:175px; " style="vertical-align:text-top;position:relative;">
                               <?php xl('Impression','e'); ?>:
                               <textarea rows=4 id="IMP" name="IMP" style="height:1in;width:90%;"><?=$IMP?></textarea>
                               <?php xl('Plan','e'); ?>/<?php xl('Recommendation','e'); ?>:
                               <textarea rows=4 id="PLAN" name="PLAN" style="width:90%;"><?=$PLAN?></textarea>
                           </div>
                           
                        </div>
                    </div>
                    <br /><br />
                    <!-- END IMP/PLAN -->  
                </div>
                <!-- end of the exam section -->

        
                
        
            </div>
        </div>
        <!-- end container for the main body of the form -->
    </form>

<!-- 
// Printing this and making a report from this are two separate ways to repesent the data and deserve their own forms...
//so leave this out for now
        <input type="button" value="Print" id="PrintButton" />
-->
</body>
<!--  
//saw this in other forms...  Perhaps this belongs in the my_base_js.js file...  
//We dont need to save this form anyway since it is done via ajax already
    <script type="text/javascript">

            $(document).ready(function(){
            $(".save").click(function() { top.restoreSession(); document.eye_mag.submit(); });
            $(".dontsave").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'; });
            // alert("hello <?=$CLINICAL?>");


        });
    </script>
-->

<!-- this is the first attempt at using an openSource html5 sketch program.  Need to work on this ! -->
    <canvas id="simple_sketch" width="800" height="300"></canvas>
    <script type="text/javascript">
        $(function() {
        $('#simple_sketch').sketch();
    });
    </script>
</html>



