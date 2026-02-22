<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

use Particle\Validator\Chain;
use Particle\Validator\Validator;

class InnerValidator extends Validator
{
    public function requiredForInsert(string $key, ?string $name, string $contextName): Chain
    {
        return $this->getChain(
            $key,
            $name,
            BaseValidator::DATABASE_INSERT_CONTEXT === $contextName,
            false
        );
    }
}
