<?php

/**
 * forms/eye_mag/php/eye_mag_functions.php
 *
 * Functions which extend clinical forms
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2016- Raymond Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$form_folder = "eye_mag";
require_once(dirname(__FILE__) . "/../../../../custom/code_types.inc.php");
require_once(dirname(__FILE__) . "/../../../../library/options.inc.php");
global $PMSFH;

    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Services\FacilityService;

    $facilityService = new FacilityService();

/**
 *  This function returns HTML old record selector widget when needed (4 input values)
 *
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO, DRAW_PRIORS_$zone
 * @param string $visit_date Future functionality to limit result set. UTC DATE Formatted
 * @param string $pid value = patient id
 * @param string $type options text(default) image
 * @return string returns the HTML old record/image selector widget for the desired zone and type
 */
function priors_select($zone, $orig_id, $id_to_show, $pid, $type = 'text')
{
    global $priors;
    global $form_id;
    global $earlier;

    if ($type == "canvas") {
        $zone = $zone . "_canvas";
    }

    $tables = array('form_eye_hpi','form_eye_ros','form_eye_vitals',
                'form_eye_acuity','form_eye_refraction','form_eye_biometrics',
                'form_eye_external', 'form_eye_antseg','form_eye_postseg',
                'form_eye_neuro','form_eye_locking');
    $output_return = "<span id='" . attr($zone) . "_prefix_oldies' name='" . attr($zone) . "_prefix_oldies' class='oldies_prefix'>";
    $selected = '';
    $current = '';
    if (!$priors) { //we have to build it
        $query = "select form_encounter.date as encounter_date,form_eye_base.id as form_id, form_eye_base.*
                    from form_eye_base,forms,form_encounter
                    where
                    form_encounter.encounter = forms.encounter and
                    form_eye_base.id=forms.form_id and
                    forms.deleted != '1' and
                    forms.pid =form_eye_base.pid and
                    forms.formdir='eye_mag' and form_eye_base.pid=? ORDER BY encounter_date DESC LIMIT 20";
                    // Unlike the obj data(PMSFH,Clinical,IMPPLAN etc), this data is static.
                    // It only needs to be passed once to the client side.
        $result     = sqlStatement($query, array($pid));
        $counter    = sqlNumRows($result);
        $priors     = array();
        if ($counter < 2) {
            return;
        }

        $i = "0";
        while ($prior = sqlFetchArray($result)) {
            $dated = new DateTime($prior['encounter_date']);
            $dated = $dated->format('Y-m-d');
            $oeexam_date = oeFormatShortDate($dated);
            foreach ($tables as $table) {
                $sql = "SELECT * from " . $table . " WHERE id=?";
                $sub_data = sqlStatement($sql, array($prior['id']));
                $data = sqlFetchArray($sub_data);
                if ($data) {
                    $prior = array_merge($prior, $data);
                }
            }
            $priors[$i] = $prior;
            $priors[$i]['encounter_date'] = $oeexam_date;

            if (($i > 0) && ($prior['PLAN'])) {
                //this plan is a todo list for next visit, which is $i-1 actually
                $j = $i - 1;
                $priors[$j]['TODO'] = array();
                $priors[$j]['TODO'] = $prior['PLAN'];
            }

            $selected = '';
            $priors[$i]['visit_date'] = $oeexam_date;
            $priors[$i]['exam_date'] = $oeexam_date;
            if ($id_to_show == $prior['form_id']) {
                $selected = 'selected="selected"';
                $current = $i;
            }

            $output .= "<option value='" . attr($prior['id']) . "' " . attr($selected) . ">" . text($oeexam_date) . "</option>";
            $selected = '';
            $i++;
        }
    } else {
        //priors[] exists, containing the visits data AND the priors[earlier] field at the end, so iterate through all but the last one.
        //$visit_count = count($priors) - 1;
        for ($i = 0; $i < count($priors); $i++) {
            if ($form_id == $priors[$i]['id']) {
                $selected = 'selected=selected';
                $current = $i;
            } else {
                $selected = '';
            }

            $output .= "<option value='" . attr($priors[$i]['id']) . "' " . attr($selected) . ">" . text($priors[$i]['exam_date']) . "</option>";
        }
    }

    $i--;
    if ($current < $i) {
        $earlier = $current + 1;
    } else {
        $earlier = $current;
    }

    if ($current > '0') {
        $later   = ($current - 1);
    } else {
        $later   = "0";
    }


    //current visit =[0]
    if (!$priors[$current]['PLAN']) {
        $priors[$current]['PLAN'] = array();
        $query = "SELECT * from form_eye_mag_orders where form_id=?";
        $orders = sqlStatement($query, array($priors[$earlier]['id']));
        while ($row = sqlFetchArray($orders)) {
            $priors[$current]["PLAN"][] = $row;
            $priors[$later]["TODO"][] = $row;
        }
    }
    if ($id_to_show != $orig_id) {
        $output_return .= '
                <span title="' . xla($zone) . ': ' . xla("Copy these values into current visit.") . '
                ' . xla("Updated fields will be purple.") . '"

                    id="COPY_' . attr($zone) . '"
                    name="COPY_' . attr($zone) . '"
                    value="' . attr($id_to_show) . '" onclick=\'$("#COPY_SECTION").val("' . attr($zone) . '-' . attr($id_to_show) . '").trigger("change");\'>
                    <i class="fa fa-paste fa-lg"></i>
                </span>
                &nbsp;&nbsp;';
    }

    $output_return .= '
        <span onclick=\'$("#PRIOR_' . attr($zone) . '").val("' . attr($priors[$i][id]) . '").trigger("change");\'
                id="PRIORS_' . attr($zone) . '_earliest"
                name="PRIORS_' . attr($zone) . '_earliest"
                class="fa fa-fast-backward fa-sm PRIORS"
                title="' . attr($zone) . ': ' . attr($priors[$i]['encounter_date']) . '">
        </span>
        &nbsp;
        <span onclick=\'$("#PRIOR_' . attr($zone) . '").val("' . attr($priors[$earlier][id]) . '").trigger("change");\'
                id="PRIORS_' . attr($zone) . '_minus_one"
                name="PRIORS_' . attr($zone) . '_minus_one"
                class="fa fa-step-backward fa-sm PRIORS"
                title="' . attr($zone) . ': ' . attr($priors[$earlier]['encounter_date']) . '">
        </span>&nbsp;&nbsp;
        <select name="PRIOR_' . attr($zone) . '"
                id="PRIOR_' . attr($zone) . '"
                style="padding:0 0;font-size:1.2em;"
                class="PRIORS">
                ' . $output . '
        </select>
                  &nbsp;
        <span onclick=\'$("#PRIOR_' . attr($zone) . '").val("' . attr($priors[$later][id]) . '").trigger("change");\'
                id="PRIORS_' . attr($zone) . '_plus_one"
                name="PRIORS_' . attr($zone) . '_plus_one"
                class="fa  fa-step-forward PRIORS"
                title="' . attr($zone) . ': ' . attr($priors[$later]['encounter_date']) . '">
        </span>&nbsp;&nbsp;
        <span onclick=\'$("#PRIOR_' . attr($zone) . '").val("' . attr($priors[0][id]) . '").trigger("change");\'
                id="PRIORS_' . attr($zone) . '_latest"
                name="PRIORS_' . attr($zone) . '_latest"
                class="fa  fa-fast-forward PRIORS"
                title="' . attr($zone) . ': ' . attr($priors[0]['encounter_date']) . '"> &nbsp;
        </span>
    </span>';
    return $output_return;
}

/**
 *  This function returns ZONE specific HTML for a PRIOR record (3 input values)
 *
 *  This is where the magic of displaying the old records happens.
 *  Each section is a duplicate of the base html except the values are from a prior visit,
 *    the background and background-color are different, and the input fields are disabled.
 *
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO. DRAW_PRIORS_$zone and IMPPLAN to do.
 * @param string $visit_date. Future functionality to limit result set. UTC DATE Formatted
 * @param string $pid value = patient id
 * @return outputs the ZONE specific HTML for a prior record + "priors_select" widget for the desired zone
 */
