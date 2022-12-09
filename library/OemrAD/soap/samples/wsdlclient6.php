<?php
/*
 *	$Id: wsdlclient6.php,v 1.1 2004/01/26 07:15:20 snichol Exp $
 *
 *	WSDL client sample.
 *
 *	Service: WSDL
 *	Payload: rpc/encoded
 *	Transport: http
 *	Authentication: none
 */
require_once('../lib/nusoap.php');
$proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
$proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
$proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
$proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
echo 'You must change the source code to specify the location of the WSDL!'; exit();
$client = new soapclient('file://f:/googleapi/GoogleSearch.wsdl', true,
						$proxyhost, $proxyport, $proxyusername, $proxypassword);
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}
$client->soap_defencoding = 'UTF-8';

echo 'You must set your own Google key in the source code to run this client!'; exit();
$key = 'set your own Google key';
$q = '"Lies and the Lying"';
$start = 1;
$maxResults = 10;
$filter = false;
$restrict = '';
$safeSearch = false;
$lr = '';
$ie = '';
$oe = '';

$params = array(
	'key' => $key, 'q' => $q, 'start' => $start, 'maxResults' => $maxResults,
	'filter' => $filter, 'restrict' => $restrict, 'safeSearch' => $safeSearch, 'lr' => $lr,
	'ie' => $ie, 'oe' => $oe
	);

$result = $client->call('doGoogleSearch', $params);
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
echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
?>
