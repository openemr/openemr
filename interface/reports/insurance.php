<?php
/**
 * Financial Report initial page
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once '../globals.php';

use OpenEMR\FinancialReports\FinancialSummaryByInsuranceController;

$page = new FinancialSummaryByInsuranceController();
echo $page->insurancepaid();


