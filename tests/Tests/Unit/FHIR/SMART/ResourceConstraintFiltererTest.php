<?php
/*
 * ResourceConstraintFiltererTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\FHIR\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Validators\ScopeValidatorFactory;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\SMART\ResourceConstraintFilterer;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ResourceConstraintFiltererTest extends TestCase {

    public function testGetResourceValueForKey(): void
    {
        $surveyCategory = UtilsService::createCodeableConcept([
            'survey' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code' => 'survey',
                    'description' => 'Survey'
                ]
            ]
        ]);
        $socialHistoryCategory = UtilsService::createCodeableConcept([
            'social-history' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code' => 'social-history',
                    'description' => 'Social History'
                ]
            ]
        ]);

        $observation = new FHIRObservation();
        $observation->addCategory($surveyCategory);
        $observation->addCategory($socialHistoryCategory);
        $resourceConstraintFilterer = new ResourceConstraintFilterer();
        $categories = $resourceConstraintFilterer->getResourceValueForKey($observation, 'category');
        $this->assertIsArray($categories, "Categories should be returned as an array");
        $this->assertCount(2, $categories, "There should be two categories returned");
        foreach ($categories as $category) {
            $this->assertInstanceOf(FHIRCodeableConcept::class, $category, "Each category should be a CodeableConcept");
        }
    }

    public static function observationAccessProvider() : array {
        return [
            ['social-history', 'user/Observation.s', true, ['social-history', 'survey']],
            ['social-history', 'user/Observation.r', true, ['social-history', 'survey']],
            ['social-history', 'user/Observation.s', false, ['vital-signs']],
            ['laboratory', 'user/Observation.s', true, ['laboratory']],
            ['laboratory', 'user/Observation.s', true, ['laboratory', 'social-history']],
            ['laboratory', 'user/Observation.s', true, ['laboratory', 'vital-signs']],
            ['social-history', 'user/Observation.s', false, ['survey', 'vital-signs']],
        ];
    }

    #[DataProvider("observationAccessProvider")]
    public function testCanAccessResourceForObservation(string $searchParam, string $scope, bool $expectedAccessValue, array $observationCategories): void
    {
        // we need to have a scope entity that has the following constraints
        $scopeValidatorFactory = new ScopeValidatorFactory();
        $scopeValidatorArray = $scopeValidatorFactory->buildScopeValidatorArray([
             'user/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|laboratory'
            , 'user/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|social-history'
        ]);
//
//        Condition.rs?category=http://terminology.hl7.org/CodeSystem/condition-category|encounter-diagnosis
//        Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern
//        Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|laboratory
//        Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|social-history
        $httpRestRequest = HttpRestRequest::create('/fhir/Observation?category=' . $searchParam, 'GET');
        $httpRestRequest->setRequestRequiredScope(ScopeEntity::createFromString($scope));
        $httpRestRequest->setAccessTokenScopeValidationArray($scopeValidatorArray);
        $observation = $this->createObservationWithCategories($observationCategories);
        $resourceConstraintFilterer = new ResourceConstraintFilterer();
        $shouldBeClause = $expectedAccessValue ? "allowed" : "denied";
        $this->assertEquals($expectedAccessValue, $resourceConstraintFilterer->canAccessResource($observation, $httpRestRequest)
            , "Access should be " .$shouldBeClause . " for Observation with categories " . implode(',', $observationCategories));
    }

    public static function conditionAccessProvider() : array {
        return [
            ['encounter-diagnosis', 'user/Condition.s', true, 'encounter-diagnosis'],
            ['encounter-diagnosis', 'user/Condition.r', true, 'encounter-diagnosis'],
            ['health-concern', 'user/Condition.r', true, 'health-concern'],
            ['health-concern', 'user/Condition.s', true, 'health-concern'],
            ['problem-list-item', 'user/Condition.r', false, 'problem-list-item'],
            ['problem-list-item', 'user/Condition.s', false, 'problem-list-item'],
        ];
    }

    #[DataProvider("conditionAccessProvider")]
    public function testCanAccessResourceForCondition(string $searchParam, string $scope, bool $expectedAccessValue, string $category): void
    {
        // we need to have a scope entity that has the following constraints
        $scopeValidatorFactory = new ScopeValidatorFactory();
        $scopeValidatorArray = $scopeValidatorFactory->buildScopeValidatorArray([
            'user/Condition.rs?category=http://terminology.hl7.org/CodeSystem/condition-category|encounter-diagnosis'
            , 'user/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern'
        ]);
        $httpRestRequest = HttpRestRequest::create('/fhir/Condition?category=' . $searchParam, 'GET');
        $httpRestRequest->setRequestRequiredScope(ScopeEntity::createFromString($scope));
        $httpRestRequest->setAccessTokenScopeValidationArray($scopeValidatorArray);


        // we need to test that a request to GET /fhir/Condition with category=encounter-diagnosis is allowed
        // where the resource has category encounter-diagnosis
        $condition = $this->createConditionWithCategory($category);
        $resourceConstraintFilterer = new ResourceConstraintFilterer();
        $shouldBeClause = $expectedAccessValue ? "allowed" : "denied";
        $this->assertEquals($expectedAccessValue, $resourceConstraintFilterer->canAccessResource($condition, $httpRestRequest)
            , "Access should be " .$shouldBeClause . " for Condition with category " . $category);

        // we need to test that a request to GET /fhir/Condition with category=health-concern is allowed
        // where the resource has category health-concern
        $condition = $this->createConditionWithCategory('health-concern');
        $resourceConstraintFilterer = new ResourceConstraintFilterer();
        $this->assertTrue($resourceConstraintFilterer->canAccessResource($condition, $httpRestRequest), "Access should be allowed for Condition with category health-concern");

        // we need to test that a request to GET /fhir/Condition with category=problem-list-item is denied
        $condition = $this->createConditionWithCategory('problem-list-item');
        $resourceConstraintFilterer = new ResourceConstraintFilterer();
        $this->assertFalse($resourceConstraintFilterer->canAccessResource($condition, $httpRestRequest), "Access should be denied for Condition with category problem-list-item");
    }

    private function createObservationWithCategories(array $array): FHIRObservation
    {
        $observation = new FHIRObservation();
        foreach ($array as $categoryCode) {
            $category = UtilsService::createCodeableConcept([
                $categoryCode => [
                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code' => $categoryCode,
                    'description' => ucfirst(str_replace('-', ' ', $categoryCode))
                ]
            ]);
            $observation->addCategory($category);
        }
        return $observation;
    }

    private function createConditionWithCategory(string $string)
    {
        $condition = new FHIRCondition();
        $category = UtilsService::createCodeableConcept([
            $string => [
                'system' => ($string === 'health-concern') ? 'http://hl7.org/fhir/us/core/CodeSystem/condition-category' : 'http://terminology.hl7.org/CodeSystem/condition-category',
                'code' => $string,
                'description' => ucfirst(str_replace('-', ' ', $string))
            ]
        ]);
        $condition->addCategory($category);
        return $condition;
    }
}
