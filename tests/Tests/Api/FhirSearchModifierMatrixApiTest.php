<?php

/**
 * Regression gates for FHIR R4 search-modifier suffixes.
 *
 * FHIR R4 §3.1.1.5.5 defines nine search-modifier suffixes that can be
 * appended to a search parameter name with a colon:
 *
 *   :exact :contains :missing :text :not :above :below :in :not-in
 *
 * This file exercises each one against a representative resource and
 * search parameter, and asserts the *current* server behavior so a
 * future change surfaces as a diff. It is a regression-gate file — not
 * a correctness file. Where the current behavior deviates from the
 * FHIR R4 spec (modifier silently ignored, modifier mishandled, etc.),
 * the test comment documents the deviation and the assertion locks the
 * behavior as-observed on master.
 *
 * The sibling `FhirSearchModifiersApiTest.php` locks the related
 * global parameters `_sort`, `_count`, `_include`, and
 * `_revInclude:Provenance:target` — this file complements it by
 * covering the per-parameter suffix modifiers. The three "known drift"
 * cases carried over from the sibling (`_count` next-link omitted,
 * `_include` not implemented, `_sort` silent no-op on resources that
 * don't override `searchForOpenEMRRecordsWithConfig`) are marked here
 * via `markTestSkipped()` so the matrix stays visually complete; when
 * the underlying prod fix lands the skip call is flipped to a real
 * assertion.
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

class FhirSearchModifierMatrixApiTest extends TestCase
{
    private const SCOPE = 'openid api:fhir user/Patient.read user/Encounter.read user/Condition.read offline_access';
    private const FAMILY_PREFIX = 'ZzModGate';
    // Three deterministic fixture patients with distinct family-name
    // suffixes so a prefix / contains / exact / above / below assertion
    // can anchor on a known subset without needing to count the whole
    // dataset. High-DOB keeps them out of any plausible demo range.
    private const FAMILY_ALPHA = self::FAMILY_PREFIX . 'Alpha';
    private const FAMILY_BRAVO = self::FAMILY_PREFIX . 'Bravo';
    private const FAMILY_CHARLIE = self::FAMILY_PREFIX . 'Charlie';
    private const DOB_1 = '2090-01-15';
    private const DOB_2 = '2095-06-15';
    private const DOB_3 = '2099-12-31';

    private string $baseUrl;
    private ?string $clientId = null;
    private ?string $accessToken = null;

    /** @var list<int> */
    private array $createdPatientPids = [];
    /** @var list<string> */
    private array $createdPatientUuids = [];

    /** @var array{pid: int, uuid: string, family: string, dob: string}|null */
    private ?array $patientAlpha = null;
    /** @var array{pid: int, uuid: string, family: string, dob: string}|null */
    private ?array $patientBravo = null;
    /** @var array{pid: int, uuid: string, family: string, dob: string}|null */
    private ?array $patientCharlie = null;

    /** @var list<string> */
    private array $tearDownFailures = [];

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

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
            $this->patientAlpha = $this->installPatientFixture(self::FAMILY_ALPHA, self::DOB_1);
            $this->patientBravo = $this->installPatientFixture(self::FAMILY_BRAVO, self::DOB_2);
            $this->patientCharlie = $this->installPatientFixture(self::FAMILY_CHARLIE, self::DOB_3);

            [$cid, $csec] = DcrClient::registerConfidential(
                $this->baseUrl,
                'FhirSearchModifierMatrixApiTest-' . bin2hex(random_bytes(3)),
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

    /**
     * `:exact` on a string param — FHIR R4 §3.1.1.5.5.1 says the match
     * must be case- and whitespace-exact on the whole value. Current
     * server behavior: implemented. StringSearchField accepts the
     * modifier and SearchFieldStatementResolver::resolveStringSearchField
     * emits `BINARY <field> = ?`.
     */
    #[Test]
    public function testStringExactMatchesWholeValue(): void
    {
        $bundle = $this->getBundle(
            '/apis/default/fhir/Patient?family:exact=' . rawurlencode(self::FAMILY_BRAVO),
        );
        $families = $this->extractFixtureFamilies($bundle);
        self::assertSame(
            [self::FAMILY_BRAVO],
            $families,
            'family:exact must return only the whole-value match',
        );

        // Confirm the complement: a prefix of a known family does NOT
        // match :exact (differentiates it from the default prefix
        // modifier).
        $prefixBundle = $this->getBundle(
            '/apis/default/fhir/Patient?family:exact=' . rawurlencode(self::FAMILY_PREFIX),
        );
        self::assertSame(
            [],
            $this->extractFixtureFamilies($prefixBundle),
            'family:exact must NOT match a mere prefix of a family name',
        );
    }

    /**
     * `:contains` on a string param — FHIR R4 §3.1.1.5.5.2 says the
     * value is a substring match. Current server behavior:
     * implemented. StringSearchField accepts the modifier and the
     * resolver emits `<field> LIKE ?` with `%value%`.
     */
    #[Test]
    public function testStringContainsMatchesSubstring(): void
    {
        $bundle = $this->getBundle(
            '/apis/default/fhir/Patient?family:contains=' . rawurlencode('Brav'),
        );
        $families = $this->extractFixtureFamilies($bundle);
        self::assertSame(
            [self::FAMILY_BRAVO],
            $families,
            'family:contains must match a substring of a family name',
        );
    }

    /**
     * `:missing=true` on a string param — FHIR R4 §3.1.1.5.5.3 says
     * the server should return resources whose referenced element is
     * absent / null. Current server behavior: the modifier passes
     * StringSearchField's whitelist but
     * SearchFieldStatementResolver::resolveStringSearchField has no
     * match arm for SearchModifier::MISSING, so `$result` is null and
     * SearchQueryFragment::setFragment(null) throws a TypeError. The
     * route returns HTTP 500 with no body (ExceptionHandlerListener
     * strips exception detail from the response).
     *
     * This is a drift surfaced during this audit — a request-induced
     * 500 is a lower-grade reliability bug but a real one. Locked
     * here so a future fix flips the assertion from 500 to a Bundle
     * response.
     */
    #[Test]
    public function testStringMissingTrueCurrentlyReturns500(): void
    {
        $resp = $this->buildClient()->get(
            $this->baseUrl . '/apis/default/fhir/Patient?family:missing=true',
            ['headers' => $this->authHeaders()],
        );
        self::assertSame(
            500,
            $resp->getStatusCode(),
            'Drift surfaced during item 3 audit — StringSearchField admits the '
            . ':missing modifier but resolveStringSearchField has no match arm '
            . 'for it, so the server crashes on a well-formed FHIR R4 request. '
            . 'When fixed, flip this to assert a 200 Bundle response.',
        );
    }

    /**
     * `:missing=false` same drift as `:missing=true` — the resolver's
     * match arm is missing for either value.
     */
    #[Test]
    public function testStringMissingFalseCurrentlyReturns500(): void
    {
        $resp = $this->buildClient()->get(
            $this->baseUrl . '/apis/default/fhir/Patient?family:missing=false',
            ['headers' => $this->authHeaders()],
        );
        self::assertSame(
            500,
            $resp->getStatusCode(),
            'See testStringMissingTrueCurrentlyReturns500 — same drift.',
        );
    }

    /**
     * `:not` on a token param — FHIR R4 §3.1.1.5.5.4 says the server
     * should return resources whose token does NOT match the given
     * value. Current server behavior: `:not` is NOT implemented.
     * TokenSearchField accepts any modifier without validation, and
     * resolveTokenField's match block only honors `MISSING` — any
     * other modifier falls through to the standard equality branch.
     * So `gender:not=male` returns rows where `gender = 'male'`
     * (silent-equality fallback), not the complement.
     *
     * This is not a data-leak: the modifier is reduced to equality
     * against the submitted code, and the caller already has
     * user/Patient.read scope over the dataset. But the response is
     * the wrong set per spec. Locked here as a regression gate.
     */
    #[Test]
    public function testTokenNotModifierCurrentlyActsAsEquality(): void
    {
        // Baseline: locate a known gender value that at least one
        // fixture patient carries so we can see the silent-equality
        // fallback. Our fixtures all install with sex=Male which
        // maps to FHIR gender=male.
        $notBundle = $this->getBundle(
            '/apis/default/fhir/Patient?gender:not=male',
        );
        $notFamilies = $this->extractFixtureFamilies($notBundle);
        $plainBundle = $this->getBundle('/apis/default/fhir/Patient?gender=male');
        $plainFamilies = $this->extractFixtureFamilies($plainBundle);

        self::assertSame(
            $plainFamilies,
            $notFamilies,
            'gender:not=male and gender=male currently return the same fixture '
            . 'set — :not is silently reduced to equality. When :not is '
            . 'implemented, the two sets should become complements.',
        );
        // Belt-and-braces: our male fixture patients are in $plainFamilies.
        self::assertNotEmpty(
            $plainFamilies,
            'Sanity: at least one male fixture patient must exist so the '
            . 'silent-equality drift above is observable.',
        );
    }

    /**
     * `:text` on a token param — FHIR R4 §3.1.1.5.5.5 says the
     * modifier matches against the token's display text (e.g.
     * CodeableConcept.text or Coding.display). Current server
     * behavior: not implemented. TokenSearchField accepts the modifier
     * and the resolver again falls to equality against the raw value,
     * matching rows where `gender = 'male'` by coincidence of the
     * fixture value happening to be a lower-case string.
     *
     * Locked as a regression gate.
     */
    #[Test]
    public function testTokenTextModifierCurrentlyActsAsEquality(): void
    {
        $textBundle = $this->getBundle(
            '/apis/default/fhir/Patient?gender:text=male',
        );
        $plainBundle = $this->getBundle('/apis/default/fhir/Patient?gender=male');
        self::assertSame(
            $this->extractFixtureFamilies($plainBundle),
            $this->extractFixtureFamilies($textBundle),
            'gender:text=male and gender=male currently return the same fixture '
            . 'set — :text is silently reduced to equality against the raw '
            . 'value. When :text is implemented, these may diverge.',
        );
    }

    /**
     * `:in` on a token param — FHIR R4 §3.1.1.5.5.6 says the value is
     * a ValueSet reference URL and the server returns resources whose
     * token is a member of that ValueSet. Current server behavior:
     * not implemented. The ValueSet URL is treated as the raw token
     * code value, so no fixture patient matches (none has gender set
     * to the URL string).
     *
     * Locked here as a regression gate.
     */
    #[Test]
    public function testTokenInModifierCurrentlyReturnsNoFixtures(): void
    {
        $bundle = $this->getBundle(
            '/apis/default/fhir/Patient?gender:in='
            . rawurlencode('http://hl7.org/fhir/ValueSet/administrative-gender'),
        );
        self::assertSame(
            [],
            $this->extractFixtureFamilies($bundle),
            ':in modifier is not implemented. The ValueSet URL is treated as '
            . 'the raw code, so no fixture matches. When implemented, this '
            . 'should return every fixture patient (all have gender in the '
            . 'administrative-gender ValueSet).',
        );
    }

    /**
     * `:not-in` on a token param — FHIR R4 §3.1.1.5.5.7 inverse of
     * `:in`. Current server behavior: not implemented, same mechanism
     * as `:in` (URL treated as raw token value, no fixtures match).
     */
    #[Test]
    public function testTokenNotInModifierCurrentlyReturnsNoFixtures(): void
    {
        $bundle = $this->getBundle(
            '/apis/default/fhir/Patient?gender:not-in='
            . rawurlencode('http://hl7.org/fhir/ValueSet/administrative-gender'),
        );
        self::assertSame(
            [],
            $this->extractFixtureFamilies($bundle),
            ':not-in modifier is not implemented. See testTokenInModifier… '
            . 'for the mechanism.',
        );
    }

    /**
     * `:above` on a string param — FHIR R4 §3.1.1.5.5.8 is primarily
     * defined for hierarchical URI/code searches. Current server
     * behavior on a string param: not implemented. StringSearchField
     * silently coerces unknown modifiers to PREFIX (see
     * `StringSearchField::__construct`), so `family:above=Zz` behaves
     * as `family=Zz` (prefix match). Our FAMILY_PREFIX starts with
     * "ZzModGate" so all three fixtures match the Zz prefix.
     *
     * Locked here as a regression gate.
     */
    #[Test]
    public function testStringAboveModifierCurrentlyCoercesToPrefix(): void
    {
        $bundle = $this->getBundle(
            '/apis/default/fhir/Patient?family:above=Zz',
        );
        $families = $this->extractFixtureFamilies($bundle);
        sort($families);
        $expected = [self::FAMILY_ALPHA, self::FAMILY_BRAVO, self::FAMILY_CHARLIE];
        sort($expected);
        self::assertSame(
            $expected,
            $families,
            'family:above=Zz currently acts as family=Zz (prefix match) because '
            . 'StringSearchField coerces unknown modifiers to PREFIX. When '
            . ':above gets its own semantics, this test will need to flip.',
        );
    }

    /**
     * `:below` on a string param — FHIR R4 §3.1.1.5.5.8 companion to
     * `:above`. Current server behavior: same as `:above` — unknown
     * modifier is coerced to PREFIX. "family:below=Alpha" with no
     * fixture whose family starts with "Alpha" alone yields 0, so we
     * use a shared prefix to make the drift observable.
     */
    #[Test]
    public function testStringBelowModifierCurrentlyCoercesToPrefix(): void
    {
        $bundle = $this->getBundle(
            '/apis/default/fhir/Patient?family:below=' . rawurlencode(self::FAMILY_PREFIX),
        );
        $families = $this->extractFixtureFamilies($bundle);
        sort($families);
        $expected = [self::FAMILY_ALPHA, self::FAMILY_BRAVO, self::FAMILY_CHARLIE];
        sort($expected);
        self::assertSame(
            $expected,
            $families,
            'family:below=' . self::FAMILY_PREFIX . ' currently acts as a '
            . 'prefix match (see testStringAboveModifier…).',
        );
    }

    /**
     * An unregistered modifier (`:bogus`) on a string param — the FHIR
     * spec does not define this suffix. Current server behavior: same
     * coerce-to-PREFIX silent fallback as `:above` / `:below`. Locked
     * here to catch future hardening that rejects unknown modifiers
     * with 400 Bad Request.
     */
    #[Test]
    public function testStringUnknownModifierCurrentlyCoercesToPrefix(): void
    {
        $bundle = $this->getBundle(
            '/apis/default/fhir/Patient?family:bogus=' . rawurlencode(self::FAMILY_PREFIX),
        );
        $families = $this->extractFixtureFamilies($bundle);
        sort($families);
        $expected = [self::FAMILY_ALPHA, self::FAMILY_BRAVO, self::FAMILY_CHARLIE];
        sort($expected);
        self::assertSame(
            $expected,
            $families,
            'family:bogus=... currently acts as prefix match. A future hardening '
            . 'that rejects unknown modifiers with 400 Bad Request should flip '
            . 'this test to assert the 400 response.',
        );
    }

    /**
     * `_count=1` next-link emission on the Bundle — the sibling file
     * `FhirSearchModifiersApiTest.php` documents this drift in prose
     * only. Mirrored here as a skip-marker so the matrix is
     * visually complete.
     */
    #[Test]
    public function testCountNextLinkDriftIntentionallyLocked(): void
    {
        $this->markTestSkipped(
            'PR #14667 identified this drift — _count returns the correct '
            . 'total + entry count, but FhirResourcesService::createBundle '
            . 'never calls QueryPagination::getFhirLinks() so the Bundle '
            . 'omits link[relation="next"]. When the prod fix lands, flip '
            . 'this skip to a real assertion against the next-link URL.',
        );
    }

    /**
     * `_include=<Resource>:<ref>` is advertised via `searchInclude:
     * ['*']` in CapabilityStatement but is not implemented. Mirrored
     * from the sibling file as a skip-marker.
     */
    #[Test]
    public function testIncludeDriftIntentionallyLocked(): void
    {
        $this->markTestSkipped(
            'PR #14667 identified this drift — _include is advertised in '
            . 'metadata but not registered in any search-factory, so '
            . 'ResourceServiceSearchTrait::createSearchParameterForField '
            . 'throws SearchFieldException. The outer getAll() swallows the '
            . 'exception and returns an empty bundle. When _include is '
            . 'implemented, flip this skip to assert the included resources '
            . 'appear in the Bundle.',
        );
    }

    /**
     * `_sort` on a resource whose service does not override
     * FhirServiceBase::searchForOpenEMRRecordsWithConfig (Encounter,
     * Observation, etc.) — the parameter is accepted without error
     * but the DB returns insertion order. Mirrored from the sibling
     * file as a skip-marker; Encounter is the probe case here.
     */
    #[Test]
    public function testSortOnEncounterDriftIntentionallyLocked(): void
    {
        $this->markTestSkipped(
            'PR #14667 identified this drift — _sort is only honored by '
            . 'services that override FhirServiceBase::searchForOpenEMRRecords'
            . 'WithConfig (Patient and Task today). On Encounter and other '
            . 'resources the _sort param is accepted without error but the '
            . 'DB returns insertion order. When Encounter opts into the '
            . 'with-config variant, flip this skip to assert sort order.',
        );
    }

    /**
     * @param array<mixed> $bundle
     *
     * @return list<string>
     */
    private function extractFixtureFamilies(array $bundle): array
    {
        $entries = $bundle['entry'] ?? [];
        self::assertIsArray($entries);
        $knownUuids = [
            $this->patientAlpha['uuid'] ?? '',
            $this->patientBravo['uuid'] ?? '',
            $this->patientCharlie['uuid'] ?? '',
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
                // Ignore non-fixture patients already in the DB — tests
                // assert the RELATIVE set of our known fixtures only.
                continue;
            }
            $names = $resource['name'] ?? null;
            self::assertIsArray($names);
            $first = $names[0] ?? null;
            self::assertIsArray($first);
            $family = $first['family'] ?? null;
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
            'headers' => $this->authHeaders(),
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
     * @return array{Authorization: string, Accept: string}
     */
    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . (string) $this->accessToken,
            'Accept' => 'application/fhir+json',
        ];
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

        $maxRow = QueryUtils::querySingleRow(
            'SELECT COALESCE(MAX(pid), 0) AS max_pid FROM patient_data WHERE pid >= 90000',
        );
        self::assertIsArray($maxRow);
        $maxPidRaw = $maxRow['max_pid'] ?? 0;
        self::assertIsNumeric($maxPidRaw);
        $pid = max(90000, (int) $maxPidRaw + 1);
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
