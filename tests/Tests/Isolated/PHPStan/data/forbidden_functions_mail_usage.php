<?php

/**
 * Fixture for ForbiddenFunctionsRuleTest (the `mail` entry).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\data;

function notifyByOtherMeans(string $to, string $subject, string $body): bool
{
    // Not `mail()` — a plain user-defined function must be left alone.
    return $to !== '' && $subject !== '' && $body !== '';
}

function sendLegacyMail(string $to, string $subject, string $body, string $headers): bool
{
    return mail($to, $subject, $body, $headers);
}
