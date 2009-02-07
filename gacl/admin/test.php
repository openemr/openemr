<?php
$debug=1;
require_once("gacl_admin.inc.php");

/*
$test = $gacl->add_group_object(10, 'user','10');
$gacl->showarray($test);

$test = $gacl->add_group_object(10, 'user','10');
$gacl->showarray($test);

$test = $gacl->del_group_object(10, 'user','10');
$gacl->showarray($test);

$test = $gacl->del_group_object(10, 'user','10');
$gacl->showarray($test);
*/

//$test = $gacl->acl_query('system','login','users','john_doe',NULL, NULL, NULL, NULL, TRUE);
//showarray($test);

/*
$test = $gacl_api->get_group_objects(14,'ARO','RECURSE');
showarray($test);

$test = $gacl_api->get_group_objects(14,'ARO');
showarray($test);
*/
/*
$group_ids = array(14);
$test = $gacl_api->acl_get_group_path($group_ids);
showarray($test);
$test = $gacl_api->get_group_objects(14,'ARO');
showarray($test);
*/

//$gacl_api->clean_path_to_root(ARO);
//$gacl_api->clean_path_to_root(AXO);

/*
$gacl_api->add_acl(	array('system' => array('login', 'enabled', 'login')),
								array('users' => array(1)),
								array(10,12,10),
								NULL,
								NULL,
								TRUE,
								TRUE,
								666,
								'NOTE');
*/
/*

$gacl_api->is_conflicting_acl(
								array('system' => array('login')),
								array('accounts' => array(1)),
								array(99),
								array('projects' => array(99)),
								array(99));
*/
//$gacl_api->consolidated_edit_acl('system', 'add_pop','accounts',1, 99);

//$gacl_api->search_acl('system','add_pop','accounts',1, 'Browsers','projects',5599,'Projects',99);

/*
$gacl_api->shift_acl(	18,
								array('accounts' => array(1)),
								array(14),
								array('projects' => array(5599)),
								array(23),
								array('system' => array('add_pop'))						
						);
*/
/*
$gacl_api->append_acl(	18,
								array('accounts' => array(1,2,3,4)),
								array(14),
								array('projects' => array(5599)),
								array(23),
								array('system' => array('add_pop'))
						);
*/

/*
$gacl_api->add_acl(	array('system' => array(99)),
								array('accounts' => array(99)),
								array(99),
								array('projects' => array(99)),
								array(99),
								TRUE,
								TRUE,
								666,
								'NOTE');
*/

//$rows = $rs->GetRows();
//showarray($rows);


/*
$query = '
	SELECT		a.value AS a_value, a.name AS a_name,
				b.value AS b_value, b.name AS b_name,
				c.value AS c_value, c.name AS c_name,
				d.value AS d_value, d.name AS d_name
	FROM		aco_sections a,
				aco b,
				aro_sections c,
				aro d
	WHERE		a.value=b.section_value
	AND			c.value=d.section_value
	ORDER BY	a.value, b.value, c.value, d.value';
//$rs = $db->Execute($query);
//$rows = $rs->GetRows();
$rs = $db->pageexecute($query, 100, 2);
showarray($rows);
$rows = $rs->GetRows();
showarray($rows);
*/

//$test=$gacl-> acl_query('system', 'email_pw', 'users', '1');
//showarray($test);

//Test object deleting.
//$gacl_api->del_object(10,'ARO', TRUE);
//$gacl_api->del_object_section(10,'ACO',TRUE);

/*
//Test AXO's
//function acl_query($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL, $root_aro_group_id=NULL, $root_axo_group_id=NULL) {
$test1= acl_query('system','login','users', '1');
showarray($test1);

$test2=acl_query('system','login','users', '1','projects','1');
showarray($test2);
*/

//Test subtree'ing
/*
$test=acl_get_groups('test_section2','user1',0);
showarray($test);

$test=acl_get_groups('test_section2','user1');
showarray($test);
*/

