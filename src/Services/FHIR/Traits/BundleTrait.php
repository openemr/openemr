<?php

/**
 * BundleTrait.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Luis M. Santos, M.D. <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 Luis M. Santos, M.D. <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 MedicalMasses L.L.C. <contact@medicalmasses.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;

use OpenEMR\Validators\ProcessingResult;
use Psr\Log\LoggerInterface;

trait BundleTrait
{
    private function insertResource($object, LoggerInterface $systemLogger): ProcessingResult {

    }
}
