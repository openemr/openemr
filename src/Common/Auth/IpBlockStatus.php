<?php

/**
 * Value object representing the result of an IP-based login rate limit check.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth;

final readonly class IpBlockStatus
{
    private function __construct(
        public bool $allowed,
        public bool $forceBlocked,
        public bool $skipTimingAttack,
        public bool $requiresEmailNotification,
    ) {
    }

    public static function allowed(): self
    {
        return new self(
            allowed: true,
            forceBlocked: false,
            skipTimingAttack: false,
            requiresEmailNotification: false,
        );
    }

    public static function blocked(
        bool $forceBlocked,
        bool $skipTimingAttack,
        bool $requiresEmailNotification,
    ): self {
        return new self(
            allowed: false,
            forceBlocked: $forceBlocked,
            skipTimingAttack: $skipTimingAttack,
            requiresEmailNotification: $requiresEmailNotification,
        );
    }
}
