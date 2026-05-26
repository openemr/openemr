<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Storage;

use OpenEMR\Services\Storage\WebUserGuard;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessExceptionInterface;
use Symfony\Component\Process\Process;

final class WebUserGuardTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/openemr-webuserguard-test-' . bin2hex(random_bytes(8));
        mkdir($this->tempDir, 0700, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            // Restore ownership before deletion in case a test chown'd it
            // away from the test process.
            $currentUid = self::currentUid();
            if ($currentUid !== null) {
                @chown($this->tempDir, $currentUid);
            }
            @rmdir($this->tempDir);
        }
    }

    public function testAssertSafeWithReferenceDoesNotThrowWhenOwnerMatchesCurrentUid(): void
    {
        $this->expectNotToPerformAssertions();
        // tempDir was just created by the test process; owner is the
        // current EUID; no mismatch.
        WebUserGuard::assertSafeWithReference('test write', $this->tempDir);
    }

    public function testAssertSafeWithReferenceNoOpsOnMissingReferencePath(): void
    {
        $this->expectNotToPerformAssertions();
        $missing = $this->tempDir . '/does-not-exist';

        WebUserGuard::assertSafeWithReference('test write', $missing);
    }

    public function testAssertSafeWithReferenceThrowsOnUidMismatch(): void
    {
        // Need to chown the reference dir to a UID different from the
        // current process's. Requires root + an available second UID.
        $currentUid = self::currentUid();
        if ($currentUid === null) {
            self::markTestSkipped('Cannot determine current UID; UID-based check does not apply');
        }
        if ($currentUid !== 0) {
            self::markTestSkipped('Test process is not root; cannot chown to fabricate a mismatch');
        }
        $apacheUid = self::userUid('apache');
        if ($apacheUid === null || $apacheUid === $currentUid) {
            self::markTestSkipped("No usable non-root user on this system to fabricate a mismatch");
        }

        chown($this->tempDir, $apacheUid);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Web-user mismatch/');
        $this->expectExceptionMessageMatches('/UID ' . preg_quote((string)$currentUid, '/') . '/');
        $this->expectExceptionMessageMatches('/UID ' . preg_quote((string)$apacheUid, '/') . '/');

        WebUserGuard::assertSafeWithReference('test write', $this->tempDir);
    }

    /** Mirror of WebUserGuard::currentEffectiveUid for posix-optional environments. */
    private static function currentUid(): ?int
    {
        if (function_exists('posix_geteuid')) {
            return posix_geteuid();
        }
        return self::idUViaProcess([]);
    }

    private static function userUid(string $username): ?int
    {
        if (function_exists('posix_getpwnam')) {
            $pw = @posix_getpwnam($username);
            return ($pw !== false) ? $pw['uid'] : null;
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
