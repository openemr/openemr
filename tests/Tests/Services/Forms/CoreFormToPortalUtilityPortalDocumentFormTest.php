<?php

/**
 * CoreFormToPortalUtility::questionnaireDocumentPatient() against the database: a questionnaire
 * form has a document patient only when a questionnaire document of that patient holds it.
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

    protected function setUp(): void
    {
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testTheFormOfThePatientsOwnQuestionnaireDocumentHasThatPatient(): void
    {
        $this->insertForm(self::PID);
        $this->insertDocument(self::PID, $this->insertTemplate('{Questionnaire:PHQ-9}'));

        $this->assertSame(self::PID, CoreFormToPortalUtility::questionnaireDocumentPatient(self::FORM_ID));
    }

    public function testAFormWithNoDocumentHasNone(): void
    {
        $this->insertForm(self::PID);

        $this->assertNull(CoreFormToPortalUtility::questionnaireDocumentPatient(self::FORM_ID));
    }

    public function testAFormHeldByAnotherPatientsDocumentHasNone(): void
    {
        $this->insertForm(self::PID);
        $this->insertDocument(self::OTHER_PID, $this->insertTemplate('{Questionnaire:PHQ-9}'));

        $this->assertNull(CoreFormToPortalUtility::questionnaireDocumentPatient(self::FORM_ID));
    }

    public function testTheSameIdHeldByADocumentEmbeddingAnotherFormHasNone(): void
    {
        // An LBF document's form id can equal a questionnaire's; each form table numbers its own rows.
        $this->insertForm(self::PID);
        $this->insertDocument(self::PID, $this->insertTemplate('{EncounterDocument:LBFtest}'));

        $this->assertNull(CoreFormToPortalUtility::questionnaireDocumentPatient(self::FORM_ID));
    }

    public function testADeletedFormHasNone(): void
    {
        $this->insertForm(self::PID, deleted: 1);
        $this->insertDocument(self::PID, $this->insertTemplate('{Questionnaire:PHQ-9}'));

        $this->assertNull(CoreFormToPortalUtility::questionnaireDocumentPatient(self::FORM_ID));
    }

    private function insertForm(int $pid, int $deleted = 0): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`,'
            . " `authorized`, `deleted`, `formdir`, `issue_id`, `provider_id`)"
            . " VALUES (NOW(), 0, ?, ?, ?, '', '', 0, ?, 'questionnaire_assessments', 0, 0)",
            [self::MARKER, self::FORM_ID, $pid, $deleted]
        );
    }

    private function insertTemplate(string $content): int
    {
        return QueryUtils::sqlInsert(
            'INSERT INTO `document_templates` (`pid`, `profile`, `category`, `template_name`, `status`,'
            . " `send_date`, `size`, `mime`, `template_content`) VALUES (-1, ?, 'Test', ?, 'New', NOW(), ?, 'text/plain', ?)",
            [self::MARKER, self::MARKER . '-' . md5($content), strlen($content), $content]
        );
    }

    private function insertDocument(int $pid, int $templateId): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO `onsite_documents` (`pid`, `encounter`, `doc_type`, `patient_signed_status`, `accept_signed_status`,'
            . " `authorizing_signator`, `denial_reason`, `file_name`, `file_path`) VALUES (?, ?, 'Consent', 0, 0, '', '', ?, ?)",
            [$pid, self::FORM_ID, self::MARKER, (string) $templateId]
        );
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `forms` WHERE `form_name` = ?', [self::MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `onsite_documents` WHERE `file_name` = ?', [self::MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `document_templates` WHERE `profile` = ?', [self::MARKER]);
    }
}
