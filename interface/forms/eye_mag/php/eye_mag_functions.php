<?php

/**
 * forms/eye_mag/php/eye_mag_functions.php
 *
 * Functions which extend clinical forms
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016- Raymond Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/drawing_backgrounds.php");
require_once(__DIR__ . "/clinical_helpers.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Lists\IssueTypeRegistry;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\CopyForward;
use OpenEMR\Forms\EyeMag\CopyMode;
use OpenEMR\Forms\EyeMag\EyeFormData;
use OpenEMR\Forms\EyeMag\FamilyHistory;
use OpenEMR\Forms\EyeMag\PmsfhPanel;
use OpenEMR\Forms\EyeMag\Zone;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\Utils\DateFormatterUtils;
use function OpenEMR\Forms\EyeMag\Clinical\display_wear_colgroup;
use function OpenEMR\Forms\EyeMag\Clinical\display_wear_comment_tail_cells;
use function OpenEMR\Forms\EyeMag\Clinical\display_wear_distance_cells;
use function OpenEMR\Forms\EyeMag\Clinical\display_wear_header_cells;
use function OpenEMR\Forms\EyeMag\Clinical\display_wear_lower_cells;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_dilation_med_options;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_iop_method_labels;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_neuro_prior;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_patient_photo;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_options;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_preference;

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
    $output = '';
    $i = 0;

    if ($type == "canvas") {
        $zone .= "_canvas";
    }

    $tables = ['form_eye_hpi','form_eye_ros','form_eye_vitals',
                'form_eye_acuity','form_eye_refraction','form_eye_biometrics',
                'form_eye_external', 'form_eye_antseg','form_eye_postseg',
                'form_eye_neuro','form_eye_locking'];
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
        $result     = sqlStatement($query, [$pid]);
        $counter    = sqlNumRows($result);
        $priors     = [];
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
                $sub_data = sqlStatement($sql, [$prior['id']]);
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
                $priors[$j]['TODO'] = [];
                $priors[$j]['TODO'] = $prior['PLAN'];
            }

            $selected = '';
            $priors[$i]['visit_date'] = $oeexam_date;
            $priors[$i]['exam_date'] = $oeexam_date;
            if ($id_to_show == $prior['form_id']) {
                $selected = 'selected="selected"';
                $current = $i;
            }

            if (!empty($output)) {
                $output .= "<option value='" . attr($prior['id']) . "' " . attr($selected) . ">" . text($oeexam_date) . "</option>";
            } else {
                $output = "<option value='" . attr($prior['id']) . "' " . attr($selected) . ">" . text($oeexam_date) . "</option>";
            }
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

            if (!empty($output)) {
                $output .= "<option value='" . attr($priors[$i]['id']) . "' " . attr($selected) . ">" . text($priors[$i]['exam_date']) . "</option>";
            } else {
                $output = "<option value='" . attr($priors[$i]['id']) . "' " . attr($selected) . ">" . text($priors[$i]['exam_date']) . "</option>";
            }
        }
    }

    $i--;
    $earlier = $current < $i ? $current + 1 : $current;

    $later = $current > '0' ? $current - 1 : "0";


    //current visit =[0]
    if (!$priors[$current]['PLAN']) {
        $priors[$current]['PLAN'] = [];
        $query = "SELECT * from form_eye_mag_orders where form_id=?";
        $orders = sqlStatement($query, [$priors[$earlier]['id']]);
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
        <span onclick=\'$("#PRIOR_' . attr($zone) . '").val("' . attr($priors[$i]['id']) . '").trigger("change");\'
                id="PRIORS_' . attr($zone) . '_earliest"
                name="PRIORS_' . attr($zone) . '_earliest"
                class="fa fa-fast-backward fa-sm PRIORS"
                title="' . attr($zone) . ': ' . attr($priors[$i]['encounter_date']) . '">
        </span>
        &nbsp;
        <span onclick=\'$("#PRIOR_' . attr($zone) . '").val("' . attr($priors[$earlier]['id']) . '").trigger("change");\'
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
        <span onclick=\'$("#PRIOR_' . attr($zone) . '").val("' . attr($priors[$later]['id']) . '").trigger("change");\'
                id="PRIORS_' . attr($zone) . '_plus_one"
                name="PRIORS_' . attr($zone) . '_plus_one"
                class="fa  fa-step-forward PRIORS"
                title="' . attr($zone) . ': ' . attr($priors[$later]['encounter_date']) . '">
        </span>&nbsp;&nbsp;
        <span onclick=\'$("#PRIOR_' . attr($zone) . '").val("' . attr($priors[0]['id']) . '").trigger("change");\'
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
 * @return string|false the ZONE specific HTML for a prior record + "priors_select" widget for the desired zone
 */
