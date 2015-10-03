    <?php

    /** 
     * forms/eye_mag/report.php 
     * 
     * Central report form for the eye_mag form.  Here is where all new data for display
     * is created.  New reports are created via new.php and then this script is displayed.
     * Edit are performed in view.php.  Nothing is editable here, but it is scrollable 
     * across time...
     * 
     * Copyright (C) 2015 Raymond Magauran <magauran@MedFetch.com> 
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
    
    include_once("../../globals.php");
    include_once("$srcdir/api.inc");
    include_once("$srcdir/sql.inc");
    require_once("$srcdir/formatting.inc.php");

    $form_name = "eye_mag";
    $form_folder = "eye_mag";

    include_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");
    
    $choice = $_REQUEST['choice'];
    
    if ($_REQUEST['ptid']) $pid = $_REQUEST['ptid'];
    if ($_REQUEST['encid']) $encounter=$_REQUEST['encid'];
    if ($_REQUEST['formid']) $form_id = $_REQUEST['formid'];
    if ($_REQUEST['formname']) $form_name=$_REQUEST['formname'];
    if (!$id) $id=$form_id;
    // Get users preferences, for this user 
    // (and if not the default where a fresh install begins from, or someone else's) 
    $query  = "SELECT * FROM form_eye_mag_prefs where PEZONE='PREFS' AND id=? ORDER BY ZONE_ORDER,ordering";
    $result = sqlStatement($query,array($_SESSION['authUserID']));
    while ($prefs= sqlFetchArray($result))   {    
      @extract($prefs);    
      $$LOCATION = $VALUE; 
    }
    function eye_mag_report($pid, $encounter, $cols, $id, $formname='eye_mag') {
      global $form_folder;
      global $form_name;
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
      * Would a DB VIEW be a better way to access this data?
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
    //var_dump($objQuery);
    $dated = new DateTime($encounter_date);
    $visit_date = $dated->format('m/d/Y'); 
    /*
    There is a global setting for displaying dates...
    If this form only uses visit_date for display purposes then use the global preference above instead.
    */
   // formHeader("Chart: ".$pat_data['fname']." ".$pat_data['lname']." ".$visit_date);

    ?>


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

            /*
            This displays the first two drawings in the encounter page, which calls report.php in main openEMR
            If you want to display something else in this pop-up area, alter this.
            The variable $choice will tell us what to display.
            * @param string $choice options NULL,TEXT,DRAW,NARRATIVE
            * @param string $encounter  encounter number
            * @param string $pid value = patient id
            * @return string returns the HTML old record selector widget for the desired zone 
            */    
            //$choice = 'drawing';    
            if ($_REQUEST['choice']) {  //this shows up on the encounter screen.
              $side="OU";
              $zone = array("HPI","PMH","VISION","NEURO","EXT","ANTSEG","RETINA","IMPPLAN");
                //  for ($i = 0; $i < count($zone); ++$i) {
                //  show only 2 for now in the encounter page
              ($choice =='drawing') ? ($count = count($zone)) : ($count ='2');
              for ($i = 0; $i < $count; ++$i) {
                $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter."/".$side."_".$zone[$i]."_VIEW.png";
                $sql = "SELECT * from documents where url='file://".$file_location."'";
                $doc = sqlQuery($sql);
                if (file_exists($file_location) && ($doc['id'] > '0')) {
                  $filetoshow = $GLOBALS['web_root']."/controller.php?document&retrieve&patient_id=$pid&document_id=$doc[id]&as_file=false";
                  ?><div class='xbordershadow' style='position:relative;float:left;width:100px;height:75px;'>
                  <img src='<?php echo $filetoshow; ?>' width=100 heght=75>
                  </div> <?
                } else {
                       // $filetoshow = "../../forms/".$form_folder."/images/".$side."_".$zone[$i]."_BASE.png?".rand();
                } 
                ?>

                <?php
              }
            } else if ($choice == "drawing") {
              ?>
              <div class="XXXXXborderShadow">
                <?php display_draw_section ("VISION",$encounter,$pid); ?>
              </div>
              <div class="XXborderShadow">

                <?php display_draw_section ("NEURO",$encounter,$pid); ?>
              </div>
              <div class="XXborderShadow">

                <?php display_draw_section ("EXT",$encounter,$pid); ?>
              </div>
              <div class="XXborderShadow">

                <?php display_draw_section ("ANTSEG",$encounter,$pid); ?>
              </div>
              <div class="XXborderShadow">

                <?php display_draw_section ("RETINA",$encounter,$pid); ?>
              </div>
              <div class="XXborderShadow">

                <?php display_draw_section ("IMPPLAN",$encounter,$pid); ?>
              </div>
              <? 
            } else if ($choice !="narrative") {
              narrative($pid, $encounter, $cols, $id);
             //   echo "hello $pid, $encounter, $cols, $form_id";
            }
            ?>
            <?
          }
          function left_overs() {
            /***************************************/
            $count = 0;
            $data = formFetch($table_name, $id);

            if ($data) {
              foreach($data as $key => $value) {
                $$key=$value;
              }
            }
          }


          function narrative($pid, $encounter, $cols, $form_id) {
    // Create a narrative
    // Patient data, Practice Data, Exam Data
            global $form_folder;
    //menu_overhaul_left($pid,$encounter);
     //
            $query="select form_encounter.date as encounter_date,form_encounter.*, form_eye_mag.* from form_eye_mag ,forms,form_encounter 
            where 
            form_encounter.encounter =? and 
            form_encounter.encounter = forms.encounter and 
            form_eye_mag.id=forms.form_id and
            forms.deleted != '1' and 
            form_eye_mag.pid=? ";        

            $encounter_data =sqlQuery($query,array($encounter,$pid));
            @extract($encounter_data);
            ?>
            <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/font-awesome-4.2.0/css/font-awesome.css">
            <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css">  
            <link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
            <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/pure-min.css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/bootstrap-3-2-0.min.css">
            <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/css/bootstrap-responsive.min.css">


            <style>
            h2 {
              font-size:1.3em;
            }
            .title {
              font-size:1em;
              position:absolute;
              right:10px;
              top:30px;
            }

            .refraction_panel {
              display:none;
              width:95%;
              height:auto;
            }

            .refraction_report {
              float: left;
              border: 1.00pt solid #000000; 
              padding: 0.1in; 
              margin: 4px; 
              webkit-box-flex: 0;
              -moz-box-flex: 0;
              box-flex: 0;
              display:inline-block;
              font-size: 1.0em;

            }
            .refraction td {
              text-align:center;
              font-size:0.9em;
              width:0.35in;
              vertical-align: text-middle;
              text-decoration: none;
              padding: 3px;
            }
            .refraction_report th {
              font-size: 1.0em;
              text-align: left;
              padding: 5 0 2 0;
              color: black; 
            }
            .refraction_report ul {
              list-style-type: none;
              margin: 0;
              padding: 0;
              overflow: hidden;
            }

            .refraction li {
              float: left;
            }

            input[type=text] {
              padding: 0.2em 0.4em;
              display: inline-block;
              text-align:center;
              Xwidth:60px;
              font-size:0.7em;
            }
            .refraction_report  b{
              text-decoration:bold;
            }
            .report_text {
              padding:2 10 2 10;
            }
            .label {
              color:black;
              border-radius:none;
            }

            .middle {
              text-align: center;
              font-weight: bold;
            }
            .body2 {
              font-size:0.7em;
              margin:0;
              padding:0;
            }
            .text {
              font-family: Arial;
            }
            .right {
              text-align:right;

            }


            .rdivTable
            {
              position:relative;
              float:left;
    /*background-image: url('../../forms/eye_mag/images/cross.png');
    */ height:100%;
    width:100%;
    border:0pt solid  red;
    border-spacing:0px;
    cursor:sw-resize;
    padding:10 0 0 5;
    display:table;
    border-collapse:collapse;

    /*cellspacing:poor IE support for  this*/
    /* border-collapse:separate;*/
  }

  .rdivRow
  {
   display: table;
   display:table-row;
   border-collapse:collapse;
   border;1pt solid black;
   Xwidth:0.8in;
   Xheight:0.75in;
 }
 .rdivMiddleRow
 {
   display:table-row;
   font-weight: 900;
   border-collapse:collapse;

 }
 .rdivMidCell {
  float:left;/*fix for  buggy browsers*/
  /* display:table-column; */
  text-align:center;
  xborder-top:3px green solid;
  border-collapse:collapse;

}

.rdivCell
{
  float:left;/*fix for  buggy browsers*/
  xdisplay:table-column;
  width:22px;
  height:25px;
  text-align:center;
  font-weight: 900;
  padding:0px;
  border-collapse:collapse;
  color:#000000;   
  display:table-cell;
}
#report_custom .encounter_form {
  padding-bottom:0px;
  margin-bottom:0px;
}

