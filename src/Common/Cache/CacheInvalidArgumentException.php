<?php

/**
 * Thrown when a cache key is invalid per PSR-16 rules.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Cache;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

final class CacheInvalidArgumentException extends \InvalidArgumentException implements PsrInvalidArgumentException
{
}
