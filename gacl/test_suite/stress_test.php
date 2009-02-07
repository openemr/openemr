<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Stress Test</title>
</head>
<body>
<pre>
<?php

/*! var
 test scale
 
 This script will create:
 $scale * 10   ACOs
 $scale * 10   ARO groups
 $scale * 1000 AROs
 $scale * 10   AXO groups
 $scale * 1000 AXOs
 $scale * 10   ACLs
 
 1		normal	~5 seconds
 10		heavy	~1 minute
 100	crazy	~1 hour
 !*/
$scale = 10;

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
 a better array_rand, this one actually works on windows
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

// require gacl
require_once (dirname (__FILE__) . '/../admin/gacl_admin.inc.php');

/*
 * Let's get ready to RUMBLE!!!
 */

echo '<b>Stress Test</b>' . "\n";
echo '    Scale: ' . $scale . "\n\n";

$overall_start = getmicrotime ();

mt_srand ((double)microtime () *10000);

$gacl_api->add_object_section ('System', 'system', 0, 0, 'ACO');

echo "<b>Create ACOs</b>\n";
flush ();


$start_time = getmicrotime ();

$start = 1;
$max = 10 * $scale;
for ( $i = $start; $i <= $max; $i++ )
{
	if ( $gacl_api->add_object ('system', 'ACO: ' . $i, $i, 10, 0, 'ACO') == FALSE )
	{
		echo "    Error creating ACO: $i.\n";
		echo '    ' . $gacl_api->_debug_msg . "\n";
	}
}

$elapsed = getmicrotime () - $start_time;

echo "Done\n";
echo '    Count:   ' . $max . "\n";
echo '    Time:    ' . $elapsed . " s\n";
echo '    Average: ' . $elapsed/$max . " s\n\n";



$gacl_api->add_object_section ('Users', 'users', 0, 0, 'ARO');

echo "<b>Create many ARO Groups.</b>\n";
flush ();

$start_time = getmicrotime ();

$query = 'SELECT id FROM '.$gacl_api->_db_table_prefix.'aro_groups';
$ids = $gacl_api->db->GetCol($query);

// print_r ($ids);

$start = 1;
$max = 10 * $scale;

// function add_group ($name, $parent_id=0, $group_type='ARO') {
for ( $i = $start; $i <= $max; $i++ )
{
	// Find a random parent
	if ( !empty ($ids) ) {
		$parent_id = $ids[array_mt_rand ($ids, 1)];
	} else {
		$parent_id = 0;
	}
	
	$result = $gacl_api->add_group ('aro_group'.$i,'ARO Group: '. $i, $parent_id, 'ARO');
	
	if ( $result == FALSE )
	{
		echo "    Error creating ARO Group: $i.\n";
		echo '    ' . $gacl_api->_debug_msg . "\n";
	}
	else
	{
		$ids[] = $result;
	}
}

$elapsed = getmicrotime () - $start_time;

echo "Done\n";
echo '    Count:   ' . $max . "\n";
echo '    Time:    ' . $elapsed . " s\n";
echo '    Average: ' . $elapsed/$max . " s\n\n";



echo "<b>Create AROs & assign to ARO Groups</b>\n";
flush ();

$start_time = getmicrotime ();

$start = 1;
$max = 1000 * $scale;

$groups = array_keys ($gacl_api->format_groups ($gacl_api->sort_groups ('ARO'), 'ARRAY'));
$randmax = count ($groups) - 1;

for ( $i = $start; $i <= $max; $i++ )
{
	if ( $gacl_api->add_object ('users', 'ARO: '. $i, $i, 10, 0, 'ARO') == FALSE )
	{
		echo "    Error creating ARO: $i.<br />\n";
		echo '    ' . $gacl_api->_debug_msg . "\n";
	}
	else
	{
		// Assign to random groups.
		$rand_key = $groups[mt_rand (0, $randmax)];
		$gacl_api->add_group_object ($rand_key, 'users', $i, 'ARO');
	}
}

$elapsed = getmicrotime () - $start_time;

echo "Done\n";
echo '    Count:   ' . $max . "\n";
echo '    Time:    ' . $elapsed . " s\n";
echo '    Average: ' . $elapsed/$max . " s\n\n";




$gacl_api->add_object_section ('Users', 'users', 0, 0, 'AXO');

echo "<b>Create many AXO Groups.</b>\n";
flush ();

$start_time = getmicrotime ();

$query = 'SELECT id FROM '.$gacl_api->_db_table_prefix.'axo_groups';
$ids = $gacl_api->db->GetCol($query);

$start = 1;
$max = 10 * $scale;

// function add_group ($name, $parent_id=0, $group_type='ARO') {
for ( $i = $start; $i <= $max; $i++ )
{
	// Find a random parent
	if ( !empty ($ids) ) {
		$parent_id = $ids[array_mt_rand ($ids, 1)];
	} else {
		$parent_id = 0;
	}
	
	$result = $gacl_api->add_group ('axo_group'.$i,'AXO Group: '. $i, $parent_id, 'AXO');
	if ( $result == FALSE )
	{
		echo "    Error creating AXO Group: $i.\n";
		echo '    ' . $gacl_api->_debug_msg . "\n";
	}
	else
	{
		$ids[] = $result;
	}
}

$elapsed = getmicrotime () - $start_time;

echo "Done\n";
echo '    Count:   ' . $max . "\n";
echo '    Time:    ' . $elapsed . " s\n";
echo '    Average: ' . $elapsed/$max . " s\n\n";



echo "<b>Create AXOs & assign to AXO Groups</b>\n";
flush ();

$start_time = getmicrotime ();

$start = 1;
$max = 1000 * $scale;

