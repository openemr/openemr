<?php

/**
 * Regression gates for FHIR search modifiers.
 *
 * Inferno §4 exercises `_revInclude:Provenance:target` on every US Core
 * resource, which keeps that modifier green across releases. The sister
 * modifiers `_sort`, `_count`, and `_include` were NOT covered at any
 * tier before this file — a regression in them could silently break
 * clients that rely on them.
 *
 * This file closes the gap for the modifiers that are actually wired
 * through the search layer today:
 *
 *   - `_sort=<date-param>` ASC on Patient — oldest fixture first
 *   - `_sort=-<date-param>` DESC on Patient — youngest fixture first
 *   - `_sort=<string-param>` ASC on Patient — alpha order (proves
 *     `_sort` is not date-only)
 *
 * Modifiers advertised in the CapabilityStatement but NOT exercised
 * here, with the behavior observed on master as of this file's commit:
 *
 *   - `_count` returns the correct `total` + entry count, but the
 *     server does NOT emit a `link[relation="next"]` entry on the
 *     Bundle. QueryPagination::getFhirLinks() exists but
 *     FhirResourcesService::createBundle() never calls it. Forward
 *     pagination via the Bundle `next` link is effectively unsupported
 *     and has no regression gate here.
 *   - `_include=<Resource>:<ref>` is advertised via `searchInclude:
 *     ['*']` in metadata but is not implemented. The parameter name is
 *     not registered in any search-factory, so
 *     ResourceServiceSearchTrait::createSearchParameterForField throws
 *     a SearchFieldException. The outer getAll() swallows it and
 *     returns an empty bundle; the HTTP response is 404 Not Found with
 *     a zero-entry Bundle body. No regression gate — the behavior is a
 *     known gap, not a stable contract to lock in.
 *   - `_sort` is only wired at the SQL layer for services that override
 *     FhirServiceBase::searchForOpenEMRRecordsWithConfig (currently
 *     Patient and Task only). On resources that haven't opted in
 *     (Encounter, Observation, etc.) the `_sort` param is accepted
 *     without error but the DB query returns records in insertion
 *     order. Only Patient is covered below for that reason.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use OpenEMR\Tests\Support\OAuth2\PasswordGrant;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FhirSearchModifiersApiTest extends TestCase
{
    private const SCOPE = 'openid api:fhir user/Patient.read offline_access';
    private const PATIENT_FAMILY_PREFIX = 'ZzSortGate';
    // High-year DOBs keep the fixtures out of any plausible demo-data
    // range so sort assertions against a mixed dataset are deterministic.
    private const BIRTHDATE_EARLIEST = '2090-01-15';
    private const BIRTHDATE_MIDDLE = '2095-06-15';
    private const BIRTHDATE_LATEST = '2099-12-31';

    private string $baseUrl;
    private ?string $clientId = null;
    private ?string $accessToken = null;

    /** @var list<int> */
    private array $createdPatientPids = [];
    /** @var list<string> */
    private array $createdPatientUuids = [];

    /** @var array{pid: int, uuid: string, family: string, dob: string}|null */
    private ?array $patientEarliestDob = null;
    /** @var array{pid: int, uuid: string, family: string, dob: string}|null */
    private ?array $patientMiddleDob = null;
    /** @var array{pid: int, uuid: string, family: string, dob: string}|null */
    private ?array $patientLatestDob = null;

    /** @var list<string> */
    private array $tearDownFailures = [];

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        // Built-in-webserver CI job sets this env var to opt OAuth-dependent
        // tests out of the hard-fail-in-CI branch below. php -S cannot do
        // the HTTPS/OAuth round-trip these tests depend on; the apache +
        // mariadb lanes exercise them instead.
        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'OAuth flow requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }
        if (stripos($probe->getHeaderLine('Server'), 'nginx') !== false) {
            $this->markTestSkipped(
                'FHIR routes under /apis/* not reachable via the nginx test config — '
                . 'apache lane covers these invariants.'
            );
        }

        try {
            // Install three patient fixtures with distinct DOBs and
            // distinct family names in a known order:
            //   DOB-earliest ↔ family 'C' (latest alphabetically)
            //   DOB-middle   ↔ family 'B'
            //   DOB-latest   ↔ family 'A' (earliest alphabetically)
            // The reverse pairing between the two sortable fields is
            // intentional — it means the two sort axes cannot be
            // confused with each other in the assertions.
            $this->patientEarliestDob = $this->installPatientFixture(
                self::PATIENT_FAMILY_PREFIX . 'C',
                self::BIRTHDATE_EARLIEST,
            );
            $this->patientMiddleDob = $this->installPatientFixture(
                self::PATIENT_FAMILY_PREFIX . 'B',
                self::BIRTHDATE_MIDDLE,
            );
            $this->patientLatestDob = $this->installPatientFixture(
                self::PATIENT_FAMILY_PREFIX . 'A',
                self::BIRTHDATE_LATEST,
            );

            [$cid, $csec] = DcrClient::registerConfidential(
                $this->baseUrl,
                'FhirSearchModifiersApiTest-' . bin2hex(random_bytes(3)),
                self::SCOPE,
                'password',
            );
            $this->clientId = $cid;
            $mint = PasswordGrant::mintUserRoleToken(
                $this->baseUrl,
                $cid,
                $csec,
                self::SCOPE,
            );
            $this->accessToken = $mint['access_token'];
        } catch (\Throwable $setUpFailure) {
            try {
                $this->tearDown();
            } catch (\Throwable $rollbackFailure) {
                throw new \RuntimeException(
                    'setUp failed; rollback via tearDown also failed: ' . $rollbackFailure->getMessage(),
                    0,
                    $setUpFailure,
                );
            }
            throw $setUpFailure;
        }
    }

    protected function tearDown(): void
    {
        $this->safeRun(function (): void {
            foreach ($this->createdPatientPids as $pid) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM patient_data WHERE pid = ?',
                    [$pid],
                );
            }
        });
        $this->safeRun(function (): void {
            foreach ($this->createdPatientUuids as $uuid) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM uuid_registry WHERE table_name = 'patient_data' AND uuid = ?",
                    [UuidRegistry::uuidToBytes($uuid)],
                );
            }
        });
        $this->safeRun(function (): void {
            if ($this->clientId !== null) {
                DcrClient::remove($this->clientId);
            }
        });

        if ($this->tearDownFailures !== []) {
            $failures = $this->tearDownFailures;
            $this->tearDownFailures = [];
            self::fail(
                'tearDown cleanup had ' . count($failures) . ' failure(s); '
                . "shared state may be stale for later tests:\n  - "
                . implode("\n  - ", $failures),
            );
        }
    }

    #[Test]
    public function testSortByBirthdateAscendingReturnsOldestFirst(): void
    {
        $bundle = $this->getBundle('/apis/default/fhir/Patient?_sort=birthdate');
        $dobs = $this->extractFixturePatientBirthdates($bundle);
        self::assertSame(
            [self::BIRTHDATE_EARLIEST, self::BIRTHDATE_MIDDLE, self::BIRTHDATE_LATEST],
            $dobs,
            '_sort=birthdate must return fixture patients in ascending birthDate order',
        );
    }

    #[Test]
    public function testSortByBirthdateDescendingReturnsYoungestFirst(): void
    {
        $bundle = $this->getBundle('/apis/default/fhir/Patient?_sort=-birthdate');
        $dobs = $this->extractFixturePatientBirthdates($bundle);
        self::assertSame(
            [self::BIRTHDATE_LATEST, self::BIRTHDATE_MIDDLE, self::BIRTHDATE_EARLIEST],
            $dobs,
            '_sort=-birthdate must return fixture patients in descending birthDate order',
        );
    }

    #[Test]
    public function testSortByFamilyNameAscendingOrdersAlphabetically(): void
    {
        $bundle = $this->getBundle('/apis/default/fhir/Patient?_sort=family');
        $families = $this->extractFixturePatientFamilyNames($bundle);
        self::assertSame(
            [
                self::PATIENT_FAMILY_PREFIX . 'A',
                self::PATIENT_FAMILY_PREFIX . 'B',
                self::PATIENT_FAMILY_PREFIX . 'C',
            ],
            $families,
            '_sort=family must return fixture patients in ascending family-name order '
            . '(proves _sort is not date-only — string search params sort too)',
        );
    }

    /**
     * @param array<mixed> $bundle
     *
     * @return list<string>
     */
    private function extractFixturePatientBirthdates(array $bundle): array
    {
        $entries = $bundle['entry'] ?? [];
        self::assertIsArray($entries);
        $knownUuids = [
            $this->patientEarliestDob['uuid'] ?? '',
            $this->patientMiddleDob['uuid'] ?? '',
            $this->patientLatestDob['uuid'] ?? '',
        ];
        $out = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $resource = $entry['resource'] ?? null;
            if (!is_array($resource)) {
                continue;
            }
            $id = $resource['id'] ?? null;
            if (!is_string($id) || !in_array($id, $knownUuids, true)) {
                // Ignore any non-fixture patients already in the DB —
                // the test asserts the RELATIVE order of our known
                // fixtures only, not the whole dataset.
                continue;
            }
            $dob = $resource['birthDate'] ?? null;
            self::assertIsString($dob);
            $out[] = $dob;
        }
        return $out;
    }

    /**
     * @param array<mixed> $bundle
     *
     * @return list<string>
     */
    private function extractFixturePatientFamilyNames(array $bundle): array
    {
        $entries = $bundle['entry'] ?? [];
        self::assertIsArray($entries);
        $knownUuids = [
            $this->patientEarliestDob['uuid'] ?? '',
            $this->patientMiddleDob['uuid'] ?? '',
            $this->patientLatestDob['uuid'] ?? '',
        ];
        $out = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $resource = $entry['resource'] ?? null;
            if (!is_array($resource)) {
                continue;
            }
            $id = $resource['id'] ?? null;
            if (!is_string($id) || !in_array($id, $knownUuids, true)) {
                continue;
            }
            $names = $resource['name'] ?? null;
            self::assertIsArray($names);
            $firstName = $names[0] ?? null;
            self::assertIsArray($firstName);
            $family = $firstName['family'] ?? null;
            self::assertIsString($family);
            $out[] = $family;
        }
        return $out;
    }

    /**
     * @return array<mixed>
     */
    private function getBundle(string $path): array
    {
        $resp = $this->buildClient()->get($this->baseUrl . $path, [
            'headers' => ['Authorization' => 'Bearer ' . (string) $this->accessToken],
        ]);
        self::assertSame(
            200,
            $resp->getStatusCode(),
            "GET $path must return 200. Body: " . (string) $resp->getBody(),
        );
        $decoded = json_decode((string) $resp->getBody(), true);
        self::assertIsArray($decoded);
        self::assertSame('Bundle', $decoded['resourceType'] ?? null);
        return $decoded;
    }

    /**
     * @return array{pid: int, uuid: string, family: string, dob: string}
     */
    private function installPatientFixture(string $family, string $dob): array
    {
        $registry = new UuidRegistry(['table_name' => 'patient_data']);
        $uuidBytes = $registry->createUuid();
        $uuidString = UuidRegistry::uuidToString($uuidBytes);
        $this->createdPatientUuids[] = $uuidString;

        // Reserve a high PID to stay out of the demo-data range, same
        // approach ConditionFixtureManager uses.
        $maxRow = QueryUtils::querySingleRow(
            'SELECT COALESCE(MAX(pid), 0) AS max_pid FROM patient_data WHERE pid >= 90000',
        );
        self::assertIsArray($maxRow);
        $maxPidRaw = $maxRow['max_pid'] ?? 0;
        self::assertIsNumeric($maxPidRaw);
        $pid = max(90000, (int) $maxPidRaw + 1);
        // Also account for anything already queued this test run (two
        // inserts in the same second could otherwise collide on
        // MAX(pid)).
        while (in_array($pid, $this->createdPatientPids, true)) {
            $pid++;
        }

        QueryUtils::sqlStatementThrowException(
            'INSERT INTO patient_data (pid, uuid, fname, lname, DOB, sex, pubpid) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$pid, $uuidBytes, 'Testfirst', $family, $dob, 'Male', 'TEST-' . $pid],
        );
        $this->createdPatientPids[] = $pid;

        return [
            'pid' => $pid,
            'uuid' => $uuidString,
            'family' => $family,
            'dob' => $dob,
        ];
    }

    private function safeRun(callable $cleanup): void
    {
        try {
            $cleanup();
        } catch (SqlQueryException $e) {
            $this->tearDownFailures[] = $e->getMessage();
            fwrite(STDERR, '[' . static::class . "::tearDown] cleanup step failed: " . $e->getMessage() . "\n");
        }
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }
}
