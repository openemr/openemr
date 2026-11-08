<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance\Support;

use Symfony\Component\BrowserKit\Response;

/**
 * BrowserKit's Response::getHeader() returns array|string|null depending
 * on the header's arity and presence. Rather than repeat the narrowing
 * dance at every call site, funnel through these helpers.
 *
 * Kept as a static-method utility rather than a value object because
 * every acceptance test needs it in the same shape; instantiating a
 * wrapper adds ceremony without any state to hold.
 */
final class ResponseHeaders
{
    public static function location(Response $response): string
    {
        return self::first($response, 'Location');
    }

    public static function first(Response $response, string $name): string
    {
        /** @var array<int, string>|string|null $value */
        $value = $response->getHeader($name);
        if (is_array($value)) {
            return $value[0] ?? '';
        }
        return $value ?? '';
    }
}
