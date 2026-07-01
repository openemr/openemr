<?php

/**
 * Classifies an OpenEMR encounter into a UDS Table 5 service category.
 *
 * The input is the encounter's calendar-category machine id
 * (`openemr_postcalendar_categories.pc_constant_id`), the most reliable stock
 * signal of service type. This is the out-of-the-box default mapping; a center
 * refines it with per-category config and provider taxonomy/specialty later.
 *
 * Known limitation: stock OpenEMR ships no Dental or SUD calendar category, so
 * those categories are not reachable from `pc_constant_id` alone and require the
 * deferred taxonomy/specialty signal. An unrecognised or missing category falls
 * back to Other professional rather than being dropped — every countable visit
 * lands somewhere. The caller must have already excluded non-visit placeholder
 * categories (no_show, lunch, …) at the query boundary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class UdsServiceClassifier
{
    public function classifyByEncounterCategory(?string $constantId): UdsServiceCategory
    {
        return match ($constantId) {
            'ophthalmological_services' => UdsServiceCategory::Vision,
            'health_and_behavioral_assessment', 'group_therapy' => UdsServiceCategory::MentalHealth,
            'office_visit',
            'preventive_care_services',
            'established_patient',
            'new_patient',
            'comlink_telehealth_new_patient',
            'comlink_telehealth_established_patient' => UdsServiceCategory::Medical,
            default => UdsServiceCategory::OtherProfessional,
        };
    }
}
