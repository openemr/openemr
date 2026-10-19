<?php

/**
 * A form counts as signed on the patient report when it or its encounter is signed.
 *
 * With "Only Include E-Signed Forms On Report" on, the report printed
 * "Not signed." for every form in an encounter signed as a whole: it checked
 * the form's own signatures only, while signing the encounter stores one
 * signature for the encounter (table form_encounter).
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
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/ESign/Api.php';

class FormSignedForReportTest extends TestCase
{
    /** Marks the fixture signatures (hash), so cleanup removes only those. */
    private const MARKER = 'esign-report-encounter-signed-test';

    // Ids no real row uses; signatures are looked up by tid and table only.
    private const FORM_ID = 990000101;
    private const ENCOUNTER = 990000102;
    private const OTHER_ENCOUNTER = 990000103;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    public function testUnsignedFormIsNotSigned(): void
    {
        $this->assertFalse($this->signedForReport());
    }

    public function testFormWithItsOwnSignatureIsSigned(): void
    {
        $this->sign(self::FORM_ID, 'forms', 0);

        $this->assertTrue($this->signedForReport());
    }

    public function testFormInALockedSignedEncounterIsSigned(): void
    {
        $this->sign(self::ENCOUNTER, 'form_encounter', 1);

        $this->assertTrue($this->signedForReport());
    }

    public function testFormInASignedEncounterWithoutALockIsSigned(): void
    {
        $this->sign(self::ENCOUNTER, 'form_encounter', 0);

        $this->assertTrue($this->signedForReport());
    }

    public function testAnotherEncountersSignatureDoesNotCount(): void
    {
        $this->sign(self::OTHER_ENCOUNTER, 'form_encounter', 1);

        $this->assertFalse($this->signedForReport());
    }

    private function signedForReport(): bool
    {
        return (new Api())->isFormSignedForReport(self::FORM_ID, 'eye_mag', self::ENCOUNTER);
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
    }
}
