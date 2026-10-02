<?php

/**
 * DocumentControllerWriteAclTest verifies that C_Document's write path enforces
 * the same anti-IDOR checks as its read path: a caller may only mutate a
 * document they can already access in its current patient/category context.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Security
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use C_Document;
use Document;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DocumentControllerWriteAclTest extends TestCase
{
    /** Arbitrary patient id the fixture document belongs to. */
    private const DOC_PATIENT_ID = 987654;

    private int $documentId = 0;

    protected function setUp(): void
    {
        // C_Document is a legacy (non-autoloaded) controller; the suite
        // bootstrap has already loaded interface/globals.php, so its
        // procedural dependencies resolve here.
        require_once __DIR__ . '/../../../controllers/C_Document.class.php';

        $GLOBALS['document_storage_method'] = Document::STORAGE_METHOD_FILESYSTEM;
        $systemUser = (new UserService())->getSystemUser();
        self::assertIsArray($systemUser, 'system user must be resolvable for the fixture');
        $owner = (int) $systemUser['id'];

        // A document tied to DOC_PATIENT_ID with no category. An empty category
        // set makes Document::can_access() grant access, which isolates the
        // patient-context check that these tests exercise from category ACL.
        $data = 'write-acl-test';
        $document = new Document();
        $document->createDocument(
            (string) self::DOC_PATIENT_ID,
            0,
            'write-acl-test-' . uniqid() . '.txt',
            'text/plain',
            $data,
            '',
            1,
            $owner
        );
        $rawDocumentId = $document->get_id();
        self::assertTrue(is_numeric($rawDocumentId), 'fixture document should expose a numeric id');
        $this->documentId = (int) $rawDocumentId;
        self::assertNotSame(0, $this->documentId, 'fixture document should have been created');
    }

    protected function tearDown(): void
    {
        if ($this->documentId !== 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM categories_to_documents WHERE document_id = ?', [$this->documentId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM documents WHERE id = ?', [$this->documentId]);
        }
    }

    /**
     * Invoke the private write-authorization guard with the given patient context.
     */
    private function authorizeWrite(?string $patientContext): Document
    {
        $controller = new C_Document();
        $guard = new ReflectionMethod(C_Document::class, 'authorizeDocumentWrite');

        $result = $guard->invoke($controller, $patientContext, $this->documentId);
        self::assertInstanceOf(Document::class, $result);
        return $result;
    }

    public function testWriteAllowedWhenPatientContextMatchesDocument(): void
    {
        $document = $this->authorizeWrite((string) self::DOC_PATIENT_ID);
        $documentId = $document->get_id();
        self::assertTrue(is_numeric($documentId));
        self::assertSame($this->documentId, (int) $documentId);
    }

    public function testWriteDeniedWhenPatientContextMismatchesDocument(): void
    {
        // A caller operating in a different patient context must not be able to
        // mutate (e.g. reassign) a document belonging to another patient.
        $this->expectException(AccessDeniedHttpException::class);
        $this->authorizeWrite((string) (self::DOC_PATIENT_ID + 1));
    }

    public function testWriteDeniedWhenPatientContextIsNull(): void
    {
        // Regression for the null-patient_id bypass: an empty "patient_id="
        // request parameter is normalised to null by Controller::dispatch().
        // The guard must fail closed on a null patient context instead of
        // skipping the patient-ownership check and relying on the category ACL
        // alone, which passes for any category the caller can already reach.
        $this->expectException(AccessDeniedHttpException::class);
        $this->authorizeWrite(null);
    }
}
