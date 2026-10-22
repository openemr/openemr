<?php

/**
 * How SELECT audit logging is written when that logging is on.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging;

/**
 * Statement keeps a row for every SELECT. Request keeps one summary of the
 * user action. An unrecognized stored value stays on statement so a bad
 * setting does not drop the per-query record.
 */
enum AuditQueryDetail: string
{
    case Statement = 'statement';
    case Request = 'request';

    public static function fromGlobal(string $value): self
    {
        return self::tryFrom($value) ?? self::Statement;
    }
}
