<?php
/*
 *	$Id: wsdlclient4.php,v 1.6 2005/05/12 21:42:06 snichol Exp $
 *
 *	WSDL client sample, based on soap builders round 2 interop.
 *
 *	Service: WSDL
 *	Payload: rpc/encoded
 *	Transport: http
 *	Authentication: none
 */
require_once('../lib/nusoap.php');
/*
 *	Grab post vars, if present
 */
$method = isset($_POST['method']) ? $_POST['method'] : '';
$null = isset($_POST['null']) ? $_POST['null'] : '';
$empty = isset($_POST['empty']) ? $_POST['empty'] : '';
$proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
$proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
$proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
$proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
/*
 *	When no method has been specified, give the user a choice
 */
if ($method == '') {
	echo '<form name="MethodForm" method="POST">';
	echo '<input type="hidden" name="proxyhost" value="' . $proxyhost .'">';
	echo '<input type="hidden" name="proxyport" value="' . $proxyport .'">';
	echo '<input type="hidden" name="proxyusername" value="' . $proxyusername .'">';
	echo '<input type="hidden" name="proxypassword" value="' . $proxypassword .'">';
	echo 'Method: <select name="method">';
	echo '<option>echoString</option>';
	echo '<option>echoStringArray</option>';
	echo '<option>echoInteger</option>';
	echo '<option>echoIntegerArray</option>';
	echo '<option>echoFloat</option>';
	echo '<option>echoFloatArray</option>';
	echo '<option>echoStruct</option>';
	echo '<option>echoStructArray</option>';
	echo '<option>echoVoid</option>';
	echo '<option>echoBoolean</option>';
	echo '<option>echoBase64</option>';
	echo '</select><br><br>';
	echo 'Null parameter? <input type="checkbox" name="null" value="1"><br>';
	echo 'Empty array? <input type="checkbox" name="empty" value="1"><br><br>';
	echo '<input type="submit" value="&#160;Execute&#160;">';
	echo '</form>';
	exit();
}
/*
 *	Execute the specified method
 */
if ($method == 'echoString') {
	if ($null != '1') {
		$params = array('inputString' => 'If you cannot echo a string, you probably cannot do much');
	} else {
		$params = array('inputString' => null);
	}
} elseif ($method == 'echoStringArray') {
	if ($null != '1') {
		if ($empty != '1') {
			$params = array('inputStringArray' => array('String 1', 'String 2', 'String Three'));
		} else {
			$params = array('inputStringArray' => array());
		}
	} else {
		$params = array('inputStringArray' => null);
	}
} elseif ($method == 'echoInteger') {
	if ($null != '1') {
		$params = array('inputInteger' => 329);
	} else {
		$params = array('inputInteger' => null);
	}
} elseif ($method == 'echoIntegerArray') {
	if ($null != '1') {
		if ($empty != '1') {
			$params = array('inputIntegerArray' => array(451, 43, -392220011, 1, 1, 2, 3, 5, 8, 13, 21));
		} else {
			$params = array('inputIntegerArray' => array());
		}
	} else {
		$params = array('inputIntegerArray' => null);
	}
} elseif ($method == 'echoFloat') {
	if ($null != '1') {
		$params = array('inputFloat' => 3.14159265);
	} else {
		$params = array('inputFloat' => null);
	}
} elseif ($method == 'echoFloatArray') {
	if ($null != '1') {
		if ($empty != '1') {
			$params = array('inputFloatArray' => array(1.1, 2.2, 3.3, 1/4, -1/9));
		} else {
			$params = array('inputFloatArray' => array());
		}
	} else {
		$params = array('inputFloatArray' => null);
	}
} elseif ($method == 'echoStruct') {
	if ($null != '1') {
		$struct = array('varString' => 'who', 'varInt' => 2, 'varFloat' => 3.14159);
		$params = array('inputStruct' => $struct);
	} else {
		$params = array('inputStruct' => null);
	}
} elseif ($method == 'echoStructArray') {
	if ($null != '1') {
		if ($empty != '1') {
			$structs[] = array('varString' => 'who', 'varInt' => 2, 'varFloat' => 3.14159);
			$structs[] = array('varString' => 'when', 'varInt' => 4, 'varFloat' => 99.9876);
			$params = array('inputStructArray' => $structs);
		} else {
			$params = array('inputStructArray' => array());
		}
	} else {
		$params = array('inputStructArray' => null);
	}
} elseif ($method == 'echoVoid') {
	$params = array();
} elseif ($method == 'echoBoolean') {
	if ($null != '1') {
		$params = array('inputBoolean' => false);
	} else {
		$params = array('inputBoolean' => null);
	}
} elseif ($method == 'echoBase64') {
	if ($null != '1') {
		$params = array('inputBase64' => base64_encode('You must encode the data you send; NuSOAP will automatically decode the data it receives'));
	} else {
		$params = array('inputBase64' => null);
	}
} else {
	echo 'Sorry, I do not know about method ' . $method;
	exit();
}
$client = new soapclient('http://www.scottnichol.com/samples/round2_base_server.php?wsdl&debug=1', true,
						$proxyhost, $proxyport, $proxyusername, $proxypassword);
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}
$client->useHTTPPersistentConnection();
echo '<h2>Execute ' . $method . '</h2>';
$result = $client->call($method, $params);
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
		print_r((!is_bool($result)) ? $result : ($result ? 'true' : 'false'));
		echo '</pre>';
		// And execute again to test persistent connection
		echo '<h2>Execute ' . $method . ' again to test persistent connection (see debug)</h2>';
		$client->debug("*** execute again to test persistent connection ***");
		$result = $client->call($method, $params);
		// And again...
		$client->debug("*** execute again ... ***");
		$result = $client->call($method, $params);
	}
}
echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
?>
