<?php

/**
 * The allergy and medication `lists` queries for Ensora eRx uploads.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

use OpenEMR\Common\Database\QueryUtils;

/**
 * Reads the issues waiting to be sent, and marks a sent one uploaded only while
 * the columns the request carried are unchanged. Rows compare by bytes, because
 * the column collation ignores case and trailing spaces.
 */
final class ErxIssueStore
{
    /**
     * @return list<array<mixed>>
     */
    public function getPatientAllergiesByPatientId(int $patientId): array
    {
        return QueryUtils::fetchRecords(
            <<<'SQL'
            SELECT id, lists.title as title1, list_options.title as title2, comments, lists.outcome
            FROM lists
                LEFT JOIN list_options ON lists.outcome = list_options.option_id
                    AND list_options.list_id = 'outcome'
            WHERE `type` = 'allergy'
                AND pid = ?
                AND erx_source = '0'
                AND erx_uploaded = '0'
                AND (
                    enddate is NULL
                    OR enddate = '0000-00-00'
                )
            SQL,
            [$patientId]
        );
    }

    public function markAllergyUploaded(int $patientId, SentAllergy $allergy): void
    {
        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            UPDATE lists
            SET erx_uploaded = '1'
            WHERE type = 'allergy'
                AND pid = ?
                AND id = ?
                AND CAST(title AS BINARY) <=> CAST(? AS BINARY)
                AND outcome = ?
                AND CAST(comments AS BINARY) <=> CAST(? AS BINARY)
            SQL,
            [$patientId, $allergy->id, $allergy->title, $allergy->outcome, $allergy->comments]
        );
    }

    public function markMedicationUploaded(SentMedication $medication): void
    {
        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            UPDATE lists
            SET erx_uploaded = '1'
            WHERE type = 'medication'
                AND id = ?
                AND CAST(title AS BINARY) = CAST(? AS BINARY)
                AND begdate <=> ?
            SQL,
            [$medication->id, $medication->title, $medication->begdate]
        );
    }
}
