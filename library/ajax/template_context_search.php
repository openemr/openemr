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

$cq = <<< createQuery
Select
    cl2.cl_list_slno id,
    cl2.cl_list_item_long text,
    cl3.cl_list_slno cat_id,
    cl3.cl_list_item_long cat_text,
    cl3.cl_creator cat_ownerid,
    IfNull(cl4.cl_list_slno, '') tmpl_id,
    IfNull(cl4.cl_list_item_long, '') tmpl_text,
    IfNull(cl4.cl_creator, '') tmpl_ownerid
From
    customlists cl2 Inner Join
    customlists cl3 On cl2.cl_list_slno = cl3.cl_list_id Left Outer Join
    customlists cl4 On cl3.cl_list_slno = cl4.cl_list_id And cl4.cl_list_type = 4 And cl4.cl_deleted = 0
Where
    cl2.cl_list_type = 2 And
    cl2.cl_deleted = 0 And
    cl3.cl_list_type = 3 And
    cl3.cl_deleted = 0 And
    cl2.cl_list_item_long Like ? And
    cl3.cl_creator = ?
Group By
    cl2.cl_list_item_long
createQuery;

$search = $_GET['search'];
$eSearch = "%" . $search . "%";
$results = [];
$r = sqlStatementNoLog($cq, array($eSearch, (int)$_SESSION['authUserID']));

while ($result = sqlFetchArray($r)) {
    $results[] = array_map('text', $result);
}

echo json_encode(array('results' => $results));
die();
