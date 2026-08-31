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
 * Convert mixed values from DB/legacy helpers into a string for display/logic.
 */
function lab_as_string(mixed $value): string
{
    if (is_string($value)) {
        return $value;
    }
    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }
    if (is_bool($value)) {
        return $value ? '1' : '';
    }

    return '';
}

/**
 * Coerce mixed request/session/DB identifiers to a non-negative int.
 */
function lab_coerce_non_negative_int(mixed $value): int
{
    if (is_int($value)) {
        return $value >= 0 ? $value : 0;
    }
    if (is_string($value) && ctype_digit($value)) {
        return (int) $value;
    }

    return 0;
}

/**
 * Coerce a mixed lab/provider foreign key to int or null when absent/invalid.
 */
function lab_coerce_optional_int(mixed $value): ?int
{
    if (is_int($value)) {
        return $value;
    }
    if (is_string($value) && ctype_digit($value)) {
        return (int) $value;
    }

    return null;
}

/**
 * Normalize a provider id for downstream helper calls.
 */
function lab_coerce_provider_id_string(mixed $value): string
{
    if (is_int($value)) {
        return (string) $value;
    }
    if (is_string($value) && $value !== '') {
        return $value;
    }

    return '';
}

/**
 * Format "City, ST ZIP" lines used on requisition printouts.
 */
function lab_format_city_state_zip(string $city, string $state, string $zip): string
{
    return trim(
        $city .
        ($city !== '' && $state !== '' ? ', ' : '') .
        $state .
        ' ' .
        $zip
    );
}

/**
 * Format a simple first/last person name.
 */
function lab_format_person_name(string $first, string $last): string
{
    return trim($first . ' ' . $last);
}

/**
 * True when the responsible-party shape has any printable content.
 *
 * @param array{name?: string, address?: string, city_st_zip?: string, relationship?: string} $party
 */
function lab_has_responsible_party(array $party): bool
{
    return lab_as_string($party['name'] ?? '') !== ''
        || lab_as_string($party['address'] ?? '') !== ''
        || lab_as_string($party['city_st_zip'] ?? '') !== ''
        || lab_as_string($party['relationship'] ?? '') !== '';
}

/**
 * Resolve barcode text from getBarId() output without generating a new value.
 *
 * @param array<string, mixed>|string $storeBar
 * @return array{found: bool, barcode: string}
 */
function lab_resolve_existing_barcode(array|string $storeBar): array
{
    if (is_array($storeBar)) {
        $reqId = lab_as_string($storeBar['req_id'] ?? '');
        if ($reqId !== '') {
            return ['found' => true, 'barcode' => $reqId];
        }
    }

    return ['found' => false, 'barcode' => ''];
}

/**
 * Stable billing-type label keys for requisition UI (callers translate).
 */
function lab_billing_type_label_key(string $billingType): string
{
    return match ($billingType) {
        'C' => 'Clinic Billing',
        'P' => 'Patient Billing',
        'T' => 'Third Party / Insurance',
        default => 'Not Specified',
    };
}

/**
 * Relationship display strategy for requisition UI.
 *
 * @param array{relationship?: mixed, relationship_is_list?: mixed} $responsibleParty
 * @return array{mode: 'empty'|'list'|'client'|'self'|'raw', value: string}
 */
function lab_relationship_display(array $responsibleParty): array
{
    $relationship = lab_as_string($responsibleParty['relationship'] ?? '');
    if ($relationship === '') {
        return ['mode' => 'empty', 'value' => ''];
    }

    $isList = (bool) ($responsibleParty['relationship_is_list'] ?? false);
    if ($isList) {
        return ['mode' => 'list', 'value' => $relationship];
    }
    if ($relationship === 'Client Billing') {
        return ['mode' => 'client', 'value' => $relationship];
    }
    if ($relationship === 'Self') {
        return ['mode' => 'self', 'value' => $relationship];
    }

    return ['mode' => 'raw', 'value' => $relationship];
}

/**
 * Collect AOE Q&A pairs across ordered procedure code rows.
 *
 * @param list<array<string, mixed>> $orders
 * @param callable(int|string, int|string, int|string, int|string): list<array{question_text: string, answer: string}> $answerFetcher
 * @return list<array{question_text: string, answer: string}>
 */
