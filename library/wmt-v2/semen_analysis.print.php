<?php
if($analysis_done) {
	$chp_printed = PrintChapter('Specimen Analysis',$chp_printed);
	PrintTwoColumn('Parameter', 'Result', 'Post Wash', 'Result', 'width: 50%;');
	PrintTwoColumn('Appearance:', ListLook($analysis{'anl_appear'},'Semen_Appearance'), 'Volume:', $analysis{'anl_wash_volume'}.'&nbsp;mL');
	PrintTwoColumn('Liquefaction:', $analysis{'anl_liq'}.'&nbsp;(&lt;60&nbsp;min)', 'Concentration:', $analysis{'anl_wash_form'}.'&nbsp;M/mL');
	$abn = '';
	if($analysis['anl_visc'] >= 3) $abn = '&nbsp;&nbsp;&nbsp;&nbsp;<b>* ABNORMAL *</b>';
	PrintTwoColumn('Viscosity:', ListLook($analysis{'anl_visc'},'Zero_To_Three').'&nbsp(1,2)'.$abn, 'Motility:', $analysis{'anl_wash_mot'}.'&nbsp;%');
	$abn = '';
	if($analysis['anl_color'] == 'r' || $analysis['anl_color'] == 'b') $abn = '&nbsp;&nbsp;&nbsp;&nbsp;<b>* ABNORMAL *</b>';
	$abn2 = '';
	if($analysis['anl_wash_prog'] == '1') $abn2 = '&nbsp;&nbsp;&nbsp;&nbsp;<b>* ABNORMAL *</b>';
	PrintTwoColumn('Color:', ListLook($analysis{'anl_color'},'Semen_Color').'&nbsp;&nbsp;(gray/white, yellow)'.$abn, 'Progression:', ListLook($analysis{'anl_wash_prog'},'One_To_Three').$abn2);
	PrintTwoColumn('Volume:',$analysis{'anl_volume'}.'&nbsp;mL&nbsp;(1.5-5.0 mL)','TMS:',$analysis{'anl_wash_tms'}.'&nbsp;M');
	PrintTwoColumn('pH:',$analysis{'anl_ph'}.'&nbsp;(&gt;7.2)','Recovery Rate:',$analysis{'anl_wash_rate'}.'&nbsp;(&gt; 8% expected)');
	PrintTwoColumn('Count 1:',$analysis{'anl_cnt1'});
	PrintTwoColumn('Count 2:',$analysis{'anl_cnt2'}.'&nbsp;M/mL&nbsp;(&gt;20 M/mL)');
	PrintTwoColumn('Concentration:',$analysis{'anl_form'},'Inventory Used','');
	PrintTwoColumn('Fructose:',$analysis{'anl_fruc'},'Gradient:',$analysis{'anl_grad'});
	PrintTwoColumn('Total Sperm Count (TSC):',$analysis{'anl_tsc'}.'&nbsp;M&nbsp;(&gt;39&nbsp;M)','Gradient Lot #:',$analysis{'anl_grad_lot'});
	PrintTwoColumn('Motility:',$analysis{'anl_mot'}.'&nbsp;%&nbsp;(&gt;40%)','Wash Medium:',$analysis{'anl_medium'});
	$abn = '';
	if($analysis['anl_prog'] == 1) $abn = '&nbsp;&nbsp;&nbsp;&nbsp;<b>* ABNORMAL *</b>';
	PrintTwoColumn('Progression:',ListLook($analysis{'anl_prog'},'One_To_Three').'&nbsp;(3)'.$abn, 'Medium Lot #:', $analysis{'anl_medium_lot'});
	PrintTwoColumn('Viability:',$analysis{'anl_via'});
	PrintTwoColumn('Total Motile Sperm (TMS):',$analysis{'anl_tms'}.'&nbsp;M&nbsp;(&gt;15.6&nbsp;M)');
	$abn = '';
	if($analysis['anl_agg'] >= 2) $abn = '&nbsp;&nbsp;&nbsp;&nbsp;<b>* ABNORMAL *</b>';
	PrintTwoColumn('Agglutination:',ListLook($analysis{'anl_agg'},'Zero_To_Three').'&nbsp;(0,1,2)'.$abn);
	PrintTwoColumn('Round Cells:',$analysis{'anl_round'}.'&nbsp;M/mL&nbsp;(&lt;&nbsp;10&nbsp;M/mL)');
	PrintTwoColumn('Leukocytes:',$analysis{'anl_leuk'}.'&nbsp;M&nbsp;(&lt;1&nbsp;M/mL)');
	PrintTwoColumn('Morphology:',$analysis{'anl_morph'}.'&nbsp;%&nbsp;(&gt;5%&nbsp;Kruger)');
	PrintTwoColumn('Stain Acceptable:',ListLook($analysis{'anl_accept'},'YesNo'));
	if($analysis{'anl_defect'}) PrintSingleLine('Major Defect:',$analysis{'anl_defect'},2);
	PrintTwoColumn('GII:',$analysis{'anl_gii'}.'&nbsp;%');
	PrintTwoColumn('GIII:', $analysis{'anl_giii'}.'&nbsp;%');
	PrintTwoColumn('DNA Fragmentation:',ListLook($analysis{'anl_dna'},'YesNo'));
	PrintOverhead('Comments:',$analysis{'anl_note'}, 2);
	if($analysis{'anl_cells'}) {
		PrintSingleLine('Due to the presence of cell clumps/debris in the semen specimen, the results reported may be inaccurate.','',2);
	}
	if($analysis{'anl_decrease'}) {
		PrintSingleLine('Decreased sperm motility may be a result of no-viable or non-motile sperm. Clinical correlation required.','',2);
	}
} else {
	$chp_printed = PrintChapter('Specimen Analysis',$chp_printed);
	PrintSingleLine('Not On File');
}

$nt = trim($analysis{'anl_msg_trail'});
if($nt) {
	$chp_printed = PrintChapter('Specimen Analysis',$chp_printed);
	PrintOverhead('Messages Sent:',$nt,2);
}
?>
