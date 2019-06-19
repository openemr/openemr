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

$cq = <<< createQuery
CREATE OR REPLACE VIEW zView_user_templates AS
SELECT cl2.cl_list_slno context_id, cl2.cl_list_item_long context_text,
cl3.cl_list_slno cat_id, cl3.cl_list_item_long cat_text, cl3.cl_creator cat_ownerid,
IFNULL(cl4.cl_list_slno,"") tmpl_id, IFNULL(cl4.cl_list_item_long,"") tmpl_text, IFNULL(cl4.cl_creator,"") tmpl_ownerid
FROM customlists cl2
INNER JOIN customlists cl3 on cl2.cl_list_slno=cl3.cl_list_id
LEFT OUTER JOIN customlists cl4 on cl3.cl_list_slno=cl4.cl_list_id and cl4.cl_list_type=4 and cl4.cl_deleted=0
WHERE cl2.cl_list_type=2 and cl2.cl_deleted=0
and cl3.cl_list_type=3 and cl3.cl_deleted=0
GROUP BY context_id;
createQuery;

require_once(dirname(__FILE__) . '/../../interface/globals.php');

if (!verifyCsrfToken($_GET["csrf_token_form"])) {
    csrfNotVerified();
}

$search = $_GET['search'];
$eSearch = "%" . $search . "%";
$results = [];
$error = sqlStatementNoLog($cq);
$sq = "Select `context_id` as `id`, `context_text` as `text` From `zView_user_templates` Where `context_text` Like ?";

$r = sqlStatementNoLog($sq, array($eSearch));
while ($result = sqlFetchArray($r)) {
    $results[] = array_map('text', $result);
}

$output = array('results' => $results);
echo json_encode($output);
die();
