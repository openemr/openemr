<?php

/**
 * Fixture for ForbiddenSessionSuperglobalRuleTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\data;

function readAuthUser(): mixed
{
    return $_SESSION['authUser'] ?? null;
}

function writeAuthUser(string $user): void
{
    $_SESSION['authUser'] = $user;
}

function readRequestGlobal(): mixed
{
    // Not $_SESSION — this rule must leave other superglobals alone.
    return $_GET['authUser'] ?? null;
}
