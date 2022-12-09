<?php
/*
 *	$Id: wsdlclient5.php,v 1.4 2007/11/06 14:49:10 snichol Exp $
 *
 *	WSDL client sample.
 *
 *	Service: WSDL
 *	Payload: rpc/encoded
 *	Transport: http
 *	Authentication: none
 */
require_once('../lib/nusoap.php');
require_once('../lib/class.wsdlcache.php');
$proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
$proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
$proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
$proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
$useCURL = isset($_POST['usecurl']) ? $_POST['usecurl'] : '0';

$cache = new wsdlcache('.', 60);
$wsdl = $cache->get('http://www.xmethods.net/sd/2001/BNQuoteService.wsdl');
if (is_null($wsdl)) {
	$wsdl = new wsdl('http://www.xmethods.net/sd/2001/BNQuoteService.wsdl',
					$proxyhost, $proxyport, $proxyusername, $proxypassword,
					0, 30, null, $useCURL);
	$err = $wsdl->getError();
	if ($err) {
		echo '<h2>WSDL Constructor error (Expect - 404 Not Found)</h2><pre>' . $err . '</pre>';
		echo '<h2>Debug</h2><pre>' . htmlspecialchars($wsdl->getDebug(), ENT_QUOTES) . '</pre>';
		exit();
	}
	$cache->put($wsdl);
} else {
	$wsdl->clearDebug();
	$wsdl->debug('Retrieved from cache');
}
$client = new nusoap_client($wsdl, 'wsdl',
						$proxyhost, $proxyport, $proxyusername, $proxypassword);
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	exit();
}
$client->setUseCurl($useCURL);
$params = array('isbn' => '0060188782');
$result = $client->call('getPrice', $params);
// Check for a fault
if ($client->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
} else {
	// Check for errors
	$err = $client->getError();
	if ($err) {
		// Display the error
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
		// Display the result
		echo '<h2>Result</h2><pre>';
		print_r($result);
		echo '</pre>';
	}
}
echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo '<h2>Cache Debug</h2><pre>' . htmlspecialchars($cache->getDebug(), ENT_QUOTES) . '</pre>';
echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
?>
