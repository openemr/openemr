<?php

/**
 * OpenEMRParticleValidator - OpenEMR specific validator.
 * This class extends the Particle\Validator\Validator class to provide OpenEMR specific validation rules.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

use Particle\Validator\Validator;

class OpenEMRParticleValidator extends Validator
{
    protected function buildChain($key, $name, $required, $allowEmpty)
    {
        return new OpenEMRChain($key, $name, $required, $allowEmpty);
    }
}
