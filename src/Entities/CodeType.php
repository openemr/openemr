<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping;

/**
 * Important note: the underlying table has the PRIMARY KEY on the `ct_key`
 * field and a separate UNIQUE on `ct_id` (despite `codes` referencing id, as
 * per normal conventions). The Doctrine model reverses this to match common
 * expectations.
 *
 * The database should get updated to reverse this, matching model declaration.
 * See #12540.
 */
#[Mapping\Entity]
#[Mapping\Table(name: 'code_types')]
#[Mapping\UniqueConstraint(fields: ['key'])]
class CodeType
{
    #[Mapping\Id]
    #[Mapping\Column(name: 'ct_id', type: Types::INTEGER)]
    public readonly int $id; // @phpstan-ignore property.uninitializedReadonly (Doctrine hydrates via reflection)

    #[Mapping\Column(name: 'ct_key', length: 15)]
    public string $key;

    #[Mapping\Column(name: 'ct_seq', type: Types::INTEGER)]
    public int $seq;

    #[Mapping\Column(name: 'ct_active', type: Types::BOOLEAN)]
    public bool $active;
}
