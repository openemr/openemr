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
 * @copyright Copyright (c) 2017-2023 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
?>
<html><head>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
<?php
require_once("$srcdir/api.inc.php");
$obj = formFetch("form_bronchitis", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/bronchitis/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<span class="title"><?php echo xlt('Bronchitis Form'); ?></span><br /><br />

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save Changes'); ?>]</a>
<br /><br />

<span class=text><?php echo xlt('Onset of Ilness: '); ?></span><input type="text" name="bronchitis_date_of_illness" value="<?php echo attr($obj["bronchitis_date_of_illness"]);?>" ><br /><br />

<span class=text><?php echo xlt('HPI:'); ?> </span><br /><textarea cols=67 rows=8 wrap=virtual name="bronchitis_hpi" ><?php echo text($obj["bronchitis_hpi"]);?></textarea><br /><br />


<table><th colspan="5">"<?php echo xlt('Other Pertinent Symptoms'); ?> ":</th>
<tr>
<td width="60" align="right"><?php echo xlt('Fever:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_fever" <?php if ($obj["bronchitis_ops_fever"] == "on") {
    echo "checked";
                                                     };?>><span class=text></span><br />

<td width="140" align="right"><?php echo xlt('Cough:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_cough" <?php if ($obj["bronchitis_ops_cough"] == "on") {
    echo "checked";
                                                     };?>><span class=text></span><br />

<td width="170" align="right"><?php echo xlt('Dizziness:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_dizziness" <?php if ($obj["bronchitis_ops_dizziness"] == "on") {
    echo "checked";
                                                         };?>><span class=text></span><br />
</tr>

<tr>
<td width="60" align="right"><?php echo xlt('Chest Pain:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_chest_pain" <?php if ($obj["bronchitis_ops_chest_pain"] == "on") {
    echo "checked";
                                                          };?>><span class=text></span><br />
<td width="130" align="right"><?php echo xlt('Dyspnea:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_dyspnea" <?php if ($obj["bronchitis_ops_dyspnea"] == "on") {
    echo "checked";
                                                       };?>><span class=text></span><br />
<td width="180" align="right"><?php echo xlt('Sweating:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_sweating" <?php if ($obj["bronchitis_ops_sweating"] == "on") {
    echo "checked";
                                                        };?>><span class=text></span><br />
</tr>

<tr>
<td width="60" align="right"><?php echo xlt('Wheezing:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_wheezing" <?php if ($obj["bronchitis_ops_wheezing"] == "on") {
    echo "checked";
                                                        };?>><span class=text></span><br />

<td width="130" align="right"><?php echo xlt('Malaise:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_malaise" <?php if ($obj["bronchitis_ops_malaise"] == "on") {
    echo "checked";
                                                       };?>><span class=text></span><br />
</tr>

<tr>
<td width="60" align="right"><?php echo xlt('Sputum:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_sputum" <?php if ($obj["bronchitis_ops_sputum"] == "on") {
    echo "checked";
                                                      };?>><span class=text></span><br /></td>

<td width="130" align="right"><?php echo xlt('Appearance:'); ?>  <span class="text"></span></td>
<td><input type="text" name="bronchitis_ops_appearance" value="<?php echo
stripslashes($obj["bronchitis_ops_appearance"]);?>" size="15"></td>
</tr>
</table>

<table><tr>
<td width="227" align="right"><?php echo xlt('All Reviewed and Negative:'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_all_reviewed" <?php if ($obj["bronchitis_ops_all_reviewed"] == "on") {
    echo "checked";
                                                            };?>><span class=text></span><br />
</tr>
</table>
<br /><br />

<table >
<tr>
<td width="60" align="right"><?php echo xlt('Review of PMH:'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_pmh" <?php if ($obj["bronchitis_review_of_pmh"] == "on") {
    echo "checked";
                                                         };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('Medications:'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_medications" <?php if ($obj["bronchitis_review_of_medications"] == "on") {
    echo "checked";
                                                                 };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('Allergies:'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_allergies" <?php if ($obj["bronchitis_review_of_allergies"] == "on") {
    echo "checked";
                                                               };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('Social History:'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_sh" <?php if ($obj["bronchitis_review_of_sh"] == "on") {
    echo "checked";
                                                        };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('Family History:'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_fh" <?php if ($obj["bronchitis_review_of_fh"] == "on") {
    echo "checked";
                                                        };?>><span class=text></span><br /></td>
</tr>
</table>
<br /><br />


<table>
<tr>
<td width="60"><?php echo xlt('TM\'S:'); ?> </td>
<td align="right"><?php echo xlt('Normal Right:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_normal_right" <?php if ($obj["bronchitis_tms_normal_right"] == "on") {
    echo "checked";
                                                            };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_normal_left" <?php if ($obj["bronchitis_tms_normal_left"] == "on") {
    echo "checked";
                                                           };?>><span class=text></span><br />
