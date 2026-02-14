<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/transmitCCD.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with ADORecordSet will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with ADORecordSet will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/assign_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with non\\-empty\\-array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_numeric\\(\\) with float\\|int\\|numeric\\-string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/track_anything/history.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_numeric\\(\\) with float\\|int\\|numeric\\-string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/track_anything/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_countable\\(\\) with list will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/finder/document_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with OpenEMR\\\\Modules\\\\FaxSMS\\\\EtherFax\\\\FaxStatus will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function property_exists\\(\\) with OpenEMR\\\\Modules\\\\FaxSMS\\\\EtherFax\\\\FaxStatus and \'FaxResult\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with null will always evaluate to true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/EtherFax/EtherFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_countable\\(\\) with non\\-empty\\-list\\<string\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_countable\\(\\) with non\\-empty\\-list\\<string\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/weno_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with ModuleManagerListener and \'moduleManagerAction\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_numeric\\(\\) with int will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array\\{array\\{callback_url\\: non\\-falsy\\-string, providers\\?\\: non\\-empty\\-list\\<non\\-empty\\-array\\>, facilities\\?\\: non\\-empty\\-list\\<non\\-empty\\-array\\>, categories\\?\\: non\\-empty\\-list\\<non\\-empty\\-array\\>, apptstats\\?\\: non\\-empty\\-list\\<non\\-empty\\-array\\>, checkedOut\\?\\: non\\-empty\\-list\\<non\\-empty\\-array\\>, clinical_reminders\\?\\: non\\-empty\\-list\\<non\\-empty\\-array\\>\\}\\} will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_numeric\\(\\) with int will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with CqmPopulationCrtiteriaFactory and \'createDenominatorEx…\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Cqm/library/AbstractCqmReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_numeric\\(\\) with int\\<min, \\-1\\>\\|int\\<1, max\\> will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array\\{pmt\\: 0\\|\'\'\\|float, fee\\: 0\\|float, clmpmt\\: 0\\|float, clmadj\\: 0\\|float, ptrsp\\: 0\\|float, svcptrsp\\: 0\\|float, svcfee\\: float\\|int, svcpmt\\?\\: \\(float\\|int\\), \\.\\.\\.\\} will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with list\\<mixed\\> will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with list\\<non\\-empty\\-list\\<string\\>\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with non\\-empty\\-array\\<non\\-falsy\\-string, list\\<non\\-empty\\-list\\<string\\>\\>\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array\\<mixed, mixed\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array\\<mixed\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_float\\(\\) with float will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with non\\-falsy\\-string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with non\\-falsy\\-string will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with non\\-falsy\\-string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_countable\\(\\) with array\\<int\\<0, max\\>, non\\-empty\\-array\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with non\\-falsy\\-string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Config_File_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with non\\-empty\\-list\\<mixed\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.fetch.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with object will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with Phreezable will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with ADORecordSet will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array\\<mixed\\> will always evaluate to true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Session/PHPSessionWrapper.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/ModulesApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with non\\-empty\\-array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_int\\(\\) with int\\<min, \\-1\\>\\|int\\<1, max\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/SectionEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/Export/ExportJob.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/PHPFHIRResponseParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with ADOConnection will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with list\\<mixed\\> will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with ADORecordSet will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with non\\-empty\\-list\\<string\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with non\\-empty\\-array will always evaluate to true\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_int\\(\\) with int will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirExportJobService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array\\<string, OpenEMR\\\\Services\\\\Search\\\\ISearchField\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Measure.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_iterable\\(\\) with \\*NEVER\\* will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Measure.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with \\*NEVER\\* will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Measure.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function property_exists\\(\\) with OpenEMR\\\\Services\\\\Qdm\\\\PopulationSet and \'stratifications\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Measure.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/QrdaReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with non\\-empty\\-array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_numeric\\(\\) with \\*NEVER\\* will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FhirSearchWhereClauseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with \\*NEVER\\* will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FhirSearchWhereClauseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with \\*NEVER\\* and \'__toString\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchFieldComparableValue.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_callable\\(\\) with callable\\(\\)\\: mixed will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/TokenSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_int\\(\\) with int will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Tests\\\\Isolated\\\\Validators\\\\Rules\\\\ListOptionRuleStub and \'getMessageParameters\' will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Rules/ListOptionRuleTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Tests\\\\Isolated\\\\Validators\\\\Rules\\\\ListOptionRuleStub and \'validate\' will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Rules/ListOptionRuleTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Telemetry\\\\GeoTelemetry and \'anonymizeIp\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/GeoTelemetryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Telemetry\\\\GeoTelemetry and \'getGeoData\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/GeoTelemetryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Telemetry\\\\GeoTelemetry and \'getServerGeoData\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/GeoTelemetryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_callable\\(\\) with Closure\\(\\)\\: array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_callable\\(\\) with Closure\\(array, string\\)\\: bool will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Telemetry\\\\TelemetryRepository and \'clearTelemetryData\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Telemetry\\\\TelemetryRepository and \'fetchUsageRecords\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Telemetry\\\\TelemetryRepository and \'saveTelemetryEvent\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Telemetry\\\\TelemetryService and \'trackApiRequestEvent\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Tests\\\\Isolated\\\\Validators\\\\ImmunizationValidatorStub and \'configureValidator\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/ImmunizationValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Validators\\\\OpenEMRChain and \'listOption\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/OpenEMRChainTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Validators\\\\OpenEMRParticleValidator and \'buildChain\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/OpenEMRParticleValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with OpenEMR\\\\Tests\\\\Isolated\\\\Validators\\\\PatientValidatorStub and \'isExistingUuid\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_callable\\(\\) with Closure\\(mixed, mixed, mixed\\=\\)\\: mixed will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Billing/InvoiceSummaryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_callable\\(\\) with Closure\\(mixed, mixed\\)\\: mixed will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Billing/InvoiceSummaryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with \'OpenEMR\\\\\\\\Billing\\\\\\\\InvoiceSummary\' and \'arGetInvoiceSummary\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Billing/InvoiceSummaryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function method_exists\\(\\) with \'OpenEMR\\\\\\\\Billing\\\\\\\\InvoiceSummary\' and \'arResponsibleParty\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Billing/InvoiceSummaryTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
