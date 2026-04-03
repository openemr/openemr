<?php

/**
 * Base cache exception for the OIDC cache layer.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Cache;

use Psr\SimpleCache\CacheException;

final class OidcCacheException extends \RuntimeException implements CacheException
{
}
