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
<span class="title">Ankle Evaluation Form</span><br></br>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" style="color: #483D8B">[Don't Save]</a>
<br></br>

<span class="text" >Date of Injury: </span><input type="entry" name="ankle_date_of_injuary" value=""></input>
<tr>
<td width="120" align="right">Work related?</td>
<td><input type="checkbox" name="ankle_work_related"></input></td>
</tr>
<br><br>

<span class="text">Foot: </span>  
   <td>Left<input type="radio" name='ankle_foot' value="Left"></input></td>
   <td>Right<input type="radio" name='ankle_foot' value="Right"></input></td>
<br><br>
   
<span class="text">Severity of Pain:   </span>  
   <td>1<input type="radio" name='ankle_severity_of_pain' value="1"></input></td>
   <td>2<input type="radio" name='ankle_severity_of_pain' value="2"></input></td>
   <td>3<input type="radio" name='ankle_severity_of_pain' value="3"></input></td>
<br><br>
 
<td width="140">Significant Swelling:</td>
<td align="right"></td>
<td><input type="checkbox" name="ankle_significant_swelling"></input></td>
<br><br>

<span class="text">Onset of Swelling:   </span>  
   <td>within minutes<input type="radio" name='ankle_onset_of_swelling' value="within minutes"></input></td>
   <td>within hours<input type="radio" name='ankle_onset_of_swelling' value="within hours"></input></td>
   <br><br>

<span class="text" >How did Injury Occur?:</span><br>
<textarea name="ankle_how_did_injury_occur" rows="4" cols="67" wrap="virtual name"></textarea>
<br></br>

<table ><th colspan="5">Ottawa Ankle Rules</th>
<tr>
<td>Bone Tenderness: Medial Malleolus<input type="radio" name='ankle_ottawa_bone_tenderness' value="Medial malleolus"></input></td>
<td>Lateral Malleolus<input type="radio" name='ankle_ottawa_bone_tenderness' value="Lateral malleolus"></input></td>
<td>Base of fifth (5th) Metarsal<input type="radio" name='ankle_ottawa_bone_tenderness' value="Base of fifth (5th) Metarsal"></input></td>
<td>At the Navicular<input type="radio" name='ankle_ottawa_bone_tenderness' value="At the Navicular"></input></td>
</tr>   
</table>   
<br>

<span class="text">Able to Bear Weight four (4) steps:</span>  
  <td>Yes<input type="radio" name='ankle_able_to_bear_weight_steps' value="Yes"></input></td>
  <td>No<input type="radio" name='ankle_able_to_bear_weight_steps' value="No"></input></td>
<br>

<table>
<tr><th>X-RAY Interpretation:</th> <th>Additional X-RAY Notes:</th></tr>
<tr>
  <td>
   <select name="ankle_x_ray_interpretation" >
      <option value="Normal">Normal</option>
      <option value="Avulsion medial malleolus">Avulsion medial malleolus </option>
      <option value="Avulsion lateral malleolus">Avulsion lateral malleolus</option>
      <option value="Fracture, Base of fifth (5th) Metatarsal">Fracture, Base of fifth (5th) Metatarsal</option>
      <option value="Trimalleolar">Trimalleolar</option>
      <option value="Fracture at the Navicula">Fracture at the Navicula</option>
      <option value="Fracture medial malleolus">Fracture medial malleolus</option>
      <option value="Fracture lateral malleolus">Fracture lateral malleolus</option>
      <option value="Other">Other</option>
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
<th>Diagnosis:</th><th>Additional Diagnosis:</th>
</tr>
<tr>
<td valign="top"><select name="ankle_diagnosis1" onChange="doCPT(this);">
      <option value="">None</option>
      <option value="845.00 ankle sprain NOS">845.00 ankle sprain NOS</option>
      <option value="845.01 Sprain Medial (Deltoid) Lig.">845.01 Sprain Medial (Deltoid) Lig.</option>
      <option value="845.02 Sprain, Calcaneal fibular">845.02 Sprain, Calcaneal fibular</option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal">825.35 Fracture, Base of fifth (5th) Metatarsal</option>
      <option value="825.32 Fracture, of Navicular (ankle)">825.32 Fracture, of Navicular (ankle)</option>
      <option value="824.2 Fracture, lateral malleolus, closed">824.2 Fracture, lateral malleolus, closed</option>
      <option value="824.0 Fracture, medial malleolus, closed">824.0 Fracture, medial malleolus, closed</option>
      <option value="824.6 Fracture, Trimalleolar, closed">824.6 Fracture, Trimalleolar, closed</option>
      <option value="Add ICD Code">Add ICD Code</option>
    </select>

