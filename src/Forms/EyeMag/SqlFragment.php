<?php

/**
 * A parameterized SQL fragment: a snippet of SQL plus the binds it needs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

/**
 * Keeps a conditional SQL snippet and its bind values together so callers
 * cannot append one without the other.
 */
final readonly class SqlFragment
{
    /**
     * @param list<string|int> $params
     */
    public function __construct(
        public string $sql,
        public array $params = [],
    ) {
    }
}
