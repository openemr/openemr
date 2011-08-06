<?php

function high_risk_screening_record_report( $pid, $encounter, $cols, $id) {
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/calendar.inc");
   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");
   if ($fres){
     $patient = sqlFetchArray($fres);
   }
   $fres=sqlStatement("select * from form_high_risk_screening_record where id=$id");
   if ($fres){
     $fdata = sqlFetchArray($fres);
   }
?>
<body <?echo $top_bg_line;?>>

<div class="srvChapter">High risk screening record* </div>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table  border="0" cellpadding="2" cellspacing="0">
    <tr align="left" valign="bottom">
      <td colspan="6" class="fibody2" id="bordR">Patient name: <?php  echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};  ?></td>
      <td colspan="5" class="fibody2" id="bordR">Birth date: <?php  echo $patient{'DOB'}; ?></td>
      <td colspan="4" class="fibody2">ID No: <?php echo $patient{'id'}; ?></td>
      </tr>
    <tr align="center" valign="middle">
      <td class="ficaption2" id="bordR">&nbsp;</td>
      <td width="6%" class="ficaption2" id="bordR">HEMO-<br>
        GLOBIN<br>
        TEST</td>
      <td width="6%" class="ficaption2" id="bordR">BONE DENSITY<br>
        SCREENING</td>
      <td width="6%" class="ficaption2" id="bordR">BACTERI-<br>
        URIA&nbsp;TEST</td>
      <td width="6%" class="ficaption2" id="bordR">STD<br>
        TESTING</td>
      <td width="6%" class="ficaption2" id="bordR">HIV test** </td>
      <td width="6%" class="ficaption2" id="bordR">Genetic testing </td>
      <td width="6%" class="ficaption2" id="bordR">Rubella titer </td>
      <td width="6%" class="ficaption2" id="bordR">TB skin test </td>
      <td width="6%" class="ficaption2" id="bordR">Lipid profile assessment </td>
      <td width="6%" class="ficaption2" id="bordR">Mammo-<br>
        graphy</td>
      <td width="6%" class="ficaption2" id="bordR">Fasting<br>
        GLUCOSE TEST</td>
      <td width="6%" class="ficaption2" id="bordR">TSH<br>
        TEST</td>
      <td width="6%" class="ficaption2" id="bordR">COLORECTAL<br>
        CANCER<br>
        SCREENING</td>
      <td width="6%" class="ficaption2">HEPATITIS C<br>
        VIRUS TEST</td>
    </tr>
<?	
$rsi = 1;
while ($rsi < 13){
list(
$hemoglobin, $bone_density, $bacteriuria, $std, $hiv, $genetic, $rubella, $tb_skin,
$lipid, $mammography, $fasting_glucose, $tsh, $cancer, $hepatitis_c
) = explode("|~",$fdata["record_".$rsi]);
list( $hemoglobin_date, $hemoglobin_res) = explode(';', $hemoglobin);
list( $bone_density_date, $bone_density_res) = explode(';', $bone_density);
list( $bacteriuria_date, $bacteriuria_res) = explode(';', $bacteriuria);
list( $std_date, $std_res) = explode(';', $std);
list( $hiv_date, $hiv_res) = explode(';', $hiv);
list( $genetic_date, $genetic_res) = explode(';', $genetic);
list( $rubella_date, $rubella_res) = explode(';', $rubella);
list( $tb_skin_date, $tdb_skin_res) = explode(';', $tdb_skin);
list( $lipid_date, $lipid_res) = explode(';', $lipid);
list( $mammography_date, $mammography_res) = explode(';', $mammography);
list( $fasting_glucose_date, $fasting_glucose_res) = explode(';', $fasting_glucose);
list( $tsh_date, $tsh_res) = explode(';', $tsh);
list( $cancer_date, $cancer_res) = explode(';', $cancer);
list( $hepatitis_c_date, $hepatitis_c_res) = explode(';', $hepatitis_c);

print <<<EOL
    <tr align="left" valign="bottom">
      <td class="fibody3" id="bordR">Date:</td>
      <td class="fibody3" id="bordR">${hemoglobin_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${bone_density_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${bacteriuria_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${std_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${hiv_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${genetic_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${rubella_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${tb_skin_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${lipid_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${mammography_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${fasting_glucose_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${tsh_date}&nbsp;</td>
      <td class="fibody3" id="bordR">${cancer_date}&nbsp;</td>
      <td class="fibody3">${hepatitis_c_date}&nbsp;</td>
    </tr>
    <tr align="left" valign="bottom">
      <td class="fibody2" id="bordR">Result:</td>
      <td class="fibody2" id="bordR">${hemoglobin_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${bone_density_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${bacteriuria_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${std_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${hiv_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${genetic_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${rubella_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${tb_skin_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${lipid_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${mammography_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${fasting_glucose_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${tsh_res}&nbsp;</td>
      <td class="fibody2" id="bordR">${cancer_res}&nbsp;</td>
      <td class="fibody2">${hepatitis_c_res}&nbsp;</td>
    </tr>
EOL;
$rsi++;
}
?>
  </table>
</div>
<p><sup>*</sup>See Table of High-Risk Factors.</p>
<p><sup>**</sup>Check state requirements before recording results.</p>
<?php } ?>