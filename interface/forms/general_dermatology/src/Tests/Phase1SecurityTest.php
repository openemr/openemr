<?php

declare(strict_types=1);

namespace GeneralDermatology\Tests;

use GeneralDermatology\App\GeneralDermatologyClaimProcessor;
use GeneralDermatology\Services\TemplateFormService;
use GeneralDermatology\Support\NoteAccessService;
use PHPUnit\Framework\TestCase;

/**
 * Phase 1 security/integrity unit tests (no DB required for pure helpers).
 */
class Phase1SecurityTest extends TestCase
{
    public function testNormalizeClaimRowAcceptsLegacyQuotedKeys(): void
    {
        $row = [
            "'code'" => '11102',
            "'description'" => 'Tangential biopsy',
            "'modifier'" => '59',
            "'justify'" => ['L82.1', '0'],
        ];

        $normalized = GeneralDermatologyClaimProcessor::normalizeClaimRow($row);

        $this->assertSame('11102', $normalized['code']);
        $this->assertSame('Tangential biopsy', $normalized['description']);
        $this->assertSame('59', $normalized['modifier']);
        $this->assertSame(['L82.1'], $normalized['justify']);
    }

    public function testNormalizeClaimRowAcceptsModernKeys(): void
    {
        $row = [
            'code' => '17000',
            'description' => 'Destruction',
            'modifier' => '',
            'justify' => ['L57.0'],
        ];

        $normalized = GeneralDermatologyClaimProcessor::normalizeClaimRow($row);
        $this->assertSame('17000', $normalized['code']);
        $this->assertSame(['L57.0'], $normalized['justify']);
    }

    public function testCreateJustificationBuildsPipeList(): void
    {
        $this->assertSame('', GeneralDermatologyClaimProcessor::createJustification([]));
        $this->assertSame('ICD10|L82.1', GeneralDermatologyClaimProcessor::createJustification(['L82.1']));
        $this->assertSame(
            'ICD10|L82.1,ICD10|L57.0',
            GeneralDermatologyClaimProcessor::createJustification(['L82.1', 'L57.0'])
        );
    }

    public function testPatientOwnsRowRejectsTemplatesAndWrongPid(): void
    {
        $this->assertFalse(NoteAccessService::rowBelongsToPatient([
            'pid' => 100,
            'general_dermatology_form_template_name' => 'FBSE',
        ], 100));

        $this->assertFalse(NoteAccessService::rowBelongsToPatient([
            'pid' => 999,
            'general_dermatology_form_template_name' => '',
        ], 100));

        $this->assertTrue(NoteAccessService::rowBelongsToPatient([
            'pid' => 100,
            'general_dermatology_form_template_name' => '',
        ], 100));
    }

    public function testTemplateNameValidationStillWorks(): void
    {
        $this->assertTrue(TemplateFormService::isValidTemplateName('Biopsy defaults'));
        $this->assertFalse(TemplateFormService::isValidTemplateName('x'));
    }
}
