<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Entities\Attributes;

use Attribute;

/**
 * Marks a UuidInterface property to be automatically set on entity
 * creation.
 *
 * Usage:
 *     #[Mapping\Column]
 *     #[Uuid]
 *     public readonly UuidInterface $uuid;
 *
 * @see \OpenEMR\Entities\EventSubscriber\AutoValueSubscriber
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Uuid
{
}
