<?php

/**
 * Reads and writes patient portal documents (`onsite_documents`).
 *
 * Records are arrays keyed by the camelCase names the portal's JSON API and its page
 * scripts use, in column order. Values read from the database are strings or null.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PortalDocuments;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Validators\Portal\FieldRule;
use OpenEMR\Validators\Portal\FieldRuleValidator;

/**
 * @phpstan-type DocumentRecord array<string, mixed>
 */
final class OnsiteDocumentRepository
{
    /** camelCase key => column, in the order the API returns them. */
    public const COLUMNS = [
        'id' => 'id',
        'pid' => 'pid',
        'facility' => 'facility',
        'provider' => 'provider',
        'encounter' => 'encounter',
        'createDate' => 'create_date',
        'docType' => 'doc_type',
        'patientSignedStatus' => 'patient_signed_status',
        'patientSignedTime' => 'patient_signed_time',
        'authorizeSignedTime' => 'authorize_signed_time',
        'acceptSignedStatus' => 'accept_signed_status',
        'authorizingSignator' => 'authorizing_signator',
        'reviewDate' => 'review_date',
        'denialReason' => 'denial_reason',
        'authorizedSignature' => 'authorized_signature',
        'patientSignature' => 'patient_signature',
        'fullDocument' => 'full_document',
        'fileName' => 'file_name',
        'filePath' => 'file_path',
        'templateData' => 'template_data',
    ];

    /** The columns the list's free-text filter searches: every column but the template data. */
    private const FILTERED = [
        'id', 'pid', 'facility', 'provider', 'encounter', 'create_date', 'doc_type',
        'patient_signed_status', 'patient_signed_time', 'authorize_signed_time',
        'accept_signed_status', 'authorizing_signator', 'review_date', 'denial_reason',
        'authorized_signature', 'patient_signature', 'full_document', 'file_name', 'file_path',
    ];

    /**
     * Field errors for a document about to be saved, keyed by camelCase field; empty when valid.
     * The rules are the old Phreeze field map's: sizes and numeric columns. Dates and unbounded
     * text are not checked.
     *
     * @param DocumentRecord $record
     * @return array<string, string>
     */
    public static function validate(array $record): array
    {
        return FieldRuleValidator::validate([
            'id' => FieldRule::number(10, optional: true),
            'pid' => FieldRule::number(10),
            'facility' => FieldRule::number(10),
            'provider' => FieldRule::number(10),
            'encounter' => FieldRule::number(10),
            'docType' => FieldRule::text(255),
            'patientSignedStatus' => FieldRule::number(5),
            'acceptSignedStatus' => FieldRule::number(5),
            'authorizingSignator' => FieldRule::text(50),
            'denialReason' => FieldRule::text(255),
            'fileName' => FieldRule::text(255),
            'filePath' => FieldRule::text(255),
        ], $record);
    }

    /**
     * @return DocumentRecord|null
     */
    public function find(int $id): ?array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT ' . self::selectList() . ' FROM `onsite_documents` WHERE `id` = ?',
            [$id],
        );

        return isset($rows[0]) ? self::toRecord($rows[0]) : null;
    }

    /**
     * One patient's documents, filtered and sorted as the query asks, as one page or all of them.
     */
    public function list(OnsiteDocumentListQuery $query): OnsiteDocumentPage
    {
        $where = ['`pid` = ?'];
        $binds = [$query->pid];
        if ($query->recordId !== null) {
            $where[] = '`id` = ?';
            $binds[] = $query->recordId;
        }
        if (!$query->includeLocked) {
            $where[] = '`denial_reason` NOT LIKE ?';
            $binds[] = '%Locked%';
        }
        if ($query->filter !== '') {
            $like = [];
            foreach (self::FILTERED as $column) {
                $like[] = '`' . $column . '` LIKE ?';
                $binds[] = '%' . $query->filter . '%';
            }
            $where[] = '(' . implode(' OR ', $like) . ')';
        }

        $direction = $query->orderDesc ? ' DESC' : '';
        $sql = 'SELECT ' . self::selectList() . ' FROM `onsite_documents` WHERE ' . implode(' AND ', $where)
            // The id last, so rows that tie on both keys keep one order from page to page.
            . ' ORDER BY `' . $query->orderBy->column() . '`' . $direction . ', `review_date`' . $direction
            . ', `id`' . $direction;

        if ($query->page === null) {
            $rows = array_map(self::toRecord(...), QueryUtils::fetchRecords($sql, $binds));

            return new OnsiteDocumentPage($rows, count($rows), 1, count($rows), 1);
        }

        $total = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `total` FROM `onsite_documents` WHERE ' . implode(' AND ', $where),
            'total',
            $binds,
        );
        $total = is_numeric($total) ? (int) $total : 0;
        $offset = ($query->page - 1) * $query->pageSize;
        $rows = array_map(
            self::toRecord(...),
            QueryUtils::fetchRecords($sql . ' LIMIT ' . $offset . ', ' . $query->pageSize, $binds),
        );

        return new OnsiteDocumentPage(
            $rows,
            $total,
            max(1, (int) ceil($total / $query->pageSize)),
            $query->pageSize,
            $query->page,
        );
    }

    /**
     * @param DocumentRecord $values camelCase key => value; `id` is ignored
     * @return int the new document's id
     */
    public function insert(array $values): int
    {
        [$columns, $binds] = self::assignments($values);
        $id = QueryUtils::sqlInsert('INSERT INTO `onsite_documents` SET ' . $columns, $binds);
        if ($id <= 0) {
            throw new \RuntimeException('The portal document was not saved');
        }

        return $id;
    }

    /**
     * @param DocumentRecord $values camelCase key => value; `id` is ignored
     */
    public function update(int $id, array $values): void
    {
        [$columns, $binds] = self::assignments($values);
        $binds[] = $id;
        QueryUtils::sqlStatementThrowException('UPDATE `onsite_documents` SET ' . $columns . ' WHERE `id` = ?', $binds);
    }

    public function delete(int $id): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `onsite_documents` WHERE `id` = ?', [$id]);
    }

    private static function selectList(): string
    {
        return implode(', ', array_map(static fn(string $column): string => '`' . $column . '`', self::COLUMNS));
    }

    /**
     * @param array<mixed> $row
     * @return DocumentRecord
     */
    private static function toRecord(array $row): array
    {
        $record = [];
        foreach (self::COLUMNS as $key => $column) {
            $value = $row[$column] ?? null;
            // The API has always returned database values as strings; the pages compare them so.
            $record[$key] = is_int($value) || is_float($value) ? (string) $value : $value;
        }

        return $record;
    }

    /**
     * @param DocumentRecord $values
     * @return array{string, list<mixed>}
     */
    private static function assignments(array $values): array
    {
        $columns = [];
        $binds = [];
        foreach (self::COLUMNS as $key => $column) {
            if ($key === 'id' || !array_key_exists($key, $values)) {
                continue;
            }
            $columns[] = '`' . $column . '` = ?';
            $binds[] = $values[$key];
        }
        if ($columns === []) {
            throw new \InvalidArgumentException('No portal document fields to save');
        }

        return [implode(', ', $columns), $binds];
    }
}
