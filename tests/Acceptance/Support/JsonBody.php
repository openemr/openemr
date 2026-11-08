<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance\Support;

use Symfony\Component\BrowserKit\Response;

/**
 * Decode a JSON response body, keeping enough of the raw bytes in the
 * failure message to identify why it did not decode.
 *
 * `assertIsArray(json_decode(...))` on its own reports "Failed asserting
 * that null is of type array" and throws the body away, which says only
 * that the response was not the JSON the test wanted -- not whether it was
 * an HTML error page, an empty body, a truncated payload, or valid JSON
 * with something appended. Those need different fixes and the acceptance
 * suite runs against a container that is torn down at the end of the job,
 * so a body that is not in the failure output cannot be recovered later.
 * openemr#13905 was a week of guesswork for exactly this reason: the
 * bodies were correct JSON documents with a second document concatenated
 * onto the end, which the discarded suffix would have shown immediately.
 */
final class JsonBody
{
    /**
     * Bytes of the body to quote from each end. Large enough to show a
     * decode-breaking prefix or suffix, small enough that a 36 KB
     * CapabilityStatement does not bury the rest of the failure output.
     */
    private const EXCERPT_BYTES = 300;

    /**
     * @return array<array-key, mixed>|null Decoded object/array, or null when the body is not a JSON structure.
     */
    public static function decode(Response $response): ?array
    {
        $decoded = json_decode($response->getContent(), true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Human-readable diagnosis of a body that failed to decode, for use as
     * the tail of an assertion message.
     */
    public static function describe(Response $response): string
    {
        $body = $response->getContent();
        if ($body === '') {
            return 'Response body was empty.';
        }

        // Decode again rather than reading json_last_error_msg() from the
        // caller's decode(): that error state is global to the process and any
        // json_* call in between would silently replace it with an unrelated one.
        json_decode($body, true);

        return sprintf(
            'json_decode: %s. Body was %d bytes: %s',
            json_last_error_msg(),
            strlen($body),
            self::excerpt($body),
        );
    }

    private static function excerpt(string $body): string
    {
        if (strlen($body) <= self::EXCERPT_BYTES * 2) {
            return var_export($body, true);
        }

        return sprintf(
            '%s ...[%d bytes elided]... %s',
            var_export(substr($body, 0, self::EXCERPT_BYTES), true),
            strlen($body) - (self::EXCERPT_BYTES * 2),
            var_export(substr($body, -self::EXCERPT_BYTES), true),
        );
    }
}
