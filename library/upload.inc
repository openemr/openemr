<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

function send_file ($fp,$size)
{
	global $billing_server;
	global $billing_user;
	global $billing_pass;

	if ($billing_server == "" || $billing_user == "" || $billing_pass == "") {
		
		include_once("{$GLOBALS["srcdir"]}/need_service.inc");

		return FALSE;
	}

	$url = "$billing_server/~$billing_user/";

	return actually_send_file($fp,$size,$url,$billing_user,$billing_pass);
}

function actually_send_file ($fp, $size, $url, $user, $pass)
{

	$filename = date("Y-m-d-G:i:s");

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url.$filename);
	curl_setopt ($ch, CURLOPT_SSLVERSION, 3);
	curl_setopt ($ch, CURLOPT_UPLOAD, 1);
	curl_setopt ($ch, CURLOPT_PUT, 1);
	curl_setopt ($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt ($ch, CURLOPT_INFILE, $fp);
	curl_setopt ($ch, CURLOPT_INFILESIZE, $size);
	curl_setopt ($ch, CURLOPT_USERPWD, "$user:$pass");
	curl_setopt ($ch, CURLOPT_MUTE, 0);
	echo "<!--\n";
	$ret = curl_exec ($ch);
	curl_close($ch);
	echo "-->\n";

	return $ret;

}

