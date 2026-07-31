<?php

/**
 * OneTimeAuthExceptionTest.
 *
 * Covers the constructors of the one-time auth exceptions, in particular that a
 * real \Throwable is accepted as $previous. Before the type was qualified it
 * resolved to OpenEMR\Common\Auth\Exception\Throwable, so passing a throwable
 * failed the parameter type check at runtime.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Exception;

use OpenEMR\Common\Auth\Exception\OneTimeAuthException;
use OpenEMR\Common\Auth\Exception\OneTimeAuthExpiredException;
use PHPUnit\Framework\TestCase;

class OneTimeAuthExceptionTest extends TestCase
{
    public function testConstructorAcceptsAThrowableAsPrevious(): void
    {
        $previous = new \RuntimeException('underlying failure');

        $exception = new OneTimeAuthException('one time auth failed', 42, 7, $previous);

        $this->assertSame('one time auth failed', $exception->getMessage());
        $this->assertSame(42, $exception->getPid());
        $this->assertSame(7, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructorDefaultsPidToNull(): void
    {
        $exception = new OneTimeAuthException();

        $this->assertSame('', $exception->getMessage());
        $this->assertNull($exception->getPid());
        $this->assertNull($exception->getPrevious());
    }

    public function testExpiredExceptionForwardsEveryArgumentToItsParent(): void
    {
        $previous = new \LogicException('expired');

        $exception = new OneTimeAuthExpiredException('one time auth expired', 13, 3, $previous);

        $this->assertSame('one time auth expired', $exception->getMessage());
        $this->assertSame(13, $exception->getPid());
        $this->assertSame(3, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
