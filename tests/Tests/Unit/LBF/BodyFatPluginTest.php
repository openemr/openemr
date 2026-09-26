<?php

/**
 * The Body Fat LBF plugins write the patient's age and sex into the
 * JavaScript that computes the body fat percentage.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\LBF;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BodyFatPluginTest extends TestCase
{
    private int $pid;

    private mixed $previousPid;

    /**
     * Load the plugins the way interface/forms/LBF/new.php does for the form it renders.
     */
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../library/patient.inc.php';
        require_once __DIR__ . '/../../../../sites/default/LBF/LBFathbf.plugin.php';
        require_once __DIR__ . '/../../../../sites/default/LBF/LBFvbf.plugin.php';
    }

    /**
     * Remember the session patient and pick a pid with no patient_data row yet.
     *
     * patient_data.pid is not auto-increment (id is), so the test takes one above the current max.
     */
    protected function setUp(): void
    {
        $this->previousPid = OEGlobalsBag::getInstance()->get('pid');
        $next = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next FROM patient_data", 'next');
        $this->pid = is_numeric($next) ? (int) $next : 0;
    }

    /**
     * Delete the test patient and restore the session patient.
     */
    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$this->pid]);
        OEGlobalsBag::getInstance()->set('pid', $this->previousPid);
    }

    /**
     * @return array<string, array{'LBFathbf'|'LBFvbf'}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function formProvider(): array
    {
        return [
            'Body Fat (LBFathbf)' => ['LBFathbf'],
            'Vitals and Body Fat (LBFvbf)' => ['LBFvbf'],
        ];
    }

    /**
     * @param 'LBFathbf'|'LBFvbf' $form
     */
    #[Test]
    #[DataProvider('formProvider')]
    public function aFemalePatientGetsHerAgeAndSex(string $form): void
    {
        $this->insertPatient($this->birthDateYearsAgo(35), 'Female');

        $javascript = $this->renderJavascript($form);

        $this->assertStringContainsString('var age = 35;', $javascript);
        $this->assertStringContainsString('var sex = 1;', $javascript);
    }

    /**
     * @param 'LBFathbf'|'LBFvbf' $form
     */
    #[Test]
    #[DataProvider('formProvider')]
    public function aMalePatientGetsHisAgeAndSex(string $form): void
    {
        $this->insertPatient($this->birthDateYearsAgo(42), 'Male');

        $javascript = $this->renderJavascript($form);

        $this->assertStringContainsString('var age = 42;', $javascript);
        $this->assertStringContainsString('var sex = 0;', $javascript);
    }

    /**
     * @param 'LBFathbf'|'LBFvbf' $form
     */
    #[Test]
    #[DataProvider('formProvider')]
    public function aPatientWithoutBirthDateGetsAgeZero(string $form): void
    {
        $this->insertPatient(null, 'Female');

        $javascript = $this->renderJavascript($form);

        $this->assertStringContainsString('var age = 0;', $javascript);
        $this->assertStringContainsString('var sex = 1;', $javascript);
    }

    /**
     * @param 'LBFathbf'|'LBFvbf' $form
     */
    #[Test]
    #[DataProvider('formProvider')]
    public function anUnknownPatientGetsTheDefaults(string $form): void
    {
        // No patient_data row for $this->pid.
        $javascript = $this->renderJavascript($form);

        $this->assertStringContainsString('var age = 0;', $javascript);
        $this->assertStringContainsString('var sex = 0;', $javascript);
    }

    /**
     * Insert the patient_data row for $this->pid.
     */
    private function insertPatient(?string $birthDate, string $sex): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO patient_data (pid, fname, lname, DOB, sex) VALUES (?, ?, ?, ?, ?)",
            [$this->pid, 'BodyFat', 'Fixture', $birthDate, $sex]
        );
    }

    /**
     * A birth date $years whole years ago, plus two months so the age does not depend on today's day.
     */
    private function birthDateYearsAgo(int $years): string
    {
        return (new \DateTimeImmutable('today'))->sub(new \DateInterval('P' . $years . 'Y2M'))->format('Y-m-d');
    }

    /**
     * Render the <head> JavaScript of the form for the current patient, as new.php does.
     *
     * @param 'LBFathbf'|'LBFvbf' $form
     */
    private function renderJavascript(string $form): string
    {
        OEGlobalsBag::getInstance()->set('pid', $this->pid);
        ob_start();
        try {
            if ($form === 'LBFathbf') {
                \LBFathbf_javascript();
            } else {
                \LBFvbf_javascript();
            }
        } finally {
            $javascript = ob_get_clean();
        }

        $this->assertIsString($javascript);
        return $javascript;
    }
}
