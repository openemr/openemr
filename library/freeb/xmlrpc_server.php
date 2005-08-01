<?php
require_once("xmlrpc.inc");
require_once("xmlrpcs.inc");
require_once("OpenemrBillingServer.class.php");

require_once(dirname(__FILE__) . "/../../includes/config.php");

$auth=false; 

if ($_SERVER['PHP_AUTH_USER'] == $GLOBALS['oer_config']['freeb']['username'] && $_SERVER['PHP_AUTH_PW'] == $GLOBALS['oer_config']['freeb']['password']) {
	$auth = true;
}


session_start();

$oerbill = new OpenemrBillingServer($GLOBALS['xmlrpcerruser']);

$s=new xmlrpc_server(false,false);

if (! $auth) {
 header('WWW-Authenticate: Basic realm="Unauthorized Access Prohibited"');
 header("HTTP/1.0 401 Unauthorized");
 return new xmlrpcresp(0, &$oerbill->xmlrpcerruser, $GLOBALS['xmlrpcerruser']); 
}

$s->registerMethods(&$oerbill);
$s->service();


?>