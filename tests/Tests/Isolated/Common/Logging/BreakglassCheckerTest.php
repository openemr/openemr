<?php

/**
 * Tests for BreakglassChecker
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging;

use Doctrine\DBAL\Connection;
use OpenEMR\Common\Logging\BreakglassChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BreakglassCheckerTest extends TestCase
{
    private Connection&MockObject $conn;

    protected function setUp(): void
    {
        $this->conn = $this->createMock(Connection::class);
    }

    public function testEmptyUserReturnsFalse(): void
    {
        $this->conn->expects($this->never())->method('fetchOne');

        $checker = new BreakglassChecker($this->conn);
        self::assertFalse($checker->isBreakglassUser(''));
    }

    public function testBreakglassUserReturnsTrue(): void
    {
        $this->conn->expects($this->once())
            ->method('fetchOne')
            ->with(self::anything(), ['breakglass', 'emergency_user'])
            ->willReturn('1');

        $checker = new BreakglassChecker($this->conn);
        self::assertTrue($checker->isBreakglassUser('emergency_user'));
    }

    public function testNonBreakglassUserReturnsFalse(): void
    {
        $this->conn->expects($this->once())
            ->method('fetchOne')
            ->with(self::anything(), ['breakglass', 'normal_user'])
            ->willReturn(false);

        $checker = new BreakglassChecker($this->conn);
        self::assertFalse($checker->isBreakglassUser('normal_user'));
    }

    public function testResultIsMemoized(): void
    {
        $this->conn->expects($this->once())
            ->method('fetchOne')
            ->willReturn('1');

        $checker = new BreakglassChecker($this->conn);

        // First call hits DB
        self::assertTrue($checker->isBreakglassUser('cached_user'));
        // Second call uses cache
        self::assertTrue($checker->isBreakglassUser('cached_user'));
    }

    public function testDifferentUsersQueriedSeparately(): void
    {
        $this->conn->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls('1', false);

        $checker = new BreakglassChecker($this->conn);

        self::assertTrue($checker->isBreakglassUser('breakglass_user'));
        self::assertFalse($checker->isBreakglassUser('normal_user'));
    }
}
