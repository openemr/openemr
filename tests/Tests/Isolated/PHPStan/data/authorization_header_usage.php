<?php

/**
 * Fixture for ForbiddenAuthorizationHeaderReadRuleTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\data;

use Symfony\Component\HttpFoundation\Request;

function readAuthorizationHeader(Request $request): ?string
{
    return $request->headers->get('Authorization');
}

function readAuthorizationHeaderLowercase(Request $request): ?string
{
    return $request->headers->get('authorization');
}

function readOtherHeader(Request $request): ?string
{
    // Not Authorization — this rule must leave other header reads alone.
    return $request->headers->get('Content-Type');
}

function readOtherBagValue(Request $request): ?string
{
    // Not a ->headers call — the rule narrows to Symfony HeaderBag reads.
    return $request->query->get('Authorization');
}

function dummyGetterCallWithAuthString(object $bag): mixed
{
    // Call shape matches ->get('Authorization') but not on ->headers.
    // Must not be flagged.
    return $bag->get('Authorization');
}
