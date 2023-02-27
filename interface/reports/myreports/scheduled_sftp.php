<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
// ALL ERRORS ROUTED TO THE LOG AND DISPLAY

error_reporting(E_ALL ^ E_NOTICE);
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors',1);

if(defined('STDIN')) {
	parse_str(implode('&', array_slice($argv,1)), $_GET);
}

$current_path = get_include_path();
if(strpos($current_path, 'phpseclib') === false) {
	set_include_path($current_path . PATH_SEPARATOR . 
		'/var/www/oemrtest/library/phpseclib/');
}

include('Net/SSH2.php');
include('Net/SFTP.php');

$here = dirname(__FILE__);
print "We are here: $here\n";
chdir('/var/hl7/ADT/out');
$here = getcwd();
print "Now We are here: $here\n";

$user = '66314!sftp';
$pass = 'fzFewkH4P';
$port = '933';
$host = 'Sftp.pathgroup.com';
// ob_start();

$sftp = new Net_SFTP($host, $port);
if(!$sftp->login($user, $pass)) {
	throw new Exception("sFTP session Could Not Initialize!!");
}

// DO THE SFTP ROUTINE HERE 
if($dirh = opendir('.')) {
	while(($fh = readdir($dirh)) !== false) {
		if($fh == '.' || $fh == '..') { continue; }
		$sftp->put($fh, $fh, NET_SFTP_LOCAL_FILE);	
	}	
}

// $output = ob_get_clean();
// $status = file_put_contents('/home/richg/auto_approve.log', $output);
// if($status === false) die('Failed to Create Log');

print "Wrote $status bytes to the auto_approve.log...\n\n";

exit;

