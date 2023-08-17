<?php

/**
 * Ajax interface for custom template context.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../interface/globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$cq = "SELECT
    cl2.cl_list_slno id,
    cl2.cl_list_item_long text,
    cl2.cl_list_item_short short,
    cl3.cl_list_slno cat_id,
    cl3.cl_list_item_long cat_text,
    cl3.cl_creator cat_ownerid,
    IFNULL(cl4.cl_list_slno, '') tmpl_id,
    IFNULL(cl4.cl_list_item_long, '') tmpl_text,
    IFNULL(cl4.cl_creator, '') tmpl_ownerid
FROM customlists cl2
INNER JOIN customlists cl3 ON cl2.cl_list_slno = cl3.cl_list_id
LEFT OUTER JOIN customlists cl4 ON cl3.cl_list_slno = cl4.cl_list_id
    AND cl4.cl_list_type = 4
    AND cl4.cl_deleted = 0
WHERE cl2.cl_list_type = 2
    AND cl2.cl_deleted = 0
    AND cl3.cl_list_type = 3
    AND cl3.cl_deleted = 0
    AND cl2.cl_list_item_long LIKE ?
    AND cl3.cl_creator = ?
GROUP BY
    cl2.cl_list_item_long";

$search = $_GET['search'];
$eSearch = "%" . $search . "%";
$results = [];
$r = sqlStatementNoLog($cq, array($eSearch, (int)$_SESSION['authUserID']));

while ($result = sqlFetchArray($r)) {
    $results[] = array_map('text', $result);
}

echo json_encode(array('results' => $results));
die();