/*
//require_once('../Cache_Lite.php');
require_once('./profiler.inc');
$profiler = new Profiler(true,true);

$options = array(
    'caching' => true,
    'cacheDir' => '/tmp/phpgacl_cache',
    'lifeTime' => 100
);

//$Cache_Lite = new Hashed_Cache_Lite($options);
$Cache_Lite = new Cache_Lite($options);

$data = '0123456789';
$Cache_Lite->save($data,'123');

$profiler->startTimer( "acl_query()");
//Test memory caching.
for ($i=0; $i < 2; $i++) {
	//$Cache_Lite->save($data,'123'.$i);

	$data = $Cache_Lite->get('123');
	//echo "$data<br>\n";
}
$profiler->stopTimer( "acl_query()");

$profiler->printTimers();
*/
/*
//Test multi-layer ACOs
$test = acl_query(array(21,19), 10);
showarray($test);
*/

//Stress test.
/*
//Cleanup
$aco_section_id = $gacl_api->get_aco_section_section_id("Stress Test");
$del_aco_ids = $gacl_api->get_aco($aco_section_id);
foreach ($del_aco_ids as $del_aco_id) {
	$gacl_api->del_aco($del_aco_id);
}
$gacl_api->del_aco_section($aco_section_id);

$aro_section_id = $gacl_api->get_aro_section_section_id("Stress Test");
$del_aro_ids = $gacl_api->get_aro($aro_section_id);
foreach ($del_aro_ids as $del_aro_id) {
	$gacl_api->del_aro($del_aro_id);
}
$gacl_api->del_aro_section($aro_section_id);

//Get all ACLs
$query = "select id from acl";
$rs = $db->GetCol($query);

foreach($rs as $del_acl_id) {
	$gacl_api->del_acl($del_acl_id);
}
*/

