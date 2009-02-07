<?php

class acl_setup {
	
	var $gacl_api;
	
	var $aco_section = array();
	var $aco = array();
	
	var $aro_section = array();
	var $aro = array();
	var $aro_group = array();
	
	var $axo_section = array();
	var $axo = array();
	var $axo_group = array();
	
	var $acl = array();
	
	function acl_setup() {
		$this->gacl_api = &$GLOBALS['gacl_api'];
	}
	
	function setup() {
		// ACO
		$this->aco_section[] = $this->gacl_api->add_object_section ('Test','test_aco',0,0,'ACO');
			$this->aco[] = $this->gacl_api->add_object ('test_aco','Access','access',0,0,'ACO');
		
		// ARO
		$this->aro_section[] = $this->gacl_api->add_object_section ('Human', 'test_human',0,0,'ARO');
			$this->aro[] = $this->gacl_api->add_object ('test_human','Han','han',0,0,'ARO');
			$this->aro[] = $this->gacl_api->add_object ('test_human','Lando','lando',0,0,'ARO');
			$this->aro[] = $this->gacl_api->add_object ('test_human','Obi-wan','obiwan',0,0,'ARO');
			$this->aro[] = $this->gacl_api->add_object ('test_human','Luke','luke',0,0,'ARO');
		
		$this->aro_section[] = $this->gacl_api->add_object_section ('Android', 'test_android',0,0,'ARO');
			$this->aro[] = $this->gacl_api->add_object ('test_android','R2D2','r2d2',0,0,'ARO');
			$this->aro[] = $this->gacl_api->add_object ('test_android','C3PO','c3po',0,0,'ARO');
		
		$this->aro_section[] = $this->gacl_api->add_object_section ('Alien', 'test_alien',0,0,'ARO');
			$this->aro[] = $this->gacl_api->add_object ('test_alien','Chewie','chewie',0,0,'ARO');
			$this->aro[] = $this->gacl_api->add_object ('test_alien','Hontook','hontook',0,0,'ARO');
		
		// ARO groups
		$this->aro_group['root'] = $this->gacl_api->add_group('millennium_falcon_passengers', 'Millennium Falcon Passengers',0,'ARO');
			$this->aro_group['crew'] = $this->gacl_api->add_group('crew', 'Crew',$this->aro_group['root'],'ARO');
			$this->aro_group['passengers'] = $this->gacl_api->add_group('passengers','Passengers',$this->aro_group['root'],'ARO');
				$this->aro_group['jedi'] = $this->gacl_api->add_group('jedi', 'Jedi',$this->aro_group['passengers'],'ARO');
			$this->aro_group['engineers'] = $this->gacl_api->add_group('engineers', 'Engineers',$this->aro_group['root'],'ARO');
		
		// add AROs to groups
		$this->gacl_api->add_group_object($this->aro_group['crew'],'test_alien','chewie','ARO');
		$this->gacl_api->add_group_object($this->aro_group['crew'],'test_human','han','ARO');
		$this->gacl_api->add_group_object($this->aro_group['crew'],'test_human','lando','ARO');
		
		$this->gacl_api->add_group_object($this->aro_group['passengers'],'test_android','c3po','ARO');
		$this->gacl_api->add_group_object($this->aro_group['passengers'],'test_android','r2d2','ARO');
		
		$this->gacl_api->add_group_object($this->aro_group['jedi'],'test_human','luke','ARO');
		$this->gacl_api->add_group_object($this->aro_group['jedi'],'test_human','obiwan','ARO');
		
		$this->gacl_api->add_group_object($this->aro_group['engineers'],'test_alien','hontook','ARO');
		$this->gacl_api->add_group_object($this->aro_group['engineers'],'test_android','r2d2','ARO');
		$this->gacl_api->add_group_object($this->aro_group['engineers'],'test_human','han','ARO');
		
		// AXO
		$this->axo_section[] = $this->gacl_api->add_object_section ('Location', 'test_location',0,0,'AXO');
			$this->axo[] = $this->gacl_api->add_object ('test_location','Engines','engines',0,0,'AXO');
			$this->axo[] = $this->gacl_api->add_object ('test_location','Lounge','lounge',0,0,'AXO');
			$this->axo[] = $this->gacl_api->add_object ('test_location','Cockpit','cockpit',0,0,'AXO');
			$this->axo[] = $this->gacl_api->add_object ('test_location','Guns','guns',0,0,'AXO');
		
		// AXO Groups
		$this->axo_group['locations'] = $this->gacl_api->add_group('locations', 'Locations',0,'AXO');
		
		// add AXOs to groups
		$this->gacl_api->add_group_object($this->axo_group['locations'],'test_location','engines','AXO');
		$this->gacl_api->add_group_object($this->axo_group['locations'],'test_location','lounge','AXO');
		$this->gacl_api->add_group_object($this->axo_group['locations'],'test_location','cockpit','AXO');
		$this->gacl_api->add_group_object($this->axo_group['locations'],'test_location','guns','AXO');
		
		// create ACLs
		$this->acl[] = $this->gacl_api->add_acl(array('test_aco'=>array('access')),NULL,array($this->aro_group['crew']),NULL,array($this->axo_group['locations']),1,1,NULL,'Crew can go anywhere');
		$this->acl[] = $this->gacl_api->add_acl(array('test_aco'=>array('access')),array('test_alien'=>array('chewie')),NULL,array('test_location'=>array('engines')),NULL,0,1,NULL,'Chewie can\'t access the engines');
		
		$this->acl[] = $this->gacl_api->add_acl(array('test_aco'=>array('access')),NULL,array($this->aro_group['passengers']),array('test_location'=>array('lounge')),NULL,1,1,NULL,'Passengers are allowed in the lounge');
		
		$this->acl[] = $this->gacl_api->add_acl(array('test_aco'=>array('access')),NULL,array($this->aro_group['jedi']),array('test_location'=>array('cockpit')),NULL,1,1,NULL,'Jedi are allowed in the cockpit');
		
		$this->acl[] = $this->gacl_api->add_acl(array('test_aco'=>array('access')),array('test_human'=>array('luke')),NULL,array('test_location'=>array('guns')),NULL,1,1,NULL,'Luke can access the guns');
		
		$this->acl[] = $this->gacl_api->add_acl(array('test_aco'=>array('access')),NULL,array($this->aro_group['engineers']),array('test_location'=>array('engines','guns')),NULL,1,1,NULL,'Engineers can access the engines and guns');
		
	}
	
