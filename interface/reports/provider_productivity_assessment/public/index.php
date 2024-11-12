<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)  Juggernaut Systems Express
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

require_once dirname(__DIR__) . '/src/DataQuery.php';
require_once dirname(__DIR__, 3) . "/globals.php";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__, 4) . '/library/formatting.inc.php';


$path = dirname(__DIR__) . '/templates';

function render_template($templateName, $data = []): void
{
    extract($data);
    include dirname(__DIR__) . "/templates/{$templateName}.php";
}

$headerData = [
    'title' => 'Provider Productivity Assessment',
];
render_template('header', $headerData);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = new OpenEMR\Reports\Productivity\DataQuery();
    $data->fromDate = DateToYYYYMMDD($_POST['fromDate'] ?? '');
    $data->toDate = DateToYYYYMMDD($_POST['toDate'] ?? '');
    $reportData = $data->getReportData();
    render_template('mainContent', $reportData);
} else {
   $reportData = [
       'message' => 'Please select a date range to view the report.'
   ];
    render_template('mainContent', $reportData);
}

$footerData = [
    'company' => 'Juggernaut Systems Express',
    'year' => date('Y')
];
render_template('footer', $footerData);
