<?php

/**
 * CoreFormToPortalUtility::isPortalDocumentForm() against the database: a form counts as a portal
 * document's only when a document of the same patient holds its id.
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
    private const FORM_DIR = 'questionnaire_assessments';

    protected function setUp(): void
    {
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testAFormHeldByThePatientsOwnDocumentIsAPortalDocumentForm(): void
    {
        $this->insertForm(self::PID);
        $this->insertDocument(self::PID);

        $this->assertTrue(CoreFormToPortalUtility::isPortalDocumentForm(self::FORM_ID, self::FORM_DIR));
    }

    public function testAFormWithNoDocumentIsNot(): void
    {
        $this->insertForm(self::PID);

        $this->assertFalse(CoreFormToPortalUtility::isPortalDocumentForm(self::FORM_ID, self::FORM_DIR));
    }

    public function testAFormHeldByAnotherPatientsDocumentIsNot(): void
    {
        $this->insertForm(self::PID);
        $this->insertDocument(self::OTHER_PID);

        $this->assertFalse(CoreFormToPortalUtility::isPortalDocumentForm(self::FORM_ID, self::FORM_DIR));
    }

    public function testTheSameIdInAnotherFormDirectoryIsNot(): void
    {
        $this->insertForm(self::PID);
        $this->insertDocument(self::PID);

        $this->assertFalse(CoreFormToPortalUtility::isPortalDocumentForm(self::FORM_ID, 'LBFtest'));
    }

    private function insertForm(int $pid): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`,'
            . " `authorized`, `deleted`, `formdir`, `issue_id`, `provider_id`) VALUES (NOW(), 0, 'portal-form-test', ?, ?, '', '', 0, 0, ?, 0, 0)",
            [self::FORM_ID, $pid, self::FORM_DIR]
        );
    }

    private function insertDocument(int $pid): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO `onsite_documents` (`pid`, `encounter`, `doc_type`, `patient_signed_status`, `accept_signed_status`,'
            . " `authorizing_signator`, `denial_reason`, `file_name`, `file_path`) VALUES (?, ?, 'Consent', 0, 0, '', '', 'portal-form-test', '')",
            [$pid, self::FORM_ID]
        );
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `forms` WHERE `form_name` = 'portal-form-test'");
        QueryUtils::sqlStatementThrowException("DELETE FROM `onsite_documents` WHERE `file_name` = 'portal-form-test'");
    }
}