function display_PRIOR_section($zone, $orig_id, $id_to_show, $pid, $report = '0')
{
    global $form_folder;

    $query  = "SELECT * FROM form_eye_mag_prefs
                where PEZONE='PREFS' AND id=?
                ORDER BY ZONE_ORDER,ordering";

    $result = sqlStatement($query, array($_SESSION['authUserID']));
    while ($prefs = sqlFetchArray($result)) {
        ${$prefs['LOCATION']} = $prefs['GOVALUE'];
    }

     $query = "SELECT  *
               from form_eye_base,
                form_eye_hpi,form_eye_ros,form_eye_vitals,
                form_eye_acuity,form_eye_refraction,form_eye_biometrics,
                form_eye_external, form_eye_antseg,form_eye_postseg,
                form_eye_neuro,form_eye_locking
              where
                    form_eye_base.id=form_eye_hpi.id and
                    form_eye_base.id=form_eye_ros.id and
                    form_eye_base.id=form_eye_vitals.id and
                    form_eye_base.id=form_eye_acuity.id and
                    form_eye_base.id=form_eye_refraction.id and
                    form_eye_base.id=form_eye_biometrics.id and
                    form_eye_base.id=form_eye_external.id and
                    form_eye_base.id=form_eye_antseg.id and
                    form_eye_base.id=form_eye_postseg.id and
                    form_eye_base.id=form_eye_neuro.id and
                    form_eye_base.id=form_eye_locking.id and
                    form_eye_base.pid =? and
                    form_eye_base.id=?";
    $result = sqlQuery($query, array($pid,$id_to_show));
    @extract($result);
    ob_start();
    if ($zone == "REFRACTIONS") {
        //TODO maybe just sql _refraction+acuity
        display_refractive_data($result);
    } elseif ($zone == "EXT") {
        if ($report == '0') {
            $output = priors_select($zone, $orig_id, $id_to_show, $pid);
        }
        ?>
        <input disabled type="hidden" id="PRIORS_<?php echo attr($zone); ?>_prefix" name="PRIORS_<?php echo attr($zone); ?>_prefix" value="">
        <span class="closeButton float-right fas fa-times" id="Close_PRIORS_<?php echo attr($zone); ?>" name="Close_PRIORS_<?php echo attr($zone); ?>"></span>
            <div name="prior_selector">
                    <?php
                    echo $output;//prior visit selector - already sanitized
                    ?>
            </div>
                <span style="font-weight:bold;">
                    <?php
                    if ($report == '0') {
                        echo xlt('Prior Exam');
                    } else {
                        echo xlt($zone);
                    } ?>: </span>
                <br />
                <div id="PRIORS_EXT_left_1">
                    <table>
                        <?php
                            list($imaging,$episode) = display($pid, $encounter, "EXT");
                            echo $episode;
                        ?>
                    </table>
                    <table>
                        <tr>
                            <td></td><td><?php echo xlt('R'); ?></td><td><?php echo xlt('L{{left}}'); ?></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('Lev Fn{{levator function}}'); ?></td>
                            <td><input disabled  type="text" size="1" name="PRIOR_RLF" id="PRIOR_RLF" value="<?php echo attr($RLF); ?>"></td>
                            <td><input disabled  type="text" size="1" name="PRIOR_LLF" id="PRIOR_LLF" value="<?php echo attr($LLF); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('MRD{{marginal reflex distance}}'); ?></td>
                            <td><input disabled type="text" size="1" name="PRIOR_RMRD" id="PRIOR_RMRD" value="<?php echo attr($RMRD); ?>"></td>
                            <td><input disabled type="text" size="1" name="PRIOR_LMRD" id="PRIOR_LMRD" value="<?php echo attr($LMRD); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('Vert Fissure{{vertical fissure height}}'); ?></td>
                            <td><input disabled type="text" size="1" name="PRIOR_RVFISSURE" id="PRIOR_RVFISSURE" value="<?php echo attr($RVFISSURE); ?>"></td>
                            <td><input disabled type="text" size="1" name="PRIOR_LVFISSURE" id="PRIOR_LVFISSURE" value="<?php echo attr($LVFISSURE); ?>"></td>
                        </tr>
                          <tr>
                            <td class="right"><?php echo xlt('Carotid Bruit'); ?></td>
                            <td><input  disabled type="text"  name="PRIOR_RCAROTID" id="PRIOR_RCAROTID" value="<?php echo attr($RCAROTID); ?>"></td>
                            <td><input  disabled type="text"  name="PRIOR_LCAROTID" id="PRIOR_LCAROTID" value="<?php echo attr($LCAROTID); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('Temporal Art.{{Temporal Artery}}'); ?></td>
                            <td><input  disabled type="text" size="1" name="PRIOR_RTEMPART" id="PRIOR_RTEMPART" value="<?php echo attr($RTEMPART); ?>"></td>
                            <td><input  disabled type="text" size="1" name="PRIOR_LTEMPART" id="PRIOR_LTEMPART" value="<?php echo attr($LTEMPART); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('CN V{{cranial nerve five}}'); ?></td>
                            <td><input  disabled type="text" size="1" name="PRIOR_RCNV" id="PRIOR_RCNV" value="<?php echo attr($RCNV); ?>"></td>
                            <td><input  disabled type="text" size="1" name="PRIOR_LCNV" id="PRIOR_LCNV" value="<?php echo attr($LCNV); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><?php echo xlt('CN VII{{cranial nerve seven}}'); ?></td>
                            <td><input disabled type="text" size="1" name="PRIOR_RCNVII" id="PRIOR_RCNVII" value="<?php echo attr($RCNVII); ?>"></td>
                            <td><input disabled type="text" size="1" name="PRIOR_LCNVII" id="PRIOR_LCNVII" value="<?php echo attr($LCNVII); ?>"></td>
                        </tr>
                        <tr><td colspan=3 class="underline"><?php echo xlt('Hertel Exophthalmometry'); ?></td></tr>
                        <tr class="center">
                            <td>
                                <input disabled type=text size=1 id="PRIOR_ODHERTEL" name="PRIOR_ODHERTEL" value="<?php echo attr($ODHERTEL); ?>">
                                <i class="fa fa-minus"></i>
                            </td>
                            <td>
                                <input disabled type=text size=3  id="PRIOR_HERTELBASE" name="PRIOR_HERTELBASE" value="<?php echo attr($HERTELBASE); ?>">
                                <i class="fa fa-minus"></i>
                            </td>
                            <td>
                                <input disabled type=text size=1  id="PRIOR_OSHERTEL" name="PRIOR_OSHERTEL" value="<?php echo attr($OSHERTEL); ?>">
                            </td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                    </table>
                </div>

            <?php ($EXT_VIEW == 1) ? ($display_EXT_view = "wide_textarea") : ($display_EXT_view = "narrow_textarea");?>
            <?php ($display_EXT_view == "wide_textarea") ? ($marker = "fa-minus-square-o") : ($marker = "fa-plus-square-o");?>
            <div id="PRIOR_EXT_text_list" name="PRIOR_EXT_text_list" class="borderShadow PRIORS <?php echo attr($display_EXT_view); ?>" >
                <span class="top_right fa <?php echo attr($marker); ?>" name="PRIOR_EXT_text_view" id="PRIOR_EXT_text_view"></span>
                <table cellspacing="0" cellpadding="0" >
                    <tr>
                        <th><?php echo xlt('Right'); ?></th><td style="width:100px;"></td><th><?php echo xlt('Left'); ?> </th>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_RBROW" id="PRIOR_RBROW" class="right EXT"><?php echo text($RBROW); ?></textarea></td>
                        <td class="ident"><?php echo xlt('Brow'); ?></td>
                        <td><textarea disabled name="PRIOR_LBROW" id="PRIOR_LBROW" class=""><?php echo text($LBROW); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_RUL" id="PRIOR_RUL" class="right"><?php echo text($RUL); ?></textarea></td>
                        <td class="ident"><?php echo xlt('Upper Lids'); ?></td>
                        <td><textarea disabled name="PRIOR_LUL" id="PRIOR_LUL" class=""><?php echo text($LUL); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_RLL" id="PRIOR_RLL" class="right"><?php echo text($RLL); ?></textarea></td>
                        <td class="ident"><?php echo xlt('Lower Lids'); ?></td>
                        <td><textarea disabled name="PRIOR_LLL" id="PRIOR_LLL" class=""><?php echo text($LLL); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_RMCT" id="PRIOR_RMCT" class="right"><?php echo text($RMCT); ?></textarea></td>
                        <td class="ident"><?php echo xlt('Medial Canthi'); ?></td>
                        <td><textarea disabled name="PRIOR_LMCT" id="PRIOR_LMCT" class=""><?php echo text($LMCT); ?></textarea></td>
                    </tr>
                     <tr>
                        <td><textarea disabled name="PRIOR_RADNEXA" id="PRIOR_RADNEXA" class="right"><?php echo text($RADNEXA); ?></textarea></td>
                        <td class="ident"><?php echo xlt('Adnexa'); ?></td>
                        <td><textarea disabled name="PRIOR_LADNEXA" id="PRIOR_LADNEXA" class=""><?php echo text($LADNEXA); ?></textarea></td>
                    </tr>
                </table>
            </div>
            <br />
            <div class="QP_lengthen">
                <span style="font-weight:bold;"><?php echo xlt('Comments'); ?>:</span>
                <br />
                <textarea disabled id="PRIOR_EXT_COMMENTS" name="PRIOR_EXT_COMMENTS"><?php echo text($EXT_COMMENTS); ?></textarea>
            </div>

            <?php
    } elseif ($zone == "ANTSEG") {
        if ($report == '0') {
            $output = priors_select($zone, $orig_id, $id_to_show, $pid);
        }
        ?>
        <input disabled type="hidden" id="PRIORS_<?php echo attr($zone); ?>_prefix" name="PRIORS_<?php echo attr($zone); ?>_prefix" value="">
        <span class="closeButton float-right fas fa-times" id="Close_PRIORS_<?php echo attr($zone); ?>" name="Close_PRIORS_<?php echo attr($zone); ?>"></span>
        <div name="prior_selector">
                <?php
                echo $output;
                ?>
        </div>

        <span style="font-weight:bold;"> <?php echo xlt('Prior Exam'); ?>:</span>
        <br />
        <div class="text_clinical" id="PRIORS_ANTSEG_left_1">
            <table>
                <?php
                    list($imaging,$episode) = display($pid, $encounter, "ANTSEG");
                    echo $episode;
                ?>
            </table>
            <table>
                <tr >
                    <td></td><td><?php echo xlt('R{{right}}'); ?></td><td><?php echo xlt('L{{left}}'); ?></td>
                </tr>
                <tr>
                    <td class="right" ><?php echo xlt('Gonio{{Gonioscopy abbreviation}}'); ?></td>
                    <td><input disabled  type="text" name="PRIOR_ODGONIO" id="PRIOR_ODGONIO" value="<?php echo attr($ODGONIO); ?>"></td>
                    <td><input disabled  type="text" name="PRIOR_OSGONIO" id="PRIOR_OSGONIO" value="<?php echo attr($OSGONIO); ?>"></td>
                </tr>
                <tr>
                    <td class="right" ><?php echo xlt('Pachymetry'); ?></td>
                    <td><input disabled type="text" name="PRIOR_ODKTHICKNESS" id="PRIOR_ODKTHICKNESS" value="<?php echo attr($ODKTHICKNESS); ?>"></td>
                    <td><input disabled type="text" name="PRIOR_OSKTHICKNESS" id="PRIOR_OSKTHICKNESS" value="<?php echo attr($OSKTHICKNESS); ?>"></td>
                </tr>
                <tr>
                    <td class="right" title="<?php echo xla('Schirmers I (w/o anesthesia)'); ?>"><?php echo xlt('Schirmer I'); ?></td>
                    <td><input disabled type="text" name="PRIOR_ODSCHIRMER1" id="PRIOR_ODSCHIRMER1" value="<?php echo attr($ODSCHIRMER1); ?>"></td>
                    <td><input disabled type="text" name="PRIOR_OSSCHRIMER2" id="PRIOR_OSSCHIRMER1" value="<?php echo attr($OSSCHIRMER1); ?>"></td>
                </tr>
                <tr>
                    <td class="right" title="<?php echo xla('Schirmers II (w/ anesthesia)'); ?>"><?php echo xlt('Schirmer II'); ?></td>
                    <td><input disabled type="text" name="PRIOR_ODSCHIRMER2" id="PRIOR_ODSCHIRMER2" value="<?php echo attr($ODSCHIRMER2); ?>"></td>
                    <td><input disabled type="text" name="PRIOR_OSSCHRIMER2" id="PRIOR_OSSCHIRMER2" value="<?php echo attr($OSSCHIRMER2); ?>"></td>
                </tr>
                <tr>
                    <td class="right" title="<?php echo xla('Tear Break Up Time'); ?>"><?php echo xlt('TBUT{{tear breakup time}}'); ?></td>
                    <td><input disabled type="text" name="PRIOR_ODTBUT" id="PRIOR_ODTBUT" value="<?php echo attr($ODTBUT); ?>"></td>
                    <td><input disabled type="text" name="PRIOR_OSTBUT" id="PRIOR_OSTBUT" value="<?php echo attr($OSTBUT); ?>"></td>
                </tr>
                <tr>
                  <td colspan="3" rowspan="4" id="PRIORS_dil_box" nowrap="">
                    <br />
                    <?php
                    // This is going to be based off a list in the near future
                    // to allow for end-user customization
                    ?>
                    <span id="PRIORS_dil_listbox_title"><?php echo xlt('Dilation'); ?>:</span>
                      <span id="PRIORS_dil_meds" class="float-right"><?php
                        if ($DIL_MEDS) {
                            echo text($DIL_MEDS);
                        }
                        ?></span>
                    <br />
                    <table id="PRIORS_dil_listbox">
                      <tr>
                        <td>
                            <input disabled type="checkbox" class="dil_drug" id="PRIORS_CycloMydril" name="PRIORS_CYCLOMYDRIL" value="Cyclomydril" <?php
                            if ($CYCLOMYDRIL == 'Cyclomydril') {
                                echo "checked='checked'";
                            } ?> />
                            <label for="CycloMydril" class="input-helper input-helper--checkbox"><?php echo text('CycloMydril'); ?></label>
                        </td>
                        <td>
                            <input disabled type="checkbox" class="dil_drug" id="PRIORS_Tropicamide" name="PRIORS_TROPICAMIDE" value="Tropicamide 2.5%" <?php
                            if ($TROPICAMIDE == 'Tropicamide 2.5%') {
                                echo "checked='checked'";
                            } ?> />
                            <label for="Tropicamide" class="input-helper input-helper--checkbox"><?php echo text('Tropic 2.5%'); ?></label>
                        </td>
                      </tr>
                      <tr>
                        <td>
                            <input disabled type="checkbox" class="dil_drug" id="PRIORS_Neo25" name="PRIORS_NEO25" value="Neosynephrine 2.5%"  <?php
                            if ($NEO25 == 'Neosynephrine 2.5%') {
                                echo "checked='checked'";
                            } ?> />
                            <label for="Neo25" class="input-helper input-helper--checkbox"><?php echo text('Neo 2.5%'); ?></label>
                        </td>
                        <td>
                            <input disabled type="checkbox" class="dil_drug" id="PRIORS_Neo10" name="PRIORS_NEO10" value="Neosynephrine 10%"  <?php
                            if ($NEO10 == 'Neosynephrine 10%') {
                                echo "checked='checked'";
                            } ?> />
                            <label for="Neo10" class="input-helper input-helper--checkbox"><?php echo text('Neo 10%'); ?></label>
                        </td>
                      </tr>
                      <tr>
                        <td>
                            <input disabled type="checkbox" class="dil_drug" id="PRIORS_Cyclogyl" style="left:150px;" name="PRIORS_CYCLOGYL" value="Cyclopentolate 1%"  <?php
                            if ($CYCLOGYL == 'Cyclopentolate 1%') {
                                echo "checked='checked'";
                            } ?> />
                            <label for="Cyclogyl" class="input-helper input-helper--checkbox"><?php echo text('Cyclo 1%'); ?></label>
                        </td>
                        <td>
                            <input disabled type="checkbox" class="dil_drug" id="PRIORS_Atropine" name="PRIORS_ATROPINE" value="Atropine 1%"  <?php
                            if ($ATROPINE == 'Atropine 1%') {
                                echo "checked='checked'";
                            } ?> />
                            <label for="Atropine" class="input-helper input-helper--checkbox"><?php echo text('Atropine 1%'); ?></label>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
            </table>
        </div>
        <?php ($ANTSEG_VIEW == '1') ? ($display_ANTSEG_view = "wide_textarea") : ($display_ANTSEG_view = "narrow_textarea");?>
        <?php ($display_ANTSEG_view == "wide_textarea") ? ($marker = "fa-minus-square-o") : ($marker = "fa-plus-square-o");?>
        <div id="PRIOR_ANTSEG_text_list"  name="PRIOR_ANTSEG_text_list" class="borderShadow PRIORS <?php echo attr($display_ANTSEG_view); ?>" >
                <span class="top_right fa <?php echo attr($marker); ?>" name="PRIOR_ANTSEG_text_view" id="PRIOR_ANTSEG_text_view"></span>
                <table>
                    <tr>
                        <th><?php echo xlt('OD{{right eye}}'); ?></th><th></th><th><?php echo xlt('OS{{left eye}}'); ?></th></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_ODCONJ" id="PRIOR_ODCONJ" class="right"><?php echo text($ODCONJ); ?></textarea></td>
                        <td class="ident"><?php echo xlt('Conj{{Conjunctiva}}'); ?> / <?php echo xlt('Sclera'); ?></td>
                        <td><textarea disabled name="PRIOR_OSCONJ" id="PRIOR_OSCONJ" class=""><?php echo text($OSCONJ); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_ODCORNEA" id="PRIOR_ODCORNEA" class="right"><?php echo text($ODCORNEA); ?></textarea></td>
                        <td class="ident"><?php echo xlt('Cornea'); ?></td>
                        <td><textarea disabled name="PRIOR_OSCORNEA" id="PRIOR_OSCORNEA" class=""><?php echo text($OSCORNEA); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_ODAC" id="PRIOR_ODAC" class="right"><?php echo text($ODAC); ?></textarea></td>
                        <td class="ident"><?php echo xlt('A/C{{anterior chamber}}'); ?></td>
                        <td><textarea disabled name="PRIOR_OSAC" id="PRIOR_OSAC" class=""><?php echo text($OSAC); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_ODLENS" id="PRIOR_ODLENS" class=" right"><?php echo text($ODLENS); ?></textarea></td>
                        <td class="ident" ><?php echo xlt('Lens'); ?></td>
                        <td><textarea disabled name="PRIOR_OSLENS" id="PRIOR_OSLENS" class=""><?php echo text($OSLENS); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><textarea disabled name="PRIOR_ODIRIS" id="PRIOR_ODIRIS" class="right"><?php echo text($ODIRIS); ?></textarea></td>
                        <td class="ident"><?php echo xlt('Iris'); ?></td>
                        <td><textarea disabled name="PRIOR_OSIRIS" id="PRIOR_OSIRIS" class=""><?php echo text($OSIRIS); ?></textarea></td>
                    </tr>
                </table>
        </div>
        <br />
        <div class="QP_lengthen">
            <span style="font-weight:bold;"><?php echo xlt('Comments'); ?>:</span>
            <br />
            <textarea disabled id="PRIOR_ANTSEG_COMMENTS" name="PRIOR_ANTSEG_COMMENTS"><?php echo text($ANTSEG_COMMENTS); ?></textarea>
        </div>

        <?php
    } elseif ($zone == "RETINA") {
        if ($report == '0') {
            $output = priors_select($zone, $orig_id, $id_to_show, $pid);
        }
        ?>
        <input disabled type="hidden" id="PRIORS_<?php echo attr($zone); ?>_prefix" name="PRIORS_<?php echo attr($zone); ?>_prefix" value="">
        <span class="closeButton float-right fas fa-times" id="Close_PRIORS_<?php echo attr($zone); ?>" name="Close_PRIORS_<?php echo attr($zone); ?>"></span>
        <div name="prior_selector">
                <?php
                echo $output;
                ?>
        </div>
        <span style="font-weight:bold;"><?php echo xlt('Prior Exam'); ?>:</span>
        <br />
        <div id="PRIORS_RETINA_left_1" class="text_clinical">
            <table>
                <?php
                list($imaging,$episode) = display($pid, $encounter, "POSTSEG");
                echo $episode;
                ?>
            </table>
            <br />
            <table>
                <tr class="bold">
                    <td></td>
                    <td><?php echo xlt('OD{{right eye}}'); ?> </td><td><?php echo xlt('OS{{left eye}}'); ?> </td>
                </tr>
                <tr>
                    <td class="bold right"><?php echo xlt('C/D Ratio{{cup to disc ration}}'); ?>:</td>
                    <td>
                        <input type="text" disabled name="PRIOR_ODCUP" size="4" id="PRIOR_ODCUP" value="<?php echo attr($ODCUP); ?>">
                    </td>
                    <td>
                        <input type="text" disabled name="PRIOR_OSCUP" size="4" id="PRIOR_OSCUP" value="<?php echo attr($OSCUP); ?>">
                    </td>
                </tr>

                <tr>
                    <td class="bold right">
                        <?php echo xlt('CMT{{Central Macular Thickness}}'); ?>:</td>
                    <td>
                        <input type="text" disabled name="PRIOR_ODCMT" size="4" id="PRIOR_ODCMT" value="<?php echo attr($ODCMT); ?>">
                    </td>
                    <td>
                        <input type="text" disabled name="PRIOR_OSCMT" size="4" id="PRIOR_OSCMT" value="<?php echo attr($OSCMT); ?>">
                    </td>
                </tr>
            </table>
            <br />
            <table>
                <?php
                list($imaging,$episode) = display($pid, $encounter, "NEURO");
                echo $episode;
                ?>
            </table>
        </div>

        <?php ($RETINA_VIEW == 1) ? ($display_RETINA_view = "wide_textarea") : ($display_RETINA_view = "narrow_textarea");?>
        <?php ($display_RETINA_view == "wide_textarea") ? ($marker = "fa-minus-square-o") : ($marker = "fa-plus-square-o");?>
        <div>
            <div id="PRIOR_RETINA_text_list" name="PRIOR_RETINA_text_list" class="borderShadow PRIORS <?php echo attr($display_RETINA_view); ?>">
                    <span class="top_right fa <?php echo attr($marker); ?>" name="PRIOR_RETINA_text_view" id="PRIOR_RETINA_text_view"></span>
                    <table cellspacing="0" cellpadding="0">
                        <tr>
                            <th><?php echo xlt('OD{{right eye}}'); ?></th><td style="width:100px;"></td><th><?php echo xlt('OS{{left eye}}'); ?></th></td>
                        </tr>
                        <tr>
                            <td><textarea disabled name="PRIOR_ODDISC" id="PRIOR_ODDISC" class="right"><?php echo text($ODDISC); ?></textarea></td>
                            <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Disc'); ?></td>
                            <td><textarea disabled name="PRIOR_OSDISC" id="PRIOR_OSDISC"><?php echo text($OSDISC); ?></textarea></td>
                        </tr>
                        <tr>
                            <td><textarea disabled name="ODMACULA" id="ODMACULA" class="right"><?php echo text($ODMACULA); ?></textarea></td>
                            <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Macula'); ?></td>
                            <td><textarea disabled name="PRIOR_OSMACULA" id="PRIOR_OSMACULA"><?php echo text($OSMACULA); ?></textarea></td>
                        </tr>
                        <tr>
                            <td><textarea disabled name="PRIOR_ODVESSELS" id="PRIOR_ODVESSELS" class="right"><?php echo text($ODVESSELS); ?></textarea></td>
                            <td style="text-align:center;font-size:0.9em;"><?php echo xlt('Vessels'); ?></td>
                            <td><textarea disabled name="PRIOR_OSVESSELS" id="PRIOR_OSVESSELS"><?php echo text($OSVESSELS); ?></textarea></td>
                        </tr>
                        <tr>
                            <td><textarea name="PRIOR_ODVITREOUS" id="PRIOR_ODVITREOUS" class="right"><?php echo text($ODVITREOUS); ?></textarea></td>
                            <td style="text-align:center;font-size:0.9em;" ><?php echo xlt('Vitreous'); ?></td>
                            <td><textarea name="PRIOR_OSVITREOUS" id="PRIOR_OSVITREOUS"><?php echo text($OSVITREOUS); ?></textarea></td>
                        </tr>
                        <tr>
                            <td><textarea disabled name="PRIOR_ODPERIPH" id="PRIOR_ODPERIPH" class="right"><?php echo text($ODPERIPH); ?></textarea></td>
                            <td style="text-align:center;font-size:0.9em;" class=""><?php echo xlt('Periph'); ?></td>
                            <td><textarea disabled name="PRIOR_OSPERIPH" id="PRIOR_OSPERIPH"><?php echo text($OSPERIPH); ?></textarea></td>
                        </tr>
                    </table>
            </div>
        </div>
        <br />
        <br />
        <div class="QP_lengthen">
            <SPAN style="font-weight:bold;"><?php echo xlt('Comments'); ?>:</SPAN>
            <br />
            <textarea disabled id="PRIOR_RETINA_COMMENTS" name="PRIOR_RETINA_COMMENTS" style="width:4.0in;height:3.0em;"><?php echo text($RETINA_COMMENTS); ?></textarea>
        </div>
        <?php
    } elseif ($zone == "NEURO") {
        if ($report == '0') {
            $output = priors_select($zone, $orig_id, $id_to_show, $pid);
        }
        ?>
        <input disabled type="hidden" id="PRIORS_<?php echo attr($zone); ?>_prefix" name="PRIORS_<?php echo attr($zone); ?>_prefix" value="">
        <span class="closeButton float-right fas fa-times" id="Close_PRIORS_<?php echo attr($zone); ?>" name="Close_PRIORS_<?php echo attr($zone); ?>"></span>
        <div name="prior_selector">
                <?php
                echo $output;
                ?>
        </div>
        <span style="font-weight:bold;"><?php echo xlt('Prior Exam'); ?>:</span>
        <br />
        <div style="float:left;margin-top:0.8em;font-size:0.8em;">
            <div id="PRIOR_NEURO_text_list" class="borderShadow PRIORS" style="border:1pt solid black;float:left;width:195px;padding:10px;text-align:center;margin:2 2;font-weight:bold;">
                <table style="font-size:1.0em;font-weight:600;">
                    <tr>
                        <td></td><td style="text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td><td style="text-align:center;"><?php echo xlt('OS{{left eye}}'); ?></td></tr>
                    <tr>
                        <td class="right">
                            <?php echo xlt('Color'); ?>:
                        </td>
                        <td>
                            <input disabled type="text" id="PRIOR_ODCOLOR" name="PRIOR_ODCOLOR" value="<?php
                            if ($ODCOLOR) {
                                echo  attr($ODCOLOR);
                            } else {
                                echo "   /   ";
                            } ?>"/>
                        </td>
                        <td>
                            <input disabled type="text" id="PRIOR_OSCOLOR" name="PRIOR_OSCOLOR" value="<?php
                            if ($OSCOLOR) {
                                echo  attr($OSCOLOR);
                            } else {
                                echo "   /   ";
                            } ?>"/>
                        </td>
                        <td style="text-align:bottom;">
                                               &nbsp;<span title="<?php echo xla('Insert normals - 11/11'); ?>" class="fa fa-reply"></span>
                                            </td>
                                        </tr>
                    <tr>
                        <td class="right" style="white-space: nowrap;font-size:0.9em;">
                            <span title="<?php echo xla('Variation in red color discrimination between the eyes (eg. OD=100, OS=75)'); ?>"><?php echo xlt('Red Desat{{red desaturation}}'); ?>:</span>
                        </td>
                        <td>
                            <input disabled type="text" size="6" name="PRIOR_ODREDDESAT" id="PRIOR_ODREDDESAT" value="<?php echo attr($ODREDDESAT); ?>"/>
                        </td>
                        <td>
                            <input disabled type="text" size="6" name="PRIOR_OSREDDESAT" id="PRIOR_OSREDDESAT" value="<?php echo attr($OSREDDESAT); ?>"/>
                        </td>
                        <td>&nbsp;
                            <span id="" class="fa fa-reply" name="" title="<?php echo xla('Insert normals - 100/100'); ?>"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="right" style="white-space: nowrap;font-size:0.9em;">
                            <span title="<?php echo xla('Variation in white (muscle) light brightness discrimination between the eyes (eg. OD=$1.00, OS=$0.75)'); ?>"><?php echo xlt('Coins'); ?>:</span>
                        </td>
                        <td>
                            <input disabled type="text" size="6" name="PRIOR_ODCOINS" id="PRIOR_ODCOINS" value="<?php echo attr($ODCOINS); ?>"/>
                        </td>
                        <td>
                            <input disabled type="text" size="6" name="PRIOR_OSCOINS" id="PRIOR_OSCOINS" value="<?php echo attr($OSCOINS); ?>"/>
                        </td>
                        <td>&nbsp;
                            <span id="" class="fa fa-reply" name="" title="<?php echo xla('Insert normals - 100/100'); ?>"></span>
                         </td>
                    </tr>
                </table>
            </div>
            <div class="borderShadow" style="position: relative;

float: right;

text-align: center;

width: 260px;

z-index: 1;

margin: 2px 0 2px 2px;">
                <span class="closeButton fa fa-th" id="PRIOR_Close_ACTMAIN" name="PRIOR_Close_ACTMAIN"></span>
                <table class="ACT_top bold">
                    <tr style="text-align:left;height:26px;vertical-align:middle;width:180px;">
                        <td >
                            <span id="PRIOR_ACTTRIGGER" name="PRIOR_ACTTRIGGER" style="text-decoration:underline;"><?php echo ('Alternate Cover Test'); ?>:</span>
                        </td>
                        <td>
                            <span id="PRIOR_ACTNORMAL_CHECK" name="PRIOR_ACTNORMAL_CHECK">
                            <label for="PRIOR_ACT" class="input-helper input-helper--checkbox"><?php echo xlt('Ortho'); ?></label>
                            <input disabled type="checkbox" name="PRIOR_ACT" id="PRIOR_ACT" checked="<?php
                            if ($ACT == '1') {
                                echo "checked";
                            } ?>"></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:center;">
                            <br />
                            <div id="PRIOR_ACTMAIN" name="PRIOR_ACTMAIN" class="ACT_TEXT nodisplay" style="position:relative;z-index:1;margin 10 auto;">
                               <table cellpadding="0" style="position:relative;margin: 7 5 19 5;">
                                    <tr>
                                        <td id="PRIOR_ACT_tab_SCDIST" name="PRIOR_ACT_tab_SCDIST" class="ACT_selected"> <?php echo xlt('scDist{{ACT without Correction Distance}}'); ?> </td>
                                        <td id="PRIOR_ACT_tab_CCDIST" name="PRIOR_ACT_tab_CCDIST" class="ACT_deselected"> <?php echo xlt('ccDist{{ACT with Correction Distance}}'); ?> </td>
                                        <td id="PRIOR_ACT_tab_SCNEAR" name="PRIOR_ACT_tab_SCNEAR" class="ACT_deselected"> <?php echo xlt('scNear{{ACT without Correction Near}}'); ?> </td>
                                        <td id="PRIOR_ACT_tab_CCNEAR" name="PRIOR_ACT_tab_CCNEAR" class="ACT_deselected"> <?php echo xlt('ccNear{{ACT with Correction Near}}'); ?> </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:center;font-size:0.8em;">
                                            <div id="PRIOR_ACT_SCDIST" name="PRIOR_ACT_SCDIST" class="ACT_box">
                                                <br />
                                                <table>
                                                    <tr>
                                                        <td class="text-center"><?php echo xlt('R{{right}}'); ?></td>
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT1SCDIST" name="PRIOR_ACT1SCDIST" class="ACT"><?php echo text($ACT1SCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT2SCDIST"  name="PRIOR_ACT2SCDIST"class="ACT"><?php echo text($ACT2SCDIST); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT3SCDIST"  name="PRIOR_ACT3SCDIST" class="ACT"><?php echo text($ACT3SCDIST); ?></textarea></td>
                                                        <td class="text-center"><?php echo xlt('L{{left}}'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center"><i class="fa fa-reply rotate-left"></i></td>
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT4SCDIST" name="PRIOR_ACT4SCDIST" class="ACT"><?php echo text($ACT4SCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT5SCDIST" name="PRIOR_ACT5SCDIST" class="ACT"><?php echo text($ACT5SCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6SCDIST" name="PRIOR_ACT6SCDIST" class="ACT"><?php echo text($ACT6SCDIST); ?></textarea></td>
                                                        <td class="text-center"><i class="fa fa-reply flip-left"></i></td>
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
                                                        <td class="text-center"><?php echo xlt('R{{right}}'); ?></td>
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT1CCDIST" name="PRIOR_ACT1CCDIST" class="ACT"><?php echo text($ACT1CCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT2CCDIST"  name="PRIOR_ACT2CCDIST"class="ACT"><?php echo text($ACT2CCDIST); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT3CCDIST"  name="PRIOR_ACT3CCDIST" class="ACT"><?php echo text($ACT3CCDIST); ?></textarea></td>
                                                        <td class="text-center"><?php echo xlt('L{{left}}'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center"><i class="fa fa-reply rotate-left"></i></td>
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT4CCDIST" name="PRIOR_ACT4CCDIST" class="ACT"><?php echo text($ACT4CCDIST); ?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT5CCDIST" name="PRIOR_ACT5CCDIST" class="ACT"><?php echo text($ACT5CCDIST); ?></textarea></td>
                                                        <td class="text-center">
                                                        <textarea disabled id="PRIOR_ACT6CCDIST" name="PRIOR_ACT6CCDIST" class="ACT"><?php echo text($ACT6CCDIST); ?></textarea></td>
                                                        <td><i class="fa fa-reply flip-left"></i></td>
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
                                                            <textarea disabled id="PRIOR_ACT11CCDIST" name="PRIOR_ACT11CCDIST" class="ACT"><?php echo text($ACT11CCDIST); ?></textarea></td>
                                                    </tr>
                                                </table>
                                                <br />
                                            </div>
                                            <div id="PRIOR_ACT_SCNEAR" name="PRIOR_ACT_SCNEAR" class="nodisplay ACT_box">
                                                <br />
                                                <table>
                                                    <tr>
                                                        <td class="text-center"><?php echo xlt('R{{right}}'); ?></td>
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT1SCNEAR" name="PRIOR_ACT1SCNEAR" class="ACT"><?php echo text($ACT1SCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT2SCNEAR"  name="PRIOR_ACT2SCNEAR"class="ACT"><?php echo text($ACT2SCNEAR); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT3SCNEAR"  name="PRIOR_ACT3SCNEAR" class="ACT"><?php echo text($ACT3SCNEAR); ?></textarea></td>
                                                        <td style="text-align:center;"><?php echo xlt('L{{left}}'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center"><i class="fa fa-reply rotate-left"></i></td>
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT4SCNEAR" name="PRIOR_ACT4SCNEAR" class="ACT"><?php echo text($ACT4SCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT5SCNEAR" name="PRIOR_ACT5SCNEAR" class="ACT"><?php echo text($ACT5SCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6SCNEAR" name="PRIOR_ACT6SCNEAR" class="ACT"><?php echo text($ACT6SCNEAR); ?></textarea></td>
                                                        <td class="text-center"><i class="fa fa-reply flip-left"></i></td>
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
                                                        <td class="text-center"><?php echo xlt('R{{right}}'); ?></td>
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT1CCNEAR" name="PRIOR_ACT1CCNEAR" class="ACT"><?php echo text($ACT1CCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT2CCNEAR"  name="PRIOR_ACT2CCNEAR"class="ACT"><?php echo text($ACT2CCNEAR); ?></textarea></td>
                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT3CCNEAR"  name="PRIOR_ACT3CCNEAR" class="ACT"><?php echo text($ACT3CCNEAR); ?></textarea></td>
                                                        <td class="text-center"><?php echo xlt('L{{left}}'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center"><i class="fa fa-reply rotate-left"></i></td>
                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                        <textarea disabled id="PRIOR_ACT4CCNEAR" name="PRIOR_ACT4CCNEAR" class="ACT"><?php echo text($ACT4CCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;text-align:center;">
                                                        <textarea disabled id="PRIOR_ACT5CCNEAR" name="PRIOR_ACT5CCNEAR" class="ACT"><?php echo text($ACT5CCNEAR); ?></textarea></td>
                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                        <textarea disabled id="PRIOR_ACT6CCNEAR" name="PRIOR_ACT6CCNEAR" class="ACT"><?php echo text($ACT6CCNEAR); ?></textarea></td>
                                                        <td class="text-center"><i class="fa fa-reply flip-left"></i></td>
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
                <br />
                <div id="PRIOR_NPCNPA" name="PRIOR_NPCNPA">
                    <table style="position:relative;float:left;text-align:center;margin: 4 2;width:100%;font-size:1.0em;padding:4px;">
                        <tr style="">
                            <td style="width:50%;"></td>
                            <td style="font-weight:bold;"><?php echo xlt('OD{{right eye}}'); ?></td>
                            <td style="font-weight:bold;"><?php echo xlt('OS{{left eye}}'); ?></td>
                        </tr>
                        <tr>
                            <td class="right"><span title="<?php echo xla('Near Point of Accomodation'); ?>"><?php echo xlt('NPA{{near point of accomodation}}'); ?>:</span></td>
                            <td><input disabled type="text" id="PRIOR_ODNPA" style="width:70%;" name="PRIOR_ODNPA" value="<?php echo attr($ODNPA); ?>"></td>
                            <td><input disabled type="text" id="PRIOR_OSNPA" style="width:70%;" name="PRIOR_OSNPA" value="<?php echo attr($OSNPA); ?>"></td>
                        </tr>
                        <tr>
                            <td class="right"><span title="<?php echo xla('Near Point of Convergence'); ?>"><?php echo xlt('NPC{{near point of convergence}}'); ?>:</span></td>
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
                            <td colspan="3">&nbsp;</td>
                        </tr>
                        <tr><td class="bold underline"><?php echo xlt('Amplitudes'); ?>:</td>
                            <td ><?php echo xlt('Distance'); ?></td>
                            <td><?php echo xlt('Near'); ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;"><?php echo xlt('Divergence'); ?>:</td>
                            <td><input disabled type="text" id="PRIOR_DACCDIST" name="PRIOR_DACCDIST" value="<?php echo attr($DACCDIST); ?>"></td>
                            <td><input disabled type="text" id="PRIOR_DACCNEAR" name="PRIOR_DACCNEAR" value="<?php echo attr($DACCNEAR); ?>"></td></tr>
                        <tr>
                            <td style="text-align:right;"><?php echo xlt('Convergence'); ?>:</td>
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
                <?php
                $hash_tag = '<i class="fa fa-minus"></i>';
                if ($MOTILITY_RS > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_RS; ++$index) {
                        $here = "PRIOR_MOTILITY_RS_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_RI > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_RI; ++$index) {
                        $here = "PRIOR_MOTILITY_RI_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_LS > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_LS; ++$index) {
                        $here = "PRIOR_MOTILITY_LS_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_LI > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_LI; ++$index) {
                        $here = "PRIOR_MOTILITY_LI_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_RRSO > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_RRSO; ++$index) {
                        $here = "PRIOR_MOTILITY_RRSO_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_LRSO > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_LRSO; ++$index) {
                        $here = "PRIOR_MOTILITY_LRSO_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_RLIO > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_RLIO; ++$index) {
                        $here = "PRIOR_MOTILITY_RLIO_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_LLIO > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_LLIO; ++$index) {
                        $here = "PRIOR_MOTILITY_LLIO_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_RLSO > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_RLSO; ++$index) {
                        $here = "PRIOR_MOTILITY_RLSO_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_LLSO > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_LLSO; ++$index) {
                        $here = "PRIOR_MOTILITY_LLSO_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_RRIO > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_RRIO; ++$index) {
                        $here = "PRIOR_MOTILITY_RRIO_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_LRIO > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_LRIO; ++$index) {
                        $here = "PRIOR_MOTILITY_LRIO_" . $index;
                        $$here = $hash_tag;
                    }
                }


                $hash_tag = '<i class="fa fa-minus rotate-left"></i>';
                if ($MOTILITY_LR > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_LR; ++$index) {
                        $here = "PRIOR_MOTILITY_LR_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_LL > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_LL; ++$index) {
                        $here = "PRIOR_MOTILITY_LL_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_RR > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_RR; ++$index) {
                        $here = "PRIOR_MOTILITY_RR_" . $index;
                        $$here = $hash_tag;
                    }
                }

                if ($MOTILITY_RL > '0') {
                    $PRIOR_MOTILITYNORMAL = '';
                    for ($index = 1; $index <= $MOTILITY_RL; ++$index) {
                        $here = "PRIOR_MOTILITY_RL_" . $index;
                        $$here = $hash_tag;
                    }
                }
                ?>
            <div id="PRIOR_NEURO_MOTILITY" class="text_clinical borderShadow"
                style="float:left;font-size:0.9em;margin:2 2;padding: 0 10;font-weight:bold;height:134px;width:195px;">
                <div>
                    <table style="width:100%;margin:0 0 1 0;">
                        <tr>
                            <td style="width:40%;font-size:0.9em;margin:0 auto;font-weight:bold;"><?php echo xlt('Motility'); ?>:</td>
                            <td style="font-size:0.9em;vertical-align:middle;text-align:right;top:0.0in;right:0.1in;height:30px;">
                                <label for="PRIOR_MOTILITYNORMAL" class="input-helper input-helper--checkbox"><?php echo xlt('Normal'); ?></label>
                                <input disabled id="PRIOR_MOTILITYNORMAL" name="PRIOR_MOTILITYNORMAL" type="checkbox" value="1" <?php
                                if ($MOTILITYNORMAL > '0') {
                                    echo "checked";
                                } ?> disabled>
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
                <input disabled type="hidden" name="PRIOR_MOTILITY_RRSO"  id="PRIOR_MOTILITY_RRSO" value="<?php echo attr($MOTILITY_RRSO); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_RLSO"  id="PRIOR_MOTILITY_RLSO" value="<?php echo attr($MOTILITY_RLSO); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_RRIO"  id="PRIOR_MOTILITY_RRIO" value="<?php echo attr($MOTILITY_RRIO); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_RLIO"  id="PRIOR_MOTILITY_RLIO" value="<?php echo attr($MOTILITY_RLIO); ?>">

                <input disabled type="hidden" name="PRIOR_MOTILITY_LRSO"  id="PRIOR_MOTILITY_LRSO" value="<?php echo attr($MOTILITY_LRSO); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_LLSO"  id="PRIOR_MOTILITY_LLSO" value="<?php echo attr($MOTILITY_LLSO); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_LRIO"  id="PRIOR_MOTILITY_LRIO" value="<?php echo attr($MOTILITY_LRIO); ?>">
                <input disabled type="hidden" name="PRIOR_MOTILITY_LLIO"  id="PRIOR_MOTILITY_LLIO" value="<?php echo attr($MOTILITY_LLIO); ?>">

                <div style="float:left;left:0.4in;text-decoration:underline;"><?php echo xlt('OD{{right eye}}'); ?></div>
                <div style="float:right;right:0.4in;text-decoration:underline;"><?php echo xlt('OS{{left eye}}'); ?></div><br />
                <div class="divTable" style="background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 90% 75%;height:0.77in;width:0.71in;padding:1px;margin:6 1 1 2;">
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RRSO_4; ?></div>
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
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RLSO_4; ?></div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RRSO_3; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_3_1" id="PRIOR_MOTILITY_RS_3_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_3" id="PRIOR_MOTILITY_RS_3"><?php echo $PRIOR_MOTILITY_RS_3; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_3_2" id="PRIOR_MOTILITY_RS_3_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RLSO_3; ?></div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RRSO_2; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_2_1" id="PRIOR_MOTILITY_RS_2_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_2" id="PRIOR_MOTILITY_RS_2"><?php echo $PRIOR_MOTILITY_RS_2; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_2_2" id="PRIOR_MOTILITY_RS_2_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RLSO_2; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RRSO_1; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_1_1" id="PRIOR_MOTILITY_RS_1_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_1" id="PRIOR_MOTILITY_RS_1"><?php echo $PRIOR_MOTILITY_RS_1; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RS_1_2" id="PRIOR_MOTILITY_RS_1_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RLSO_1; ?></div>
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
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RRIO_1; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_1_1" id="PRIOR_MOTILITY_RI_1_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_RI_1" name="PRIOR_MOTILITY_RI_1"><?php echo $PRIOR_MOTILITY_RI_1; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_1_2" id="PRIOR_MOTILITY_RI_1_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RLIO_1; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RRIO_2; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_2_1" id="PRIOR_MOTILITY_RI_2_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_RI_2" name="PRIOR_MOTILITY_RI_2"><?php echo $PRIOR_MOTILITY_RI_2; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_2_2" id="PRIOR_MOTILITY_RI_2_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RLIO_2; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RRIO_3; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_5" id="PRIOR_MOTILITY_RI_3_5">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_3" id="PRIOR_MOTILITY_RI_3_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_1" id="PRIOR_MOTILITY_RI_3_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_RI_3" name="PRIOR_MOTILITY_RI_3"><?php echo $PRIOR_MOTILITY_RI_3; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_2" id="PRIOR_MOTILITY_RI_3_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_4" id="PRIOR_MOTILITY_RI_3_4">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RI_3_6" id="PRIOR_MOTILITY_RI_3_6">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RLIO_3; ?></div>
                        <div class="divCell"></div>
                    </div>
                    <div class="divRow">
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RRIO_4; ?></div>
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
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_RLIO_4; ?></div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                    </div>
                </div>
                <div class="divTable" style="float:right;background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 90% 75%;height:0.77in;width:0.71in;padding:1px;margin:6 2 0 0;">
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LRSO_4; ?></div>
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
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LLSO_4; ?></div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LRSO_3; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_3_1" id="PRIOR_MOTILITY_LS_3_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_3" id="PRIOR_MOTILITY_LS_3"><?php echo $PRIOR_MOTILITY_LS_3; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_3_2" id="PRIOR_MOTILITY_LS_3_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LLSO_3; ?></div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LRSO_2; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_2_1" id="PRIOR_MOTILITY_LS_2_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_2" id="PRIOR_MOTILITY_LS_2"><?php echo $PRIOR_MOTILITY_LS_2; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_2_2" id="PRIOR_MOTILITY_LS_2_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LLSO_2; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LRSO_1; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_1_1" id="PRIOR_MOTILITY_LS_1_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_1" id="PRIOR_MOTILITY_LS_1"><?php echo $PRIOR_MOTILITY_LS_1; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LS_1_2" id="PRIOR_MOTILITY_LS_1_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LLSO_1; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_RO_I_2" id="PRIOR_MOTILITY_RO_I_2"><?php echo $PRIOR_MOTILITY_LRIO_1; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_LI_1" name="PRIOR_MOTILITY_LI_1"><?php echo $PRIOR_MOTILITY_LI_1; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_2" id="PRIOR_MOTILITY_LO_I_2"><?php echo $PRIOR_MOTILITY_LLIO_1; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_3_4" id="PRIOR_MOTILITY_LL_3_4">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LL_4_4" id="PRIOR_MOTILITY_LL_4_4">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                    </div>
                    <div class="divRow">
                        <div class="divCell" name="PRIOR_MOTILITY_RO_I_3_1" id="PRIOR_MOTILITY_RO_I_3_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_RO_I_3" id="PRIOR_MOTILITY_RO_I_3">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LRIO_2; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_2_1" id="PRIOR_MOTILITY_LI_2_1">&nbsp;</div>
                        <div class="divCell" id="PRIOR_MOTILITY_LI_2" name="PRIOR_MOTILITY_LI_2"><?php echo $PRIOR_MOTILITY_LI_2; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_2_2" id="PRIOR_MOTILITY_LI_2_2">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LLIO_2; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_2" id="PRIOR_MOTILITY_RO_I_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_3_1" id="PRIOR_MOTILITY_LO_I_3_1">&nbsp;</div>
                        </div>
                    <div class="divRow">
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_3" id="PRIOR_MOTILITY_RO_I_3">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LRIO_3; ?></div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_5" id="PRIOR_MOTILITY_LI_3_5">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_3" id="PRIOR_MOTILITY_LI_3_3">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_1" id="PRIOR_MOTILITY_LI_3_1">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3"   id="PRIOR_MOTILITY_LI_3"><?php echo $PRIOR_MOTILITY_LI_3; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_2" id="PRIOR_MOTILITY_LI_3_2">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_4" id="PRIOR_MOTILITY_LI_3_4">&nbsp;</div>
                        <div class="divCell" name="PRIOR_MOTILITY_LI_3_6" id="PRIOR_MOTILITY_LI_3_6">&nbsp;</div>
                        <div class="divCell">&nbsp;</div>
                        <div class="divCell"><?php echo $PRIOR_MOTILITY_LLIO_3; ?></div>
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_3" id="PRIOR_MOTILITY_LO_I_3">&nbsp;</div>

                    </div>
                    <div class="divRow">
                        <div class="divCell" name="PRIOR_MOTILITY_RO_I_4" id="PRIOR_MOTILITY_RO_I_4"><?php echo $PRIOR_MOTILITY_LRIO_4; ?></div>
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
                        <div class="divCell" name="PRIOR_MOTILITY_LO_I_4" id="PRIOR_MOTILITY_LO_I_4"><?php echo $PRIOR_MOTILITY_LLIO_4; ?></div>
                    </div>
                    <div class="divRow"><div class="divCell">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
        <br />
        <div class="QP_lengthen">
            <span style="font-weight:bold;"><?php echo xlt('Comments'); ?>:</span>
            <br />
            <textarea disabled id="PRIOR_NEURO_COMMENTS" name="PRIOR_NEURO_COMMENTS"><?php echo text($NEURO_COMMENTS); ?></textarea>
        </div>
        <input type="hidden" name="PRIOR_PREFS_ACT_SHOW"  id="PRIOR_PREFS_ACT_SHOW" value="<?php echo attr($ACT_SHOW); ?>">

        <script>
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
                $("#PRIOR_ACTMAIN").toggleClass('nodisplay');
                $("#PRIOR_NPCNPA").toggleClass('nodisplay');
                $("#PRIOR_ACTNORMAL_CHECK").toggleClass('nodisplay');
                $("#PRIOR_ACTTRIGGER").toggleClass('underline');
                var show = $("#PREFS_ACT_SHOW").val();
                $("#PRIOR_ACT_tab_"+show).trigger('click');
            }
        </script>
        <?php
    } elseif ($zone == "IMPPLAN") {
        if ($report == '0') {
            $output =  priors_select($zone, $orig_id, $id_to_show, $pid);
        }
        ?>
        <input disabled type="hidden" id="PRIORS_<?php echo attr($zone); ?>_prefix" name="PRIORS_<?php echo attr($zone); ?>_prefix" value="">
        <span class="closeButton float-right fas fa-times" id="Close_PRIORS_<?php echo attr($zone); ?>" name="Close_PRIORS_<?php echo attr($zone); ?>"></span>
        <div name="prior_selector" class="PRIORS">
                <?php
                echo $output;
                ?>
        </div>
        <span style="font-weight:bold;"> <?php echo xlt('Prior IMP/PLAN'); ?>:</span>
        <br />
        <?php
        $PRIOR_IMPPLAN_items = build_IMPPLAN_items($pid, $id_to_show);

        if ($PRIOR_IMPPLAN_items) {
            echo "<br /><br /><div style='width:90%;'>";
            $i = '0';
            $k = '1';
            foreach ($PRIOR_IMPPLAN_items as $item) {
                echo "<div class='IMPPLAN_class' style='clear:both;margin:10px;'>";
                echo "  <span>$k. " . text($item['title']) . "</span><span class='float-right'>" . $item['code'] . "</span><br />";
                echo '  <div class="fake-textarea-disabled-4">' . nl2br(text($item['plan'])) . '</div>';
                echo '</div>';
                $i++;
                $k++;
            }

            echo "</div>";
        }
    } elseif ($zone == "ALL") {
        echo $selector = priors_select($zone, $orig_id, $id_to_show, $pid);
    } elseif ($zone == "PMSFH") {
        // Check authorization.
        if (AclMain::aclCheckCore('patients', 'med')) {
            $tmp = getPatientData($pid);
        }

        // We are going to build the PMSFH panel.
        // There are two rows in our panel.
        echo "<div style='height:auto;'>";
        echo $display_PMSFH = display_PMSFH('2');
        echo "</div>";
    }

    $output = ob_get_contents();

    ob_end_clean();
    return $output;
}

/**
 * Function to prepare for sending the PMSFH_panel and PMSFH_right_panel
 * via display_PMSFH('2') and show_PMSFH_panel($PMSFH) respectively,
 * to javascript to display changes to the user.
 * @param associative array $PMSFH if it exists
 * @return json encoded string
 */
function send_json_values($PMSFH = "")
{
    global $pid;
    global $form_id;
    if (!$PMSFH) {
        build_PMSFH();
    }

    $send['PMSFH'] = $PMSFH[0]; //actual array
    $send['PMH_panel'] = display_PMSFH('2');//display PMSFH next to the PMSFH Builder
    $send['right_panel'] = show_PMSFH_panel($PMSFH);//display PMSFH in a slidable right-sided panel
    $send['IMPPLAN_items'] = build_IMPPLAN_items($pid, $form_id);
    echo json_encode($send);
}

/**
 *  This function builds the complete PMSFH array for a given patient, including the ROS for this encounter.
 *
 *  It returns the PMSFH array to be used to display it anyway you like.
 *  Currently it is used to display the expanded PMSFH 3 ways:
 *      in the Quick Pick square;
 *      as a persistent/hideable Right Panel;
 *      and in the Printable Report form.
 *  For other specialties, breaking out subtypes of surgeries, meds and
 *  medical_problems should be done here by defining new ISSUE_TYPES which are subcategories of the current
 *  ISSUE_TYPES medical_problem, surgery and medication.  This way we do not change the base install ISSUE_TYPES,
 *  we merely extend them through subcategorization, allowing the reporting features built in for MU1/2/3/100?
 *  to function at their base level.
 *
 * @param string $pid is the patient identifier
 * @return $PMSFH array, access items as $PMSFH[0]
 */
function build_PMSFH($pid)
{
    global $form_id;

    $PMSFH = [];
    $PMSFH['CHRONIC'] = [];
    //Define the PMSFH array elements as you need them:
    $PMSFH_labels = array("POH", "POS", "Eye Meds", "PMH", "Surgery", "Medication", "Allergy", "SOCH", "FH", "ROS");
    foreach ($PMSFH_labels as $panel_type) {
        $PMSFH[$panel_type] = [];
        $subtype = " and (subtype is NULL or subtype ='' )";
        $order = "ORDER BY title";
        if ($panel_type == "FH" || $panel_type == "SOCH" || $panel_type == "ROS") {
            /*
             *  We are going to build SocHx, FH and ROS separately below since they don't feed off of
             *  the pre-existing ISSUE_TYPE array - so for now do nothing
             */
            continue;
        } elseif ($panel_type == 'POH') {
            $focusISSUE = "medical_problem"; //openEMR ISSUE_TYPE
            $subtype = " and subtype ='eye'";
            /* This is an "eye" form: providers would like ophthalmic medical problems listed separately.
             * Thus we split the ISSUE_TYPE 'medical_problem' using subtype "eye"
             * but it could be "GYN", "ONC", "GU" etc - for whoever wants to
             * extend this for their own specific "sub"-lists.
             * Similarly, consider Past Ocular Surgery, or Past GYN Surgery, etc for specialty-specific
             * surgery lists.  They would be subtypes of the ISSUE_TYPE 'surgery'...
             * eg.
             *   if ($panel_type =='POS') { //Past Ocular Surgery
             *   $focusISSUE = "surgery";
             *   $subtype=" and subtype ='eye'";
             *   }
             * The concept is extensible to sub lists for Allergies & Medications too.
             * eg.
             *   if ($panel_type =='OncMeds') {
             *      $focusISSUE = "medication";
             *      $subtype=" and subtype ='onc'";
             *   }
             */
        } elseif ($panel_type == 'POS') {
            $focusISSUE = "surgery"; //openEMR ISSUE_TYPE
            $subtype = " and subtype ='eye'";
        } elseif ($panel_type == 'PMH') {
            $focusISSUE = "medical_problem"; //openEMR ISSUE_TYPE
            $subtype = " and (subtype = '' OR subtype IS NULL)"; //fee_sheet makes subtype=
        } elseif ($panel_type == 'Surgery') {
            $focusISSUE = "surgery"; //openEMR ISSUE_TYPE
            $subtype = "  and (subtype = '' OR subtype IS NULL)";
            $order = "ORDER BY begdate DESC";
        } elseif ($panel_type == 'Allergy') {
            $focusISSUE = "allergy"; //openEMR ISSUE_TYPE
            $subtype = "";
        } elseif ($panel_type == 'Medication') {
            $focusISSUE = "medication"; //openEMR ISSUE_TYPE
            $subtype = "";
        } elseif ($panel_type == 'Eye Meds') {
            $focusISSUE = "medication"; //openEMR ISSUE_TYPE
            $subtype = "and subtype = 'eye'";// and subtype ='eye' ";
        }

        $pres = sqlStatement("SELECT * FROM lists WHERE pid = ? AND type = ? " .
            $subtype . " " . $order, array($pid,$focusISSUE));
        $row_counter = '0';
        while ($row = sqlFetchArray($pres)) {
            $rowid = $row['id'];
            $disptitle = text(trim($row['title'])) ? text($row['title']) : "[" . xlt("Missing Title") . "]";
            //  look up the diag codes
            $codetext = "";
            $codedesc = "";
            $codetype = "";
            $code = "";
            if ($row['diagnosis'] != "") {
                $diags = explode(";", $row['diagnosis']);
                foreach ($diags as $diag) {
                    $codedesc = lookup_code_descriptions($diag);
                    list($codetype, $code) = explode(':', $diag);
                    $order   = array("\r\n", "\n","\r");
                    $codedesc = str_replace($order, '', $codedesc);
                    $codetext .= text($diag) . " (" . text($codedesc) . ")";
                }
            }

            // calculate the status
            if ($row['outcome'] == "1" && $row['enddate'] != null) {
              // Resolved
                $statusCompute = generate_display_field(array('data_type' => '1','list_id' => 'outcome'), $row['outcome']);
            } elseif ($row['enddate'] == null) {
                   $statusCompute = xlt("Active");
            } else {
                   $statusCompute = xlt("Inactive");
            }

            ($row['comments'] != null) ? ($comments = $row['comments']) : ($comments = "");
            $counter_here = count($PMSFH[$panel_type]);
            $newdata =  array (
                'title' => $disptitle,
                'status' => $statusCompute,
                'begdate' => $row['begdate'],
                'enddate' => $row['enddate'],
                'returndate' => $row['returndate'],
                'occurrence' => $row['occurrence'],
                'classification' => $row['classification'],
                'referredby' => $row['referredby'],
                'extrainfo' => $row['extrainfo'],
                'diagnosis' => $row['diagnosis'],
                'activity' => $row['activity'],
                'code' => $code,
                'codedesc' => $codedesc,
                'codetext' => $codetext,
                'codetype' => $codetype,
                'comments' => $comments,
                'issue' => $row['id'],
                'rowid' => $row['id'],
                'row_type' => $row['type'],
                'row_subtype' => $row['subtype'],
                'user' => $row['user'],
                'groupname' => $row['groupname'],
                'outcome' => $row['outcome'],
                'destination' => $row['destination'],
                'reinjury_id' => $row['reinjury_id'],
                'injury_part' => $row['injury_part'],
                'injury_type' => $row['injury_type'],
                'injury_grade' => $row['injury_grade'],
                'reaction' => $row['reaction'],
                'external_allergyid' => $row['external_allergyid'],
                'erx_source' => $row['erx_source'],
                'erx_uploaded' => $row['erx_uploaded'],
                'modifydate' => $row['modifydate'],
                'PMSFH_link' => $panel_type . "_" . $row_counter
            );
            //let the end user decide on display elsewhere...  This is all about the array itself.
            $PMSFH[$panel_type][] = $newdata;
            if ($row['occurrence'] == '4') {
                $PMSFH['CHRONIC'][] = $newdata;
            }

            $row_counter++;
        }
    }

    //Build the SocHx portion of $PMSFH for this patient.
    //$given ="coffee,tobacco,alcohol,sleep_patterns,exercise_patterns,seatbelt_use,counseling,hazardous_activities,recreational_drugs";
    $result1 = sqlQuery("select * from history_data where pid=? order by date DESC limit 0,1", array($pid));

    $group_fields_query = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'HIS' AND group_id = '4' AND uor > 0 " .
    "ORDER BY seq");
    $PMSFH['SOCH'] = [];
    while ($group_fields = sqlFetchArray($group_fields_query)) {
        $titlecols  = $group_fields['titlecols'];
        $datacols   = $group_fields['datacols'];
        $data_type  = $group_fields['data_type'];
        $field_id   = $group_fields['field_id'];
        $list_id    = $group_fields['list_id'];
        $currvalue  = '';
        if ((preg_match("/^\|?0\|?\|?/", $result1[$field_id])) || ($result1[$field_id] == '')) {
            continue;
        } else {
            $currvalue = $result1[$field_id];
        }
        $PMSFH['SOCH'][$field_id] = [];
        if ($data_type == 28 || $data_type == 32) {
            $tmp = explode('|', $currvalue);
            switch (count($tmp)) {
                case "4":
                    $PMSFH['SOCH'][$field_id]['resnote'] = $tmp[0];
                    $PMSFH['SOCH'][$field_id]['restype'] = $tmp[1];
                    $PMSFH['SOCH'][$field_id]['resdate'] = $tmp[2];
                    $PMSFH['SOCH'][$field_id]['reslist'] = $tmp[3];
                    break;
                case "3":
                    $PMSFH['SOCH'][$field_id]['resnote'] = $tmp[0];
                    $PMSFH['SOCH'][$field_id]['restype'] = $tmp[1];
                    $PMSFH['SOCH'][$field_id]['resdate'] = $tmp[2];
                    break;
                case "2":
                    $PMSFH['SOCH'][$field_id]['resnote'] = $tmp[0];
                    $PMSFH['SOCH'][$field_id]['restype'] = $tmp[1];
                    $PMSFH['SOCH'][$field_id]['resdate'] = "";
                    break;
                case "1":
                    $PMSFH['SOCH'][$field_id]['resnote'] = $tmp[0];
                    $PMSFH['SOCH'][$field_id]['resdate'] = $PMSFH['SOCH'][$field_id]['restype'] = "";
                    break;
                default:
                    $PMSFH['SOCH'][$field_id]['restype'] = $PMSFH['SOCH'][$field_id]['resdate'] = $PMSFH['SOCH'][$field_id]['resnote'] = "";
                    break;
            }

            $PMSFH['SOCH'][$field_id]['resnote'] = text($PMSFH['SOCH'][$field_id]['resnote']);
            $PMSFH['SOCH'][$field_id]['resdate'] = text($PMSFH['SOCH'][$field_id]['resdate']);
        } elseif ($data_type == 2) {
            $PMSFH['SOCH'][$field_id]['resnote'] = nl2br(htmlspecialchars($currvalue, ENT_NOQUOTES));
        }

        if ($PMSFH['SOCH'][$field_id]['resnote'] > '') {
            $PMSFH['SOCH'][$field_id]['display'] = substr($PMSFH['SOCH'][$field_id]['resnote'], 0, 10);
        } elseif ($PMSFH['SOCH'][$field_id]['restype']) {
            $PMSFH['SOCH'][$field_id]['display'] = str_replace($field_id, '', $PMSFH['SOCH'][$field_id]['restype']);
        }

        //coffee,tobacco,alcohol,sleep_patterns,exercise_patterns,seatbelt_use,counseling,hazardous_activities,recreational_drugs
        if ($field_id == "coffee") {
            $PMSFH['SOCH'][$field_id]['short_title'] = xlt("Caffeine");
        }

        if ($field_id == "tobacco") {
            $PMSFH['SOCH'][$field_id]['short_title'] = xlt("Cigs");
        }

        if ($field_id == "alcohol") {
            $PMSFH['SOCH'][$field_id]['short_title'] = xlt("ETOH");
        }

        if ($field_id == "sleep_patterns") {
            $PMSFH['SOCH'][$field_id]['short_title'] = xlt("Sleep");
        }

        if ($field_id == "exercise_patterns") {
            $PMSFH['SOCH'][$field_id]['short_title'] = xlt("Exercise");
        }

        if ($field_id == "seatbelt_use") {
            $PMSFH['SOCH'][$field_id]['short_title'] = xlt("Seatbelt");
        }

        if ($field_id == "counseling") {
            $PMSFH['SOCH'][$field_id]['short_title'] = xlt("Therapy");
        }

        if ($field_id == "hazardous_activities") {
            $PMSFH['SOCH'][$field_id]['short_title'] = xlt("Thrills");
        }

        if ($field_id == "recreational_drugs") {
            $PMSFH['SOCH'][$field_id]['short_title'] = xlt("Drug Use");
        }
    }

    //  Drag in Marital status and Employment history to this Social Hx area.
    $patient = getPatientData($pid, "*");
    $PMSFH['SOCH']['marital_status'] = [];
    $PMSFH['SOCH']['occupation'] = [];
    $PMSFH['SOCH']['marital_status']['short_title'] = xlt("Marital");
    $PMSFH['SOCH']['marital_status']['display'] = text($patient['status']);
    $PMSFH['SOCH']['occupation']['short_title'] = xlt("Occupation");
    $PMSFH['SOCH']['occupation']['display'] = text($patient['occupation']);


    // Build the FH portion of $PMSFH,$PMSFH['FH']
    // history_mother  history_father  history_siblings    history_offspring   history_spouse
    // relatives_cancer    relatives_tuberculosis  relatives_diabetes  relatives_high_blood_pressure   relatives_heart_problems    relatives_stroke    relatives_epilepsy  relatives_mental_illness    relatives_suicide
    //  There are two ways FH is stored in the history area, one on a specific relationship basis
    // ie. parent,sibling, offspring has X, or the other by "relatives_disease" basis.
    // Hmmm, neither really meets our needs.  This is an eye form; we do a focused family history.
    // Cataracts, glaucoma, AMD, RD, cancer, heart disease etc.
    // The openEMR people who want to adapt this for another specialty will no doubt
    // have different diseases they want listed in the FH specifically.  We all need to be able to
    // adjust the form.  Perhaps we should use the UserDefined fields at the end of this history_data table?
    // Question 1. is, does anything use this family history data - any higher function like reporting?
    // Also 2., if there is an engine to validate level of exam, how do we tell it that this was completed?
    // First we would need to know the criteria this engine looks for and I don't think in reality there is anything
    // written yet that does validate exams for coding level, so maybe we should create a flag in the user defined area of the history_data
    // table to notate that the FH portion of the exam was completed? TBD.
    /*
    Cancer:     Tuberculosis:
    Diabetes:       High Blood Pressure:
    Heart Problems:     Stroke:
    Epilepsy:       Mental Illness:
    Suicide:
    */
    $group_fields_query = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'HIS' AND group_id = '3' AND uor > 0 " .
    "ORDER BY seq");
    while ($group_fields = sqlFetchArray($group_fields_query)) {
        $titlecols  = $group_fields['titlecols'];
        $datacols   = $group_fields['datacols'];
        $data_type  = $group_fields['data_type'];
        $field_id   = $group_fields['field_id'];
        $list_id    = $group_fields['list_id'];
        $currvalue  = '';
        if ((preg_match("/^\|?0\|?\|?/", $result1[$field_id])) || ($result1[$field_id] == '')) {
            continue;
        } else {
            $currvalue = $result1[$field_id];
        }

        $PMSFH['FH'][$field_id]['resnote'] = nl2br(htmlspecialchars($currvalue, ENT_NOQUOTES));
        if ($PMSFH['FH'][$field_id]['resnote'] > '') {
            $PMSFH['FH'][$field_id]['display'] = substr($PMSFH['FH'][$field_id]['resnote'], 0, 100);
        } elseif ($PMSFH['FH'][$field_id]['restype']) {
            $PMSFH['FH'][$field_id]['display'] = str_replace($field_id, '', $PMSFH['FH'][$field_id]['restype']);
        } else {
            $PMSFH['FH'][$field_id]['display'] = xlt("denies");
        }

        //coffee,tobacco,alcohol,sleep_patterns,exercise_patterns,seatbelt_use,counseling,hazardous_activities,recreational_drugs
        if ($field_id == "relatives_cancer") {
            $PMSFH['FH'][$field_id]['short_title'] = xlt("Cancer");
        }

        if ($field_id == "relatives_diabetes") {
            $PMSFH['FH'][$field_id]['short_title'] = xlt("Diabetes");
        }

        if ($field_id == "relatives_high_blood_pressure") {
            $PMSFH['FH'][$field_id]['short_title'] = xlt("HTN{{hypertension}}");
        }

        if ($field_id == "relatives_heart_problems") {
            $PMSFH['FH'][$field_id]['short_title'] = xlt("Cor Disease");
        }

        if ($field_id == "relatives_epilepsy") {
            $PMSFH['FH'][$field_id]['short_title'] = xlt("Epilepsy");
        }

        if ($field_id == "relatives_mental_illness") {
            $PMSFH['FH'][$field_id]['short_title'] = xlt("Psych");
        }

        if ($field_id == "relatives_suicide") {
            $PMSFH['FH'][$field_id]['short_title'] = xlt("Suicide");
        }

        if ($field_id == "relatives_stroke") {
            $PMSFH['FH'][$field_id]['short_title'] = xlt("Stroke");
        }

        if ($field_id == "relatives_tuberculosis") {
            $PMSFH['FH'][$field_id]['short_title'] = xlt("TB");
        }
    }

    // Now make some of our own using the usertext11-30 fields
    // These can be customized for specialties but remember this is just an array,
    // you will need to check the code re: how it is displayed elsewhere...
    // For now, just changing the short_titles will display intelligently
    // but it is best to change both in the long run.
    // $PMSFH['FH']['my_term']['display'] = (substr($result1['usertext11'],0,10));
    // $PMSFH['FH']['my_term']['short_title'] = xlt("My Term");

    $PMSFH['FH']['glaucoma']['display'] = (substr($result1['usertext11'], 0, 100));
    $PMSFH['FH']['glaucoma']['short_title'] = xlt("Glaucoma");
    $PMSFH['FH']['cataract']['display'] = (substr($result1['usertext12'], 0, 100));
    $PMSFH['FH']['cataract']['short_title'] = xlt("Cataract");
    $PMSFH['FH']['amd']['display'] = (substr($result1['usertext13'], 0, 100));
    $PMSFH['FH']['amd']['short_title'] = xlt("AMD{{age related macular degeneration}}");
    $PMSFH['FH']['RD']['display'] = (substr($result1['usertext14'], 0, 100));
    $PMSFH['FH']['RD']['short_title'] = xlt("RD{{retinal detachment}}");
    $PMSFH['FH']['blindness']['display'] = (substr($result1['usertext15'], 0, 100));
    $PMSFH['FH']['blindness']['short_title'] = xlt("Blindness");
    $PMSFH['FH']['amblyopia']['display'] = (substr($result1['usertext16'], 0, 100));
    $PMSFH['FH']['amblyopia']['short_title'] = xlt("Amblyopia");
    $PMSFH['FH']['strabismus']['display'] = (substr($result1['usertext17'], 0, 100));
    $PMSFH['FH']['strabismus']['short_title'] = xlt("Strabismus");
    $PMSFH['FH']['other']['display'] = (substr($result1['usertext18'], 0, 100));
    $PMSFH['FH']['other']['short_title'] = xlt("Other");

    // Thinking this might be a good place to put in last_retinal exam and last_HbA1C?
    // I don't know enough about the reporting parameters - it is probably some alreay in openEMR?
    // Pull it in if it is and put it where?
    // $PMSFH['SOCH'][$field_id]['resnote'] = nl2br(htmlspecialchars($currvalue,ENT_NOQUOTES));

    // Build ROS into $PMSFH['ROS'] also for this patient.
    // ROS is not static and is directly linked to each encounter
    // True it could be a separate table, but it is currently in form_eye_mag for each visit
    // To use this for any other forms, we should consider making this its own separate table with id,pid and ?encounter link,
    // just like we are doing for Impression Plan.  Mybe we can piggybak onto one of the ROS tables already in OpenEMR?

    //define the ROS area to include = $given
    $given = "ROSGENERAL,ROSHEENT,ROSCV,ROSPULM,ROSGI,ROSGU,ROSDERM,ROSNEURO,ROSPSYCH,ROSMUSCULO,ROSIMMUNO,ROSENDOCRINE,ROSCOMMENTS";
    $ROS_table = "form_eye_ros";
    $query = "SELECT $given from " . $ROS_table . " where id=?";

    $ROS = sqlStatement($query, array($form_id));
    while ($row = sqlFetchArray($ROS)) {
        foreach (explode(',', $given) as $item) {
            $PMSFH['ROS'][$item]['display'] = $row[$item];
        }
    }

    // translator will need to translate each item in $given
    $PMSFH['ROS']['ROSGENERAL']['short_title'] = xlt("GEN{{General}}");
    $PMSFH['ROS']['ROSHEENT']['short_title'] = xlt("HEENT");
    $PMSFH['ROS']['ROSCV']['short_title'] = xlt("CV{{Cardiovascular}}");
    $PMSFH['ROS']['ROSPULM']['short_title'] = xlt("PULM{{Pulmonary}}");
    $PMSFH['ROS']['ROSGI']['short_title'] = xlt("GI{{Gastrointestinal}}");
    $PMSFH['ROS']['ROSGU']['short_title'] = xlt("GU{{Genitourinary}}");
    $PMSFH['ROS']['ROSDERM']['short_title'] = xlt("DERM{{Dermatology}}");
    $PMSFH['ROS']['ROSNEURO']['short_title'] = xlt("NEURO{{Neurology}}");
    $PMSFH['ROS']['ROSPSYCH']['short_title'] = xlt("PSYCH{{Psychiatry}}");
    $PMSFH['ROS']['ROSMUSCULO']['short_title'] = xlt("ORTHO{{Orthopedics}}");
    $PMSFH['ROS']['ROSIMMUNO']['short_title'] = xlt("IMMUNO{{Immunology/Rheumatology}}");
    $PMSFH['ROS']['ROSENDOCRINE']['short_title'] = xlt("ENDO{{Endocrine}}");
    $PMSFH['ROS']['ROSCOMMENTS']['short_title'] = xlt("Comments");

    $PMSFH['ROS']['ROSGENERAL']['title'] = xlt("General");
    $PMSFH['ROS']['ROSHEENT']['title'] = xlt("HEENT");
    $PMSFH['ROS']['ROSCV']['title'] = xlt("Cardiovascular");
    $PMSFH['ROS']['ROSPULM']['title'] = xlt("Pulmonary");
    $PMSFH['ROS']['ROSGI']['title'] = xlt("GI{{Gastrointestinal}}");
    $PMSFH['ROS']['ROSGU']['title'] = xlt("GU{{Genitourinary}}");
    $PMSFH['ROS']['ROSDERM']['title'] = xlt("Dermatology");
    $PMSFH['ROS']['ROSNEURO']['title'] = xlt("Neurology");
    $PMSFH['ROS']['ROSPSYCH']['title'] = xlt("Pyschiatry");
    $PMSFH['ROS']['ROSMUSCULO']['title'] = xlt("Musculoskeletal");
    $PMSFH['ROS']['ROSIMMUNO']['title'] = xlt("Immune System");
    $PMSFH['ROS']['ROSENDOCRINE']['title'] = xlt("Endocrine");
    $PMSFH['ROS']['ROSCOMMENTS']['title'] = xlt("Comments");

    return array($PMSFH); //yowsah!
}
/**
 *  This function uses the complete PMSFH array for a given patient, including the ROS for this encounter
 *  and returns the PMSFH display square.
 *  @param integer rows is the number of rows you want to display
 *  @param option string view defaults to white on beige, versus right sliding panel (text on beige only).
 *  @param option string min_height to set min height for the row
 *  @return $display_PMSFH HTML pane when PMSFH is expanded to two panes.
 */
function display_PMSFH($rows, $view = "pending", $min_height = "min-height:344px;")
{
    global $PMSFH;
    global $pid;
    global $PMSFH_titles;
    if (!$PMFSH) {
        $PMSFH = build_PMSFH($pid);
    }

    ob_start();
    // There are two rows in our PMH section, only one in the side panel.
    // If you want it across the bottom in a panel with 8 rows?  Or other wise?
    // This should be able to handle that too.

    // We are building the PMSFH panel.
    // Let's put half in each of the 2 rows... or try to at least.
    // Find out the number of items present now and put half in each column.
    foreach ($PMSFH[0] as $key => $value) {
        $total_PMSFH += count($PMSFH[0][$key]);
        $total_PMSFH += 2; //add two for the title and a space
        $count[$key] = count($PMSFH[0][$key]) + 1;
    }

    //SOCH, FH and ROS are listed in $PMSFH even if negative, only count positives
    foreach ($PMSFH[0]['ROS'] as $key => $value) {
        if ($value['display'] == '') {
            $total_PMSFH--;
            $count['ROS']--;
        }
    }

    foreach ($PMSFH[0]['FH'] as $key => $value) {
        if ($value['display'] == '') {
            $total_PMSFH--;
            $count['FH']--;
        }
    }

    foreach ($PMSFH[0]['SOCH'] as $key => $value) {
        if (($value['display'] == '') || ($item['display'] == 'not_applicable')) {
            $total_PMSFH--;
            $count['SOCH']--;
        }
    }

    $counter = "0";
    $column_max = round($total_PMSFH / $rows) + 1;
    if ($column_max < "25") {
        $column_max = '20';
    }

    $open_table = "<div style='float:left' class='table PMSFH_table'>";
    $close_table = "</div>";
    // $div is used when $counter reaches $column_max and a new row is needed.
    // It is used only if $row_count <= $rows, ie. $rows -1 times.
    $div = '</div>
    <div id="PMSFH_block_2" name="PMSFH_block_2" class="QP_block_outer borderShadow text_clinical" style="' . attr($min_height) . '">';

    echo $header = '
            <div id="PMSFH_block_1" name="PMSFH_block_1" class="QP_block borderShadow text_clinical" style="' . attr($min_height) . ';">
             ';
    $row_count = 1;

    foreach ($PMSFH[0] as $key => $value) {
        if ($key == "FH" || $key == "SOCH" || $key == "ROS") {
            // We are going to build SocHx, FH and ROS separately below since they are different..
            continue;
        }

        $table = '';
        $header = '';
        $header .= '    <table class="PMSFH_header">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.9em;">' . xlt($key) . '</span>
                    </td>
                    <td>
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue2(\'0\',' . attr_js($key) . ',\'0\');" style="text-align:right;font-size:8px;">' . xlt("New") . '</span>
                    </td>
                </tr>
                </table>
        ';
        if (count($PMSFH[0][$key]) > '0') {
            $index = 0;
            foreach ($PMSFH[0][$key] as $item) {
                if (
                    ( ($key == "Medication") || ($key == "Eye Meds") )
                    &&
                        ($item['status'] == "Inactive")
                ) {
                    continue;
                }
                if (($key == "Medication") && ( !empty($item['row_subtype']))) {
                    $subtype_Meds[$item['row_subtype']]['name'] = $item['row_subtype'];
                    $subtype_Meds[$item['row_subtype']]['header'] = '
                        <table class="PMSFH_header">
                            <tr>
                                <td width="90%">
                                    <span class="left" style="font-weight:800;font-size:0.9em;">' . xlt(ucwords($item['row_subtype'])) . ' Meds</span>
                                </td>
                                <td>
                                    <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue2(\'0\',\'Eye Meds\',\'0\');" style="text-align:right;font-size:8px;">' . xlt("New") . '</span>
                                </td>
                            </tr>
                        </table>
                        ';
                    $subtype_Meds[$item['row_subtype']]['table'] .= "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
                            onclick=\"alter_issue2(" . attr_js($item['rowid']) . ",\"Eye Meds\"," . attr_js($index) . ");\">" . text($item['title']) . "</span><br />";
                    $index++;
                    continue;
                }

                if ($key == "Allergy") {
                    if ($item['reaction']) {
                        $reaction = " (" . text($item['reaction']) . ")";
                    } else {
                        $reaction = "";
                    }

                    $red = "style='color:red;'";
                } else {
                    $red = '';
                }

                $table .= "<span $red name='QP_PMH_" . $item['rowid'] . "' href='#PMH_anchor' id='QP_PMH_" . $item['rowid'] . "'
                onclick=\"alter_issue2(" . attr_js($item['rowid']) . "," . attr_js($key) . "," . attr_js($index) . ");\">" . text($item['title'] . $reaction) . "</span><br />";
                $index++;
            }
        } else {
            if ($key == "Allergy") {
                $table .= xlt("NKDA{{No known drug allergies}}");
            } else {
                $table .= xlt("None");
            }

            $counter++;
        }

        $display_PMSFH[$key] = $header . $open_table . $table . $close_table;
    }

    echo $display_PMSFH['POH'];
    $count = $count['POH'] + $count['PMH'] + 4;
    if ($count >= $column_max) {
        echo $div . $header1;
    }

    echo $display_PMSFH['POS'];
    $count = $count + $count['POS'] + 4;
    if ($count >= $column_max) {
        echo $div . $header1;
    }

    echo $display_PMSFH['Eye Meds'];
    $count = $count + $count['Surgery'] +  4;
    if (($count >= $column_max) && ($row_count < $rows)) {
        echo $div;
        $count = 0;
        $row_count = 2;
    }

    echo $display_PMSFH['PMH'];
    $count = $count + $count['Surgery'] +  4;
    if (($count >= $column_max) && ($row_count < $rows)) {
        echo $div;
        $count = 0;
        $row_count = 2;
    }

    echo $display_PMSFH['Surgery'];

    $count = $count + $count['Medication'] + 4;
    if (($count >= $column_max) && ($row_count < $rows)) {
        echo $div;
        $count = 0;
        $row_count = 2;
    }

    echo $display_PMSFH['Medication'];

    $count = $count + $count['Allergy'] + 4;
    if (($count >= $column_max) && ($row_count < $rows)) {
        echo $div;
        $count = 0;
        $row_count = 2;
    }

    echo $display_PMSFH['Allergy'];

    $count = $count + $count['FH'] + 4;
    if (($count >= $column_max) && ($row_count < $rows)) {
        echo $div;
        $count = 0;
        $row_count = 2;
    } ?>
        <table class="PMSFH_header">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.9em;"><?php echo xlt("FH{{Family History}}"); ?></span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue2('0','FH','');" style="text-align:right;font-size:8px;"><?php echo xlt("New"); ?></span>
                    </td>
                </tr>
        </table>
        <?php
                echo $open_table;
                $mentions_FH = '';
        if (count($PMSFH[0]['FH']) > 0) {
            foreach ($PMSFH[0]['FH'] as $item) {
                if (($counter > $column_max) && ($row_count < $rows)) {
                    echo $close_table . $div . $open_table;
                    $counter = "0";
                    $row_count++;
                }

                if ($item['display'] > '') {
                    $counter++;
                    echo "<span name='QP_PMH_" . $item['rowid'] . "' href='#PMH_anchor' id='QP_PMH_" . $item['rowid'] . "'
                            onclick=\"alter_issue2('0','FH','');\">" . xlt($item['short_title']) . ": " . text($item['display']) . "</span><br />";
                    $mentions_FH++;
                }
            }
        }

        if ($mentions_FH < '1') { ?>
                <span href="#PMH_anchor"
        onclick="alter_issue2('0','FH','');" style="text-align:right;"><?php echo xlt("Negative"); ?></span><br />
                <?php
                $counter = $counter + 3;
        }

        echo $close_table;
        $count = $count + $count['SOCH'] + 4;

        if (($count > $column_max) && ($row_count < $rows)) {
            echo $div;
            $count = 0;
            $row_count = 2;
        } ?>
                <table class="PMSFH_header">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.9em;"><?php echo xlt("Social"); ?></span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue2('0','SOCH','');" style="text-align:right;font-size:8px;"><?php echo xlt("New"); ?></span>
                    </td>
                </tr>
                </table>
                <?php
                    echo $open_table;
                foreach ($PMSFH[0]['SOCH'] as $item) {
                    if (($counter > $column_max) && ($row_count < $rows)) {
                        echo $close_table . $div . $open_table;
                        $counter = "0";
                        $row_count++;
                    }

                    if (($item['display'] > '') && ($item['display'] != 'not_applicable')) {
                        echo "<span name='QP_PMH_" . $item['rowid'] . "' href='#PMH_anchor' id='QP_PMH_" . $item['rowid'] . "'
                                onclick=\"alter_issue2('0','SOCH','');\">" . xlt($item['short_title']) . ": " . text($item['display']) . "</span><br />";
                        $counter++;
                        $mentions_SOCH++;
                    }
                }

                if (!$mentions_SOCH) {
                    ?>
                    <span href="#PMH_anchor"
                    onclick="alter_issue2('0','SOCH','');" style="text-align:right;"><?php echo xlt("Not documented"); ?></span><br />
                    <?php
                    $counter = $counter + 2;
                }

                echo $close_table;
                $count = $count + $count['ROS'] + 4;

                if (($count > $column_max) && ($row_count < $rows)) {
                    echo $div;
                    $count = 0;
                    $row_count = 2;
                } ?>
            <table class="PMSFH_header">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.9em;"><?php echo xlt("ROS{{Review of Systems}}"); ?></span>
                    </td>
                    <td >
                        <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue2('0','ROS','');" style="text-align:right;font-size:8px;"><?php echo xlt("New"); ?></span>
                    </td>
                </tr>
            </table>
            <?php
                    echo $open_table;
            foreach ($PMSFH[0]['ROS'] as $item) {
                if ($item['display'] > '') {
                    if (($counter > $column_max) && ($row_count < $rows)) {
                        echo $close_table . $div . $open_table;
                        $counter = "0";
                        $row_count++;
                    }

                    //xlt($item['short_title']) - for a list of short_titles, see the predefined ROS categories
                    echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
                             onclick=\"alter_issue2('0','ROS','');\">" . xlt($item['short_title']) . ": " . text($item['display']) . "</span><br />";
                    $mention++;
                    $counter++;
                }
            }

            if ($mention < 1) {
                echo  xlt("Negative") . "<br />";
                $counter = $counter++;
            }

                    echo $close_table;
            ?>
        </div>
            <?php
            $PMH_panel = ob_get_contents();
            ob_end_clean();
            return $PMH_panel;
}

/**
 *  This function uses the complete PMSFH array for a given patient, including the ROS for this encounter
 *  and returns the PMSFH/ROS sliding Right Panel
 *
 *  @param array $PMSFH
 *  @return $right_panel html
 */
function show_PMSFH_panel($PMSFH, $columns = '1')
{
    global $pcp_data;
    global $ref_data;
    ob_start();

    //<!-- POH -->
    echo "<br /><span class='panel_title' title='" . xla('Past Ocular History') . "'>" . xlt("POH{{Past Ocular History}}") . ":</span>";
    ?>
    <span class="top-right btn-sm" href="#PMH_anchor"
        onclick="alter_issue2('0','POH','');"
        style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?></span>
    <br />
    <?php
    if ($PMSFH[0]['POH']) {
        $i = 0;
        foreach ($PMSFH[0]['POH'] as $item) {
            echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
            onclick=\"alter_issue2(" . attr_js($item['rowid']) . ",'POH','" . $i . "');\">" . text($item['title']) . "</span><br />";
            $i++;
        }
    } else { ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','POH','');" class="disabled_button"><?php echo xlt("None"); ?><br /></span>
        <?php
    }

    //<!-- POS -->
    echo "<br /><span class='panel_title' title='" . xla('Past Ocular Surgery') . "'>" . xlt("POS{{Past Ocular Surgery}}") . ":</span>";
    ?>
    <span class="top-right btn-sm" href="#PMH_anchor"
        onclick="alter_issue2('0','POS','');"
        style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?></span>
    <br />
    <?php
    if ($PMSFH[0]['POS']) {
        $i = 0;
        foreach ($PMSFH[0]['POS'] as $item) {
            echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
            onclick=\"alter_issue2(" . attr_js($item['rowid']) . ",'POS','" . $i . "');\">" . text($item['title']) . "</span><br />";
            $i++;
        }
    } else { ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','POS','');" class="disabled_button"><?php echo xlt("None"); ?><br /></span>
        <?php
    }

//<!-- Eye Meds -->
    echo "<br /><span class='panel_title' title='" . xla("Eye Meds") . "'>" . xlt("Eye Meds") . ":</span>";
    ?><span class="top-right btn-sm" href="#PMH_anchor"
            onclick="alter_issue2('0','Eye Meds','');" style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?></span>
    <br />
    <?php
    if ($PMSFH[0]['Eye Meds']) {
         $i = 0;
        foreach ($PMSFH[0]['Eye Meds'] as $item) {
            if (($item['status'] == "Inactive") || ($item['row_subtype'] != "eye")) {
                $i++;
                continue;
            }
            echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
            onclick=\"alter_issue2(" . attr_js($item['rowid']) . ",'Eye Meds','" . $i . "');\" > " . text($item['title']) . "</span><br />";
            $i++;
        }
    } else { ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','Medication','');" class="disabled_button"><?php echo xlt("None"); ?><br /></span>
        <?php
    }

    //<!-- PMH -->
    echo "<br /> <span class='panel_title' title='" . xla('Past Medical History') . "'>" . xlt("PMH{{Past Medical History}}") . ":</span>";
    ?><span class="top-right btn-sm" href="#PMH_anchor"
    onclick="alter_issue2('0','PMH','');" style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?></span>
    <br />
    <?php
    if ($PMSFH[0]['PMH']) {
        $i = 0;
        foreach ($PMSFH[0]['PMH'] as $item) {
            if ($item['enddate'] !== " ") {
                echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
            onclick=\"alter_issue2(" . attr_js($item['rowid']) . ",'PMH','" . $i . "');\">" . text($item['title']) . "</span><br />";
                $i++;
            }
        }
    } else { ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','PMH','');" class="disabled_button"><?php echo xlt("None"); ?><br /></span>
        <?php
    }

    //<!-- Surgeries -->
    echo "<br /><span class='panel_title' title='" . xla("Past Surgical History") . "'>" . xlt("Surgery") . ":</span>";
    ?><span class="top-right btn-sm" href="#PMH_anchor"
    onclick="alter_issue2('0','Surgery','');" style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?></span>
    <br />
    <?php
    if ($PMSFH[0]['Surgery']) {
        $i = 0;
        foreach ($PMSFH[0]['Surgery'] as $item) {
            echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
            onclick=\"alter_issue2(" . attr_js($item['rowid']) . ",'Surgery','" . $i . "');\">" . text($item['title']) . "<br /></span>";
            $i++;
        }
    } else { ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','Surgery','');" class="disabled_button"><?php echo xlt("None"); ?><br /></span>
        <?php
    }

    //<!-- Meds -->
    echo "<br /><span class='panel_title' title='" . xla("Medications") . "'>" . xlt("Medication") . ":</span>";
    ?><span class="top-right btn-sm" href="#PMH_anchor"
    onclick="alter_issue2('0','Medication','');" style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?></span>
    <br />
    <?php
    if ($PMSFH[0]['Medication']) {
        $i = 0;
        foreach ($PMSFH[0]['Medication'] as $item) {
            if (($item['row_subtype'] == "eye") || ($item['status'] == "Inactive")) {
                $i++;
                continue;
            }
            echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
            onclick=\"alter_issue2(" . attr_js($item['rowid']) . ",'Medication','" . $i . "');\">" . text($item['title']) . "</span><br />";
            $i++;
        }
    } else { ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','Medication','');" class="disabled_button"><?php echo xlt("None"); ?><br /></span>
        <?php
    }


    //<!-- Allergies -->
    echo "<br /><span class='panel_title' title='" . xla("Allergies") . "'>" . xlt("Allergy") . ":</span>";
    ?><span class="top-right btn-sm" href="#PMH_anchor"
    onclick="alter_issue2('0','Allergy','');" style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?></span>
    <br />
    <?php
    if ($PMSFH[0]['Allergy']) {
        $i = 0;
        foreach ($PMSFH[0]['Allergy'] as $item) {
            if ($item['reaction']) {
                $reaction = "(" . text($item['reaction']) . ")";
            } else {
                $reaction = "";
            }

            echo "<span style='color:red;' name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
      onclick=\"alter_issue2(" . attr_js($item['rowid']) . ",'Allergy','" . $i . "');\">" . text($item['title']) . " " . $reaction . "</span><br />";
            $i++;
        }
    } else { ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','Allergy','');" ><?php echo xlt("NKDA{{No known drug allergies}}"); ?><br /></span>
        <?php
    }

       //<!-- Social History -->
    echo "<br /><span class='panel_title' title='" . xla("Social History") . "'>" . xlt('Soc Hx{{Social History}}') . ":</span>";
    ?><span class="top-right btn-sm" href="#PMH_anchor"
    onclick="alter_issue2('0','SOCH','');" style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?>
    </span><br />
    <?php
    foreach ($PMSFH[0]['SOCH'] as $k => $item) {
        if (($item['display']) && ($item['display'] != 'not_applicable')) {
            echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
        onclick=\"alter_issue2('0','SOCH','');\">" . xlt($item['short_title']) . ": " . text($item['display']) . "<br /></span>";

            $mention_SOCH++;
        }
    }

    if (!$mention_SOCH) {
        ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','SOCH','');" class="disabled_button"><?php echo xlt("Negative"); ?><br /></span>
        <?php
    }

    //<!-- Family History -->
    echo "<br /><span class='panel_title' title='" . xla("Family History") . "'>" . xlt("FH{{Family History}}") . ":</span>";
    ?><span class="top-right btn-sm" href="#PMH_anchor"
    onclick="alter_issue2('0','FH','');" style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?></span><br />

    <?php
    if (count($PMSFH[0]['FH']) > 0) {
        foreach ($PMSFH[0]['FH'] as $item) {
            if ($item['display'] > '') {
                echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
                onclick=\"alter_issue2('0','FH','');\">" . xlt($item['short_title']) . ": " . text($item['display']) . "<br /></span>";
                $mention_FH++;
            }
        }
    }

    if (!$mention_FH) {
        ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','FH','');" class="disabled_button"><?php echo xlt("Negative"); ?><br /></span>
        <?php
    }

    echo "<br /><span class='panel_title' title='" . xla("Review of Systems") . "'>" . xlt("ROS{{Review of Systems}}") . ":</span>";
    ?><span class="top-right btn-sm" href="#PMH_anchor"
    onclick="alter_issue('0','ROS','');" style="text-align:right;font-size:8px;"><?php echo xlt("Add"); ?></span>
    <br />
    <?php
    foreach ($PMSFH[0]['ROS'] as $item) {
        if ($item['display']) {
            echo "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
            onclick=\"alter_issue2('0','ROS','');\">" . text($item['short_title']) . ": " . text($item['display']) . "</span><br />";
            $mention_ROS++;
        }
    }

    if (!$mention_ROS) { ?>
        <span href="#PMH_anchor"
        onclick="alter_issue2('0','ROS','');" class="disabled_button"><?php echo xlt('Negative'); ?><br /></span>
        <?php
    }

    echo "<br /><br /><br />";
        $right_panel = ob_get_contents();

    ob_end_clean();
    return $right_panel;
}

/**
 *  This function displays via echo the PMSFH/ROS in the report
 *
 *  @param array $PMSFH
 *
 */
function show_PMSFH_report($PMSFH)
{
    global $pid;
    global $ISSUE_TYPES;

    //4 panels
    $rows = '4';
    if (!$PMFSH) {
        $PMSFH = build_PMSFH($pid);
    }

    // Find out the number of items present now and put 1/4 in each column.
    foreach ($PMSFH[0] as $key => $value) {
        $total_PMSFH += count($PMSFH[0][$key]);
        $total_PMSFH += 2; //add two for the title and a space
        $count[$key] = count($PMSFH[0][$key]) + 1;
    }

    //SOCH, FH and ROS are listed in $PMSFH even if negative, only count positives
    foreach ($PMSFH[0]['ROS'] as $key => $value) {
        if ($value['display'] == '') {
            $total_PMSFH--;
            $count['ROS']--;
        }
    }

    foreach ($PMSFH[0]['FH'] as $key => $value) {
        if ($value['display'] == '') {
            $total_PMSFH--;
            $count['FH']--;
        }
    }

    foreach ($PMSFH[0]['SOCH'] as $key => $value) {
        if (($value['display'] == '') || ($value['display'] == 'not_applicable')) {
            $total_PMSFH--;
            $count['SOCH']--;
        }
    }

    $counter = "0";
    $column_max = round($total_PMSFH / $rows) ;
    $panel_size = round($total_PMSFH / $rows) ;

    //<!-- POH -->
    $counter++;
    $counter++;
    echo "<table style='width:700px;'>
                <tr>
                    <td style='vertical-align:top;
                               width:150px;
                               padding-left: 14px;
                               padding-right: 4px;
                               text-align: left;'>
                               <br /><span style='font-weight:bold;'>" . xlt("POH{{Past Ocular History}}") . ":</span>";
    //note the HTML2PDF does not like <span style="font-weight:bold;"></span> so we are using the deprecated <b></b>
    ?>
    <br />
    <?php
    if ($PMSFH[0]['POH'] > "") {
        foreach ($PMSFH[0]['POH'] as $item) {
            echo text($item['title']) . " " . text($item['diagnosis']) . "<br />";
            $counter++;
        }
    } else {
        echo xlt("None") . "<br />";
    }

    if (($counter + $count['POS']) > $panel_size) {
        echo "</td><td style='padding-left: 14px;
                              padding-right: 4px;
                              text-align: left;
                              vertical-align:top;
                              width:150px;'>";
        $counter = "0";
    }

    $counter++;
    $counter++;
    //<!-- PMH -->
    echo "<br /><span style='font-weight:bold;'>" . xlt("Eye Surgery") . ":</span>";
    ?>
    <br />
    <?php
    if ($PMSFH[0]['POS'] > "") {
        foreach ($PMSFH[0]['POS'] as $item) {
            echo text($item['title']) . " " . text($item['diagnosis']) . "<br />";
            $counter++;
        }
    } else {
        echo xlt("None") . "<br />";
    }

    if (($counter + $count['PMH']) > $panel_size) {
        echo "</td><td style='padding-left: 14px;
                                padding-right: 4px;
                                text-align: left;
                                vertical-align:top;
                                width:150px;'>";
        $counter = "0";
    }

    $counter++;
    $counter++;
    //<!-- PMH -->
    echo "<br /><span style='font-weight:bold;'>" . xlt("PMH") . ":</span>";
    ?>
    <br />
    <?php
    if ($PMSFH[0]['PMH'] > "") {
        foreach ($PMSFH[0]['PMH'] as $item) {
            echo text($item['title']) . " " . text($item['diagnosis']) . "<br />";
            $counter++;
        }
    } else {
        echo xlt("None") . "<br />";
    }


    if ($counter + $count['Medication'] > $panel_size) {
        echo "</td><td style='padding-left: 14px;
                              padding-right: 4px;
                              text-align: left;
                              vertical-align:top;
                              width:150px;'>";
        $counter = "0";
    }

    $counter++;
    $counter++;
    //<!-- Meds -->
    echo "<br /><span style='font-weight:bold;'>" . xlt("Medication") . ":</span>";
    ?>
    <br />
    <?php
    if ($PMSFH[0]['Medication'] > "") {
        foreach ($PMSFH[0]['Medication'] as $item) {
            echo text($item['title']) . " " . text($item['diagnosis']) . "<br />";
            $counter++;
        }
    } else {
        echo xlt("None") . "<br />";
    }

    if ($counter + $count['Surgery'] > $panel_size) {
        echo "</td><td style='padding-left: 14px;
                              padding-right: 4px;
                              text-align: left;
                              vertical-align:top;
                              width:150px;'>";
        $counter = "0";
    }

    //<!-- Surgeries -->
    $counter++;
    $counter++;
    echo "<br /><span style='font-weight:bold;'>" . xlt("Surgery") . ":</span>";
    ?><br />
    <?php
    if ($PMSFH[0]['Surgery'] > "") {
        foreach ($PMSFH[0]['Surgery'] as $item) {
            echo text($item['title']) . " " . text($item['diagnosis']) . "<br />";
            $counter++;
        }
    } else {
        echo xlt("None") . "<br />";
    }

    if ($counter + $count['Allergy'] > $panel_size) {
        echo "</td><td style='padding-left: 14px;
                                padding-right: 4px;
                                text-align: left;
                                vertical-align:top;
                                width:150px;'>";
        $counter = "0";
    }

    $counter++;
    $counter++;
    //<!-- Allergies -->
    echo "<br /><span style='font-weight:bold;'>" . xlt("Allergy") . ":</span>";
    ?>
    <br />
    <?php
    if ($PMSFH[0]['Allergy'] > "") {
        foreach ($PMSFH[0]['Allergy'] as $item) {
            echo text($item['title']) . "<br />";
            $counter++;
        }
    } else {
        echo xlt("NKDA{{No known drug allergies}}") . "<br />";
    }

    if ($counter + $count['SOCH'] > $panel_size) {
        echo "</td><td style='padding-left: 14px;
                              padding-right: 4px;
                              text-align: left;
                              vertical-align:top;
                              width:150px;'>";
        $counter = "0";
    }

    $counter++;
    $counter++;
    //<!-- SocHx -->
    echo "<br /><span style='font-weight:bold;'>" . xlt("Soc Hx{{Social History}}") . ":</span>";
    ?>
    <br />
    <?php
    foreach ($PMSFH[0]['SOCH'] as $k => $item) {
        if (($item['display']) && ($item['display'] != 'not_applicable')) {
            echo xlt($item['short_title']) . ": " . text($item['display']) . "<br />";
            $mention_PSOCH++;
            $counter++;
        }
    }

    if (!$mention_PSOCH) {
        echo xlt("Negative") . "<br />";
    }

    if (($counter + $count['FH']) > $panel_size) {
        echo "</td><td style='padding-left: 14px;
                              padding-right: 4px;
                              text-align: left;
                              vertical-align:top;
                              width:150px;'>";
        $counter = "0";
    }

    $counter++;
    $counter++;
    //<!-- FH -->
    echo "<br /><span style='font-weight:bold;'>" . xlt("FH{{Family History}}") . ":</span>";
    ?>
    <br />
    <?php
    foreach ($PMSFH[0]['FH'] as $item) {
        if ($item['display']) {
            echo xlt($item['short_title']) . ": " . text($item['display']) . "<br />";
            $mention_FH++;
            $counter++;
        }
    }

    if (!$mention_FH) {
        echo xlt("Negative") . "<br />";
    }

    if (($counter !== "0") && (($counter + $count['ROS']) > $panel_size)) {
        echo "</td><td style='padding-left: 14px;
                                padding-right: 4px;
                                text-align: left
                                vertical-align:top;
                                width:150px;'>";
        $counter = "0";
    }

    $counter++;
    $counter++;
    //<!-- ROS -->
    echo "<br /><span style='font-weight:bold;'>" . xlt("ROS{{Review of Systems}}") . ":</span>";
    ?><br />
    <?php
    foreach ($PMSFH[0]['ROS'] as $item) {
        if ($item['display']) {
            echo xlt($item['short_title']) . ": " . $item['display'] . "<br />";
            $mention_ROS++;
            $counter++;
        }
    }

    if ($mention_ROS < '1') {
        echo xlt("Negative");
    }

    echo "</td></tr></table>";
}

/**
 *  This function returns the Provider-specific Quick Pick selections for a zone (2 input values)
 *
 *  These selctions are draw from an openEMR list, Eye_QP_$zone_$provider_id.
 *  This list is created from Eye_QP_$zone_defaults when a new provider opens the form.
 *  Because it is a "list", the end-user can modify it.
 *  A link to the list "the pencil icon" is provided to allow customization - displayed in RTop frame.
 *  If frames are ever removed, this will need to be reworked.
 *
 *  @param string $zone options EXT,ANTSEG,RETINA,NEURO
 *  @param string $provider_id
 *  @return QP text: when called directly outputs the ZONE QP lists for this provider
 */
function display_QP($zone, $provider_id)
{
    global $prov_data;
    if (!$zone || !$provider_id) {
        return;
    }

    ob_start();
    $query  = "SELECT * FROM list_options where list_id =?  ORDER BY seq";
    $result = sqlStatement($query, array("Eye_QP_" . $zone . "_$provider_id"));
    if (sqlNumRows($result) < '1') {
        //this provider's list has not been created yet.
        $query = "REPLACE INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('lists', ?, ?, '0', '1', '0')";
        sqlStatement($query, array('Eye_QP_' . $zone . '_' . $provider_id,'Eye QP List ' . $zone . ' for ' . $prov_data['lname']));
        $query = "SELECT * FROM list_options where list_id =? ORDER BY seq";
        $result = sqlStatement($query, array("Eye_QP_" . $zone . "_defaults"));
        $SQL_INSERT = "INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `mapping`, `notes`, `codes`, `activity`, `subtype`) VALUES (?,?,?,?,?,?,?,?,?)";
    } else {
        $SQL_INSERT = '';
    }

    while ($QP = sqlFetchArray($result)) {
        if (!empty($SQL_INSERT)) {
            sqlStatement($SQL_INSERT, array("Eye_QP_" . $zone . "_" . $provider_id,$QP['option_id'],$QP['title'],$QP['seq'],$QP['mapping'],$QP['notes'],$QP['codes'],$QP['activity'],$QP['subtype']));
        }

        $here[$QP['title']][$QP['subtype']]['notes']    = $QP['notes'];     //the text to fill into form
        $here[$QP['title']][$QP['subtype']]['codes']    = $QP['codes'];     //the code if attached.
        $here[$QP['title']][$QP['subtype']]['mapping']  = $QP['mapping'];   //the fieldname without laterality eg CONJ
        $here[$QP['title']][$QP['subtype']]['activity'] = $QP['activity'];  //1 to replace, 2 to append
    }

    foreach ($here as $title => $values) { //start QP section items
        $title_show = (strlen($title) > 19) ? substr($title, 0, 16) . '...' : $title;
        if (preg_match('/clear field/', $title)) {
            $title_show = "<em><strong>$title</strong></em>";
        }

        if ($values['OD']) {
            if ($values['OD']['activity'] == '0') {
                $action = "ADD";
            }

            if ($values['OD']['activity'] == '1') {
                $action = "REPLACE" ;
            }

            if ($values['OD']['activity'] == '2') {
                $action = "APPEND" ;
            }
            ?>
            <span>
                <a class="underline QP" onclick="fill_QP_field('<?php echo attr($zone); ?>','OD','<?php echo attr($values['OD']['mapping']); ?>','<?php echo attr($values['OD']['notes']); ?>','<?php echo attr($action); ?>');"><?php echo xlt('OD{{right eye}}'); ?></a> |
                <a class="underline QP" onclick="fill_QP_field('<?php echo attr($zone); ?>','OS','<?php echo attr($values['OS']['mapping']); ?>','<?php echo attr($values['OS']['notes']); ?>','<?php echo attr($action); ?>');"><?php echo xlt('OS{{left eye}}'); ?></a> |
                <a class="underline QP" onclick="fill_QP_2fields('<?php echo attr($zone); ?>','OU','<?php echo attr($values['OU']['mapping']); ?>','<?php echo attr($values['OU']['notes']); ?>','<?php echo attr($action); ?>');"><?php echo xlt('OU{{both eyes}}'); ?></a>
            </span>
            &nbsp;
            <?php
        } elseif ($values['R']) {
            if ($values['R']['activity'] == '0') {
                $action = "ADD";
            }

            if ($values['R']['activity'] == '1') {
                $action = "REPLACE" ;
            }

            if ($values['R']['activity'] == '2') {
                $action = "APPEND" ;
            }
            ?>
            <span>
                <a class="underline QP" onclick="fill_QP_field('<?php echo attr($zone); ?>','R','<?php echo attr($values['R']['mapping']); ?>','<?php echo attr($values['R']['notes']); ?>','<?php echo attr($action); ?>');"><?php echo xlt('R{{right side}}'); ?></a> |
                <a class="underline QP" onclick="fill_QP_field('<?php echo attr($zone); ?>','L','<?php echo attr($values['L']['mapping']); ?>','<?php echo attr($values['L']['notes']); ?>','<?php echo attr($action); ?>');"><?php echo xlt('L{{left side}}'); ?></a> |
                <a class="underline QP" onclick="fill_QP_2fields('<?php echo attr($zone); ?>','B','<?php echo attr($values['B']['mapping']); ?>','<?php echo attr($values['B']['notes']); ?>','<?php echo attr($action); ?>');"><?php echo xlt('B{{both sides}}'); ?></a>
            </span>
            &nbsp;
            <?php
        }

        echo $title_show;
        $number_rows++;
        ?><br />
        <?php
        if ($number_rows == 19) {  ?>
          </div>
          <div class="QP_block_outer borderShadow text_clinical" ><?php
        }

        if ($number_rows == 38) {
            break;
        }
    } //end QP section items
    ?>
      <a href="JavaScript:void(0);"
         onclick="openNewForm('<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=Eye_QP_<?php echo attr($zone) . "_" . attr($provider_id); ?>','QP Editor');"
         title="<?php echo xla('Click here to Edit this Doctor\'s Quick Pick list'); ?>"
         name="provider_todo"
         class="bold black"><i class="closeButton float-right fa fa-pencil-alt fa-fw"></i> </a>
        <?php
        $QP_panel = ob_get_contents();
        ob_end_clean();
        return $QP_panel;
}

function canvas_select($zone, $encounter, $pid)
{
    /* This will provide a way to scroll back through prior VISIT images, to copy forward to today's visit,
     * just like we do in the text fields.
     * Will need to do a lot of thinking to create this.  Jist is ajax call to server for image retrieval.
     * To get this to work we need a way to select an old image to work from, use current or return to baseline.
     * This will require a global BACK button like above (BUTTON_BACK_<?php echo attr($zone); ?>).
     * The Undo Redo buttons are currently javascript client side.
     * The Undo Redo features will only work for changes made since form was loaded locally.

     * If we want to look back at a prior VISITs saved final images,
     * we will need to create this logic.
     * Need to think about how to display this visually so it's intuitive, without cluttering the page...
     * At first glance, using the text PRIORS selection method should work...  Not yet.
     *
     *
     *  $documents['categories']=$categories;
     *  $documents['my_name']=$my_name;
     *  $documents['children_names']=$children_names;
     *  $documents['parent_name'] = $parent_name;
     *  $documents['zones'] = $zones;
     *  $documents['docs_in_zone'] = $docs_in_zone;
     *  $documents['docs_in_cat_id'] = $docs_in_cat_id;
     *  $documents['docs_in_name'] = $docs_in_name;
     *  $documents['docs_by_date'] = $docs_by_date;
     *
     * Let's try $documents['docs_in_name'] where ['name']['zone']
     */
    //iterate through documents?
        // which are in this zone?
    //are any from the same as the encounter?  If so selected=selected
    //
    global $documents;
    $side = "OU";
    $type_name = $side . "_" . $zone . "_VIEW";
    $canvi = [];
    if (!empty($documents['zones'][$zone]) && !empty($documents['docs_in_name']['Drawings'])) {
        foreach ($documents['docs_in_name']['Drawings'] as $doc) {
            if (!preg_match("/" . $zone . "_VIEW/", $doc['name'])) {
                continue;
            }
            if (!$doc['encounter_id']) {
                continue;
            }
            $canvi[] = $doc;
        }
    }
    usort($canvi, function ($a, $b) {
        return $b['encounter_date'] <=> $a['encounter_date'];
    });
    if (!empty($canvi)) {
        if ($canvi[0]['encounter_id'] != $encounter) {
            //put today on the front as current, item "0"
            //echo "<pre style='text-align:left;'>".$canvi[0]['id'] ." and ". $encounter['encounter_id'];var_dump($canvi);echo "</pre>";
            //$today_doc = ["Hi"];
            $today_doc['encounter_date'] = 'New';
            array_unshift($canvi, $today_doc);
        }
   //
        if (count($canvi) > '1') {
            $select = '<div class="">';
            $select .= '<span id="old_canvas_leftest_' . attr($zone) . '"
                        name="old_canvas_leftest"
                        class="fa fa-fast-backward fa-sm PRIORS hand"
                        data-target="SELECT_CANVAS_' . attr($zone) . '"
                        data-direction="oldest"
                        title="' . xla('Oldest drawing') . '"></span>';
            $select .= '<span id="old_canvas_left_' . attr($zone) . '"
                        name="old_canvas"
                        class="fa fa-step-backward fa-sm PRIORS hand"
                        data-target="SELECT_CANVAS_' . attr($zone) . '"
                        data-direction="older"
                        title="' . xla('Look back one drawing') . '"></span>';

            $select .= "<select id='SELECT_CANVAS_" . attr($zone) . "' name='CANVAS_selector' data-step='0'>";
            $count = '0';
            foreach ($canvi as $hit) {
                if ($count == '0') {
                    $select .= "<option value='current'>" . text($hit['encounter_date']) . "</option>\n";
                } else {
                    $select .= "<option value='" . attr($hit['document_id']) . "'>" . text($hit['encounter_date']) . "</option>\n";
                }
                $count++;
            }
            $select .= "</select>";
            $select .= '<span id="old_canvas_right_' . attr($zone) . '"
                    name="old_canvas"
                    class="fa  fa-step-forward PRIORS hand"
                    data-target="SELECT_CANVAS_' . attr($zone) . '"
                    data-direction="newer"
                    data-step="1"
                    title="' . xla('Forward one drawing') . '"></span>';
            $select .= '<span id="old_canvas_rightest_' . attr($zone) . '"
                    name="old_canvas_rightest"
                    class="fa fa-fast-forward PRIORS hand"
                    data-target="SELECT_CANVAS_' . attr($zone) . '"
                    data-direction="newest"
                    title="' . xla('Forward to current canvas') . '"></span>

        </div>';
        }
    }
    return $select;
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
 *  @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO
 *  @param string $visit_date Future functionality to limit result set. UTC DATE Formatted
 *  @param string $pid value = patient id
 *  @param string OU by default.  Future functionality will allow OD and OS values- not implemented yet.
 *  @return true : when called directly outputs the ZONE specific HTML5 CANVAS widget
 */
function display_draw_section($zone, $encounter, $pid, $side = 'OU', $counter = '')
{
    global $form_folder;
    $filepath = $GLOBALS['oer_config']['documents']['repository'] . $pid . "/";
    $base_name = $pid . "_" . $encounter . "_" . $side . "_" . $zone . "_VIEW";

    $file_history =  $filepath . $base_name;
    $file_store = $file_history . ".jpg";
    ?>
    <div id="Draw_<?php echo attr($zone); ?>" name="Draw_<?php echo attr($zone); ?>" style="text-align:center;height: 2.5in;" class="Draw_class canvas">
        <span class="far fa-file-alt closeButton" id="BUTTON_TEXT_<?php echo attr($zone); ?>" name="BUTTON_TEXT_<?php echo attr($zone); ?>"></span>
        <i class="closeButton_2 fas fa-database" id="BUTTON_QP_<?php echo attr($zone); ?>_2" name="BUTTON_QP_<?php echo attr($zone); ?>"></i>
        <i class="closeButton_3 fas fa-user-md fa-sm fa-2" name="Shorthand_kb" title="<?php echo xla("Open the Shorthand Window and display Shorthand Codes"); ?>"></i>

        <?php
            $output = canvas_select($zone, $encounter, $pid);
            echo $output;
        ?>
        <div id="<?php echo attr($zone); ?>_canvas">
            <div class="tools">
                <div id="sketch_tooled_<?php echo attr($zone); ?>_8">
                    <span id="sketch_tool_<?php echo attr($zone);?>_color"
                          class="color_indicator jscolor"
                          data-jscolor="{ previewElement:'#sketch_tool_<?php echo attr($zone); ?>_color',
                                     previewSize:75,
                                     valueElement:'#selColor_<?php echo attr($zone); ?>',
                                     position:'right',
                                     value:'#000',
                                     hash:'true'
                                    }"
                          data-target="selColor_<?php echo attr($zone); ?>"
                    ></span>
                </div>
                <?php

                $sql = "SELECT * from documents where name like ? ORDER by id DESC";
                $doc = sqlQuery($sql, array("%" . $base_name . "%"));
                $base_filetoshow = $GLOBALS['web_root'] . "/interface/forms/" . $form_folder . "/images/" . $side . "_" . $zone . "_BASE.jpg";
                if (($doc['id'] > '0')) {
                    $filetoshow = $GLOBALS['web_root'] . "/controller.php?document&retrieve&patient_id=" . attr($pid) . "&document_id=" . attr($doc['id']) . "&as_file=false&show_original=true&blahblah=" . rand();
                } else {
                    //base image.
                    $filetoshow = $base_filetoshow;
                }
                ?>

                <input type="hidden" id="url_<?php echo attr($zone); ?>" name="url_<?php echo attr($zone); ?>" value="<?php echo $filetoshow; ?>" />
                <input type="hidden" id="base_url_<?php echo attr($zone); ?>" name="base_url_<?php echo attr($zone); ?>" value="<?php echo $base_filetoshow; ?>" />
                <input type="hidden" id="selWidth_<?php echo attr($zone); ?>" value="1">
                <input type="hidden" id="selColor_<?php echo attr($zone); ?>" value="#000" />



                <img id="sketch_tools_<?php echo attr($zone); ?>_1" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#1AA2E1");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_blue.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_2" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#ff0");'  src="../../forms/<?php echo $form_folder; ?>/images/pencil_yellow.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_3" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#ffad00");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_orange.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_4" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#AC8359");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_brown.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_5" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#E10A17");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_red.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_6" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#000");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_black.png" style="height:50px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_7" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#fff");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_white.png" style="height:30px;width:15px;">

                <span style="min-width:1in;">&nbsp;</span>
                <!-- now to pencil size -->
                <img id="sketch_sizes_<?php echo attr($zone); ?>_1" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("1");' src="../../forms/<?php echo $form_folder; ?>/images/brush_1.png" style="height:20px;width:20px; border-bottom: 2pt solid black;">
                <img id="sketch_sizes_<?php echo attr($zone); ?>_3" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("3");' src="../../forms/<?php echo $form_folder; ?>/images/brush_3.png" style="height:20px;width:20px;">
                <img id="sketch_sizes_<?php echo attr($zone); ?>_5" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("5");' src="../../forms/<?php echo $form_folder; ?>/images/brush_5.png" style="height:20px;width:20px;">
                <img id="sketch_sizes_<?php echo attr($zone); ?>_10" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("10");' src="../../forms/<?php echo $form_folder; ?>/images/brush_10.png" style="height:20px;width:20px;">
                <img id="sketch_sizes_<?php echo attr($zone); ?>_15" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("15");' src="../../forms/<?php echo $form_folder; ?>/images/brush_15.png" style="height:20px;width:20px;">
            </div>

            <div align="center" class="borderShadow">
                <canvas id="myCanvas_<?php echo attr($zone); ?>" name="myCanvas_<?php echo attr($zone); ?>" width="450" height="225"></canvas>
            </div>
            <div style="margin-top: 7px;">
                <button onclick="javascript:cUndo('<?php echo attr($zone); ?>');return false;" id="Undo_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("Undo"); ?></button>
                <button onclick="javascript:cRedo('<?php echo attr($zone); ?>');return false;" id="Redo_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("Redo"); ?></button>
                <button onclick="javascript:drawImage('<?php echo attr($zone); ?>');return false;" id="Revert_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("Revert"); ?></button>
                <button onclick="javascript:cReload('<?php echo attr($zone); ?>');return false;" id="Clear_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("New"); ?></button>
                <button id="Blank_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("Blank"); ?></button>
            </div>
        </div>
        <div id="<?php echo attr($zone); ?>_olddrawing"></div>
    </div>

    <?php
}

/**
 *  This function returns a JSON object to replace a requested section with copy_forward values (3 input values)
 *  It will not replace the drawings with older encounter drawings... Not yet anyway.
 *
 * @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO, EXT_DRAW, ANTSEG_DRAW, RETINA_DRAW, NEURO_DRAW
 * @param string $form_id is the form_eye_*.id where the data to carry forward is located
 * @param string $pid value = patient id
 * @return true : when called directly outputs the ZONE specific HTML for a prior record + widget for the desired zone
 */
function copy_forward($zone, $copy_from, $copy_to, $pid)
{
    global $form_id;

    $query = "select  *,form_encounter.date as encounter_date

               from forms,form_encounter,form_eye_base,
                form_eye_hpi,form_eye_ros,form_eye_vitals,
                form_eye_acuity,form_eye_refraction,form_eye_biometrics,
                form_eye_external,form_eye_antseg,form_eye_postseg,
                form_eye_neuro,form_eye_locking
                    where
                    forms.deleted != '1'  and
                    forms.formdir='eye_mag' and
                    forms.encounter=form_encounter.encounter and
                    forms.form_id=form_eye_base.id and
                    forms.form_id=form_eye_hpi.id and
                    forms.form_id=form_eye_ros.id and
                    forms.form_id=form_eye_vitals.id and
                    forms.form_id=form_eye_acuity.id and
                    forms.form_id=form_eye_refraction.id and
                    forms.form_id=form_eye_biometrics.id and
                    forms.form_id=form_eye_external.id and
                    forms.form_id=form_eye_antseg.id and
                    forms.form_id=form_eye_postseg.id and
                    forms.form_id=form_eye_neuro.id and
                    forms.form_id=form_eye_locking.id and
                    forms.pid =? and
                    forms.form_id =? ";

    $objQuery = sqlQuery($query, array($pid,$copy_from));
    if ($zone == "EXT") {
        $result['RUL'] = $objQuery['RUL'];
        $result['LUL'] = $objQuery['LUL'];
        $result['RLL'] = $objQuery['RLL'];
        $result['LLL'] = $objQuery['LLL'];
        $result['RBROW'] = $objQuery['RBROW'];
        $result['LBROW'] = $objQuery['LBROW'];
        $result['RMCT'] = $objQuery['RMCT'];
        $result['LMCT'] = $objQuery['LMCT'];
        $result['RADNEXA'] = $objQuery['RADNEXA'];
        $result['LADNEXA'] = $objQuery['LADNEXA'];
        $result['RMRD'] = $objQuery['RMRD'];
        $result['LMRD'] = $objQuery['LMRD'];
        $result['RLF'] = $objQuery['RLF'];
        $result['LLF'] = $objQuery['LLF'];
        $result['RVFISSURE'] = $objQuery['RVFISSURE'];
        $result['LVFISSURE'] = $objQuery['LVFISSURE'];
        $result['RCAROTID'] = $objQuery['RCAROTID'];
        $result['LCAROTID'] = $objQuery['LCAROTID'];
        $result['RTEMPART'] = $objQuery['RTEMPART'];
        $result['LTEMPART'] = $objQuery['LTEMPART'];
        $result['RCNV'] = $objQuery['RCNV'];
        $result['LCNV'] = $objQuery['LCNV'];
        $result['RCNVII'] = $objQuery['RCNVII'];
        $result['LCNVII'] = $objQuery['LCNVII'];
        $result['ODSCHIRMER1'] = $objQuery['ODSCHIRMER1'];
        $result['OSSCHIRMER1'] = $objQuery['OSSCHIRMER1'];
        $result['ODSCHIRMER2'] = $objQuery['ODSCHIRMER2'];
        $result['OSSCHIRMER2'] = $objQuery['OSSCHIRMER2'];
        $result['ODTBUT'] = $objQuery['ODTBUT'];
        $result['OSTBUT'] = $objQuery['OSTBUT'];
        $result['OSHERTEL'] = $objQuery['OSHERTEL'];
        $result['HERTELBASE'] = $objQuery['HERTELBASE'];
        $result['ODPIC'] = $objQuery['ODPIC'];
        $result['OSPIC'] = $objQuery['OSPIC'];
        $result['EXT_COMMENTS'] = $objQuery['EXT_COMMENTS'];
        $result["json"] = json_encode($result);
        echo json_encode($result);
    } elseif ($zone == "ANTSEG") {
        $result['OSCONJ'] = $objQuery['OSCONJ'];
        $result['ODCONJ'] = $objQuery['ODCONJ'];
        $result['ODCORNEA'] = $objQuery['ODCORNEA'];
        $result['OSCORNEA'] = $objQuery['OSCORNEA'];
        $result['ODAC'] = $objQuery['ODAC'];
        $result['OSAC'] = $objQuery['OSAC'];
        $result['ODLENS'] = $objQuery['ODLENS'];
        $result['OSLENS'] = $objQuery['OSLENS'];
        $result['ODIRIS'] = $objQuery['ODIRIS'];
        $result['OSIRIS'] = $objQuery['OSIRIS'];
        $result['ODKTHICKNESS'] = $objQuery['ODKTHICKNESS'];
        $result['OSKTHICKNESS'] = $objQuery['OSKTHICKNESS'];
        $result['ODGONIO'] = $objQuery['ODGONIO'];
        $result['OSGONIO'] = $objQuery['OSGONIO'];
        $result['ODSHRIMER1'] = $objQuery['ODSHIRMER1'];
        $result['OSSHRIMER1'] = $objQuery['OSSHIRMER1'];
        $result['ODSHRIMER2'] = $objQuery['ODSHIRMER2'];
        $result['OSSHRIMER2'] = $objQuery['OSSHIRMER2'];
        $result['ODTBUT'] = $objQuery['ODTBUT'];
        $result['OSTBUT'] = $objQuery['OSTBUT'];
        $result['ANTSEG_COMMENTS'] = $objQuery['ANTSEG_COMMENTS'];
        $result["json"] = json_encode($result);
        echo json_encode($result);
    } elseif ($zone == "RETINA") {
        $result['ODDISC'] = $objQuery['ODDISC'];
        $result['OSDISC'] = $objQuery['OSDISC'];
        $result['ODCUP'] = $objQuery['ODCUP'];
        $result['OSCUP'] = $objQuery['OSCUP'];
        $result['ODMACULA'] = $objQuery['ODMACULA'];
        $result['OSMACULA'] = $objQuery['OSMACULA'];
        $result['ODVESSELS'] = $objQuery['ODVESSELS'];
        $result['OSVESSELS'] = $objQuery['OSVESSELS'];
        $result['ODVITREOUS'] = $objQuery['ODVITREOUS'];
        $result['OSVITREOUS'] = $objQuery['OSVITREOUS'];
        $result['ODPERIPH'] = $objQuery['ODPERIPH'];
        $result['OSPERIPH'] = $objQuery['OSPERIPH'];
        $result['ODDRAWING'] = $objQuery['ODDRAWING'];
        $result['OSDRAWING'] = $objQuery['OSDRAWING'];
        $result['ODCMT'] = $objQuery['ODCMT'];
        $result['OSCMT'] = $objQuery['OSCMT'];
        $result['RETINA_COMMENTS'] = $objQuery['RETINA_COMMENTS'];
        $result["json"] = json_encode($result);
        echo json_encode($result);
    } elseif ($zone == "NEURO") {
        $result['ACT'] = $objQuery['ACT'];
        $result['ACT5CCDIST'] = $objQuery['ACT5CCDIST'];
        $result['ACT1CCDIST'] = $objQuery['ACT1CCDIST'];
        $result['ACT2CCDIST'] = $objQuery['ACT2CCDIST'];
        $result['ACT3CCDIST'] = $objQuery['ACT3CCDIST'];
        $result['ACT4CCDIST'] = $objQuery['ACT4CCDIST'];
        $result['ACT6CCDIST'] = $objQuery['ACT6CCDIST'];
        $result['ACT7CCDIST'] = $objQuery['ACT7CCDIST'];
        $result['ACT8CCDIST'] = $objQuery['ACT8CCDIST'];
        $result['ACT9CCDIST'] = $objQuery['ACT9CCDIST'];
        $result['ACT10CCDIST'] = $objQuery['ACT10CCDIST'];
        $result['ACT11CCDIST'] = $objQuery['ACT11CCDIST'];
        $result['ACT1SCDIST'] = $objQuery['ACT1SCDIST'];
        $result['ACT2SCDIST'] = $objQuery['ACT2SCDIST'];
        $result['ACT3SCDIST'] = $objQuery['ACT3SCDIST'];
        $result['ACT4SCDIST'] = $objQuery['ACT4SCDIST'];
        $result['ACT5SCDIST'] = $objQuery['ACT5SCDIST'];
        $result['ACT6SCDIST'] = $objQuery['ACT6SCDIST'];
        $result['ACT7SCDIST'] = $objQuery['ACT7SCDIST'];
        $result['ACT8SCDIST'] = $objQuery['ACT8SCDIST'];
        $result['ACT9SCDIST'] = $objQuery['ACT9SCDIST'];
        $result['ACT10SCDIST'] = $objQuery['ACT10SCDIST'];
        $result['ACT11SCDIST'] = $objQuery['ACT11SCDIST'];
        $result['ACT1SCNEAR'] = $objQuery['ACT1SCNEAR'];
        $result['ACT2SCNEAR'] = $objQuery['ACT2SCNEAR'];
        $result['ACT3SCNEAR'] = $objQuery['ACT3SCNEAR'];
        $result['ACT4SCNEAR'] = $objQuery['ACT4SCNEAR'];
        $result['ACT5CCNEAR'] = $objQuery['ACT5CCNEAR'];
        $result['ACT6CCNEAR'] = $objQuery['ACT6CCNEAR'];
        $result['ACT7CCNEAR'] = $objQuery['ACT7CCNEAR'];
        $result['ACT8CCNEAR'] = $objQuery['ACT8CCNEAR'];
        $result['ACT9CCNEAR'] = $objQuery['ACT9CCNEAR'];
        $result['ACT10CCNEAR'] = $objQuery['ACT10CCNEAR'];
        $result['ACT11CCNEAR'] = $objQuery['ACT11CCNEAR'];
        $result['ACT5SCNEAR'] = $objQuery['ACT5SCNEAR'];
        $result['ACT6SCNEAR'] = $objQuery['ACT6SCNEAR'];
        $result['ACT7SCNEAR'] = $objQuery['ACT7SCNEAR'];
        $result['ACT8SCNEAR'] = $objQuery['ACT8SCNEAR'];
        $result['ACT9SCNEAR'] = $objQuery['ACT9SCNEAR'];
        $result['ACT10SCNEAR'] = $objQuery['ACT10SCNEAR'];
        $result['ACT11SCNEAR'] = $objQuery['ACT11SCNEAR'];
        $result['ACT1CCNEAR'] = $objQuery['ACT1CCNEAR'];
        $result['ACT2CCNEAR'] = $objQuery['ACT2CCNEAR'];
        $result['ACT3CCNEAR'] = $objQuery['ACT3CCNEAR'];
        $result['ACT4CCNEAR'] = $objQuery['ACT4CCNEAR'];
        $result['ODVF1'] = $objQuery['ODVF1'];
        $result['ODVF2'] = $objQuery['ODVF2'];
        $result['ODVF3'] = $objQuery['ODVF3'];
        $result['ODVF4'] = $objQuery['ODVF4'];
        $result['OSVF1'] = $objQuery['OSVF1'];
        $result['OSVF2'] = $objQuery['OSVF2'];
        $result['OSVF3'] = $objQuery['OSVF3'];
        $result['OSVF4'] = $objQuery['OSVF4'];
        $result['MOTILITY_RS'] = $objQuery['MOTILITY_RS'];
        $result['MOTILITY_RI'] = $objQuery['MOTILITY_RI'];
        $result['MOTILITY_RR'] = $objQuery['MOTILITY_RR'];
        $result['MOTILITY_RL'] = $objQuery['MOTILITY_RL'];
        $result['MOTILITY_LS'] = $objQuery['MOTILITY_LS'];
        $result['MOTILITY_LI'] = $objQuery['MOTILITY_LI'];
        $result['MOTILITY_LR'] = $objQuery['MOTILITY_LR'];
        $result['MOTILITY_LL'] = $objQuery['MOTILITY_LL'];
        $result['NEURO_COMMENTS'] = $objQuery['NEURO_COMMENTS'];
        $result['STEREOPSIS'] = $objQuery['STEREOPSIS'];
        $result['ODNPA'] = $objQuery['ODNPA'];
        $result['OSNPA'] = $objQuery['OSNPA'];
        $result['VERTFUSAMPS'] = $objQuery['VERTFUSAMPS'];
        $result['DIVERGENCEAMPS'] = $objQuery['DIVERGENCEAMPS'];
        $result['NPC'] = $objQuery['NPC'];
        $result['DACCDIST'] = $objQuery['DACCDIST'];
        $result['DACCNEAR'] = $objQuery['DACCNEAR'];
        $result['CACCDIST'] = $objQuery['CACCDIST'];
        $result['CACCNEAR'] = $objQuery['CACCNEAR'];
        $result['ODCOLOR'] = $objQuery['ODCOLOR'];
        $result['OSCOLOR'] = $objQuery['OSCOLOR'];
        $result['ODCOINS'] = $objQuery['ODCOINS'];
        $result['OSCOINS'] = $objQuery['OSCOINS'];
        $result['ODREDDESAT'] = $objQuery['ODREDDESAT'];
        $result['OSREDDESAT'] = $objQuery['OSREDDESAT'];
        $result['ODPUPILSIZE1'] = $objQuery['ODPUPILSIZE1'];
        $result['ODPUPILSIZE2'] = $objQuery['ODPUPILSIZE2'];
        $result['ODPUPILREACTIVITY'] = $objQuery['ODPUPILREACTIVITY'];
        $result['ODAPD'] = $objQuery['ODAPD'];
        $result['OSPUPILSIZE1'] = $objQuery['OSPUPILSIZE1'];
        $result['OSPUPILSIZE2'] = $objQuery['OSPUPILSIZE2'];
        $result['OSPUPILREACTIVITY'] = $objQuery['OSPUPILREACTIVITY'];
        $result['OSAPD'] = $objQuery['OSAPD'];
        $result['DIMODPUPILSIZE1'] = $objQuery['DIMODPUPILSIZE1'];
        $result['DIMODPUPILSIZE2'] = $objQuery['DIMODPUPILSIZE2'];
        $result['DIMODPUPILREACTIVITY'] = $objQuery['DIMODPUPILREACTIVITY'];
        $result['DIMOSPUPILSIZE1'] = $objQuery['DIMOSPUPILSIZE1'];
        $result['DIMOSPUPILSIZE2'] = $objQuery['DIMOSPUPILSIZE2'];
        $result['DIMOSPUPILREACTIVITY'] = $objQuery['DIMOSPUPILREACTIVITY'];
        $result['PUPIL_COMMENTS'] = $objQuery['PUPIL_COMMENTS'];
        $result["json"] = json_encode($result);
        echo json_encode($result);
    } elseif ($zone == "IMPPLAN") {
        $result['IMPPLAN'] = build_IMPPLAN_items($pid, $copy_from);
        echo json_encode($result);
    } elseif ($zone == "ALL") {
        $result['RUL'] = $objQuery['RUL'];
        $result['LUL'] = $objQuery['LUL'];
        $result['RLL'] = $objQuery['RLL'];
        $result['LLL'] = $objQuery['LLL'];
        $result['RBROW'] = $objQuery['RBROW'];
        $result['LBROW'] = $objQuery['LBROW'];
        $result['RMCT'] = $objQuery['RMCT'];
        $result['LMCT'] = $objQuery['LMCT'];
        $result['RADNEXA'] = $objQuery['RADNEXA'];
        $result['LADNEXA'] = $objQuery['LADNEXA'];
        $result['RMRD'] = $objQuery['RMRD'];
        $result['LMRD'] = $objQuery['LMRD'];
        $result['RLF'] = $objQuery['RLF'];
        $result['LLF'] = $objQuery['LLF'];
        $result['RVFISSURE'] = $objQuery['RVFISSURE'];
        $result['LVFISSURE'] = $objQuery['LVFISSURE'];
        $result['ODHERTEL'] = $objQuery['ODHERTEL'];
        $result['OSHERTEL'] = $objQuery['OSHERTEL'];
        $result['HERTELBASE'] = $objQuery['HERTELBASE'];
        $result['ODPIC'] = $objQuery['ODPIC'];
        $result['OSPIC'] = $objQuery['OSPIC'];
        $result['EXT_COMMENTS'] = $objQuery['EXT_COMMENTS'];

        $result['OSCONJ'] = $objQuery['OSCONJ'];
        $result['ODCONJ'] = $objQuery['ODCONJ'];
        $result['ODCORNEA'] = $objQuery['ODCORNEA'];
        $result['OSCORNEA'] = $objQuery['OSCORNEA'];
        $result['ODAC'] = $objQuery['ODAC'];
        $result['OSAC'] = $objQuery['OSAC'];
        $result['ODLENS'] = $objQuery['ODLENS'];
        $result['OSLENS'] = $objQuery['OSLENS'];
        $result['ODIRIS'] = $objQuery['ODIRIS'];
        $result['OSIRIS'] = $objQuery['OSIRIS'];
        $result['ODKTHICKNESS'] = $objQuery['ODKTHICKNESS'];
        $result['OSKTHICKNESS'] = $objQuery['OSKTHICKNESS'];
        $result['ODGONIO'] = $objQuery['ODGONIO'];
        $result['OSGONIO'] = $objQuery['OSGONIO'];
        $result['ANTSEG_COMMENTS'] = $objQuery['ANTSEG_COMMENTS'];

        $result['ODDISC'] = $objQuery['ODDISC'];
        $result['OSDISC'] = $objQuery['OSDISC'];
        $result['ODCUP'] = $objQuery['ODCUP'];
        $result['OSCUP'] = $objQuery['OSCUP'];
        $result['ODMACULA'] = $objQuery['ODMACULA'];
        $result['OSMACULA'] = $objQuery['OSMACULA'];
        $result['ODVESSELS'] = $objQuery['ODVESSELS'];
        $result['OSVESSELS'] = $objQuery['OSVESSELS'];
        $result['ODVITREOUS'] = $objQuery['ODVITREOUS'];
        $result['OSVITREOUS'] = $objQuery['OSVITREOUS'];
        $result['ODPERIPH'] = $objQuery['ODPERIPH'];
        $result['OSPERIPH'] = $objQuery['OSPERIPH'];
        $result['ODDRAWING'] = $objQuery['ODDRAWING'];
        $result['OSDRAWING'] = $objQuery['OSDRAWING'];
        $result['ODCMT'] = $objQuery['ODCMT'];
        $result['OSCMT'] = $objQuery['OSCMT'];
        $result['RETINA_COMMENTS'] = $objQuery['RETINA_COMMENTS'];

        $result['ACT'] = $objQuery['ACT'];
        $result['ACT5CCDIST'] = $objQuery['ACT5CCDIST'];
        $result['ACT1CCDIST'] = $objQuery['ACT1CCDIST'];
        $result['ACT2CCDIST'] = $objQuery['ACT2CCDIST'];
        $result['ACT3CCDIST'] = $objQuery['ACT3CCDIST'];
        $result['ACT4CCDIST'] = $objQuery['ACT4CCDIST'];
        $result['ACT6CCDIST'] = $objQuery['ACT6CCDIST'];
        $result['ACT7CCDIST'] = $objQuery['ACT7CCDIST'];
        $result['ACT8CCDIST'] = $objQuery['ACT8CCDIST'];
        $result['ACT9CCDIST'] = $objQuery['ACT9CCDIST'];
        $result['ACT10CCDIST'] = $objQuery['ACT10CCDIST'];
        $result['ACT11CCDIST'] = $objQuery['ACT11CCDIST'];
        $result['ACT1SCDIST'] = $objQuery['ACT1SCDIST'];
        $result['ACT2SCDIST'] = $objQuery['ACT2SCDIST'];
        $result['ACT3SCDIST'] = $objQuery['ACT3SCDIST'];
        $result['ACT4SCDIST'] = $objQuery['ACT4SCDIST'];
        $result['ACT5SCDIST'] = $objQuery['ACT5SCDIST'];
        $result['ACT6SCDIST'] = $objQuery['ACT6SCDIST'];
        $result['ACT7SCDIST'] = $objQuery['ACT7SCDIST'];
        $result['ACT8SCDIST'] = $objQuery['ACT8SCDIST'];
        $result['ACT9SCDIST'] = $objQuery['ACT9SCDIST'];
        $result['ACT10SCDIST'] = $objQuery['ACT10SCDIST'];
        $result['ACT11SCDIST'] = $objQuery['ACT11SCDIST'];
        $result['ACT1SCNEAR'] = $objQuery['ACT1SCNEAR'];
        $result['ACT2SCNEAR'] = $objQuery['ACT2SCNEAR'];
        $result['ACT3SCNEAR'] = $objQuery['ACT3SCNEAR'];
        $result['ACT4SCNEAR'] = $objQuery['ACT4SCNEAR'];
        $result['ACT5CCNEAR'] = $objQuery['ACT5CCNEAR'];
        $result['ACT6CCNEAR'] = $objQuery['ACT6CCNEAR'];
        $result['ACT7CCNEAR'] = $objQuery['ACT7CCNEAR'];
        $result['ACT8CCNEAR'] = $objQuery['ACT8CCNEAR'];
        $result['ACT9CCNEAR'] = $objQuery['ACT9CCNEAR'];
        $result['ACT10CCNEAR'] = $objQuery['ACT10CCNEAR'];
        $result['ACT11CCNEAR'] = $objQuery['ACT11CCNEAR'];
        $result['ACT5SCNEAR'] = $objQuery['ACT5SCNEAR'];
        $result['ACT6SCNEAR'] = $objQuery['ACT6SCNEAR'];
        $result['ACT7SCNEAR'] = $objQuery['ACT7SCNEAR'];
        $result['ACT8SCNEAR'] = $objQuery['ACT8SCNEAR'];
        $result['ACT9SCNEAR'] = $objQuery['ACT9SCNEAR'];
        $result['ACT10SCNEAR'] = $objQuery['ACT10SCNEAR'];
        $result['ACT11SCNEAR'] = $objQuery['ACT11SCNEAR'];
        $result['ACT1CCNEAR'] = $objQuery['ACT1CCNEAR'];
        $result['ACT2CCNEAR'] = $objQuery['ACT2CCNEAR'];
        $result['ACT3CCNEAR'] = $objQuery['ACT3CCNEAR'];
        $result['ACT4CCNEAR'] = $objQuery['ACT4CCNEAR'];
        $result['ODVF1'] = $objQuery['ODVF1'];
        $result['ODVF2'] = $objQuery['ODVF2'];
        $result['ODVF3'] = $objQuery['ODVF3'];
        $result['ODVF4'] = $objQuery['ODVF4'];
        $result['OSVF1'] = $objQuery['OSVF1'];
        $result['OSVF2'] = $objQuery['OSVF2'];
        $result['OSVF3'] = $objQuery['OSVF3'];
        $result['OSVF4'] = $objQuery['OSVF4'];
        $result['MOTILITY_RS'] = $objQuery['MOTILITY_RS'];
        $result['MOTILITY_RI'] = $objQuery['MOTILITY_RI'];
        $result['MOTILITY_RR'] = $objQuery['MOTILITY_RR'];
        $result['MOTILITY_RL'] = $objQuery['MOTILITY_RL'];
        $result['MOTILITY_LS'] = $objQuery['MOTILITY_LS'];
        $result['MOTILITY_LI'] = $objQuery['MOTILITY_LI'];
        $result['MOTILITY_LR'] = $objQuery['MOTILITY_LR'];
        $result['MOTILITY_LL'] = $objQuery['MOTILITY_LL'];
        $result['NEURO_COMMENTS'] = $objQuery['NEURO_COMMENTS'];
        $result['STEREOPSIS'] = $objQuery['STEREOPSIS'];
        $result['ODNPA'] = $objQuery['ODNPA'];
        $result['OSNPA'] = $objQuery['OSNPA'];
        $result['VERTFUSAMPS'] = $objQuery['VERTFUSAMPS'];
        $result['DIVERGENCEAMPS'] = $objQuery['DIVERGENCEAMPS'];
        $result['NPC'] = $objQuery['NPC'];
        $result['DACCDIST'] = $objQuery['DACCDIST'];
        $result['DACCNEAR'] = $objQuery['DACCNEAR'];
        $result['CACCDIST'] = $objQuery['CACCDIST'];
        $result['CACCNEAR'] = $objQuery['CACCNEAR'];
        $result['ODCOLOR'] = $objQuery['ODCOLOR'];
        $result['OSCOLOR'] = $objQuery['OSCOLOR'];
        $result['ODCOINS'] = $objQuery['ODCOINS'];
        $result['OSCOINS'] = $objQuery['OSCOINS'];
        $result['ODREDDESAT'] = $objQuery['ODREDDESAT'];
        $result['OSREDDESAT'] = $objQuery['OSREDDESAT'];
        $result['ODPUPILSIZE1'] = $objQuery['ODPUPILSIZE1'];
        $result['ODPUPILSIZE2'] = $objQuery['ODPUPILSIZE2'];
        $result['ODPUPILREACTIVITY'] = $objQuery['ODPUPILREACTIVITY'];
        $result['ODAPD'] = $objQuery['ODAPD'];
        $result['OSPUPILSIZE1'] = $objQuery['OSPUPILSIZE1'];
        $result['OSPUPILSIZE2'] = $objQuery['OSPUPILSIZE2'];
        $result['OSPUPILREACTIVITY'] = $objQuery['OSPUPILREACTIVITY'];
        $result['OSAPD'] = $objQuery['OSAPD'];
        $result['DIMODPUPILSIZE1'] = $objQuery['DIMODPUPILSIZE1'];
        $result['DIMODPUPILSIZE2'] = $objQuery['DIMODPUPILSIZE2'];
        $result['DIMODPUPILREACTIVITY'] = $objQuery['DIMODPUPILREACTIVITY'];
        $result['DIMOSPUPILSIZE1'] = $objQuery['DIMOSPUPILSIZE1'];
        $result['DIMOSPUPILSIZE2'] = $objQuery['DIMOSPUPILSIZE2'];
        $result['DIMOSPUPILREACTIVITY'] = $objQuery['DIMOSPUPILREACTIVITY'];
        $result['PUPIL_COMMENTS'] = $objQuery['PUPIL_COMMENTS'];
        $result['IMP'] = $objQuery['IMP'];
        $result["json"] = json_encode($result);
        echo json_encode($result);
    } elseif ($zone == "READONLY") {
        $result = $objQuery;
        $count_rx = '0';
        $query1 = "select * from form_eye_mag_wearing where PID=? and ENCOUNTER=? and FORM_ID >'0' ORDER BY RX_NUMBER";
        $wear = sqlStatement($query1, array($pid,$_SESSION['encounter']));
        while ($wearing = sqlFetchArray($wear)) {
            ${"display_W_$count_rx"}        = '';
                  ${"ODSPH_$count_rx"}            = $wearing['ODSPH'];
                  ${"ODCYL_$count_rx"}            = $wearing['ODCYL'];
                  ${"ODAXIS_$count_rx"}           = $wearing['ODAXIS'];
                  ${"OSSPH_$count_rx"}            = $wearing['OSSPH'];
                  ${"OSCYL_$count_rx"}            = $wearing['OSCYL'];
                  ${"OSAXIS_$count_rx"}           = $wearing['OSAXIS'];
                  ${"ODMIDADD_$count_rx"}         = $wearing['ODMIDADD'];
                  ${"OSMIDADD_$count_rx"}         = $wearing['OSMIDADD'];
                  ${"ODADD_$count_rx"}            = $wearing['ODADD'];
                  ${"OSADD_$count_rx"}            = $wearing['OSADD'];
                  ${"ODVA_$count_rx"}             = $wearing['ODVA'];
                  ${"OSVA_$count_rx"}             = $wearing['OSVA'];
                  ${"ODNEARVA_$count_rx"}         = $wearing['ODNEARVA'];
                  ${"OSNEARVA_$count_rx"}         = $wearing['OSNEARVA'];
                  ${"ODPRISM_$count_rx"}          = $wearing['ODPRISM'];
                  ${"OSPRISM_$count_rx"}          = $wearing['OSPRISM'];
                  ${"W_$count_rx"}                = '1';
                  ${"RX_TYPE_$count_rx"}          = $wearing['RX_TYPE'];
                  ${"ODHPD_$count_rx"}            = $wearing['ODHPD'];
                  ${"ODHBASE_$count_rx"}          = $wearing['ODHBASE'];
                  ${"ODVPD_$count_rx"}            = $wearing['ODVPD'];
                  ${"ODVBASE_$count_rx"}          = $wearing['ODVBASE'];
                  ${"ODSLABOFF_$count_rx"}        = $wearing['ODSLABOFF'];
                  ${"ODVERTEXDIST_$count_rx"}     = $wearing['ODVERTEXDIST'];
                  ${"OSHPD_$count_rx"}            = $wearing['OSHPD'];
                  ${"OSHBASE_$count_rx"}          = $wearing['OSHBASE'];
                  ${"OSVPD_$count_rx"}            = $wearing['OSVPD'];
                  ${"OSVBASE_$count_rx"}          = $wearing['OSVBASE'];
                  ${"OSSLABOFF_$count_rx"}        = $wearing['OSSLABOFF'];
                  ${"OSVERTEXDIST_$count_rx"}     = $wearing['OSVERTEXDIST'];
                  ${"ODMPDD_$count_rx"}           = $wearing['ODMPDD'];
                  ${"ODMPDN_$count_rx"}           = $wearing['ODMPDN'];
                  ${"OSMPDD_$count_rx"}           = $wearing['OSMPDD'];
                  ${"OSMPDN_$count_rx"}           = $wearing['OSMPDN'];
                  ${"BPDD_$count_rx"}             = $wearing['BPDD'];
                  ${"BPDN_$count_rx"}             = $wearing['BPDN'];
                  ${"LENS_MATERIAL_$count_rx"}    = $wearing['LENS_MATERIAL'];
                  ${"LENS_TREATMENTS_$count_rx"}  = $wearing['LENS_TREATMENTS'];
                  ${"COMMENTS_$count_rx"}         = $wearing['COMMENTS'];
        }
        $result['IMPPLAN'] = build_IMPPLAN_items($pid, $copy_from);
        $result['query'] = $query;
        $result["json"] = json_encode($result);
        echo json_encode($result);
    }
}

/**
 *  This builds the IMPPLAN_items variable for a given pid and form_id.
 *  @param string $pid patient_id
 *  @param string $form_id field id in table form_eye_mag
 *  @return object IMPPLAN_items
 */
function build_IMPPLAN_items($pid, $form_id)
{
    global $form_folder;
    $query = "select * from form_" . $form_folder . "_impplan where form_id=? and pid=? ORDER BY IMPPLAN_order";
    $newdata = array();
    $fres = sqlStatement($query, array($form_id,$pid));
    $i = 0;
    while ($frow = sqlFetchArray($fres)) {
        $IMPPLAN_items[$i]['form_id'] = $frow['form_id'];
        $IMPPLAN_items[$i]['pid'] = $frow['pid'];
        $IMPPLAN_items[$i]['id'] = $frow['id'];
        $IMPPLAN_items[$i]['title'] = $frow['title'];
        $IMPPLAN_items[$i]['code'] = $frow['code'];
        $IMPPLAN_items[$i]['codetype'] = $frow['codetype'];
        $IMPPLAN_items[$i]['codedesc'] = $frow['codedesc'];
        $IMPPLAN_items[$i]['codetext'] = $frow['codetext'];
        $IMPPLAN_items[$i]['plan'] = $frow['plan'];
        $IMPPLAN_items[$i]['PMSFH_link'] = $frow['PMSFH_link'];
        $IMPPLAN_items[$i]['IMPPLAN_order'] = $frow['IMPPLAN_order'];
        $i++;
    }

    return $IMPPLAN_items;
}

            /**
             *  This builds the CODING_items variable for a given pid and encounter.
             *  @param string $pid patient_id
             *  @param string $encounter field id in table form_encounters
             *  @return object CODING_items
             */
function build_CODING_items($pid, $encounter)
{
    $query = "select * from billing where encounter=? and pid=? ORDER BY id";
    $fres = sqlStatement($query, array($encounter,$pid));
    $i = 0;

    while ($frow = sqlFetchArray($fres)) {
        $CODING_items[$i]['encounter'] = $frow['encounter'];
        $CODING_items[$i]['pid'] = $frow['pid'];
        $CODING_items[$i]['id'] = $frow['id'];
        $CODING_items[$i]['codetype'] = $frow['code_type'];
        $CODING_items[$i]['codedesc'] = $frow['code_desc'];
        $CODING_items[$i]['codetext'] = $frow['code_text'];
        $CODING_items[$i]['justify'] = $frow['justify'];
        $i++;
    }

    return $CODING_items;
}

/**
 *  This function builds an array of documents for this patient ($pid).
 *  We first list all the categories this practice has created by name and by category_id
 *  for this patient ($pid)
 *  Each document info from documents table is added to these as arrays
 *
 *  @param string $pid patient_id
 *  @return array($documents)
 */
function document_engine($pid)
{
    $sql1 =  sqlStatement("Select * from categories");
    while ($row1 = sqlFetchArray($sql1)) {
        $categories[] = $row1;
        $row1['name'] = preg_replace('/ - Eye/', '', $row1['name']);

        $my_name[$row1['id']] = $row1['name'];
        $children_names[$row1['parent']][] = $row1['name'];
        $parent_name[$row1['name']] = $my_name[$row1['parent']];
        if ($row1['value'] > '') {
            //if there is a value, tells us what segment of exam ($zone) this belongs in...
            $zones[$row1['value']][] = $row1;
        } else {
            if ($row1['name'] != "Categories") {
                $zones['OTHER'][] = $row1;
            }
        }
    }

    $query = "Select *, categories.name as cat_name
                from
                categories, documents,categories_to_documents
                where documents.foreign_id=? and documents.id=categories_to_documents.document_id and
                categories_to_documents.category_id=categories.id and documents.deleted = 0 ORDER BY categories.name";
    $sql2 =  sqlStatement($query, array($pid));
    while ($row2 = sqlFetchArray($sql2)) {
        //the document may not be created on the same day as the encounter, use encounter date first
        //get encounter date from encounter id
        $row2['cat_name'] = preg_replace('/ - Eye/', '', $row2['cat_name']);
        $row2['display_url'] = preg_replace("|file:///.*/sites/|", $GLOBALS['webroot'] . "/sites/", $row2['url']);
        if ($row2['encounter_id']) {
            $visit = getEncounterDateByEncounter($row2['encounter_id']);
            $row2['encounter_date'] = oeFormatSDFT(strtotime($visit['date']));
        } else {
            $row2['encounter_date'] = $row2['docdate'];
        }

        $documents[] = $row2;
        $docs_in_cat_id[$row2['category_id']][] = $row2;
        if ($row2['value'] > '') {
            $docs_in_zone[$row2['value']][] = $row2;
        } else {
            $docs_in_zone['OTHER'][] = $row2;
        }

        $docs_in_name[$row2['cat_name']][] = $row2;
        $docs_by_date[$row2['encounter_date']][] = $row2;
    }

    $documents['categories']        = $categories;
    $documents['my_name']           = $my_name;
    $documents['children_names']    = $children_names;
    $documents['parent_name']       = $parent_name;
    $documents['zones']             = $zones;
    $documents['docs_in_zone']      = $docs_in_zone;
    $documents['docs_in_cat_id']    = $docs_in_cat_id;
    $documents['docs_in_name']      = $docs_in_name;
    $documents['docs_by_date']      = $docs_by_date;

    return array($documents);
}

/**
 *  This function returns ICONS with links for a specific clinical subsection of the Document Library.
 *
 *  @param string $pid value = patient id
 *  @param string $encounter is the encounter_id
 *  @param string $category_value options EXT,ANTSEG,POSTSEG,NEURO,OTHER
 *                These values are taken from the "value" field in the Documents' table "categories".
 *                They allow us to regroup the categories how we like them.
 *  @return array($imaging,$episode)
 */
function display($pid, $encounter, $category_value)
{
    global $form_folder;
    global $id;
    global $documents;
    $episode = '';
       /**
        *   Each document is stored in a specific category.  Think of a category as a Folder.
        *   Practices can add/alter/delete category names as they wish.
        *   In the Eye Form we link to these categories, not by name but by what part of the physical exam they belong to.
        *   We needed a pointer to tell us if a document category is specific to a clinical section.
        *   For example, a photo of the retina is stored in the category we named "Fundus".
        *       A photo of the optic nerve is stored in the "Optic Disc" category.  Someone else might change the
        *       name to "Optic Nerve", or even a different language.  No matter, these categories include documents
        *       we would like to directly link to/open from the RETINA section of the link.
        *   The categories table does have an unused field - "value".
        *   This is where we link document categories to a clinical zone.  We add the clinical section name
        *   on install but the end user can change or add others as the devices evolve.
        *   Currently the base install has EXT,ANTSEG,POSTSEG,NEURO
        *   New names new categories.  OCT would not have been a category 5 years ago.
        *   Who knows what is next?  Gene-lab construction?
        *   So the name is user assigned as is the location.
        *   Thus we need to build out the Documents section by adding another layer "zones"
        *   to the associative array.
        */
    if (!$documents) {
        list($documents) = document_engine($pid);
    }

    for ($j = 0; $j < count($documents['zones'][$category_value]); $j++) {
        $count_here = empty($documents['docs_in_cat_id'][$documents['zones'][$category_value][$j]['id']]) ? 0 : count($documents['docs_in_cat_id'][$documents['zones'][$category_value][$j]['id']]);

        $id_to_show = $documents['docs_in_cat_id'][$documents['zones'][$category_value][$j]['id']][$count_here - 1]['document_id'];
        $documents['zones'][$category_value][$j]['name'] = preg_replace("( - Eye)", "", $documents['zones'][$category_value][$j]['name']);
        $episode .= "<tr>
        <td class='right'><span class='font-weight-bold'>" . text($documents['zones'][$category_value][$j]['name']) . "</span>:&nbsp;</td>
        <td>
            <a onclick=\"openNewForm('" . $GLOBALS['webroot'] . "/controller.php?document&upload&patient_id=" . attr($pid) . "&parent_id=" . attr($documents['zones'][$category_value][$j]['id']) . "&', '" . xla('Upload') . " " . attr($documents['zones'][$category_value][$j]['name']) . "');\" href='#'>
            <img src='../../forms/" . $form_folder . "/images/upload_file.png' class='little_image'>
            </a>
        </td>
        <td>
            <a onclick=\"return showpnotes('" . $id_to_show . "');\">
                <img  src='../../forms/" . $form_folder . "/images/upload_multi.png' class='little_image'>
            </a>
        </td>
        <td>";
        //open via OpenEMR Documents with treemenu
        if ($count_here > '0') {
            $episode .= '<a onclick="openNewForm(\'' . $GLOBALS['webroot'] . '/controller.php?document&view&patient_id=' . $pid . '&doc_id=' . $id_to_show . '\',\'' . xla('Documents') . ': ' . attr($documents['zones'][$category_value][$j]['name']) . '\');"><img src="../../forms/' . $form_folder . '/images/jpg.png" class="little_image" /></a>';
        }

        $episode .= '</td></tr>';
        $i++;
    }

    return array($documents,$episode);
}

/**
 *  This is an application style menu (bootstrap) to start shifting clinical functions into a single page.
 *
 *  @param string $pid is the patient id
 *  @param string $encounter is the encounter_id
 *  @param string $title is the form title
 *
 *  @return nothing, outputs directly to screen
 */
function menu_overhaul_top($pid, $encounter, $title = "Eye Exam")
{
    global $form_folder;
    global $prov_data;
    global $encounter;
    global $form_id;
    global $display;
    global $provider_id;

    $providerNAME = $prov_data['fname'] . " " . $prov_data['lname'];
    if ($prov_data['suffix']) {
        $providerNAME .= ", " . $prov_data['suffix'];
    }

    if (!empty($_REQUEST['display']) && ($_REQUEST['display'] == "fullscreen")) {
        $fullscreen_disable = 'disabled';
    } else {
        $frame_disabled = 'disabled';
        echo "<style>.tabHide{ display:none; }</style>";
    }
    ?>
       <!-- Navigation -->
    <nav class="navbar fixed-top navbar-custom navbar-bright navbar-inner navbar-expand-lg" data-role="page banner navigation"
         style="margin-bottom: 0;z-index: 9999999;">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="container-fluid" style="margin-top:0px;padding:2px;">
            <div class="navbar-brand" style="color:black;">
                &nbsp;
                <img src="<?php echo $GLOBALS['webroot']; ?>/sites/default/images/login_logo.gif" class="little_image">
                <span class="brand"><?php echo xlt('Eye Exam'); ?></span>
            </div>
            <div class="navbar-collapse oer-navbar-collapse mr-auto mt-2 mt-lg-0" id="oer-navbar-collapse-1">
                <ul class="navbar-nav mr-auto">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_file" role="button" aria-expanded="true"><?php echo xlt("File"); ?> </a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="nav-lik" id="menu_PRINT_narrative" name="menu_PRINT_report"><a class="nav-link black" id="BUTTON_PRINT_report" target="_new" href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/report/custom_report.php?printable=1&pdf=0&<?php echo attr_url($form_folder) . "_" . attr_url($form_id) . "=" . attr_url($encounter); ?>"><?php echo xlt("Print Report"); ?></a></li>
                            <li class="nav-ite" id="menu_PRINT_narrative_2" name="menu_PRINT_report_2"><a class="nav-link black" id="BUTTON_PRINT_report_2" target="_new" href="#"
                                onclick="top.restoreSession(); create_task('<?php echo attr($provider_id); ?>','Report','menu'); return false;">
                                <?php echo xlt("Save Report as PDF"); ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_edit" role="button" aria-expanded="true"><?php echo xlt("Edit"); ?> </a>
                        <ul class="dropdown-menu" role="menu">
                            <li id="menu_Defaults" name="menu_Defaults" class="">
                                <a class="nav-link black"
                                   id="BUTTON_Defaults_menu"
                                   onclick="openNewForm('<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=Eye_defaults_<?php echo attr($provider_id); ?>', '<?php echo xla('Default Exam Values'); ?>');"
                                   name="provider_todo"
                                   href="JavaScript:void(0);">
                                <?php echo xlt("Default Values"); ?> &nbsp;
                                <span class="menu_icon"><i class="fa fa-pencil-alt fa-fw"></i> </span></a></li>
                            <li id="menu_TEXT" name="menu_TEXT" class="active">
                                <a class="nav-link black" href="#"><?php echo xlt("Text"); ?><span class="menu_icon">Ctl-T</span></a></li>
                            <li id="menu_DRAW" name="menu_DRAW">
                                <a class="nav-link black" href="#" id="BUTTON_DRAW_menu" name="BUTTON_DRAW_menu"><?php echo xlt("Draw"); ?><span class="menu_icon">Ctl-D</span></a></li>
                            <li id="menu_QP" name="menu_QP">
                                <a class="nav-link black" href="#" id="BUTTON_QP_menu" name="BUTTON_QP_menu"><?php echo xlt("Quick Picks"); ?><span class="menu_icon">Ctl-B</span></a></li>
                            <li id="menu_PRIORS" name="menu_PRIORS">
                                <a class="nav-link black" href="#"><?php echo xlt("Prior Visits"); ?><span class="menu_icon">Ctl-P</span></a></li>
                            <li id="menu_KB" name="menu_KB">
                                <a class="nav-link black" href="#"><?php echo xlt("Shorthand"); ?><span class="menu_icon">Ctl-K</span></a></li>
                            <?php
                                /*
                                // This only shows up in fullscreen currently so hide it.
                                // If the decision is made to show this is framed openEMR, then display it
                                */
                            if ($display !== "fullscreen") { ?>
                                    <li class="divider"></li>
                                    <li id="menu_fullscreen" name="menu_fullscreen" <?php echo ($fullscreen ?? ''); ?>>
                                        <a class="nav-link black"
                                           onclick="openNewForm('<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/encounter/load_form.php?formname=fee_sheet');top.restoreSession();dopopup('<?php echo $_SERVER['REQUEST_URI'] . '&display=fullscreen&encounter=' . $encounter; ?>');"
                                           href="JavaScript:void(0);"
                                           ><?php echo xlt('Fullscreen'); ?></a>
                                    </li>
                                    <?php
                            } ?>
                        </ul>
                    </li>
               
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_view" role="button" aria-expanded="true"><?php echo xlt("View"); ?> </a>
                        <ul class="dropdown-menu" role="menu">
                            <li id="menu_HPI" name="menu_HPI"><a class="nav-link black" href="#"><?php echo xlt("HPI"); ?></a></li>
                            <li id="menu_PMH" name="menu_PMH"><a class="nav-link black" href="#"><?php echo xlt("PMH{{Past Medical History}}"); ?></a></li>
                            <li id="menu_EXT" name="menu_EXT" ><a class="nav-link black" href="#"><?php echo xlt("External"); ?></a></li>
                            <li id="menu_ANTSEG" name="menu_ANTSEG" ><a class="nav-link black" href="#"><?php echo xlt("Anterior Segment"); ?></a></li>
                            <li id="menu_POSTSEG" name="menu_POSTSEG" ><a class="nav-link black" href="#"><?php echo xlt("Posterior Segment"); ?></a></li>
                            <li id="menu_NEURO" name="menu_NEURO" ><a class="nav-link black" href="#"><?php echo xlt("Neuro"); ?></a></li>
                            <li id="menu_IMPPLAN" name="menu_IMPPLAN" ><a class="nav-link black" href="#"><?php echo xlt("Imp Plan"); ?></a></li>
                            <li class="divider"></li>
                            <li id="menu_Right_Panel" name="menu_Right_Panel"><a class="nav-link black" href="#"><?php echo xlt("PMSFH Panel"); ?><span class="menu_icon"><i class="fa fa-list" ></i></span></a></li>
                            <li id="menu_left_tabs" name="menu_left_tabs"><a class="nav-link black" href="#"><?php echo xlt("Chart View"); ?><span class="menu_icon"><i class="fa fa-user-md" ></i></span></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown"
                           id="menu_dropdown_library" role="button"
                           aria-expanded="true"><?php echo xlt("Library"); ?> </a>
                        <ul class="dropdown-menu" role="menu">
                            <li id="menu_IOP_graph" name="menu_IOP_graph" ><a class="nav-link black" href="#"><?php echo xlt("IOP Graph"); ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown"
                           id="menu_dropdown_help" role="button"
                           aria-expanded="true"><?php echo xlt("Help"); ?> </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                            <li>
                                <a class="nav-link black" href="#"  tabindex="-1" id="tooltips_toggle" name="tooltips_toggle">
                                <i class="fa fa-help"></i>  <?php echo xlt("Tooltips"); ?>
                                <span id="tooltips_status" name="tooltips_status"></span>
                                <span class="menu_icon"><i title="<?php echo xla('Turn the Tooltips on/off'); ?>" id="qtip_icon" class="fa fa-check fa-1"></i></span></a>
                            </li>
                            <li>
                                <a class="nav-link black" tabindex="-1" target="_shorthand" href="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php">
                                    <i class="fa fa-help"></i>  <?php echo xlt("Shorthand Help"); ?>
                                    <span class="menu_icon">
                                        <i title="<?php echo xla('Click for Shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></span></a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right my-2 my-lg-0">
                    <li><span style="margin-right:15px;color:black;"  onclick="editScripts('<?php echo $GLOBALS['web_root']; ?>/controller.php?prescription&list&id=<?php echo attr_url($pid); ?>');">eRx</button>
                        </span></li>
                    <li ><span id="active_flag" name="active_flag" style="margin-right:15px;color:red;"> <?php echo xlt('Active Chart'); ?> </span>
                        <span name="active_icon" id="active_icon" style="color:black;"><i class='fa fa-toggle-on'></i></span></li>

                </ul>

            </div><!-- /.navbar-collapse -->
        </div>
    </nav>
    <?php
        return;
}
/**
 *  This is currently a floating div top with patient demographics and such.
 *  Used in fullscreen mode at the top.
 *
 *  @param string $pid patient_id
 *  @param string $encounter is the current encounter number
 *  @return nothing, outputs directly to screen
 */
function menu_overhaul_left($pid, $encounter)
{
    global $form_folder;
    global $pat_data;
    global $visit_date;
    global $documents;
    global $display;
    global $reason;
    global $priors;
    global $pcp_data;
    global $ref_data;
    global $ins_coA;
    global $ins_coB;

    /*
     * find out if the patient has a photo
     */
    if (!$documents) {
        list($documents) = document_engine($pid);
    }
    ?>
    <div class="borderShadow row" id="title_bar">
        <div id="left_menu" name="left_menu" class="col-sm-4" style="padding-left: 18px;">
                <table style="text-align:left;">
                    <tr><td class="right" >
                            <?php
                            $age = getPatientAgeDisplay($pat_data['DOB'], $encounter_date);
                            $DOB = oeFormatShortDate($pat_data['DOB']);
                            echo "<span class='font-weight-bold'>" . xlt('Name') . ":</span> </td><td nowrap> &nbsp;" . text($pat_data['fname']) . "  " . text($pat_data['lname']) . " (" . text($pid) . ")</td></tr>
                                    <tr><td class='right'><span class='font-weight-bold'>" . xlt('DOB') . ":</span></td><td  nowrap> &nbsp;" . text($DOB) . "&nbsp;&nbsp;(" . text($age) . ")";
                            ?>
                            <?php
                            ?>
                        </td>
                    </tr>
                    <?php
                        echo "<tr><td class='right' nowrap><span class='font-weight-bold'>" . xlt('Visit Date') . ":</span></td><td>&nbsp;" . $visit_date . "</td></tr>";
                    ?>
                    <tr><td class="right" style="vertical-align:top;" nowrap><span style="font-weight:bold;"><?php echo xlt("Provider"); ?>:</span>&nbsp;</td>
                        <td><?php echo text(getProviderName(getProviderIdOfEncounter($encounter))); ?></td>
                    </tr>

                    <tr>
                        <td class="right" style="vertical-align:top;" nowrap><span style="font-weight:bold;"><?php echo xlt("Reason"); ?>:</span>&nbsp;</td>
                        <td><?php echo text($reason); ?></td>
                    </tr>
                    <?php
                    if ($priors[0]['TODO']) {
                        ?>
                    <tr>
                        <td class="right" style="vertical-align:top;" nowrap><span style="font-weight:bold;"><?php echo xlt("Plan"); ?>:</span>&nbsp;</td>
                        <td style="vertical-align:top;">
                            <?php
                            $j = 1;
                            foreach ($priors[0]['TODO'] as $plan) {
                                echo "<span class='button'>" . $j++ . "</span> " . text($plan['ORDER_DETAILS']) . "<br />";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
        </div>
        <div id="left_menu3" name="left_menu3" class="col-sm-3" style="font-size:1.0em;">
            <?php             //if the patient has a photograph, use it else use generic avitar thing.

            if (!empty($documents['docs_in_name']['Patient Photograph'][0])) {
                ?>
                <object><embed
                            src="<?php echo $GLOBALS['webroot']; ?>/controller.php?document&retrieve&patient_id=<?php echo attr($pid); ?>&document_id=<?php echo attr($documents['docs_in_name']['Patient Photograph'][0]['id']); ?>&as_file=false&original_file=true&disable_exit=false&show_original=true&context=patient_picture"
                            
                            Xsrc="<?php echo $GLOBALS['webroot']; ?>/controller.php?document&amp;retrieve&amp;patient_id=<?php echo attr($pid); ?>&amp;document_id=<?php echo attr($documents['docs_in_name']['Patient Photograph'][0]['id']); ?>&amp;as_file=false" frameborder="0"
                     type="<?php echo attr($documents['docs_in_name']['Patient Photograph'][0]['mimetype']); ?>" allowscriptaccess="always" allowfullscreen="false" height="100"></embed></object>
                <?php
            } else {
                ?>
            <object><embed src="<?php echo $GLOBALS['web_root']; ?>/interface/forms/<?php echo $form_folder; ?>/images/anon.gif" frameborder="0"
                 type="image/gif" height="50"></embed></object>
                <?php
            }
            ?>
        </div>

        <div id="left_menu2" name="left_menu2" class="col-sm-4" style="font-size:1.0em;">

            <div style="position:relative;float:left;padding-left:18px;top:0px;">
                <table style="border:1pt;font-size:1.0em;">
                    <tr>
                        <td class="right"><span style="font-weight:bold;"><?php echo xlt("PCP"); ?>:</span>&nbsp;</td>
                        <td class="left"> <span id="pcp_name"><?php echo text($pcp_data['fname']) . " " . text($pcp_data['lname']); ?><?php if ($pcp_data['suffix']) {
                                    echo ", " . text($pcp_data['suffix']);} ?></span></td>
                        </td>
                    </tr>

                    <tr><td class="right" nowrap><span style="font-weight:bold;"><?php echo xlt("Referred By"); ?>:</span>&nbsp;</td>
                        <td class="left"> <span id="ref_name"><?php echo text($ref_data['fname']) . " " . text($ref_data['lname']); ?><?php if ($ref_data['suffix']) {
                                    echo ", " . text($ref_data['suffix']);} ?></span></td>
                        </tr>
                    <tr><td class="right"><span style="font-weight:bold;"><?php echo xlt("Insurance"); ?>:</span>&nbsp;</td><td class="left">&nbsp;<?php echo text($ins_coA); ?></td></tr>
                    <tr><td class="right"><span style="font-weight:bold;"><?php echo xlt("Secondary"); ?>:</span>&nbsp;</td><td class="left">&nbsp;<?php echo text($ins_coB); ?></td></tr>
                    <tr><td class="right"><span style="font-weight:bold;"><?php echo xlt("Pharmacy"); ?>:</span>&nbsp;</td>
                        <td class="left">&nbsp;
                            <?php
                            if (!empty($pat_data['pharmacy_id'])) {
                                $sql = "SELECT d.id, d.name, a.line1, a.city, " .
                                    "a.state, p.area_code, p.prefix, p.number FROM pharmacies AS d " .
                                    "LEFT OUTER JOIN addresses AS a ON a.foreign_id = d.id " .
                                    "LEFT OUTER JOIN phone_numbers AS p ON p.foreign_id = d.id " .
                                    "AND p.type = 2 where d.id=?" .
                                    "ORDER BY state, city, name, area_code, prefix, number";
                                $pharm = sqlQuery($sql, array($pat_data['pharmacy_id']));
                                echo text($pharm['name'] . ", " . $pharm['city'] . " " . $pharm['state']);
                            }
                            ?>
                        </td></tr>

                </table>
            </div>
        </div>

    </div>
    <?php
}
/**
 *  This is currently not used.  It can easily be a footer with the practice info
 *  or whatever you like.  Maybe a placeholder for user groups or link outs to data repositories
 *  such as Medfetch.com/PubMed/UpToDate/DynaMed????
 *  It could provide information as to available data imports from connected machines - yes we have
 *  data from an autorefractor needed to be imported.  The footer can be fixed or floating.
 *  It could have balance info, notes, or an upside down menu mirroring the header menu, maybe allowing
 *  the user to decide which is fixed and which is not?  Messaging? Oh the possibilities.
 *
 *  @param string $pid patient_id
 *  @param string $encounter is the current encounter number
 *  @return nothing, outputs directly to screen
 */

function menu_overhaul_bottom($pid, $encounter)
{
    ?><div class="navbar-custom" style="width:100%;height:25px;position:relative;border-top:1pt solid black;bottom:0px;z-index:1000000;">&nbsp;</div><?php
}

/*
 * This was taken from new_form.php and is helping to integrate new menu with openEMR
 * menu seen on encounter page.
 */
function Menu_myGetRegistered($state = "1", $limit = "unlimited", $offset = "0")
{
    $sql = "SELECT category, nickname, name, state, directory, id, sql_run, " .
      "unpackaged, date FROM registry WHERE " .
      "state LIKE ? ORDER BY category, priority, name";
    if ($limit != "unlimited") {
        $sql .= " limit " . escape_limit($limit) . ", " . escape_limit($offset);
    }

    $res = sqlStatement($sql, array($state));
    if ($res) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }
    } else {
        return false;
    }

    return $all;
}
/**
 * This prints a header for documents.  Keeps the brand uniform...
 *  @param string $pid patient_id
 *  @param string $direction, options "web" or anything else.  Web provides apache-friendly url links.
 *  @return outputs directly to screen
 */
function report_header($pid, $direction = 'shell')
{
    global $encounter;
    global $visit_date;
    global $facilityService;
    global $OE_SITE_DIR;

    /*******************************************************************
    $titleres = getPatientData($pid, "fname,lname,providerID");
    $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
    *******************************************************************/
    //$titleres = getPatientData($pid, "fname,lname,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
    $titleres = getPatientData($pid, "fname,lname,providerID,DOB");
    $facility = null;
    if ($_SESSION['pc_facility']) {
        $facility = $facilityService->getById($_SESSION['pc_facility']);
    } else {
        $facility = $facilityService->getPrimaryBillingLocation();
    }

    $DOB = oeFormatShortDate($titleres['DOB']);
    /******************************************************************/
    ob_start();
    // Use logo if it exists as 'practice_logo.gif' in the site dir
    // old code used the global custom dir which is no longer a valid
    //need to fix logo for multi-site
    ?>
    <table style="width:100%;">
        <tr>
            <td style='width:150px;text-align:top;'>
                <?php
                if ($direction == "web") {
                    $practice_logo = $GLOBALS['webroot'] . "/sites/default/images/practice_logo.gif";
                    if (file_exists($OE_SITE_DIR . "/images/practice_logo.gif")) {
                        echo "<img src='$practice_logo' align='left' style='width:150px;margin:0px 10px;'><br />\n";
                    }
                } else {
                    $practice_logo = "$OE_SITE_DIR/images/practice_logo.gif";
                    if (file_exists($practice_logo)) {
                        echo "<img src='$practice_logo' align='left' style='width:100px;margin:0px 10px;'><br />\n";
                    } else {
                        $practice_logo = "$OE_SITE_DIR/images/login_logo.gif";
                        if (file_exists($practice_logo)) {
                            echo "<img src='$practice_logo' align='left' style='width:100px;margin:0px 10px;'><br />\n";
                        }
                    }
                }
                ?>
            </td>
            <td style='width:40%;'>
                <em style="font-weight:bold;font-size:1.4em;"><?php echo text($facility['name']); ?></em><br />
                <?php echo text($facility['street']); ?><br />
                <?php echo text($facility['city']); ?>, <?php echo text($facility['state']); ?> <?php echo text($facility['postal_code']); ?><br />
                <?php echo xlt('Phone') . ': ' . text($facility['phone']); ?><br />
                <?php echo xlt('Fax') . ': ' . text($facility['fax']); ?><br />
                <br clear='all' />
                <?php
                    $visit = getEncounterDateByEncounter($encounter);
                    $visit_date = $visit['date'];
                ?>
            </td>
                <td>
                <em style="font-weight:bold;font-size:1.4em;"><?php echo text($titleres['fname']) . " " . text($titleres['lname']); ?></em><br />
                <span style="font-weight:bold;"><?php echo xlt('DOB'); ?>:</span> <?php echo text($DOB); ?><br />
                <span style="font-weight:bold;"><?php echo xlt('Generated on'); ?>:</span> <?php echo text(oeFormatShortDate()); ?><br />
                <span style="font-weight:bold;"><?php echo xlt('Visit Date'); ?>:</span> <?php echo oeFormatSDFT(strtotime($visit_date)); ?><br />
                <span style="font-weight:bold;"><?php echo xlt('Provider') . ':</span> ' . text(getProviderName(getProviderIdOfEncounter($encounter))) . '<br />'; ?>

          </td>
        </tr>
    </table>
        <?php
        $output = ob_get_contents();
          ob_end_clean();
          return $output;
}

/**
 *  This function mines the clinical fields for potential diagnostic codes.
 *  The clinical fields are found in table list_options with list_id = Eye_Coding_Fields_
 *  The clinical terms to mine for are in table list_options with list_id = Eye_Coding_Terms
 *  Both can be directly extended by the user the via Administration -> Lists interface.
 *  The Coding_Eye_Form_Terms list includes the following important fields:
 *       Title (the term),
 *       Notes (the form_field to search for the term)
 *       Code(s) (the optional user-defined code).
 *  Terms found in a form_field (Notes) with a predefined Code(s), have that code applied.
 *  Terms found in a form_field (Notes) without a predefined Code(s) are concated with
 *      the text value for the form_field (Notes) (found in the list Coding_Eye_Form_Fields: Notes)
 *      and the codebase is searched for a match.
 *  For example: the term "ptosis" is found in the RUL clinical field, and there is no Code value in the
 *      Coding_Eye_Form_Terms Code(s) field.  Thus openEMR Eye Form searches the active codebases for a match.
 *      The codebases are determined in Administration->Lists->Code Types and include those Codesets flagged
 *      as active and as Diagnostic codes.  The terms "ptosis right upper eyelid" are sent to the
 *      standard openEMR code search engine.
 *  @param string $FIELDS - all the clinical fields we are going to scour for clinical terms to code.
 *  @return outputs directly to screen
 */
function start_your_engines($FIELDS)
{
//pass an assoc array of fields with terms in it and search them
    global $pid;
    global $codes_found;
    global $PMSFH;
    if (!$PMFSH) {
        $PMSFH = build_PMSFH($pid);
    }

    $query = "select * from list_options where list_id ='Eye_Coding_Fields' Order by seq";
    $result = sqlStatement($query);
    while ($fielding = sqlFetchArray($result)) {//build the list of clinical fields to search through
        $fields[$fielding['title']] = $fielding['notes'];
    }

    //get the clinical terms to search for (title) and what field/where to look for it (notes)
    $query = "SELECT * FROM list_options WHERE list_id = 'Eye_Coding_Terms' order by seq";
    $result = sqlStatement($query);
    while ($term_sheet = sqlFetchArray($result)) {
        if ($term_sheet['title'] > '') {
            $newdata =  array (
              'term'        => $term_sheet['title'], //the term =/- possible option_values eg. CSME:DM|IOL|RVO
              'location'    => $term_sheet['notes'], //the form field to search for the term
              'codes'       => $term_sheet['codes']  //the specific code for this term/location, may be blank
              );
            $clinical_terms[] = $newdata;
        }
    }

    if (!$clinical_terms) {
        return;
    }

    $positives = array();
    // Terms are sequenced in the DB (seq) from detailed (more complex descriptions) to a simple (one word) description.
    // $clinical_terms[] is built in this sequence also.
    // eg. "cicatricial ectropion","spastic ectropion", "ectropion".
    // If "cicatricial ectropion" is present in this clinical field (or "spastic ectropion" for that matter),
    // then there is no need to report the presence of "ectropion" as a clinical finding to the Imp/Plan Builder.
    // needle/haystack lookup $positives[] = $term;
    // For terms that overlap other diseases, use term:option|option|option.  These are always last to process.

    foreach ($clinical_terms as $amihere) {
        $option_values = "";
        $term = "";
        $code_found = array();
        if (stripos($amihere['term'], ":") !== false) { //options are stored here code:option_values
            list ($term,$option_values) = explode(":", $amihere['term']);
        } else {
            $term = $amihere['term'];
        }

        if (stripos($FIELDS[$amihere['location']], $term) !== false) {
            //the term is in the field
            $within_array = 'no';
            if (isset($positives[$amihere['location']]) > '') { //true if anything was already found in this field
                //do any of the previous hits found in in this location contain this term already?
                //if so stop; if not, continue onward to add to Builder.
                foreach ($positives[$amihere['location']] as $k => $v) {
                    if (preg_match("/$term/", $v)) {
                        $within_array = 'yes';
                        break;
                    }
                }
            }

            if ($within_array == "yes") {
                continue;
            }

            $positives[$amihere['location']][] = $term;
            if (preg_match("/^(OD)/", $amihere['location'])) {
                $side = "right eye";
                $side1 = "OD";
                $side2 = "R";
            } else {
                $side = "left eye";
                $side1 = "OS";
                $side2 = "L";
            }

            if (($amihere['codes'] > '') && ($option_values == "")) { //did the user define a code for this term in list Eye_Coding_Terms?
                //If so process - we are primed and don't need the carburetor for the Builder
                //eg ICD10:H02.891
                if (stripos($amihere['codes'], ":") !== false) {
                    list($code_type,$code) = explode(":", $amihere['codes']);
                } else {
                    //default to ICD10.  Maybe there is a GLOBALS value for this? Maybe there should be?
                    $code_type = "ICD10";
                }

                $code_found['code'] = $code_type . ":" . $code;
                $code_found['code_type'] = $code_type;
                list($sub_term,$newdata) = coding_engine($term, $code_found, $amihere['location']);
                $codes_found[$sub_term][] = $newdata;
                $positives[$amihere['location']][] = $term;
            } else { //no code was defined, further processing needed.
                if ($option_values) {
                    // This clinical finding (term) can be found in more than one disease process ('option_values')
                    // This special group of terms should be processed last, to identify all
                    // possible results for the Builder.
                    // 'option_values' contains pertinent DXs separated by '|', eg. CSME has option values='DM|IOL|RVO'
                    // Need to see if any of these DX apply and builder Codes_found based on the currently installed list of codes
                    // Currently for most users this is ICD10 but it is built to allow extension to any code sets in openEMR,
                    // including foreign laguage code sets.
                    $options = explode("|", $option_values);
                    $hit_here = "0";

                    foreach ($options as $option) {
                        // if it has mass, try to execute it.
                        $term_now = "";
                        if ($option == "DM") {
                            //This option is run for 3 conditions at present:
                            //CSME/NVD/NVE per eye.  It is the same every time so only do it once, per eye.
                            //Did we already code this?  If so move on.
                            if ($hit_DM[$side1] == '1') {
                                continue;
                            }

                            //Are ICD10 etc codes used in other languages?  Via Snomed?  Via user?
                            //Assume there is a standard for xlt/xla purposes...

                            //is the patient diabetic?
                            //search medical_problem for DM
                            $within_array = "";
                            foreach ($PMSFH[0]['PMH'] as $k => $v) {
                                if (stripos($v['codedesc'], "diabetes")) {
                                    $DM_code = $v['codedesc'];
                                    $within_array = 'yes';
                                }
                            }

                            if ($within_array == "yes") {
                                if (stripos($DM_code, "1")) {
                                    $DM_text = "Type 1 diabetes mellitus";
                                    $label = "DM 1";
                                } elseif (stripos($DM_code, "2")) {
                                    $DM_text = "Type 2 diabetes mellitus";
                                    $label = "DM 2";
                                } else {
                                    $DM_text = "Other specified diabetes";
                                    $label = "DM";
                                }
                            } else { //there is no code that lists diabetes in the PMH
                                continue;
                            }

                            //is there CSME
                            if ($side == "right eye") {
                                $location  = "ODMACULA";
                                $location1 = "ODDISC";
                                $location2 = "ODVESSELS";
                                $location3 = "ODPERIPH";
                            } elseif ($side == "left eye") {
                                $location  = "OSMACULA";
                                $location1 = "OSDISC";
                                $location2 = "OSVESSELS";
                                $location3 = "OSPERIPH";
                            }

                            if ((stripos($FIELDS[$location], "flat") === false) && (stripos($FIELDS[$location], "CSME") !== false)) {
                                //what if they type "no CSME" or "not flat"?
                                $MAC_text = "with macular edema";
                                $hit_CSME = "w/ CSME";
                            } else {
                                $MAC_text = "without macular edema";
                                $hit_CSME = "w/o CSME";
                            }

                            //is there (NVD or NVE) or BDR?
                            //we often document this though as "no NVD/NVE/PDR" which in the current state would mean these would match as hits...
                            $NVD    = "NVD";
                            $NVE    = "NVE";
                            $PPDR   = "PPDR";
                            $PDR    = "PDR";
                            $BDR    = "BDR";
                            $IRMA   = "IrMA";
                            //note stripos() is case-insensitive
                            if (
                                ( (stripos($FIELDS[$location1], $NVD) !== false) ||
                                (stripos($FIELDS[$location2], $NVE) !== false) ||
                                (stripos($FIELDS[$location3], $NVE) !== false) ) &&
                                ( (stripos($FIELDS[$location1], "no " . $NVD) !== true) ||
                                (stripos($FIELDS[$location2], "no " . $NVE) !== true) ||
                                (stripos($FIELDS[$location3], "no " . $NVE) !== true) )
                            ) {
                                $DX = "with proliferative";
                                $label = $label . "w/ PDR " . $hit_CSME;
                                $hit_PDR[$side] = '1';
                            } elseif (
                                (stripos($FIELDS[$location2], $PPDR) !== false) ||
                                (stripos($FIELDS[$location2], $PPDR) !== false) ||
                                (stripos($FIELDS[$location], $IRMA)  !== false) ||
                                (stripos($FIELDS[$location2], $IRMA) !== false) ||
                                (stripos($FIELDS[$location3], $IRMA) !== false)
                            ) {
                                $DX = "with severe nonproliferative";
                                $label = $label . " w/ PPDR " . $hit_CSME;
                                $hit_PPDR[$side] = '1';
                            } elseif (
                                (stripos($FIELDS[$location], $BDR) !== false) ||
                                (stripos($FIELDS[$location2], $BDR) !== false)
                            ) {
                                    $trace = "tr";
                                if (
                                    (stripos($FIELDS[$location], $trace . " " . $BDR) !== false) ||
                                    (stripos($FIELDS[$location2], "+1 " . $BDR) !== false) ||
                                    (stripos($FIELDS[$location], $trace . " " . $BDR) !== false) ||
                                    (stripos($FIELDS[$location2], "+1 " . $BDR) !== false)
                                ) {
                                    $DX = "with mild nonproliferative";
                                    $label = $label . " w/ mild BDR " . $hit_CSME;
                                    $hit_BDR[$side] = '1';
                                } else {
                                    $DX = "with moderate nonproliferative";
                                    $label = $label . " w/ mod BDR " . $hit_CSME;
                                    $hit_BDR[$side] = '1';
                                }
                            }

                            $code_found = coding_carburetor($DM_text, $MAC_text);
                            if (isset($code_found)) { //there are matches, present them to the engine
                                foreach ($code_found as $found) {
                                    list($sub_term,$newdata) = coding_engine($label, $found, $amihere['location'], $side1);
                                    // The carburetor is a simple machine - it has no boolean options -
                                    // so "with" and "without" match a search for "with"...
                                    // We need to be specific to whittle down the options.
                                    if ((stripos($newdata['codedesc'], $MAC_text)) && (stripos($newdata['codedesc'], $DX))) {
                                        //does this code already exist for the other eye (right eye is always first)?
                                        //if so, change OD to OU and skip adding this code.
                                        //or is there a code for both eyes?
                                        if ($side1 == "OS") {
                                            $count = '0';
                                            for ($i = 0; $i < count($codes_found[$sub_term]); $i++) {
                                                $swap = "OD";
                                                $codes_found[$sub_term][$i]['title'] = str_replace($swap, "OU", $codes_found[$sub_term][$i]['title']);
                                                break 2;
                                                $count++;
                                            }
                                        }

                                        $codes_found[$sub_term][] = $newdata;
                                        $positives["DM" . $side1][] = $newdata['code'];
                                        $hit_DM[$side1] = '1';
                                    }
                                }
                            }
                        } elseif ($option == "RVO") {
                            //is there a CRVO or BRVO associated?
                            //check Clinical fields for these terms
                            if ($side == "right eye") {
                                $location = "ODVESSELS";
                            } else {
                                $location = "OSVESSELS";
                            }

                            if ($hit_RVO[$location] == '1') {
                                continue;
                            }

                            if (stripos($FIELDS[$location], "CRVO") !== false) {
                               // this is a CRVO, look up code for location
                                $terms = "CRVO";
                                $code_found = coding_carburetor("central retinal vein", $side);
                                if (isset($code_found)) { //there are matches, present them to the Builder
                                    foreach ($code_found as $found) {
                                        list($sub_term,$newdata) = coding_engine($terms, $found, $location, $side1);
                                        $codes_found[$sub_term][] = $newdata;
                                        $positives[$location][] = "CRVO";
                                        $hit_RVO[$location] = "1";
                                    }
                                }
                            } elseif (stripos($FIELDS[$location], "BRVO") !== false) {
                               // this is a BRVO, look up code for location
                                $code_found = coding_carburetor("branch retinal vein", $side);
                                $terms = "BRVO " . $term;
                                if (isset($code_found)) { //there are matches, present them to the Builder
                                    foreach ($code_found as $found) {
                                        list($sub_term,$newdata) = coding_engine($terms, $found, $location, $side1);
                                        $codes_found[$sub_term][] = $newdata;
                                        $positives[$location][] = "BRVO";
                                        $hit_RVO[$location] = '1';
                                    }
                                }
                            }

                            if (($term == "CSME") && ($hit_RVO[$location] == '1')) {
                                //$code = "H35.81";
                                $code_found = coding_carburetor("retinal", "edema");
                                $terms = "Vein occlusion and macular edema";
                                if (isset($code_found)) { //there are matches, present them to the Builder
                                    foreach ($code_found as $found) {
                                        if ($found['code'] == "ICD10:H35.81") {
                                            list($sub_term,$newdata) = coding_engine($terms, $found, $location, $side1);
                                            $codes_found[$sub_term][] = $newdata;
                                            $positives[$location][] = "CSME";
                                            $hit_RVO_CSME = '1';
                                        }
                                    }
                                }
                            }
                        } elseif ($option == "IOL") {
                            //are they within 3 months of cataract surgery on this eye?  Yag?
                            //search the same side Lens field for term IOL, ? procedure this eye in last 3 months?
                            //search surgery_issue_list or even search the billng engine
                            $query = "select begdate as surg_date from lists where pid=? and type='surgery' and title like '%IOL%' and (title like '%" . xlt($side1) . "%')";
                            $surg = sqlQuery($query, array($pid));
                            if ($surg['surg_date'] > '') {
                                $date1 = date('Y-m-d');
                                //$date2 = (DateTime($surg['surg_date']));
                                //echo $term."\n".$date."\n";continue;
                                $date_diff = strtotime($date1) - strtotime($surg['surg_date']);
                                $interval = $date_diff / (60 * 60 * 24);
                                //$interval was 180, now = 90;
                                if (($interval < '90') && ($term == "CSME")) {
                                    //then this could be post procedure CSME cystoid macular edema  H59.031,2 OD OS
                                    $code_found = coding_carburetor("cystoid macular edema", $side);
                                    if (isset($code_found)) { //there are matches, present them to the Builder
                                        foreach ($code_found as $found) {
                                            $term = "Post-cataract CME";
                                            list($sub_term,$newdata) = coding_engine($term, $found, $amihere['location'], $side1);
                                            $codes_found[$sub_term][] = $newdata;
                                            $positives[$amihere['location']][] = $term;
                                            $hit_IOL = '1';
                                        }

                                        if ($side1 == "OS") {
                                            $count = '0';
                                            for ($i = 0; $i < count($codes_found[$sub_term]); $i++) {
                                                $swap = "OD";
                                                $codes_found[$sub_term][$i]['title'] = str_replace($swap, "OU", $codes_found[$sub_term][$i]['title']);
                                                break 2;
                                                $count++;
                                            }
                                        }
                                    } else {
                                        //echo "Not here. $term.  $interval \n";
                                    }
                                }
                            }
                        } else {
                            //should we have another big Dx often altering what a finding means to a coder; this is a placeholder.
                            //include $option in our code search for this term
                            $term_now = $term . " " . $option;
                            $code_found = coding_carburetor($term_now, $FIELDS[$amihere['location']]);
                            if (isset($code_found)) { //there are matches, present them to the Builder
                                foreach ($code_found as $found) {
                                    list($sub_term,$newdata) = coding_engine($term, $found, $amihere['location'], $side1);
                                    $codes_found[$sub_term][] = $newdata;
                                    $positives[$amihere['location']][] = $term_now;
                                }
                            }
                        }
                    }
                } else {
                    //there are no options and no code identified,
                    //search via carburetor for possible matches to term and description of the form field
                    $code_found = coding_carburetor($term, $FIELDS[$amihere['location']]);
                    if ($code_found !== null) { //there are matches, present them to the Builder
                        foreach ($code_found as $found) {
                            list($sub_term,$newdata) = coding_engine($term, $found, $amihere['location']);
                            $codes_found[$sub_term][] = $newdata;
                            $positives[$amihere['location']][] = $term;
                        }
                    }
                }
            }
        }
    }

    // $codes_found contains the PE/Clinical findings for the Imp/Plan Builder engine.
    // It also gets "horsepower" from the POH/POS and PMH findings.
    // Together these three form the Imp/Plan Builder's suggestions available to the end user to build the Imp/Plan,
    // and by extension one of the data sources for the Coding Engine to populate the fee sheet.
    // When entering a Dx in the PMSFH, it pays to assign these codes up front...
    // The rest is exhaust fumes for the muffler.
    return $codes_found;
}
/**
 *  This function checks a single field for a term and, if found, codes it.
 *  It is not called directly but via the wrapper function start_your_engines().
 *
 *  @param string $term, text to search for in the coding tables.
 *  @param string $field, location where to search. In fact any text that refines the search can be contained here.
 *  @return outputs array of $codes matching the $term & $field
 */
function coding_carburetor($term, $field)
{
    if (!$term || !$field) {
        return;
    }

    $codes = array();
    $code_type = "ICD10";  //only option is PROD (product or drug search) or NOT PROD...
    $search_term = $term . " " . $field;
    $res = main_code_set_search($code_type, $search_term);
    while ($row = sqlFetchArray($res)) {
        $newdata =  array (
                        'code'  =>  $row['code'],
                        'code_text' => $row['code_text'],
                        'code_type' => $row['code_type_name'],
                        'code_desc' => $row['code_desc']
                    );
        $codes[] = $newdata;
    }

    return $codes;
}
/**
 *  This function prepares a code found in a clinical field and returns it in $codes_found format.
 *  @param $code is in the format code_type:code eg. ICD10:H34.811
 *  @param $location is the descriptive name of the clinical field in question
 *  @param $side is optional.  Used as the descriptive text for finding in the Builder
 *      and IMP/Plan if selected from the Builder
 *  @return $subterm,$newdata.  $subterm is used to link items in IMP/PLAN back to its orgin.
 *          $newdata is the array of newly found items to include in the Builder.
 *
 *  This function is not called directly but via the wrapper function start_your_engines().
 */
function coding_engine($term, $code_found, $location, $side = '')
{
    if (strpos($code_found['code'], ":")) {
        list($code_type, $code) = explode(':', $code_found['code']);
    } else {
        $code = $code_found['code'];
        $code_type = "ICD10";//default to ICD10
        $code_found['code'] = $code_type . ":" . $code_found['code'];
    }

    $code_desc = lookup_code_descriptions($code_found['code']);
    $order   = array("\r\n", "\n","\r");
    $code_desc = str_replace($order, '', $code_desc);

    $code_text = text($code_found['code']) . " (" . text($code_desc) . ")";
    $replace = " ";
    $sub_term =  str_replace($replace, "", $term);
    //some codes are bilateral, some not, some are per eyelid.  Comment this out for now:
    //(preg_match("/right/",$code_desc))? $side = xlt('OD{{right eye}}') : $side = xlt('OS{{left eye}}');

    $newdata =  array (
        'title'         => ucfirst($term) . " " . $side,
        'location'      => $location,
        'diagnosis'     => $code,
        'code'          => $code,
        'codetype'      => $code_found['code_type'],
        'codedesc'      => $code_desc,
        'codetext'      => $code_text,
        'PMSFH_link'    => "Clinical_" . $sub_term
    );
    return array($sub_term,$newdata);
}
/**
 *  This is a function to sort an array of dates/times etc
 *  Anything strtotime() can recognize at least.
 */
function cmp($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return (strtotime($a) < strtotime($b)) ? -1 : 1;
}

/**
 *  This function returns the TARGET IOP values for a given ($pid) if ever set, otherwise returns the DEFAULT IOP.
 *  when a value is found for a given field in the Eye Form for a given patient ($pid)
 *  @param $name is in the name of the field
 *
 *  @return $ranges.  A mysqlArray(max_FIELD,max_date,min_date)
 */
function display_GlaucomaFlowSheet($pid, $bywhat = 'byday')
{
    global $PMSFH;
    global $form_folder;
    global $priors;
    global $provider_id;
    global $documents;
    global $encounter_data;
    global $ODIOPTARGET;
    global $OSIOPTARGET;
    global $dated;
    global $visit_date;

    if (!$documents) {
        list($documents) = document_engine($pid);
    }

    $count_OCT = empty($documents['docs_in_name']['OCT']) ? 0 : count($documents['docs_in_name']['OCT']);
    if ($count_OCT > 0) {
        foreach ($documents['docs_in_name']['OCT'] as $OCT) {
            $OCT_date[] = $OCT['docdate'];
        }
    }

    $count_VF = empty($documents['docs_in_name']['VF']) ? 0 : count($documents['docs_in_name']['VF']);
    if ($count_VF > 0) {
        foreach ($documents['docs_in_name']['VF'] as $VF) {
            $VF_date[] = $VF['docdate'];
        }
    }

    $i = 0;
        //if there are no priors, this is the first visit, display a generic splash screen.
    if ((array)$priors) {
        foreach ($priors as $visit) {
            //we need to build the lists - dates_OU,times_OU,gonio_OU,OCT_OU,VF_OU,ODIOP,OSIOP,IOPTARGETS
            if ($visit['date'] == '') {
                continue;
            }

            $old_date_timestamp = strtotime($visit['visit_date']);
            $visit['exam_date'] = date('Y-m-d', $old_date_timestamp);
            $VISITS_date[$i] = $visit['exam_date'];

            //$date_OU[$i] = $visit['exam_date'];

            $time_here = explode(":", $visit['IOPTIME']);
            $time = $time_here[0] . ":" . $time_here[1];
            $time_OU[$i] = $time;

            if (($visit['ODGONIO'] > '') || ($visit['OSGONIO'] > '')) {
                $GONIO_date[$i] = $visit["exam_date"];
                $GONIO[$i]["list"] = '1';
            } else {
                $GONIO[$i]["list"] = '';
            }

            if ($visit['ODIOPAP'] > '') {
                $ODIOP[$i]['IOP'] = $visit['ODIOPAP'];
                $ODIOP[$i]['method'] = "AP";
            } elseif ($visit['ODIOPTPN'] > '') {
                $ODIOP[$i]['IOP'] = $visit['ODIOPTPN'];
                $ODIOP[$i]['method'] = "TPN";
            } else {
                $ODIOP[$i]['IOP'] = "";
            }

            if ($visit['OSIOPAP'] > '') {
                $OSIOP[$i]['IOP'] = $visit['OSIOPAP'];
                $OSIOPMETHOD[$i]['method'] = "AP";
            } elseif ($visit['OSIOPTPN'] > '') {
                $OSIOP[$i]['IOP'] = $visit['OSIOPTPN'];
                $OSIOPMETHOD[$i]['method'] = "TPN";
            } else {
                $OSIOP[$i]['IOP'] = "null";
                //we are ignoring finger tension for graphing purposes but include this should another form of IOP measurement arrive...
                //What about the Triggerfish contact lens continuous IOP device for example...
            }

            //build the Target line values for each date.
            $j =  $i - 1;

            if ($visit['ODIOPTARGET'] > '') {
                $ODIOPTARGETS[$i] = $visit['ODIOPTARGET'];
            } elseif ($i == 0) { //this should be set on in view/page load.  Keep for reports though...
                list($ODIOPTARGETS[$i], ) = getIOPTARGETS($pid, $id, $provider_id);
            } elseif (!$ODIOPTARGETS[$j]) {
                list($ODIOPTARGETS[$i], ) = getIOPTARGETS($pid, $id, $provider_id);
            } else {
                $ODIOPTARGETS[$i] = $ODIOPTARGETS[$j];
            }

            if ($visit['OSIOPTARGET'] > '') {
                 $OSIOPTARGETS[$i] = $visit['OSIOPTARGET'];
            } elseif ($i == 0) {
                list( ,$OSIOPTARGETS[$i]) = getIOPTARGETS($pid, $id, $provider_id);
            } elseif (!$OSIOPTARGETS[$j]) {
                list( ,$OSIOPTARGETS[$i]) = getIOPTARGETS($pid, $id, $provider_id);
            } else {
                $ODIOPTARGETS[$i] = $ODIOPTARGETS[$j];
            }
            $i++;
        }
    } else { //there are no priors, get info for this visit
        $VISITS_date[0] = $dated;
        if ($encounter_data['IOPTIME']) {
            $time_here = explode(":", $encounter_data['IOPTIME']);
            $time = $time_here[0] . ":" . $time_here[1];
            $time_OU[] = $time;
        }

        if ($encounter_data['ODGONIO'] || $encounter_data['OSGONIO']) {
            $GONIO_date[$i] = $dated;
        }

        $ODIOP[$i]['time'] = $time;
        $OSIOP[$i]['time'] = $time;
        //$IOPTARGET['visit_date'] = $encounter_data['exam_date'];
        if ($encounter_data['ODIOPAP'] > '') {
            if (!is_int($encounter_data['ODIOPAP'])) {
                $ODIOP[$k]['IOP'] = '';
            } else {
                $ODIOP[$i]['IOP'] = $encounter_data['ODIOPAP']; }
            $ODIOP[$i]['method'] = "AP";
        } elseif ($encounter_data['ODIOPTPN'] > '') {
            $ODIOP[$i]['IOP'] = $encounter_data['ODIOPTPN'];
            $ODIOP[$i]['method'] = "TPN";
        }

        if ($encounter_data['OSIOPAP'] > '') {
            if (!is_int($encounter_data['OSIOPAP'])) {
                $OSIOP[$k]['IOP'] = '';
            } else {
                $OSIOP[$i]['IOP'] = $encounter_data['OSIOPAP']; }
            $OSIOP[$i]['method'] = "AP";
        } elseif ($encounter_data['OSIOPTPN'] > '') {
            $OSIOP[$i]['IOP'] = $encounter_data['OSIOPTPN'];
            $OSIOP[$i]['method'] = "TPN";
        } else {
            //we are ignoring finger tension for graphing purposes but include this should another form of IOP measurement arrive...
            //What about the Triggerfish contact lens continuous IOP device for example...  iCare device, etc
        }

        if ($encounter_data['ODIOPTARGET'] > '0') {
            $ODIOPTARGETS[$i] = $encounter_data['ODIOPTARGET'];
        } else {
            list($ODIOPTARGET, ) = getIOPTARGETS($pid, $id, $provider_id);
            $ODIOPTARGETS[$i] = $ODIOPTARGET;
            $encounter_data['ODIOPTARGET'] = $ODIOPTARGET;
        }

        if ($encounter_data['OSIOPTARGET']) {
            $OSIOPTARGETS[$i] = $encounter_data['ODIOPTARGET'];
        } else {
            list( ,$OSIOPTARGET ) = getIOPTARGETS($pid, $id, $provider_id);
            $OSIOPTARGETS[$i] = $OSIOPTARGET;
            $encounter_data['OSIOPTARGET'] = $OSIOPTARGET;
        }
    }

    //There are visits for testing only, no IOP.
    //We need to insert these dates into the arrays created above.
    //recreate them to include the testing only dates, placing null values for those dates if not done.

    //can't merge empty arrays
    $list = array();
    $arrs[] = $OCT_date;
    $arrs[] = $VF_date;
    $arrs[] = $GONIO_date;
    $arrs[] = $VISITS_date;

    foreach ($arrs as $arr) {
        if (is_array($arr)) {
            $list = array_merge($list, $arr);
        }
    }

    $date_OU = array_unique($list);
    usort($date_OU, "cmp");
    $times_OU = $time_OU;
    usort($times_OU, "cmp");

    for ($a = 0; $a < count($date_OU); $a++) {
        if (!empty($GONIO_date)) {
            foreach ($GONIO_date as $GONIO) {
                if ($date_OU[$a] == $GONIO) {
                    $GONIO_values[$a] = "1";
                    break;
                }
            }
        }

        if (!$GONIO_values[$a]) {
            $GONIO_values[$a] = "";
        }

        if ($count_OCT > 0) {
            foreach ($OCT_date as $OCT) {
                if ($date_OU[$a] == $OCT) {
                    $OCT_values[$a] = "1";
                    break;
                }
            }
        }

        if (!$OCT_values[$a]) {
            $OCT_values[$a] = "";
        }

        if ($count_VF > 0) {
            foreach ($VF_date as $VF) {
                if ($date_OU[$a] == $VF) {
                    $VF_values[$a] = "1";
                    break;
                }
            }
        }

        if (!$VF_values[$a]) {
            $VF_values[] = "";
        }

        for ($k = 0; $k < count($VISITS_date); $k++) {
            if ($date_OU[$a] == $VISITS_date[$k]) {
                if (preg_match('/[a-z]/i', $ODIOP[$k]['IOP'])) {
                    $ODIOP[$k]['IOP'] = '';}
                if (preg_match('/[a-z]/i', $OSIOP[$k]['IOP'])) {
                    $OSIOP[$k]['IOP'] = '';}
                $OD_values[$a] = "'" . $ODIOP[$k]['IOP'] . "'";
                $OD_methods[$a] = $ODIOP[$k]['method'];
                $OS_values[$a] = $OSIOP[$k]['IOP'];
                $OS_methods[$a] = $OSIOP[$k]['method'];
                $ODIOPTARGET_values[$a] = $ODIOPTARGETS[$k];
                $OSIOPTARGET_values[$a] = $OSIOPTARGETS[$k];
                break;
            }
        }

        if (!$OD_values[$a]) {
            $OD_values[$a] = '';
        }

        if (!$OS_values[$a]) {
            $OS_values[$a] = '';
        }

        if (!$OD_methods[$a]) {
            $OD_methods[$a] = "";
        }

        if (!$OS_methods[$a]) {
            $OS_methods[$a] = "";
        }

        if (!$ODIOPTARGET_values[$a]) {
            $ODIOPTARGET_values[$a] = "";
        }

        if (!$OSIOPTARGET_values[$a]) {
            $OSIOPTARGET_values[$a] = "";
        }
    }

    for ($a = 0; $a < count($times_OU); $a++) {
        for ($k = 0; $k < count($ODIOP); $k++) {
            if ($times_OU[$a] == $time_OU[$k]) {
                $OD_time_values[$a] = $ODIOP[$k]['IOP'];
                $OS_time_values[$a] = $OSIOP[$k]['IOP'];
                break;
            }
        }

        if (( !$OD_time_values[$a]) || (!is_int($OD_time_values[$a]))) {
            $OD_time_values[$a] = "";
        }

        if (!$OS_time_values[$a]) {
            $OS_time_values[$a] = "";
        }
    }

    $dates_OU = "'" . implode("','", $date_OU) . "'";
    $OD_values = implode(",", $OD_values);
    $OS_values = implode(",", $OS_values);
    $OCT_values = "'" . implode("','", $OCT_values) . "'";
    $VF_values = "'" . implode("','", $VF_values) . "'";
    $GONIO_values =  "'" . implode("','", $GONIO_values) . "'";
    $IOPTARGET_values =  implode(",", $ODIOPTARGET_values);
    $times_OU = "'" . implode("','", $times_OU) . "'";
    $OD_time_values = "'" . implode("','", $OD_time_values) . "'";
    $OS_time_values = "'" . implode("','", $OS_time_values) . "'";

    ?> <p style="font-weight:bold;"> <?php echo xlt('Glaucoma Zone'); ?>:</p>
        <br />
        <br />
       <span class="closeButton fas fa-times" id="Close_IOP" name="Close_IOP"></span>
        <div id="GFS_table" name="GFS_table" class="table-responsive borderShadow" style="position:relative;display:table;float:left;margin-top:10px;padding:15px;text-align:left;vertical-align:center;width:30%;">
            <table class="GFS_table">
                <tr >
                    <td colspan="1" class="GFS_title_1" style="padding-bottom:3px;border:none;" nowrap><?php echo xlt('Current Target'); ?>:
                        <td class='GFS_title center' style="padding-bottom:3px;border:none;" nowrap><?php echo xlt('OD{{right eye}}'); ?>: <input type="text" style="width: 20px;" name="ODIOPTARGET" id="ODIOPTARGET" value="<?php echo attr($ODIOPTARGET); ?>" /></td>
                        <td class='GFS_title center' style="padding-bottom:3px;border:none;" nowrap><?php echo xlt('OS{{left eye}}'); ?>: <input type="text" style="width: 20px;" name="OSIOPTARGET" id="OSIOPTARGET"  value="<?php echo attr($encounter_data['ODIOPTARGET']); ?>"  /></td>
                </tr>
                <tr>
                    <td colspan="3" class="hideme nodisplay">
                        TARGET IOP HISTORY
                    </td>
                </tr>
                <?php
                    //what active meds have a subtype eye?
                    $i = 0;
                    $count_Meds = count($PMSFH[0]['Medication']);
                if ($count_Meds > '0') {
                    foreach ($PMSFH[0]['Medication'] as $drug) {
                        if (($drug['row_subtype'] == "eye") && (strtotime($drug['enddate']) < strtotime($visit_date) ) && ($drug['status'] != "Inactive")) {
                            $current_drugs .= "<tr><td colspan='2' class='GFS_td_1'><span name='QP_PMH_" . attr($drug['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($drug['rowid']) . "'
                                      onclick=\"alter_issue2(" . attr_js($drug['rowid']) . ",'Medication','" . $i . "');\">" . text($drug['title']) . "</span></td>
                                      <td class='GFS_td'>" . text(oeFormatShortDate($drug['begdate'])) . "</td></tr>";
                        } elseif (($drug['row_subtype'] == "eye") && (!empty($drug['enddate']))) {//what meds have a subtype eye that are discontinued?
                            $hideme = "hideme_drugs nodisplay";
                            $FAILED_drugs .= "<tr class='" . $hideme . "'><td colspan='1' class='GFS_td_1'><span name='QP_PMH_" . attr($drug['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($drug['rowid']) . "'
                                      onclick=\"alter_issue2(" . attr_js($drug['rowid']) . ",'Medication','" . $i . "');\">" . text($drug['title']) . "</span></td>
                                      <td class='GFS_td'>" . text(oeFormatShortDate($drug['begdate'])) . "</td><td class='GFS_td'>" . text(oeFormatShortDate($drug['enddate'])) . "</td></tr>";
                        }

                        $i++;
                    }

                    if (!$current_drugs) {
                        $current_drugs = "<tr><td colspan='3' class='GFS_td_1' style='text-align:center;'>" . xlt('None documented') . "</td></tr>";
                        $no_drugs = '1';
                    }

                    foreach ($PMSFH[0]['Medication'] as $drug) {
                        if (($drug['row_subtype'] == "eye") && (!empty($drug['enddate']))) {
                            $FAILED_drug .= "<li>" . text($drug['title']) . "</li>";
                        }
                    }
                }
                ?>
                <tr class="GFS_tr">
                    <td colspan="2" class="GFS_title"><?php echo xlt('Current Eye Meds'); ?>:</td>
                        <?php  ($no_drugs) ? ($meds_here = '') : $meds_here = xlt('Start'); ?>
                    <td class="GFS_title" style="text-align:center;"><?php echo $meds_here; ?></td>
                    <?php
                    if ($FAILED_drugs) {
                        echo '<td><span class="right toggleme" id="toggle_drugs"><i class="fa fa-toggle-down"></i></span></td>';
                    } ?>
                </tr>
                <?php
                echo $current_drugs;
                if ($FAILED_drugs) {
                    echo '<tr class="' . $hideme . '"><td class="GFS_title" colspan="1">' . xlt('Prior Eye Meds') . '</td><td class="GFS_title" style="text-align:center;">' . xlt('Start') . '</td><td  style="text-align:center;" class="GFS_title">End</td></tr>';
                }

                echo $FAILED_drugs;

                //start VF section
                if ($count_VF > '0') { //need to decide how many to show on open, and hide the rest?  For now the first only.
                    $count = 0;
                    foreach ($documents['docs_in_name']['VF'] as $VF) {
                        if ($count < 1) {
                            //    $episode .= '<a onclick="openNewForm(\''.$GLOBALS['webroot'].'/controller.php?document&view&patient_id='.$pid.'&doc_id='.$id_to_show.'\',\'Documents\');"><img src="../../forms/'.$form_folder.'/images/jpg.png" class="little_image" /></a>';

                            $current_VF = '<tr><td class="GFS_td_1 blue">
                                <a onclick="openNewForm(\'' . $GLOBALS['webroot'] . '/controller.php?document&view&patient_id=' . attr($pid) . '&doc_id=' . attr($VF['id']) . '\',\'Documents\');">
                                <img src="../../forms/' . $form_folder . '/images/jpg.png" class="little_image" style="width:15px; height:15px;" /></a>
                                </td>
                                <td class="GFS_td_1">' . $VF['docdate'] . '</td>
                                </tr>';
                        } else {
                            $old_VFs .= '<tr><td class="GFS_td_1 hideme_VFs nodisplay"">
                                <a onclick="openNewForm(\'' . $GLOBALS['webroot'] . '/controller.php?document&view&patient_id=' . attr($pid) . '&doc_id=' . attr($VF['id']) . '\',\'Documents\');">
                                <img src="../../forms/' . $form_folder . '/images/jpg.png" class="little_image" style="width:15px; height:15px;" /></a></td>
                                <td class="hideme_VFs nodisplay GFS_td_1">' . $VF['docdate'] . '</td></tr>';
                        }

                        $count++;
                    }
                } else {
                    $current_VF = "<tr><td colspan='3' class='GFS_td_1' style='text-align:center;'>" . xlt('Not documented') . "</td></tr>";
                }
                ?>
                <tr class="GFS_tr">
                    <td colspan="3" class="GFS_title"><?php echo xlt('Visual Fields'); ?>:
                    <?php
                    if ($old_VFs) {
                        echo '<td><span class="top right" id="toggle_VFs"><i class="fa fa-toggle-down"></i></span></td>';
                    }
                    ?>
                </tr>
                <?php echo $current_VF . $old_VFs;
                //end VF section

                //start Optic Nerve section
                ?>
                <tr>
                    <td colspan="3" class="GFS_title"><?php echo xlt('Optic Nerve Analysis'); ?>:&nbsp;
                        <?php
                        if ($count_OCT > '0') { //need to decide how many to show on open, and hide the rest?  For now show first, hide rest.
                            $count = 0;
                            foreach ($documents['docs_in_name']['OCT'] as $OCT) {
                                //get encounter date from encounter id
                                if ($count < 1) {
                                    $current_OCT = '<tr>
                                            <td class="GFS_td_1">
                                            <a onclick="openNewForm(\'' . $GLOBALS['webroot'] . '/controller.php?document&view&patient_id=' . attr($pid) . '&doc_id=' . attr($OCT['id']) . '\',\'Documents\');"><img src="../../forms/' . $form_folder . '/images/jpg.png" class="little_image" style="width:15px; height:15px;" /></a>
                                            </td>
                                            <td class="GFS_td_1">' . $OCT['docdate'] . '</td>
                                        </tr>
                                        ';
                                } else {
                                    $old_OCTs .= '<tr><td class="hideme_OCTs nodisplay GFS_td_1">
                                                <a onclick="openNewForm(\'' . $GLOBALS['webroot'] . '/controller.php?document&view&patient_id=' . attr($pid) . '&doc_id=' . attr($OCT['id']) . '\',\'Documents\');"><img src="../../forms/' . $form_folder . '/images/jpg.png" class="little_image" style="width:15px; height:15px;" /></a>
                                                </td><td class="hideme_OCTs nodisplay GFS_td_1">' . $OCT['docdate'] . '</td></tr>';
                                }
                                $count++;
                            }
                        } else {
                            $current_OCT = "<tr><td colspan='3' class='GFS_td_1' style='text-align:center;'>" . xlt('Not documented') . "</td></tr>";
                        }

                        if ($old_OCTs) {
                            echo '<td><span class="top right " id="toggle_OCTs"><i class="fa fa-toggle-down"></i></span></td>';
                        }

                        echo "</tr>";
                        echo $current_OCT . $old_OCTs;

                        $count = 0;
                        $hideme = '';
                        foreach ($priors as $visit) {
                            if (($visit['ODGONIO'] > " ") || ($visit['OSGONIO'] > " ")) { // something is here
                                if ($count > 0) {
                                    $hideme = "hideme_gonios nodisplay";// show the first only, hide the rest for now
                                }

                                $gonios .= "<tr><td class='GFS_td_1 " . $hideme . "'>" . $visit['exam_date'] . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;'>" . $visit['ODGONIO'] . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;'>" . $visit['OSGONIO'] . "</td></tr>";
                                $GONIO_chart .= '"1",';
                                $count++;
                            } else {
                                $GONIO_chart .= ',';
                            }
                        }
                        if (!empty($GONIO)) {
                            $GONIO = chop($GONIO, ",");
                        }
                        if ($count == 0) {
                            $gonios = "<tr><td colspan='3' class='GFS_td_1' style='text-align:center;'>" . xlt('Not documented') . "</td></tr>";
                        }
                        ?>
                <tr>
                    <td class="GFS_title_1" id="GFS_gonios" name="GFS_gonios" style="position:relative;"><?php echo xlt('Gonioscopy'); ?>:</td>
                    <?php
                    if ($count > '0') {
                        echo "<td class='GFS_title center'>" . xlt('OD{{right eye}}') . "</td><td class='GFS_title center'>" . xlt('OS{{left eye}}') . "</td>";
                    } else {
                        echo "<td class='GFS_title center'></td><td class='GFS_title center'></td>";
                    }

                    if ($hideme) {
                        echo '<td><span class="top right" id="toggle_gonios"><i class="fa fa-toggle-down"></i></span></td>';
                    }
                    ?>
                </tr>
                    <?php echo $gonios;

                    $count = '0';
                    $hideme = '';
                    foreach ($priors as $visit) {
                        if (($visit['ODCUP'] > "") || ($visit['OSCUP'] > "")) {
                            if ($count > 0) {
                                $hideme = "hideme_cups nodisplay";
                            }

                            $cups .= "<tr><td class='GFS_td_1 " . $hideme . " '>" . text($visit['exam_date']) . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;'>" . text($visit['ODCUP']) . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;''>" . text($visit['OSCUP']) . "</td></tr>";
                            $DISCS_chart .= '"1",';
                            $count++;
                        } else {
                            $DISCS_chart .= '"",';
                        }
                    }

                    $DISCS_chart = chop($DISCS_chart, ",");
                    if ($count == 0) {
                        $cups = "<tr><td colspan='3' class='GFS_td_1' style='text-align:center;'>" . xlt('Not documented') . "</td></tr>";
                    }
                    ?>
                <tr>
                    <td class="GFS_title_1" id="GFS_cups" name="GFS_cups" title="<?php echo xla('Click this to display/hide additional tests'); ?>"style="position:relative;"><?php echo xlt('Optic Discs'); ?>:
                    <?php
                    if ($hideme) {
                        $plus = '<td><span class="top right" id="toggle_cups"><i class="fa fa-toggle-down"></i></span></td>';
                    }

                    if ($count > '0') {
                        echo "<td class='GFS_title center'>" . xlt('OD{{right eye}}') . "</td><td class='GFS_title center'>" . xlt('OS{{left eye}}') . "</td>" . $plus;
                    } else {
                        echo "<td class='GFS_title center'></td><td class='GFS_title center'></td>";
                    }
                    ?>
                </tr>
                        <?php echo $cups; ?>

            </table>
        </div>
        <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/chart.js/dist/Chart.bundle.min.js"></script>
        <div style="position:relative;float:right; margin: 0px 5px;text-align:center;width:60%;">
            <?php
            if ($priors) {
                if ($bywhat == 'byday') { //$bywhat='byday'
                        $class_1 = "nodisplay";
                        $class_2 = "";
                } else {
                    $class_2 = "nodisplay";
                    $class_1 = "";
                }


                ?>
                <canvas id="canvas_byday" class="<?php echo $class_2; ?>"></canvas>
                <canvas id="canvas_byhour" class="<?php echo $class_1; ?>"></canvas>

                <button id="dailyData" class="<?php echo $class_1; ?>"><?php echo xlt('Show IOP by Date'); ?></button>
                <button id="hourlyData" class="<?php echo $class_2; ?>"><?php echo xlt('Show IOP by Time'); ?></button>
                <script>
                    /**
                     *  Below is the Chart.js code to render IOP by day and IOP by time
                     *
                     */
                    var visit_date = '<?php echo attr($dated); ?>';
                    var dateFormat = 'YYYY-MM-DD';
                    var timeFormat = 'HH tt';
                    var customTooltips = function(tooltip) {
                        // Tooltip Element
                        var tooltipEl = $('#chartjs-tooltip');
                        if (!tooltipEl[0]) {
                            $('body').append('<div id="chartjs-tooltip"></div>');
                            tooltipEl = $('#chartjs-tooltip');
                        }
                            // Hide if no tooltip
                        if (!tooltip.opacity) {
                            tooltipEl.css({
                                          opacity: 0.3
                                          });
                            $('.chartjs-wrap canvas')
                            .each(function(index, el) {
                                  $(el).css('cursor', 'default');
                                  });
                            return;
                        }
                        $(this._chart.canvas).css('cursor', 'pointer');
                            // Set caret Position
                        tooltipEl.removeClass('above below no-transform');
                        if (tooltip.yAlign) {
                            tooltipEl.addClass(tooltip.yAlign);
                        } else {
                            tooltipEl.addClass('no-transform');
                        }

                            // Set Text
                        if (tooltip.body) {
                            var innerHtml = [
                                             (tooltip.beforeTitle || []).join('\n'), (tooltip.title || []).join('\n'), (tooltip.afterTitle || []).join('\n'), (tooltip.beforeBody || []).join('\n'), (tooltip.body || []).join('\n'), (tooltip.afterBody || []).join('\n'), (tooltip.beforeFooter || [])
                                             .join('\n'), (tooltip.footer || []).join('\n'), (tooltip.afterFooter || []).join('\n')
                                             ];
                            tooltipEl.html(innerHtml.join('\n'));
                        }

                            // Find Y Location on page
                        var top = 0;
                        if (tooltip.yAlign) {
                            if (tooltip.yAlign == 'above') {
                                top = tooltip.y - tooltip.caretHeight - tooltip.caretPadding;
                            } else {
                                top = tooltip.y + tooltip.caretHeight + tooltip.caretPadding;
                            }
                        }
                        var position = $(this._chart.canvas)[0].getBoundingClientRect();
                            // Display, position, and set styles for font
                        tooltipEl.css({
                                      opacity: 0.5,
                                      width: tooltip.width ? (tooltip.width + 'px') : 'auto',
                                      left: position.left + tooltip.x + 'px',
                                      top: position.top + top + 'px',
                                      fontFamily: tooltip._fontFamily,
                                      fontSize: tooltip.fontSize,
                                      fontStyle: tooltip._fontStyle,
                                      padding: tooltip.yPadding + 'px ' + tooltip.xPadding + 'px',
                                      });
                    };

                    var config_byhour = {
                        type: 'line',
                        data: {
                            labels: [<?php echo $times_OU; ?>],
                            datasets: [{
                                   label: "OD",
                                   data: [<?php echo $OD_time_values; ?>],
                                   fill: false,
                                   borderColor : "#44a3a7",
                                   backgroundColor : "#44a3a7",
                                   pointBorderColor : "#055d2b",
                                   pointBackgroundColor : "#44a3a7",
                                   pointBorderWidth : 3,
                                   lineTension: 0.3,
                                   borderCapStyle: 'butt',
                                   borderDashOffset: 0.0,
                                   borderJoinStyle: 'miter',
                                   pointHoverRadius: 5,
                                   pointHoverBorderWidth: 2,
                                   pointRadius: 1,
                                   pointHitRadius: 3
                                   }, {
                                   label: 'OS',
                                   data: [<?php echo $OS_time_values; ?>],
                                   fill: false,
                                   lineTension: 3,
                                   borderColor : "#000099",
                                   backgroundColor : "#000099",
                                   pointBorderColor : "black",
                                   pointBackgroundColor : "#000099",
                                   pointBorderWidth : 3,
                                   lineTension: 0.3,
                                   borderCapStyle: 'butt',
                                   borderJoinStyle: 'miter',
                                   pointHoverRadius: 5,
                                   pointHoverBorderWidth: 2,
                                   pointRadius: 1,
                                   pointHitRadius: 3,
                                   }]
                            },
                        options: {
                            responsive: true,
                            animation: false,
                            onAnimationComplete: function () {
                                    // prevents the update from triggering an infinite loop
                                if (!this.clearCycle) {
                                    this.clearCycle = true;

                                    this.datasets.forEach(function (dataset) {
                                                          dataset.points.forEach(function (point) {
                                                                                 if (point.value === 0) {
                                                                                 point.display = false;
                                                                                 point.hasValue = function () {
                                                                                 return false;
                                                                                 }
                                                                                 }
                                                                                 })
                                                          })
                                    this.update();
                                }
                            else
                                delete this.clearCycle;
                            },
                            scaleShowHorizontalLines: true,
                            title:{
                            display:true,
                            text:'<?php echo xla("Intraocular Pressures") . " (" . xla("mmHg") . ") by Hour"; ?>'
                            },
                            tooltips: {
                            mode: 'label'
                            },
                            hover: {
                            mode: 'dataset'
                            },
                            scales: {
                                xAxes:  [{
                                     type: "time",
                                     time: {
                                     format: "HH:mm",
                                     unit: 'hour',
                                     unitStepSize: 2,
                                     displayFormats: {
                                     'minute': 'h:mm a',
                                     'hour': 'h:mm a'
                                     },
                                     tooltipFormat: 'h:mm a'
                                     },
                                     scaleLabel: {
                                     display: true,
                                     labelString: 'Time'
                                     },
                                     ticks: {
                                     suggestedMin: 4,
                                     suggestedMax: 24,
                                     }
                                     } ],
                                yAxes: [{
                                    type: "linear",
                                    display: true,
                                    position: "left",
                                    //id: "y-axis-2",
                                    gridLines:{
                                    display: false
                                    },
                                    labels: {
                                    show:true,

                                    },
                                    scaleLabel: {
                                    display: true,
                                    labelString: 'IOP (mmHg)'
                                    },
                                    ticks: {
                                    suggestedMin: 0,
                                    suggestedMax: 24,
                                    }
                                    }]
                            }
                        }
                    };

                    $('#dailyData').click(function(event) {
                                          event.preventDefault();
                                          $('#canvas_byday').removeClass('nodisplay');
                                          $('#canvas_byhour').addClass('nodisplay');

                                          $('#dailyData').addClass('nodisplay');
                                          $('#hourlyData').removeClass('nodisplay');
                                          $('#showTesting').addClass('nodisplay');
                                          });
                    $('#hourlyData').click(function(event) {
                                           event.preventDefault();
                                           $('#canvas_byhour').removeClass('nodisplay');
                                           $('#canvas_byday').addClass('nodisplay');
                                           $('#dailyData').removeClass('nodisplay');
                                           $('#hourlyData').addClass('nodisplay');
                                           $('#showTesting').removeClass('nodisplay');
                                           });
                    var config_byday = {
                        type: 'bar',
                        data: {
                        labels: [<?php echo $dates_OU; ?>],
                        datasets: [
                               {
                               type: 'line',
                               label: "Target",
                               data: [<?php echo $IOPTARGET_values; ?>],
                               fill: false,
                               borderColor : "#f28282",
                               backgroundColor : "#f28282",
                               pointBorderColor : "black",
                               pointBackgroundColor : "#f28282",
                               pointBorderWidth : 3,
                               drugs: ["test1\ntimoptic","test2","test3"],
                               yAxisID: 'y-axis-1',
                               lineTension: 0.3,
                               borderCapStyle: 'round',
                               borderDash: [1,5],
                               borderJoinStyle: 'miter',
                               pointHoverRadius: 5,
                               pointHoverBorderWidth: 2,
                               pointRadius: 1,
                               pointHitRadius: 3
                               },{ type: 'line',
                               label: "OD",
                               data: [<?php echo $OD_values; ?>],
                               fill: false,
                               borderColor : "#44a3a7",
                               backgroundColor : "#44a3a7",
                               pointBorderColor : "#055d2b",
                               pointBackgroundColor : "#44a3a7",
                               pointBorderWidth : 3,
                               yAxisID: 'y-axis-1',
                               lineTension: 0.3,
                               borderCapStyle: 'butt',
                               borderDashOffset: 0.0,
                               borderJoinStyle: 'miter',
                               pointHoverRadius: 5,
                               pointHoverBorderWidth: 2,
                               pointRadius: 1,
                               pointHitRadius: 3
                               }, {
                               type: 'line',
                               label: 'OS',
                               data: [<?php echo $OS_values; ?>],
                               fill: false,
                               lineTension: 3,
                               borderColor : "#000099",
                               backgroundColor : "#000099",
                               pointBorderColor : "black",
                               pointBackgroundColor : "#000099",
                               pointBorderWidth : 3,
                               yAxisID: 'y-axis-1',
                               lineTension: 0.3,
                               borderCapStyle: 'butt',
                               borderJoinStyle: 'miter',
                               pointHoverRadius: 5,
                               pointHoverBorderWidth: 2,
                               pointRadius: 1,
                               pointHitRadius: 3,
                               },{
                               type: 'bar',
                               label: "VF",
                               strokeColor: '#5CABFA',
                               fillColor:"#5CABFA",
                               data: [<?php echo $VF_values; ?>],
                               fill: false,
                               backgroundColor: '#5CABFA',
                               borderColor: 'var(--black)',
                               yAxisID: 'y-axis-2'
                               },{
                               type: 'bar',
                               label: "OCT",
                               data: [<?php echo $OCT_values; ?>],//0/null is not done, 1 if performed.
                               fill: true,
                               backgroundColor: '#71B37C',
                               borderColor: 'var(--black)',
                               yAxisID: 'y-axis-2'
                               },{
                               type: 'bar',
                               label: "Gonio",
                               data: [<?php echo $GONIO_values; ?>],
                               fill: false,
                               strokeColor: 'rgba(209, 30, 93, 0.3)',
                               fillColor:'rgba(209, 30, 93, 0.3)',
                               backgroundColor: 'red',
                               borderColor: 'var(--black)',
                               yAxisID: 'y-axis-2'
                               }]
                        },
                        options: {
                            responsive: true,
                            scaleShowHorizontalLines: true,
                            title:{
                            display: true,
                            text:'<?php echo xla("Intraocular Pressures (mmHg) by Date"); ?>'
                            },
                            tooltips: {
                            enabled: true,
                                //id: "tooltip-1",
                                //backgroundColor: '#FCFFC5',
                                //mode: 'label',
                            enabled: true,
                            shared: false,

                            callbacks: {
                            label: function(tooltipItem, data) {
                                if (tooltipItem.yLabel =='0') {
                                    return data.datasets[tooltipItem.datasetIndex].label + "  ---  "; ;
                                } else if (tooltipItem.yLabel =='1') {
                                    return data.datasets[tooltipItem.datasetIndex].label + " <?php echo xlt('performed'); ?>";
                                } else if (tooltipItem.yLabel > '1') {
                                    return data.datasets[tooltipItem.datasetIndex].label + ": "+tooltipItem.yLabel;
                                }
                                },
                                afterBody: function(tooltipItems, data) {
                                    //console.log(tooltipItems);
                                    //return data.datasets[2].drugs[tagme];
                                }
                            }
                            },
                            hover: {
                                mode: 'label'
                            },
                            scales: {
                            xAxes:  [{
                                 type: "time",
                                 stacked:false,
                                 id: "x-axis-1",
                                 time: {
                                 format: dateFormat,
                                 round: 'day',
                                 tooltipFormat: 'll'
                                 },
                                 categoryPercentage: 0.5,
                                 barPercentage:1.0,
                                 //categoryPercentage:0.3,
                                 scaleLabel: {
                                 display: true,
                                 labelString: 'Date'
                                 },
                                 ticks: {
                                 suggestedMin: 3,
                                 suggestedMax: 6
                                 }
                                 }, ],
                            yAxes: [{
                                type: "linear",
                                display: false,
                                position: "right",
                                id: "y-axis-2",
                                stacked: false,
                                gridLines:{
                                display: false
                                },
                                labels: {
                                show:true,
                                },
                                scaleLabel: {
                                display: false,
                                labelString: 'Testing'
                                },
                                ticks: {
                                suggestedMin: 4,
                                suggestedMax: 4
                                }
                                }, {
                                type: "linear",
                                display: true,
                                position: "left",
                                id: "y-axis-1",
                                gridLines:{
                                display: true
                                },
                                labels: {
                                show:true,
                                },
                                scaleLabel: {
                                display: true,
                                labelString: 'IOP (mmHg)'
                                },
                                ticks: {
                                suggestedMin: 4,
                                suggestedMax: 24,
                                }
                                }]
                            }
                        }
                    };

                    var ctx1 = document.getElementById("canvas_byday").getContext("2d");
                    var ctx2 = document.getElementById("canvas_byhour").getContext("2d");

                    var myLine = new Chart.Bar(ctx1, config_byday);
                    var myLine2 = new Chart(ctx2, config_byhour);
                </script>
                <?php
            } else {
                echo "<div style='text-align:left;padding-left:20px;'><h4>The Glaucoma Flow Sheet graphically displays:
                <ul>
                <li> IOP measurements</li>
                <li> Target IOPs </li>
                <li> related tests (OCT/VF/Gonio)</li>
                <li> diurnal IOP curve</li>
                </ul>
                The graphs are not generated on the initial visit...</h4></div>";
            } ?>
        </div>
    </div>
            <?php
}

# gets the provider from the encounter file , or from the logged on user or from the patient file
function findProvider($pid, $encounter)
{
    $find_provider = sqlQuery("SELECT * FROM form_encounter " .
        "WHERE pid = ? AND encounter = ? " .
        "ORDER BY id DESC LIMIT 1", array($pid,$encounter));
    $providerid = $find_provider['provider_id'];
    if ($providerid < '1') {
       //find the default providerID from the calendar
        $visit_date = date('Y-m-d', strtotime($find_provider['date']));
        $query = "select * from openemr_postcalendar_events where pc_pid=? and pc_eventDate=?";
        $find_provider3 = sqlQuery($query, array($pid,$visit_date));
        $new_providerid = $find_provider3['pc_aid'];
        if (($new_providerid < '1') || (!$new_providerid)) {
            $get_authorized = $_SESSION['userauthorized'];
            if ($get_authorized == 1) {
                $find_provider2 = sqlQuery("SELECT providerID FROM patient_data WHERE pid = ? ", array($pid));
                $new_providerid = $find_provider2['providerID'];
            }
        }

        $providerid = $new_providerid;
        sqlStatement("UPDATE form_encounter set provider_id =? WHERE pid = ? AND encounter = ?", array($providerid,$pid,$encounter));
        sqlStatement("UPDATE patient_data set providerID =? WHERE pid = ?", array($providerid,$pid));
    }

    return $providerid;
}

function generate_lens_treatments($W, $LTs_present)
{
    ob_start();
    $query = "SELECT * FROM list_options where list_id =? and activity='1' ORDER BY seq";
    $TXs_data = sqlStatement($query, array("Eye_Lens_Treatments"));
    $counter = 0;
    $TXs_arr = explode("|", $LTs_present);
    $tabindex = $W . "0144";
    while ($row = sqlFetchArray($TXs_data)) {
        $checked = '';
        $ID = $row['option_id'];
        if (in_array($ID, $TXs_arr)) {
            $checked = "checked='yes'";
        }

        echo "<input type='checkbox' id='TXs_" . $W . "_" . $counter . "' name='LENS_TREATMENTS_" . $W . "[]' $checked value='" . attr($ID) . "' tabindex='$tabindex'> ";
        $label = text(substr($row['title'], 0, 30));
        echo "<label for='TXs_" . $W . "_" . $counter . "' class='input-helper input-helper--checkbox' title='" . attr($row['notes']) . "'>";
        echo $label . "</label><br />";
        $counter++;
        $tabindex++;
    }

    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

/**
 *  Function to display the fields for a currently worn glasses/spectacle Rx.
 *  @param $W - the Rx number, in order of documentation
 */
function generate_specRx($W)
{
    global $pid,$form_id,$encounter,$display_W_width;

    $query  = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? and ENCOUNTER=? and RX_NUMBER =?";
    $wear   = sqlQuery($query, array($pid,$form_id,$encounter,$W));
    if ($wear) {
        $RX_VALUE = '1';
        @extract($wear);
    } else {
        $RX_VALUE = '';
        $display_W = 'nodisplay';
    }

    ob_start();
    ?>
    <input type="hidden" id="W_<?php echo attr($W); ?>" name="W_<?php echo attr($W); ?>" value="<?php echo attr($RX_VALUE); ?>">

    <div id="LayerVision_W_<?php echo attr($W); ?>" name="currentRX" class="refraction current_W borderShadow <?php echo attr($display_W); ?> <?php echo $display_W_width; ?>">
                      <i class="closeButton fas fa-times" id="Close_W_<?php echo attr($W); ?>" name="Close_W_<?php echo attr($W); ?>"
                        title="<?php echo xla('Close this panel and delete this Rx'); ?>"></i>
                      <i class="closeButton_2 fas fa-arrows-alt-h" id="W_width_display_<?php echo attr($W); ?>" name="W_width_display"
                        title="<?php echo xla("Rx Details"); ?>" ></i>
                      <i onclick="top.restoreSession();  doscript('W','<?php echo attr($pid); ?>','<?php echo attr($encounter); ?>','<?php echo attr($W); ?>'); return false;"
                       title="<?php echo xla("Dispense Rx"); ?>" class="closeButton_3 fa fa-print"></i>
                      <i onclick="top.restoreSession();  dispensed('<?php echo attr($pid); ?>');return false;"
                         title="<?php echo xla("List of previously dispensed Spectacle and Contact Lens Rxs"); ?>" class="closeButton_4 fa fa-list-ul"></i>
                      <table id="wearing_<?php echo attr($W); ?>" >
                        <tr>
                          <th colspan="7"><?php echo xlt('Current Glasses'); ?>: #<?php echo attr($W); ?>
                          </th>
                        </tr>
                        <tr>
                          <td></td>
                          <td><i class="fa fa-gamepad" name="reverseme" title="<?php echo xla('Convert between plus and minus cylinder'); ?>"aria-hidden="true" id="revW<?php echo attr($W); ?>" ></i></td>
                          <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                          <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                          <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                          <td><?php echo xlt('Acuity'); ?></td>
                                                    <td name="W_wide"></td>
                          <td name="W_wide" title="<?php echo xla('Horizontal Prism Power'); ?>"><?php echo xlt('HP{{abbreviation for Horizontal Prism Power}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Horizontal Prism Base'); ?>"><?php echo xlt('HB{{abbreviation for Horizontal Prism Base}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Vertical Prism Power'); ?>"><?php echo xlt('VP{{abbreviation for Vertical Prism Power}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Vertical Prism Base'); ?>"><?php echo xlt('VB{{abbreviation for Vertical Prism Base}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Slab Off'); ?>"><?php echo xlt('Slab Off'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Vertex Distance'); ?>"><?php echo xlt('VD{{abbreviation for Vertex Distance}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Monocular Pupillary Diameter - Distance'); ?>"><?php echo xlt('MPD-D{{abbreviation for Monocular Pupillary Diameter - Distance}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Monocular Pupillary Diameter - Near'); ?>"><?php echo xlt('MPD-N{{abbreviation for Monocular Pupillary Diameter - Near}}'); ?></td>

                          <td rowspan="6" class="right">
                            <?php echo xlt('Rx Type{{Type of glasses prescription}}'); ?></span><br />
                            <label for="Single_<?php echo attr($W); ?>" class="input-helper input-helper--checkbox"><?php echo xlt('Single'); ?></label>
                            <input type="radio" value="0" id="Single_<?php echo attr($W); ?>" name="RX_TYPE_<?php echo attr($W); ?>" <?php
                            if ($RX_TYPE == '0') {
                                echo 'checked="checked"';
                            } ?> /></span><br /><br />
                            <label for="Bifocal_<?php echo attr($W); ?>" class="input-helper input-helper--checkbox"><?php echo xlt('Bifocal'); ?></label>
                            <input type="radio" value="1" id="Bifocal_<?php echo attr($W); ?>" name="RX_TYPE_<?php echo attr($W); ?>" <?php
                            if ($RX_TYPE == '1') {
                                echo 'checked="checked"';
                            } ?> /></span><br /><br />
                            <label for="Trifocal_<?php echo attr($W); ?>" class="input-helper input-helper--checkbox"><?php echo xlt('Trifocal'); ?></label>
                            <input type="radio" value="2" id="Trifocal_<?php echo attr($W); ?>" name="RX_TYPE_<?php echo attr($W); ?>" <?php
                            if ($RX_TYPE == '2') {
                                echo 'checked="checked"';
                            } ?> /></span><br /><br />
                            <label for="Progressive_<?php echo attr($W); ?>" class="input-helper input-helper--checkbox"><?php echo xlt('Prog.{{Progressive lenses}}'); ?></label>
                            <input type="radio" value="3" id="Progressive_<?php echo attr($W); ?>" name="RX_TYPE_<?php echo attr($W); ?>" <?php
                            if ($RX_TYPE == '3') {
                                echo 'checked="checked"';
                            } ?> /></span><br />
                          </td>
                        </tr>
                        <tr>
                          <td rowspan="2"><?php echo xlt('Dist{{distance}}'); ?></td>
                          <td style="font-weight:bold;"><?php echo xlt('OD{{right eye}}'); ?>:</td>
                          <td><?php echo ${"ODSPH_$W"}; ?><input type="text" class="sphere" id="ODSPH_<?php echo attr($W); ?>" name="ODSPH_<?php echo attr($W); ?>"  value="<?php echo attr($ODSPH); ?>" tabindex="<?php echo attr($W); ?>0100"></td>
                          <td><input type="text" class="cylinder" id="ODCYL_<?php echo attr($W); ?>" name="ODCYL_<?php echo attr($W); ?>"  value="<?php echo attr($ODCYL); ?>" tabindex="<?php echo attr($W); ?>0101"></td>
                          <td><input type="text" class="axis" id="ODAXIS_<?php echo attr($W); ?>" name="ODAXIS_<?php echo attr($W); ?>" value="<?php echo attr($ODAXIS); ?>" tabindex="<?php echo attr($W); ?>0102"></td>
                          <td><input type="text" class="acuity" id="ODVA_<?php echo attr($W); ?>" name="ODVA_<?php echo attr($W); ?>" value="<?php echo attr($ODVA); ?>" tabindex="<?php echo attr($W); ?>0108"></td>

                          <td name="W_wide"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODHPD_<?php echo attr($W); ?>" name="ODHPD_<?php echo attr($W); ?>" value="<?php echo attr($ODHPD); ?>" tabindex="<?php echo attr($W); ?>0112"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODHBASE_<?php echo attr($W); ?>" name="ODHBASE_<?php echo attr($W); ?>" value="<?php echo attr($ODHBASE); ?>" tabindex="<?php echo attr($W); ?>0114"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODVPD_<?php echo attr($W); ?>" name="ODVPD_<?php echo attr($W); ?>" value="<?php echo attr($ODVPD); ?>" tabindex="<?php echo attr($W); ?>0116"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODVBASE_<?php echo attr($W); ?>" name="ODVBASE_<?php echo attr($W); ?>" value="<?php echo attr($ODVBASE); ?>" tabindex="<?php echo attr($W); ?>0118"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODSLABOFF_<?php echo attr($W); ?>" name="ODSLABOFF_<?php echo attr($W); ?>" value="<?php echo attr($ODSLABOFF); ?>" tabindex="<?php echo attr($W); ?>0120"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODVERTEXDIST_<?php echo attr($W); ?>" name="ODVERTEXDIST_<?php echo attr($W); ?>" value="<?php echo attr($ODVERTEXDIST); ?>" tabindex="<?php echo attr($W); ?>0122"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODMPDD_<?php echo attr($W); ?>" name="ODMPDD_<?php echo attr($W); ?>" value="<?php echo attr($ODMPDD); ?>" tabindex="<?php echo attr($W); ?>0124"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODMPDN_<?php echo attr($W); ?>" name="ODMPDN_<?php echo attr($W); ?>" value="<?php echo attr($ODMPDN); ?>" tabindex="<?php echo attr($W); ?>0126"></td>
                        </tr>
                        <tr>
                          <td style="font-weight:bold;"><?php echo xlt('OS{{left eye}}'); ?>:</td>
                          <td><input type="text" class="sphere" id="OSSPH_<?php echo attr($W); ?>" name="OSSPH_<?php echo attr($W); ?>" value="<?php echo attr($OSSPH); ?>" tabindex="<?php echo attr($W); ?>0103"></td>
                          <td><input type="text" class="cylinder" id="OSCYL_<?php echo attr($W); ?>" name="OSCYL_<?php echo attr($W); ?>" value="<?php echo attr($OSCYL); ?>" tabindex="<?php echo attr($W); ?>0104"></td>
                          <td><input type="text" class="axis" id="OSAXIS_<?php echo attr($W); ?>" name="OSAXIS_<?php echo attr($W); ?>" value="<?php echo attr($OSAXIS); ?>" tabindex="<?php echo attr($W); ?>0105"></td>
                          <td><input type="text" class="acuity" id="OSVA_<?php echo attr($W); ?>" name="OSVA_<?php echo attr($W); ?>" value="<?php echo attr($OSVA); ?>" tabindex="<?php echo attr($W); ?>0109"></td>

                          <td name="W_wide"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSHPD_<?php echo attr($W); ?>" name="OSHPD_<?php echo attr($W); ?>" value="<?php echo attr($OSHPD); ?>" tabindex="<?php echo attr($W); ?>0113"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSHBASE_<?php echo attr($W); ?>" name="OSHBASE_<?php echo attr($W); ?>" value="<?php echo attr($OSHBASE); ?>" tabindex="<?php echo attr($W); ?>0115"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSVPD_<?php echo attr($W); ?>" name="OSVPD_<?php echo attr($W); ?>" value="<?php echo attr($OSVPD); ?>" tabindex="<?php echo attr($W); ?>0117"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSVBASE_<?php echo attr($W); ?>" name="OSVBASE_<?php echo attr($W); ?>" value="<?php echo attr($OSVBASE); ?>" tabindex="<?php echo attr($W); ?>0119"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSSLABOFF_<?php echo attr($W); ?>" name="OSSLABOFF_<?php echo attr($W); ?>" value="<?php echo attr($OSSLABOFF); ?>" tabindex="<?php echo attr($W); ?>0121"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSVERTEXDIST_<?php echo attr($W); ?>" name="OSVERTEXDIST_<?php echo attr($W); ?>" value="<?php echo attr($OSVERTEXDIST); ?>" tabindex="<?php echo attr($W); ?>0123"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSMPDD_<?php echo attr($W); ?>" name="OSMPDD_<?php echo attr($W); ?>" value="<?php echo attr($OSMPDD); ?>" tabindex="<?php echo attr($W); ?>0125"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSMPDN_<?php echo attr($W); ?>" name="OSMPDN_<?php echo attr($W); ?>" value="<?php echo attr($OSMPDN); ?>" tabindex="<?php echo attr($W); ?>0127"></td>
                        </tr>
                        <tr class="WNEAR">
                          <td rowspan=2><?php echo xlt('Mid{{middle Rx strength}}'); ?>/<br /><?php echo xlt('Near'); ?></td>
                          <td style="font-weight:bold;"><?php echo xlt('OD{{right eye}}'); ?>:</td>
                            <?php echo '<input type="hidden" name="RXStart_' . $W . ' id="RXStart_' . $W . '" value="' . attr($RX_TYPE) . '">'; ?>
                          <td class="WMid"><input type="text" class="presbyopia" id="ODMIDADD_<?php echo attr($W); ?>" name="ODMIDADD_<?php echo attr($W); ?>" value="<?php echo attr($ODMIDADD); ?>"></td>
                          <td class="WAdd2"><input type="text" class="presbyopia" id="ODADD_<?php echo attr($W); ?>" name="ODADD_<?php echo attr($W); ?>" value="<?php echo attr($ODADD); ?>" tabindex="<?php echo attr($W); ?>0106"></td>
                          <td></td>
                          <td><input class="jaeger" type="text" id="NEARODVA_<?php echo attr($W); ?>" name="NEARODVA_<?php echo attr($W); ?>" value="<?php echo attr($NEARODVA); ?>" tabindex="<?php echo attr($W); ?>0110"></td>

                          <td name="W_wide"></td>

                          <td name="W_wide" title="<?php echo xla('Binocular Pupillary Diameter - Distance'); ?>"><?php echo xlt('PD-D{{abbreviation for Binocular Pupillary Diameter - Distance}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Binocular Pupillary Diameter - Near'); ?>"><?php echo xlt('PD-N{{abbreviation for Binocular Pupillary Diameter - Near}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Lens Material'); ?>" colspan="2">
                            <a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=Eye_Lens_Material" target="RTop"
                                  title="<?php echo xla('Click here to edit list of available Lens Materials'); ?>"
                                  name="Lens_mat"><span class="underline"><?php echo xlt('Lens Material'); ?></span> <i class="fa fa-pencil-alt-alt fa-fw"></i> </a>
                          </td>
                          <td name="W_wide2" colspan="4" rowspan="4">
                            <a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=Eye_Lens_Treatments" target="RTop"
                                  title="<?php echo xla('Click here to edit list of available Lens Treatment Options'); ?>"
                                  name="Lens_txs"><span class="underline"><?php echo xlt('Lens Treatments'); ?></span> <i class="fa fa-pencil-alt-alt fa-fw"></i> </a>
                            <br />
                            <?php  echo generate_lens_treatments($W, $LENS_TREATMENTS); ?>
                          </td>
                        </tr>
                        <tr class="WNEAR">
                          <td style="font-weight:bold;"><?php echo xlt('OS{{left eye}}'); ?>:</td>
                          <td class="WMid"><input type="text" class="presbyopia" id="OSMIDADD_<?php echo attr($W); ?>" name="OSMIDADD_<?php echo attr($W); ?>" value="<?php echo attr($OSMIDADD); ?>"></td>
                          <td class="WAdd2"><input type="text" class="presbyopia" id="OSADD_<?php echo attr($W); ?>" name="OSADD_<?php echo attr($W); ?>" value="<?php echo attr($OSADD); ?>" tabindex="<?php echo attr($W); ?>0107"></td>
                          <td></td>
                          <td><input class="jaeger" type="text" id="NEAROSVA_<?php echo attr($W); ?>" name="NEAROSVA_<?php echo attr($W); ?>" value="<?php echo attr($NEAROSVA); ?>" tabindex="<?php echo attr($W); ?>0111"></td>

                          <td name="W_wide"></td>

                          <td name="W_wide"><input type="text" class="prism" id="BPDD_<?php echo attr($W); ?>" name="BPDD_<?php echo attr($W); ?>" value="<?php echo attr($BPDD); ?>" tabindex="<?php echo attr($W); ?>0128"></td>
                          <td name="W_wide"><input type="text" class="prism" id="BPDN_<?php echo attr($W); ?>" name="BPDN_<?php echo attr($W); ?>" value="<?php echo attr($BPDN); ?>" tabindex="<?php echo attr($W); ?>0129"></td>
                          <td name="W_wide" title="<?php echo xla('Lens Material Options'); ?>" colspan="2">
                            <?php echo generate_select_list("LENS_MATERIAL_" . $W, "Eye_Lens_Material", "$LENS_MATERIAL", '', ' ', '', 'restoreSession;submit_form();', '', array('style' => 'width:120px','tabindex' => $W . '0130')); ?>
                          </td>
                        </tr>
                        <tr>
                          <td style="font-weight:bold;" colspan="2"><?php echo xlt('Comments'); ?>:
                          </td>
                          <td colspan="4" class="up"></td>
                        </tr>
                        <tr>
                          <td colspan="6">
                            <textarea id="COMMENTS_<?php echo attr($W); ?>" name="COMMENTS_W" tabindex="<?php echo attr($W); ?>0110"><?php echo text($COMMENTS); ?></textarea>
                          </td>
                          <td colspan="2">
                          </td>
                        </tr>
                      </table>
    </div>
    <?php
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

/**
 * Function to display Refractive Data for an encounter
 * @param array $encounter_data, visit data for a given encounter
 */
function display_refractive_data($encounter_data)
{
    @extract($encounter_data);
    $count_rx = '0';

    $query = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? ORDER BY RX_NUMBER";

    $wear = sqlStatement($query, array($pid,$id));
    while ($wearing = sqlFetchArray($wear)) {
        $count_rx++;
        ${"display_W_$count_rx"} = '';
        ${"ODSPH_$count_rx"} = $wearing['ODSPH'];
        ${"ODCYL_$count_rx"} = $wearing['ODCYL'];
        ${"ODAXIS_$count_rx"} = $wearing['ODAXIS'];
        ${"OSSPH_$count_rx"} = $wearing['OSSPH'];
        ${"OSCYL_$count_rx"} = $wearing['OSCYL'];
        ${"OSAXIS_$count_rx"} = $wearing['OSAXIS'];
        ${"ODMIDADD_$count_rx"} = $wearing['ODMIDADD'];
        ${"OSMIDADD_$count_rx"} = $wearing['OSMIDADD'];
        ${"ODADD_$count_rx"} = $wearing['ODADD'];
        ${"OSADD_$count_rx"} = $wearing['OSADD'];
        ${"ODVA_$count_rx"} = $wearing['ODVA'];
        ${"OSVA_$count_rx"} = $wearing['OSVA'];
        ${"ODNEARVA_$count_rx"} = $wearing['ODNEARVA'];
        ${"OSNEARVA_$count_rx"} = $wearing['OSNEARVA'];
        ${"ODPRISM_$count_rx"} = $wearing['ODPRISM'];
        ${"OSPRISM_$count_rx"} = $wearing['OSPRISM'];
        ${"COMMENTS_$count_rx"} = $wearing['COMMENTS'];
        ${"W_$count_rx"} = '1';
        ${"RX_TYPE_$count_rx"} = $wearing['RX_TYPE'];
    }

    if (!$ODVA || $OSVA || $ARODSPH || $AROSSPH || $MRODSPH || $MROSSPH || $CRODSPH || $CROSSPH || $CTLODSPH || $CTLOSSPH) { ?>
        <table class="refraction_tables">
           <tr class="text-center bold underline" style="background-color: #F3EEC7;">
                <td ><?php echo oeFormatShortDate($date); ?></td>
                <td ><?php echo xlt('Eye'); ?></td>
                <td ><?php echo xlt('Sph{{Sphere}}'); ?></td>
                <td ><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                <td ><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                <td ><?php echo xlt('Prism'); ?></td>
                <td ><?php echo xlt('Acuity'); ?></td>
                <td ><?php echo xlt('Mid{{Middle Distance Add}}'); ?></td>
                <td ><?php echo xlt('ADD{{Near Add}}'); ?></td>
                <td ><?php echo xlt('Acuity'); ?></td>
            </tr>
            <?php
               //$count_rx++;
            for ($i = 1; $i <= $count_rx; $i++) {
                if (${"RX_TYPE_$i"} == "0") {
                    $RX_TYPE = '';
                } elseif (${"RX_TYPE_$i"} == "1") {
                    $RX_TYPE = xlt('Bifocals');
                } elseif (${"RX_TYPE_$i"} == "2") {
                    $RX_TYPE = xlt('Trifocals');
                } elseif (${"RX_TYPE_$i"} == "3") {
                    $RX_TYPE = xlt('Progressive');
                }
                ?>
                <tr>
                    <td class="bold"><?php echo xlt('Wear RX') . " #" . $i . ": "; ?></td>
                    <td class="bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                    <td ><?php echo (text(${"ODSPH_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"ODCYL_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"ODAXIS_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"ODPRISM_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"ODVA_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"ODMIDADD_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"ODADD_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"ODNEARVA_$i"}) ?: "-"); ?></td>
                   </tr>
                   <tr>
                    <td><?php echo $RX_TYPE; ?></td>
                    <td class="bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                    <td ><?php echo (text(${"OSSPH_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"OSCYL_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"OSAXIS_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"OSPRISM_$i"}) ?: "-");  ?></td>
                    <td ><?php echo (text(${"OSVA_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"OSMIDADD_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"OSADD_$i"}) ?: "-"); ?></td>
                    <td ><?php echo (text(${"OSNEARVA_$i"}) ?: "-"); ?></td>
                   </tr>
                    <?php
                    if (${"COMMENTS_$i"}) {
                        ?>
                        <tr>
                           <td></td>
                           <td colspan="2"><?php echo xlt('Comments'); ?>:</td>
                           <td colspan="7"><?php echo text(${"COMMENTS_$i"}); ?></td>
                        </tr>
                        <?php
                    }
                    ?><tr><td colspan="10">--------------------------------------------------------</td></tr>
                    <?php
            }

            if ($ARODSPH || $AROSSPH) { ?>
                   <tr style="border-bottom:1pt solid black;">
                       <td class="bold"><?php echo xlt('AutoRef'); ?></td>
                       <td class="bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                       <td ><?php echo (text($ARODSPH) ?: "-");  ?></td>
                       <td ><?php echo (text($ARODCYL) ?: "-");  ?></td>
                       <td ><?php echo (text($ARODAXIS) ?: "-");  ?></td>
                       <td ><?php echo (text($ARODPRISM) ?: "-");  ?></td>
                       <td ><?php echo (text($ARODVA) ?: "-");  ?></td>
                       <td >-</td>
                       <td ><?php echo (text($ARODADD) ?: "-");  ?></td>
                       <td ><?php echo (text($ARNEARODVA) ?: "-"); ?></td>
                   </tr>
                   <tr>
                       <td>&nbsp;</td>
                       <td class="bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                       <td ><?php echo (text($AROSSPH) ?: "-");  ?></td>
                       <td ><?php echo (text($AROSCYL) ?: "-");  ?></td>
                       <td ><?php echo (text($AROSAXIS) ?: "-");  ?></td>
                       <td ><?php echo (text($AROSPRISM) ?: "-");  ?></td>
                       <td ><?php echo (text($AROSVA) ?: "-");  ?></td>
                       <td >-</td>
                       <td ><?php echo (text($AROSADD) ?: "-");  ?></td>
                       <td ><?php echo (text($ARNEAROSVA) ?: "-"); ?></td>
                   </tr>
                    <?php
                    if (${"COMMENTS_$i"}) {
                        ?>
                        <tr>
                           <td></td><td></td>
                           <td>Comments:</td>
                           <td colspan="7"><?php echo text(${"COMMENTS_$i"}); ?></td>
                        </tr>
                        <?php
                    }?>
                   <tr><td colspan="10">--------------------------------------------------------</td></tr>
                    <?php
            }

            if ($MRODSPH || $MROSSPH) { ?>
                   <tr>
                       <td class="bold"><?php echo xlt('MR (Dry)'); ?></td>
                       <td class="bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                       <td ><?php echo (text($MRODSPH) ?: "-");  ?></td>
                       <td ><?php echo (text($MRODCYL) ?: "-");  ?></td>
                       <td ><?php echo (text($MRODAXIS) ?: "-");  ?></td>
                       <td ><?php echo (text($MRODPRISM) ?: "-");  ?></td>
                       <td ><?php echo (text($MRODVA) ?: "-");  ?></td>
                       <td >-</td>
                       <td ><?php echo (text($MRODADD) ?: "-");  ?></td>
                       <td ><?php echo (text($MRNEARODVA) ?: "-"); ?></td>
                   </tr>
                   <tr></tr>
                   <tr>
                       <td></td>
                       <td class="bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                       <td ><?php echo (text($MROSSPH) ?: "-");  ?></td>
                       <td ><?php echo (text($MROSCYL) ?: "-");  ?></td>
                       <td ><?php echo (text($MROSAXIS) ?: "-");  ?></td>
                       <td ><?php echo (text($MROSPRISM) ?: "-");  ?></td>
                       <td ><?php echo (text($MROSVA) ?: "-");  ?></td>
                       <td >-</td>
                       <td ><?php echo (text($MROSADD) ?: "-");  ?></td>
                       <td ><?php echo (text($MRNEAROSVA) ?: "-"); ?></td>
                   </tr>
                <tr><td colspan="10">--------------------------------------------------------</td></tr>
                    <?php
            }

            if ($CRODSPH || $CROSSPH) { ?>
                   <tr>
                       <td class="bold"><?php echo xlt('CR (Wet)'); ?></td>
                       <td class="bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                       <td ><?php echo (text($CRODSPH) ?: "-");  ?></td>
                       <td ><?php echo (text($CRODCYL) ?: "-");  ?></td>
                       <td ><?php echo (text($CRODAXIS) ?: "-");  ?></td>
                       <td ><?php echo (text($CRODPRISM) ?: "-");  ?></td>
                       <td ><?php echo (text($CRODVA) ?: "-");  ?></td>
                       <td >-</td>
                       <td ><?php echo (text($CRODADD) ?: "-");  ?></td>
                       <td ><?php echo (text($CRNEARODVA) ?: "-"); ?></td>
                   </tr>
                   <tr>
                       <td></td>
                       <td class="bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                       <td ><?php echo (text($CROSSPH) ?: "-");  ?></td>
                       <td ><?php echo (text($CROSCYL) ?: "-");  ?></td>
                       <td ><?php echo (text($CROSAXIS) ?: "-");  ?></td>
                       <td ><?php echo (text($CROSPRISM) ?: "-");  ?>&nbsp;</td>
                       <td ><?php echo (text($CROSVA) ?: "-");  ?></td>
                       <td >-</td>
                       <td ><?php echo (text($CROSADD) ?: "-");  ?></td>
                       <td ><?php echo (text($CRNEAROSVA) ?: "-"); ?></td>
                   </tr>
                <tr><td colspan="10">--------------------------------------------------------</td></tr>
                    <?php
            }

            if ($CTLODSPH || $CTLOSSPH) { ?>
                   <tr class="bold text-center underline">
                       <td></td>
                       <td><?php echo xlt('Eye'); ?></td>
                       <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                       <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                       <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                       <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                       <td><?php echo xlt('Diam{{Diameter}}'); ?></td>
                       <td></td>
                       <td><?php echo xlt('ADD'); ?></td>
                       <td><?php echo xlt('Acuity'); ?></td>
                   </tr>
                   <tr>
                       <td class="bold"><?php echo xlt('CTL'); ?></td>
                       <td class="bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                       <td ><?php echo (text($CTLODSPH) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLODCYL) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLODAXIS) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLODBC) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLODDIAM) ?: "-");  ?></td>
                       <td></td>
                       <td ><?php echo (text($CTLODADD) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLODVA) ?: "-"); ?></td>
                   </tr>
                    <?php if (!empty($CTLODQUANTITY)) { ?>
                       <tr>
                           <td></td>
                           <td colspan="8" class="text-left" style="font-size:10px;"><?php echo text($CTLODQUANTITY); ?></td>
                       </tr>
                    <?php } ?>
                   <tr style="font-size:0.6em;">
                       <td></td>
                       <td colspan="3" class="bold text-left" style="font-size:10px;"><?php echo xlt('Brand'); ?>:<?php echo (text($CTLBRANDOD) ?: "-");  ?></td>
                       <td colspan="2" class="bold text-left" style="font-size:10px;"><?php echo xlt('by{{made by/manufacturer}}'); ?> <?php echo (text($CTLMANUFACTUREROD) ?: "-");  ?></td>
                       <td colspan="3" class="bold text-left" style="font-size:10px;"><?php echo xlt('via{{shipped by/supplier}}'); ?> <?php echo (text($CTLSUPPLIEROD) ?: "-");  ?></td>

                   </tr>
                   <tr>
                       <td></td>
                       <td class="bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                       <td ><?php echo (text($CTLOSSPH) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLOSCYL) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLOSAXIS) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLOSBC) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLOSDIAM) ?: "-");  ?></td>
                       <td></td>
                       <td ><?php echo (text($CTLOSADD) ?: "-");  ?></td>
                       <td ><?php echo (text($CTLOSVA) ?: "-"); ?></td>
                   </tr>
                   <tr style="font-size:9px;">
                       <td></td>
                       <td colspan="3" class="bold text-left" style="font-size:10px;"><?php echo xlt('Brand'); ?>: <?php echo (text($CTLBRANDOS) ?: "-");  ?></td>
                       <td colspan="2" class="bold text-left" style="font-size:10px;"><?php echo xlt('by{{made by/manufacturer}}'); ?> <?php echo (text($CTLMANUFACTUREROS) ?: "-");  ?></td>
                       <td colspan="3" class="bold text-left" style="font-size:10px;"><?php echo xlt('via{{shipped by/supplier}}'); ?> <?php echo (text($CTLSUPPLIEROS) ?: "-");  ?></td>
                   </tr>
                    <?php if (!empty($CTLOSQUANTITY)) { ?>
                        <tr>
                           <td></td>
                           <td colspan="8" class="text-left" style="font-size:10px;"><?php echo text($CTLOSQUANTITY); ?></td>
                       </tr>
                    <?php }
                    if (!empty($COMMENTS)) { ?>
                            <tr>
                                <td></td>
                                <td colspan="8" class="text-left" style="font-size:10px;"><?php echo text($COMMENTS); ?></td>
                            </tr>
                                        <?php }
            }
            ?>
            <tr><td colspan="10">--------------------------------------------------------</td></tr>
        </table>

        <?php
    } ?>

    <?php
    if ($GLAREODVA || $CONTRASTODVA || $ODK1 || $ODK2 || $LIODVA || $PAMODBA) { ?>
      <table>
        <tr>
          <td id="LayerVision_ADDITIONAL" class="refraction <?php echo $display_Add; ?>" style="padding:10px;font-size:10px;">
          <table id="Additional" style="padding:5;font-size:10px;">
            <tr><td colspan="9" style="text-align:left;text-decoration:underline;font-weight:bold;"><?php echo xlt('Additional Data Points'); ?></td></tr>
            <tr class="bold"><td></td>
              <td><?php echo xlt('PH{{Pinhole}}'); ?></td>
              <td><?php echo xlt('PAM{{Potential Acuity Meter}}'); ?></td>
              <td><?php echo xlt('LI{{Laser Interferometry}}'); ?></td>
              <td><?php echo xlt('BAT{{Brightness Acuity Testing}}'); ?></td>
              <td><?php echo xlt('K1{{Keratometry 1}}'); ?></td>
              <td><?php echo xlt('K2{{Keratometry 2}}'); ?></td>
              <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
            </tr>
            <tr><td class="bold"><?php echo xlt('OD{{right eye}}'); ?>:</td>
              <td><?php echo text($PHODVA); ?></td>
              <td><?php echo text($PAMODVA); ?></td>
              <td><?php echo text($LIODVA); ?></td>
              <td><?php echo text($GLAREODVA); ?></td>
              <td><?php echo text($ODK1); ?></td>
              <td><?php echo text($ODK2); ?></td>
              <td><?php echo text($ODK2AXIS); ?></td>
            </tr>
            <tr>
              <td class="bold"><?php echo xlt('OS{{left eye}}'); ?>:</td>
              <td><?php echo text($PHOSVA); ?></td>
              <td><?php echo text($PAMOSVA); ?></td>
              <td><?php echo text($LIOSVA); ?></td>
              <td><?php echo text($GLAREOSVA); ?></td>
              <td><?php echo text($OSK1); ?></td>
              <td><?php echo text($OSK2); ?></td>
              <td><?php echo text($OSK2AXIS); ?></td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr class="bold">
              <td></td>
              <td><?php echo xlt('AxLength{{axial Length}}'); ?></td>
              <td><?php echo xlt('ACD{{anterior chamber depth}}'); ?></td>
              <td><?php echo xlt('PD{{pupillary distance}}'); ?></td>
              <td><?php echo xlt('LT{{lens thickness}}'); ?></td>
              <td><?php echo xlt('W2W{{white-to-white}}'); ?></td>
              <td><?php echo xlt('ECL{{equivalent contact lens power at the corneal level}}'); ?></td>
              <td><?php echo xlt('VABiNoc{{Binocular visual acuity}}'); ?></td>
            </tr>
            <tr><td class="bold"><?php echo xlt('OD{{right eye}}'); ?>:</td>
              <td><?php echo text($ODAXIALLENGTH); ?></td>
              <td><?php echo text($ODACD); ?></td>
              <td><?php echo text($ODPDMeasured); ?></td>
              <td><?php echo text($ODLT); ?></td>
              <td><?php echo text($ODW2W); ?></td>
              <td><?php echo text($ODECL); ?></td>
              <td><?php echo text($VABINOC); ?></td>
            </tr>
            <tr>
              <td class="bold"><?php echo xlt('OS{{left eye}}'); ?>:</td>
              <td><?php echo text($OSAXIALLENGTH); ?></td>
              <td><?php echo text($OSACD); ?></td>
              <td><?php echo text($OSPDMeasured); ?></td>
              <td><?php echo text($OSLT); ?></td>
              <td><?php echo text($OSW2W); ?></td>
              <td><?php echo text($OSECL); ?></td>
              <!--  <td><input type=text id="pend" name="pend" value="<?php echo text($pend); ?>"></td> -->
            </tr>
          </table>
          </td>
        </tr>
      </table>
        <?php
    }
}

/**
 * Function to search recursively through a multi-dimensional array for an item
 * Would be nice if it returned the location in the array too but it it doesn't.
 * Only returns true or false.
 * @param $needle
 * @param $haystack
 * @param bool $strict
 * @return bool*
 */
function in_array_r($needle, $haystack, $strict = false)
{
    if (empty($haystack)) {
        return false;
    }
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}

            /**
             * Function to recursively search through prior eye appointments
             * to discover the target IOPS for this patient.
             *
             * @param $pid = patient id
             * @param $id = form_id values are needed for
             * @param $provider_id = who is the patient's provider is only needed if there is no value anywhere else.
             * @return array (ODIOPTARGET AND OSIOPTARGET to be saved in this encounter
             */
function getIOPTARGETS($pid, $id, $provider_id)
{
    //iterate through this patient's encounters to find IOPTARGETS.
    //if none use provider's default value, or 21.

    $query = "SELECT ODIOPTARGET, OSIOPTARGET from form_eye_vitals where pid=? and id < ? ORDER BY id DESC";
    $result = sqlStatement($query, array($pid, $id));

    while ($row = sqlFetchArray($result)) {
        if (($row['ODIOPTARGET'] > '0') || ($row['OSIOPTARGET'] > '0')) {
            return array($row['ODIOPTARGET'], $row['OSIOPTARGET']);
        }
    }
    $query = "SELECT * FROM `list_options`
            WHERE
            `list_id` LIKE ? AND
            (   option_id = 'ODIOPTARGET' OR
                option_id = 'OSIOPTARGET'  )
             ";
    $result = sqlQuery($query, array("Eye_defaults_" . $provider_id));
    while ($default_TARGETS = sqlFetchArray($result)) {
        if ($default_TARGETS['option_id'] == 'ODIOPTARGET') {
            $ODIOPTARGET = $default_TARGETS["title"];
        }
        if ($default_TARGETS['option_id'] == 'OSIOPTARGET') {
            $OSIOPTARGET = $default_TARGETS["title"];
        }
    }
    if (($ODIOPTARGET > '0') || ($OSIOPTARGET > '0')) {
        return array($ODIOPTARGET, $OSIOPTARGET);
    }
    return array('21','21');
}

?>
