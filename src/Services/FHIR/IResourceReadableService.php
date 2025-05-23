<?php

/**
 * IResourceReadableService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Validators\ProcessingResult;

interface IResourceReadableService
{
    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult;

    public function getOne($fhirResourceId, $puuidBind = null): ProcessingResult;
}
