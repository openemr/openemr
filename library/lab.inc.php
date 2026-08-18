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
 * @return list<array<string, mixed>>
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

    /** @var list<array<string, mixed>> $rows */
    $rows = [];
    foreach (QueryUtils::fetchRecords($sql, [$oid, $encounter, $oid]) as $row) {
        $typed = [];
        foreach ($row as $key => $value) {
            $typed[(string) $key] = $value;
        }
        $rows[] = $typed;
    }

    return $rows;
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
    $relationship = $res['subscriber_relationship'] ?? null;
    return $relationship === null ? null : (string) $relationship;
}

/**
 * @param int|string $prov_id
 * @return array{0: string, 1: string}
 */
function getNPI($prov_id): array
{
    $res = QueryUtils::querySingleRow(
        "SELECT npi, upin FROM users WHERE id = ?",
        [$prov_id]
    );
    return [(string) ($res['npi'] ?? ''), (string) ($res['upin'] ?? '')];
}

/**
 * @param int|string $prov_id
 * @return array<string, mixed>
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
    return is_array($res) ? $res : [];
}

/**
 * @param int|string $prov_id
 * @return array<string, mixed>|null
 */
function getLabProviders($prov_id): ?array
{
    $res = QueryUtils::querySingleRow(
        "SELECT fname, lname FROM users
         WHERE authorized = 1 AND active = 1 AND username != '' AND id = ?",
        [$prov_id]
    );

    // querySingleRow returns false when no row matches; normalize to null.
    return is_array($res) ? $res : null;
}

/**
 * Returns the lab provider configuration row for the given procedure provider.
 *
 * @param int $ppid procedure_providers.ppid
 * @return array<string, mixed>|false
 */
function getLabconfig(int $ppid): array|false
{
    $res = QueryUtils::querySingleRow(
        "SELECT recv_app_id, recv_fac_id FROM procedure_providers WHERE ppid = ?",
        [$ppid]
    );
    return is_array($res) ? $res : false;
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

/**
 * @param int|string $lab_id
 * @param int|string $pid
 * @return array<string, mixed>|string
 */
function getBarId($lab_id, $pid): array|string
{
    // If the associated procedure order was deleted, clean up the orphaned requisition row.
    $isOrder = QueryUtils::querySingleRow(
        "SELECT procedure_order_id FROM procedure_order WHERE procedure_order_id = ? AND patient_id = ?",
        [$lab_id, $pid]
    );

    if (!is_array($isOrder) || !isset($isOrder['procedure_order_id']) || $isOrder['procedure_order_id'] === '' || $isOrder['procedure_order_id'] === null) {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM requisition WHERE lab_id = ? AND pid = ?",
            [$lab_id, $pid]
        );
        return '';
    }

    $req = QueryUtils::querySingleRow(
        "SELECT req_id FROM requisition WHERE lab_id = ? AND pid = ?",
        [$lab_id, $pid]
    );

    return is_array($req) ? $req : '';
}

/**
 * Builds the responsible party array from billing type and context data.
 * Pure function — no database access — suitable for unit testing.
 *
 * @param string $billingType  'C' = Clinic, 'P' = Patient, 'T' = Third Party/Insurance
 * @param array<string, mixed> $facility Facility data (name, street, city, state, postal_code)
 * @param array<string, mixed> $pdata Patient data (fname, lname, street, city, state, postal_code)
 * @param array<string, mixed> $primaryIns Primary insurance data (subscriber_fname, subscriber_lname, line1, city, state, zip, subscriber_relationship)
 * @return array{name: string, address: string, city_st_zip: string, relationship: string, relationship_is_list: bool}|array{}
 */
function buildResponsibleParty(string $billingType, array $facility, array $pdata, array $primaryIns): array
{
    if ($billingType === 'C') {
        $city = (string) ($facility['city'] ?? '');
        $state = (string) ($facility['state'] ?? '');
        $postal = (string) ($facility['postal_code'] ?? '');
        return [
            'name'            => (string) ($facility['name'] ?? ''),
            'address'         => (string) ($facility['street'] ?? ''),
            'city_st_zip'     => trim($city . ', ' . $state . ' ' . $postal),
            'relationship'    => 'Client Billing',
            'relationship_is_list' => false,
        ];
    }

    if ($billingType === 'P') {
        $fname = (string) ($pdata['fname'] ?? '');
        $lname = (string) ($pdata['lname'] ?? '');
        $city = (string) ($pdata['city'] ?? '');
        $state = (string) ($pdata['state'] ?? '');
        $postal = (string) ($pdata['postal_code'] ?? '');
        return [
            'name'            => trim($fname . ' ' . $lname),
            'address'         => (string) ($pdata['street'] ?? ''),
            'city_st_zip'     => trim($city . ', ' . $state . ' ' . $postal),
            'relationship'    => 'Self',
            'relationship_is_list' => false,
        ];
    }

    if ($billingType === 'T' && $primaryIns !== []) {
        $fname = (string) ($primaryIns['subscriber_fname'] ?? '');
        $lname = (string) ($primaryIns['subscriber_lname'] ?? '');
        $city = (string) ($primaryIns['city'] ?? '');
        $state = (string) ($primaryIns['state'] ?? '');
        $zip = (string) ($primaryIns['zip'] ?? '');
        return [
            'name'            => trim($fname . ' ' . $lname),
            'address'         => (string) ($primaryIns['line1'] ?? ''),
            'city_st_zip'     => trim($city . ', ' . $state . ' ' . $zip),
            'relationship'    => (string) ($primaryIns['subscriber_relationship'] ?? ''),
            'relationship_is_list' => true,
        ];
    }

    return [];
}

/**
 * Returns AOE (Ask On Entry) question/answer pairs for a procedure order code row.
 *
 * @param int|string $oid procedure_order_id
 * @param int|string $labId procedure_order.lab_id / procedure_providers.ppid
 * @param int|string $procedureCode procedure_order_code.procedure_code
 * @param int|string $procedureOrderSeq procedure_order_code.procedure_order_seq
 * @return list<array{question_text: string, answer: string}>
 */
function getProcedureOrderAnswers($oid, $labId, $procedureCode, $procedureOrderSeq): array
{
    $rows = QueryUtils::fetchRecords(
        "SELECT q.question_text, a.answer
         FROM procedure_answers AS a
         LEFT JOIN procedure_questions AS q
           ON q.lab_id = ?
          AND q.procedure_code = ?
          AND q.question_code = a.question_code
         WHERE a.procedure_order_id = ?
           AND a.procedure_order_seq = ?
         ORDER BY q.seq, a.answer_seq",
        [$labId, $procedureCode, $oid, $procedureOrderSeq]
    );

    $answers = [];
    foreach ($rows as $row) {
        $answers[] = [
            'question_text' => (string) ($row['question_text'] ?? ''),
            'answer' => (string) ($row['answer'] ?? ''),
        ];
    }

    return $answers;
}

/**
 * @param string $facilityID Format: XX_YY where YY is the users.id for the facility
 * @return array<string, mixed>|false
 */
function getFacilityInfo($facilityID): array|false
{
    $parts = explode('_', (string) $facilityID);
    if (count($parts) < 2) {
        return false;
    }
    $res = QueryUtils::querySingleRow(
        "SELECT title, fname, lname, street, city, state, zip, organization, phone
         FROM users WHERE id = ?",
        [$parts[1]]
    );
    return is_array($res) ? $res : false;
}
