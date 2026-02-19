<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\EhiExporter\\\\Models\\\\ExportState\\:\\:\\$queue with generic class SplQueue does not specify its types\\: TValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/ExportState.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\Acl\\:\\:getInputFilter\\(\\) return type with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/Acl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\Acl\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/Acl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\Application\\:\\:getInputFilter\\(\\) return type with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/Application.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\Application\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/Application.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Controller\\\\ModuleconfigController\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/ModuleconfigController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Carecoordination\\\\Controller\\\\ModuleconfigController\\:\\:\\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/ModuleconfigController.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Carecoordination\\\\Form\\\\ModuleconfigForm extends generic class Laminas\\\\Form\\\\Form but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Form/ModuleconfigForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Carecoordination\\\\Model\\\\Configuration extends generic class Laminas\\\\Form\\\\Form but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\Moduleconfig\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/Moduleconfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Ccr\\\\Model\\\\Ccr\\:\\:getInputFilter\\(\\) return type with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/Ccr.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Ccr\\\\Model\\\\Ccr\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/Ccr.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Documents\\\\Model\\\\Documents\\:\\:getInputFilter\\(\\) return type with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/Documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Documents\\\\Model\\\\Documents\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/Documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Controller\\\\ModuleconfigController\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ModuleconfigController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Immunization\\\\Controller\\\\ModuleconfigController\\:\\:\\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ModuleconfigController.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Immunization\\\\Form\\\\ImmunizationForm extends generic class Laminas\\\\Form\\\\Form but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Form/ImmunizationForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Immunization\\\\Model\\\\Configuration extends generic class Laminas\\\\Form\\\\Form but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Immunization\\\\Model\\\\Configuration\\:\\:\\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\Immunization\\:\\:getInputFilter\\(\\) return type with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/Immunization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\Immunization\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/Immunization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModule\\:\\:getInputFilter\\(\\) return type with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModule\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModule.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Installer\\\\Model\\\\InstModule\\:\\:\\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Multipledb\\\\Model\\\\Multipledb\\:\\:getInputFilter\\(\\) return type with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/Multipledb.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Multipledb\\\\Model\\\\Multipledb\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/Multipledb.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Multipledb\\\\Model\\\\Multipledb\\:\\:\\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/Multipledb.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Patientvalidation\\\\Model\\\\PatientData\\:\\:getInputFilter\\(\\) return type with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Model/PatientData.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Patientvalidation\\\\Model\\\\PatientData\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Model/PatientData.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Syndromicsurveillance\\\\Controller\\\\ModuleconfigController\\:\\:setInputFilter\\(\\) has parameter \\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Controller/ModuleconfigController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Syndromicsurveillance\\\\Controller\\\\ModuleconfigController\\:\\:\\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Controller/ModuleconfigController.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Syndromicsurveillance\\\\Model\\\\Configuration extends generic class Laminas\\\\Form\\\\Form but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Syndromicsurveillance\\\\Model\\\\Configuration\\:\\:\\$inputFilter with generic interface Laminas\\\\InputFilter\\\\InputFilterInterface does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Syndromicsurveillance\\\\Model\\\\Syndromicsurveillance extends generic class Laminas\\\\Form\\\\Form but does not specify its types\\: TFilteredValues$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/Syndromicsurveillance.php',
];
$ignoreErrors[] = [
    'message' => '#^Class RsPopulation implements generic interface ArrayAccess but does not specify its types\\: TKey, TValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/library/RsPopulation.php',
];
$ignoreErrors[] = [
    'message' => '#^Class RsPopulation implements generic interface Iterator but does not specify its types\\: TKey, TValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/library/RsPopulation.php',
];
$ignoreErrors[] = [
    'message' => '#^Class DataPage implements generic interface Iterator but does not specify its types\\: TKey, TValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Class DataSet implements generic interface Iterator but does not specify its types\\: TKey, TValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ResourceScopeEntityList extends generic class ArrayObject but does not specify its types\\: TKey, TValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Entities/ResourceScopeEntityList.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\FHIR\\\\R4\\\\PHPFHIRParserMap implements generic interface ArrayAccess but does not specify its types\\: TKey, TValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/PHPFHIRParserMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\FHIR\\\\R4\\\\PHPFHIRParserMap implements generic interface Iterator but does not specify its types\\: TKey, TValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/PHPFHIRParserMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Menu\\\\MenuItems extends generic class ArrayObject but does not specify its types\\: TKey, TValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/MenuItems.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\G9_Certification\\\\CCDADocRefGenerationTest\\:\\:replaceRootIdForNodes\\(\\) has parameter \\$currentList1 with generic class DOMNodeList but does not specify its types\\: TNode$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\G9_Certification\\\\CCDADocRefGenerationTest\\:\\:replaceRootIdForNodes\\(\\) has parameter \\$expectedList2 with generic class DOMNodeList but does not specify its types\\: TNode$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\Modules\\\\CareCoordination\\\\Model\\\\CcdaServiceDocumentRequestorTest\\:\\:replaceRootIdForNodes\\(\\) has parameter \\$currentList1 with generic class DOMNodeList but does not specify its types\\: TNode$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\Modules\\\\CareCoordination\\\\Model\\\\CcdaServiceDocumentRequestorTest\\:\\:replaceRootIdForNodes\\(\\) has parameter \\$expectedList2 with generic class DOMNodeList but does not specify its types\\: TNode$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
