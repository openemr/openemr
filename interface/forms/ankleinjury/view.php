<?php

/**
 * ankleinjury view.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    cfapress <cfapress>
 * @author    Robert Down <robertdown@live.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2004 Nikolai Vitsyn
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

?>
<html><head>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
<?php
require_once("$srcdir/api.inc");
$obj = formFetch("form_ankleinjury", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/ankleinjury/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<span class="title"><?php echo xlt('Ankle Evaluation Form'); ?></span><br /><br />

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save Changes'); ?>]</a>
<br /><br />

<span class=text>Date of Injury: </span><input type="text" name="ankle_date_of_injuary" value="<?php echo attr($obj["ankle_date_of_injuary"]); ?>" >
<td align="right"><?php echo xlt('Work related?'); ?>:</td>
<td><input type=checkbox name="ankle_work_related" <?php if ($obj["ankle_work_related"] == "on") {
    echo "checked";
                                                   }

                                                   ;?>><span class=text></span><br /></td>

<table >
<tr>
<td align="right"><?php echo xlt('Foot:'); ?></td>
<td><input type=radio name="ankle_foot" value="Left" <?php if ($obj["ankle_foot"] == "Left") {
    echo "checked";
                                                     };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type=radio name="ankle_foot" value="Right" <?php if ($obj["ankle_foot"] == "Right") {
    echo "checked";
                                                      };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('Right:'); ?></td>
</tr>
</table>

<table >
<tr>
<td align="right"><?php echo xlt('Severity of Pain:'); ?></td>
<td align="right">1:</td>
<td><input type=radio name="ankle_severity_of_pain" value="1" <?php if ($obj["ankle_severity_of_pain"] == "1") {
    echo "checked";
                                                              };?>><span class=text></span><br /></td>

<td align="right">2:</td>
<td><input type=radio name="ankle_severity_of_pain" value="2" <?php if ($obj["ankle_severity_of_pain"] == "2") {
    echo "checked";
                                                              };?>><span class=text></span><br /></td>

<td align="right">3:</td>
<td><input type=radio name="ankle_severity_of_pain" value="3" <?php if ($obj["ankle_severity_of_pain"] == "3") {
    echo "checked";
                                                              };?>><span class=text></span><br /></td>
</tr>
</table>

<table><tr>
<td align="right"><?php echo xlt('Significant Swelling:'); ?></td>
<td><input type=checkbox name="ankle_significant_swelling" <?php if ($obj["ankle_significant_swelling"] == "on") {
    echo "checked";
                                                           };?>><span class=text></span><br />
</tr>
</table>


<table >
<tr>
<td align="right"><?php echo xlt('Onset of Swelling:'); ?></td>
<td><input type=radio name="ankle_onset_of_swelling" value="within minutes" <?php if ($obj["ankle_onset_of_swelling"] == "within minutes") {
    echo "checked";
                                                                            };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('within minutes:'); ?></td>
<td><input type=radio name="ankle_onset_of_swelling" value="within hours" <?php if ($obj["ankle_onset_of_swelling"] == "within hours") {
    echo "checked";
                                                                          };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('within hours:');?></td>
</tr>
</table>

<span class="text"><?php echo xlt('How did Injury Occur?:'); ?></span><br />
<textarea name="ankle_how_did_injury_occur" cols ="67" rows="4"  wrap="virtual name">
<?php echo text($obj["ankle_how_did_injury_occur"]); ?></textarea>
<br />

<table><th colspan="5"><?php echo xlt('Ottawa Ankle Rules'); ?></th>
<tr>
<td align="right"><?php echo xlt('Bone Tenderness:'); ?></td>
<td align="right"><?php echo xlt('Medial malleolus:'); ?></td>
<td><input type=radio name="ankle_ottawa_bone_tenderness" value="Medial malleolus" <?php if ($obj["ankle_ottawa_bone_tenderness"] == "Medial malleolus") {
    echo "checked";
                                                                                   };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('Lateral malleolus:'); ?></td>
<td><input type=radio name="ankle_ottawa_bone_tenderness"  value="Lateral malleolus" <?php if ($obj["ankle_ottawa_bone_tenderness"] == "Lateral malleolus") {
    echo "checked";
                                                                                     };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('Base of fifth (5th) Metarsal:'); ?></td>
<td><input type=radio name="ankle_ottawa_bone_tenderness" value="Base of fifth (5th) Metarsal" <?php if ($obj["ankle_ottawa_bone_tenderness"] == "Base of fifth (5th) Metarsal") {
    echo "checked";
                                                                                               };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('At the Navicular:'); ?></td>
<td><input type=radio name="ankle_ottawa_bone_tenderness" value="At the Navicular" <?php if ($obj["ankle_ottawa_bone_tenderness"] == "At the Navicular") {
    echo "checked";
                                                                                   };?>><span class=text></span><br /></td>
</tr>
</table>

<table >
<tr>
<td align="right"><?php echo xlt('Able to Bear Weight four (4) steps:'); ?></td>
<td align="right"><?php echo xlt('Yes:'); ?></td>
<td><input type=radio name="ankle_able_to_bear_weight_steps" value="Yes" <?php if ($obj["ankle_able_to_bear_weight_steps"] == "Yes") {
    echo "checked";
                                                                         };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('No:'); ?></td>
<td><input type=radio name="ankle_able_to_bear_weight_steps" value="No" <?php if ($obj["ankle_able_to_bear_weight_steps"] == "No") {
    echo "checked";
                                                                        };?>><span class=text></span><br /></td>
</tr>
</table>

<table>
<tr><th><?php echo xlt('X-Ray Interpretation:'); ?></th> <th><?php echo xlt('Additional X-RAY Notes:'); ?></th></tr>
<tr>
<td>
<input type="text" name="ankle_x_ray_interpretation" value="<?php echo
attr($obj["ankle_x_ray_interpretation"]); ?>" size="50">
</td>
<td rowspan=2>
<textarea name="ankle_additional_x_ray_notes" cols ="30" rows="1" wrap="virtual name">
<?php echo attr($obj["ankle_additional_x_ray_notes"]); ?></textarea>
<td>
</tr>
</table>

<table>
<tr><th><?php echo xlt('Diagnosis:'); ?></th><th><?php echo xlt('Additional Diagnosis:'); ?></th></tr>
<tr>
<td><input type="text" name="ankle_diagnosis1" value="<?php echo
attr($obj["ankle_diagnosis1"]); ?>" size="50">
</td>
<td rowspan=2>
<textarea name="ankle_additional_diagnisis" rows="2" cols="30" wrap="virtual name">
<?php echo attr($obj["ankle_additional_diagnisis"]); ?></textarea>
</td>

<tr>
<td><input type="text" name="ankle_diagnosis2" value="<?php echo
attr($obj["ankle_diagnosis2"]); ?>" size="50"></td>
</tr>
<td><input type="text" name="ankle_diagnosis3" value="<?php echo
attr($obj["ankle_diagnosis3"]); ?>" size="50"></td>
</tr>
<td><input type="text" name="ankle_diagnosis4" value="<?php echo
attr($obj["ankle_diagnosis4"]); ?>" size="50"></td>
</tr>
</table>

<table><tr><th><?php echo xlt('Plan:'); ?></th><tr>
<tr><td>
<textarea name="ankle_plan" rows="4" cols="67" wrap="virtual name">
<?php echo text($obj["ankle_plan"]); ?></textarea>
</td></tr></table>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save Changes'); ?>]</a>
</form>
<?php
formFooter();
?>
