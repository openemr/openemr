<?php

require_once(dirname(__FILE__) . "/../../interface/globals.php");

// GUZZLE CALL OF API
//$response = oeHttp::usingBaseUri($_SERVER['HTTP_HOST'] . $GLOBALS['web_root'] . '/apis/api/')->get('facility/1');
//fix for when using localhost with a port when using the server
//does make me wonder if needing to use the server name for local api's will be very tough sometimes to support, though
// (for example, if trying to run this through a php-fpm instance that does not include apache/nginx then not sure how it would work)
use OpenEMR\Common\Http\oeHttp;

error_log("DEBUG1: " . $_SERVER['HTTP_HOST'] . " : " . $GLOBALS['web_root']);
$server = (preg_match('/^localhost:[0-9]+$/', $_SERVER['HTTP_HOST'])) ? 'localhost' : $_SERVER['HTTP_HOST'];
$response = oeHttp::setLocalApiContext(true)->get($server . $GLOBALS['web_root'] . '/apis/api/facility');
echo $response->body();


// BYPASS GUZZLE AND DIRECTLY CALL API
use OpenEMR\Common\Http\HttpRestRouteHandler;

require_once(dirname(__FILE__) . "/../../_rest_config.php");
$gbl = RestConfig::GetInstance();
HttpRestRouteHandler::dispatch($gbl::$ROUTE_MAP, '/api/facility', "GET");