</td>
<td rowspan="4">
<textarea cols=30 rows=2 wrap=virtual name="ankle_additional_diagnisis" ></textarea>
<br><br>
<table>
<tr>
	<td width="10"></td>
	<td>CPT Codes</td>
	<td></td>
</tr>
<tr>
	<td></td>
	<td colspan="2">
		&nbsp;&nbsp;&nbsp;<input type="radio" name="openemr_net_cptcode" value="">none<br>
		&nbsp;&nbsp;&nbsp;<input type="radio" name="openemr_net_cptcode" value="99212 Established - Uncomplicated">99212 Established - Uncomplicated<br>
		&nbsp;&nbsp;&nbsp;<input type="radio" name="openemr_net_cptcode" value="99213 Established - Low Complexity">99213 Established - Low Complexity<br>
	</td>
</tr>
</table>

</td>

<tr>
<td>
  <select name="ankle_diagnosis2" onChange="doCPT(this);">
      <option value="">None</option>
      <option value="845.00 ankle sprain NOS">845.00 ankle sprain NOS</option>
      <option value="845.01 Sprain Medial (Deltoid) Lig.">845.01 Sprain Medial (Deltoid) Lig.</option>
      <option value="845.02 Sprain, Calcaneal fibular">845.02 Sprain, Calcaneal fibular</option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal">825.35 Fracture, Base of fifth (5th) Metatarsal</option>
      <option value="825.32 Fracture, of Navicular (ankle)">825.32 Fracture, of Navicular (ankle)</option>
      <option value="824.2 Fracture, lateral malleolus, closed">824.2 Fracture, lateral malleolus, closed</option>
      <option value="824.0 Fracture, medial malleolus, closed">824.0 Fracture, medial malleolus, closed</option>
      <option value="824.6 Fracture, Trimalleolar, closed">824.6 Fracture, Trimalleolar, closed</option>
      <option value="Add ICD Code">Add ICD Code</option>
    </select>
   </td></tr>
<td>
   <select name="ankle_diagnosis3" onChange="doCPT(this);">
      <option value="">None</option>
      <option value="845.00 ankle sprain NOS">845.00 ankle sprain NOS</option>
      <option value="845.01 Sprain Medial (Deltoid) Lig.">845.01 Sprain Medial (Deltoid) Lig.</option>
      <option value="845.02 Sprain, Calcaneal fibular">845.02 Sprain, Calcaneal fibular</option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal">825.35 Fracture, Base of fifth (5th) Metatarsal</option>
      <option value="825.32 Fracture, of Navicular (ankle)">825.32 Fracture, of Navicular (ankle)</option>
      <option value="824.2 Fracture, lateral malleolus, closed">824.2 Fracture, lateral malleolus, closed</option>
      <option value="824.0 Fracture, medial malleolus, closed">824.0 Fracture, medial malleolus, closed</option>
      <option value="824.6 Fracture, Trimalleolar, closed">824.6 Fracture, Trimalleolar, closed</option>
      <option value="Add ICD Code">Add ICD Code</option>
   </select>
   </td>
</tr>
<td>
   <select name="ankle_diagnosis4" onChange="doCPT(this);">
      <option value="">None</option>
      <option value="845.00 ankle sprain NOS">845.00 ankle sprain NOS</option>
      <option value="845.01 Sprain Medial (Deltoid) Lig.">845.01 Sprain Medial (Deltoid) Lig.</option>
      <option value="845.02 Sprain, Calcaneal fibular">845.02 Sprain, Calcaneal fibular</option>
      <option value="825.35 Fracture, Base of fifth (5th) Metatarsal">825.35 Fracture, Base of fifth (5th) Metatarsal</option>
      <option value="825.32 Fracture, of Navicular (ankle)">825.32 Fracture, of Navicular (ankle)</option>
      <option value="824.2 Fracture, lateral malleolus, closed">824.2 Fracture, lateral malleolus, closed</option>
      <option value="824.0 Fracture, medial malleolus, closed">824.0 Fracture, medial malleolus, closed</option>
      <option value="824.6 Fracture, Trimalleolar, closed">824.6 Fracture, Trimalleolar, closed</option>
<option value="Add ICD Code">Add ICD Code</option>
</select>
</td>
</tr>
</table>

<table><tr><th>Plan:</th><tr>
<tr><td>
<textarea name="ankle_plan" rows="7" cols="67"
wrap="virtual name">1.Rest
2. Ice for two days
3. Compression, leave the dressing in place unless the foot develops numbness or pale color
4. Elevate the foot and leg
</textarea>
</td>
</tr>
</table>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" style="color: #483D8B">[Don't Save]</a>
</form>
<?php
formFooter();
?>
