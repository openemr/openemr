<?php

/**
 * ConnectionConfig Unit Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR <warp@agent.dev>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Admin;

use OpenEMR\Admin\ValueObjects\ConnectionConfig;
use PHPUnit\Framework\TestCase;

class ConnectionConfigTest extends TestCase
{
    public function testCreatesWithDefaultValues(): void
    {
        $config = new ConnectionConfig();

        $this->assertSame(3, $config->getMaxRetries());
        $this->assertSame(100000, $config->getRetryDelayMicros());
    }

    public function testCreatesWithCustomValues(): void
    {
        $config = new ConnectionConfig(5, 200000);

        $this->assertSame(5, $config->getMaxRetries());
        $this->assertSame(200000, $config->getRetryDelayMicros());
    }

    public function testRejectsInvalidMaxRetries(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maxRetries must be at least 1');

        new ConnectionConfig(0, 100000);
    }

    public function testRejectsNegativeRetryDelay(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('retryDelayMicros must be non-negative');

        new ConnectionConfig(3, -1);
    }

    public function testAcceptsZeroRetryDelay(): void
    {
        $config = new ConnectionConfig(3, 0);

        $this->assertSame(0, $config->getRetryDelayMicros());
    }
}