/*
$max_aco=10;
$max_aro=50;

$max_acl=100;
$min_rand_aco=1;
$max_rand_aco=9;
$min_rand_aro=1;
$max_rand_aro=9;

//Seed random. 
srand ((float) microtime() * 10000000);

//Grab ACO Section_id
$aco_section_id = $gacl_api->get_aco_section_section_id("Stress Test");

if (!$aco_section_id) {
	//Add an ACO section.
	$aco_section_id = $gacl_api->add_aco_section("Stress Test", 999,999);
	$gacl_api->debug_text("Stress Test: ACO Section ID: $aco_section_id");
}

//Add 100 random ACO's
if ($aco_section_id) {

	for ($i=0; $i < $max_aco; $i++) {
		$aco_id = $gacl_api->get_aco_id("Stress Test ACO #$i");

		if (!$aco_id) {
			//Add ACO.
			$aco_id = $gacl_api->add_aco($aco_section_id, "Stress Test ACO #$i",$i, $i);
		}
	}
}
$aco_ids = $gacl_api->get_aco($aco_section_id);
//showarray($aco_ids);

//Grab ARO section id
$aro_section_id = $gacl_api->get_aro_section_section_id("Stress Test");

if (!$aro_section_id) {
	//Add an ACO section.
	$aro_section_id = $gacl_api->add_aro_section("Stress Test", 999,999);
	$gacl_api->debug_text("Stress Test: ARO Section ID: $aro_section_id");
}

//Add 10,000 random ARO's
if ($aro_section_id) {

	for ($i=0; $i < $max_aro; $i++) {
		$aro_id = $gacl_api->get_aro_id("Stress Test ARO #$i");

		if (!$aro_id) {
			//Add ARO.
			$aro_id = $gacl_api->add_aro($aro_section_id, "Stress Test ARO #$i",$i, $i);
		}
	}
}
$aro_ids = $gacl_api->get_aro($aro_section_id);
//showarray($aro_ids);

//Create random ACL's using the above stress test ACO/AROs
if (count($aco_ids) > 1 AND count($aro_ids) > 1) {
	for ($i=0; $i < $max_acl; $i++) {
		//Get random ACO IDS
		$rand_aco_keys = array_rand($aco_ids, mt_rand($min_rand_aco, $max_rand_aco) );

		unset($rand_aco_ids);
		foreach ($rand_aco_keys as $rand_aco_key) {
			$rand_aco_ids[] = $aco_ids[$rand_aco_key];	
		}

		//Get random ARO IDS
		$rand_aro_keys = array_rand($aro_ids, mt_rand($min_rand_aro, $max_rand_aro));

		unset($rand_aro_ids);
		foreach ($rand_aro_keys as $rand_aro_key) {
			$rand_aro_ids[] = $aro_ids[$rand_aro_key];	
		}

		//Random ALLOW
		$allow = mt_rand(0,1);

		$gacl_api->debug_text("Inserting ACL with ". count($rand_aco_ids) ." ACOs and ". count($rand_aro_ids) ." AROs - Allow: $allow");
		$gacl_api->add_acl($rand_aco_ids, $rand_aro_ids, NULL, $allow, 1);
	}
}		


//Create much more Decoy data
$max_aco=100;
$max_aro=4000;

$max_acl=1000;
$min_rand_aco=1;
$max_rand_aco=10;
$min_rand_aro=1;
$max_rand_aro=10;

//Seed random. 
srand ((float) microtime() * 10000000);

//Grab ACO Section_id
$aco_section_id = $gacl_api->get_aco_section_section_id("Stress Test Decoy");

if (!$aco_section_id) {
	//Add an ACO section.
	$aco_section_id = $gacl_api->add_aco_section("Stress Test Decoy", 1000,1000);
	$gacl_api->debug_text("Stress Test: ACO Section ID: $aco_section_id");
}

//Add 100 random ACO's
if ($aco_section_id) {

	for ($i=0; $i < $max_aco; $i++) {
		$aco_id = $gacl_api->get_aco_id("Stress Test Decoy ACO #$i");

		if (!$aco_id) {
			//Add ACO.
			$aco_id = $gacl_api->add_aco($aco_section_id, "Stress Test ACO Decoy #$i",$i, $i);
		}
	}
}
$aco_ids = $gacl_api->get_aco($aco_section_id);
//showarray($aco_ids);

//Grab ARO section id
$aro_section_id = $gacl_api->get_aro_section_section_id("Stress Test Decoy");

if (!$aro_section_id) {
	//Add an ACO section.
	$aro_section_id = $gacl_api->add_aro_section("Stress Test Decoy", 1000,1000);
	$gacl_api->debug_text("Stress Test: ARO Section ID: $aro_section_id");
}

//Add 10,000 random ARO's
if ($aro_section_id) {

	for ($i=0; $i < $max_aro; $i++) {
		$aro_id = $gacl_api->get_aro_id("Stress Test Decoy ARO #$i");

		if (!$aro_id) {
			//Add ARO.
			$aro_id = $gacl_api->add_aro($aro_section_id, "Stress Test Decoy ARO #$i",$i, $i);
		}
	}
}
$aro_ids = $gacl_api->get_aro($aro_section_id);
//showarray($aro_ids);

//Create random ACL's using the above stress test ACO/AROs
if (count($aco_ids) > 1 AND count($aro_ids) > 1) {
	for ($i=0; $i < $max_acl; $i++) {
		//Get random ACO IDS
		$rand_aco_keys = array_rand($aco_ids, mt_rand($min_rand_aco, $max_rand_aco) );

		unset($rand_aco_ids);
		foreach ($rand_aco_keys as $rand_aco_key) {
			$rand_aco_ids[] = $aco_ids[$rand_aco_key];	
		}

		//Get random ARO IDS
		$rand_aro_keys = array_rand($aro_ids, mt_rand($min_rand_aro, $max_rand_aro));

		unset($rand_aro_ids);
		foreach ($rand_aro_keys as $rand_aro_key) {
			$rand_aro_ids[] = $aro_ids[$rand_aro_key];	
		}

		//Random ALLOW
		$allow = mt_rand(0,1);

		$gacl_api->debug_text("Inserting ACL with ". count($rand_aco_ids) ." ACOs and ". count($rand_aro_ids) ." AROs - Allow: $allow");
		$gacl_api->add_acl($rand_aco_ids, $rand_aro_ids, NULL, $allow, 1);
	}
}		
*/







/*
//Test subtree'ing
$aco_id=10;
$aro_id=22;
$root_group_id=30;

$test=acl_query($aco_id,$aro_id,$root_group_id);
showarray($test);

$aco_id=10;
$aro_id=22;
$root_group_id=33;

$test=acl_query($aco_id,$aro_id,$root_group_id);
showarray($test);
*/

/*
//Populate the ARO's
$max_aros = 100;
for ($i=0; $i < $max_aros; $i++) {

	$aro_id = $gacl_api->add_aro(41,"$i First $i Last", $i, $i);

	if ($aro_id) {
		$gacl_api->debug_text("ARO ID: $aro_id");
	} else {
		$gacl_api->debug_text("Insert ARO ID FAILED!");
	}
}
*/
?>
