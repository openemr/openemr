<?php

/**
 * Database test: the tab bar's request for the line under the patient's name answers
 * with the parts as JSON, and with a 404 for a pid that names no patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Tabs;

use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PatientService;
use OpenEMR\Tabs\PatientBarInfoEndpoint;
use OpenEMR\Tabs\PatientBarInfoService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientBarInfoEndpointTest extends TestCase
{
    /** A synthetic id, clear of the demo data. */
    private const PID = 99990071;

    private bool $translationWas = false;

    private PatientBarInfoEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->translationWas = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, pubpid, fname, lname, sex, date) VALUES (?, 'E-71', 'PAT', 'BARTEST', '', NOW())",
            [self::PID]
        );
        $this->endpoint = new PatientBarInfoEndpoint(new PatientBarInfoService(
            new PatientService(),
            new FrozenClock(new \DateTimeImmutable('2026-11-23 12:00:00')),
        ));
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        OEGlobalsBag::getInstance()->set('disable_translation', $this->translationWas);
    }

    public function testAKnownPatientGetsTheirParts(): void
    {
        $response = $this->endpoint->respond((string) self::PID);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"parts":["PID ' . self::PID . '"]}', $response->getContent());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function noPatientProvider(): array
    {
        return [
            'no such patient' => ['99990079'],
            'empty' => [''],
            'not a number' => ['43 OR 1=1'],
            'negative' => ['-43'],
        ];
    }

    #[DataProvider('noPatientProvider')]
    public function testAPidThatNamesNoPatientGetsA404(string $rawPid): void
    {
        $response = $this->endpoint->respond($rawPid);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('{"parts":[]}', $response->getContent());
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
    }
}