function display_PRIOR_section($zone, $orig_id, $id_to_show, $pid, $report = '0')
{
    global $form_folder;

    $query  = "SELECT * FROM form_eye_mag_prefs
                where PEZONE='PREFS' AND id=?
                ORDER BY ZONE_ORDER,ordering";

    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $result = sqlStatement($query, [$session->get('authUserID')]);
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
    $result = sqlQuery($query, [$pid,$id_to_show]);
    @extract($result);
    $ACT ??= null; $ACT10CCDIST ??= null; $ACT10CCNEAR ??= null; $ACT10SCDIST ??= null;
    $ACT10SCNEAR ??= null; $ACT11CCDIST ??= null; $ACT11CCNEAR ??= null; $ACT11SCDIST ??= null;
    $ACT11SCNEAR ??= null; $ACT1CCDIST ??= null; $ACT1CCNEAR ??= null; $ACT1SCDIST ??= null;
    $ACT1SCNEAR ??= null; $ACT2CCDIST ??= null; $ACT2CCNEAR ??= null; $ACT2SCDIST ??= null;
    $ACT2SCNEAR ??= null; $ACT3CCDIST ??= null; $ACT3CCNEAR ??= null; $ACT3SCDIST ??= null;
    $ACT3SCNEAR ??= null; $ACT4CCDIST ??= null; $ACT4CCNEAR ??= null; $ACT4SCDIST ??= null;
    $ACT4SCNEAR ??= null; $ACT5CCDIST ??= null; $ACT5CCNEAR ??= null; $ACT5SCDIST ??= null;
    $ACT5SCNEAR ??= null; $ACT6CCDIST ??= null; $ACT6CCNEAR ??= null; $ACT6SCDIST ??= null;
    $ACT6SCNEAR ??= null; $ACT7CCDIST ??= null; $ACT7CCNEAR ??= null; $ACT7SCDIST ??= null;
    $ACT7SCNEAR ??= null; $ACT8CCDIST ??= null; $ACT8CCNEAR ??= null; $ACT8SCDIST ??= null;
    $ACT8SCNEAR ??= null; $ACT9CCDIST ??= null; $ACT9CCNEAR ??= null; $ACT9SCDIST ??= null;
    $ACT9SCNEAR ??= null; $ANTSEG_COMMENTS ??= null; $ATROPINE ??= null; $CACCDIST ??= null;
    $CACCNEAR ??= null; $CYCLOGYL ??= null; $CYCLOMYDRIL ??= null; $DACCDIST ??= null; $DACCNEAR ??= null;
    $DIL_MEDS ??= null; $EXT_COMMENTS ??= null; $HERTELBASE ??= null; $LADNEXA ??= null; $LBROW ??= null;
    $LCAROTID ??= null; $LCNV ??= null; $LCNVII ??= null; $LLF ??= null; $LLL ??= null; $LMCT ??= null;
    $LMRD ??= null; $LTEMPART ??= null; $LUL ??= null; $LVFISSURE ??= null; $MOTILITYNORMAL ??= null;
    $MOTILITY_LI ??= null; $MOTILITY_LL ??= null; $MOTILITY_LLIO ??= null; $MOTILITY_LLSO ??= null;
    $MOTILITY_LR ??= null; $MOTILITY_LRIO ??= null; $MOTILITY_LRSO ??= null; $MOTILITY_LS ??= null;
    $MOTILITY_RI ??= null; $MOTILITY_RL ??= null; $MOTILITY_RLIO ??= null; $MOTILITY_RLSO ??= null;
    $MOTILITY_RR ??= null; $MOTILITY_RRIO ??= null; $MOTILITY_RRSO ??= null; $MOTILITY_RS ??= null;
    $NEO25 ??= null; $NEURO_COMMENTS ??= null; $NPC ??= null; $ODAC ??= null; $ODCMT ??= null;
    $ODCOINS ??= null; $ODCOLOR ??= null; $ODCONJ ??= null; $ODCORNEA ??= null; $ODCUP ??= null;
    $ODDISC ??= null; $ODGONIO ??= null; $ODHERTEL ??= null; $ODIRIS ??= null; $ODKTHICKNESS ??= null;
    $ODLENS ??= null; $ODMACULA ??= null; $ODNPA ??= null; $ODPERIPH ??= null; $ODREDDESAT ??= null;
    $ODSCHIRMER1 ??= null; $ODSCHIRMER2 ??= null; $ODTBUT ??= null; $ODVESSELS ??= null;
    $ODVITREOUS ??= null; $OSAC ??= null; $OSCMT ??= null; $OSCOINS ??= null; $OSCOLOR ??= null;
    $OSCONJ ??= null; $OSCORNEA ??= null; $OSCUP ??= null; $OSDISC ??= null; $OSGONIO ??= null;
    $OSHERTEL ??= null; $OSIRIS ??= null; $OSKTHICKNESS ??= null; $OSLENS ??= null; $OSMACULA ??= null;
    $OSNPA ??= null; $OSPERIPH ??= null; $OSREDDESAT ??= null; $OSSCHIRMER1 ??= null; $OSSCHIRMER2 ??= null;
    $OSTBUT ??= null; $OSVESSELS ??= null; $OSVITREOUS ??= null; $RADNEXA ??= null; $RBROW ??= null;
    $RCAROTID ??= null; $RCNV ??= null; $RCNVII ??= null; $RETINA_COMMENTS ??= null; $RLF ??= null;
    $RLL ??= null; $RMCT ??= null; $RMRD ??= null; $RTEMPART ??= null; $RUL ??= null; $RVFISSURE ??= null;
    $STEREOPSIS ??= null; $TROPICAMIDE ??= null; $VERTFUSAMPS ??= null; $display_PMSFH ??= null;
    $here ??= null; $i ??= null; $k ??= null; $output ??= null;
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
                    // Reachable only inside the elseif ($zone == "EXT") branch above.
                    echo ($report == '0' ? xlt('Prior Exam') : xlt('EXT'));
                    ?>: </span>
                <br />
                <div id="PRIORS_EXT_left_1">
                    <table>
                        <?php
                            [$imaging, $episode] = display($pid, $encounter ?? '', "EXT");
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

            <?php
                [$display_EXT_view, $marker] = (($EXT_VIEW ?? '') == 1)
                    ? ['wide_textarea', 'fa-minus-square-o']
                    : ['narrow_textarea', 'fa-plus-square-o'];
            ?>
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
                    [$imaging, $episode] = display($pid, $encounter ?? '', "ANTSEG");
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
                    <td><input disabled type="text" name="PRIOR_OSSCHIRMER1" id="PRIOR_OSSCHIRMER1" value="<?php echo attr($OSSCHIRMER1); ?>"></td>
                </tr>
                <tr>
                    <td class="right" title="<?php echo xla('Schirmers II (w/ anesthesia)'); ?>"><?php echo xlt('Schirmer II'); ?></td>
                    <td><input disabled type="text" name="PRIOR_ODSCHIRMER2" id="PRIOR_ODSCHIRMER2" value="<?php echo attr($ODSCHIRMER2); ?>"></td>
                    <td><input disabled type="text" name="PRIOR_OSSCHIRMER2" id="PRIOR_OSSCHIRMER2" value="<?php echo attr($OSSCHIRMER2); ?>"></td>
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
                      <?php foreach (array_chunk(eye_mag_dilation_med_options(), 2) as $dilationRow) { ?>
                      <tr>
                            <?php foreach ($dilationRow as $dilationOption) {
                                $dilationField = $dilationOption['field'];
                                $dilationCurrent = trim((string)(${$dilationField} ?? ''));
                                $dilationChecked = !in_array($dilationCurrent, ['', '0', 'off'], true);
                                $dilationTitle = $dilationOption['title'];
                                $dilationValue = $dilationOption['stored_value'];
                                if ($dilationField === 'TROPICAMIDE' && $dilationCurrent === 'Tropicamide 2.5%') {
                                    $dilationTitle = xl('Tropic 2.5%');
                                    $dilationValue = $dilationCurrent;
                                }
                                $dilationLabel = mb_strimwidth($dilationTitle, 0, 15, '…', 'UTF-8');
                                $dilationId = 'PRIORS_DIL_' . $dilationField;
                                ?>
                        <td>
                            <input disabled type="checkbox" class="dil_drug" id="<?php echo attr($dilationId); ?>"
                                   name="PRIORS_<?php echo attr($dilationField); ?>"
                                   value="<?php echo attr($dilationValue); ?>"
                                   <?php echo $dilationChecked ? "checked='checked'" : ''; ?>>
                            <label for="<?php echo attr($dilationId); ?>" class="input-helper input-helper--checkbox dil_drug_label"
                                   title="<?php echo attr($dilationTitle); ?>"><?php echo text($dilationLabel); ?></label>
                        </td>
                        <?php } ?>
                            <?php if (count($dilationRow) === 1) {
                                ?><td></td><?php } ?>
                      </tr>
                      <?php } ?>
                    </table>
                  </td>
                </tr>
            </table>
        </div>
        <?php
            [$display_ANTSEG_view, $marker] = (($ANTSEG_VIEW ?? '') == '1')
                ? ['wide_textarea', 'fa-minus-square-o']
                : ['narrow_textarea', 'fa-plus-square-o'];
        ?>
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
                [$imaging, $episode] = display($pid, $encounter ?? '', "POSTSEG");
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
                [$imaging, $episode] = display($pid, $encounter ?? '', "NEURO");
                echo $episode;
                ?>
            </table>
        </div>

        <?php
            [$display_RETINA_view, $marker] = (($RETINA_VIEW ?? '') == 1)
                ? ['wide_textarea', 'fa-minus-square-o']
                : ['narrow_textarea', 'fa-plus-square-o'];
        ?>
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
        $output = $report == '0' ? priors_select($zone, $orig_id, $id_to_show, $pid) : '';
        ?>
        <input disabled type="hidden" id="PRIORS_NEURO_prefix" name="PRIORS_NEURO_prefix" value="">
        <span class="closeButton float-right fas fa-times" id="Close_PRIORS_NEURO" name="Close_PRIORS_NEURO"></span>
        <div name="prior_selector"><?php echo $output; ?></div>
        <strong><?php echo xlt('Prior Exam'); ?>:</strong>
        <?php
        // Read the current chart style without seeding preferences while viewing a prior exam.
        $priorMotilityPreference = sqlQuery(
            "SELECT option_id FROM list_options WHERE list_id = 'Eye_Motility_Display'
             AND is_default = 1 AND activity = 1 ORDER BY seq LIMIT 1"
        );
        echo eye_mag_neuro_prior(EyeFormData::row($result), (string)($priorMotilityPreference['option_id'] ?? 'X'));
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
 * @param array $PMSFH associative if it exists
 * @return void
 */
function send_json_values($PMSFH = ""): void
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
 * @return array{array<string, mixed>} access items as $PMSFH[0]
 */
function build_PMSFH($pid): array
{
    global $form_id;

    $PMSFH = [];
    $PMSFH['CHRONIC'] = [];
    /*
     *  Each of these panels is one stock ISSUE_TYPE narrowed by a subtype. This
     *  is an "eye" form: providers would like ophthalmic medical problems listed
     *  separately, so the ISSUE_TYPE 'medical_problem' is split using subtype
     *  "eye" -- but it could be "GYN", "ONC", "GU" etc for whoever wants to
     *  extend this with their own specific "sub"-lists. The same concept covers
     *  Past Ocular Surgery, Oncology Medications, and so on; add a case to
     *  PmsfhPanel rather than another branch here.
     */
    foreach (PmsfhPanel::cases() as $panel) {
        $panel_type = $panel->value;
        $PMSFH[$panel_type] = [];

        $issues = $panel->issuesQuery($pid);
        $row_counter = '0';
        foreach (QueryUtils::fetchRecords($issues->sql, $issues->params) as $row) {
            $rowid = $row['id'];
            $disptitle = text(trim((string) $row['title'])) ? text($row['title']) : "[" . xlt("Missing Title") . "]";
            //  look up the diag codes
            $codetext = "";
            $codedesc = "";
            $codetype = "";
            $code = "";
            if ($row['diagnosis'] != "") {
                $diags = explode(";", (string) $row['diagnosis']);
                foreach ($diags as $diag) {
                    $codedesc = lookup_code_descriptions($diag);
                    if (str_contains($diag, ':')) {
                        [$codetype, $code] = explode(':', $diag);
                    }
                    $order   = ["\r\n", "\n","\r"];
                    $codedesc = str_replace($order, '', $codedesc);
                    $codetext .= text($diag) . " (" . text($codedesc) . ")";
                }
            }

            // calculate the status
            if ($row['outcome'] == "1" && $row['enddate'] != null) {
              // Resolved
                $statusCompute = generate_display_field(['data_type' => '1','list_id' => 'outcome'], $row['outcome']);
            } elseif ($row['enddate'] == null) {
                   $statusCompute = xlt("Active");
            } else {
                   $statusCompute = xlt("Inactive");
            }

            ($row['comments'] != null) ? ($comments = $row['comments']) : ($comments = "");
            $counter_here = count($PMSFH[$panel_type]);
            $newdata =   [
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
            ];
            //let the end user decide on display elsewhere...  This is all about the array itself.
            $PMSFH[$panel_type][] = $newdata;
            if ($row['occurrence'] == '4') {
                $PMSFH['CHRONIC'][] = $newdata;
            }

            $row_counter++;
        }
    }

    /*
     *  SocHx, FH and ROS are PMSFH panels too, but they are assembled from the
     *  history and layout tables rather than from the ISSUE_TYPE array, so they
     *  have no PmsfhPanel case. Declaring their keys here keeps them in the
     *  order the form renders them, after the issue-list panels above.
     */
    $PMSFH['SOCH'] = [];
    $PMSFH['FH'] = [];
    $PMSFH['ROS'] = [];

    //Build the SocHx portion of $PMSFH for this patient.
    //$given ="coffee,tobacco,alcohol,sleep_patterns,exercise_patterns,seatbelt_use,counseling,hazardous_activities,recreational_drugs";
    $result1 = sqlQuery("select * from history_data where pid=? order by date DESC limit 0,1", [$pid]);

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
        if ((preg_match("/^\|?0\|?\|?/", ($result1[$field_id] ?? ''))) || ($result1[$field_id] == '')) {
            continue;
        } else {
            $currvalue = $result1[$field_id];
        }
        $PMSFH['SOCH'][$field_id] = [];
        if ($data_type == 28 || $data_type == 32) {
            $tmp = explode('|', (string) $currvalue);
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
            $PMSFH['SOCH'][$field_id]['resnote'] = nl2br(htmlspecialchars((string) $currvalue, ENT_NOQUOTES));
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
    // The medical columns come from a fixed list, not the History layout:
    // a practice may hide or move those fields there, and the eye exam would
    // then save them without ever showing them again.
    $medicalShortTitles = [
        'relatives_cancer' => xlt("Cancer"),
        'relatives_tuberculosis' => xlt("TB"),
        'relatives_diabetes' => xlt("Diabetes"),
        'relatives_high_blood_pressure' => xlt("HTN{{hypertension}}"),
        'relatives_heart_problems' => xlt("Cor Disease"),
        'relatives_stroke' => xlt("Stroke"),
        'relatives_epilepsy' => xlt("Epilepsy"),
        'relatives_mental_illness' => xlt("Psych"),
        'relatives_suicide' => xlt("Suicide"),
    ];
    $familyMedical = [];
    foreach (FamilyHistory::recordedMedical(is_array($result1) ? $result1 : []) as $field_id => $currvalue) {
        $familyMedical[$field_id] = [
            'resnote' => nl2br(htmlspecialchars($currvalue, ENT_NOQUOTES)),
            // Raw, like the eye rows below: the renderers escape it.
            'display' => FamilyHistory::display($currvalue),
            'short_title' => $medicalShortTitles[$field_id],
        ];
    }
    $PMSFH['FH'] = $familyMedical;

    // Now make some of our own using the usertext11-30 fields
    // These can be customized for specialties but remember this is just an array,
    // you will need to check the code re: how it is displayed elsewhere...
    // For now, just changing the short_titles will display intelligently
    // but it is best to change both in the long run.
    // $PMSFH['FH']['my_term']['display'] = (substr($result1['usertext11'],0,10));
    // $PMSFH['FH']['my_term']['short_title'] = xlt("My Term");

    $PMSFH['FH']['glaucoma']['display'] = (substr($result1['usertext11'] ?? '', 0, 100));
    $PMSFH['FH']['glaucoma']['short_title'] = xlt("Glaucoma");
    $PMSFH['FH']['cataract']['display'] = (substr((string) $result1['usertext12'], 0, 100));
    $PMSFH['FH']['cataract']['short_title'] = xlt("Cataract");
    $PMSFH['FH']['amd']['display'] = (substr((string) $result1['usertext13'], 0, 100));
    $PMSFH['FH']['amd']['short_title'] = xlt("AMD{{age related macular degeneration}}");
    $PMSFH['FH']['RD']['display'] = (substr((string) $result1['usertext14'], 0, 100));
    $PMSFH['FH']['RD']['short_title'] = xlt("RD{{retinal detachment}}");
    $PMSFH['FH']['blindness']['display'] = (substr((string) $result1['usertext15'], 0, 100));
    $PMSFH['FH']['blindness']['short_title'] = xlt("Blindness");
    $PMSFH['FH']['amblyopia']['display'] = (substr((string) $result1['usertext16'], 0, 100));
    $PMSFH['FH']['amblyopia']['short_title'] = xlt("Amblyopia");
    $PMSFH['FH']['strabismus']['display'] = (substr((string) $result1['usertext17'], 0, 100));
    $PMSFH['FH']['strabismus']['short_title'] = xlt("Strabismus");
    $PMSFH['FH']['other']['display'] = (substr((string) $result1['usertext18'], 0, 100));
    $PMSFH['FH']['other']['short_title'] = xlt("Other");

    // Thinking this might be a good place to put in last_retinal exam and last_HbA1C?
    // I don't know enough about the reporting parameters - it is probably something already in OpenEMR?
    // Pull it in if it is and put it where?
    // $PMSFH['SOCH'][$field_id]['resnote'] = nl2br(htmlspecialchars($currvalue,ENT_NOQUOTES));

    // Build ROS into $PMSFH['ROS'] also for this patient.
    // ROS is not static and is directly linked to each encounter
    // True it could be a separate table, but it is currently in form_eye_mag for each visit
    // To use this for any other forms, we should consider making this its own separate table with id,pid and ?encounter link,
    // just like we are doing for Impression Plan.  Maybe we can piggyback onto one of the ROS tables already in OpenEMR?

    //define the ROS area to include = $given
    $given = "ROSGENERAL,ROSHEENT,ROSCV,ROSPULM,ROSGI,ROSGU,ROSDERM,ROSNEURO,ROSPSYCH,ROSMUSCULO,ROSIMMUNO,ROSENDOCRINE,ROSCOMMENTS";
    $ROS_table = "form_eye_ros";
    $query = "SELECT $given from " . $ROS_table . " where id = ?";

    $ROS = sqlStatement($query, [$form_id]);
    $ros_display = [];
    while ($row = sqlFetchArray($ROS)) {
        foreach (explode(',', $given) as $item) {
            $ros_display[$item]['display'] = $row[$item];
        }
    }
    $PMSFH['ROS'] = $ros_display;

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

    return [$PMSFH]; //yowsah!
}
/**
 *  This function uses the complete PMSFH array for a given patient, including the ROS for this encounter
 *  and returns the PMSFH display square.
 *  @param int $rows the number of rows you want to display
 *  @param string $view defaults to white on beige, versus right sliding panel (text on beige only).
 *  @param string $min_height to set min height for the row
 *  @return mixed HTML pane when PMSFH is expanded to two panes.
 */
function display_PMSFH($rows, $view = "pending", $min_height = "min-height:344px;")
{
    global $PMSFH;
    global $pid;
    global $PMSFH_titles;
    $count = [];
    $total_PMSFH = 0;
    $header1 = '';
    $display_PMSFH = [];
    $PMSFH = build_PMSFH($pid);

    ob_start();
    // There are two rows in our PMH section, only one in the side panel.
    // If you want it across the bottom in a panel with 8 rows?  Or other wise?
    // This should be able to handle that too.

    // We are building the PMSFH panel.
    // Let's put half in each of the 2 rows... or try to at least.
    // Find out the number of items present now and put half in each column.
    foreach ($PMSFH[0] as $key => $value) {
        if (!empty($total_PMSFH)) {
            $total_PMSFH += count($PMSFH[0][$key]);
            $total_PMSFH += 2; //add two for the title and a space
        } else {
            $total_PMSFH = count($PMSFH[0][$key]);
            $total_PMSFH = 2; //add two for the title and a space
        }
        $count[$key] = count($PMSFH[0][$key]) + 1;
    }

    //SOCH, FH and ROS are listed in $PMSFH even if negative, only count positives
    foreach ($PMSFH[0]['ROS'] as $key => $value) {
        if (empty($value['display'])) {
            if (!empty($total_PMSFH)) {
                $total_PMSFH--;
            }
            $count['ROS']--;
        }
    }

    foreach ($PMSFH[0]['FH'] as $key => $value) {
        if ($value['display'] == '') {
            if (!empty($total_PMSFH)) {
                $total_PMSFH--;
            } else {
                $total_PMSFH = 0;
            }
            $count['FH']--;
        }
    }

    foreach ($PMSFH[0]['SOCH'] as $key => $value) {
        if (($value['display'] == '') || (($item['display'] ?? '') == 'not_applicable')) {
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
        if (in_array($key, ["FH", "SOCH", "ROS"])) {
            // We are going to build SocHx, FH and ROS separately below since they are different..
            continue;
        }

        $table = '';
        $header = '';
        $sectionTitle = match ($key) {
            "Medication" => xlt("Medications"),
            "Eye Meds"   => xlt("Eye Medications"),
            "Surgery"    => xlt("Past Surgical History"),
            "Allergy"    => xlt("Allergies"),
            "POH"        => xlt("POH"),
            "POS"        => xlt("POS"),
            "PMH"        => xlt("Past Medical History"),
            default      => text($key),
        };
        $header .= '    <table class="PMSFH_header">
                <tr>
                    <td width="90%">
                        <span class="left" style="font-weight:800;font-size:0.9em;">' . $sectionTitle . '</span>
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
                                    <span class="left" style="font-weight:800;font-size:0.9em;">' . xlt(ucwords((string) $item['row_subtype'])) . ' Meds</span>
                                </td>
                                <td>
                                    <span class="right btn-sm" href="#PMH_anchor" onclick="alter_issue2(\'0\',\'Eye Meds\',\'0\');" style="text-align:right;font-size:8px;">' . xlt("New") . '</span>
                                </td>
                            </tr>
                        </table>
                        ';
                    $subtype_Meds[$item['row_subtype']]['table'] ??= '';
                    $subtype_Meds[$item['row_subtype']]['table'] .= "<span name='QP_PMH_" . attr($item['rowid']) . "' href='#PMH_anchor' id='QP_PMH_" . attr($item['rowid']) . "'
                            onclick=\"alter_issue2(" . attr_js($item['rowid']) . ",\"Eye Meds\"," . attr_js($index) . ");\">" . text($item['title']) . "</span><br />";
                    $index++;
                    continue;
                }

                if ($key == "Allergy") {
                    $reaction = $item['reaction'] ? " (" . text($item['reaction']) . ")" : "";

                    $red = "style='color:red;'";
                } else {
                    $red = '';
                }

                $table .= "<span $red name='QP_PMH_" . $item['rowid'] . "' href='#PMH_anchor' id='QP_PMH_" . $item['rowid'] . "'
                onclick=\"alter_issue2(" . attr_js($item['rowid']) . "," . attr_js($key) . "," . attr_js($index) . ");\">" . text($item['title'] . ($reaction ?? '')) . "</span><br />";
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
    $count = $count + ($count['POS'] ?? null) + 4;
    if ($count >= $column_max) {
        echo $div . $header1;
    }

    echo $display_PMSFH['Eye Meds'];
    $count = $count + ($count['Surgery'] ?? null) +  4;
    if (($count >= $column_max) && ($row_count < $rows)) {
        echo $div;
        $count = 0;
        $row_count = 2;
    }

    echo $display_PMSFH['PMH'];
    $count = $count + ($count['Surgery'] ?? null) +  4;
    if (($count >= $column_max) && ($row_count < $rows)) {
        echo $div;
        $count = 0;
        $row_count = 2;
    }

    echo $display_PMSFH['Surgery'];

    $count = $count + ($count['Medication'] ?? null) + 4;
    if (($count >= $column_max) && ($row_count < $rows)) {
        echo $div;
        $count = 0;
        $row_count = 2;
    }

    echo $display_PMSFH['Medication'];

    $count = $count + ($count['Allergy'] ?? null) + 4;
    if (($count >= $column_max) && ($row_count < $rows)) {
        echo $div;
        $count = 0;
        $row_count = 2;
    }

    echo $display_PMSFH['Allergy'];

    $count = $count + ($count['FH'] ?? null) + 4;
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
        $mention_FH = 0;
        if (count($PMSFH[0]['FH']) > 0) {
            foreach ($PMSFH[0]['FH'] as $item) {
                if (($counter > $column_max) && ($row_count < $rows)) {
                    echo $close_table . $div . $open_table;
                    $counter = "0";
                    $row_count++;
                }

                if ($item['display'] > '') {
                    $counter++;
                    $rawRowId = $item['rowid'] ?? '';
                    $rowId = attr(is_scalar($rawRowId) ? (string) $rawRowId : '');
                    $shortTitle = is_string($item['short_title'] ?? null) ? $item['short_title'] : '';
                    $displayHtml = text($item['display']);
                    echo <<<HTML
                        <span name="QP_PMH_{$rowId}" href="#PMH_anchor" id="QP_PMH_{$rowId}" onclick="alter_issue2('0','FH','');">{$shortTitle}: {$displayHtml}</span><br />
                        HTML;
                    $mention_FH++;
                }
            }
        }

        if (empty($mention_FH)) { ?>
                <span href="#PMH_anchor" onclick="alter_issue2('0','FH','');" style="text-align:right;"><?php echo xlt("Negative"); ?></span><br />
                <?php
                $counter += 3;
        }

        echo $close_table;
        $count = $count + ($count['SOCH'] ?? null) + 4;

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
                $mention_SOCH = 0;
                foreach ($PMSFH[0]['SOCH'] as $item) {
                    if (($counter > $column_max) && ($row_count < $rows)) {
                        echo $close_table . $div . $open_table;
                        $counter = "0";
                        $row_count++;
                    }

                    if (($item['display'] > '') && ($item['display'] != 'not_applicable')) {
                        $rawRowId = $item['rowid'] ?? '';
                        $rowId = attr(is_scalar($rawRowId) ? (string) $rawRowId : '');
                        $shortTitle = is_string($item['short_title'] ?? null) ? $item['short_title'] : '';
                        $displayHtml = text($item['display']);
                        echo <<<HTML
                            <span name="QP_PMH_{$rowId}" href="#PMH_anchor" id="QP_PMH_{$rowId}" onclick="alter_issue2('0','SOCH','');">{$shortTitle}: {$displayHtml}</span><br />
                            HTML;
                        $counter++;
                        $mention_SOCH++;
                    }
                }

                if (empty($mention_SOCH)) {
                    ?>
                    <span href="#PMH_anchor"
                    onclick="alter_issue2('0','SOCH','');" style="text-align:right;"><?php echo xlt("Not documented"); ?></span><br />
                    <?php
                    $counter += 2;
                }

                echo $close_table;
                $count = $count + ($count['ROS'] ?? null) + 4;

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
            $mention = 0;

            foreach ($PMSFH[0]['ROS'] as $item) {
                if (($item['display'] ?? '') > '') {
                    if (($counter > $column_max) && ($row_count < $rows)) {
                        echo $close_table . $div . $open_table;
                        $counter = "0";
                        $row_count++;
                    }

                    // For the list of ROS short_titles, see the predefined ROS categories.
                    $rawRowId = $item['rowid'] ?? '';
                    $rowId = attr(is_scalar($rawRowId) ? (string) $rawRowId : '');
                    $shortTitle = is_string($item['short_title'] ?? null) ? $item['short_title'] : '';
                    $displayHtml = text($item['display']);
                    echo <<<HTML
                        <span name="QP_PMH_{$rowId}" href="#PMH_anchor" id="QP_PMH_{$rowId}" onclick="alter_issue2('0','ROS','');">{$shortTitle}: {$displayHtml}</span><br />
                        HTML;
                    $mention++;
                    $counter++;
                }
            }

            if (empty($mention)) {
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
 *  @return mixed html
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
            $reaction = $item['reaction'] ? "(" . text($item['reaction']) . ")" : "";

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
    $mention_SOCH = 0;
    foreach ($PMSFH[0]['SOCH'] as $item) {
        if (($item['display']) && ($item['display'] != 'not_applicable')) {
            $rawRowId = $item['rowid'] ?? '';
            $rowId = attr(is_scalar($rawRowId) ? (string) $rawRowId : '');
            $shortTitle = is_string($item['short_title'] ?? null) ? $item['short_title'] : '';
            $displayHtml = text($item['display']);
            echo <<<HTML
                <span name="QP_PMH_{$rowId}" href="#PMH_anchor" id="QP_PMH_{$rowId}" onclick="alter_issue2('0','SOCH','');">{$shortTitle}: {$displayHtml}<br /></span>
                HTML;
            $mention_SOCH++;
        }
    }

    if (empty($mention_SOCH)) {
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
    $mention_FH = 0;
    if (count($PMSFH[0]['FH']) > 0) {
        foreach ($PMSFH[0]['FH'] as $item) {
            if ($item['display'] > '') {
                $rawRowId = $item['rowid'] ?? '';
                $rowId = attr(is_scalar($rawRowId) ? (string) $rawRowId : '');
                $shortTitle = is_string($item['short_title'] ?? null) ? $item['short_title'] : '';
                $displayHtml = text($item['display']);
                echo <<<HTML
                    <span name="QP_PMH_{$rowId}" href="#PMH_anchor" id="QP_PMH_{$rowId}" onclick="alter_issue2('0','FH','');">{$shortTitle}: {$displayHtml}<br /></span>
                    HTML;
                $mention_FH++;
            }
        }
    }

    if (empty($mention_FH)) {
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
    $mention_ROS = 0;
    foreach ($PMSFH[0]['ROS'] as $item) {
        if ($item['display'] ?? '') {
            $rawRowId = $item['rowid'] ?? '';
            $rowId = attr(is_scalar($rawRowId) ? (string) $rawRowId : '');
            $shortTitle = is_string($item['short_title'] ?? null) ? $item['short_title'] : '';
            $displayHtml = text($item['display']);
            echo <<<HTML
                <span name="QP_PMH_{$rowId}" href="#PMH_anchor" id="QP_PMH_{$rowId}" onclick="alter_issue2('0','ROS','');">{$shortTitle}: {$displayHtml}</span><br />
                HTML;
            $mention_ROS++;
        }
    }

    if (empty($mention_ROS)) { ?>
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
function show_PMSFH_report($PMSFH): void
{
    global $pid;
    $ISSUE_TYPES = IssueTypeRegistry::issueTypes();
    $count = [];
    $total_PMSFH = 0;

    //4 panels
    $rows = '4';
    $PMSFH = build_PMSFH($pid);

    // Find out the number of items present now and put 1/4 in each column.
    foreach ($PMSFH[0] as $key => $value) {
        if (!empty($total_PMSFH)) {
            $total_PMSFH += count($PMSFH[0][$key]);
            $total_PMSFH += 2; //add two for the title and a space
        } else {
            $total_PMSFH = count($PMSFH[0][$key]);
            $total_PMSFH = 2; //add two for the title and a space
        }
        $count[$key] = count($PMSFH[0][$key]) + 1;
    }

    //SOCH, FH and ROS are listed in $PMSFH even if negative, only count positives
    foreach ($PMSFH[0]['ROS'] as $value) {
        if (($value['display'] ?? '') == '') {
            if (!empty($total_PMSFH)) {
                $total_PMSFH--;
            } else {
                $total_PMSFH = 0;
            }
            $count['ROS']--;
        }
    }

    foreach ($PMSFH[0]['FH'] as $value) {
        if ($value['display'] == '') {
            if (!empty($total_PMSFH)) {
                $total_PMSFH--;
            } else {
                $total_PMSFH = 0;
            }
            $count['FH']--;
        }
    }

    foreach ($PMSFH[0]['SOCH'] as $value) {
        if (($value['display'] == '') || ($value['display'] == 'not_applicable')) {
            if (!empty($total_PMSFH)) {
                $total_PMSFH--;
            } else {
                $total_PMSFH = 0;
            }
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
    $mention_PSOCH = 0;
    foreach ($PMSFH[0]['SOCH'] as $item) {
        if (($item['display']) && ($item['display'] != 'not_applicable')) {
            $shortTitle = is_string($item['short_title'] ?? null) ? $item['short_title'] : '';
            echo $shortTitle . ": " . text($item['display']) . "<br />";
            $mention_PSOCH++;
            $counter++;
        }
    }

    if (empty($mention_PSOCH)) {
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
    $mention_FH = 0;
    foreach ($PMSFH[0]['FH'] as $item) {
        if ($item['display']) {
            $shortTitle = is_string($item['short_title'] ?? null) ? $item['short_title'] : '';
            echo $shortTitle . ": " . text($item['display']) . "<br />";
            $mention_FH++;
            $counter++;
        }
    }

    if (empty($mention_FH)) {
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
    $mention_ROS = 0;
    foreach ($PMSFH[0]['ROS'] as $item) {
        if ($item['display'] ?? '') {
            $shortTitle = is_string($item['short_title'] ?? null) ? $item['short_title'] : '';
            echo $shortTitle . ": " . text($item['display']) . "<br />";
            $mention_ROS++;
            $counter++;
        }
    }

    if (empty($mention_ROS)) {
        echo xlt("Negative");
    }

    echo "</td></tr></table>";
}

/**
 *  This function returns the Provider-specific Quick Pick selections for a zone (2 input values)
 *
 *  These selections are drawn from an OpenEMR list, Eye_QP_$zone_$provider_id.
 *  This list is created from Eye_QP_$zone_defaults when a new provider opens the form.
 *  Because it is a "list", the end-user can modify it.
 *  A link to the list "the pencil icon" is provided to allow customization - displayed in RTop frame.
 *  If frames are ever removed, this will need to be reworked.
 *
 *  @param string $zone options EXT,ANTSEG,RETINA,NEURO
 *  @param string $provider_id
 *  @return string|false|null the ZONE QP lists for this provider
 */
function display_QP($zone, $provider_id)
{
    global $prov_data;
    if (!$zone || !$provider_id) {
        return;
    }

    $here = [];
    $action = '';
    ob_start();
    $query  = "SELECT * FROM list_options where list_id =?  ORDER BY seq";
    $result = sqlStatement($query, ["Eye_QP_" . $zone . "_$provider_id"]);
    if (sqlNumRows($result) < '1') {
        //this provider's list has not been created yet.
        $query = "REPLACE INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`) VALUES ('lists', ?, ?, '0', '1', '0')";
        sqlStatement($query, ['Eye_QP_' . $zone . '_' . $provider_id,'Eye QP List ' . $zone . ' for ' . $prov_data['lname']]);
        $query = "SELECT * FROM list_options where list_id =? ORDER BY seq";
        $result = sqlStatement($query, ["Eye_QP_" . $zone . "_defaults"]);
        $SQL_INSERT = "INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `mapping`, `notes`, `codes`, `activity`, `subtype`) VALUES (?,?,?,?,?,?,?,?,?)";
    } else {
        $SQL_INSERT = '';
    }

    while ($QP = sqlFetchArray($result)) {
        if (!empty($SQL_INSERT)) {
            sqlStatement($SQL_INSERT, ["Eye_QP_" . $zone . "_" . $provider_id,$QP['option_id'],$QP['title'],$QP['seq'],$QP['mapping'],$QP['notes'],$QP['codes'],$QP['activity'],$QP['subtype']]);
        }

        $here[$QP['title']][$QP['subtype']]['notes']    = $QP['notes'];     //the text to fill into form
        $here[$QP['title']][$QP['subtype']]['codes']    = $QP['codes'];     //the code if attached.
        $here[$QP['title']][$QP['subtype']]['mapping']  = $QP['mapping'];   //the fieldname without laterality eg CONJ
        $here[$QP['title']][$QP['subtype']]['activity'] = $QP['activity'];  //1 to replace, 2 to append
    }

    foreach ($here as $title => $values) { //start QP section items
        $titleShort = (strlen((string) $title) > 19) ? substr((string) $title, 0, 16) . '...' : (string) $title;
        $title_show = text($titleShort);
        if (preg_match('/clear field/', (string) $title)) {
            $title_show = "<em><strong>" . text($titleShort) . "</strong></em>";
        }

        if ($values['OD'] ?? '') {
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
                <a class="underline QP" onclick="fill_QP_field('<?php echo attr($zone); ?>','L','<?php echo attr($values['L']['mapping'] ?? ''); ?>','<?php echo attr($values['L']['notes'] ?? ''); ?>','<?php echo attr($action); ?>');"><?php echo xlt('L{{left side}}'); ?></a> |
                <a class="underline QP" onclick="fill_QP_2fields('<?php echo attr($zone); ?>','B','<?php echo attr($values['B']['mapping']); ?>','<?php echo attr($values['B']['notes']); ?>','<?php echo attr($action); ?>');"><?php echo xlt('B{{both sides}}'); ?></a>
            </span>
            &nbsp;
            <?php
        }

        echo $title_show;
        if (!empty($number_rows)) {
            $number_rows++;
        } else {
            $number_rows = 1;
        }
        ?><br />
        <?php
        if (($number_rows ?? null) == 19) {  ?>
          </div>
          <div class="QP_block_outer borderShadow text_clinical" ><?php
        }

        if (($number_rows ?? null) == 38) {
            break;
        }
    } //end QP section items
    ?>
      <a href="JavaScript:void(0);"
         onclick="openNewForm('<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/super/edit_list.php?list_id=Eye_QP_<?php echo attr($zone) . "_" . attr($provider_id); ?>','QP Editor');"
         title="<?php echo xla('Click here to Edit this Doctor\'s Quick Pick list'); ?>"
         name="provider_todo"
         class="bold black"><i class="closeButton float-right fa fa-pencil-alt fa-fw"></i> </a>
        <?php
        $QP_panel = ob_get_contents();
        ob_end_clean();
        return $QP_panel;
}

/** Return the drawing toolbar and prior-image selection controls for an exam zone. */
function canvas_select($zone, $encounter, $pid): string
{
    /* This will provide a way to scroll back through prior VISIT images, to copy forward to today's visit,
     * just like we do in the text fields.
     * Will need to do a lot of thinking to create this.  Gist is ajax call to server for image retrieval.
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
    // iterate through documents?
    // which are in this zone?
    // are any from the same as the encounter?  If so selected=selected

    global $documents;
    //  Currently $side = "OU" everywhere.  We may need OD and OS.  Then we will use $type_name.
    // $type_name = $side . "_" . $zone . "_VIEW";
    $canvi = [];
    if (!empty($documents['docs_in_name']['Drawings'])) {
        foreach ($documents['docs_in_name']['Drawings'] as $doc) {
            if (!preg_match("/_" . $zone . "_VIEW/", (string) $doc['name'])) {
                continue;
            }
            if (!$doc['encounter_id']) {
                continue;
            }
            $canvi[] = $doc;
        }
    }
    usort($canvi, fn($a, $b): int => $b['encounter_date'] <=> $a['encounter_date']);
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
            $select = '<div class="eye-canvas-history">';
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
    return $select ?? '';
}
/**
 *  This function returns display the draw/sketch diagram for a zone (4 input values)
 *
 *  If there is already a drawing for this zone in this encounter, it is pulled from
 *  from its stored location:
 *  OEGlobalsBag::getInstance()->getWebRoot()."/sites/".$session->get('site_id')."/".$form_folder."/".$pid."/".$encounter."/".$side."_".$zone."_VIEW.png?".rand();
 *
 *  Otherwise a "BASE" image is pulled from the images directory of the form...  Customizable.
 *
 *  @param string $zone options ALL,EXT,ANTSEG,RETINA,NEURO
 *  @param string $visit_date Future functionality to limit result set. UTC DATE Formatted
 *  @param string $pid value = patient id
 *  @param string $side OU by default.  Future functionality will allow OD and OS values- not implemented yet.
 *  @return void : outputs the ZONE specific HTML5 CANVAS widget
 */
function display_draw_section($zone, $encounter, $pid, $side = 'OU', $counter = '', mixed $formId = 0, mixed $neuroDrawingJson = ''): void
{
    global $form_folder;
    $drawingSession = SessionWrapperFactory::getInstance()->getActiveSession();
    $toolPreference = QueryUtils::querySingleRow(
        "SELECT GOVALUE FROM form_eye_mag_prefs WHERE PEZONE='DRAW_TOOL' AND id=? AND LOCATION=?",
        [EyeFormData::integer($drawingSession->get('authUserID')), $zone . '_TOOL']
    );
    $drawingTool = ($toolPreference['GOVALUE'] ?? '') === 'draw' ? 'draw' : 'select';
    $backgroundProviderId = EyeFormData::integer($drawingSession->get('authUserID'));
    $backgroundOptions = eye_mag_drawing_background_options($zone, $backgroundProviderId, $side);
    $backgroundPreference = eye_mag_drawing_background_preference($zone, $backgroundProviderId);
    $backgroundOptionsByKey = array_column($backgroundOptions, null, 'key');
    if (!isset($backgroundOptionsByKey[$backgroundPreference])) {
        $backgroundPreference = $zone === 'SDRETINA' ? 'large-retina' : 'default';
    }
    $overlayRow = EyeFormData::integer($formId) > 0 ? QueryUtils::querySingleRow(
        "SELECT overlay_json, background_key FROM form_eye_mag_drawing_overlays WHERE form_id=? AND pid=? AND zone=?",
        [EyeFormData::integer($formId), EyeFormData::integer($pid), (string) $zone]
    ) : [];
    $drawingOverlayJson = EyeFormData::text($overlayRow['overlay_json'] ?? '');
    if ($drawingOverlayJson === '' && $zone === 'NEURO') {
        $drawingOverlayJson = EyeFormData::text($neuroDrawingJson);
    }
    $drawingOverlayJson = $drawingOverlayJson !== '' ? $drawingOverlayJson : '[]';
    $drawingBackground = EyeFormData::text($overlayRow['background_key'] ?? '');
    if ($drawingBackground === '') {
        $drawingBackground = $zone === 'SDRETINA' ? 'large-retina' : 'default';
    }
    $filepath = OEGlobalsBag::getInstance()->get('oer_config')['documents']['repository'] . $pid . "/";
    $base_name = $pid . "_" . $encounter . "_" . $side . "_" . $zone . "_VIEW";

    $file_history =  $filepath . $base_name;
    //$file_store = $file_history . ".jpg";

    ?>
    <div id="Draw_<?php echo attr($zone); ?>" name="Draw_<?php echo attr($zone); ?>" class="Draw_class canvas">
        <?php
        if ($zone != "SDRETINA") {
            ?>
        <span class="far fa-file-alt closeButton" id="BUTTON_TEXT_<?php echo attr($zone); ?>" name="BUTTON_TEXT_<?php echo attr($zone); ?>"></span>
        <i class="closeButton_2 fas fa-database" id="BUTTON_QP_<?php echo attr($zone); ?>_2" name="BUTTON_QP_<?php echo attr($zone); ?>"></i>
        <i class="closeButton_3 fas fa-user-md" name="Shorthand_kb" title="<?php echo xla("Open the Shorthand Window and display Shorthand Codes"); ?>"></i>

            <?php
        } ?>
        <?php
            $output = canvas_select($zone, $encounter, $pid);
        ?>
                <?php

                $sql = "SELECT * from documents where name like ? AND foreign_id = ? AND deleted = 0 ORDER by id DESC";
                $doc = QueryUtils::querySingleRow($sql, ["%" . $base_name . "%", EyeFormData::integer($pid)]);
                $has_saved_drawing = EyeFormData::integer($doc['id'] ?? 0) > 0;
                if (!$has_saved_drawing && $drawingOverlayJson === '[]') {
                    $drawingBackground = $backgroundPreference;
                }
                $activeBackground = $backgroundOptionsByKey[$drawingBackground] ?? null;
                if (!$activeBackground && str_starts_with($drawingBackground, 'saved-')) {
                    // Deleted library entries still supply backgrounds already used in exams.
                    $archivedBackgrounds = array_column(eye_mag_drawing_background_options($zone, $backgroundProviderId, $side, true), null, 'key');
                    $activeBackground = $archivedBackgrounds[$drawingBackground] ?? null;
                }
                $activeBackground ??= $backgroundOptionsByKey[$zone === 'SDRETINA' ? 'large-retina' : 'default'];
                $base_filetoshow = $activeBackground->url;
                $structuredBackground = !in_array($drawingBackground, ['blank', 'practice'], true)
                    && !str_starts_with($drawingBackground, 'custom-')
                    && !str_starts_with($drawingBackground, 'saved-')
                    && $activeBackground->structured;
                if ($has_saved_drawing) {
                    $filetoshow = OEGlobalsBag::getInstance()->getWebRoot()
                        . "/controller.php?document&retrieve&patient_id=" . attr_url($pid)
                        . "&document_id=" . attr_url(EyeFormData::text($doc['id'] ?? ''))
                        . "&as_file=false&original_file=true&disable_exit=false&show_original=true";
                } else {
                    //base image.
                    $filetoshow = $base_filetoshow;
                }
                ?>

                <input type="hidden" id="url_<?php echo attr($zone); ?>" name="url_<?php echo attr($zone); ?>" value="<?php echo attr($filetoshow); ?>" />
                <input type="hidden" id="base_url_<?php echo attr($zone); ?>" name="base_url_<?php echo attr($zone); ?>" value="<?php echo attr($base_filetoshow); ?>" />
                <input type="hidden" id="current_background_<?php echo attr($zone); ?>" value="<?php echo attr($drawingBackground); ?>" />
                <input type="hidden" id="selWidth_<?php echo attr($zone); ?>" value="1">
                <input type="hidden" id="selColor_<?php echo attr($zone); ?>" value="#000" />

                <input type="hidden" id="<?php echo attr($zone); ?>_KONVA_JSON" value="<?php echo attr($drawingOverlayJson); ?>" />
                <div class="eye-drawing-command-row">
                <?php echo $output; ?>
                <div id="<?php echo attr($zone); ?>_KONVA_TOOLS" class="eye-konva-tools" data-zone="<?php echo attr($zone); ?>"
                     data-tool="<?php echo attr($drawingTool); ?>"
                     data-preference-url="<?php echo attr(OEGlobalsBag::getInstance()->getWebRoot() . '/interface/forms/eye_mag/save.php'); ?>"
                     data-csrf="<?php echo attr(\OpenEMR\Common\Csrf\CsrfUtils::collectCsrfToken(session: $drawingSession)); ?>"
                     role="toolbar" aria-label="<?php echo xla('Editable image annotation tools'); ?>">
                    <button type="button" data-konva-tool="draw" title="<?php echo xla('Draw freehand'); ?>"><i class="fas fa-pencil-alt"></i></button>
                    <button type="button" data-konva-tool="select" title="<?php echo xla('Select and move annotations'); ?>"><i class="fas fa-mouse-pointer"></i></button>
                    <button type="button" data-konva-add="text" title="<?php echo xla('Add editable text'); ?>">T</button>
                    <button type="button" data-konva-add="rect" title="<?php echo xla('Add rectangle'); ?>"><i class="far fa-square"></i></button>
                    <button type="button" data-konva-add="ellipse" title="<?php echo xla('Add ellipse'); ?>"><i class="far fa-circle"></i></button>
                    <button type="button" data-konva-add="arrow" title="<?php echo xla('Add arrow'); ?>"><i class="fas fa-long-arrow-alt-right"></i></button>
                    <button type="button" data-konva-action="library" data-zone="<?php echo attr($zone); ?>" title="<?php echo xla('Saved overlays and backgrounds'); ?>" aria-label="<?php echo xla('Saved overlays and backgrounds'); ?>"><i class="far fa-images"></i></button>
                    <button type="button" data-konva-action="transparency" title="<?php echo xla('Toggle selected object background transparency'); ?>"><i class="fas fa-adjust"></i></button>
                    <button type="button" data-konva-action="delete" title="<?php echo xla('Delete selected annotation'); ?>"><i class="fas fa-trash-alt"></i></button>
                </div>
                </div>
        <div id="<?php echo attr($zone); ?>_canvas">
                <div class="eye-drawing-library-config" hidden data-zone="<?php echo attr($zone); ?>"
                     data-url="<?php echo attr(OEGlobalsBag::getInstance()->getWebRoot() . '/interface/forms/eye_mag/drawing_background.php'); ?>"
                     data-csrf="<?php echo attr(\OpenEMR\Common\Csrf\CsrfUtils::collectCsrfToken(session: $drawingSession)); ?>"
                     data-default="<?php echo attr($backgroundPreference); ?>"
                     data-options="<?php echo attr(json_encode($backgroundOptions, JSON_THROW_ON_ERROR)); ?>"></div>
                <div class="tools">
                <div class="eye-raster-tools" aria-label="<?php echo xla('Freehand drawing colors and brush sizes'); ?>">
                <div id="sketch_tooled_<?php echo attr($zone); ?>_8" class="eye-current-pencil">
                    <span id="sketch_tool_<?php echo attr($zone);?>_color"
                          class="color_indicator"
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
                <span class="eye-pencil-colors">
                <img id="sketch_tools_<?php echo attr($zone); ?>_1" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#1AA2E1");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_blue.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_2" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#ff0");'  src="../../forms/<?php echo $form_folder; ?>/images/pencil_yellow.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_3" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#ffad00");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_orange.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_4" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#AC8359");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_brown.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_5" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#E10A17");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_red.png" style="height:30px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_6" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#000");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_black.png" style="height:50px;width:15px;">
                <img id="sketch_tools_<?php echo attr($zone); ?>_7" onclick='$("#selColor_<?php echo attr($zone); ?>").val("#fff");' src="../../forms/<?php echo $form_folder; ?>/images/pencil_white.png" style="height:30px;width:15px;">
                </span>
                <!-- now to pencil size -->
                <span class="eye-brush-sizes">
                <img id="sketch_sizes_<?php echo attr($zone); ?>_1" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("1");' src="../../forms/<?php echo $form_folder; ?>/images/brush_1.png" style="height:20px;width:20px; border-bottom: 2pt solid black;">
                <img id="sketch_sizes_<?php echo attr($zone); ?>_3" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("3");' src="../../forms/<?php echo $form_folder; ?>/images/brush_3.png" style="height:20px;width:20px;">
                <img id="sketch_sizes_<?php echo attr($zone); ?>_5" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("5");' src="../../forms/<?php echo $form_folder; ?>/images/brush_5.png" style="height:20px;width:20px;">
                <img id="sketch_sizes_<?php echo attr($zone); ?>_10" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("10");' src="../../forms/<?php echo $form_folder; ?>/images/brush_10.png" style="height:20px;width:20px;">
                <img id="sketch_sizes_<?php echo attr($zone); ?>_15" onclick='$("#selWidth_<?php echo attr($zone); ?>").val("15");' src="../../forms/<?php echo $form_folder; ?>/images/brush_15.png" style="height:20px;width:20px;">
                </span>
                </div>
            </div>

            <div class="borderShadow">
                <div class="eye-drawing-stage" data-structured-background="<?php echo $structuredBackground ? '1' : '0'; ?>" data-has-saved-drawing="<?php echo $has_saved_drawing ? '1' : '0'; ?>">
                <canvas id="myCanvas_<?php echo attr($zone); ?>"
                        name="myCanvas_<?php echo attr($zone); ?>"
                    <?php
                    if ($zone == "SDRETINA") {
                        $height_canvas = "500";
                        $width_canvas = "1000";
                    } else {
                        $height_canvas = "250";
                        $width_canvas = "450";
                    }
                    ?>
                        width="<?php echo attr($width_canvas); ?>"
                        height="<?php echo attr($height_canvas); ?>"></canvas>
                    <div id="<?php echo attr($zone); ?>_konva_stage" class="eye-konva-stage" data-zone="<?php echo attr($zone); ?>" aria-label="<?php echo xla('Editable image annotations'); ?>"></div>
                <?php if ($zone === 'NEURO') { ?>
                    <svg id="NEURO_structured_overlay" class="neuro-structured-overlay"
                         viewBox="0 0 450 250" preserveAspectRatio="none" aria-hidden="true">
                        <g id="NEURO_overlay_motility_diagrams"></g>
                        <!-- Repaint the fixed Stereo template on every composite. This
                             removes structured marks that were flattened by an earlier
                             save before the current values are drawn. -->
                        <g id="NEURO_overlay_stereo_template" fill="#111"
                           style="font-family:Arial,sans-serif;font-size:14px;font-weight:400;">
                            <rect x="326" y="34" width="124" height="216" fill="#fff"></rect>
                            <rect x="24" y="215" width="302" height="35" fill="#fff"></rect>
                            <text x="350" y="59">Stereo:</text>
                            <text x="354" y="92" text-anchor="middle">+</text>
                            <text x="378" y="92" text-anchor="middle">−</text>
                            <text x="392" y="92">Fly</text>
                            <line x1="350" y1="137" x2="378" y2="137" stroke="#777" stroke-width="1"></line>
                            <text x="383" y="141">/ 3 An</text>
                            <line x1="350" y1="188" x2="378" y2="188" stroke="#777" stroke-width="1"></line>
                            <text x="383" y="192">/ 9 C</text>
                            <text x="36" y="240">Fixation pref:</text>
                            <text x="150" y="240" text-anchor="middle">OD</text>
                            <text x="191" y="240" text-anchor="middle">OS</text>
                            <text x="233" y="240" text-anchor="middle">OU</text>
                            <text x="278" y="240" text-anchor="middle">ALT</text>
                            <text x="374" y="240" text-anchor="middle">Unable</text>
                        </g>
                        <text id="NEURO_overlay_animals" class="neuro-overlay-value" x="364" y="137" text-anchor="middle"></text>
                        <text id="NEURO_overlay_circles" class="neuro-overlay-value" x="364" y="188" text-anchor="middle"></text>
                        <ellipse id="NEURO_overlay_fly_yes" class="neuro-overlay-mark" cx="354" cy="87" rx="10" ry="10"></ellipse>
                        <ellipse id="NEURO_overlay_fly_no" class="neuro-overlay-mark" cx="378" cy="87" rx="10" ry="10"></ellipse>
                        <ellipse id="NEURO_overlay_fixation_od" class="neuro-overlay-mark" cx="150" cy="233" rx="17" ry="11"></ellipse>
                        <ellipse id="NEURO_overlay_fixation_os" class="neuro-overlay-mark" cx="191" cy="233" rx="17" ry="11"></ellipse>
                        <ellipse id="NEURO_overlay_fixation_ou" class="neuro-overlay-mark" cx="233" cy="233" rx="18" ry="11"></ellipse>
                        <ellipse id="NEURO_overlay_fixation_alt" class="neuro-overlay-mark" cx="278" cy="233" rx="21" ry="11"></ellipse>
                        <ellipse id="NEURO_overlay_unable" class="neuro-overlay-mark" cx="374" cy="233" rx="28" ry="11"></ellipse>
                        <ellipse id="NEURO_overlay_motility_od" class="neuro-overlay-mark" cx="115" cy="68" rx="24" ry="12"></ellipse>
                        <ellipse id="NEURO_overlay_motility_os" class="neuro-overlay-mark" cx="244" cy="68" rx="24" ry="12"></ellipse>
                    </svg>
                <?php } ?>
                </div>
            </div>
            <div style="margin-top: 7px;">
                <button onclick="javascript:cUndo('<?php echo attr($zone); ?>');return false;" id="Undo_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("Undo"); ?></button>
                <button onclick="javascript:cRedo('<?php echo attr($zone); ?>');return false;" id="Redo_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("Redo"); ?></button>
                <button onclick="javascript:drawImage('<?php echo attr($zone); ?>');return false;" id="Revert_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("Revert"); ?></button>
                <button onclick="javascript:eyeDrawingStartNew('<?php echo attr($zone); ?>');return false;" id="Clear_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("New"); ?></button>
                <button id="Blank_Canvas_<?php echo attr($zone); ?>"><?php echo xlt("Blank"); ?></button>
                <button type="button" id="SaveAs_Canvas_<?php echo attr($zone); ?>" data-drawing-save-as="<?php echo attr($zone); ?>"><?php echo xlt('Save As'); ?></button>
            </div>
        </div>
        <div id="<?php echo attr($zone); ?>_olddrawing" name="<?php echo attr($zone); ?>_olddrawing"></div>
    </div>

    <?php
}

/**
 *  Echoes the JSON payload the browser applies to today's chart when copying a
 *  section (or the whole record) forward from a prior encounter.
 *  It will not replace the drawings with older encounter drawings... Not yet anyway.
 *
 * @param Zone|CopyMode $mode what to copy: a single exam zone, or a whole-form operation
 * @param string $copy_from is the form_eye_*.id where the data to carry forward is located
 * @param string $pid value = patient id
 * @return void : echoes the values of the requested zone as JSON
 */
function copy_forward(Zone|CopyMode $mode, string $copy_from, string $pid): void
{
    $record = QueryUtils::querySingleRow(CopyForward::RECORD_QUERY, [$pid, $copy_from]);
    if (!is_array($record)) {
        // No such form for this patient: nothing to carry forward.
        echo json_encode([]);
        return;
    }

    $stereoFields = ['STEREO_FLY', 'STEREO_ANIMALS', 'STEREO_CIRCLES', 'STEREO_FIXATION_PREF'];
    if ($mode instanceof Zone) {
        $fields = $mode->fields();
        if ($mode === Zone::NEURO) {
            $fields = array_merge($fields, $stereoFields);
        }
        echo json_encode(CopyForward::pick($record, $fields));
        return;
    }

    $result = match ($mode) {
        CopyMode::IMPPLAN => ['IMPPLAN' => build_IMPPLAN_items($pid, $copy_from)],
        CopyMode::ALL => CopyForward::pick($record, array_merge(CopyForward::allFields(), $stereoFields)),
        CopyMode::READONLY => $record + ['IMPPLAN' => build_IMPPLAN_items($pid, $copy_from)],
    };

    echo json_encode($result);
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
    $newdata = [];
    $fres = sqlStatement($query, [$form_id,$pid]);
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

    return $IMPPLAN_items ?? [];
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
    $fres = sqlStatement($query, [$encounter,$pid]);
    $i = 0;

    while ($frow = sqlFetchArray($fres)) {
        $CODING_items[$i]['encounter'] = $frow['encounter'];
        $CODING_items[$i]['pid'] = $frow['pid'];
        $CODING_items[$i]['id'] = $frow['id'];
        $CODING_items[$i]['codetype'] = $frow['code_type'];
        $CODING_items[$i]['codedesc'] = $frow['code_desc'] ?? '';
        $CODING_items[$i]['codetext'] = $frow['code_text'];
        $CODING_items[$i]['justify'] = $frow['justify'];
        $i++;
    }

    return $CODING_items ?? '';
}

/**
 *  This function builds an array of documents for this patient ($pid).
 *  We first list all the categories this practice has created by name and by category_id
 *  for this patient ($pid)
 *  Each document info from documents table is added to these as arrays
 *
 *  @param string $pid patient_id
 *  @return array
 */
function document_engine($pid): array
{
    $categories = [];
    $my_name = [];
    $children_names = [];
    $zones = [];
    $sql1 =  sqlStatement("Select * from categories");
    while ($row1 = sqlFetchArray($sql1)) {
        $categories[] = $row1;
        $row1['name'] = preg_replace('/ - Eye/', '', (string) $row1['name']);

        $my_name[$row1['id']] = $row1['name'];
        $children_names[$row1['parent'] ?? ''][] = $row1['name'] ?? '';
        if (!empty($parent_name)) {
            $parent_name[$row1['name']] = $my_name[$row1['parent']];
        }
        if (($row1['value'] ?? '') > '') {
            //if there is a value, tells us what segment of exam ($zone) this belongs in...
            $zones[$row1['value']][] = $row1;
            $zone[$row1['name']] = true;
        } else {
            if ($row1['name'] != "Categories") {
                $zones['OTHER'][] = $row1;
            }
        }
    }

    $query = "Select *, categories.name as cat_name
                from
                categories, documents, categories_to_documents
                where documents.foreign_id=? and documents.id=categories_to_documents.document_id and
                categories_to_documents.category_id=categories.id and documents.deleted = 0 ORDER BY categories.name";
    $sql2 =  sqlStatement($query, [$pid]);
    while ($row2 = sqlFetchArray($sql2)) {
        //the document may not be created on the same day as the encounter, use encounter date first
        //get encounter date from encounter id
        $row2['cat_name'] = preg_replace('/ - Eye/', '', (string) $row2['cat_name']);
        $row2['display_url'] = preg_replace("|file:///.*/sites/|", OEGlobalsBag::getInstance()->getWebRoot() . "/sites/", (string) $row2['url']);
        if ($row2['encounter_id']) {
            $visit = getEncounterDateByEncounter($row2['encounter_id']);
            $row2['encounter_date'] = oeFormatSDFT(strtotime($visit['date'] ?? ''));
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
    $documents['parent_name']       = $parent_name ?? null;
    $documents['zones']             = $zones;
    $documents['docs_in_zone']      = $docs_in_zone ?? '';
    $documents['docs_in_cat_id']    = $docs_in_cat_id ?? '';
    $documents['docs_in_name']      = $docs_in_name ?? '';
    $documents['docs_by_date']      = $docs_by_date ?? '';

    return [$documents];
}

/**
 *  This function returns ICONS with links for a specific clinical subsection of the Document Library.
 *
 *  @param string $pid value = patient id
 *  @param string $encounter is the encounter_id
 *  @param string $category_value options EXT,ANTSEG,RETINA,NEURO,OTHER
 *                These values are taken from the "value" field in the Documents' table "categories".
 *                They allow us to regroup the categories how we like them.
 *  @return array
 */
function display($pid, $encounter, $category_value): array
{
    global $form_folder;
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
        *   Currently the base install has EXT,ANTSEG,RETINA,NEURO
        *   New names new categories.  OCT would not have been a category 5 years ago.
        *   Who knows what is next?  Gene-lab construction?
        *   So the name is user assigned as is the location.
        *   Thus we need to build out the Documents section by adding another layer "zones"
        *   to the associative array.
        */
    if (!$documents) {
        [$documents] = document_engine($pid);
    }

    for ($j = 0; $j < count($documents['zones'][$category_value]); $j++) {
        $count_here = empty($documents['docs_in_cat_id'][$documents['zones'][$category_value][$j]['id']]) ? 0 : count($documents['docs_in_cat_id'][$documents['zones'][$category_value][$j]['id']]);

        $id_to_show = $documents['docs_in_cat_id'][$documents['zones'][$category_value][$j]['id']][$count_here - 1]['document_id'] ?? '';
        $documents['zones'][$category_value][$j]['name'] = preg_replace("( - Eye)", "", (string) $documents['zones'][$category_value][$j]['name']);
        $categoryName = is_string($documents['zones'][$category_value][$j]['name'] ?? null)
            ? $documents['zones'][$category_value][$j]['name']
            : '';
        $categoryDisplayName = $categoryName;
        if (in_array($category_value, ['POSTSEG', 'RETINA'], true) && strcasecmp(trim($categoryDisplayName), 'Fundus') === 0) {
            $categoryDisplayName = xl('Photo');
        }
        $categoryId = $documents['zones'][$category_value][$j]['id'] ?? '';
        $uploadUrl = OEGlobalsBag::getInstance()->getWebRoot()
            . '/controller.php?document&upload&patient_id=' . urlencode((string) $pid)
            . '&parent_id=' . urlencode((string) $categoryId)
            . '&';
        $viewUrl = OEGlobalsBag::getInstance()->getWebRoot()
            . '/controller.php?document&view&patient_id=' . urlencode((string) $pid)
            . '&doc_id=' . urlencode((string) $id_to_show);
        $episode .= "<tr>
        <td class='right'><span class='font-weight-bold'>" . text($categoryDisplayName) . "</span>:&nbsp;</td>
        <td>
            <a onclick=\"openNewForm(" . attr_js($uploadUrl) . ", " . attr_js(xl('Upload') . ' ' . $categoryDisplayName) . ");\" href='#'>
            <img src='../../forms/" . $form_folder . "/images/upload_file.png' class='little_image'>
            </a>
        </td>
        <td>
            <a onclick=\"return showpnotes(" . attr_js((string) $id_to_show) . ");\">
                <img  src='../../forms/" . $form_folder . "/images/upload_multi.png' class='little_image'>
            </a>
        </td>
        <td>";
        //open via OpenEMR Documents with treemenu
        if ($count_here > '0') {
            $episode .= '<a onclick="openNewForm(' . attr_js($viewUrl) . ', ' . attr_js(xl('Documents') . ': ' . $categoryDisplayName) . ');"><img src="../../forms/' . $form_folder . '/images/jpg.png" class="little_image" /></a>';
        }

        $episode .= '</td></tr>';
        if (!empty($i)) {
            $i++;
        } else {
            $i = 1;
        }
    }

    return [$documents,$episode];
}

/**
 *  This is an application style menu (bootstrap) to start shifting clinical functions into a single page.
 *
 *  @param string $pid is the patient id
 *  @param string $encounter is the encounter_id
 *  @param string $title is the form title
 *
 *  @return void
 */
function menu_overhaul_top($pid, $encounter, $title = "Eye Exam"): void
{
    global $form_folder;
    global $prov_data;
    global $encounter;
    global $form_id;
    global $display;
    global $provider_id;

    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $authUserId = EyeFormData::integer($session->get('authUserID'));
    $providerId = EyeFormData::integer($provider_id);
    $isProviderOfRecord = $providerId > 0 && $authUserId === $providerId;
    $isAdmin = AclMain::aclCheckCore('admin', 'super');
    $assetManagerUrl = OEGlobalsBag::getInstance()->getWebRoot() . '/interface/forms/eye_mag/asset_manager.php';

    $providerNAME = EyeFormData::text($prov_data['fname'] ?? '') . " " . EyeFormData::text($prov_data['lname'] ?? '');
    if ($prov_data['suffix']) {
        $providerNAME .= ", " . EyeFormData::text($prov_data['suffix']);
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
                <img src="<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/sites/default/images/login_logo.gif" class="little_image">
                <span class="brand"><?php echo xlt('Eye Exam'); ?></span>
            </div>
            <button class="navbar-toggler eye-mag-menu-toggle" type="button"
                    data-toggle="collapse" data-target="#oer-navbar-collapse-1"
                    aria-controls="oer-navbar-collapse-1" aria-expanded="false"
                    aria-label="<?php echo xla('Toggle Eye Form menu'); ?>">
                <span class="fas fa-bars" aria-hidden="true"></span>
            </button>
            <div class="collapse navbar-collapse oer-navbar-collapse mr-auto mt-2 mt-lg-0" id="oer-navbar-collapse-1">
                <ul class="navbar-nav mr-auto">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_file" role="button" aria-expanded="true"><?php echo xlt("File"); ?> </a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="nav-lik" id="menu_PRINT_narrative" name="menu_PRINT_report"><a class="nav-link black" id="BUTTON_PRINT_report" target="_new" href="<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/patient_file/report/custom_report.php?printable=1&pdf=0&<?php echo attr_url($form_folder) . "_" . attr_url($form_id) . "=" . attr_url($encounter); ?>"><?php echo xlt("Print Report"); ?><span class="menu_icon"><i class="fa fa-print"></i></span></a></li>
                            <li class="nav-ite" id="menu_PRINT_narrative_2" name="menu_PRINT_report_2"><a class="nav-link black" id="BUTTON_PRINT_report_2" target="_new" href="#"
                                onclick="top.restoreSession(); create_task('<?php echo attr($provider_id); ?>','Report','menu'); return false;">
                                <?php echo xlt("Save Report as PDF"); ?><span class="menu_icon"><i class="fa fa-file-pdf"></i></span></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_edit" role="button" aria-expanded="true"><?php echo xlt("Edit"); ?> </a>
                        <ul class="dropdown-menu" role="menu">
                            <li id="menu_Defaults" name="menu_Defaults" class="">
                                <a class="nav-link black"
                                   id="BUTTON_Defaults_menu"
                                   onclick="openNewForm('<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/super/edit_list.php?list_id=Eye_defaults_<?php echo attr($provider_id); ?>', '<?php echo xla('Default Exam Values'); ?>');"
                                   name="provider_todo"
                                   href="JavaScript:void(0);">
                                <?php echo xlt("Default Values"); ?> &nbsp;
                                <span class="menu_icon"><i class="fa fa-pencil-alt fa-fw"></i> </span></a></li>
                            <?php if ($isProviderOfRecord) { ?>
                            <li id="menu_My_Signature" name="menu_My_Signature">
                                <a class="nav-link black" href="JavaScript:void(0);"
                                   onclick="if (typeof top.restoreSession === 'function') { top.restoreSession(); } else if (typeof window.restoreSession === 'function') { window.restoreSession(); } dlgopen(<?php echo attr(js_escape($assetManagerUrl . '?asset=signature&provider_id=' . $providerId)); ?>, 'eye_signature', 780, 720, '', <?php echo attr(xlj('My Signature')); ?>); return false;">
                                    <?php echo xlt('My Signature'); ?>
                                    <span class="menu_icon"><i class="fa fa-signature fa-fw"></i></span>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($isAdmin) { ?>
                            <li id="menu_Practice_Logo" name="menu_Practice_Logo">
                                <a class="nav-link black" href="JavaScript:void(0);"
                                   onclick="if (typeof top.restoreSession === 'function') { top.restoreSession(); } else if (typeof window.restoreSession === 'function') { window.restoreSession(); } dlgopen(<?php echo attr(js_escape($assetManagerUrl . '?asset=logo')); ?>, 'eye_practice_logo', 720, 470, '', <?php echo attr(xlj('Practice Logo')); ?>); return false;">
                                    <?php echo xlt('Practice Logo'); ?>
                                    <span class="menu_icon"><i class="fa fa-image fa-fw"></i></span>
                                </a>
                            </li>
                            <?php } ?>
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
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_view" role="button" aria-expanded="true"><?php echo xlt("View"); ?> </a>
                        <ul class="dropdown-menu" role="menu">
                            <li id="menu_HPI" name="menu_HPI"><a class="nav-link black" href="#"><?php echo xlt("HPI"); ?></a></li>
                            <li id="menu_PMH" name="menu_PMH"><a class="nav-link black" href="#"><?php echo xlt("PMH{{Past Medical History}}"); ?></a></li>
                            <li id="menu_EXT" name="menu_EXT" ><a class="nav-link black" href="#"><?php echo xlt("External"); ?></a></li>
                            <li id="menu_ANTSEG" name="menu_ANTSEG" ><a class="nav-link black" href="#"><?php echo xlt("Anterior Segment"); ?></a></li>
                            <li id="menu_RETINA" name="menu_RETINA" ><a class="nav-link black" href="#"><?php echo xlt("Posterior Segment"); ?></a></li>
                            <li id="menu_NEURO" name="menu_NEURO" ><a class="nav-link black" href="#"><?php echo xlt("Neuro"); ?></a></li>
                            <li id="menu_IMPPLAN" name="menu_IMPPLAN" ><a class="nav-link black" href="#"><?php echo xlt("Imp Plan"); ?></a></li>
                            <li class="divider"></li>
                            <li id="menu_Right_Panel" name="menu_Right_Panel"><a class="nav-link black" href="#"><?php echo xlt("PMSFH Panel"); ?><span class="menu_icon"><i class="fa fa-list" ></i></span></a></li>
                            <li id="menu_left_tabs" name="menu_left_tabs"><a class="nav-link black" href="#"><?php echo xlt("Chart View"); ?><span class="menu_icon"><i class="fa fa-user-md" ></i></span></a></li>
                            <?php
                            // Fullscreen belongs with the other display modes, under Chart
                            // View.  It only makes sense framed (fullscreen itself hides it).
                            if ($display !== "fullscreen") { ?>
                            <li id="menu_fullscreen" name="menu_fullscreen" <?php echo ($fullscreen ?? ''); ?>>
                                <a class="nav-link black"
                                   title="<?php echo xla('Open this form without the surrounding OpenEMR frame'); ?>"
                                   onclick="openNewForm(<?php echo attr_js(OEGlobalsBag::getInstance()->getWebRoot()); ?> + '/interface/patient_file/encounter/load_form.php?formname=fee_sheet');top.restoreSession();(function(){const p=new URLSearchParams({display:'fullscreen',encounter:<?php echo attr_js(urlencode((string) $encounter)); ?>});dopopup(<?php echo attr_js($_SERVER['REQUEST_URI']); ?> + '&' + p.toString());})();"
                                   href="JavaScript:void(0);"
                                   ><?php echo xlt('Fullscreen'); ?><span class="menu_icon"><i class="fa fa-window-maximize"></i></span></a>
                            </li>
                            <?php } ?>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown"
                           id="menu_dropdown_library" role="button"
                           aria-expanded="true"><?php echo xlt("Library"); ?> </a>
                        <ul class="dropdown-menu" role="menu">
                            <li id="menu_IOP_graph" name="menu_IOP_graph" ><a class="nav-link black" href="#"><?php echo xlt("IOP Graph"); ?><span class="menu_icon"><i class="fa fa-chart-line"></i></span></a></li>
                            <li id="menu_My_Phrases" name="menu_My_Phrases">
                                <a class="nav-link black"
                                   href="JavaScript:void(0);"
                                   title="<?php echo xla('Open My Phrases Builder to manage, create or deploy dot phrases'); ?>"
                                   onclick="if (typeof window.eyeMagBuilderOpen === 'function') eyeMagBuilderOpen();"
                                   ><?php echo xlt("My Phrases"); ?><span class="menu_icon"><i class="fa fa-dot-circle" ></i></span></a></li>
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
                                <span class="menu_icon"><i title="<?php echo xla('Tooltips on/off'); ?>" id="qtip_icon" class="fa fa-info-circle fa-1"></i></span></a>
                            </li>
                            <li>
                                <a class="nav-link black" tabindex="-1" target="_shorthand" href="<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/forms/eye_mag/help.php">
                                    <i class="fa fa-help"></i>  <?php echo xlt("Shorthand Help"); ?>
                                    <span class="menu_icon">
                                        <i title="<?php echo xla('Click for Shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></span></a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right my-2 my-lg-0">
                    <li><span style="margin-right:15px;color:black;"  onclick="editScripts('<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/controller.php?prescription&list&id=<?php echo attr_url($pid); ?>');">eRx</button>
                        </span></li>
                    <li ><span id="active_flag" name="active_flag" style="margin-right:15px;color:red;"> <?php echo xlt('Active Chart'); ?> </span>
                        <span name="active_icon" id="active_icon" style="color:black;"><i class='fa fa-toggle-on'></i></span></li>

                </ul>

            </div><!-- /.navbar-collapse -->
        </div>
    </nav>
    <script>
        document.addEventListener('click', function (event) {
            var clickedLink = event.target.closest
                ? event.target.closest('#oer-navbar-collapse-1 .dropdown-menu a')
                : null;
            if (window.innerWidth >= 992 || !clickedLink) {
                return;
            }
            var menu = document.getElementById('oer-navbar-collapse-1');
            var toggle = document.querySelector('.eye-mag-menu-toggle');
            if (menu && toggle && menu.classList.contains('show')) {
                toggle.click();
            }
        });
    </script>
    <?php
        return;
}


/** Render the encounter sidebar with patient, provider, and document actions. */
function menu_overhaul_left(string $pid, string $encounter): void
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
        [$documents] = document_engine($pid);
    }
    ?>
    <div class="borderShadow row" id="title_bar">
        <div id="left_menu" name="left_menu" class="col-sm-4" style="padding-left: 18px;">
                <table style="text-align:left;">
                    <tr><td class="right" >
                            <?php
                            $age = getPatientAgeDisplay($pat_data['DOB'], ($encounter_date ?? ''));
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
                    if ($priors[0]['TODO'] ?? '') {
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

            $patientPhoto = eye_mag_patient_photo($pid);
            if (!empty($patientPhoto['id'])) {
                ?>
                <object><embed
                            src="<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/controller.php?document&retrieve&patient_id=<?php echo attr($pid); ?>&document_id=<?php echo attr($patientPhoto['id']); ?>&as_file=false&original_file=true&disable_exit=false&show_original=true&context=patient_picture"

                            Xsrc="<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/controller.php?document&retrieve&patient_id=<?php echo attr($pid); ?>&document_id=<?php echo attr($patientPhoto['id']); ?>&as_file=false" frameborder="0"
                     type="<?php echo attr($patientPhoto['mimetype']); ?>" allowscriptaccess="always" allowfullscreen="false" height="100"></embed></object>
                <?php
            } else {
                ?>
            <object><embed src="<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/forms/<?php echo $form_folder; ?>/images/anon.gif" frameborder="0"
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
                        <td class="left"> <span id="pcp_name"><?php echo text($pcp_data['fname'] ?? '') . " " . text($pcp_data['lname'] ?? ''); ?><?php if ($pcp_data['suffix'] ?? '') {
                                    echo ", " . text($pcp_data['suffix']);} ?></span></td>
                        </td>
                    </tr>

                    <tr><td class="right" nowrap><span style="font-weight:bold;"><?php echo xlt("Referred By"); ?>:</span>&nbsp;</td>
                        <td class="left"> <span id="ref_name"><?php echo text($ref_data['fname'] ?? '') . " " . text($ref_data['lname'] ?? ''); ?><?php if ($ref_data['suffix'] ?? '') {
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
                                    "AND p.type = 2 where d.id=? " .
                                    "ORDER BY state, city, name, area_code, prefix, number";
                                $pharm = sqlQuery($sql, [$pat_data['pharmacy_id']]);
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
 *  @return void
 */

function menu_overhaul_bottom($pid, $encounter): void
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
    $sqlBindArray = [$state];
    if ($limit != "unlimited") {
        $sql .= " LIMIT ? OFFSET ?";
        array_push($sqlBindArray, is_numeric($limit) ? (int) $limit : 0, is_numeric($offset) ? (int) $offset : 0);
    }

    return QueryUtils::fetchRecords($sql, $sqlBindArray);
}
/**
 * This prints a header for documents.  Keeps the brand uniform...
 *  @param string $pid patient_id
 *  @param string $direction, options "web" or anything else.  Web provides apache-friendly url links.
 *  @param bool $allowLogoEdit show the non-printing Practice Logo editor link
 *  @return string|false the header HTML
 */
function report_header($pid, $direction = 'shell', $allowLogoEdit = false)
{
    global $encounter;
    global $visit_date;
    global $OE_SITE_DIR;

    /*******************************************************************
    $titleres = getPatientData($pid, "fname,lname,providerID");
    $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
    *******************************************************************/
    //$titleres = getPatientData($pid, "fname,lname,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
    $titleres = getPatientData($pid, "fname,lname,providerID,DOB");
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $pc_facility = $session->get('pc_facility');
    $facilityService = new FacilityService();
    $facility = $pc_facility ? $facilityService->getById($pc_facility) : $facilityService->getPrimaryBillingLocation();

    $DOB = oeFormatShortDate($titleres['DOB']);
    // Browser images fill their third of the header. mPDF resolves image
    // percentages against the page, so give it a column-safe physical width.
    $logoStyle = $direction === 'web'
        ? 'display:block;width:100%;max-width:100%;height:auto;'
        : 'width:55mm;max-width:100%;height:auto;';
    /******************************************************************/
    ob_start();
    // Use logo if it exists as 'practice_logo.gif' in the site dir
    // old code used the global custom dir which is no longer a valid
    //need to fix logo for multi-site
    ?>
    <table style="width:100%;table-layout:fixed;">
        <tr>
            <td style='width:33%;vertical-align:top;padding-right:12px;'>
                <?php
                if ($direction == "web") {
                    $practiceLogoPath = $OE_SITE_DIR . "/images/practice_logo.gif";
                    $practice_logo = OEGlobalsBag::getInstance()->getWebRoot() . "/sites/" . rawurlencode(basename((string)$OE_SITE_DIR)) . "/images/practice_logo.gif";
                    if (file_exists($practiceLogoPath)) {
                        $practice_logo .= '?t=' . (int)@filemtime($practiceLogoPath);
                        echo "<img src='" . attr($practice_logo) . "' style='" . attr($logoStyle) . "'><br />\n";
                    }
                    if ($allowLogoEdit) { ?>
                        <a href="#" class="no-print" style="display:inline-block;font-size:.85rem;margin-left:10px;"
                           onclick="openEyeAssetEditor('logo', 0); return false;"><?php echo xlt('Edit'); ?></a>
                        <?php
                    }
                } else {
                    $practice_logo = "$OE_SITE_DIR/images/practice_logo.gif";
                    if (file_exists($practice_logo)) {
                        echo "<img src='" . attr($practice_logo) . "' style='" . attr($logoStyle) . "'><br />\n";
                    } else {
                        $practice_logo = "$OE_SITE_DIR/images/login_logo.gif";
                        if (file_exists($practice_logo)) {
                            echo "<img src='" . attr($practice_logo) . "' style='" . attr($logoStyle) . "'><br />\n";
                        }
                    }
                }
                ?>
            </td>
            <td style='width:33%;vertical-align:top;'>
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
                <td style='width:34%;vertical-align:top;'>
                <em style="font-weight:bold;font-size:1.4em;"><?php echo text($titleres['fname']) . " " . text($titleres['lname']); ?></em><br />
                <span style="font-weight:bold;"><?php echo xlt('DOB'); ?>:</span> <?php echo text($DOB); ?><br />
                <span style="font-weight:bold;"><?php echo xlt('Generated on'); ?>:</span> <?php echo text(oeFormatShortDate()); ?><br />
                <span style="font-weight:bold;"><?php echo xlt('Visit Date'); ?>:</span> <?php echo oeFormatSDFT(strtotime((string) $visit_date)); ?><br />
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
 *  The clinical fields are found in table list_options with list_id = Eye_Coding_Fields
 *  The clinical terms to mine for are in table list_options with list_id = Eye_Coding_Terms
 *  Both can be directly extended by the user the via Administration -> Lists interface.
 *  The Eye_Coding_Terms list includes the following important fields:
 *       Title (the term),
 *       Notes (the form_field to search for the term)
 *       Code(s) (the optional user-defined code).
 *  Terms found in a form_field (Notes) with a predefined Code(s), have that code applied.
 *  Terms found in a form_field (Notes) without a predefined Code(s) are concated with
 *      the text value for the form_field (Notes) (found in the list Coding_Eye_Form_Fields: Notes)
 *      and the codebase is searched for a match.
 *  For example: the term "ptosis" is entered in the RUL clinical field of The Eye Form, and there is no Code value in the
 *      Eye_Coding_Terms list's Code(s) field.  Thus openEMR Eye Form searches the active codebases for a match.
 *      The codebases are determined in Admin->Forms->Lists->Code Types and include those Codesets flagged
 *      as active and as Diagnostic codes.  The terms "ptosis right eyelid" are sent to the
 *      standard openEMR code search engine.
 *  @param array<array-key,mixed> $FIELDS - all the clinical fields we are going to scour for clinical terms to code.
 *  @return mixed the codes found
 */
function start_your_engines($FIELDS)
{
//pass an assoc array of fields with terms in it and search them
    global $pid;
    global $codes_found;
    global $PMSFH;
    $FIELDS = EyeFormData::textRow(EyeFormData::row($FIELDS));
    $clinical_terms = [];
    $hit_RVO = [];
    $code = '';
    $DM_code = '';
    $DX = '';
    $sub_term = '';
    $count = 0;
    $PMSFH = build_PMSFH($pid);

    $query = "select * from list_options where list_id ='Eye_Coding_Fields' Order by seq";
    $result = sqlStatement($query);
    while ($fielding = sqlFetchArray($result)) {//build the list of clinical fields to search through
        $fields[$fielding['title']] = $fielding['notes'];
    }

    //get the clinical terms to search for (title) and what field/where to look for it (notes)
    $query = "SELECT * FROM list_options WHERE list_id = 'Eye_Coding_Terms' and activity='1' order by seq";
    $result = sqlStatement($query);
    while ($term_sheet = sqlFetchArray($result)) {
        if ($term_sheet['title'] > '') {
            $newdata =   [
              'term'        => $term_sheet['title'], //the term to search for + possible option_values eg. CSME:DM|IOL|RVO
              'location'    => $term_sheet['notes'], //the Eye Form field to search for the term
              'codes'       => $term_sheet['codes']  //the specific code for this term/location, may be blank
              ];
            $clinical_terms[] = $newdata;
        }
    }

    if (!$clinical_terms) {
        return;
    }

    $positives = [];
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
        $code_found = [];
        if (stripos((string) $amihere['term'], ":") !== false) { //options are stored here code:option_values
            [$term, $option_values] = explode(":", (string) $amihere['term']);
        } else {
            $term = $amihere['term'];
        }

        $matches = [];
        preg_match("/\b$term\b/", ($FIELDS[EyeFormData::text($amihere['location'])] ?? ''), $matches);
        if (!empty($matches)) {
            //the term is in the field
            $within_array = 'no';
            if (isset($positives[EyeFormData::text($amihere['location'])]) > '') { //true if anything was already found in this field
                //do any of the previous hits found in in this location contain this term already?
                //if so stop; if not, continue onward to add to Builder.
                foreach ($positives[EyeFormData::text($amihere['location'])] as $v) {
                    if (preg_match("/\b$term\b/", (string) $v)) {
                        $within_array = 'yes';
                        break;
                    }
                }
            }

            if ($within_array == "yes") {
                continue;
            }

            $positives[EyeFormData::text($amihere['location'])][] = $term;
            if (preg_match("/^(OD)/", EyeFormData::text($amihere['location']))) {
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
                if (stripos((string) $amihere['codes'], ":") !== false) {
                    [$code_type, $code] = explode(":", (string) $amihere['codes']);
                } else {
                    //default to ICD10.  Maybe there is a GLOBALS value for this? Maybe there should be?
                    $code_type = "ICD10";
                }

                $code_found['code'] = $code_type . ":" . $code;
                $code_found['code_type'] = $code_type;
                [$sub_term, $newdata] = coding_engine($term, $code_found, EyeFormData::text($amihere['location']));
                $codes_found[$sub_term][] = $newdata;
                $positives[EyeFormData::text($amihere['location'])][] = $term;
            } else { //no code was defined, further processing needed.
                if ($option_values) {
                    // This clinical finding (term) can be found in more than one disease process ('option_values')
                    // This special group of terms should be processed last, to identify all
                    // possible results for the Builder.
                    // 'option_values' contains pertinent DXs separated by '|', eg. CSME has option values='DM|IOL|RVO'
                    // Need to see if any of these DX apply and builder Codes_found based on the currently installed list of codes
                    // Currently for most users this is ICD10 but it is built to allow extension to any code sets in openEMR,
                    // including foreign language code sets.
                    $options = explode("|", $option_values);
                    $hit_here = "0";

                    foreach ($options as $option) {
                        // if it has mass, try to execute it.
                        $term_now = "";
                        if ($option == "DM") {
                            //This option is run for 3 conditions at present:
                            //CSME/NVD/NVE per eye.  It is the same every time so only do it once, per eye.
                            //Did we already code this?  If so move on.
                            if (!empty($hit_DM) && ($hit_DM[$side1] ?? '') == '1') {
                                continue;
                            }

                            //Are ICD10 etc codes used in other languages?  Via Snomed?  Via user?
                            //Assume there is a standard for xlt/xla purposes...

                            //is the patient diabetic?
                            //search medical_problem for DM
                            $within_array = "";
                            foreach ($PMSFH[0]['PMH'] as $v) {
                                if (stripos((string) $v['codedesc'], "diabetes")) {
                                    $DM_code = $v['codedesc'];
                                    $within_array = 'yes';
                                }
                            }

                            if ($within_array == "yes") {
                                if (stripos((string) $DM_code, "1")) {
                                    $DM_text = "Type 1 diabetes mellitus";
                                    $label = "DM 1";
                                } elseif (stripos((string) $DM_code, "2")) {
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

                            if ((stripos((string) $FIELDS[$location], "flat") === false) && (stripos((string) $FIELDS[$location], "CSME") !== false)) {
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
                                ( (stripos((string) $FIELDS[$location1], $NVD) !== false) ||
                                (stripos((string) $FIELDS[$location2], $NVE) !== false) ||
                                (stripos((string) $FIELDS[$location3], $NVE) !== false) ) &&
                                ( (stripos((string) $FIELDS[$location1], "no " . $NVD) !== true) ||
                                (stripos((string) $FIELDS[$location2], "no " . $NVE) !== true) ||
                                (stripos((string) $FIELDS[$location3], "no " . $NVE) !== true) )
                            ) {
                                $DX = "with proliferative";
                                $label = $label . "w/ PDR " . $hit_CSME;
                                $hit_PDR[$side] = '1';
                            } elseif (
                                (stripos((string) $FIELDS[$location2], $PPDR) !== false) ||
                                (stripos((string) $FIELDS[$location], $IRMA)  !== false) ||
                                (stripos((string) $FIELDS[$location2], $IRMA) !== false) ||
                                (stripos((string) $FIELDS[$location3], $IRMA) !== false)
                            ) {
                                $DX = "with severe nonproliferative";
                                $label = $label . " w/ PPDR " . $hit_CSME;
                                $hit_PPDR[$side] = '1';
                            } elseif (
                                (stripos((string) $FIELDS[$location], $BDR) !== false) ||
                                (stripos((string) $FIELDS[$location2], $BDR) !== false)
                            ) {
                                    $trace = "tr";
                                if (
                                    (stripos((string) $FIELDS[$location], $trace . " " . $BDR) !== false) ||
                                    (stripos((string) $FIELDS[$location2], "+1 " . $BDR) !== false)
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
                                    [$sub_term, $newdata] = coding_engine($label, $found, EyeFormData::text($amihere['location']), $side1);
                                    // The carburetor is a simple machine - it has no boolean options -
                                    // so "with" and "without" match a search for "with"...
                                    // We need to be specific to whittle down the options.
                                    if ((stripos((string) $newdata['codedesc'], $MAC_text)) && (stripos((string) $newdata['codedesc'], $DX))) {
                                        //does this code already exist for the other eye (right eye is always first)?
                                        //if so, change OD to OU and skip adding this code.
                                        //or is there a code for both eyes?
                                        if ($side1 == "OS") {
                                            $count = '0';
                                            for ($i = 0; $i < count($codes_found[$sub_term] ?? []); $i++) {
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
                            $location = $side == "right eye" ? "ODVESSELS" : "OSVESSELS";

                            if ($hit_RVO[$location] == '1') {
                                continue;
                            }

                            if (stripos((string) $FIELDS[$location], "CRVO") !== false) {
                               // this is a CRVO, look up code for location
                                $terms = "CRVO";
                                $code_found = coding_carburetor("central retinal vein", $side);
                                if (isset($code_found)) { //there are matches, present them to the Builder
                                    foreach ($code_found as $found) {
                                        [$sub_term, $newdata] = coding_engine($terms, $found, $location, $side1);
                                        $codes_found[$sub_term][] = $newdata;
                                        $positives[$location][] = "CRVO";
                                        $hit_RVO[$location] = "1";
                                    }
                                }
                            } elseif (stripos((string) $FIELDS[$location], "BRVO") !== false) {
                               // this is a BRVO, look up code for location
                                $code_found = coding_carburetor("branch retinal vein", $side);
                                $terms = "BRVO " . $term;
                                if (isset($code_found)) { //there are matches, present them to the Builder
                                    foreach ($code_found as $found) {
                                        [$sub_term, $newdata] = coding_engine($terms, $found, $location, $side1);
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
                                            [$sub_term, $newdata] = coding_engine($terms, $found, $location, $side1);
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
                            $query = "select begdate as surg_date from lists where pid=? and type='surgery' and title like '%IOL%' and (title like ?)";
                            $surgDate = QueryUtils::fetchSingleValue($query, 'surg_date', [$pid, "%" . $side1 . "%"]);
                            if ($surgDate > '') {
                                $date1 = date('Y-m-d');
                                //$date2 = (DateTime($surgDate));
                                //echo $term."\n".$date."\n";continue;
                                $date_diff = strtotime($date1) - strtotime((string) $surgDate);
                                $interval = $date_diff / (60 * 60 * 24);
                                //$interval was 180, now = 90;
                                if (($interval < '90') && ($term == "CSME")) {
                                    //then this could be post procedure CSME cystoid macular edema  H59.031,2 OD OS
                                    $code_found = coding_carburetor("cystoid macular edema", $side);
                                    if (isset($code_found)) { //there are matches, present them to the Builder
                                        foreach ($code_found as $found) {
                                            $term = "Post-cataract CME";
                                            [$sub_term, $newdata] = coding_engine($term, $found, EyeFormData::text($amihere['location']), $side1);
                                            $codes_found[$sub_term][] = $newdata;
                                            $positives[EyeFormData::text($amihere['location'])][] = $term;
                                            $hit_IOL = '1';
                                        }

                                        if ($side1 == "OS") {
                                            $count = '0';
                                            for ($i = 0; $i < count($codes_found[$sub_term] ?? []); $i++) {
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
                            $code_found = coding_carburetor($term_now, $FIELDS[EyeFormData::text($amihere['location'])]);
                            if (isset($code_found)) { //there are matches, present them to the Builder
                                foreach ($code_found as $found) {
                                    [$sub_term, $newdata] = coding_engine($term, $found, EyeFormData::text($amihere['location']), $side1);
                                    $codes_found[$sub_term][] = $newdata;
                                    $positives[EyeFormData::text($amihere['location'])][] = $term_now;
                                }
                            }
                        }
                    }
                } else {
                    //there are no options and no code identified,
                    //search via carburetor for possible matches to term and description of the form field
                    $code_found = coding_carburetor($term, $FIELDS[EyeFormData::text($amihere['location'])]);
                    if ($code_found !== null) { //there are matches, present them to the Builder
                        foreach ($code_found as $found) {
                            [$sub_term, $newdata] = coding_engine($term, $found, EyeFormData::text($amihere['location']));
                            $codes_found[$sub_term][] = $newdata;
                            $positives[EyeFormData::text($amihere['location'])][] = $term;
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
 *  @return array<array<string, mixed>> array of $codes matching the $term & $field
 */
function coding_carburetor($term, $field)
{
    if (!$term || !$field) {
        return;
    }

    $codes = [];
    $code_type = "ICD10";  //only option is PROD (product or drug search) or NOT PROD...
    $search_term = $term . " " . $field;
    $res = main_code_set_search($code_type, $search_term);
    while ($row = sqlFetchArray($res)) {
        $newdata =   [
                        'code'  =>  $row['code'],
                        'code_text' => $row['code_text'],
                        'code_type' => $row['code_type_name'],
                        'code_desc' => $row['code_desc'] ?? ''
                    ];
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
 *  @return array{0: string, 1: array} First element (subterm) links IMP/PLAN items to origin;
 *          second element (newdata) is array of found items to include in Builder.
 *
 *  This function is not called directly but via the wrapper function start_your_engines().
 */
function coding_engine($term, $code_found, $location, $side = ''): array
{
    if (strpos((string) $code_found['code'], ":")) {
        [$code_type, $code] = explode(':', (string) $code_found['code']);
    } else {
        $code = $code_found['code'];
        $code_type = "ICD10";//default to ICD10
        $code_found['code'] = $code_type . ":" . $code_found['code'];
    }

    $code_desc = lookup_code_descriptions($code_found['code']);
    $order   = ["\r\n", "\n","\r"];
    $code_desc = str_replace($order, '', $code_desc);

    $code_text = text($code_found['code']) . " (" . text($code_desc) . ")";
    $replace = " ";
    $sub_term =  str_replace($replace, "", $term);
    //some codes are bilateral, some not, some are per eyelid.  Comment this out for now:
    //(preg_match("/right/",$code_desc))? $side = xlt('OD{{right eye}}') : $side = xlt('OS{{left eye}}');

    $newdata =   [
        'title'         => ucfirst((string) $term) . " " . $side,
        'location'      => $location,
        'diagnosis'     => $code,
        'code'          => $code,
        'codetype'      => $code_found['code_type'],
        'codedesc'      => $code_desc,
        'codetext'      => $code_text,
        'PMSFH_link'    => "Clinical_" . $sub_term
    ];
    return [$sub_term,$newdata];
}
/**
 *  This is a function to sort an array of dates/times etc
 *  Anything strtotime() can recognize at least.
 */
function cmp($a, $b): int
{
    if ($a == $b) {
        return 0;
    }

    return (strtotime((string) $a) < strtotime((string) $b)) ? -1 : 1;
}

/**
 * This function displays the Glaucoma Flow Sheet.
 * Default is to display IOP measurements 'byday'.
 *
 * @param int    $pid
 * @param string $bywhat == byday or byhour
 * @return string
 */
function display_GlaucomaFlowSheet($pid, $bywhat = 'byday', mixed $currentDate = ''): string
{
    ob_start();
    global $PMSFH;
    global $form_folder;
    global $priors;
    global $provider_id;
    global $documents;
    global $encounter_data;
    global $visit_date;
    $currentDate = EyeFormData::text($currentDate);
    $OCT_date = []; $VF_date = []; $VISITS_date = []; $time_OU = []; $GONIO_date = [];
    $GONIO = []; $ODIOP = []; $OSIOP = []; $ODIOPTARGETS = []; $OSIOPTARGETS = [];
    $GONIO_values = []; $OCT_values = []; $VF_values = []; $OD_values = []; $OS_values = [];
    $OD_methods = []; $OS_methods = []; $ODIOPTARGET_values = []; $OSIOPTARGET_values = [];
    $OD_time_values = []; $OS_time_values = [];
    $OD_time_methods = []; $OS_time_methods = [];
    $current_OCT = ''; $current_VF = ''; $old_OCTs = ''; $old_VFs = '';
    $FAILED_drug = ''; $FAILED_drugs = ''; $gonios = ''; $hideme = ''; $time = '';
    $count = 0;
    $ODIOPTARGET = ''; $OSIOPTARGET = '';

    $iopMethodLabels = eye_mag_iop_method_labels();

    if (!$documents) {
        [$documents] = document_engine($pid);
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
    if (empty($encounter_data['ODIOPTARGET']) || empty($encounter_data['OSIOPTARGET'])) {
        [$ODIOPTARGET, $OSIOPTARGET] = getIOPTARGETS($pid, ($id ?? ''), $provider_id);
    } else {
        $ODIOPTARGET = $encounter_data['ODIOPTARGET'];
        $OSIOPTARGET = $encounter_data['OSIOPTARGET'];
    }

    $i = 0;
    [$ODIOPTARGET, $OSIOPTARGET] = getIOPTARGETS($pid, ($id ?? ''), $provider_id);
    //if there are no priors, this is the first visit, display a generic splash screen.
    if ((array)$priors) {
        if (empty($encounter_data['ODIOPTARGET'])) {
            $encounter_data['ODIOPTARGET'] = $ODIOPTARGET;
        }

        if (empty($encounter_data['OSIOPTARGET'])) {
            $encounter_data['OSIOPTARGET'] = $OSIOPTARGET;
        }
        foreach ($priors as $visit) {
            $visit = EyeFormData::textRow(EyeFormData::row($visit));
            //we need to build the lists - dates_OU,times_OU,gonio_OU,OCT_OU,VF_OU,ODIOP,OSIOP,IOPTARGETS
            if ($visit['date'] == '') {
                continue;
            }

            $old_date_timestamp = strtotime((string) $visit['visit_date']);
            $visit['exam_date'] = date('Y-m-d', $old_date_timestamp);
            $VISITS_date[$i] = $visit['exam_date'];

            $time_here = explode(":", (string) $visit['IOPTIME']);
            $time = $time_here[0] . ":" . $time_here[1];
            $time_OU[$i] = $time;

            if (($visit['ODGONIO'] > '') || ($visit['OSGONIO'] > '')) {
                $GONIO_date[$i] = $visit["exam_date"];
                $GONIO[$i]['list'] = '1';
            } else {
                $GONIO[$i]['list'] = '';
            }

            if ($visit['ODIOPAP'] > '') {
                $ODIOP[$i]['IOP'] = $visit['ODIOPAP'];
                $ODIOP[$i]['method'] = $iopMethodLabels['AP'];
            } elseif ($visit['ODIOPTPN'] > '') {
                $ODIOP[$i]['IOP'] = $visit['ODIOPTPN'];
                $ODIOP[$i]['method'] = $iopMethodLabels['TPN'];
            } else {
                $ODIOP[$i]['IOP'] = "";
            }

            if ($visit['OSIOPAP'] > '') {
                $OSIOP[$i]['IOP'] = $visit['OSIOPAP'];
                $OSIOP[$i]['method'] = $iopMethodLabels['AP'];
            } elseif ($visit['OSIOPTPN'] > '') {
                $OSIOP[$i]['IOP'] = $visit['OSIOPTPN'];
                $OSIOP[$i]['method'] = $iopMethodLabels['TPN'];
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
                $ODIOPTARGETS[$i] = $ODIOPTARGET;
            } elseif (!$ODIOPTARGETS[$j]) {
                $ODIOPTARGETS[$i] = $ODIOPTARGET;
            } else {
                $ODIOPTARGETS[$i] = $ODIOPTARGETS[$j];
            }

            if ($visit['OSIOPTARGET'] > '') {
                $OSIOPTARGETS[$i] = $visit['OSIOPTARGET'];
            } elseif ($i == 0) {
                $OSIOPTARGETS[$i] = $OSIOPTARGET;
            } elseif (!$OSIOPTARGETS[$j]) {
                $OSIOPTARGETS[$i] = $OSIOPTARGET;
            } else {
                $OSIOPTARGETS[$i] = $OSIOPTARGETS[$j];
            }
            $i++;
        }
    } else { //there are no priors, get info from this visit
            $VISITS_date[0] = $currentDate;
        if ($encounter_data['IOPTIME']) {
            $time_here = explode(":", (string) $encounter_data['IOPTIME']);
            $time = $time_here[0] . ":" . $time_here[1];
            $time_OU[] = $time;
        }

        if ($encounter_data['ODGONIO'] || $encounter_data['OSGONIO']) {
            $GONIO_date[$i] = $currentDate;
        }

            $ODIOP[$i]['time'] = $time;
            $OSIOP[$i]['time'] = $time;

        if ($encounter_data['ODIOPAP'] > '') {
            $ODIOP[$i]['IOP'] = !is_int($encounter_data['ODIOPAP']) ? '' : $encounter_data['ODIOPAP'];
            $ODIOP[$i]['method'] = $iopMethodLabels['AP'];
        } elseif ($encounter_data['ODIOPTPN'] > '') {
            $ODIOP[$i]['IOP'] = $encounter_data['ODIOPTPN'];
            $ODIOP[$i]['method'] = $iopMethodLabels['TPN'];
        }

        if ($encounter_data['OSIOPAP'] > '') {
            $OSIOP[$i]['IOP'] = !is_int($encounter_data['OSIOPAP']) ? '' : $encounter_data['OSIOPAP'];
            $OSIOP[$i]['method'] = $iopMethodLabels['AP'];
        } elseif ($encounter_data['OSIOPTPN'] > '') {
            $OSIOP[$i]['IOP'] = $encounter_data['OSIOPTPN'];
            $OSIOP[$i]['method'] = $iopMethodLabels['TPN'];
        } else {
            //we are ignoring finger tension for graphing purposes but include this should another form of IOP measurement arrive...
            //What about the Triggerfish contact lens continuous IOP device for example...  iCare device, etc
        }
        if ($encounter_data['ODIOPTARGET'] > '0') {
            $ODIOPTARGETS[$i] = $encounter_data['ODIOPTARGET'];
        } else {
            $ODIOPTARGETS[$i] = $ODIOPTARGET;
            $encounter_data['ODIOPTARGET'] = $ODIOPTARGET;
        }

        if ($encounter_data['OSIOPTARGET']) {
            $OSIOPTARGETS[$i] = $encounter_data['OSIOPTARGET'];
        } else {
            $OSIOPTARGETS[$i] = $OSIOPTARGET;
            $encounter_data['OSIOPTARGET'] = $OSIOPTARGET;
        }
    }

    //There are visits for testing only, no IOP, or old tests have been imported, like VF or OCTs...
    //We need to insert these dates into the arrays created above.
    //recreate them to include the testing only dates, placing null values for those dates if not done.

    //can't merge empty arrays
    $list = [];
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
    usort($date_OU, cmp(...));
    $times_OU = $time_OU;
    usort($times_OU, cmp(...));
    $timeLabelsOU = array_map(
        /** Format each pressure-measurement time using the practice time display setting. */
        static fn(string $time): string => DateFormatterUtils::oeFormatTime($time, 'global'),
        $times_OU
    );

    for ($a = 0; $a < count($date_OU); $a++) {
        if (!empty($GONIO_date)) {
            foreach ($GONIO_date as $GONIO) {
                if ($date_OU[$a] == $GONIO) {
                    $GONIO_values[$a] = "4";
                    break;
                }
            }
        }

        if (!($GONIO_values[$a] ?? null)) {
            $GONIO_values[$a] = "";
        }

        if ($count_OCT > 0) {
            foreach ($OCT_date as $OCT) {
                if ($date_OU[$a] == $OCT) {
                    $OCT_values[$a] = "4";
                    break;
                }
            }
        }

        if (!($OCT_values[$a] ?? null)) {
            $OCT_values[$a] = "";
        }

        if ($count_VF > 0) {
            foreach ($VF_date as $VF) {
                if ($date_OU[$a] == $VF) {
                    $VF_values[$a] = "4";
                    break;
                }
            }
        }

        if (!($VF_values[$a] ?? null)) {
            $VF_values[$a] = "";
        }

        for ($k = 0; $k < count($VISITS_date); $k++) {
            if ($date_OU[$a] == $VISITS_date[$k]) {
                if (preg_match('/[a-z]/i', EyeFormData::text($ODIOP[$k]['IOP'] ?? ''))) {
                    $ODIOP[$k]['IOP'] = '';}
                if (preg_match('/[a-z]/i', EyeFormData::text($OSIOP[$k]['IOP'] ?? ''))) {
                    $OSIOP[$k]['IOP'] = '';}
                $OD_values[$a] = ($ODIOP[$k]['IOP'] ?? '');
                $OD_methods[$a] = $ODIOP[$k]['method'] ?? '';
                $OS_values[$a] = $OSIOP[$k]['IOP'] ?? '';
                $OS_methods[$a] = $OSIOP[$k]['method'] ?? '';
                $ODIOPTARGET_values[$a] = $ODIOPTARGETS[$k] ?? '';
                $OSIOPTARGET_values[$a] = $OSIOPTARGETS[$k] ?? '';
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
                $OD_time_values[$a] = ($ODIOP[$k]['IOP'] ?? '');
                $OS_time_values[$a] = ($OSIOP[$k]['IOP'] ?? '');
                $OD_time_methods[$a] = ($ODIOP[$k]['method'] ?? '');
                $OS_time_methods[$a] = ($OSIOP[$k]['method'] ?? '');
                break;
            }
        }
        $OD_time_methods[$a] ??= '';
        $OS_time_methods[$a] ??= '';
    }

    ?> <p style="font-weight:bold;"> <?php echo xlt('Glaucoma Zone'); ?>:</p>
       <span class="closeButton fas fa-times" id="Close_IOP" name="Close_IOP"></span>
        <div id="GFS_table" name="GFS_table"
             style="position:relative;display:table;float:left;margin-top:10px;padding:15px;text-align:left;vertical-align:center;width:30%;">
            <table class="GFS_table table-responsive borderShadow">
                <tr >
                    <td colspan="1" class="GFS_title_1" style="padding-bottom:3px;border:none;" nowrap><?php echo xlt('Current Targets'); ?>:
                        <td class='GFS_title center' style="padding-bottom:3px;border:none;" nowrap><?php echo xlt('OD{{right eye}}'); ?>: <input type="text" style="width: 20px;" name="ODIOPTARGET" id="ODIOPTARGET" value="<?php echo attr($encounter_data['ODIOPTARGET']); ?>" /></td>
                        <td class='GFS_title center' style="padding-bottom:3px;border:none;" nowrap><?php echo xlt('OS{{left eye}}'); ?>: <input type="text" style="width: 20px;" name="OSIOPTARGET" id="OSIOPTARGET"  value="<?php echo attr($encounter_data['OSIOPTARGET']); ?>"  /></td>
                </tr>

                <?php
                    //what active meds have a subtype eye?
                    $i = 0;
                    $count_Meds = count($PMSFH[0]['Medication']);
                if ($count_Meds > '0') {
                    foreach ($PMSFH[0]['Medication'] as $drug) {
                        if (($drug['row_subtype'] == "eye") && (strtotime($drug['enddate'] ?? '') < strtotime($visit_date ?? '') ) && ($drug['status'] != "Inactive")) {
                            $current_drugs ??= '';
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

                    if (!($current_drugs ?? null)) {
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
                        <?php  ($no_drugs ?? null) ? ($meds_here = '') : $meds_here = xlt('Start'); ?>
                    <td class="GFS_title" style="text-align:center;"><?php echo $meds_here; ?></td>
                    <?php
                    if ($FAILED_drugs !== '') {
                        echo '<td><span class="right toggleme" id="toggle_drugs"><i class="fa-regular fa-square-caret-down"></i></span></td>';
                    } ?>
                </tr>
                <?php
                echo $current_drugs ?? '';
                if ($FAILED_drugs !== '') {
                    echo '<tr class="' . $hideme . '"><td class="GFS_title" colspan="1">' . xlt('Prior Eye Meds') . '</td><td class="GFS_title" style="text-align:center;">' . xlt('Start') . '</td><td  style="text-align:center;" class="GFS_title">End</td></tr>';
                }

                echo $FAILED_drugs;

                //start VF section
                if ($count_VF > '0') { //need to decide how many to show on open, and hide the rest?  For now the first only.
                    $count = 0;
                    foreach ($documents['docs_in_name']['VF'] as $VF) {
                        if ($count < 1) {
                            $current_VF = '<tr><td class="GFS_td_1 blue">
                                <a onclick="openNewForm(\'' . OEGlobalsBag::getInstance()->getWebRoot() . '/controller.php?document&view&patient_id=' . attr($pid) . '&doc_id=' . attr($VF['id']) . '\',\'Documents\');">
                                <img src="../../forms/' . $form_folder . '/images/jpg.png" class="little_image" style="width:15px; height:15px;" /></a>
                                </td>
                                <td class="GFS_td_1">' . $VF['docdate'] . '</td>
                                </tr>';
                        } else {
                            $old_VFs .= '<tr><td class="GFS_td_1 hideme_VFs nodisplay"">
                                <a onclick="openNewForm(\'' . OEGlobalsBag::getInstance()->getWebRoot() . '/controller.php?document&view&patient_id=' . attr($pid) . '&doc_id=' . attr($VF['id']) . '\',\'Documents\');">
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
                    if ($old_VFs !== '') {
                        echo '<td><span class="top right" id="toggle_VFs"><i class="fa-regular fa-square-caret-down"></i></span></td>';
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
                                            <a onclick="openNewForm(\'' . OEGlobalsBag::getInstance()->getWebRoot() . '/controller.php?document&view&patient_id=' . attr($pid) . '&doc_id=' . attr($OCT['id']) . '\',\'Documents\');"><img src="../../forms/' . $form_folder . '/images/jpg.png" class="little_image" style="width:15px; height:15px;" /></a>
                                            </td>
                                            <td class="GFS_td_1">' . $OCT['docdate'] . '</td>
                                        </tr>
                                        ';
                                } else {
                                    $old_OCTs .= '<tr><td class="hideme_OCTs nodisplay GFS_td_1">
                                                <a onclick="openNewForm(\'' . OEGlobalsBag::getInstance()->getWebRoot() . '/controller.php?document&view&patient_id=' . attr($pid) . '&doc_id=' . attr($OCT['id']) . '\',\'Documents\');"><img src="../../forms/' . $form_folder . '/images/jpg.png" class="little_image" style="width:15px; height:15px;" /></a>
                                                </td><td class="hideme_OCTs nodisplay GFS_td_1">' . $OCT['docdate'] . '</td></tr>';
                                }
                                $count++;
                            }
                        } else {
                            $current_OCT = "<tr><td colspan='3' class='GFS_td_1' style='text-align:center;'>" . xlt('Not documented') . "</td></tr>";
                        }

                        if ($old_OCTs !== '') {
                            echo '<td><span class="top right " id="toggle_OCTs"><i class="fa-regular fa-square-caret-down"></i></span></td>';
                        }

                        echo "</tr>";
                        echo $current_OCT . $old_OCTs;

                        $count = 0;
                        $hideme = '';
                        foreach ($priors as $visit) {
                            $visit = EyeFormData::textRow(EyeFormData::row($visit));
                            if (($visit['ODGONIO'] > " ") || ($visit['OSGONIO'] > " ")) { // something is here
                                if ($count > 0) {
                                    $hideme = "hideme_gonios nodisplay";// show the first only, hide the rest for now
                                }

                                $examDate = $visit['exam_date'] ?? null;
                                $odGonio = $visit['ODGONIO'] ?? null;
                                $osGonio = $visit['OSGONIO'] ?? null;
                                $gonios .= "<tr><td class='GFS_td_1 " . $hideme . "'>"
                                    . text(is_string($examDate) ? $examDate : '')
                                    . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;'>"
                                    . text(is_string($odGonio) ? $odGonio : '')
                                    . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;'>"
                                    . text(is_string($osGonio) ? $osGonio : '')
                                    . "</td></tr>";
                                if (!empty($GONIO_chart)) {
                                    $GONIO_chart .= '"1",';
                                } else {
                                    $GONIO_chart = '"1",';
                                }
                                $count++;
                            } else {
                                if (!empty($GONIO_chart)) {
                                    $GONIO_chart .= ',';
                                } else {
                                    $GONIO_chart = '"1",';
                                }
                            }
                        }
                        if (!empty($GONIO[$i]['list'])) {
                            $GONIO = rtrim(((string) $GONIO[$i]['list']), ",");
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
                        echo '<td><span class="top right" id="toggle_gonios"><i class="fa-regular fa-square-caret-down"></i></span></td>';
                    }
                    ?>
                </tr>
                    <?php echo $gonios;

                    $count = '0';
                    $hideme = '';
                    foreach ($priors as $visit) {
                        $visit = EyeFormData::textRow(EyeFormData::row($visit));
                        if (($visit['ODCUP'] > "") || ($visit['OSCUP'] > "")) {
                            if ($count > 0) {
                                $hideme = "hideme_cups nodisplay";
                            }

                            if ($cups ?? null) {
                                $cups .= "<tr><td class='GFS_td_1 " . $hideme . " '>" . text($visit['exam_date']) . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;'>" . text($visit['ODCUP']) . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;''>" . text($visit['OSCUP']) . "</td></tr>";
                            } else {
                                $cups = "<tr><td class='GFS_td_1 " . $hideme . " '>" . text($visit['exam_date']) . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;'>" . text($visit['ODCUP']) . "</td><td class='GFS_td " . $hideme . "' style='border:1pt dotted gray;''>" . text($visit['OSCUP']) . "</td></tr>";
                            }

                            $DISCS_chart ??= '';
                            $DISCS_chart .= '"1",';
                            $count++;
                        } else {
                            if (!empty($DISCS_chart)) {
                                $DISCS_chart .= '"",';
                            } else {
                                $DISCS_chart = '"",';
                            }
                        }
                    }

                    $DISCS_chart = rtrim(($DISCS_chart ?? ''), ",");
                    if ($count == 0) {
                        $cups = "<tr><td colspan='3' class='GFS_td_1' style='text-align:center;'>" . xlt('Not documented') . "</td></tr>";
                    }
                    ?>
                <tr>
                    <td class="GFS_title_1" id="GFS_cups" name="GFS_cups" title="<?php echo xla('Click this to display/hide additional tests'); ?>"style="position:relative;"><?php echo xlt('Optic Discs'); ?>:
                    <?php
                    if ($hideme) {
                        $plus = '<td><span class="top right" id="toggle_cups"><i class="fa-regular fa-square-caret-down"></i></span></td>';
                    }

                    if ($count > '0') {
                        echo "<td class='GFS_title center'>" . xlt('OD{{right eye}}') . "</td><td class='GFS_title center'>" . xlt('OS{{left eye}}') . "</td>" . ($plus ?? '');
                    } else {
                        echo "<td class='GFS_title center'></td><td class='GFS_title center'></td>";
                    }
                    ?>
                </tr>
                        <?php echo $cups ?? ''; ?>

            </table>
        </div>
        <div class="right" style="position:relative;float:right; margin: 0px 5px 10px;text-align:center;width:60%;">
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
                    <div id="gfs_byday_header" class="gfs-chart-header <?php echo attr($class_2); ?>">
                        <div class="gfs-chart-title"><?php echo xlt('Intraocular Pressures by Date'); ?></div>
                        <div class="gfs-chart-legend-row">
                            <button type="button" data-dataset-index="0"><span class="gfs-legend-swatch gfs-target-od"></span><?php echo xlt('Target OD'); ?></button>
                            <button type="button" data-dataset-index="1"><span class="gfs-legend-swatch gfs-target-os"></span><?php echo xlt('Target OS'); ?></button>
                            <button type="button" data-dataset-index="2"><span class="gfs-legend-swatch gfs-iop-od"></span><?php echo xlt('OD'); ?></button>
                            <button type="button" data-dataset-index="3"><span class="gfs-legend-swatch gfs-iop-os"></span><?php echo xlt('OS'); ?></button>
                        </div>
                        <div class="gfs-chart-legend-row">
                            <button type="button" data-dataset-index="4"><span class="gfs-legend-swatch gfs-test-vf"></span><?php echo xlt('VF'); ?></button>
                            <button type="button" data-dataset-index="5"><span class="gfs-legend-swatch gfs-test-oct"></span><?php echo xlt('OCT'); ?></button>
                            <button type="button" data-dataset-index="6"><span class="gfs-legend-swatch gfs-test-gonio"></span><?php echo xlt('Gonio'); ?></button>
                        </div>
                    </div>
                    <canvas id="canvas_byday" class="<?php echo $class_2; ?>"></canvas>
                    <div id="gfs_byhour_header" class="gfs-chart-header <?php echo attr($class_1); ?>">
                        <div class="gfs-chart-title"><?php echo xlt('Intraocular Pressures by Time of Day'); ?></div>
                        <div class="gfs-chart-legend-row">
                            <button type="button" data-dataset-index="0"><span class="gfs-legend-swatch gfs-iop-od"></span><?php echo xlt('OD'); ?></button>
                            <button type="button" data-dataset-index="1"><span class="gfs-legend-swatch gfs-iop-os"></span><?php echo xlt('OS'); ?></button>
                        </div>
                    </div>
                    <canvas id="canvas_byhour" class="<?php echo $class_1; ?>"></canvas>
                    <hr />
                    <button id="dailyData" class="<?php echo $class_1; ?>"><?php echo xlt('Show IOP by Date'); ?></button>
                    <button id="hourlyData" class="<?php echo $class_2; ?>"><?php echo xlt('Show IOP by Time'); ?></button>


                    <script>
                    /**
                     *  Below is the Chart.js code to render IOP by day and IOP by time
                     *
                     */
                    var visit_date = '<?php echo attr($currentDate); ?>';
                    var dateFormat = 'YYYY-MM-DD';

                    var config_byhour = {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($timeLabelsOU, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                            rawTimes: <?php echo json_encode($times_OU, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                            datasets: [
                                {
                                    type: 'line',
                                    label: "OD",
                                    data: <?php echo json_encode($OD_time_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                                    iopMethods: <?php echo json_encode($OD_time_methods, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
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
                                },
                                {
                                    type: 'line',
                                    label: 'OS',
                                    data: <?php echo json_encode($OS_time_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                                    iopMethods: <?php echo json_encode($OS_time_methods, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                                    fill: false,
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
                                    pointHitRadius: 3
                                }
                            ]
                        },
                        options: {
                            plugins: {
                                title: {
                                    display: false,
                                    text: '<?php echo xla("Intraocular Pressures by Time of Day"); ?>'
                                },
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        afterLabel: function(context) {
                                            var methods = context.dataset.iopMethods || [];
                                            var method = methods[context.dataIndex] || '';
                                            return method ? '<?php echo xla('Method'); ?>: ' + method : '';
                                        }
                                    }
                                }
                            },

                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: '<?php echo xla("Time of Day"); ?>',
                                        font: {
                                            weight: 'bold' // Set the font weight to bold
                                        }
                                    },
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: '<?php echo xla("Intraocular Pressures (mmHg)"); ?>',
                                        font: {
                                            weight: 'bold' // Set the font weight to bold
                                        }
                                    },
                                    beginAtZero: true,
                                    Min: 0,
                                    suggestedMax: 35
                                }
                            }
                        }
                    };

                    $('#dailyData').click(function(event) {
                                          event.preventDefault();
                                          $('#gfs_byday_header').removeClass('nodisplay');
                                          $('#gfs_byhour_header').addClass('nodisplay');
                                          $('#canvas_byday').removeClass('nodisplay');
                                          $('#canvas_byhour').addClass('nodisplay');

                                          $('#dailyData').addClass('nodisplay');
                                          $('#hourlyData').removeClass('nodisplay');
                                          $('#showTesting').addClass('nodisplay');
                                          });
                    $('#hourlyData').click(function(event) {
                                           event.preventDefault();
                                           $('#gfs_byday_header').addClass('nodisplay');
                                           $('#gfs_byhour_header').removeClass('nodisplay');
                                           $('#canvas_byhour').removeClass('nodisplay');
                                           $('#canvas_byday').addClass('nodisplay');
                                           $('#dailyData').removeClass('nodisplay');
                                           $('#hourlyData').addClass('nodisplay');
                                           $('#showTesting').removeClass('nodisplay');
                                           });
                    var config_byday = {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($date_OU, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                            datasets: [
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "Target OD",
                                    data: <?php echo json_encode($ODIOPTARGET_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                                    fill: false,
                                    borderColor : "#f28282",
                                    backgroundColor : "#f28282",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "#f28282",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                },
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "Target OS",
                                    data: <?php echo json_encode($OSIOPTARGET_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                                    fill: false,
                                    borderColor : "#AA8282",
                                    backgroundColor : "#AA8282",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "#f28282",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                },

                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "OD",
                                    data: <?php echo json_encode($OD_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                                    iopMethods: <?php echo json_encode($OD_methods, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
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
                                },
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: 'OS',
                                    data: <?php echo json_encode($OS_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                                    iopMethods: <?php echo json_encode($OS_methods, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
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
                                    pointHitRadius: 3
                                },
                                {
                                    axis: 'y',
                                    type: 'bar',
                                    label: "VF",
                                    strokeColor: '#5CABFA',
                                    fillColor:"#5CABFA",
                                    data: <?php echo json_encode($VF_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                                    fill: true,
                                    backgroundColor: '#5CABFA'
                                },
                                {
                                    axis: 'y',
                                    type: 'bar',
                                    label: "OCT",
                                    data: <?php echo json_encode($OCT_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>, //0/null is not done, 1 if performed.
                                    fill: true,
                                    backgroundColor: '#71B37C'
                                },
                                {
                                    axis: 'y',
                                    type: 'bar',
                                    label: "Gonio",
                                    data: <?php echo json_encode($GONIO_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>,
                                    fill: false,
                                    strokeColor: 'rgba(209, 30, 93, 0.3)',
                                    fillColor:'rgba(209, 30, 93, 0.3)',
                                    backgroundColor: 'red'
                                }
                            ]
                        },
                        options: {
                            plugins: {
                                title: {
                                    display: false,
                                    text: '<?php echo xla("Intraocular Pressures by Date"); ?>'
                                },
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        afterLabel: function(context) {
                                            var methods = context.dataset.iopMethods || [];
                                            var method = methods[context.dataIndex] || '';
                                            return method ? '<?php echo xla('Method'); ?>: ' + method : '';
                                        }
                                    }
                                }
                            },
                            responsive: true,
                            tooltips: {
                                enabled: true
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: '<?php echo xla("Visit Dates"); ?>',
                                        font: {
                                            weight: 'bold' // Set the font weight to bold
                                        }
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: '<?php echo xla("Intraocular Pressures (mmHg)"); ?>',
                                        font: {
                                            weight: 'bold' // Set the font weight to bold
                                        }
                                    },
                                    beginAtZero: true,
                                    Min: 0,
                                    suggestedMax: 35
                                }
                            }
                        }
                    };

                    var ctx1 = document.getElementById("canvas_byday").getContext("2d");
                    var ctx2 = document.getElementById("canvas_byhour").getContext("2d");

                    var myLine = new Chart(ctx1, config_byday);
                    var myLine2 = new Chart(ctx2, config_byhour);
                    $('#gfs_byday_header [data-dataset-index]').on('click', function() {
                        var datasetIndex = parseInt($(this).attr('data-dataset-index'), 10);
                        var wasVisible = myLine.isDatasetVisible(datasetIndex);
                        myLine.setDatasetVisibility(datasetIndex, !wasVisible);
                        $(this).toggleClass('is-hidden', wasVisible);
                        myLine.update();
                    });
                    $('#gfs_byhour_header [data-dataset-index]').on('click', function() {
                        var datasetIndex = parseInt($(this).attr('data-dataset-index'), 10);
                        var wasVisible = myLine2.isDatasetVisible(datasetIndex);
                        myLine2.setDatasetVisibility(datasetIndex, !wasVisible);
                        $(this).toggleClass('is-hidden', wasVisible);
                        myLine2.update();
                    });

                </script>
                <?php
            } else {
                echo "<div style='text-align:left;padding-left:20px;margin:40px;font-size:1.3em;'><span>The Glaucoma Flow Sheet graphically displays:
                <ul>
                <li> IOP measurements</li>
                <li> Target IOPs </li>
                <li> related tests (OCT/VF/Gonio)</li>
                <li> diurnal IOP curve</li>
                </ul>
                The graphs are not generated on the initial visit...</span></div>";
            } ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


/**
 * This function displays today + historical visual acuity measurements
 * I imagined this as a condensed table with top row = types of acuities
 *   Date | sc | CC | Ph | AR | MR | CR | CTL | comments
 * second row OD/OS
 * OD - OS|OD - OS|OD - OS|OD - OS|OD - OS|OD - OS|
 *
 * and 3rd row on: the leftmost/first column containing Date of visit, then the actual measurements obtained
 *
 * @param int $pid
 * @return string
 */
function display_VisualAcuities($pid = 0): string
{
    ob_start();
    global $priors;
    global $visit_date;
    global $dated;

    if ($priors) {
        // We have to get this data if it exists for each prior visit.
        // if it is a resource hog, then maybe show just the last 10
        //  Have to test that but for now show it all.
        usort($priors, function ($a, $b) {
            $dateA = strtotime((string) $a['visit_date']);
            $dateB = strtotime((string) $b['visit_date']);

            return $dateB - $dateA; // Sort in descending order
            //return $b['visit_date'] <=> $a['visit_date'];
        });
        $flip_priors = new ArrayIterator(array_reverse($priors));
        $array_va_dates = [];
        $array_va_SCODVA = [];
        $array_va_SCOSVA = [];
        $array_va_CCODVA = [];
        $array_va_CCOSVA = [];
        $array_va_PHODVA = [];
        $array_va_PHOSVA = [];
        $array_va_ARODVA = [];
        $array_va_AROSVA = [];
        $array_va_MRODVA = [];
        $array_va_MROSVA = [];
        $array_va_CRODVA = [];
        $array_va_CROSVA = [];
        $array_va_CTLODVA = [];
        $array_va_CTLOSVA = [];
        foreach ($flip_priors as $prior) {
            $prior = EyeFormData::textRow(EyeFormData::row($prior));
            if ($prior['visit_date'] > $visit_date) {
                continue;
            }
            $old_date_timestamp = strtotime((string) $prior['visit_date']);
            $visit_timestamp = strtotime((string) $visit_date);

            if ($old_date_timestamp > $visit_timestamp) {
                continue;
            }
            // Current Correction or CC refers to the VA with current glasses.
            $query  = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? and RX_NUMBER =1";
            $wear   = sqlQuery($query, [$pid,$prior['form_id']]);
            $prior['CCODVA'] = $wear['ODVA'];
            $prior['CCOSVA'] = $wear['OSVA'];

            $visit['exam_date'] = date('Y-m-d', $old_date_timestamp);
            $array_va_dates[]   = $visit['exam_date'];
            // Some people write Va as 20/20, or 6/6.
            // We need to remove the prefix + the "/"
            // We are also ignoring HM,LP,NLP,CF etc for graphing purposes.
            // Just take digits, even ignore + and - notations
            $prior['SCODVA'] = preg_replace("/\d+\//", "", (string) $prior['SCODVA']);
            $prior['SCOSVA'] = preg_replace("/\d+\//", "", (string) $prior['SCOSVA']);
            $prior['CCODVA'] = preg_replace("/\d+\//", "", (string) $prior['CCODVA']);
            $prior['CCOSVA'] = preg_replace("/\d+\//", "", (string) $prior['CCOSVA']);
            $prior['ARODVA'] = preg_replace("/\d+\//", "", (string) $prior['ARODVA']);
            $prior['AROSVA'] = preg_replace("/\d+\//", "", (string) $prior['AROSVA']);
            $prior['CRODVA'] = preg_replace("/\d+\//", "", (string) $prior['CRODVA']);
            $prior['CROSVA'] = preg_replace("/\d+\//", "", (string) $prior['CROSVA']);
            $prior['MRODVA'] = preg_replace("/\d+\//", "", (string) $prior['MRODVA']);
            $prior['MROSVA'] = preg_replace("/\d+\//", "", (string) $prior['MROSVA']);
            $prior['CTLODVA'] = preg_replace("/\d+\//", "", (string) $prior['CTLODVA']);
            $prior['CTLOSVA'] = preg_replace("/\d+\//", "", (string) $prior['CTLOSVA']);
            $array_va_SCODVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['SCODVA']);
            $array_va_SCOSVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['SCOSVA']);
            $array_va_CCODVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['CCODVA']);
            $array_va_CCOSVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['CCOSVA']);
            $array_va_PHODVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['PHODVA']);
            $array_va_PHOSVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['PHOSVA']);
            $array_va_ARODVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['ARODVA']);
            $array_va_AROSVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['AROSVA']);
            $array_va_MRODVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['MRODVA']);
            $array_va_MROSVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['MROSVA']);
            $array_va_CRODVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['CRODVA']);
            $array_va_CROSVA[]  = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['CROSVA']);
            $array_va_CTLODVA[] = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['CTLODVA']);
            $array_va_CTLOSVA[] = preg_replace("/(.*)[^\d](.*)/", "$1", (string) $prior['CTLOSVA']);
            $flip_priors_CC[] = $prior;
        }

        $VA_dates = implode("','", $array_va_dates);
        $VA_SCODVA = implode(',', $array_va_SCODVA);
        $VA_SCOSVA = implode(',', $array_va_SCOSVA);
        $VA_CCODVA = implode(',', $array_va_CCODVA);
        $VA_CCOSVA = implode(',', $array_va_CCOSVA);
        $VA_PHODVA = implode(',', $array_va_PHODVA);
        $VA_PHOSVA = implode(',', $array_va_PHOSVA);
        $VA_ARODVA = implode(',', $array_va_ARODVA);
        $VA_AROSVA = implode(',', $array_va_AROSVA);
        $VA_MRODVA = implode(',', $array_va_MRODVA);
        $VA_MROSVA = implode(',', $array_va_MROSVA);
        $VA_CRODVA = implode(',', $array_va_CRODVA);
        $VA_CROSVA = implode(',', $array_va_CROSVA);
        $VA_CTLODVA = implode(',', $array_va_CTLODVA);
        $VA_CTLOSVA = implode(',', $array_va_CTLOSVA);
        ?>
        <div>
            <span class="closeButton fas fa-times" id="Close_VAHx" name="Close_VAHx"></span>
            <h5><?php echo xlt('Visual Acuity History'); ?></h5>
        </div>
        <div id="VAHX_table" name="VAHX_table" class="borderShadow table-responsive"
             style="position:relative;display:inline-block;float:left;vertical-align: middle;width:40%;">
            <table class="table d-inline">
                    <tr class="bold text-center">
                        <th></th>
                        <?php if ($VA_SCODVA || $VA_SCOSVA) { ?>
                            <th colspan="2" class="vasc_class"><?php echo xlt('SC{{abbr without correction}}'); ?></th>
                            <?php
                        }
                        if ($VA_CCODVA || $VA_CCOSVA) { ?>
                                <th colspan="2" class="vacc_class"><?php echo xlt('CC{{abbr Acuity with current Correction}}'); ?></th>
                                <?php
                        }
                        if ($VA_PHODVA || $VA_PHOSVA) { ?>
                                <th colspan="2" class="vaph_class"><?php echo xlt('Ph{{abbr Pinhole acuity}}'); ?></th>
                                <?php
                        }
                        if ($VA_ARODVA || $VA_AROSVA) { ?>
                                <th colspan="2" class="vaar_class"><?php echo xlt('AR{{abbr Autorefraction acuity}}'); ?></th>
                                <?php
                        }
                        if ($VA_MRODVA || $VA_MROSVA) { ?>
                                <th colspan="2" class="vamr_class"><?php echo xlt('MR{{abbr Manifest Refraction acuity}}'); ?></th>
                                <?php
                        }
                        if ($VA_CRODVA || $VA_CROSVA) { ?>
                                <th colspan="2" class="vacr_class"><?php echo xlt('CR{{abbr Cycloplegic Refraction acuity}}'); ?></th>
                                <?php
                        }
                        if ($VA_CTLODVA || $VA_CTLOSVA) { ?>
                                <th colspan="2" class="vactl_class"><?php echo xlt('CTL{{abbr Contact Lens Refraction acuity}}'); ?></th>
                            <?php  }  ?>
                    </tr>
                    <tr class="bold underline text-center">
                        <td><?php echo xlt('Date'); ?></td>
                        <?php if ($VA_SCODVA || $VA_SCOSVA) { ?>
                            <td class="vasc_class"><?php echo xlt('OD{{abbr Right Eye}}'); ?></td><td class="vasc_class"><?php echo xlt('OS{{abbr Left Eye}}'); ?></td>
                            <?php
                        }
                        if ($VA_CCODVA || $VA_CCOSVA) { ?>
                                <td class="vacc_class"><?php echo xlt('OD{{abbr Right Eye}}'); ?></td><td class="vacc_class"><?php echo xlt('OS{{abbr Left Eye}}'); ?></td>
                                <?php
                        }
                        if ($VA_PHODVA || $VA_PHOSVA) { ?>
                                <td class="vaph_class"><?php echo xlt('OD{{abbr Right Eye}}'); ?></td><td class="vaph_class"><?php echo xlt('OS{{abbr Left Eye}}'); ?></td>
                                <?php
                        }
                        if ($VA_ARODVA || $VA_AROSVA) { ?>
                                <td class="vaar_class"><?php echo xlt('OD{{abbr Right Eye}}'); ?></td><td class="vaar_class"><?php echo xlt('OS{{abbr Left Eye}}'); ?></td>
                                <?php
                        }
                        if ($VA_MRODVA || $VA_MROSVA) { ?>
                                <td class="vacr_class"><?php echo xlt('OD{{abbr Right Eye}}'); ?></td><td class="vacr_class"><?php echo xlt('OS{{abbr Left Eye}}'); ?></td>
                                <?php
                        }
                        if ($VA_CRODVA || $VA_CROSVA) { ?>
                                <td class="vacr_class"><?php echo xlt('OD{{abbr Right Eye}}'); ?></td><td class="vacr_class"><?php echo xlt('OS{{abbr Left Eye}}'); ?></td>
                                <?php
                        }
                        if ($VA_CTLODVA || $VA_CTLOSVA) { ?>
                                <td class="vactl_class"><?php echo xlt('OD{{abbr Right Eye}}'); ?></td><td class="vactl_class"><?php echo xlt('OS{{abbr Left Eye}}'); ?></td>
                                <?php
                        }
                        ?>
                    </tr>
                    <?php
                    if (!empty($flip_priors_CC)) {
                        foreach ($flip_priors_CC as $prior) {
                            ?>
                                <tr>
                                    <td><?php echo text($prior['visit_date']); ?></td>
                                <?php
                                if ($VA_SCODVA || $VA_SCOSVA) { ?>
                                            <td class="vasc_class"><?php echo text(EyeFormData::text($prior['SCODVA'])); ?></td><td class="vasc_class"><?php echo text(EyeFormData::text($prior['SCOSVA'])); ?></td>
                                            <?php
                                }
                                if ($VA_CCODVA || $VA_CCOSVA) { ?>
                                            <td class="vacc_class"><?php echo text(EyeFormData::text($prior['CCODVA'])); ?></td><td class="vacc_class"><?php echo text(EyeFormData::text($prior['CCOSVA'])); ?></td>
                                            <?php
                                }
                                if ($VA_PHODVA || $VA_PHOSVA) { ?>
                                            <td class="vaph_class"><?php echo text(EyeFormData::text($prior['PHODVA'])); ?></td><td class="vaph_class"><?php echo text(EyeFormData::text($prior['PHOSVA'])); ?></td>
                                            <?php
                                }
                                if ($VA_ARODVA || $VA_AROSVA) { ?>
                                            <td class="vaar_class"><?php echo text(EyeFormData::text($prior['ARODVA'])); ?></td><td class="vaar_class"><?php echo text(EyeFormData::text($prior['AROSVA'])); ?></td>
                                            <?php
                                }
                                if ($VA_MRODVA || $VA_MROSVA) { ?>
                                            <td class="vamr_class"><?php echo text(EyeFormData::text($prior['MRODVA'])); ?></td><td class="vamr_class"><?php echo text(EyeFormData::text($prior['MROSVA'])); ?></td>
                                            <?php
                                }
                                if ($VA_CRODVA || $VA_CROSVA) { ?>
                                            <td class="vacr_class"><?php echo text(EyeFormData::text($prior['CRODVA'])); ?></td><td class="vacr_class"><?php echo text(EyeFormData::text($prior['CROSVA'])); ?></td>
                                            <?php
                                }
                                if ($VA_CTLODVA || $VA_CTLOSVA) { ?>
                                            <td class="vactl_class"><?php echo text(EyeFormData::text($prior['CTLODVA'])); ?></td><td class="vactl_class"><?php echo text(EyeFormData::text($prior['CTLOSVA'])); ?></td>
                                        <?php } ?>
                                </tr>

                                <?php
                        }
                    }
                    ?>
                </table>
        </div>
        <div class="right" style="position:relative;float:right;margin: 0px 5px 10px;text-align:center;width:55%;">
            <table class="top_right" style="width:40%;">
                <canvas id="canvas_VA"></canvas>
                <script>
                    /**
                     *  Below is the Chart.js code to render Va for each refraction type
                     *
                     */
                    var visit_date = '<?php echo attr($dated); ?>';
                    var dateFormat = 'YYYY-MM-DD';

                    var config_byVA = {
                        type: 'line',
                        data: {
                            labels: '<?php echo js_escape($VA_dates); ?>',
                            datasets: [
                                <?php
                                if (!empty($VA_SCODVA) || !empty($VA_SCOSVA)) { ?>
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "<?php echo xla("OD sc{{Visual Acuity without correction right eye}}"); ?>",
                                    data: <?php echo js_escape($VA_SCODVA); ?>,
                                    fill: false,
                                    borderColor : "#f28282",
                                    backgroundColor : "#f28282",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "#f28282",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                },
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "<?php echo xla("OS sc{{Visual Acuity without correction left eye}}"); ?>",
                                    data: <?php echo js_escape($VA_SCOSVA); ?>,
                                    fill: false,
                                    borderColor : "#AA8282",
                                    backgroundColor : "#AA8282",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "#AA8282",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                },
                                <?php  }
                                if (!empty($VA_CCODVA) || !empty($VA_CCOSVA)) {
                                    ?>
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "<?php echo xla("OD CC{{Visual Acuity with correction right eye}}"); ?>",
                                    data: <?php echo js_escape($VA_CCODVA); ?>,
                                    fill: false,
                                    borderColor : "red",
                                    backgroundColor : "red",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "red",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                },
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "<?php echo xla("OS CC{{Visual Acuity with correction left eye}}"); ?>",
                                    data: <?php echo js_escape($VA_CCOSVA); ?>,
                                    fill: false,
                                    borderColor : "blue",
                                    backgroundColor : "blue",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "blue",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                },
                                <?php  }
                                if (!empty($VA_MRODVA) || !empty($VA_MROSVA)) {
                                    ?>
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "<?php echo xla("OD MR{{Visual Acuity with Manifest refraction right eye}}"); ?>",
                                    data: <?php echo js_escape($VA_MRODVA); ?>,
                                    fill: false,
                                    borderColor : "navy",
                                    backgroundColor : "navy",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "navy",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                },
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "<?php echo xla("OS MR{{Visual Acuity with Manifest refraction left eye}}"); ?>",
                                    data: <?php echo js_escape($VA_MROSVA); ?>,
                                    fill: false,
                                    borderColor : "yellow",
                                    backgroundColor : "yellow",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "yellow",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                },
                                <?php  }
                                if (!empty($VA_CTLODVA) || !empty($VA_CTLOSVA)) {
                                    ?>
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "<?php echo xla("OD CTL{{Va with Contact Lens right eye}}"); ?>",
                                    data: <?php echo js_escape($VA_CTLODVA); ?>,
                                    fill: false,
                                    borderColor : "orange",
                                    backgroundColor : "orange",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "orange",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                },
                                {
                                    axis: 'y',
                                    type: 'line',
                                    label: "<?php echo xla("OS CTL{{Va with Contact Lens left eye}}"); ?>",
                                    data: <?php echo js_escape($VA_CTLOSVA); ?>,
                                    fill: false,
                                    borderColor : "purple",
                                    backgroundColor : "purple",
                                    pointBorderColor : "black",
                                    pointBackgroundColor : "purple",
                                    pointBorderWidth : 3,
                                    lineTension: 0.3,
                                    borderCapStyle: 'round',
                                    borderDash: [1,5],
                                    borderJoinStyle: 'miter',
                                    pointHoverRadius: 5,
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 3
                                }
                                    <?php
                                }
                                ?>
                            ]
                        },
                        options: {
                            plugins: {
                                title: {
                                    display: true,
                                    text: '<?php echo xla("Visual Acuities by Date"); ?>'
                                }
                            },
                            responsive: true,
                            tooltips: {
                                enabled: true
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: '<?php echo xla("Visit Dates"); ?>',
                                        font: {
                                            weight: 'bold' // Set the font weight to bold
                                        }
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: '<?php echo xla("Visual Acuity"); ?>',
                                        font: {
                                            weight: 'bold' // Set the font weight to bold
                                        }
                                    },
                                    beginAtZero: true,
                                    Min: 0,
                                    suggestedMax: 50
                                }
                            }
                        }
                    };

                    var ctx3 = document.getElementById("canvas_VA").getContext("2d");

                    var myLine = new Chart(ctx3, config_byVA);
                    // Apply the provider's stored exclusions and auto cross-out datasets
                    // that have no values documented at all.
                    var vahx_excluded = (($('#PREFS_VAHX_EXCLUDE').val()) || '').split(',').filter(function (s) { return s !== ''; });
                    config_byVA.data.datasets.forEach(function (ds, i) {
                        var hasData = (ds.data || []).some(function (v) {
                            return (v !== '' && v !== null && typeof v !== 'undefined');
                        });
                        if (!hasData || vahx_excluded.indexOf(ds.label) !== -1) {
                            myLine.setDatasetVisibility(i, false);
                        }
                    });
                    myLine.update();

                </script>
                <?php
    } else {
        echo "<div style='text-align:left;padding-left:20px;'><span>The Visual Acuity graphically displays:
                            <ul>
                            <li> Va measurements</li>

                            </ul>
                            Visual Acuity graphs are not generated on the initial visit...</span></div>";
    } ?>
            </table>
        </div>
    <?php
    return ob_get_clean();
}


# gets the provider from the encounter file , or from the logged on user or from the patient file
/** Resolve the encounter provider using calendar and patient-provider fallbacks. */
function findProvider($pid, $encounter)
{
    $find_provider = QueryUtils::querySingleRow("SELECT * FROM form_encounter " .
        "WHERE pid = ? AND encounter = ? " .
        "ORDER BY id DESC LIMIT 1", [$pid,$encounter]);
    $providerid = $find_provider['provider_id'] ?? '';
    if ($providerid < '1') {
       //find the default providerID from the calendar
        $visit_date = date('Y-m-d', strtotime($find_provider['date'] ?? ''));
        $query = "select * from openemr_postcalendar_events where pc_pid=? and pc_eventDate=?";
        $find_provider3 = QueryUtils::querySingleRow($query, [$pid,$visit_date]);
        $new_providerid = $find_provider3['pc_aid'] ?? '';
        if (($new_providerid < '1') || (!$new_providerid)) {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $get_authorized = $session->get('userauthorized');
            if ($get_authorized == 1) {
                $find_provider2 = QueryUtils::querySingleRow("SELECT providerID FROM patient_data WHERE pid = ? ", [$pid]);
                $new_providerid = $find_provider2['providerID'] ?? '';
            }
        }

        $providerid = $new_providerid;
        sqlStatement("UPDATE form_encounter set provider_id =? WHERE pid = ? AND encounter = ?", [$providerid,$pid,$encounter]);
        sqlStatement("UPDATE patient_data set providerID =? WHERE pid = ?", [$providerid,$pid]);
    }

    return $providerid;
}

/** Return active lens-treatment checkboxes with the current prescription's selections. */
function generate_lens_treatments(mixed $W, mixed $LTs_present): string
{
    $W = EyeFormData::text($W);
    ob_start();
    $query = "SELECT * FROM list_options where list_id =? and activity='1' ORDER BY seq";
    $TXs_data = sqlStatement($query, ["Eye_Lens_Treatments"]);
    $counter = 0;
    $TXs_arr = explode("|", EyeFormData::text($LTs_present));
    $tabindex = $W . "0144";
    while ($row = sqlFetchArray($TXs_data)) {
        $checked = '';
        $ID = $row['option_id'];
        if (in_array($ID, $TXs_arr)) {
            $checked = "checked='yes'";
        }

        echo "<div class='treat-row'><input type='checkbox' id='TXs_" . $W . "_" . $counter . "' name='LENS_TREATMENTS_" . $W . "[]' $checked value='" . attr($ID) . "' tabindex='$tabindex'> ";
        $label = text(substr(EyeFormData::text($row['title']), 0, 30));
        echo "<label for='TXs_" . $W . "_" . $counter . "' class='input-helper input-helper--checkbox' title='" . attr($row['notes']) . "'>";
        echo $label . "</label></div>";
        $counter++;
        $tabindex++;
    }

    $output = ob_get_contents();
    ob_end_clean();
    return $output ?: '';
}


/**
 *  Function to display the fields for a currently worn glasses/spectacle Rx.
 *  @param $W - the Rx number, in order of documentation
 */
function generate_specRx($W)
{
    global $pid,$form_id,$encounter,$display_W_width;
    $W = EyeFormData::text($W);

    $query  = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? and ENCOUNTER=? and RX_NUMBER =?";
    $wear = EyeFormData::textRow(EyeFormData::row(QueryUtils::querySingleRow($query, [$pid,$form_id,$encounter,$W])));
    if ($wear) {
        $RX_VALUE = '1';
        extract($wear, EXTR_SKIP);
        $ODPRISM = trim(EyeFormData::text($wear['ODPRISM'] ?? '')) ?: trim(trim((EyeFormData::text($wear['ODHPD'] ?? '')) . ' ' . (EyeFormData::text($wear['ODHBASE'] ?? ''))) . ' ' . trim((EyeFormData::text($wear['ODVPD'] ?? '')) . ' ' . (EyeFormData::text($wear['ODVBASE'] ?? ''))));
        $OSPRISM = trim(EyeFormData::text($wear['OSPRISM'] ?? '')) ?: trim(trim((EyeFormData::text($wear['OSHPD'] ?? '')) . ' ' . (EyeFormData::text($wear['OSHBASE'] ?? ''))) . ' ' . trim((EyeFormData::text($wear['OSVPD'] ?? '')) . ' ' . (EyeFormData::text($wear['OSVBASE'] ?? ''))));
    } else {
        $RX_VALUE = '';
        $display_W = 'nodisplay';
        $ODPRISM = '';
        $OSPRISM = '';
    }

    $ODNEARVA = $wear['ODNEARVA'] ?? '';
    $OSNEARVA = $wear['OSNEARVA'] ?? '';
    $wearDetails = [
        'ODPRISM' => $ODPRISM, 'OSPRISM' => $OSPRISM,
        'ODHPD' => EyeFormData::text($wear['ODHPD'] ?? ''), 'ODHBASE' => EyeFormData::text($wear['ODHBASE'] ?? ''), 'ODVPD' => EyeFormData::text($wear['ODVPD'] ?? ''), 'ODVBASE' => EyeFormData::text($wear['ODVBASE'] ?? ''),
        'OSHPD' => EyeFormData::text($wear['OSHPD'] ?? ''), 'OSHBASE' => EyeFormData::text($wear['OSHBASE'] ?? ''), 'OSVPD' => EyeFormData::text($wear['OSVPD'] ?? ''), 'OSVBASE' => EyeFormData::text($wear['OSVBASE'] ?? ''),
        'ODMPDD' => $ODMPDD ?? '', 'ODMPDN' => $ODMPDN ?? '', 'OSMPDD' => $OSMPDD ?? '', 'OSMPDN' => $OSMPDN ?? '',
        'BPDD' => $BPDD ?? '', 'BPDN' => $BPDN ?? '',
        'ODVERTEXDIST' => $ODVERTEXDIST ?? '', 'OSVERTEXDIST' => $OSVERTEXDIST ?? '',
        'ODSLABOFF' => $ODSLABOFF ?? '', 'OSSLABOFF' => $OSSLABOFF ?? '',
        'RX_TYPE' => $RX_TYPE ?? '', 'LENS_MATERIAL' => $LENS_MATERIAL ?? '', 'LENS_TREATMENTS' => $LENS_TREATMENTS ?? '',
    ];

    ob_start();
    ?>
    <input type="hidden" id="W_<?php echo attr($W); ?>" name="W_<?php echo attr($W); ?>" value="<?php echo attr($RX_VALUE); ?>">

    <div id="LayerVision_W_<?php echo attr($W); ?>" name="currentRX" class="refraction current_W borderShadow <?php echo attr($display_W ?? ''); ?> <?php echo $display_W_width; ?>">
                      <i class="closeButton fas fa-times" id="Close_W_<?php echo attr($W); ?>" name="Close_W_<?php echo attr($W); ?>"
                        title="<?php echo xla('Close this panel and delete this Rx'); ?>"></i>
                      <i class="closeButton_2 fas fa-arrows-alt-h" id="W_width_display_<?php echo attr($W); ?>" name="W_width_display"
                        title="<?php echo xla("Rx Details"); ?>" ></i>
                      <i onclick="top.restoreSession();  doscript('W','<?php echo attr($pid); ?>','<?php echo attr($encounter); ?>','<?php echo attr($W); ?>'); return false;"
                       title="<?php echo xla("Dispense Rx"); ?>" class="closeButton_3 fa fa-print"></i>
                      <i onclick="top.restoreSession();  dispensed('<?php echo attr($pid); ?>');return false;"
                         title="<?php echo xla("List of previously dispensed Spectacle and Contact Lens Rxs"); ?>" class="closeButton_4 fa fa-list-ul"></i>
                      <table id="wearing_<?php echo attr($W); ?>" class="wear-rx-table">
                        <?php display_wear_colgroup(); ?>
                        <tr>
                          <th colspan="10" class="refraction-panel-title wear-panel-heading">
                            <div class="wear-header-bar">
                              <span class="wear-header-title"><?php echo xlt('Current Glasses'); ?>: #<?php echo attr($W); ?></span>
                              <span id="W_source_label_<?php echo attr($W); ?>" class="wear-source-label badge badge-info"></span>
                            </div>
                          </th>
                        </tr>
                        <tr>
                          <td><i class="fa fa-gamepad" name="reverseme" title="<?php echo xla('Convert between plus and minus cylinder'); ?>"aria-hidden="true" id="revW<?php echo attr($W); ?>" ></i></td>
                          <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                          <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                          <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                          <td><?php echo xlt('Acuity'); ?></td>
                          <?php display_wear_header_cells($W, $wearDetails); ?>
                        </tr>
                        <tr>
                          <td style="font-weight:bold;"><?php echo xlt('OD{{right eye}}'); ?>:</td>
                          <td><?php if ((EyeFormData::text(${"ODSPH_$W"} ?? '') !== '')) {
                                echo ${"ODSPH_$W"};} ?><input type="text" class="sphere" id="ODSPH_<?php echo attr($W); ?>" name="ODSPH_<?php echo attr($W); ?>"  value="<?php echo attr($ODSPH ?? ''); ?>" tabindex="<?php echo attr($W); ?>0100"></td>
                          <td><input type="text" class="cylinder" id="ODCYL_<?php echo attr($W); ?>" name="ODCYL_<?php echo attr($W); ?>"  value="<?php echo attr($ODCYL ?? ''); ?>" tabindex="<?php echo attr($W); ?>0101"></td>
                          <td><input type="text" class="axis" id="ODAXIS_<?php echo attr($W); ?>" name="ODAXIS_<?php echo attr($W); ?>" value="<?php echo attr($ODAXIS ?? ''); ?>" tabindex="<?php echo attr($W); ?>0102"></td>
                          <td><input type="text" class="acuity" id="ODVA_<?php echo attr($W); ?>" name="ODVA_<?php echo attr($W); ?>" value="<?php echo attr($ODVA ?? ''); ?>" tabindex="<?php echo attr($W); ?>0108"></td>
                          <?php display_wear_distance_cells($W, 'OD', $wearDetails); ?>
                        </tr>
                        <tr>
                          <td style="font-weight:bold;"><?php echo xlt('OS{{left eye}}'); ?>:</td>
                          <td><input type="text" class="sphere" id="OSSPH_<?php echo attr($W); ?>" name="OSSPH_<?php echo attr($W); ?>" value="<?php echo attr($OSSPH ?? ''); ?>" tabindex="<?php echo attr($W); ?>0103"></td>
                          <td><input type="text" class="cylinder" id="OSCYL_<?php echo attr($W); ?>" name="OSCYL_<?php echo attr($W); ?>" value="<?php echo attr($OSCYL ?? ''); ?>" tabindex="<?php echo attr($W); ?>0104"></td>
                          <td><input type="text" class="axis" id="OSAXIS_<?php echo attr($W); ?>" name="OSAXIS_<?php echo attr($W); ?>" value="<?php echo attr($OSAXIS ?? ''); ?>" tabindex="<?php echo attr($W); ?>0105"></td>
                          <td><input type="text" class="acuity" id="OSVA_<?php echo attr($W); ?>" name="OSVA_<?php echo attr($W); ?>" value="<?php echo attr($OSVA ?? ''); ?>" tabindex="<?php echo attr($W); ?>0109"></td>
                          <?php display_wear_distance_cells($W, 'OS', $wearDetails); ?>
                        </tr>
                        <tr class="WNEAR">
                          <td style="font-weight:bold;"><?php echo xlt('OD{{right eye}}'); ?>:</td>
                            <?php echo '<input type="hidden" name="RXStart_' . $W . ' id="RXStart_' . $W . '" value="' . attr($RX_TYPE ?? '') . '">'; ?>
                          <td class="WMid"><input type="text" class="presbyopia" id="ODMIDADD_<?php echo attr($W); ?>" name="ODMIDADD_<?php echo attr($W); ?>" value="<?php echo attr($ODMIDADD ?? ''); ?>"></td>
                          <td class="WAdd2"><input type="text" class="presbyopia" id="ODADD_<?php echo attr($W); ?>" name="ODADD_<?php echo attr($W); ?>" value="<?php echo attr($ODADD ?? ''); ?>" tabindex="<?php echo attr($W); ?>0106"></td>
                          <td></td>
                          <td><input class="jaeger" type="text" id="ODNEARVA_<?php echo attr($W); ?>" name="ODNEARVA_<?php echo attr($W); ?>" value="<?php echo attr($ODNEARVA); ?>" tabindex="<?php echo attr($W); ?>0110"></td>
                          <?php display_wear_lower_cells($W, 'OD', $wearDetails); ?>
                        </tr>
                        <tr class="WNEAR">
                          <td style="font-weight:bold;"><?php echo xlt('OS{{left eye}}'); ?>:</td>
                          <td class="WMid"><input type="text" class="presbyopia" id="OSMIDADD_<?php echo attr($W); ?>" name="OSMIDADD_<?php echo attr($W); ?>" value="<?php echo attr($OSMIDADD ?? ''); ?>"></td>
                          <td class="WAdd2"><input type="text" class="presbyopia" id="OSADD_<?php echo attr($W); ?>" name="OSADD_<?php echo attr($W); ?>" value="<?php echo attr($OSADD ?? ''); ?>" tabindex="<?php echo attr($W); ?>0107"></td>
                          <td></td>
                          <td><input class="jaeger" type="text" id="OSNEARVA_<?php echo attr($W); ?>" name="OSNEARVA_<?php echo attr($W); ?>" value="<?php echo attr($OSNEARVA); ?>" tabindex="<?php echo attr($W); ?>0111"></td>
                          <?php display_wear_lower_cells($W, 'OS', $wearDetails); ?>
                        </tr>
                        <tr>
                          <td class="right bold current-glasses-comments-label"><?php echo xlt('Comments'); ?>:</td>
                          <td colspan="4" class="current-glasses-comments-cell">
                            <textarea id="COMMENTS_<?php echo attr($W); ?>" name="COMMENTS_<?php echo attr($W); ?>" class="current-glasses-comments" tabindex="<?php echo attr($W); ?>0110"><?php echo text($COMMENTS ?? ''); ?></textarea>
                          </td>
                          <?php display_wear_comment_tail_cells(); ?>
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
function display_refractive_data($encounter_data): void
{
    @extract($encounter_data);
    $ARNEARODVA ??= null; $ARNEAROSVA ??= null; $ARODADD ??= null; $ARODAXIS ??= null; $ARODCYL ??= null;
    $ARODPRISM ??= null; $ARODSPH ??= null; $ARODVA ??= null; $AROSADD ??= null; $AROSAXIS ??= null;
    $AROSCYL ??= null; $AROSPRISM ??= null; $AROSSPH ??= null; $AROSVA ??= null; $CONTRASTODVA ??= null;
    $CRNEARODVA ??= null; $CRNEAROSVA ??= null; $CRODADD ??= null; $CRODAXIS ??= null; $CRODCYL ??= null;
    $CRODPRISM ??= null; $CRODSPH ??= null; $CRODVA ??= null; $CROSADD ??= null; $CROSAXIS ??= null;
    $CROSCYL ??= null; $CROSPRISM ??= null; $CROSSPH ??= null; $CROSVA ??= null; $CTLBRANDOD ??= null;
    $CTLBRANDOS ??= null; $CTLMANUFACTUREROD ??= null; $CTLMANUFACTUREROS ??= null; $CTLODADD ??= null;
    $CTLODAXIS ??= null; $CTLODBC ??= null; $CTLODCYL ??= null; $CTLODDIAM ??= null; $CTLODSPH ??= null;
    $CTLODVA ??= null; $CTLOSADD ??= null; $CTLOSAXIS ??= null; $CTLOSBC ??= null; $CTLOSCYL ??= null;
    $CTLOSDIAM ??= null; $CTLOSSPH ??= null; $CTLOSVA ??= null; $CTLSUPPLIEROD ??= null;
    $CTLSUPPLIEROS ??= null; $GLAREODVA ??= null; $GLAREOSVA ??= null; $LIODVA ??= null; $LIOSVA ??= null;
    $MRNEARODVA ??= null; $MRNEAROSVA ??= null; $MRODADD ??= null; $MRODAXIS ??= null; $MRODCYL ??= null;
    $MRODPRISM ??= null; $MRODSPH ??= null; $MRODVA ??= null; $MROSADD ??= null; $MROSAXIS ??= null;
    $MROSCYL ??= null; $MROSPRISM ??= null; $MROSSPH ??= null; $MROSVA ??= null; $ODACD ??= null;
    $ODAXIALLENGTH ??= null; $ODECL ??= null; $ODK1 ??= null; $ODK2 ??= null; $ODK2AXIS ??= null;
    $ODLT ??= null; $ODPDMeasured ??= null; $ODVA ??= null; $ODW2W ??= null; $OSACD ??= null;
    $OSAXIALLENGTH ??= null; $OSECL ??= null; $OSK1 ??= null; $OSK2 ??= null; $OSK2AXIS ??= null;
    $OSLT ??= null; $OSPDMeasured ??= null; $OSVA ??= null; $OSW2W ??= null; $PAMODBA ??= null;
    $PAMODVA ??= null; $PAMOSVA ??= null; $PHODVA ??= null; $PHOSVA ??= null; $RX_TYPE ??= null;
    $VABINOC ??= null; $date ??= null; $display_Add ??= null; $i ??= null; $id ??= null; $pend ??= null;
    $pid ??= null;
    $count_rx = 0;
    $wearValues = [];

    $query = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? ORDER BY RX_NUMBER";

    $wear = sqlStatement($query, [$pid,$id]);
    while ($wearing = sqlFetchArray($wear)) {
        $wearing = EyeFormData::textRow(EyeFormData::row($wearing));
        $count_rx++;
        $wearValues[$count_rx]["display_W"] = '';
        $wearValues[$count_rx]["ODSPH"] = $wearing['ODSPH'];
        $wearValues[$count_rx]["ODCYL"] = $wearing['ODCYL'];
        $wearValues[$count_rx]["ODAXIS"] = $wearing['ODAXIS'];
        $wearValues[$count_rx]["OSSPH"] = $wearing['OSSPH'];
        $wearValues[$count_rx]["OSCYL"] = $wearing['OSCYL'];
        $wearValues[$count_rx]["OSAXIS"] = $wearing['OSAXIS'];
        $wearValues[$count_rx]["ODMIDADD"] = $wearing['ODMIDADD'];
        $wearValues[$count_rx]["OSMIDADD"] = $wearing['OSMIDADD'];
        $wearValues[$count_rx]["ODADD"] = $wearing['ODADD'];
        $wearValues[$count_rx]["OSADD"] = $wearing['OSADD'];
        $wearValues[$count_rx]["ODVA"] = $wearing['ODVA'];
        $wearValues[$count_rx]["OSVA"] = $wearing['OSVA'];
        $wearValues[$count_rx]["ODNEARVA"] = $wearing['ODNEARVA'];
        $wearValues[$count_rx]["OSNEARVA"] = $wearing['OSNEARVA'];
        $wearValues[$count_rx]["ODPRISM"] = trim((string)($wearing['ODPRISM'] ?? '')) ?: trim(trim(($wearing['ODHPD'] ?? '') . ' ' . ($wearing['ODHBASE'] ?? '')) . ' ' . trim(($wearing['ODVPD'] ?? '') . ' ' . ($wearing['ODVBASE'] ?? '')));
        $wearValues[$count_rx]["OSPRISM"] = trim((string)($wearing['OSPRISM'] ?? '')) ?: trim(trim(($wearing['OSHPD'] ?? '') . ' ' . ($wearing['OSHBASE'] ?? '')) . ' ' . trim(($wearing['OSVPD'] ?? '') . ' ' . ($wearing['OSVBASE'] ?? '')));
        $wearValues[$count_rx]["COMMENTS"] = $wearing['COMMENTS'];
        $wearValues[$count_rx]["W"] = '1';
        $wearValues[$count_rx]["RX_TYPE"] = $wearing['RX_TYPE'];
    }

    $encounter_data = EyeFormData::row($encounter_data);
    /** Detect documented refraction fields while ignoring blank and placeholder-only values. */
    $sectionHasData = static function (string $prefix) use ($encounter_data): bool {
        foreach ($encounter_data as $field => $value) {
            $normalized = trim(EyeFormData::text($value));
            if (
                str_starts_with(EyeFormData::text($field), $prefix)
                && $normalized !== ''
                && !in_array($normalized, ['-', '–', '—'], true)
            ) {
                return true;
            }
        }
        return false;
    };

    if ($sectionHasData('AR') || $sectionHasData('MR') || $sectionHasData('CR') || $sectionHasData('CTL') || $count_rx > 0) { ?>
        <table class="refraction_tables">
           <tr class="text-center bold underline" style="background-color: #F3EEC7;">
                <td ><?php echo oeFormatShortDate($date); ?></td>
                <td ><?php echo xlt('Eye'); ?></td>
                <td ><?php echo xlt('Sph{{Sphere}}'); ?></td>
                <td ><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                <td ><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                <td ><?php echo xlt('Prisms'); ?></td>
                <td ><?php echo xlt('Acuity'); ?></td>
                <td ><?php echo xlt('Mid{{Middle Distance Add}}'); ?></td>
                <td ><?php echo xlt('ADD{{Near Add}}'); ?></td>
                <td ><?php echo xlt('Acuity'); ?></td>
            </tr>
            <?php
               //$count_rx++;
            for ($i = 1; $i <= $count_rx; $i++) {
                if ($wearValues[$i]["RX_TYPE"] == "0") {
                    $RX_TYPE = '';
                } elseif ($wearValues[$i]["RX_TYPE"] == "1") {
                    $RX_TYPE = xlt('Bifocals');
                } elseif ($wearValues[$i]["RX_TYPE"] == "2") {
                    $RX_TYPE = xlt('Trifocals');
                } elseif ($wearValues[$i]["RX_TYPE"] == "3") {
                    $RX_TYPE = xlt('Progressive');
                }
                ?>
                <tr>
                    <td class="bold"><?php echo xlt('Wear RX') . " #" . $i . ": "; ?></td>
                    <td class="bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                    <td ><?php echo (text($wearValues[$i]["ODSPH"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["ODCYL"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["ODAXIS"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["ODPRISM"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["ODVA"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["ODMIDADD"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["ODADD"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["ODNEARVA"]) ?: "-"); ?></td>
                   </tr>
                   <tr>
                    <td><?php echo $RX_TYPE; ?></td>
                    <td class="bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                    <td ><?php echo (text($wearValues[$i]["OSSPH"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["OSCYL"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["OSAXIS"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["OSPRISM"]) ?: "-");  ?></td>
                    <td ><?php echo (text($wearValues[$i]["OSVA"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["OSMIDADD"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["OSADD"]) ?: "-"); ?></td>
                    <td ><?php echo (text($wearValues[$i]["OSNEARVA"]) ?: "-"); ?></td>
                   </tr>
                    <?php
                    if (($wearValues[EyeFormData::integer($i)]['COMMENTS'] ?? '')) {
                        ?>
                        <tr>
                           <td></td>
                           <td colspan="2"><?php echo xlt('Comments'); ?>:</td>
                           <td colspan="7"><?php echo text($wearValues[EyeFormData::integer($i)]['COMMENTS']); ?></td>
                        </tr>
                        <?php
                    }
                    ?><tr><td colspan="10">--------------------------------------------------------</td></tr>
                    <?php
            }

            if ($sectionHasData('AR')) { ?>
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
                    if (($wearValues[EyeFormData::integer($i)]['COMMENTS'] ?? '')) {
                        ?>
                        <tr>
                           <td></td><td></td>
                           <td>Comments:</td>
                           <td colspan="7"><?php echo text($wearValues[EyeFormData::integer($i)]['COMMENTS']); ?></td>
                        </tr>
                        <?php
                    }?>
                   <tr><td colspan="10">--------------------------------------------------------</td></tr>
                    <?php
            }

            if ($sectionHasData('MR')) { ?>
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

            if ($sectionHasData('CR')) { ?>
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

            if ($sectionHasData('CTL')) { ?>
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
              <!--  <td><input type=text id="pend" name="pend" value="<?php echo attr($pend); ?>"></td> -->
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
 * @return bool
 */
function in_array_r($needle, $haystack, $strict = false): bool
{
    if (!is_array($haystack) || $haystack === []) {
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
function getIOPTARGETS($pid, $id, $provider_id): array
{
    $ODIOPTARGET = '';
    $OSIOPTARGET = '';
    //iterate through this patient's encounters to find IOPTARGETS.
    //if none use provider's default value, or 21.
    //If a practice is loading old visits into OpenEMR, the visit date and id will not correlate linearly.
    //We cannot rely on visit id to represent visit order.  We need to use visit dates to make this work.

    $query = "SELECT ODIOPTARGET, OSIOPTARGET from form_eye_vitals join form_encounter on form_encounter.id=form_eye_vitals.id
                where form_eye_vitals.pid=? and form_encounter.date <= (
                    SELECT date from form_encounter where id = ?
                    )
                ORDER BY form_encounter.date DESC";
    $result = sqlStatement($query, [$pid, $id]);
    while ($row = sqlFetchArray($result)) {
        if (($row['ODIOPTARGET'] > '0') && ($row['OSIOPTARGET'] > '0')) {
            return [$row['ODIOPTARGET'], $row['OSIOPTARGET']];
        }
    }
    $query = "SELECT * FROM `list_options`
            WHERE
            `list_id` LIKE ? AND
            (   option_id = 'ODIOPTARGET' OR
                option_id = 'OSIOPTARGET'  )
             ";
    $result = sqlQuery($query, ["Eye_defaults_" . $provider_id]);
    while ($default_TARGETS = sqlFetchArray($result)) {
        if ($default_TARGETS['option_id'] == 'ODIOPTARGET') {
            $ODIOPTARGET = $default_TARGETS["title"];
        }
        if ($default_TARGETS['option_id'] == 'OSIOPTARGET') {
            $OSIOPTARGET = $default_TARGETS["title"];
        }
    }
    if ((($ODIOPTARGET ?? null) > '0') || (($OSIOPTARGET ?? null) > '0')) {
        return [$ODIOPTARGET, $OSIOPTARGET];
    }
    return ['21','21'];
}

?>
