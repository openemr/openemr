<?php

/**
 * Fixture for ForbiddenSessionAllowWriteRuleTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\data;

function optIn(): bool
{
    $sessionAllowWrite = true;
    return $sessionAllowWrite;
}

function optInWithInt(): int
{
    $sessionAllowWrite = 1;
    return $sessionAllowWrite;
}

function optInConditionally(bool $write): bool
{
    $sessionAllowWrite = $write;
    return $sessionAllowWrite;
}

function stayReadOnly(): ?int
{
    // Always falsy, so the session stays read-only — the rule must not flag these.
    $sessionAllowWrite = false;
    $sessionAllowWrite = 0;
    $sessionAllowWrite = null;
    return $sessionAllowWrite;
}

function otherVariable(): bool
{
    // Not $sessionAllowWrite — the rule must leave other variables alone.
    $allowWrite = true;
    return $allowWrite;
}
