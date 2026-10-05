<?php

/**
 * FhirBundleImporter.
 *
 * Walks Synthea FHIR R4 Bundle *.json files and POSTs each entry to the
 * per-resource FHIR write endpoints added in #13281.
 *
 * See src/Services/FHIR/Import/README.md for the readiness checklist of
 * server-side bugs, strict validators, unimplemented resources, and the
 * peelable transforms below that work around each item.
 *
 * CLI entry: bin/console openemr:fhir-import (see
 * src/Common/Command/FhirImport.php).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\Import;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Common\Database\QueryUtils;

final class FhirBundleImporter
{
    /** @var list<string> */
    private const WRITABLE_RESOURCES = [
        'AllergyIntolerance',
        'Appointment',
        'CarePlan',
        'CareTeam',
        'Condition',
        'Coverage',
        'Device',
        'Encounter',
        'Goal',
        'Immunization',
        'Medication',
        'MedicationRequest',
        'Observation',
        'Organization',
        'Patient',
        'Person',
        'Practitioner',
        'PractitionerRole',
        'Questionnaire',
        'QuestionnaireResponse',
        'RelatedPerson',
        'ServiceRequest',
    ];

    /** @var list<string> */
    private const IMPORT_ORDER = [
        'Organization',
        'Location',
        'Practitioner',
        'PractitionerRole',
        'Patient',
        'Person',
        'RelatedPerson',
        'Coverage',
        'Device',
        'Medication',
        'Encounter',
        'Condition',
        'AllergyIntolerance',
        'Immunization',
        'Observation',
        'MedicationRequest',
        'CarePlan',
        'CareTeam',
        'Goal',
        'ServiceRequest',
        'Appointment',
        'Questionnaire',
        'QuestionnaireResponse',
    ];

    /** @var list<string> */
    private const OBSERVATION_ACCEPTED_CATEGORIES = ['vital-signs'];

    /** @var list<string> */
    private const OBSERVATION_REJECTED_LOINCS = [
        '39156-5', // BMI — "derived from height and weight; post those instead"
        '72514-3', // pain scale — "written through their form"
    ];

    /** @var array<string, string> */
    private array $referenceMap = [];

    /** @var array{bundles: int, posted: int, skipped: int, transformSkipped: int, failed: int} */
    private array $counters = [
        'bundles' => 0,
        'posted' => 0,
        'skipped' => 0,
        'transformSkipped' => 0,
        'failed' => 0,
    ];

    // Universal transforms currently never drop a resource (they shim shape
    // issues present on every resource type), so the signature omits the
    // |null branch. When a future universal transform needs to drop, widen
    // the return type here and add a null check in applyTransforms().
    /** @var list<callable(array<array-key, mixed>, array<string, array<array-key, mixed>>): array<array-key, mixed>> */
    private readonly array $universalTransforms;

    /** @var array<string, list<callable(array<array-key, mixed>, array<string, array<array-key, mixed>>): (array<array-key, mixed>|null)>> */
    private array $perTypeTransforms;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $site,
        private readonly string $openemrPath,
        private readonly string $adminUser,
        private readonly string $adminPass,
        private readonly string $logFile = '/tmp/fhir_import.log',
    ) {
        $this->universalTransforms = [
            self::transformStripExtensions(...),
            self::transformStripNonAsciiTelecomEmails(...),
        ];
        $this->perTypeTransforms = [
            'Patient'           => [self::transformTagFirstNameOfficial(...)],
            'Practitioner'      => [self::transformTagFirstNameOfficial(...)],
            'Organization'      => [self::transformMintOrganizationNpi(...)],
            'CarePlan'          => [self::transformCareplanForcePlanIntent(...)],
            'CareTeam'          => [self::transformCareteamKeepPractitionerParticipants(...)],
            'Observation'       => [self::transformObservationDropRejectedLoincs(...)],
            'MedicationRequest' => [self::transformInlineMedicationReference(...)],
        ];
    }

    public function run(string $sourceDir): int
    {
        $sourceDir = rtrim($sourceDir, '/');
        $this->logMessage("OpenEMR path: " . $this->openemrPath . "\n");
        $this->logMessage("FHIR imports location: " . $sourceDir . "\n");
        $this->logMessage("Site: " . $this->site . "\n");
        $this->logMessage("Base URL: " . $this->baseUrl . "\n");

        $scopes = $this->readScopeListing();
        if ($scopes === null) {
            return 1;
        }

        $http = new Client([
            'base_uri' => $this->baseUrl,
            'verify' => false,
            'http_errors' => false,
            'timeout' => 60,
        ]);

        $client = $this->registerOauth2Client($http, $scopes);
        if ($client === null) {
            return 1;
        }
        // From here on the oauth client exists on the server; every exit
        // path (early return on password-grant failure, empty-source failure,
        // normal completion, or an uncaught Throwable from importBundle) has
        // to delete it. Wrap in try/finally so a thrown exception can't leak
        // the client. registerOauth2Client() handles the "DB UPDATE threw
        // after DCR succeeded" sub-case internally — it deletes the client
        // and returns null, so the branch above sees no $client to clean up.
        try {
            $token = $this->passwordGrant($http, $client['id'], $client['secret'], $scopes);
            if ($token === null) {
                return 1;
            }

            $files = $this->listSortedBundleFiles($sourceDir);
            if ($files === []) {
                $this->logMessage("FAIL: no *.json files in " . $sourceDir . "\n");
                return 1;
            }

            $start = (int) round(microtime(true) * 1000);
            foreach ($files as $file) {
                $this->importBundle($file, $http, $token);
            }
            $seconds = (int) round(((int) round(microtime(true) * 1000) - $start) / 1000);

            $this->logMessage(sprintf(
                "\nCompleted FHIR import: %d bundles, %d POSTed, %d skipped (unsupported), %d filtered-by-transform, %d failed, %d seconds total.\n",
                $this->counters['bundles'],
                $this->counters['posted'],
                $this->counters['skipped'],
                $this->counters['transformSkipped'],
                $this->counters['failed'],
                $seconds,
            ));

            return $this->counters['failed'] > 0 ? 1 : 0;
        } finally {
            $this->deleteOauth2Client($client['id']);
        }
    }

    private function readScopeListing(): ?string
    {
        $file = $this->openemrPath . '/docker/library/api-scope-listing';
        if (!is_readable($file)) {
            $this->logMessage("FAIL: cannot read " . $file . "\n");
            return null;
        }
        $contents = file_get_contents($file);
        if ($contents === false) {
            $this->logMessage("FAIL: cannot read " . $file . "\n");
            return null;
        }
        return trim($contents);
    }

    /** @return array{id: string, secret: string}|null */
    private function registerOauth2Client(Client $http, string $scopes): ?array
    {
        $this->logMessage("Registering oauth2 client...\n");
        $payload = (string) json_encode([
            'application_type' => 'private',
            'redirect_uris' => [$this->baseUrl . '/swagger/oauth2-redirect.html'],
            'client_name' => 'fhir_import ' . date('c'),
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['fhir_import@example.org'],
            'scope' => $scopes,
        ]);
        try {
            $resp = $http->post('/oauth2/' . $this->site . '/registration', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $payload,
            ]);
        } catch (GuzzleException) {
            $this->logMessage("FAIL: client registration transport error\n");
            return null;
        }
        $status = $resp->getStatusCode();
        $body = (string) $resp->getBody();
        $data = json_decode($body, true);
        if (
            $status !== 200
            || !is_array($data)
            || !isset($data['client_id'], $data['client_secret'])
            || !is_string($data['client_id'])
            || !is_string($data['client_secret'])
            || $data['client_id'] === ''
        ) {
            $this->logMessage("FAIL: client registration returned " . $status . ": " . $body . "\n");
            return null;
        }
        // Grant password type + flip is_enabled=1. DCR cannot authorize these,
        // so an administrator ordinarily does it in the UI; here we are that
        // administrator.
        //
        // If the UPDATE fails after DCR succeeded, the oauth client row
        // already exists on the server but run()'s try/finally hasn't started
        // yet. Delete the stranded client here and rethrow so the caller
        // treats it as a registration failure.
        try {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
                ['password', $data['client_id']],
                true,
            );
        } catch (\Throwable $e) {
            try {
                $this->deleteOauth2Client($data['client_id']);
            } catch (\Throwable) {
                // A SQL-layer exception here is less interesting than the
                // original UPDATE failure; rethrow the UPDATE exception so
                // the delete-side error does not replace the real cause.
                // Error subclasses (TypeError, ParseError, etc.) are
                // deliberately not caught so they still propagate — those
                // indicate a programmer bug the global handler must surface.
                throw $e;
            }
            throw $e;
        }
        return ['id' => $data['client_id'], 'secret' => $data['client_secret']];
    }

    private function passwordGrant(Client $http, string $clientId, string $clientSecret, string $scopes): ?string
    {
        $this->logMessage("Obtaining admin bearer token via password grant...\n");
        try {
            $resp = $http->post('/oauth2/' . $this->site . '/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'user_role' => 'users',
                    'username' => $this->adminUser,
                    'password' => $this->adminPass,
                    'scope' => $scopes,
                ],
            ]);
        } catch (GuzzleException) {
            $this->logMessage("FAIL: token request transport error\n");
            return null;
        }
        $status = $resp->getStatusCode();
        $body = (string) $resp->getBody();
        $data = json_decode($body, true);
        if (
            $status !== 200
            || !is_array($data)
            || !isset($data['access_token'])
            || !is_string($data['access_token'])
            || $data['access_token'] === ''
        ) {
            $this->logMessage("FAIL: token request returned " . $status . ": " . $body . "\n");
            return null;
        }
        $this->logMessage("Token acquired.\n");
        return $data['access_token'];
    }

    private function deleteOauth2Client(string $clientId): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
            [$clientId],
            true,
        );
    }

    /** @return list<string> */
    private function listSortedBundleFiles(string $sourceDir): array
    {
        $files = glob($sourceDir . '/*.json');
        if ($files === false || $files === []) {
            return [];
        }
        usort($files, static function (string $a, string $b): int {
            $priority = static function (string $f): int {
                $n = basename($f);
                if (str_starts_with($n, 'hospitalInformation')) {
                    return 0;
                }
                if (str_starts_with($n, 'practitionerInformation')) {
                    return 1;
                }
                return 2;
            };
            return $priority($a) <=> $priority($b) ?: strcmp($a, $b);
        });
        return $files;
    }

    private function importBundle(string $file, Client $http, string $token): void
    {
        // Count unreadable / non-Bundle inputs as failures. If every input
        // file gets silently skipped the run would otherwise return success
        // with zero imported bundles — misleading when the sourcePath is
        // wrong, the files are corrupt, or synthea produced a different
        // shape than expected.
        $raw = file_get_contents($file);
        if ($raw === false) {
            $this->counters['failed']++;
            $this->logMessage("FAIL (unreadable): " . basename($file) . "\n");
            return;
        }
        $bundle = json_decode($raw, true);
        if (!is_array($bundle) || ($bundle['resourceType'] ?? null) !== 'Bundle') {
            $this->counters['failed']++;
            $this->logMessage("FAIL (not a Bundle): " . basename($file) . "\n");
            return;
        }
        $this->counters['bundles']++;
        $this->logMessage("Importing bundle: " . basename($file) . "\n");

        $entries = $bundle['entry'] ?? [];
        if (!is_array($entries)) {
            return;
        }

        // Group entries by resource type for dependency-ordered processing,
        // and index by fullUrl so transforms that resolve a same-bundle
        // reference (MedicationRequest.medicationReference → Medication.code)
        // can look up the referent before we POST.
        /** @var array<string, list<array<array-key, mixed>>> $byType */
        $byType = [];
        /** @var array<string, array<array-key, mixed>> $bundleIndex */
        $bundleIndex = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $resource = $entry['resource'] ?? null;
            if (!is_array($resource)) {
                continue;
            }
            $url = $entry['fullUrl'] ?? null;
            if (is_string($url)) {
                $bundleIndex[$url] = $resource;
            }
            $type = $resource['resourceType'] ?? null;
            if (!is_string($type)) {
                continue;
            }
            $byType[$type][] = $entry;
        }

        $extra = array_diff(array_keys($byType), self::IMPORT_ORDER);
        foreach ([...self::IMPORT_ORDER, ...$extra] as $type) {
            foreach ($byType[$type] ?? [] as $entry) {
                $this->importEntry($entry, $type, $bundleIndex, $http, $token);
            }
        }
    }

    /**
     * @param array<array-key, mixed> $entry
     * @param array<string, array<array-key, mixed>> $bundleIndex
     */
    private function importEntry(array $entry, string $type, array $bundleIndex, Client $http, string $token): void
    {
        if (!in_array($type, self::WRITABLE_RESOURCES, true)) {
            $this->counters['skipped']++;
            return;
        }
        $resource = $entry['resource'] ?? null;
        if (!is_array($resource)) {
            return;
        }
        $fullUrl = $entry['fullUrl'] ?? null;
        $fullUrl = is_string($fullUrl) ? $fullUrl : null;

        // Transforms run BEFORE reference rewriting on purpose:
        // transformInlineMedicationReference looks up the referenced Medication
        // in the bundleIndex (keyed by urn:uuid:...). If rewriteReferences ran
        // first, it would have already replaced urn:uuid:X with
        // Medication/<uuid>, and the bundleIndex lookup would miss.
        // Transforms that don't care about refs are order-safe.
        //
        // The Bundle-local `id` is kept on the resource across transforms —
        // transformMintOrganizationNpi uses it to seed a deterministic NPI,
        // and stripping it earlier would collapse every Organization that
        // shares a name onto a single minted NPI. The id gets stripped at
        // the POST boundary (below) so the server still mints its own uuid.
        $transformed = $this->applyTransforms($resource, $type, $bundleIndex);
        if ($transformed === null) {
            $this->counters['transformSkipped']++;
            return;
        }
        $resource = $this->rewriteReferences($transformed);

        // Strip the Bundle-local id just before POST. The server mints its
        // own uuid; keeping the Synthea id would force the FHIR write path
        // to treat the POST as an update against a nonexistent resource.
        unset($resource['id']);

        try {
            $resp = $http->post('/apis/' . $this->site . '/fhir/' . $type, [
                'headers' => [
                    'Content-Type' => 'application/fhir+json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'body' => (string) json_encode($resource),
            ]);
        } catch (GuzzleException) {
            $this->counters['failed']++;
            $this->logMessage(sprintf("  FAIL %s: transport error\n", $type));
            return;
        }
        $status = $resp->getStatusCode();
        $body = (string) $resp->getBody();

        if ($status !== 201) {
            $this->counters['failed']++;
            $this->logMessage(sprintf(
                "  FAIL %s: HTTP %d — %s\n",
                $type,
                $status,
                substr($body, 0, 200),
            ));
            return;
        }

        $newId = $this->extractResourceId($body);
        if ($newId === null) {
            $this->counters['failed']++;
            $this->logMessage(sprintf("  FAIL %s: 201 but no id in body — %s\n", $type, substr($body, 0, 200)));
            return;
        }

        $this->counters['posted']++;
        $this->registerReferenceTargets($type, $newId, $fullUrl, $resource);
    }

    /**
     * The FHIR write services return non-uniform response bodies: Patient
     * uses `uuid`, Encounter uses `euuid`, Condition uses `cuuid`, etc. Try
     * the canonical keys first, then fall back to any *uuid-suffixed key or
     * bare `id`. Return null if nothing matches so the caller can treat it
     * as a visible failure rather than silently lose the resource from the
     * ref map.
     */
    private function extractResourceId(string $body): ?string
    {
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return null;
        }
        foreach (['uuid', 'id'] as $k) {
            if (isset($decoded[$k]) && is_string($decoded[$k]) && $decoded[$k] !== '') {
                return $decoded[$k];
            }
        }
        foreach ($decoded as $k => $v) {
            if (is_string($k) && is_string($v) && $v !== '' && str_ends_with($k, 'uuid')) {
                return $v;
            }
        }
        return null;
    }

    /**
     * Synthea emits three cross-bundle reference forms we need to resolve
     * later: `urn:uuid:X` (within-bundle), `Practitioner?identifier=<sys>|<val>`
     * (FHIR search-URL), and `{identifier: {system, value}}` with no
     * `reference` string (Reference datatype logical identifier). Populate
     * the referenceMap with all three keys per POSTed resource so the shared
     * rewriter handles every form uniformly.
     *
     * @param array<array-key, mixed> $resource
     */
    private function registerReferenceTargets(string $type, string $newId, ?string $fullUrl, array $resource): void
    {
        $target = $type . '/' . $newId;
        if ($fullUrl !== null) {
            $this->referenceMap[$fullUrl] = $target;
        }
        $identifiers = $resource['identifier'] ?? [];
        if (!is_array($identifiers)) {
            return;
        }
        foreach ($identifiers as $identifier) {
            if (!is_array($identifier)) {
                continue;
            }
            $system = $identifier['system'] ?? null;
            $value = $identifier['value'] ?? null;
            if (!is_string($system) || !is_string($value) || $system === '' || $value === '') {
                continue;
            }
            $this->referenceMap[$type . '?identifier=' . $system . '|' . $value] = $target;
            $this->referenceMap[$system . '|' . $value] = $target;
        }
    }

    /**
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>|null
     */
    private function applyTransforms(array $resource, string $type, array $bundleIndex): ?array
    {
        foreach ($this->universalTransforms as $fn) {
            // Universal transforms don't drop (see signature) so no null check.
            $resource = $fn($resource, $bundleIndex);
        }
        foreach ($this->perTypeTransforms[$type] ?? [] as $fn) {
            $next = $fn($resource, $bundleIndex);
            if ($next === null) {
                return null;
            }
            $resource = $next;
        }
        return $resource;
    }

    /**
     * Recursively rewrite references into server-side resource paths.
     *
     * Handles the three Synthea reference forms uniformly: within-bundle
     * `urn:uuid:...`, FHIR search-URL form, and the Reference datatype's
     * logical-identifier form (object with `identifier.{system,value}` and
     * no `reference` string). For the third form the function adds a
     * `reference` string to the node and leaves the original `identifier`
     * object in place since the server reads both.
     *
     * @param array<array-key, mixed> $node
     * @return array<array-key, mixed>
     */
    private function rewriteReferences(array $node): array
    {
        foreach ($node as $k => $v) {
            if ($k === 'reference' && is_string($v) && isset($this->referenceMap[$v])) {
                $node[$k] = $this->referenceMap[$v];
                continue;
            }
            if (is_array($v)) {
                $node[$k] = $this->rewriteReferences($v);
            }
        }
        if (!isset($node['reference']) && isset($node['identifier']) && is_array($node['identifier'])) {
            $sys = $node['identifier']['system'] ?? null;
            $val = $node['identifier']['value'] ?? null;
            if (is_string($sys) && is_string($val) && $sys !== '' && $val !== '') {
                $key = $sys . '|' . $val;
                if (isset($this->referenceMap[$key])) {
                    $node['reference'] = $this->referenceMap[$key];
                }
            }
        }
        return $node;
    }

    private function logMessage(string $message): void
    {
        echo $message;
        file_put_contents($this->logFile, $message, FILE_APPEND);
    }

    // ========================================================================
    // Peelable Synthea → OpenEMR transforms.
    //
    // Each transform below works around a specific README.md checklist item.
    // Delete the method AND its registry entry in __construct() when the
    // upstream fix lands. The checklist tag in each docblock (e.g.
    // [README A1]) maps directly to a bullet in
    // src/Services/FHIR/Import/README.md so grep + delete is one step.
    //
    // Signature: each takes the current resource and the bundle index
    // (fullUrl → resource) and returns either the transformed resource or
    // null to drop it from the import. Transforms that can drop return
    // ?array; non-dropping ones return array.
    // ========================================================================

    /**
     * [README A1] Strip extension[] from every resource.
     *
     * FHIRElement::__construct (src/FHIR/R4/FHIRElement.php:141-143) stores
     * raw JSON-decoded extension arrays into $this->extension without
     * wrapping them as FHIRExtension objects. Any service that iterates
     * ->getExtension() and calls ->getUrl() on an item crashes with "Call
     * to a member function getUrl() on array". Observed on Patient POST;
     * applies universally to any resource type whose write path reads
     * extensions. Strip here so no resource type can trigger the crash.
     *
     * Known loss: race/ethnicity/birthsex/mother's-maiden-name/birthplace
     * demographics and synthea telemetry extensions. Delete this transform
     * when the FHIRElement constructor wraps raw arrays into FHIRExtension.
     *
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>
     */
    private static function transformStripExtensions(array $resource, array $bundleIndex): array
    {
        unset($resource['extension']);
        return $resource;
    }

    /**
     * [README A2] Mark the first HumanName as use="official" if no use is set.
     *
     * FhirPractitionerService::parseFhirResource (and FhirPatientService)
     * only read the HumanName with use="official", leaving fname/lname
     * empty when no name carries that attribute. Synthea Practitioner names
     * omit "use" entirely. Tag the first name as official so the mapping
     * fires. Delete when the server falls back to name[0] on
     * no-official-match.
     *
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>
     */
    private static function transformTagFirstNameOfficial(array $resource, array $bundleIndex): array
    {
        if (
            isset($resource['name'])
            && is_array($resource['name'])
            && isset($resource['name'][0])
            && is_array($resource['name'][0])
            && !isset($resource['name'][0]['use'])
        ) {
            $resource['name'][0]['use'] = 'official';
        }
        return $resource;
    }

    /**
     * [README A3] Drop telecom[] entries carrying a non-ASCII email value.
     *
     * The write path's email validator rejects RFC 6531-style unicode local
     * parts (observed: Arturo47.Valentín837@example.com). Synthea emits
     * them for Practitioners/Patients with internationalized first names.
     * Only drop entries whose value falls outside pure ASCII so the rest of
     * the telecom collection stays. Applies generically to any resource
     * with telecom[].
     *
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>
     */
    private static function transformStripNonAsciiTelecomEmails(array $resource, array $bundleIndex): array
    {
        if (!isset($resource['telecom']) || !is_array($resource['telecom'])) {
            return $resource;
        }
        $kept = [];
        foreach ($resource['telecom'] as $t) {
            if (
                is_array($t)
                && (($t['system'] ?? null) === 'email')
                && isset($t['value'])
                && is_string($t['value'])
                && preg_match('/[^\x00-\x7F]/', $t['value']) === 1
            ) {
                continue;
            }
            $kept[] = $t;
        }
        $resource['telecom'] = $kept;
        return $resource;
    }

    /**
     * [README D1] Mint a deterministic NPI identifier for Synthea Organizations.
     *
     * FhirOrganizationFacilityService reads facility_npi from
     * identifier[system=http://hl7.org/fhir/sid/us-npi].value. Synthea
     * Organizations carry only identifier[system=https://.../synthea], so
     * the write path rejects them with "facility_npi must be provided".
     * The NPI requirement is likely ONC-cert-related and will stay; the
     * transform mints a deterministic 10-digit NPI from a SHA-1 hash of
     * the Organization id so re-runs produce the same NPI (idempotent
     * across bundle imports).
     *
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>
     */
    private static function transformMintOrganizationNpi(array $resource, array $bundleIndex): array
    {
        $identifiers = $resource['identifier'] ?? [];
        if (!is_array($identifiers)) {
            $identifiers = [];
        }
        foreach ($identifiers as $i) {
            if (is_array($i) && (($i['system'] ?? null) === 'http://hl7.org/fhir/sid/us-npi')) {
                return $resource;
            }
        }
        $seedCandidate = $resource['id'] ?? ($resource['name'] ?? '');
        $seed = is_string($seedCandidate) ? $seedCandidate : '';
        $digits = (string) preg_replace('/[^0-9]/', '', hash('sha1', $seed));
        $npi = str_pad(substr($digits, 0, 10), 10, '0', STR_PAD_LEFT);
        $identifiers[] = ['system' => 'http://hl7.org/fhir/sid/us-npi', 'value' => $npi];
        $resource['identifier'] = $identifiers;
        return $resource;
    }

    /**
     * [README B2] Rewrite CarePlan.intent → "plan".
     *
     * FhirCarePlanService rejects every R4 intent valueset value except
     * "plan". Synthea emits "order" for care plans tied to orders. Flatten
     * to "plan" so the record lands; semantics drift slightly but suffices
     * for a dev tool. Delete when the write path accepts the full R4
     * valueset.
     *
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>
     */
    private static function transformCareplanForcePlanIntent(array $resource, array $bundleIndex): array
    {
        if (($resource['intent'] ?? null) !== 'plan') {
            $resource['intent'] = 'plan';
        }
        return $resource;
    }

    /**
     * [README C2+C3+B3] Drop Observations the write path refuses.
     *
     * Two tiers:
     *   1. Category filter — only vital-signs lands; laboratory, survey,
     *      and social-history are rejected ("written through procedure
     *      order / form / patient history").
     *   2. LOINC filter within accepted categories — vital-signs still
     *      refuses derived measures (BMI 39156-5, pain scale 72514-3).
     *
     * Trim tier 1 as the write path adds laboratory/survey/social-history
     * support. Trim tier 2 as narrow bans relax. The transform disappears
     * entirely once every category is accepted.
     *
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>|null
     */
    private static function transformObservationDropRejectedLoincs(array $resource, array $bundleIndex): ?array
    {
        $categoryCodes = [];
        $categories = $resource['category'] ?? [];
        if (is_array($categories)) {
            foreach ($categories as $cat) {
                if (!is_array($cat)) {
                    continue;
                }
                $coding = $cat['coding'] ?? [];
                if (!is_array($coding)) {
                    continue;
                }
                foreach ($coding as $c) {
                    if (is_array($c) && isset($c['code']) && is_string($c['code'])) {
                        $categoryCodes[] = $c['code'];
                    }
                }
            }
        }
        if ($categoryCodes !== [] && array_intersect($categoryCodes, self::OBSERVATION_ACCEPTED_CATEGORIES) === []) {
            return null;
        }

        $code = $resource['code'] ?? null;
        $codeCoding = is_array($code) ? ($code['coding'] ?? []) : [];
        if (is_array($codeCoding)) {
            foreach ($codeCoding as $coding) {
                if (
                    is_array($coding)
                    && isset($coding['code'])
                    && is_string($coding['code'])
                    && in_array($coding['code'], self::OBSERVATION_REJECTED_LOINCS, true)
                ) {
                    return null;
                }
            }
        }
        return $resource;
    }

    /**
     * [README B4] Filter CareTeam.participant[] to Practitioner-member only.
     *
     * FhirCareTeamService::parseFhirResource throws "CareTeam.participant
     * .member supports only Practitioner references on write" when any
     * participant member resolves to a non-Practitioner. Synthea's CareTeam
     * carries three participants per team: the Patient, the Practitioner,
     * and the Organization. Keep only the Practitioner participants. If
     * every participant is non-Practitioner the whole CareTeam drops
     * (participant has 1..* cardinality). Delete when the write path
     * accepts Patient/Organization members.
     *
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>|null
     */
    private static function transformCareteamKeepPractitionerParticipants(array $resource, array $bundleIndex): ?array
    {
        if (!isset($resource['participant']) || !is_array($resource['participant'])) {
            return $resource;
        }
        $kept = [];
        foreach ($resource['participant'] as $p) {
            if (!is_array($p)) {
                continue;
            }
            $member = $p['member'] ?? null;
            $ref = is_array($member) ? ($member['reference'] ?? null) : null;
            if (!is_string($ref)) {
                continue;
            }
            if (str_starts_with($ref, 'Practitioner/') || str_starts_with($ref, 'Practitioner?')) {
                $kept[] = $p;
                continue;
            }
            if (str_starts_with($ref, 'urn:uuid:')) {
                $referent = $bundleIndex[$ref] ?? null;
                if (is_array($referent) && ($referent['resourceType'] ?? null) === 'Practitioner') {
                    $kept[] = $p;
                }
            }
        }
        if ($kept === []) {
            return null;
        }
        $resource['participant'] = $kept;
        return $resource;
    }

    /**
     * [README B1] Convert MedicationRequest.medicationReference → medication
     * CodeableConcept by resolving the referenced Medication in the current
     * bundle and inlining its code. The write path only accepts
     * medicationCodeableConcept. Delete when the server accepts
     * medicationReference.
     *
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>
     */
    private static function transformInlineMedicationReference(array $resource, array $bundleIndex): array
    {
        $medRef = $resource['medicationReference'] ?? null;
        if (!is_array($medRef)) {
            return $resource;
        }
        $ref = $medRef['reference'] ?? null;
        if (!is_string($ref)) {
            return $resource;
        }
        $referent = $bundleIndex[$ref] ?? null;
        if (!is_array($referent) || !isset($referent['code'])) {
            return $resource;
        }
        $resource['medicationCodeableConcept'] = $referent['code'];
        unset($resource['medicationReference']);
        return $resource;
    }
}
