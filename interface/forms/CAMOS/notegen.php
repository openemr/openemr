<?
$depth = '../../../';
include_once ($depth.'interface/globals.php');
include_once ($depth.'library/classes/class.ezpdf.php');
?>
<?
if (!$_POST['submit'] && !($_GET['pid'] && $_GET['encounter'])) {
?>
<html>
<head>
<title>
<?php xl('Print Notes','e'); ?>
</title>
<style type="text/css">@import url('<?php echo $depth ?>library/dynarch_calendar.css');</style>
<script type="text/javascript" src="<?php echo $depth ?>library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $depth ?>library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $depth ?>library/dynarch_calendar.js"></script>
<script type="text/javascript" src="<?php echo $depth ?>library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="<?php echo $depth ?>library/dynarch_calendar_setup.js"></script>
</head>

<body>
<script language='JavaScript'> var mypcc = '1'; </script>

<form method=post name=choose_patients> 

<table>
<tr><td>
<span class='text'><?php xl('Start (yyyy-mm-dd): ','e') ?></span>
</td><td>
<input type='text' size='10' name='start' id='start' value='<? echo $_POST['end'] ? $_POST['end'] : date('Y-m-d') ?>' 
onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
title='<?php xl('yyyy-mm-dd last date of this event','e'); ?>' />
<img src='<?php echo $depth ?>interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
id='img_start' border='0' alt='[?]' style='cursor:pointer'
title='<?php xl('Click here to choose a date','e'); ?>'>
<script>
Calendar.setup({inputField:'start', ifFormat:'%Y-%m-%d', button:'img_start'});
</script>
</td></tr>

<tr><td>
<span class='text'><?php xl('End (yyyy-mm-dd): ','e') ?></span>
</td><td>
<input type='text' size='10' name='end' id='end' value ='<? echo $_POST['end'] ? $_POST['end'] : date('Y-m-d') ?>' 
onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
title='<?php xl('yyyy-mm-dd last date of this event','e'); ?>' />
<img src='<?php echo $depth ?>interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
id='img_end' border='0' alt='[?]' style='cursor:pointer'
title='<?php xl('Click here to choose a date','e'); ?>'>
<script>
Calendar.setup({inputField:'end', ifFormat:'%Y-%m-%d', button:'img_end'});
</script>
</td></tr>
<tr><td></td><td></td></tr>
<tr><td><?php xl('Last Name','e'); ?>: </td><td>
<input type='text' name='lname'/> 
</td></tr>
<tr><td><?php xl('First Name','e'); ?>: </td><td>
<input type='text' name='fname'/> 
</td></tr>
<tr><td>
<input type='submit' name='submit' value='<?php xl('Submit','e'); ?>'>
</td><td>
</td></tr>
</table>
</form>
</body>
</html>
<?
}
if ($_POST['submit'] || ($_GET['pid'] && $_GET['encounter'])) {
  	$pdf =& new Cezpdf();
	$pdf->selectFont($depth.'library/fonts/Helvetica');
	$pdf->ezSetCmMargins(3,1,1,1);
	$output = getFormData($_POST['start'],$_POST['end'],$_POST['lname'],$_POST['fname']);
	ksort($output);
	$first = 1;
	foreach ($output as $datekey => $dailynote) {
		foreach ($dailynote as $note_id => $notecontents) {
			preg_match('/(\d+)_(\d+)/', $note_id, $matches); //the unique note id contains the pid and encounter
			$pid = $matches[1];
			$enc = $matches[2];
			if (!$first) { //generate a new page each time except first iteration when nothing has been printed yet
				$pdf->ezNewPage();
			}
			else {
				$first = 0;
			}
			$pdf->ezText(xl("Date").": ".$notecontents['date'],8);
			$pdf->ezText(xl("Name").": ".$notecontents['name'],8);
//			$pdf->ezText("ID: ".$note_id,8);

			$query = sqlStatement("select pubpid from patient_data where id=".$_GET['pid']);
			if ($results = mysql_fetch_array($query, MYSQL_ASSOC)) {
				$pubpid = $results['pubpid'];
			}
			$pdf->ezText(xl("Claim")."# ".$pubpid,8);

			$pdf->ezText("",8);
			$pdf->ezText(xl("Chief Complaint").": ".$notecontents['reason'],8);
			if ($notecontents['vitals']) {
				$pdf->ezText("",8);
				$pdf->ezText($notecontents['vitals'],8);
			}
			if (count($notecontents['exam']) > 0) {
				$pdf->ezText("",8);
				$pdf->ezText(xl("Progress Notes"),12);
				$pdf->ezText("",8);
				foreach($notecontents['exam'] as $examnote) {
					$pdf->ezText("$examnote");
				}
			}
			if (count($notecontents['prescriptions']) > 0) {
				$pdf->ezText("",8);
				$pdf->ezText(xl("Prescriptions"),12);
				$pdf->ezText("",8);
				foreach($notecontents['prescriptions'] as $rx) {
					$pdf->ezText($rx);
				}
			}
			if (count($notecontents['other']) > 0) {
				$pdf->ezText("",8);
				$pdf->ezText("Other",12);
				$pdf->ezText("",8);
				foreach($notecontents['other'] as $other => $othercat) {
					$pdf->ezText($other,8);
					foreach($othercat as $items) {
						$pdf->ezText($items,8);
					}
				}
			}
			if (count($notecontents['billing']) > 0) {
				$tmp = array();
				foreach($notecontents['billing'] as $code) {
					$tmp[$code]++;
				}
				if (count($tmp) > 0) {
					$pdf->ezText("",8);
					$pdf->ezText(xl("Coding"),12);
					$pdf->ezText("",8);
					foreach($tmp as $code => $val) {
						$pdf->ezText($code,8);
					}
				}
			}
			if (count($notecontents['calories']) > 0) {
				$sum = 0;
				$pdf->ezText("",8);
				$pdf->ezText(xl("Calories"),12);
				$pdf->ezText("",8);
				foreach($notecontents['calories'] as $calories => $value) {
					$pdf->ezText($value['content'].' - '.$value['item'].' - '.$value['date'],8);
					$sum += $value['content'];
				}
				$pdf->ezText("--------",8);
				$pdf->ezText($sum,8);
			}
			$pdf->ezText("",12);
			$pdf->ezText("",12);
			$pdf->ezText(xl("Digitally Signed"),12);

			$query = sqlStatement("select t2.id, t2.fname, t2.lname, t2.title from forms as t1 join users as t2 on " .
				"(t1.user like t2.username) where t1.pid=$pid and t1.encounter=$encounter");
			if ($results = mysql_fetch_array($query, MYSQL_ASSOC)) {
				$name = $results['fname']." ".$results['lname'].", ".$results['title'];
				$user_id = $results['id'];
			}
			$path = $GLOBALS['fileroot']."/interface/forms/CAMOS";
			if (file_exists($path."/sig".$user_id.".jpg")) {
				$pdf->ezImage($path."/sig".$user_id.".jpg",'','72','','left','');
			}
			$pdf->ezText($name,12);
		}
	}
	$pdf->ezStream();
}
function getFormData($start_date,$end_date,$lname,$fname) { //dates in sql format
	$lname = trim($lname);
	$fname = trim($fname);
	$name_clause = '';
	$date_clause = "date(t2.date) >= '".$start_date."' and date(t2.date) <= '".$end_date."' ";
	if ($lname || $fname) {
		$name_clause = "and t3.lname like '%".$lname."%' and t3.fname like '%".$fname."%' ";
	}
	$dates = array();
	if ($_GET['pid'] && $_GET['encounter']) {
		$date_clause = '';
		$name_clause = "t2.pid=".$_GET['pid']." and t2.encounter=".$_GET['encounter']." ";
	}
	$query1 = sqlStatement(
		"select t1.form_id, t1.form_name, t1.pid, date_format(t2.date,'%m-%d-%Y') as date, " .
		"date_format(t2.date,'%Y%m%d') as datekey, " .
		"t3.lname, t3.fname, date_format(t3.DOB,'%m-%d-%Y') as dob, " .
		"t2.encounter as enc, " .
	      	"t2.reason from " .
		"forms as t1 join " .
		"form_encounter as t2 on " .
		"(t1.pid = t2.pid and t1.encounter = t2.encounter) " . 
		"join patient_data as t3 on " .
		"(t1.pid = t3.pid) where " .
		$date_clause .
		$name_clause .
		"order by date,pid");
	while ($results1 = mysql_fetch_array($query1, MYSQL_ASSOC)) {
		if (!$dates[$results1['datekey']]) {
			$dates[$results1['datekey']] = array();
		}
		if (!$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]) {
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']] = array();
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['name'] = $results1['fname'].' '.$results1['lname'];
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['date'] = $results1['date'];
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['dob'] = $results1['dob'];
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['vitals'] = '';
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['reason'] = $results1['reason'];
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['exam'] = array();
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['prescriptions'] = array();
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['other'] = array();
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['billing'] = array();
			$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['calories'] = array();
		}
		// get icd9 codes for this encounter
		$query2 = sqlStatement("select * from billing where encounter = ".
			$results1['enc']." and pid = ".$results1['pid']." and code_type like 'ICD9' and activity=1");
                while ($results2 = mysql_fetch_array($query2, MYSQL_ASSOC)) {
			array_push($dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['billing'],
				$results2['code'].' '.$results2['code_text']);
		}
		if (strtolower($results1['form_name']) == 'vitals') { // deal with Vitals
			$query2 = sqlStatement("select * from form_vitals where id = " .
			    	$results1['form_id']);	
	                if ($results2 = mysql_fetch_array($query2, MYSQL_ASSOC)) {
				$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['vitals'] = formatVitals($results2);
			}
		}
		if (substr(strtolower($results1['form_name']),0,5) == 'camos') { // deal with camos
			$query2 = sqlStatement("select category,subcategory,item,content,date_format(date,'%h:%i %p') as date from form_CAMOS where id = " .
			    	$results1['form_id']);	
	                if ($results2 = mysql_fetch_array($query2, MYSQL_ASSOC)) {
				if ($results2['category'] == 'exam') {
					array_push($dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['exam'],$results2['content']);
				}
				elseif ($results2['category'] == 'prescriptions') {
					array_push($dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['prescriptions'],preg_replace("/\n+/",' ',$results2['content'])); 
				}
				elseif ($results2['category'] == 'communications') {
					//do nothing
				}
				elseif ($results2['category'] == 'calorie intake') {
					$values = array('subcategory' => $results2['subcategory'],
						'item' => $results2['item'],
						'content' => $results2['content'],
						'date' => $results2['date']);
					array_push($dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['calories'],$values);
					
				}
				else {
					if (!$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['other'][$results2['category']]) {
						$dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['other'][$results2['category']] = array();
					}
					array_push($dates[$results1['datekey']][$results1['pid'].'_'.$results1['enc']]['other'][$results2['category']],
						preg_replace(array("/\n+/","/patientname/i"),array(' ',$results1['fname'].' '.$results1['lname']),$results2['content']));
				}
			}
		}
	}
	return $dates;
}
function formatVitals($raw) { //pass raw vitals array, format and return as string
	$height = '';
	$weight = '';
	$bmi = '';
	$temp= '';
	$bp = '';
	$pulse = '';
	$respiration = '';
	$oxygen_saturation = '';
	if ($raw['height'] && $raw['height'] > 0) {
		$height = xl("HT").": ".$raw['height']." ";
	}
	if ($raw['weight'] && $raw['weight'] > 0) {
		$weight = xl("WT").": ".$raw['weight']." ";
	}
	if ($raw['BMI'] && $raw['BMI'] > 0) {
		$bmi = xl("BMI").": ".$raw['BMI']." ";
	}
	if ($raw['temperature'] && $raw['temperature'] > 0) {
		$temp = xl("Temp").": ".$raw['temperature']." ";
	}
	if ($raw['bps'] && $raw['bpd'] && $raw['bps'] > 0 && $raw['bpd'] > 0) {
		$bp = xl("BP").": ".$raw['bps']."/".$raw['bpd']." ";
	}
	if ($raw['pulse'] && $raw['pulse'] > 0) {
		$pulse = xl("Pulse").": ".$raw['pulse']." ";
	}
	if ($raw['respiration'] && $raw['respiration'] > 0) {
		$respiration = xl("Respiration").": ".$raw['respiration']." ";
	}
	if ($raw['oxygen_saturation'] && $raw['oxygen_saturation'] > 0) {
		$oxygen_saturation = xl("O2 Sat").": ".$raw['oxygen_saturation']."% ";
	}
	$ret = $height.$weight.$bmi.$temp.$bp.
		$pulse.$respiration.$oxygen_saturation;
	if ($ret != '') {
		$ret = xl("Vital Signs").": ".$ret;
	}
	return $ret;
}
