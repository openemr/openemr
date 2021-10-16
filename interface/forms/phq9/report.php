<?php 

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");
function phq9_report($pid, $encounter, $cols, $id){

	$sql = "SELECT * FROM `form_phq9` WHERE pid = ? AND encounter = ? AND id = ?";
	$res = sqlStatement($sql, array($pid, $encounter, $id));
	$data = array();
	for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
		$data[$iter] = $row;
	}

	$score = 0;
	if($data[0]['little_interest']!=""){
		$score = $score+$data[0]['little_interest'];
	}

	if($data[0]['feeling_down']!=""){
		$score = $score+$data[0]['feeling_down'];
	}

	if($data[0]['trouble_falling']!=""){
		$score = $score+$data[0]['trouble_falling'];
	}

	if($data[0]['feeling_tired']!=""){
		$score = $score+$data[0]['feeling_tired'];
	}

	if($data[0]['overeating']!=""){
		$score = $score+$data[0]['overeating'];
	}

	if($data[0]['feeling_bad']!=""){
		$score = $score+$data[0]['feeling_bad'];
	}
	
	if($data[0]['television']!=""){
		$score = $score+$data[0]['television'];
	}

	if($data[0]['restless']!=""){
		$score = $score+$data[0]['restless'];
	}
	
	if($data[0]['hurtingyourself']!=""){
		$score = $score+$data[0]['hurtingyourself'];
	}
	
	
	$riskStaus="";
	$textColor="red";
	if($score>=0 && $score<=4){
	   $textColor = "black";
	   $riskStaus="Minimal Depression.";
	} elseif($score>=5 && $score<=9){
	   $riskStaus="Mild depression, consider evidence-based interventions and clinical guidelines.";
	} elseif($score>=10 && $score<=14){
		$riskStaus="Moderate depression, consider evidence-based interventions and clinical guidelines.";
	} elseif($score>=15 && $score<=19){
		$riskStaus="Moderately severe depression, consider evidence-based interventions and clinical guidelines.";
	} elseif($score>=20 && $score<=27){
		$riskStaus="Severe Depression, consider evidence-based interventions and clinical guidelines.";
	}

	if ($data) {
	
		if($data[0]['unabletoevaluate']!="true")
		{
			echo 'Score: ' . $score . '<br>';
			echo 'Clinical Consideration: ' . '<span style=color:'.$textColor.'>'.$riskStaus .'</span>';	
		}
		else
		{
			echo 'Score: N/A' . '<br>';
			echo 'Clinical Consideration: Unable to evaluate'.'<br>';
			echo $data[0]['unable_to_evaluate_desc'];		
		}
	}

 }?>