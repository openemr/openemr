<?php

require_once(dirname(__FILE__) . "/../../interface/globals.php");

use OpenEMR\Common\Http\oeHttp;

error_log("DEBUG1: " . $_SERVER['HTTP_HOST'] . " : " . $GLOBALS['web_root']);

//$response = oeHttp::usingBaseUri($_SERVER['HTTP_HOST'] . $GLOBALS['web_root'] . '/apis/api/')->get('facility/1');

//fix for when using localhost with a port when using the server
//does make me wonder if needing to use the server name for local api's will be very tough sometimes to support, though
// (for example, if trying to run this through a php-fpm instance that does not include apache/nginx then not sure how it would work)
$server = (preg_match('/^localhost:[0-9]+$/', $_SERVER['HTTP_HOST'])) ? 'localhost' : $_SERVER['HTTP_HOST'];

$response = oeHttp::bodyFormat('body')->get($server . $GLOBALS['web_root'] . '/apis/api/facility');
echo $response->body();
