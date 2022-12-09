<?php
$_print= false;
if(!isset($chp_title)) $chp_title = 'Obstetrical History';
if(!isset($GLOBALS['wmt::show_ob_totals'])) $GLOBALS['wmt::show_ob_totals'] = 1;
$obhist = getPastPregnancies($pid, $encounter);
if(isset($obhist) && (count($obhist) > 0)) $_print= true;
if($GLOBALS['wmt::show_ob_totals'] && 
	($dt['db_pregnancies'] || $dt['db_deliveries'] || $dt['db_live_births'])) 
			$_print= true;
if($_print) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	echo "		<tr>\n";
	echo "       <td style='width: 8%' class='wmtPrnLabel3Center'>Date</td>\n";
	echo "       <td style='width: 8%' class='wmtPrnLabel3CenterBorderL'>Conception</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderL' style='width: 6%'>GA</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB' style='width: 6%' rowspan='2'>Length of Labor</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderL' colspan='2'>Birth Weight</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderL' style='width: 6%'>Sex</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB' style='width: 8%' rowspan='2'>";
	echo ($client_id == 'mcrm' || substr($client_id,-3) == '_oh') ? 'Outcome' : 'Delivery Type';
	echo "</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB' style='width: 9%' rowspan='2'>Anesthesia</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB' style='width: 15%' rowspan='2'>Place of Delivery</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderL' style='width: 8%'>Preterm</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB' rowspan='2'>Doctor</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderL'>";
	echo ($client_id == 'mcrm') ? 'Complications' : 'Comments';
	echo " /</td>\n";
	echo "     </tr>\n";
	echo "     <tr>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderB'>YYYY-MM</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB'>Method</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB'>Weeks</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB' style='width: 4%'>lb.</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderB' style='width: 4%'>oz.</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB'>M/F</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB'>Labor</td>\n";
	echo "       <td class='wmtPrnLabel3CenterBorderLB'>";
	echo ($client_id == 'mcrm') ? 'Medications' : 'Complications';
	echo "</td>\n";
	echo "		</tr>\n";
	foreach($obhist as $preg) {
  	echo "		<tr>\n";
  	echo "  		<td class='wmtPrnBody3BorderB'>",$preg['pp_date_of_pregnancy'],"&nbsp;</td>\n";
  	echo "			<td class='wmtPrnBody3BorderLB'>",ListLook($preg['pp_conception'],'PP_Conception'),"&nbsp;</td>\n";
  	echo "  		<td class='wmtPrnBody3BorderLB'>",$preg['pp_ga_weeks'],"&nbsp;</td>\n";
  	echo "  		<td class='wmtPrnBody3BorderLB'>",$preg['pp_labor_length'],"&nbsp;</td>\n"; 
  	echo "  		<td class='wmtPrnBody3BorderLB'>",$preg['pp_weight_lb'],"&nbsp;</td>\n"; 
  	echo "	 		<td class='wmtPrnBody3BorderLB'>",$preg['pp_weight_oz'],"&nbsp;</td>\n";
  	echo "			<td class='wmtPrnBody3BorderLB'>",ListLook($preg['pp_sex'],'PP_Sex'),"&nbsp;</td>\n";
  	echo "  		<td class='wmtPrnBody3BorderLB'>",ListLook($preg['pp_delivery'],'PP_Delivery'),"&nbsp;</td>\n";
  	echo "  		<td class='wmtPrnBody3BorderLB'>",ListLook($preg['pp_anes'],'PP_Anesthesia'),"&nbsp;</td>\n";
  	echo " 		  <td class='wmtPrnBody3BorderLB'>",$preg['pp_place'],"&nbsp;</td>\n";
  	echo "		  <td class='wmtPrnBody3BorderLB'>",ListLook($preg['pp_preterm'],'PP_Preterm'),"&nbsp;</td>\n";
  	echo "  		<td class='wmtPrnBody3BorderLB'>",$preg['pp_doc'],"&nbsp;</td>\n";
  	echo "  		<td class='wmtPrnBody3BorderLB'>",$preg['pp_comment'],"&nbsp;</td>\n";
  	echo "		</tr>\n";
	}
	if($show_ob_totals) {
		echo "			<tr>\n";
		echo "				<td class='wmtPrnLabel wmtPrnR' colspan='4' style='border-top: solid 1px black'>Total Pregnancies:&nbsp;&nbsp;";
		echo $dt{'db_pregnancies'};
		echo "</td>\n";
		echo "				<td style='border-top: solid 1px black;'>&nbsp;</td>\n";
		if($client_id != 'sfa') {
			echo "				<td colspan='2' style='border-top: solid 1px black'>&nbsp;</td>\n";
		}
		echo "				<td class='wmtPrnLabel wmtPrnC' colspan='3' style='border-top: solid 1px black'>Total Deliveries:&nbsp;&nbsp;";
		echo $dt{'db_deliveries'};
		echo "</td>\n";
		if($client_id == 'sfa') {
			echo "				<td style='border-top: solid 1px black;'>&nbsp;</td>\n";
			echo "				<td class='wmtPrnLabel' colspan='3' style='border-top: solid 1px black'>Living Children:&nbsp;&nbsp;";
			echo $dt{'db_live_births'};
			echo "</td>\n";
		} else {
			echo "				<td colspan='2' style='border-top: solid 1px black;'>&nbsp;</td>\n";
		}
		echo "			</tr>\n";
	}
	if($dt['fyi_pp_nt']) {
		echo "			<tr>\n";
		echo "				<td class='wmtPrnLabel' colspan='13' style='border-top: solid 1px black'>Other Notes:</td>\n";
		echo "			</tr>\n";
		echo "			<tr>\n";
		echo "				<td class='wmtPrnBody' colspan='12'>",$dt['fyi_pp_nt'],"</td>\n";
		echo "			</tr>\n";
	}
}
// if(!$printed) {
// 	echo "		<tr>\n";
//  echo "  		<td class='wmtPrnBody3BorderB'>None</td>\n";
//  echo "  		<td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
// 	echo "  		<td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
// 	echo "  		<td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
// 	echo "	 		<td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
// 	echo "			<td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
// 	echo "  		<td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
// 	echo "  		<td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
// 	echo " 		  <td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
// 	echo "		  <td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
// 	echo "  		<td class='wmtPrnBody3BorderLB'>&nbsp;</td>\n";
//	echo "		</tr>\n";
//}
?>
