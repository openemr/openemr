<?php

/**
 * Base cache exception for OpenEMR cache backends.
 *
 * Implements the PSR-16 marker interface so callers can catch cache
 * failures via the standard contract.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Cache;

use Psr\SimpleCache\CacheException as PsrCacheException;

final class CacheException extends \RuntimeException implements PsrCacheException
{
}
