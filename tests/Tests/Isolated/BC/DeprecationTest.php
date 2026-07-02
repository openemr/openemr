<?php

/**
 * Tests for BC\Deprecation utility.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use ErrorException;
use OpenEMR\BC\Deprecation;
use OpenEMR\BC\DeprecationMode;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

use const E_USER_DEPRECATED;

class DeprecationTest extends TestCase
{
    private DeprecationMode $originalMode;

    protected function setUp(): void
    {
        $this->originalMode = Deprecation::$mode;
    }

    protected function tearDown(): void
    {
        Deprecation::$mode = $this->originalMode;
    }

    #[WithoutErrorHandler]
    public function testEmitInWarningModeTriggersDeprecation(): void
    {
        Deprecation::$mode = DeprecationMode::Warning;

        $triggered = null;
        set_error_handler(function (int $errno, string $errstr) use (&$triggered): bool {
            $triggered = ['errno' => $errno, 'errstr' => $errstr];
            return true;
        });

        try {
            Deprecation::emit('test message');
        } finally {
            restore_error_handler();
        }

        self::assertNotNull($triggered, 'Deprecation should have been triggered');
        self::assertSame(E_USER_DEPRECATED, $triggered['errno'], 'Error level should be E_USER_DEPRECATED');
        self::assertSame('Deprecated: test message', $triggered['errstr'], 'Error message should match');
    }

    public function testEmitInErrorModeThrowsErrorException(): void
    {
        Deprecation::$mode = DeprecationMode::Error;

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Deprecated: test message');

        Deprecation::emit('test message');
    }

    public function testErrorExceptionHasCorrectSeverity(): void
    {
        Deprecation::$mode = DeprecationMode::Error;

        try {
            Deprecation::emit('test message');
            self::fail('Expected ErrorException to be thrown');
        } catch (ErrorException $e) {
            self::assertSame(
                E_USER_DEPRECATED,
                $e->getSeverity(),
                'ErrorException severity should be E_USER_DEPRECATED',
            );
        }
    }
}
