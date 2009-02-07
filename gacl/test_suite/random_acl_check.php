<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Random ACL Check</title>
</head>
<body>
<pre>
<?php

set_time_limit (6000);

/*! function
 get time accurate to the nearest microsecond, used for script timing
 !*/
function getmicrotime ()
{
	list ($usec, $sec) = explode (' ', microtime ());
	return (float)$usec + (float)$sec;
}

/*! function
 a better array_rand, this one actualluy works on windows
 !*/
function array_mt_rand ($array, $items)
{
	$keys = array_keys ($array);
	$max = count ($keys) - 1;
	
	if ( $items == 1 )
	{
		return $keys[mt_rand (0, $max)];
	}
	
	$return = array ();
	
	for ( $i = 1; $i <= $items; $i++ )
	{
		$return[] = $keys[mt_rand (0, $max)];
	}
	
	return $return;
}

/*! function
 grab random objects from the database
 !*/
function random_objects ($type, $limit = NULL)
{
	$sql = 'SELECT id, section_value, value FROM ' . $GLOBALS['gacl_api']->_db_table_prefix . $type . ' ORDER BY RAND()';
	
	if ( is_scalar ($limit) )
	{
		$rs = $GLOBALS['gacl_api']->db->SelectLimit ($sql,$limit);
	}
	else
	{
		$rs = $GLOBALS['gacl_api']->db->Execute ($sql);
	}
	
	if ( !is_object ($rs) )
	{
		return FALSE;
	}
	
	$retarr = array ();
	
	while ( $row = $rs->FetchRow () )
	{
		$retarr[$row[0]] = array (
			$row[1],
			$row[2]
		);
	}
	
	return $retarr;
}

// require gacl
require_once (dirname (__FILE__) . '/../admin/gacl_admin.inc.php');

/*
 * Let's get ready to RUMBLE!!!
 */
$scale = 100;

echo '<b>Random ACL Check</b>' . "\n";
echo '    Scale: ' . $scale . "\n\n";

$overall_start = getmicrotime ();

mt_srand ((double)microtime () *10000);

echo "<b>Generating Test Data Set</b>\n";
flush ();

$start_time = getmicrotime ();

$start = 1;
$max = 5 * $scale;
// $max = 1;

$check = array ();

$aco = random_objects ('aco', $max);
$aro = random_objects ('aro', $max);
$axo = random_objects ('axo', $max);

for ( $i = $start; $i <= $max; $i++ )
{
	$rand_aco_id = array_mt_rand ($aco, 1);
	$rand_aro_id = array_mt_rand ($aro, 1);
	$rand_axo_id = array_mt_rand ($axo, 1);
	
	// echo '    Rand ACO: '. $rand_aco_id .' ARO: '. $rand_aro_id . ' AXO: ' . $rand_axo_id . "\n";
	
	$check[$i] = array (
		'aco' => $aco[$rand_aco_id],
		'aro' => $aro[$rand_aro_id],
		'axo' => $axo[$rand_axo_id]
	);
}

$elapsed = getmicrotime () - $start_time;

echo "Done\n\n";
echo '    Count:   ' . $max . "\n";
echo '    Time:    ' . $elapsed . " s\n";
echo '    Average: ' . $elapsed/$max . " s\n\n";

echo "<b>Testing...</b>\n";
flush ();

$best = 99999;
$worst = 0;
$total = 0;

$allowed = 0;
$denied = 0;

$allowed_time = 0;
$denied_time = 0;

foreach ( $check as $i => $data )
{
	echo '    Trying: ACO Section: '. $data['aco'][0] .' Value: '. $data['aco'][1] .' ARO Section: '. $data['aro'][0] .' Value: '. $data['aro'][1] . ' ARO Section: '. $data['axo'][0] .' Value: '. $data['axo'][1] . "\n";
	
	$check_start = getmicrotime ();
	
	$allow = $gacl_api->acl_check ($data['aco'][0],$data['aco'][1],$data['aro'][0],$data['aro'][1],$data['axo'][0],$data['axo'][1]);
	
	$check_time = getmicrotime () - $check_start;
	
	if ( $allow ) {
		echo '<font color="#00ff00">    ' . $i . ". Access Granted</font>";
		$allowed++;
		$allowed_time += $check_time;
	} else {
		echo '<font color="#ff0000">    ' . $i . ". Access Denied</font>";
		$denied++;
		$denied_time += $check_time;
	}
	
	echo ' - ' . $check_time . " s\n";
	
	$best = min ($best, $check_time);
	$worst = max ($worst, $check_time);
	$total = $total + $check_time;
}

echo "Done\n";
echo '    Count:   ' . $max . "\n";
echo '    Total:   ' . $total . " s\n";
echo '    Average: ' . $total/$max . " s\n\n";

echo '    Allowed: ' . $allowed . "\n";
echo '    Total:   ' . $allowed_time . " s\n";
echo '    Average: ' . $allowed_time/$allowed . " s\n\n";

echo '    Denied:  ' . $denied . "\n";
echo '    Total:   ' . $denied_time . " s\n";
echo '    Average: ' . $denied_time/$denied . " s\n\n";

echo '    Best:    ' . $best . " s\n";
echo '    Worst:   ' . $worst . " s\n\n";

// print_r ($gacl_api->db);


$elapsed = getmicrotime () - $overall_start;

echo '<b>All Finished</b>' . "\n";
echo '    Total Time: ' . $elapsed . " s\n";

/*
 * end of script
 */

?>
</pre>
</body>
</html>
