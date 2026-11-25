<?php

/**
 * Isolated tests for the demo seed loader's work directory checks.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\DemoSeed;

use OpenEMR\DemoSeed\WorkDirectory;
use OpenEMR\DemoSeed\WorkDirectoryException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class WorkDirectoryTest extends TestCase
{
    private Filesystem $filesystem;
    private string $base;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->base = $this->filesystem->tempnam(sys_get_temp_dir(), 'demo-seed-test-');
        $this->filesystem->remove($this->base);
        $this->filesystem->mkdir($this->base, 0700);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->base);
    }

    public function testMakesAMissingDirectoryPrivateAndResolvesARelativePath(): void
    {
        $workDirectory = WorkDirectory::open('work', $this->base, $this->filesystem);

        $this->assertSame($this->base . '/work', $workDirectory->path);
        $this->assertSame($this->base . '/work/patients.json', $workDirectory->file('patients.json'));
        $this->assertSame(0o700, $this->permissions($this->base . '/work'));
    }

    public function testRefusesADirectoryOthersCanWriteTo(): void
    {
        $this->filesystem->mkdir($this->base . '/shared');
        $this->filesystem->chmod($this->base . '/shared', 0o777);

        $this->expectException(WorkDirectoryException::class);
        $this->expectExceptionMessage('can be written by other users');

        WorkDirectory::open($this->base . '/shared', '/', $this->filesystem);
    }

    public function testRefusesASymlink(): void
    {
        $this->filesystem->mkdir($this->base . '/real', 0700);
        $this->filesystem->symlink($this->base . '/real', $this->base . '/link');

        $this->expectException(WorkDirectoryException::class);
        $this->expectExceptionMessage('is not a directory');

        WorkDirectory::open($this->base . '/link', '/', $this->filesystem);
    }

    public function testRefusesADirectoryUnderOneOthersCanWriteTo(): void
    {
        $this->filesystem->mkdir($this->base . '/open');
        $this->filesystem->chmod($this->base . '/open', 0o777);

        $this->expectException(WorkDirectoryException::class);
        $this->expectExceptionMessage('/open can be written by other users, who could replace');

        WorkDirectory::open($this->base . '/open/work', '/', $this->filesystem);
    }

    public function testAcceptsADirectoryUnderAStickyOneLikeTmp(): void
    {
        $this->filesystem->mkdir($this->base . '/sticky');
        $this->filesystem->chmod($this->base . '/sticky', 0o1777);

        $workDirectory = WorkDirectory::open($this->base . '/sticky/work', '/', $this->filesystem);

        $this->assertSame($this->base . '/sticky/work', $workDirectory->path);
    }

    public function testResolvesASymlinkAboveTheDirectory(): void
    {
        $this->filesystem->mkdir($this->base . '/real', 0700);
        $this->filesystem->symlink($this->base . '/real', $this->base . '/link');

        $workDirectory = WorkDirectory::open($this->base . '/link/work', '/', $this->filesystem);

        $this->assertSame($this->base . '/real/work', $workDirectory->path);
    }

    public function testASecondRunCanNotUseTheDirectoryWhileTheFirstHoldsIt(): void
    {
        $first = WorkDirectory::open($this->base . '/work', '/', $this->filesystem);

        try {
            WorkDirectory::open($this->base . '/work', '/', $this->filesystem);
            $this->fail('The second open should have been refused');
        } catch (WorkDirectoryException $e) {
            $this->assertStringContainsString('Another run of the loader is using', $e->getMessage());
        }
        unset($first);

        // released once the first run is done with it
        WorkDirectory::open($this->base . '/work', '/', $this->filesystem);
    }

    public function testAcceptsAPrivateFileAndRefusesOneOthersCanChange(): void
    {
        $workDirectory = WorkDirectory::open($this->base . '/work', '/', $this->filesystem);
        $jar = $workDirectory->file('synthea.jar');
        $this->filesystem->dumpFile($jar, 'jar');
        $this->filesystem->chmod($jar, 0o644);

        $workDirectory->assertPrivateFile($jar);

        $this->filesystem->chmod($jar, 0o666);
        $this->expectException(WorkDirectoryException::class);
        $this->expectExceptionMessage('can be changed by other users');
        $workDirectory->assertPrivateFile($jar);
    }

    private function permissions(string $path): int
    {
        clearstatcache(true, $path);
        $permissions = fileperms($path);
        $this->assertNotFalse($permissions);
        return $permissions & 0o777;
    }
}
