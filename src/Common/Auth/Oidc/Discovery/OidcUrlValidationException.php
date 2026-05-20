<?php

/**
 * Thrown by {@see OidcUrlValidator} when a URL fails an SSRF safety check.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Discovery;

final class OidcUrlValidationException extends \RuntimeException
{
}
