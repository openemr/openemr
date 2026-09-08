<?php

/**
 * Patient Document API Endpoint Test Cases.
 *
 * Covers the standard (non-FHIR) patient document endpoints:
 * - POST /api/patient/:pid/document
 * - GET  /api/patient/:pid/document
 * - GET  /api/patient/:pid/document/:did
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class DocumentApiTest extends TestCase
{
    /**
     * A category path is a slash delimited list of `categories`.`name` values where spaces in the
     * name are represented by underscores. `/Categories/Medical_Record` is installed by default.
     */
    private const CATEGORY_PATH = "/Categories/Medical_Record";
    private const INVALID_CATEGORY_PATH = "/Categories/No_Such_Category_Exists";

    private const FILE_CONTENTS = "OpenEMR patient document API test payload.\n";
    private const FILE_NAME = "document-api-test.txt";

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private int $pid;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $patientFixture = $this->fixtureManager->getSinglePatientFixture();
        $this->fixtureManager->installSinglePatientFixture($patientFixture);

        $patient = $this->fetchRow(
            "SELECT `pid` FROM `patient_data` WHERE `pubpid` = ?",
            [$patientFixture['pubpid']]
        );
        if ($patient === null) {
            self::fail("Patient fixture should have been installed");
        }
        $this->pid = $this->intColumn($patient, 'pid');
    }

    /**
     * PHPUnit runs tearDown() even when setUp() throws part way through, so each cleanup is
     * guarded on the property it needs. Touching an unassigned typed property would raise an
     * Error that masks the original setUp() failure and skips the cleanup that could still run.
     */
    protected function tearDown(): void
    {
        if (isset($this->pid)) {
            $this->removeDocumentsForFixturePatient();
        }
        if (isset($this->fixtureManager)) {
            $this->fixtureManager->removePatientFixtures();
        }
        if (isset($this->testClient)) {
            $this->testClient->cleanupRevokeAuth();
            $this->testClient->cleanupClient();
        }
    }

    #[Test]
    public function testPostDocument(): void
    {
        $response = $this->postDocument();

        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "Uploading a patient document should succeed: " . $response->getBody()
        );
        $this->assertTrue(
            json_decode((string)$response->getBody(), true),
            "The upload endpoint responds with a bare boolean rather than the created document"
        );
    }

    #[Test]
    public function testPostDocumentPersistsFileAgainstThePatientAndCategory(): void
    {
        $this->assertEquals(200, $this->postDocument()->getStatusCode());

        $document = $this->fetchRow(
            "SELECT d.`id`, d.`name`, d.`mimetype`, d.`deleted`, ctd.`category_id`
             FROM `documents` d
             JOIN `categories_to_documents` ctd ON ctd.`document_id` = d.`id`
             WHERE d.`foreign_id` = ?",
            [$this->pid]
        );

        if ($document === null) {
            self::fail("The uploaded document should be persisted for the patient");
        }
        $this->assertEquals(self::FILE_NAME, $this->stringColumn($document, 'name'));
        $this->assertEquals("text/plain", $this->stringColumn($document, 'mimetype'));
        $this->assertEquals(0, $this->intColumn($document, 'deleted'));

        $category = $this->fetchRow(
            "SELECT `name` FROM `categories` WHERE `id` = ?",
            [$this->intColumn($document, 'category_id')]
        );
        if ($category === null) {
            self::fail("The document should be filed under an existing category");
        }
        $this->assertEquals(
            "Medical Record",
            $this->stringColumn($category, 'name'),
            "The document should be filed under the category named in the path query parameter"
        );
    }

    #[Test]
    public function testPostDocumentWithEncounterId(): void
    {
        $encounterId = $this->createEncounter();

        $response = $this->postDocument(['path' => self::CATEGORY_PATH, 'eid' => (string)$encounterId]);
        $this->assertEquals(200, $response->getStatusCode(), (string)$response->getBody());

        $document = $this->fetchRow(
            "SELECT `encounter_id` FROM `documents` WHERE `foreign_id` = ?",
            [$this->pid]
        );
        if ($document === null) {
            self::fail("The uploaded document should be persisted for the patient");
        }
        $this->assertEquals(
            $encounterId,
            $this->intColumn($document, 'encounter_id'),
            "The eid query parameter should tag the document with the encounter"
        );
    }

    #[Test]
    public function testPostDocumentWithInvalidCategoryPath(): void
    {
        $response = $this->postDocument(['path' => self::INVALID_CATEGORY_PATH]);

        $this->assertEquals(
            404,
            $response->getStatusCode(),
            "Uploading to a category that does not exist should be rejected"
        );
        $this->assertNull(
            $this->fetchRow("SELECT `id` FROM `documents` WHERE `foreign_id` = ?", [$this->pid]),
            "No document should be stored when the category path is invalid"
        );
    }

    #[Test]
    public function testPostDocumentWithUnknownPid(): void
    {
        $response = $this->testClient->postMultipart(
            "/apis/default/api/patient/" . $this->unknownPid() . "/document",
            [['name' => 'document', 'contents' => self::FILE_CONTENTS, 'filename' => self::FILE_NAME]],
            ['path' => self::CATEGORY_PATH]
        );

        $this->assertEquals(
            400,
            $response->getStatusCode(),
            "Uploading against a pid with no patient should be a bad request"
        );
        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('validationErrors', $body);
        $validationErrors = $body['validationErrors'];
        $this->assertIsArray($validationErrors);
        $this->assertArrayHasKey('pid', $validationErrors);
    }

    #[Test]
    public function testPostDocumentWithoutAFile(): void
    {
        $response = $this->testClient->postMultipart(
            $this->documentEndpoint(),
            [['name' => 'not_the_document_field', 'contents' => self::FILE_CONTENTS]],
            ['path' => self::CATEGORY_PATH]
        );

        $this->assertEquals(
            400,
            $response->getStatusCode(),
            "A request that carries no document file should be a bad request"
        );
        $this->assertNull(
            $this->fetchRow("SELECT `id` FROM `documents` WHERE `foreign_id` = ?", [$this->pid]),
            "No document should be stored when the request carried no file"
        );
    }

    /**
     * A MAX_FILE_SIZE field ahead of the file makes PHP reject the upload with
     * UPLOAD_ERR_FORM_SIZE. This is the shape a failed upload actually takes: $_FILES still
     * carries the original `name`, and only `tmp_name` comes back empty, so checking that both
     * keys hold strings is not enough to catch it -- the `error` code has to be read.
     */
    #[Test]
    public function testPostDocumentWithAFailedUpload(): void
    {
        $response = $this->testClient->postMultipart(
            $this->documentEndpoint(),
            [
                ['name' => 'MAX_FILE_SIZE', 'contents' => '4'],
                ['name' => 'document', 'contents' => self::FILE_CONTENTS, 'filename' => self::FILE_NAME],
            ],
            ['path' => self::CATEGORY_PATH]
        );

        $this->assertEquals(
            400,
            $response->getStatusCode(),
            "An upload PHP reported an error for should be a bad request"
        );
        $this->assertNull(
            $this->fetchRow("SELECT `id` FROM `documents` WHERE `foreign_id` = ?", [$this->pid]),
            "No document should be stored when the upload failed"
        );
    }

    #[Test]
    public function testPostDocumentWithoutAuthorization(): void
    {
        $this->testClient->removeAuthToken();
        $response = $this->postDocument();

        $this->assertEquals(401, $response->getStatusCode());
    }

    #[Test]
    public function testGetAllAtPath(): void
    {
        $this->assertEquals(200, $this->postDocument()->getStatusCode());

        $response = $this->testClient->get($this->documentEndpoint(), ['path' => self::CATEGORY_PATH]);
        $this->assertEquals(200, $response->getStatusCode(), (string)$response->getBody());

        $documents = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($documents);
        $this->assertCount(1, $documents);

        $document = $documents[0];
        $this->assertIsArray($document);
        $this->assertEquals(self::FILE_NAME, $document['filename']);
        $this->assertEquals("text/plain", $document['mimetype']);
        $this->assertNotEmpty($document['id']);
        $this->assertNotEmpty($document['hash']);
    }

    #[Test]
    public function testGetAllAtPathWithInvalidCategoryPath(): void
    {
        $response = $this->testClient->get($this->documentEndpoint(), ['path' => self::INVALID_CATEGORY_PATH]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function testGetAllAtPathWithUnknownPid(): void
    {
        $response = $this->testClient->get(
            "/apis/default/api/patient/" . $this->unknownPid() . "/document",
            ['path' => self::CATEGORY_PATH]
        );

        $this->assertEquals(400, $response->getStatusCode());
    }

    #[Test]
    public function testGetAllAtPathWithoutAuthorization(): void
    {
        $this->testClient->removeAuthToken();
        $response = $this->testClient->get($this->documentEndpoint(), ['path' => self::CATEGORY_PATH]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Pre-existing bug: `GET /api/patient/:pid/document/:did` is unusable over the REST API.
     *
     * DocumentService::getFile() constructs C_Document, whose constructor calls
     * CsrfUtils::collectCsrfToken() against the active session. OAuth2AuthorizationListener only
     * seeds `csrf_private_key` for `/oauth2/...` requests (its shouldProcessRequest() matches on a
     * `/oauth2` base path), and AuthorizationController strips the key from the API session, so a
     * bearer token request to `/apis/...` has no key. The constructor therefore throws and the
     * request fails with a 500 before the document is ever read.
     *
     * This is not reachable through the UI download path (controller.php), which runs under a
     * fully bootstrapped browser session. Once the API session carries a CSRF key, this endpoint
     * serves the file correctly, so when that is fixed this test should assert a 200 with the
     * uploaded bytes and a `Content-Disposition` attachment header carrying the stored filename.
     */
    #[Test]
    public function testDownloadFileFailsBecauseTheApiSessionHasNoCsrfKey(): void
    {
        $this->assertEquals(200, $this->postDocument()->getStatusCode());
        $documentId = $this->getUploadedDocumentId();

        $response = $this->testClient->getOne($this->documentEndpoint(), (string)$documentId);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertNotEquals(
            self::FILE_CONTENTS,
            (string)$response->getBody(),
            "The endpoint fails before it reads the document"
        );
    }

    #[Test]
    public function testDownloadFileWithUnknownPid(): void
    {
        $this->assertEquals(200, $this->postDocument()->getStatusCode());
        $documentId = $this->getUploadedDocumentId();

        $response = $this->testClient->getOne(
            "/apis/default/api/patient/" . $this->unknownPid() . "/document",
            (string)$documentId
        );

        $this->assertEquals(
            400,
            $response->getStatusCode(),
            "A document may not be retrieved through a pid that has no patient"
        );
        $this->assertNotEquals(self::FILE_CONTENTS, (string)$response->getBody());
    }

    #[Test]
    public function testDownloadFileWithoutAuthorization(): void
    {
        $this->assertEquals(200, $this->postDocument()->getStatusCode());
        $documentId = $this->getUploadedDocumentId();

        $this->testClient->removeAuthToken();
        $response = $this->testClient->getOne($this->documentEndpoint(), (string)$documentId);

        $this->assertEquals(401, $response->getStatusCode());
    }

    private function documentEndpoint(): string
    {
        return "/apis/default/api/patient/" . $this->pid . "/document";
    }

    /**
     * A pid that is well formed but has no patient behind it. Derived from the highest stored pid
     * so that it cannot collide with a patient in a heavily seeded database.
     */
    private function unknownPid(): int
    {
        $row = $this->fetchRow("SELECT MAX(`pid`) AS `max_pid` FROM `patient_data`", []);
        $maxPid = $row === null ? null : ($row['max_pid'] ?? null);

        return (is_numeric($maxPid) ? (int)$maxPid : $this->pid) + 1;
    }

    /**
     * @param array<string, string>|null $query
     */
    private function postDocument(?array $query = null): ResponseInterface
    {
        return $this->testClient->postMultipart(
            $this->documentEndpoint(),
            [
                [
                    'name' => 'document',
                    'contents' => self::FILE_CONTENTS,
                    'filename' => self::FILE_NAME,
                    'headers' => ['Content-Type' => 'text/plain'],
                ],
            ],
            $query ?? ['path' => self::CATEGORY_PATH]
        );
    }

    private function getUploadedDocumentId(): int
    {
        $document = $this->fetchRow("SELECT `id` FROM `documents` WHERE `foreign_id` = ?", [$this->pid]);
        if ($document === null) {
            self::fail("Expected an uploaded document for the fixture patient");
        }

        return $this->intColumn($document, 'id');
    }

    private function createEncounter(): int
    {
        $encounterId = QueryUtils::sqlInsert(
            "INSERT INTO `form_encounter` (`date`, `encounter`, `pid`, `reason`) VALUES (NOW(), ?, ?, ?)",
            [1, $this->pid, 'document-api-test']
        );
        $this->assertGreaterThan(0, $encounterId, "Expected an encounter to be created for the fixture patient");

        return $encounterId;
    }

    /**
     * Removes any documents created against the fixture patient, including the files written to disk.
     */
    private function removeDocumentsForFixturePatient(): void
    {
        $documents = QueryUtils::fetchRecords(
            "SELECT `id`, `url` FROM `documents` WHERE `foreign_id` = ?",
            [$this->pid]
        );
        foreach ($documents as $document) {
            $url = $document['url'] ?? null;
            $filePath = is_string($url) ? preg_replace("|^file://|", "", $url) : null;
            if ($filePath !== null && $filePath !== '' && is_file($filePath)) {
                unlink($filePath);
            }
            $documentId = $this->intColumn($document, 'id');
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `categories_to_documents` WHERE `document_id` = ?",
                [$documentId]
            );
            QueryUtils::sqlStatementThrowException("DELETE FROM `documents` WHERE `id` = ?", [$documentId]);
        }

        QueryUtils::sqlStatementThrowException("DELETE FROM `form_encounter` WHERE `pid` = ?", [$this->pid]);
    }

    /**
     * Fetches the first matching row, or null when the query matched nothing.
     *
     * @param list<mixed> $binds
     * @return array<array-key, mixed>|null
     */
    private function fetchRow(string $sql, array $binds): ?array
    {
        $records = QueryUtils::fetchRecords($sql, $binds);

        return $records[0] ?? null;
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function stringColumn(array $row, string $column): string
    {
        $value = $row[$column] ?? null;
        if (!is_string($value)) {
            self::fail(sprintf("Expected column `%s` to be a string, got %s", $column, get_debug_type($value)));
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function intColumn(array $row, string $column): int
    {
        $value = $row[$column] ?? null;
        if (!is_numeric($value)) {
            self::fail(sprintf("Expected column `%s` to be numeric, got %s", $column, get_debug_type($value)));
        }

        return (int)$value;
    }
}
