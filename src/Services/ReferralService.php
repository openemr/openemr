<?php

/**
 * Referrals: the Transactions screen's referral form (LBTref), read for the FHIR
 * ServiceRequest resource's referral category (D8 and C6 of the questionnaire-driven
 * workflows epic, #13266). Each referral is a transactions row, and its fields are
 * lbt_data rows keyed by the transaction's id.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\BasicSearchField;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\ReferenceSearchField;
use OpenEMR\Services\Search\SearchComparator;
use OpenEMR\Services\Search\SearchFieldComparableValue;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldStatementResolver;
use OpenEMR\Services\Search\SearchQueryFragment;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class ReferralService extends BaseService
{
    public const TABLE_NAME = 'transactions';

    /** The referral form's id, in transactions.title. */
    public const FORM_ID = 'LBTref';

    /**
     * The referral's own Tasks, the ones ReferralTaskService makes: code fulfill, not part of another
     * Task. For a query that names the task table task, as the eCQM reports read too
     * (Qdm\Services\ConsultantReportService).
     */
    public const OWN_TASK_SQL = "task.code = 'fulfill' AND task.part_of_uuid IS NULL";

    /** The lbt_data fields read, each as a column of the same name. */
    private const FIELDS = ['refer_date', 'refer_from', 'refer_to', 'body', 'refer_diag', 'refer_related_code', 'reply_date'];

    /**
     * Address book types PractitionerService exposes as Practitioners, alongside users
     * with a login; either way only with an NPI.
     */
    private const PRACTITIONER_ABOOK_TYPES = "'ord_lab', 'spe', 'external_provider'";

    /**
     * Search columns that are the transaction's own, by the column each is read from.
     * Filters on them are applied before the fields are grouped, so that a search by
     * patient or id doesn't group every referral first. The last update isn't one: it
     * moves with the referral's Task too (lastUpdatedAtSource()).
     */
    private const SOURCE_COLUMNS = ['uuid' => 't.uuid', 'puuid' => 'patients.uuid'];

    /**
     * @param bool $createMissingUuids give every referral, patient and user without a uuid one,
     *     as FHIR searches need; false where only a few known rows are read (a save, or one
     *     patient's referrals on a page), which fill in their own rows or don't need uuids
     */
    public function __construct(bool $createMissingUuids = true)
    {
        parent::__construct(self::TABLE_NAME);
        if ($createMissingUuids) {
            UuidRegistry::createMissingUuidsForTables([self::TABLE_NAME, 'patient_data', 'users']);
        }
    }

    /**
     * @return string[]
     */
    public function getUuidFields(): array
    {
        return ['uuid', 'puuid', 'requester_uuid', 'performer_uuid'];
    }

    /**
     * The status of the latest Task owned by the referral's recipient (Refer To), for the
     * transaction aliased $transaction. Only the current recipient's Task decides: when Refer To
     * changes, an earlier recipient's Task stops being the referral's status (#14710). Only the
     * referral's own Tasks count (code fulfill, not part of another), not a recipient's sub-Tasks, and
     * only those that still count as the recipient's (countsForRecipientSql()). The Task a reply
     * completes is the same one (ReferralTaskService::recipientTask()).
     */
    public static function taskStatusSql(string $transaction): string
    {
        return "(SELECT task.status FROM task
            JOIN lbt_data AS recipient ON recipient.form_id = $transaction.id AND recipient.field_id = 'refer_to'
            JOIN users AS recipient_user ON recipient_user.id = recipient.field_value
            WHERE task.focus_type = 'ServiceRequest' AND task.focus_uuid = $transaction.uuid AND task.owner_uuid = recipient_user.uuid
                AND " . self::OWN_TASK_SQL . ' AND ' . self::countsForRecipientSql() . "
            ORDER BY task.id DESC LIMIT 1)";
    }

    /**
     * Whether the Task aliased task still counts as its recipient's, as
     * ReferralTaskService::countsForRecipient() decides: not when ReferralTaskService set it aside
     * (a status in its SET_ASIDE_STATUSES with a reason in its OWN_REASONS: the referral went
     * elsewhere, or the reply that completed it was cleared), since that isn't the recipient's
     * answer. The reason is compared byte for byte, as PHP compares it: under the column's collation
     * a recipient's reason in another case, with an accent or a trailing space would match one.
     * Never NULL, so it is always true or false.
     */
    private static function countsForRecipientSql(): string
    {
        return 'NOT (task.status IN (' . self::literals(ReferralTaskService::SET_ASIDE_STATUSES) . ')'
            . " AND CAST(COALESCE(task.status_reason, '') AS BINARY) IN (" . self::literals(ReferralTaskService::OWN_REASONS) . '))';
    }

    /**
     * ReferralTaskService's constants as SQL string literals, a quote in one doubled.
     *
     * @param list<string> $values
     */
    private static function literals(array $values): string
    {
        return implode(', ', array_map(static fn(string $value): string => "'" . str_replace("'", "''", $value) . "'", $values));
    }

    /**
     * A referral's uuid as text, by its transaction id, or null when it has none or the transaction
     * isn't a referral.
     */
    public function getReferralUuid(int $transactionId): ?string
    {
        $uuid = QueryUtils::fetchSingleValue(
            'SELECT uuid FROM ' . self::TABLE_NAME . ' WHERE id = ? AND title = ?',
            'uuid',
            [$transactionId, self::FORM_ID]
        );
        return is_string($uuid) && $uuid !== '' ? UuidRegistry::uuidToString($uuid) : null;
    }

    /**
     * Each referral with its fields, its patient's uuid, the status of its recipient's latest Task, its
     * status (completed once a reply date is recorded or its Task is completed, revoked
     * when its Task is cancelled) and, for the requester and the person referred to, their
     * uuid and whether FHIR serves them as a Practitioner, or the one referred to as an
     * Organization (an External Organization in the address book). Its last update is the
     * later of the transaction's and its Tasks', since a Task's status is the referral's.
     *
     * authored is the Referral Date as a DATE, so a date search compares dates, not text
     * ('2026-10-10' sorts before '2026-10-10 00:00:00'). A referral without one has none:
     * the transaction's date moves with every edit. (A CASE, not NULLIF: MySQL 8.4 casts
     * NULLIF() of this column to NULL.)
     */
    private static function select(string $sourceFilter = ''): string
    {
        $fields = '';
        foreach (self::FIELDS as $field) {
            $fields .= ", MAX(CASE WHEN d.field_id = '$field' THEN d.field_value END) AS $field";
        }
        $isPractitioner = static fn(string $alias): string => "($alias.id IS NOT NULL AND COALESCE($alias.npi, '') <> ''"
            . " AND (COALESCE($alias.username, '') <> '' OR $alias.abook_type IN (" . self::PRACTITIONER_ABOOK_TYPES . ')))';

        return "SELECT r.*,
                CASE WHEN COALESCE(r.reply_date, '') <> '' OR r.task_status = 'completed' THEN 'completed'
                    WHEN r.task_status = 'cancelled' THEN 'revoked'
                    WHEN r.task_status = 'entered-in-error' THEN 'entered-in-error'
                    ELSE 'active' END AS status,
                'order' AS intent,
                COALESCE(GREATEST(r.transaction_date, r.task_modified), r.transaction_date, r.task_modified) AS last_modified,
                CAST(CASE WHEN r.refer_date <> '' THEN r.refer_date END AS DATE) AS authored,
                requester.uuid AS requester_uuid, " . $isPractitioner('requester') . " AS requester_is_practitioner,
                TRIM(CONCAT_WS(' ', requester.fname, requester.lname)) AS requester_name,
                performer.uuid AS performer_uuid, " . $isPractitioner('performer') . " AS performer_is_practitioner,
                (performer.id IS NOT NULL AND " . AddressBookOrganizationService::isOrganizationSql('performer') . ") AS performer_is_organization,
                CASE WHEN " . AddressBookOrganizationService::isOrganizationSql('performer') . ' THEN ' . AddressBookOrganizationService::nameSql('performer') . "
                    ELSE COALESCE(NULLIF(TRIM(CONCAT_WS(' ', performer.fname, performer.lname)), ''), performer.organization) END AS performer_name
            FROM (
                SELECT t.id, t.uuid, t.pid AS patient_id, patients.uuid AS puuid, t.date AS transaction_date $fields,
                    " . self::taskStatusSql('t') . " AS task_status,
                    (SELECT MAX(task.last_modified) FROM task WHERE task.focus_type = 'ServiceRequest' AND task.focus_uuid = t.uuid
                        AND " . self::OWN_TASK_SQL . ") AS task_modified
                FROM transactions AS t
                JOIN patient_data AS patients ON patients.pid = t.pid
                LEFT JOIN lbt_data AS d ON d.form_id = t.id
                WHERE t.title = '" . self::FORM_ID . "'$sourceFilter
                GROUP BY t.id, t.uuid, t.pid, patients.uuid, t.date
            ) AS r
            LEFT JOIN users AS requester ON requester.id = r.refer_from
            LEFT JOIN users AS performer ON performer.id = r.refer_to";
    }

    /**
     * One referral as search() returns it, by its transaction id, or null when the
     * transaction isn't a referral.
     *
     * @return ?array<string, mixed>
     */
    public function getReferral(int $transactionId): ?array
    {
        $rows = QueryUtils::fetchRecords('SELECT referrals.* FROM (' . self::select(' AND t.id = ?') . ') AS referrals', [$transactionId]);
        $row = $rows[0] ?? null;
        if (!is_array($row)) {
            return null;
        }
        $record = [];
        foreach ($row as $column => $value) {
            if (is_string($column)) {
                $record[$column] = $value;
            }
        }
        return $this->createResultRecordFromDatabaseResult($record);
    }

    /**
     * One referral as search() returns it, by its uuid, or null.
     *
     * @return ?array<string, mixed>
     */
    public function getReferralByUuid(string $uuid): ?array
    {
        if (!UuidRegistry::isValidStringUUID($uuid)) {
            return null;
        }
        $id = QueryUtils::fetchSingleValue('SELECT id FROM transactions WHERE uuid = ?', 'id', [UuidRegistry::uuidToBytes($uuid)]);
        return is_numeric($id) ? $this->getReferral((int)$id) : null;
    }

    /**
     * The address book entries that can be a Task's owner without being a user who logs in: the
     * External Organizations and practitioners ReferralTaskService can send a referral to.
     *
     * @return list<array{uuid: string, name: string, type: string}> by name
     */
    public function addressBookRecipients(): array
    {
        $name = AddressBookOrganizationService::nameSql('u');
        $isOrganization = AddressBookOrganizationService::isOrganizationSql('u');
        $rows = QueryUtils::fetchRecords(
            "SELECT u.uuid, $isOrganization AS is_organization,
                    CASE WHEN $isOrganization THEN $name ELSE COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.fname, u.lname)), ''), u.organization) END AS name
                FROM users AS u
                WHERE u.active = 1 AND u.uuid IS NOT NULL AND COALESCE(u.username, '') = ''
                    AND ($isOrganization OR (COALESCE(u.npi, '') <> '' AND u.abook_type IN (" . self::PRACTITIONER_ABOOK_TYPES . ')))
                ORDER BY name'
        );
        $recipients = [];
        foreach ($rows as $row) {
            if (is_string($row['uuid'] ?? null)) {
                $recipients[] = [
                    'uuid' => UuidRegistry::uuidToString($row['uuid']),
                    'name' => is_string($row['name'] ?? null) ? $row['name'] : '',
                    'type' => in_array($row['is_organization'] ?? null, [1, '1'], true) ? 'Organization' : 'Practitioner',
                ];
            }
        }
        return $recipients;
    }

    /**
     * Each referral's Task status as taskStatusSql() finds it, by transaction id, in one query: for a
     * page that lists referrals with their fields already in hand.
     *
     * @param list<int> $transactionIds
     * @return array<int, ?string>
     */
    public function getTaskStatuses(array $transactionIds): array
    {
        if ($transactionIds === []) {
            return [];
        }
        $rows = QueryUtils::fetchRecords(
            'SELECT t.id, ' . self::taskStatusSql('t') . ' AS task_status FROM transactions AS t WHERE t.title = ? AND t.id IN ('
                . implode(',', array_fill(0, count($transactionIds), '?')) . ')',
            array_merge([self::FORM_ID], $transactionIds)
        );
        $statuses = [];
        foreach ($rows as $row) {
            if (is_numeric($row['id'] ?? null)) {
                $status = $row['task_status'] ?? null;
                $statuses[(int)$row['id']] = is_string($status) ? $status : null;
            }
        }
        return $statuses;
    }

    /**
     * @param array<string, ISearchField> $search
     * @param mixed $isAndCondition
     * @param ?string $recipientOnly an address book entry's uuid, for only the referrals it's the current
     *     recipient of with a Task that counts: once a referral goes elsewhere, its earlier recipient
     *     no longer reads it
     */
    public function search(array $search, $isAndCondition = true, ?string $recipientOnly = null): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        // Fields joined by OR stay together, after grouping.
        $isAnd = $isAndCondition !== false;
        $grouped = [];
        $sourceFilter = '';
        $sourceValues = [];
        if ($recipientOnly !== null) {
            $sourceFilter = " AND EXISTS (SELECT 1 FROM task
                JOIN lbt_data AS recipient ON recipient.form_id = t.id AND recipient.field_id = 'refer_to'
                JOIN users AS recipient_user ON recipient_user.id = recipient.field_value
                WHERE task.focus_type = 'ServiceRequest' AND task.focus_uuid = t.uuid AND task.owner_uuid = recipient_user.uuid
                    AND recipient_user.uuid = ? AND " . self::OWN_TASK_SQL . ' AND ' . self::countsForRecipientSql() . ')';
            $sourceValues[] = UuidRegistry::uuidToBytes($recipientOnly);
        }
        // Only turning the fields into SQL can refuse a search: a field or modifier the statement
        // builders don't take. The query and the mapping below don't throw SearchFieldException.
        try {
            foreach ($search as $key => $field) {
                $lastUpdated = $isAnd ? self::lastUpdatedAtSource($field) : null;
                if ($lastUpdated !== null) {
                    $sourceFilter .= ' AND ' . $lastUpdated->getFragment();
                    $sourceValues = array_merge($sourceValues, $lastUpdated->getBoundValues());
                }
                $sourceField = $isAnd ? self::atSource($field) : null;
                if ($sourceField === null) {
                    $grouped[$key] = $field;
                    continue;
                }
                $statement = SearchFieldStatementResolver::getStatementForSearchField($sourceField);
                $sourceFilter .= ' AND (' . $statement->getFragment() . ')';
                $sourceValues = array_merge($sourceValues, $statement->getBoundValues());
            }
            $where = FhirSearchWhereClauseBuilder::build($grouped, $isAnd);
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
            return $processingResult;
        }
        $records = QueryUtils::fetchRecords(
            'SELECT referrals.* FROM (' . self::select($sourceFilter) . ') AS referrals ' . $where->getFragment() . ' ORDER BY referrals.id',
            array_merge($sourceValues, $where->getBoundValues())
        );
        foreach ($records as $row) {
            $record = [];
            foreach ($row as $column => $value) {
                if (is_string($column)) {
                    $record[$column] = $value;
                }
            }
            $processingResult->addData($this->createResultRecordFromDatabaseResult($record));
        }
        return $processingResult;
    }

    /**
     * A _lastUpdated search's lower bound before the fields are grouped, or null for a search with
     * none, so that a client asking what changed lately doesn't group every referral first. The last
     * update is the later of the transaction's date and its own Tasks' last change, so it's after a
     * date only when one of those is. This narrows what's grouped and decides nothing: the search's
     * own filter still runs on the grouped last_modified. An upper bound can't narrow it this way,
     * since either one being before a date says nothing about the other.
     */
    public static function lastUpdatedAtSource(ISearchField $field): ?SearchQueryFragment
    {
        if (!($field instanceof DateSearchField) || $field->getField() !== 'last_modified') {
            return null;
        }
        $values = $field->getValues();
        // Alternatives (ge2026-01-01,lt2020-01-01) can each match on their own, so no one of them narrows the search.
        if (count($values) > 1 && !$field->isAnd()) {
            return null;
        }
        $lowerBounds = array_values(array_filter(
            $values,
            static fn(SearchFieldComparableValue $value): bool => in_array($value->getComparator(), [SearchComparator::GREATER_THAN, SearchComparator::GREATER_THAN_OR_EQUAL_TO, SearchComparator::STARTS_AFTER], true)
        ));
        if ($lowerBounds === []) {
            return null;
        }
        $transaction = SearchFieldStatementResolver::getStatementForSearchField(new DateSearchField('t.date', $lowerBounds, $field->getDateType(), true));
        $task = SearchFieldStatementResolver::getStatementForSearchField(new DateSearchField('task.last_modified', $lowerBounds, $field->getDateType(), true));
        $fragment = new SearchQueryFragment(
            '(' . $transaction->getFragment() . " OR EXISTS (SELECT 1 FROM task WHERE task.focus_type = 'ServiceRequest' AND task.focus_uuid = t.uuid AND "
                . self::OWN_TASK_SQL . ' AND ' . $task->getFragment() . '))'
        );
        foreach (array_merge($transaction->getBoundValues(), $task->getBoundValues()) as $value) {
            $fragment->addBoundValue($value);
        }
        return $fragment;
    }

    /**
     * The same filter on the column its value is read from, or null for one on a value
     * worked out from the grouped fields (status, authored) or of a kind not handled here.
     */
    private static function atSource(ISearchField $field): ?BasicSearchField
    {
        $column = self::SOURCE_COLUMNS[$field->getField()] ?? null;
        if ($column === null || !($field instanceof BasicSearchField)) {
            return null;
        }
        $sourceField = match (true) {
            $field instanceof TokenSearchField => new TokenSearchField($column, $field->getValues(), $field->isUuid() === true),
            $field instanceof ReferenceSearchField => new ReferenceSearchField($column, $field->getValues(), true),
            $field instanceof DateSearchField => new DateSearchField($column, $field->getValues(), $field->getDateType(), $field->isAnd()),
            default => null,
        };
        $sourceField?->setModifier($field->getModifier());
        return $sourceField;
    }
}
