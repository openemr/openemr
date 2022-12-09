<?php
/*
 *	$Id: wsdlclient15.php,v 1.1 2008/02/12 00:13:50 snichol Exp $
 *
 *	UTF-8 client sample that sends and receives data with characters UTF-8 encoded.
 *
 *	Service: WSDL
 *	Payload: document/literal
 *	Transport: http
 *	Authentication: none
 */
require_once('../lib/nusoap.php');
$proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
$proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
$proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
$proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
$useCURL = isset($_POST['usecurl']) ? $_POST['usecurl'] : '0';
$client = new nusoap_client('http://www.scottnichol.com/samples/helloutf8.php?wsdl', 'wsdl',
						$proxyhost, $proxyport, $proxyusername, $proxypassword);
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	echo '<h2>Debug</h2>';
	echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
	exit();
}
$client->setUseCurl($useCURL);
$client->soap_defencoding = 'UTF-8';
$client->decode_utf8 = false;
$utf8string = array('stuff' => "\xc2\xa9\xc2\xae\xc2\xbc\xc2\xbd\xc2\xbe");
$result = $client->call('echoback', $utf8string);
if ($client->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
} else {
	$err = $client->getError();
	if ($err) {
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
		echo '<h2>Result</h2><pre>';
		// Decode the result: it so happens we sent Latin-1 characters
		if (isset($result['return'])) {
			$result1 = utf8_decode($result['return']);
		} elseif (!is_array($result)) {
			$result1 = utf8_decode($result);
		} else {
			$result1 = $result;
		}
		print_r($result1);
		echo '</pre>';
	}
}
echo '<h2>Request</h2>';
echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2>';
echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo '<h2>Debug</h2>';
echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
?>
