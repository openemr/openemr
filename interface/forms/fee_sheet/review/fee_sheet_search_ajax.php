<?php

/**
 * Controller for AJAX requests to search for codes from the fee sheet
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../globals.php");
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

$search_type = $_REQUEST['search_type'] ?? 'ICD9';

$search_type_id = $_REQUEST['search_type_id'] ?? 2;

$retval['codes'] = diagnosis_search($search_type_id, $search_type, $search_query);

echo json_encode($retval);
