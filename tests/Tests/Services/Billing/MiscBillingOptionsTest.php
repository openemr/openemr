<?php

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\MiscBillingOptions;
use PHPUnit\Framework\TestCase;

/**
 * Service tests for MiscBillingOptions class
 * This class uses the xl() translation function which requires database access
 */
class MiscBillingOptionsTest extends TestCase
{
    private $options;

    protected function setUp(): void
    {
        $this->options = new MiscBillingOptions();
    }

    public function testConstructorInitializesBox14QualifierOptions(): void
    {
        $this->assertIsArray($this->options->box_14_qualifier_options);
        $this->assertCount(2, $this->options->box_14_qualifier_options);

        // Verify structure
        $this->assertIsArray($this->options->box_14_qualifier_options[0]);
        $this->assertCount(2, $this->options->box_14_qualifier_options[0]);

        // Verify codes
        $this->assertEquals('431', $this->options->box_14_qualifier_options[0][1]);
        $this->assertEquals('484', $this->options->box_14_qualifier_options[1][1]);
    }

    public function testConstructorInitializesBox15QualifierOptions(): void
    {
        $this->assertIsArray($this->options->box_15_qualifier_options);
        $this->assertCount(9, $this->options->box_15_qualifier_options);

        // Verify codes exist
        $codes = array_column($this->options->box_15_qualifier_options, 1);
        $this->assertContains('454', $codes); // Initial Treatment
        $this->assertContains('304', $codes); // Latest Visit or Consultation
        $this->assertContains('453', $codes); // Acute Manifestation
        $this->assertContains('439', $codes); // Accident
        $this->assertContains('455', $codes); // Last X-ray
        $this->assertContains('471', $codes); // Prescription
        $this->assertContains('090', $codes); // Report Start
        $this->assertContains('091', $codes); // Report End
        $this->assertContains('444', $codes); // First Visit
    }

    public function testConstructorInitializesHcfaDateQuals(): void
    {
        $this->assertIsArray($this->options->hcfa_date_quals);
        $this->assertArrayHasKey('box_14_date_qual', $this->options->hcfa_date_quals);
        $this->assertArrayHasKey('box_15_date_qual', $this->options->hcfa_date_quals);

        $this->assertEquals(
            $this->options->box_14_qualifier_options,
            $this->options->hcfa_date_quals['box_14_date_qual']
        );

        $this->assertEquals(
            $this->options->box_15_qualifier_options,
            $this->options->hcfa_date_quals['box_15_date_qual']
        );
    }

    public function testQualIdToDescriptionBox14Valid(): void
    {
        // Test finding a valid box 14 qualifier
        $result = $this->options->qual_id_to_description('box_14_date_qual', '431');
        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function testQualIdToDescriptionBox15Valid(): void
    {
        // Test finding a valid box 15 qualifier
        $result = $this->options->qual_id_to_description('box_15_date_qual', '454');
        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function testQualIdToDescriptionInvalidQualType(): void
    {
        // Test with invalid qual_type
        $result = $this->options->qual_id_to_description('invalid_type', '431');
        $this->assertNull($result);
    }

    public function testQualIdToDescriptionInvalidValue(): void
    {
        // Test with invalid value
        $result = $this->options->qual_id_to_description('box_14_date_qual', '999');
        $this->assertNull($result);
    }

    public function testQualIdToDescriptionAllBox14Values(): void
    {
        // Test all box 14 values can be found
        foreach ($this->options->box_14_qualifier_options as $option) {
            $result = $this->options->qual_id_to_description('box_14_date_qual', $option[1]);
            $this->assertNotNull($result, "Failed to find description for code: {$option[1]}");
            $this->assertEquals($option[0], $result);
        }
    }

    public function testQualIdToDescriptionAllBox15Values(): void
    {
        // Test all box 15 values can be found
        foreach ($this->options->box_15_qualifier_options as $option) {
            $result = $this->options->qual_id_to_description('box_15_date_qual', $option[1]);
            $this->assertNotNull($result, "Failed to find description for code: {$option[1]}");
            $this->assertEquals($option[0], $result);
        }
    }

    public function testBox14QualifierOptionsStructure(): void
    {
        // Each option should be [description, code]
        foreach ($this->options->box_14_qualifier_options as $option) {
            $this->assertIsArray($option);
            $this->assertCount(2, $option);
            $this->assertIsString($option[0]); // Description
            $this->assertIsString($option[1]); // Code
            $this->assertNotEmpty($option[0]);
            $this->assertNotEmpty($option[1]);
        }
    }

    public function testBox15QualifierOptionsStructure(): void
    {
        // Each option should be [description, code]
        foreach ($this->options->box_15_qualifier_options as $option) {
            $this->assertIsArray($option);
            $this->assertCount(2, $option);
            $this->assertIsString($option[0]); // Description
            $this->assertIsString($option[1]); // Code
            $this->assertNotEmpty($option[0]);
            $this->assertNotEmpty($option[1]);
        }
    }

    public function testQualifierCodesAreUnique(): void
    {
        // Box 14 codes should be unique
        $box14Codes = array_column($this->options->box_14_qualifier_options, 1);
        $this->assertCount(count($box14Codes), array_unique($box14Codes));

        // Box 15 codes should be unique
        $box15Codes = array_column($this->options->box_15_qualifier_options, 1);
        $this->assertCount(count($box15Codes), array_unique($box15Codes));
    }

    public function testQualIdToDescriptionEmptyString(): void
    {
        $result = $this->options->qual_id_to_description('box_14_date_qual', '');
        $this->assertNull($result);
    }

    public function testQualIdToDescriptionCaseSensitive(): void
    {
        // Codes should be case-sensitive (they're numeric strings, but test anyway)
        $result = $this->options->qual_id_to_description('box_14_date_qual', '431');
        $this->assertNotNull($result);

        // The codes are numeric strings, so case doesn't really apply,
        // but we verify the exact match works
        $this->assertIsString($result);
    }
}
