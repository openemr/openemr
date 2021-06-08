<?php

require_once("../../../../globals.php");
require_once("../../../../drugs/drugs.inc.php");

use OpenEMR\Common\Acl\AclMain;

function find_contraceptive_methods($contraceptive_code)
{
    $retval = array();
    $code = "IPPFCM:" . $contraceptive_code;
    $sqlSearch = "SELECT name,drugs.drug_id,related_code, selector FROM drugs, drug_templates"
              . " WHERE related_code like ? "
              . " AND drug_templates.drug_id=drugs.drug_id AND drugs.active = 1 AND drugs.consumable = 0 "
              . " ORDER BY drugs.name, drug_templates.selector, drug_templates.drug_id";
    $results = sqlStatement($sqlSearch, array("%" . $code . "%"));
    while ($row = sqlFetchArray($results)) {
        if (!isProductSelectable($row['drug_id'])) {
            continue;
        }
        $rel_codes = explode(";", $row['related_code']);
        $match = false;
        foreach ($rel_codes as $cur_code) {
            if ($cur_code === $code) {
                $match = true;
            }
        }
        if ($match) {
            array_push($retval, array("name" => $row['name'], "drug_id" => $row['drug_id'], "selector" => $row['selector']));
        }
    }
    return $retval;
}

function get_method_description($contraceptive_code)
{
    $sqlSearch = " SELECT code_text FROM codes "
               . " WHERE code_type = 32 "
               . " AND code = ? AND active = 1";
    $results = sqlStatement($sqlSearch, array($contraceptive_code));
    if ($results) {
        $row = sqlFetchArray($results);
        return $row['code_text'];
    }
}

if (!AclMain::aclCheckCore('acct', 'bill')) {
    header("HTTP/1.0 403 Forbidden");
    echo "Not authorized for billing";
    return false;
}

$retval = array();
$methods_lookup = array();
if (isset($_REQUEST['methods'])) {
    $methods = $_REQUEST['methods'];
    foreach ($methods as $method_code) {
        if (!isset($methods_lookup[$method_code])) {
            $list = array();
            $list['products'] = find_contraceptive_methods($method_code);
            $list['method'] = get_method_description($method_code);
            $methods_lookup[$method_code] = $list;
            array_push($retval, $list);
        }
    }
}



echo json_encode($retval);
