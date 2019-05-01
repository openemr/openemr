<?php

require_once(dirname(__FILE__) . "/../../interface/globals.php");


// Looking into how to use the api locally via php calls (4 methods are shown below)


// GUZZLE CALL OF API
//  noted needed fix for when using localhost with a port when using the server
//  does make me wonder if needing to use the server name for local api's will be very tough, sometimes impossible, to support, though
//   (for example, if trying to run this through a php-fpm instance that does not include apache/nginx then not sure how it would work)
use OpenEMR\Common\Http\oeHttp;

error_log("DEBUG1: " . $_SERVER['HTTP_HOST'] . " : " . $GLOBALS['web_root']);
$server = (preg_match('/^localhost:[0-9]+$/', $_SERVER['HTTP_HOST'])) ? 'localhost' : $_SERVER['HTTP_HOST'];
$response = oeHttp::setLocalApiContext(true)->get($server . $GLOBALS['web_root'] . '/apis/api/facility');
echo $response->body();


// BYPASS GUZZLE AND DIRECTLY CALL API via route handler
//  Needed to comment out pertinent authorization_check call for this to work
use OpenEMR\Common\Http\HttpRestRouteHandler;

require_once(dirname(__FILE__) . "/../../_rest_config.php");
$gbl = RestConfig::GetInstance();
HttpRestRouteHandler::dispatch($gbl::$ROUTE_MAP, '/api/facility', "GET");


// BYPASS GUZZLE AND USE THE SERVICE WITHOUT REST CONTROLLER
use OpenEMR\Services\FacilityService;

echo json_encode((new FacilityService())->getAll());


// BYPASS GUZZLE AND USE THE SERVICE WITH REST CONTROLLER
use OpenEMR\RestControllers\FacilityRestController;

echo json_encode((new FacilityRestController())->getAll());
