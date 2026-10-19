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
        UuidRegistry::createMissingUuidsForTables([self::TABLE_NAME]);
    }

    /**
     * @return string[]
     */
    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    /**
     * Each organization with a name: the Organization field, or the contact's name when
     * that is all the entry has. The phone is the work phone, which the address book's
     * NPI lookup fills for an organization, else the other phone.
     */
    private static function select(): string
    {
        $name = "COALESCE(NULLIF(TRIM(u.organization), ''), NULLIF(TRIM(CONCAT_WS(' ', u.fname, u.lname)), ''))";
        return "SELECT u.id, u.uuid, $name AS name, u.active, u.npi, u.email, u.url,
                COALESCE(NULLIF(TRIM(u.phonew1), ''), NULLIF(TRIM(u.phone), '')) AS phone, u.fax,
                u.street, u.streetb AS street_line_2, u.city, u.state, u.zip AS postal_code, u.last_updated
            FROM users AS u
            WHERE u.abook_type = '" . self::ABOOK_TYPE . "' AND COALESCE(u.username, '') = '' AND $name IS NOT NULL";
    }

    /**
     * @param array<string, ISearchField> $search
     * @param mixed $isAndCondition
     */
    public function search(array $search, $isAndCondition = true): ProcessingResult
    {
        $processingResult = new ProcessingResult();
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
}