function lab_collect_aoe_answers(array $orders, int $oid, int $lab, callable $answerFetcher): array
{
    $aoeAnswers = [];
    foreach ($orders as $codeRow) {
        $code = lab_as_string($codeRow['procedure_code'] ?? '');
        $seq = lab_as_string($codeRow['procedure_order_seq'] ?? '');
        if ($code === '' || $seq === '') {
            continue;
        }
        foreach ($answerFetcher($oid, $lab, $code, $seq) as $aoeRow) {
            $aoeAnswers[] = $aoeRow;
        }
    }

    return $aoeAnswers;
}

/**
 * Normalize insurance row lists from legacy getAllinsurances() output.
 *
 * @param mixed $insRaw
 * @return list<array<string, mixed>>
 */
function lab_normalize_insurance_rows(mixed $insRaw): array
{
    $ins = [];
    foreach ((array) $insRaw as $insRow) {
        $ins[] = lab_normalize_array_row((array) $insRow);
    }

    return $ins;
}

/**
 * Normalize associative/list keys on a DB row to strings.
 *
 * @param array<mixed> $row
 * @return array<string, mixed>
 */
function lab_normalize_array_row(array $row): array
{
    $normalized = [];
    foreach ($row as $key => $value) {
        $normalized[lab_as_string($key)] = $value;
    }

    return $normalized;
}

/**
 * Normalize a QueryUtils single-row result (array<mixed>|false).
 *
 * @param array<mixed>|false $row
 * @return array<string, mixed>|false
 */
function lab_normalize_row(array|false $row): array|false
{
    if ($row === false) {
        return false;
    }

    return lab_normalize_array_row($row);
}

/**
 * @param list<array<mixed>> $rows
 * @return list<array<string, mixed>>
 */
function lab_normalize_rows(array $rows): array
{
    $normalized = [];
    foreach ($rows as $row) {
        $normalized[] = lab_normalize_array_row($row);
    }

    return $normalized;
}

/**
 * @param int|string $pid
 * @param int|string $encounter
 * @return mixed
 */
function fetchProcedureId($pid, $encounter): mixed
{
    $res = lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT procedure_order_id FROM procedure_order WHERE patient_id = ? AND encounter_id = ?",
        [$pid, $encounter]
    ));

    return $res === false ? null : ($res['procedure_order_id'] ?? null);
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

    return lab_normalize_rows(QueryUtils::fetchRecords($sql, [$oid, $encounter, $oid]));
}

/**
 * @param int|string $pid
 * @return string|null
 */
function getSelfPay($pid): ?string
{
    $res = lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT subscriber_relationship FROM insurance_data WHERE pid = ?",
        [$pid]
    ));
    if ($res === false || !array_key_exists('subscriber_relationship', $res)) {
        return null;
    }

    $relationship = $res['subscriber_relationship'];
    if ($relationship === null) {
        return null;
    }

    return lab_as_string($relationship);
}

/**
 * @param int|string $prov_id
 * @return array{0: string, 1: string}
 */
function getNPI($prov_id): array
{
    $res = lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT npi, upin FROM users WHERE id = ?",
        [$prov_id]
    ));
    if ($res === false) {
        return ['', ''];
    }

    return [lab_as_string($res['npi'] ?? ''), lab_as_string($res['upin'] ?? '')];
}

/**
 * @param int|string $prov_id
 * @return array<string, mixed>
 */
function getProcedureProvider($prov_id): array
{
    $res = lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT i.organization, i.street, i.city, i.state, i.zip, i.fax, i.phone, pi.lab_director
         FROM users AS i
         JOIN procedure_providers AS pi ON pi.lab_director = i.id
         WHERE pi.ppid = ?",
        [$prov_id]
    ));

    return $res === false ? [] : $res;
}

/**
 * @param int|string $prov_id
 * @return array<string, mixed>|null
 */
function getLabProviders($prov_id): ?array
{
    $res = lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT fname, lname FROM users
         WHERE authorized = 1 AND active = 1 AND username != '' AND id = ?",
        [$prov_id]
    ));

    return $res === false ? null : $res;
}

/**
 * Returns the lab provider configuration row for the given procedure provider.
 *
 * @param int $ppid procedure_providers.ppid
 * @return array<string, mixed>|false
 */
