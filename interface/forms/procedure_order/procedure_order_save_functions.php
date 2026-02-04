<?php

/**
 * Template for rendering specimen rows within a procedure order form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Uuid\UuidRegistry;

/**
 * Save or update procedure order codes with UUID preservation and sequence tracking
 *
 * @param int   $formid   The procedure order ID
 * @param array $postData The POST data array
 * @return array Array mapping form indices to order sequences
 */
function saveProcedureOrderCodes($formid, $postData): array
{
    // Track existing order codes by sequence
    $existingCodes = [];
    $existingQuery = sqlStatement(
        "SELECT procedure_order_seq
         FROM procedure_order_code
         WHERE procedure_order_id = ?
         ORDER BY procedure_order_seq",
        [$formid]
    );

    while ($row = sqlFetchArray($existingQuery)) {
        $existingCodes[] = (int)$row['procedure_order_seq'];
    }

    // Track which sequences we've processed and map form index to seq
    $processedSequences = [];
    $indexToSeqMap = [];

    for ($i = 0; isset($postData['form_proc_type'][$i]); ++$i) {
        $ptid = (int)$postData['form_proc_type'][$i];

        if ($ptid <= 0 && $ptid !== -2) {
            continue;
        }

        // Handle new code from picker
        if ($ptid === -2) {
            $ptid = getOrCreateProcedureType($postData, $i);
            if (!$ptid) {
                continue;
            }
        }

        // Check if user provided an existing order_seq (for updates)
        $existing_seq = !empty($postData['form_proc_order_seq'][$i]) ? (int)$postData['form_proc_order_seq'][$i] : 0;

        // Prepare data for insert/update
        $reason_code = trim($postData['form_proc_reason_code'][$i] ?? '');
        $reason_description = trim($postData['form_proc_reason_description'][$i] ?? '');
        $reason_date_low = trim($postData['form_proc_reason_date_low'][$i] ?? '');
        $reason_date_high = trim($postData['form_proc_reason_date_high'][$i] ?? '');
        $reason_status = trim($postData['form_proc_reason_status'][$i] ?? '');

        // Nullify related fields if reason_code is empty
        if (empty($reason_code)) {
            $reason_description = null;
            $reason_date_low = null;
            $reason_date_high = null;
            $reason_status = null;
        }

        // Nullify empty dates
        if (empty($reason_date_low)) {
            $reason_date_low = null;
        }
        if (empty($reason_date_high)) {
            $reason_date_high = null;
        }

        $orderCodeData = [
            'diagnoses' => trim((string) $postData['form_proc_type_diag'][$i]),
            'procedure_order_title' => trim((string) $postData['form_proc_order_title'][$i]),
            'transport' => trim((string) $postData['form_transport'][$i]),
            'procedure_type' => trim($postData['form_procedure_type'][$i] ?: $postData['procedure_type_names'] ?? ''),
            'reason_code' => $reason_code,
            'reason_description' => $reason_description,
            'reason_date_low' => $reason_date_low,
            'reason_date_high' => $reason_date_high,
            'reason_status' => $reason_status
        ];

        // Determine if UPDATE or INSERT
        if ($existing_seq > 0 && in_array($existing_seq, $existingCodes)) {
            // UPDATE existing record
            updateProcedureOrderCode($formid, $existing_seq, $orderCodeData, $ptid);
            $processedSequences[] = $existing_seq;
            $order_seq = $existing_seq;
        } else {
            // INSERT new record - get next sequence
            $order_seq_result = sqlQuery(
                "SELECT IFNULL(MAX(procedure_order_seq), 0) + 1 AS seq
                 FROM procedure_order_code
                 WHERE procedure_order_id = ?",
                [$formid]
            );
            $order_seq = (int)$order_seq_result['seq'];

            insertProcedureOrderCode($formid, $order_seq, $orderCodeData, $ptid);
            $processedSequences[] = $order_seq;
        }

        // Map form index to database sequence
        $indexToSeqMap[$i] = $order_seq;

        // Save specimens for this order line
        saveProcedureSpecimens($formid, $order_seq, $postData, $i);

        // Save QOE answers
        saveProcedureAnswers($formid, $order_seq, $ptid, $postData, $i);
    }

    // Delete order codes that were not in the POST data (removed by user)
    // NOTE: This is now redundant if using AJAX deletion, but kept as safety net
    deleteRemovedOrderCodes($formid, $processedSequences);

    return $indexToSeqMap; // Return mapping for client-side updates
}

