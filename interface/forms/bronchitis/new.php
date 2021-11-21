<?php

/**
 * bronchitis new.php
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
require_once("$srcdir/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: bronchitis");
$returnurl = 'encounter_top.php';
?>
<html><head>
<script>
<!--

   function onset_check (form)   {

    var d, s = "Today's date is: "; //Declare variables.
    d = new Date(); //Create Date object.
    s += (d.getMonth() + 1) + "-"; //Get month
    s += d.getDate() + "-"; //Get day
    s += d.getYear(); //Get year.

    onset_str = form.bronchitis_date_of_illness.value;
    if (onset_str == "") {
    alert("No valid date into Onset of illness field!!! Enter date as YYYY-MM-DD");
    alert(d);

    return;
      }
    if (onset_str.length != 10) {
     alert("Your date should be 10 characters");
    return;
      }
    alert("OK, Bye!!!");
    return;
   }
</script>

<?php Header::setupHeader(); ?>
</head>
<body class="body_top">

<form method=post action="<?php echo $rootdir;?>/forms/bronchitis/save.php?mode=new" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<br /><br />
<span class="title" ><?php echo xlt('Bronchitis Form'); ?></span>
<br /><br />

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" style="color: #483D8B"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save'); ?>]</a>
<br /><br />
<span class="text" ><?php echo xlt('Onset of Illness:'); ?> </span><input type="text" name="bronchitis_date_of_illness" value=""></input>
<br /><br />

<span class="text" ><?php echo xlt('HPI:'); ?>:</span><br /><br />
<textarea name="bronchitis_hpi" rows="4" cols="67" wrap="virtual name"></textarea>
<br /><br />

<table ><th colspan="5"><?php echo xlt('Other Pertinent Symptoms:'); ?></th>
<tr>
<td width="80" align="right"><?php echo xlt('Fever:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_fever"></input></td>
<td width="100" align="right"><?php echo xlt('Cough:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_cough"></input></td>
<td width="60" align="right"><?php echo xlt('Dizziness:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_dizziness"></input></td>
</tr>
<tr>
<td width="80" align="right"><?php echo xlt('Chest Pain:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_chest_pain"></input></td>
<td width="100" align="right"><?php echo xlt('Dyspnea:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_dyspnea"></input></td>
<td width="60" align="right"><?php echo xlt('Sweating:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_sweating"></input></td>
</tr>
<tr>
<td width="80" align="right"><?php echo xlt('Wheezing:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_wheezing"></input></td>
<td width="100" align="right"><?php echo xlt('Malaise:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_malaise"></input></td>
</tr>
<tr>
<td width="80" align="right"><?php echo xlt('Sputum:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_sputum"></input></td>
<td width="100" align="right"><?php echo xlt('Appearance:'); ?></td>
<td><input type="text" name="bronchitis_ops_appearance" size="10" value="<?php echo xla('none{{Symptoms}}'); ?>"></input></td>
</tr>
</table>

<table>
<tr>
<td width="205" align="right"><?php echo xlt('All Reviewed and Negative:'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_all_reviewed"></input></td>
</tr>
</table>
<br /><br />


<table >
<tr>
<td width="60"><?php echo xlt('Review of PMH:'); ?></td>
<td align="right"></td>
<td><input type="checkbox" name="bronchitis_review_of_pmh"></input></td>
<td align="right"><?php echo xlt('Medications:'); ?></td>
<td><input type="checkbox" name="bronchitis_review_of_medications"></input></td>
<td align="right"><?php echo xlt('Allergies:'); ?></td>
<td><input type="checkbox" name="bronchitis_review_of_allergies"></input></td>
<td align="right"><?php echo xlt('Social History:'); ?></td>
<td><input type="checkbox" name="bronchitis_review_of_sh"></input></td>
<td align="right"><?php echo xlt('Family History:'); ?></td>
<td><input type="checkbox" name="bronchitis_review_of_fh"></input></td>
</tr>
</table>
<br /><br />

<table>
<tr>
<td width="60"><?php echo xlt('TM\'S:'); ?></td>
<td align="right"><?php echo xlt('Normal Right:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_normal_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_normal_left"></input></td>

<td width="80"><?php echo xlt('NARES:'); ?> </td>
<td align="right"><?php echo xlt('Normal Right'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_normal_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_normal_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php echo xlt('Thickened Right:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_thickened_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_thickened_left"></input></td>

<td width="80"></td>
<td align="right"><?php echo xlt('Swelling Right'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_swelling_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_swelling_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php echo xlt('A/F Level Right:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_af_level_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_af_level_left"></input></td>

<td width="80"></td>
<td align="right"><?php echo xlt('Discharge Right:'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_discharge_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_discharge_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php echo xlt('Retracted Right:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_retracted_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_retracted_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php echo xlt('Bulging Right:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_bulging_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_bulging_left"></input></td>

</tr>

<tr>
<td width="50"></td>
<td align="right"><?php echo xlt('Perforated Right:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_perforated_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_perforated_left"></input></td>
</tr>
</table>

<table>
<tr>
<td width="220" align="right"><?php echo xlt('Not Examined:'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_nares_not_examined"></input></td>
</tr>
</table>
<br /><br />

<table>
<tr>
<td width="90"><?php echo xlt('SINUS TENDERNESS:'); ?></td>
<td align="right"><?php echo xlt('No Sinus Tenderness:'); ?></td>
<td><input type="checkbox" name="bronchitis_no_sinus_tenderness"></input></td>
<td align="right"></td>

<td width="90"><?php echo xlt('OROPHARYNX: '); ?></td>
<td align="right"><?php echo xlt('Normal Oropharynx:'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_normal"></input></td>
<td align="right"></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php echo xlt('Frontal Right:'); ?></td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_frontal_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_frontal_left"></input></td>
<td align="right"><?php echo xlt('Erythema:'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_erythema"></input></td>
<td align="right"><?php echo xlt('Exudate:'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_exudate"></input></td>
<td align="right"><?php echo xlt('Abcess:'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_abcess"></input></td>
<td align="right"><?php echo xlt('Ulcers:'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_ulcers"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php echo xlt('Maxillary Right:'); ?></td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_maxillary_right"></input></td>
<td align="right"><?php echo xlt('Left:'); ?></td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_maxillary_left"></input></td>

<td width="120" align="right"><?php echo xlt('Appearance:'); ?></td>
<td><input type="text" name="bronchitis_oropharynx_appearance" size="10" value="normal"></input></td>
</tr>
</table>

<table>
<tr>
<td width="238" align="right" ><?php echo xlt('Not Examined:'); ?> </td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_not_examined"></input></td>
<td width="268" align="right" ><?php echo xlt('Not Examined:'); ?> </td>
<td><input type="checkbox" name="bronchitis_oropharynx_not_examined"></input></td>
</tr>
</table>
<br /><br />

<table>
<tr>
<td width="60"><?php echo xlt('HEART:');?></td>
<td align="right"><?php echo xlt('laterally displaced PMI:'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_pmi"></input></td>
<td align="right"><?php echo xlt('S3:'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_s3"></input></td>
<td align="right"><?php echo xlt('S4:'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_s4"></input></td>
</tr>
<tr>
<td width="60"></td>
<td align="right"><?php echo xlt('Click:'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_click"></input></td>
<td align="right"><?php echo xlt('Rub:'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_rub"></input></td>
</tr>
</table>

<table>
<tr>
<td width="60"></td>
<td><?php echo xlt('Murmur:'); ?></td>
<td><input type="text" name="bronchitis_heart_murmur" size="10" value="none"></input></td>
<td><?php echo xlt('Grade:'); ?></td>
<td><input type="text" name="bronchitis_heart_grade" size="10" value="n/a"></input></td>
<td><?php echo xlt('Location:'); ?></td>
<td><input type="text" name="bronchitis_heart_location" size="10" value="n/a"></input></td>
</tr>
</table>

<table>
<tr>
<td width="203" align="right" ><?php echo xlt('Normal Cardiac Exam: '); ?></td>
<td><input type="checkbox" name="bronchitis_heart_normal"></input></td>
<td width="93" align="right"><?php echo xlt('Not Examined:'); ?> </td>
<td><input type="checkbox" name="bronchitis_heart_not_examined"></input></td>
</tr>
</table>
<br /><br />

<table>
<tr>
<td width="60"><?php echo xlt('LUNGS:'); ?></td>
<td width="106"><?php echo xlt('Breath Sounds:'); ?></td>
<td align="right"> <?php echo xlt('normal:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_bs_normal"></input></td>
<td align="right"><?php echo xlt('reduced:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_bs_reduced"></input></td>
<td align="right"><?php echo xlt('increased:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_bs_increased"></input></td>
</tr>

<tr>
<td width="60"></td>
<td><?php echo xlt('Crackles:'); ?></td>
<td align="right"><?php echo xlt('LLL:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_crackles_lll"></input></td>
<td align="right"><?php echo xlt('RLL:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_crackles_rll"></input></td>
<td align="right"><?php echo xlt('Bilateral:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_crackles_bll"></input></td>
</tr>

<tr>
<td width="60"></td>
<td><?php echo xlt('Rubs:'); ?></td>
<td align="right"><?php echo xlt('LLL:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_rubs_lll"></input></td>
<td align="right"><?php echo xlt('RLL:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_rubs_rll"></input></td>
<td align="right"><?php echo xlt('Bilateral:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_rubs_bll"></input></td>
</tr>

<tr>
<td width="60"></td>
<td><?php echo xlt('Wheezes:'); ?></td>
<td align="right"><?php echo xlt('LLL:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_lll"></input></td>
<td align="right"><?php echo xlt('RLL:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_rll"></input></td>
<td align="right"><?php echo xlt('Bilateral:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_bll"></input></td>
<td align="right"><?php echo xlt('Diffuse:'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_dll"></input></td>
</tr>
</table>

<table>
<tr>
<td width="218" align="right" ><?php echo xlt('Normal Lung Exam: '); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_normal_exam"></input></td>
<td width="140" align="right" ><?php echo xlt('Not Examined: '); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_not_examined"></input></td>
</tr>
</table>
<br /><br />

<span class="text" ><?php echo xlt('Diagnostic Tests:'); ?></span><br /><br />
<textarea name="bronchitis_diagnostic_tests" rows="4" cols="67" wrap="virtual name"></textarea>
<br /><br />

<span class="text" ><?php echo xlt('Diagnosis:'); ?> </span>
<table><tr>
   <td>
   <select name="diagnosis1_bronchitis_form" >
      <option value="None"><?php echo xlt('None{{Diagnosis}}'); ?></option>
      <option value="465.9, URI"><?php echo xlt('465.9, URI'); ?></option>
      <option value="466.0, Bronchitis, Acute NOS"><?php echo xlt('466.0, Bronchitis, Acute NOS'); ?></option>
      <option value="493.92, Astma, Acute Exac."><?php echo xlt('493.92, Asthma, Acute Exac.'); ?></option>
      <option value="491.8, Bronchitis, Chronic"><?php echo xlt('491.8, Bronchitis, Chronic'); ?></option>
      <option value="496.0, COPD"><?php echo xlt('496.0, COPD'); ?></option>
      <option value="491.21,COPD Exacerbation"><?php echo xlt('491.21, COPD Exacerbation'); ?></option>
      <option value="486.0, Pneumonia, Acute"><?php echo xlt('486.0, Pneumonia, Acute'); ?></option>
      <option value="519.7, Bronchospasm"><?php echo xlt('519.7, Bronchospasm'); ?></option>
      <br /><br />
   </select>
   </td>
</tr>
<tr>
   <td>
   <select name="diagnosis2_bronchitis_form">
      <option value="None"><?php echo xlt('None{{Diagnosis}}'); ?></option>
      <option value="465.9, URI"><?php echo xlt('465.9, URI'); ?></option>
      <option value="466.0, Bronchitis, Acute NOS"><?php echo xlt('466.0, Bronchitis, Acute NOS'); ?></option>
      <option value="493.92, Asthma, Acute Exac."><?php echo xlt('493.92, Asthma, Acute Exac.'); ?></option>
      <option value="491.8, Bronchitis, Chronic"><?php echo xlt('491.8, Bronchitis, Chronic'); ?></option>
      <option value="496.0, COPD"><?php echo xlt('496.0, COPD'); ?></option>
      <option value="491.21,COPD Exacerbation"><?php echo xlt('491.21, COPD Exacerbation'); ?></option>
      <option value="486.0, Pneumonia, Acute"><?php echo xlt('486.0, Pneumonia, Acute'); ?></option>
      <option value="519.7, Bronchospasm"><?php echo xlt('519.7, Bronchospasm'); ?></option>
      <br /><br />
   </select>
   </td>
</tr>
<tr>
   <td>
   <select name="diagnosis3_bronchitis_form">
      <option value="None"><?php echo xlt('None{{Diagnosis}}'); ?></option>
      <option value="465.9, URI"><?php echo xlt('465.9, URI'); ?></option>
      <option value="466.0, Bronchitis, Acute NOS"><?php echo xlt('466.0, Bronchitis, Acute NOS'); ?></option>
      <option value="493.92, Asthma, Acute Exac."><?php echo xlt('493.92, Asthma, Acute Exac.'); ?></option>
      <option value="491.8, Bronchitis, Chronic"><?php echo xlt('491.8, Bronchitis, Chronic'); ?></option>
      <option value="496.0, COPD"><?php echo xlt('496.0, COPD'); ?></option>
      <option value="491.21,COPD Exacerbation"><?php echo xlt('491.21, COPD Exacerbation'); ?></option>
      <option value="486.0, Pneumonia, Acute"><?php echo xlt('486.0, Pneumonia, Acute'); ?></option>
      <option value="519.7, Bronchospasm"><?php echo xlt('519.7, Bronchospasm'); ?></option>
     <br /><br />
   </select>
   </td>
</tr>
<tr>
   <td>
   <select name="diagnosis4_bronchitis_form">
      <option value="None"><?php echo xlt('None{{Diagnosis}}'); ?></option>
      <option value="465.9, URI"><?php echo xlt('465.9, URI'); ?></option>
      <option value="466.0, Bronchitis, Acute NOS"><?php echo xlt('466.0, Bronchitis, Acute NOS'); ?></option>
      <option value="493.92, Asthma, Acute Exac."><?php echo xlt('493.92, Asthma, Acute Exac.'); ?></option>
      <option value="491.8, Bronchitis, Chronic"><?php echo xlt('491.8, Bronchitis, Chronic'); ?></option>
      <option value="496.0, COPD"><?php echo xlt('496.0, COPD'); ?></option>
      <option value="491.21,COPD Exacerbation"><?php echo xlt('491.21, COPD Exacerbation'); ?></option>
      <option value="486.0, Pneumonia, Acute"><?php echo xlt('486.0, Pneumonia, Acute'); ?></option>
      <option value="519.7, Bronchospasm"><?php echo xlt('519.7, Bronchospasm'); ?></option>
     <br /><br />
   </select>
   </td>
</tr>
<table>
<br /><br />

<span class="text" ><?php echo xlt('Additional Diagnosis:'); ?> </span><br /><br />
<textarea name="bronchitis_additional_diagnosis" rows="4" cols="67" wrap="virtual name"></textarea>
<br /><br />

<span class="text" ><?php echo xlt('Treatment:'); ?> </span><br /><br />
<textarea name="bronchitis_treatment" rows="4" cols="67" wrap="virtual name"></textarea>

<br /><br />
<input type="Button" value="<?php echo xla('Check Input Data'); ?>" style="color: #483D8B" onClick = "onset_check(my_form)"<br />
<br />
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" style="color: #483D8B"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save'); ?>]</a>
</form>

<?php
formFooter();
?>
