<?php
/** 
 * forms/eye_mag/php/eye_mag_functions.php 
 * 
 * Function which extend the eye_mag form
 *   
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
 * @category forms
 * @subpackage eye_mag 
 * @version 0.8 (will be 1.0 when acceptted in code base) 
 * @filesource openemr/interface/forms/eye_maga/php/eye_mag_functions.php
 * @author Ray Magauran <magauran@MedFetch.com> 
 * @link http://www.open-emr.org 
 */


/**
 *  This function returns HTML old record selector widget when needed (3 input values)
 * 
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO, DRAW_PRIORS_$zone 
 * @param string $visit_date Future functionality to limit result set. UTC DATE Formatted 
 * @param string $pid value = patient id
 * @return string returns the HTML old record selector widget for the desired zone 
 */ 
function priors_select($zone,$visit_date,$pid) {
	global $form_folder;
  	$output_return ="<span style='font-size:0.8em;padding:0px;margin:0 0 5 0;vertical-align:text-top;padding-left:10px;z-index:10;' 
        				   id='".$zone."_prefix_oldies' name='".$zone."_prefix_oldies'  class='display ' >";
    $selected='';
    $current='';
    if (!$priors) {
        $query="select form_encounter.date as encounter_date,form_eye_mag.* from form_eye_mag ,forms,form_encounter 
                    where 
                    form_encounter.encounter = forms.encounter and 
                    form_eye_mag.id=forms.form_id and
                    forms.pid ='".$pid."' ORDER BY encounter_date DESC";
        $result = sqlStatement($query);
        $counter = sqlNumRows($result);
        global $priors;
     	global $current;
        $priors = array();
        if ($counter < 2) return;
        $i="0";
        while ($prior= sqlFetchArray($result))   {   
           	$visit_date_local = date_create($prior['encounter_date']);
           	$exam_date = date_format($visit_date_local, 'm/d/Y'); 
            // there may be an openEMR global user preference for date formatting
           	$priors[$i] = $prior;
           	$priors[$i]['exam_date'] = $exam_date;
            if ($visit_date ==$prior['date']) {
                $selected = 'selected="selected"';
                $current = $i;
            }
           $output .= "<option value='".$prior['date']."' ".$selected.">".$priors[$i]['exam_date']."</option>";
           $selected ='';
           $i++;
    	}
    } else {
        for ($i=0; $i< count($priors); $i++) {
            if ($visit_date ==$priors[$i]['date']) {
                $selected = 'selected="selected"';
                $current = $i;
            }
            $output .= "<option value='".$priors[$i]['date']."' ".$selected.">".$selected.$priors[$i]['exam_date']."</option>";
        }
    }
    $i--;
    if ($current < $i)  { $earlier = $current + 1;} else { $earlier = $current; }
    if ($current > '0') { $later   = ($current) - 1;} else { $later   = "0"; }

    $output_return .= '
    <span title="This is a feature request - it will copy this data to the current visit fields..."><i class="fa fa-paste fa-lg"></i></span>&nbsp;
    &nbsp;        <span onclick=\'$("#PRIOR_'.$zone.'").val("'.$priors[$i]['date'].'").trigger("change");\' 
                id="PRIORS_'.$zone.'_earliest" name="PRIORS_'.$zone.'_earliest" class="fa fa-fast-backward fa-sm PRIORS">
                &nbsp;
        </span>
        <span onclick=\'$("#PRIOR_'.$zone.'").val("'.$priors[$earlier]['date'].'").trigger("change");\' 
                id="PRIORS_'.$zone.'_minus_one" name="PRIORS_'.$zone.'_minus_one" class="fa fa-step-backward fa-sm PRIORS">
        </span>
        
        <select name="PRIOR_'.$zone.'" id="PRIOR_'.$zone.'" style="padding:0;" class="PRIORS">
                '.$output.'
        </select>
                              
        <span onclick=\'$("#PRIOR_'.$zone.'").val("'.$priors[$later]["date"].'").trigger("change");\'  
                id="PRIORS_'.$zone.'_plus_one" name="PRIORS_'.$zone.'_plus_one" class="fa  fa-step-forward PRIORS"> 
        </span>&nbsp;
        <span onclick=\'$("#PRIOR_'.$zone.'").val("'.$priors[0]["date"].'").trigger("change");\'  
                id="PRIORS_'.$zone.'_latest" name="PRIORS_'.$zone.'_latest" class="fa  fa-fast-forward PRIORS"> &nbsp;
        </span>
        
    </span>';
                 
     return $output_return;   
}

/**
 *  This function returns ZONE specific HTML for a prior record (3 input values)
 * 
 *  This is where the magic of displaying the old record happens.
 *  Each section is a duplicate of the base html except the values are changed,
 *    the background and background-color are different, and the input fields are disabled.
 *
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO, DRAW_PRIORS_$zone 
 * @param string $visit_date Future functionality to limit result set. UTC DATE Formatted 
 * @param string $pid value = patient id
 * @return true : when called directly outputs the ZONE specific HTML for a prior record + widget for the desired zone 
 */ 
