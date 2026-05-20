<?php

/**
 * Thrown by {@see SsrfSafeHttpClient} when the configured
 * {@see OidcUrlValidator} rejects the destination of an outbound HTTP
 * request — typically because the host resolves to a private/loopback/
 * link-local address, fails the scheme/userinfo policy, or cannot be
 * resolved at all. Implements PSR-18's `ClientExceptionInterface` so
 * existing call sites that catch PSR-18 exceptions handle this uniformly.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Discovery;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

final class SsrfBlockedException extends RuntimeException implements ClientExceptionInterface
{
}