/**
 * Insert new procedure order code with sequence
 */
function insertProcedureOrderCode($formid, $order_seq, $data, $ptid): void
{
    sqlInsert(
        "INSERT INTO procedure_order_code SET
           procedure_order_id = ?,
           procedure_order_seq = ?,
           diagnoses = ?,
           procedure_order_title = ?,
           transport = ?,
           procedure_code = (SELECT procedure_code FROM procedure_type WHERE procedure_type_id = ?),
           procedure_name = (SELECT name FROM procedure_type WHERE procedure_type_id = ?),
           procedure_type = ?,
           reason_code = ?,
           reason_description = ?,
           reason_date_low = ?,
           reason_date_high = ?,
           reason_status = ?",
        [
            $formid,
            $order_seq,
            $data['diagnoses'],
            $data['procedure_order_title'],
            $data['transport'],
            $ptid,
            $ptid,
            $data['procedure_type'],
            $data['reason_code'],
            $data['reason_description'],
            $data['reason_date_low'],
            $data['reason_date_high'],
            $data['reason_status']
        ]
    );
}

/**
 * FIXED: Save procedure specimens with proper tracking
 */
function saveProcedureSpecimens($formid, $order_seq, $postData, $index): void
{
    // Get existing ACTIVE specimens for this order line
    $existingSpecimens = [];
    $existingQuery = sqlStatement(
        "SELECT procedure_specimen_id, uuid
         FROM procedure_specimen
         WHERE procedure_order_id = ?
           AND procedure_order_seq = ?

         ORDER BY procedure_specimen_id",
        [$formid, $order_seq]
    );

    while ($row = sqlFetchArray($existingQuery)) {
        $existingSpecimens[$row['procedure_specimen_id']] = $row['uuid'];
    }


    // Get specimen IDs from POST (tracks which specimens to keep)
    $specimenIds = $postData['form_proc_specimen_id'][$index] ?? [];

    // Collect specimen data from POST
    $ids = $postData['form_proc_specimen_identifier'][$index] ?? [];
    $accs = $postData['form_proc_accession_identifier'][$index] ?? [];
    $typeCodes = $postData['form_proc_specimen_type_code'][$index] ?? [];
    $types = $postData['form_proc_specimen_type'][$index] ?? [];
    $methodCodes = $postData['form_proc_collection_method_code'][$index] ?? [];
    $methods = $postData['form_proc_collection_method'][$index] ?? [];
    $siteCodes = $postData['form_proc_specimen_location_code'][$index] ?? [];
    $sites = $postData['form_proc_specimen_location'][$index] ?? [];
    $lowDates = $postData['form_proc_specimen_date_low'][$index] ?? [];
    $highDates = $postData['form_proc_specimen_date_high'][$index] ?? [];
    $collecteds = $postData['form_proc_specimen_collected'][$index] ?? [];
    $volValues = $postData['form_proc_specimen_volume_value'][$index] ?? [];
    $volUnits = $postData['form_proc_specimen_volume_unit'][$index] ?? [];
    $condCodes = $postData['form_proc_specimen_condition_code'][$index] ?? [];
    $condTxts = $postData['form_proc_specimen_condition'][$index] ?? [];
    $comments = $postData['form_proc_specimen_comments'][$index] ?? [];

    $rows = max(
        count($ids),
        count($accs),
        count($types),
        count($typeCodes),
        count($siteCodes),
        count($sites),
        count($methodCodes),
        count($methods),
        count($lowDates),
        count($highDates),
        count($collecteds),
        count($volValues),
        count($volUnits),
        count($condCodes),
        count($condTxts),
        count($comments),
        count($specimenIds)
    );

    $processedSpecimenIds = [];

    for ($s = 0; $s <= $rows; $s++) {
        // Skip blank lines
        $any = trim(($ids[$s] ?? '')) . trim(($accs[$s] ?? '')) . trim(($types[$s] ?? '')) .
            trim(($sites[$s] ?? '')) . trim(($lowDates[$s] ?? '')) . trim(($highDates[$s] ?? '')) .
            trim(($volValues[$s] ?? '')) . trim(($condCodes[$s] ?? '')) .
            trim(($condTxts[$s] ?? '')) . trim(($comments[$s] ?? ''));

        if ($any === '') {
            continue;
        }

        $specimenData = [
            'specimen_identifier' => trim($ids[$s] ?? '') ?: null,
            'accession_identifier' => trim($accs[$s] ?? '') ?: null,
            'specimen_type_code' => trim($typeCodes[$s] ?? '') ?: null,
            'specimen_type' => trim($types[$s] ?? '') ?: null,
            'collection_method_code' => trim($methodCodes[$s] ?? '') ?: null,
            'collection_method' => trim($methods[$s] ?? '') ?: null,
            'specimen_location_code' => trim($siteCodes[$s] ?? '') ?: null,
            'specimen_location' => trim($sites[$s] ?? '') ?: null,
            'collected_date' => ($collecteds[$s] ?? '') ?: null,
            'collection_date_low' => ($lowDates[$s] ?? '') ?: null,
            'collection_date_high' => ($highDates[$s] ?? '') ?: null,
            'volume_value' => strlen($volValues[$s] ?? '') ? (float)$volValues[$s] : null,
            'volume_unit' => ($volUnits[$s] ?? 'mL') ?: 'mL',
            'condition_code' => trim($condCodes[$s] ?? '') ?: null,
            'specimen_condition' => trim($condTxts[$s] ?? '') ?: null,
            'comments' => trim($comments[$s] ?? '') ?: null
        ];

        $specimenId = !empty($specimenIds[$s]) ? (int)$specimenIds[$s] : 0;

        if ($specimenId > 0 && isset($existingSpecimens[$specimenId])) {
            // UPDATE existing
            updateProcedureSpecimen($specimenId, $specimenData);
            $processedSpecimenIds[] = $specimenId;
        } else {
            // INSERT new
            $newId = insertProcedureSpecimen($formid, $order_seq, $specimenData);
            $processedSpecimenIds[] = $newId;
        }
    }

    // Soft delete specimens that were removed (not in processedSpecimenIds)
    deleteRemovedSpecimens($formid, $order_seq, $processedSpecimenIds);
}

