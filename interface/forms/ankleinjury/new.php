<?php

/**
 * ankleinjury new.php
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
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: ankleinjury");
?>

<html><head>
    <?php Header::setupHeader(); ?>
</head>

<body class="body_top">
<form method=post action="<?php echo $rootdir;?>/forms/ankleinjury/save.php?mode=new" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<span class="title"><?php echo xlt('Ankle Evaluation Form'); ?></span><br /><br />

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" style="color: #483D8B"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save'); ?>]</a>
<br /><br />

<span class="text" ><?php echo xlt('Date of Injury'); ?>: </span><input type="text" name="ankle_date_of_injuary" value=""></input>
<tr>
<td width="120" align="right"><?php echo xlt('Work related?'); ?></td>
<td><input type="checkbox" name="ankle_work_related"></input></td>
</tr>
<br /><br />

<span class="text"><?php echo xlt('Foot:'); ?> </span>
   <td><?php echo xlt('Left'); ?><input type="radio" name='ankle_foot' value="<?php echo xla('Left'); ?>"></input></td>
   <td><?php echo xlt('Right'); ?><input type="radio" name='ankle_foot' value="<?php echo xla('Right'); ?>"></input></td>
<br /><br />

<span class="text"><?php echo xlt('Severity of Pain'); ?>:   </span>
   <td>1<input type="radio" name='ankle_severity_of_pain' value="1"></input></td>
   <td>2<input type="radio" name='ankle_severity_of_pain' value="2"></input></td>
   <td>3<input type="radio" name='ankle_severity_of_pain' value="3"></input></td>
<br /><br />

<td width="140"><?php echo xlt('Significant Swelling:'); ?></td>
<td align="right"></td>
<td><input type="checkbox" name="ankle_significant_swelling"></input></td>
<br /><br />

<span class="text"><?php echo xlt('Onset of Swelling:'); ?>   </span>
   <td><?php echo xlt('within minutes'); ?><input type="radio" name='ankle_onset_of_swelling' value="<?php echo xla('within minutes'); ?>"></input></td>
   <td><?php echo xlt('within hours'); ?><input type="radio" name='ankle_onset_of_swelling' value="<?php echo xla('within hours'); ?>"></input></td>
   <br /><br />

<span class="text" ><?php echo xlt('How did Injury Occur?'); ?>:</span><br />
<textarea name="ankle_how_did_injury_occur" rows="4" cols="67" wrap="virtual name"></textarea>
<br /><br />

<table ><th colspan="5"><?php echo xlt('Ottawa Ankle Rules'); ?></th>
<tr>
<td><?php echo xlt('Bone Tenderness: Medial Malleolus'); ?><input type="radio" name='ankle_ottawa_bone_tenderness' value="Medial malleolus"></input></td>
<td><?php echo xlt('Lateral Malleolus'); ?><input type="radio" name='ankle_ottawa_bone_tenderness' value="Lateral malleolus"></input></td>
<td><?php echo xlt('Base of fifth (5th) Metarsal'); ?><input type="radio" name='ankle_ottawa_bone_tenderness' value="Base of fifth (5th) Metarsal"></input></td>
<td><?php echo xlt('At the Navicular'); ?><input type="radio" name='ankle_ottawa_bone_tenderness' value="At the Navicular"></input></td>
</tr>
</table>
<br />

<span class="text"><?php echo xlt('Able to Bear Weight four (4) steps:'); ?></span>
  <td><?php echo xlt('Yes'); ?><input type="radio" name='ankle_able_to_bear_weight_steps' value="<?php echo xla('Yes'); ?>"></input></td>
  <td><?php echo xlt('No'); ?><input type="radio" name='ankle_able_to_bear_weight_steps' value="<?php echo xla('No'); ?>"></input></td>
<br />

<table>
<tr><th><?php echo xlt('X-RAY Interpretation:'); ?></th> <th><?php echo xlt('Additional X-RAY Notes:'); ?></th></tr>
<tr>
  <td>
   <select name="ankle_x_ray_interpretation" >
      <option value="Normal"><?php echo xlt('Normal'); ?></option>
      <option value="Avulsion medial malleolus"><?php echo xlt('Avulsion medial malleolus '); ?></option>
      <option value="Avulsion lateral malleolus"><?php echo xlt('Avulsion lateral malleolus'); ?></option>
      <option value="Fracture, Base of fifth (5th) Metatarsal"><?php echo xlt('Fracture, Base of fifth (5th) Metatarsal'); ?></option>
      <option value="Trimalleolar"><?php echo xlt('Trimalleolar'); ?></option>
      <option value="Fracture at the Navicula"><?php echo xlt('Fracture at the Navicula'); ?></option>
      <option value="Fracture medial malleolus"><?php echo xlt('Fracture medial malleolus'); ?></option>
      <option value="Fracture lateral malleolus"><?php echo xlt('Fracture lateral malleolus'); ?></option>
      <option value="Other"><?php echo xlt('Other'); ?></option>
      </select>
   </td>

<td rowspan=2>
<textarea cols=35 rows=1 wrap=virtual name="ankle_additional_x_ray_notes" ></textarea>

</td>

</tr>
</table>
 <script>
 <!--
 function doCPT(select) {
    var numchecked = 0;
    for (i=0; i<document.my_form.openemr_net_cptcode.length; i++) {
        if (document.my_form.openemr_net_cptcode[i].checked == true) {
            numchecked++;
        }
    }
    if (numchecked == 0) {
        document.my_form.openemr_net_cptcode[1].checked = true;
    }
 }
 -->
 </script>
<table>
<tr>
<th><?php echo xlt('Diagnosis:'); ?></th><th><?php echo xlt('Additional Diagnosis:'); ?></th>
</tr>
<tr>
<td valign="top"><select name="ankle_diagnosis1" onChange="doCPT(this);">
      <option value=""><?php echo xlt('None{{Diagnosis}}'); ?></option>
      <option value="845.00 ankle sprain NOS"><?php echo xlt('845.00 ankle sprain NOS'); ?></option>
      <option value="845.01 Sprain Medial (Deltoid) Lig."><?php echo xlt('845.01 Sprain Medial (Deltoid) Lig.'); ?></option>
      <option value="845.02 Sprain, Calcaneal fibular"><?php echo xlt('845.02 Sprain, Calcaneal fibular'); ?></option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal"><?php echo xlt('825.35 Fracture, Base of fifth (5th) Metatarsal'); ?></option>
      <option value="825.32 Fracture, of Navicular (ankle)"><?php echo xlt('825.32 Fracture, of Navicular (ankle)'); ?></option>
      <option value="824.2 Fracture, lateral malleolus, closed"><?php echo xlt('824.2 Fracture, lateral malleolus, closed'); ?></option>
      <option value="824.0 Fracture, medial malleolus, closed"><?php echo xlt('824.0 Fracture, medial malleolus, closed'); ?></option>
      <option value="824.6 Fracture, Trimalleolar, closed"><?php echo xlt('824.6 Fracture, Trimalleolar, closed'); ?></option>
      <option value="Add ICD Code"><?php echo xlt('Add ICD Code'); ?></option>
    </select>

</td>
<td rowspan="4">
<textarea cols=30 rows=2 wrap=virtual name="ankle_additional_diagnisis" ></textarea>
<br /><br />
<table>
<tr>
    <td width="10"></td>
    <td><?php echo xlt('CPT Codes');?></td>
    <td></td>
</tr>
<tr>
    <td></td>
    <td colspan="2">
        &nbsp;&nbsp;&nbsp;<input type="radio" name="openemr_net_cptcode" value=""><?php echo xlt('none{{Code}}'); ?><br />
        &nbsp;&nbsp;&nbsp;<input type="radio" name="openemr_net_cptcode" value="99212 Established - Uncomplicated"><?php echo xlt('99212 Established - Uncomplicated'); ?><br />
        &nbsp;&nbsp;&nbsp;<input type="radio" name="openemr_net_cptcode" value="99213 Established - Low Complexity"><?php echo xlt('99213 Established - Low Complexity'); ?><br />
    </td>
</tr>
</table>

</td>

<tr>
<td>
  <select name="ankle_diagnosis2" onChange="doCPT(this);">
      <option value=""><?php echo xlt('None{{Diagnosis}}'); ?></option>
      <option value="845.00 ankle sprain NOS"><?php echo xlt('845.00 ankle sprain NOS'); ?></option>
      <option value="845.01 Sprain Medial (Deltoid) Lig."><?php echo xlt('845.01 Sprain Medial (Deltoid) Lig.'); ?></option>
      <option value="845.02 Sprain, Calcaneal fibular"><?php echo xlt('845.02 Sprain, Calcaneal fibular'); ?></option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal"><?php echo xlt('825.35 Fracture, Base of fifth (5th) Metatarsal'); ?></option>
      <option value="825.32 Fracture, of Navicular (ankle)"><?php echo xlt('825.32 Fracture, of Navicular (ankle)'); ?></option>
      <option value="824.2 Fracture, lateral malleolus, closed"><?php echo xlt('824.2 Fracture, lateral malleolus, closed'); ?></option>
      <option value="824.0 Fracture, medial malleolus, closed"><?php echo xlt('824.0 Fracture, medial malleolus, closed'); ?></option>
      <option value="824.6 Fracture, Trimalleolar, closed"><?php echo xlt('824.6 Fracture, Trimalleolar, closed'); ?></option>
      <option value="Add ICD Code"><?php echo xlt('Add ICD Code'); ?></option>
    </select>
   </td></tr>
<td>
   <select name="ankle_diagnosis3" onChange="doCPT(this);">
      <option value=""><?php echo xlt('None{{Diagnosis}}'); ?></option>
      <option value="845.00 ankle sprain NOS"><?php echo xlt('845.00 ankle sprain NOS'); ?></option>
      <option value="845.01 Sprain Medial (Deltoid) Lig."><?php echo xlt('845.01 Sprain Medial (Deltoid) Lig.'); ?></option>
      <option value="845.02 Sprain, Calcaneal fibular"><?php echo xlt('845.02 Sprain, Calcaneal fibular'); ?></option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal"><?php echo xlt('825.35 Fracture, Base of fifth (5th) Metatarsal'); ?></option>
      <option value="825.32 Fracture, of Navicular (ankle)"><?php echo xlt('825.32 Fracture, of Navicular (ankle)'); ?></option>
      <option value="824.2 Fracture, lateral malleolus, closed"><?php echo xlt('824.2 Fracture, lateral malleolus, closed'); ?></option>
      <option value="824.0 Fracture, medial malleolus, closed"><?php echo xlt('824.0 Fracture, medial malleolus, closed'); ?></option>
      <option value="824.6 Fracture, Trimalleolar, closed"><?php echo xlt('824.6 Fracture, Trimalleolar, closed'); ?></option>
      <option value="Add ICD Code"><?php echo xlt('Add ICD Code'); ?></option>
   </select>
   </td>
</tr>
<td>
   <select name="ankle_diagnosis4" onChange="doCPT(this);">
      <option value=""><?php echo xlt('None{{Diagnosis}}'); ?></option>
      <option value="845.00 ankle sprain NOS"><?php echo xlt('845.00 ankle sprain NOS'); ?></option>
      <option value="845.01 Sprain Medial (Deltoid) Lig."><?php echo xlt('845.01 Sprain Medial (Deltoid) Lig.'); ?></option>
      <option value="845.02 Sprain, Calcaneal fibular"><?php echo xlt('845.02 Sprain, Calcaneal fibular'); ?></option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal"><?php echo xlt('825.35 Fracture, Base of fifth (5th) Metatarsal'); ?></option>
      <option value="825.32 Fracture, of Navicular (ankle)"><?php echo xlt('825.32 Fracture, of Navicular (ankle)'); ?></option>
      <option value="824.2 Fracture, lateral malleolus, closed"><?php echo xlt('824.2 Fracture, lateral malleolus, closed'); ?></option>
      <option value="824.0 Fracture, medial malleolus, closed"><?php echo xlt('824.0 Fracture, medial malleolus, closed'); ?></option>
      <option value="824.6 Fracture, Trimalleolar, closed"><?php echo xlt('824.6 Fracture, Trimalleolar, closed'); ?></option>
<option value="Add ICD Code"><?php echo xlt('Add ICD Code'); ?></option>
</select>
</td>
</tr>
</table>

<table><tr><th><?php echo xlt('Plan:'); ?></th><tr>
<tr><td>
<textarea name="ankle_plan" rows="7" cols="67"
wrap="virtual name"><?php echo xlt('1.Rest
2. Ice for two days
3. Compression, leave the dressing in place unless the foot develops numbness or pale color
4. Elevate the foot and leg'); ?>
</textarea>
</td>
</tr>
</table>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" style="color: #483D8B"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save'); ?>]</a>
</form>
<?php
formFooter();
?>
