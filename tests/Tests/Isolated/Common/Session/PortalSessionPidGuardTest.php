<?php

/**
 * Isolated unit tests for the pure predicates of
 * `OpenEMR\Common\Session\PortalSessionPidGuard`.
 *
 * The predicates isMatchingPid() and scanRequestSourceForMismatch() carry
 * the full decision; the assert-* wrappers only compose the predicate result
 * with AccessDeniedHelper::deny() (which calls exit(), so the wrappers are
 * exercised in the integration-testing layer).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Session;

use OpenEMR\Common\Session\PortalSessionPidGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PortalSessionPidGuardTest extends TestCase
{
    #[DataProvider('isMatchingPidCases')]
    public function testIsMatchingPid(mixed $subjectPid, mixed $sessionPid, bool $expected): void
    {
        $this->assertSame(
            $expected,
            PortalSessionPidGuard::isMatchingPid($subjectPid, $sessionPid),
        );
    }

    /**
     * @return array<string, array{mixed, mixed, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function isMatchingPidCases(): array
    {
        return [
            'exact int match'                       => [5, 5, true],
            'numeric string matches int session'    => ['5', 5, true],
            'int mismatch'                          => [99, 5, false],
            'numeric string mismatch'               => ['99', 5, false],
            'zero session pid rejects everything'   => [5, 0, false],
            'negative session pid rejects'          => [5, -1, false],
            'null session pid rejects'              => [5, null, false],
            'empty-string session pid rejects'      => [5, '', false],
            'non-numeric session pid rejects'       => [5, 'abc', false],
            'null request pid'                      => [null, 5, false],
            'empty-string request pid'              => ['', 5, false],
            'non-numeric string request pid'        => ['abc', 5, false],
            'mixed alphanumeric request pid'        => ['5abc', 5, false],
            'array as request pid'                  => [[5], 5, false],
            'object as request pid'                 => [new \stdClass(), 5, false],
            'bool true request pid'                 => [true, 5, false],
            'bool false request pid'                => [false, 5, false],
            'float rejected even when whole'        => [5.0, 5, false],
            'float rejected (would truncate)'       => [5.9, 5, false],
            'decimal string rejected'               => ['5.5', 5, false],
            'scientific-notation string rejected'   => ['1e0', 5, false],
        ];
    }

    /**
     * @param array<int|string, mixed> $source
     */
    #[DataProvider('scanRequestSourceForMismatchCases')]
    public function testScanRequestSourceForMismatch(array $source, int $sessionPid, ?string $expected): void
    {
        $this->assertSame(
            $expected,
            PortalSessionPidGuard::scanRequestSourceForMismatch($source, $sessionPid),
        );
    }

    /**
     * @return array<string, array{array<int|string, mixed>, int, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function scanRequestSourceForMismatchCases(): array
    {
        return [
            // Clean cases
            'empty source is clean'                        => [[], 5, null],
            'non-pid keys ignored'                         => [['foo' => 'bar', 'name' => 'x'], 5, null],
            'pid matches'                                  => [['pid' => 5], 5, null],
            'patientId matches'                            => [['patientId' => 5], 5, null],
            'PID uppercase matches'                        => [['PID' => 5], 5, null],
            'PatientId matches'                            => [['PatientId' => '5'], 5, null],
            'Pid_Equals matches (Phreez variant)'          => [['Pid_Equals' => 5], 5, null],
            'PatientId_In matches (Phreez variant)'        => [['PatientId_In' => 5], 5, null],
            'multiple pid keys all match'                  => [['pid' => 5, 'patientId' => 5, 'Pid_Equals' => 5], 5, null],
            'unrelated key starting with pi ignored'       => [['picture' => 'x', 'pipe' => 'y'], 5, null],
            'unrelated key starting with p ignored'        => [['patient_name' => 'x'], 5, null],

            // Attack cases
            'pid mismatch'                                 => [['pid' => 99], 5, 'pid'],
            'patientId mismatch'                           => [['patientId' => 99], 5, 'patientId'],
            'case-variant bypass (Pid)'                    => [['Pid' => 99], 5, 'Pid'],
            'case-variant bypass (pID)'                    => [['pID' => 99], 5, 'pID'],
            'Phreez Pid_Equals bypass (GHSA-hvp7)'         => [['Pid_Equals' => 99], 5, 'Pid_Equals'],
            'Phreez PatientId_In bypass'                   => [['PatientId_In' => 99], 5, 'PatientId_In'],
            'Phreez pid_greaterthan bypass'                => [['pid_greaterthan' => 0], 5, 'pid_greaterthan'],
            'Phreez pid_contains bypass'                   => [['pid_contains' => '99'], 5, 'pid_contains'],
            'empty-string overwrite (GHSA-v688)'           => [['pid' => ''], 5, 'pid'],
            'empty-string on Phreez variant'               => [['Pid_Equals' => ''], 5, 'Pid_Equals'],
            'null value on pid key'                        => [['pid' => null], 5, 'pid'],
            'non-numeric value'                            => [['pid' => 'abc'], 5, 'pid'],
            'mixed alphanumeric value'                     => [['pid' => '5abc'], 5, 'pid'],
            'array value'                                  => [['pid' => [5]], 5, 'pid'],
            'clean key precedes attack key'                => [['pid' => 5, 'PatientId_In' => 99], 5, 'PatientId_In'],
            'attack key precedes clean key'                => [['Pid_Equals' => 99, 'pid' => 5], 5, 'Pid_Equals'],

            // Integer keys are ignored (no HTTP key can be numeric-only anyway)
            'integer key ignored'                          => [[0 => 99, 1 => 99], 5, null],
        ];
    }
}
