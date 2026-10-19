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
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldStatementResolver;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class ReferralService extends BaseService
{
    public const TABLE_NAME = 'transactions';

    /** The referral form's id, in transactions.title. */
    public const FORM_ID = 'LBTref';

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
     * patient, id or last update doesn't group every referral first.
     */
    private const SOURCE_COLUMNS = ['uuid' => 't.uuid', 'puuid' => 'patients.uuid', 'last_modified' => 't.date'];

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        UuidRegistry::createMissingUuidsForTables([self::TABLE_NAME, 'patient_data', 'users']);
    }

    /**
     * @return string[]
     */
    public function getUuidFields(): array
    {
        return ['uuid', 'puuid', 'requester_uuid', 'performer_uuid'];
    }

    /**
     * Each referral with its fields, its patient's uuid, its status (completed once a
     * reply date is recorded) and, for the requester and the person referred to, their
     * uuid and whether FHIR serves them as a Practitioner, or the one referred to as an
     * Organization (an External Organization in the address book).
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
                CASE WHEN COALESCE(r.reply_date, '') <> '' THEN 'completed' ELSE 'active' END AS status,
                'order' AS intent,
                COALESCE(NULLIF(r.refer_date, ''), DATE(r.last_modified)) AS authored,
                requester.uuid AS requester_uuid, " . $isPractitioner('requester') . " AS requester_is_practitioner,
                TRIM(CONCAT_WS(' ', requester.fname, requester.lname)) AS requester_name,
                performer.uuid AS performer_uuid, " . $isPractitioner('performer') . " AS performer_is_practitioner,
                (performer.id IS NOT NULL AND " . AddressBookOrganizationService::isOrganizationSql('performer') . ") AS performer_is_organization,
                CASE WHEN " . AddressBookOrganizationService::isOrganizationSql('performer') . ' THEN ' . AddressBookOrganizationService::nameSql('performer') . "
                    ELSE COALESCE(NULLIF(TRIM(CONCAT_WS(' ', performer.fname, performer.lname)), ''), performer.organization) END AS performer_name
            FROM (
                SELECT t.id, t.uuid, t.pid AS patient_id, patients.uuid AS puuid, t.date AS last_modified $fields
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
     * @param array<string, ISearchField> $search
     * @param mixed $isAndCondition
     */
    public function search(array $search, $isAndCondition = true): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        try {
            // Fields joined by OR stay together, after grouping.
            $isAnd = $isAndCondition !== false;
            $grouped = [];
            $sourceFilter = '';
            $sourceValues = [];
            foreach ($search as $key => $field) {
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
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $processingResult;
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