// $groups = array_keys ($gacl_api->format_groups ($gacl_api->sort_groups ('AXO'), 'ARRAY'));
$rand_max = count ($groups) - 1;

for ( $i = $start; $i <= $max; $i++ )
{
	if ( $gacl_api->add_object ('users', 'AXO: ' . $i, $i, 10, 0, 'AXO') == FALSE )
	{
		echo "    Error creating ARO: $i.<br />\n";
		echo '    ' . $gacl_api->_debug_msg . "\n";
	}
	else
	{
		// Assign to random groups.
		$rand_key = $groups[mt_rand (0, $rand_max)];
		$gacl_api->add_group_object ($rand_key, 'users', $i, 'AXO');
	}
}

$elapsed = getmicrotime () - $start_time;

echo "Done\n";
echo '    Count:   ' . $max . "\n";
echo '    Time:    ' . $elapsed . " s\n";
echo '    Average: ' . $elapsed/$max . " s\n\n";



echo "<b>Generate random ACLs now.</b>\n";
flush ();

$start_time = getmicrotime ();

$start = 1;
$max = 10 * $scale;

$aco_list = $gacl_api->get_object ('system', 1, 'ACO');

$query = 'SELECT id, name FROM '.$gacl_api->_db_table_prefix.''. $gacl_.'aro_groups ORDER BY parent_id DESC LIMIT 100';
$rs = $gacl_api->db->Execute($query);
$aro_groups = $rs->GetAssoc();

$query = 'SELECT id, name FROM '.$gacl_api->_db_table_prefix.'axo_groups ORDER BY parent_id DESC LIMIT 100';
$rs = $gacl_api->db->Execute($query);
$axo_groups = $rs->GetAssoc();

// $aro_groups = $gacl_api->format_groups ($gacl_api->sort_groups ('ARO'), 'ARRAY');

print_r ($aro_groups);

// $axo_groups = $gacl_api->format_groups ($gacl_api->sort_groups ('AXO'), 'ARRAY');

print_r ($axo_groups);

for ( $i = $start; $i <= $max; $i++ )
{
	$rand_aco_key = array_mt_rand ($aco_list, mt_rand (2, 10));
	$rand_aro_key = array_mt_rand ($aro_groups, mt_rand(2,10));
	$rand_axo_key = array_mt_rand ($axo_groups, mt_rand(2,10));
	
	$aco_array = array ();
	
	foreach ( $rand_aco_key as $aco_key )
	{
		$aco_data = $gacl_api->get_object_data ($aco_list[$aco_key], 'ACO');
		$aco_array[$aco_data[0][0]][] = $aco_data[0][1];
	}
	
	// Randomly create ACLs with AXOs assigned to them.
	// if ($i % 2 == 0) {
	$axo_array = $rand_axo_key;
	// }
	
	if ( $gacl_api->add_acl ($aco_array, NULL, $rand_aro_key, NULL, $axo_array) == FALSE )
	{
		echo "    Error creating ACL: $i.\n";
		echo '    ' . $gacl_api->_debug_msg . "\n";
		// print_r (array_slice ($gacl_api->_debug_msg, -2));
	}
	
	unset ($axo_array);
}

$elapsed = getmicrotime () - $start_time;

echo "Done\n";
echo '    Count:   ' . $max . "\n";
echo '    Time:    ' . $elapsed . " s\n";
echo '    Average: ' . $elapsed/$max . " s\n\n";


echo "<b>Generating Test Data Set</b>\n";
flush ();

$start_time = getmicrotime ();

$start = 1;
$max = 5 * $scale;
// $max = 1;

$check = array ();

for ( $i = $start; $i <= $max; $i++ )
{
	$rand_aco_key = mt_rand (10,10 * $scale);
	$rand_aro_key = mt_rand (10,1000 * $scale);
	$rand_axo_key = mt_rand (10,1000 * $scale);
	
	// echo '    Rand ACO: '. $rand_aco_key .' ARO: '. $rand_aro_key . ' AXO: ' . $rand_axo_key . "\n";
	
	$aco_data = &$gacl_api->get_object_data ($rand_aco_key, 'ACO');
	$aro_data = &$gacl_api->get_object_data ($rand_aro_key, 'ARO');
	$axo_data = &$gacl_api->get_object_data ($rand_axo_key, 'AXO');
	
	$check[$i] = array (
		'aco' => $aco_data[0],
		'aro' => $aro_data[0],
		'axo' => $axo_data[0]
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

foreach ( $check as $i => $data )
{
	echo '    Trying: ACO Section: '. $data['aco'][0] .' Value: '. $data['aco'][1] .' ARO Section: '. $data['aro'][0] .' Value: '. $data['aro'][1] . ' ARO Section: '. $data['axo'][0] .' Value: '. $data['axo'][1] . "\n";
	
	$check_start = getmicrotime ();
	
	$allow = $gacl_api->acl_check ($data['aco'][0],$data['aco'][1],$data['aro'][0],$data['aro'][1],$data['axo'][0],$data['axo'][1]);
	
	$check_time = getmicrotime () - $check_start;
	
	if ( $allow ) {
		echo '<font color="#00ff00">    ' . $i . ". Access Granted!</font>";
	} else {
		echo '<font color="#ff0000">    ' . $i . ". Access Denied!</font>";
	}
	
	echo ' - ' . $check_time . " s\n";
	
	$best = min ($best, $check_time);
	$worst = max ($worst, $check_time);
	$total = $total + $check_time;
}

echo "Done\n";
echo '    Count:   ' . $max . "\n\n";

echo '    Total:   ' . $total . " s\n";
echo '    Average: ' . $total/$max . " s\n\n";

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
