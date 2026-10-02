<?php

/**
 * Isolated unit tests for the ESign Router module allowlist.
 *
 * Guards against the path-traversal / Local File Inclusion regression in
 * ESign\Router: the `module` request parameter must resolve only to the
 * fixed set of known ESign modules and reject anything else, so no
 * attacker-controlled bytes can reach the require_once path or the
 * instantiated controller class name.
 *
 * Runs in the "isolated" suite (no database or globals bootstrap). The
 * ESign namespace is not PSR-4 autoloaded, so the router is required
 * directly; its own top-level require resolves through OEGlobalsBag, which
 * we seed with the library path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\ESign;

use ESign\Router;
use InvalidArgumentException;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $srcDir = dirname(__DIR__, 4) . '/library';
        // getSrcDir() falls back to the 'srcdir' global when no Kernel is
        // booted (the isolated suite boots none); seed both so the router's
        // top-level require_once resolves.
        $GLOBALS['srcdir'] = $srcDir;
        OEGlobalsBag::getInstance()->set('srcdir', $srcDir);
        require_once $srcDir . '/ESign/Router.php';
    }

    #[DataProvider('validModules')]
    public function testResolvesKnownModulesCaseInsensitively(string $input, string $expected): void
    {
        $this->assertSame($expected, Router::resolveModule($input));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function validModules(): array
    {
        return [
            'lowercase form'      => ['form', 'Form'],
            'lowercase encounter' => ['encounter', 'Encounter'],
            'ucfirst form'        => ['Form', 'Form'],
            'upper encounter'     => ['ENCOUNTER', 'Encounter'],
            'padded form'         => ['  form  ', 'Form'],
        ];
    }

    #[DataProvider('maliciousModules')]
    public function testRejectsUnknownOrTraversalModules(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        Router::resolveModule($input);
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function maliciousModules(): array
    {
        return [
            'empty string'       => [''],
            'null'               => [null],
            'traversal to tmp'   => ['../../../../../../../../tmp/send'],
            'absolute path'      => ['/etc/passwd'],
            'dot dot'            => ['..'],
            'sibling Abstract'   => ['Abstract'],
            'sibling Utils'      => ['Utils'],
            'embedded null byte' => ["fo\0rm"],
            'unknown module'     => ['admin'],
            'nested valid name'  => ['../form'],
        ];
    }
}
