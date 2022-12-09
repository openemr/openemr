<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");
function getCustomForms()
{
  $sql = "SHOW TABLES LIKE 'form\_%'";
  $tres = sqlStatement($sql);
  $all=array();
  while($row = sqlFetchArray($tres)) {
    $formdir = array_shift($row);
    $sql= "SHOW COLUMNS FROM ".$formdir." LIKE 'form\_complete'";
    $cres = sqlStatement($sql);
    $ccols = sqlFetchArray($cres);
		$tst = '';
		if(is_array($ccols)) { $tst= array_shift($ccols); }
    if($tst == 'form_complete') {
      // echo "This test works!! $tst<br />\n";
			$formdir=substr($formdir, 5);
			if($formdir == '') { continue; }
			$sql = "SELECT name, nickname, directory FROM registry WHERE ".
					"directory='$formdir'";
			$fres = sqlStatement($sql);
			$frow = sqlFetchArray($fres);
			$name=trim($frow{'nickname'});
			if(!$frow{'directory'}) { continue; }
			if(empty($name)) { $name=trim($frow{'name'}); }
			$all[]=array('form_name' => $name, 
									'form_dir' => $frow{'directory'});
    }
  }
  return $all;
}

?>
