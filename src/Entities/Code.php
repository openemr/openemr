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

#[Mapping\Entity]
#[Mapping\Table(name: 'codes')]
class Code
{
    #[Mapping\Id]
    #[Mapping\GeneratedValue]
    #[Mapping\Column(type: Types::INTEGER)]
    public readonly int $id; // @phpstan-ignore property.uninitializedReadonly (Doctrine hydrates via reflection)

    #[Mapping\Column(length: 25)]
    public string $code;

    #[Mapping\Column(type: Types::TEXT, nullable: true)]
    public ?string $codeText = null;

    #[Mapping\Column(type: Types::SMALLINT, nullable: true)]
    public ?int $codeType = null;
}
