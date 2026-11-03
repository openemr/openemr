<?php

/**
 * CoreFormToPortalUtility::questionnaireDocumentForm() against the database: a questionnaire form
 * is a document's only when a document of the form's own patient saved a questionnaire frame.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Forms;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use PHPUnit\Framework\TestCase;

class CoreFormToPortalUtilityPortalDocumentFormTest extends TestCase
{
    /** Patient ids and a form id no fixture uses. */
    private const PID = 990081;
    private const OTHER_PID = 990082;
    private const FORM_ID = 990083;
    private const MARKER = 'portal-form-test';

    /** The frame src a questionnaire document saves (the form id is bound by onsite_documents.encounter). */
    private const QUESTIONNAIRE_FRAME = '/openemr/interface/forms/questionnaire_assessments/questionnaire_assessments.php'
        . '?formname=PHQ-9&formOrigin=0&id=0&isPortal=1';

    private const LBF_FRAME = '/openemr/interface/forms/LBF/new.php?formname=LBFtest&formOrigin=0&id=0&isPortal=1';

    protected function setUp(): void
    {
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testTheFormOfThePatientsOwnQuestionnaireDocumentHasThatPatientAndItsEncounter(): void
    {
        $this->insertForm(self::PID, encounter: 77);
        $this->insertDocument(self::PID, self::QUESTIONNAIRE_FRAME);

        $form = CoreFormToPortalUtility::questionnaireDocumentForm(self::FORM_ID);
        $this->assertNotNull($form);
        $this->assertSame([self::PID, 77, false], [$form->pid, $form->encounter, $form->locked]);
    }

    public function testAFormHeldByALockedDocumentIsReportedLocked(): void
    {
        $this->insertForm(self::PID);
        $this->insertDocument(self::PID, self::QUESTIONNAIRE_FRAME);
        $this->insertDocument(self::PID, self::QUESTIONNAIRE_FRAME, denialReason: 'Locked');

        $this->assertTrue(CoreFormToPortalUtility::questionnaireDocumentForm(self::FORM_ID)?->locked);
    }

    public function testAFormWithNoDocumentHasNone(): void
    {
        $this->insertForm(self::PID);

        $this->assertNull(CoreFormToPortalUtility::questionnaireDocumentForm(self::FORM_ID));
    }

    public function testAFormHeldByAnotherPatientsDocumentHasNone(): void
    {
        $this->insertForm(self::PID);
        $this->insertDocument(self::OTHER_PID, self::QUESTIONNAIRE_FRAME);

        $this->assertNull(CoreFormToPortalUtility::questionnaireDocumentForm(self::FORM_ID));
    }

    public function testTheSameIdHeldByADocumentEmbeddingAnotherFormHasNone(): void
    {
        // An LBF document's form id can equal a questionnaire's; each form table numbers its own rows.
        $this->insertForm(self::PID);
        $this->insertDocument(self::PID, self::LBF_FRAME);

        $this->assertNull(CoreFormToPortalUtility::questionnaireDocumentForm(self::FORM_ID));
    }

    public function testADocumentThatSavedNoFrameHasNone(): void
    {
        $this->insertForm(self::PID);
        $this->insertDocument(self::PID, null);

        $this->assertNull(CoreFormToPortalUtility::questionnaireDocumentForm(self::FORM_ID));
    }

    public function testTheDocumentsOwnFrameDecidesWhateverItsTemplateNowSays(): void
    {
        // The document's template is edited after the document was saved; what it saved stands.
        $this->insertForm(self::PID);
        $this->insertDocument(self::PID, self::QUESTIONNAIRE_FRAME, filePath: $this->insertTemplate('{EncounterDocument:LBFtest}'));

        $this->assertNotNull(CoreFormToPortalUtility::questionnaireDocumentForm(self::FORM_ID));
    }

    public function testADeletedFormHasNone(): void
    {
        $this->insertForm(self::PID, deleted: 1);
        $this->insertDocument(self::PID, self::QUESTIONNAIRE_FRAME);

        $this->assertNull(CoreFormToPortalUtility::questionnaireDocumentForm(self::FORM_ID));
    }

    private function insertForm(int $pid, int $deleted = 0, int $encounter = 0): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`,'
            . " `authorized`, `deleted`, `formdir`, `issue_id`, `provider_id`)"
            . " VALUES (NOW(), ?, ?, ?, ?, '', '', 0, ?, 'questionnaire_assessments', 0, 0)",
            [$encounter, self::MARKER, self::FORM_ID, $pid, $deleted]
        );
    }

    private function insertTemplate(string $content): string
    {
        return (string) QueryUtils::sqlInsert(
            'INSERT INTO `document_templates` (`pid`, `profile`, `category`, `template_name`, `status`,'
            . " `send_date`, `size`, `mime`, `template_content`) VALUES (-1, ?, 'Test', ?, 'New', NOW(), ?, 'text/plain', ?)",
            [self::MARKER, self::MARKER . '-' . md5($content), strlen($content), $content]
        );
    }

    /**
     * @param ?string $frameSrc The encounterForm src the document saved; null when it saved none.
     */
    private function insertDocument(int $pid, ?string $frameSrc, string $filePath = '', string $denialReason = ''): void
    {
        $elements = [['name' => 'patient_signature0', 'value' => '']];
        if ($frameSrc !== null) {
            $elements[] = ['name' => 'encounterForm', 'value' => $frameSrc];
        }
        $elements[] = ['name' => 'encounterFormId', 'value' => self::FORM_ID];
        QueryUtils::sqlInsert(
            'INSERT INTO `onsite_documents` (`pid`, `encounter`, `doc_type`, `patient_signed_status`, `accept_signed_status`,'
            . " `authorizing_signator`, `denial_reason`, `file_name`, `file_path`, `template_data`)"
            . " VALUES (?, ?, 'Consent', 0, 0, '', ?, ?, ?, ?)",
            [$pid, self::FORM_ID, $denialReason, self::MARKER, $filePath, json_encode($elements, JSON_THROW_ON_ERROR)]
        );
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `forms` WHERE `form_name` = ?', [self::MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `onsite_documents` WHERE `file_name` = ?', [self::MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `document_templates` WHERE `profile` = ?', [self::MARKER]);
    }
}
