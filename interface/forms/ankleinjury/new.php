<!-- Form created by Nikolai Vitsyn: 2004/02/13  -->

<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: ankleinjury");
?>

<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>

<body <?echo $top_bg_line;?>
topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/ankleinjury/save.php?mode=new" name="my_form">
<span class="title"><?php xl('Ankle Evaluation Form','e'); ?></span><br></br>

<a href="javascript:document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" style="color: #483D8B">[<?php xl('Don\'t Save','e'); ?>]</a>
<br></br>

<span class="text" ><?php xl('Date of Injury','e'); ?>: </span><input type="entry" name="ankle_date_of_injuary" value=""></input>
<tr>
<td width="120" align="right"><?php xl('Work related?','e'); ?></td>
<td><input type="checkbox" name="ankle_work_related"></input></td>
</tr>
<br><br>

<span class="text"><?php xl('Foot:','e');?> </span>  
   <td><?php xl('Left','e');?><input type="radio" name='ankle_foot' value="<?php xl('Left','e');?>"></input></td>
   <td><?php xl('Right','e');?><input type="radio" name='ankle_foot' value="<?php xl('Right','e');?>"></input></td>
<br><br>
   
<span class="text"><?php xl('Severity of Pain','e');?>:   </span>  
   <td>1<input type="radio" name='ankle_severity_of_pain' value="1"></input></td>
   <td>2<input type="radio" name='ankle_severity_of_pain' value="2"></input></td>
   <td>3<input type="radio" name='ankle_severity_of_pain' value="3"></input></td>
<br><br>
 
<td width="140"><?php xl('Significant Swelling:','e');?></td>
<td align="right"></td>
<td><input type="checkbox" name="ankle_significant_swelling"></input></td>
<br><br>

<span class="text"><?php xl('Onset of Swelling:','e');?>   </span>  
   <td><?php xl('within minutes','e');?><input type="radio" name='ankle_onset_of_swelling' value="<?php xl('within minutes','e');?>"></input></td>
   <td><?php xl('within hours','e');?><input type="radio" name='ankle_onset_of_swelling' value="<?php xl('within hours','e');?>"></input></td>
   <br><br>

<span class="text" ><?php xl('How did Injury Occur?','e');?>:</span><br>
<textarea name="ankle_how_did_injury_occur" rows="4" cols="67" wrap="virtual name"></textarea>
<br></br>

<table ><th colspan="5"><?php xl('Ottawa Ankle Rules','e'); ?></th>
<tr>
<td><?php xl('Bone Tenderness: Medial Malleolus','e'); ?><input type="radio" name='ankle_ottawa_bone_tenderness' value="Medial malleolus"></input></td>
<td><?php xl('Lateral Malleolus','e'); ?><input type="radio" name='ankle_ottawa_bone_tenderness' value="Lateral malleolus"></input></td>
<td><?php xl('Base of fifth (5th) Metarsal','e'); ?><input type="radio" name='ankle_ottawa_bone_tenderness' value="Base of fifth (5th) Metarsal"></input></td>
<td><?php xl('At the Navicular','e'); ?><input type="radio" name='ankle_ottawa_bone_tenderness' value="At the Navicular"></input></td>
</tr>   
</table>   
<br>

<span class="text"><?php xl('Able to Bear Weight four (4) steps:','e'); ?></span>  
  <td><?php xl('Yes','e'); ?><input type="radio" name='ankle_able_to_bear_weight_steps' value="<?php xl('Yes','e'); ?>"></input></td>
  <td><?php xl('No','e'); ?><input type="radio" name='ankle_able_to_bear_weight_steps' value="<?php xl('No','e'); ?>"></input></td>
<br>

