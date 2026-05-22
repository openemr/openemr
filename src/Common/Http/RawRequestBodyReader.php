<?php

/**
 * RawRequestBodyReader — reads the raw HTTP request body once per instance
 * and caches the result.
 *
 * PHP's `file_get_contents('php://input')` has a `string|false` return type;
 * isolating that call here keeps the false-on-error API out of business code.
 * The reader also caches the body because `php://input` cannot be read more
 * than once in some SAPI configurations.
 *
 * Constructor accepts an optional stream identifier so tests can substitute a
 * fixture stream (e.g. `data://text/plain,foo=1&bar=2`) without polluting the
 * real request lifecycle.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Andrew Alanis <progradedteam@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Http;

final class RawRequestBodyReader
{
    private ?string $cache = null;

    public function __construct(private readonly string $streamIdentifier = 'php://input')
    {
    }

    /**
     * Returns the raw request body. The stream is read once per instance.
     *
     * @throws RawPostParserException if the stream cannot be read. The
     *         exception message includes the underlying PHP error message
     *         (via error_get_last) so failures on tempfile / data:// /
     *         open_basedir-restricted paths surface with a real reason
     *         rather than just the path that couldn't be opened.
     */
    public function read(): string
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $raw = file_get_contents($this->streamIdentifier);
        if ($raw === false) {
            $reason = error_get_last()['message'] ?? 'unknown error';
            throw new RawPostParserException(
                sprintf('Unable to read raw request body from %s: %s', $this->streamIdentifier, $reason),
            );
        }

        return $this->cache = $raw;
    }
}
