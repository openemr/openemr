<?php

/**
 * DocumentViewDeleteLinkTest verifies that the document view page sends the
 * patient context that interface/patient_file/deleter.php checks before it
 * deletes a document.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use C_Document;
use Document;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;

final class DocumentViewDeleteLinkTest extends TestCase
{
    /**
     * Arbitrary patient id the fixture document belongs to.
     */
    private const DOC_PATIENT_ID = 987655;

    private int $documentId = 0;

    /**
     * Session values this test sets, with what they held before.
     *
     * @var array<string, mixed>
     */
    private array $previousSession = [];

    private ?string $previousRequestUri = null;

    private bool $hadDocumentStorageMethod = false;

    private mixed $previousDocumentStorageMethod = null;

    /**
     * Filesystem path of the fixture document, removed in tearDown().
     */
    private string $documentFile = '';

    protected function setUp(): void
    {
        // C_Document is a legacy (non-autoloaded) controller; the suite
        // bootstrap has already loaded interface/globals.php.
        require_once __DIR__ . '/../../../controllers/C_Document.class.php';

        // The view page renders a CSRF token and checks the documents ACL as the session user.
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach (['authUser' => 'admin', 'csrf_private_key' => 'document-view-delete-link-test'] as $key => $value) {
            $this->previousSession[$key] = $session->get($key);
            $session->set($key, $value);
        }

        $this->hadDocumentStorageMethod = array_key_exists('document_storage_method', $GLOBALS);
        $this->previousDocumentStorageMethod = $GLOBALS['document_storage_method'] ?? null;
        $GLOBALS['document_storage_method'] = Document::STORAGE_METHOD_FILESYSTEM;
        $systemUser = (new UserService())->getSystemUser();
        $this->assertIsArray($systemUser);

        $data = 'view-delete-link-test';
        $document = new Document();
        $document->createDocument(
            (string) self::DOC_PATIENT_ID,
            0,
            'view-delete-link-test-' . uniqid() . '.txt',
            'text/plain',
            $data,
            '',
            1,
            (int) $systemUser['id']
        );
        $documentId = $document->get_id();
        $this->assertTrue(is_numeric($documentId));
        $this->documentId = (int) $documentId;
        $documentFile = $document->get_url_filepath();
        $this->assertIsString($documentFile);
        $this->documentFile = $documentFile;

        // Controller::_link() builds the page's action links from the request URI.
        $requestUri = $_SERVER['REQUEST_URI'] ?? null;
        $this->previousRequestUri = is_string($requestUri) ? $requestUri : null;
        $_SERVER['REQUEST_URI'] = '/controller.php?document&view&patient_id=' . self::DOC_PATIENT_ID . '&doc_id=' . $this->documentId;
    }

    protected function tearDown(): void
    {
        if ($this->documentId !== 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM categories_to_documents WHERE document_id = ?', [$this->documentId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM documents WHERE id = ?', [$this->documentId]);
        }

        if ($this->documentFile !== '' && is_file($this->documentFile)) {
            unlink($this->documentFile);
            // createDocument() made a directory for the fixture patient; drop it once empty.
            $directory = dirname($this->documentFile);
            if (scandir($directory) === ['.', '..']) {
                rmdir($directory);
            }
        }

        if ($this->hadDocumentStorageMethod) {
            $GLOBALS['document_storage_method'] = $this->previousDocumentStorageMethod;
        } else {
            unset($GLOBALS['document_storage_method']);
        }

        if ($this->previousRequestUri === null) {
            unset($_SERVER['REQUEST_URI']);
        } else {
            $_SERVER['REQUEST_URI'] = $this->previousRequestUri;
        }

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach ($this->previousSession as $key => $value) {
            $session->set($key, $value);
        }
    }

    public function testDeleteLinkCarriesThePatientTheDocumentIsFiledUnder(): void
    {
        $html = (new C_Document())->view_action((string) self::DOC_PATIENT_ID, $this->documentId);
        $this->assertIsString($html);

        // deleter.php denies the delete unless document_pid matches documents.foreign_id.
        $this->assertStringContainsString(
            "deleter.php?document=' + encodeURIComponent(docid) + '&document_pid=' + encodeURIComponent(\"" . self::DOC_PATIENT_ID . "\")",
            $html
        );
        // In deleter.php a `patient` parameter selects the patient itself for deletion.
        $this->assertStringNotContainsString('deleter.php?patient=', $html);
        $this->assertStringContainsString("onclick='return deleteme(", $html);
    }

    public function testNoDeleteLinkWhenThePageHasNoPatient(): void
    {
        $html = (new C_Document())->view_action(null, $this->documentId);
        $this->assertIsString($html);

        // document_pid 0 never matches a document filed under a patient, so deleter.php would refuse the delete.
        $this->assertStringContainsString("&document_pid=' + encodeURIComponent(\"0\")", $html);
        $this->assertStringNotContainsString("onclick='return deleteme(", $html);
    }
}
