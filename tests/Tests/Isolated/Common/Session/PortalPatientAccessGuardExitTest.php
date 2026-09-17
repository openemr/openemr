<?php

/**
 * Exit-aware tests for portal patient demographic authorization failures.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Session;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class PortalPatientAccessGuardExitTest extends TestCase
{
    private const FIXTURE = __DIR__ . '/fixtures/portal-patient-access-guard-denial.php';

    public function testPortalPatientCannotReadAnotherPatient(): void
    {
        $process = new Process([PHP_BINARY, self::FIXTURE, '11', '22']);
        $process->run();

        $this->assertSame(
            1,
            $process->getExitCode(),
            $process->getErrorOutput(),
        );
        $this->assertSame('Access denied', $process->getOutput());
        $this->assertSame('', $process->getErrorOutput());
    }
}
