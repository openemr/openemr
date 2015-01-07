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

$fake_register_globals=false;
$sanitize_all_escapes=true;

/**
 *  This function returns HTML old record selector widget when needed (3 input values)
 * 
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO, DRAW_PRIORS_$zone 
 * @param string $visit_date Future functionality to limit result set. UTC DATE Formatted 
 * @param string $pid value = patient id
 * @return string returns the HTML old record selector widget for the desired zone 
 */ 

//error_reporting(E_ALL & ~E_NOTICE);

$form_folder = "eye_mag";
function priors_select($zone,$orig_id,$id_to_show,$pid) {
    global $form_folder;
    global $visit_date;

    $output_return ="<span style='right:0.241in;
                                font-size:0.72em;
                                padding:1 0 0 10;
                                margin:0 0 5 0;
                                z-index:10;
                                display: nowrap;' 
                                id='".attr($zone)."_prefix_oldies' 
                                name='".attr($zone)."_prefix_oldies'  
                                class='display ' >";
    $selected='';
    $current='';
    if (!$priors) {
        $query="select form_encounter.date as encounter_date,form_eye_mag.id as form_id, form_eye_mag.* 
                    from form_eye_mag,forms,form_encounter 
                    where 
                    form_encounter.encounter = forms.encounter and 
                    form_eye_mag.id=forms.form_id and
                    forms.form_name ='".$form_folder."' and 
                    forms.deleted != '1' and 
                    forms.pid =form_eye_mag.pid and form_eye_mag.pid=? ORDER BY encounter_date DESC";
        $result = sqlStatement($query,array($pid));
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
            //there is - use when ready...
            $priors[$i] = $prior;
            $selected ='';
            $priors[$i]['exam_date'] = $exam_date;
            if ($id_to_show ==$prior['form_id']) {
                $selected = 'selected="selected"';
                $current = $i;
            }
           $output .= "<option value='".attr($prior['id'])."' ".attr($selected).">".xlt($priors[$i]['exam_date'])."</option>";
           $selected ='';
           $i++;
        }
    } else {
        for ($i=0; $i< count($priors); $i++) {
            if ($form_id ==$priors[$i]['id']) {
                $selected = 'selected=selected';
                $current = $i;
            }
            $output .= "<option value='".attr($priors[$i]['id'])."' ".attr($selected).">".xlt($priors[$i]['exam_date'])."</option>";
        }
    }
    $i--;
    if ($current < $i)  { $earlier = $current + 1;} else { $earlier = $current; }
    if ($current > '0') { $later   = ($current) - 1;} else { $later   = "0"; }
    if ($GLOBALS['date_display_format'] == 1)      // mm/dd/yyyy 
    {   $priors[$i]['encounter_date'] = date("m/d/Y", strtotime($priors[$i]['encounter_date']));
        $priors[$earlier]['encounter_date'] = date("m/d/Y", strtotime($priors[$earlier]['encounter_date']));
        $priors[$later]['encounter_date'] = date("m/d/Y", strtotime($priors[$later]['encounter_date']));
        $priors[0]['encounter_date'] = date("m/d/Y", strtotime($priors[0]['encounter_date']));
        $priors[$current]['encounter_date'] = date("m/d/Y", strtotime($priors[$current]['encounter_date']));
    } else {
        $priors[$i]['encounter_date'] = date("d/m/Y", strtotime($priors[$i]['encounter_date']));
        $priors[$earlier]['encounter_date'] = date("d/m/Y", strtotime($priors[$earlier]['encounter_date']));
        $priors[$later]['encounter_date'] = date("d/m/Y", strtotime($priors[$later]['encounter_date']));
        $priors[0]['encounter_date'] = date("d/m/Y", strtotime($priors[0]['encounter_date']));
        $priors[$current]['encounter_date'] = date("d/m/Y", strtotime($priors[$current]['encounter_date']));
    }
    if ($id_to_show != $orig_id) {
        $output_return .= '
                <span title="   '.xla("Copy $zone values from ".$priors[$current]['exam_date']." to current visit.").'
    '.xla("Updated fields are purple.").'"

                    id="COPY_'.attr($zone).'"
                    name="COPY_'.attr($zone).'"
                    value="'.attr($id_to_show).'" onclick=\'$("#COPY_SECTION").val("'.attr($zone).'-'.attr($id_to_show).'").trigger("change");\'>
                    <i class="fa fa-paste fa-lg"></i>
                </span>
                &nbsp;&nbsp;';
    }
    $output_return .= '
        <span onclick=\'$("#PRIOR_'.attr($zone).'").val("'.attr($priors[$i][id]).'").trigger("change");\' 
                id="PRIORS_'.attr($zone).'_earliest" 
                name="PRIORS_'.attr($zone).'_earliest" 
                class="fa fa-fast-backward fa-sm PRIORS"
                title="'.attr($zone).': '.attr($priors[$i]['encounter_date']).'">
        </span>
        &nbsp;
        <span onclick=\'$("#PRIOR_'.attr($zone).'").val("'.attr($priors[$earlier][id]).'").trigger("change");\' 
                id="PRIORS_'.attr($zone).'_minus_one" 
                name="PRIORS_'.attr($zone).'_minus_one" 
                class="fa fa-step-backward fa-sm PRIORS"
                title="'.attr($zone).': '.attr($priors[$earlier]['encounter_date']).'">
        </span>&nbsp;&nbsp;
        <select name="PRIOR_'.attr($zone).'" 
                id="PRIOR_'.attr($zone).'" 
                style="padding:0 5;font-size:1.1em;" 
                class="PRIORS">
                '.$output.'
        </select>
                  &nbsp;            
        <span onclick=\'$("#PRIOR_'.attr($zone).'").val("'.attr($priors[$later][id]).'").trigger("change");\'  
                id="PRIORS_'.attr($zone).'_plus_one" 
                name="PRIORS_'.attr($zone).'_plus_one" 
                class="fa  fa-step-forward PRIORS"
                title="'.attr($zone).': '.attr($priors[$later]['encounter_date']).'"> 
        </span>&nbsp;&nbsp;
        <span onclick=\'$("#PRIOR_'.attr($zone).'").val("'.attr($priors[0][id]).'").trigger("change");\'  
                id="PRIORS_'.attr($zone).'_latest" 
                name="PRIORS_'.attr($zone).'_latest" 
                class="fa  fa-fast-forward PRIORS"
                title="'.attr($zone).': '.attr($priors[0]['encounter_date']).'"> &nbsp;
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
function display_section ($zone,$orig_id,$id_to_show,$pid,$report = '0') {
    global $form_folder;
    global $id;
    global $ISSUE_TYPES;
    global $ISSUE_TYPE_STYLES;
    $query  = "SELECT * FROM form_eye_mag_prefs 
                where PEZONE='PREFS' AND id=? 
                ORDER BY ZONE_ORDER,ordering";
    $result = sqlStatement($query,array($_SESSION['authUserID']));
    while ($prefs= sqlFetchArray($result))   {    
        @extract($prefs);    
        $$LOCATION = $VALUE; 
    }
    $query = "SELECT * FROM form_".$form_folder." where pid =? and id = ?";
    $result = sqlQuery($query, array($pid,$id_to_show));
    @extract($result); 
   

    if ($zone == "EXT") {
        if ($report =='0') $output = priors_select($zone,$orig_id,$id_to_show,$pid);
        ?> 
        
        <input disabled type="hidden" id="PRIORS_<?php echo attr($zone); ?>_prefix" name="PRIORS_<?php echo attr($zone); ?>_prefix" value="">
        <span class="closeButton pull-right fa fa-close" id="Close_PRIORS_<?php echo attr($zone); ?>" name="Close_PRIORS_<?php echo attr($zone); ?>"></span> 
                <div style="position:absolute;top:0.083in;right:0.241in;">
                     <?php
                     echo $output;
                      ?>
                </div>
                <b> 
                    <?php 
                        if ($report =='0') { echo xlt('Prior Exam'); } else { echo xlt($zone);}
                     ?>: </b><br />
                <div style="position:relative;float:right;top:0.2in;">
                    <table style="text-align:center;font-weight:bold;font-size:0.7em;">
                        <tr><td></td><td><?php echo xlt('OD'); ?></td><td><?php echo xlt('OS'); ?></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('Lev Fn'); ?></td>
                            <td><input disabled  type="text" size="1" name="PRIOR_RLF" id="PRIOR_RLF" value="<?php echo attr($RLF); ?>"></td>
                            <td><input disabled  type="text" size="1" name="PRIOR_LLF" id="PRIOR_LLF" value="<?php echo attr($LLF); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('MRD'); ?></td>
                            <td><input disabled type="text" size="1" name="PRIOR_RMRD" id="PRIOR_RMRD" value="<?php echo attr($RMRD); ?>"></td>
                            <td><input disabled type="text" size="1" name="PRIOR_LMRD" id="PRIOR_LMRD" value="<?php echo attr($LMRD); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('Vert Fissure'); ?></td>
                            <td><input disabled type="text" size="1" name="PRIOR_RVFISSURE" id="PRIOR_RVFISSURE" value="<?php echo attr($RVFISSURE); ?>"></td>
                            <td><input disabled type="text" size="1" name="PRIOR_LVFISSURE" id="PRIOR_LVFISSURE" value="<?php echo attr($LVFISSURE); ?>"></td>
                        </tr>
                          <tr>
                            <td class="right"><?php echo xlt('Carotid Bruit'); ?></td>
                            <td><input  disabled type="text"  name="PRIOR_RCAROTID" id="PRIOR_RCAROTID" value="<?php echo attr($RCAROTID); ?>"></td>
                            <td><input  disabled type="text"  name="PRIOR_LCAROTID" id="PRIOR_LCAROTID" value="<?php echo attr($LCAROTID); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('Temporal Art.'); ?></td>
                            <td><input  disabled type="text" size="1" name="PRIOR_RTEMPART" id="PRIOR_RTEMPART" value="<?php echo attr($RTEMPART); ?>"></td>
                            <td><input  disabled type="text" size="1" name="PRIOR_LTEMPART" id="PRIOR_LTEMPART" value="<?php echo attr($LTEMPART); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('CN V'); ?></td>
                            <td><input  disabled type="text" size="1" name="PRIOR_RCNV" id="PRIOR_RCNV" value="<?php echo attr($RCNV); ?>"></td>
                            <td><input  disabled type="text" size="1" name="PRIOR_LCNV" id="PRIOR_LCNV" value="<?php echo attr($LCNV); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('CN VII'); ?></td>
                            <td><input disabled type="text" size="1" name="PRIOR_RCNVII" id="PRIOR_RCNVII" value="<?php echo attr($RCNVII); ?>"></td>
                            <td><input disabled type="text" size="1" name="PRIOR_LCNVII" id="PRIOR_LCNVII" value="<?php echo attr($LCNVII); ?>"></td>
                        </tr>
                        <tr><td colspan=3 style="padding-top:0.15in;background-color:none;text-decoration:underline;"><br /><?php echo xlt('Hertel Exophthalmometry'); ?></td></tr>
                        <tr style="text-align:center;">
                            <td>
                                <input disabled type=text size=1 id="PRIOR_ODHERTEL" name="PRIOR_ODHERTEL" value="<?php echo attr($ODHERTEL); ?>">
                                <span style="width:40px;-moz-text-decoration-line: line-through;text-align:center;"> &nbsp;&nbsp;&nbsp;&nbsp; </span>
                            </td>
                            <td>
                                <input disabled type=text size=3  id="PRIOR_HERTELBASE" name="PRIOR_HERTELBASE" value="<?php echo attr($HERTELBASE); ?>">
                                <span style="width:400px;-moz-text-decoration-line: line-through;"> &nbsp;&nbsp;&nbsp;&nbsp; </span>
                            </td>
                            <td>
                                <input disabled type=text size=1  id="PRIOR_OSHERTEL" name="PRIOR_OSHERTEL" value="<?php echo attr($OSHERTEL); ?>">
                            </td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                    </table>
                </div>

            <?php ($EXT_VIEW ==1) ? ($display_EXT_view = "wide_textarea") : ($display_EXT_view= "narrow_textarea");?>                                 
            <?php ($display_EXT_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
            <div id="PRIOR_EXT_text_list" name="PRIOR_EXT_text_list" class="borderShadow PRIORS <?php echo attr($display_EXT_view); ?>" >
                <span class="top_right fa <?php echo attr($marker); ?>" name="PRIOR_EXT_text_view" id="PRIOR_EXT_text_view"></span>
                <table cellspacing="0" cellpadding="0" >
                    <tr>
                        <th><?php echo xlt('Right'); ?></th><td style="width:100px;"></td><th><?php echo xlt('Left'); ?> </th>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_RBROW" id="PRIOR_RBROW" class="right "><?php echo text($RBROW); ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Brow'); ?></td>
                        <td><textarea disabled name="PRIOR_LBROW" id="PRIOR_LBROW" class=""><?php echo text($LBROW); ?></textarea></td>
                    </tr> 
                    <tr>
                        <td><textarea disabled name="PRIOR_RUL" id="PRIOR_RUL" class="right"><?php echo text($RUL); ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Upper Lids'); ?></td>
                        <td><textarea disabled name="PRIOR_LUL" id="PRIOR_LUL" class=""><?php echo text($LUL); ?></textarea></td>
                    </tr> 
                    <tr>
                        <td><textarea disabled name="PRIOR_RLL" id="PRIOR_RLL" class="right"><?php echo text($RLL); ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Lower Lids'); ?></td>
                        <td><textarea disabled name="PRIOR_LLL" id="PRIOR_LLL" class=""><?php echo text($LLL); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_RMCT" id="PRIOR_RMCT" class="right"><?php echo text($RMCT); ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Medial Canthi'); ?></td>
                        <td><textarea disabled name="PRIOR_LMCT" id="PRIOR_LMCT" class=""><?php echo text($LMCT); ?></textarea></td>
                    </tr>
                     <tr>
                        <td><textarea disabled name="PRIOR_RADNEXA" id="PRIOR_RADNEXA" class="right"><?php echo text($RADNEXA); ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Adnexa'); ?></td>
                        <td><textarea disabled name="PRIOR_LADNEXA" id="PRIOR_LADNEXA" class=""><?php echo text($LADNEXA); ?></textarea></td>
                    </tr>
                </table>
            </div>  <br />
            <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> <b><?php echo xlt('Comments'); ?>:</b><br />
                  <textarea disabled id="PRIOR_EXT_COMMENTS" name="PRIOR_EXT_COMMENTS" style="width:4.0in;height:3em;"><?php echo text($EXT_COMMENTS); ?></textarea>
            </div>  

            <?
            return;
    } elseif ($zone =="ANTSEG") {
        if ($report =='0') $output = priors_select($zone,$orig_id,$id_to_show,$pid);
        ?> 
        <input disabled type="hidden" id="PRIORS_<?php echo attr($zone); ?>_prefix" name="PRIORS_<?php echo attr($zone); ?>_prefix" value="">
        <span class="closeButton pull-right fa  fa-close" id="Close_PRIORS_<?php echo attr($zone); ?>" name="Close_PRIORS_<?php echo attr($zone); ?>"></span> 
        <div style="position:absolute;top:0.083in;right:0.241in;">
             <?php
             echo $output;
              ?>
        </div>

        <b> <?php echo xlt('Prior Exam'); ?>:</b><br />
        <div class="text_clinical" style="position:relative;float:right;top:0.2in;">
            <table style="text-align:center;font-size:0.8em;font-weight:bold;"> 
                <tr >
                    <td></td><td><?php echo xlt('OD'); ?></td><td><?php echo xlt('OS'); ?></td>
                </tr>
                <tr>
                    <td class="right" ><?php echo xlt('Gonioscopy'); ?></td>
                    <td><input disabled  type="text" name="PRIOR_ODGONIO" id="PRIOR_ODGONIO" value="<?php echo attr($ODGONIO); ?>"></td>
                    <td><input disabled  type="text" name="PRIOR_OSGONIO" id="PRIOR_OSGONIO" value="<?php echo attr($OSGONIO); ?>"></td>
                </tr>
                <tr>
                    <td class="right" ><?php echo xlt('Pachymetry'); ?></td>
                    <td><input disabled type="text" size="1" name="PRIOR_ODKTHICKNESS" id="PRIOR_ODKTHICKNESS" value="<?php echo attr($ODKTHICKNESS); ?>"></td>
                    <td><input disabled type="text" size="1" name="PRIOR_OSKTHICKNESS" id="PRIOR_OSKTHICKNESS" value="<?php echo attr($OSKTHICKNESS); ?>"></td>
                </tr>
                <tr>
                    <td class="right" title="<?php echo xla('Schirmers I (w/o anesthesia)'); ?>"><?php echo xlt('Schirmer I'); ?></td>
                    <td><input disabled type="text" size="1" name="PRIOR_ODSCHIRMER1" id="PRIOR_ODSCHIRMER1" value="<?php echo attr($ODSCHIRMER1); ?>"></td>
                    <td><input disabled type="text" size="1" name="PRIOR_OSSCHRIMER2" id="PRIOR_OSSCHIRMER1" value="<?php echo attr($OSSCHIRMER1); ?>"></td>
                </tr>
                 <tr>
                    <td class="right" title="<?php echo xla('Schirmers II (w/ anesthesia)'); ?>"><?php echo xlt('Schirmer II'); ?></td>
                    <td><input disabled type="text" size="1" name="PRIOR_ODSCHIRMER2" id="PRIOR_ODSCHIRMER2" value="<?php echo attr($ODSCHIRMER2); ?>"></td>
                    <td><input disabled type="text" size="1" name="PRIOR_OSSCHRIMER2" id="PRIOR_OSSCHIRMER2" value="<?php echo attr($OSSCHIRMER2); ?>"></td>
                </tr>
                <tr>
                    <td class="right" title="<?php echo xla('Tear Break Up Time'); ?>"><?php echo xlt('TBUT'); ?></td>
                    <td><input disabled type="text" size="1" name="PRIOR_ODTBUT" id="PRIOR_ODTBUT" value="<?php echo attr($ODTBUT); ?>"></td>
                    <td><input disabled type="text" size="1" name="PRIOR_OSTBUT" id="PRIOR_OSTBUT" value="<?php echo attr($OSTBUT); ?>"></td>
                </tr>
            </table>
        </div>
        <?php ($ANTSEG_VIEW !='1') ? ($display_ANTSEG_view = "wide_textarea") : ($display_ANTSEG_view= "narrow_textarea");?>
        <?php ($display_ANTSEG_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
        <div id="PRIOR_ANTSEG_text_list"  name="PRIOR_ANTSEG_text_list" class="borderShadow PRIORS <?php echo attr($display_ANTSEG_view); ?>" >
                <span class="top_right fa <?php echo attr($marker); ?>" name="PRIOR_ANTSEG_text_view" id="PRIOR_ANTSEG_text_view"></span>
                <table class="" style="" cellspacing="0" cellpadding="0">
                    <tr>
                        <th><?php echo xlt('OD'); ?></th><td style="width:100px;"></td><th><?php echo xlt('OS'); ?></th></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_ODCONJ" id="PRIOR_ODCONJ" class="right"><?php echo text($ODCONJ); ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Conj'); ?> / <?php echo xlt('Sclera'); ?></td>
                        <td><textarea disabled name="PRIOR_OSCONJ" id="PRIOR_OSCONJ" class=""><?php echo text($OSCONJ); ?></textarea></td>
                    </tr> 
                    <tr>
                        <td><textarea disabled name="PRIOR_ODCORNEA" id="PRIOR_ODCORNEA" class="right"><?php echo text($ODCORNEA); ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Cornea'); ?></td>
                        <td><textarea disabled name="PRIOR_OSCORNEA" id="PRIOR_OSCORNEA" class=""><?php echo text($OSCORNEA); ?></textarea></td>
                    </tr> 
                    <tr>
                        <td><textarea disabled name="PRIOR_ODAC" id="PRIOR_ODAC" class="right"><?php echo text($ODAC); ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;"><?php echo xlt('A/C'); ?></td>
                        <td><textarea disabled name="PRIOR_OSAC" id="PRIOR_OSAC" class=""><?php echo text($OSAC); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_ODLENS" id="PRIOR_ODLENS" class=" right"><?php echo text($ODLENS); ?></textarea></td>
                        <td style="text-align:center;font-size:0.9em;font-size:0.9em;" class="dropShadow"><?php echo xlt('Lens'); ?></td>
                        <td><textarea disabled name="PRIOR_OSLENS" id="PRIOR_OSLENS" class=""><?php echo text($OSLENS); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_ODIRIS" id="PRIOR_ODIRIS" class="right"><?php echo text($ODIRIS); ?></textarea></td>
                        <td style="text-align:center;"><?php echo xlt('Iris'); ?></td>
                        <td><textarea disabled name="PRIOR_OSIRIS" id="PRIOR_OSIRIS" class=""><?php echo text($OSIRIS); ?></textarea></td>
                    </tr>
                </table>
        </div>  <br />
        <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> <b><?php echo xlt('Comments'); ?>:</b><br />
            <textarea disabled id="PRIOR_ANTSEG_COMMENTS" name="PRIOR_ANTSEG_COMMENTS" style="width:4.0in;height:3.0em;"><?php echo text($ANTSEG_COMMENTS); ?></textarea>
        </div>   
       
        <?
        return;
    } elseif ($zone=="RETINA") {
        if ($report =='0') $output = priors_select($zone,$orig_id,$id_to_show,$pid);
        ?> 
        
        <input disabled type="hidden" id="PRIORS_<?php echo attr($zone); ?>_prefix" name="PRIORS_<?php echo attr($zone); ?>_prefix" value="">
        <span class="closeButton pull-right fa fa-close" id="Close_PRIORS_<?php echo attr($zone); ?>" name="Close_PRIORS_<?php echo attr($zone); ?>"></span> 
        <div style="position:absolute;top:0.083in;right:0.241in;">                              
             <?php
             echo $output;
              ?>
        </div>
           <b><?php echo xlt('Prior Exam'); ?>:</b><br />
                                <div style="position:relative;float:right;top:0.2in;">
                                    <table style="float:right;text-align:right;font-size:0.8em;font-weight:bold;padding:10px 0px 5px 10px;">
                                        <tr>
                                            <td>
                                                <?php echo xlt('OCT Report'); ?>:
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
                                                <?php echo xlt('FA/ICG'); ?>:
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
                                                <?php echo xlt('Imaging'); ?>:
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
                                                <?php echo xlt('Electrophysiology'); ?>:
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
                                                <?php echo xlt('Extended ophthal'); ?>:</td>
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
                                            <td> <?php echo xlt('OD'); ?> </td><td> <?php echo xlt('OS'); ?> </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <?php echo xlt('CMT'); ?>:</td>
                                            <td>
                                                <input disabled name="PRIOR_ODCMT" size="4" id="PRIOR_ODCMT" value="<?php echo attr($ODCMT); ?>">
                                            </td>
                                            <td>
                                                <input disabled name="PRIOR_OSCMT" size="4" id="PRIOR_OSCMT" value="<?php echo attr($OSCMT); ?>">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
      
                                <?php ($RETINA_VIEW ==1) ? ($display_RETINA_view = "wide_textarea") : ($display_RETINA_view= "narrow_textarea");?>
                                <?php ($display_RETINA_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                                <div>
                                    <div id="PRIOR_RETINA_text_list" name="PRIOR_RETINA_text_list" class="borderShadow PRIORS <?php echo attr($display_RETINA_view); ?>">
                                        <span class="top_right fa <?php echo attr($marker); ?>" name="PRIOR_RETINA_text_view" id="PRIOR_RETINA_text_view"></span>
                                        <table  cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <th><?php echo xlt('OD'); ?></th><td style="width:100px;"></td><th><?php echo xlt('OS'); ?></th></td>
                                                </tr>
                                                <tr>
                                                    <td><textarea disabled name="ODDISC" id="ODDISC" class="right"><?php echo text($ODDISC); ?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Disc'); ?></td>
                                                    <td><textarea disabled name="OSDISC" id="OSDISC" class=""><?php echo text($OSDISC); ?></textarea></td>
                                                </tr> 
                                                <tr>
                                                    <td><textarea disabled name="ODCUP" id="ODCUP" class="right"><?php echo text($ODCUP); ?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Cup'); ?></td>
                                                    <td><textarea disabled name="OSCUP" id="OSCUP" class=""><?php echo text($OSCUP); ?></textarea></td>
                                                </tr> 
                                                <tr>
                                                    <td><textarea disabled name="ODMACULA" id="ODMACULA" class="right"><?php echo text($ODMACULA); ?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Macula'); ?></td>
                                                    <td><textarea disabled name="OSMACULA" id="OSMACULA" class=""><?php echo text($OSMACULA); ?></textarea></td>
                                                </tr>
                                                <tr>
                                                    <td><textarea disabled name="ODVESSELS" id="ODVESSELS" class="right"><?php echo text($ODVESSELS); ?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;" class=""><?php echo xlt('Vessels'); ?></td>
                                                    <td><textarea disabled name="OSVESSELS" id="OSVESSELS" class=""><?php echo text($OSVESSELS); ?></textarea></td>
                                                </tr>
                                                <tr>
                                                    <td><textarea disabled name="ODPERIPH" id="ODPERIPH" class="right"><?php echo text($ODPERIPH); ?></textarea></td>
                                                    <td style="text-align:center;font-size:0.9em;" class=""><?php echo xlt('Periph'); ?></td>
                                                    <td><textarea disabled name="OSPERIPH" id="OSPERIPH" class=""><?php echo text($OSPERIPH); ?></textarea></td>
                                                </tr>
                                        </table>
                                    </div>
                                </div>                           
                            </div>
                            <br />
                            <br />
                            <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.7em;text-align:left;padding-left:25px;"> 
                                <b><?php echo xlt('Comments'); ?>:</b><br />
                                <textarea disabled id="RETINA_COMMENTS" name="RETINA_COMMENTS" style="width:4.0in;height:3.0em;"><?php echo text($RETINA_COMMENTS); ?></textarea>
                            </div> 
                            <?php 
                            return;
    } elseif ($zone=="NEURO") {
        if ($report =='0') $output = priors_select($zone,$orig_id,$id_to_show,$pid);
        ?> 
        
        <input disabled type="hidden" id="PRIORS_<?php echo attr($zone); ?>_prefix" name="PRIORS_<?php echo attr($zone); ?>_prefix" value="">
        <span class="closeButton pull-right fa fa-close" id="Close_PRIORS_<?php echo attr($zone); ?>" name="Close_PRIORS_<?php echo attr($zone); ?>"></span> 
        <div style="position:absolute;top:0.083in;right:0.241in;">
             <?php
             echo $output;
              ?>
        </div>
        <b><?php echo xlt('Prior Exam'); ?>:</b><br />
        <div style="float:left;margin-top:0.1in;">
            <div id="PRIOR_NEURO_text_list" class="borderShadow PRIORS" style="float:left;width:165px;text-align:center;margin:2 auto;font-weight:bold;">
                <table style="font-size:1.1em;font-weight:600;padding:2px;">
                    <tr>
                        <td></td><td style="text-align:center;"><?php echo xlt('OD'); ?></td><td style="text-align:center;"><?php echo xlt('OS'); ?></td></tr>
                    <tr>
                        <td class="right">
                            <?php echo xlt('Color'); ?>: 
                        </td>
                        <td>
                            <input disabled type="text" id="PRIOR_ODCOLOR" name="PRIOR_ODCOLOR" value="<?php if ($ODCOLOR) { echo  attr($ODCOLOR); } else { echo "   /   "; } ?>"/>
                        </td>
                        <td>
                            <input disabled type="text" id="PRIOR_OSCOLOR" name="PRIOR_OSCOLOR" value="<?php if ($OSCOLOR) { echo  attr($OSCOLOR); } else { echo "   /   "; } ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="right" style="white-space: nowrap;">
                            <span title="<?php echo xla('Variation in red color discrimination between the eyes (eg. OD=100, OS=75)'); ?>"><?php echo xlt('Red Desat'); ?>:</span>
                        </td>
                        <td>
                            <input disabled type="text" size="6" name="PRIOR_ODREDDESAT" id="PRIOR_ODREDDESAT" value="<?php echo attr($ODREDDESAT); ?>"/> 
                        </td>
                        <td>
                            <input disabled type="text" size="6" name="PRIOR_OSREDDESAT" id="PRIOR_OSREDDESAT" value="<?php echo attr($OSREDDESAT); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="right" style="white-space: nowrap;">
                            <span title="<?php echo xla('Variation in white (muscle) light brightness discrimination between the eyes (eg. OD=$1.00, OS=$0.75)'); ?>"><?php echo xlt('Coins'); ?>:</span>
                        </td>
                        <td>
                            <input disabled type="text" size="6" name="PRIOR_ODCOINS" id="PRIOR_ODCOINS" value="<?php echo attr($ODCOINS); ?>"/> 
                        </td>
                        <td>
                            <input disabled type="text" size="6" name="PRIOR_OSCOINS" id="PRIOR_OSCOINS" value="<?php echo attr($OSCOINS); ?>"/>
                        </td>
                    </tr>                  
                </table>
            </div>          
            <div class="borderShadow" style="position:relative;float:right;text-align:center;width:230px;">
                <span class="closeButton fa fa-th" id="PRIOR_Close_ACTMAIN" name="PRIOR_Close_ACTMAIN"></span>
                <table style="position:relative;float:left;font-size:1.2em;width:210px;font-weight:600;"> 
                    <tr style="text-align:left;height:26px;vertical-align:middle;width:180px;">
                        <td >
                            <span id="PRIOR_ACTTRIGGER" name="PRIOR_ACTTRIGGER" style="text-decoration:underline;"><?php echo ('Alternate Cover Test'); ?>:</span>
                        </td>
                        <td>
                            <span id="PRIOR_ACTNORMAL_CHECK" name="PRIOR_ACTNORMAL_CHECK">
                            <label for="PRIOR_ACT" class="input-helper input-helper--checkbox"><?php echo xlt('Ortho'); ?></label>
                            <input disabled type="checkbox" name="PRIOR_ACT" id="PRIOR_ACT" checked="<?php if ($ACT =='1') echo "checked"; ?>"></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:center;"> 
                            <div id="PRIOR_ACTMAIN" name="PRIOR_ACTMAIN" class=" ACT_TEXT nodisplay" style="position:relative;z-index:1;margin 10 auto;">
                               <table cellpadding="0" style="position:relative;text-align:center;font-size:0.9em;margin: 7 5 19 5;border-collapse: separate;">
                                    <tr>
                                        <td id="PRIOR_ACT_tab_SCDIST" name="PRIOR_ACT_tab_SCDIST" class="ACT_selected"> <?php echo xlt('scDist'); ?> </td>
                                        <td id="PRIOR_ACT_tab_CCDIST" name="PRIOR_ACT_tab_CCDIST" class="ACT_deselected"> <?php echo xlt('ccDist'); ?> </td>
                                        <td id="PRIOR_ACT_tab_SCNEAR" name="PRIOR_ACT_tab_SCNEAR" class="ACT_deselected"> <?php echo xlt('scNear'); ?> </td>
                                        <td id="PRIOR_ACT_tab_CCNEAR" name="PRIOR_ACT_tab_CCNEAR" class="ACT_deselected"> <?php echo xlt('ccNear'); ?> </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:center;font-size:0.8em;">
                                            <div id="PRIOR_ACT_SCDIST" name="PRIOR_ACT_SCDIST" class="ACT_box">
                                                <br />
                                                <table> 
                                                    <tr> 
                                                        <td style="text-align:center;"><?php echo xlt('R'); ?></td>   
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT1SCDIST" name="PRIOR_ACT1SCDIST" class="ACT"><?php echo text($ACT1SCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT2SCDIST"  name="PRIOR_ACT2SCDIST"class="ACT"><?php echo text($ACT2SCDIST); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT3SCDIST"  name="PRIOR_ACT3SCDIST" class="ACT"><?php echo text($ACT3SCDIST); ?></textarea></td>
                                                        <td style="text-align:center;"><?php echo xlt('L'); ?></td> 
                                                    </tr>
                                                    <tr>    
                                                        <td style="text-align:right;"><i class="fa fa-reply rotate-left right"></i></td> 
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT4SCDIST" name="PRIOR_ACT4SCDIST" class="ACT"><?php echo text($ACT4SCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACTPRIMSCDIST" name="PRIOR_ACTPRIMSCDIST" class="ACT"><?php echo text($ACTPRIMSCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6SCDIST" name="PRIOR_ACT6SCDIST" class="ACT"><?php echo text($ACT6SCDIST); ?></textarea></td>
                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACTRTILTSCDIST" name="PRIOR_ACTRTILTSCDIST" class="ACT"><?php echo text($ACTRTILTSCDIST); ?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT7SCDIST" name="PRIOR_ACT7SCDIST" class="ACT"><?php echo text($ACT7SCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea disabled id="PRIOR_ACT8SCDIST" name="PRIOR_ACT8SCDIST" class="ACT"><?php echo text($ACT8SCDIST); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea disabled id="PRIOR_ACT9SCDIST" name="PRIOR_ACT9SCDIST" class="ACT"><?php echo text($ACT9SCDIST); ?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea disabled id="PRIOR_ACTLTILTSCDIST" name="PRIOR_ACTLTILTSCDIST" class="ACT"><?php echo text($ACTLTILTSCDIST); ?></textarea>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <br />
                                            </div>
                                            <div id="PRIOR_ACT_CCDIST" name="PRIOR_ACT_CCDIST" class="nodisplay ACT_box">
                                                <br />
                                                <table> 
                                                   <tr> 
                                                        <td style="text-align:center;"><?php echo xlt('R'); ?></td>   
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT1CCDIST" name="PRIOR_ACT1CCDIST" class="ACT"><?php echo text($ACT1CCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT2CCDIST"  name="PRIOR_ACT2CCDIST"class="ACT"><?php echo text($ACT2CCDIST); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT3CCDIST"  name="PRIOR_ACT3CCDIST" class="ACT"><?php echo text($ACT3CCDIST); ?></textarea></td>
                                                        <td style="text-align:center;"><?php echo xlt('L'); ?></td> 
                                                    </tr>
                                                    <tr>    
                                                        <td style="text-align:right;"><i class="fa fa-reply rotate-left"></i></td> 
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT4CCDIST" name="PRIOR_ACT4CCDIST" class="ACT"><?php echo text($ACT4CCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACTPRIMCCDIST" name="PRIOR_ACTPRIMCCDIST" class="ACT"><?php echo text($ACTPRIMCCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6CCDIST" name="PRIOR_ACT6CCDIST" class="ACT"><?php echo text($ACT6CCDIST); ?></textarea></td>
                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACTRTILTCCDIST" name="PRIOR_ACTRTILTCCDIST" class="ACT"><?php echo text($ACTRTILTCCDIST); ?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT7CCDIST" name="PRIOR_ACT7CCDIST" class="ACT"><?php echo text($ACT7CCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea disabled id="PRIOR_ACT8CCDIST" name="PRIOR_ACT8CCDIST" class="ACT"><?php echo text($ACT8CCDIST); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea disabled id="PRIOR_ACT9CCDIST" name="PRIOR_ACT9CCDIST" class="ACT"><?php echo text($ACT9CCDIST); ?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea disabled id="PRIOR_ACTLTILTCCDIST" name="PRIOR_ACTLTILTCCDIST" class="ACT"><?php echo text($ACTLTILTCCDIST); ?></textarea>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <br />
                                            </div>
                                            <div id="PRIOR_ACT_SCNEAR" name="PRIOR_ACT_SCNEAR" class="nodisplay ACT_box">
                                                <br />
                                                <table> 
                                                    <tr> 
                                                        <td style="text-align:center;"><?php echo xlt('R'); ?></td>    
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT1SCNEAR" name="PRIOR_ACT1SCNEAR" class="ACT"><?php echo text($ACT1SCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT2SCNEAR"  name="PRIOR_ACT2SCNEAR"class="ACT"><?php echo text($ACT2SCNEAR); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT3SCNEAR"  name="PRIOR_ACT3SCNEAR" class="ACT"><?php echo text($ACT3SCNEAR); ?></textarea></td>
                                                        <td style="text-align:center;"><?php echo xlt('L'); ?></td> 
                                                    </tr>
                                                    <tr>    
                                                        <td style="text-align:right;"><i class="fa fa-reply rotate-left"></i></td> 
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT4SCNEAR" name="PRIOR_ACT4SCNEAR" class="ACT"><?php echo text($ACT4SCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACTPRIMSCNEAR" name="PRIOR_ACTPRIMSCNEAR" class="ACT"><?php echo text($ACTPRIMSCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6SCNEAR" name="PRIOR_ACT6SCNEAR" class="ACT"><?php echo text($ACT6SCNEAR); ?></textarea></td>
                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACTRTILTSCNEAR" name="PRIOR_ACTRTILTSCNEAR" class="ACT"><?php echo text($ACTRTILTSCNEAR); ?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT7SCNEAR" name="PRIOR_ACT7SCNEAR" class="ACT"><?php echo text($ACT7SCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea disabled id="PRIOR_ACT8SCNEAR" name="PRIOR_ACT8SCNEAR" class="ACT"><?php echo text($ACT8SCNEAR); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea disabled id="PRIOR_ACT9SCNEAR" name="PRIOR_ACT9SCNEAR" class="ACT"><?php echo text($ACT9SCNEAR); ?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea disabled id="PRIOR_ACTLTILTSCNEAR" name="PRIOR_ACTLTILTSCNEAR" class="ACT"><?php echo text($ACTLTILTSCNEAR); ?></textarea>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <br />
                                            </div>
                                            <div id="PRIOR_ACT_CCNEAR" name="PRIOR_ACT_CCNEAR" class="nodisplay ACT_box">
                                                <br />
                                                <table> 
                                                    <tr> 
                                                        <td style="text-align:center;"><?php echo xlt('R'); ?></td>    
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT1CCNEAR" name="PRIOR_ACT1CCNEAR" class="ACT"><?php echo text($ACT1CCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT2CCNEAR"  name="PRIOR_ACT2CCNEAR"class="ACT"><?php echo text($ACT2CCNEAR); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT3CCNEAR"  name="PRIOR_ACT3CCNEAR" class="ACT"><?php echo text($ACT3CCNEAR); ?></textarea></td>
                                                        <td style="text-align:center;"><?php echo xlt('L'); ?></td>
                                                    </tr>
                                                    <tr>    
                                                        <td style="text-align:right;"><i class="fa fa-reply rotate-left"></i></td> 
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT4CCNEAR" name="PRIOR_ACT4CCNEAR" class="ACT"><?php echo text($ACT4CCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACTPRIMCCNEAR" name="PRIOR_ACTPRIMCCNEAR" class="ACT"><?php echo text($ACTPRIMCCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6CCNEAR" name="PRIOR_ACT6CCNEAR" class="ACT"><?php echo text($ACT6CCNEAR); ?></textarea></td><td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACTRTILTCCNEAR" name="PRIOR_ACTRTILTCCNEAR" class="ACT"><?php echo text($ACTRTILTCCNEAR); ?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT7CCNEAR" name="PRIOR_ACT7CCNEAR" class="ACT"><?php echo text($ACT7CCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea disabled id="PRIOR_ACT8CCNEAR" name="PRIOR_ACT8CCNEAR" class="ACT"><?php echo text($ACT8CCNEAR); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea disabled id="PRIOR_ACT9CCNEAR" name="PRIOR_ACT9CCNEAR" class="ACT"><?php echo text($ACT9CCNEAR); ?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea disabled id="PRIOR_ACTLTILTCCNEAR" name="PRIOR_ACTLTILTCCNEAR" class="ACT"><?php echo text($ACTLTILTCCNEAR); ?></textarea>
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
                        <tr style=""><td style="width:50%;"></td><td><?php echo xlt('OD'); ?></td><td><?php echo xlt('OS'); ?></td></tr>
                        <tr>
                            <td class="right"><span title="<?php echo xla('Near Point of Accomodation'); ?>"><?php echo xlt('NPA'); ?>:</span></td>
                            <td><input disabled type="text" id="PRIOR_ODNPA" style="width:70%;" name="PRIOR_ODNPA" value="<?php echo attr($ODNPA); ?>"></td>
                            <td><input disabled type="text" id="PRIOR_OSNPA" style="width:70%;" name="PRIOR_OSNPA" value="<?php echo attr($OSNPA); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><span title="<?php echo xla('Near Point of Convergence'); ?>"><?php echo xlt('NPC'); ?>:</span></td>
                            <td colspan="2" ><input disabled type="text" style="width:85%;" id="PRIOR_NPC" name="PRIOR_NPC" value="<?php echo attr($NPC); ?>">
                            </td>
                        </tr>
                         <tr>
                            <td class="right">
                                <?php echo xlt('Stereopsis'); ?>:
                            </td>
                            <td colspan="2">
                                <input disabled type="text" style="width:85%;" name="PRIOR_STEREOPSIS" id="PRIOR_STEREOPSIS" value="<?php echo attr($STEREOPSIS); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3"><br /><u><?php echo xlt('Amplitudes'); ?></u><br />
                            </td>
                        </tr>
                        <tr><td ></td><td ><?php echo xlt('Distance'); ?></td><td><?php echo xlt('Near'); ?></td></tr>
                        <tr>
                            <td style="text-align:right;"><?php echo xlt('Divergence'); ?></td>
                            <td><input disabled type="text" id="PRIOR_CASCDIST" name="PRIOR_CASCDIST" value="<?php echo attr($CASCDIST); ?>"></td>
                            <td><input disabled type="text" id="PRIOR_CASCNEAR" name="PRIOR_CASCNEAR" value="<?php echo attr($CASCNEAR); ?>"></td></tr>
                        <tr>
                            <td style="text-align:right;"><?php echo xlt('Convergence'); ?></td>
                            <td><input disabled type="text" id="PRIOR_CACCDIST" name="PRIOR_CACCDIST" value="<?php echo attr($CACCDIST); ?>"></td>
                            <td><input disabled type="text" id="PRIOR_CACCNEAR" name="PRIOR_CACCNEAR" value="<?php echo attr($CACCNEAR); ?>"></td></tr>
                        </tr>
                         <tr>
                            <td class="right">
                                <?php echo xlt('Vertical Fusional'); ?>:
                            </td>
                            <td colspan="2">
                                <input disabled type="text" style="width:90%;" name="PRIOR_VERTFUSAMPS" id="PRIOR_VERTFUSAMPS" value="<?php echo attr($VERTFUSAMPS); ?>">
                                <br />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?
                $hash_tag = '<i class="fa fa-minus"></i>';
               
                if ($MOTILITY_RS > '0') {
                    $PRIOR_MOTILITYNORMAL='';
                    for ($index =1; $index <= $MOTILITY_RS; ++$index) {
                        $here = "PRIOR_MOTILITY_RS_".$index;
                        $$here= $hash_tag;
                    }
                }
                if ($MOTILITY_RI > '0') {
                    $PRIOR_MOTILITYNORMAL='';
                    for ($index =1; $index <= $MOTILITY_RI; ++$index) {
                        $here ="PRIOR_MOTILITY_RI_".$index;
                        $$here = $hash_tag;
                    }
                }
                if ($MOTILITY_LS > '0') {
                    $PRIOR_MOTILITYNORMAL='';
                    for ($index =1; $index <= $MOTILITY_LS; ++$index) {
                        $here ="PRIOR_MOTILITY_LS_".$index;
                        $$here = $hash_tag;
                    }
                }
                if ($MOTILITY_LI > '0') {
                    $PRIOR_MOTILITYNORMAL='';
                    for ($index =1; $index <= $MOTILITY_LI; ++$index) {
                       $here ="PRIOR_MOTILITY_LI_".$index;
                        $$here = $hash_tag;
                    }
                }
                

                $hash_tag = '<i class="fa fa-minus rotate-left"></i>';
                if ($MOTILITY_LR > '0') {
                    $PRIOR_MOTILITYNORMAL='';
                    for ($index =1; $index <= $MOTILITY_LR; ++$index) {
                       $here ="PRIOR_MOTILITY_LR_".$index;
                        $$here = $hash_tag;
                    }
                }
                if ($MOTILITY_LL > '0') {
                    $PRIOR_MOTILITYNORMAL='';
                    for ($index =1; $index <= $MOTILITY_LL; ++$index) {
                        $here ="PRIOR_MOTILITY_LL_".$index;
                        $$here = $hash_tag;
                    }
                }
                if ($MOTILITY_RR > '0') {
                    $PRIOR_MOTILITYNORMAL='';
                    for ($index =1; $index <= $MOTILITY_RR; ++$index) {
                        $here ="PRIOR_MOTILITY_RR_".$index;
                        $$here = $hash_tag;
                    }
                }
                if ($MOTILITY_RL > '0') {
                    $PRIOR_MOTILITYNORMAL='';
                    for ($index =1; $index <= $MOTILITY_RL; ++$index) {
                        $here ="PRIOR_MOTILITY_RL_".$index;
                        $$here = $hash_tag;
                    }
                }
                ?>
            <div id="PRIOR_NEURO_MOTILITY" class="text_clinical borderShadow" style="float:left;font-size:1.0em;margin:3 auto;font-weight:bold;height:135px;width:165px;">
                <div>
                    <table style="width:100%;margin:0 0 15 0;">
                        <tr>
                            <td style="width:40%;font-size:0.9em;margin:0 auto;font-weight:bold;"><?php echo xlt('Motility'); ?>:</td>
                            <td style="font-size:0.9em;vertical-align:top;text-align:right;top:0.0in;right:0.1in;height:0px;">
                                <label for="PRIOR_MOTILITYNORMAL" class="input-helper input-helper--checkbox"><?php echo xlt('Normal'); ?></label>
                                <input disabled id="PRIOR_MOTILITYNORMAL" name="PRIOR_MOTILITYNORMAL" type="checkbox" value="1" <?php if ($MOTILITYNORMAL >'0') echo "checked"; ?> disabled>
                            </td>
                        </tr>
                    </table>
                </div>
                <input disabled type="hidden" name="PRIOR_MOTILITY_RS"  id="PRIOR_MOTILITY_RS" value="<?php echo attr($MOTILITY_RS); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_RI"  id="PRIOR_MOTILITY_RI" value="<?php echo attr($MOTILITY_RI); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_RR"  id="PRIOR_MOTILITY_RR" value="<?php echo attr($MOTILITY_RR); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_RL"  id="PRIOR_MOTILITY_RL" value="<?php echo attr($MOTILITY_RL); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_LS"  id="PRIOR_MOTILITY_LS" value="<?php echo attr($MOTILITY_LS); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_LI"  id="PRIOR_MOTILITY_LI" value="<?php echo attr($MOTILITY_LI); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_LR"  id="PRIOR_MOTILITY_LR" value="<?php echo attr($MOTILITY_LR); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_LL"  id="PRIOR_MOTILITY_LL" value="<?php echo attr($MOTILITY_LL); ?>">
                
                <div style="float:left;left:0.4in;text-decoration:underline;"><?php echo xlt('OD'); ?></div>
                <div style="float:right;right:0.4in;text-decoration:underline;"><?php echo xlt('OS'); ?></div><br />
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
                        <div class="divCell" name="PRIOR_MOTILITY_RS_4" id="PRIOR_MOTILITY_RS_4"><?php echo $PRIOR_MOTILITY_RS_4; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RS_3" id="PRIOR_MOTILITY_RS_3"><?php echo $PRIOR_MOTILITY_RS_3; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RS_2" id="PRIOR_MOTILITY_RS_2"><?php echo $PRIOR_MOTILITY_RS_2; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RS_1" id="PRIOR_MOTILITY_RS_1"><?php echo $PRIOR_MOTILITY_RS_1; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RR_4" id="PRIOR_MOTILITY_RR_4"><?php echo $PRIOR_MOTILITY_RR_4; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RR_3" id="PRIOR_MOTILITY_RR_3"><?php echo $PRIOR_MOTILITY_RR_3; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RR_2" id="PRIOR_MOTILITY_RR_2"><?php echo $PRIOR_MOTILITY_RR_2; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RR_1" id="PRIOR_MOTILITY_RR_1"><?php echo $PRIOR_MOTILITY_RR_1; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RR_0" id="PRIOR_MOTILITY_RR_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_R0" id="PRIOR_MOTILITY_R0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_0" id="PRIOR_MOTILITY_RL_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_1" id="PRIOR_MOTILITY_RL_1"><?php echo $PRIOR_MOTILITY_RL_1; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_2" id="PRIOR_MOTILITY_RL_2"><?php echo $PRIOR_MOTILITY_RL_2; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_3" id="PRIOR_MOTILITY_RL_3"><?php echo $PRIOR_MOTILITY_RL_3; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RL_4" id="PRIOR_MOTILITY_RL_4"><?php echo $PRIOR_MOTILITY_RL_4; ?></div>
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
                        <div class="divCell" id="PRIOR_MOTILITY_RI_1" name="PRIOR_MOTILITY_RI_1"><?php echo $PRIOR_MOTILITY_RI_1; ?></div>
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
                        <div class="divCell" id="PRIOR_MOTILITY_RI_2" name="PRIOR_MOTILITY_RI_2"><?php echo $PRIOR_MOTILITY_RI_2; ?></div>
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
                        <div class="divCell" id="PRIOR_MOTILITY_RI_3" name="PRIOR_MOTILITY_RI_3"><?php echo $PRIOR_MOTILITY_RI_3; ?></div>
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
                        <div class="divCell" id="PRIOR_MOTILITY_RI_4" name="PRIOR_MOTILITY_RI_4"><?php echo $PRIOR_MOTILITY_RI_4; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LS_4" id="PRIOR_MOTILITY_LS_4"><?php echo $PRIOR_MOTILITY_LS_4; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LS_3" id="PRIOR_MOTILITY_LS_3"><?php echo $PRIOR_MOTILITY_LS_3; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LS_2" id="PRIOR_MOTILITY_LS_2"><?php echo $PRIOR_MOTILITY_LS_2; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LS_1" id="PRIOR_MOTILITY_LS_1"><?php echo $PRIOR_MOTILITY_LS_1; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LR_4" id="PRIOR_MOTILITY_LR_4"><?php echo $PRIOR_MOTILITY_LR_4; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_3" id="PRIOR_MOTILITY_LR_3"><?php echo $PRIOR_MOTILITY_LR_3; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_2" id="PRIOR_MOTILITY_LR_2"><?php echo $PRIOR_MOTILITY_LR_2; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_1" id="PRIOR_MOTILITY_LR_1"><?php echo $PRIOR_MOTILITY_LR_1; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LR_0" id="PRIOR_MOTILITY_LR_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_L0" id="PRIOR_MOTILITY_L0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_0" id="PRIOR_MOTILITY_LL_0">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_1" id="PRIOR_MOTILITY_LL_1"><?php echo $PRIOR_MOTILITY_LL_1; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_2" id="PRIOR_MOTILITY_LL_2"><?php echo $PRIOR_MOTILITY_LL_2; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_3" id="PRIOR_MOTILITY_LL_3"><?php echo $PRIOR_MOTILITY_LL_3; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_4" id="PRIOR_MOTILITY_LL_4"><?php echo $PRIOR_MOTILITY_LL_4; ?></div>
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
                        <div class="divCell" id="PRIOR_MOTILITY_LI_1" name="PRIOR_MOTILITY_LI_1"><?php echo $PRIOR_MOTILITY_LI_1; ?></div>
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
                        <div class="divCell" id="PRIOR_MOTILITY_LI_2" name="PRIOR_MOTILITY_LI_2"><?php echo $PRIOR_MOTILITY_LI_2; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3"   id="PRIOR_MOTILITY_LI_3"><?php echo $PRIOR_MOTILITY_LI_3; ?></div>
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
                        <div class="divCell" id="PRIOR_MOTILITY_LI_4" name="PRIOR_MOTILITY_LI_4"><?php echo $PRIOR_MOTILITY_LI_4; ?></div>
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
        <div style="position: absolute;bottom:0.05in;clear:both;font-size:0.9em;text-align:left;padding-left:25px;"> 
            <b><?php echo xlt('Comments'); ?>:</b><br />
            <textarea disabled id="PRIOR_NEURO_COMMENTS" name="PRIOR_NEURO_COMMENTS" style="width:4.0in;height:3.0em;"><?php echo text($NEURO_COMMENTS); ?></textarea>
        </div>
        <input type="hidden" name="PRIOR_PREFS_ACT_SHOW"  id="PRIOR_PREFS_ACT_SHOW" value="<?php echo attr($ACT_SHOW); ?>">
            
        <script type="text/javascript">
            $("#PRIOR_ACTTRIGGER").mouseover(function() {
                                                   $("#PRIOR_ACTTRIGGER").toggleClass('buttonRefraction_selected').toggleClass('underline');
                                                   });
            $("#PRIOR_ACTTRIGGER").mouseout(function() {
                                                  $("#PRIOR_ACTTRIGGER").toggleClass('buttonRefraction_selected').toggleClass('underline');
                                                  });
            $("#PRIOR_ACTTRIGGER").click(function() {
                                               $("#PRIOR_ACTMAIN").toggleClass('nodisplay'); //.toggleClass('fullscreen');
                                               $("#PRIOR_NPCNPA").toggleClass('nodisplay');
                                               $("#PRIOR_ACTNORMAL_CHECK").toggleClass('nodisplay');
                                               $("#PRIOR_ACTTRIGGER").toggleClass('underline');
                                               $("#PRIOR_Close_ACTMAIN").toggleClass('fa-random').toggleClass('fa-eye');
                                               });
            $("[name^='PRIOR_ACT_tab_']").click(function()  {
                                                var section = this.id.match(/PRIOR_ACT_tab_(.*)/)[1];
                                                $("[name^='PRIOR_ACT_']").addClass('nodisplay');
                                                $("[name^='PRIOR_ACT_tab_']").removeClass('nodisplay').removeClass('ACT_selected').addClass('ACT_deselected');
                                                $("#PRIOR_ACT_tab_" + section).addClass('ACT_selected').removeClass('ACT_deselected');
                                                $("#PRIOR_ACT_" + section).removeClass('nodisplay');
                                                $("#PRIOR_PREFS_ACT_SHOW").val(section);
                                                });

            $("[name^='PRIOR_Close_']").click(function()  {
                                              var section = this.id.match(/PRIOR_Close_(.*)$/)[1];
                                              if (section =="ACTMAIN") {
                                                $("#PRIOR_ACTTRIGGER").trigger( "click" );
                                              } else {
                                                $("#LayerVision_"+section+"_lightswitch").click();
                                              }
                                              });
            if ($("#PREFS_ACT_VIEW").val() == '1') {
                $("#PRIOR_ACTMAIN").toggleClass('nodisplay'); //.toggleClass('fullscreen');
                $("#PRIOR_NPCNPA").toggleClass('nodisplay');
                $("#PRIOR_ACTNORMAL_CHECK").toggleClass('nodisplay');
                $("#PRIOR_ACTTRIGGER").toggleClass('underline');
                var show = $("#PREFS_ACT_SHOW").val();
                $("#PRIOR_ACT_tab_"+show).trigger('click');
            }
        </script>
          <?php 
        return;
    } elseif ($zone =="ALL") {
        echo priors_select($zone,$orig_id,$id_to_show,$pid);
        return;
    } elseif ($zone =="PMSFH") {
        require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
        require_once($GLOBALS['srcdir'].'/options.inc.php');
         // Check authorization.
        if (acl_check('patients','med')) {
            $tmp = getPatientData($pid);
        }
         // Collect parameter(s)
        $category = empty($_REQUEST['category']) ? '' : $_REQUEST['category'];
   
        //we want to display PMSFH in this little box...
        //let's start to see what it will look like.
        //let's try a 2 column approach 1st
        //why not use the Quick Picks 2 column approach?
        
        //ok
        /*
        <div id="stats_div" name="stats_div">
            tesy
        </div>
        */
        ?>
        <div id="PMFSH_block_1" name="PMFSH_block_1" class="QP_block borderShadow text_clinical" >
            <?php
            $encount = 0;
            $lasttype = "";
            $first = 1; // flag for first section
            //echo "<pre>";
            //var_dump($ISSUE_TYPES);
            /*
            <script type="text/javascript" language="JavaScript">
            $("#stats_div").load("../../../interface/patient_file/summary/stats.php");
    
            </script>
                </div>
            */
            ?><script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
            <?php
                    
            foreach ($ISSUE_TYPES as $focustype => $focustitles) {
                $counter++; 
                if ($category) {
                    //    Only show this category
                   if ($focustype != $category) continue;
                }

                $counter++; //at 19 lines we need to make a new row.
                $column_length = '18';
                if ($counter > $column_length and !$second_row) {
                        // start a new column
                    $second_row='1';
                                      ?>
            </div>
                
            <div style="margin-left:10px;" id="PMFSH_block_2" name="PMFSH_block_2" class="QP_block borderShadow text_clinical" >
                             <?php
                }
                $disptype = $focustitles[0];
                echo '<span class="left" style="font-weight:800;">'.$disptype."</span><br />";
                $pres = sqlStatement("SELECT * FROM lists WHERE pid = ? AND type = ? " .
                    "ORDER BY begdate", array($pid,$focustype) );
                echo "
                <table style='margin-bottom:20px;border:1pt solid black;max-height:1.5in;max-width:1.9in;background-color:lightgrey;font-size:0.9em;'>
                    <tr>
                        <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>";

                // if no issues (will place a 'None' text vs. toggle algorithm here)
                if (sqlNumRows($pres) < 1) {
                    echo  xla("None") ."<br /><br /><br /><br />";
                    echo " </td></tr></table>";
                    $counter = $counter+4; 
                    continue;
                }

                $section_count='4';

              //  if ($section_count <2)
                while ($row = sqlFetchArray($pres)) {
                    $counter++;
                    $section_count--;
                    if ($counter > $column_length) {
                        // start a new column
                                      ?>
                        <br /></td></tr></table></div>
                        <br />
                        <div id="PMFSH_block_2" name="PMFSH_block_2" class="QP_block borderShadow text_clinical" >
                           <table style='margin-bottom:20px;border:2pt solid black;max-height:1.5in;max-width:1.5in;background-color:lightgrey;font-size:0.9em;'>
                    <tr>
                        <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>
                                     <?php
                    }
                    $rowid = $row['id'];
                    $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";

                    $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
                      "list_id = ?", array($rowid) );

                    // encount is used to toggle the color of the table-row output below
                    ++$encount;
                    $bgclass = (($encount & 1) ? "bg1" : "bg2");

                    // look up the diag codes
                    $codetext = "";
                    if ($row['diagnosis'] != "") {
                        $diags = explode(";", $row['diagnosis']);
                        foreach ($diags as $diag) {
                            $codedesc = lookup_code_descriptions($diag);
                            $codetext .= xlt($diag) . " (" . xlt($codedesc) . ")<br>";
                        }
                    }

                    // calculate the status
                    if ($row['outcome'] == "1" && $row['enddate'] != NULL) {
                      // Resolved
                      $statusCompute = generate_display_field(array('data_type'=>'1','list_id'=>'outcome'), $row['outcome']);
                    }
                    else if($row['enddate'] == NULL) {
                   //   $statusCompute = htmlspecialchars( xl("Active") ,ENT_NOQUOTES);
                    }
                    else {
                   //   $statusCompute = htmlspecialchars( xl("Inactive") ,ENT_NOQUOTES);
                    }
                    $click_class='statrow';
                    if($row['erx_source']==1 && $focustype=='allergy')
                    $click_class='';
                    elseif($row['erx_uploaded']==1 && $focustype=='medication')
                    $click_class='';
                    // output the TD row of info
                    if ($row['enddate'] == NULL) {
                    //    echo " <tr class='$bgclass detail $click_class' style='color:red;font-weight:bold' id='$rowid'>\n";
                    }
                    else {
                   //   echo " <tr class='$bgclass detail $click_class' id='$rowid'>\n";
                    }
                    echo xlt($disptitle) . "\n<br />";
                      //  echo "  <td>" . htmlspecialchars($row['begdate'],ENT_NOQUOTES) . "&nbsp;</td>\n";
                      //  echo "  <td>" . htmlspecialchars($row['enddate'],ENT_NOQUOTES) . "&nbsp;</td>\n";
                        // both codetext and statusCompute have already been escaped above with htmlspecialchars)
                     //   echo "  <td>" . $codetext . "</td>\n";
                     //   echo "  <td>" . $statusCompute . "&nbsp;</td>\n";
                     //   echo "  <td class='nowrap'>";
                     //   echo generate_display_field(array('data_type'=>'1','list_id'=>'occurrence'), $row['occurrence']);
                     //   echo "</td>\n";
                    if ($focustype == "allergy") {
                      echo "  <td>" . htmlspecialchars($row['reaction'],ENT_NOQUOTES) . "&nbsp;</td>\n";
                    }
                    if ($GLOBALS['athletic_team']) {
                   //     echo "  <td class='center'>" . $row['extrainfo'] . "</td>\n"; // games missed
                    }
                    else {
                     //   echo "  <td>" . htmlspecialchars($row['referredby'],ENT_NOQUOTES) . "</td>\n";
                    }
                    //echo "  <td>" . htmlspecialchars($row['comments'],ENT_NOQUOTES) . "</td>\n";
                    //echo "  <td id='e_$rowid' class='noclick center' title='" . htmlspecialchars( xl('View related encounters'), ENT_QUOTES) . "'>";
                    //echo "  <input type='button' value='" . htmlspecialchars($ierow['count'],ENT_QUOTES) . "' class='editenc' id='" . htmlspecialchars($rowid,ENT_QUOTES) . "' />";
                    //echo "  </td>";
                    
                }
                echo " <br /></td></tr></table>\n";
                $counter++; 

            }
            echo '<span class="left" style="font-weight:800;">ROS</span><br />';
            echo "
                <table style='margin-bottom:20px;border:1pt solid black;max-height:1.5in;max-width:1.9in;background-color:lightgrey;font-size:0.9em;'>
                    <tr>
                        <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>";

            // if no issues (will place a 'None' text vs. toggle algorithm here)
            if (sqlNumRows($pres) < 1) {
                echo  xla("None") ."<br /><br /><br /><br />";
                echo " </td></tr></table>";
            }
            ?>
        </div> <!-- end patient_stats -->

<?php 
/*
        <!-- Trial of left menu -->

            <script type="text/javascript">

                //Nested Side Bar Menu (Mar 20th, 09)
                //By Dynamic Drive: http://www.dynamicdrive.com/style/

                var menuids=["sidebarmenu1"] //Enter id(s) of each Side Bar Menu's main UL, separated by commas

                function initsidebarmenu(){
                for (var x=0; x<menuids.length; x++){
                  var ultags=document.getElementById(menuids[x]).getElementsByTagName("ul")
                    for (var t=0; t<ultags.length; t++){
                    ultags[t].parentNode.getElementsByTagName("a")[0].className+=" subfolderstyle"
                  if (ultags[t].parentNode.parentNode.id==menuids[x]) //if this is a first level submenu
                   ultags[t].style.left=ultags[t].parentNode.offsetWidth+"px" //dynamically position first level submenus to be width of main menu item
                  else //else if this is a sub level submenu (ul)
                    ultags[t].style.left=ultags[t-1].getElementsByTagName("a")[0].offsetWidth+"px" //position menu to the right of menu item that activated it
                    ultags[t].parentNode.onmouseover=function(){
                    this.getElementsByTagName("ul")[0].style.display="block"
                    }
                    ultags[t].parentNode.onmouseout=function(){
                    this.getElementsByTagName("ul")[0].style.display="none"
                    }
                    }
                  for (var t=ultags.length-1; t>-1; t--){ //loop through all sub menus again, and use "display:none" to hide menus (to prevent possible page scrollbars
                  ultags[t].style.visibility="visible"
                  ultags[t].style.display="none"
                  }
                  }
                }

                if (window.addEventListener)
                window.addEventListener("load", initsidebarmenu, false)
                else if (window.attachEvent)
                window.attachEvent("onload", initsidebarmenu)

            </script>

            <div class="sidebarmenu">
                <ul id="sidebarmenu1">
            <?php
     
                    
            foreach ($ISSUE_TYPES as $focustype => $focustitles) {
                $counter++; 
                if ($category) {
                    //    Only show this category
                   if ($focustype != $category) continue;
                }

                
                $disptype = $focustitles[0];
                echo '<li><span class="left" style="font-weight:800;">'.$disptype."</span><br />";
                $pres = sqlStatement("SELECT * FROM lists WHERE pid = ? AND type = ? " .
                    "ORDER BY begdate", array($pid,$focustype) );
                echo "
                <table style='margin-bottom:20px;border:1pt solid black;max-height:1.5in;max-width:1.9in;background-color:lightgrey;font-size:0.9em;'>
                    <tr>
                        <td style='min-height:1.2in;min-width:1.75in;padding-left:5px;'>";

                // if no issues (will place a 'None' text vs. toggle algorithm here)
                if (sqlNumRows($pres) < 1) {
                    echo  xla("None") ."<br /><br /><br /><br />";
                    echo " </td></tr></table></li>";
                    $counter = $counter+4; 
                    continue;
                }

                $section_count='4';

              //  if ($section_count <2)
                while ($row = sqlFetchArray($pres)) {
                    $rowid = $row['id'];
                    $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";

                    $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
                      "list_id = ?", array($rowid) );

                   echo xlt($disptitle) . "\n<br />";
                      //  echo "  <td>" . htmlspecialchars($row['begdate'],ENT_NOQUOTES) . "&nbsp;</td>\n";
                      //  echo "  <td>" . htmlspecialchars($row['enddate'],ENT_NOQUOTES) . "&nbsp;</td>\n";
                        // both codetext and statusCompute have already been escaped above with htmlspecialchars)
                     //   echo "  <td>" . $codetext . "</td>\n";
                     //   echo "  <td>" . $statusCompute . "&nbsp;</td>\n";
                     //   echo "  <td class='nowrap'>";
                     //   echo generate_display_field(array('data_type'=>'1','list_id'=>'occurrence'), $row['occurrence']);
                     //   echo "</td>\n";
                    if ($focustype == "allergy") {
                      echo "  <td>" . htmlspecialchars($row['reaction'],ENT_NOQUOTES) . "&nbsp;</td>\n";
                    }
                    
                    
                }
                echo " <br /></td></tr></table></li>\n";
                $counter++; 

            }

            echo '<li><span class="left" style="font-weight:800;">ROS</span><br />';
            echo "
                <table style='margin-bottom:20px;border:1pt solid black;max-height:1.5in;max-width:1.9in;background-color:lightgrey;font-size:0.9em;'>
                    <tr>
                        <td style='min-height:1.2in;min-width:1.75in;padding-left:5px;'>";

            // if no issues (will place a 'None' text vs. toggle algorithm here)
 //           if (sqlNumRows($presROS) < 1) {
                echo  xla("None") ."<br /><br /><br /><br />";
                echo " </td></tr></table><li>";
   //         }

                
            ?>

</ul>
</div>
<!-- END TRIAL LEFT MENU -->
        <?php
        */
        return;
    }
}

/**
 *  This function returns display the sketch diagram for a zone (4 input values)
 * 
 *  If there is already a drawing for this zone in this encounter, it is pulled from
 *  from its stored location:
 *  $GLOBALS['web_root']."/sites/".$_SESSION['site_id']."/eye_mag/".$pid."/".$encounter."/".$side."_".$zone."_VIEW.png?".rand();
 *  
 *  Otherwise a "BASE" image is pulled from:
 *  
 *  The user can replace the given BASE images if they wish.  For Skeych.js and the format we employ, the image 
 *  created must be  in png format and have the dimensions of 432px x 250px.  It is possible to modify the source code to 
 *  accept any image by employing imagecopyresampled but we ran into problems... See more about the image names in save.php
 *
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO 
 * @param string $visit_date Future functionality to limit result set. UTC DATE Formatted 
 * @param string $pid value = patient id
 * @param string OU by default.  Future functionality will allow OD and OS values- not implemented yet.
 * @return true : when called directly outputs the ZONE specific HTML for a prior record + widget for the desired zone 
 */ 
function display_draw_section ($zone,$encounter,$pid,$side ='OU') {
    global $form_folder;
    ?>
    <div id="Draw_<?php echo attr($zone); ?>" name="Draw_<?php echo attr($zone); ?>" style="text-align:center;height: 2.5in;" class="Draw_class canvas">
        <span class="closeButton fa fa-file-text-o" id="BUTTON_TEXT_<?php echo attr($zone); ?>" name="BUTTON_TEXT_<?php echo attr($zone); ?>"></span>

        <div class="tools" style="text-align:center;left:0.02in;width:90%;">
            <a href="#Sketch_<?php echo attr($zone); ?>" data-color="#f00" > &nbsp;&nbsp;</a>
            <a style="width: 5px; background: yellow;" data-color="#ff0" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: red;" data-color="red" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: #FF5C5C;" data-color="#FF5C5C" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: brown;" data-color="#AC8359" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: fuchsia;" data-color="#f0f" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: black;" data-color="#000" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="width: 5px; background: white;" data-color="#fff" href="#Sketch_<?php echo attr($zone); ?>"> &nbsp;&nbsp;</a>
            <a style="background: #CCC" data-size="1" href="#Sketch_<?php echo attr($zone); ?>"><?php echo xlt('1'); ?></a>
            <a style="background: #CCC" data-size="3" href="#Sketch_<?php echo attr($zone); ?>"><?php echo xlt('3'); ?></a>
            <a style="background: #CCC" data-size="5" href="#Sketch_<?php echo attr($zone); ?>"><?php echo xlt('5'); ?></a>
            <a style="background: #CCC" data-size="10" href="#Sketch_<?php echo attr($zone); ?>"><?php echo xlt('10'); ?></a>
            <a style="background: #CCC" data-size="15" href="#Sketch_<?php echo attr($zone); ?>"><?php echo xlt('15'); ?></a>  
        </div>
        <?php 
        //apache2 vhost config defaults to deny all access to documents directory directly.
        //thus we need to move from this directory, elsewhere?
        //No, ext access to all is not hippa compliant
        // we need a better way to serve the document
        //perhaps just like all the other documents from documents.php?
        //find the document_id by the filename

            $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter."/".$side."_".$zone."_VIEW.png";
            $sql = "SELECT * from documents where url='file://".$file_location."'";
            $doc = sqlQuery($sql);
            if (file_exists($file_location) && ($doc['id'] > '0')) {

                $filetoshow = $GLOBALS['web_root']."/controller.php?document&retrieve&patient_id=$pid&document_id=$doc[id]&as_file=false";
            } else {
                $filetoshow = "../../forms/".$form_folder."/images/".$side."_".$zone."_BASE.png?".rand();
            }
        ?>
        <canvas id="Sketch_<?php echo attr($zone); ?>" class="borderShadow2" style="background: url(<?php echo attr($filetoshow); ?>)  no-repeat center center;background-size: 100% 100%;padding:0in;margin: 0.1in;"></canvas>
        <script type="text/javascript">
            $(function() {
                $('canvas').attr('height', '250px'); 
                $('canvas').attr('width', '432px'); 
                $('#Sketch_<?php echo attr($zone); ?>').sketch({defaultSize:"1"});
            });
        </script>
        <br />
    </div>
    <?php
}

/**
 *  This function returns HTML to replace a requested section with copy_forward values (3 input values)
 *  It will also replace the drawings if ALL is selected
 *  
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO, EXT_DRAW, ANTSEG_DRAW, RETINA_DRAW, NEURO_DRAW 
 * @param string $form_id is the form_eye_mag.id where the data to carry forward is located
 * @param string $pid value = patient id
 * @return true : when called directly outputs the ZONE specific HTML for a prior record + widget for the desired zone 
 */ 
function copy_forward($zone,$copy_from,$copy_to,$pid) {
    $query="select form_encounter.date as encounter_date,form_eye_mag.* from form_eye_mag ,forms,form_encounter 
                where 
                form_encounter.encounter = forms.encounter and 
                form_eye_mag.id=forms.form_id and
                forms.pid =form_eye_mag.pid and 
                form_eye_mag.pid=? 
                and form_eye_mag.id =? ";        

    $objQuery =sqlQuery($query,array($pid,$copy_from));
    @extract($objQuery);
    if ($zone =="EXT") {
        $result['RUL']="$RUL";
        $result['LUL']="$LUL";
        $result['RLL']="$RLL";
        $result['LLL']="$LLL";
        $result['RBROW']="$RBROW";
        $result['LBROW']="$LBROW";
        $result['RMCT']="$RMCT";
        $result['LMCT']="$LMCT";
        $result['RADNEXA']="$RADNEXA";
        $result['LADNEXA']="$LADNEXA";
        $result['RMRD']="$RMRD";
        $result['LMRD']="$LMRD";
        $result['RLF']="$RLF";
        $result['LLF']="$LLF";
        $result['RVFISSURE']="$RVFISSURE";
        $result['LVFISSURE']="$LVFISSURE";
        $result['ODHERTEL']="$ODHERTEL";
        $result['OSHERTEL']="$OSHERTEL";
        $result['HERTELBASE']="$HERTELBASE";
        $result['ODPIC']="$ODPIC";
        $result['OSPIC']="$OSPIC";
        $result['EXT_COMMENTS']="$EXT_COMMENTS";
        $result["json"] = json_encode($result);
        echo json_encode($result); 
    } elseif ($zone =="ANTSEG") {
        $result['OSCONJ']="$OSCONJ";
        $result['ODCONJ']="$ODCONJ";
        $result['ODCORNEA']="$ODCORNEA";
        $result['OSCORNEA']="$OSCORNEA";
        $result['ODAC']="$ODAC";
        $result['OSAC']="$OSAC";
        $result['ODLENS']="$ODLENS";
        $result['OSLENS']="$OSLENS";
        $result['ODIRIS']="$ODIRIS";
        $result['OSIRIS']="$OSIRIS";
        $result['ODKTHICKNESS']="$ODKTHICKNESS";
        $result['OSKTHICKNESS']="$OSKTHICKNESS";
        $result['ODGONIO']="$ODGONIO";
        $result['OSGONIO']="$OSGONIO";
        $result['ANTSEG_COMMENTS']="$ANTSEG_COMMENTS";
        $result["json"] = json_encode($result);
        echo json_encode($result); 
    } elseif ($zone =="RETINA") {
        $result['ODDISC']="$ODDISC";
        $result['OSDISC']="$OSDISC";
        $result['ODCUP']="$ODCUP";
        $result['OSCUP']="$OSCUP";
        $result['ODMACULA']="$ODMACULA";
        $result['OSMACULA']="$OSMACULA";
        $result['ODVESSELS']="$ODVESSELS";
        $result['OSVESSELS']="$OSVESSELS";
        $result['ODPERIPH']="$ODPERIPH";
        $result['OSPERIPH']="$OSPERIPH";
        $result['ODDRAWING']="$ODDRAWING";
        $result['OSDRAWING']="$OSDRAWING";
        $result['ODCMT']="$ODCMT";
        $result['OSCMT']="$OSCMT";
        $result['RETINA_COMMENTS']="$RETINA_COMMENTS";
        $result["json"] = json_encode($result);
        echo json_encode($result); 
    } elseif ($zone =="NEURO") {
        $result['ACT']="$ACT";
        $result['ACTPRIMCCDIST']="$ACTPRIMCCDIST";
        $result['ACT1CCDIST']="$ACT1CCDIST";
        $result['ACT2CCDIST']="$ACT2CCDIST";
        $result['ACT3CCDIST']="$ACT3CCDIST";
        $result['ACT4CCDIST']="$ACT4CCDIST";
        $result['ACT6CCDIST']="$ACT6CCDIST";
        $result['ACT7CCDIST']="$ACT7CCDIST";
        $result['ACT8CCDIST']="$ACT8CCDIST";
        $result['ACT9CCDIST']="$ACT9CCDIST";
        $result['ACTRTILTCCDIST']="$ACTRTILTCCDIST";
        $result['ACTLTILTCCDIST']="$ACTLTILTCCDIST";
        $result['ACT1SCDIST']="$ACT1SCDIST";
        $result['ACT2SCDIST']="$ACT2SCDIST";
        $result['ACT3SCDIST']="$ACT3SCDIST";
        $result['ACT4SCDIST']="$ACT4SCDIST";
        $result['ACTPRIMSCDIST']="$ACTPRIMSCDIST";
        $result['ACT6SCDIST']="$ACT6SCDIST";
        $result['ACT7SCDIST']="$ACT7SCDIST";
        $result['ACT8SCDIST']="$ACT8SCDIST";
        $result['ACT9SCDIST']="$ACT9SCDIST";
        $result['ACTRTILTSCDIST']="$ACTRTILTSCDIST";
        $result['ACTLTILTSCDIST']="$ACTLTILTSCDIST";
        $result['ACT1SCNEAR']="$ACT1SCNEAR";
        $result['ACT2SCNEAR']="$ACT2SCNEAR";
        $result['ACT3SCNEAR']="$ACT3SCNEAR";
        $result['ACT4SCNEAR']="$ACT4SCNEAR";
        $result['ACTPRIMCCNEAR']="$ACTPRIMCCNEAR";
        $result['ACT6CCNEAR']="$ACT6CCNEAR";
        $result['ACT7CCNEAR']="$ACT7CCNEAR";
        $result['ACT8CCNEAR']="$ACT8CCNEAR";
        $result['ACT9CCNEAR']="$ACT9CCNEAR";
        $result['ACTRTILTCCNEAR']="$ACTRTILTCCNEAR";
        $result['ACTLTILTCCNEAR']="$ACTLTILTCCNEAR";
        $result['ACTPRIMSCNEAR']="$ACTPRIMSCNEAR";
        $result['ACT6SCNEAR']="$ACT6SCNEAR";
        $result['ACT7SCNEAR']="$ACT7SCNEAR";
        $result['ACT8SCNEAR']="$ACT8SCNEAR";
        $result['ACT9SCNEAR']="$ACT9SCNEAR";
        $result['ACTRTILTSCNEAR']="$ACTRTILTSCNEAR";
        $result['ACTLTILTSCNEAR']="$ACTLTILTSCNEAR";
        $result['ACT1CCNEAR']="$ACT1CCNEAR";
        $result['ACT2CCNEAR']="$ACT2CCNEAR";
        $result['ACT3CCNEAR']="$ACT3CCNEAR";
        $result['ACT4CCNEAR']="$ACT4CCNEAR";
        $result['ODVF1']="$ODVF1";
        $result['ODVF2']="$ODVF2";
        $result['ODVF3']="$ODVF3";
        $result['ODVF4']="$ODVF4";
        $result['OSVF1']="$OSVF1";
        $result['OSVF2']="$OSVF2";
        $result['OSVF3']="$OSVF3";
        $result['OSVF4']="$OSVF4";
        $result['MOTILITY_RS']="$MOTILITY_RS";
        $result['MOTILITY_RI']="$MOTILITY_RI";
        $result['MOTILITY_RR']="$MOTILITY_RR";
        $result['MOTILITY_RL']="$MOTILITY_RL";
        $result['MOTILITY_LS']="$MOTILITY_LS";
        $result['MOTILITY_LI']="$MOTILITY_LI";
        $result['MOTILITY_LR']="$MOTILITY_LR";
        $result['MOTILITY_LL']="$MOTILITY_LL";
        $result['NEURO_COMMENTS']="$NEURO_COMMENTS";
        $result['STEREOPSIS']="$STEREOPSIS";
        $result['ODNPA']="$ODNPA";
        $result['OSNPA']="$OSNPA";
        $result['VERTFUSAMPS']="$VERTFUSAMPS";
        $result['DIVERGENCEAMPS']="$DIVERGENCEAMPS";
        $result['NPC']="$NPC";
        $result['CASCDIST']="$CASCDIST";
        $result['CASCNEAR']="$CASCNEAR";
        $result['CACCDIST']="$CACCDIST";
        $result['CACCNEAR']="$CACCNEAR";
        $result['ODCOLOR']="$ODCOLOR";
        $result['OSCOLOR']="$OSCOLOR";
        $result['ODCOINS']="$ODCOINS";
        $result['OSCOINS']="$OSCOINS";
        $result['ODREDDESAT']="$ODREDDESAT";
        $result['OSREDDESAT']="$OSREDDESAT";


        $result['ODPUPILSIZE1']="$ODPUPILSIZE1";
        $result['ODPUPILSIZE2']="$ODPUPILSIZE2";
        $result['ODPUPILREACTIVITY']="$ODPUPILREACTIVITY";
        $result['ODAPD']="$ODAPD";
        $result['OSPUPILSIZE1']="$OSPUPILSIZE1";
        $result['OSPUPILSIZE2']="$OSPUPILSIZE2";
        $result['OSPUPILREACTIVITY']="$OSPUPILREACTIVITY";
        $result['OSAPD']="$OSAPD";
        $result['DIMODPUPILSIZE1']="$DIMODPUPILSIZE1";
        $result['DIMODPUPILSIZE2']="$DIMODPUPILSIZE2";
        $result['DIMODPUPILREACTIVITY']="$DIMODPUPILREACTIVITY";
        $result['DIMOSPUPILSIZE1']="$DIMOSPUPILSIZE1";
        $result['DIMOSPUPILSIZE2']="$DIMOSPUPILSIZE2";
        $result['DIMOSPUPILREACTIVITY']="$DIMOSPUPILREACTIVITY";
        $result['PUPIL_COMMENTS']="$PUPIL_COMMENTS";
        $result['ODVFCONFRONTATION1']="$ODVFCONFRONTATION1";
        $result['ODVFCONFRONTATION2']="$ODVFCONFRONTATION2";
        $result['ODVFCONFRONTATION3']="$ODVFCONFRONTATION3";
        $result['ODVFCONFRONTATION4']="$ODVFCONFRONTATION4";
        $result['ODVFCONFRONTATION5']="$ODVFCONFRONTATION5";
        $result['OSVFCONFRONTATION1']="$OSVFCONFRONTATION1";
        $result['OSVFCONFRONTATION2']="$OSVFCONFRONTATION2";
        $result['OSVFCONFRONTATION3']="$OSVFCONFRONTATION3";
        $result['OSVFCONFRONTATION4']="$OSVFCONFRONTATION4";
        $result['OSVFCONFRONTATION5']="$OSVFCONFRONTATION5";
        $result["json"] = json_encode($result);
        echo json_encode($result); 
    } elseif ($zone =="ALL") {
        $result['RUL']="$RUL";
        $result['LUL']="$LUL";
        $result['RLL']="$RLL";
        $result['LLL']="$LLL";
        $result['RBROW']="$RBROW";
        $result['LBROW']="$LBROW";
        $result['RMCT']="$RMCT";
        $result['LMCT']="$LMCT";
        $result['RADNEXA']="$RADNEXA";
        $result['LADNEXA']="$LADNEXA";
        $result['RMRD']="$RMRD";
        $result['LMRD']="$LMRD";
        $result['RLF']="$RLF";
        $result['LLF']="$LLF";
        $result['RVFISSURE']="$RVFISSURE";
        $result['LVFISSURE']="$LVFISSURE";
        $result['ODHERTEL']="$ODHERTEL";
        $result['OSHERTEL']="$OSHERTEL";
        $result['HERTELBASE']="$HERTELBASE";
        $result['ODPIC']="$ODPIC";
        $result['OSPIC']="$OSPIC";
        $result['EXT_COMMENTS']="$EXT_COMMENTS";
        
        $result['OSCONJ']="$OSCONJ";
        $result['ODCONJ']="$ODCONJ";
        $result['ODCORNEA']="$ODCORNEA";
        $result['OSCORNEA']="$OSCORNEA";
        $result['ODAC']="$ODAC";
        $result['OSAC']="$OSAC";
        $result['ODLENS']="$ODLENS";
        $result['OSLENS']="$OSLENS";
        $result['ODIRIS']="$ODIRIS";
        $result['OSIRIS']="$OSIRIS";
        $result['ODKTHICKNESS']="$ODKTHICKNESS";
        $result['OSKTHICKNESS']="$OSKTHICKNESS";
        $result['ODGONIO']="$ODGONIO";
        $result['OSGONIO']="$OSGONIO";
        $result['ANTSEG_COMMENTS']="$ANTSEG_COMMENTS";
        
        $result['ODDISC']="$ODDISC";
        $result['OSDISC']="$OSDISC";
        $result['ODCUP']="$ODCUP";
        $result['OSCUP']="$OSCUP";
        $result['ODMACULA']="$ODMACULA";
        $result['OSMACULA']="$OSMACULA";
        $result['ODVESSELS']="$ODVESSELS";
        $result['OSVESSELS']="$OSVESSELS";
        $result['ODPERIPH']="$ODPERIPH";
        $result['OSPERIPH']="$OSPERIPH";
        $result['ODDRAWING']="$ODDRAWING";
        $result['OSDRAWING']="$OSDRAWING";
        $result['ODCMT']="$ODCMT";
        $result['OSCMT']="$OSCMT";
        $result['RETINA_COMMENTS']="$RETINA_COMMENTS";

        $result['ACT']="$ACT";
        $result['ACTPRIMCCDIST']="$ACTPRIMCCDIST";
        $result['ACT1CCDIST']="$ACT1CCDIST";
        $result['ACT2CCDIST']="$ACT2CCDIST";
        $result['ACT3CCDIST']="$ACT3CCDIST";
        $result['ACT4CCDIST']="$ACT4CCDIST";
        $result['ACT6CCDIST']="$ACT6CCDIST";
        $result['ACT7CCDIST']="$ACT7CCDIST";
        $result['ACT8CCDIST']="$ACT8CCDIST";
        $result['ACT9CCDIST']="$ACT9CCDIST";
        $result['ACTRTILTCCDIST']="$ACTRTILTCCDIST";
        $result['ACTLTILTCCDIST']="$ACTLTILTCCDIST";
        $result['ACT1SCDIST']="$ACT1SCDIST";
        $result['ACT2SCDIST']="$ACT2SCDIST";
        $result['ACT3SCDIST']="$ACT3SCDIST";
        $result['ACT4SCDIST']="$ACT4SCDIST";
        $result['ACTPRIMSCDIST']="$ACTPRIMSCDIST";
        $result['ACT6SCDIST']="$ACT6SCDIST";
        $result['ACT7SCDIST']="$ACT7SCDIST";
        $result['ACT8SCDIST']="$ACT8SCDIST";
        $result['ACT9SCDIST']="$ACT9SCDIST";
        $result['ACTRTILTSCDIST']="$ACTRTILTSCDIST";
        $result['ACTLTILTSCDIST']="$ACTLTILTSCDIST";
        $result['ACT1SCNEAR']="$ACT1SCNEAR";
        $result['ACT2SCNEAR']="$ACT2SCNEAR";
        $result['ACT3SCNEAR']="$ACT3SCNEAR";
        $result['ACT4SCNEAR']="$ACT4SCNEAR";
        $result['ACTPRIMCCNEAR']="$ACTPRIMCCNEAR";
        $result['ACT6CCNEAR']="$ACT6CCNEAR";
        $result['ACT7CCNEAR']="$ACT7CCNEAR";
        $result['ACT8CCNEAR']="$ACT8CCNEAR";
        $result['ACT9CCNEAR']="$ACT9CCNEAR";
        $result['ACTRTILTCCNEAR']="$ACTRTILTCCNEAR";
        $result['ACTLTILTCCNEAR']="$ACTLTILTCCNEAR";
        $result['ACTPRIMSCNEAR']="$ACTPRIMSCNEAR";
        $result['ACT6SCNEAR']="$ACT6SCNEAR";
        $result['ACT7SCNEAR']="$ACT7SCNEAR";
        $result['ACT8SCNEAR']="$ACT8SCNEAR";
        $result['ACT9SCNEAR']="$ACT9SCNEAR";
        $result['ACTRTILTSCNEAR']="$ACTRTILTSCNEAR";
        $result['ACTLTILTSCNEAR']="$ACTLTILTSCNEAR";
        $result['ACT1CCNEAR']="$ACT1CCNEAR";
        $result['ACT2CCNEAR']="$ACT2CCNEAR";
        $result['ACT3CCNEAR']="$ACT3CCNEAR";
        $result['ACT4CCNEAR']="$ACT4CCNEAR";
        $result['ODVF1']="$ODVF1";
        $result['ODVF2']="$ODVF2";
        $result['ODVF3']="$ODVF3";
        $result['ODVF4']="$ODVF4";
        $result['OSVF1']="$OSVF1";
        $result['OSVF2']="$OSVF2";
        $result['OSVF3']="$OSVF3";
        $result['OSVF4']="$OSVF4";
        $result['MOTILITY_RS']="$MOTILITY_RS";
        $result['MOTILITY_RI']="$MOTILITY_RI";
        $result['MOTILITY_RR']="$MOTILITY_RR";
        $result['MOTILITY_RL']="$MOTILITY_RL";
        $result['MOTILITY_LS']="$MOTILITY_LS";
        $result['MOTILITY_LI']="$MOTILITY_LI";
        $result['MOTILITY_LR']="$MOTILITY_LR";
        $result['MOTILITY_LL']="$MOTILITY_LL";
        $result['NEURO_COMMENTS']="$NEURO_COMMENTS";
        $result['STEREOPSIS']="$STEREOPSIS";
        $result['ODNPA']="$ODNPA";
        $result['OSNPA']="$OSNPA";
        $result['VERTFUSAMPS']="$VERTFUSAMPS";
        $result['DIVERGENCEAMPS']="$DIVERGENCEAMPS";
        $result['NPC']="$NPC";
        $result['CASCDIST']="$CASCDIST";
        $result['CASCNEAR']="$CASCNEAR";
        $result['CACCDIST']="$CACCDIST";
        $result['CACCNEAR']="$CACCNEAR";
        $result['ODCOLOR']="$ODCOLOR";
        $result['OSCOLOR']="$OSCOLOR";
        $result['ODCOINS']="$ODCOINS";
        $result['OSCOINS']="$OSCOINS";
        $result['ODREDDESAT']="$ODREDDESAT";
        $result['OSREDDESAT']="$OSREDDESAT";


        $result['ODPUPILSIZE1']="$ODPUPILSIZE1";
        $result['ODPUPILSIZE2']="$ODPUPILSIZE2";
        $result['ODPUPILREACTIVITY']="$ODPUPILREACTIVITY";
        $result['ODAPD']="$ODAPD";
        $result['OSPUPILSIZE1']="$OSPUPILSIZE1";
        $result['OSPUPILSIZE2']="$OSPUPILSIZE2";
        $result['OSPUPILREACTIVITY']="$OSPUPILREACTIVITY";
        $result['OSAPD']="$OSAPD";
        $result['DIMODPUPILSIZE1']="$DIMODPUPILSIZE1";
        $result['DIMODPUPILSIZE2']="$DIMODPUPILSIZE2";
        $result['DIMODPUPILREACTIVITY']="$DIMODPUPILREACTIVITY";
        $result['DIMOSPUPILSIZE1']="$DIMOSPUPILSIZE1";
        $result['DIMOSPUPILSIZE2']="$DIMOSPUPILSIZE2";
        $result['DIMOSPUPILREACTIVITY']="$DIMOSPUPILREACTIVITY";
        $result['PUPIL_COMMENTS']="$PUPIL_COMMENTS";
        $result['ODVFCONFRONTATION1']="$ODVFCONFRONTATION1";
        $result['ODVFCONFRONTATION2']="$ODVFCONFRONTATION2";
        $result['ODVFCONFRONTATION3']="$ODVFCONFRONTATION3";
        $result['ODVFCONFRONTATION4']="$ODVFCONFRONTATION4";
        $result['ODVFCONFRONTATION5']="$ODVFCONFRONTATION5";
        $result['OSVFCONFRONTATION1']="$OSVFCONFRONTATION1";
        $result['OSVFCONFRONTATION2']="$OSVFCONFRONTATION2";
        $result['OSVFCONFRONTATION3']="$OSVFCONFRONTATION3";
        $result['OSVFCONFRONTATION4']="$OSVFCONFRONTATION4";
        $result['OSVFCONFRONTATION5']="$OSVFCONFRONTATION5";
        $result["json"] = json_encode($result);
        echo json_encode($result); 

    }
}

/**
*  This function builds an array of documents for this patient ($pid).
*  We first list all the categories this practice has created by name and by category_id  
*  
*  Each document info from documents table is added to these as arrays
*  
*/

function document_engine($pid) {
    $sql1 =  sqlStatement("Select * from categories");
    while ($row1 = sqlFetchArray($sql1)) {
        $categories[] = $row1;
        $my_name[$row1['id']] = $row1['name'];
        $children_names[$row1['parent']][]=$row1['name'];
        $parent_name[$row1['name']] = $my_name[$row1['parent']];
        if ($row1['value'] >'') {
            //if there is a value, tells us what segment of exam ($zone) this belongs in...
            $zones[$row1['value']][] = $row1;
        } else {
            if ($row1['name'] != "Categories") {
                $zones['OTHER'][] = $row1;
            }
        }
    }

    $query = "Select *
                from 
                categories, documents,categories_to_documents
                where documents.foreign_id=? and documents.id=categories_to_documents.document_id and
                categories_to_documents.category_id=categories.id ORDER BY categories.name";
         //       echo $query;
    $sql2 =  sqlStatement($query,array($pid));
    while ($row2 = sqlFetchArray($sql2)) {
        $documents[]= $row2;
        $docs_in_cat_id[$row2['category_id']][] = $row2;
        if ($row2['value'] > '') {
            $docs_in_zone[$row2['value']][] = $row2;
        } else {
                $docs_in_zone['OTHER'][]=$row2;
        }
        $docs_in_name[$row2['name']][] = $row2;
    }
    $documents['categories']=$categories;
    $documents['my_name']=$my_name;
    $documents['children_names']=$children_names;
    $documents['parent_name'] = $parent_name;
    $documents['zones'] = $zones;
    $documents['docs_in_zone'] = $docs_in_zone;
    $documents['docs_in_cat_id'] = $docs_in_cat_id;
    $documents['docs_in_name'] = $docs_in_name;
    
    return array($documents);
}

/**
 *  This function returns hooks/links for the Document Library, 
 *      Reports (to do), upload(to do)and image DB(done)
 *      based on the category/zone
 *
 *  @param string $pid value = patient id
 *  @param string $encounter is the encounter_id 
 *  @param string $category_value options EXT,ANTSEG,POSTSEG,NEURO,OTHER
 *                These values are taken from the "value" field in the category table
 *                They allow us to regroup the categories how we like them.
 *  @return array($imaging,$episode)
 */ 
function display($pid,$encounter,$category_value) {
    global $form_folder;
    global $id;
    global $documents;
        /**
        *  I wish to open a popup here perhaps fancy-box to upload a file here
        *  We will need to know what the id of the section is in the categories table
        *  Until written, the will lead to the PAGE to do this action.  The pop-up will be much 
        *  preferred.  Perhaps a "show the other type" button will store a preference?
        *  Maybe this is something for the yet-to-be used Settings button?  Maybe a small div appears
        *  below this with the Seclect File and Upload buttons?  Nothing needs to pop up.
        *  Simply replace buttons w/ ajax swirl until the completed response is displayed.
        *  The second button is to view the files held within.  How they are displayed gets 
        *  us into the DICOM image viewer features, some of which have been developed elsewhere
        *  on sourceforge I believe.  For now it is not unreasonable to be able to flip through
        *  all the images in this subtype like the treemenu or Apple does.  Use Anything Slider.
        */

        /*
        * 1. get the id for the categories under imaging:
        *  We created them on form installation so they will be site 
        *  specific - any previous categories added affects the id, 
        *  and they can name them soemthing else in the DB, so we can't use name
        *  what if they created a sub category for imaging and put images there?
        *  This will not get those yet?  Do we need them? Not now, just get this working.
        */
        /**
        *   Each section will need a designator as to the section it belongs in.
        *   The categories table does not have that but it has an unused value field.
        *   This is where we link it to the image database.  We add this link value  
        *   on install but end user can change or add others as the devices evolve.
        *   New names new categories.  OCT would not have been a category 5 years ago.
        *   Who knows what is next?  Gene-lab construction?  Sure will.  
        *   So the name is user assigned as is the location.  
        *   Thus we need to build out the Documents section by adding another layer "zones"
        *   to the treemenu backbone.  
        */
        if (!$documents) {
            list($documents) = document_engine($pid);
        }
       
    for ($j=0; $j < count($documents['zones'][$category_value]); $j++) {
        $episode .= "<tr>
        <td class='right'><b>".$documents['zones'][$category_value][$j]['name']."</b>:&nbsp;</td>
        <td>
            <a href='../../../controller.php?document&upload&patient_id=".$pid."&parent_id=".$documents['zones'][$category_value][$j]['id']."&'>
            <img src='../../forms/".$form_folder."/images/upload_file.png' class='little_image'>
            </a>
        </td>
        <td>
            <img src='../../forms/".$form_folder."/images/upload_multi.png' class='little_image'>
        </td>
        <td>";
        // theorectically above leads to a document management engine.  Gotta build that...
        // we only need to know if there is one as this link will open the image management engine/display
       if (count($documents['docs_in_cat_id'][$documents['zones'][$category_value][$j]['id']]) > '0') {
            $episode .= '<a href="../../forms/'.$form_folder.'/css/AnythingSlider/simple.php?display=i&category_id='.$documents['zones'][$category_value][$j]['id'].'&encounter='.$encounter.'&category_name='.urlencode(xla($category_value)).'"
                    onclick="return dopopup(\'../../forms/'.$form_folder.'/css/AnythingSlider/simple.php?display=i&category_id='.$documents['zones'][$category_value][$j]['id'].'&encounter='.$encounter.'&category_name='.urlencode(xla($category_value)).'\')">
                    <img src="../../forms/'.$form_folder.'/images/jpg.png" class="little_image" /></a>';
        }
        $episode .= '</td></tr>';
        $i++;
    }
   
    return array($documents,$episode);
}

function menu_overhaul_top($pid,$encounter,$title="Eye Exam") {
    global $form_folder;
    ?>

    <div id="wrapper">
        <!-- Navigation -->
                <!-- Navigation -->
   
    <nav class="navbar-fixed-top navbar-custom" role="navigation" style="margin-bottom: 0">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <img src="/openemr/sites/default/images/login_logo.gif" class="little_image left">
                                        
            <a class="navbar-brand right" href="/openemr" style="font-size:0.8em;font-weight:600;">OpenEMR</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="navbar-custom " id="bs-example-navbar-collapse-1">
        <ul class="navbar-nav">

            <li><a href="#">File <span class="sr-only">(current)</span></a></li>
            <li><a href="#">Edit</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">View <span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
                <li id="BUTTON_TEXT_menu" class="active"> <a href="#">Text <i class="fa fa-align-right fa-clipboard-notes"></i></a></li>
                <li><a href="#" id="BUTTON_DRAW_menu">Draw</a></li>
                <li><a href="#"  onclick='show_QP();'>Quick Picks</a></li>
                <li class="divider"></li>
                <li><a href="#">More</a></li>
                <li class="divider"></li>
                <li><a href="#">One more separated link</a></li>
              </ul>
            </li> 
            <li class="dropdown">
                <a class="dropdown-toggle" role="button" id="menu1" data-toggle="dropdown">Patients <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo $GLOBALS['webroot'] ?>"><i class="fa fa-exchange"></i> Patients </a></li>
                  <li ><a tabindex="-1" href="#"><span class="glyphicon glyphicon-search"></span> New/Search</a> </li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Summary</a></li>
                  <li role="presentation" class="divider"></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Create Visit</a><span class="closeButton fa fa-paint-brush" id="BUTTON_DRAW_HPI" name="BUTTON_DRAW_HPI"></span></li>
                  <li class="active"><a role="menuitem" id="BUTTON_DRAW_menu" tabindex="-1" href="#">Curent</a></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Visit History</a></li>
                  <li role="presentation" class="divider"></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Record Request</a></li>
                  <li role="presentation" class="divider"></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Upload Item</a></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Pending Approval</a></li>
                </ul>
                </li>
                <li class="dropdown">
                
                <a class="dropdown-toggle" role="button" id="menu1" data-toggle="dropdown">Clinical<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Eye Exam</a></li>
                    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Documents</a></li>
                    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Imaging</a></li>
                    <li role="presentation" class="divider"></li>
                    </ul>
                </li>

              <li class="dropdown">
                
                <a class="dropdown-toggle" role="button" id="menu1" data-toggle="dropdown">Window <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#"><i class="fa fa-calendar text-error"> </i>  Calendar</a></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Messages</a></li>
                  <li role="presentation" class="dropdown-header">Patient/client/li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Patients</a></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">New/Search</a></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Summary</a></li>
                  <li role="presentation" class="divider"></li>
                  <li role="presentation" class="dropdown-header">Dropdown header 2</li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">About Us</a></li>
                </ul>
              </li>
                    <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Library <span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
                <li><a href="#">Upload</a></li>
                <li><a href="../../forms/eye_mag/css/AnythingSlider/simple.php?display=fullscreen&encounter=<?php echo xla($encounter); ?>">Documents</a></li>
                <li><a href="#">Images</a></li>
                <li class="divider"></li>
                <li><a href="#">More</a></li>
                <li class="divider"></li>
                <li><a href="#">One more separated link</a></li>
              </ul>
            </li>
        </ul>

        <ul class="nav navbar-nav navbar-right">
                <li><a href="#">Link</a></li>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Dropdown <span class="caret"></span></a>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </li>
              </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
       

            
            <!-- /.navbar-top-links -->
            
    </nav>

        <div id="page-wrapper" style="margin: 0px 0px 0px 0px;">

            <div class="container">

                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12" >
                        <br />
                 <br />
                 <br />
                 
                
                
                
    <?php
}

function menu_overhaul_left($pid,$encounter) {

}

function menu_overhaul_bottom($pid,$encounter) {
 ?>
                
            </div>
            <!-- /.container -->

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
 <?php

 }

return ;
?>