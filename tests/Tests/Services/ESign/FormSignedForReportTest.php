<?php

/**
 * A form counts as signed on the patient report when it is signed, or locked
 * by its encounter's signature.
 *
 * With "Only Include E-Signed Forms On Report" on, the report printed
 * "Not signed." for every form in an encounter signed and locked as a whole:
 * it checked the form's own signatures only, while signing the encounter
 * stores one signature for the encounter (table form_encounter).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\ESign;

use ESign\Api;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/ESign/Api.php';

class FormSignedForReportTest extends TestCase
{
    /** Marks the fixture rows (signature hash, form name), so cleanup removes only those. */
    private const MARKER = 'esign-report-encounter-signed-test';

    // Ids no real row uses.
    private const PID = 990000100;
    private const ENCOUNTER = 990000102;
    private const OTHER_ENCOUNTER = 990000103;

    private const LOCK_GLOBALS = ['lock_esign_all', 'lock_esign_individual'];

    /** @var array<string, mixed> */
    private array $savedGlobals = [];

    protected function setUp(): void
    {
        parent::setUp();
        $globals = OEGlobalsBag::getInstance();
        foreach (self::LOCK_GLOBALS as $name) {
            $this->savedGlobals[$name] = $globals->get($name);
        }
        $this->setLocking(true);
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        $globals = OEGlobalsBag::getInstance();
        foreach ($this->savedGlobals as $name => $value) {
            $globals->set($name, $value);
        }
        parent::tearDown();
    }

    public function testUnsignedFormIsNotSigned(): void
    {
        $formId = $this->addForm(self::ENCOUNTER);

        $this->assertFalse($this->signedForReport($formId));
    }

    public function testFormWithItsOwnSignatureIsSigned(): void
    {
        $formId = $this->addForm(self::ENCOUNTER);
        $this->sign($formId, 'forms', 0);

        $this->assertTrue($this->signedForReport($formId));
    }

    public function testFormLockedByItsEncountersSignatureIsSigned(): void
    {
        $formId = $this->addForm(self::ENCOUNTER);
        $this->sign(self::ENCOUNTER, 'form_encounter', 1);

        $this->assertTrue($this->signedForReport($formId));
    }

    public function testEncounterSignatureWithoutALockDoesNotCount(): void
    {
        // It doesn't freeze the encounter, so it may not cover forms added later.
        $formId = $this->addForm(self::ENCOUNTER);
        $this->sign(self::ENCOUNTER, 'form_encounter', 0);

        $this->assertFalse($this->signedForReport($formId));
    }

    public function testEncounterLockIsIgnoredWhenEncounterLockingIsOff(): void
    {
        $this->setLocking(false);
        $formId = $this->addForm(self::ENCOUNTER);
        $this->sign(self::ENCOUNTER, 'form_encounter', 1);

        $this->assertFalse($this->signedForReport($formId));
    }

    public function testAnotherEncountersSignatureDoesNotCount(): void
    {
        $formId = $this->addForm(self::ENCOUNTER);
        $this->sign(self::OTHER_ENCOUNTER, 'form_encounter', 1);

        $this->assertFalse($this->signedForReport($formId));
    }

    public function testDeletedFormIsNotSigned(): void
    {
        $formId = $this->addForm(self::ENCOUNTER, 1);
        $this->sign(self::ENCOUNTER, 'form_encounter', 1);

        $this->assertFalse($this->signedForReport($formId));
    }

    private function signedForReport(int $formId): bool
    {
        return (new Api())->isFormSignedForReport($formId, 'eye_mag');
    }

    private function setLocking(bool $encounterLocks): void
    {
        $globals = OEGlobalsBag::getInstance();
        $globals->set('lock_esign_all', $encounterLocks);
        $globals->set('lock_esign_individual', false);
    }

    private function addForm(int $encounter, int $deleted = 0): int
    {
        return QueryUtils::sqlInsert(
            'INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`, `authorized`, `deleted`, `formdir`) VALUES (NOW(), ?, ?, 1, ?, ?, ?, 1, ?, ?)',
            [$encounter, self::MARKER, self::PID, 'admin', 'Default', $deleted, 'eye_mag']
        );
    }

    private function sign(int $tid, string $table, int $isLock): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO `esign_signatures` (`tid`, `table`, `uid`, `datetime`, `is_lock`, `hash`, `signature_hash`) VALUES (?, ?, 1, NOW(), ?, ?, ?)',
            [$tid, $table, $isLock, self::MARKER, self::MARKER]
        );
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `esign_signatures` WHERE `hash` = ?', [self::MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `forms` WHERE `form_name` = ?', [self::MARKER]);
    }
}
