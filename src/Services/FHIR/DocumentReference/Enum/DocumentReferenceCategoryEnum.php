<?php
/*
 * DocumentReferenceCategoryEnum.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\DocumentReference\Enum;

enum DocumentReferenceCategoryEnum: string {
    case CLINICAL_NOTE = 'clinical-note';
    case ADVANCE_CARE_DIRECTIVE = '42348-3';  // LOINC code for Advance Care Directive
}
