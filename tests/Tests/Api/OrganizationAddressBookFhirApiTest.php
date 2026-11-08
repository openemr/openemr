<?php

/**
 * External organizations from the address book, through GET /fhir/Organization (#14710).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class OrganizationAddressBookFhirApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/Organization';

    private ApiTestClient $testClient;

    /** @var array<string, array{uuid: string, id: int}> address book entries by name */
    private array $entries = [];

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->entries['pantry'] = $this->insertEntry([
            'abook_type' => 'external_org',
            'organization' => 'Zq Northside Food Pantry',
            'fname' => 'Dana',
            'lname' => 'Reyes',
            'npi' => '1245319599',
            'phonew1' => '555-0101',
            'phone' => '555-0199',
            'fax' => '555-0102',
            'email' => 'referrals@zq-pantry.example',
            'url' => 'https://zq-pantry.example',
            'street' => '12 Market St',
            'streetb' => 'Suite 4',
            'city' => 'Zqville',
            'state' => 'OH',
            'zip' => '43001',
        ]);
        // Only a contact's name and a home phone: named after the contact.
        $this->entries['contact only'] = $this->insertEntry(['abook_type' => 'external_org', 'fname' => 'Zq', 'lname' => 'Housing Desk', 'phone' => '555-0200', 'city' => 'Zqtown']);
        $this->entries['inactive'] = $this->insertEntry(['abook_type' => 'external_org', 'organization' => 'Zq Closed Shelter', 'active' => '0']);
        // Not Organizations: no name at all, a login (PractitionerService's), and a person.
        $this->entries['nameless'] = $this->insertEntry(['abook_type' => 'external_org', 'city' => 'Zqville']);
        $this->entries['with login'] = $this->insertEntry(['abook_type' => 'external_org', 'organization' => 'Zq Logged In Org', 'username' => 'zq-org-login', 'city' => 'Zqville']);
        $this->entries['person'] = $this->insertEntry(['abook_type' => 'external_provider', 'organization' => 'Zq Person Practice', 'fname' => 'Pat', 'lname' => 'Zq', 'npi' => '1234567893', 'city' => 'Zqville']);
    }

    protected function tearDown(): void
    {
        foreach ($this->entries as $entry) {
            QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$entry['id']]);
        }
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testAnExternalOrganizationReadsAsAnOrganization(): void
    {
        $organization = $this->read($this->entries['pantry']['uuid']);

        $this->assertSame('Organization', $organization['resourceType'] ?? null);
        $this->assertSame('Zq Northside Food Pantry', $organization['name'] ?? null);
        $this->assertTrue($organization['active'] ?? null);
        $this->assertSame([['system' => 'http://hl7.org/fhir/sid/us-npi', 'value' => '1245319599']], $organization['identifier'] ?? null);
        $this->assertSame([
            ['system' => 'phone', 'value' => '555-0101', 'use' => 'work'],
            ['system' => 'fax', 'value' => '555-0102', 'use' => 'work'],
            ['system' => 'email', 'value' => 'referrals@zq-pantry.example', 'use' => 'work'],
            ['system' => 'url', 'value' => 'https://zq-pantry.example', 'use' => 'work'],
        ], $organization['telecom'] ?? null, 'the work phone, not the other one');
        $this->assertSame([['line' => ['12 Market St', 'Suite 4'], 'city' => 'Zqville', 'state' => 'OH', 'postalCode' => '43001']], $organization['address'] ?? null);
        $meta = $organization['meta'] ?? null;
        $this->assertIsArray($meta);
        $this->assertIsString($meta['lastUpdated'] ?? null);
    }

    public function testAnEntryWithOnlyAContactIsNamedAfterThem(): void
    {
        $organization = $this->read($this->entries['contact only']['uuid']);

        $this->assertSame('Zq Housing Desk', $organization['name'] ?? null);
        $this->assertSame([['system' => 'phone', 'value' => '555-0200', 'use' => 'work']], $organization['telecom'] ?? null);
        $this->assertFalse($this->read($this->entries['inactive']['uuid'])['active'] ?? null);
    }

    public function testOnlyNamedExternalOrganizationsWithoutALoginAreOrganizations(): void
    {
        $ids = $this->idsOfEntries($this->search(['address-city' => 'Zqville']));

        $this->assertContains($this->entries['pantry']['uuid'], $ids);
        $this->assertNotContains($this->entries['nameless']['uuid'], $ids, 'an entry with no name');
        $this->assertNotContains($this->entries['with login']['uuid'], $ids, 'an entry with a login');
        $this->assertNotContains($this->entries['person']['uuid'], $ids, 'an external provider');
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->testClient->getOne(self::RESOURCE_URL, $this->entries['person']['uuid'])->getStatusCode());
    }

    public function testSearchParametersApply(): void
    {
        $pantry = $this->entries['pantry']['uuid'];
        $contact = $this->entries['contact only']['uuid'];

        $this->assertSame([$pantry], $this->idsOfEntries($this->search(['name' => 'Zq Northside'])));
        $this->assertSame([$contact], $this->idsOfEntries($this->search(['name' => 'Zq Housing'])));
        $this->assertSame([$pantry], $this->idsOfEntries($this->search(['address-postalcode' => '43001'])));
        $this->assertSame([$pantry], $this->idsOfEntries($this->search(['address' => '12 Market', 'name' => 'Zq'])));
        $this->assertSame([$contact], $this->idsOfEntries($this->search(['address-city' => 'Zqtown'])));
        $this->assertSame([$pantry], $this->idsOfEntries($this->search(['email' => 'referrals@zq-pantry.example'])));
        $this->assertSame([$pantry], $this->idsOfEntries($this->search(['phone' => '555-0101'])));
        $this->assertSame([$contact], $this->idsOfEntries($this->search(['telecom' => '555-0200'])));
        $this->assertSame([$pantry], $this->idsOfEntries($this->search(['_id' => $pantry])));
        $this->assertSame([], $this->idsOfEntries($this->search(['_id' => $pantry, '_lastUpdated' => 'lt2000-01-01'])));
    }

    public function testAnEntrySavedWithoutAUuidGetsOneAndOtherUsersDoNot(): void
    {
        // As the address book saves them: no uuid.
        $this->entries['no uuid'] = ['uuid' => '', 'id' => (int)QueryUtils::sqlInsert(
            "INSERT INTO users (username, active, authorized, abook_type, organization, city) VALUES ('', 1, 0, 'external_org', 'Zq Saved Without Uuid', 'Zqville')"
        )];
        $this->entries['vendor'] = ['uuid' => '', 'id' => (int)QueryUtils::sqlInsert(
            "INSERT INTO users (username, active, authorized, abook_type, organization) VALUES ('', 1, 0, 'vendor', 'Zq Vendor Without Uuid')"
        )];

        $found = $this->search(['name' => 'Zq Saved Without']);
        $this->assertCount(1, $found);
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM users WHERE id = ?', 'uuid', [$this->entries['no uuid']['id']]);
        $this->assertIsString($uuid);
        $this->assertSame(UuidRegistry::uuidToString($uuid), $found[0]['id'] ?? null);
        $this->assertNull(QueryUtils::fetchSingleValue('SELECT uuid FROM users WHERE id = ?', 'uuid', [$this->entries['vendor']['id']]), 'another address book entry is left as it was');
    }

    public function testAnExternalOrganizationIsReadOnly(): void
    {
        $body = [];
        foreach ($this->read($this->entries['pantry']['uuid']) as $element => $value) {
            if (is_string($element) && $element !== 'meta') {
                $body[$element] = $value;
            }
        }
        $body['name'] = 'Zq Renamed';

        $response = $this->testClient->put(self::RESOURCE_URL, $this->entries['pantry']['uuid'], $body);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), (string)$response->getBody());
        $this->assertSame('Zq Northside Food Pantry', $this->read($this->entries['pantry']['uuid'])['name'] ?? null);
    }

    /**
     * @param array<string, string> $query
     * @return list<array<mixed>> the resources in the bundle
     */
    private function search(array $query): array
    {
        $response = $this->testClient->get(self::RESOURCE_URL, $query);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $bundle = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($bundle);
        $resources = [];
        foreach (is_array($bundle['entry'] ?? null) ? $bundle['entry'] : [] as $entry) {
            if (is_array($entry) && is_array($entry['resource'] ?? null)) {
                $resources[] = $entry['resource'];
            }
        }
        return $resources;
    }

    /**
     * @return array<mixed>
     */
    private function read(string $uuid): array
    {
        $response = $this->testClient->getOne(self::RESOURCE_URL, $uuid);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $resource = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($resource);
        return $resource;
    }

    /**
     * The test's own entries among the resources. Other organizations in the database
     * don't count, and the procedure-provider source ignores the address and telecom
     * parameters, so it returns all of its rows for such a search.
     *
     * @param list<array<mixed>> $resources
     * @return list<string>
     */
    private function idsOfEntries(array $resources): array
    {
        $ours = array_column($this->entries, 'uuid');
        return array_values(array_filter(self::ids($resources), static fn(string $id): bool => in_array($id, $ours, true)));
    }

    /**
     * @param list<array<mixed>> $resources
     * @return list<string>
     */
    private static function ids(array $resources): array
    {
        $ids = [];
        foreach ($resources as $resource) {
            if (is_string($resource['id'] ?? null)) {
                $ids[] = $resource['id'];
            }
        }
        return $ids;
    }

    /**
     * @param array<string, string> $columns
     * @return array{uuid: string, id: int}
     */
    private function insertEntry(array $columns): array
    {
        $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();
        $row = $columns + ['username' => '', 'active' => '1', 'authorized' => '0'];
        $row['uuid'] = $uuid;
        $id = QueryUtils::sqlInsert(
            'INSERT INTO users (' . implode(', ', array_keys($row)) . ') VALUES (' . implode(', ', array_fill(0, count($row), '?')) . ')',
            array_values($row)
        );
        return ['uuid' => UuidRegistry::uuidToString($uuid), 'id' => (int)$id];
    }
}
