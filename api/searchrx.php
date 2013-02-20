<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<RXreport>";

$token = $_POST['token'];
$form_from_date = $_POST['form_from_date'];
$form_to_date = $_POST['form_to_date'];
$form_patient_id = $_POST['patient_id'];
$form_drug_name = $_POST['drug_name'];
$form_lot_number = $_POST['lot_number'];
$form_facility = $_POST['facility'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'med', $user);

    if ($acl_allow) {
        $where = "r.date_modified >= '$form_from_date' AND " .
                "r.date_modified <= '$form_to_date'";
        if ($form_patient_id)
            $where .= " AND p.pid = '$form_patient_id'";
        if ($form_drug_name)
            $where .= " AND (d.name LIKE '$form_drug_name' OR r.drug LIKE '$form_drug_name')";
        if ($form_lot_number)
            $where .= " AND i.lot_number LIKE '$form_lot_number'";

        $query = "SELECT r.id, r.patient_id, " .
                "r.date_modified, r.dosage, r.route, r.interval, r.refills, r.drug, " .
                "d.name, d.ndc_number, d.form, d.size, d.unit, d.reactions, " .
                "s.sale_id, s.sale_date, s.quantity, " .
                "i.manufacturer, i.lot_number, i.expiration, " .
                "p.pubpid, " .
                "p.fname, p.lname, p.mname, u.facility_id " .
                "FROM prescriptions AS r " .
                "LEFT OUTER JOIN drugs AS d ON d.drug_id = r.drug_id " .
                "LEFT OUTER JOIN drug_sales AS s ON s.prescription_id = r.id " .
                "LEFT OUTER JOIN drug_inventory AS i ON i.inventory_id = s.inventory_id " .
                "LEFT OUTER JOIN patient_data AS p ON p.pid = r.patient_id " .
                "LEFT OUTER JOIN users AS u ON u.id = r.provider_id " .
                "WHERE $where " .
                "ORDER BY p.lname, p.fname, p.pubpid, r.id, s.sale_id";

        $result = sqlStatement($query);


        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Search Processed successfully</reason>";

            while ($row = sqlFetchArray($result)) {

                if ($form_facility !== '') {
                    if ($form_facility) {
                        if ($row['facility_id'] != $form_facility)
                            continue;
                    }else {
                        if (!empty($row['facility_id']))
                            continue;
                    }
                }
                $xml_string .= "<record>\n";
                foreach ($row as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $xml_string .= "</record>\n";
            }
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Could find results</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</RXreport>";
echo $xml_string;
?>