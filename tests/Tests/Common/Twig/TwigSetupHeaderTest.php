<?php

/**
 * setupHeader() from Twig puts the header in the page once.
 *
 * Runs with the full bootstrap, because Header::setupHeader() needs the kernel's event
 * dispatcher and the asset configuration.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Twig;

use OpenEMR\Common\Twig\TwigExtension;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class TwigSetupHeaderTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function templateProvider(): array
    {
        return [
            'no arguments' => ['{{ setupHeader() }}'],
            'assets' => ["{{ setupHeader(['opener']) }}"],
            'echo already turned off' => ['{{ setupHeader([], false) }}'],
        ];
    }

    #[Test]
    #[DataProvider('templateProvider')]
    public function theHeaderAppearsOnceAndIsNotEchoed(string $template): void
    {
        $globals = OEGlobalsBag::getInstance();
        $twig = new Environment(new ArrayLoader(['page' => $template]));
        $twig->addExtension(new TwigExtension($globals, $globals->getKernel()));

        $this->expectOutputString('');
        $html = $twig->render('page');

        $this->assertSame(1, substr_count($html, 'jquery.min.js'), $html);
        $this->assertSame(1, substr_count($html, 'bootstrap.bundle.min.js'), $html);
    }
}
