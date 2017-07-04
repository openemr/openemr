<?php

//validation functions
function check_date_format ($date) {
	$pat="/^(19[0-9]{2}|20[0-1]{1}[0-9]{1})-(0[1-9]|1[0-2])-(0[1-9]{1}|1[0-9]{1}|2[0-9]{1}|3[0-1]{1})$/";
	return preg_match($pat, $date) OR $date=='' OR $date=='0000-00-00';
}

function check_age ($age) {
	$age=trim ($age);
	$pat="/^([0-9]+)$/";
	return preg_match($pat, $age) OR $age=='';
}

function check_select ($select, $array) {
	return array_search($select,$array) OR 0===array_search($select,$array);
}

function check_and_or ($option) {
	return $option=='AND' OR $option=='OR' OR $option=='AND NOT' OR $option=='OR NOT';
}

function where_or_and ($and) {
	if  ($and=='') {
		$and='WHERE ';
	} elseif ($and=='WHERE '){
		$and='AND ';
	} else {
		$and='AND ';
	}
	return $and;
}
	
function register_email($patient_id, $sent_by, $msg_type, $msg_subject, $msg_text) {

	$sql="INSERT INTO batchcom SET ";
	$sql.=" patient_id='$patient_id', ";
	$sql.="sent_by='$sent_by', ";
	$sql.="msg_type='$msg_type', ";
	$sql.="msg_subject='$msg_subject', ";
	$sql.="msg_text='$msg_text', ";
	$sql.="msg_date_sent=NOW() ";
	
	echo $sql;

	$res = sqlStatement($sql);
}

function generate_csv($sql_result) {
	/*	batch CSV processor, included from batchcom */
	// create file header.
	// menu for fields could be added in the future

	while ($row=sqlFetchArray($sql_result)) {
		if (!$flag_on) {
			$flag_on = TRUE;
			foreach( $row as $key => $value ) {
				$file .= "$key,";
			}
			$file = substr($file,0,-1);
			$file .= "\n";
			reset($row);
		}

		foreach ($row as $key => $value) {
			$line .= "$value,";
		}
		$line = substr($line,0,-1);
		$line .= "\n";
		$file .= $line;
		$line = '';
	}

	//download
	$today = date('Y-m-d:H:i:s');
	$filename = "CSVdata-".$today.".csv";
	header('Pragma: private');
	header('Cache-control: private, must-revalidate');
	header("Content-type: text/comma-separated-values");
	header("Content-Disposition: attachment; filename=" . $filename);
	print $file;
	exit();
}

?>