<td align="right"><?php echo xlt('NARES: Normal Right'); ?> </td>
<td><input type=checkbox name="bronchitis_nares_normal_right" <?php if ($obj["bronchitis_nares_normal_right"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left:'); ?>  </td>
<td><input type=checkbox name="bronchitis_nares_normal_left" <?php if ($obj["bronchitis_nares_normal_left"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />
</tr>

<tr>
<td width="60"></td>
<td align="right"> <?php echo xlt('Thickened Right:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_thickened_right" <?php if ($obj["bronchitis_tms_thickened_right"] == "on") {
    echo "checked";
                                                               };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_thickened_left" <?php if ($obj["bronchitis_tms_thickened_left"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />

<td align="right"><?php echo xlt('Swelling Right'); ?> </td>
<td><input type=checkbox name="bronchitis_nares_swelling_right" <?php if ($obj["bronchitis_nares_swelling_right"] == "on") {
    echo "checked";
                                                                };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left: '); ?> </td>
<td><input type=checkbox name="bronchitis_nares_swelling_left" <?php if ($obj["bronchitis_nares_swelling_left"] == "on") {
    echo "checked";
                                                               };?>><span class=text></span><br />
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php echo xlt('A/F Level Right:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_af_level_right" <?php if ($obj["bronchitis_tms_af_level_right"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_af_level_left" <?php if ($obj["bronchitis_tms_af_level_left"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />

<td align="right"><?php echo xlt('Discharge Right'); ?> </td>
<td><input type=checkbox name="bronchitis_nares_discharge_right" <?php if ($obj["bronchitis_nares_discharge_right"] == "on") {
    echo "checked";
                                                                 };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left: '); ?> </td>
<td><input type=checkbox name="bronchitis_nares_discharge_left" <?php if ($obj["bronchitis_nares_discharge_left"] == "on") {
    echo "checked";
                                                                };?>><span class=text></span><br />
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php echo xlt('Retracted Right:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_retracted_right" <?php if ($obj["bronchitis_tms_retracted_right"] == "on") {
    echo "checked";
                                                               };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_retracted_left" <?php if ($obj["bronchitis_tms_retracted_left"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php echo xlt('Bulging Right:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_bulging_right" <?php if ($obj["bronchitis_tms_bulging_right"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_bulging_left" <?php if ($obj["bronchitis_tms_bulging_left"] == "on") {
    echo "checked";
                                                            };?>><span class=text></span><br />
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php echo xlt('Perforated Right:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_perforated_right" <?php if ($obj["bronchitis_tms_perforated_right"] == "on") {
    echo "checked";
                                                                };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_perforated_left" <?php if ($obj["bronchitis_tms_perforated_left"] == "on") {
    echo "checked";
                                                               };?>><span class=text></span><br />
</tr>
</table>

<table><tr>
<td width="127"></td>
<td align="right"><?php echo xlt('Not Examined:'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_nares_not_examined" <?php if ($obj["bronchitis_tms_nares_not_examined"] == "on") {
    echo "checked";
                                                                  };?>><span class=text></span><br />
</tr></table>
<br /><br />

<table>
<tr>
<td width="90"><?php echo xlt('SINUS TENDERNESS:'); ?> </td>
<td align="right"><?php echo xlt('No Sinus Tenderness:'); ?> </td>
<td><input type=checkbox name="bronchitis_no_sinus_tenderness" <?php if ($obj["bronchitis_no_sinus_tenderness"] == "on") {
    echo "checked";
                                                               };?>><span class=text></span><br />
<td width="90"><?php echo xlt('OROPHARYNX: '); ?> </td>
<td align="right"><?php echo xlt('Normal Oropharynx:'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_normal"<?php if ($obj["bronchitis_oropharynx_normal"] == "on") {
    echo "checked";
                                                            };?>><span class=text></span><br />
</tr>

<tr>
<td width="90" align="right"><?php echo xlt('Frontal Right:'); ?>  </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_frontal_right" <?php if ($obj["bronchitis_sinus_tenderness_frontal_right"] == "on") {
    echo "checked";
                                                                          };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Left:'); ?> </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_frontal_left" <?php if ($obj["bronchitis_sinus_tenderness_frontal_left"] == "on") {
    echo "checked";
                                                                         };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Erythema:'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_erythema" <?php if ($obj["bronchitis_oropharynx_erythema"] == "on") {
    echo "checked";
                                                               };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Exudate:'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_exudate" <?php if ($obj["bronchitis_oropharynx_exudate"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Abcess:'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_abcess" <?php if ($obj["bronchitis_oropharynx_abcess"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Ulcers:'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_ulcers" <?php if ($obj["bronchitis_oropharynx_ulcers"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />
</tr>

<tr>
<td width ="90" align="right"><?php echo xlt('Maxillary Right:'); ?> </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_maxillary_right" <?php if ($obj["bronchitis_sinus_tenderness_maxillary_right"] == "on") {
    echo "checked";
                                                                            };?>><span class=text></span><br /></td>
<td align="right"><?php echo xlt('Left:'); ?> </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_maxillary_left" <?php if ($obj["bronchitis_sinus_tenderness_maxillary_left"] == "on") {
    echo "checked";
                                                                           };?>><span class=text></span><br /></td>
<td width="130" align="right"><?php echo xlt('Appearance:'); ?>  <span class="text"></span></td>
<td><input type="text" name="bronchitis_oropharynx_appearance" value="<?php echo
stripslashes($obj["bronchitis_oropharynx_appearance"]);?>" size="15"></td>
</tr>
</table>

<table>
<tr>
<td width="256" align="right"><?php echo xlt('Not Examined:'); ?>  </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_not_examined" <?php if ($obj["bronchitis_sinus_tenderness_not_examined"] == "on") {
    echo "checked";
                                                                         };?>><span class=text></span><br />
<td width="208" align="right"><?php echo xlt('Not Examined:'); ?>  </td>
<td><input type=checkbox name="bronchitis_oropharynx_not_examined" <?php if ($obj["bronchitis_oropharynx_not_examined"] == "on") {
    echo "checked";
                                                                   };?>><span class=text></span><br />
</tr>
</table>
<br /><br />

<table>
<tr>
<td width="60"><?php echo xlt('HEART:'); ?> </td>
<td align="right"><?php echo xlt('laterally displaced PMI:'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_pmi" <?php if ($obj["bronchitis_heart_pmi"] == "on") {
    echo "checked";
                                                     };?>><span class=text></span><br />
<td align="right"><?php echo xlt('S3:'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_s3" <?php if ($obj["bronchitis_heart_s3"] == "on") {
    echo "checked";
                                                    };?>><span class=text></span><br />
<td align="right"><?php echo xlt('S4:'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_s4" <?php if ($obj["bronchitis_heart_s4"] == "on") {
    echo "checked";
                                                    };?>><span class=text></span><br />
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php echo xlt('Click:'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_click" <?php if ($obj["bronchitis_heart_click"] == "on") {
    echo "checked";
                                                       };?>><span class=text></span><br />
<td align="right"><?php echo xlt('Rub:'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_rub" <?php if ($obj["bronchitis_heart_rub"] == "on") {
    echo "checked";
                                                     };?>><span class=text></span><br />
</tr>
</table>

<table><tr>
<td width="200" align="right"><?php echo xlt('Murmur:'); ?>  <span class="text"></span></td>
<td><input type="text" name="bronchitis_heart_murmur" value="<?php echo
attr($obj["bronchitis_heart_murmur"]); ?>" size="15"></td>

<td><span class="text"><?php echo xlt('Grade:'); ?>  </span></td><td>
<input type="text" name="bronchitis_heart_grade" value="<?php echo
attr($obj["bronchitis_heart_grade"]); ?>" size="15"></td>

<td><span class="text"><?php echo xlt('Location:'); ?>  </span></td><td>
<input type="text" name="bronchitis_heart_location" value="<?php echo
attr($obj["bronchitis_heart_location"]); ?>" size="15"></td>
</tr>
</table>

<table><tr>
<td width="205" align="right"><?php echo xlt('Normal Cardiac Exam:'); ?>  </td>
<td><input type=checkbox name="bronchitis_heart_normal" <?php if ($obj["bronchitis_heart_normal"] == "on") {
    echo "checked";
                                                        };?>><span class=text></span><br />
<td width="95" align="right"><?php echo xlt('Not Examined:'); ?>  </td>
<td><input type=checkbox name="bronchitis_heart_not_examined" <?php if ($obj["bronchitis_heart_not_examined"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />
</tr></table>
<br /><br />

<table><tr>
<td width="60"><?php echo xlt('Lungs:'); ?> </td>
<td width="106"><?php echo xlt('Breath Sounds:'); ?> </td>
<td align="right"> <?php echo xlt('normal:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_bs_normal" <?php if ($obj["bronchitis_lungs_bs_normal"] == "on") {
    echo "checked";
                                                           };?>><span class=text></span><br />

<td align="right"><?php echo xlt('reduced:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_bs_reduced" <?php if ($obj["bronchitis_lungs_bs_reduced"] == "on") {
    echo "checked";
                                                            };?>><span class=text></span><br />

<td align="right"><?php echo xlt('increased:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_bs_increased" <?php if ($obj["bronchitis_lungs_bs_increased"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />
</tr>

<tr>
<td width="60"></td>
<td><?php echo xlt('Crackles:'); ?> </td>
<td align="right"><?php echo xlt(' LLL:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_crackles_lll" <?php if ($obj["bronchitis_lungs_crackles_lll"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />

<td align="right"><?php echo xlt('RLL:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_crackles_rll" <?php if ($obj["bronchitis_lungs_crackles_rll"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />

<td align="right"><?php echo xlt('Bilateral:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_crackles_bll" <?php if ($obj["bronchitis_lungs_crackles_bll"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />
</tr>

<tr>
<td width="60"></td>
<td><?php echo xlt('Rubs:'); ?> </td>
<td align="right"><?php echo xlt('LLL:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_rubs_lll" <?php if ($obj["bronchitis_lungs_rubs_lll"] == "on") {
    echo "checked";
                                                          };?>><span class=text></span><br />

<td align="right"><?php echo xlt('RLL:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_rubs_rll" <?php if ($obj["bronchitis_lungs_rubs_rll"] == "on") {
    echo "checked";
                                                          };?>><span class=text></span><br />

<td align="right"><?php echo xlt('Bilateral:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_rubs_bll" <?php if ($obj["bronchitis_lungs_rubs_bll"] == "on") {
    echo "checked";
                                                          };?>><span class=text></span><br />
</tr>

<tr>
<td width="60"></td>
<td><?php echo xlt('Wheezes:'); ?> </td>
<td align="right"><?php echo xlt('LLL:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_lll" <?php if ($obj["bronchitis_lungs_wheezes_lll"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />

<td align="right"><?php echo xlt('RLL:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_rll" <?php if ($obj["bronchitis_lungs_wheezes_rll"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />

<td align="right"><?php echo xlt('Bilateral:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_bll" <?php if ($obj["bronchitis_lungs_wheezes_bll"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />

<td align="right"><?php echo xlt('Diffuse:'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_dll" <?php if ($obj["bronchitis_lungs_wheezes_dll"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />
</tr>
</table>


<table><tr>
<td width="218" align="right"><?php echo xlt('Normal Lung Exam:'); ?>  </td>
<td><input type=checkbox name="bronchitis_lungs_normal_exam" <?php if ($obj["bronchitis_lungs_normal_exam"] == "on") {
    echo "checked";
                                                             };?>><span class=text></span><br />
<td width="140" align="right"><?php echo xlt('Not Examined'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_not_examined" <?php if ($obj["bronchitis_lungs_not_examined"] == "on") {
    echo "checked";
                                                              };?>><span class=text></span><br />
</tr></table>
<br /><br />

<span class="text"><?php echo xlt('Diagnostic Tests:'); ?> </span><br />
<textarea name="bronchitis_diagnostic_tests" cols ="67" rows="4"  wrap="virtual name">
<?php echo text($obj["bronchitis_diagnostic_tests"]);?></textarea>
<br /><br />

<table><tr>
<span class="text"><?php echo xlt('Diagnosis: '); ?> </span>
<br /><input type="text" name="diagnosis1_bronchitis_form" value="<?php echo
attr($obj["diagnosis1_bronchitis_form"]);?>" size="40"><br />
</tr>

<tr>
<input type="text" name="diagnosis2_bronchitis_form" value="<?php echo
attr($obj["diagnosis2_bronchitis_form"]);?>" size="40"><br />
</tr>

<tr>
<input type="text" name="diagnosis3_bronchitis_form" value="<?php echo
attr($obj["diagnosis3_bronchitis_form"]);?>" size="40"><br />
</tr>

<tr>
<input type="text" name="diagnosis4_bronchitis_form" value="<?php echo
attr($obj["diagnosis4_bronchitis_form"]);?>" size="40"><br />
</tr>

<table>
<br />
<span class="text"><?php echo xlt('Additional Diagnosis:'); ?>  </span><br />
<textarea name="bronchitis_additional_diagnosis" rows="4" cols="67" wrap="virtual name">
<?php echo text($obj["bronchitis_additional_diagnosis"]);?></textarea>
<br /><br />

<span class="text"><?php echo xlt('Treatment: '); ?> </span><br />
<textarea name="bronchitis_treatment" rows="4" cols="67" wrap="virtual name">
<?php echo text($obj["bronchitis_treatment"]);?></textarea>
<br />

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?> ]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save Changes'); ?> ]</a>

</form>
<?php
formFooter();
?>
