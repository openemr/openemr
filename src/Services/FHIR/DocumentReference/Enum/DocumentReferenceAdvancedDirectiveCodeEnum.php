<?php
/*
 * DocumentReferenceAdvancedDirectiveCodeEnum.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\DocumentReference\Enum;

use OpenEMR\Services\FHIR\FhirCodeSystemConstants;

enum DocumentReferenceAdvancedDirectiveCodeEnum: string {

    case MENTAL_HEALTH_DIRECTIVE = '104144-1'; // LOINC code for Mental Health Advance Directive
    case LIVING_WILL = '86533-7';
    case DURABLE_POWER_OF_ATTORNEY = '64298-3';
    case DO_NOT_RESUSCITATE_ORDER = '84095-9';
    case ADVANCE_DIRECTIVE = '42348-3';

    public function getDescription() {
        return match($this) {
            self::MENTAL_HEALTH_DIRECTIVE => 'Mental Health Advance Directive',
            self::LIVING_WILL => 'Patient Living will',
            self::DURABLE_POWER_OF_ATTORNEY => 'Power of attorney',
            self::DO_NOT_RESUSCITATE_ORDER => 'Do not resuscitate',
            self::ADVANCE_DIRECTIVE => 'Advance directive',
        };
    }

    public function getSystem(): string {
        return FhirCodeSystemConstants::LOINC;
    }

    public function getSystemOid(): string {
        return '2.16.840.1.113883.6.1'; // LOINC system
    }

    public function getCodeType(): string {
        return 'LOINC';
    }

    public function getFullCodeString(): string {
        return $this->getCodeType() . '|' . $this->value;
    }

    public static function getFullOpenEMRCodeList(): array {
        $list = [];
        foreach (self::cases() as $case) {
            $list[] = $case->getCodeType() . ":" . $case->value;
        }
        return $list;
    }

    public static function getFullCodeWithSystemList(): array {
        $list = [];
        foreach (self::cases() as $case) {
            $list[] = $case->getSystem() . '|' . $case->value;
        }
        return $list;
    }
}
