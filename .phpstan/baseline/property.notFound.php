<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$provider_number_type_array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$rendering_provider_number_type_array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Pharmacy\\:\\:\\$patient\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Cache_Lite\\:\\:\\$_memoryCachingState\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Profiler\\:\\:\\$output_enabled\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/profiler.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Profiler\\:\\:\\$trace_enabled\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/profiler.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property AbstractClickmapModel\\:\\:\\$hideNav\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property eRxPage\\:\\:\\$prescriptions\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$GetPatientAllergyHistoryV3Result\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$GetPatientFreeFormAllergyHistoryResult\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$GetPatientFullMedicationHistory6Result\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property OpenEMR\\\\Modules\\\\FaxSMS\\\\EtherFax\\\\FaxAccount\\:\\:\\$TimeZone\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Confidence\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$JobId\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Name\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$ReceivedOn\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/FaxDocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property RingCentral\\\\SDK\\\\Http\\\\ApiException\\:\\:\\$apiResponse\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property OpenEMR\\\\Modules\\\\FaxSMS\\\\EtherFax\\\\FaxAccount\\:\\:\\$TimeZone\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/EtherFax/EtherFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Carecoordination\\\\Model\\\\ModuleconfigTable\\:\\:\\$applicationTable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/ModuleconfigTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Installer\\\\Controller\\\\InstallerController\\:\\:\\$helperObject\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Installer\\\\Model\\\\InstModule\\:\\:\\$mod_enc_menu\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Installer\\\\Model\\\\InstModule\\:\\:\\$mod_nick_name\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$GetAccountStatusResult\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/soap_functions/soap_accountStatusDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property ParticipantsController\\:\\:\\$groupEventsModel\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property ParticipantsController\\:\\:\\$groupModel\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property ParticipantsController\\:\\:\\$groupParticipantsModel\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property TherapyGroupsController\\:\\:\\$counselorsModel\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property MedExApi\\\\Display\\:\\:\\$lastError\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property MedExApi\\\\Events\\:\\:\\$lastError\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property MedExApi\\\\Practice\\:\\:\\$lastError\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property MedExApi\\\\Setup\\:\\:\\$lastError\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property CouchDB\\:\\:\\$body\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property CouchDB\\:\\:\\$dbase\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property CouchDB\\:\\:\\$headers\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property CouchDB\\:\\:\\$host\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property CouchDB\\:\\:\\$pass\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property CouchDB\\:\\:\\$port\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property CouchDB\\:\\:\\$user\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$ensureVisible\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$parent\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property RsPatient\\:\\:\\$object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/library/AbstractAmcReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property smtp_class\\:\\:\\$pending_sender\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Thumbnail\\:\\:\\$max_size\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property CacheMemCache\\:\\:\\$LastServerError\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheMemCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property DataSet\\:\\:\\$_total\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property MockRouter\\:\\:\\$delim\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/MockRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property MockRouter\\:\\:\\$stripApi\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/MockRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property PHPRenderEngine\\:\\:\\$templatePath\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PHPRenderEngine.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property KeyMap\\:\\:\\$ColumnName\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property KeyMap\\:\\:\\$TableName\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Savant3\\:\\:\\$url\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/SavantRenderEngine.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Encounter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$FullDocument\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Pid\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Provider\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$ActionTakenTime\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$ActionTaken\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$ActionUser\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Activity\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Checksum\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Date\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Narrative\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PendingAction\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$RequireAudit\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Status\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$TableAction\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$TableArgs\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$AllowHealthInfoEx\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$AllowImmInfoShare\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$AllowImmRegUse\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$AllowPatientPortal\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$CareTeam\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$City\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$ContactRelationship\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$CountryCode\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$County\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Date\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Dob\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$DriversLicense\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$EmailDirect\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Email\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Ethnicity\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Ethnoracial\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$FamilySize\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Fname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Guardiansname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaAllowemail\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaAllowsms\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaMail\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaMessage\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaNotice\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaVoice\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Language\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Lname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Mname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Mothersname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Occupation\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PharmacyId\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PhoneBiz\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PhoneCell\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PhoneContact\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PhoneHome\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PostalCode\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Providerid\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Race\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$RefProviderid\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Referrer\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Referrerid\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Regdate\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Religion\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Sex\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Ss\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$State\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Status\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Street\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Title\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$AllowHealthInfoEx\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$AllowImmInfoShare\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$AllowImmRegUse\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$AllowPatientPortal\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$CareTeam\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$City\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$ContactRelationship\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$CountryCode\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$County\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Date\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Dob\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$DriversLicense\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$EmailDirect\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Email\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Ethnicity\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Ethnoracial\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$FamilySize\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Fname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Guardiansname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaAllowemail\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaAllowsms\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaMail\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaMessage\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaNotice\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$HipaaVoice\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Language\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Lname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Mname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Mothersname\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Note\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Occupation\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PharmacyId\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PhoneBiz\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PhoneCell\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PhoneContact\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PhoneHome\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$PostalCode\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Providerid\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Pubpid\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Race\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$RefProviderid\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Referrer\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Referrerid\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Religion\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Sex\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Ss\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$State\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Status\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Street\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$Title\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property Criteria\\:\\:\\$Pid_Equals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Reporter/PatientReporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property BaseController\\:\\:\\$viewBean\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$kid\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/JsonWebKeySet.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property OpenEMR\\\\Common\\\\ORDataObject\\\\ContactAddress\\:\\:\\$use\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property OpenEMR\\\\Gacl\\\\Gacl\\:\\:\\$Cache_Lite\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property OpenEMR\\\\Gacl\\\\GaclApi\\:\\:\\$Cache_Lite\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$label\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Menu/PatientMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property DOMNameSpaceNode\\|DOMNode\\:\\:\\$textContent\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$dataElementCodes\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qdm/CqmCalculator.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$negationRationale\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$reason\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$dosage\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$frequency\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$negationRationale\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$reason\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$result\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$stratifications\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/AggregateCount.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$family\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat1.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property object\\:\\:\\$family\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat3.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property OpenEMR\\\\Services\\\\Search\\\\CompositeSearchField\\:\\:\\$getValues\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/CompositeSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:\\$additional_users\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:\\$collate\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:\\$conffile\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:\\$dbh\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:\\$dumpfiles\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:\\$error_message\\.$#',
    'count' => 43,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:\\$new_theme\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
