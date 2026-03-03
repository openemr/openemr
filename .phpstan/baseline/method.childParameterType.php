<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$inputFilter \\(Laminas\\\\InputFilter\\\\InputFilterInterface\\<mixed\\>\\) of method Carecoordination\\\\Model\\\\Configuration\\:\\:setInputFilter\\(\\) should be contravariant with parameter \\$inputFilter \\(Laminas\\\\InputFilter\\\\InputFilterInterface\\) of method Laminas\\\\InputFilter\\\\InputFilterAwareInterface\\:\\:setInputFilter\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$inputFilter \\(Laminas\\\\InputFilter\\\\InputFilterInterface\\<mixed\\>\\) of method Immunization\\\\Model\\\\Configuration\\:\\:setInputFilter\\(\\) should be contravariant with parameter \\$inputFilter \\(Laminas\\\\InputFilter\\\\InputFilterInterface\\) of method Laminas\\\\InputFilter\\\\InputFilterAwareInterface\\:\\:setInputFilter\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$inputFilter \\(Laminas\\\\InputFilter\\\\InputFilterInterface\\<mixed\\>\\) of method Syndromicsurveillance\\\\Model\\\\Configuration\\:\\:setInputFilter\\(\\) should be contravariant with parameter \\$inputFilter \\(Laminas\\\\InputFilter\\\\InputFilterInterface\\) of method Laminas\\\\InputFilter\\\\InputFilterAwareInterface\\:\\:setInputFilter\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$inputFilter \\(Laminas\\\\InputFilter\\\\InputFilterInterface\\<mixed\\>\\) of method Syndromicsurveillance\\\\Model\\\\Syndromicsurveillance\\:\\:setInputFilter\\(\\) should be contravariant with parameter \\$inputFilter \\(Laminas\\\\InputFilter\\\\InputFilterInterface\\) of method Laminas\\\\InputFilter\\\\InputFilterAwareInterface\\:\\:setInputFilter\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/Syndromicsurveillance.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$criteriaType \\(OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType\\) of method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaAgeBuilder\\:\\:newInstance\\(\\) should be contravariant with parameter \\$criteriaType \\(mixed\\) of method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaBuilder\\:\\:newInstance\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaAgeBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$ruleCriteriaType \\(OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType\\) of method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaDatabaseBuilder\\:\\:newInstance\\(\\) should be contravariant with parameter \\$criteriaType \\(mixed\\) of method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaBuilder\\:\\:newInstance\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaDatabaseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$criteriaType \\(OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType\\) of method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaSexBuilder\\:\\:newInstance\\(\\) should be contravariant with parameter \\$criteriaType \\(mixed\\) of method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaBuilder\\:\\:newInstance\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaSexBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$criteria \\(OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria\\) of method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaTargetFactory\\:\\:modify\\(\\) should be contravariant with parameter \\$criteria \\(mixed\\) of method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaFactory\\:\\:modify\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaTargetFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$key \\(int\\|string\\|null\\) of method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ResourceScopeEntityList\\:\\:offsetSet\\(\\) should be contravariant with parameter \\$key \\(mixed\\) of method ArrayObject\\<\\(int\\|string\\),mixed\\>\\:\\:offsetSet\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Entities/ResourceScopeEntityList.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$key \\(int\\|string\\|null\\) of method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ResourceScopeEntityList\\:\\:offsetSet\\(\\) should be contravariant with parameter \\$offset \\(mixed\\) of method ArrayAccess\\<mixed,mixed\\>\\:\\:offsetSet\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Entities/ResourceScopeEntityList.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$scopes \\(array\\|string\\) of method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Grant\\\\CustomRefreshTokenGrant\\:\\:validateScopes\\(\\) should be contravariant with parameter \\$scopes \\(array\\|string\\|null\\) of method League\\\\OAuth2\\\\Server\\\\Grant\\\\AbstractGrant\\:\\:validateScopes\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomRefreshTokenGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$scopes \\(array\\<OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ScopeEntity\\>\\) of method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ScopeRepository\\:\\:finalizeScopes\\(\\) should be contravariant with parameter \\$scopes \\(array\\<League\\\\OAuth2\\\\Server\\\\Entities\\\\ScopeEntityInterface\\>\\) of method League\\\\OAuth2\\\\Server\\\\Repositories\\\\ScopeRepositoryInterface\\:\\:finalizeScopes\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$message \\(string\\) of method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:alert\\(\\) should be contravariant with parameter \\$message \\(string\\|Stringable\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:alert\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$message \\(string\\) of method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:critical\\(\\) should be contravariant with parameter \\$message \\(string\\|Stringable\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:critical\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$message \\(string\\) of method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:debug\\(\\) should be contravariant with parameter \\$message \\(string\\|Stringable\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:debug\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$message \\(string\\) of method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:emergency\\(\\) should be contravariant with parameter \\$message \\(string\\|Stringable\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:emergency\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$message \\(string\\) of method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:error\\(\\) should be contravariant with parameter \\$message \\(string\\|Stringable\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:error\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$message \\(string\\) of method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:info\\(\\) should be contravariant with parameter \\$message \\(string\\|Stringable\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:info\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$message \\(string\\) of method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:notice\\(\\) should be contravariant with parameter \\$message \\(string\\|Stringable\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:notice\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$message \\(string\\) of method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:warning\\(\\) should be contravariant with parameter \\$message \\(string\\|Stringable\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:warning\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$message \\(string\\) of method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:log\\(\\) should be contravariant with parameter \\$message \\(string\\|Stringable\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:log\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$results \\(array\\) of method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:populate_array\\(\\) should be contravariant with parameter \\$results \\(mixed\\) of method OpenEMR\\\\Common\\\\ORDataObject\\\\ORDataObject\\:\\:populate_array\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$key \\(int\\|string\\|null\\) of method OpenEMR\\\\Menu\\\\MenuItems\\:\\:offsetSet\\(\\) should be contravariant with parameter \\$key \\(mixed\\) of method ArrayObject\\<\\(int\\|string\\),mixed\\>\\:\\:offsetSet\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/MenuItems.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$key \\(int\\|string\\|null\\) of method OpenEMR\\\\Menu\\\\MenuItems\\:\\:offsetSet\\(\\) should be contravariant with parameter \\$offset \\(mixed\\) of method ArrayAccess\\<mixed,mixed\\>\\:\\:offsetSet\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/MenuItems.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\AllergyIntoleranceService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\AllergyIntoleranceService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\AppointmentService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\AppointmentService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$data \\(array\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:filterData\\(\\) should be contravariant with parameter \\$data \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:filterData\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$date \\(string\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:processDateTime\\(\\) should be contravariant with parameter \\$date \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:processDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$id \\(string\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:getUuidById\\(\\) should be contravariant with parameter \\$id \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:getUuidById\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$idField \\(string\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:getFreshId\\(\\) should be contravariant with parameter \\$idField \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:getFreshId\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$map \\(array\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:queryFields\\(\\) should be contravariant with parameter \\$map \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:queryFields\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$uuid \\(string\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:getIdByUuid\\(\\) should be contravariant with parameter \\$uuid \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:getIdByUuid\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$table \\(string\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:getFreshId\\(\\) should be contravariant with parameter \\$table \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:getFreshId\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$table \\(string\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:getIdByUuid\\(\\) should be contravariant with parameter \\$table \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:getIdByUuid\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$table \\(string\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:getUuidById\\(\\) should be contravariant with parameter \\$table \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:getUuidById\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$whitelistedFields \\(array\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:filterData\\(\\) should be contravariant with parameter \\$whitelistedFields \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:filterData\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#3 \\$field \\(string\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:getIdByUuid\\(\\) should be contravariant with parameter \\$field \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:getIdByUuid\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#3 \\$field \\(string\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:getUuidById\\(\\) should be contravariant with parameter \\$field \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:getUuidById\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\CarePlanService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\CarePlanService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\CareTeamService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\CareTeamService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\ClinicalNotesService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\ClinicalNotesService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\ConditionService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\ConditionService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\DeviceService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\DeviceService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\DocumentService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\DocumentService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\DrugSalesService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DrugSalesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\DrugSalesService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DrugSalesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\DrugService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DrugService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\DrugService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DrugService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\EmployerService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\EmployerService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\) of method OpenEMR\\\\Services\\\\EncounterService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\EncounterService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportClinicalNotesService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDiagnosticReport\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportLaboratoryService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportLaboratoryService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirClinicalNotesService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirPatientDocumentReferenceService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirPatientDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAllergyIntoleranceService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAllergyIntoleranceService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCarePlanService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCoverageService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCoverageService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDiagnosticReportService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDiagnosticReportService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDiagnosticReportService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocumentReferenceService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocumentReferenceService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocumentReferenceService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirGoalService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGoalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirGroupService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirGroupService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirGroupService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirImmunizationService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirLocationService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirLocationService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirLocationService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationDispenseService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationDispenseService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationDispenseService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirObservationService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirObservationService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirObservationService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEmrRecord \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:insertOpenEMRRecord\\(\\) should be contravariant with parameter \\$openEmrRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:insertOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirResource \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPersonService\\:\\:parseFhirResource\\(\\) should be compatible with parameter \\$fhirResource \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseFhirResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPersonService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEmrRecord \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPersonService\\:\\:insertOpenEMRRecord\\(\\) should be contravariant with parameter \\$openEmrRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:insertOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerRoleService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerRoleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEmrRecord \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerService\\:\\:insertOpenEMRRecord\\(\\) should be contravariant with parameter \\$openEmrRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:insertOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirRelatedPersonService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirRelatedPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirResourceId \\(string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:update\\(\\) should be contravariant with parameter \\$fhirResourceId \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceUpdateableService\\:\\:update\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceRequestService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirSpecimenService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirValueSetService\\:\\:getAll\\(\\) should be contravariant with parameter \\$fhirSearchParameters \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValueSetService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirValueSetService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValueSetService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$puuidBind \\(string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirValueSetService\\:\\:getAll\\(\\) should be contravariant with parameter \\$puuidBind \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService\\:\\:getAll\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValueSetService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Group\\\\FhirPatientProviderGroupService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Group/FhirPatientProviderGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationObservationFormService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationObservationFormService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationQuestionnaireItemService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationQuestionnaireItemService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(array\\|OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRObservation\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationFacilityService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationFacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirResource \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationInsuranceService\\:\\:parseFhirResource\\(\\) should be compatible with parameter \\$fhirResource \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseFhirResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationInsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationInsuranceService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationInsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationProcedureProviderService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationProcedureProviderService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Questionnaire\\\\FhirQuestionnaireFormService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Questionnaire\\\\FhirQuestionnaireFormService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$dataRecord \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormService\\:\\:createProvenanceResource\\(\\) should be contravariant with parameter \\$dataRecord \\(mixed\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormService\\:\\:searchForOpenEMRRecords\\(\\) should be contravariant with parameter \\$openEMRSearchParameters \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:searchForOpenEMRRecords\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\ImmunizationService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\ImmunizationService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\InsuranceCompanyService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceCompanyService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\InsuranceCompanyService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceCompanyService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\InsuranceService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\InsuranceService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\ObservationLabService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationLabService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\ObservationLabService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationLabService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\|string\\>\\) of method OpenEMR\\\\Services\\\\ObservationService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\ObservationService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\PatientAdvanceDirectiveService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\PatientAdvanceDirectiveService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\PatientIssuesService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientIssuesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\PatientIssuesService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientIssuesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\PatientService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\PatientService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\|string\\) of method OpenEMR\\\\Services\\\\PersonService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\PersonService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\PractitionerRoleService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PractitionerRoleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\PractitionerRoleService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PractitionerRoleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\PractitionerService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\PractitionerService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\ProcedureProviderService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ProcedureProviderService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\ProcedureProviderService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ProcedureProviderService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\ProcedureService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\ProcedureService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\ProcedureService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\QuestionnaireResponseService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\QuestionnaireResponseService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$openEMRSearchParameters \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\|string\\>\\) of method OpenEMR\\\\Services\\\\SDOH\\\\HistorySdohService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\SDOH\\\\HistorySdohService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\SocialHistoryService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\SocialHistoryService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\SurgeryService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\SurgeryService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\VitalsCalculatedService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsCalculatedService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\VitalsCalculatedService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsCalculatedService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$search \\(array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\) of method OpenEMR\\\\Services\\\\VitalsService\\:\\:search\\(\\) should be contravariant with parameter \\$search \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$isAndCondition \\(bool\\) of method OpenEMR\\\\Services\\\\VitalsService\\:\\:search\\(\\) should be contravariant with parameter \\$isAndCondition \\(mixed\\) of method OpenEMR\\\\Services\\\\BaseServiceInterface\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
