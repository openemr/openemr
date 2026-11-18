<?php

/**
 * JsonUtils - JSON decoding that throws instead of returning null on bad input.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Utils;

final class JsonUtils
{
    /**
     * Decodes JSON objects as associative arrays.
     *
     * @throws \JsonException when $json is not valid JSON
     */
    public static function decode(string $json): mixed
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
