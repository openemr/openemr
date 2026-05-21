<?php

/**
 * lab.inc
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2023 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2010 OpenEMR Support LLC
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Database\QueryUtils;

/**
 * @param int|string $pid
 * @param int|string $encounter
 * @return mixed
 */
function fetchProcedureId($pid, $encounter): mixed
{
    $res = QueryUtils::querySingleRow(
        "SELECT procedure_order_id FROM procedure_order WHERE patient_id = ? AND encounter_id = ?",
        [$pid, $encounter]
    );
    return $res['procedure_order_id'] ?? null;
}

/**
 * Returns a flat numeric array of procedure order fields.
 * Each code row contributes 14 elements, appended in order:
 * [0]  procedure_order_id   [1]  procedure_order_seq  [2]  procedure_code
 * [3]  procedure_name       [4]  diagnoses            [5]  provider_id
 * [6]  date_collected       [7]  lab_id               [8]  clinical_hx
 * [9]  date_ordered         [10] patient_instructions [11] specimen_type
 * [12] specimen_location    [13] specimen_volume
 * Subsequent rows start at index 14, 28, etc.
 *
 * @param int|string $oid
 * @param int|string $encounter
 * @return array
 */
function getProceduresInfo($oid, $encounter): array
{
    $sql = "SELECT pc.procedure_order_id, pc.procedure_order_seq, pc.procedure_code, pc.procedure_name,
        pc.diagnoses, po.provider_id, po.date_collected, po.lab_id, po.clinical_hx, po.date_ordered,
        po.patient_instructions, po.specimen_type, po.specimen_location, po.specimen_volume
     FROM procedure_order_code AS pc
     JOIN procedure_order AS po ON pc.procedure_order_id = po.procedure_order_id
     WHERE pc.procedure_order_id = ?
       AND po.encounter_id = ?
       AND po.procedure_order_id = ?";

    $rows   = QueryUtils::fetchRecords($sql, [$oid, $encounter, $oid]);
    $orders = [];
    foreach ($rows as $row) {
        $orders[] = $row['procedure_order_id'];
        $orders[] = $row['procedure_order_seq'];
        $orders[] = $row['procedure_code'];
        $orders[] = $row['procedure_name'];
        $orders[] = $row['diagnoses'];
        $orders[] = $row['provider_id'];
        $orders[] = $row['date_collected'];
        $orders[] = $row['lab_id'];            // procedure_order.ppid
        $orders[] = $row['clinical_hx'];
        $orders[] = $row['date_ordered'];
        $orders[] = $row['patient_instructions'];
        $orders[] = $row['specimen_type'];
        $orders[] = $row['specimen_location'];
        $orders[] = $row['specimen_volume'];
    }
    return $orders;
}

/**
 * @param int|string $pid
 * @return string|null
 */
function getSelfPay($pid): ?string
{
    $res = QueryUtils::querySingleRow(
        "SELECT subscriber_relationship FROM insurance_data WHERE pid = ?",
        [$pid]
    );
    return $res['subscriber_relationship'] ?? null;
}

/**
 * @param int|string $prov_id
 * @return array
 */
function getNPI($prov_id): array
{
    $res = QueryUtils::querySingleRow(
        "SELECT npi, upin FROM users WHERE id = ?",
        [$prov_id]
    );
    return [$res['npi'] ?? '', $res['upin'] ?? ''];
}

/**
 * @param int|string $prov_id
 * @return array
 */
function getProcedureProvider($prov_id): array
{
    $res = QueryUtils::querySingleRow(
        "SELECT i.organization, i.street, i.city, i.state, i.zip, i.fax, i.phone, pi.lab_director
         FROM users AS i
         JOIN procedure_providers AS pi ON pi.lab_director = i.id
         WHERE pi.ppid = ?",
        [$prov_id]
    );
    return $res ?: [];
}

/**
 * @param int|string $prov_id
 * @return array|null
 */
function getLabProviders($prov_id): ?array
{
    return QueryUtils::querySingleRow(
        "SELECT fname, lname FROM users
         WHERE authorized = 1 AND active = 1 AND username != '' AND id = ?",
        [$prov_id]
    );
}

/**
 * Returns the Quest lab provider configuration row.
 */
function getLabconfig(): array|false
{
    return QueryUtils::querySingleRow(
        "SELECT recv_app_id, recv_fac_id FROM procedure_providers WHERE name = 'Quest'",
        []
    );
}

/**
 * Returns the billing_type for a given procedure order.
 * Values: 'C' = Client/Clinic, 'P' = Patient, 'T' = Third Party/Insurance
 *
 * @param int $oid procedure_order_id
 * @return string billing_type value, or empty string if not found
 */
function getProcedureBillingType(int $oid): string
{
    $res = QueryUtils::querySingleRow(
        "SELECT billing_type FROM procedure_order WHERE procedure_order_id = ?",
        [$oid]
    );
    return trim((string) ($res['billing_type'] ?? ''));
}

function saveBarCode($bar, $pid, $order): void
{
    QueryUtils::sqlStatementThrowException(
        "INSERT INTO requisition (id, req_id, pid, lab_id) VALUES (NULL, ?, ?, ?)",
        [$bar, $pid, $order]
    );
}

function getBarId($lab_id, $pid): array|string|false
{
    // If the associated procedure order was deleted, clean up the orphaned requisition row.
    $isOrder = QueryUtils::querySingleRow(
        "SELECT procedure_order_id FROM procedure_order WHERE procedure_order_id = ? AND patient_id = ?",
        [$lab_id, $pid]
    );

    if (empty($isOrder['procedure_order_id'])) {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM requisition WHERE lab_id = ? AND pid = ?",
            [$lab_id, $pid]
        );
        return '';
    }

    return QueryUtils::querySingleRow(
        "SELECT req_id FROM requisition WHERE lab_id = ? AND pid = ?",
        [$lab_id, $pid]
    ) ?: '';
}

/**
 * @param string $facilityID Format: XX_YY where YY is the users.id for the facility
 * @return array|false
 */
function getFacilityInfo($facilityID): array|false
{
    $parts = explode('_', (string) $facilityID);
    if (count($parts) < 2) {
        return false;
    }
    return QueryUtils::querySingleRow(
        "SELECT title, fname, lname, street, city, state, zip, organization, phone
         FROM users WHERE id = ?",
        [$parts[1]]
    );
}

function formatPhone($phone): string
{
    $phone = preg_replace('/[^0-9]/', '', (string) $phone);
    if (strlen($phone) === 7) {
        return preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $phone);
    }
    if (strlen($phone) === 10) {
        return preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $phone);
    }
    return $phone;
}
