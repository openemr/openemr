<?php
require_once(dirname(__FILE__) . "/../freeb/xmlrpc.inc");
require_once(dirname(__FILE__) . "/../freeb/xmlrpcs.inc");

class WSWrapper {
	
	var $_config;
	var $value;

	function WSWrapper($function,$send = true) {
		$this->_config = $GLOBALS['oer_config']['ws_accounting'];
		//print_r($this->_config);
		if (!$this->_config['enabled']) return;
		
		if ($send) {
			$this->send($function);	
		}		
	}
	
	function send($function) {
		list($name,$var) = each($function);
		$f=new xmlrpcmsg($name,$var);
		//print "<pre>" . htmlentities($f->serialize()) . "</pre>\n";
		$c=new xmlrpc_client($this->_config['url'], $this->_config['server'],$this->_config['port']);
		$c->setCredentials($this->_config['username'],$this->_config['password']);
		//$c->setDebug(1);
		$r=$c->send($f);
		if (!$r) { echo("send failed"); }
		$tv=$r->value();
		if (is_object($tv)) {
			$this->value = $tv->getval();
		}
		else {
			$this->value = null;	
		}
		
		if ($r->faultCode()) {
			echo "<HR>Fault: ";
			echo "Code: " . $r->faultCode() . " Reason '" .$r->faultString()."'<BR>";
		}	
	}
}
?>
