<?php

/**
 * UsersRowPatientAllowlistIsolatedTest
 *
 * Locks the column and search-parameter allowlist governing the patient
 * caller view over `users`-backed FHIR services (Practitioner today;
 * Person is denied at the route so it does not consume the allowlist,
 * PractitionerRole draws from a work-only projection SQL and never touches
 * the personal columns so it does not need the filter either).
 *
 * The tests are structured as an inventory: every column in the current
 * users-row shape appears in either the "must be allowed" or the
 * "must be blocked" list, so a future addition to `users` cannot slip
 * into the patient view without an explicit decision here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\Services\FHIR\UsersRowPatientAllowlist;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UsersRowPatientAllowlistIsolatedTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function requiredColumnProvider(): array
    {
        return [
            'resource id (uuid)'          => ['uuid'],
            'status flag'                 => ['active'],
            'meta.lastUpdated timestamp'  => ['last_updated'],
            'name — first'                => ['fname'],
            'name — middle'               => ['mname'],
            'name — last'                 => ['lname'],
            'name — prefix/title'         => ['title'],
            'work phone (labeled work)'   => ['phonew1'],
            'NPI (Practitioner id)'       => ['npi'],
        ];
    }

    #[DataProvider('requiredColumnProvider')]
    public function testAllowlistIncludesRequiredColumn(string $column): void
    {
        $this->assertContains(
            $column,
            UsersRowPatientAllowlist::allowedColumns(),
            sprintf('Patient allowlist must include %s so a legit care-team lookup keeps working', $column)
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function forbiddenColumnProvider(): array
    {
        return [
            'home street (line 1)'          => ['street'],
            'home street (line 2)'          => ['streetb'],
            'home postal code'              => ['zip'],
            'home city'                     => ['city'],
            'home state'                    => ['state'],
            'home phone'                    => ['phone'],
            'personal cell'                 => ['phonecell'],
            'home email (per FHIR use=home)' => ['email'],
        ];
    }

    #[DataProvider('forbiddenColumnProvider')]
    public function testAllowlistExcludesForbiddenColumn(string $column): void
    {
        $this->assertNotContains(
            $column,
            UsersRowPatientAllowlist::allowedColumns(),
            sprintf(
                'Patient allowlist must NOT include %s — the row filter must drop it before parseOpenEMRRecord builds the FHIR resource',
                $column
            )
        );
    }

    public function testFilterRowDropsForbiddenColumnsAndKeepsAllowed(): void
    {
        $fullRow = [
            'uuid'         => 'aaaa-bbbb',
            'active'       => '1',
            'last_updated' => '2026-01-01 00:00:00',
            'fname'        => 'Alex',
            'mname'        => 'M',
            'lname'        => 'Provider',
            'title'        => 'Dr.',
            'phonew1'      => '+15550000001',
            'npi'          => '1234567890',
            // The below must be dropped:
            'street'       => '123 Home St',
            'streetb'      => 'Apt 4',
            'zip'          => '02139',
            'city'         => 'Somewhere',
            'state'        => 'MA',
            'phone'        => '+15550000002',
            'phonecell'    => '+15550000003',
            'email'        => 'private@example.com',
        ];

        $filtered = UsersRowPatientAllowlist::filterRow($fullRow);

        $this->assertSame(
            [
                'uuid'         => 'aaaa-bbbb',
                'active'       => '1',
                'last_updated' => '2026-01-01 00:00:00',
                'fname'        => 'Alex',
                'mname'        => 'M',
                'lname'        => 'Provider',
                'title'        => 'Dr.',
                'phonew1'      => '+15550000001',
                'npi'          => '1234567890',
            ],
            $filtered,
            'filterRow must project exactly the allowed columns and drop every other key'
        );
    }

    public function testFilterRowSurvivesEmptyRow(): void
    {
        $this->assertSame([], UsersRowPatientAllowlist::filterRow([]));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function allowedSearchParamProvider(): array
    {
        return [
            '_id'          => ['_id'],
            '_lastUpdated' => ['_lastUpdated'],
            'active'       => ['active'],
            'family'       => ['family'],
            'given'        => ['given'],
            'name'         => ['name'],
            'identifier'   => ['identifier'],
        ];
    }

    #[DataProvider('allowedSearchParamProvider')]
    public function testAllowlistIncludesRequiredSearchParam(string $param): void
    {
        $this->assertContains(
            $param,
            UsersRowPatientAllowlist::allowedSearchParams(),
            sprintf('%s must remain a valid patient-caller search parameter', $param)
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function forbiddenSearchParamProvider(): array
    {
        // These search params probe the same columns the row filter hides.
        // Leaving them in the allowlist would turn the search endpoint into
        // an oracle over private contact info.
        return [
            'address'            => ['address'],
            'address-city'       => ['address-city'],
            'address-postalcode' => ['address-postalcode'],
            'address-state'      => ['address-state'],
            'email'              => ['email'],
            'phone'              => ['phone'],
            'telecom'            => ['telecom'],
        ];
    }

    #[DataProvider('forbiddenSearchParamProvider')]
    public function testAllowlistExcludesForbiddenSearchParam(string $param): void
    {
        $this->assertNotContains(
            $param,
            UsersRowPatientAllowlist::allowedSearchParams(),
            sprintf(
                '%s must NOT appear in the patient-caller search allowlist — it probes the columns the row filter hides',
                $param
            )
        );
    }

    public function testFilterSearchParamsDropsForbiddenKeys(): void
    {
        $input = [
            '_id'     => 'aaaa',
            'name'    => 'Smith',
            'family'  => 'Smith',
            'email'   => 'private@example.com',
            'phone'   => '+15550000001',
            'address' => '123 Home St',
            'telecom' => 'phone|+15550000001',
        ];

        $filtered = UsersRowPatientAllowlist::filterSearchParams($input);

        $this->assertSame(
            [
                '_id'    => 'aaaa',
                'name'   => 'Smith',
                'family' => 'Smith',
            ],
            $filtered,
            'filterSearchParams must project exactly the allowed keys'
        );
    }
}
