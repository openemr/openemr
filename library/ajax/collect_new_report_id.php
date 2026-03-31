<?php

/**
 * Collect/bookmark a new report id in report_results sql table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../interface/globals.php");
require_once(__DIR__ . "/../report_database.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"], session: $session)) {
    CsrfUtils::csrfNotVerified();
}

//  Collect/bookmark a new report id in report_results sql table and send it back.
echo bookmarkReportDatabase();
