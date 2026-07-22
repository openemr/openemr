<?php

/**
 * SmartLaunchControllerTest unit tests the questionnaire SMART launch context
 * validation and the continue/review/start action resolution.
 *
 * These tests exercise only the pure, pre-database validation paths of
 * SmartLaunchController via reflection; no session, ACL, or database
 * access occurs.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\FHIR\SMART;

use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SmartLaunchControllerTest extends TestCase
{
    /**
     * @param list<mixed> $args
     */
    private function invokePrivate(string $method, array $args): mixed
    {
        $controller = new SmartLaunchController();
        $reflection = new \ReflectionMethod(SmartLaunchController::class, $method);
        return $reflection->invokeArgs($controller, $args);
    }

    private function makeToken(?string $intent): SMARTLaunchToken
    {
        $token = new SMARTLaunchToken();
        if ($intent !== null) {
            $token->setIntent($intent);
        }
        return $token;
    }

    public function testNoQuestionnaireContextWithOtherIntentIsIgnored(): void
    {
        $token = $this->makeToken(SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG);
        $this->invokePrivate('addQuestionnaireLaunchContext', [$token, [], 1]);
        $this->assertSame([], $token->getFhirContext(), "Non-questionnaire launch should not gain FHIR context");
        $this->assertNull($token->getAppContext(), "Non-questionnaire launch should not gain app context");
    }

    public function testAssessmentIntentWithoutQuestionnaireContextThrows(): void
    {
        $token = $this->makeToken(SMARTLaunchToken::INTENT_QUESTIONNAIRE_ASSESSMENT);
        $this->expectException(\InvalidArgumentException::class);
        $this->invokePrivate('addQuestionnaireLaunchContext', [$token, [], 1]);
    }

    public function testQuestionnaireContextWithoutAssessmentIntentThrows(): void
    {
        $token = $this->makeToken(SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG);
        $this->expectException(\InvalidArgumentException::class);
        $this->invokePrivate('addQuestionnaireLaunchContext', [$token, ['questionnaire_id' => '5'], 1]);
    }

    public function testInvalidQuestionnaireIdThrows(): void
    {
        $token = $this->makeToken(SMARTLaunchToken::INTENT_QUESTIONNAIRE_ASSESSMENT);
        $this->expectException(\InvalidArgumentException::class);
        $this->invokePrivate('addQuestionnaireLaunchContext', [$token, ['questionnaire_id' => 'abc'], 1]);
    }

    public function testInvalidQuestionnaireResponseIdThrows(): void
    {
        $token = $this->makeToken(SMARTLaunchToken::INTENT_QUESTIONNAIRE_ASSESSMENT);
        $this->expectException(\InvalidArgumentException::class);
        $this->invokePrivate(
            'addQuestionnaireLaunchContext',
            [$token, ['questionnaire_id' => '5', 'questionnaire_response_id' => 'abc'], 1]
        );
    }

    public function testMissingPatientIdThrows(): void
    {
        $token = $this->makeToken(SMARTLaunchToken::INTENT_QUESTIONNAIRE_ASSESSMENT);
        $this->expectException(\InvalidArgumentException::class);
        $this->invokePrivate('addQuestionnaireLaunchContext', [$token, ['questionnaire_id' => '5'], null]);
    }

    #[DataProvider('positiveIntegerProvider')]
    public function testGetPositiveInteger(mixed $value, ?int $expected): void
    {
        $this->assertSame($expected, $this->invokePrivate('getPositiveInteger', [$value]));
    }

    /**
     * @return array<string, array{mixed, ?int}>
     */
    public static function positiveIntegerProvider(): array
    {
        return [
            'positive int' => [5, 5],
            'zero int' => [0, null],
            'negative int' => [-3, null],
            'digit string' => ['12', 12],
            'zero string' => ['0', null],
            'negative string' => ['-3', null],
            'non-numeric string' => ['abc', null],
            'empty string' => ['', null],
            'null' => [null, null],
            'float' => [5.5, null],
            'bool' => [true, null],
            'array' => [[], null],
            'overflow string' => ['99999999999999999999999999', null],
        ];
    }

    /**
     * @param array<string, string> $expected
     */
    #[DataProvider('questionnaireContextItemProvider')]
    public function testBuildQuestionnaireContextItem(?string $json, array $expected): void
    {
        $item = $this->invokePrivate('buildQuestionnaireContextItem', ['q-uuid', $json]);
        $this->assertSame($expected, $item);
    }

    /**
     * @return array<string, array{?string, array<string, string>}>
     */
    public static function questionnaireContextItemProvider(): array
    {
        $base = ['type' => 'Questionnaire', 'reference' => 'Questionnaire/q-uuid'];
        return [
            'no stored json' => [null, $base],
            'empty json' => ['', $base],
            'invalid json' => ['{not json', $base],
            'json without url' => ['{"resourceType":"Questionnaire"}', $base],
            'invalid url' => ['{"url":"not a url"}', $base],
            'url without version' => [
                '{"url":"https://example.org/Questionnaire/123"}',
                $base + ['canonical' => 'https://example.org/Questionnaire/123'],
            ],
            'url with version gets pipe suffix' => [
                '{"url":"https://example.org/Questionnaire/123","version":"v2023-05-03"}',
                $base + ['canonical' => 'https://example.org/Questionnaire/123|v2023-05-03'],
            ],
            'non-string version ignored' => [
                '{"url":"https://example.org/Questionnaire/123","version":7}',
                $base + ['canonical' => 'https://example.org/Questionnaire/123'],
            ],
        ];
    }

    #[DataProvider('appContextActionProvider')]
    public function testBuildQuestionnaireAppContextAction(?int $responseId, string $status, string $expectedAction): void
    {
        $json = $this->invokePrivate('buildQuestionnaireAppContext', [$responseId, $status]);
        $this->assertIsString($json);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);
        $this->assertSame('questionnaire-assessment', $decoded['workflow'] ?? null);
        $this->assertSame('patient-fhir-assessments', $decoded['returnContext'] ?? null);
        $this->assertSame($expectedAction, $decoded['action'] ?? null);
    }

    /**
     * @return array<string, array{?int, string, string}>
     */
    public static function appContextActionProvider(): array
    {
        return [
            'no response starts fresh' => [null, '', 'start'],
            'fhir in-progress continues' => [7, 'in-progress', 'continue'],
            'legacy incomplete continues' => [7, 'incomplete', 'continue'],
            'legacy active continues' => [7, 'active', 'continue'],
            'completed reviews' => [7, 'completed', 'review'],
            'amended reviews' => [7, 'amended', 'review'],
            'unknown status reviews' => [7, 'weird-status', 'review'],
            'empty status reviews' => [7, '', 'review'],
        ];
    }
}
