<?php
include_once("../../interface/globals.php");
include_once("$srcdir/api.inc");

function ListLook($thisData, $thisList) {
  if($thisList == 'occurrence') {
    if(!$thisData || $thisData == '') return 'Unknown or N/A'; 
  }
  if($thisData == '') return ''; 
  $fres=sqlStatement("SELECT * FROM list_options WHERE list_id=? ".
        "AND option_id=?", array($thisList, $thisData));
  if($fres) {
    $rret=sqlFetchArray($fres);
    $dispValue= $rret{'title'};
    if($thisList == 'occurrence' && $dispValue == '') {
      $dispValue = 'Unknown or N/A';
    }
  } else {
    $dispValue= '* Not Found *';
  }
  return $dispValue;
}

$sql = "SELECT id, injury_part, title, injury_type FROM lists WHERE ".
		"type='wmt_family_history' AND (title IS NULL OR title='')";
$work=sqlStatement($sql);
echo "Processing Family History<br/>\n";
while($list = sqlFetchArray($work)) {
	$title='No Type of History';
	$tmp=ListLook($list{'injury_type'},'Family_History_Problems');
	if($tmp != '') { $title=$tmp.' - '; }
	$tmp=ListLook($list{'injury_part'},'Family_Relationships');
	if($tmp == '') { $tmp='No Family Member Specified'; }
	$title.=$tmp;
	$title=htmlspecialchars($title,ENT_QUOTES);
	$id=$list{'id'};
	echo "Updating ID: $id  ->  Old Title [",$list{'title'},"]   New (";
	echo $title,")<br/>\n";
	$sql="UPDATE lists SET title='$title' WHERE id=$id";
	sqlStatement($sql);
}

$sql = "SELECT id, title, injury_type FROM lists WHERE ".
		"type='wmt_med_history' AND (title IS NULL OR title='')";
echo "<br/><br/><br/>Processing Medical History<br/>\n";
$work=sqlStatement($sql);
while($list = sqlFetchArray($work)) {
	$title=ListLook($list{'injury_type'},'Medical_History_Problems');
	if($title == '') { $title='No Type of History Specified'; }
	$title=htmlspecialchars($title,ENT_QUOTES);
	$id=$list{'id'};
	echo "Updating ID: $id  ->  Old Title [",$list{'title'},"]   New (";
	echo $title,")<br/>\n";
	$sql="UPDATE lists SET title='$title' WHERE id=$id";
	sqlStatement($sql);
}

$sql = "SELECT id, title, injury_type FROM lists WHERE ".
		"type='wmt_img_history' AND (title IS NULL OR title='')";
echo "<br/><br/><br/>Processing Image History<br/>\n";
$work=sqlStatement($sql);
while($list = sqlFetchArray($work)) {
	$title=ListLook($list{'injury_type'},'Image_Types');
	if($title == '') { $title='No Type of History Specified'; }
	$title=htmlspecialchars($title,ENT_QUOTES);
	$id=$list{'id'};
	echo "Updating ID: $id  ->  Old Title [",$list{'title'},"]   New (";
	echo $title,")<br/>\n";
	$sql="UPDATE lists SET title='$title' WHERE id=$id";
	sqlStatement($sql);
}
