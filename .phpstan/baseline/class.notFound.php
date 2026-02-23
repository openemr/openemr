<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset 2 on an unknown class type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset 9 on an unknown class type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset int on an unknown class type\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$col_code of function define_external_table\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$col_description of function define_external_table\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$col_description_brief of function define_external_table\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$filter_clauses of function define_external_table\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$joins of function define_external_table\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$results of function define_external_table\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$table_name of function define_external_table\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$version_order of function define_external_table\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'city\' on an unknown class OpenEMR\\\\Common\\\\Database\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'country\' on an unknown class OpenEMR\\\\Common\\\\Database\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'id\' on an unknown class OpenEMR\\\\Common\\\\Database\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'name\' on an unknown class OpenEMR\\\\Common\\\\Database\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'postal_code\' on an unknown class OpenEMR\\\\Common\\\\Database\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'state\' on an unknown class OpenEMR\\\\Common\\\\Database\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'street\' on an unknown class OpenEMR\\\\Common\\\\Database\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Execute\\(\\) on an unknown class connection\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method dispatch\\(\\) on an unknown class EventDispatcherInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/search_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$eventDispatcher contains unknown class EventDispatcherInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/search_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Method AbstractClickmapModel\\:\\:getCode\\(\\) has invalid return type A\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method AbstractClickmapModel\\:\\:getTitle\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Property AbstractClickmapModel\\:\\:\\$activity has unknown class activity as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Property AbstractClickmapModel\\:\\:\\$authorized has unknown class authorized as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Property AbstractClickmapModel\\:\\:\\$data has unknown class data as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Property AbstractClickmapModel\\:\\:\\$date has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Property AbstractClickmapModel\\:\\:\\$groupname has unknown class groupname as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Property AbstractClickmapModel\\:\\:\\$id has unknown class id as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Property AbstractClickmapModel\\:\\:\\$pid has unknown class pid as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$hideNav on an unknown class Model\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_AbstractClickmap\\:\\:createModel\\(\\) has invalid return type Model\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_AbstractClickmap\\:\\:getImage\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_AbstractClickmap\\:\\:getOptionList\\(\\) has invalid return type A\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_AbstractClickmap\\:\\:getOptionsLabel\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property C_AbstractClickmap\\:\\:\\$template_dir has unknown class template_dir as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function load_fee_sheet_options\\(\\) has invalid return type an\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_options_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$search_query of function diagnosis_search\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_search_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$search_type of function diagnosis_search\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_search_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$search_type_id of function diagnosis_search\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_search_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_FormPainMap\\:\\:createModel\\(\\) has invalid return type Model\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_FormPainMap\\:\\:getImage\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_FormPainMap\\:\\:getOptionList\\(\\) has invalid return type A\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_FormPainMap\\:\\:getOptionsLabel\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property C_FormPainMap\\:\\:\\$FORM_CODE has unknown class FORM_CODE as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property C_FormPainMap\\:\\:\\$FORM_TITLE has unknown class FORM_TITLE as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method FormPainMap\\:\\:getCode\\(\\) has invalid return type A\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/FormPainMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method FormPainMap\\:\\:getTitle\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/FormPainMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property FormPainMap\\:\\:\\$TABLE_NAME has unknown class TABLE_NAME as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/FormPainMap.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var contains unknown class Dotenv\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var contains unknown class Kernel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method dispatch\\(\\) on an unknown class EventDispatcherInterface\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$eventDispatcher contains unknown class EventDispatcherInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnConfigGetVar\\(\\) has invalid return type value\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnGetBaseURL\\(\\) has invalid return type base\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModURL\\(\\) has invalid return type absolute\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Modules\\\\ClaimRevConnector\\\\CustomSkeletonFHIRResourceService not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleHealthCalendarController\\:\\:\\$loggedInUserId has unknown class Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\The as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthCalendarController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\:\\:getDateRegistered\\(\\) has invalid return type Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\DateTime\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Models/TeleHealthUser.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\TelehealthGlobalConfig\\:\\:\\$publicWebPath has unknown class Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\publicWebPath as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/TelehealthGlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Modules\\\\Dorn\\\\CustomSkeletonFHIRResourceService not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\Dorn\\\\models\\\\OrderStatusViewModel\\:\\:\\$createdDateTimeUtc has unknown class OpenEMR\\\\Modules\\\\Dorn\\\\models\\\\DateTime as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/models/OrderStatusViewModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\LogProperties\\:\\:\\$container has unknown class OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\Container as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/AclTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method listAutoSuggest\\(\\) on an unknown class Application\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Controller\\\\IndexController\\:\\:getApplicationTable\\(\\) has invalid return type Application\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getCCDAComponents\\(\\) on an unknown class Application\\\\Controller\\\\type\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getCombinationFormComponents\\(\\) on an unknown class Application\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getFacility\\(\\) on an unknown class Application\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getFaxRecievers\\(\\) on an unknown class Application\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getUsers\\(\\) on an unknown class Application\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Controller\\\\SendtoController\\:\\:getApplicationTable\\(\\) has invalid return type Application\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Controller\\\\SendtoController\\:\\:getSendtoTable\\(\\) has invalid return type Application\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Application\\\\Listener\\\\Listener\\:\\:\\$listeners has unknown class Laminas\\\\Stdlib\\\\CallbackHandler as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Listener/Listener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method count\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getTraceAsString\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\ApplicationTable\\:\\:quoteValue\\(\\) has invalid return type Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\ApplicationTable\\:\\:zQuery\\(\\) has invalid return type Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$e of method Application\\\\Model\\\\ApplicationTable\\:\\:errorHandler\\(\\) has invalid type Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$value of method Application\\\\Model\\\\ApplicationTable\\:\\:quoteValue\\(\\) has invalid type Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method current\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/CommonPlugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getGeneratedValue\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/CommonPlugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/CommonPlugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Plugin\\\\CommonPlugin\\:\\:checkACL\\(\\) has invalid return type Application\\\\Plugin\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/CommonPlugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$container of method Application\\\\Plugin\\\\CommonPlugin\\:\\:__construct\\(\\) has invalid type Application\\\\Plugin\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/CommonPlugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$container of method Application\\\\Plugin\\\\Phimail\\:\\:__construct\\(\\) has invalid type Application\\\\Plugin\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/Phimail.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getDocumentsTable\\(\\) on an unknown class Carecoordination\\\\Controller\\\\Documents\\\\Controller\\\\DocumentsController\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method isZipUpload\\(\\) on an unknown class Carecoordination\\\\Controller\\\\Documents\\\\Controller\\\\DocumentsController\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method uploadAction\\(\\) on an unknown class Carecoordination\\\\Controller\\\\Documents\\\\Controller\\\\DocumentsController\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Carecoordination\\\\Controller\\\\CarecoordinationController\\:\\:\\$carecoordinationTable has unknown class Carecoordination\\\\Controller\\\\Carecoordination\\\\Model\\\\CarecoordinationTable as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Carecoordination\\\\Controller\\\\CarecoordinationController\\:\\:\\$documentsController has unknown class Carecoordination\\\\Controller\\\\Documents\\\\Controller\\\\DocumentsController as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Carecoordination\\\\Controller\\\\CarecoordinationController\\:\\:\\$listenerObject has unknown class Carecoordination\\\\Controller\\\\Application\\\\Listener\\\\Listener as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method uploadAction\\(\\) on an unknown class Carecoordination\\\\Controller\\\\Documents\\\\Controller\\\\DocumentsController\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CcdController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Carecoordination\\\\Controller\\\\CcdController\\:\\:\\$documentsController has unknown class Carecoordination\\\\Controller\\\\Documents\\\\Controller\\\\DocumentsController as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CcdController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getDocuments\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getFormsList\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getLbfList\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getMappedFields\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getMaxIdCcda\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getSections\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getTableList\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method insertChild\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method insertMaster\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method updateExistingMappedFields\\(\\) on an unknown class Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Controller\\\\MapperController\\:\\:getMapperTable\\(\\) has invalid return type Carecoordination\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method count\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method current\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 29,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method count\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method current\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getGeneratedValue\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 47,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method count\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/MapperTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/SetupTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method createAuditArray\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method discardCCRData\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method document_fetch\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method fetch_cat_id\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method fetch_uploaded_documents\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getAllergies\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getDemographicsOld\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getDemographics\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getDocument\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getImmunizations\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getLabResults\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getMedications\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getProblems\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method insertApprovedData\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method insert_ccr_into_audit_data\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method insert_patient\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method parseXmlStream\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method update_document\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method update_imported\\(\\) on an unknown class Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Ccr\\\\Controller\\\\CcrController\\:\\:getCcrTable\\(\\) has invalid return type Ccr\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method count\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getGeneratedValue\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method current\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/DocumentsTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/DocumentsTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method get\\(\\) on an unknown class Documents\\\\Plugin\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Plugin/Documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Plugin/Documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$sm of method Documents\\\\Plugin\\\\Documents\\:\\:__construct\\(\\) has invalid type Documents\\\\Plugin\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Plugin/Documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Controller\\\\ImmunizationController\\:\\:format_cvx_code\\(\\) has invalid return type Immunization\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Controller\\\\ImmunizationController\\:\\:format_ethnicity\\(\\) has invalid return type Immunization\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Controller\\\\ImmunizationController\\:\\:tr\\(\\) has invalid return type Immunization\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$a of method Immunization\\\\Controller\\\\ImmunizationController\\:\\:tr\\(\\) has invalid type Immunization\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$cvx_code of method Immunization\\\\Controller\\\\ImmunizationController\\:\\:format_cvx_code\\(\\) has invalid type Immunization\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$ethnicity of method Immunization\\\\Controller\\\\ImmunizationController\\:\\:format_ethnicity\\(\\) has invalid type Immunization\\\\Controller\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'code_bind_values\' on an unknown class Immunization\\\\Model\\\\type\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'form_from_date\' on an unknown class Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'form_get_hl7\' on an unknown class Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'form_to_date\' on an unknown class Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'limit_start\' on an unknown class Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'pid_bind_values\' on an unknown class Immunization\\\\Model\\\\type\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'query_codes\' on an unknown class Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'query_pids\' on an unknown class Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'results\' on an unknown class Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\ImmunizationTable\\:\\:getImmunizationObservationResultsData\\(\\) has invalid return type Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\ImmunizationTable\\:\\:immunizedPatientDetails\\(\\) has invalid return type Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$form_data of method Immunization\\\\Model\\\\ImmunizationTable\\:\\:immunizedPatientDetails\\(\\) has invalid type Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$id of method Immunization\\\\Model\\\\ImmunizationTable\\:\\:getImmunizationObservationResultsData\\(\\) has invalid type Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$pid of method Immunization\\\\Model\\\\ImmunizationTable\\:\\:getImmunizationObservationResultsData\\(\\) has invalid type Immunization\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getAclConfig\\(\\) on an unknown class Installer\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Installer\\\\Controller\\\\unknown_type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$data of method Installer\\\\Controller\\\\InstallerController\\:\\:getContent\\(\\) has invalid type Installer\\\\Controller\\\\unknown_type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method count\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method current\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getAclConfig\\(\\) on an unknown class Installer\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getGeneratedValue\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getHookConfig\\(\\) on an unknown class Installer\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModuleTable\\:\\:getObject\\(\\) has invalid return type Installer\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$adapter of method Installer\\\\Model\\\\InstModuleTable\\:\\:getObject\\(\\) has invalid type Installer\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$dir of method Installer\\\\Model\\\\InstModuleTable\\:\\:installSQL\\(\\) has invalid type Installer\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$features of method Installer\\\\Model\\\\InstModuleTableGateway\\:\\:__construct\\(\\) has invalid type Installer\\\\Model\\\\Feature\\\\AbstractFeature\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTableGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$features of method Installer\\\\Model\\\\InstModuleTableGateway\\:\\:__construct\\(\\) has invalid type Installer\\\\Model\\\\Feature\\\\FeatureSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTableGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Multipledb\\\\Controller\\\\BaseController\\:\\:getPostParamsArray\\(\\) has invalid return type Multipledb\\\\Controller\\\\post\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Patientvalidation\\\\Controller\\\\BaseController\\:\\:getPostParamsArray\\(\\) has invalid return type Patientvalidation\\\\Controller\\\\post\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Model/PatientDataTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method current\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/SyndromicsurveillanceTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/SyndromicsurveillanceTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$RECEIVING_APPLICATION on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$RECEIVING_FACILITY on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$SENDING_FACILITY on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method buildRequest\\(\\) on an unknown class QuestResultClient\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method buildResultAck\\(\\) on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getResultsBatch\\(\\) on an unknown class QuestResultClient\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method sendResultAck\\(\\) on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class QuestResultClient not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$direction of function receive_hl7_results\\(\\) has invalid type char\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Caught class MpdfException not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$encounterId of method ESign\\\\Encounter_Log\\:\\:__construct\\(\\) has invalid type ESign\\\\unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Encounter/Log.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$encounterId of method ESign\\\\Form_Log\\:\\:__construct\\(\\) has invalid type ESign\\\\unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Form/Log.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$formDir of method ESign\\\\Form_Log\\:\\:__construct\\(\\) has invalid type ESign\\\\unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Form/Log.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$formId of method ESign\\\\Form_Log\\:\\:__construct\\(\\) has invalid type ESign\\\\unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Form/Log.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method NWeekdayOfMonth\\(\\) on an unknown class MedExApi\\\\Date_Calc\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class MedExApi\\\\DateTime not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Method NumberToText\\:\\:convert\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/NumberToText.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method NumberToText\\:\\:n2t_convertthree\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/NumberToText.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Execute\\(\\) on an unknown class connection\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function listingCDRReminderLog\\(\\) has invalid return type sqlret\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_crt\\(\\) has invalid return type data\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/create_ssl_certificate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getImmunizationList\\(\\) has invalid return type recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/immunization_helper.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getEmployerData\\(\\) has invalid return type OpenEMR\\\\Common\\\\Database\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$asOfYMD of function getPatientAgeDisplay\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$date of function getPatientAgeYMD\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$dob of function getPatientAgeYMD\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$dobYMD of function getPatientAgeDisplay\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$function of method Smarty_Legacy\\:\\:_get_filter_name\\(\\) has invalid type callback\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$function of method Smarty_Legacy\\:\\:register_outputfilter\\(\\) has invalid type callback\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$function of method Smarty_Legacy\\:\\:register_postfilter\\(\\) has invalid type callback\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$function of method Smarty_Legacy\\:\\:register_prefilter\\(\\) has invalid type callback\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$function of method Smarty_Legacy\\:\\:unregister_outputfilter\\(\\) has invalid type callback\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$function of method Smarty_Legacy\\:\\:unregister_postfilter\\(\\) has invalid type callback\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$function of method Smarty_Legacy\\:\\:unregister_prefilter\\(\\) has invalid type callback\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Smarty_Legacy\\:\\:\\$_conf_obj has unknown class Config_file as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_db\\(\\) has invalid return type connection\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlQ\\(\\) has invalid return type recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getUserSetting\\(\\) has invalid return type Effective\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function prevSetting\\(\\) has invalid return type Prior\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method xmltoarray_parser_htmlfix\\:\\:_struct_to_array\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/xmltoarray_parser_htmlfix.php',
];
$ignoreErrors[] = [
    'message' => '#^Method xmltoarray_parser_htmlfix\\:\\:createArray\\(\\) has invalid return type The\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/xmltoarray_parser_htmlfix.php',
];
$ignoreErrors[] = [
    'message' => '#^Method xmltoarray_parser_htmlfix\\:\\:fix_html_entities\\(\\) has invalid return type A\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/xmltoarray_parser_htmlfix.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getTraceAsString\\(\\) on an unknown class type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ApplicationTable\\:\\:zQuery\\(\\) has invalid return type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$e of method ApplicationTable\\:\\:errorHandler\\(\\) has invalid type type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class OpenEMR\\\\PatientPortal\\\\Chat\\\\ChatController not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/messaging/secure_chat.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method assign\\(\\) on an unknown class specify\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class specify not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$APP_ROOT has unknown class app as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$CONVERT_NULL_TO_EMPTYSTRING has unknown class Setting as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$DEBUG_MODE has unknown class set as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$DEFAULT_ACTION has unknown class default as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$ROOT_URL has unknown class root as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$ROUTE_MAP has unknown class routemap as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$TEMPLATE_CACHE_PATH has unknown class template as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$TEMPLATE_ENGINE has unknown class specify as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$TEMPLATE_PATH has unknown class template as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$WEB_ROOT has unknown class root as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_enclose_value\\(\\) has invalid return type Processed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_rfile\\(\\) has invalid return type Data\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:auto\\(\\) has invalid return type delimiter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:output\\(\\) has invalid return type CSV\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:unparse\\(\\) has invalid return type CSV\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method IDataDriver\\:\\:Open\\(\\) has invalid return type connection\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/IDataDriver.php',
];
$ignoreErrors[] = [
    'message' => '#^Method IDataDriver\\:\\:Query\\(\\) has invalid return type resultset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/IDataDriver.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'Data_free\' on an unknown class resultset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataDriverMySQLi\\:\\:Query\\(\\) has invalid return type resultset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Context\\:\\:Get\\(\\) has invalid return type value\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/Context.php',
];
$ignoreErrors[] = [
    'message' => '#^Property RequestUtil\\:\\:\\$bodyCache has unknown class body as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method Escape\\(\\) on an unknown class instance\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method GetQuotedSql\\(\\) on an unknown class instance\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataAdapter\\:\\:Select\\(\\) has invalid return type resultset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$rs of method DataAdapter\\:\\:Fetch\\(\\) has invalid type resultset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$rs of method DataAdapter\\:\\:Release\\(\\) has invalid type resultset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Property DataAdapter\\:\\:\\$DRIVER_INSTANCE has unknown class instance as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Property DataAdapter\\:\\:\\$_num_retries has unknown class used as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method ToObject\\(\\) on an unknown class Preezable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataSet\\:\\:Next\\(\\) has invalid return type Preezable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataSet\\:\\:_getObject\\(\\) has invalid return type Preezable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getActiveSheet\\(\\) on an unknown class PHPExcel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getDefaultStyle\\(\\) on an unknown class PHPExcel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getProperties\\(\\) on an unknown class PHPExcel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method setActiveSheetIndex\\(\\) on an unknown class PHPExcel\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method createWriter\\(\\) on an unknown class PHPExcel_IOFactory\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class PHPExcel not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$property of method IDaoMap2\\:\\:SetFetchingStrategy\\(\\) has invalid type unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/IDaoMap2.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$value of method MockRouter\\:\\:SetUri\\(\\) has invalid type unknown_type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/MockRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$value of method MockRouter\\:\\:SetUrl\\(\\) has invalid type unknown_type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/MockRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Phreezable\\:\\:\\$NoCacheProperties has unknown class these as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Phreezable\\:\\:\\$PublicPropCache has unknown class cache as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Phreezer\\:\\:\\$CacheQueryObjectLevel2 has unknown class set as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method GetRSSAuthor\\(\\) on an unknown class IRSSFeedItem\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method GetRSSDescription\\(\\) on an unknown class IRSSFeedItem\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method GetRSSGUID\\(\\) on an unknown class IRSSFeedItem\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method GetRSSLink\\(\\) on an unknown class IRSSFeedItem\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method GetRSSPublishDate\\(\\) on an unknown class IRSSFeedItem\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method GetRSSTitle\\(\\) on an unknown class IRSSFeedItem\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method addCategory\\(\\) on an unknown class RSS_Writer\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method addItem\\(\\) on an unknown class RSS_Writer\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method setLanguage\\(\\) on an unknown class RSS_Writer\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method writeOut\\(\\) on an unknown class RSS_Writer\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Class IRSSFeedItem not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class RSS_Writer not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Reporter\\:\\:\\$NoCacheProperties has unknown class these as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Reporter\\:\\:\\$PublicPropCache has unknown class cache as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset 0 on an unknown class char\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset 1 on an unknown class char\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset 2 on an unknown class char\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset 3 on an unknown class char\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$c of method VerySimpleStringUtil\\:\\:unicode_entity_replace\\(\\) has invalid type char\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Property VerySimpleStringUtil\\:\\:\\$CONTROL_CODE_CHARS has unknown class characters as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Property VerySimpleStringUtil\\:\\:\\$DEFAULT_CHARACTER_SET has unknown class the as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Property VerySimpleStringUtil\\:\\:\\$HTML_ENTITIES_TABLE has unknown class associative as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Property VerySimpleStringUtil\\:\\:\\$INVALID_CODE_CHARS has unknown class common as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteActivityViewDAO\\:\\:\\$ActionTakenTime has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteActivityViewDAO\\:\\:\\$Checksum has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteActivityViewDAO\\:\\:\\$Date has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteActivityViewDAO\\:\\:\\$Dob has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteActivityViewDAO\\:\\:\\$Narrative has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteActivityViewDAO\\:\\:\\$TableAction has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteActivityViewDAO\\:\\:\\$TableArgs has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$property of method OnsiteActivityViewMap\\:\\:SetFetchingStrategy\\(\\) has invalid type unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteDocumentDAO\\:\\:\\$AuthorizeSignedTime has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteDocumentDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteDocumentDAO\\:\\:\\$CreateDate has unknown class timestamp as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteDocumentDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteDocumentDAO\\:\\:\\$FullDocument has unknown class blob as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteDocumentDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteDocumentDAO\\:\\:\\$PatientSignedTime has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteDocumentDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsiteDocumentDAO\\:\\:\\$ReviewDate has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteDocumentDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$property of method OnsiteDocumentMap\\:\\:SetFetchingStrategy\\(\\) has invalid type unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteDocumentMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsitePortalActivityDAO\\:\\:\\$ActionTakenTime has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsitePortalActivityDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsitePortalActivityDAO\\:\\:\\$Checksum has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsitePortalActivityDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsitePortalActivityDAO\\:\\:\\$Date has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsitePortalActivityDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsitePortalActivityDAO\\:\\:\\$Narrative has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsitePortalActivityDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsitePortalActivityDAO\\:\\:\\$TableAction has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsitePortalActivityDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OnsitePortalActivityDAO\\:\\:\\$TableArgs has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsitePortalActivityDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$property of method OnsitePortalActivityMap\\:\\:SetFetchingStrategy\\(\\) has invalid type unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsitePortalActivityMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property PatientDAO\\:\\:\\$Date has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/PatientDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property PatientDAO\\:\\:\\$Dob has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/PatientDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property PatientDAO\\:\\:\\$Occupation has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/PatientDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property PatientDAO\\:\\:\\$Regdate has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/PatientDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$property of method PatientMap\\:\\:SetFetchingStrategy\\(\\) has invalid type unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/PatientMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property UserDAO\\:\\:\\$Info has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property UserDAO\\:\\:\\$Password has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property UserDAO\\:\\:\\$PwdExpirationDate has unknown class date as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property UserDAO\\:\\:\\$PwdHistory1 has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Property UserDAO\\:\\:\\$PwdHistory2 has unknown class longtext as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$property of method UserMap\\:\\:SetFetchingStrategy\\(\\) has invalid type unknown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function report_header_2\\(\\) has invalid return type outputs\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getPosition\\(\\) on an unknown class OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:getPosition\\(\\) has invalid return type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$column of method OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:__construct\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$first of method OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:cmpHcfaInfo\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$info of method OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:__construct\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$row of method OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:__construct\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$second of method OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:cmpHcfaInfo\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$width of method OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:__construct\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:\\$column has unknown class OpenEMR\\\\Billing\\\\type as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:\\$info has unknown class OpenEMR\\\\Billing\\\\type as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:\\$row has unknown class OpenEMR\\\\Billing\\\\type as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:\\$width has unknown class OpenEMR\\\\Billing\\\\type as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$diagtype on an unknown class OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method diagArray\\(\\) on an unknown class OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method medicaidOriginalReference\\(\\) on an unknown class OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method medicaidResubmissionCode\\(\\) on an unknown class OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method priorAuth\\(\\) on an unknown class OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$claim of method OpenEMR\\\\Billing\\\\Hcfa1500\\:\\:processDiagnoses0212\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$col of method OpenEMR\\\\Billing\\\\Hcfa1500\\:\\:putHcfa\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$data of method OpenEMR\\\\Billing\\\\Hcfa1500\\:\\:putHcfa\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$diag of method OpenEMR\\\\Billing\\\\Hcfa1500\\:\\:addDiagnosis\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$line of method OpenEMR\\\\Billing\\\\Hcfa1500\\:\\:putHcfa\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$maxlen of method OpenEMR\\\\Billing\\\\Hcfa1500\\:\\:putHcfa\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$number of method OpenEMR\\\\Billing\\\\Hcfa1500\\:\\:addDiagnosis\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$strip of method OpenEMR\\\\Billing\\\\Hcfa1500\\:\\:putHcfa\\(\\) has invalid type OpenEMR\\\\Billing\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\Rule\\:\\:\\$groups has unknown class RuleTargetActionGroups as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/Rule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\AuthUtils\\:\\:collectIpLoginFailsSql\\(\\) has invalid return type recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\AuthUtils\\:\\:rehashPassword\\(\\) has invalid return type s\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$previous of method OpenEMR\\\\Common\\\\Auth\\\\Exception\\\\OneTimeAuthException\\:\\:__construct\\(\\) has invalid type OpenEMR\\\\Common\\\\Auth\\\\Exception\\\\Throwable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/Exception/OneTimeAuthException.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$previous of method OpenEMR\\\\Common\\\\Auth\\\\Exception\\\\OneTimeAuthExpiredException\\:\\:__construct\\(\\) has invalid type OpenEMR\\\\Common\\\\Auth\\\\Exception\\\\Throwable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/Exception/OneTimeAuthExpiredException.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Compatibility\\\\Checker\\:\\:checkPhpVersion\\(\\) has invalid return type OpenEMR\\\\Common\\\\Compatibility\\\\warning\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Compatibility/Checker.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\ORDataObject\\\\ContactAddress\\:\\:\\$notes has unknown class OpenEMR\\\\Common\\\\ORDataObject\\\\Note as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method MetaPrimaryKeys\\(\\) on an unknown class connection\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ORDataObject.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$uuid of method OpenEMR\\\\Common\\\\Uuid\\\\UuidRegistry\\:\\:getRegistryRecordForUuid\\(\\) has invalid type OpenEMR\\\\Common\\\\Uuid\\\\binary\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:getFormat\\(\\) has invalid return type OpenEMR\\\\Events\\\\PatientDocuments\\\\html\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:getFormat\\(\\) has invalid return type OpenEMR\\\\Events\\\\PatientDocuments\\\\xml\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:getFormat\\(\\) has invalid return type OpenEMR\\\\Events\\\\PatientDocuments\\\\zip\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$format of method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:setFormat\\(\\) has invalid type OpenEMR\\\\Events\\\\PatientDocuments\\\\html\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$format of method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:setFormat\\(\\) has invalid type OpenEMR\\\\Events\\\\PatientDocuments\\\\xml\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$format of method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:setFormat\\(\\) has invalid type OpenEMR\\\\Events\\\\PatientDocuments\\\\zip\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:\\$format has unknown class OpenEMR\\\\Events\\\\PatientDocuments\\\\html as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:\\$format has unknown class OpenEMR\\\\Events\\\\PatientDocuments\\\\xml as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:\\$format has unknown class OpenEMR\\\\Events\\\\PatientDocuments\\\\zip as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\PageHeadingRenderEvent\\:\\:setActions\\(\\) has invalid return type OpenEMR\\\\Events\\\\UserInterface\\\\UserEditRenderEvent\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/PageHeadingRenderEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNarrative\\:\\:getDiv\\(\\) has invalid return type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNarrative.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$div of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNarrative\\:\\:setDiv\\(\\) has invalid type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNarrative.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNarrative\\:\\:\\$div has unknown class string as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNarrative.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class OpenEMR\\\\Gacl\\\\Hashed_Cache_Lite not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object\\(\\) has invalid return type OpenEMR\\\\Gacl\\\\ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method dispatch\\(\\) on an unknown class OpenEMR\\\\Menu\\\\EventDispatcher\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Menu/MainMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Menu\\\\MainMenuRole\\:\\:\\$dispatcher has unknown class OpenEMR\\\\Menu\\\\EventDispatcher as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/MainMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$APP_ROOT has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\app as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$FHIR_ROUTE_MAP has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\fhir as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$PORTAL_ROUTE_MAP has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\portal as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$ROOT_URL has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\root as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$ROUTE_MAP has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\routemap as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$localCall has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\set as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$notRestCall has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\set as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:getOne\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:post\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:put\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirAllergyIntoleranceRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirAppointmentRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirCarePlanRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCarePlanRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirCareTeamRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCareTeamRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirCoverageRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCoverageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirDeviceRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDeviceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirDiagnosticReportRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDiagnosticReportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirDocumentReferenceRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentReferenceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirEncounterRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirEncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirGoalRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGoalRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirGroupRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGroupRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirImmunizationRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirLocationRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirLocationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirMedicationRequestRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirMedicationRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirObservationRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirObservationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirPersonRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirPractitionerRoleRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRoleRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirProcedureRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirProvenanceRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProvenanceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirServiceRequestRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirServiceRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirSpecimenRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirSpecimenRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirValueSetRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirValueSetRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getBody\\(\\) on an unknown class OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\ResponseInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDefinitionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FhirOperationDefinitionRestController\\:\\:createResponseForCode\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\ResponseInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDefinitionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FhirOperationDefinitionRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDefinitionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getBody\\(\\) on an unknown class OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\ResponseInterface\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FhirOperationDocRefRestController\\:\\:createResponseForCode\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\ResponseInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FhirOperationDocRefRestController\\:\\:getAll\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FHIR\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\PractitionerRestController\\:\\:patch\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\PractitionerRestController\\:\\:post\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\TransactionRestController\\:\\:CreateTransaction\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TransactionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method count\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 40,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method current\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getGeneratedValue\\(\\) on an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Application\\\\Model\\\\type\\.$#',
    'count' => 35,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportClinicalNotesService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirClinicalNotesService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:parseOpenEMRRecord\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:parseOpenEMRRecord\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getAll\\(\\) on an unknown class OpenEMR\\\\Services\\\\FHIR\\\\MedicationService\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationService\\:\\:\\$medicationService has unknown class OpenEMR\\\\Services\\\\FHIR\\\\MedicationService as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:parseOpenEMRRecord\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getValues\\(\\) on an unknown class OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method hasCodeValue\\(\\) on an unknown class OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$openEMRSearchParameters of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:searchForOpenEMRRecords\\(\\) has invalid type OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:parseOpenEMRRecord\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method hasCodeValue\\(\\) on an unknown class OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$openEMRSearchParameters of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:searchForOpenEMRRecords\\(\\) has invalid type OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Globals\\\\UserSettingsService\\:\\:getUserSetting\\(\\) has invalid return type OpenEMR\\\\Services\\\\Globals\\\\Effective\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Globals\\\\UserSettingsService\\:\\:prevSetting\\(\\) has invalid return type OpenEMR\\\\Services\\\\Globals\\\\Prior\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:getChartTrackerInformationActivity\\(\\) has invalid return type OpenEMR\\\\Services\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:getChartTrackerInformation\\(\\) has invalid return type OpenEMR\\\\Services\\\\recordset\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$asOfYMD of method OpenEMR\\\\Services\\\\PatientService\\:\\:getPatientAgeDisplay\\(\\) has invalid type OpenEMR\\\\Services\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$date of method OpenEMR\\\\Services\\\\PatientService\\:\\:getPatientAgeYMD\\(\\) has invalid type OpenEMR\\\\Services\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$dob of method OpenEMR\\\\Services\\\\PatientService\\:\\:getPatientAgeYMD\\(\\) has invalid type OpenEMR\\\\Services\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$dobYMD of method OpenEMR\\\\Services\\\\PatientService\\:\\:getPatientAgeDisplay\\(\\) has invalid type OpenEMR\\\\Services\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\Search\\\\DateSearchField\\:\\:\\$dateType has unknown class OpenEMR\\\\Services\\\\Search\\\\Tracks as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/DateSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class OpenEMR\\\\Services\\\\Utils\\\\SQLStatement\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:getTablesList\\(\\) has invalid return type OpenEMR\\\\Services\\\\Utils\\\\SQLStatement\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Api\\\\ApiTestClient\\:\\:setAuthToken\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Api\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getBody\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Api\\\\the\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getStatusCode\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Api\\\\the\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Api\\\\BulkAPITestClient\\:\\:setAuthToken\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Api\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/BulkAPITestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getStatusCode\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Api\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/IntrospectionTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getNextId\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getSingleEntry\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getSingleFixture\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getUnregisteredUuid\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\uuid4\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:installFixturesForTable\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FacilityFixtureManager\\:\\:getSingleFacilityFixture\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FacilityFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FacilityFixtureManager\\:\\:installSingleFacilityFixture\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\count\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FacilityFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'address_id\' on an unknown class OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'addresses\' on an unknown class OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'contact_id\' on an unknown class OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getNextPid\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getSingleEntry\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getSinglePatientFixture\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getUnregisteredUuid\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\uuid4\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:installFixtures\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:installSinglePatientFixture\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\count\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getNextId\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getSingleEntry\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getSinglePractitionerFixture\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getUnregisteredUuid\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\uuid4\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:installFixtures\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:installSinglePractitionerFixture\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Fixtures\\\\count\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method clearTelemetryData\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method expects\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method fetchUsageRecords\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method saveTelemetryEvent\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$repository contains unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getBody\\(\\) on an unknown class OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PractitionerRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getStatusCode\\(\\) on an unknown class OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PractitionerRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\CarePlanServiceTest\\:\\:\\$fixture has unknown class OpenEMR\\\\Tests\\\\Services\\\\CarePlanFixture as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/CarePlanServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\EncounterServiceTest\\:\\:\\$fixture has unknown class OpenEMR\\\\Tests\\\\Services\\\\EncounterFixture as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/EncounterServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset 0 on an unknown class OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\matching\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirPatientServiceMappingTest\\:\\:findIdentiferCodeValue\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirPatientServiceMappingTest\\:\\:findTelecomEntry\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\matching\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to offset \'pubpid\' on an unknown class OpenEMR\\\\Tests\\\\Fixtures\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Qrda/QrdaReportServiceTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
