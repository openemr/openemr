<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

set_time_limit(0);
$sql = "SELECT * FROM form_repository WHERE id >= ? AND id < ?";
$cnt = 0;

$update = "UPDATE form_repository SET content = ? WHERE id = ?";
while($cnt < 500) {
	$mres = sqlStatement($sql, array($cnt, ($cnt + 500)));
	while($mrow = sqlFetchArray($mres)) {
		$id = $mrow{'id'};
		echo "Read [$id]<br>\n";
		if(preg_match('/\&nbsp(?!;)/', $mrow{'content'})) {
			echo "Found a bad one: ";
			echo $mrow{'content'};
			echo "<br>\n";
			$new = preg_replace('/\&nbsp(?!;)/','&nbsp;',$mrow{'content'});
			echo "The New one: $new <br>\n";
			$mrow{'content'} = $new;
			sqlStatement($update, array($new, $id));
		}
	}
	$cnt = $cnt + 500;
}
exit;

?>
