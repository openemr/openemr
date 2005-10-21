<?php
/*	
	batch CSV processor, included from batchcom 
*/

// create file header.
// menu for fields could be added in the future

while ($row=sqlFetchArray($res)) {
	
	if (!$flag_on) {
		$flag_on=TRUE;
		foreach ($row as $key => $value ){
			$file.="$key,";
		}
		$file=substr($file,0,-1);
		$file.="\n";
		reset ($row);
	}

	foreach ($row as $key => $value) {
		$line.="$value,";
	}
	$line=substr($line,0,-1);
	$line.="\n";
	$file.=$line;
	$line='';

}

//download
$today=date('Y-m-d:H:i:s');
$filename="CSVdata-".$today.".csv";
header('Pragma: private');
header('Cache-control: private, must-revalidate');
header("Content-type: text/comma-separated-values");
header("Content-Disposition: attachment; filename=".$filename);
print $file; 
exit()
?>