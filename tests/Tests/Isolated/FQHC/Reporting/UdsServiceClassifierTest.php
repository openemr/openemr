<?php

/**
 * Isolated tests for the OpenEMR encounter → UDS service category classifier.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Reporting\UdsServiceCategory;
use OpenEMR\FQHC\Reporting\UdsServiceClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UdsServiceClassifierTest extends TestCase
{
    #[DataProvider('categoryProvider')]
    public function testClassifyByEncounterCategory(?string $constantId, UdsServiceCategory $expected): void
    {
        self::assertSame($expected, (new UdsServiceClassifier())->classifyByEncounterCategory($constantId));
    }

    /**
     * Constant ids confirmed against the openemr_postcalendar_categories seed
     * and the comlink telehealth module.
     *
     * @return array<string, array{?string, UdsServiceCategory}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function categoryProvider(): array
    {
        return [
            'ophthalmology is vision' => ['ophthalmological_services', UdsServiceCategory::Vision],
            'health & behavioral assessment is mental health' => ['health_and_behavioral_assessment', UdsServiceCategory::MentalHealth],
            'group therapy is mental health' => ['group_therapy', UdsServiceCategory::MentalHealth],
            'office visit is medical' => ['office_visit', UdsServiceCategory::Medical],
            'preventive care is medical' => ['preventive_care_services', UdsServiceCategory::Medical],
            'established patient is medical' => ['established_patient', UdsServiceCategory::Medical],
            'new patient is medical' => ['new_patient', UdsServiceCategory::Medical],
            'telehealth new patient is medical' => ['comlink_telehealth_new_patient', UdsServiceCategory::Medical],
            'telehealth established is medical' => ['comlink_telehealth_established_patient', UdsServiceCategory::Medical],
            'unknown category is other professional' => ['some_custom_category', UdsServiceCategory::OtherProfessional],
            'null category is other professional' => [null, UdsServiceCategory::OtherProfessional],
        ];
    }
}