/**
 * Soft delete specimens not in the processed list
 */
function softDeleteRemovedSpecimens($formid, $order_seq, $processedIds): void
{
    if (empty($processedIds)) {
        // Mark all as deleted
        sqlStatement(
            "UPDATE procedure_specimen
             SET deleted = 1, updated_by = ?
             WHERE procedure_order_id = ?
               AND procedure_order_seq = ?
               AND deleted = 0",
            [($_SESSION['authUserID'] ?? null), $formid, $order_seq]
        );
        return;
    }

    $placeholders = implode(',', array_fill(0, count($processedIds), '?'));
    $params = array_merge([($_SESSION['authUserID'] ?? null), $formid, $order_seq], $processedIds);

    sqlStatement(
        "UPDATE procedure_specimen
         SET deleted = 1, updated_by = ?
         WHERE procedure_order_id = ?
           AND procedure_order_seq = ?
           AND deleted = 0
           AND procedure_specimen_id NOT IN ($placeholders)",
        $params
    );
}
// end of specimen functions

/**
 * Get or create procedure type for new codes
 *
 * @param array $postData POST data
 * @param int $index Current index
 * @return int|null Procedure type ID
 */
function getOrCreateProcedureType($postData, $index): ?int
{
    $query_select_pt = 'SELECT * FROM procedure_type WHERE procedure_code = ? AND lab_id = ?';
    $result_types = sqlQuery(
        $query_select_pt,
        [$postData['form_proc_code'][$index], $postData['form_lab_id']]
    );

    $ptid = (int)($result_types['procedure_type_id'] ?? 0);

    if ($ptid === 0) {
        $query_insert = 'INSERT INTO procedure_type(name, lab_id, procedure_code, procedure_type, activity, procedure_type_name)
                         VALUES (?, ?, ?, ?, ?, ?)';
        $ptid = sqlInsert(
            $query_insert,
            [
                $postData['form_proc_type_desc'][$index],
                $postData['form_lab_id'],
                $postData['form_proc_code'][$index],
                'ord',
                1,
                $postData['procedure_type_names']
            ]
        );

        $query_update_pt = 'UPDATE procedure_type SET parent = ? WHERE procedure_type_id = ?';
        sqlQuery($query_update_pt, [$ptid, $ptid]);
    }

    return $ptid;
}

