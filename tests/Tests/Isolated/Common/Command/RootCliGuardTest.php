<?php

/**
 * Tests for RootCliGuard.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command;

use OpenEMR\Common\Command\RootCliGuard;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessExceptionInterface;
use Symfony\Component\Process\Process;

final class RootCliGuardTest extends TestCase
{
    public function testAssertNotRootDoesNotThrowAsNonRoot(): void
    {
        $uid = self::currentUid();
        if ($uid === null) {
            self::markTestSkipped('Cannot determine current UID');
        }
        if ($uid === 0) {
            self::markTestSkipped('Test process is root; covered by the throws-as-root test');
        }

        $this->expectNotToPerformAssertions();
        RootCliGuard::assertNotRoot();
    }

    public function testAssertNotRootThrowsAsRoot(): void
    {
        $uid = self::currentUid();
        if ($uid !== 0) {
            self::markTestSkipped('Test process is not root; covered by the passes-as-non-root test');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/must not be run as root/');
        // Sanity-check that the message uses bracket placeholder (not
        // HTML-special angle brackets) so it survives accidental
        // browser rendering.
        $this->expectExceptionMessageMatches('/\\[your command\\]/');

        RootCliGuard::assertNotRoot();
    }

    public function testAssertNotRootPassesInSubprocessDroppedToApache(): void
    {
        // Only meaningful when this process IS root and an apache user
        // exists to drop into — covers the non-root code path even when
        // the test runner itself is root (typical CI container case).
        $uid = self::currentUid();
        if ($uid !== 0) {
            self::markTestSkipped('Test process is not root; cannot drop privileges');
        }
        $apacheUid = self::userUid('apache');
        if ($apacheUid === null || $apacheUid === 0) {
            self::markTestSkipped('No usable non-root user on this system');
        }

        $autoload = realpath(__DIR__ . '/../../../../../vendor/autoload.php');
        self::assertNotFalse($autoload, 'vendor/autoload.php must exist for the subprocess');

        $php = '<?php require ' . var_export($autoload, true) . ';'
            . ' \\OpenEMR\\Common\\Command\\RootCliGuard::assertNotRoot();'
            . ' echo "OK";';
        $tmpScript = tempnam(sys_get_temp_dir(), 'rootcliguard-');
        self::assertNotFalse($tmpScript);
        file_put_contents($tmpScript, $php);
        // Apache must be able to read it.
        chmod($tmpScript, 0644);

        try {
            $process = new Process(['su', '-s', '/bin/sh', 'apache', '-c', 'php ' . escapeshellarg($tmpScript)]);
            $process->run();
            self::assertTrue(
                $process->isSuccessful(),
                "Subprocess as apache failed: stdout=" . $process->getOutput()
                . " stderr=" . $process->getErrorOutput(),
            );
            self::assertSame('OK', trim($process->getOutput()));
        } finally {
            @unlink($tmpScript);
        }
    }

    private static function currentUid(): ?int
    {
        if (function_exists('posix_geteuid')) {
            return posix_geteuid();
        }
        if (PHP_OS_FAMILY === 'Windows') {
            return null;
        }
        return self::idUViaProcess([]);
    }

    private static function userUid(string $username): ?int
    {
        if (function_exists('posix_getpwnam')) {
            $pw = @posix_getpwnam($username);
            return ($pw !== false) ? $pw['uid'] : null;
        }
        if (PHP_OS_FAMILY === 'Windows') {
            return null;
        }
        return self::idUViaProcess([$username]);
    }

    /**
     * @param list<string> $args
     */
    private static function idUViaProcess(array $args): ?int
    {
        try {
            $process = new Process(['id', '-u', ...$args]);
            $process->run();
            if (!$process->isSuccessful()) {
                return null;
            }
            $t = trim($process->getOutput());
            return ($t !== '' && ctype_digit($t)) ? (int) $t : null;
        } catch (ProcessExceptionInterface) {
            return null;
        }
    }
}
