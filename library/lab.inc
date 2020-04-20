<?php

/**
 * lab.inc
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2010 OpenEMR Support LLC
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


/**
 * @param $pid
 * @param $encounter
 * @return mixed
 */
function fetchProcedureId($pid, $encounter)
{
    $sql = "SELECT procedure_order_id FROM procedure_order WHERE patient_id = ? AND encounter_id = ?";
    $res = sqlQuery($sql, array($pid,$encounter));

    return $res['procedure_order_id'];
}

/**
 * @param $oid
 * @param $encounter
 * @return array
 */
function getProceduresInfo($oid, $encounter)
{

    $sql = "SELECT pc.procedure_order_id, pc.procedure_order_seq, pc.procedure_code, pc.procedure_name, 
	 pc.diagnoses, po.provider_id, po.date_collected,po.lab_id, po.clinical_hx, po.date_ordered, po.patient_instructions, po.specimen_type, 
	 po.specimen_location, po.specimen_volume
     FROM procedure_order_code AS pc  
     JOIN procedure_order AS po ON pc.procedure_order_id 
	 AND po.procedure_order_id 
     WHERE pc.procedure_order_id = ? 
	 AND po.encounter_id = ?
	 AND po.procedure_order_id = ?";

    $listOrders = sqlStatement($sql, array($oid,$encounter,$oid));
    $orders = array();
    while ($rows = sqlFetchArray($listOrders)) {
        $orders[] = $rows['procedure_order_id'];
        $orders[] = $rows['procedure_order_seq'];
        $orders[] = $rows['procedure_code'];
        $orders[] = $rows['procedure_name'];
        $orders[] = $rows['diagnoses'];
        $orders[] = $rows['provider_id'];
        $orders[] = $rows['date_collected'];
        $orders[] = $rows['lab_id'];            //procedure_order.ppid
        $orders[] = $rows['clinical_hx'];
        $orders[] = $rows['date_ordered'];
        $orders[] = $rows['patient_instructions'];
        $orders[] = $rows['specimen_type'];
        $orders[] = $rows['specimen_location'];
        $orders[] = $rows['specimen_volume'];
    }

    return $orders;
}

/**
 * @param $pid
 * @return mixed
 */

function getSelfPay($pid)
{
    $sql = "SELECT subscriber_relationship FROM insurance_data WHERE pid = ?";
    $res = sqlQuery($sql, array($pid));

    return $res['subscriber_relationship'];
}

/**
 * @param $prov_id
 * @return array
 */
function getNPI($prov_id)
{
    $sql = "SELECT npi, upin FROM users WHERE id = ?";
    $res = sqlQuery($sql, array($prov_id));
    return array($res['npi'], $res['upin']);
}

/**
 * @return array
 */
function getProcedureProvider($prov_id)
{
    $sql = "SELECT i.organization, i.street, i.city, i.state, i.zip, i.fax, i.phone, pi.lab_director " .
           "FROM users AS i, procedure_providers AS pi WHERE pi.ppid = ? AND pi.lab_director = i.id ";

    $res = sqlStatement($sql, array($prov_id));
    $labs = sqlFetchArray($res);

    return $labs;
}

/**
 * @param $prov_id
 * @return array|null
 */
function getLabProviders($prov_id)
{

    $sql = "select fname, lname from users where authorized = 1 and active = 1 and username != '' and id = ?";
    $rez = sqlQuery($sql, array($prov_id));


    return $rez;
}

/*
* This is going to be adjusted when there is more than one provider.
*/
function getLabconfig()
{
    $sql = "SELECT recv_app_id, recv_fac_id FROM procedure_providers ";
    $res = sqlQuery($sql);
    return $res;
}

function saveBarCode($bar, $pid, $order)
{
    $sql = "INSERT INTO `requisition` (`id`, `req_id`, `pid`, `lab_id`) VALUES (NULL, ?, ?, ?)";
    $inarr = array($bar,$pid,$order);
    sqlStatement($sql, $inarr);
}

function getBarId($lab_id, $pid)
{
    $sql = "SELECT req_id FROM requisition WHERE lab_id = ? AND pid = ?";
    $bar = sqlQuery($sql, array($lab_id,$pid));

    return $bar;
}

/**
 *
 * @param <type> $facilityID
 * @return <type> the result set, false if the input is malformed
 */
function getFacilityInfo($facilityID)
{
    // facility ID will be in the format XX_YY, where XX is the lab-assigned id, Y is the user.id record representing that lab facility, and the _ is a divider.
    $facility = explode("_", $facilityID);

    if (count($facility) > 1) {
        $query = "SELECT title, fname, lname, street, city, state, zip, organization, phone FROM users WHERE id = ?";

        $res = sqlStatement($query, array($facility[1]));
        return sqlFetchArray($res);
    }

    return false;
}

function formatPhone($phone)
{
    $phone = preg_replace("/[^0-9]/", "", $phone);
    if (strlen($phone) == 7) {
        return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
    } elseif (strlen($phone) == 10) {
        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
    } else {
        return $phone;
    }
}
