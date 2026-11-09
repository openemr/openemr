<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Config;

use Firehed\Container\Compiler;
use OpenEMR\BC\FallbackRouter;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class ContainerCompilationTest extends TestCase
{
    private string $outputPath;

    protected function setUp(): void
    {
        $this->outputPath = sys_get_temp_dir() . '/openemr-container-' . bin2hex(random_bytes(8)) . '.php';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->outputPath)) {
            unlink($this->outputPath);
        }
    }

    public function testConfigCompiles(): void
    {
        $compiler = new Compiler($this->outputPath);
        $compiler->addDirectory(__DIR__ . '/../../../../config');
        $container = $compiler->build();

        self::assertFileExists($this->outputPath, 'compiling should write the container to disk');
        self::assertTrue($container->has(FallbackRouter::class), 'compiled container should include config/ definitions');
    }
}