<table>
<tr><th><?php xl('X-RAY Interpretation:','e'); ?></th> <th><?php xl('Additional X-RAY Notes:','e'); ?></th></tr>
<tr>
  <td>
   <select name="ankle_x_ray_interpretation" >
      <option value="Normal"><?php xl('Normal','e'); ?></option>
      <option value="Avulsion medial malleolus"><?php xl('Avulsion medial malleolus ','e'); ?></option>
      <option value="Avulsion lateral malleolus"><?php xl('Avulsion lateral malleolus','e'); ?></option>
      <option value="Fracture, Base of fifth (5th) Metatarsal"><?php xl('Fracture, Base of fifth (5th) Metatarsal','e'); ?></option>
      <option value="Trimalleolar"><?php xl('Trimalleolar','e'); ?></option>
      <option value="Fracture at the Navicula"><?php xl('Fracture at the Navicula','e'); ?></option>
      <option value="Fracture medial malleolus"><?php xl('Fracture medial malleolus','e'); ?></option>
      <option value="Fracture lateral malleolus"><?php xl('Fracture lateral malleolus','e'); ?></option>
      <option value="Other"><?php xl('Other','e'); ?></option>
      </select>
   </td>

<td rowspan=2>
<textarea cols=35 rows=1 wrap=virtual name="ankle_additional_x_ray_notes" ></textarea>

</td>

</tr>
</table>
 <script language="javascript">
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
<th><?php xl('Diagnosis:','e');?></th><th><?php xl('Additional Diagnosis:','e');?></th>
</tr>
<tr>
<td valign="top"><select name="ankle_diagnosis1" onChange="doCPT(this);">
      <option value=""><?php xl('None','e');?></option>
      <option value="845.00 ankle sprain NOS"><?php xl('845.00 ankle sprain NOS','e');?></option>
      <option value="845.01 Sprain Medial (Deltoid) Lig."><?php xl('845.01 Sprain Medial (Deltoid) Lig.','e');?></option>
      <option value="845.02 Sprain, Calcaneal fibular"><?php xl('845.02 Sprain, Calcaneal fibular','e');?></option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal"><?php xl('825.35 Fracture, Base of fifth (5th) Metatarsal','e');?></option>
      <option value="825.32 Fracture, of Navicular (ankle)"><?php xl('825.32 Fracture, of Navicular (ankle)','e');?></option>
      <option value="824.2 Fracture, lateral malleolus, closed"><?php xl('824.2 Fracture, lateral malleolus, closed','e');?></option>
      <option value="824.0 Fracture, medial malleolus, closed"><?php xl('824.0 Fracture, medial malleolus, closed','e');?></option>
      <option value="824.6 Fracture, Trimalleolar, closed"><?php xl('824.6 Fracture, Trimalleolar, closed','e');?></option>
      <option value="Add ICD Code"><?php xl('Add ICD Code','e');?></option>
    </select>

</td>
<td rowspan="4">
<textarea cols=30 rows=2 wrap=virtual name="ankle_additional_diagnisis" ></textarea>
<br><br>
<table>
<tr>
	<td width="10"></td>
	<td><?php xl('CPT Codes','e');?></td>
	<td></td>
</tr>
<tr>
	<td></td>
	<td colspan="2">
		&nbsp;&nbsp;&nbsp;<input type="radio" name="openemr_net_cptcode" value=""><?php xl('none','e');?><br>
		&nbsp;&nbsp;&nbsp;<input type="radio" name="openemr_net_cptcode" value="99212 Established - Uncomplicated"><?php xl('99212 Established - Uncomplicated','e');?><br>
		&nbsp;&nbsp;&nbsp;<input type="radio" name="openemr_net_cptcode" value="99213 Established - Low Complexity"><?php xl('99213 Established - Low Complexity','e');?><br>
	</td>
</tr>
</table>

</td>

