<?php

/**
 * A fragment of a WHERE clause with the values its placeholders bind, in order.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Dates;

final readonly class SqlCondition
{
    /**
     * @param string            $sql   the fragment, with one ? per bound value
     * @param list<string|int|null> $binds
     */
    public function __construct(
        public string $sql,
        public array $binds,
    ) {
    }
}
