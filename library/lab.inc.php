<?php

/**
 * lab.inc
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
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
 * Returns an array of associative rows for the given procedure order.
 * Each row contains the full set of columns from procedure_order_code
 * joined with procedure_order. Order-level fields (provider_id, lab_id,
 * clinical_hx, date_collected, date_ordered, etc.) are identical across
 * rows and can be read from the first element.
 *
 * @param int|string $oid
 * @param int|string $encounter
 * @return array<int, array<string, mixed>>
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

    return QueryUtils::fetchRecords($sql, [$oid, $encounter, $oid]);
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
 * Returns the lab provider configuration row for the given procedure provider.
 *
 * @param int $ppid procedure_providers.ppid
 * @return array|false
 */
function getLabconfig(int $ppid): array|false
{
    return QueryUtils::querySingleRow(
        "SELECT recv_app_id, recv_fac_id FROM procedure_providers WHERE ppid = ?",
        [$ppid]
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
 * Builds the responsible party array from billing type and context data.
 * Pure function — no database access — suitable for unit testing.
 *
 * @param string $billingType  'C' = Clinic, 'P' = Patient, 'T' = Third Party/Insurance
 * @param array  $facility     Facility data (name, street, city, state, postal_code)
 * @param array  $pdata        Patient data (fname, lname, street, city, state, postal_code)
 * @param array  $primaryIns   Primary insurance data (subscriber_fname, subscriber_lname, line1, city, state, zip, subscriber_relationship)
 * @return array{name: string, address: string, city_st_zip: string, relationship: string, relationship_is_list: bool}|array{}
 */
function buildResponsibleParty(string $billingType, array $facility, array $pdata, array $primaryIns): array
{
    if ($billingType === 'C') {
        return [
            'name'            => $facility['name'] ?? '',
            'address'         => $facility['street'] ?? '',
            'city_st_zip'     => trim(($facility['city'] ?? '') . ', ' . ($facility['state'] ?? '') . ' ' . ($facility['postal_code'] ?? '')),
            'relationship'    => 'Client Billing',
            'relationship_is_list' => false,
        ];
    }

    if ($billingType === 'P') {
        return [
            'name'            => trim(($pdata['fname'] ?? '') . ' ' . ($pdata['lname'] ?? '')),
            'address'         => $pdata['street'] ?? '',
            'city_st_zip'     => trim(($pdata['city'] ?? '') . ', ' . ($pdata['state'] ?? '') . ' ' . ($pdata['postal_code'] ?? '')),
            'relationship'    => 'Self',
            'relationship_is_list' => false,
        ];
    }

    if ($billingType === 'T' && !empty($primaryIns)) {
        return [
            'name'            => trim(($primaryIns['subscriber_fname'] ?? '') . ' ' . ($primaryIns['subscriber_lname'] ?? '')),
            'address'         => $primaryIns['line1'] ?? '',
            'city_st_zip'     => trim(($primaryIns['city'] ?? '') . ', ' . ($primaryIns['state'] ?? '') . ' ' . ($primaryIns['zip'] ?? '')),
            'relationship'    => $primaryIns['subscriber_relationship'] ?? '',
            'relationship_is_list' => true,
        ];
    }

    return [];
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

