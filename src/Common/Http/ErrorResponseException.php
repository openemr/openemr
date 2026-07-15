<?php

/**
 * Caller-side exception for error HTTP responses (>= 400).
 *
 * PSR-18 clients must not throw on 4xx/5xx, so this is opt-in for callers that
 * prefer exceptions over inspecting the status. It implements the base
 * ClientExceptionInterface rather than RequestExceptionInterface, which would
 * require the originating request; this only has the response.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

final class ErrorResponseException extends \RuntimeException implements ClientExceptionInterface
{
    public function __construct(
        public readonly ResponseInterface $response,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('HTTP request failed with status %d', $response->getStatusCode()),
            $response->getStatusCode(),
            $previous,
        );
    }

    public static function throwIfError(ResponseInterface $response): ResponseInterface
    {
        if ($response->getStatusCode() >= 400) {
            throw new self($response);
        }
        return $response;
    }
}
