<?php

/*
 *
 *   package   OpenEMR
 *   link      http://www.open-emr.org
 *   author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)
 *   All rights reserved
 */

use Juggernaut\Module\Bamboo\Controllers\GatewayRequests;
use Juggernaut\Module\Bamboo\Controllers\ReportDataRequestMinXmlBuilder;

require_once dirname(__FILE__, 5) . '/globals.php';

$xmlBuilderMin = new ReportDataRequestMinXmlBuilder();
$gatewayRequests = new GatewayRequests($xmlBuilderMin);
$gatewayRequests->url = $_POST['url'];

//fetching the report data
$report = $gatewayRequests->fetchReportData($gatewayRequests->url);

libxml_use_internal_errors(TRUE);
$displayReport = simplexml_load_string($report);
$report = json_encode($displayReport);
$reportDisplay = json_decode($report, TRUE);

echo $reportDisplay['ReportLink'];
