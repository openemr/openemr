<?php

/**
 * Address book entries of the External Organization type (abook_type external_org), read
 * for the FHIR Organization resource, so that a referral's recipient or a Task's owner can
 * be an organization outside the practice (#14710). Practitioners come from the same table
 * through PractitionerService, which serves any user with a login and an NPI, so an entry
 * with a login is left to it.
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
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Validators\ProcessingResult;

class AddressBookOrganizationService extends BaseService
{
    public const TABLE_NAME = 'users';

    /** The address book type, in list abook_type. */
    public const ABOOK_TYPE = 'external_org';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * @return string[]
     */
    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    /**
     * An organization's name, for a users row under the given alias: the Organization field,
     * or the contact's name when that is all the entry has.
     */
    public static function nameSql(string $alias): string
    {
        return "COALESCE(NULLIF(TRIM($alias.organization), ''), NULLIF(TRIM(CONCAT_WS(' ', $alias.fname, $alias.lname)), ''))";
    }

    /**
     * Whether a users row under the given alias is one of these organizations, for a query
     * that points at address-book entries (such as a referral's recipient).
     */
    public static function isOrganizationSql(string $alias): string
    {
        return "($alias.abook_type = '" . self::ABOOK_TYPE . "' AND COALESCE($alias.username, '') = '' AND " . self::nameSql($alias) . ' IS NOT NULL)';
    }

    /**
     * Each organization. The phone is the work phone, which the address book's NPI lookup
     * fills for an organization, else the other phone.
     */
    private static function select(): string
    {
        return 'SELECT u.id, u.uuid, ' . self::nameSql('u') . " AS name, u.active, u.npi, u.email, u.url,
                COALESCE(NULLIF(TRIM(u.phonew1), ''), NULLIF(TRIM(u.phone), '')) AS phone, u.fax,
                u.street, u.streetb AS street_line_2, u.city, u.state, u.zip AS postal_code, u.last_updated
            FROM users AS u
            WHERE " . self::isOrganizationSql('u');
    }

    /**
     * @param array<string, ISearchField> $search
     * @param mixed $isAndCondition
     */
    public function search(array $search, $isAndCondition = true): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        self::createMissingUuids();
        try {
            $where = FhirSearchWhereClauseBuilder::build($search, $isAndCondition !== false);
            $records = QueryUtils::fetchRecords(
                'SELECT organizations.* FROM (' . self::select() . ') AS organizations ' . $where->getFragment() . ' ORDER BY organizations.id',
                $where->getBoundValues()
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
     * Gives these entries a uuid when they have none, since the address book saves them
     * without one. Row by row, so that a search doesn't write to every other user missing one.
     */
    private static function createMissingUuids(): void
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT id FROM users WHERE abook_type = ? AND (uuid IS NULL OR uuid = '' OR uuid = ?)",
            [self::ABOOK_TYPE, str_repeat("\0", 16)]
        );
        foreach ($rows as $row) {
            if (is_numeric($row['id'] ?? null)) {
                UuidRegistry::createMissingUuidForRow(self::TABLE_NAME, 'id', (int)$row['id']);
            }
        }
    }
}