</style>
<body>
  <div class="body2">
    <div style="float:left;left:0px;padding-right:5px;margin-right:15px;width:90px;border-right:2pt grey solid;">

    <?php 
    $PMSFH = build_PMSFH($pid);
    show_PMSFH_report($PMSFH);
    ?> 
    </div> 
    <table >
              <tr>
                    <!-- Begin right column -->
                    <td colspan="2" style="text-align:left;padding-left:10px;font-size:0.7em">
                      <b><?php echo xlt('Chief Complaint'); ?>:</b> &nbsp;<?php echo text($CC1); ?>
                      
                      <b><?php echo xlt('HPI'); ?>:</b>   
                      &nbsp;<?php echo text($HPI1); ?>
                      
                      <div style="padding-left:10px;">
                        <?php 
                        if ($TIMING1) {
                          echo "<i>".xlt('Timing'); ?>:</i>  &nbsp;<?php echo text($TIMING1).""; 
                        }
                        if ($CONTEXT1) {
                          echo "<i>".xlt('Context'); ?>:</i> &nbsp;<?php echo text($CONTEXT1).""; 
                        }
                        if ($SEVERITY1) {
                          echo "<i>".xlt('Severity'); ?>:</i> &nbsp;<?php echo text($SEVERITY1).""; 
                        }
                        if ($MODIFY1) {
                          echo "<i>".xlt('Modifying'); ?>:</i> &nbsp;<?php echo text($MODIFY1)."";
                        }
                        if ($ASSOCIATED1) {
                          echo "<i>".xlt('Associated'); ?>:</i> &nbsp;<?php echo text($ASSOCIATED1).""; 
                        }
                        if ($LOCATION1) {
                          echo "<i>".xlt('Location'); ?>:</i> &nbsp;<?php echo text($LOCATION1)."";
                        }
                        if ($QUALITY1) {
                          echo "<i>".xlt('Quality'); ?>:</i> &nbsp;<?php echo text($QUALITY1)."";
                        }
                        if ($DURATION1) {
                          echo "<i>".xlt('Duration'); ?>:</i> &nbsp;<?php echo text($DURATION1)."";
                        }
                        ?>

                        <?php 
                        if ($CC2) {
                          echo "
                          ";
                          echo "<b>".xlt('Chief Complaint 2'); ?>:</b> &nbsp;<?php echo text($CC2); ?>
                          
                          
                          <div style="padding-left:10px;">
                            <?php 
                            if ($TIMING2) {
                              echo "<i>".xlt('Timing'); ?>:</i>  &nbsp;<?php echo text($TIMING2).""; 
                            }
                            if ($CONTEXT2) {
                              echo "<i>".xlt('Context'); ?>:</i> &nbsp;<?php echo text($CONTEXT2).""; 
                            }
                            if ($SEVERITY2) {
                              echo "<i>".xlt('Severity'); ?>:</i> &nbsp;<?php echo text($SEVERITY2).""; 
                            }
                            if ($MODIFY2) {
                              echo "<i>".xlt('Modifying'); ?>:</i> &nbsp;<?php echo text($MODIFY2)."";
                            }
                            if ($ASSOCIATED2) {
                              echo "<i>".xlt('Associated'); ?>:</i> &nbsp;<?php echo text($ASSOCIATED2).""; 
                            }
                            if ($LOCATION2) {
                              echo "<i>".xlt('Location'); ?>:</i> &nbsp;<?php echo text($LOCATION2)."";
                            }
                            if ($QUALITY2) {
                              echo "<i>".xlt('Quality'); ?>:</i> &nbsp;<?php echo text($QUALITY2)."";
                            }
                            if ($DURATION2) {
                              echo "<i>".xlt('Duration'); ?>:</i> &nbsp;<?php echo text($DURATION2)."";
                            }
                            ?>
                          </div>
                          <?
                        }
                        if ($CC3) {
                          ?>
                          
                          
                          <?php echo "<b>".xlt('Chief Complaint 3'); ?>:</b> &nbsp;<?php echo text($CC3); ?>
                          
                          <?php echo xlt('HPI'); ?>&nbsp; <?php echo text($HPI3); ?>
                          
                          <div style="padding-left:10px;">
                            <?php 
                            if ($TIMING3) {
                              echo "<i>".xlt('Timing'); ?>:</i>  &nbsp;<?php echo text($TIMING3); 
                            }
                            if ($CONTEXT3) {
                              echo "<i>".xlt('Context'); ?>:</i> &nbsp;<?php echo text($CONTEXT3); 
                            }
                            if ($SEVERITY3) {
                              echo "<i>".xlt('Severity'); ?>:</i> &nbsp;<?php echo text($SEVERITY3); 
                            }
                            if ($MODIFY3) {
                              echo "<i>".xlt('Modifying'); ?>:</i> &nbsp;<?php echo text($MODIFY3);
                            }
                            if ($ASSOCIATED3) {
                              echo "<i>".xlt('Associated'); ?>:</i> &nbsp;<?php echo text($ASSOCIATED3); 
                            }
                            if ($LOCATION3) {
                              echo "<i>".xlt('Location'); ?>:</i> &nbsp;<?php echo text($LOCATION3);
                            }
                            if ($QUALITY3) {
                              echo "<i>".xlt('Quality'); ?>:</i> &nbsp;<?php echo text($QUALITY3);
                            }
                            if ($DURATION3) {
                              echo "<i>".xlt('Duration'); ?>:</i> &nbsp;<?php echo text($DURATION3)."";
                            }
                            ?>
                          </div>
                          <div id="tab2_HPI_text" class="nodisplay tab_content" style="min-height: 2.0in;text-align:left;">                 
                          </div>
                          <? 
                        } 
                        ?>
                        
                      </div>
                      <?php if ($CHRONIC1) { ?>
                      <b><?php echo xlt('Chronic or Inactive Problems'); ?>:</b> 
                      &nbsp;<?php echo text($CHRONIC1); 
                      if ($CHRONIC2) echo "&nbsp;".$CHRONIC2;
                      if ($CHRONIC3) echo "&nbsp;".$CHRONIC3;
                      echo "<hr>";
                    } ?>
                    <!-- Start of the Vision box -->   
                    <br />
                    <div style="float:left;border-right:1pt black solid;margin-bottom:20px;font-size:0.9em;">
                      <b class="underline"><?php echo xlt('Visual Acuities'); ?>:</b>   
                      <table id="Additional_VA" cellspacing="2" style="margin:2;text-align:center;font-size:1.0em;">
                        <tr style="font-weight:bold;">
                          <td style="width:50px;text-align:center;"></td>
                          <td style="width:50px;text-align:center;text-decoration:underline;">OD</td>
                          <td style="width:50px;text-align:center;text-decoration:underline;">OS</td>
                        </tr>
                        <? if ($SCODVA||$SCOSVA) { ?>
                        <tr>
                          <td><?php echo xlt('sc'); ?></td>
                          <td><?php echo attr($SCODVA); ?></td>
                          <td><?php echo attr($SCOSVA); ?></td>
                        </tr>
                        <? } if ($WODVA||$WOSVA) { ?>
                        <tr>
                          <td><?php echo xlt('cc'); ?></td>
                          <td><?php echo attr($WODVA); ?></td>
                          <td><?php echo attr($WOSVA); ?></td>
                        </tr>
                        <? } if ($ARODVA||$AROSVA) { ?>
                        <tr>
                          <td><?php echo xlt('AR'); ?></td>
                          <td><?php echo attr($ARODVA); ?></td>
                          <td><?php echo attr($AROSVA); ?></td>
                        </tr>
                        <? } if ($MRODVA||$MROSVA) { ?>
                        <tr>
                          <td><?php echo xlt('MR'); ?></td>
                          <td><?php echo attr($MRODVA); ?></td>
                          <td><?php echo attr($MROSVA); ?></td>
                        </tr>
                        <? } if ($CRODVA||$CROSVA) { ?>
                        <tr>
                          <td><?php echo xlt('CR'); ?></td>
                          <td><?php echo attr($CRODVA); ?></td>
                          <td><?php echo attr($CROSVA); ?></td>
                        </tr>
                        <? } if ($PHODVA||$PHOSVA) { ?>
                        <tr>
                          <td><?php echo xlt('PH'); ?></td>
                          <td><?php echo attr($PHODVA); ?></td>
                          <td><?php echo attr($PHOSVA); ?></td>
                        </tr>
                        <? } if ($CTLODVA||$CTLOSVA) { ?>
                        <tr>
                          <td><?php echo xlt('CTL'); ?></td>
                          <td><?php echo attr($CTLODVA); ?></td>
                          <td><?php echo attr($CTLOSVA); ?></td>
                        </tr>
                        <? } if ($SCNEARODVA||$SCNEAROSVA) { ?>
                        <tr>
                          <td><?php echo xlt('scNear'); ?></td>
                          <td><?php echo attr($SCNEARODVA); ?></td>
                          <td><?php echo attr($SCNEAROSVA); ?></td>
                        </tr>
                        <? } if ($WNEARODVA||$WNEAROSVA) { ?>
                        <tr>
                          <td><?php echo xlt('ccNear'); ?></td>
                          <td><?php echo attr($WNEARODVA); ?></td>
                          <td><?php echo attr($WNEAROSVA); ?></td>
                        </tr>
                        <? } if ($ARNEARODVA||$ARNEAROSVA) { ?>
                        <tr>
                          <td><?php echo xlt('ARNear'); ?></td>
                          <td><?php echo attr($ARNEARODVA); ?></td>
                          <td><?php echo attr($ARNEAROSVA); ?></td>
                        </tr>
                        <? } if ($SCNEARODVA||$SCNEAROSVA) { ?>
                        <tr>
                          <td><?php echo xlt('MRNear'); ?></td>
                          <td><?php echo attr($MRNEARODVA); ?></td>
                          <td><?php echo attr($MRNEAROSVA); ?></td>
                        </tr>
                        <? } if ($SCNEARODVA||$SCNEAROSVA) { ?>
                        <tr>
                          <td><?php echo xlt('PAM'); ?></td>
                          <td><?php echo attr($PAMODVA); ?></td>
                          <td><?php echo attr($PAMOSVA); ?></td>
                        </tr>
                        <? } if ($GLAREODVA||$GLAREOSVA) { ?>
                        <tr>
                          <td><?php echo xlt('Glare'); ?></td>
                          <td><?php echo attr($GLAREODVA); ?></td>
                          <td><?php echo attr($GLAREOSVA); ?></td>
                        </tr>
                        <? } if ($CONTRASTODVA||$CONTRASTOSVA) { ?>
                        <tr>
                          <td><?php echo xlt('Contrast'); ?></td>
                          <td><?php echo attr($CONTRASTODVA); ?></td>
                          <td><?php echo attr($CONTRASTOSVA); ?></td>
                        </tr>
                        <? } ?>
                      </table>
                    </div>

                    <!-- START OF THE PRESSURE BOX -->
                    <div style="position:relative;float:left;text-align:center;padding-left:10px;border-right:1pt solid black;margin-bottom:20px;font-size:0.9em;">    
                      <b class="underline"><?php echo xlt('Intraocular Pressures'); ?>:</b>
                      <table cellspacing="2" style="margin:2;text-align:center;font-size:1.0em;"> 
                        <tr style="font-weight:bold;">
                          <td style="text-align:center;"></td>
                          <td style="text-align:center;text-decoration:underline;">OD</td>
                          <td style="text-align:center;text-decoration:underline;">OS</td>
                        </tr>
                        <?php
                        if ($ODIOPAP||$OSIOPAP) echo "<tr><td style='text-align:right;padding-right:10px;'>App:</td><td style='text-align:center;'>".$ODIOPAP."</td><td style='width:75px;text-align:center;'>".attr($OSIOPAP)."</td></tr>";
                        if ($ODIOPTPN||$OSIOPTPN) echo "<tr><td style='text-align:right;padding-right:10px;'>TPN:</td><td style='text-align:center;'>".$ODIOPTPN."</td><td style='width:75px;text-align:center;'>".attr($OSIOPTPN)."</td></tr>";
                        if ($ODIOPFTN||$OSIOPFTN) echo "<tr><td style='text-align:right;padding-right:10px;'>FTN:</td><td style='text-align:center;'>".$ODIOPFTN."</td><td style='width:75px;text-align:center;'>".attr($OSIOPFTN)."</td></tr>";
                        ?>
                        <tr>
                          <td colspan="3" style="padding:5px;text-align:center;">
                            @ <?php echo attr($IOPTIME); ?>
                          </td>
                        </tr>
                      </table>
                    </div>

                    <!-- START OF THE AMSLER BOX -->
                    <?php if ($AMSLEROD||$AMSLEROS) { ?>
                    <div style="float:left;text-align:center;margin-bottom:20px;" class="<?php if (!$ODAMSLER && !$OSAMSLER) echo "display"; ?>">    
                      <b class="underline"><?php echo xlt('Amsler'); ?>:</b>      
                      <?php 
                      if (!$AMSLEROD) $AMSLEROD= "0";
                      if (!$AMSLEROS) $AMSLEROS= "0";
                      ?> 
                      <table style="font-size:0.7em;">
                        <tr>
                          <td style="text-align:center;"><b><?php echo xlt('OD'); ?></b>
                          </td>
                          <td></td>
                          <td style="text-align:center;"><b><?php echo xlt('OS'); ?></b>
                          </td>
                        </tr>

                        <tr>
                          <td style="text-align:center;">
                            <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?php echo attr($AMSLEROD); ?>.jpg" id="AmslerOD" style="margin:0.05in;height:0.5in;width:0.6in;" />
                            <small><?php echo text($AMSLEROD); ?>/5</small>
                          </td>
                          <td></td>
                          <td style="text-align:center;">
                            <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?php echo attr($AMSLEROS); ?>.jpg" id="AmslerOS" style="margin:0.05in;height:0.5in;width:0.6in;" />
                            <small><?php echo text($AMSLEROS); ?>/5</small>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <?php } ?>
                    <!-- start of the Fields box -->
                    <div style="float:left;text-align:center;padding-left:10px;margin-bottom:20px;" class="">   
                      <b class="underline"><?php echo xlt('Fields'); ?>:</b>
                      <?php 
                                  // if the VF zone is checked, display it
                                  // if ODVF1 = 1 (true boolean) the value="0" checked="true"
                      for ($z=1; $z <5; $z++) {
                        $ODzone = "ODVF".$z;
                        if ($$ODzone =='1') {
                          $ODVF[$z] = '<i class="fa fa-square fa-5"></i>';
                          $bad++;
                        } else {
                          $ODVF[$z] = '<i class="fa fa-square-o fa-5"></i>';
                        }
                        $OSzone = "OSVF".$z;
                        if ($$OSzone =="1") {
                          $OSVF[$z] = '<i class="fa fa-square fa-5"></i>';
                          $bad++;
                        } else {
                         $OSVF[$z] = '<i class="fa fa-square-o fa-5"></i>';
                       }
                     }
                     ?>
                     <?php 
                     if ($bad < '1' ) { 
                      echo "&nbsp;&nbsp;Full OU";
                    } else {
                      ?>
                    <table style="font-size:0.9em;">
                        <tr>
                          <td colspan="2" style="text-align:center;">        
                            <table cellpadding='0' cellspacing="0"> 
                              <tr>    
                                <td style="width:0.5in;text-align:center;" colspan="2"><b><?php echo xlt('OD'); ?></b></td>
                                <td style="width:0.1in;"> </td>
                                <td style="width:0.5in;text-align:center;" colspan="2"><b><?php echo xlt('OS'); ?></b></td>
                              </tr> 
                              <tr>    
                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                  <?php echo $ODVF['1']; ?>
                                </td>
                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                  <?php echo $ODVF['2']; ?>
                                </td>
                                <td></td>
                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                  <?php echo $OSVF['1']; ?>
                                </td>
                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                  <?php echo $OSVF['2']; ?>
                                </td>
                              </tr>       
                              <tr>    
                                <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:center;">
                                  <?php echo $ODVF['3']; ?>
                                </td>
                                <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:center;">
                                  <?php echo $ODVF['4']; ?>
                                </td>
                                <td></td>
                                <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:center;">
                                 <?php echo $OSVF['3']; ?>
                               </td>
                               <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:center;">
                                 <?php echo $OSVF['4']; ?>
                               </td>                    
                             </tr>
                           </table>
                         </td>
                       </tr>
                    </table>  
                     <?php } ?>
                  </div>

                  <div style="float:left;text-align:center;padding-left:10px;margin-bottom:20px;">
                    <? if ($PUPIL_NORMAL =='1') { ?>
                        <b class="underline">Pupils:</b>&nbsp;&nbsp;R + R
                        <?php } elseif ($ODPUPILSIZE1||$OSPUPILSIZE1) { ?>
                      <table cellspacing="2" style="margin:2;text-align:center;font-size:1.0em;">
                      <tr>
                        <!-- start of the Pupils box -->
                        <td>
                          <div style="margin:2;float:left; width:1.75in;padding: 5; border: 1.00pt solid #000000; ">  
                            <b class="underline"><?php echo xlt('Pupils'); ?>:</b> 
                            <div id="Lyr7.0" style="border: none;padding:0 4;">
                              <table cellpadding=1 cellspacing=1 style="font-size: 0.9em;"> 
                                <tr style="font-size: 0.7em;">    
                                  <th style="width:0.4in;"> &nbsp;
                                  </th>
                                  <th style="width:1.0in;padding: 0.1;"><?php echo xlt('size'); ?> (<?php echo xlt('mm'); ?>)
                                  </th>
                                  <th style="width:0.4in;padding: 0.1;"><?php echo xlt('react'); ?> 
                                  </th>
                                  <th style="width:0.4in;padding: 0.1;"><?php echo xlt('APD'); ?>
                                  </th>
                                </tr>
                                <tr>    
                                  <td><b><?php echo xlt('OD'); ?></b>
                                  </td>
                                  <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                    <?php echo attr($ODPUPILSIZE1); ?>
                                    --&gt;
                                    <?php echo attr($ODPUPILSIZE2); ?>
                                  </td>
                                  <td style="text-align:center;border-left:1pt solid black;border-right:1pt solid black;border-bottom:1pt solid black;">
                                    <?php echo attr($ODPUPILREACTIVITY); ?>
                                  </td>
                                  <td style="text-align:center;border-bottom:1pt solid black;">
                                    <?php echo attr($ODAPD); ?>
                                  </td>
                                </tr>
                                <tr>    
                                  <td><b><?php echo xlt('OS'); ?></b>
                                  </td>
                                  <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:center;">
                                    <?php echo attr($OSPUPILSIZE1); ?>
                                    --&gt;
                                    <?php echo attr($OSPUPILSIZE2); ?>
                                  </td>
                                  <td style="text-align:center;border-left:1pt solid black;border-right:1pt solid black;border-top:1pt solid black;">
                                    <?php echo attr($OSPUPILREACTIVITY); ?>
                                  </td>
                                  <td style="text-align:center;border-top:1pt solid black;">
                                    <?php echo attr($OSAPD); ?>
                                  </td>
                                </tr>
                              </table>
                            </div>  
                          </div>
                            <? if ($DIMODPUPILSIZE1||$DIMOSPUPILSIZE1) { ?>
                            <!-- start of slide down pupils_panel --> 
                            <?php (($DIMODPUPILSIZE1) || ($DIMOSPUPILSIZE1)) ? ($display_DP='display') : ($display_DP='nodisplay'); ?>
                            <div id="dim_pupils_panel" class="<?php echo $display_DP; ?>" style="margin:2;float:left; padding: 5; border: 1.00pt solid #000000; ">  
                              <b  class="underline"><?php echo xlt('Pupils') ?>: <?php echo xlt('Dim'); ?></b>
                              <div id="Lyr7.1" style="border: none;padding:0 4;" xstyle="top: 0.4in; left: 0.15in; border: none;padding:5;">
                                <table cellpadding="1" cellpadding="1" style="font-size: 0.9em;text-align:top;"> 
                                  <tr >    
                                    <td></td>
                                    <td style="width:0.5in;padding: 0;font-size: 0.7em;text-align:center;"><?php echo xlt('size'); ?> (<?php echo xlt('mm'); ?>)
                                    </td>
                                    <td rowspan="4" style="padding-left:3px;text-align:bottom;max-width:100px;font-size:0.8em;">
                                      
                                      <?php echo text($PUPIL_COMMENTS); ?>
                                    </td>
                                  </tr>
                                  <tr>    
                                    <td><b><?php echo xlt('OD'); ?></b>
                                    </td>
                                    <td style="text-align:bottom;border-bottom:1pt solid black;padding-left:0.1in;">
                                      <?php echo attr($DIMODPUPILSIZE1); ?>
                                      --&gt;
                                      <?php echo attr($DIMODPUPILSIZE2); ?>
                                    </td>

                                  </tr>
                                  <tr>    
                                    <td ><b><?php echo xlt('OS'); ?></b>
                                    </td>
                                    <td style="border-top:1pt solid black;padding-left:0.1in;">
                                      <?php echo attr($DIMOSPUPILSIZE1); ?>
                                      --&gt;
                                      <?php echo attr($DIMOSPUPILSIZE2); ?>
                                    </td>
                                  </tr>
                                </table>
                              </div>   
                            </div> 
                            <!-- end of slide down pupils_panel --> 
                          
                        </td>
                        <!-- end of the Pupils box -->
                        <? } ?>
                      </tr>
                      </table>
                    <?php } ?>
                  </div>   
                </td>
              </tr>
    </table>

    <!-- start of the refraction boxes -->
    <table style="padding:10;font-size:0.9em;">
      <?php ($WODSPH||$WOSSPH) ? ($display_W = "display") : ($display_W = "nodisplay"); ?>
      <tr class="<?php echo $display_W; ?>">
        <td style="padding:10 0;">
          <table id="wearing" style="padding:10;font-size:1.2em;">
            <tr>
              <td colspan="9" id="wearing_title"><?php echo xlt('Current Glasses'); ?>
              </td>
            </tr>
            <tr style="text-align:center;">
              <td style="width:50px;"></td>
              <td></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Sph'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Cyl'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Axis'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Prism'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Acuity'); ?></td>
              <td rowspan="7" class="right" style="padding:10 0 10 10;font-size:0.6em;">
                <?php echo xlt('Single'); ?>
                <input type="radio" value="0" id="Single" name="RX1" <?php if ($RX1 == '0') echo 'checked="checked"'; ?> />
                <br />
                <?php echo xlt('Bifocal'); ?>
                <input type="radio" value="1" id="Bi" name="RX1" <?php if ($RX1 == '1') echo 'checked="checked"'; ?> />
                <br />
                <?php echo xlt('Trifocal'); ?>
                <input type="radio" value="2" id="Tri" name="RX1" <?php if ($RX1 == '2') echo 'checked="checked"'; ?> />
                <br />
                <?php echo xlt('Prog.'); ?>
                <input type="radio" value="3" id="Prog" name="RX1" <?php if ($RX1 == '3') echo 'checked="checked"'; ?> />
              </td>
            </tr>
            <tr>
              <td rowspan="2" style="font-weight:400;font-size:0.7em;text-align:center;"><?php echo xlt('Dist'); ?></td>    
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OD'); ?>:</td>
              <td><input type=text id="WODSPH" name="WODSPH"  value="<?php echo attr($WODSPH); ?>"></td>
              <td><input type=text id="WODCYL" name="WODCYL"  value="<?php echo attr($WODCYL); ?>"></td>
              <td><input type=text id="WODAXIS" name="WODAXIS" value="<?php echo attr($WODAXIS); ?>"></td>
              <td><input type=text id="WODPRISM" name="WODPRISM" value="<?php echo attr($WODPRISM); ?>"></td>
              <td><input type=text id="WODVA" name="WODVA" value="<?php echo attr($WODVA); ?>"></td>
            </tr>
            <tr>
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OS'); ?>:</td>
              <td><input type=text id="WOSSPH" name="WOSSPH" value="<?php echo attr($WOSSPH); ?>"></td>
              <td><input type=text id="WOSCYL" name="WOSCYL" value="<?php echo attr($WOSCYL); ?>"></td>
              <td><input type=text id="WOSAXIS" name="WOSAXIS" value="<?php echo attr($WOSAXIS); ?>"></td>
              <td><input type=text id="WOSPRISM" name="WOSPRISM" value="<?php echo attr($WOSPRISM); ?>"></td>
              <td><input type=text id="WOSVA" name="WOSVA" value="<?php echo attr($WOSVA); ?>"></td>
            </tr>
            <tr class="WNEAR">
              <td rowspan=2><span style="text-decoration:none;font-weight:400;font-size:0.7em;text-align:center;"><?php echo xlt('Mid'); ?>/<?php echo xlt('Near'); ?></span></td>    
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OD'); ?>:</td>
              <td class="WMid nodisplay"><input type=text id="WODADD1" name="WODADD1" value="<?php echo attr($WODADD1); ?>"></td>
              <td class="WAdd2"><input type=text id="WODADD2" name="WODADD2" value="<?php echo attr($WODADD2); ?>"></td>
              <td class="WHIDECYL"><input type=text id="WNEARODCYL" name="WNEARODCYL" value="<?php echo attr($WNEARODCYL); ?>"></td>
              <td><input type=text id="WNEARODAXIS" name="WNEARODAXIS" value="<?php echo attr($WNEARODAXIS); ?>"></td>
              <td><input type=text id="WNEARODPRISM" name="WODPRISMNEAR" value="<?php echo attr($WNEARODPRISM); ?>"></td>
              <td><input type=text id="WNEARODVA" name="WNEARODVA" value="<?php echo attr($WNEARODVA); ?>"></td>
            </tr>
            <tr class="WNEAR">
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OS'); ?>:</td>
              <td class="WMid nodisplay"><input type=text id="WOSADD1" name="WOSADD1" value="<?php echo attr($WOSADD1); ?>"></td>
              <td class="WAdd2"><input type=text id="WOSADD2" name="WOSADD2" value="<?php echo attr($WOSADD2); ?>"></td>
              <td class="WHIDECYL"><input type=text id="WNEAROSCYL" name="WNEAROSCYL" value="<?php echo attr($WNEAROSCYL); ?>"></td>
              <td><input type=text id="WNEAROSAXIS" name="WNEAROSAXIS" value="<?php echo attr($WNEAROSAXIS); ?>"></td>
              <td><input type=text id="WNEAROSPRISM" name="WNEAROSPRISM" value="<?php echo attr($WNEAROSPRISM); ?>"></td>
              <td><input type=text id="WNEAROSVA" name="WNEAROSVA" value="<?php echo attr($WNEAROSVA); ?>"></td>
            </tr>

            <?php ($WCOMMENTS) ? ($display_WC = "display") : ($display_WC = "nodisplay");?>
            <tr style="top:3.5in;" class="<?php echo $display_WC; ?>">
              <td colspan="6" style="text-align:left;vertical-align:top;top:0px;font-size:0.8em;"><b><?php echo xlt('Comments'); ?>:</b>
                <?php echo text($WCOMMENTS); ?>   
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <?php ($ARODSPH||$AROSSPH) ? ($display_AR = "display") : ($display_AR = "nodisplay");?>
      <tr class="<?php echo $display_AR; ?>">
        <td id="LayerVision_MR" style="padding:10 0;" >
          <table id="autorefraction" style="padding:10;font-size:1.2em;">
            <td colspan="9"><?php echo xlt('Autorefraction'); ?></td>
            <tr>
              <td></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Sph'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Cyl'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Axis'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Acuity'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('ADD'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Jaeger'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Prism'); ?></td>
            </tr>
            <tr>
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OD'); ?>:</td>
              <td><input type=text id="ARODSPH" name="ARODSPH" value="<?php echo attr($ARODSPH); ?>"></td>
              <td><input type=text id="ARODCYL" name="ARODCYL" value="<?php echo attr($ARODCYL); ?>"></td>
              <td><input type=text id="ARODAXIS" name="ARODAXIS" value="<?php echo attr($ARODAXIS); ?>"></td>
              <td><input type=text id="ARODVA" name="ARODVA" value="<?php echo attr($ARODVA); ?>"></td>
              <td><input type=text id="ARODADD" name="ARODADD" value="<?php echo attr($ARODADD); ?>"></td>
              <td><input type=text id="ARNEARODVA" name="ARNEARODVA" value="<?php echo attr($ARNEARODVA); ?>"></td>
              <td><input type=text id="ARODPRISM" name="ARODPRISM" value="<?php echo attr($ARODPRISM); ?>"></td>
            </tr>
            <tr>
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OS'); ?>:</td>
              <td><input type=text id="AROSSPH" name="AROSSPH" value="<?php echo attr($AROSSPH); ?>"></td>
              <td><input type=text id="AROSCYL" name="AROSCYL" value="<?php echo attr($AROSCYL); ?>"></td>
              <td><input type=text id="AROSAXIS" name="AROSAXIS" value="<?php echo attr($AROSAXIS); ?>"></td>
              <td><input type=text id="AROSVA" name="AROSVA" value="<?php echo attr($AROSVA); ?>"></td>
              <td><input type=text id="AROSADD" name="AROSADD" value="<?php echo attr($AROSADD); ?>"></td>
              <td><input type=text id="ARNEAROSVA" name="ARNEAROSVA" value="<?php echo attr($ARNEAROSVA); ?>"></td>
              <td><input type=text id="AROSPRISM" name="AROSPRISM" value="<?php echo attr($AROSPRISM); ?>"></td>
            </tr>
          </table>
        </td>
      </tr>

      <?php ($MRODSPH||$MROSSPH) ? ($display_MR = "display") : ($display_MR = "nodisplay");?>
      <tr class="<?php echo $display_MR; ?>">
        <td style="padding:10 0;">
          <table id="MRrefraction" style="padding:10;font-size:1.2em;">
            <tr><td colspan="7">Manifest (Dry) Refraction</td></tr>
            <tr>
              <td></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Sph'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Cyl'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Axis'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Acuity'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('ADD'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Jaeger'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Prism'); ?></td>

            </tr>
            <tr>
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OD'); ?>:</td>
              <td><input type=text id="MRODSPH" name="MRODSPH" value="<?php echo attr($MRODSPH); ?>"></td>
              <td><input type=text id="MRODCYL" name="MRODCYL" value="<?php echo attr($MRODCYL); ?>"></td>
              <td><input type=text id="MRODAXIS"  name="MRODAXIS" value="<?php echo attr($MRODAXIS); ?>"></td>
              <td><input type=text id="MRODVA"  name="MRODVA" value="<?php echo attr($MRODVA); ?>"></td>
              <td><input type=text id="MRODADD"  name="MRODADD" value="<?php echo attr($MRODADD); ?>"></td>
              <td><input type=text id="MRNEARODVA"  name="MRNEARODVA" value="<?php echo attr($MRNEARODVA); ?>"></td>
              <td><input type=text id="MRODPRISM"  name="MRODPRISM" value="<?php echo attr($MRODPRISM); ?>"></td>
            </tr>
            <tr>
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OS'); ?>:</td>
              <td><input type=text id="MROSSPH" name="MROSSPH" value="<?php echo attr($MROSSPH); ?>"></td>
              <td><input type=text id="MROSCYL" name="MROSCYL" value="<?php echo attr($MROSCYL); ?>"></td>
              <td><input type=text id="MROSAXIS"  name="MROSAXIS" value="<?php echo attr($MROSAXIS); ?>"></td>
              <td><input type=text id="MROSVA"  name="MROSVA" value="<?php echo attr($MROSVA); ?>"></td>
              <td><input type=text id="MROSADD"  name="MROSADD" value="<?php echo attr($MROSADD); ?>"></td>
              <td><input type=text id="MRNEAROSVA"  name="MRNEAROSVA" value="<?php echo attr($MRNEAROSVA); ?>"></td>
              <td><input type=text id="MROSPRISM"  name="MROSPRISM" value="<?php echo attr($MROSPRISM); ?>"></td>
            </tr>
          </table>
        </td>
      </tr>

      <?php ($CRODSPH||$CROSSPH)  ? ($display_Cyclo = "display") : ($display_Cyclo = "nodisplay"); ?>
      <tr>
        <td id="LayerVision_CR" class="<?php echo $display_Cyclo; ?>" style="padding:10px;">
          <table id="cycloplegia" style="padding:10;font-size:1.2em;">
            <td colspan=9><?php echo xlt('Cycloplegic (Wet) Refraction'); ?></td>
            <tr>
              <td></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Sph'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Cyl'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Axis'); ?></td>
              <td style="font-weight:600;font-size:0.7em;text-align:center;"><?php echo xlt('Acuity'); ?></td>
              <td colspan="1" style="font-weight:600;font-size:0.7em;text-align:left;width:60px;">
                <input type="radio" name="WETTYPE" id="Flash" value="Flash" <?php if ($WETTYPE == "Flash") echo "checked='checked'"; ?>/>
                <label for="Flash" class="input-helper input-helper--checkbox"><?php echo xlt('Flash'); ?></label>
              </td>
              <td colspan="2" rowspan="4" style="text-align:left;width:75px;font-size:0.6em;"><b style="text-align:center;width:70px;text-decoration:underline;"><?php echo xlt('Dilated with'); ?>:</b>
                <input type="checkbox" id="CycloMydril" name="CYCLOMYDRIL" value="Cyclomydril" <?php if ($CYCLOMYDRIL == 'Cyclomydril') echo "checked='checked'"; ?> />
                <label for="CycloMydril" class="input-helper input-helper--checkbox"><?php echo xlt('CycloMydril'); ?></label>

                <input type="checkbox" id="Tropicamide" name="TROPICAMIDE" value="Tropicamide 2.5%" <?php if ($TROPICAMIDE == 'Tropicamide 2.5%') echo "checked='checked'"; ?> />
                <label for="Tropicamide" class="input-helper input-helper--checkbox"><?php echo xlt('Tropic 2.5%'); ?></label>

                <input type="checkbox" id="Neo25" name="NEO25" value="Neosynephrine 2.5%"  <?php if ($NEO25 =='Neosynephrine 2.5%') echo "checked='checked'"; ?> />
                <label for="Neo25" class="input-helper input-helper--checkbox"><?php echo xlt('Neo 2.5%'); ?></label>

                <input type="checkbox" id="Cyclogyl" name="CYCLOGYL" value="Cyclopentolate 1%"  <?php if ($CYCLOGYL == 'Cyclopentolate 1%') echo "checked='checked'"; ?> />
                <label for="Cyclogyl" class="input-helper input-helper--checkbox"><?php echo xlt('Cyclo 1%'); ?></label>

                <input type="checkbox" id="Atropine" name="ATROPINE" value="Atropine 1%"  <?php if ($ATROPINE == 'Atropine 1%') echo "checked='checked'"; ?> />
                <label for="Atropine" class="input-helper input-helper--checkbox"><?php echo xlt('Atropine 1%'); ?></label>

              </td>
            </tr>
            <tr>
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OD'); ?>:</td>
              <td><input type=text id="CRODSPH" name="CRODSPH" value="<?php echo attr($CRODSPH); ?>"></td>
              <td><input type=text id="CRODCYL" name="CRODCYL" value="<?php echo attr($CRODCYL); ?>"></td>
              <td><input type=text id="CRODAXIS" name="CRODAXIS" value="<?php echo attr($CRODAXIS); ?>"></td>
              <td><input type=text id="CRODVA" name="CRODVA"  value="<?php echo attr($CRODVA); ?>"></td>
              <td colspan="1" style="font-weight:600;font-size:0.7em;text-align:left;">
                <input type="radio" name="WETTYPE" id="Auto" value="Auto" <?php if ($WETTYPE == "Auto") echo "checked='checked'"; ?>>
                <label for="Auto" class="input-helper input-helper--checkbox"><?php echo xlt('Auto'); ?></label>
              </td>
            </tr>
            <tr>
              <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('OS'); ?>:</td>
              <td><input type=text id="CROSSPH" name="CROSSPH" value="<?php echo attr($CROSSPH); ?>"></td>
              <td><input type=text id="CROSCYL" name="CROSCYL" value="<?php echo attr($CROSCYL); ?>"></td>
              <td><input type=text id="CROSAXIS" name="CROSAXIS" value="<?php echo attr($CROSAXIS); ?>"></td>
              <td><input type=text id="CROSVA" name="CROSVA" value="<?php echo attr($CROSVA); ?>"></td>
              <td colspan="1" style="font-weight:600;font-size:0.7em;text-align:left;">
                <input type="radio" name="WETTYPE" id="Manual" value="Manual" <?php if ($WETTYPE == "Manual") echo "checked='checked'"; ?>>
                <label for="Manual" class="input-helper input-helper--checkbox"><?php echo xlt('Manual'); ?></label>
              </td>
            </tr>
            <tr>
              <td colspan="5" style="vertical-align:text-top;">
                <input type="checkbox" id="DIL_RISKS" name="DIL_RISKS" value="on" <?php if ($DIL_RISKS =='on') echo "checked='checked'"; ?>>
                <label for="DIL_RISKS" class="input-helper input-helper--checkbox"><?php echo xlt('Dilation risks reviewed'); ?></label>
              </td>
              <td colspan="1" style="font-weight:600;font-size:0.5em;text-align:left;">
                <input type="checkbox" name="BALANCED" id="Balanced" value="on" <?php if ($BALANCED =='on') echo "checked='checked'"; ?>>
                <label for="Balanced" class="input-helper input-helper--checkbox"><?php echo xlt('Balanced'); ?></label>
              </td>
            </tr>
            <tr class="<?php if (!$CRCOMMENTS) echo "nodisplay"; ?>">
              <td colspan="6" style="vertical-align:bottom;text-align:left;font-size:0.8em;"><b><?php echo xlt('Comments'); ?>:</b></br />
                <?php echo attr($CRCOMMENTS); ?>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <?php ($CTLODSPH||$CTLOSSPH) ? ($display_CTL = "display") : ($display_CTL = "nodisplay"); ?>
      <tr>
        <td id="LayerVision_CTL" class="<?php echo $display_CTL; ?>" style="padding:10px;">
          <table id="CTL" style="padding:10;font-size:1.2em;">
            <td colspan="9"><?php echo xlt('Contact Lens Refraction'); ?></td>
            <tr>
              <td style="text-align:center;">
                <div style="box-shadow: 1px 1px 2px #888888;border-radius: 8px; margin: 5 auto; position:inline-block; Xpadding: 0.02in; border: 1.00pt solid #000000; ">
                  <table>
                    <tr>
                      <td></td>
                      <td><?php echo xlt('Manufacturer'); ?></td>
                      <td><?php echo xlt('Supplier'); ?></td>
                      <td><?php echo xlt('Brand'); ?></td>
                    </tr>
                    <tr>
                      <td><b><?php echo xlt('OD'); ?>:</b></td>
                      <td>
                        <!--  these will need to be pulled from a CTL specific table probably -->
                        <select id="CTLMANUFACTUREROD" name="CTLMANUFACTUREROD">
                          <option></option>
                          <option value="BL"><?php echo xlt('Bausch and Lomb'); ?></option>
                          <option value="JNJ"><?php echo xlt('JNJ'); ?></option>
                        </select>
                      </td>
                      <td>
                        <select id="CTLSUPPLIEROD" name="CTLMANUFACTUREROD">
                          <option></option>
                          <option value="ABB"><?php echo xlt('ABB'); ?></option>
                          <option value="JNJ"><?php echo xlt('JNJ'); ?></option>
                        </select>
                      </td>
                      <td>
                        <select id="CTLBRANDOD" name="CTLBRANDOD">
                          <option></option>
                          <option value="Accuvue"><?php echo xlt('Accuvue'); ?></option>
                          <option value="ExtremeH2O"><?php echo xlt('Extreme H2O'); ?></option>
                        </select>
                      </td>
                    </tr>
                    <tr >
                      <td><b><?php echo xlt('OS'); ?>:</b></td>
                      <td>
                        <select id="CTLMANUFACTUREROS" name="CTLMANUFACTUREROS">
                          <option></option>
                          <option value="BL"><?php echo xlt('Bausch and Lomb'); ?></option>
                          <option value="JNJ"><?php echo xlt('JNJ'); ?></option>
                        </select>
                      </td>
                      <td>
                        <select id="CTLSUPPLIEROS" name="CTLSUPPLIEROS">
                          <option></option>
                          <option value="ABB"><?php echo xlt('ABB'); ?></option>
                          <option value="JNJ"><?php echo xlt('JNJ'); ?></option>
                        </select>
                      </td>
                      <td>
                        <select id="CTLBRANDOS" name="CTLBRANDOS">
                          <option></option>
                          <option value="Accuvue"><?php echo xlt('Accuvue'); ?></option>
                          <option value="ExtremeH2O"><?php echo xlt('Extreme H2O'); ?></option>
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
              <td><?php echo xlt('Sph'); ?></td>
              <td><?php echo xlt('Cyl'); ?></td>
              <td><?php echo xlt('Axis'); ?></td>
              <td><?php echo xlt('BC'); ?></td>
              <td><?php echo xlt('Diam'); ?></td>
              <td><?php echo xlt('ADD'); ?></td>
              <td><?php echo xlt('Acuity'); ?></td>
            </tr>
            <tr>
              <td><b><?php echo xlt('OD'); ?>:</b></td>
              <td><input type=text id="CTLODSPH" name="CTLODSPH" value="<?php echo attr($CTLODSPH); ?>"></td>
              <td><input type=text id="CTLODCYL" name="CTLODCYL" value="<?php echo attr($CTLODCYL); ?>"></td>
              <td><input type=text id="CTLODAXIS" name="CTLODAXIS" value="<?php echo attr($CTLODAXIS); ?>"></td>
              <td><input type=text id="CTLODBC" name="CTLODBC" value="<?php echo attr($CTLODBC); ?>"></td>
              <td><input type=text id="CTLODDIAM" name="CTLODDIAM" value="<?php echo attr($CTLODDIAM); ?>"></td>
              <td><input type=text id="CTLODADD" name="CTLODADD" value="<?php echo attr($CTLODADD); ?>"></td>
              <td><input type=text id="CTLODVA" name="CTLODVA" value="<?php echo attr($CTLODVA); ?>"></td>
            </tr>
            <tr >
              <td><b><?php echo xlt('OS'); ?>:</b></td>
              <td><input type=text id="CTLOSSPH" name="CTLOSSPH" value="<?php echo attr($CTLOSSPH); ?>"></td>
              <td><input type=text id="CTLOSCYL" name="CTLOSCYL" value="<?php echo attr($CTLOSCYL); ?>"></td>
              <td><input type=text id="CTLOSAXIS" name="CTLOSAXIS" value="<?php echo attr($CTLOSAXIS); ?>"></td>
              <td><input type=text id="CTLOSBC" name="CTLOSBC" value="<?php echo attr($CTLOSBC); ?>"></td>
              <td><input type=text id="CTLOSDIAM" name="CTLOSDIAM" value="<?php echo attr($CTLOSDIAM); ?>"></td>
              <td><input type=text id="CTLOSADD" name="CTLOSADD" value="<?php echo attr($CTLOSADD); ?>"></td>
              <td><input type=text id="CTLOSVA" name="CTLOSVA" value="<?php echo attr($CTLOSVA); ?>"></td>
            </tr>
          </table>
        </td>
      </tr>

      <?php ($PHODVA||$GLAREODVA||$CONTRASTODVA||$ODK1||$ODK2||$LIODVA||$PAMODBA) ? ($display_Add = "nodisplay") : ($display_Add = "nodisplay"); ?>
      <tr>
        <td id="LayerVision_ADDITIONAL" class="refraction <?php echo $display_Add; ?>" style="padding:10px;">
          <table id="Additional" style="padding:10;font-size:1.2em;">
            <tr><td colspan=9><?php echo xlt('Additional Data Points'); ?></td></tr>
            <tr><td></td>
              <td><?php echo xlt('PH'); ?></td>
              <td><?php echo xlt('PAM'); ?></td>
              <td><?php echo xlt('LI'); ?></td>
              <td><?php echo xlt('BAT'); ?></td>
              <td><?php echo xlt('K1'); ?></td>
              <td><?php echo xlt('K2'); ?></td>
              <td><?php echo xlt('Axis'); ?></td>
            </tr>
            <tr><td><b><?php echo xlt('OD'); ?>:</b></td>
              <td><input type=text id="PHODVA" name="PHODVA" value="<?php echo attr($PHODVA); ?>"></td>
              <td><input type=text id="PAMODVA" name="PAMODVA" value="<?php echo attr($PAMODVA); ?>"></td>
              <td><input type=text id="LIODVA" name="LIODVA"  title="test" value="<?php echo attr($LIODVA); ?>"></td>
              <td><input type=text id="GLAREODVA" name="GLAREODVA" value="<?php echo attr($GLAREODVA); ?>"></td>
              <td><input type=text id="ODK1" name="ODK1" value="<?php echo attr($ODK1); ?>"></td>
              <td><input type=text id="ODK2" name="ODK2" value="<?php echo attr($ODK2); ?>"></td>
              <td><input type=text id="ODK2AXIS" name="ODK2AXIS" value="<?php echo attr($ODK2AXIS); ?>"></td>
            </tr>
            <tr>
              <td><b><?php echo xlt('OS'); ?>:</b></td>
              <td><input type=text id="PHOSVA" name="PHOSVA" value="<?php echo attr($PHOSVA); ?>"></td>
              <td><input type=text id="PAMOSVA" name="PAMOSVA" value="<?php echo attr($PAMOSVA); ?>"></td>
              <td><input type=text id="LIOSVA" name="LIOSVA" value="<?php echo attr($LIOSVA); ?>"></td>
              <td><input type=text id="GLAREOSVA" name="GLAREOSVA" value="<?php echo attr($GLAREOSVA); ?>"></td>
              <td><input type=text id="OSK1" name="OSK1" value="<?php echo attr($OSK1); ?>"></td>
              <td><input type=text id="OSK2" name="OSK2" value="<?php echo attr($OSK2); ?>"></td>
              <td><input type=text id="OSK2AXIS" name="OSK2AXIS" value="<?php echo attr($OSK2AXIS); ?>"></td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
              <td></td>
              <td><?php echo xlt('AxLength'); ?></td>
              <td><?php echo xlt('ACD'); ?></td>
              <td><?php echo xlt('PD'); ?></td>
              <td><?php echo xlt('LT'); ?></td>
              <td><?php echo xlt('W2W'); ?></td>
              <td><?php echo xlt('ECL'); ?></td>
              <!-- <td><?php echo xlt('pend'); ?></td> -->
            </tr>
            <tr><td><b><?php echo xlt('OD'); ?>:</b></td>
              <td><input type=text id="ODAXIALLENGTH" name="ODAXIALLENGTH"  value="<?php echo attr($ODAXIALLENGTH); ?>"></td>
              <td><input type=text id="ODACD" name="ODACD"  value="<?php echo attr($ODACD); ?>"></td>
              <td><input type=text id="ODPDMeasured" name="ODPDMeasured"  value="<?php echo attr($ODPDMeasured); ?>"></td>
              <td><input type=text id="ODLT" name="ODLT"  value="<?php echo attr($ODLT); ?>"></td>
              <td><input type=text id="ODW2W" name="ODW2W"  value="<?php echo attr($ODW2W); ?>"></td>
              <td><input type=text id="ODECL" name="ODECL"  value="<?php echo attr($ODECL); ?>"></td>
              <!-- <td><input type=text id="pend" name="pend"  value="<?php echo attr($pend); ?>"></td> -->
            </tr>
            <tr>
              <td><b><?php echo xlt('OS'); ?>:</b></td>
              <td><input type=text id="OSAXIALLENGTH" name="OSAXIALLENGTH" value="<?php echo attr($OSAXIALLENGTH); ?>"></td>
              <td><input type=text id="OSACD" name="OSACD" value="<?php echo attr($OSACD); ?>"></td>
              <td><input type=text id="OSPDMeasured" name="OSPDMeasured" value="<?php echo attr($OSPDMeasured); ?>"></td>
              <td><input type=text id="OSLT" name="OSLT" value="<?php echo attr($OSLT); ?>"></td>
              <td><input type=text id="OSW2W" name="OSW2W" value="<?php echo attr($OSW2W); ?>"></td>
              <td><input type=text id="OSECL" name="OSECL" value="<?php echo attr($OSECL); ?>"></td>
              <!--  <td><input type=text id="pend" name="pend" value="<?php echo attr($pend); ?>"></td> -->
            </tr>
          </table>
        </td>
      </tr>  
    </table>
    
    <!-- end of the refraction boxes -->  

    <table style="font-size:1em;" >
        <tr style="border-bottom:1pt grey dashed;">
          <td style="text-align:center;padding:1px;vertical-align:top;">           
            <!-- start of external exam -->
            <div style="text-align:left;border-right:1pt grey dashed;border-top:1pt grey dashed;padding:5 2;">
              <b><u><?php echo xlt('External Exam'); ?>:</u></b>
              <table style="width:2.5in;padding:5px;font-size:1.1em;">
                <tr>
                  <td class="bold" style="text-align:center;text-decoration:underline;width:200px;"><?php echo xlt('Right'); ?></td>
                  <td style="width:100px;"></td>
                  <td class="bold" style="text-align:center;text-decoration:underline;width:200px;"><?php echo xlt('Left'); ?></td>
                </tr>
                <tr>
                  <td class="report_text right"><?php echo text($RBROW); ?></td>
                  <td class="middle"><?php echo xlt('Brow'); ?></td>
                  <td class="report_text"><?php echo text($LBROW); ?></td>
                </tr> 
                <tr>
                  <td class="report_text right "><?php echo text($RUL); ?></td>
                  <td class="middle"><?php echo xlt('Upper Lids'); ?></td>
                  <td class="report_text"><?php echo text($LUL); ?></td>
                </tr> 
                <tr>
                  <td class="report_text right "><?php echo text($RLL); ?></td>
                  <td class="middle"><?php echo xlt('Lower Lids'); ?></td>
                  <td class="report_text"><?php echo text($LLL); ?></td>
                </tr>
                <tr>
                  <td class="report_text right "><?php echo text($RMCT); ?></td>
                  <td class="middle"><?php echo xlt('Medial Canthi'); ?></td>
                  <td class="report_text"><?php echo text($LMCT); ?></td>
                </tr>
                  <?php 
                  if ($RADNEXA || $LADNEXA) { 
                  ?> 
                <tr>
                    <td class="report_text right"><?php echo text($RADNEXA); ?></td>
                    <td class="middle"><?php echo xlt('Adnexa'); ?></td>
                    <td class="report_text"><?php echo text($LADNEXA); ?></td>
                </tr>
                  <?php  } ?>

                <tr class="<?php if (!$EXT_COMMENTS) echo "nodisplay"; ?>">
                  <td colspan="3" style="font-size:1.0em;">

                    <b><?php echo xlt('Comments'); ?>:</b>
                    <span style="width:2.5in;height:3.0em;">
                      <?php echo text($EXT_COMMENTS); ?>
                    </span>
                  </td>
                </tr>
              </table>
            </div>
              <?php 
              display_draw_image ("EXT",$encounter,$pid); 
              ?>           
              <!-- end of external exam -->
          </td>   
          <td  style="text-align:center;padding:1px;vertical-align:top;max-width:40%;">
            <!-- start of Anterior Segment exam -->
              <div style="text-align:left;padding:5 2;border-top:1pt grey dashed;">
                <b><u><?php echo xlt('Anterior Segment'); ?></u>:</b>
                <table style="width:2.5in;padding:5px;font-size:1.1em;">
                  <tr>
                    <td class="bold" style="text-align:center;text-decoration:underline;width:200px;"><?php echo xlt('Right'); ?></td>
                    <td style="width:100px;"></td>
                    <td class="bold" style="text-align:center;text-decoration:underline;width:200px;"><?php echo xlt('Left'); ?></td>
                  </tr>
                  <tr>
                    <td class="report_text right"><?php echo text($ODCONJ); ?></td>
                    <td class="middle"><?php echo xlt('Conj'); ?></td>
                    <td class="report_text"><?php echo text($OSCONJ); ?></td>
                  </tr> 
                  <tr>
                    <td class="report_text right "><?php echo text($ODCORNEA); ?></td>
                    <td  class="middle"><?php echo xlt('Cornea'); ?></td>
                    <td class="report_text"><?php echo text($OSCORNEA); ?></td>
                  </tr> 
                  <tr>
                    <td class="report_text right "><?php echo text($ODAC); ?></td>
                    <td class="middle"><?php echo xlt('A/C'); ?></td>
                    <td class="report_text"><?php echo text($OSAC); ?></td>
                  </tr>
                  <tr>
                    <td class="report_text right "><?php echo text($ODLENS); ?></td>
                    <td class="middle"><?php echo xlt('Lens'); ?></td>
                    <td class="report_text"><?php echo text($OSLENS); ?></td>
                  </tr>
                  <tr>
                    <td class="report_text right"><?php echo text($ODIRIS); ?></td>
                    <td class="middle"><?php echo xlt('Iris'); ?></td>
                    <td class="report_text"><?php echo text($OSIRIS); ?></td>
                  </tr>
                  <?php if ($ODGONIO||$OSDGONIO) { ?>
                  <tr>
                    <td class="report_text right" style="width:100px;"><?php echo $ODGONIO; ?></td>
                    <td class="middle"><?php echo xlt('Gonioscopy'); ?></td>
                    <td class="report_text" style="width:100px;"><?php echo text($OSGONIO); ?></td>
                  </tr> 
                  <?php } if ($ODKTHICKNESS||$OSKTHICKNESS) { ?>
                  <tr>
                    <td class="report_text right"><?php echo text($ODKTHICKNESS); ?></td>
                    <td class="middle" title="<?php echo xla('Pachymetry'); ?>"><?php echo xlt('Pachymetry'); ?></td>
                    <td  class="report_text"><?php echo text($OSKTHICKNESS); ?></td>
                  </tr>
                  <?php } if ($ODSCHIRMER1||$OSSCHIRMER1) { ?>
                  <tr>
                    <td class="report_text right"><?php echo attr($ODSCHIRMER1); ?></td>
                    <td class="middle" title="<?php echo xla('Schirmers I'); ?>"><?php echo xlt('Schirmers I'); ?></td>
                    <td class="report_text"><?php echo attr($OSSCHIRMER1); ?></td>
                  </tr>
                  <?php } if ($ODSCHIRMER2||$OSSCHIRMER2) { ?>
                  <tr>
                    <td class="report_text right"><?php echo attr($ODSCHIRMER2); ?></td>
                    <td class="middle" title="<?php echo xla('Schirmers II'); ?>"><?php echo xlt('Schirmers II'); ?></td>
                    <td class="report_text"><?php echo attr($OSSCHIRMER2); ?></td>
                  </tr>
                  <?php } if ($ODTBUT||$OSTBUT) { ?>
                  <tr>
                    <td class="report_text right"><?php echo attr($ODTBUT); ?></td>
                    <td class="middle" title="<?php echo xla('Tear Break Up Time'); ?>"><?php echo xlt('TBUT'); ?></td>
                    <td class="report_text"><?php echo attr($OSTBUT); ?></td>
                  </tr>
                  <?php }  ?>
                  <tr class="<?php if (!$ANTSEG_COMMENTS) echo "nodisplay"; ?>">
                    <td colspan="2" style="font-size:0.7em;">

                      <b><?php echo xlt('Comments'); ?>:</b>
                      <span style="width:4.0in;height:3.0em;">
                        <?php echo text($ANTSEG_COMMENTS); ?>
                      </span>
                    </td>
                  </tr>
                </table>
              </div>
              <?php 
              display_draw_image ("ANTSEG",$encounter,$pid); 
              ?>
              <!-- end of Anterior Segment exam -->    
          </td>
        </tr>
    </table>

    <table style="font-size:1em;">
      <tr style="border-bottom:1pt grey dashed;">
          <?php 
          if ($ODDISC||$ODCUP||$ODMACULA||$ODVESSELS||$ODPERIPH) {
            ?>
        <td style="text-align:center;padding:1px;vertical-align:top;">

            <!-- start of RETINA exam -->
            <div style="text-align:left;border-right:1pt grey dashed;padding:5 2;">
              <b><u><?php echo xlt('Retina'); ?>:</u></b>
              <table style="width:2.5in;font-size:1.1em;">
                <tr>
                  <td class="bold" style="text-align:center;text-decoration:underline;width:200px;"><?php echo xlt('Right'); ?></td>
                  <td style="width:100px;"></td>
                  <td class="bold" style="text-align:center;text-decoration:underline;width:200px;"><?php echo xlt('Left'); ?></td>
                </tr>
                <tr>
                          <td class="report_text right"><?php echo $ODDISC; ?></td>
                          <td class="middle"><?php echo xlt('Disc'); ?></td>
                          <td class="report_text"><?php echo $OSDISC; ?></td>
                        </tr> 
                        <tr>
                          <td class="report_text right"><?php echo $ODCUP; ?></td>
                          <td class="middle"><?php echo xlt('Cup'); ?></td>
                          <td class="report_text "><?php echo $OSCUP; ?></td>
                        </tr> 
                        <tr>
                          <td class="report_text right"><?php echo $ODMACULA; ?></td>
                          <td class="middle"><?php echo xlt('Macula'); ?></td>
                          <td class="report_text "><?php echo $OSMACULA; ?></td>
                        </tr>
                        <tr>
                          <td class="report_text right"><?php echo $ODVESSELS; ?></td>
                          <td class="middle"><?php echo xlt('Vessels'); ?></td>
                          <td class="report_text "><?php echo $OSVESSELS; ?></td>
                        </tr>
                        <?php  if ($ODPERIPH||$OSPERIPH) { ?>

                        <tr>
                          <td class="report_text right"><?php echo $ODPERIPH; ?></td>
                          <td class="middle"><?php echo xlt('Periph'); ?></td>
                          <td class="report_text "><?php echo $OSPERIPH; ?></td>
                        </tr>
                        <?php } if ($ODCMT||$OSCMT) { ?>

                        <tr>
                          <td class="report_text right">&nbsp;<?php echo $ODCMT; ?></td>
                          <td class="middle"><?php echo xlt('Central Macular Thickness'); ?> </td>
                          <td class="report_text " >&nbsp;<?php echo $OSCMT; ?></td>
                        </tr>
                        <? } ?>  
                        <tr>
                          <td colspan="3"  style="font-size:0.7em;">

                    <?php if ($RETINA_COMMENTS) { ?>
                    <b><?php echo xlt('Comments'); ?>:</b>
                    <span style="width:2.5in;height:3.0em;">
                      <?php echo text($RETINA_COMMENTS); ?>
                    </span>
                    <?php } ?>
                          </td>
                </tr>
              </table>
            </div>
            <?php 
            display_draw_image ("RETINA",$encounter,$pid); 
            ?>           
            <!-- end of RETINA exam -->
        </td>
      
          <?php } ?>
        <td style="text-align:center;vertical-align:top;padding:1px;;vertical-align:top;">
            <!-- start Neuro -->
            <div style="text-align:left;padding:5 2;">
              <b><u><?php echo xlt('Other'); ?>:</u></b>  <span id="ACTNORMAL_CHECK" name="ACTNORMAL_CHECK" style="padding-left:70px;font-size:0.8em;">
              <?php echo xlt('Ortho'); ?>
              <input type="checkbox" name="ACT" id="ACT" <?php if ($ACT =='on') echo "checked='checked'"; ?> /></span>
              <table style="width:2.5in;padding:5px;font-size:1.1em;">
                  <?php if ($RLF || $LLF) { ?> 
                  <tr>
                    <td class="report_text right" style=""><?php echo $RLF; ?></td>
                    <td class="middle" style="width:120px;"><?php echo xlt('Levator Function'); ?></td>
                    <td class="report_text" style=""><?php echo text($LLF); ?></td>
                  </tr> 
                  <?php  } 
                  if ($RMRD || $LMRD) { ?> 
                  <tr>
                    <td class="report_text right"><?php echo text($RMRD); ?></td>
                    <td class="middle" title="<?php echo xla('Marginal Reflex Distance'); ?>"><?php echo xlt('MRD'); ?></td>
                    <td  class="report_text"><?php echo text($LMRD); ?></td>
                  </tr>
                  <?php  } 
                  if ($RVFISSURE || $LVFISSURE) { ?> 
                  <tr>
                    <td class="report_text right"><?php echo attr($RVFISSURE); ?></td>
                    <td class="middle" title="<?php echo xla('Vertical Fissure: central height between lid margins'); ?>"><?php echo xlt('Vert Fissure'); ?></td>
                    <td class="report_text"><?php echo attr($LVFISSURE); ?></td>
                  </tr>
                  <?php  } 
                  if ($RCAROTID || $LCAROTID) { ?> 
                  <tr>
                    <td class="report_text right"><?php echo attr($RCAROTID); ?></td>
                    <td class="middle" title="<?php echo xla('Any carotid bruits appreciated?'); ?>"><?php echo xlt('Carotid'); ?></td>
                    <td class="report_text"><?php echo attr($LCAROTID); ?></td>
                  </tr>
                  <?php  } 
                  if ($RTEMPART || $LTEMPART) { ?> 
                  <tr>
                    <td class="report_text right"><?php echo attr($RTEMPART); ?></td>
                    <td class="middle" title="<?php echo xla('Temporal Arteries'); ?>"><?php echo xlt('Temp. Art.'); ?></td>
                    <td class="report_text"><?php echo attr($LTEMPART); ?></td>
                  </tr>
                  <?php  } 
                  if ($RCNV || $LCNV) { ?> 
                  <tr>
                    <td class="report_text right"><?php echo attr($RCNV); ?></td>
                    <td class="middle" title="<?php echo xla('Cranial Nerve 5: Trigeminal Nerve'); ?>"><?php echo xlt('CN V'); ?></td>
                    <td class="report_text"><?php echo attr($LCNV); ?></td>
                  </tr>
                  <?php  } 
                  if ($RCNVII || $LCNVII) { ?> 
                  <tr>
                    <td class="report_text right"><?php echo text($RCNVII); ?></td>
                    <td class="middle" title="<?php echo xla('Cranial Nerve 7: Facial Nerve'); ?>"><?php echo xlt('CN VII'); ?></td>
                    <td class="report_text"><?php echo attr($LCNVII); ?></td>
                  </tr>
                  <?php  } 
                  if ($HERTELBASE) { ?> 
                  <tr>
                    <td colspan="3" style="text-align:center;">
                      <span style="text-decoration:underline;padding-bottom:5px;">
                        <?php echo xlt('Hertel Exophthalmometry'); ?>
                      </span>
                      <br />
                      <? 
                      if ($HERTELBASE) { ?>

                      <span style="border:1pt solid black;width:30px;text-align:center;padding:0 5;">
                        <?php echo attr($ODHERTEL); ?>
                      </span>
                      <i class="fa fa-minus"></i> 
                      <span style="border:1pt solid black;width:40px;text-align:center;padding:0 5;">
                        <?php echo attr($HERTELBASE); ?>
                      </span>
                      <i class="fa fa-minus"></i> 
                      <span style="border:1pt solid black;width:30px;text-align:center;padding:0 5;">
                        <?php echo attr($OSHERTEL); ?>
                      </span>
                      <? 
                    } ?>
                    </td>
                  </tr>
                  <?php  }  
                  if ($ODCOLOR || $OSCOLOR) { ?> 
                  <tr>
                    <td class="report_text right"><?php echo text($ODCOLOR); ?></td>
                    <td class="middle" title="<?php echo xla('Color Vision'); ?>"><?php echo xlt('Color'); ?></td>
                    <td class="report_text"><?php echo attr($OSCOLOR); ?></td>
                  </tr>
                  <?php  } 
                  if ($ODREDDESAT || $OSREDDESAT) { ?> 
                  <tr>
                    <td class="report_text right"><?php echo text($ODREDDESAT); ?></td>
                    <td class="middle" title="<?php echo xla('Red Desaturation'); ?>"><?php echo xlt('Red Desat'); ?></td>
                    <td class="report_text"><?php echo attr($OSREDDESAT); ?></td>
                  </tr>
                  <?php  }  
                   if ($ODCOINS || $OSCOINS) { ?> 
                  <tr>
                    <td class="report_text right"><?php echo text($ODCOINS); ?></td>
                    <td class="middle" title="<?php echo xla('Coins: strength 0-100'); ?>"><?php echo xlt('Coins'); ?></td>
                    <td class="report_text"><?php echo attr($OSCOINS); ?></td>
                  </tr>
                  <?php  }  ?> 
              </table>
              <br />
              <table style="width:1.5in;padding:20 5 5 5;font-size:1.1em;">
                <tr>
                    <td style="font-size:0.9em;margin:0 auto;font-weight:bold;text-decoration:underline;"><?php echo xlt('Motility'); ?>:</td>
                    <td style="font-size:0.8em;vertical-align:top;text-align:left;top:0.0in;right:0.1in;height:0px;">
                      <?php echo xlt('Normal'); ?>
                      <input id="MOTILITYNORMAL" name="MOTILITYNORMAL" type="checkbox" <?php if ($MOTILITYNORMAL =='on') echo "checked='checked'"; ?>>
                    </td>
                </tr>
                <?php if ($MOTILITYNORMAL !='on') { ?>
                <tr>
                  <td style="text-decoration:underline;font-size:0.8em;"><?php echo xlt('OD'); ?></td>
                  <td style="text-decoration:underline;font-size:0.8em;"><?php echo xlt('OS'); ?></td>
                </tr>
                                        <? 
                                //get motility values to be '' for 0, and negative for the rest...
                    $zone = array("MOTILITY_RSR","MOTILITY_RS","MOTILITY_RSL","MOTILITY_RR","MOTILITY_R0","MOTILITY_RL","MOTILITY_RIR","MOTILITY_RI","MOTILITY_RIL","MOTILITY_LSR","MOTILITY_LS","MOTILITY_LSL","MOTILITY_LR","MOTILITY_L0","MOTILITY_LL","MOTILITY_LIR","MOTILITY_LI","MOTILITY_LIL");

                    for ($i = 0; $i < count($zone); ++$i) {
                                //echo $i." ".$zone[$i]. " = ".$$zone[$i]."";
                      ($$zone[$i] >= '1') ? ($$zone[$i] = "-".$$zone[$i]) : ($$zone[$i] = '');
                              //  echo $i." ".$zone[$i]. " = ".$$zone[$i]."";
                    }
                    ?>

                <tr>
                  <td>


                    <div class="divTable" style="background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 85% 85%;height:0.77in;width:0.71in;padding:1px;margin:6 1 1 1;">
                      <div class="divRow">
                        <div class="rdivCell" name="MOTILITY_RSR" id="MOTILITY_RSL"><?php echo attr($MOTILITY_RSR); ?></div>
                        <div class="rdivCell" name="MOTILITY_RS" id="MOTILITY_RSS"><?php echo attr($MOTILITY_RS); ?></div>
                        <div class="rdivCell" name="MOTILITY_RSL" id="MOTILITY_RSL"><?php echo attr($MOTILITY_RSL); ?></div>
                      </div>
                      <div class="divRow">
                        <div class="rdivCell" name="MOTILITY_RSR" id="MOTILITY_RSL"><?php echo attr($MOTILITY_RR); ?></div>
                        <div class="rdivCell" name="MOTILITY_RS" id="MOTILITY_RSS"><?php echo attr($MOTILITY_R0); ?></div>
                        <div class="rdivCell" name="MOTILITY_RSL" id="MOTILITY_RSL"><?php echo attr($MOTILITY_RL); ?></div>
                      </div>
                      <div class="divRow">
                        <div class="rdivCell" name="MOTILITY_RSR" id="MOTILITY_RSL"><?php echo attr($MOTILITY_RIR); ?></div>
                        <div class="rdivCell" name="MOTILITY_RS" id="MOTILITY_RSS"><?php echo attr($MOTILITY_RI); ?></div>
                        <div class="rdivCell" name="MOTILITY_RSL" id="MOTILITY_RSL"><?php echo attr($MOTILITY_RIL); ?></div>
                      </div>

                    </div> 
                  </td>
                  <td>
                    <div class="divTable" style="float:left;background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 85% 85%;height:0.77in;width:0.71in;padding:1px;margin:6 1 1 1;">
                      <div class="divRow">
                        <div class="rdivCell" name="MOTILITY_LSR" id="MOTILITY_LSL"><?php echo attr($MOTILITY_LSR); ?></div>
                        <div class="rdivCell" name="MOTILITY_LS" id="MOTILITY_LSS"><?php echo attr($MOTILITY_LS); ?></div>
                        <div class="rdivCell" name="MOTILITY_LSL" id="MOTILITY_LSL"><?php echo attr($MOTILITY_LSL); ?></div>
                      </div>
                      <div class="divRow">
                        <div class="rdivCell" name="MOTILITY_LSR" id="MOTILITY_LSL"><?php echo attr($MOTILITY_LR); ?></div>
                        <div class="rdivCell" name="MOTILITY_LS" id="MOTILITY_LSS"><?php echo attr($MOTILITY_L0); ?></div>
                        <div class="rdivCell" name="MOTILITY_LSL" id="MOTILITY_LSL"><?php echo attr($MOTILITY_LL); ?></div>
                      </div>
                      <div class="divRow">
                        <div class="rdivCell" name="MOTILITY_LSR" id="MOTILITY_LSL"><?php echo attr($MOTILITY_LIR); ?></div>
                        <div class="rdivCell" name="MOTILITY_LS" id="MOTILITY_LSS"><?php echo attr($MOTILITY_LI); ?></div>
                        <div class="rdivCell" name="MOTILITY_LSL" id="MOTILITY_LSL"><?php echo attr($MOTILITY_LIL); ?></div>
                      </div>
                    </div> 
                  </td>
                </tr>
                <?php } ?>
              </table>
            </div>
        </td>
      </tr>
    </table>

   
    
    <table style="font-size:1em;">
      <tr>
        <td style="text-align:center;padding:1px;vertical-align:top;">
          <!-- start of IMPPLAN exam -->
          <div style="text-align:left;padding:5 20;">
            <b><u><?php echo xlt('Impression/Plan'); ?>:</u></b>
            <table style="width:3.0in;font-size:1.1em;">
                <tr>
                  <td>
                    <?php 

                    $order   = array("\r\n", "\n", "\r");
                    $replace = '<br />';
                    // Processes \r\n's first so they aren't converted twice.
                    $IMP = str_replace($order, $replace, $IMP);
                    //    $PLAN = str_replace($order, $replace, $PLAN);
                    echo $IMP; ?>

                  </td>

                </tr>
            </table>

            <?php 
            display_draw_image ("IMPPLAN",$encounter,$pid); 
            ?>
          </div>
          <!-- end of IMPPLAN exam -->    
        </td>
        <td style="text-align:center;vertical-align:bottom;padding:1px;">
          <?php
            $signature = $GLOBALS["webserver_root"]."/interface/forms/eye_mag/images/sign_".$_SESSION['authUserID'].".jpg";
              if (file_exists($signature)) {
                ?>
                <div style="position:relative;left:0.in;padding-left:5 2;">
                  <img src="/openemr/interface/forms/eye_mag/images/sign_<?php echo $_SESSION['authUserID']; ?>.jpg" style="width:200px;height:70px;bottom:1px;" /> 
                  <br />
                  <span style="border-top:1pt solid black;">Raymond G. Magauran, MD, MBA</span>
                </div>
                <?php
              }
          ?> 
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
 <?php exit; ?>    
                    <?php
                              //end central_wrapper
                              //return;
                                    //end central_wrapper
                                  //return;
                }
                function display_draw_image($zone,$encounter,$pid){
                  global $form_folder;
                  return;
                  $side = "OU";

                  $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter."/".$side."_".$zone."_VIEW.png";

                  $sql = "SELECT * from documents where url='file://".$file_location."'";
                  $doc = sqlQuery($sql);
                    // random to not pull from cache.
                  if (file_exists($file_location) && ($doc['id'] > '0')) {
                    $filetoshow = $GLOBALS['web_root']."/controller.php?document&retrieve&patient_id=$pid&document_id=".$doc['id']."&as_file=false&blahblah=".rand();
                    echo '<div style="float:left;margin:70 10 auto 10;border:2pt solid grey;">';
                    echo "<img src='".$filetoshow."' style='padding: 0px 0px 0px 5px;'>
                    </div>";
                  } else {
                  //base image. 
                  //$filetoshow = "../../forms/".$form_folder."/images/".$side."_".$zone."_BASE.png"; 
                  }


                  return;

                }


                function full_report( $pid, $encounter, $cols='2', $id) {
                  return;

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
                  print "</tr></table><h4>Electronically signed: Raymond Magauran, MD, MBA";
                }

                ?> 