<tr>
<td>
  <select name="ankle_diagnosis2" onChange="doCPT(this);">
      <option value=""><?php xl('None','e') ;?></option>
      <option value="845.00 ankle sprain NOS"><?php xl('845.00 ankle sprain NOS','e') ;?></option>
      <option value="845.01 Sprain Medial (Deltoid) Lig."><?php xl('845.01 Sprain Medial (Deltoid) Lig.','e') ;?></option>
      <option value="845.02 Sprain, Calcaneal fibular"><?php xl('845.02 Sprain, Calcaneal fibular','e') ;?></option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal"><?php xl('825.35 Fracture, Base of fifth (5th) Metatarsal','e') ;?></option>
      <option value="825.32 Fracture, of Navicular (ankle)"><?php xl('825.32 Fracture, of Navicular (ankle)','e') ;?></option>
      <option value="824.2 Fracture, lateral malleolus, closed"><?php xl('824.2 Fracture, lateral malleolus, closed','e') ;?></option>
      <option value="824.0 Fracture, medial malleolus, closed"><?php xl('824.0 Fracture, medial malleolus, closed','e') ;?></option>
      <option value="824.6 Fracture, Trimalleolar, closed"><?php xl('824.6 Fracture, Trimalleolar, closed','e') ;?></option>
      <option value="Add ICD Code"><?php xl('Add ICD Code','e') ;?></option>
    </select>
   </td></tr>
<td>
   <select name="ankle_diagnosis3" onChange="doCPT(this);">
      <option value=""><?php xl('None','e') ;?></option>
      <option value="845.00 ankle sprain NOS"><?php xl('845.00 ankle sprain NOS','e') ;?></option>
      <option value="845.01 Sprain Medial (Deltoid) Lig."><?php xl('845.01 Sprain Medial (Deltoid) Lig.','e') ;?></option>
      <option value="845.02 Sprain, Calcaneal fibular"><?php xl('845.02 Sprain, Calcaneal fibular','e') ;?></option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal"><?php xl('825.35 Fracture, Base of fifth (5th) Metatarsal','e') ;?></option>
      <option value="825.32 Fracture, of Navicular (ankle)"><?php xl('825.32 Fracture, of Navicular (ankle)','e') ;?></option>
      <option value="824.2 Fracture, lateral malleolus, closed"><?php xl('824.2 Fracture, lateral malleolus, closed','e') ;?></option>
      <option value="824.0 Fracture, medial malleolus, closed"><?php xl('824.0 Fracture, medial malleolus, closed','e') ;?></option>
      <option value="824.6 Fracture, Trimalleolar, closed"><?php xl('824.6 Fracture, Trimalleolar, closed','e') ;?></option>
      <option value="Add ICD Code"><?php xl('Add ICD Code','e') ;?></option>
   </select>
   </td>
</tr>
<td>
   <select name="ankle_diagnosis4" onChange="doCPT(this);">
      <option value=""><?php xl('None','e') ;?></option>
      <option value="845.00 ankle sprain NOS"><?php xl('845.00 ankle sprain NOS','e') ;?></option>
      <option value="845.01 Sprain Medial (Deltoid) Lig."><?php xl('845.01 Sprain Medial (Deltoid) Lig.','e') ;?></option>
      <option value="845.02 Sprain, Calcaneal fibular"><?php xl('845.02 Sprain, Calcaneal fibular','e') ;?></option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal"><?php xl('825.35 Fracture, Base of fifth (5th) Metatarsal','e') ;?></option>
      <option value="825.32 Fracture, of Navicular (ankle)"><?php xl('825.32 Fracture, of Navicular (ankle)','e') ;?></option>
      <option value="824.2 Fracture, lateral malleolus, closed"><?php xl('824.2 Fracture, lateral malleolus, closed','e') ;?></option>
      <option value="824.0 Fracture, medial malleolus, closed"><?php xl('824.0 Fracture, medial malleolus, closed','e') ;?></option>
      <option value="824.6 Fracture, Trimalleolar, closed"><?php xl('824.6 Fracture, Trimalleolar, closed','e') ;?></option>
<option value="Add ICD Code"><?php xl('Add ICD Code','e') ;?></option>
</select>
</td>
</tr>
</table>

<table><tr><th><?php xl('Plan:','e'); ?></th><tr>
<tr><td>
<textarea name="ankle_plan" rows="7" cols="67"
wrap="virtual name"><?php xl('1.Rest
2. Ice for two days
3. Compression, leave the dressing in place unless the foot develops numbness or pale color
4. Elevate the foot and leg','e');?>
</textarea>
</td>
</tr>
</table>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" style="color: #483D8B">[<?php xl('Don\'t Save','e');?>]</a>
</form>
<?php
formFooter();
?>
