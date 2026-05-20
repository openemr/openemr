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

class RawRequestBodyReader
{
    private ?string $cache = null;

    public function __construct(private readonly string $streamIdentifier = 'php://input')
    {
    }

    /**
     * Returns the raw request body. The stream is read once per instance.
     *
     * @throws RawPostParserException if the stream cannot be read.
     */
    public function read(): string
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $raw = @file_get_contents($this->streamIdentifier);
        if ($raw === false) {
            throw new RawPostParserException(
                sprintf('Unable to read raw request body from %s', $this->streamIdentifier),
            );
        }

        return $this->cache = $raw;
    }
}
