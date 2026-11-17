<?php

/**
 * Records the HTTP contract of the patient portal's portal/patient pages and JSON API, as a
 * staff user, so the Phreeze replacement can be checked against it: status codes, response
 * shapes and the keys of every record. The page scripts depend on these keys.
 *
 * Setup is HTTP-only (see tests/Tests/WebUi/README.md): the test logs in as admin and creates
 * its own patient through the new-patient form. The patient is not removed afterwards, because
 * deleting a patient over HTTP needs the allow_pat_delete global; the portal documents the test
 * creates are deleted through the API.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\WebUi;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class PortalPatientApiContractTest extends TestCase
{
    /** The camelCase keys of a portal document, in column order. */
    private const DOCUMENT_KEYS = [
        'id', 'pid', 'facility', 'provider', 'encounter', 'createDate', 'docType',
        'patientSignedStatus', 'patientSignedTime', 'authorizeSignedTime', 'acceptSignedStatus',
        'authorizingSignator', 'reviewDate', 'denialReason', 'authorizedSignature',
        'patientSignature', 'fullDocument', 'fileName', 'filePath', 'templateData',
    ];

    /** The keys of api/patient/{id} (the Patient model, plus the review note). */
    private const PATIENT_KEYS = [
        'note', 'id', 'title', 'language', 'financial', 'fname', 'lname', 'mname', 'dob', 'street',
        'postalCode', 'city', 'state', 'countryCode', 'driversLicense', 'ss', 'occupation',
        'phoneHome', 'phoneBiz', 'phoneContact', 'phoneCell', 'pharmacyId', 'status',
        'contactRelationship', 'date', 'sex', 'referrer', 'referrerid', 'providerid',
        'refProviderid', 'email', 'emailDirect', 'ethnoracial', 'race', 'ethnicity', 'religion',
        'familySize', 'pubpid', 'pid', 'hipaaMail', 'hipaaVoice', 'hipaaNotice', 'hipaaMessage',
        'hipaaAllowsms', 'hipaaAllowemail', 'regdate', 'mothersname', 'guardiansname',
        'allowImmRegUse', 'allowImmInfoShare', 'allowHealthInfoEx', 'allowPatientPortal',
        'careTeam', 'county',
    ];

    /** The keys of an api/patientdata row (the profile form's field set). */
    private const PROFILE_KEYS = [
        'id', 'title', 'language', 'financial', 'fname', 'lname', 'mname', 'dob', 'street',
        'postalCode', 'city', 'state', 'countryCode', 'driversLicense', 'ss', 'occupation',
        'phoneHome', 'phoneBiz', 'phoneContact', 'phoneCell', 'pharmacyId', 'status',
        'contactRelationship', 'date', 'sex', 'referrer', 'referrerid', 'providerid',
        'refProviderid', 'email', 'emailDirect', 'ethnoracial', 'race', 'ethnicity', 'religion',
        'interpreter', 'migrantseasonal', 'familySize', 'monthlyIncome', 'billingNote', 'homeless',
        'financialReview', 'pubpid', 'pid', 'genericname1', 'genericval1', 'genericname2',
        'genericval2', 'hipaaMail', 'hipaaVoice', 'hipaaNotice', 'hipaaMessage', 'hipaaAllowsms',
        'hipaaAllowemail', 'squad', 'fitness', 'referralSource', 'usertext1', 'usertext2',
        'usertext3', 'usertext4', 'usertext5', 'usertext6', 'usertext7', 'usertext8', 'userlist1',
        'userlist2', 'userlist3', 'userlist4', 'userlist5', 'userlist6', 'userlist7', 'pricelevel',
        'regdate', 'contrastart', 'completedAd', 'adReviewed', 'vfc', 'mothersname',
        'guardiansname', 'allowImmRegUse', 'allowImmInfoShare', 'allowHealthInfoEx',
        'allowPatientPortal', 'deceasedDate', 'deceasedReason', 'soapImportStatus',
        'cmsportalLogin', 'careTeam', 'county', 'industry',
    ];

    /**
     * The profile form always sends its provider selects. A new chart has these empty, and the
     * profile's validation, unchanged from Phreeze, refuses an empty number.
     */
    private const PROVIDER_FIELDS = ['providerid' => '0', 'refProviderid' => '0', 'careTeam' => '0'];

    private const LIST_KEYS = ['orderBy', 'orderDesc', 'rows', 'totalResults', 'totalPages', 'pageSize', 'currentPage'];

    /** Template id 3, "Hipaa Document", ships with every install. */
    private const TEMPLATE_ID = '3';

    private static string $baseUrl = '';
    private static ?Client $http = null;
    private static int $pid = 0;
    private static int $patientId = 0;
    private static string $apiToken = '';

    /** @var list<int> */
    private array $createdDocuments = [];

    /**
     * The site under test, from OPENEMR_BASE_URL_API.
     */
    public static function setUpBeforeClass(): void
    {
        self::$baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
    }

    /**
     * Signs in once as staff and creates the test patient, unless HTTPS tests are skipped.
     */
    protected function setUp(): void
    {
        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        if (self::$http instanceof Client) {
            return;
        }

        $http = new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'allow_redirects' => true,
            'timeout' => 30,
        ]);
        if ($http->get(self::$baseUrl . '/')->getHeaderLine('Server') === '') {
            $message = 'The portal contract test needs a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }

        $http->get(self::$baseUrl . '/interface/login/login.php?site=default');
        $http->post(self::$baseUrl . '/interface/main/main_screen.php?auth=login&site=default', [
            'form_params' => [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
            ],
        ]);

        self::$http = $http;
        self::$pid = $this->createPatient();
        self::$apiToken = $this->apiToken(self::$pid);
        $profile = $this->json($this->get('/portal/patient/api/patientdata?patientId=' . self::$pid));
        $rows = $profile['rows'] ?? null;
        $this->assertIsArray($rows);
        $this->assertIsArray($rows[0] ?? null);
        $this->assertIsNumeric($rows[0]['id'] ?? null);
        self::$patientId = (int) $rows[0]['id'];
    }

    /**
     * Deletes the documents the test created.
     */
    protected function tearDown(): void
    {
        foreach ($this->createdDocuments as $id) {
            $this->send('DELETE', '/portal/patient/api/onsitedocument/' . $id);
        }
    }

    #[Test]
    public function documentCreateReturnsTheSavedDocument(): void
    {
        $response = $this->send('POST', '/portal/patient/api/onsitedocument', $this->newDocument());
        $this->assertSame(200, $response->getStatusCode());
        $document = $this->json($response);
        $this->rememberDocument($document);

        $this->assertSame(self::DOCUMENT_KEYS, array_keys($document));
        $this->assertSame('Hipaa Document', $document['docType']);
        $this->assertSame('New', $document['denialReason']);
        $this->assertSame(self::TEMPLATE_ID, $this->text($document['filePath']));
        $this->assertIsString($document['fullDocument']);
        $this->assertStringContainsString("id='portal_version'", $document['fullDocument'], 'a "New" version document stores the template with the version marker');
        $this->assertSame('1970-01-01 00:00:00', $document['reviewDate'], 'an empty date is saved as the epoch');
    }

    #[Test]
    public function documentReadUpdateAndDelete(): void
    {
        $id = $this->createDocument();
        $path = '/portal/patient/api/onsitedocument/' . $id;

        $read = $this->json($this->get($path));
        $this->assertSame(self::DOCUMENT_KEYS, array_keys($read));
        $this->assertSame((string) $id, $this->text($read['id']));
        $this->assertSame((string) self::$pid, $this->text($read['pid']));

        $update = $this->send('PUT', $path, ['denialReason' => 'In Review', 'templateData' => '{}']);
        $this->assertSame(200, $update->getStatusCode());
        $updated = $this->json($update);
        $this->assertSame(self::DOCUMENT_KEYS, array_keys($updated));
        $this->assertSame('In Review', $updated['denialReason']);
        $this->assertSame('In Review', $this->json($this->get($path))['denialReason']);

        $delete = $this->send('DELETE', $path);
        $this->assertSame(200, $delete->getStatusCode());
        $this->assertSame([], $this->json($delete));
        $this->createdDocuments = [];

        $this->assertError($this->get($path));
    }

    #[Test]
    public function documentValidationErrorsNameTheField(): void
    {
        $id = $this->createDocument();

        $response = $this->send('PUT', '/portal/patient/api/onsitedocument/' . $id, ['facility' => 'abc']);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(
            [
                'success' => false,
                'message' => 'Please check the form for errors',
                'errors' => ['facility' => 'Facility is not a valid number'],
            ],
            $this->json($response),
        );
    }

    #[Test]
    public function documentUpdateRefusesAnUnflattenedDocumentBody(): void
    {
        $id = $this->createDocument();

        $this->assertError($this->send('PUT', '/portal/patient/api/onsitedocument/' . $id, ['fullDocument' => '<p>replaced</p>']));
        $this->assertStringNotContainsString(
            'replaced',
            $this->text($this->json($this->get('/portal/patient/api/onsitedocument/' . $id))['fullDocument']),
        );
    }

    #[Test]
    public function documentUpdatePurifiesAFlattenedDocument(): void
    {
        $id = $this->createDocument();

        $response = $this->send('PUT', '/portal/patient/api/onsitedocument/' . $id, [
            'type' => 'flattened',
            'fullDocument' => '<p>Answer</p><script>alert(1)</script>',
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $saved = $this->text($this->json($response)['fullDocument']);
        $this->assertStringContainsString('<p>Answer</p>', $saved);
        $this->assertStringNotContainsString('<script', $saved);
    }

    /**
     * The document list keeps its response shape and honours its filters.
     */
    #[Test]
    public function documentListShapesAndFilters(): void
    {
        $id = $this->createDocument();
        $base = '/portal/patient/api/onsitedocuments?patientId=' . self::$pid;

        $all = $this->json($this->get($base . '&showActive=true'));
        $this->assertSame(self::LIST_KEYS, array_keys($all));
        $this->assertSame('ReviewDate', $all['orderBy']);
        $this->assertContains((string) $id, $this->documentIds($all));
        $this->assertIsArray($all['rows']);
        foreach ($all['rows'] as $row) {
            $this->assertIsArray($row);
            $this->assertSame(self::DOCUMENT_KEYS, array_keys($row));
            $this->assertSame((string) self::$pid, $this->text($row['pid']));
        }

        $this->assertContains((string) $id, $this->documentIds($this->json($this->get($base))), 'a document that is not Locked is listed by default');
        $this->assertSame([(string) $id], $this->documentIds($this->json($this->get($base . '&recid=' . $id))));

        $page = $this->json($this->get($base . '&page=1'));
        $this->assertSame(self::LIST_KEYS, array_keys($page));
        $this->assertIsNumeric($page['currentPage']);
        $this->assertSame(1, (int) $page['currentPage'], 'currentPage echoes the requested page');
        $this->assertIsNumeric($page['pageSize']);
        $this->assertSame(25, (int) $page['pageSize']);
    }

    #[Test]
    public function documentListFilterMatchesText(): void
    {
        $id = $this->createDocument();
        $base = '/portal/patient/api/onsitedocuments?patientId=' . self::$pid . '&showActive=true&filter=';

        $this->assertContains((string) $id, $this->documentIds($this->json($this->get($base . 'Hipaa'))));
        $this->assertSame([], $this->documentIds($this->json($this->get($base . rawurlencode("zz' OR '1'='1")))));
    }

    /**
     * The document list needs a patient id and ignores a pid key in the request.
     */
    #[Test]
    public function documentListRequiresAPatientAndIgnoresAPidKey(): void
    {
        $this->createDocument();

        $this->assertError($this->get('/portal/patient/api/onsitedocuments'));
        $this->assertError($this->get('/portal/patient/api/onsitedocuments?patientId=abc'));

        $other = self::$pid === 1 ? 2 : 1;
        $list = $this->json($this->get('/portal/patient/api/onsitedocuments?patientId=' . self::$pid . '&pid=' . $other . '&showActive=true'));
        $this->assertIsArray($list['rows']);
        $this->assertNotEmpty($list['rows']);
        foreach ($list['rows'] as $row) {
            $this->assertIsArray($row);
            $this->assertSame((string) self::$pid, $this->text($row['pid']), 'a pid request key must not replace the patient');
        }
    }

    #[Test]
    public function patientEndpointsKeepTheirFieldSets(): void
    {
        $profile = $this->json($this->get('/portal/patient/api/patientdata?patientId=' . self::$pid));
        $this->assertSame(self::LIST_KEYS, array_keys($profile));
        $this->assertIsArray($profile['rows']);
        $this->assertCount(1, $profile['rows']);
        $this->assertIsArray($profile['rows'][0]);
        $this->assertSame(self::PROFILE_KEYS, array_keys($profile['rows'][0]));
        $this->assertSame('PortalContract', $profile['rows'][0]['fname']);

        $patient = $this->json($this->get('/portal/patient/api/patient/' . self::$patientId));
        $this->assertSame(self::PATIENT_KEYS, array_keys($patient));
        $this->assertSame((string) self::$pid, $this->text($patient['pid']));

        $this->assertError($this->get('/portal/patient/api/patient/999999999'));

        $pending = $this->get('/portal/patient/api/portalpatient/' . self::$patientId);
        $this->assertSame(200, $pending->getStatusCode());
        $this->assertSame([], $this->json($pending), 'no pending profile change for a new patient');
    }

    /**
     * A profile change waits for review, leaving the chart alone, then reaches the chart once accepted.
     */
    #[Test]
    public function profileChangeIsReviewedThenCommittedToTheChart(): void
    {
        $record = '/portal/patient/api/patient/' . self::$patientId;
        $review = '/portal/patient/api/portalpatient/' . self::$patientId;
        $phone = '555-' . random_int(1000, 9999);

        // Sending a change for review returns the changed record but leaves the chart alone.
        $sent = $this->send('PUT', $review, ['phoneHome' => $phone, 'note' => 'new number'] + self::PROVIDER_FIELDS);
        $this->assertSame(200, $sent->getStatusCode());
        $changed = $this->json($sent);
        $this->assertSame(self::PATIENT_KEYS, array_keys($changed));
        $this->assertSame($phone, $changed['phoneHome']);
        $this->assertSame('new number', $changed['note']);
        $this->assertNotSame($phone, $this->json($this->get($record))['phoneHome']);

        $pending = $this->json($this->get($review));
        $this->assertSame($phone, $pending['phoneHome'] ?? null, 'staff see the pending change, with camelCase keys');
        $this->assertSame('new number', $pending['note'] ?? null);
        $this->assertSame((string) self::$pid, $this->text($pending['pid'] ?? null));

        // Commit to Chart saves it and closes the review.
        $committed = $this->send('PUT', $record, ['phoneHome' => $phone] + self::PROVIDER_FIELDS);
        $this->assertSame(200, $committed->getStatusCode());
        $saved = $this->json($committed);
        $this->assertSame(self::PATIENT_KEYS, array_keys($saved));
        $this->assertSame($phone, $saved['phoneHome']);
        $this->assertSame($phone, $this->json($this->get($record))['phoneHome']);
        $this->assertSame([], $this->json($this->get($review)), 'the committed change is no longer pending');
    }

    /**
     * A rejected profile change names the field that failed.
     */
    #[Test]
    public function profileValidationErrorsNameTheField(): void
    {
        $response = $this->send('PUT', '/portal/patient/api/patient/' . self::$patientId, ['providerid' => 'abc'] + self::PROVIDER_FIELDS);

        $this->assertSame(401, $response->getStatusCode());
        $body = $this->json($response);
        $this->assertSame('Please check the form for errors', $body['message'] ?? null);
        $this->assertSame(['providerid' => 'Providerid is not a valid number'], $body['errors'] ?? null);
    }

    /**
     * The portal pages render with the CSRF token, and unknown routes are refused.
     */
    #[Test]
    public function pagesRenderAndUnknownRoutesAreRefused(): void
    {
        // page => whether it calls the API, and so needs the CSRF token. The dashboard does not.
        foreach (
            [
                '/portal/patient/provider' => false,
                '/portal/patient/patientdata?pid=' . self::$pid . '&user=1' => true,
                '/portal/patient/onsitedocuments?pid=' . self::$pid . '&user=1' => true,
                '/portal/patient/onsiteactivityviews' => true,
            ] as $page => $callsTheApi
        ) {
            $response = $this->get($page);
            $this->assertSame(200, $response->getStatusCode(), $page);
            $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'), $page);
            if ($callsTheApi) {
                $this->assertStringContainsString('window.portalApiCsrfToken', (string) $response->getBody(), $page);
            } else {
                $this->assertStringContainsString('Portal Dashboard', (string) $response->getBody(), $page);
            }
        }

        $this->assertSame(200, $this->get('/portal/patient/no-such-page')->getStatusCode(), 'an unknown page gets the not-found page');
        $this->assertSame(401, $this->get('/portal/patient/api/no-such-endpoint')->getStatusCode());
    }

    #[Test]
    public function writesWithoutTheCsrfTokenAreRefused(): void
    {
        $id = $this->createDocument();

        $response = $this->http()->request('DELETE', self::$baseUrl . '/portal/patient/api/onsitedocument/' . $id);
        $this->assertSame(403, $response->getStatusCode(), 'refused, not an authentication failure');
        $this->assertSame(200, $this->get('/portal/patient/api/onsitedocument/' . $id)->getStatusCode(), 'the document is still there');
    }

    private function createPatient(): int
    {
        $form = (string) $this->get('/interface/new/new_comprehensive.php')->getBody();
        $this->assertSame(1, preg_match('/name="csrf_token_form" value="([^"]+)"/', $form, $token), 'the new-patient form prints a CSRF token');

        $saved = (string) $this->http()->post(self::$baseUrl . '/interface/new/new_comprehensive_save.php', [
            'allow_redirects' => false,
            'form_params' => [
                'csrf_token_form' => $token[1],
                'form_fname' => 'PortalContract',
                'form_lname' => 'Test' . bin2hex(random_bytes(3)),
                'form_DOB' => '1980-01-02',
                'form_sex' => 'Female',
            ],
        ])->getBody();
        $this->assertSame(1, preg_match('/set_pid=(\d+)/', $saved, $pid), 'the new-patient save names the new pid');

        return (int) $pid[1];
    }

    private function apiToken(int $pid): string
    {
        $page = (string) $this->get('/portal/patient/onsitedocuments?pid=' . $pid . '&user=1')->getBody();
        $this->assertSame(1, preg_match('/window\.portalApiCsrfToken = "([0-9a-f]+)"/', $page, $token), 'the documents page prints the API CSRF token');

        return $token[1];
    }

    private function createDocument(): int
    {
        $document = $this->json($this->send('POST', '/portal/patient/api/onsitedocument', $this->newDocument()));

        return $this->rememberDocument($document);
    }

    /**
     * @param array<mixed> $document
     */
    private function rememberDocument(array $document): int
    {
        $this->assertIsNumeric($document['id'] ?? null);
        $id = (int) $document['id'];
        $this->createdDocuments[] = $id;

        return $id;
    }

    /**
     * @return array<string, mixed>
     */
    private function newDocument(): array
    {
        return [
            'pid' => self::$pid,
            'facility' => 0,
            'provider' => 1,
            'encounter' => 0,
            'createDate' => '2026-11-04 10:00:00',
            'docType' => 'Hipaa Document',
            'patientSignedStatus' => 0,
            'patientSignedTime' => '',
            'authorizeSignedTime' => '',
            'acceptSignedStatus' => 0,
            'authorizingSignator' => '',
            'reviewDate' => '',
            'denialReason' => 'New',
            'authorizedSignature' => '',
            'patientSignature' => '',
            'fileName' => 'Hipaa Document',
            'filePath' => self::TEMPLATE_ID,
            'templateData' => '',
            'version' => 'New',
        ];
    }

    /**
     * @param array<mixed> $list
     * @return list<string>
     */
    private function documentIds(array $list): array
    {
        $this->assertIsArray($list['rows'] ?? null);
        $ids = [];
        foreach ($list['rows'] as $row) {
            $this->assertIsArray($row);
            $ids[] = $this->text($row['id']);
        }

        return $ids;
    }

    /**
     * The response is a portal API error: 401 with success false and a message.
     */
    private function assertError(ResponseInterface $response): void
    {
        $this->assertSame(401, $response->getStatusCode());
        $body = $this->json($response);
        $this->assertFalse($body['success'] ?? null);
        $this->assertIsString($body['message'] ?? null);
    }

    private function get(string $path): ResponseInterface
    {
        return $this->http()->get(self::$baseUrl . $path);
    }

    /**
     * @param array<string, mixed>|null $body
     */
    private function send(string $method, string $path, ?array $body = null): ResponseInterface
    {
        $options = ['headers' => ['X-CSRF-Token' => self::$apiToken]];
        if ($body !== null) {
            $options['json'] = $body;
        }

        return $this->http()->request($method, self::$baseUrl . $path, $options);
    }

    /**
     * @return array<mixed>
     */
    private function json(ResponseInterface $response): array
    {
        $decoded = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($decoded, 'the response is a JSON object or array: ' . substr((string) $response->getBody(), 0, 300));

        return $decoded;
    }

    /**
     * A JSON scalar as a string: the API returns database values as strings and request
     * values unchanged, so ids may arrive as either.
     */
    private function text(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }
        $this->assertIsString($value);

        return $value;
    }

    private function http(): Client
    {
        $this->assertInstanceOf(Client::class, self::$http);

        return self::$http;
    }
}
