<?php

/**
 * UsersRowPatientAllowlist
 *
 * Shared allowlist governing which columns and search parameters the
 * `users`-backed FHIR services (Practitioner, PractitionerRole, Person)
 * expose to a patient-scoped caller. The pair mirrors the shape US Core
 * defines for Practitioner (identifier + name; no home contact) and
 * keeps the two services from drifting apart.
 *
 * The allowlist is deliberately narrow: any column not enumerated here
 * is dropped at the row boundary before it reaches parseOpenEMRRecord,
 * and any search parameter not enumerated here is dropped before it
 * reaches the SQL WHERE builder. New columns or search parameters added
 * downstream default to blocked for patient callers until explicitly
 * added here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

final class UsersRowPatientAllowlist
{
    /**
     * Columns on the `users` row a patient caller may see, keyed by the
     * OpenEMR column name each FHIR builder reads. Everything else is
     * dropped before parseOpenEMRRecord runs — the empty guards in the
     * builder methods then omit the corresponding FHIR fields.
     *
     * Included:
     *   uuid, active, last_updated       — resource plumbing (id/meta)
     *   fname, mname, lname, title       — human name components
     *   phonew1                          — work phone (labeled use=work)
     *   npi                              — Practitioner identifier
     *
     * Excluded (each already emitted by the full parse):
     *   street, streetb, zip, city, state — address (home)
     *   phone                             — home phone
     *   phonecell                         — cell phone
     *   email                             — labeled use=home in the parse
     *
     * @var list<string>
     */
    private const ALLOWED_COLUMNS = [
        'uuid',
        'active',
        'last_updated',
        'fname',
        'mname',
        'lname',
        'title',
        'phonew1',
        'npi',
    ];

    /**
     * FHIR search parameter names a patient caller may supply. Bind by
     * `_id` or by name lets legit care-team lookups continue; the address
     * / phone / email / telecom parameters are dropped so a patient caller
     * cannot use the search endpoint as an oracle over the same columns
     * the row filter hides.
     *
     * @var list<string>
     */
    private const ALLOWED_SEARCH_PARAMS = [
        '_id',
        '_lastUpdated',
        'active',
        'family',
        'given',
        'name',
        'identifier',
    ];

    /**
     * Reduce a `users`-shaped row to the patient-allowlisted columns.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function filterRow(array $row): array
    {
        return array_intersect_key($row, array_flip(self::ALLOWED_COLUMNS));
    }

    /**
     * Reduce a FHIR search-parameter map to the patient-allowlisted names.
     *
     * @template TValue
     * @param array<string, TValue> $searchParams
     * @return array<string, TValue>
     */
    public static function filterSearchParams(array $searchParams): array
    {
        return array_intersect_key($searchParams, array_flip(self::ALLOWED_SEARCH_PARAMS));
    }

    /**
     * @return list<string>
     */
    public static function allowedColumns(): array
    {
        return self::ALLOWED_COLUMNS;
    }

    /**
     * @return list<string>
     */
    public static function allowedSearchParams(): array
    {
        return self::ALLOWED_SEARCH_PARAMS;
    }
}
