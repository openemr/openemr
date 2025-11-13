<?php

/**
 * OpenEMRChain - OpenEMR specific validator chain.
 * This class extends the Particle\Validator\Chain class to provide OpenEMR specific validation rules.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

use OpenEMR\Validators\Rules\ListOptionRule;
use Particle\Validator\Chain;

class OpenEMRChain extends Chain
{
    public function listOption($listId): self
    {
        return $this->addRule(new ListOptionRule($listId));
    }
}
