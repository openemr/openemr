<?php

/**
 * The HL7 v3 ActCode coverage types a FHIR Coverage's type is sent with.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\Enum;

enum CoverageTypeActCode: string
{
    case HealthInsurancePlan = 'HIP';
    case PublicHealthcare = 'PUBLICPOL';

    /**
     * The code system's display for the code.
     */
    public function display(): string
    {
        return match ($this) {
            self::HealthInsurancePlan => 'health insurance plan policy',
            self::PublicHealthcare => 'public healthcare',
        };
    }
}
