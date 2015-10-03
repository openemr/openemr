<?php
/** 
 * forms/eye_mag/php/eye_mag_functions.php 
 * 
 * Function which extend the eye_mag form
 *   
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
 */

/**
 *  This function returns HTML old record selector widget when needed (4 input values)
 * 
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO, DRAW_PRIORS_$zone 
 * @param string $visit_date Future functionality to limit result set. UTC DATE Formatted 
 * @param string $pid value = patient id
 * @param string $type options text(default) image 
 * @return string returns the HTML old record/image selector widget for the desired zone and type
 */ 

$form_folder = "eye_mag";

function priors_select($zone,$orig_id,$id_to_show,$pid,$type='text') {
    global $form_folder;
    global $form_name;
    global $visit_date;
    $form_name = "Eye Exam"; 
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
                    forms.form_name =? and 
                    forms.deleted != '1' and 
                    forms.pid =form_eye_mag.pid and form_eye_mag.pid=? ORDER BY encounter_date DESC";

        $result = sqlStatement($query,array('Eye Exam',$pid));
        $counter = sqlNumRows($result);
        global $priors;
        global $current;
        $priors = array();
        if ($counter < 2) return;
        $i="0";
        while ($prior= sqlFetchArray($result))   {   
            $visit_date_local = date_create($prior['encounter_date']);
            $exam_date = date_format($visit_date_local, 'm/d/Y'); 
            //  there may be an openEMR global user preference for date formatting
            //  there is - use when ready...
        //    echo $visit_date_local." -- ".$exam_date;
            $priors[$i] = $prior;
            $selected ='';
            $priors[$i]['exam_date'] = $exam_date;
            if ($id_to_show ==$prior['form_id']) {
                $selected = 'selected="selected"';
                $current = $i;
            }
           $output .= "<option value='".attr($prior['id'])."' ".attr($selected).">".$priors[$i]['exam_date']."</option>";
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
                <span title="'.xla("Copy $zone values from ".$priors[$current]['exam_date']." to current visit.").'
                '.xla("Updated fields will be purple."). '"

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
                style="padding:0 0;font-size:1.1em;" 
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
 *  This function returns ZONE specific HTML for a PRIOR record (3 input values)
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

function display_PRIOR_section ($zone,$orig_id,$id_to_show,$pid,$report = '0') {
    global $form_folder;
    global $id;
    global $ISSUE_TYPES;
    global $ISSUE_TYPE_STYLES;

   // echo "<pre>".$zone. " - ". $orig_id. " - ".$id_to_show. " - ".$pid;
    $query  = "SELECT * FROM form_eye_mag_prefs 
                where PEZONE='PREFS' AND id=? 
                ORDER BY ZONE_ORDER,ordering";

    $result = sqlStatement($query,array($_SESSION['authUserID']));
    while ($prefs= sqlFetchArray($result))   {   
        @extract($prefs);    
        $$LOCATION = $VALUE; //same as concept ${$prefs['LOCATION']} = $prefs['VALUE'];
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
        <?php ($ANTSEG_VIEW =='1') ? ($display_ANTSEG_view = "wide_textarea") : ($display_ANTSEG_view= "narrow_textarea");?>
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
                                                        <textarea disabled id="PRIOR_ACT5SCDIST" name="PRIOR_ACT5SCDIST" class="ACT"><?php echo text($ACT5SCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6SCDIST" name="PRIOR_ACT6SCDIST" class="ACT"><?php echo text($ACT6SCDIST); ?></textarea></td>
                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT10SCDIST" name="PRIOR_ACT10SCDIST" class="ACT"><?php echo text($ACT10SCDIST); ?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT7SCDIST" name="PRIOR_ACT7SCDIST" class="ACT"><?php echo text($ACT7SCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea disabled id="PRIOR_ACT8SCDIST" name="PRIOR_ACT8SCDIST" class="ACT"><?php echo text($ACT8SCDIST); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea disabled id="PRIOR_ACT9SCDIST" name="PRIOR_ACT9SCDIST" class="ACT"><?php echo text($ACT9SCDIST); ?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea disabled id="PRIOR_ACT11SCDIST" name="PRIOR_ACT11SCDIST" class="ACT"><?php echo text($ACT11SCDIST); ?></textarea>
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
                                                        <textarea disabled id="PRIOR_ACT5CCDIST" name="PRIOR_ACT5CCDIST" class="ACT"><?php echo text($ACT5CCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6CCDIST" name="PRIOR_ACT6CCDIST" class="ACT"><?php echo text($ACT6CCDIST); ?></textarea></td>
                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT10CCDIST" name="PRIOR_ACT10CCDIST" class="ACT"><?php echo text($ACT10CCDIST); ?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT7CCDIST" name="PRIOR_ACT7CCDIST" class="ACT"><?php echo text($ACT7CCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea disabled id="PRIOR_ACT8CCDIST" name="PRIOR_ACT8CCDIST" class="ACT"><?php echo text($ACT8CCDIST); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea disabled id="PRIOR_ACT9CCDIST" name="PRIOR_ACT9CCDIST" class="ACT"><?php echo text($ACT9CCDIST); ?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea disabled id="PRIOR_ACT11CCDIST" name="PRIOR_ACT11CCDIST" class="ACT"><?php echo text($ACT11CCDIST); ?></textarea>
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
                                                        <textarea disabled id="PRIOR_ACT5SCNEAR" name="PRIOR_ACT5SCNEAR" class="ACT"><?php echo text($ACT5SCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6SCNEAR" name="PRIOR_ACT6SCNEAR" class="ACT"><?php echo text($ACT6SCNEAR); ?></textarea></td>
                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT10SCNEAR" name="PRIOR_ACT10SCNEAR" class="ACT"><?php echo text($ACT10SCNEAR); ?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT7SCNEAR" name="PRIOR_ACT7SCNEAR" class="ACT"><?php echo text($ACT7SCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea disabled id="PRIOR_ACT8SCNEAR" name="PRIOR_ACT8SCNEAR" class="ACT"><?php echo text($ACT8SCNEAR); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea disabled id="PRIOR_ACT9SCNEAR" name="PRIOR_ACT9SCNEAR" class="ACT"><?php echo text($ACT9SCNEAR); ?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea disabled id="PRIOR_ACT11SCNEAR" name="PRIOR_ACT11SCNEAR" class="ACT"><?php echo text($ACT11SCNEAR); ?></textarea>
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
                                                        <textarea disabled id="PRIOR_ACT5CCNEAR" name="PRIOR_ACT5CCNEAR" class="ACT"><?php echo text($ACT5CCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6CCNEAR" name="PRIOR_ACT6CCNEAR" class="ACT"><?php echo text($ACT6CCNEAR); ?></textarea></td><td><i class="fa fa-share rotate-right"></i></td> 
                                                    </tr> 
                                                    <tr> 
                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT10CCNEAR" name="PRIOR_ACT10CCNEAR" class="ACT"><?php echo text($ACT10CCNEAR); ?></textarea></td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                            <textarea disabled id="PRIOR_ACT7CCNEAR" name="PRIOR_ACT7CCNEAR" class="ACT"><?php echo text($ACT7CCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                            <textarea disabled id="PRIOR_ACT8CCNEAR" name="PRIOR_ACT8CCNEAR" class="ACT"><?php echo text($ACT8CCNEAR); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                            <textarea disabled id="PRIOR_ACT9CCNEAR" name="PRIOR_ACT9CCNEAR" class="ACT"><?php echo text($ACT9CCNEAR); ?></textarea></td>
                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                            <textarea disabled id="PRIOR_ACT11CCNEAR" name="PRIOR_ACT11CCNEAR" class="ACT"><?php echo text($ACT11CCNEAR); ?></textarea>
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
                            <td><input disabled type="text" id="PRIOR_DACCDIST" name="PRIOR_DACCDIST" value="<?php echo attr($DACCDIST); ?>"></td>
                            <td><input disabled type="text" id="PRIOR_DACCNEAR" name="PRIOR_DACCNEAR" value="<?php echo attr($DACCNEAR); ?>"></td></tr>
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
                <div class="divTable" style="left:-0.01in;background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 90% 90%;height:0.75in;width:0.7in;padding:1px;margin:0 1 0 0;">
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
                <div class="divTable" style="left:-0.01in;background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 90% 90%;height:0.75in;width:0.7in;padding:1px;margin:0 1 0 0;">
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

        $PMSFH = build_PMSFH($pid);
           
        // Collect parameter(s)
        $category = empty($_REQUEST['category']) ? '' : $_REQUEST['category'];
        $div = '</div><div id="PMH_QP_block2" name="PMH_QP_block2" class="QP_block_outer borderShadow text_clinical" style="height:3.2in;overflow:auto;">';
                                              
        ?><span class="closeButton fa fa-close pull-right" id="BUTTON_TEXTD_PMH" name="BUTTON_TEXTD_PMH" value="1"></span>
            
        <div id="PMSFH_block_1" name="PMSFH_block_1" class="QP_block borderShadow text_clinical" style="height:3.2in;overflow:auto;">
            <?php
            $encount = 0; //should this be $encounter?
            $lasttype = "";
            $first = 1; // flag for first section
            $counter="1";
            $column = '19';
            //each column can handle 19? rows, so a toal of 38, 
            // including 8 for headings (as of today) and at least 12 for
            // separating lines, leaving room for 18 actual items.
            // "None" counts as 2 lines right now...
            // So if count($PMSFH[0]) > 20, maybe less for "None"s, there will be overflow
            // causing the display to be funky looking...
            ?>
            <table style="width:1.6in;">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.7em;">POH</span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue('0','medical_problem','eye');" style="text-align:right;font-size:8px;">New</span>
                    </td>
                </tr>
            </table>                
            <table style='margin-bottom:10px;border:1pt solid black;max-height:1.5in;max-width:1.5in;background-color: rgb(255, 248, 220); font-size:0.9em;overflow:auto;'>
                <tr>
                    <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>
                    <?php
                    if (count($PMSFH[0]['POH']) > 0) {
                        foreach ($PMSFH[0]['POH'] as $item) {
                            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                            onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','eye');\">".$item['title']."</span><br />";
                            $counter++;
                        }
                    }
                    if (count($PMSFH[0]['POH']) < 1) {
                        echo  "".xla("None") ."<br /><br /><br />";
                        $counter = $counter+4; 
                    }
                
                    ?>
                    </td>
                </tr>
            </table>

            <table style="width:1.6in;">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.7em;">PMH</span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue('0','medical_problem','');" style="text-align:right;font-size:8px;">New</span>
                    </td>
                </tr>
            </table>
            <table style='margin-bottom:10px;border:1pt solid black;max-height:1.5in;max-width:1.5in;background-color: rgb(255, 248, 220); font-size:0.9em;overflow:auto;'>
                <tr>
                    <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>
                    <?php
                    $counter++;
                    if (count($PMSFH[0]['medical_problem']) > 0) {
                        foreach ($PMSFH[0]['medical_problem'] as $item) {
                            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                            onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','');\">".$item['title']."</span><br />";
                            $counter++;
                        }
                    }
                    if (count($PMSFH[0]['medical_problem']) < 1) {
                        echo  "".xla("None") ."<br /><br />";
                        $counter = $counter+4; 
                    }
                
                    ?>
                    </td>
                </tr>
            </table>

             
            <?php $counter++;if (($counter + count($PMSFH[0]['surgery'])) > $column) {echo $div; $counter="1";} ?>
            <table style="width:1.6in;">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.7em;">Surgery</span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue('0','surgery','');" style="text-align:right;font-size:8px;">New</span>
                    </td>
                </tr>
            </table>      
            <table style='margin-bottom:10px;border:1pt solid black;max-height:1.5in;max-width:1.5in;background-color: rgb(255, 248, 220); font-size:0.9em;overflow:auto;'>
                <tr>
                    <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>
                    <?php
                    if (count($PMSFH[0]['surgery']) > 0) {
                        foreach ($PMSFH[0]['surgery'] as $item) {
                            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                            onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','');\">".$item['title']."</span><br />";
                            $counter++;
                        }
                    }
                    if (count($PMSFH[0]['surgery']) < 1) {
                        echo  "".xla("None") ."<br /><br />";
                        $counter = $counter+4; 
                    }
                    
                    ?>
                    </td>
                </tr>
            </table>

            <?php $counter++;if (($counter + count($PMSFH[0]['allergy'])) > $column) {echo $div; $counter="1";} ?>
            <table style="width:1.6in;">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.7em;">Allergy</span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue('0','allergy','');" style="text-align:right;font-size:8px;">New</span>
                    </td>
                </tr>
            </table>
            <table style='margin-bottom:10px;border:1pt solid black;max-height:1.5in;max-width:1.5in;background-color: rgb(255, 248, 220); font-size:0.9em;overflow:auto;'>
                <tr>
                    <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>
                    <?php
                        $counter++;
                        if (count($PMSFH[0]['allergy']) > 0) {
                            foreach ($PMSFH[0]['allergy'] as $item) {
                                echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                                onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','');\">".$item['title']."</span><br />";
                                $counter++;
                            }
                        }
                            if (count($PMSFH[0]['allergy']) < 1) {
                                echo  "".xla("None") ."<br /><br />";
                                $counter = $counter+4; 
                            }
                        
                    ?>
                    </td>
                </tr>
            </table>

            <?php $counter++; if (($counter + count($PMSFH[0]['medication'])) > $column) {echo $div; $counter="1";} ?>
            <table style="width:1.6in;">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.7em;">Meds</span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue('0','medication','');" style="text-align:right;font-size:8px;">New</span>
                    </td>
                </tr>
            </table>
            <table style='margin-bottom:10px;border:1pt solid black;max-height:1.5in;max-width:1.5in;background-color: rgb(255, 248, 220); font-size:0.9em;overflow:auto;'>
                <tr>
                    <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>
                    <?php
                    if (count($PMSFH[0]['medication']) > 0) {
                        foreach ($PMSFH[0]['medication'] as $item) {
                            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                            onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','');\">".$item['title']."</span><br />";
                            $counter++;
                        }
                        if (count($PMSFH[0]['medication']) < 1) {
                            echo  "".xla("None") ."<br /><br />";
                            $counter = $counter+4; 
                        }
                    }
                    ?>
                    </td>
                </tr>
            </table>
           
            
            <?php $counter++;if (($counter + count($PMSFH[0]['FH'])) > $column) {echo $div; $counter="1";} ?>
            <table style="width:1.6in;">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.7em;">FH</span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue('0','FH','');" style="text-align:right;font-size:8px;">New</span>
                    </td>
                </tr>
            </table>
            <table style='margin-bottom:10px;border:1pt solid black;max-height:1.5in;max-width:1.5in;background-color: rgb(255, 248, 220); font-size:0.9em;overflow:auto;'>
                <tr>
                    <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>
                    <?php
                    foreach ($PMSFH[0]['FH'] as $item) {
                        if ($item['display'] > '') {
                            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                            onclick=\"alter_issue('0','FH','');\">".$item['short_title'].": ".$item['display']."</span><br />";
                        }
                        $mentions_FH++;
                    }
                    if (!$mentions_FH) {
                        ?>
                        <span href="#PMH_anchor" 
                        onclick="alter_issue('0','FH','');" style="text-align:right;">Negative</span><br />
                        <?
                    }

                   
                    ?>
                    </td>
                </tr>
            </table>

            <?php $counter++;if ($counter > $column) {echo $div; $counter="1";} ?>
            <table style="width:1.6in;">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.7em;">Social</span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue('0','SOCH','');" style="text-align:right;font-size:8px;">New</span>
                    </td>
                </tr>
            </table>
            <table style='margin-bottom:10px;border:1pt solid black;max-height:1.5in;max-width:1.5in;background-color: rgb(255, 248, 220); font-size:0.9em;overflow:auto;'>
                <tr>
                    <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>
                    <?php
                    if (count($PMSFH[0]['SOCH']) > '0') {
                        foreach ($PMSFH[0]['SOCH'] as $k => $item) {
                            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                            onclick=\"alter_issue('0','SOCH','');\">".$item['short_title'].": ".$item['display']."</span><br />";
                        }
                    } else {
                        ?>
                        <span href="#PMH_anchor" 
                        onclick="alter_issue('0','SOCH','');" style="text-align:right;">Not documented</span><br />
                        <?
                    }
                    ?>
                    </td>
                </tr>
            </table>

            <table style="width:1.6in;">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.7em;">ROS</span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue('0','ROS','');" style="text-align:right;font-size:8px;">New</span>
                    </td>
                </tr>
            </table>               
            <table style='margin-bottom:10px;border:1pt solid black;max-height:1.5in;max-width:1.5in;background-color: rgb(255, 248, 220); font-size:0.9em;overflow:auto;'>
                <tr>
                    <td style='min-height:1.2in;min-width:1.5in;padding-left:5px;'>
                    <?php
                    foreach ($PMSFH[0]['ROS'] as $item) {
                        //var_dump($item);
                        if ($item['display'] > '') {
                            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                             onclick=\"alter_issue('0','ROS','');\">".$item['short_title'].": ".$item['display']."</span><br />";
                            $counter++;
                            $mention++;
                        }
                    }
                    if ($mention < 1) {
                        echo  "".xla("Negative") ."<br />";
                        $counter = $counter+2; 
                    }
                    ?>
                    </td>
                </tr>
            </table>

        </div>
            <?php
            return;
    }
}

function build_PMSFH($pid) {
    global $form_folder;
    global $form_id;
    global $id;
    global $ISSUE_TYPES;
    global $ISSUE_TYPE_STYLES;

    require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
    require_once($GLOBALS['srcdir'].'/options.inc.php');
    // build a variable with all the PMH/PSurgHx/FH/SocHx/All
    // then we can display it however we like 
    $PMSFH = array();
     //       $ISSUE_TYPES[] = "POH";
       //     $ISSUE_TYPES[] = "FH";
         //   $ISSUE_TYPES[] = "SOCH";
    $ISSUE_TYPES['POH'] = array("Past Ocular History","POH","O","1");
    $ISSUE_TYPES['FH'] = array("Family History","FH","O","1");
    $ISSUE_TYPES['SOCH'] = array("Social History","SocH","O","1");

    foreach ($ISSUE_TYPES as $focustype => $focustitles) {
            if ($category) {
                //    Only show this category
               if ($focustype != $category) continue;
            }
           // $PMSFH[$focustitles[1]]="category";
          //  $focustype= $focustitles[3];
            $subtype = " and (subtype is NULL or subtype ='' )";
            if ($focustype =='POH') {
                $focustype = "medical_problem";
                $subtype=" and subtype ='eye'";
            }
            if ($focustype == "FH" || $focustype == "SOCH") {
                //we are doing SocHx and FH below, so for now do nothing
                continue;
            }
            $pres = sqlStatement("SELECT * FROM lists WHERE pid = ? AND type = ? " .
                $subtype." ORDER BY begdate", array($pid,$focustype) );
            $row_counter='0';
            while ($row = sqlFetchArray($pres)) {
                    $panel_type = $row['type'];
                    if ($row['subtype'] == "eye") {
                        $panel_type = "POH";
                    }
                    $rowid = $row['id'];
                    $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";
                    
                    // look up the diag codes
                    $codetext = "";
                    if ($row['diagnosis'] != "") {
                        $diags = explode(";", $row['diagnosis']);
                        foreach ($diags as $diag) {
                            $codedesc = lookup_code_descriptions($diag);
                            $codetext .= xlt($diag) . " (" . xlt($codedesc) . ")<br />";
                     //       $PMSFH['category'][]['title'][]['codedesc'][] = $codedesc;
                      //      $PMSFH['category'][]['title'][]['codetext'][] = $codetext;
                        }
                    }

                    // calculate the status
                    if ($row['outcome'] == "1" && $row['enddate'] != NULL) {
                      // Resolved
                      $statusCompute = generate_display_field(array('data_type'=>'1','list_id'=>'outcome'), $row['outcome']);
                    } else if($row['enddate'] == NULL) {
                           $statusCompute = htmlspecialchars( xl("Active") ,ENT_NOQUOTES);
                    } else {
                           $statusCompute = htmlspecialchars( xl("Inactive") ,ENT_NOQUOTES);
                    }
                $newdata =  array (
                    'title' => $disptitle,
                    'status' => $statusCompute,
                    'enddate' => $row['enddate'],
                    'reaction' => $row['reaction'],
                    'referredby' => $row['referredby'],
                    'extrainfo' => $row['extrainfo'],
                    'codedesc' => $codedesc,
                    'codetype' => $codetype,
                    'comments' => $row['comments'],
                    'rowid' => $row['id'],
                    'row_type' => $row['type']
                );
                $PMSFH[$panel_type][] = $newdata;
                //array_push($PMSFH[$focustype], $newdata);

                }
    }
    //build the SocHx portion of $PMSFH
    //$given ="coffee,tobacco,alcohol,sleep_patterns,exercise_patterns,seatbelt_use,counseling,hazardous_activities,recreational_drugs";
    $result1 = sqlQuery("select * from history_data where pid=? order by date DESC limit 0,1", array($pid) );
     
    $group_fields_query = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'HIS' AND group_name = '4Lifestyle' AND uor > 0 " .
    "ORDER BY seq");
    while ($group_fields = sqlFetchArray($group_fields_query)) {
        $titlecols  = $group_fields['titlecols'];
        $datacols   = $group_fields['datacols'];
        $data_type  = $group_fields['data_type'];
        $field_id   = $group_fields['field_id'];
        $list_id    = $group_fields['list_id'];
        $currvalue  = '';
        if ((preg_match("/^\|?0\|?\|?/", $result1[$field_id]))|| ($result1[$field_id]=='')) {
            continue;
        } else {
            $currvalue = $result1[$field_id];
        }
        if ($data_type == 28 || $data_type == 32) {
            $tmp = explode('|', $currvalue);
            switch(count($tmp)) {
                case "4": {
                    $PMSFH['SOCH'][$field_id]['resnote'] = $tmp[0];
                    $PMSFH['SOCH'][$field_id]['restype'] = $tmp[1];
                    $PMSFH['SOCH'][$field_id]['resdate'] = $tmp[2];
                    $PMSFH['SOCH'][$field_id]['reslist'] = $tmp[3];
                } break;
                case "3": {
                    $PMSFH['SOCH'][$field_id]['resnote'] = $tmp[0];
                    $PMSFH['SOCH'][$field_id]['restype'] = $tmp[1];
                    $PMSFH['SOCH'][$field_id]['resdate'] = $tmp[2];
                } break;
                case "2": {
                    $PMSFH['SOCH'][$field_id]['resnote'] = $tmp[0];
                    $PMSFH['SOCH'][$field_id]['restype'] = $tmp[1];
                    $PMSFH['SOCH'][$field_id]['resdate'] = "";
                } break;
                case "1": {
                    $PMSFH['SOCH'][$field_id]['resnote'] = $tmp[0];
                    $PMSFH['SOCH'][$field_id]['resdate'] = $PMSFH['SOCH'][$field_id]['restype'] = "";
                } break;
                default: {
                    $PMSFH['SOCH'][$field_id]['restype'] = $PMSFH['SOCH'][$field_id]['resdate'] = $PMSFH['SOCH'][$field_id]['resnote'] = "";
                } break;
            }
            $PMSFH['SOCH'][$field_id]['resnote'] = htmlspecialchars( $PMSFH['SOCH'][$field_id]['resnote'], ENT_QUOTES);
            $PMSFH['SOCH'][$field_id]['resdate'] = htmlspecialchars( $PMSFH['SOCH'][$field_id]['resdate'], ENT_QUOTES);
                //  if ($group_fields['title']) echo htmlspecialchars(xl_layout_label($group_fields['title']).":",ENT_NOQUOTES)."</b>"; else echo "&nbsp;";
                //      echo generate_display_field($group_fields, $currvalue);
        } else if ($data_type == 2) {
             $PMSFH['SOCH'][$field_id]['resnote'] = nl2br(htmlspecialchars($currvalue,ENT_NOQUOTES));
        }
        if ($PMSFH['SOCH'][$field_id]['resnote'] > '') {
            $PMSFH['SOCH'][$field_id]['display'] = substr($PMSFH['SOCH'][$field_id]['resnote'],0,10);
        } elseif ($PMSFH['SOCH'][$field_id]['restype']) {
            $PMSFH['SOCH'][$field_id]['display'] = str_replace($field_id,'',$PMSFH['SOCH'][$field_id]['restype']);
        }
        //coffee,tobacco,alcohol,sleep_patterns,exercise_patterns,seatbelt_use,counseling,hazardous_activities,recreational_drugs
        if ($field_id =="coffee") $PMSFH['SOCH'][$field_id]['short_title'] = "Caffeine";
        if ($field_id =="tobacco") $PMSFH['SOCH'][$field_id]['short_title'] = "Cigs";
        if ($field_id =="alcohol") $PMSFH['SOCH'][$field_id]['short_title'] = "ETOH";
        if ($field_id =="sleep_patterns") $PMSFH['SOCH'][$field_id]['short_title'] = "Sleep";
        if ($field_id =="exercise_patterns") $PMSFH['SOCH'][$field_id]['short_title'] = "Exercise";
        if ($field_id =="seatbelt_use") $PMSFH['SOCH'][$field_id]['short_title'] = "Seatbelt";
        if ($field_id =="counseling") $PMSFH['SOCH'][$field_id]['short_title'] = "Therapy";
        if ($field_id =="hazardous_activities") $PMSFH['SOCH'][$field_id]['short_title'] = "Thrills";
        if ($field_id =="recreational_drugs") $PMSFH['SOCH'][$field_id]['short_title'] = "Drug Use";
    }
    
    //  Drag in Marital status and Employment history to this Social Hx area.
    $patient = getPatientData($pid, "*");
    $PMSFH['SOCH']['marital_status']['short_title']="Marital";
    $PMSFH['SOCH']['marital_status']['display']=$patient['status'];
    $PMSFH['SOCH']['occupation']['short_title']="Occupation";
    $PMSFH['SOCH']['occupation']['display']=$patient['occupation'];


    // Build the FH portion of $PMSFH,$PMSFH['FH']
    // history_mother  history_father  history_siblings    history_offspring   history_spouse  
    // relatives_cancer    relatives_tuberculosis  relatives_diabetes  relatives_high_blood_pressure   relatives_heart_problems    relatives_stroke    relatives_epilepsy  relatives_mental_illness    relatives_suicide
    //  There are two ways FH is stored in the history area, one on a specific relationship basis
    // ie. parent,sibling, offspring has X, or the other by "relatives_disease" basis.  
    // Hmmm, neither really meets our needs.  This is an eye form,
    // and we don't really care about most non-eye FH diseases - we do a focused family history.
    // Cataracts, glaucoma, AMD, RD, cancer, heart disease etc.  
    // The openEMR people who want to adapt this for another specialty will no doubt
    // have different diseases they want listed in the FH specifically.  We all need to be able to 
    // adjust the form.  Perhaps we should use the UserDefined fields at the end of this history_data table?
    // Question is, does anything use this family history data - any higher function like reporting? 
    // If there is an engine to validate level of exam, how do we tell it that this was completed?
    // First we would need to know the criteria it looks for and I don't think in reality there is anything 
    // written yet that does, so maybe we should create a flag in the user defined area of the history_data 
    // table to notate that the FH portion of the exam was completed.
    /*
    Cancer:     Tuberculosis:   
    Diabetes:       High Blood Pressure:    
    Heart Problems:     Stroke: 
    Epilepsy:       Mental Illness: 
    Suicide:    
    */
  //return array($PMSFH);
    $group_fields_query = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'HIS' AND group_name = '3Relatives' AND uor > 0 " .
    "ORDER BY seq");
    while ($group_fields = sqlFetchArray($group_fields_query)) {
        $titlecols  = $group_fields['titlecols'];
        $datacols   = $group_fields['datacols'];
        $data_type  = $group_fields['data_type'];
        $field_id   = $group_fields['field_id'];
        $list_id    = $group_fields['list_id'];
        $currvalue  = '';

        if ((preg_match("/^\|?0\|?\|?/", $result1[$field_id]))|| ($result1[$field_id]=='')) {
            continue;
        } else {
            $currvalue = $result1[$field_id];
        }
    
        $PMSFH['FH'][$field_id]['resnote'] = nl2br(htmlspecialchars($currvalue,ENT_NOQUOTES));
        
        if ($PMSFH['FH'][$field_id]['resnote'] > '') {
            $PMSFH['FH'][$field_id]['display'] = substr($PMSFH['FH'][$field_id]['resnote'],0,10);
        } elseif ($PMSFH['FH'][$field_id]['restype']) {
            $PMSFH['FH'][$field_id]['display'] = str_replace($field_id,'',$PMSFH['FH'][$field_id]['restype']);
        } else {
            $PMSFH['FH'][$field_id]['display'] = "denies";
        }
      
        //coffee,tobacco,alcohol,sleep_patterns,exercise_patterns,seatbelt_use,counseling,hazardous_activities,recreational_drugs
        if ($field_id =="relatives_cancer") $PMSFH['FH'][$field_id]['short_title'] = "Cancer";
        if ($field_id =="relatives_diabetes") $PMSFH['FH'][$field_id]['short_title'] = "Diabetes";
        if ($field_id =="relatives_high_blood_pressure") $PMSFH['FH'][$field_id]['short_title'] = "HTN";
        if ($field_id =="relatives_diabetes") $PMSFH['FH'][$field_id]['short_title'] = "Diabetes";

        if ($field_id =="relatives_heart_problems") $PMSFH['FH'][$field_id]['short_title'] = "Cor Disease";
        if ($field_id =="relatives_epilepsy") $PMSFH['FH'][$field_id]['short_title'] = "Epilepsy";
        if ($field_id =="relatives_mental_illness") $PMSFH['FH'][$field_id]['short_title'] = "Psych";
        if ($field_id =="relatives_suicide") $PMSFH['FH'][$field_id]['short_title'] = "Suicide";

        if ($field_id =="relatives_stroke") $PMSFH['FH'][$field_id]['short_title'] = "Stroke";
        if ($field_id =="relatives_tuberculosis") $PMSFH['FH'][$field_id]['short_title'] = "TB";
        
   }
     //now make some of our own using the usertext11-30 fields
   /*
    if (!$result1['usertext11']) $result1['usertext11'] = "None";
    if (!$result1['usertext12']) $result1['usertext12'] = "None";
    if (!$result1['usertext13']) $result1['usertext13'] = "None";
    if (!$result1['usertext14']) $result1['usertext14'] = "None";
*/        //if (!$result1['usertext15']) $result1['usertext15'] = "denies";
    $PMSFH['FH']['glaucoma']['display'] = (substr($result1['usertext11'],0,10));
    $PMSFH['FH']['glaucoma']['short_title'] = "Glaucoma";
    $PMSFH['FH']['cataract']['display'] = (substr($result1['usertext12'],0,10));
    $PMSFH['FH']['cataract']['short_title'] = "Cataract";
    $PMSFH['FH']['amd']['display'] = (substr($result1['usertext13'],0,10));
    $PMSFH['FH']['amd']['short_title'] = "AMD";
    $PMSFH['FH']['RD']['display'] = (substr($result1['usertext14'],0,10));
    $PMSFH['FH']['RD']['short_title'] = "RD";
    $PMSFH['FH']['blindness']['short_title'] = "Blindness";
    $PMSFH['FH']['blindness']['display'] = (substr($result1['usertext15'],0,10));
    $PMSFH['FH']['amblyopia']['short_title'] = "Amblyopia";
    $PMSFH['FH']['amblyopia']['display'] = (substr($result1['usertext16'],0,10));
    $PMSFH['FH']['strabismus']['short_title'] = "Strabismus";
    $PMSFH['FH']['strabismus']['display'] = (substr($result1['usertext17'],0,10));
    $PMSFH['FH']['other']['short_title'] = "Other";
    $PMSFH['FH']['other']['display'] = (substr($result1['usertext18'],0,10));

    //last_retinal    last_hemoglobin     
    //   $PMSFH['SOCH'][$field_id]['resnote'] = nl2br(htmlspecialchars($currvalue,ENT_NOQUOTES));
    //$PMSFH=$PMSFH[0];

    // Build ROS into $PMSFH['ROS'] also
    $given="ROSGENERAL,ROSHEENT,ROSCV,ROSPULM,ROSGI,ROSGU,ROSDERM,ROSNEURO,ROSPSYCH,ROSMUSCULO,ROSIMMUNO,ROSENDOCRINE";
    $query="SELECT $given from form_eye_mag where id=? and pid=?";

    $ROS = sqlStatement($query,array($id,$pid));
    while ($row = sqlFetchArray($ROS)) {
        foreach (split(',',$given) as $item) {
            $PMSFH['ROS'][$item]['display']= $row[$item];
        }
    }
    $PMSFH['ROS']['ROSGENERAL']['short_title']="GEN";
    $PMSFH['ROS']['ROSHEENT']['short_title']="HEENT";
    $PMSFH['ROS']['ROSCV']['short_title']="CV";
    $PMSFH['ROS']['ROSPULM']['short_title']="PULM";
    $PMSFH['ROS']['ROSGI']['short_title']="GI";
    $PMSFH['ROS']['ROSGU']['short_title']="GU";
    $PMSFH['ROS']['ROSDERM']['short_title']="DERM";
    $PMSFH['ROS']['ROSNEURO']['short_title']="NEURO";
    $PMSFH['ROS']['ROSPSYCH']['short_title']="PSYCH";
    $PMSFH['ROS']['ROSMUSCULO']['short_title']="ORTHO";
    $PMSFH['ROS']['ROSIMMUNO']['short_title']="IMMUNO";
    $PMSFH['ROS']['ROSENDOCRINE']['short_title']="ENDO";

    $PMSFH['ROS']['ROSGENERAL']['title']="General";
    $PMSFH['ROS']['ROSHEENT']['title']="HEENT";
    $PMSFH['ROS']['ROSCV']['title']="Cardiovascular";
    $PMSFH['ROS']['ROSPULM']['title']="Pulmonary";
    $PMSFH['ROS']['ROSGI']['title']="GI";
    $PMSFH['ROS']['ROSGU']['title']="GU";
    $PMSFH['ROS']['ROSDERM']['title']="Dermatology";
    $PMSFH['ROS']['ROSNEURO']['title']="Neurology";
    $PMSFH['ROS']['ROSPSYCH']['title']="Pyschiatry";
    $PMSFH['ROS']['ROSMUSCULO']['title']="Musculoskeletal";
    $PMSFH['ROS']['ROSIMMUNO']['title']="Immune System";
    $PMSFH['ROS']['ROSENDOCRINE']['title']="Endocrine";

    return array($PMSFH);
}

function show_PMSFH_panel($PMSFH) {
    echo '<div style="font-size:1.0em;padding:30 2 2 5;z-index:1;">';
      //nice idea to put a TEXT-DRAW-DB selector up here.. ;)
      ?><div style="margin:top:10px;text-align:center;">
      <span class="fa fa-file-text-o" id="PANEL_TEXT" name="PANEL_TEXT" style="margin:5;"></span>
      <span class="fa fa-database" id="PANEL_QP" name="PANEL_QP" style="margin:5;"></span>
      <span class="fa fa-paint-brush" id="PANEL_DRAW" name="PANEL_DRAW" style="margin:5;"></span>
      <span class="fa fa-close" id="close-panel-bt" style="margin:5;"></span><BR />
      </div>
        <div>
      <?php
      //<!-- POH -->
      echo "<br /><span class='panel_title'>POH:</span>";
      //nice idea to put a TEXT-DRAW-DB selector up here.. ;)
      ?>
      <span class="top-right btn-sm" href="#PMH_anchor" 
      onclick="alter_issue('0','medical_problem','eye');" style="text-align:right;font-size:8px;">Add</span>
      <br />
      <?php
      if (count($PMSFH[0]['POH']) > 0) {
          foreach ($PMSFH[0]['POH'] as $item) {
            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
            onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','eye');\">".$item['title']."</span><br />";
          }
      }
       //<!-- PMH -->
      echo "<br />
      <span class='panel_title'>PMH:</span>";
      ?><span class="top-right btn-sm" href="#PMH_anchor" 
      onclick="alter_issue('0','medical_problem','');" style="text-align:right;font-size:8px;">Add</span>
      <br />
      <?php
      if (count($PMSFH[0]['medical_problem']) > 0) {
          foreach ($PMSFH[0]['medical_problem'] as $item) {
            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
            onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','');\">".$item['title']."</span><br />";
          }
      }
      
       //<!-- Meds -->
      echo "<br /><span class='panel_title'>Medication:</span>";
      ?><span class="top-right btn-sm" href="#PMH_anchor" 
      onclick="alter_issue('0','medication','');" style="text-align:right;font-size:8px;">Add</span>
      <br />
      <?php
      if (count($PMSFH[0]['medication']) > 0) {
          foreach ($PMSFH[0]['medication'] as $item) {
            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
            onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','');\">".$item['title']."</span><br />";
          }
      }
      //<!-- Surgeries -->
      echo "<br /><span class='panel_title'>Surgery:</span>";
      ?><span class="top-right btn-sm" href="#PMH_anchor" 
      onclick="alter_issue('0','surgery','');" style="text-align:right;font-size:8px;">Add</span>
      <br />
      <?php
      if (count($PMSFH[0]['surgery']) > '0') {
        foreach ($PMSFH[0]['surgery'] as $item) {
          echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
          onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','');\">".$item['title']."</span><br />";
        }
      } else { ?>
        <span href="#PMH_anchor" 
        onclick="alter_issue('0','surgery','');" style="text-align:right;">None</span><br />
        <?
      }
      
      //<!-- Allergies -->
      echo "<br /><span class='panel_title'>Allergy:</span>";
      ?><span class="top-right btn-sm" href="#PMH_anchor" 
      onclick="alter_issue('0','allergy','');" style="text-align:right;font-size:8px;">Add</span>
      <br />
      <?php
      if (count($PMSFH[0]['allergy']) > '0') {
        foreach ($PMSFH[0]['allergy'] as $item) {
          echo "<span style='color:red;' name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
          onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."','');\">".$item['title']."</span><br />";
        } 
      } else { ?>
        <span href="#PMH_anchor" 
        onclick="alter_issue('0','allergy','');" style="text-align:right;">NKDA</span><br />
        <?
      }
      
       //<!-- Social History -->
      echo "<br /><span class='panel_title'>Soc Hx:</span>";
      ?><span class="top-right btn-sm" href="#PMH_anchor" 
      onclick="alter_issue('0','SOCH','');" style="text-align:right;font-size:8px;">Add</span>
      <br />
      <?php
        foreach ($PMSFH[0]['SOCH'] as $k => $item) {
            if ($item['display']) {
            echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
            onclick=\"alter_issue('0','SOCH','');\">".$item['short_title'].": ".$item['display']."</span><br />";
            }
            $mention_SOCH++;
        }
        if (!$mention_SOCH) {
            ?>
            <span href="#PMH_anchor" 
            onclick="alter_issue('0','SOCH','');" style="text-align:right;">Negative</span><br />
            <?
        }

        //<!-- Family History -->
      echo "<br /><span class='panel_title'>FH:</span>";
      ?><span class="top-right btn-sm" href="#PMH_anchor" 
      onclick="alter_issue('0','FH','');" style="text-align:right;font-size:8px;">Add</span>
      <br />
      <?php
        if (count($PMSFH[0]['FH']) > 0) {
            foreach ($PMSFH[0]['FH'] as $item) {
                if ($item['display'] > '') {
                    echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                    onclick=\"alter_issue('".$item['rowid']."','".$item['row_type']."');\">".$item['short_title'].": ".$item['display']."</span><br />";
                    $mention_FH++;
                }
            }
        }
        if (!$mention_FH) {
            ?>
            <span href="#PMH_anchor" 
            onclick="alter_issue('0','FH','');" style="text-align:right;">Negative</span><br />
            <?
        }

      echo "<br /><span class='panel_title'>ROS:</span>";
      ?><span class="top-right btn-sm" href="#PMH_anchor" 
      onclick="alter_issue('0','ROS','');" style="text-align:right;font-size:8px;">Add</span>
      <br />
      <?php
        foreach ($PMSFH[0]['ROS'] as $item) {
            if ($item['display']) {
                echo "<span name='QP_PMH_".$item['rowid']."' href='#PMH_anchor' id='QP_PMH_".$item['rowid']."' 
                onclick=\"alter_issue('0','ROS','');\">".$item['short_title'].": ".$item['display']."</span><br />";
            $mentions++;
            }
        }
      if ($mentions < '1') {
              ?>
        <span href="#PMH_anchor" 
        onclick="alter_issue('0','ROS','');" style="text-align:right;">Negative</span><br />
        <?
      }
}
function show_PMSFH_report($PMSFH) {
      ?>        
    
      <?php
      //<!-- POH -->
      echo "<span class='panel_title'>POH:</span>";
      ?>
      <br />
      <?php
      if (count($PMSFH[0]['POH']) > '0') {
        foreach ($PMSFH[0]['POH'] as $item) {
            echo $item['title']."<br />";
        }
      } else {
        echo "None<br />";
      }
       //<!-- PMH -->
      echo "<br />
      <span class='panel_title'>PMH:</span>";
      ?>
      <br />
      <?php
      if (count($PMSFH[0]['medical_problem']) > '0') {
          foreach ($PMSFH[0]['medical_problem'] as $item) {
            echo $item['title']."<br />";
          }
      } else {
        echo "None<br />";
      }
      
       //<!-- Meds -->
      echo "<br /><span class='panel_title'>Medication:</span>";
      ?>
      <br />
      <?php
        if (count($PMSFH[0]['medication']) > '0') {
            foreach ($PMSFH[0]['medication'] as $item) {
                echo $item['title']."<br />";
            }
        } else {
            echo "None<br />";
        }
      
      //<!-- Surgeries -->
      echo "<br /><span class='panel_title'>Surgery:</span>";
      ?><br />
      <?php
      if (count($PMSFH[0]['surgery']) > '0') {
        foreach ($PMSFH[0]['surgery'] as $item) {
          echo $item['title']."<br />";
        }
      } else { ?>
        None<br />
        <?

      }
      
      //<!-- Allergies -->
      echo "<br /><span class='panel_title'>Allergy:</span>";
      ?>
      <br />
      <?php
      if (count($PMSFH[0]['allergy']) > '0') {
        foreach ($PMSFH[0]['allergy'] as $item) {
          echo $item['title']."<br />";
        } 
      } else { ?>
        NKDA<br />
        <?
      }
      
       //<!-- SocHx -->
      echo "<br /><span class='panel_title'>Soc Hx:</span>";
      ?>
      <br />
      <?php
        foreach ($PMSFH[0]['SOCH'] as $k => $item) {
            if ($item['display']) {
            echo $item['short_title'].": ".$item['display']."<br />";
            $mention_PSOCH++;
            }
        }
      if (!$PSOCH) {
        ?>
        Negative<br />
        <?
      }

      echo "<br /><span class='panel_title'>FH:</span>";
      ?>
      <br />
        <?php
        foreach ($PMSFH[0]['FH'] as $item) {
            if ($item['display']) {
                echo $item['short_title'].": ".$item['display']."<br />";
                $mention_FH++;
            }
        }
        if (!$mention_FH) {
            echo "Negative";
        }

        echo "<br /><br /><span class='panel_title'>ROS:</span>";
        ?><br />
        <?php
        $mentions='';
        foreach ($PMSFH[0]['ROS'] as $item) {
            if ($item['display']) {
                echo $item['short_title'].": ".$item['display']."<br />";
                $mentions++;
            }
        }
        if ($mentions < '1') {
            echo "Negative";
        }
      
}

/**
 *  This function returns display the draw/sketch diagram for a zone (4 input values)
 * 
 *  If there is already a drawing for this zone in this encounter, it is pulled from
 *  from its stored location:
 *  $GLOBALS['web_root']."/sites/".$_SESSION['site_id']."/".$form_folder."/".$pid."/".$encounter."/".$side."_".$zone."_VIEW.png?".rand();
 *  
 *  Otherwise a "BASE" image is pulled from the images directory of the form...  Customizable.
 *
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO 
 * @param string $visit_date Future functionality to limit result set. UTC DATE Formatted 
 * @param string $pid value = patient id
 * @param string OU by default.  Future functionality will allow OD and OS values- not implemented yet.
 * @return true : when called directly outputs the ZONE specific HTML5 CANVAS widget 
 */ 
function display_draw_section ($zone,$encounter,$pid,$side ='OU',$counter='') {
    global $form_folder;
    $storage = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter;
    $file_history = $storage."/OU_".$zone."_DRAW_1";
    $file_store= $file_history.".png";
    $additional = '1';
    //limit it to 10 for now...
  
  while (file_exists($file_history.".png")) {
        $file_history = $storage."/OU_".$zone."_DRAW_". $additional++;
        $file_store= $file_history.".png";
  }

    ?>
    <div id="Draw_<?php echo attr($zone); ?>" name="Draw_<?php echo attr($zone); ?>" style="text-align:center;height: 2.5in;" class="Draw_class canvas">
        <span class="closeButton fa fa-file-text-o" id="BUTTON_TEXT_<?php echo attr($zone); ?>" name="BUTTON_TEXT_<?php echo attr($zone); ?>"></span>
        
        <?php  
            /* This will provide a way to scroll back through prior VISIT images, to copy forward to today's visit.
             * Will need to do a lot of coding to create this.  Jist is ajax call to server for image retrieval.
             * To get this to work we need a way to select an old image to work from, use current or return to baseline.
             * This will require a global BACK button like above (BUTTON_BACK_<?php echo attr($zone); ?>). 
             * The Undo Redo buttons scroll through current encounter zone images as they are incrementally added, to "Undo" a mishap.  
             * If we look back at a prior VISIT's saved final image, 
             * we should store a copy of this image client side also in order.
             * just like we had drawn new stuff and stored the changes. 
             * Thus the Undo feature will only retrieve images from today's encounter directory
             * Need to think about how to display this visually so it's intuitive, without cluttering the page...
             */
        //$output = priors_select($zone,$orig_id,$id_to_show,$pid); echo $output; 
        ?>
        
        <div class="tools" style="text-align:center;width:100%;">
           
            <img  id="sketch_tools_<?php echo attr($zone); ?>" onclick='$("#selColor_<?php echo $zone; ?>").val("blue");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_blue.png" style="height:30px;width:15px;">
            <img id="sketch_tools_<?php echo attr($zone); ?>" onclick='$("#selColor_<?php echo $zone; ?>").val("#ff0");'  src="../../forms/<?php echo $form_folder; ?>/images/pencil_yellow.png" style="height:30px;width:15px;">
            <img id="sketch_tools_<?php echo attr($zone); ?>" onclick='$("#selColor_<?php echo $zone; ?>").val("#ffad00");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_orange.png" style="height:30px;width:15px;">
            <img id="sketch_tools_<?php echo attr($zone); ?>" onclick='$("#selColor_<?php echo $zone; ?>").val("#AC8359");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_brown.png" style="height:30px;width:15px;">
            <img id="sketch_tools_<?php echo attr($zone); ?>" onclick='$("#selColor_<?php echo $zone; ?>").val("red");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_red.png" style="height:30px;width:15px;">
            <img id="sketch_tools_<?php echo attr($zone); ?>" onclick='$("#selColor_<?php echo $zone; ?>").val("#000");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_black.png" style="height:50px;width:15px;">
            <img id="sketch_tools_<?php echo attr($zone); ?>" onclick='$("#selColor_<?php echo $zone; ?>").val("#fff");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_white.png" style="height:30px;width:15px;">
             
            <span style="min-width:1in;">&nbsp;</span>
            <!-- now to pencil size -->
            <img id="sketch_sizes_<?php echo attr($zone); ?>" onclick='$("#selWidth_<?php echo $zone; ?>").val("1");' src="../../forms/<?php echo $form_folder; ?>/images/brush_1.png" style="height:20px;width:20px; border-bottom: 2pt solid black;">
            <img id="sketch_sizes_<?php echo attr($zone); ?>" onclick='$("#selWidth_<?php echo $zone; ?>").val("3");' src="../../forms/<?php echo $form_folder; ?>/images/brush_3.png" style="height:20px;width:20px;">
            <img id="sketch_sizes_<?php echo attr($zone); ?>" onclick='$("#selWidth_<?php echo $zone; ?>").val("5");' src="../../forms/<?php echo $form_folder; ?>/images/brush_5.png" style="height:20px;width:20px;">
            <img id="sketch_sizes_<?php echo attr($zone); ?>" onclick='$("#selWidth_<?php echo $zone; ?>").val("10");' src="../../forms/<?php echo $form_folder; ?>/images/brush_10.png" style="height:20px;width:20px;">
            <img id="sketch_sizes_<?php echo attr($zone); ?>" onclick='$("#selWidth_<?php echo $zone; ?>").val("15");' src="../../forms/<?php echo $form_folder; ?>/images/brush_15.png" style="height:20px;width:20px;">
        </div>
        
        <?php 
            $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter."/".$side."_".$zone."_VIEW.png";
            $sql = "SELECT * from documents where url='file://".$file_location."'";
            $doc = sqlQuery($sql);
            // random to not pull from cache.
            if (file_exists($file_location) && ($doc['id'] > '0')) {
                $filetoshow = $GLOBALS['web_root']."/controller.php?document&retrieve&patient_id=$pid&document_id=".$doc['id']."&as_file=false&blahblah=".rand();
            } else {
                //base image. 
                $filetoshow = $GLOBALS['web_root']."/interface/forms/".$form_folder."/images/".$side."_".$zone."_BASE.png"; 
            }
        ?>
        <input type="hidden" id="url_<?php echo attr($zone); ?>" name="url_<?php echo attr($zone); ?>" value="<?php echo $filetoshow; ?>">
       
        <div align="center" class="borderShadow">
            <canvas id="myCanvas_<?php echo $zone; ?>" name="myCanvas_<?php echo $zone; ?>" width="400" height="225"></canvas>
        </div>
        <input type="hidden" id="selWidth_<?php echo $zone; ?>" value="1">
        <input type="hidden" id="selColor_<?php echo $zone; ?>" value="#000">
        <div style="margin-top: 7px;">
            <button onclick="javascript:cUndo('<?php echo $zone; ?>');return false;" id="Undo_Canvas_<?php echo $zone; ?>">Undo</button>
            <button onclick="javascript:cRedo('<?php echo $zone; ?>');return false;" id="Redo_Canvas_<?php echo $zone; ?>">Redo</button>
            <button onclick="javascript:drawImage('<?php echo $zone; ?>');return false;" id="Clear_Canvas_<?php echo $zone; ?>">Clear</button>
        <!-- <button onclick="return false;" id="Base_Canvas_<?php echo $zone; ?>">Change Base</button>
    -->
        
        </div>
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
    if ($zone =="EXT") {
        $result['RUL']=$objQuery['RUL'];
        $result['LUL']=$objQuery['LUL'];
        $result['RLL']=$objQuery['RLL'];
        $result['LLL']=$objQuery['LLL'];
        $result['RBROW']=$objQuery['RBROW'];
        $result['LBROW']=$objQuery['LBROW'];
        $result['RMCT']=$objQuery['RMCT'];
        $result['LMCT']=$objQuery['LMCT'];
        $result['RADNEXA']=$objQuery['RADNEXA'];
        $result['LADNEXA']=$objQuery['LADNEXA'];
        $result['RMRD']=$objQuery['RMRD'];
        $result['LMRD']=$objQuery['LMRD'];
        $result['RLF']=$objQuery['RLF'];
        $result['LLF']=$objQuery['LLF'];
        $result['RVFISSURE']=$objQuery['RVFISSURE'];
        $result['LVFISSURE']=$objQuery['LVFISSURE'];
        $result['RCAROTID']=$objQuery['RCAROTID'];
        $result['LCAROTID']=$objQuery['LCAROTID'];
        $result['RTEMPART']=$objQuery['RTEMPART'];
        $result['LTEMPART']=$objQuery['LTEMPART'];
        $result['RCNV']=$objQuery['RCNV'];
        $result['LCNV']=$objQuery['LCNV'];
        $result['RCNVII']=$objQuery['RCNVII'];
        $result['LCNVII']=$objQuery['LCNVII'];
        $result['ODSCHIRMER1']=$objQuery['ODSCHIRMER1'];
        $result['OSSCHIRMER1']=$objQuery['OSSCHIRMER1'];
        $result['ODSCHIRMER2']=$objQuery['ODSCHIRMER2'];
        $result['OSSCHIRMER2']=$objQuery['OSSCHIRMER2'];
        $result['ODTBUT']=$objQuery['ODTBUT'];
        $result['OSTBUT']=$objQuery['OSTBUT'];
        $result['OSHERTEL']=$objQuery['OSHERTEL'];
        $result['HERTELBASE']=$objQuery['HERTELBASE'];
        $result['ODPIC']=$objQuery['ODPIC'];
        $result['OSPIC']=$objQuery['OSPIC'];
        $result['EXT_COMMENTS']=$objQuery['EXT_COMMENTS'];
        $result["json"] = json_encode($result);
        echo json_encode($result); 
    } elseif ($zone =="ANTSEG") {
        $result['OSCONJ']=$objQuery['OSCONJ'];
        $result['ODCONJ']=$objQuery['ODCONJ'];
        $result['ODCORNEA']=$objQuery['ODCORNEA'];
        $result['OSCORNEA']=$objQuery['OSCORNEA'];
        $result['ODAC']=$objQuery['ODAC'];
        $result['OSAC']=$objQuery['OSAC'];
        $result['ODLENS']=$objQuery['ODLENS'];
        $result['OSLENS']=$objQuery['OSLENS'];
        $result['ODIRIS']=$objQuery['ODIRIS'];
        $result['OSIRIS']=$objQuery['OSIRIS'];
        $result['ODKTHICKNESS']=$objQuery['ODKTHICKNESS'];
        $result['OSKTHICKNESS']=$objQuery['OSKTHICKNESS'];
        $result['ODGONIO']=$objQuery['ODGONIO'];
        $result['OSGONIO']=$objQuery['OSGONIO'];
        $result['ODSHRIMER1']=$objQuery['ODSHIRMER1'];
        $result['OSSHRIMER1']=$objQuery['OSSHIRMER1'];
        $result['ODSHRIMER2']=$objQuery['ODSHIRMER2'];
        $result['OSSHRIMER2']=$objQuery['OSSHIRMER2'];
        $result['ODTBUT']=$objQuery['ODTBUT'];
        $result['OSTBUT']=$objQuery['OSTBUT'];
        $result['ANTSEG_COMMENTS']=$objQuery['ANTSEG_COMMENTS'];
        $result["json"] = json_encode($result);
        echo json_encode($result); 
    } elseif ($zone =="RETINA") {
        $result['ODDISC']=$objQuery['ODDISC'];
        $result['OSDISC']=$objQuery['OSDISC'];
        $result['ODCUP']=$objQuery['ODCUP'];
        $result['OSCUP']=$objQuery['OSCUP'];
        $result['ODMACULA']=$objQuery['ODMACULA'];
        $result['OSMACULA']=$objQuery['OSMACULA'];
        $result['ODVESSELS']=$objQuery['ODVESSELS'];
        $result['OSVESSELS']=$objQuery['OSVESSELS'];
        $result['ODPERIPH']=$objQuery['ODPERIPH'];
        $result['OSPERIPH']=$objQuery['OSPERIPH'];
        $result['ODDRAWING']=$objQuery['ODDRAWING'];
        $result['OSDRAWING']=$objQuery['OSDRAWING'];
        $result['ODCMT']=$objQuery['ODCMT'];
        $result['OSCMT']=$objQuery['OSCMT'];
        $result['RETINA_COMMENTS']=$objQuery['RETINA_COMMENTS'];
        $result["json"] = json_encode($result);
        echo json_encode($result); 
    } elseif ($zone =="NEURO") {
        $result['ACT']=$objQuery['ACT'];
        $result['ACT5CCDIST']=$objQuery['ACT5CCDIST'];
        $result['ACT1CCDIST']=$objQuery['ACT1CCDIST'];
        $result['ACT2CCDIST']=$objQuery['ACT2CCDIST'];
        $result['ACT3CCDIST']=$objQuery['ACT3CCDIST'];
        $result['ACT4CCDIST']=$objQuery['ACT4CCDIST'];
        $result['ACT6CCDIST']=$objQuery['ACT6CCDIST'];
        $result['ACT7CCDIST']=$objQuery['ACT7CCDIST'];
        $result['ACT8CCDIST']=$objQuery['ACT8CCDIST'];
        $result['ACT9CCDIST']=$objQuery['ACT9CCDIST'];
        $result['ACT10CCDIST']=$objQuery['ACT10CCDIST'];
        $result['ACT11CCDIST']=$objQuery['ACT11CCDIST'];
        $result['ACT1SCDIST']=$objQuery['ACT1SCDIST'];
        $result['ACT2SCDIST']=$objQuery['ACT2SCDIST'];
        $result['ACT3SCDIST']=$objQuery['ACT3SCDIST'];
        $result['ACT4SCDIST']=$objQuery['ACT4SCDIST'];
        $result['ACT5SCDIST']=$objQuery['ACT5SCDIST'];
        $result['ACT6SCDIST']=$objQuery['ACT6SCDIST'];
        $result['ACT7SCDIST']=$objQuery['ACT7SCDIST'];
        $result['ACT8SCDIST']=$objQuery['ACT8SCDIST'];
        $result['ACT9SCDIST']=$objQuery['ACT9SCDIST'];
        $result['ACT10SCDIST']=$objQuery['ACT10SCDIST'];
        $result['ACT11SCDIST']=$objQuery['ACT11SCDIST'];
        $result['ACT1SCNEAR']=$objQuery['ACT1SCNEAR'];
        $result['ACT2SCNEAR']=$objQuery['ACT2SCNEAR'];
        $result['ACT3SCNEAR']=$objQuery['ACT3SCNEAR'];
        $result['ACT4SCNEAR']=$objQuery['ACT4SCNEAR'];
        $result['ACT5CCNEAR']=$objQuery['ACT5CCNEAR'];
        $result['ACT6CCNEAR']=$objQuery['ACT6CCNEAR'];
        $result['ACT7CCNEAR']=$objQuery['ACT7CCNEAR'];
        $result['ACT8CCNEAR']=$objQuery['ACT8CCNEAR'];
        $result['ACT9CCNEAR']=$objQuery['ACT9CCNEAR'];
        $result['ACT10CCNEAR']=$objQuery['ACT10CCNEAR'];
        $result['ACT11CCNEAR']=$objQuery['ACT11CCNEAR'];
        $result['ACT5SCNEAR']=$objQuery['ACT5SCNEAR'];
        $result['ACT6SCNEAR']=$objQuery['ACT6SCNEAR'];
        $result['ACT7SCNEAR']=$objQuery['ACT7SCNEAR'];
        $result['ACT8SCNEAR']=$objQuery['ACT8SCNEAR'];
        $result['ACT9SCNEAR']=$objQuery['ACT9SCNEAR'];
        $result['ACT10SCNEAR']=$objQuery['ACT10SCNEAR'];
        $result['ACT11SCNEAR']=$objQuery['ACT11SCNEAR'];
        $result['ACT1CCNEAR']=$objQuery['ACT1CCNEAR'];
        $result['ACT2CCNEAR']=$objQuery['ACT2CCNEAR'];
        $result['ACT3CCNEAR']=$objQuery['ACT3CCNEAR'];
        $result['ACT4CCNEAR']=$objQuery['ACT4CCNEAR'];
        $result['ODVF1']=$objQuery['ODVF1'];
        $result['ODVF2']=$objQuery['ODVF2'];
        $result['ODVF3']=$objQuery['ODVF3'];
        $result['ODVF4']=$objQuery['ODVF4'];
        $result['OSVF1']=$objQuery['OSVF1'];
        $result['OSVF2']=$objQuery['OSVF2'];
        $result['OSVF3']=$objQuery['OSVF3'];
        $result['OSVF4']=$objQuery['OSVF4'];
        $result['MOTILITY_RS']=$objQuery['MOTILITY_RS'];
        $result['MOTILITY_RI']=$objQuery['MOTILITY_RI'];
        $result['MOTILITY_RR']=$objQuery['MOTILITY_RR'];
        $result['MOTILITY_RL']=$objQuery['MOTILITY_RL'];
        $result['MOTILITY_LS']=$objQuery['MOTILITY_LS'];
        $result['MOTILITY_LI']=$objQuery['MOTILITY_LI'];
        $result['MOTILITY_LR']=$objQuery['MOTILITY_LR'];
        $result['MOTILITY_LL']=$objQuery['MOTILITY_LL'];
        $result['NEURO_COMMENTS']=$objQuery['NEURO_COMMENTS'];
        $result['STEREOPSIS']=$objQuery['STEREOPSIS'];
        $result['ODNPA']=$objQuery['ODNPA'];
        $result['OSNPA']=$objQuery['OSNPA'];
        $result['VERTFUSAMPS']=$objQuery['VERTFUSAMPS'];
        $result['DIVERGENCEAMPS']=$objQuery['DIVERGENCEAMPS'];
        $result['NPC']=$objQuery['NPC'];
        $result['DACCDIST']=$objQuery['DACCDIST'];
        $result['DACCNEAR']=$objQuery['DACCNEAR'];
        $result['CACCDIST']=$objQuery['CACCDIST'];
        $result['CACCNEAR']=$objQuery['CACCNEAR'];
        $result['ODCOLOR']=$objQuery['ODCOLOR'];
        $result['OSCOLOR']=$objQuery['OSCOLOR'];
        $result['ODCOINS']=$objQuery['ODCOINS'];
        $result['OSCOINS']=$objQuery['OSCOINS'];
        $result['ODREDDESAT']=$objQuery['ODREDDESAT'];
        $result['OSREDDESAT']=$objQuery['OSREDDESAT'];
        $result['ODPUPILSIZE1']=$objQuery['ODPUPILSIZE1'];
        $result['ODPUPILSIZE2']=$objQuery['ODPUPILSIZE2'];
        $result['ODPUPILREACTIVITY']=$objQuery['ODPUPILREACTIVITY'];
        $result['ODAPD']=$objQuery['ODAPD'];
        $result['OSPUPILSIZE1']=$objQuery['OSPUPILSIZE1'];
        $result['OSPUPILSIZE2']=$objQuery['OSPUPILSIZE2'];
        $result['OSPUPILREACTIVITY']=$objQuery['OSPUPILREACTIVITY'];
        $result['OSAPD']=$objQuery['OSAPD'];
        $result['DIMODPUPILSIZE1']=$objQuery['DIMODPUPILSIZE1'];
        $result['DIMODPUPILSIZE2']=$objQuery['DIMODPUPILSIZE2'];
        $result['DIMODPUPILREACTIVITY']=$objQuery['DIMODPUPILREACTIVITY'];
        $result['DIMOSPUPILSIZE1']=$objQuery['DIMOSPUPILSIZE1'];
        $result['DIMOSPUPILSIZE2']=$objQuery['DIMOSPUPILSIZE2'];
        $result['DIMOSPUPILREACTIVITY']=$objQuery['DIMOSPUPILREACTIVITY'];
        $result['PUPIL_COMMENTS']=$objQuery['PUPIL_COMMENTS'];
        $result['ODVFCONFRONTATION1']=$objQuery['ODVFCONFRONTATION1'];
        $result['ODVFCONFRONTATION2']=$objQuery['ODVFCONFRONTATION2'];
        $result['ODVFCONFRONTATION3']=$objQuery['ODVFCONFRONTATION3'];
        $result['ODVFCONFRONTATION4']=$objQuery['ODVFCONFRONTATION4'];
        $result['ODVFCONFRONTATION5']=$objQuery['ODVFCONFRONTATION5'];
        $result['OSVFCONFRONTATION1']=$objQuery['OSVFCONFRONTATION1'];
        $result['OSVFCONFRONTATION2']=$objQuery['OSVFCONFRONTATION2'];
        $result['OSVFCONFRONTATION3']=$objQuery['OSVFCONFRONTATION3'];
        $result['OSVFCONFRONTATION4']=$objQuery['OSVFCONFRONTATION4'];
        $result['OSVFCONFRONTATION5']=$objQuery['OSVFCONFRONTATION5'];
        $result["json"] = json_encode($result);
        echo json_encode($result); 
    } elseif ($zone =="ALL") {
        $result['RUL']=$objQuery['RUL'];
        $result['LUL']=$objQuery['LUL'];
        $result['RLL']=$objQuery['RLL'];
        $result['LLL']=$objQuery['LLL'];
        $result['RBROW']=$objQuery['RBROW'];
        $result['LBROW']=$objQuery['LBROW'];
        $result['RMCT']=$objQuery['RMCT'];
        $result['LMCT']=$objQuery['LMCT'];
        $result['RADNEXA']=$objQuery['RADNEXA'];
        $result['LADNEXA']=$objQuery['LADNEXA'];
        $result['RMRD']=$objQuery['RMRD'];
        $result['LMRD']=$objQuery['LMRD'];
        $result['RLF']=$objQuery['RLF'];
        $result['LLF']=$objQuery['LLF'];
        $result['RVFISSURE']=$objQuery['RVFISSURE'];
        $result['LVFISSURE']=$objQuery['LVFISSURE'];
        $result['ODHERTEL']=$objQuery['ODHERTEL'];
        $result['OSHERTEL']=$objQuery['OSHERTEL'];
        $result['HERTELBASE']=$objQuery['HERTELBASE'];
        $result['ODPIC']=$objQuery['ODPIC'];
        $result['OSPIC']=$objQuery['OSPIC'];
        $result['EXT_COMMENTS']=$objQuery['EXT_COMMENTS'];
        
        $result['OSCONJ']=$objQuery['OSCONJ'];
        $result['ODCONJ']=$objQuery['ODCONJ'];
        $result['ODCORNEA']=$objQuery['ODCORNEA'];
        $result['OSCORNEA']=$objQuery['OSCORNEA'];
        $result['ODAC']=$objQuery['ODAC'];
        $result['OSAC']=$objQuery['OSAC'];
        $result['ODLENS']=$objQuery['ODLENS'];
        $result['OSLENS']=$objQuery['OSLENS'];
        $result['ODIRIS']=$objQuery['ODIRIS'];
        $result['OSIRIS']=$objQuery['OSIRIS'];
        $result['ODKTHICKNESS']=$objQuery['ODKTHICKNESS'];
        $result['OSKTHICKNESS']=$objQuery['OSKTHICKNESS'];
        $result['ODGONIO']=$objQuery['ODGONIO'];
        $result['OSGONIO']=$objQuery['OSGONIO'];
        $result['ANTSEG_COMMENTS']=$objQuery['ANTSEG_COMMENTS'];
        
        $result['ODDISC']=$objQuery['ODDISC'];
        $result['OSDISC']=$objQuery['OSDISC'];
        $result['ODCUP']=$objQuery['ODCUP'];
        $result['OSCUP']=$objQuery['OSCUP'];
        $result['ODMACULA']=$objQuery['ODMACULA'];
        $result['OSMACULA']=$objQuery['OSMACULA'];
        $result['ODVESSELS']=$objQuery['ODVESSELS'];
        $result['OSVESSELS']=$objQuery['OSVESSELS'];
        $result['ODPERIPH']=$objQuery['ODPERIPH'];
        $result['OSPERIPH']=$objQuery['OSPERIPH'];
        $result['ODDRAWING']=$objQuery['ODDRAWING'];
        $result['OSDRAWING']=$objQuery['OSDRAWING'];
        $result['ODCMT']=$objQuery['ODCMT'];
        $result['OSCMT']=$objQuery['OSCMT'];
        $result['RETINA_COMMENTS']=$objQuery['RETINA_COMMENTS'];

        $result['ACT']=$objQuery['ACT'];
        $result['ACT5CCDIST']=$objQuery['ACT5CCDIST'];
        $result['ACT1CCDIST']=$objQuery['ACT1CCDIST'];
        $result['ACT2CCDIST']=$objQuery['ACT2CCDIST'];
        $result['ACT3CCDIST']=$objQuery['ACT3CCDIST'];
        $result['ACT4CCDIST']=$objQuery['ACT4CCDIST'];
        $result['ACT6CCDIST']=$objQuery['ACT6CCDIST'];
        $result['ACT7CCDIST']=$objQuery['ACT7CCDIST'];
        $result['ACT8CCDIST']=$objQuery['ACT8CCDIST'];
        $result['ACT9CCDIST']=$objQuery['ACT9CCDIST'];
        $result['ACT10CCDIST']=$objQuery['ACT10CCDIST'];
        $result['ACT11CCDIST']=$objQuery['ACT11CCDIST'];
        $result['ACT1SCDIST']=$objQuery['ACT1SCDIST'];
        $result['ACT2SCDIST']=$objQuery['ACT2SCDIST'];
        $result['ACT3SCDIST']=$objQuery['ACT3SCDIST'];
        $result['ACT4SCDIST']=$objQuery['ACT4SCDIST'];
        $result['ACT5SCDIST']=$objQuery['ACT5SCDIST'];
        $result['ACT6SCDIST']=$objQuery['ACT6SCDIST'];
        $result['ACT7SCDIST']=$objQuery['ACT7SCDIST'];
        $result['ACT8SCDIST']=$objQuery['ACT8SCDIST'];
        $result['ACT9SCDIST']=$objQuery['ACT9SCDIST'];
        $result['ACT10SCDIST']=$objQuery['ACT10SCDIST'];
        $result['ACT11SCDIST']=$objQuery['ACT11SCDIST'];
        $result['ACT1SCNEAR']=$objQuery['ACT1SCNEAR'];
        $result['ACT2SCNEAR']=$objQuery['ACT2SCNEAR'];
        $result['ACT3SCNEAR']=$objQuery['ACT3SCNEAR'];
        $result['ACT4SCNEAR']=$objQuery['ACT4SCNEAR'];
        $result['ACT5CCNEAR']=$objQuery['ACT5CCNEAR'];
        $result['ACT6CCNEAR']=$objQuery['ACT6CCNEAR'];
        $result['ACT7CCNEAR']=$objQuery['ACT7CCNEAR'];
        $result['ACT8CCNEAR']=$objQuery['ACT8CCNEAR'];
        $result['ACT9CCNEAR']=$objQuery['ACT9CCNEAR'];
        $result['ACT10CCNEAR']=$objQuery['ACT10CCNEAR'];
        $result['ACT11CCNEAR']=$objQuery['ACT11CCNEAR'];
        $result['ACT5SCNEAR']=$objQuery['ACT5SCNEAR'];
        $result['ACT6SCNEAR']=$objQuery['ACT6SCNEAR'];
        $result['ACT7SCNEAR']=$objQuery['ACT7SCNEAR'];
        $result['ACT8SCNEAR']=$objQuery['ACT8SCNEAR'];
        $result['ACT9SCNEAR']=$objQuery['ACT9SCNEAR'];
        $result['ACT10SCNEAR']=$objQuery['ACT10SCNEAR'];
        $result['ACT11SCNEAR']=$objQuery['ACT11SCNEAR'];
        $result['ACT1CCNEAR']=$objQuery['ACT1CCNEAR'];
        $result['ACT2CCNEAR']=$objQuery['ACT2CCNEAR'];
        $result['ACT3CCNEAR']=$objQuery['ACT3CCNEAR'];
        $result['ACT4CCNEAR']=$objQuery['ACT4CCNEAR'];
        $result['ODVF1']=$objQuery['ODVF1'];
        $result['ODVF2']=$objQuery['ODVF2'];
        $result['ODVF3']=$objQuery['ODVF3'];
        $result['ODVF4']=$objQuery['ODVF4'];
        $result['OSVF1']=$objQuery['OSVF1'];
        $result['OSVF2']=$objQuery['OSVF2'];
        $result['OSVF3']=$objQuery['OSVF3'];
        $result['OSVF4']=$objQuery['OSVF4'];
        $result['MOTILITY_RS']=$objQuery['MOTILITY_RS'];
        $result['MOTILITY_RI']=$objQuery['MOTILITY_RI'];
        $result['MOTILITY_RR']=$objQuery['MOTILITY_RR'];
        $result['MOTILITY_RL']=$objQuery['MOTILITY_RL'];
        $result['MOTILITY_LS']=$objQuery['MOTILITY_LS'];
        $result['MOTILITY_LI']=$objQuery['MOTILITY_LI'];
        $result['MOTILITY_LR']=$objQuery['MOTILITY_LR'];
        $result['MOTILITY_LL']=$objQuery['MOTILITY_LL'];
        $result['NEURO_COMMENTS']=$objQuery['NEURO_COMMENTS'];
        $result['STEREOPSIS']=$objQuery['STEREOPSIS'];
        $result['ODNPA']=$objQuery['ODNPA'];
        $result['OSNPA']=$objQuery['OSNPA'];
        $result['VERTFUSAMPS']=$objQuery['VERTFUSAMPS'];
        $result['DIVERGENCEAMPS']=$objQuery['DIVERGENCEAMPS'];
        $result['NPC']=$objQuery['NPC'];
        $result['DACCDIST']=$objQuery['DACCDIST'];
        $result['DACCNEAR']=$objQuery['DACCNEAR'];
        $result['CACCDIST']=$objQuery['CACCDIST'];
        $result['CACCNEAR']=$objQuery['CACCNEAR'];
        $result['ODCOLOR']=$objQuery['ODCOLOR'];
        $result['OSCOLOR']=$objQuery['OSCOLOR'];
        $result['ODCOINS']=$objQuery['ODCOINS'];
        $result['OSCOINS']=$objQuery['OSCOINS'];
        $result['ODREDDESAT']=$objQuery['ODREDDESAT'];
        $result['OSREDDESAT']=$objQuery['OSREDDESAT'];


        $result['ODPUPILSIZE1']=$objQuery['ODPUPILSIZE1'];
        $result['ODPUPILSIZE2']=$objQuery['ODPUPILSIZE2'];
        $result['ODPUPILREACTIVITY']=$objQuery['ODPUPILREACTIVITY'];
        $result['ODAPD']=$objQuery['ODAPD'];
        $result['OSPUPILSIZE1']=$objQuery['OSPUPILSIZE1'];
        $result['OSPUPILSIZE2']=$objQuery['OSPUPILSIZE2'];
        $result['OSPUPILREACTIVITY']=$objQuery['OSPUPILREACTIVITY'];
        $result['OSAPD']=$objQuery['OSAPD'];
        $result['DIMODPUPILSIZE1']=$objQuery['DIMODPUPILSIZE1'];
        $result['DIMODPUPILSIZE2']=$objQuery['DIMODPUPILSIZE2'];
        $result['DIMODPUPILREACTIVITY']=$objQuery['DIMODPUPILREACTIVITY'];
        $result['DIMOSPUPILSIZE1']=$objQuery['DIMOSPUPILSIZE1'];
        $result['DIMOSPUPILSIZE2']=$objQuery['DIMOSPUPILSIZE2'];
        $result['DIMOSPUPILREACTIVITY']=$objQuery['DIMOSPUPILREACTIVITY'];
        $result['PUPIL_COMMENTS']=$objQuery['PUPIL_COMMENTS'];
        $result['ODVFCONFRONTATION1']=$objQuery['ODVFCONFRONTATION1'];
        $result['ODVFCONFRONTATION2']=$objQuery['ODVFCONFRONTATION2'];
        $result['ODVFCONFRONTATION3']=$objQuery['ODVFCONFRONTATION3'];
        $result['ODVFCONFRONTATION4']=$objQuery['ODVFCONFRONTATION4'];
        $result['ODVFCONFRONTATION5']=$objQuery['ODVFCONFRONTATION5'];
        $result['OSVFCONFRONTATION1']=$objQuery['OSVFCONFRONTATION1'];
        $result['OSVFCONFRONTATION2']=$objQuery['OSVFCONFRONTATION2'];
        $result['OSVFCONFRONTATION3']=$objQuery['OSVFCONFRONTATION3'];
        $result['OSVFCONFRONTATION4']=$objQuery['OSVFCONFRONTATION4'];
        $result['OSVFCONFRONTATION5']=$objQuery['OSVFCONFRONTATION5'];
        $result["json"] = json_encode($result);
        echo json_encode($result); 

    }}

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
 *      Reports (to do), upload(done)and image DB(done)
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
        // use openEMR functionality of now...
        /*  if (count($documents['docs_in_cat_id'][$documents['zones'][$category_value][$j]['id']]) > '0') {
            $episode .= '<a href="../../forms/'.$form_folder.'/css/AnythingSlider/simple.php?display=i&category_id='.$documents['zones'][$category_value][$j]['id'].'&encounter='.$encounter.'&category_name='.urlencode(xla($category_value)).'"
                    onclick="return dopopup(\'../../forms/'.$form_folder.'/css/AnythingSlider/simple.php?display=i&category_id='.$documents['zones'][$category_value][$j]['id'].'&encounter='.$encounter.'&category_name='.urlencode(xla($category_value)).'\')">
                    <img src="../../forms/'.$form_folder.'/images/jpg.png" class="little_image" /></a>';
        
        */
        if (count($documents['docs_in_cat_id'][$documents['zones'][$category_value][$j]['id']]) > '0') {
            $episode .= '<a href="../../../controller.php?document&view&patient_id='.$pid.'&parent_idX='.$documents['zones'][$category_value][$j]['id'].'&" 
                    onclick="return dopopup(\'../../../controller.php?document&view&patient_id='.$pid.'&parent_idX='.$documents['zones'][$category_value][$j]['id'].'&document_id='.$doc[id].'&as_file=false\')">
                    <img src="../../forms/'.$form_folder.'/images/jpg.png" class="little_image" /></a>';
        }
    //http://www.oculoplasticsllc.com/openemr/controller.php?document&view&patient_id=1&doc_id=411&
        $episode .= '</td></tr>';
        $i++;
    }  
    return array($documents,$episode);
}

/**
 *  
 */
function redirector($url) {
    global $form_folder;
    
     ?>
    <html>
    <head>
    <!-- jQuery library -->
    <script src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="<?php echo $GLOBALS['webroot']; ?>/library/js/bootstrap.min.js"></script>  
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <!-- Add Font stuff for the look and feel.  -->
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/css/pure-min.css">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/style.css" type="text/css">    
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/css/font-awesome-4.2.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
    <body>
    <?php
    $input_echo = menu_overhaul_top($pid,$encounter);
    //url
    ?>
    <object data="<?php echo $GLOBALS['webroot'].$url; ?>" width="600" height="400"> 
    <embed src="<?php echo $GLOBALS['webroot'].$url; ?>" width="600" height="400"> </embed> 
    Error: Embedded data could not be displayed. </object>
    <?php 
    
    $output = menu_overhaul_bottom($pid,$encounter);
    exit(0);}

/**
 *  This is an experiment to start shifting clinical functions into a separate interface.
 */
function menu_overhaul_top($pid,$encounter,$title="Eye Exam") {
    global $form_folder;
    global $prov_data;
    $providerNAME = $prov_data['fname']." ".$prov_data['lname'];
    
    ?>
    <div id="wrapper" style="font-size: 1.4em;">
        <!-- Navigation -->
                <!-- Navigation -->
                <br /><br />
    <nav class="navbar-fixed-top navbar-custom navbar-bright navbar-fixed-top" role="banner" role="navigation" style="margin-bottom: 0;z-index:1999999;">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#oer-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand right" href="/openemr" onclick="window.close();" title="Close this window." style="font-size:0.8em;font-weight:600;">OpenEMR <img src="/openemr/sites/default/images/login_logo.gif" class="little_image left"></a>
        </div>

       <div class="navbar-custom" id="oer-navbar-collapse-1">
            <ul class="navbar-nav">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_file" role="button" aria-expanded="true">File </a>
                    <ul class="dropdown-menu" role="menu">
                       <li id="menu_PREFERENCES" name="menu_PREFERENCES"> <a  id="BUTTON_PREFERENCES_menu" href="#">Preferences</a></li>
                        <li id="menu_TEXT" name="menu_TEXT" class="active"> <a  id="BUTTON_SAVE_menu" href="#"> Save </a></li>
                        <li id="menu_DRAW" name="menu_DRAW"> <a href="#" id="BUTTON_PRINT_menu" onclick="window.print();return false;">Print</a></li>
                        <li class="dropdown-submenu">
                            <a href="#">Print2 </a>
                            <ul class="dropdown-menu" role="menu">
                                <li id="menu_PRINT_screen" name="menu_PRINT_screen"> <a href="#" id="BUTTON_PRINT_screen" onclick="window.print();return false;">Print Screen</a></li>
                                <li id="menu_PRINT_draw" name="menu_PRINT_draw"> <a href="#" id="BUTTON_PRINT_draw" onclick="window.print();return false;">Print Drawings</a></li>
                                <li id="menu_PRINT_narrative" name="menu_PRINT_narrative"> <a href="#" id="BUTTON_PRINT_narrative" onclick="window.print();return false;">Print Narrative</a></li>
                            </ul>
                        </li>
                        <li id="menu_QP" name="menu_QP" ><a href="#"  onclick='window.close();'> Close Window</a></li>
                        <li class="divider"></li>
                        <li id="menu_HPI" name="menu_HPI" ><a href="#" onclick='window.close();' >Return to OpenEMR</a></li>
                        <li id="menu_PMH" name="menu_PMH" ><a href="#PMH_1">Quit</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_edit" role="button" aria-expanded="true">Edit </a>
                    <ul class="dropdown-menu" role="menu">
                        <li id="menu_Undo" name="menu_Undo" class="disabled"> <a  id="BUTTON_Undo_menu" href="#"> Undo </a></li>
                        <li id="menu_Redo" name="menu_Redo" class="disabled"> <a  id="BUTTON_Redo_menu" href="#"> Redo </a></li>
                        <li class="divider"></li>
                        <li id="menu_Copy" name="menu_Copy" class="disabled"> <a class="right" style="padding:auto 10 auto auto;" href="#" id="BUTTON_DRAW_menu">Copy Ctl-C</a></li>
                        <li id="menu_Cut" name="menu_Cut" class="disabled"><a href="#"  onclick='show_QP();'> Cut</a></li>
                        <li id="menu_Paste" name="menu_Paste" class="disabled" ><a href="#" onclick='show_Section("HPI_1");'>Paste</a></li>
                        <li id="menu_Delete" name="menu_Delete" class="disabled"><a href="#PMH_1">Delete</a></li>
                        <li class="divider"></li>
                        <li id="menu_PRIORS" name="menu_PRIORS" class="disabled"> <a href="#">Show Priors</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_view" role="button" aria-expanded="true">View </a>
                    <ul class="dropdown-menu" role="menu">
                        <li id="menu_TEXT" name="menu_TEXT" class="active"> <a  id="BUTTON_TEXT_menu" href="#" onclick="show_TEXT();">Text <i class="right fa fa-text"></i></a></li>
                        <li id="menu_DRAW" name="menu_DRAW"> <a href="#mid_menu" id="BUTTON_DRAW_menu" nam="BUTTON_DRAW_menu">Draw</a></li>
                        <li id="menu_QP" name="menu_QP" ><a href="#mid_menu"  onclick='show_QP();'> Quick Picks</a></li>
                        <li class="divider"></li>
                        <li id="menu_HPI" name="menu_HPI" ><a href="#HPI_anchor" onclick='show_Section("HPI_1");' >HPI</a></li>
                        <li id="menu_PMH" name="menu_PMH" ><a href="#PMH_anchor">PMH</a></li>
                        <li id="menu_EXT" name="menu_EXT" ><a href="#EXT_anchor">External</a></li>
                        <li id="menu_ANTSEG" name="menu_ANTSEG" ><a href="#ANTSEG_anchor">Anterior Segment</a></li>
                        <li id="menu_POSTSEG" name="menu_POSTSEG" ><a href="#RETINA_anchor">Posterior Segment</a></li>
                        <li id="menu_NEURO" name="menu_NEURO" ><a href="#NEURO_anchor">Neuro</a></li>
                        <li class="divider"></li>
                        <li id="menu_PRIORS" name="menu_PRIORS" > <a href="#SELECTION_ROW_anchor" onclick='$("#PRIOR_ALL").val("").trigger("change");'>Show Priors</a></li>
                    </ul>
                </li> 
                <li class="dropdown">
                    <a class="dropdown-toggle"  class="disabled" role="button" id="menu_dropdown_patients" data-toggle="dropdown">Patients</a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                      <li role="presentation" class="disabled"><a role="menuitem"  class="disabled" tabindex="-1" onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/view.php?url=<?php echo urlencode("/interface/main/finder/dynamic_finder.php"); ?>');"> Patients </a></li>
                      <li  class="disabled"><a tabindex="-1" href="#">New/Search</a> </li>
                      <li role="presentation" class="disabled"><a role="menuitem"  class="disabled" tabindex="-1" href="#">  Summary</a></li>
                      <li role="presentation" class="divider"></li>
                      <li role="presentation" class="disabled"><a role="menuitem"  class="disabled" tabindex="-1" href="#">Create Visit</a></span></li>
                      <li class="active"><a role="menuitem" id="BUTTON_DRAW_menu" tabindex="-1" href="#">  Current</a></li>
                      <li role="presentation" class="disabled"><a role="menuitem"  class="disabled" tabindex="-1" href="#">Visit History</a></li>
                      <li role="presentation" class="divider"></li>
                      <li role="presentation" class="disabled"><a role="menuitem"  class="disabled" tabindex="-1" href="#">Record Request</a></li>
                      <li role="presentation" class="divider"></li>
                      <li role="presentation" class="disabled"><a role="menuitem"  class="disabled" tabindex="-1" href="#">Upload Item</a></li>
                      <li role="presentation" class="disabled"><a role="menuitem"  class="disabled" tabindex="-1" href="#">Pending Approval</a></li>
                    </ul>
                </li>
                <!--
                <li class="dropdown">
                    <a class="dropdown-toggle" role="button" id="menu_dropdown_clinical" data-toggle="dropdown">Encounter</a>
                    <?php
                    /*
                     *  Here we need to incorporate the menu from openEMR too.  What Forms are active for this installation?
                     *  openEMR uses Encounter Summary - Administrative - Clinical.  Think about the menu as a new entity with
                     *  this + new functionaity.  It is OK to keep or consider changing any NAMES when creating the menu.  I assume
                     *  a consensus will develop. 
                    */
                    ?>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                        <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="#">Eye Exam</a></li>
                        <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="#">Documents</a></li>
                        <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="#">Imaging</a></li>
                        <li role="presentation" class="divider"></li>
                        <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="#IOP_CHART">IOP Chart</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle" role="button" id="menu_dropdown_window" data-toggle="dropdown">Window</a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                      <li role="presentation" class=""><a role="menuitem" tabindex="-1" onclick="restoreSession();" href="http://www.oculoplasticsllc.com/openemr/interface/main/calendar/index.php?module=PostCalendar&viewtype=day&func=view&framewidth=1020"><i class="fa fa-calendar text-error"> </i>  Calendar</a></li>
                      <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="#">Messages</a></li>
                      <li role="presentation" class="dropdown-header">Patient/client</li>
                      <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="#">Patients</a></li>
                      <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="#">New/Search</a></li>
                      <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="#">Summary</a></li>
                      <li role="presentation" class="disabled divider"></li>
                      <li role="presentation" class="disabled"><a role="menuitem" class="disabled" tabindex="-1" href="#">About Us</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" 
                       id="menu_dropdown_library" role="button" 
                       aria-expanded="false">Library</a>
                    <ul class="dropdown-menu" role="menu">
                        <li class="disabled"><a href="#" >Upload</a></li>
                        <li class="disabled"><a href="../../forms/eye_mag/css/AnythingSlider/simple.php?display=fullscreen&encounter=<?php echo xla($encounter); ?>">Documents</a></li>
                        <li class="disabled"><a href="#">Images</a></li>
                        <li class="divider"></li>
                        <li class="disabled"><a href="#">More</a></li>
                        <li class="divider"></li>
                        <li class="disabled"><a href="#">One more separated link</a></li>
                    </ul>
                </li>
             -->  
               <!-- let's import the openEMR menu here.  -->
                <?php
                    $reg = Menu_myGetRegistered();
                    if (!empty($reg)) {
                        $StringEcho= '<li class="dropdown">';
                        if ( $encounterLocked === false || !(isset($encounterLocked))) {
                            foreach ($reg as $entry) {
                                $new_category = trim($entry['category']);
                                $new_nickname = trim($entry['nickname']);
                                if ($new_category == '') {$new_category = htmlspecialchars(xl('Miscellaneous'),ENT_QUOTES);}
                                if ($new_nickname != '') {$nickname = $new_nickname;}
                                else {$nickname = $entry['name'];}
                                if ($old_category != $new_category) { //new category, new menu section
                                    $new_category_ = $new_category;
                                    $new_category_ = str_replace(' ','_',$new_category_);
                                    if ($old_category != '') {
                                        $StringEcho.= "
                                            </ul>
                                        </li>
                                        <li class='dropdown'>
                                        ";
                                    }
                                  $StringEcho.= '
                                  <a class="dropdown-toggle" data-toggle="dropdown" 
                                    id="menu_dropdown_'.$new_category_.'" role="button" 
                                    aria-expanded="false">'.$new_category.'</a>
                                    <ul class="dropdown-menu" role="menu">
                                    ';
                                  $old_category = $new_category;
                                } //target this link back into the correct frame.  Mais porquois?
                                $StringEcho.= "<li><a href='".$GLOBALS['webroot']."/interface/patient_file/encounter/load_form.php?formname=" .urlencode($entry['directory'])."'>" . xl_form_title($nickname) . "</a></li>";
                          }
                      }
                      $StringEcho.= '
                        </ul>
                      </li>
                      ';
                    } else { $StringEcho .= "nada here que pasa?"; }
                    echo $StringEcho;
                ?>
            </ul>
             <ul class="navbar-right navbar-nav dropdown">
                <li><a href="#"><?php echo $providerNAME; ?></a></li>
            </ul> 
        </div><!-- /.navbar-collapse -->
    </nav>

    <?php 

        return $input_echo;
}
/**
 *  This is currently a floating div top and near the left with patient demographics and such.
 *  It can also be modified to create a left had column full of the PMH/MEDS/POH/ALL/etc that either
 *  moves with the page or remains static.  If so, changes to the PMH data will need to show up here too...
 */
function menu_overhaul_left($pid,$encounter) {
    global $form_folder;
    global $pat_data;
    @extract($pat_data);
    /*
     * We need to find out if the patient has a photo right? 
     */
    list($documents) = document_engine($pid);
        ?>    
    <div id="left_menu" name="left_menu" class="borderShadow col-sm-3" style="position:relative;margin-left:18px;text-align:center;padding:5px 0px 5px 5px;">
            <?
            //if the patient has a photograph, use it else use generic avitar thing.
        if ($documents['docs_in_name']['Patient Photograph'][0]['id']) {
            ?>
            <object><embed src="/openemr/controller.php?document&amp;retrieve&amp;patient_id=<?php echo $pid; ?>&amp;document_id=<?php echo $documents['docs_in_name']['Patient Photograph'][0]['id']; ?>&amp;as_file=false" frameborder="0"
                 type="<?php echo $documents['docs_in_name']['Patient Photograph'][0]['mimetype']; ?>" allowscriptaccess="always" allowfullscreen="false" width="60"></embed></object>
        <?php 
        } else {
        ?>
            <object><embed src="<?php echo $GLOBALS['web_root']; ?>/interface/forms/<?php echo $form_folder; ?>/images/anon.gif" frameborder="0"
                 type="image/gif" width="60"></embed></object>
                <?php
        }
        ?>
        
        
        <div style="position:relative;float:left;margin:auto 5px;width:140px;top:0px;">
            <table style="position:relative;float:left;margin:10px 15px;width:140px;top:0px;right:0px;font-size:12px;">
                    <tr>
                        <td class="right" >
                            <?php 
                            $age = getPatientAgeDisplay($DOB, $encounter_date);
                            echo "<b>".xlt('Name').":</b> </td><td> &nbsp;".$fname."  ".$lname."</td></tr>
                                    <tr><td class='right'><b>".xlt('DOB').":</b></td><td> &nbsp;".$DOB. "&nbsp;(".$age.")</td></tr>
                                    "; 
                            ?>
                                    <?php 
                                        /**
                                          * ?>
                                          * <tr><td class='middle' colspan='2'>
                                          *     <select>
                                          *         <option><?php global $visit_date; echo $visit_date; ?> (<?php echo $encounter; ?>)</option>
                                          * <?
                                          * List out the prior eye_mag encounters as options.  
                                          * The one above is today.  
                                          * This will run a function to go back a day, changing all the form's values to that day, 
                                          * and if e-signed and locked, changes are disabled.  Perhaps if locked what is shown is the PDF of this?
                                          * Or the report.php version?
                                          * Too slow?  Loss of javascript control of presentation.  Unable to widen or narrow or flip to the drawings,
                                          * which must also be brought into the DOM from the records area, or just be another page in the PDF.  Not so much fun.
                                          * So, same form, all fields disabled, but display JS actions active, and no saving allowed, or report.php.
                                          * We'll see.  For now just list the Visit Date: and don't allow look backs yet.
                                          *
                                          * ?>
                                          *     </select>
                                          * </td></tr>
                                          */
                                        global $visit_date;
                                        echo "<tr><td class='right'><b>".xlt('Date').":</b></td><td>&nbsp;".$visit_date."</td></tr>";
                                    ?>
                           </form>
                        </td>
                    </tr>
            </table>
        </div>
     </div>
    <div id="left_menu2" name="left_menu2" class="borderShadow col-sm-3" 
    style="width:280px;float:left;margin-left:18px;text-align:center;padding:5px 0px 5px 5px;">
    <?php 
    $query = "Select * from users where id =?";
    $prov = sqlQuery($query,array($pat_data['ref_providerID']));
    $provider = $prov['fname']." ".$prov['lname'];
    ?>
            <table style="font-size:12px;">
                <tr><td class="right"><b>PCP:</b></td><td><?php echo $provider; ?></td></tr>
                <tr><td class="right"><b>Referred By:</b></td><td><?php echo $provider; ?></td></tr>
            </table>
        </div>
         <br />

    <?php
}

/**
 *  This is currently just closing up the divs.  It can easily be a footer with the practice info
 *  or whatever you like.  Maybe a placeholder for user groups or link outs to data repositories 
 *  such as Medfetch.com/PubMed/UpToDate/DynaMed????
 *  It could provide information as to available data imports from connected machines - yes we have 
 *  data from an autorefractor needed to be imported.  The footer can be fixed or floating.
 *  It could have balance info, notes, or an upside down menu mirroring the header menu, maybe allowing
 *  the user to decide which is fixed and which is not?  Oh the possibilities.
 */
function menu_overhaul_bottom($pid,$encounter) {
 ?>
                </div>
            </div>
            <!-- /.container -->

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
 <?php
}

function undo() {
    /**  
      *  CURRENTLY JAVASCRIPT implementation 6/27/15
      *  In order to have an undo feature, we need to keep copies of the old records - not last visit but last save.
      *  And how should we do that?  The form_eye_mag is the official current record.
      *  SERVER SIDE: Create another table form_eye_mag_undo.  Each save to form_eye_mag is also saved to this table, incrementally.
      *  CLIENT SIDE: Each snapshot of text values sent to the server during a save are stored in an inmemory array.
      *  To undo simply change all the values on the form back to the prior values, either from the server or client storage area.
      *  Just like with PRIORS (server side) but all of them on the form.  Doing this client side makes the most sense.
      *  Using similar logic as is now done for the canvases in canvasdraw.js, we should be able to do this...
      *  The end user will have an unlimited number of "undos" available - well they will be able to go back to the record's
      *  original blank state, in order, how ever many that is.  When undone is run, or selected from the menu, 
      *  the values returned replace the current on screen values for display only -- a call to "save.php" has not gone out, yet.
      *  The user can scroll foward and back (Redo and Undo).  Redo is disabled if this is the latest.  Undo > once
      *  and it is enabled.  
      *  Leave the page, touch any field on the page and you will trigger a "save.php" and now you are at the tip
      *  of the sanpshots, and you will not be able to go forward.
      *  If you are happy with the change the undo provided, proceed with another entry or leave the page and save.php will store it. 
      *  We need a way to reset the undo table to this number.  It will have to be a session key or a hard coded hidden html input field.
      *  That works if we use a server side storage methid.
      *  Another way to do this is all with javascript.  We can create another monster array clientside, containing (sequential) all variable values
      *  that just drop in without involving ajax and server calls.  Seems this should be faster too, since locally performed?
      *
      *  The same server side concept has been applied to the drawings already.  If the user is drawing, there will be stored incremental images of each stroke,
      *  for each section/zone, on the client side, with only the latest changes sent to server.  Scrolling back and forth pulls from the browser cache, not server.
      *
      */ 
}

function row_deleter($table, $where) {
  $tres = sqlStatement("SELECT * FROM $table WHERE $where");
  $count = 0;
 // echo "hey there";
  while ($trow = sqlFetchArray($tres)) {
   $logstring = "";
   foreach ($trow as $key => $value) {
    if (! $value || $value == '0000-00-00 00:00:00') continue;
    if ($logstring) $logstring .= " ";
    $logstring .= $key . "='" . addslashes($value) . "'";
   }
   newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $logstring");
   ++$count;
  }
  if ($count) {
   $query = "DELETE FROM $table WHERE $where";
   echo $query . "<br />\n";
   sqlStatement($query);
  }
 }


/*
*  To make this all work, we need to delete every record for this form and encounter in the undo folder.
*  The act of finalizing and "esigning" a document to me means the document is locked.  There should be some sort of
*  encryption key here with a checksum and/or digital time mark to say this is locked and if the key fails, the values do
*  NOT natch the esigned document.  Indeed all knock-on changes should be added as addeneums or notes or whatever exists in the main
*  openEMR.  The file needs to be locked and unless someone goes into the DB to change a field's value, the program should not allow
*  any update.  If they do that, the keys will not match.  An immediate chart integreity issue is raised.  I don't know how to do this
*  but someone does...  Can a DB field be made permanent?  Can a DB record of all fields have an encryption protocol attached to it 
*  so if it is changed, the stored key no longer matches and the record is forever tainted? We should make openEMR records
*  untaintable, if that is a word.
*/
function  finalize() {
    global $form_folder;
    global $pid;
    global $encounter;
    if (($_REQUEST['action'] =='finalize') or ($_REQUEST['final'] == '1')) {
        //logic to finalize according to openEMR protocol
    }
    return;
}
/*
 * This was taken from new_form.php and is helping to integrate new menu with openEMR
 * menu seen on encounter page.
 */
function Menu_myGetRegistered($state="1", $limit="unlimited", $offset="0") {
    $sql = "SELECT category, nickname, name, state, directory, id, sql_run, " .
      "unpackaged, date FROM registry WHERE " .
      "state LIKE \"$state\" ORDER BY category, priority, name";
    if ($limit != "unlimited") $sql .= " limit $limit, $offset";
    $res = sqlStatement($sql);
    if ($res) {
        for($iter=0; $row=sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }
    } else {
        return false;
    }
    return $all;
}

function display_PMH_selector() { 
    global $issue;
  $irow = array();
if ($issue) {
  $irow = sqlQuery("SELECT * FROM lists WHERE id = ?",array($issue));
} else if ($thistype) {
  $irow['type'] = $thistype;
  $irow['subtype'] = $subtype;
}
$type_index = 0;

if (!empty($irow['type'])) {
  foreach ($ISSUE_TYPES as $key => $value) {
    if ($key == $irow['type']) break;
    ++$type_index;
  }
}

  ?>
  <html>
  <head>
  <title><?php echo $issue ? xlt('Edit') : xlt('Add New'); ?><?php echo " ".xlt('Issue'); ?></title>
  <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
  <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/style.css" type="text/css"> 
  <!-- jQuery library -->
  <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.min.js"></script>

  <!-- Latest compiled JavaScript -->
  <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/bootstrap.min.js"></script>  
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
      <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
      <![endif]-->
  <script type="text/javascript" src="../../forms/<?php echo $form_folder; ?>/js/shortcut.js"></script>
  <script type="text/javascript" src="../../forms/<?php echo $form_folder; ?>/js/my_js_base.js"></script>
  <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/css/font-awesome-4.2.0/css/font-awesome.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>

  td, select, textarea {
   font-family: Fontawesome, Arial, Helvetica, sans-serif;
   font-size: 8pt;
   } 
   
   input[type="text"]{
   text-align:left;
   background-color: #FFF8DC;
   text-align: left;

  }

  div.section {
   border: solid;
   border-width: 1px;
   border-color: #0000ff;
   margin: 0 0 0 10pt;
   padding: 5pt;
  }

  </style>

  <style type="text/css">@import url(<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.css);</style>
  <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.js"></script>
  <?php require_once($GLOBALS['srcdir'].'/dynarch_calendar_en.inc.php'); ?>
  <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar_setup.js"></script>
  <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
  <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>



  <script language="JavaScript">
   var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
   var aitypes = new Array(); // issue type attributes
   var aopts   = new Array(); // Option objects
  <?php
   $i = 0;  

  //This builds the litle quick pick list in this section.
   // Would be better as an autocomplete so lots of stuff can be seen.
   //or it is an ajax autocomplete live to the DB where a 
    // a cronjob or a mysql based view and trigger to update
   // the list_options for each $key based on the current provider
   // eg. one provider might have a lot of cataract surgery patients and list it as Phaco/PCIOL and another
   // might use a femto laser assisted Restore IOL procedure and he uses FT/Restore IOL
   // No matter the specialty, how the doctor documents can be analyzed and list_options created in the VIEW TABLE in the order
   // of their frequency.  Start at 10 should they want to always have something at the top.
   //I like the option of when updating the lists table, a trigger updates a VIEW and this autocomplete 
   //draws from the VIEW table.  Nice.  Real time update...  Need to consider top picks and that can be the role
   // of the current list_options table...  Cool.  1-10 from list_options, after that from VIEW via trigger that
   // ranks them by frequency over a limited time to keep DB humming...
         $i='0';
    foreach ($ISSUE_TYPES as $key => $value) {
      echo " aitypes[$i] = " . attr($value[3]) . ";\n";
      echo " aopts[$i] = new Array();\n";
      if ($i < "4") { // "0" = medical_problem_issue_list
        $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = ? and subtype not like 'eye'",array($key."_issue_list"));
      } else if ($i == "4") { // POH medical group - leave surgical for now. surgical will require a new issue type above too
        $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = 'medical_problem_issue_list' and subtype = 'eye'");
      } else if ($i == "5") { // FH group
        //need a way to pull FH out of patient_dataand will display frame very differently
        $qry = "";
      } else if ($i == "6") { // SocHx group - leave blank for now?
        $qry = ""; 
      } 
      while($res = sqlFetchArray($qry)){
        echo " aopts[$i][aopts[$i].length] = new Option('".attr(trim($res['option_id']))."', '".attr(xl_list_label(trim($res['title'])))."', false, false);\n";
      }
    ++$i;
    }

  ?>

  <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

   // React to selection of an issue type.  This loads the associated
   // shortcuts into the selection list of titles, and determines which
   // rows are displayed or hidden.
   // Need to work on this to display "non-openEMR" issue types like POH/FH/ROS
   function newtype(index) {
    var f = document.forms[0];
    var theopts = f.form_titles.options;
    theopts.length = 0;
    var i = 0;
    for (i = 0; i < aopts[index].length; ++i) {
     theopts[i] = aopts[index][i];
    }
    document.getElementById('row_titles').style.display = i ? '' : 'none';
    // Show or hide various rows depending on issue type, except do not
    // hide the comments or referred-by fields if they have data.
    var comdisp = (aitypes[index] == 1) ? 'none' : '';
    var revdisp = (aitypes[index] == 1) ? '' : 'none';
    var injdisp = (aitypes[index] == 2) ? '' : 'none';
    var nordisp = (aitypes[index] == 0) ? '' : 'none';
    var surgdisp = (aitypes[index] != 4) ? 'none' : 'none';
    // reaction row should be displayed only for medication allergy.
    var alldisp =  (index == <?php echo issueTypeIndex('allergy'); ?>) ? '' : 'none';
   
    document.getElementById('row_enddate'       ).style.display = comdisp;
    // Note that by default all the issues will not show the active row
    //  (which is desired functionality, since then use the end date
    //   to inactivate the item.)
    document.getElementById('row_active'        ).style.display = revdisp;
    document.getElementById('row_diagnosis'     ).style.display = comdisp;
    document.getElementById('row_occurrence'    ).style.display = comdisp;
    document.getElementById('row_classification').style.display = injdisp;
    document.getElementById('row_reinjury_id'   ).style.display = injdisp;
    document.getElementById('row_reaction'      ).style.display = alldisp;
    // document.getElementById('row_referredby'   ).style.display = comdisp;
    //always hide referred by? was: = (f.form_referredby.value) ? '' : comdisp;
    document.getElementById('row_comments'      ).style.display = (f.form_comments.value  ) ? '' : revdisp;
    <?php if ($GLOBALS['athletic_team']) { ?>
    document.getElementById('row_returndate' ).style.display = comdisp;
    document.getElementById('row_injury_grade'  ).style.display = injdisp;
    document.getElementById('row_injury_part'   ).style.display = injdisp;
    document.getElementById('row_injury_type'   ).style.display = injdisp;
    document.getElementById('row_medical_system').style.display = nordisp;
    document.getElementById('row_medical_type'  ).style.display = nordisp;
    // Change label text of 'title' row depending on issue type:
    document.getElementById('title_diagnosis').innerHTML = '<b>' +
     (index == <?php echo issueTypeIndex('allergy'); ?> ?
     '<?php echo xla('Allergy') ?>' :
     (index == <?php echo issueTypeIndex('general'); ?> ?
     '<?php echo xla('Title') ?>' :
     '<?php echo xla('Text Diagnosis') ?>')) +
     ':</b>';
  <?php } else { ?>
   document.getElementById('row_referredby'    ).style.display = (f.form_referredby.value) ? 'comdisp' : comdisp;
  <?php } ?>
  <?php
    if ($ISSUE_TYPES['football_injury']) {
      // Generate more of these for football injury fields.
      issue_football_injury_newtype();
    }
    if ($ISSUE_TYPES['ippf_gcac'] && !$_REQUEST['form_save']) {
      // Generate more of these for gcac and contraceptive fields.
      if (empty($issue) || $irow['type'] == 'ippf_gcac'    ) issue_ippf_gcac_newtype();
      if (empty($issue) || $irow['type'] == 'contraceptive') issue_ippf_con_newtype();
    }
  ?>
   }

   // If a clickoption title is selected, copy it to the title field.
   function set_text() {
    var f = document.forms[0];
    f.form_title.value = f.form_titles.options[f.form_titles.selectedIndex].text;
    f.form_titles.selectedIndex = -1;
   }
  function refreshIssue(issue, title) {
   parent.refreshIssues;
   top.refreshIssues;
  }
  function submit_this_form() {
      var url = "../../forms/eye_mag/a_issue.php?form_save=1";
      var formData = $("form#theform").serialize();
      $.ajax({
             type   : 'POST',   // define the type of HTTP verb we want to use (POST for our form)
             url    : url,      // the url where we want to POST
             data   : formData, // our data object
             success  : function(result)  {
              $("#page").html(result);
            }
          }).done(function (){
            refreshIssues();
          });
  }
   // Process click on Delete link.
  function deleteme() {
      var url = "../../forms/eye_mag/a_issue.php?issue=<?php echo attr($issue); ?>&delete=1";
      var formData = $("form#theform").serialize();
     $.ajax({
             type    : 'POST',   // define the type of HTTP verb we want to use (POST for our form)
             data    : { 
                          issue  : '<?php echo attr($issue) ?>',
                          delete : '1'
                        },
              url    : url,      // the url where we want to POST
             success  : function(result)  {
             // alert("YUP!");
            }
             }).done(function (){
              //CLEAR THE FORM TOO...
              refreshIssues();
              document.forms['theform'].reset();
            
             });
  }
   // Called by the deleteme.php window on a successful delete.
   function imdeleted() {
    closeme();
   }

   function closeme() {
      if (parent.$) parent.$.fancybox.close();
      window.close();
   }

   // Called when the Active checkbox is clicked.  For consistency we
   // use the existence of an end date to indicate inactivity, even
   // though the simple verion of the form does not show an end date.
   function activeClicked(cb) {
    var f = document.forms[0];
    if (cb.checked) {
     f.form_end.value = '';
    } else {
     var today = new Date();
     f.form_end.value = '' + (today.getYear() + 1900) + '-' +
      (today.getMonth() + 1) + '-' + today.getDate();
    }
   }

   // Called when resolved outcome is chosen and the end date is entered.
   function outcomeClicked(cb) {
    var f = document.forms[0];
    if (cb.value == '1'){
     var today = new Date();
     f.form_end.value = '' + (today.getYear() + 1900) + '-' +
      ("0" + (today.getMonth() + 1)).slice(-2) + '-' + ("0" + today.getDate()).slice(-2);
     f.form_end.focus();
    }
   }

  // This is for callback by the find-code popup.
  // Appends to or erases the current list of diagnoses.
  function set_related(codetype, code, selector, codedesc) {
   var f = document.forms[0];
   var s = f.form_diagnosis.value;
   var title = f.form_title.value;
   if (code) {
    if (s.length > 0) s += ';';
    s += codetype + ':' + code;
   } else {
    s = '';
   }
   f.form_diagnosis.value = s;
   if(title == '') f.form_title.value = codedesc;
  }

  // This invokes the find-code popup.
  function sel_diagnosis() {
    <?php
    if($irow['type'] == 'medical_problem')
    {
    ?>
   dlgopen('../../patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("medical_problem","csv")) ?>', '_blank', 500, 400);
    <?php
    }
    else{
    ?>
    dlgopen('../../patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("diagnosis","csv")) ?>', '_blank', 500, 400);
    <?php
    }
    ?>
  }

  // Check for errors when the form is submitted.
  function validate() {
   var f = document.forms[0];
   if(f.form_begin.value > f.form_end.value && (f.form_end.value)) {
    alert("<?php echo addslashes(xl('Please Enter End Date greater than Begin Date!')); ?>");
    return false;
   }
   if (! f.form_title.value) {
    alert("<?php echo addslashes(xl('Please enter a title!')); ?>");
    return false;
   }
   top.restoreSession();
   return true;
  }

  // Supports customizable forms (currently just for IPPF).
  function divclick(cb, divid) {
   var divstyle = document.getElementById(divid).style;
   if (cb.checked) {
    divstyle.display = 'block';
   } else {
    divstyle.display = 'none';
   }
   return true;
  }

  </script>

  </head>

  <body  style="padding-right:0.5em;font-family: FontAwesome,serif,Arial;">
  <input type="hidden" name="form_id" id="form_id" value = "$form_id">
  <div id="page" name="page">
      <form method='post' name='theform' id='theform'
       action='a_issue.php?issue=<?php echo attr($issue); ?>&thispid=<?php echo attr($thispid); ?>&thisenc=<?php echo attr($thisenc); ?>'
       onsubmit='return validate();'>
       <input type="hiden" name="id" id="id" value="<?php echo attr($id); ?>">
          <?php
           $index = 0;
           $output ='';
          global $counter_header;
          $count_header='0';
          $output= array();
          foreach ($ISSUE_TYPES as $value => $focustitles) {
            
              /* if ($issue || $thistype) {
              if ($index == $type_index) {
                $disptype = xlt($focustitles[0]);
                echo '<b style="padding-bottom:5px;">'.$disptype.':</b>';
                echo "<input type='hidden' name='form_type' value='".xla($index)."'>\n";
                $checked = "checked='checked'";
              }
              } else { */
              //$output .= " <span style='padding-bottom:5px;font-size:0.8em;'><input type='radio' name='form_type' id='".xla($index)."' value='".xla($index)."' onclick='top.restoreSession();newtype($index);'";
              $checked = '';
              if ($issue || $thistype) {
                if ($index == $type_index) { $checked .= " checked='checked' ";}
              } else if ($focustitles[1] == "Problem") {
                $checked .= " checked='checked' "; 
              }

              if ($focustitles[1] == "Medication") $focustitles[1] = "Meds";
              if ($focustitles[1] == "Problem") $focustitles[1] = "PMH";
              if ($focustitles[1] == "Surgery") $focustitles[1] = "PSurgH";
  //echo $focustitles[1]. " - ";
              $HELLO[$focustitles[1]] = "<input type='radio' name='form_type' id='".xla($index)."' value='".xla($index)."' ".$checked. " onclick='top.restoreSession();newtype($index);' /><span style='Xpadding-bottom:2px;font-size:0.8em;font-weight:bold;'><label for='".xla($index)."' class='input-helper input-helper--checkbox'>" . xlt($focustitles[1]) . "</label></span>&nbsp;\n";
                //}
              ++$index;
          }
    
          echo $HELLO['POH'].$HELLO['PMH'].$HELLO['PSurgH'].$HELLO['Meds'].$HELLO['Allergy'].$HELLO['FH'].$HELLO['SocH'];

          ?>
         
      <div class="borderShadow" style="text-align:center;margin-top:7px;">
          <table  border='0' width='98%'>
            <tr id='row_titles'>
              <td valign='top' nowrap>&nbsp;</td>
              <td valign='top'>
                <select name='form_titles' size='<?php echo $GLOBALS['athletic_team'] ? 10 : 4; ?>' onchange='set_text()'>
                </select> <?php echo xlt('(Select one of these, or type in your own)'); ?>
              </td>
            </tr>

          <tr>
            <td valign='top' id='title_diagnosis' nowrap><b><?php echo $GLOBALS['athletic_team'] ? xlt('Text Diagnosis') : xlt('Title').$focustitle[1]; ?>:</b></td>
            <td>
              <input type='text' size='40' name='form_title' value='<?php echo xla($irow['title']) ?>' style='width:100%;text-align:left;' />
            </td>
          </tr>

          <tr id='row_diagnosis'>
            <td valign='top' nowrap><b><?php echo xlt('Diagnosis Code'); ?>:</b></td>
            <td>
              <input type='text' size='50' name='form_diagnosis'
                value='<?php echo attr($irow['diagnosis']) ?>' onclick='top.restoreSession();sel_diagnosis();'
                title='<?php echo xla('Click to select or change diagnoses'); ?>'
                style='width:100%' />
            </td>
          </tr>


          <tr>
            <td valign='top' nowrap><b><?php echo xlt('Begin Date'); ?>:</b></td>
            <td>

             <input type='text' size='10' name='form_begin' id='form_begin'
              value="<?php echo attr($irow['begdate']) ?>"
              style="width: 75px;"
              onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
              title='<?php echo xla('yyyy-mm-dd date of onset, surgery or start of medication'); ?>' />
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
              id='img_begin' border='0' alt='[?]' style='cursor:pointer'
              title='<?php echo xla('Click here to choose a date'); ?>' />
            </td>
          </tr>

          <tr id='row_enddate'>
            <td valign='top' nowrap><b><?php echo xlt('End Date'); ?>:</b></td>
            <td>
             <input type='text' size='10' name='form_end' id='form_end'
              style="width: 75px;"
              value='<?php echo attr($irow['enddate']) ?>'
              onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
              title='<?php echo xla('yyyy-mm-dd date of recovery or end of medication'); ?>' />
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
              id='img_end' border='0' alt='[?]' style='cursor:pointer'
              title='<?php echo xla('Click here to choose a date'); ?>' />
              &nbsp;(<?php echo xlt('leave blank if still active'); ?>)
            </td>
           </tr>

           <tr id='row_active'>
            <td valign='top' nowrap><b><?php echo xlt('Active'); ?>:</b></td>
            <td>
             <input type='checkbox' name='form_active' value='1' <?php echo attr($irow['enddate']) ? "" : "checked"; ?>
              onclick='top.restoreSession();activeClicked(this);'
              title='<?php echo xla('Indicates if this issue is currently active'); ?>' />
            </td>
           </tr>

           <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_returndate'>
            <td valign='top' nowrap><b><?php echo xlt('Returned to Play'); ?>:</b></td>
            <td>
             <input type='text' size='10' name='form_return' id='form_return'
              value='<?php echo attr($irow['returndate']) ?>'
              onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
              title='<?php echo xla('yyyy-mm-dd date returned to play'); ?>' />
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
              id='img_return' border='0' alt='[?]' style='cursor:pointer'
              title='<?php echo xla('Click here to choose a date'); ?>' />
              &nbsp;(<?php echo xlt('leave blank if still active'); ?>)
            </td>
           </tr>

           <tr id='row_occurrence'>
            <td valign='top' nowrap><b><?php echo xlt('Occurrence'); ?>:</b></td>
            <td>
             <?php
              // Modified 6/2009 by BM to incorporate the occurrence items into the list_options listings
              generate_form_field(array('data_type'=>1,'field_id'=>'occur','list_id'=>'occurrence','empty_title'=>'SKIP'), $irow['occurrence']);
             ?>
            </td>
           </tr>

           <tr id='row_classification'>
            <td valign='top' nowrap><b><?php echo xlt('Classification'); ?>:</b></td>
            <td>
             <select name='form_classification'>
            <?php
           foreach ($ISSUE_CLASSIFICATIONS as $key => $value) {
            echo "   <option value='".attr($key)."'";
            if ($key == $irow['classification']) echo " selected";
            echo ">".text($value)."\n";
           }
            ?>
             </select>
            </td>
          </tr>

           <tr<?php if (! $GLOBALS['athletic_team']) echo " style='display:none;'"; ?> id='row_reinjury_id'>
              <td valign='top' nowrap><b><?php echo xlt('Re-Injury?'); ?>:</b></td>
              <td>
               <select name='form_reinjury_id'>
                <option value='0'><?php echo xlt('No'); ?></option>
              <?php
              $pres = sqlStatement(
               "SELECT id, begdate, title " .
               "FROM lists WHERE " .
               "pid = ? AND " .
               "type = 'football_injury' AND " .
               "activity = 1 " .
               "ORDER BY begdate DESC", array($thispid)
              );
              while ($prow = sqlFetchArray($pres)) {
                echo "   <option value='" . attr($prow['id']) . "'";
                if ($prow['id'] == $irow['reinjury_id']) echo " selected";
                echo ">" . text($prow['begdate']) . " " . text($prow['title']) . "\n";
              }
              ?>
             </select>
            </td>
          </tr>
           <!-- Reaction For Medication Allergy -->
          <tr id='row_reaction'>
             <td valign='top' nowrap><b><?php echo xlt('Reaction'); ?>:</b></td>
             <td>
              <input type='text' size='40' name='form_reaction' value='<?php echo attr($irow['reaction']) ?>'
               style='width:100%' title='<?php echo xla('Allergy Reaction'); ?>' />
             </td>
          </tr>
          <!-- End of reaction -->
            <!--  

            <?php 
            /*
             *  The referred by inputs need ony be shown for certain fields.  They do not show up (or fit) in Allergies.
             *
             */

             ?>
                -->      <tr

                     <?php 
                     if (!$GLOBALS['athletic_team']) echo " style='display:none;'"; 


                     ?> id='row_referredby'>
                      <td valign='top' nowrap><b><?php echo xlt('Referred by'); ?>:</b></td>
                      <td>
                       <input type='text' size='40' name='form_referredby' value='<?php echo attr($irow['referredby']) ?>'
                        style='width:100%' title='<?php echo xla('Referring physician and practice'); ?>' />
                      </td>
                     </tr>
           
          <tr id='row_comments'>
            <td valign='top' nowrap><b><?php echo xlt('Comments'); ?>:</b></td>
            <td>
             <textarea name='form_comments' rows='1' cols='40' wrap='virtual' style='width:100%'><?php echo text($irow['comments']) ?></textarea>
            </td>
          </tr>

          <tr<?php if ($GLOBALS['athletic_team'] || $GLOBALS['ippf_specific']) echo " style='display:none;'"; ?>>
            <td valign='top' nowrap><b><?php echo xlt('Outcome'); ?>:</b></td>
            <td>
             <?php
              echo generate_select_list('form_outcome', 'outcome', $irow['outcome'], '', '', '', 'outcomeClicked(this);');
             ?>
            </td>
          </tr>

          <tr<?php if (!$GLOBALS['athletic_team'] || $GLOBALS['ippf_specific']) echo " style='display:none;'"; ?>>
            <td valign='top' nowrap><b><?php echo xlt('Destination'); ?>:</b></td>
            <td>
            <?php if (true) { ?>
             <input type='text' size='40' name='form_destination' value='<?php echo attr($irow['destination']) ?>'
              style='width:100%' title='GP, Secondary care specialist, etc.' />
            <?php } else { // leave this here for now, please -- Rod ?>
             <?php echo rbinput('form_destination', '1', 'GP'                 , 'destination') ?>&nbsp;
             <?php echo rbinput('form_destination', '2', 'Secondary care spec', 'destination') ?>&nbsp;
             <?php echo rbinput('form_destination', '3', 'GP via physio'      , 'destination') ?>&nbsp;
             <?php echo rbinput('form_destination', '4', 'GP via podiatry'    , 'destination') ?>
            <?php } ?>
            </td>
          </tr>

        </table>
      </div>

      <center>
      <p style="margin-top:7px;">

      <input type='button' id='form_save' name='form_save' onclick='top.restoreSession();submit_this_form();' value='<?php echo xla('Save'); ?>' />

      <?php if ($issue && acl_check('admin', 'super')) { ?>
      &nbsp;
      <input type='button' name='delete' onclick='top.restoreSession();deleteme();' value='<?php echo xla('Delete'); ?>' />
      <?php } ?>
  <!--
      &nbsp;
      <input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
  -->
      </p>
      </center>

      </form>
  <script language='JavaScript'>
   newtype(<?php echo $type_index ?>);
   Calendar.setup({inputField:"form_begin", ifFormat:"%Y-%m-%d", button:"img_begin"});
   Calendar.setup({inputField:"form_end", ifFormat:"%Y-%m-%d", button:"img_end"});
   Calendar.setup({inputField:"form_return", ifFormat:"%Y-%m-%d", button:"img_return"});
  </script>
  </div>
  </body>
  </html>
<?php 
return;
} 


return ;
?>