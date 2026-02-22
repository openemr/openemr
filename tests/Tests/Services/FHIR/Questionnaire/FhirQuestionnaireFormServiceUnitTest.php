<?php

/*
 * FhirQuestionnaireFormServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR\Questionnaire;

use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FHIR\Questionnaire\FhirQuestionnaireFormService;
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use PHPUnit\Framework\TestCase;

class FhirQuestionnaireFormServiceUnitTest extends TestCase
{
    public function testCreateProvenanceResource(): void
    {
        $this->markTestIncomplete("Not implemented yet");
    }

    public function testSupportsCode(): void
    {
        $loinCodes = ['1234-5', '6789-0', '1111-2', '2222-3'];
        $service = new FhirQuestionnaireFormService();
        foreach ($loinCodes as $code) {
            $this->assertTrue($service->supportsCode($code), "Service should support LOINC code: $code");
        }
    }

    public function testParseOpenEMRRecord(): void
    {
        $service = new FhirQuestionnaireFormService();
        $service->setSystemLogger(new SystemLogger(Level::Critical));
        $jsonQuestionnaire = file_get_contents(__DIR__ . '/../../../data/Services/FHIR/Questionnaire/questionnaire-sdc-pathology.json');
        $dataToParse = [
            'questionnaire' => $jsonQuestionnaire
            , 'source_url' => 'http://example.com/source'
            , 'uuid' => 'questionnaire-uuid-123'
        ];
        $parsedQuestionnaire = $service->parseOpenEMRRecord($dataToParse);
        $this->assertEquals($dataToParse['uuid'], $parsedQuestionnaire->getId()->getValue());
        $this->assertEquals('4.0.0', $parsedQuestionnaire->getVersion());
        $this->assertEquals($dataToParse['source_url'], $parsedQuestionnaire->getUrl());
        $this->assertEquals('QuestionnaireSDCProfileExampleCap', $parsedQuestionnaire->getName());
        $this->assertCount(2, $parsedQuestionnaire->getItem());
        $this->assertEquals("1", $parsedQuestionnaire->getItem()[0]->getLinkId());
        $this->assertEquals("2", $parsedQuestionnaire->getItem()[1]->getLinkId());
    }
}
