<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

/**
 * OpenEMRParticleValidator - OpenEMR specific validator.
 * This class extends the Particle\Validator\Validator class to provide OpenEMR specific validation rules.
 */
class OpenEMRParticleValidator extends InnerValidator
{
    protected function buildChain($key, $name, $required, $allowEmpty)
    {
        return new OpenEMRChain($key, $name, $required, $allowEmpty);
    }
}
