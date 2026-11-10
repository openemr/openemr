<?php

/**
 * A code system as an ELM CodeSystemDef gives it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final readonly class CodeSystem
{
    public function __construct(
        public string $id,
        public ?string $version = null,
        public ?string $name = null,
    ) {
    }
}