/**
 * Update existing procedure order code
 *
 * @param int $formid Order ID
 * @param int $seq Sequence number
 * @param array $data Order code data
 * @param int $ptid Procedure type ID
 * @return void
 */
function updateProcedureOrderCode($formid, $seq, $data, $ptid): void
{
    sqlStatement(
        "UPDATE procedure_order_code SET
           diagnoses = ?,
           procedure_order_title = ?,
           transport = ?,
           procedure_code = (SELECT procedure_code FROM procedure_type WHERE procedure_type_id = ?),
           procedure_name = (SELECT name FROM procedure_type WHERE procedure_type_id = ?),
           procedure_type = ?,
           reason_code = ?,
           reason_description = ?,
           reason_date_low = ?,
           reason_date_high = ?,
           reason_status = ?
         WHERE procedure_order_id = ?
           AND procedure_order_seq = ?",
        [
            $data['diagnoses'],
            $data['procedure_order_title'],
            $data['transport'],
            $ptid,
            $ptid,
            $data['procedure_type'],
            $data['reason_code'],
            $data['reason_description'],
            $data['reason_date_low'],
            $data['reason_date_high'],
            $data['reason_status'],
            $formid,
            $seq
        ]
    );
}

/**
 * Delete order codes that were removed from the form
 *
 * @param int $formid Order ID
 * @param array $processedSequences Array of sequences that were processed
 * @return void
 */
function deleteRemovedOrderCodes($formid, $processedSequences): void
{
    if (empty($processedSequences)) {
        // If no sequences processed, delete all
        sqlStatement(
            "DELETE FROM procedure_order_code WHERE procedure_order_id = ?",
            [$formid]
        );
        return;
    }

    $placeholders = implode(',', array_fill(0, count($processedSequences), '?'));
    $params = array_merge([$formid], $processedSequences);

    sqlStatement(
        "DELETE FROM procedure_order_code
         WHERE procedure_order_id = ?
           AND procedure_order_seq NOT IN ($placeholders)",
        $params
    );
}

/**
 * Insert new procedure specimen
 *
 * @param int $formid Order ID
 * @param int $order_seq Order sequence
 * @param array $data Specimen data
 * @return int New specimen ID
 */
function insertProcedureSpecimen($formid, $order_seq, $data)
{

    $uuid = (new UuidRegistry(['table_name' => 'procedure_specimen']))->createUuid();

    return sqlInsert(
        "INSERT INTO procedure_specimen SET
         uuid = ?,
         procedure_order_id = ?,
         procedure_order_seq = ?,
         specimen_identifier = ?,
         accession_identifier = ?,
         specimen_type_code = ?,
         specimen_type = ?,
         collection_method_code = ?,
         collection_method = ?,
         specimen_location_code = ?,
         specimen_location = ?,
         collected_date = ?,
         collection_date_low = ?,
         collection_date_high = ?,
         volume_value = ?,
         volume_unit = ?,
         condition_code = ?,
         specimen_condition = ?,
         comments = ?,
         created_by = ?,
         updated_by = ?",
        [
            $uuid,
            $formid,
            $order_seq,
            $data['specimen_identifier'],
            $data['accession_identifier'],
            $data['specimen_type_code'],
            $data['specimen_type'],
            $data['collection_method_code'],
            $data['collection_method'],
            $data['specimen_location_code'],
            $data['specimen_location'],
            $data['collected_date'],
            $data['collection_date_low'],
            $data['collection_date_high'],
            $data['volume_value'],
            $data['volume_unit'],
            $data['condition_code'],
            $data['specimen_condition'],
            $data['comments'],
            ($_SESSION['authUserID'] ?? null),
            ($_SESSION['authUserID'] ?? null)
        ]
    );
}

/**
 * Update existing procedure specimen
 *
 * @param int $specimenId Specimen ID
 * @param array $data Specimen data
 * @return void
 */
