<?php

/**
 * Deterministic PSR-20 clock for testing time-dependent code.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Token;

use Lcobucci\Clock\Clock;

final class FakeClock implements Clock
{
    public function __construct(
        private \DateTimeImmutable $now,
    ) {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function advance(\DateInterval $interval): void
    {
        $this->now = $this->now->add($interval);
    }

    public function setNow(\DateTimeImmutable $now): void
    {
        $this->now = $now;
    }
}
