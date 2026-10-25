<?php

declare(strict_types=1);

namespace GeneralDermatology\Tests;

use GeneralDermatology\Services\TreatmentPlanService;
use PHPUnit\Framework\TestCase;

class TreatmentPlanServiceTest extends TestCase
{
    private TreatmentPlanService $treatmentPlanService;

    protected function setUp(): void
    {
        $this->treatmentPlanService = new TreatmentPlanService();
    }

    /**
     * Test generating HTML for a treatment plan using real storage field names.
     */
    public function testGeneratePlanHtml(): void
    {
        $plans = [
            [
                'finding_location' => 'Test Location',
                'dx_s' => 'L82.1',
                'dx_desc' => 'Test Diagnosis',
                'severity_drop' => '',
                'plan' => 'This is a test plan',
                'procedure_plans' => 'Biopsy',
                'areas_biopsy' => 'Test Biopsy Area',
                'biopsy-shave' => '1',
            ],
        ];

        $result = $this->treatmentPlanService->generatePlanHtml($plans);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('planHtmlBlocks', $result);
        $this->assertArrayHasKey('planCounter', $result);
        $this->assertArrayHasKey('html', $result);
        $this->assertEquals(1, $result['planCounter']);

        $html = $result['html'];
        $this->assertStringContainsString('Test Location', $html);
        $this->assertStringContainsString('Test Diagnosis', $html);
        $this->assertStringContainsString('This is a test plan', $html);
        $this->assertStringContainsString('page-break-inside: avoid', $html);
        $this->assertStringContainsString("class='plan-item'", $html);
    }

    /**
     * Test generating HTML with empty plans.
     */
    public function testGeneratePlanHtmlWithEmptyPlans(): void
    {
        $result = $this->treatmentPlanService->generatePlanHtml([]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('planHtmlBlocks', $result);
        $this->assertArrayHasKey('planCounter', $result);
        $this->assertEquals(0, $result['planCounter']);
        $this->assertEmpty($result['planHtmlBlocks']);
    }
}