	function teardown() {
		// delete ACLs
		foreach ($this->acl as $id) {
			$this->gacl_api->del_acl($id);
		}
		
		// delete AXO groups
		foreach (array_reverse($this->axo_group) as $id) {
			$this->gacl_api->del_group($id,TRUE,'AXO');
		}
		
		// delete AXOs
		foreach ($this->axo as $id) {
			$this->gacl_api->del_object($id,'AXO');
		}
		
		// delete AXO sections
		foreach ($this->axo_section as $id) {
			$this->gacl_api->del_object_section($id,'AXO');
		}
		
		// delete ARO groups
		foreach (array_reverse($this->aro_group) as $id) {
			$this->gacl_api->del_group($id,TRUE,'ARO');
		}
		
		// delete AROs
		foreach ($this->aro as $id) {
			$this->gacl_api->del_object($id,'ARO');
		}
		
		// delete ARO sections
		foreach ($this->aro_section as $id) {
			$this->gacl_api->del_object_section($id,'ARO');
		}
		
		// delete ACOs
		foreach ($this->aco as $id) {
			$this->gacl_api->del_object($id,'ACO');
		}
		
		// delete ACO sections
		foreach ($this->aco_section as $id) {
			$this->gacl_api->del_object_section($id,'ACO');
		}
	}
}

class acl_test extends gacl_test_case {
	
	var $acl_setup;
	
	function acl_test($name) {
		$this->gacl_test_case($name);
		$this->acl_setup = &$GLOBALS['acl_setup'];
	}
	
	function test_check_luke_lounge() {
		$result = $this->gacl_api->acl_check('test_aco','access','test_human','luke','test_location','lounge');
		$message = 'Luke should have access to the Lounge';
		$this->assertEquals(TRUE, $result, $message);
	}
	
	function test_check_luke_engines() {
		$result = $this->gacl_api->acl_check('test_aco','access','test_human','luke','test_location','engines');
		$message = 'Luke shouldn\'t have access to the Engines';
		$this->assertEquals(FALSE, $result, $message);
	}
	
	function test_check_chewie_guns() {
		$result = $this->gacl_api->acl_check('test_aco','access','test_alien','chewie','test_location','guns');
		$message = 'Chewie should have access to the Guns';
		$this->assertEquals(TRUE, $result, $message);
	}
	
	function test_check_chewie_engines() {
		$result = $this->gacl_api->acl_check('test_aco','access','test_alien','chewie','test_location','engines');
		$message = 'Chewie shouldn\'t have access to the Engines';
		$this->assertEquals(FALSE, $result, $message);
	}
	
	function test_query_luke_lounge() {
		$result = $this->gacl_api->acl_query('test_aco','access','test_human','luke','test_location','lounge');
		$expected = array(
			'acl_id' => $this->acl_setup->acl[2],
			'return_value' => '',
			'allow' => TRUE
		);
		$message = 'Luke should have access to the Lounge';
		
		$this->assertEquals($expected, $result, $message);
	}
}

// check no previous tests failed
if ( $result->failureCount() > 0 )
{
	echo '<p>Previous test failed, not running ACL check tests.</p>';
	return;
}

echo '<p>Running ACL tests...<br />';

// set up test environment
$acl_setup = new acl_setup;

echo '&nbsp;&nbsp;Setting Up... ';
$acl_setup->setup();
echo 'Done<br/>';

// run tests & destroy suite
$suite = new gacl_test_suite('acl_test');

echo '&nbsp;&nbsp;Running Tests... ';
$suite->run($result);
echo 'Done<br />';
unset ($suite);

// tear down test environment;
echo '&nbsp;&nbsp;Cleaning Up... ';
$acl_setup->teardown();
unset ($acl_setup);
echo 'Done<br />';

echo '<b>Done</b></p>';
// done.

?>