function display_section ($zone,$visit_date,$pid) {
	global $form_folder;
	$query  = "SELECT * FROM form_eye_mag_prefs where PEZONE='PREFS' AND id='".$_SESSION['authUserID']."' ORDER BY ZONE_ORDER,ordering";
    $result = sqlStatement($query);
    while ($prefs= mysql_fetch_array($result))   {    @extract($prefs);    $$LOCATION = $VALUE; 
    }
	$query = "SELECT * FROM form_eye_mag where pid = '".$pid."' ORDER BY id desc";
    $result = sqlStatement($query);

    $prior = array();
    $i=0;
    $current ='';
      while ($priors= mysql_fetch_array($result))   {
          $prior[$i] = $priors;
          if ($prior[$i]['date'] == $visit_date) {
            $current = $i;
            @extract($prior[$i]);
          }
          $i++;
      }
                
	if ($zone == "EXT") {
		$output =  priors_select($zone,$visit_date,$pid);
		?> 
		
		<input type="hidden" id="PRIORS_<?=$zone?>_prefix" name="PRIORS_<?=$zone?>_prefix" value="">
        <span class="closeButton pull-right fa  fa-close" id="Close_PRIORS_<?=$zone?>" name="Close_PRIORS_<?=$zone?>"></span> 
                <div style="position:absolute;top:0.083in;right:0.241in;">
                     <?php
                     echo $output;
                      ?>
                </div>
                <b> Prior Exam: </b><br />
                <div style="position:relative;float:right;top:0.2in;">
                    <table style="text-align:center;font-weight:bold;font-size:0.8em;">
                        <tr><td></td><td>OD</td><td>OS</td>
                        </tr>
                        <tr>
	                        <td>Lev Fn</td>
	                        <td><input  type="text" size="1" name="PRIOR_RLF" id="PRIOR_RLF" value="<?=$RLF?>"></td>
	                        <td><input  type="text" size="1" name="PRIOR_LLF" id="PRIOR_LLF" value="<?=$LLF?>"></td>
	                    </tr>
	                    <tr>
	                        <td>MRD</td>
	                        <td><input type="text" size="1" name="PRIOR_RMRD" id="PRIOR_RMRD" value="<?=$RMRD?>"></td>
	                        <td><input type="text" size="1" name="PRIOR_LMRD" id="PRIOR_LMRD" value="<?=$LMRD?>"></td>
	                    </tr>
	                    <tr>
	                        <td>Vert Fissure</td>
	                        <td><input type="text" size="1" name="PRIOR_RVFISSURE" id="PRIOR_RVFISSURE" value="<?=$RVFISSURE?>"></td>
	                        <td><input type="text" size="1" name="PRIOR_LVFISSURE" id="PRIOR_LVFISSURE" value="<?=$LVFISSURE?>"></td>
	                    </tr>
	                    <tr><td colspan=3><u style="padding-top:0.15in;background-color:none;"><br />Hertel Exophthalmometry</u></td></tr>
	                    <tr style="text-align:center;">
	                        <td>
	                            <input type=text size=1 id="PRIOR_ODHERTEL" name="PRIOR_ODHERTEL" value="<?=$ODHERTEL?>">
	                            <span style="width:40px;-moz-text-decoration-line: line-through;text-align:center;"> &nbsp;&nbsp;&nbsp;&nbsp; </span>
	                        </td>
	                        <td>
	                            <input type=text size=3  id="PRIOR_HERTELBASE" name="PRIOR_HERTELBASE" value="<?=$HERTELBASE?>"><span style="width:400px;-moz-text-decoration-line: line-through;"> &nbsp;&nbsp;&nbsp;&nbsp; </span>
	                        </td>
	                        <td>
	                            <input type=text size=1  id="PRIOR_OSHERTEL" name="PRIOR_OSHERTEL" value="<?=$OSHERTEL?>">
	                        </td>
	                    </tr>
	                </table>
	            </div>

            <? ($EXT_VIEW ==1) ? ($display_EXT_view = "wide_textarea") : ($display_EXT_view= "narrow_textarea");?>                                 
            <? ($display_EXT_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
            <div id="PRIOR_EXT_text_list" name="PRIOR_EXT_text_list" class="borderShadow PRIORS <?=$display_EXT_view?>" >
                <span class="top_right fa <?=$marker?>" name="PRIOR_EXT_text_view" id="PRIOR_EXT_text_view"></span>
                <table cellspacing="0" cellpadding="0" >
                    <tr>
                        <th>Right</th><td style="width:100px;"></td><th>Left </th>
                    </tr>
                    <tr>
                        <td><textarea name="PRIOR_RBROW" id="PRIOR_RBROW" class="right "><?=$RBROW?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;">Brow</td>
                        <td><textarea name="PRIOR_LBROW" id="PRIOR_LBROW" class=""><?=$LBROW?></textarea></td>
                    </tr> 
                    <tr>
                        <td><textarea name="PRIOR_RUL" id="PRIOR_RUL" class="right"><?=$RUL?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;">Upper Lids</td>
                        <td><textarea name="PRIOR_LUL" id="PRIOR_LUL" class=""><?=$LUL?></textarea></td>
                    </tr> 
                    <tr>
                        <td><textarea name="PRIOR_RLL" id="PRIOR_RLL" class="right"><?=$RLL?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;">Lower Lids</td>
                        <td><textarea name="PRIOR_LLL" id="PRIOR_LLL" class=""><?=$LLL?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea name="PRIOR_RMCT" id="PRIOR_RMCT" class="right"><?=$RMCT?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;">Medial Canthi</td>
                        <td><textarea name="PRIOR_LMCT" id="PRIOR_LMCT" class=""><?=$LMCT?></textarea></td>
                    </tr>
                     <tr>
                        <td><textarea name="PRIOR_RMAX" id="PRIOR_RMAX" class="right"><?=$RADNEXA?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;">Adnexa</td>
                        <td><textarea name="PRIOR_LMAX" id="PRIOR_LMAX" class=""><?=$LADNEXA?></textarea></td>
                    </tr>
                </table>
            </div>  <br />
            <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> <b>Comments:</b><br />
                  <textarea id="PRIOR_EXT_COMMENTS" name="PRIOR_EXT_COMMENTS" style="width:4.0in;height:3em;"><?=$EXT_COMMENTS?></textarea>
            </div>  

            <?
            return;
	} elseif ($zone =="ANTSEG") {
		$output =  priors_select($zone,$visit_date,$pid);

		?> 
		
		<input type="hidden" id="PRIORS_<?=$zone?>_prefix" name="PRIORS_<?=$zone?>_prefix" value="">
        <span class="closeButton pull-right fa  fa-close" id="Close_PRIORS_<?=$zone?>" name="Close_PRIORS_<?=$zone?>"></span> 
        <div style="position:absolute;top:0.083in;right:0.241in;">
             <?php
             echo $output;
              ?>
        </div>

        <b> Prior Exam:</b><br />
        <div class="text_clinical" style="position:relative;float:right;top:0.2in;">
            <table style="text-align:center;font-size:0.8em;font-weight:bold;"> 
                <tr >
                    <td></td><td>OD</td><td>OS</td>
                </tr>
                <tr>
                    <td>Gonioscopy</td>
                    <td><input  type="text" class="" name="PRIOR_ODGONIO" id="PRIOR_ODGONIO" value="<?=$ODGONIO?>"></td>
                    <td><input  type="text" size="2" name="PRIOR_OSGONIO" id="PRIOR_OSGONIO" value="<?=$OSGONIO?>"></td>
                </tr>
                <tr>
                    <td>Pachymetry</td>
                    <td><input type="text" size="1" name="PRIOR_ODKTHICKNESS" id="PRIOR_ODKTHICKNESS" value="<?=$ODKTHICKNESS?>"></td>
                    <td><input type="text" size="1" name="PRIOR_OSKTHICKNESS" id="PRIOR_OSKTHICKNESS" value="<?=$OSKTHICKNESS?>"></td>
                </tr>
            </table>
        </div>

        <? ($ANTSEG_VIEW !='1') ? ($display_ANTSEG_view = "wide_textarea") : ($display_ANTSEG_view= "narrow_textarea");?>
        <? ($display_ANTSEG_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
        <div id="PRIOR_ANTSEG_text_list"  name="PRIOR_ANTSEG_text_list" class="borderShadow PRIORS <?=$display_ANTSEG_view?>" >
                <span class="top_right fa <?=$marker?>" name="PRIOR_ANTSEG_text_view" id="PRIOR_ANTSEG_text_view"></span>
                <table class="" style="" cellspacing="0" cellpadding="0">
                    <tr>
                        <th>OD</th><td style="width:100px;"></td><th>OS</th></td>
                    </tr>
                    <tr>
                        <td><textarea name="PRIOR_ODCONJ" id="PRIOR_ODCONJ" class=" right"><?=$ODCONJ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;">Conj / Sclera</td>
                        <td><textarea name="PRIOR_OSCONJ" id="PRIOR_OSCONJ" class=""><?=$OSCONJ?></textarea></td>
                    </tr> 
                    <tr>
                        <td><textarea name="PRIOR_ODCORNEA" id="PRIOR_ODCORNEA" class=" right"><?=$ODCORNEA?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;">Cornea</td>
                        <td><textarea name="PRIOR_OSCORNEA" id="PRIOR_OSCORNEA" class=""><?=$OSCORNEA?></textarea></td>
                    </tr> 
                    <tr>
                        <td><textarea name="PRIOR_ODAC" id="PRIOR_ODAC" class=" right"><?=$ODAC?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;">A/C</td>
                        <td><textarea name="PRIOR_OSAC" id="PRIOR_OSAC" class=""><?=$OSAC?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea name="PRIOR_ODLENS" id="PRIOR_ODLENS" class=" right"><?=$ODLENS?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;font-size:0.9em;" class="dropShadow">Lens</td>
                        <td><textarea name="PRIOR_OSLENS" id="PRIOR_OSLENS" class=""><?=$OSLENS?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea name="PRIOR_ODIRIS" id="PRIOR_ODIRIS" class="right"><?=$ODIRIS?></textarea></td>
                        <td style="text-align:center;">Iris</td>
                        <td><textarea name="PRIOR_OSIRIS" id="PRIOR_OSIRIS" class=""><?=$OSIRIS?></textarea></td>
                    </tr>
                </table>
        </div>  <br />
        <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> <b>Comments:</b><br />
            <textarea id="PRIOR_ANTSEG_COMMENTS" name="PRIOR_ANTSEG_COMMENTS" style="width:4.0in;height:3.0em;"><?=$ANTSEG_COMMENTS?></textarea>
        </div>   
       
        <?
        return;
	} elseif ($zone=="RETINA") {
		$output =  priors_select($zone,$visit_date,$pid);

		?> 
		
		<input type="hidden" id="PRIORS_<?=$zone?>_prefix" name="PRIORS_<?=$zone?>_prefix" value="">
        <span class="closeButton pull-right fa  fa-close" id="Close_PRIORS_<?=$zone?>" name="Close_PRIORS_<?=$zone?>"></span> 
        <div style="position:absolute;top:0.083in;right:0.241in;">                              
             <?php
             echo $output;
              ?>
        </div>
           <b>Prior Exam:</b><br />
                                <div style="position:relative;float:right;top:0.2in;">
                                    <table style="float:right;text-align:right;font-size:0.8em;font-weight:bold;padding:10px 0px 5px 10px;">
                                        <tr>
                                            <td>
                                                OCT Report:
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
                                                FA/ICG:
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
                                                Imaging:
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
                                                Electrophysiology:
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
                                                Extended ophthal:</td>
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
                                        	<td> OD </td><td> OS </td></tr>
                                            <td>
                                                CMT:</td>
                                            <td>
                                                <input name="PRIOR_ODCMT" size="4" id="PRIOR_ODCMT" value="<?=$ODCMT?>">
                                            </td>
                                            <td>
                                                <input name="PRIOR_OSCMT" size="4" id="PRIOR_OSCMT" value="<?=$OSCMT?>">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
      
                                <? ($RETINA_VIEW ==1) ? ($display_RETINA_view = "wide_textarea") : ($display_RETINA_view= "narrow_textarea");?>
                                <? ($display_RETINA_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                                <div>
                                    <div id="PRIOR_RETINA_text_list" name="PRIOR_RETINA_text_list" class="borderShadow PRIORS <?=$display_RETINA_view?>">
                                        <span class="top_right fa <?=$marker?>" name="PRIOR_RETINA_text_view" id="PRIOR_RETINA_text_view"></span>
                                        <table  cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <th>OD</th><td style="width:100px;"></td><th>OS</th></td>
                                                </tr>
                                                <tr>
                                                    <td><textarea name="ODDISC" id="ODDISC" class="right"><?=$ODDISC?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;">Disc</td>
                                                    <td><textarea name="OSDISC" id="OSDISC" class=""><?=$OSDISC?></textarea></td>
                                                </tr> 
                                                <tr>
                                                    <td><textarea name="ODCUP" id="ODCUP" class="right"><?=$ODCUP?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;">Cup</td>
                                                    <td><textarea name="OSCUP" id="OSCUP" class=""><?=$OSCUP?></textarea></td>
                                                </tr> 
                                                <tr>
                                                    <td><textarea name="ODMACULA" id="ODMACULA" class="right"><?=$ODMACULA?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;">Macula</td>
                                                    <td><textarea name="OSMACULA" id="OSMACULA" class=""><?=$OSMACULA?></textarea></td>
                                                </tr>
                                                <tr>
                                                    <td><textarea name="ODVESSELS" id="ODVESSELS" class="right"><?=$ODVESSELS?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;" class="">Vessels</td>
                                                    <td><textarea name="OSVESSELS" id="OSVESSELS" class=""><?=$OSVESSELS?></textarea></td>
                                                </tr>
                                                <tr>
                                                    <td><textarea name="ODPERIPH" id="ODPERIPH" class="right"><?=$ODPERIPH?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;" class="">Periph</td>
                                                    <td><textarea name="OSPERIPH" id="OSPERIPH" class=""><?=$OSPERIPH?></textarea></td>
                                                </tr>
                                        </table>
                                    </div>
                                </div>                           
                            </div>
                            <br />
                            <br />
                            <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> 
                                <b>Comments:</b><br />
                                <textarea id="RETINA_COMMENTS" name="RETINA_COMMENTS" style="width:4.0in;height:3.0em;"><?=$RETINA_COMMENTS?></textarea>
                            </div> 
                            <? 
                            return;
	} elseif ($zone=="NEURO") {
		$output =  priors_select($zone,$visit_date,$pid);
		?> 
		
		<input type="hidden" id="PRIORS_<?=$zone?>_prefix" name="PRIORS_<?=$zone?>_prefix" value="">
        <span class="closeButton pull-right fa  fa-close" id="Close_PRIORS_<?=$zone?>" name="Close_PRIORS_<?=$zone?>"></span> 
        <div style="position:absolute;top:0.083in;right:0.241in;">
             <?php
             echo $output;
              ?>
        </div>

		<b>Neuro:</b><br />
        <div style="float:left;font-size:0.9em;">
            <div id="PRIOR_NEURO_text_list" class="borderShadow PRIORS" style="float:left;width:165px;text-align:center;margin:2 auto;font-weight:bold;">
                <table style="font-size:1.1em;font-weight:600;padding:2px;">
                    <tr>
                        <td></td><td style="text-align:center;">OD</td><td style="text-align:center;">OS</td></tr>
                    <tr>
                        <td class="right">
                            Color: 
                        </td>
                        <td>
                            <input type="text" id="PRIOR_ODCOLOR" name="PRIOR_ODCOLOR" value="<? if ($ODCOLOR) { echo  $ODCOLOR; } else { echo "   /  "; } ?>"/>
                        </td>
                        <td>
                            <input type="text" id="PRIOR_OSCOLOR" name="PRIOR_OSCOLOR" value="<? if ($OSCOLOR) { echo  $OSCOLOR; } else { echo "   /  "; } ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="right" style="white-space: nowrap;">
                            <span title="Variation in red color discrimination between the eyes (eg. OD=100, OS=75)">Red Desat:</span>
                        </td>
                        <td>
                            <input type="text" size="6" name="PRIOR_ODREDDESAT" id="PRIOR_ODREDDESAT" value="<?=$ODREDDESAT?>"/> 
                        </td>
                        <td>
                            <input type="text" size="6" name="PRIOR_OSREDDESAT" id="PRIOR_OSREDDESAT" value="<?=$OSREDDESAT?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="right" style="white-space: nowrap;">
                            <span title="Variation in white (muscle) light brightness discrimination between the eyes (eg. OD=$1.00, OS=$0.75)">Coins:</span>
                        </td>
                        <td>
                            <input type="text" size="6" name="PRIOR_ODCOINS" id="PRIOR_ODCOINS" value="<?=$ODCOINS?>"/> 
                        </td>
                        <td>
                            <input type="text" size="6" name="PRIOR_OSCOINS" id="PRIOR_OSCOINS" value="<?=$OSCOINS?>"/>
                        </td>
                    </tr>
                   
                </table>
            </div>
           
            <div class="borderShadow" style="position:relative;float:right;text-align:center;width:230px;">
                <span class="closeButton fa fa-th" id="PRIOR_Close_ACTMAIN" name="PRIOR_Close_ACTMAIN"></span>
                <table style="position:relative;float:left;font-size:1.2em;width:210px;font-weight:600;"> 
                    <tr style="text-align:left;height:26px;vertical-align:middle;width:180px;">
                        <td >
                            <span id="PRIOR_ACTTRIGGER" name="PRIOR_ACTTRIGGER">Alternate Cover Test:</span>
                        </td>
                        <td>
                            <span id="PRIOR_ACTNORMAL_CHECK" name="PRIOR_ACTNORMAL_CHECK">
                            <label for="ACT" class="input-helper input-helper--checkbox">Ortho</label>
                            <input type="checkbox" name="PRIOR_ACT" id="PRIOR_ACT" checked="<? if ($ACT =='1') echo "checked"; ?>"></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:center;"> 
                            <div id="PRIOR_ACTMAIN" name="PRIOR_ACTMAIN" class="nodisplay ACT_TEXT" style="position:relative;z-index:1;margin 10 auto;">
                               <br /> 

                               <table cellpadding="0" style="position:relative;text-align:center;font-size:0.9em;margin: 7 5 19 5;border-collapse: separate;">
                                    <tr>
                                        <td id="PRIOR_ACT_tab_SCDIST" name="PRIOR_ACT_tab_SCDIST" class="ACT_selected"> scDist </td>
                                        <td id="PRIOR_ACT_tab_CCDIST" name="PRIOR_ACT_tab_CCDIST" class="ACT_deselected"> ccDist </td>
                                        <td id="PRIOR_ACT_tab_SCNEAR" name="PRIOR_ACT_tab_SCNEAR" class="ACT_deselected"> scNear </td>
                                        <td id="PRIOR_ACT_tab_CCNEAR" name="PRIOR_ACT_tab_CCNEAR" class="ACT_deselected"> ccNear </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:center;font-size:0.8em;"><div id="PRIOR_ACT_SCDIST" name="PRIOR_ACT_SCDIST" class="ACT_box">
                                            <br />
                                            <table> 
                                                    <tr> 
                                                        <td style="text-align:center;">R</td>   
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea id="PRIOR_ACT1SCDIST" name="PRIOR_ACT1SCDIST" class="ACT"><?=$ACT1SCDIST?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea id="PRIOR_ACT2SCDIST"  name="PRIOR_ACT2SCDIST"class="ACT"><?=$ACT2SCDIST?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea id="PRIOR_ACT3SCDIST"  name="PRIOR_ACT3SCDIST" class="ACT"><?=$ACT3SCDIST?></textarea></td>
                                                        <td style="text-align:center;">L</td> 
                                                    </tr>
                                                    <tr>    
                                                        <td><i class="fa fa-reply rotate-left"></i></td> 
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea id="PRIOR_ACT4SCDIST" name="PRIOR_ACT4SCDIST" class="ACT"><?=$ACT4SCDIST?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea id="PRIOR_ACTPRIMSCDIST" name="PRIOR_ACTPRIMSCDIST" class="ACT"><?=$ACTPRIMSCDIST?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea id="PRIOR_ACT6SCDIST" name="PRIOR_ACT6SCDIST" class="ACT"><?=$ACT6SCDIST?></textarea></td>
                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea id="PRIOR_ACTRTILTSCDIST" name="PRIOR_ACTRTILTSCDIST" class="ACT"><?=$ACTRTILTSCDIST?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea id="PRIOR_ACT7SCDIST" name="PRIOR_ACT7SCDIST" class="ACT"><?=$ACT7SCDIST?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea id="PRIOR_ACT8SCDIST" name="PRIOR_ACT8SCDIST" class="ACT"><?=$ACT8SCDIST?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea id="PRIOR_ACT9SCDIST" name="PRIOR_ACT9SCDIST" class="ACT"><?=$ACT9SCDIST?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea id="PRIOR_ACTLTILTSCDIST" name="PRIOR_ACTLTILTSCDIST" class="ACT"><?=$ACTLTILTSCDIST?></textarea>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <br />
                                            </div>
                                            <div id="PRIOR_ACT_CCDIST" name="PRIOR_ACT_CCDIST" class="nodisplay ACT_box">
                                                <br />
                                                <table> 
                                                   <tr> 
                                                        <td style="text-align:center;">R</td>   
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea id="PRIOR_ACT1CCDIST" name="PRIOR_ACT1CCDIST" class="ACT"><?=$ACT1CCDIST?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea id="PRIOR_ACT2CCDIST"  name="PRIOR_ACT2CCDIST"class="ACT"><?=$ACT2CCDIST?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea id="PRIOR_ACT3CCDIST"  name="PRIOR_ACT3CCDIST" class="ACT"><?=$ACT3CCDIST?></textarea></td>
                                                        <td style="text-align:center;">L</td> 
                                                    </tr>
                                                    <tr>    <td><i class="fa fa-reply rotate-left"></i></td> 
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea id="PRIOR_ACT4CCDIST" name="PRIOR_ACT4CCDIST" class="ACT"><?=$ACT4CCDIST?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea id="PRIOR_ACTPRIMCCDIST" name="PRIOR_ACTPRIMCCDIST" class="ACT"><?=$ACTPRIMCCDIST?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea id="PRIOR_ACT6CCDIST" name="PRIOR_ACT6CCDIST" class="ACT"><?=$ACT6CCDIST?></textarea></td>
                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea id="PRIOR_ACTRTILTCCDIST" name="PRIOR_ACTRTILTCCDIST" class="ACT"><?=$ACTRTILTCCDIST?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea id="PRIOR_ACT7CCDIST" name="PRIOR_ACT7CCDIST" class="ACT"><?=$ACT7CCDIST?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea id="PRIOR_ACT8CCDIST" name="PRIOR_ACT8CCDIST" class="ACT"><?=$ACT8CCDIST?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea id="PRIOR_ACT9CCDIST" name="PRIOR_ACT9CCDIST" class="ACT"><?=$ACT9CCDIST?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea id="PRIOR_ACTLTILTCCDIST" name="PRIOR_ACTLTILTCCDIST" class="ACT"><?=$ACTLTILTCCDIST?></textarea>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <br />
                                            </div>
                                            <div id="PRIOR_ACT_SCNEAR" name="PRIOR_ACT_SCNEAR" class="nodisplay ACT_box">
                                                <br />
                                                <table> 
                                                    <tr> 
                                                        <td style="text-align:center;">R</td>    
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea id="PRIOR_ACT1SCNEAR" name="PRIOR_ACT1SCNEAR" class="ACT"><?=$ACT1SCNEAR?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea id="PRIOR_ACT2SCNEAR"  name="PRIOR_ACT2SCNEAR"class="ACT"><?=$ACT2SCNEAR?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea id="PRIOR_ACT3SCNEAR"  name="PRIOR_ACT3SCNEAR" class="ACT"><?=$ACT3SCNEAR?></textarea></td>
                                                        <td style="text-align:center;">L</td> 
                                                    </tr>
                                                    <tr>    <td><i class="fa fa-reply rotate-left"></i></td> 
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea id="PRIOR_ACT4SCNEAR" name="PRIOR_ACT4SCNEAR" class="ACT"><?=$ACT4SCNEAR?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea id="PRIOR_ACTPRIMSCNEAR" name="PRIOR_ACTPRIMSCNEAR" class="ACT"><?=$ACTPRIMSCNEAR?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea id="PRIOR_ACT6SCNEAR" name="PRIOR_ACT6SCNEAR" class="ACT"><?=$ACT6SCNEAR?></textarea></td>
                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea id="PRIOR_ACTRTILTSCNEAR" name="PRIOR_ACTRTILTSCNEAR" class="ACT"><?=$ACTRTILTSCNEAR?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea id="PRIOR_ACT7SCNEAR" name="PRIOR_ACT7SCNEAR" class="ACT"><?=$ACT7SCNEAR?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea id="PRIOR_ACT8SCNEAR" name="PRIOR_ACT8SCNEAR" class="ACT"><?=$ACT8SCNEAR?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea id="PRIOR_ACT9SCNEAR" name="PRIOR_ACT9SCNEAR" class="ACT"><?=$ACT9SCNEAR?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea id="PRIOR_ACTLTILTSCNEAR" name="PRIOR_ACTLTILTSCNEAR" class="ACT"><?=$ACTLTILTSCNEAR?></textarea>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <br />
                                            </div>
                                            <div id="PRIOR_ACT_CCNEAR" name="PRIOR_ACT_CCNEAR" class="nodisplay ACT_box">
                                                <br />
                                                <table> 
                                                    <tr> 
                                                        <td style="text-align:center;">R</td>    
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea id="PRIOR_ACT1CCNEAR" name="PRIOR_ACT1CCNEAR" class="ACT"><?=$ACT1CCNEAR?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea id="PRIOR_ACT2CCNEAR"  name="PRIOR_ACT2CCNEAR"class="ACT"><?=$ACT2CCNEAR?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea id="PRIOR_ACT3CCNEAR"  name="PRIOR_ACT3CCNEAR" class="ACT"><?=$ACT3CCNEAR?></textarea></td>
                                                        <td style="text-align:center;">L</td>
                                                    </tr>
                                                    <tr>    <td><i class="fa fa-reply rotate-left"></i></td> 
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea id="PRIOR_ACT4CCNEAR" name="PRIOR_ACT4CCNEAR" class="ACT"><?=$ACT4CCNEAR?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea id="PRIOR_ACTPRIMCCNEAR" name="PRIOR_ACTPRIMCCNEAR" class="ACT"><?=$ACTPRIMCCNEAR?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea id="PRIOR_ACT6CCNEAR" name="PRIOR_ACT6CCNEAR" class="ACT"><?=$ACT6CCNEAR?></textarea></td><td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea id="PRIOR_ACTRTILTCCNEAR" name="PRIOR_ACTRTILTCCNEAR" class="ACT"><?=$ACTRTILTCCNEAR?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea id="PRIOR_ACT7CCNEAR" name="PRIOR_ACT7CCNEAR" class="ACT"><?=$ACT7CCNEAR?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea id="PRIOR_ACT8CCNEAR" name="PRIOR_ACT8CCNEAR" class="ACT"><?=$ACT8CCNEAR?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea id="PRIOR_ACT9CCNEAR" name="PRIOR_ACT9CCNEAR" class="ACT"><?=$ACT9CCNEAR?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea id="PRIOR_ACTLTILTCCNEAR" name="PRIOR_ACTLTILTCCNEAR" class="ACT"><?=$ACTLTILTCCNEAR?></textarea>
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
                <div id="PRIOR_NPCNPA" name="PRIOR_NPCNPA">
                    <table style="position:relative;float:left;text-align:center;margin: 4 2;width:100%;font-weight:bold;font-size:1.1em;padding:4px;">
                        <tr style=""><td style="width:50%;"></td><td>OD</td><td>OS</td></tr>
                        <tr>
                            <td class="right"><span title="Near Point of Accomodation">NPA:</span></td>
                            <td><input type="text" id="PRIOR_ODNPA" style="width:70%;" name="PRIOR_ODNPA" value="<?=$ODNPA?>"></td>
                            <td><input type="text" id="PRIOR_OSNPA" style="width:70%;" name="PRIOR_OSNPA" value="<?=$OSNPA?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><span title="Near Point of Convergence">NPC:</span></td>
                            <td colspan="2" ><input type="text" style="width:85%;" id="PRIOR_NPC" name="PRIOR_NPC" value="<?=$NPC?>">
                            </td>
                        </tr>
                         <tr>
                            <td class="right">
                                Stereopsis:
                            </td>
                            <td colspan="2">
                                <input type="text" style="width:85%;" name="PRIOR_STEREOPSIS" id="PRIOR_STEREOPSIS" value="<?=$STEREOPSIS?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="right">
                                Vertical Fusional Amps:
                            </td>
                            <td colspan="2">
                                <input type="text" style="width:85%;" name="PRIOR_VERTFUSAMPS" id="PRIOR_VERTFUSAMPS" value="<?=$VERTFUSAMPS?>">
                                <br />
                            </td>
                        </tr>
                        <tr><td colspan="3"><br /><u>Convergence Amplitudes</u><br /><span style="font-size:0.8em;font-weight:400;">(Breakdown/Recovery in PD)</span></td></tr>
                        <tr><td ></td><td >Distance</td><td>Near</td></tr>
                        <tr>
                            <td style="text-align:right;">w/o correction</td>
                            <td><input type="text" id="PRIOR_CASCDIST" name="PRIOR_CASCDIST" value="<?=$CASCDIST?>"></td>
                            <td><input type="text" id="PRIOR_CASCNEAR" name="PRIOR_CASCNEAR" value="<?=$CASCNEAR?>"></td></tr>
                        <tr>
                            <td style="text-align:right;">w/ correction</td>
                            <td><input type="text" id="PRIOR_CACCDIST" name="PRIOR_CACCDIST" value="<?=$CACCDIST?>"></td>
                            <td><input type="text" id="PRIOR_CACCNEAR" name="PRIOR_CACCNEAR" value="<?=$CACCNEAR?>"></td></tr>
                        </tr>
                    </table>
                </div>
            </div>
            <div id="PRIOR_NEURO_MOTILITY" class="text_clinical borderShadow" style="float:left;font-size:1.0em;margin:3 auto;font-weight:bold;height:135px;width:165px;">
                <div>
                    <table style="width:100%;margin:0 0 15 0;">
                        <tr>
                            <td style="width:40%;font-size:0.9em;margin:0 auto;font-weight:bold;">Motility:</td>
                            <td style="font-size:0.9em;vertical-align:top;text-align:right;top:0.0in;right:0.1in;height:0px;">
                                <label for="MOTILITYNORMAL" class="input-helper input-helper--checkbox">Normal</label>
                                <input id="PRIOR_MOTILITYNORMAL" name="PRIOR_MOTILITYNORMAL" type="checkbox" value="1" checked>
                            </td>
                        </tr>
                    </table>
                </div>
                <input type="hidden" name="PRIOR_MOTILITY_RS"  id="PRIOR_MOTILITY_RS" value="<?=$MOTILITY_RS?>">
                <input type="hidden" name="PRIOR_MOTILITY_RI"  id="PRIOR_MOTILITY_RI" value="<?=$MOTILITY_RI?>">
                <input type="hidden" name="PRIOR_MOTILITY_RR"  id="PRIOR_MOTILITY_RR" value="<?=$MOTILITY_RR?>">
                <input type="hidden" name="PRIOR_MOTILITY_RL"  id="PRIOR_MOTILITY_RL" value="<?=$MOTILITY_RL?>">
                <input type="hidden" name="PRIOR_MOTILITY_LS"  id="PRIOR_MOTILITY_LS" value="<?=$MOTILITY_LS?>">
                <input type="hidden" name="PRIOR_MOTILITY_LI"  id="PRIOR_MOTILITY_LI" value="<?=$MOTILITY_LI?>">
                <input type="hidden" name="PRIOR_MOTILITY_LR"  id="PRIOR_MOTILITY_LR" value="<?=$MOTILITY_LR?>">
                <input type="hidden" name="PRIOR_MOTILITY_LL"  id="PRIOR_MOTILITY_LL" value="<?=$MOTILITY_LL?>">
                
                <div style="float:left;left:0.4in;text-decoration:underline;">OD</div>
                <div style="float:right;right:0.4in;text-decoration:underline;">OS</div><br />
                <div class="divTable" style="left:-0.1in;background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 90% 90%;height:0.7in;width:0.7in;padding:1px;margin:6 1 0 0;">
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_4_3" id="PRIOR_MOTILITY_RS_4_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_4_1" id="PRIOR_MOTILITY_RS_4_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_4" id="PRIOR_MOTILITY_RS_4" value="<?=$MOTILITY_RS?>">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_4_2" id="PRIOR_MOTILITY_RS_4_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_4_4" id="PRIOR_MOTILITY_RS_4_4">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RS_3_1" id="PRIOR_MOTILITY_RS_3_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_3" id="PRIOR_MOTILITY_RS_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_3_2" id="PRIOR_MOTILITY_RS_3_2">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RS_2_1" id="PRIOR_MOTILITY_RS_2_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_2" id="PRIOR_MOTILITY_RS_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_2_2" id="PRIOR_MOTILITY_RS_2_2">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RS_1_1" id="PRIOR_MOTILITY_RS_1_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_1" id="PRIOR_MOTILITY_RS_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_1_2" id="PRIOR_MOTILITY_RS_1_2">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RS_0_1" id="PRIOR_MOTILITY_RS_0_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_0" id="PRIOR_MOTILITY_RS_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_0_1" id="PRIOR_MOTILITY_RS_0_1">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divMiddleRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RR_4" id="PRIOR_MOTILITY_RR_4" value="<?=$MOTILITY_RR?>">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RR_3" id="PRIOR_MOTILITY_RR_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RR_2" id="PRIOR_MOTILITY_RR_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RR_1" id="PRIOR_MOTILITY_RR_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RR_0" id="PRIOR_MOTILITY_RR_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_R0" id="PRIOR_MOTILITY_R0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_0" id="PRIOR_MOTILITY_RL_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_1" id="PRIOR_MOTILITY_RL_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_2" id="PRIOR_MOTILITY_RL_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_3" id="PRIOR_MOTILITY_RL_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_4" id="PRIOR_MOTILITY_RL_4" value="<?=$MOTILITY_RL?>">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_0_1" id="PRIOR_MOTILITY_RI_0_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_RI_0" name="PRIOR_MOTILITY_RI_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_0_2" id="PRIOR_MOTILITY_RI_0_2">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RI_1_1" id="PRIOR_MOTILITY_RI_1_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_RI_1" name="PRIOR_MOTILITY_RI_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_1_2" id="PRIOR_MOTILITY_RI_1_2">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RI_2_1" id="PRIOR_MOTILITY_RI_2_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_RI_2" name="PRIOR_MOTILITY_RI_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_2_2" id="PRIOR_MOTILITY_RI_2_2">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_5" id="PRIOR_MOTILITY_RI_3_5">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_3" id="PRIOR_MOTILITY_RI_3_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_1" id="PRIOR_MOTILITY_RI_3_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_RI_3" name="PRIOR_MOTILITY_RI_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_2" id="PRIOR_MOTILITY_RI_3_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_4" id="PRIOR_MOTILITY_RI_3_4">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_6" id="PRIOR_MOTILITY_RI_3_6">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_4_5" id="PRIOR_MOTILITY_RI_4_5">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_4_3" id="PRIOR_MOTILITY_RI_4_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_4_1" id="PRIOR_MOTILITY_RI_4_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_RI_4" name="PRIOR_MOTILITY_RI_4" value="<?=$MOTILITY_RI?>">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_4_2" id="PRIOR_MOTILITY_RI_4_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_4_4" id="PRIOR_MOTILITY_RI_4_4">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_4_6" id="PRIOR_MOTILITY_RI_4_6">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LS_4_3" id="PRIOR_MOTILITY_LS_4_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_4_1" id="PRIOR_MOTILITY_LS_4_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_4" id="PRIOR_MOTILITY_LS_4" value="<?=$MOTILITY_LS?>">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_4_2" id="PRIOR_MOTILITY_LS_4_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_4_4" id="PRIOR_MOTILITY_LS_4_4">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LS_3_1" id="PRIOR_MOTILITY_LS_3_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_3" id="PRIOR_MOTILITY_LS_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_3_2" id="PRIOR_MOTILITY_LS_3_2">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LS_2_1" id="PRIOR_MOTILITY_LS_2_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_2" id="PRIOR_MOTILITY_LS_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_2_2" id="PRIOR_MOTILITY_LS_2_2">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LS_1_1" id="PRIOR_MOTILITY_LS_1_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_1" id="PRIOR_MOTILITY_LS_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_1_2" id="PRIOR_MOTILITY_LS_1_2">&nbsp;</div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LS_0_1" id="PRIOR_MOTILITY_LS_0_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_0" id="PRIOR_MOTILITY_LS_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_0_1" id="PRIOR_MOTILITY_LS_0_1">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divMiddleRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_4" id="PRIOR_MOTILITY_LR_4" value="<?=$MOTILITY_LR?>">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_3" id="PRIOR_MOTILITY_LR_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_2" id="PRIOR_MOTILITY_LR_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_1" id="PRIOR_MOTILITY_LR_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_0" id="PRIOR_MOTILITY_LR_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_L0" id="PRIOR_MOTILITY_L0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_0" id="PRIOR_MOTILITY_LL_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_1" id="PRIOR_MOTILITY_LL_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_2" id="PRIOR_MOTILITY_LL_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_3" id="PRIOR_MOTILITY_LL_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_4" id="PRIOR_MOTILITY_LL_4" value="<?=$MOTILITY_LL?>">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_4_1" id="PRIOR_MOTILITY_LR_4_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_3_1" id="PRIOR_MOTILITY_LR_3_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_2_1" id="PRIOR_MOTILITY_LR_2_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RO_I_1" id="PRIOR_MOTILITY_RO_I_1">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_LI_0" name="PRIOR_MOTILITY_LI_0">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_1" id="PRIOR_MOTILITY_LO_I_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_2_2" id="PRIOR_MOTILITY_LL_2_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_3_2" id="PRIOR_MOTILITY_LL_3_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_4_2" id="PRIOR_MOTILITY_LL_4_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                     <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_4_3" id="PRIOR_MOTILITY_LR_4_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_3_3" id="PRIOR_MOTILITY_LR_3_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RO_I_2" id="PRIOR_MOTILITY_RO_I_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_LI_1" name="PRIOR_MOTILITY_LI_1">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_2" id="PRIOR_MOTILITY_LO_I_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_3_4" id="PRIOR_MOTILITY_LL_3_4">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_4_4" id="PRIOR_MOTILITY_LL_4_4">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell" name="PRIOR_MOTILITY_RO_I_3_1" id="PRIOR_MOTILITY_RO_I_3_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RO_I_3" id="PRIOR_MOTILITY_RO_I_3">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_2_1" id="PRIOR_MOTILITY_LI_2_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_LI_2" name="PRIOR_MOTILITY_LI_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_2_2" id="PRIOR_MOTILITY_LI_2_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_2" id="PRIOR_MOTILITY_RO_I_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_3_1" id="PRIOR_MOTILITY_LO_I_3_1">&nbsp;</div>
                        </div>
                    <div class="divRow">
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_3" id="PRIOR_MOTILITY_RO_I_3">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_5" id="PRIOR_MOTILITY_LI_3_5">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_3" id="PRIOR_MOTILITY_LI_3_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_1" id="PRIOR_MOTILITY_LI_3_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_LI_3" name="PRIOR_MOTILITY_LI_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_2" id="PRIOR_MOTILITY_LI_3_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_4" id="PRIOR_MOTILITY_LI_3_4">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_6" id="PRIOR_MOTILITY_LI_3_6">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_3" id="PRIOR_MOTILITY_LO_I_3">&nbsp;</div>
                        
                    </div>
                    <div class="divRow">
                        <div class="divCell" name="PRIOR_MOTILITY_RO_I_4" id="PRIOR_MOTILITY_RO_I_4">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_4_5" id="PRIOR_MOTILITY_LI_4_5">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_4_3" id="PRIOR_MOTILITY_LI_4_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_4_1" id="PRIOR_MOTILITY_LI_4_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_LI_4" name="PRIOR_MOTILITY_LI_4"  value="<?=$MOTILITY_LI?>">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_4_2" id="PRIOR_MOTILITY_LI_4_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_4_4" id="PRIOR_MOTILITY_LI_4_4">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_4_6" id="PRIOR_MOTILITY_LI_4_6">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_4" id="PRIOR_MOTILITY_LO_I_4">&nbsp;</div>
                    </div>   
                    <div class="divRow"><div class="divCell">&nbsp;</div>
                    </div>
                </div> 
            </div>
        </div>
        <br />
        <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> 
            <b>Comments:</b><br />
            <textarea id="PRIOR_NEURO_COMMENTS" name="PRIOR_NEURO_COMMENTS" style="width:4.0in;height:3.0em;"><?=$NEURO_COMMENTS?></textarea>
        </div>  <? 
        return;
    } elseif ($zone =="ALL") {
    	echo priors_select($zone,$visit_date,$pid);
    	return;
	}
}

function display_draw_section ($zone,$encounter,$pid) {
    ?>
    <div id="Draw_<?php echo attrib($zone); ?>" name="Draw_<?php echo attrib($zone); ?>" style="text-align:center;height: 2.5in;">
        <div class="tools" style="text-align:center;left:0.02in;width:90%;">
            <a href="#Sketch_<?php echo attr($zone); ?>" data-color="#f00" > &nbsp;&nbsp;</a>
            <a style="width: 5px; background: yellow;" data-color="#ff0" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: red;" data-color="red" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: aqua;" data-color="#0ff" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: blue;" data-color="#00f" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: fuchsia;" data-color="#f0f" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: black;" data-color="#000" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: white;" data-color="#fff" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="background: #CCC" data-size="1" href="#Sketch_<?php echo attr($zone); ?>">1</a>
            <a style="background: #CCC" data-size="3" href="#Sketch_<?php echo attr($zone); ?>">3</a>
            <a style="background: #CCC" data-size="5" href="#Sketch_<?php echo attr($zone); ?>">5</a>
            <a style="background: #CCC" data-size="10" href="#Sketch_<?php echo attr($zone); ?>">10</a>
            <a style="background: #CCC" data-size="15" href="#Sketch_<?php echo attr($zone); ?>">15</a>  
        </div>
        <canvas id="Sketch_<?php echo attr($zone); ?>" class="borderShadow2" style="background: url(../../forms/<?php echo $form_folder; ?>/images/antseg_OU.png)  no-repeat center center;background-size: 100% 100%;height:1.5in;width:4.5in;padding:0in;margin: 0.1in;"></canvas>
        <script type="text/javascript">
            $(function() {
                $('#Sketch_<?php echo attr($zone); ?>').sketch({defaultSize:"1"});
            });
        </script>
        <br />
    </div>
}
return ;
?>