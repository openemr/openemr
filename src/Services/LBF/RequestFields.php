<?php

/**
 * Read a request field that must be a string.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\LBF;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Posted text, then the query string. A list is a bad request.
 */
final class RequestFields
{
    public static function string(Request $request, string $key): string
    {
        $posted = self::read($request->request->all(), $key);
        if ($posted === false) {
            throw new BadRequestException('Expected a string.');
        }
        if (is_string($posted)) {
            return $posted;
        }
        $query = self::read($request->query->all(), $key);
        if ($query === false) {
            throw new BadRequestException('Expected a string.');
        }

        return is_string($query) ? $query : '';
    }

    /**
     * @param array<mixed> $bag
     * @return string|false|null Null when the key is absent. False when it is not text.
     */
    private static function read(array $bag, string $key): string|false|null
    {
        if (!array_key_exists($key, $bag)) {
            return null;
        }
        $value = $bag[$key];

        return is_string($value) ? $value : false;
    }
}
