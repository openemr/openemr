<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\System;

use OpenEMR\Common\System\EnvironmentVariable;
use PHPUnit\Framework\TestCase;

class EnvironmentVariableTest extends TestCase
{
    private const NAME = 'OPENEMR_ENVIRONMENT_VARIABLE_TEST';

    private ?string $original = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->original = EnvironmentVariable::get(self::NAME);
    }

    protected function tearDown(): void
    {
        $original = $this->original;
        self::assertTrue(putenv($original === null ? self::NAME : self::NAME . '=' . $original));

        parent::tearDown();
    }

    public function testUnsetVariableIsNull(): void
    {
        self::assertTrue(putenv(self::NAME));

        self::assertNull(EnvironmentVariable::get(self::NAME));
    }

    public function testSetVariableIsReturned(): void
    {
        self::assertTrue(putenv(self::NAME . '=value'));

        self::assertSame('value', EnvironmentVariable::get(self::NAME));
    }

    public function testEmptyVariableIsAnEmptyString(): void
    {
        self::assertTrue(putenv(self::NAME . '='));

        self::assertSame('', EnvironmentVariable::get(self::NAME));
    }
}