function getLabconfig(int $ppid): array|false
{
    return lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT recv_app_id, recv_fac_id FROM procedure_providers WHERE ppid = ?",
        [$ppid]
    ));
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
    $res = lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT billing_type FROM procedure_order WHERE procedure_order_id = ?",
        [$oid]
    ));

    return $res === false ? '' : trim(lab_as_string($res['billing_type'] ?? ''));
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
    $isOrder = lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT procedure_order_id FROM procedure_order WHERE procedure_order_id = ? AND patient_id = ?",
        [$lab_id, $pid]
    ));

    $orderId = $isOrder === false ? '' : lab_as_string($isOrder['procedure_order_id'] ?? '');
    if ($orderId === '') {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM requisition WHERE lab_id = ? AND pid = ?",
            [$lab_id, $pid]
        );
        return '';
    }

    $req = lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT req_id FROM requisition WHERE lab_id = ? AND pid = ?",
        [$lab_id, $pid]
    ));

    return $req === false ? '' : $req;
}

/**
 * Builds the responsible party array from billing type and context data.
 * Pure function — no database access — suitable for unit testing.
 *
 * Always returns a complete shape so callers can read keys without empty-array unions.
 *
 * @param string $billingType  'C' = Clinic, 'P' = Patient, 'T' = Third Party/Insurance
 * @param array<mixed> $facility Facility data (name, street, city, state, postal_code)
 * @param array<mixed> $pdata Patient data (fname, lname, street, city, state, postal_code)
 * @param array<mixed> $primaryIns Primary insurance data
 * @return array{name: string, address: string, city_st_zip: string, relationship: string, relationship_is_list: bool}
 */
function buildResponsibleParty(string $billingType, array $facility, array $pdata, array $primaryIns): array
{
    $empty = [
        'name' => '',
        'address' => '',
        'city_st_zip' => '',
        'relationship' => '',
        'relationship_is_list' => false,
    ];

    if ($billingType === 'C') {
        $city = lab_as_string($facility['city'] ?? '');
        $state = lab_as_string($facility['state'] ?? '');
        $postal = lab_as_string($facility['postal_code'] ?? '');
        return [
            'name' => lab_as_string($facility['name'] ?? ''),
            'address' => lab_as_string($facility['street'] ?? ''),
            'city_st_zip' => trim($city . ', ' . $state . ' ' . $postal),
            'relationship' => 'Client Billing',
            'relationship_is_list' => false,
        ];
    }

    if ($billingType === 'P') {
        $fname = lab_as_string($pdata['fname'] ?? '');
        $lname = lab_as_string($pdata['lname'] ?? '');
        $city = lab_as_string($pdata['city'] ?? '');
        $state = lab_as_string($pdata['state'] ?? '');
        $postal = lab_as_string($pdata['postal_code'] ?? '');
        return [
            'name' => trim($fname . ' ' . $lname),
            'address' => lab_as_string($pdata['street'] ?? ''),
            'city_st_zip' => trim($city . ', ' . $state . ' ' . $postal),
            'relationship' => 'Self',
            'relationship_is_list' => false,
        ];
    }

    if ($billingType === 'T' && $primaryIns !== []) {
        $fname = lab_as_string($primaryIns['subscriber_fname'] ?? '');
        $lname = lab_as_string($primaryIns['subscriber_lname'] ?? '');
        $city = lab_as_string($primaryIns['city'] ?? '');
        $state = lab_as_string($primaryIns['state'] ?? '');
        $zip = lab_as_string($primaryIns['zip'] ?? '');
        return [
            'name' => trim($fname . ' ' . $lname),
            'address' => lab_as_string($primaryIns['line1'] ?? ''),
            'city_st_zip' => trim($city . ', ' . $state . ' ' . $zip),
            'relationship' => lab_as_string($primaryIns['subscriber_relationship'] ?? ''),
            'relationship_is_list' => true,
        ];
    }

    return $empty;
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
    $rows = lab_normalize_rows(QueryUtils::fetchRecords(
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
    ));

    $answers = [];
    foreach ($rows as $row) {
        $answers[] = [
            'question_text' => lab_as_string($row['question_text'] ?? ''),
            'answer' => lab_as_string($row['answer'] ?? ''),
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
    $parts = explode('_', $facilityID);
    if (count($parts) < 2) {
        return false;
    }

    return lab_normalize_row(QueryUtils::querySingleRow(
        "SELECT title, fname, lname, street, city, state, zip, organization, phone
         FROM users WHERE id = ?",
        [$parts[1]]
    ));
}