function updateProcedureSpecimen($specimenId, $data): void
{
    sqlStatement(
        "UPDATE procedure_specimen SET
         specimen_identifier = ?,
         accession_identifier = ?,
         specimen_type_code = ?,
         specimen_type = ?,
         collection_method_code = ?,
         collection_method = ?,
         specimen_location_code = ?,
         specimen_location = ?,
         collected_date = ?,
         collection_date_low = ?,
         collection_date_high = ?,
         volume_value = ?,
         volume_unit = ?,
         condition_code = ?,
         specimen_condition = ?,
         comments = ?,
         updated_by = ?
         WHERE procedure_specimen_id = ?",
        [
            $data['specimen_identifier'],
            $data['accession_identifier'],
            $data['specimen_type_code'],
            $data['specimen_type'],
            $data['collection_method_code'],
            $data['collection_method'],
            $data['specimen_location_code'],
            $data['specimen_location'],
            $data['collected_date'],
            $data['collection_date_low'],
            $data['collection_date_high'],
            $data['volume_value'],
            $data['volume_unit'],
            $data['condition_code'],
            $data['specimen_condition'],
            $data['comments'],
            ($_SESSION['authUserID'] ?? null),
            $specimenId
        ]
    );
}

/**
 * Delete specimens that were removed from the form
 *
 * @param int $formid Order ID
 * @param int $order_seq Order sequence
 * @param array $processedIds Array of specimen IDs that were processed
 * @return void
 */
function deleteRemovedSpecimens($formid, $order_seq, $processedIds): void
{
    if (empty($processedIds)) {
        // If no specimens processed, delete all for this line
        sqlStatement(
            "DELETE FROM procedure_specimen
             WHERE procedure_order_id = ?
               AND procedure_order_seq = ?",
            [$formid, $order_seq]
        );
        return;
    }

    $placeholders = implode(',', array_fill(0, count($processedIds), '?'));
    $params = array_merge([$formid, $order_seq], $processedIds);

    sqlStatement(
        "DELETE FROM procedure_specimen
         WHERE procedure_order_id = ?
           AND procedure_order_seq = ?
           AND procedure_specimen_id NOT IN ($placeholders)",
        $params
    );
}

/**
 * Save procedure answers (QOE) - keeps existing delete/recreate pattern
 *
 * @param int $formid Order ID
 * @param int $poseq Order sequence
 * @param int $ptid Procedure type ID
 * @param array $postData POST data
 * @param int $index Current index
 * @return void
 */
function saveProcedureAnswers($formid, $poseq, $ptid, $postData, $index): void
{
    $prefix = "ans$index" . "_";

    $qres = sqlStatement(
        "SELECT
            q.procedure_code,
            q.question_code,
            q.options,
            q.fldtype
        FROM
            procedure_type AS t
        JOIN
            procedure_questions AS q
            ON q.lab_id = t.lab_id
            AND q.procedure_code = t.procedure_code
            AND q.activity = 1
        WHERE
            t.procedure_type_id = ?
        ORDER BY
            q.seq, q.question_text",
        [$ptid]
    );

    while ($qrow = sqlFetchArray($qres)) {
        $qcode = trim((string) $qrow['question_code']);
        $pcode = trim((string) $qrow['procedure_code']);
        $fldtype = $qrow['fldtype'];
        $data = '';

        if ($fldtype == 'G') {
            if ($postData["G1_$prefix$qcode"]) {
                $data = $postData["G1_$prefix$qcode"] * 7 + $postData["G2_$prefix$qcode"];
            }
        } else {
            $data = $postData["$prefix$qcode"];
        }

        if (!isset($data) || $data === '') {
            continue;
        }

        if (!is_array($data)) {
            $data = [$data];
        }

        foreach ($data as $datum) {
            sqlBeginTrans();
            $answer_seq = sqlQuery(
                "SELECT IFNULL(MAX(answer_seq), 0) + 1 AS increment
                 FROM procedure_answers
                 WHERE procedure_order_id = ?
                   AND procedure_order_seq = ?
                   AND question_code = ?",
                [$formid, $poseq, $qcode]
            );

            sqlStatement(
                "INSERT INTO procedure_answers SET
                 procedure_order_id = ?,
                 procedure_order_seq = ?,
                 question_code = ?,
                 answer_seq = ?,
                 answer = ?,
                 procedure_code = ?",
                [$formid, $poseq, $qcode, $answer_seq['increment'], trim((string) $datum), $pcode]
            );
            sqlCommitTrans();
        }
    }
}
