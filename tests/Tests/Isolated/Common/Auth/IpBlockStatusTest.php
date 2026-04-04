<?php

/**
 * Isolated unit tests for IpBlockStatus value object.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth;

use OpenEMR\Common\Auth\IpBlockStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IpBlockStatus::class)]
final class IpBlockStatusTest extends TestCase
{
    public function testAllowedFactoryReturnsAllowedStatus(): void
    {
        $status = IpBlockStatus::allowed();

        self::assertTrue($status->allowed);
        self::assertFalse($status->forceBlocked);
        self::assertFalse($status->skipTimingAttack);
        self::assertFalse($status->requiresEmailNotification);
    }

    public function testBlockedWithForceBlock(): void
    {
        $status = IpBlockStatus::blocked(
            forceBlocked: true,
            skipTimingAttack: false,
            requiresEmailNotification: false,
        );

        self::assertFalse($status->allowed);
        self::assertTrue($status->forceBlocked);
        self::assertFalse($status->skipTimingAttack);
        self::assertFalse($status->requiresEmailNotification);
    }

    public function testBlockedWithSkipTimingAttack(): void
    {
        $status = IpBlockStatus::blocked(
            forceBlocked: true,
            skipTimingAttack: true,
            requiresEmailNotification: false,
        );

        self::assertFalse($status->allowed);
        self::assertTrue($status->forceBlocked);
        self::assertTrue($status->skipTimingAttack);
        self::assertFalse($status->requiresEmailNotification);
    }

    public function testBlockedByAutoBlockWithEmailNotification(): void
    {
        $status = IpBlockStatus::blocked(
            forceBlocked: false,
            skipTimingAttack: false,
            requiresEmailNotification: true,
        );

        self::assertFalse($status->allowed);
        self::assertFalse($status->forceBlocked);
        self::assertFalse($status->skipTimingAttack);
        self::assertTrue($status->requiresEmailNotification);
    }

    public function testBlockedByAutoBlockWithoutEmailNotification(): void
    {
        $status = IpBlockStatus::blocked(
            forceBlocked: false,
            skipTimingAttack: false,
            requiresEmailNotification: false,
        );

        self::assertFalse($status->allowed);
        self::assertFalse($status->forceBlocked);
        self::assertFalse($status->skipTimingAttack);
        self::assertFalse($status->requiresEmailNotification);
    }
}
