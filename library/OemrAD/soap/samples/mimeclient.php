<?php
/*
 *	$Id: mimeclient.php,v 1.6 2007/04/16 22:08:17 snichol Exp $
 *
 *	MIME client sample.
 *
 *	Service: SOAP endpoint
 *	Payload: rpc/encoded
 *	Transport: http
 *	Authentication: none
 */
require_once('../lib/nusoap.php');
require_once('../lib/nusoapmime.php');
$proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
$proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
$proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
$proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
$useCURL = isset($_POST['usecurl']) ? $_POST['usecurl'] : '0';
$client = new nusoap_client_mime('http://www.scottnichol.com/samples/mimetest.php', false,
							$proxyhost, $proxyport, $proxyusername, $proxypassword);
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}
$client->setUseCurl($useCURL);
$client->setHTTPEncoding('deflate, gzip');
$cid = $client->addAttachment('', 'mimeclient.php');
$result = $client->call('hello', array('name' => 'Scott'));
if ($client->fault) {
	echo '<h2>Fault</h2><pre>'; print_r($result); echo '</pre>';
} else {
	$err = $client->getError();
	if ($err) {
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
		echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
		echo '<h2>Attachments</h2><pre>';
		$attachments = $client->getAttachments();
		foreach ($attachments as $a) {
			echo 'Filename: ' . $a['filename'] . "\r\n";
			echo 'Content-Type: ' . $a['contenttype'] . "\r\n";
			echo 'cid: ' . htmlspecialchars($a['cid'], ENT_QUOTES) . "\r\n";
			echo htmlspecialchars($a['data'], ENT_QUOTES);
			echo "\r\n";
		}
		echo '</pre>';
	}
}
echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo '<h2>ResponseData</h2><pre>' . htmlspecialchars($client->responseData, ENT_QUOTES) . '</pre>';
echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
?>
