<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption\Storage;

/**
 * Wrapper that adds type safety for low-level key material identifiers used by
 * the storage tooling.
 */
readonly final class KeyMaterialId
{
    public function __construct(public string $id)
    {
    }
}
