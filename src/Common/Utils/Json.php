<?php

/**
 * JSON encoding and decoding that throws on failure instead of returning false or null.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Utils;

final class Json
{
    /**
     * JSON_PARTIAL_OUTPUT_ON_ERROR is ignored, since it would turn a failure into partial output.
     *
     * @throws \JsonException when the value cannot be encoded
     */
    public static function encode(mixed $value, int $flags = 0): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | ($flags & ~JSON_PARTIAL_OUTPUT_ON_ERROR));
    }

    /**
     * Decodes objects as associative arrays.
     *
     * @throws \JsonException when the text is not valid JSON
     */
    public static function decode(string $json): mixed
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
