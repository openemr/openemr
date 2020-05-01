<?php

/**
 * Controller for AJAX requests to search for codes from the fee sheet
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../globals.php");
require_once("fee_sheet_classes.php");
require_once("fee_sheet_search_queries.php");

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('acct', 'bill')) {
    header("HTTP/1.0 403 Forbidden");
    echo "Not authorized for billing";
    return false;
}

if (isset($_REQUEST['search_query'])) {
    $search_query = $_REQUEST['search_query'];
} else {
    header("HTTP/1.0 403 Forbidden");
    echo "No search parameter specified";
    return false;
}

if (isset($_REQUEST['search_type'])) {
    $search_type = $_REQUEST['search_type'];
} else {
    $search_type = 'ICD9';
}

if (isset($_REQUEST['search_type_id'])) {
    $search_type_id = $_REQUEST['search_type_id'];
} else {
    $search_type_id = 2;
}

$retval['codes'] = diagnosis_search($search_type_id, $search_type, $search_query);

echo json_encode($retval);
