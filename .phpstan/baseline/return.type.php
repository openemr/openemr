<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function convert_type_id_to_key\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Hashed_Cache_Lite\\:\\:clean\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Hashed_Cache_Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Cache_Lite\\:\\:_read\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Cache_Lite\\:\\:_read\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Cache_Lite\\:\\:get\\(\\) should return string but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Cache_Lite\\:\\:get\\(\\) should return string but returns mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Cache_Lite\\:\\:lastModified\\(\\) should return int but returns int\\<0, max\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sellDrug\\(\\) should return bool\\|int\\|void but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getAccountId\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getAccountName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getAccountPassword\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getDebugSetting\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getDefaultPatientCountry\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getDisplayAllergy\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getDisplayMedication\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getEnabled\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getImportStatusMessage\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getOpenEMRSiteDirectory\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getPartnerName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getPath\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getTTLSoapAllergies\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getTTLSoapMedications\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxGlobals\\:\\:getUploadActive\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxPage\\:\\:buildXML\\(\\) should return eRxPage but returns array\\<string, mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxPage\\:\\:getAuthUserId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxPage\\:\\:getDestination\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxPage\\:\\:getPatientId\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxPage\\:\\:getPrescriptionCount\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxPage\\:\\:getPrescriptionIds\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxPage\\:\\:getXMLBuilder\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxPage\\:\\:getXML\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getAccountId\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getAccountStatus\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getAuthUserDetails\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getAuthUserId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getGlobals\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getPatientAllergyHistoryV3\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getPatientFreeFormAllergyHistory\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getPatientFullMedicationHistory6\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getPatientId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getSiteId\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getSoapClient\\(\\) should return SoapClient but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getSoapSettings\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getStore\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:getTTL\\(\\) should return bool\\|float\\|int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:htmlFixXmlToArray\\(\\) should return array\\|bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:initializeSoapClient\\(\\) should return SoapClient but returns eRxSOAP\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:insertMissingListOptions\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxSOAP\\:\\:setGlobals\\(\\) should return eRxPage but returns \\$this\\(eRxSOAP\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:getFacilityPrimary\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:getLastSOAP\\(\\) should return bool\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:getPatientByPatientId\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:getPatientImportStatusByPatientId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:getPrescriptionById\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:getUserById\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:getUserFacility\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:sanitizeNumber\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:selectActiveAllergiesByPatientId\\(\\) should return resource but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:selectAllergyErxSourceByPatientIdName\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:selectFederalEin\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:selectOptionIdByTitle\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:selectOptionIdsByListId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:selectPrescriptionIdByGuidPatientId\\(\\) should return resource but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxStore\\:\\:selectUserIdByUserName\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxXMLBuilder\\:\\:getGlobals\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxXMLBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxXMLBuilder\\:\\:getStore\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxXMLBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxXMLBuilder\\:\\:setGlobals\\(\\) should return eRxPage but returns \\$this\\(eRxXMLBuilder\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxXMLBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method eRxXMLBuilder\\:\\:setStore\\(\\) should return eRxPage but returns \\$this\\(eRxXMLBuilder\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxXMLBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Function build_CODING_items\\(\\) should return object but returns array\\<int, array\\<string, mixed\\>\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function build_IMPPLAN_items\\(\\) should return object but returns array\\<int\\<0, max\\>, array\\<string, mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_GlaucomaFlowSheet\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_VisualAcuities\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function load_fee_sheet_options\\(\\) should return an but returns list\\<fee_sheet_option\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_options_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_appt_data\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_form_id_of_existing_attendance_form\\(\\) should return array\\|null but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_group_encounter_data\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function if_to_create_for_patient\\(\\) should return bool but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_lab_name\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnConfigGetVar\\(\\) should return value but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnConfigGetVar\\(\\) should return value but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnDBGetTables\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnGetBaseURL\\(\\) should return base but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnVarPrepForStore\\(\\) should return list\\<string\\>\\|string but returns list\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnVarPrepForStore\\(\\) should return list\\<string\\>\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:EndPage\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:StartPage\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Method pnHTML\\:\\:Text\\(\\) should return string but returns list\\<string\\|null\\>\\|string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModAPILoad\\(\\) should return true but returns false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModAvailable\\(\\) should return true but returns false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModLoad\\(\\) should return string\\|false\\|null but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModURL\\(\\) should return absolute but returns false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModURL\\(\\) should return absolute but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_getmonthname\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_getmonthname\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Date_Calc\\:\\:NWeekdayOfMonth\\(\\) should return string but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Date_Calc\\:\\:dateToDays\\(\\) should return int but returns float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Date_Calc\\:\\:dayOfWeek\\(\\) should return int but returns float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Date_Calc\\:\\:getMonthFromFullName\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Date_Calc\\:\\:getMonthFullname\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Date_Calc\\:\\:getWeekdayFullname\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Date_Calc\\:\\:weekOfYear\\(\\) should return int but returns float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Date_Calc\\:\\:weeksInMonth\\(\\) should return int but returns float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Holidays_Controller\\:\\:get_last_error\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/holidays/Holidays_Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Holidays_Controller\\:\\:get_target_file\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/holidays/Holidays_Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ModuleManagerListener\\:\\:moduleManagerAction\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleHealthVideoRegistrationController\\:\\:shouldCreateRegistrationForProvider\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthVideoRegistrationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleconferenceRoomController\\:\\:getApiKeyForPassword\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleconferenceRoomController\\:\\:getParticipantListForAppointment\\(\\) should return array\\<array\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleconferenceRoomController\\:\\:isPendingAppointment\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Repository\\\\TeleHealthPersonSettingsRepository\\:\\:saveSettingsForPerson\\(\\) should return Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthPersonSettings but returns Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthPersonSettings\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Repository/TeleHealthPersonSettingsRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Repository\\\\TeleHealthUserRepository\\:\\:createResultRecordFromDatabaseResult\\(\\) should return array\\<string, mixed\\> but returns Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Repository/TeleHealthUserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Repository\\\\TeleHealthUserRepository\\:\\:getUser\\(\\) should return Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Repository/TeleHealthUserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Services\\\\TeleHealthRemoteRegistrationService\\:\\:addNewUser\\(\\) should return int\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthRemoteRegistrationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Services\\\\TeleHealthRemoteRegistrationService\\:\\:getHttpClient\\(\\) should return GuzzleHttp\\\\Client but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthRemoteRegistrationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Services\\\\TeleHealthRemoteRegistrationService\\:\\:updateUserFromRequest\\(\\) should return int\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthRemoteRegistrationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ModuleManagerListener\\:\\:moduleManagerAction\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\DashboardContext\\\\Controller\\\\ContextWidgetController\\:\\:renderNavbarDropdown\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/ContextWidgetController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\DashboardContext\\\\Controller\\\\ContextWidgetController\\:\\:renderWidget\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/ContextWidgetController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\DashboardContext\\\\Services\\\\DashboardContextAdminService\\:\\:getRoleDefaultContext\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Services/DashboardContextAdminService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\DashboardContext\\\\Services\\\\DashboardContextService\\:\\:getActiveContext\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Services/DashboardContextService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\DashboardContext\\\\Services\\\\DashboardContextService\\:\\:isWidgetVisible\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Services/DashboardContextService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ModuleManagerListener\\:\\:moduleManagerAction\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\DornGenHl7Order\\:\\:createGt1\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\DornGenHl7Order\\:\\:createIn1\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\DornGenHl7Order\\:\\:createObr\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\DornGenHl7Order\\:\\:createOrc\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\DornGenHl7Order\\:\\:createPid\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\DornGenHl7Order\\:\\:createPv1\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\DornGenHl7Order\\:\\:createTq1\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\DornGenHl7Order\\:\\:sendHl7Order\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\ReceiveHl7Results\\:\\:rhl7DecodeData\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\ReceiveHl7Results\\:\\:rhl7DecodeData\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\EhiExporter\\\\Models\\\\ExportState\\:\\:getTableDefinitionForTable\\(\\) should return OpenEMR\\\\Modules\\\\EhiExporter\\\\TableDefinitions\\\\ExportTableDefinition\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/ExportState.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\EhiExporter\\\\Services\\\\EhiExporter\\:\\:createExportTasksFromJob\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\EhiExporter\\\\Services\\\\EhiExporter\\:\\:createJobForRequest\\(\\) should return OpenEMR\\\\Modules\\\\EhiExporter\\\\Models\\\\EhiExportJob but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\EhiExporter\\\\Services\\\\EhiExporter\\:\\:exportAll\\(\\) should return OpenEMR\\\\Modules\\\\EhiExporter\\\\Models\\\\EhiExportJob but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\EhiExporter\\\\Services\\\\EhiExporter\\:\\:runExportTask\\(\\) should return OpenEMR\\\\Modules\\\\EhiExporter\\\\Models\\\\EhiExportJobTask but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ModuleManagerListener\\:\\:moduleManagerAction\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\BootstrapService\\:\\:fetchPersistedSetupSettings\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/BootstrapService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\BootstrapService\\:\\:getVendorGlobal\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/BootstrapService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\AppDispatch\\:\\:getApiService\\(\\) should return OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|void\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\AppDispatch\\:\\:getLoggedInUser\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\:\\:authenticate\\(\\) should return int but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/ClickatellSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\:\\:fetchReminderCount\\(\\) should return bool\\|string but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/ClickatellSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\:\\:authenticate\\(\\) should return int but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:authenticate\\(\\) should return int but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:disposeDocument\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:fetchReminderCount\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:formatFax\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:getUser\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:parseValidators\\(\\) should return array\\<string\\> but returns array\\<string, mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:sendFax\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:setFaxDeleted\\(\\) should return bool but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:validatePhone\\(\\) should return bool but returns int\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:viewFax\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:chartFaxDocument\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:disposeDocument\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:fetchTextMessage\\(\\) should return string but returns int\\<min, 0\\>\\|int\\<2, max\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:fetchTextMessage\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:formatFax\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:getCachedAuth\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:getCredentials\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:getSipProvision\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:getUser\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:getVoicemailAttachment\\(\\) should return string\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:viewFax\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:assignFax\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:fetchReminderCount\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:forwardFax\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:sendFax\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:sendSMS\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:validatePhone\\(\\) should return bool but returns int\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\:\\:authenticate\\(\\) should return int but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\:\\:fetchReminderCount\\(\\) should return bool\\|string but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\VoiceClient\\:\\:getCachedAuth\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/VoiceClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\VoiceClient\\:\\:getCredentials\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/VoiceClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\EtherFax\\\\FaxResult\\:\\:getFaxResult\\(\\) should return int\\|string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/EtherFax/FaxResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\EtherFax\\\\FaxState\\:\\:getFaxState\\(\\) should return int\\|string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/EtherFax/FaxState.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Events\\\\NotificationEventListener\\:\\:getRCCredentials\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ModuleManagerListener\\:\\:moduleManagerAction\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Juggernaut\\\\OpenEMR\\\\Modules\\\\PriorAuthModule\\\\Controller\\\\AuthorizationService\\:\\:listPatientAuths\\(\\) should return ADORecordSet_mysqli\\|array\\|false but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/AuthorizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Juggernaut\\\\OpenEMR\\\\Modules\\\\PriorAuthModule\\\\Controller\\\\ListAuthorizations\\:\\:getAllAuthorizations\\(\\) should return ADORecordSet_mysqli\\|array\\|false but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/ListAuthorizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ModuleManagerListener\\:\\:moduleManagerAction\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Bootstrap\\:\\:moduleSqlUpgrade\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Bootstrap\\:\\:moduleSqlUpgrade\\(\\) should return string but returns true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\DownloadWenoPharmacies\\:\\:extractFile\\(\\) should return string\\|null but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\DownloadWenoPharmacies\\:\\:extractFile\\(\\) should return string\\|null but returns int\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\DownloadWenoPharmacies\\:\\:retrieveDataFile\\(\\) should return string\\|null but returns true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:getAge\\(\\) should return string but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:getFacilityInfo\\(\\) should return array\\|false\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:getPayload\\(\\) should return string\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\WenoValidate\\:\\:extractValidationResult\\(\\) should return bool\\|string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoValidate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\WenoValidate\\:\\:sendRequest\\(\\) should return array\\|string\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoValidate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\WenoValidate\\:\\:validateAdminCredentials\\(\\) should return bool but returns int\\<998, max\\>\\|string\\|true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoValidate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\WenoValidate\\:\\:validateAdminCredentials\\(\\) should return bool but returns int\\<min, 997\\>\\|string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoValidate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\Acl\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/Acl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\AclTable\\:\\:aclSections\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/AclTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\AclTable\\:\\:aclUserGroupMapping\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/AclTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\AclTable\\:\\:getAclDataGroups\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/AclTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\AclTable\\:\\:getAclDataUsers\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/AclTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\AclTable\\:\\:getActiveModules\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/AclTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\AclTable\\:\\:getGroupAcl\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/AclTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\AclTable\\:\\:getGroups\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/AclTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Acl\\\\Model\\\\AclTable\\:\\:getModuleSections\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Model/AclTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Controller\\\\IndexController\\:\\:listAutoSuggest\\(\\) should return array\\<string, mixed\\> but returns array\\<int\\|string, array\\<mixed\\>\\|int\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Controller\\\\IndexController\\:\\:searchAction\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Helper\\\\SendToHieHelper\\:\\:__invoke\\(\\) should return array but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\Application\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/Application.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\ApplicationTable\\:\\:dateFormat\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\SendtoTable\\:\\:getFacility\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/SendtoTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\SendtoTable\\:\\:getFaxRecievers\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/SendtoTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\SendtoTable\\:\\:getUsers\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/SendtoTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Controller\\\\CarecoordinationController\\:\\:getCarecoordinationTable\\(\\) should return Carecoordination\\\\Model\\\\CarecoordinationTable but returns Carecoordination\\\\Controller\\\\Carecoordination\\\\Model\\\\CarecoordinationTable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Controller\\\\CarecoordinationController\\:\\:indexAction\\(\\) should return Laminas\\\\View\\\\Model\\\\ViewModel but returns Laminas\\\\Http\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Controller\\\\CcdController\\:\\:getCarecoordinationTable\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CcdController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Controller\\\\EncounterccdadispatchController\\:\\:getEncounterccdadispatchTable\\(\\) should return Carecoordination\\\\Model\\\\EncounterccdadispatchTable but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Controller\\\\EncountermanagerController\\:\\:buildCCDAHtml\\(\\) should return string\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Controller\\\\MapperController\\:\\:getMapperTable\\(\\) should return Carecoordination\\\\Model\\\\MapperTable but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\CarecoordinationTable\\:\\:cleanCcdaXmlContent\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\CarecoordinationTable\\:\\:getCCDAComponents\\(\\) should return array\\<string\\> but returns array\\<mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\CarecoordinationTable\\:\\:getImportService\\(\\) should return OpenEMR\\\\Services\\\\Cda\\\\CdaTemplateImportDispose but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\CcdaGenerator\\:\\:socket_get\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\CcdaServiceRequestModelGenerator\\:\\:getCreatedTime\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceRequestModelGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\CcdaServiceRequestModelGenerator\\:\\:getData\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceRequestModelGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\Configuration\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface\\<mixed\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getAge\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getCCDAComponents\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getCarecoordinationModuleSettingValue\\(\\) should return null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getDocumentAuthorRecord\\(\\) should return null but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getLatestEncounter\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getMostRecentPatientReferral\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getPreviousAddresses\\(\\) should return array\\<OpenEMR\\\\Common\\\\ORDataObject\\\\ContactAddress\\> but returns list\\<array\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getPreviousNames\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getSettings\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Ccr\\\\Controller\\\\CcrController\\:\\:getCcrTable\\(\\) should return Ccr\\\\Model\\\\CcrTable but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Ccr\\\\Model\\\\Ccr\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/Ccr.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Documents\\\\Model\\\\Documents\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/Documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Documents\\\\Model\\\\DocumentsTable\\:\\:getCategories\\(\\) should return array\\<int, array\\{category_id\\: mixed, category_name\\: mixed\\}\\> but returns array\\<array\\{category_id\\: mixed, category_name\\: mixed\\}\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/DocumentsTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Documents\\\\Model\\\\DocumentsTable\\:\\:getCategoryIDs\\(\\) should return array\\<string, mixed\\> but returns array\\<mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/DocumentsTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Documents\\\\Model\\\\DocumentsTable\\:\\:getCategory\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/DocumentsTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Documents\\\\Model\\\\DocumentsTable\\:\\:getDocument\\(\\) should return array\\<string, mixed\\>\\|false but returns array\\<mixed\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/DocumentsTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Documents\\\\Plugin\\\\Documents\\:\\:getDocument\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Plugin/Documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Controller\\\\ImmunizationController\\:\\:format_cvx_code\\(\\) should return string but returns int\\<10, max\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\Immunization\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/Immunization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\ImmunizationTable\\:\\:codeslist\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\ImmunizationTable\\:\\:getImmunizationObservationResultsData\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\ImmunizationTable\\:\\:immunizedPatientDetails\\(\\) should return int\\|list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Controller\\\\InstallerController\\:\\:DisableModule\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Controller\\\\InstallerController\\:\\:EnableModule\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Controller\\\\InstallerController\\:\\:InstallModuleACL\\(\\) should return bool but returns array\\<int, string\\|false\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Controller\\\\InstallerController\\:\\:InstallModule\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Controller\\\\InstallerController\\:\\:UnregisterModule\\(\\) should return bool\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Controller\\\\InstallerController\\:\\:getModuleId\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Controller\\\\InstallerController\\:\\:upgradeAclFromVersion\\(\\) should return int\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModule\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface but returns Laminas\\\\InputFilter\\\\InputFilterInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModuleTable\\:\\:installSQL\\(\\) should return bool but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModuleTable\\:\\:loadModuleConfigFile\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModuleTable\\:\\:register\\(\\) should return bool but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModuleTable\\:\\:validateNickName\\(\\) should return bool but returns int\\<0, max\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Patientvalidation\\\\Controller\\\\BaseController\\:\\:getPostParamsArray\\(\\) should return Patientvalidation\\\\Controller\\\\post but returns array\\<int\\|string, array\\<mixed\\>\\|string\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Patientvalidation\\\\Controller\\\\BaseController\\:\\:getUserId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Patientvalidation\\\\Controller\\\\PatientvalidationController\\:\\:indexAction\\(\\) should return Laminas\\\\Stdlib\\\\ResponseInterface but returns array\\<string, mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/PatientvalidationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Patientvalidation\\\\Model\\\\PatientData\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Model/PatientData.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Patientvalidation\\\\Model\\\\PatientDataTable\\:\\:getPatients\\(\\) should return list\\<array\\<string, mixed\\>\\> but returns list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Model/PatientDataTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Syndromicsurveillance\\\\Model\\\\Syndromicsurveillance\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface\\<mixed\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/Syndromicsurveillance.php',
];
$ignoreErrors[] = [
    'message' => '#^Function receive_hl7_results\\(\\) should return array but returns mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7DecodeData\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7DecodeData\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hs_lo_title\\(\\) should return string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_widget.php',
];
$ignoreErrors[] = [
    'message' => '#^Method TherapyGroupsController\\:\\:alreadyExist\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Method TherapyGroupsController\\:\\:saveNewGroup\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Method TherapyGroupsController\\:\\:updateGroup\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Group_Statuses\\:\\:getGroupAttendanceStatuses\\(\\) should return ADORecordSet_mysqli but returns list\\<array\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_models/group_statuses_model.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Group_Statuses\\:\\:getGroupStatuses\\(\\) should return ADORecordSet_mysqli but returns list\\<array\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_models/group_statuses_model.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Therapy_Groups_Encounters\\:\\:getGroupEncounters\\(\\) should return ADORecordSet_mysqli but returns list\\<array\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_models/therapy_groups_encounters_model.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Therapy_Groups_Events\\:\\:getGroupEvents\\(\\) should return ADORecordSet_mysqli but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_models/therapy_groups_events_model.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Users\\:\\:checkIfMultiple\\(\\) should return ADORecordSet_mysqli\\|bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_models/users_model.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ESign\\\\Api\\:\\:configToJson\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Api.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ESign\\\\ESign\\:\\:isLocked\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/ESign.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ESign\\\\ESign\\:\\:isLogViewable\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/ESign.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MedExApi\\\\Events\\:\\:addRecurrent\\(\\) should return array\\|bool but returns int\\<0, max\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MedExApi\\\\Events\\:\\:process\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MedExApi\\\\Events\\:\\:process_deletes\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Document\\:\\:getDocumentsForForeignReferenceId\\(\\) should return array\\<Document\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Document\\:\\:getDocumentsForPatient\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Document\\:\\:get_data\\(\\) should return string\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Document\\:\\:get_date_expires\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Document\\:\\:get_deleted\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Document\\:\\:get_foreign_reference_id\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Document\\:\\:get_foreign_reference_table\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Document\\:\\:get_name\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\:\\:create_database\\(\\) should return bool but returns bool\\|mysqli_result\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\:\\:drop_database\\(\\) should return bool but returns bool\\|mysqli_result\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\:\\:getCurrentTheme\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\:\\:grant_privileges\\(\\) should return bool but returns bool\\|mysqli_result\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\:\\:mysqliNumRows\\(\\) should return int but returns int\\<0, max\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method POSRef\\:\\:get_pos_ref\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/POSRef.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Totp\\:\\:getSecret\\(\\) should return string but returns bool\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Totp.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method HTML_TreeMenu\\:\\:addItem\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Method HTML_TreeMenu\\:\\:createFromStructure\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Method AmcItemizedActionData\\:\\:getDenominatorActionData\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/library/AmcItemizedActionData.php',
];
$ignoreErrors[] = [
    'message' => '#^Method AmcItemizedActionData\\:\\:getNumeratorActionData\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/library/AmcItemizedActionData.php',
];
$ignoreErrors[] = [
    'message' => '#^Method AMC_315g_2c_Denominator\\:\\:getItemizedDataForLastTest\\(\\) should return AmcItemizedActionData but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_2c/Denominator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method AMC_315g_2c_Numerator\\:\\:getItemizedDataForLastTest\\(\\) should return AmcItemizedActionData but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_2c/Numerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method AMC_315g_2c_Numerator\\:\\:test\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_2c/Numerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method AMC_315g_7_Denominator\\:\\:getItemizedDataForLastTest\\(\\) should return AmcItemizedActionData but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_7/Denominator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method AMC_315g_7_Numerator\\:\\:getItemizedDataForLastTest\\(\\) should return AmcItemizedActionData but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_7/Numerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method RsPopulation\\:\\:current\\(\\) should return RsPatient but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/library/RsPopulation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Thumbnail\\:\\:create_thumbnail\\(\\) should return resource but returns GdImage\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Thumbnail\\:\\:create_thumbnail\\(\\) should return resource but returns \\(GdImage\\|false\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Thumbnail\\:\\:create_thumbnail\\(\\) should return resource but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Thumbnail\\:\\:get_string_file\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ThumbnailGenerator\\:\\:generate_couch_file\\(\\) should return bool\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Function active_alert_summary\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_plan\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_rule\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertDobtoAgeMonthDecimal\\(\\) should return float but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertDobtoAgeYearDecimal\\(\\) should return float but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function database_check\\(\\) should return bool but returns string\\|true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function listingCDRReminderLog\\(\\) should return sqlret but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lists_check\\(\\) should return bool but returns string\\|true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function procedure_check\\(\\) should return bool but returns string\\|true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function test_rules_clinic_cqm_amc_rule\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_crt\\(\\) should return data but returns OpenSSLCertificate\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/create_ssl_certificate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_csr\\(\\) should return array but returns false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/create_ssl_certificate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_user_certificate\\(\\) should return string but returns false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/create_ssl_certificate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_user_certificate\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/create_ssl_certificate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_notify\\(\\) should return true but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_notify\\(\\) should return true but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_extension\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_err_report\\(\\) should return array but returns string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_error\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_create_zip\\(\\) should return bool but returns int\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_csv_array\\(\\) should return array but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_csv_combine\\(\\) should return string but returns int\\<0, max\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_csv_split\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_date\\(\\) should return string but returns false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_filenames\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_move_old\\(\\) should return int but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_report\\(\\) should return array but returns string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_csv_to_html\\(\\) should return string but returns false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_archive_select_list\\(\\) should return array but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_array_flatten\\(\\) should return array but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_check_filepath\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_check_x12_obj\\(\\) should return object but returns false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_dirfile_list\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_edih_tmpdir\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_edihist_log\\(\\) should return int but returns int\\<0, max\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_file_by_controlnum\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_file_by_enctr\\(\\) should return array\\|bool but returns string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_file_by_trace\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_file_type\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_log_manage\\(\\) should return array but returns string\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_newfile_list\\(\\) should return array but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_processed_files_list\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_search_record\\(\\) should return array but returns false\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_setup\\(\\) should return bool but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_table_header\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_table_select_list\\(\\) should return array but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_thead_html\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_csv_write\\(\\) should return bool but returns int\\<0, max\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_278_csv_data\\(\\) should return array but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_parse_date\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_archive_report\\(\\) should return string but returns array\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_php_inivals\\(\\) should return array but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_upload_files\\(\\) should return array but returns false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Method edih_x12_file\\:\\:edih_gs_type\\(\\) should return bool\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method edih_x12_file\\:\\:edih_x12_type\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method edih_x12_file\\:\\:edih_x12_type\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oeFormatSDFT\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formatting.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function escape_identifier\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function escape_identifier\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function escape_limit\\(\\) should return string but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formDataCore\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function process_cols_escape\\(\\) should return array but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addForm\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFormByEncounter\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Digits\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAge\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLayoutRes\\(\\) should return ADORecordSet_mysqli but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hl7Date\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function parse_note\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function js_escape\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/htmlspecialchars.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLabProviders\\(\\) should return array\\|null but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFacilities\\(\\) should return array\\|int but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientData\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_patient_balance\\(\\) should return float\\|int but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPnotesByUser\\(\\) should return int but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Config_File_Legacy\\:\\:get\\(\\) should return array\\|string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Config_File_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_compile_custom_tag\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_compile_custom_tag\\(\\) should return string but returns true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_compile_foreach_start\\(\\) should return string but returns mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_compile_insert_tag\\(\\) should return string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_compile_plugin_call\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_compile_smarty_ref\\(\\) should return string but returns null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_compile_tag\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_expand_quoted_text\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_parse_var_props\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Compiler_Legacy\\:\\:_pop_tag\\(\\) should return string but returns mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:_compile_source\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:_fetch_resource_info\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:_get_filter_name\\(\\) should return string but returns callback\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:_process_compiled_include_callback\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:_read_file\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:_run_mod_handler\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:_smarty_cache_attrs\\(\\) should return array but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:clear_cache\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:get_config_vars\\(\\) should return array but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:get_config_vars\\(\\) should return array but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:get_registered_object\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:get_template_vars\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:get_template_vars\\(\\) should return array but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Smarty_Legacy\\:\\:is_cached\\(\\) should return string\\|false but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_assemble_plugin_filepath\\(\\) should return string\\|false but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.assemble_plugin_filepath.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_is_secure\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_secure.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_is_trusted\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_trusted.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_process_cached_inserts\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_process_cached_inserts\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_process_compiled_include\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_compiled_include.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_rm_auto\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rm_auto.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_run_insert_handler\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_run_insert_handler\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_write_cache_file\\(\\) should return true\\|null but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_write_compiled_resource\\(\\) should return true but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_compiled_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_counter\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.counter.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_cycle\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.cycle.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_debug\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_popup\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_capitalize\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.capitalize.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_count_characters\\(\\) should return int but returns int\\<0, max\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.count_characters.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_count_paragraphs\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.count_paragraphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_count_sentences\\(\\) should return int but returns int\\<0, max\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.count_sentences.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_count_words\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.count_words.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_default\\(\\) should return string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.default.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_escape\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.escape.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_escape\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.escape.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_indent\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.indent.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_regex_replace\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.regex_replace.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_strip\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.strip.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_strip_tags\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.strip_tags.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_truncate\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.truncate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_escape_special_chars\\(\\) should return string but returns array\\<mixed, mixed\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/shared.escape_special_chars.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_make_timestamp\\(\\) should return string but returns int\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/shared.make_timestamp.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_db\\(\\) should return ADODB_mysqli_log but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlClose\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlGetAssoc\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function xl\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/translation.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getUserIDInfo\\(\\) should return array\\|false\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function prevSetting\\(\\) should return Prior but returns OpenEMR\\\\Services\\\\Globals\\\\Prior\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function removeUserSetting\\(\\) should return null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method xmltoarray_parser_htmlfix\\:\\:createArray\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/xmltoarray_parser_htmlfix.php',
];
$ignoreErrors[] = [
    'message' => '#^Method sms_clickatell\\:\\:getbalance\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/sms_clickatell.php',
];
$ignoreErrors[] = [
    'message' => '#^Method sms_tmb4\\:\\:_send\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/sms_tmb4.php',
];
$ignoreErrors[] = [
    'message' => '#^Method sms_tmb4\\:\\:_send_curl\\(\\) should return string but returns bool\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/sms_tmb4.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPidHolder\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method GlobalConfig\\:\\:GetContext\\(\\) should return Context but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Method GlobalConfig\\:\\:GetDefaultAction\\(\\) should return string but returns default\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Method GlobalConfig\\:\\:GetPhreezer\\(\\) should return Phreezer but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Method GlobalConfig\\:\\:GetRenderEngine\\(\\) should return IRenderEngine but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Method GlobalConfig\\:\\:GetRouter\\(\\) should return UrlWriter but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Savant3\\:\\:applyFilters\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Savant3\\:\\:getEscape\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Savant3\\:\\:getOutput\\(\\) should return string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Savant3\\:\\:template\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Savant3\\:\\:template\\(\\) should return string but returns object\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Savant3_Filter_trimwhitespace\\:\\:filter\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/resources/Savant3_Filter_trimwhitespace.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Mime_Types\\:\\:get_extension\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/Mime_Types.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Mime_Types\\:\\:get_file_type\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/Mime_Types.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Mime_Types\\:\\:get_file_type\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/Mime_Types.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Mime_Types\\:\\:get_type\\(\\) should return string but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/Mime_Types.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Mime_Types\\:\\:get_type\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/Mime_Types.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_enclose_value\\(\\) should return Processed but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_rfile\\(\\) should return Data but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_rfile\\(\\) should return Data but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_validate_offset\\(\\) should return true but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_validate_row_condition\\(\\) should return true but returns \'0\'\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_validate_row_condition\\(\\) should return true but returns \'1\'\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_validate_row_conditions\\(\\) should return true but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_validate_row_conditions\\(\\) should return true but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:_wfile\\(\\) should return true but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:auto\\(\\) should return delimiter but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:auto\\(\\) should return delimiter but returns int\\|string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method parseCSV\\:\\:unparse\\(\\) should return CSV but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Auth401\\:\\:GetPassword\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Authentication/Auth401.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Auth401\\:\\:GetUsername\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Authentication/Auth401.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataDriverMySQLi\\:\\:Execute\\(\\) should return int but returns int\\<\\-1, max\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataDriverMySQLi\\:\\:Fetch\\(\\) should return array but returns array\\<string, float\\|int\\|string\\|null\\>\\|false\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataDriverMySQLi\\:\\:Query\\(\\) should return resultset but returns mysqli_result\\|true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method BrowserDevice\\:\\:GetInstance\\(\\) should return BrowserDevice but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/BrowserDevice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Context\\:\\:Get\\(\\) should return value but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/Context.php',
];
$ignoreErrors[] = [
    'message' => '#^Method RequestUtil\\:\\:GetBody\\(\\) should return string but returns body\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method RequestUtil\\:\\:GetCurrency\\(\\) should return string but returns array\\<string\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method RequestUtil\\:\\:GetFileUpload\\(\\) should return FileUpload but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method RequestUtil\\:\\:GetPersisted\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method RequestUtil\\:\\:GetRemoteHost\\(\\) should return string but returns mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method RequestUtil\\:\\:GetRequestHeaders\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method RequestUtil\\:\\:Get\\(\\) should return array\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ActionRouter\\:\\:GetUrlParam\\(\\) should return string but returns array\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Criteria\\:\\:GetAnds\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Criteria\\:\\:GetFilters\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Criteria\\:\\:GetOrs\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataAdapter\\:\\:Escape\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataAdapter\\:\\:Execute\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataAdapter\\:\\:GetLastInsertId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataAdapter\\:\\:GetQuotedSql\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataAdapter\\:\\:GetTableNames\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataAdapter\\:\\:IsTransactionInProgress\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataPage\\:\\:Next\\(\\) should return Phreezable but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataPage\\:\\:ToObjectArray\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataPage\\:\\:current\\(\\) should return Phreezable but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataSet\\:\\:Count\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataSet\\:\\:GetDataPage\\(\\) should return DataPage but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataSet\\:\\:GetEmptyArray\\(\\) should return array but returns array\\|SplFixedArray\\<mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataSet\\:\\:GetLabelArray\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataSet\\:\\:Next\\(\\) should return Preezable but returns Preezable\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataSet\\:\\:ToObjectArray\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method DataSet\\:\\:_getObject\\(\\) should return Preezable but returns object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method FieldMap\\:\\:GetEnumValues\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/FieldMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method GenericRouter\\:\\:GetUri\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method GenericRouter\\:\\:GetUrlParam\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MockRouter\\:\\:GetUri\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/MockRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MockRouter\\:\\:GetUrlParam\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/MockRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MockRouter\\:\\:GetUrlParams\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/MockRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MockRouter\\:\\:GetUrl\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/MockRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method PHPRenderEngine\\:\\:fetch\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PHPRenderEngine.php',
];
$ignoreErrors[] = [
    'message' => '#^Method PHPRenderEngine\\:\\:getAll\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PHPRenderEngine.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:CacheLevel\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:Delete\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:GetCache\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:GetCustomQuery\\(\\) should return string but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:GetPrimaryKeyName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:GetPrimaryKeyValue\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:GetPublicProperties\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:GetValidationErrors\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:IsLoaded\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:IsPartiallyLoaded\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:NoCache\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezable\\:\\:Save\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:Compare\\(\\) should return bool but returns int\\<\\-1, 1\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetByCriteria\\(\\) should return Phreezable but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetCache\\(\\) should return Phreezable but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetCache\\(\\) should return Phreezable but returns null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetColumnName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetCustomCountQuery\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetCustomQuery\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetFieldMap\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetFieldMaps\\(\\) should return array but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetKeyMap\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetKeyMaps\\(\\) should return array but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetOneToMany\\(\\) should return Criteria but returns DataSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetPrimaryKeyMap\\(\\) should return KeyMap\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetTableName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetValueCache\\(\\) should return VARIANT but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:GetValueCache\\(\\) should return VARIANT but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:Get\\(\\) should return Phreezable but returns Preezable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:Save\\(\\) should return int but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:SelectAdapter\\(\\) should return DataAdapter but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:SetValueCache\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method PortalController\\:\\:Get401Authentication\\(\\) should return IAuthenticatable but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method PortalController\\:\\:GetCSRFToken\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method PortalController\\:\\:GetRouter\\(\\) should return IRouter but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method PortalController\\:\\:IsTerminated\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method PortalController\\:\\:LoadFromForm\\(\\) should return Phreezable but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Reporter\\:\\:CacheLevel\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Reporter\\:\\:GetPublicProperties\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Reporter\\:\\:IsLoaded\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Reporter\\:\\:IsPartiallyLoaded\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Reporter\\:\\:NoCache\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Reporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method SavantRenderEngine\\:\\:fetch\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/SavantRenderEngine.php',
];
$ignoreErrors[] = [
    'message' => '#^Method VerySimpleStringUtil\\:\\:ConvertEmailToMailTo\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method VerySimpleStringUtil\\:\\:ConvertUrlToLink\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method VerySimpleStringUtil\\:\\:EncodeToHTML\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method VerySimpleStringUtil\\:\\:GetCharArray\\(\\) should return array but returns list\\<string\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method VerySimpleStringUtil\\:\\:unicode_entity_replace\\(\\) should return string but returns char\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OnsiteActivityViewMap\\:\\:GetFieldMaps\\(\\) should return array\\<FieldMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OnsiteActivityViewMap\\:\\:GetKeyMaps\\(\\) should return array\\<KeyMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteActivityViewMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OnsiteDocumentMap\\:\\:GetFieldMaps\\(\\) should return array\\<FieldMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteDocumentMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OnsiteDocumentMap\\:\\:GetKeyMaps\\(\\) should return array\\<KeyMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsiteDocumentMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OnsitePortalActivityMap\\:\\:GetFieldMaps\\(\\) should return array\\<FieldMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsitePortalActivityMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OnsitePortalActivityMap\\:\\:GetKeyMaps\\(\\) should return array\\<KeyMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/OnsitePortalActivityMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method PatientMap\\:\\:GetFieldMaps\\(\\) should return array\\<FieldMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/PatientMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method PatientMap\\:\\:GetKeyMaps\\(\\) should return array\\<KeyMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/PatientMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method UserDAO\\:\\:GetExaminerFormHearings\\(\\) should return DataSet but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Method UserDAO\\:\\:GetReviewerFormHearings\\(\\) should return DataSet but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Method UserMap\\:\\:GetFieldMaps\\(\\) should return array\\<FieldMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method UserMap\\:\\:GetKeyMaps\\(\\) should return array\\<KeyMap\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function report_header_2\\(\\) should return outputs but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\BC\\\\Database\\:\\:execute\\(\\) should return int but returns int\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/BC/Database.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\BC\\\\Database\\:\\:extractSqlErrorFromDBAL\\(\\) should return array\\{string, string, string\\}\\|null but returns array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/BC/Database.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\BillingProcessor\\\\BillingClaim\\:\\:getEncounter\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaim.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\BillingProcessor\\\\BillingClaim\\:\\:getPid\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaim.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\BillingProcessor\\\\BillingClaim\\:\\:getTarget\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaim.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\BillingProcessor\\\\BillingClaimBatch\\:\\:getBatContent\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaimBatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\BillingProcessor\\\\BillingClaimBatch\\:\\:getBatFiledir\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaimBatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\BillingProcessor\\\\BillingClaimBatch\\:\\:getBatFilename\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaimBatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\BillingProcessor\\\\BillingClaimBatch\\:\\:getBatIcn\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaimBatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\Claim\\:\\:getOrdererId\\(\\) should return int\\|string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\Claim\\:\\:providerNumberType\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\Claim\\:\\:x12Clean\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\Claim\\:\\:x12Zip\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\MiscBillingOptions\\:\\:qual_id_to_description\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/MiscBillingOptions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\PaymentGateway\\:\\:submitPaymentCard\\(\\) should return bool\\|string but returns mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Billing\\\\PaymentGateway\\:\\:submitPaymentToken\\(\\) should return bool\\|string but returns mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\ActionRouter\\:\\:renderView\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\BaseController\\:\\:getRuleManager\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\Common\\:\\:get\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\Common\\:\\:post\\(\\) should return array\\<string\\>\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\ControllerRouter\\:\\:route\\(\\) should return Symfony\\\\Component\\\\HttpFoundation\\\\Response but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/ControllerRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\CodeManager\\:\\:get\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\Code but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/CodeManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\ReminderIntervalRange\\:\\:from\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\ReminderIntervalType but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/ReminderIntervalRange.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\ReminderIntervalType\\:\\:from\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\ReminderIntervalType but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/ReminderIntervalType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\ReminderIntervals\\:\\:getDetailFor\\(\\) should return array but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/ReminderIntervals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\ReminderIntervals\\:\\:getDetailFor\\(\\) should return array but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/ReminderIntervals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaFactory\\:\\:getBuilderFor\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaBuilder\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType\\:\\:from\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:createFilterRuleCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:createTargetRuleCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleFilterCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleTargetCriteriaByGroupId\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleTargetCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleType\\:\\:from\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleType but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\TimeUnit\\:\\:from\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\ReminderIntervalType but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/TimeUnit.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Acl\\\\AclExtended\\:\\:addNewACL\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Acl\\\\AclExtended\\:\\:getAclIdNumber\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\AuthUtils\\:\\:rehashPassword\\(\\) should return s\\|string\\|void but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\MfaUtils\\:\\:errorMessage\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/MfaUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ClientEntity\\:\\:getJwksUri\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Entities/ClientEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ClientEntity\\:\\:getJwks\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Entities/ClientEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ScopeEntity\\:\\:getScopeLookupKey\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Entities/ScopeEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\UserEntity\\:\\:getClaimsType\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Entities/UserEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\IdTokenSMARTResponse\\:\\:getBuilder\\(\\) should return Lcobucci\\\\JWT\\\\Token\\\\Builder but returns Lcobucci\\\\JWT\\\\Builder\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/IdTokenSMARTResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\JWT\\\\JsonWebKeySet\\:\\:getJSONWebKey\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/JsonWebKeySet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\JWT\\\\JsonWebKeySet\\:\\:getJSONWebKey\\(\\) should return object but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/JsonWebKeySet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\JWT\\\\RsaSha384Signer\\:\\:verify\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/RsaSha384Signer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\AccessTokenRepository\\:\\:getTokenExpiration\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/AccessTokenRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ScopeRepository\\:\\:lookupDescriptionForScope\\(\\) should return string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\UserRepository\\:\\:getUserEntityByUserCredentials\\(\\) should return League\\\\OAuth2\\\\Server\\\\Entities\\\\UserEntityInterface\\|null but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/UserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\UuidUserAccount\\:\\:collectUserRole\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/UuidUserAccount.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\UuidUserAccount\\:\\:getUserRole\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/UuidUserAccount.php',
];
$ignoreErrors[] = [
    'message' => '#^Anonymous function should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Command\\\\GenerateAccessTokenCommand\\:\\:generateRefreshToken\\(\\) should return League\\\\OAuth2\\\\Server\\\\Entities\\\\RefreshTokenEntityInterface but returns League\\\\OAuth2\\\\Server\\\\Entities\\\\RefreshTokenEntityInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Command\\\\SymfonyCommandRunner\\:\\:findCommands\\(\\) should return array\\<Symfony\\\\Component\\\\Console\\\\Command\\\\Command\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/SymfonyCommandRunner.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Command\\\\SymfonyCommandRunner\\:\\:getEventDispatcher\\(\\) should return Symfony\\\\Component\\\\EventDispatcher\\\\EventDispatcherInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/SymfonyCommandRunner.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Compatibility\\\\Checker\\:\\:checkPhpVersion\\(\\) should return bool\\|OpenEMR\\\\Common\\\\Compatibility\\\\warning but returns string\\|true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Compatibility/Checker.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen\\:\\:collectCryptoKey\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Crypto/CryptoGen.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen\\:\\:getOpenSSLCipherIvLength\\(\\) should return int but returns int\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Crypto/CryptoGen.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen\\:\\:openSSLEncrypt\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Crypto/CryptoGen.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:ediGenerateId\\(\\) should return int but returns bool\\|int\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:escapeLimit\\(\\) should return int but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:generateId\\(\\) should return int but returns bool\\|int\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:getADODB\\(\\) should return ADODB_mysqli_log but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:getLastInsertId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:listTableFields\\(\\) should return array\\<string\\> but returns list\\<mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:sqlInsert\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Forms\\\\FormLocator\\:\\:findFile\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormLocator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Forms\\\\FormVitalDetails\\:\\:get_form_id\\(\\) should return int\\|null but returns float\\|int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitalDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Forms\\\\FormVitalDetails\\:\\:get_reason_code\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitalDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Forms\\\\FormVitalDetails\\:\\:get_reason_description\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitalDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Forms\\\\FormVitalDetails\\:\\:get_reason_status\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitalDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Forms\\\\FormVitals\\:\\:get_uuid\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Forms\\\\Types\\\\EncounterListOptionType\\:\\:buildDisplayView\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/EncounterListOptionType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:\\:getAccessTokenScopeEntityList\\(\\) should return array\\<OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ScopeEntity\\> but returns list\\<mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:\\:getAccessTokenScopes\\(\\) should return array\\<string\\> but returns list\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:\\:getHeader\\(\\) should return array\\<string\\> but returns list\\<string\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:\\:getHeaders\\(\\) should return array\\<array\\<string\\>\\> but returns array\\<string, list\\<string\\|null\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:\\:getRequestUserId\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:\\:getScopeContextForResource\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:\\:getUri\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\oeHttpResponse\\:\\:header\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\oeHttpResponse\\:\\:headers\\(\\) should return array\\<string, array\\<string\\>\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Http\\\\oeHttpResponse\\:\\:status\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Logging\\\\EventAuditLogger\\:\\:createInstance\\(\\) should return static\\(OpenEMR\\\\Common\\\\Logging\\\\EventAuditLogger\\) but returns OpenEMR\\\\Common\\\\Logging\\\\EventAuditLogger\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/EventAuditLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Logging\\\\EventAuditLogger\\:\\:eventCategoryFinder\\(\\) should return string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Logging/EventAuditLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Address\\:\\:get_postalcode\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Address.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Address\\:\\:toArray\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Address.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactAddress\\:\\:get_notes\\(\\) should return string\\|null but returns OpenEMR\\\\Common\\\\ORDataObject\\\\Note\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactAddress\\:\\:get_status\\(\\) should return string but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:getContact\\(\\) should return OpenEMR\\\\Common\\\\ORDataObject\\\\Contact but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_active\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_can_make_medical_decisions\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_can_receive_medical_info\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_contact_id\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_contact_priority\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_end_date\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_is_emergency_contact\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_is_primary_contact\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_notes\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_relationship\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_role\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_start_date\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_target_id\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactRelation\\:\\:get_target_table\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactRelation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:getContact\\(\\) should return OpenEMR\\\\Common\\\\ORDataObject\\\\Contact but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_author\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_contact_id\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_created_date\\(\\) should return DateTime but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_inactivated_reason\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_is_primary\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_notes\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_period_end\\(\\) should return DateTime\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_period_start\\(\\) should return DateTime but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_rank\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_system\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_use\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\ContactTelecom\\:\\:get_value\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:deactivate\\(\\) should return bool but returns bool\\|int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_age\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_birth_date\\(\\) should return DateTime\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_communication\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_created_by\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_created_date\\(\\) should return DateTime\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_death_date\\(\\) should return DateTime\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_ethnicity\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_first_name\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_gender\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_inactive_date\\(\\) should return DateTime\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_inactive_reason\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_last_name\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_marital_status\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_middle_name\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_notes\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_preferred_language\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_preferred_name\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_race\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_ssn\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_title\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_updated_by\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:get_updated_date\\(\\) should return DateTime\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:persist\\(\\) should return bool\\|int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:reactivate\\(\\) should return bool but returns bool\\|int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Session\\\\SessionUtil\\:\\:getAppCookie\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Utils\\\\FileUtils\\:\\:getExtensionFromMimeType\\(\\) should return string but returns int\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Utils/FileUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Utils\\\\FileUtils\\:\\:getMimeTypeFromExtension\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Utils/FileUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateFloat\\(\\) should return float\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Utils/ValidationUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateInt\\(\\) should return int\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Utils/ValidationUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Uuid\\\\UuidRegistry\\:\\:createMissingMappedUuids\\(\\) should return int but returns int\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Uuid\\\\UuidRegistry\\:\\:createUuid\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Controllers\\\\Interface\\\\Forms\\\\Observation\\\\ObservationController\\:\\:getFormService\\(\\) should return OpenEMR\\\\Services\\\\FormService but returns OpenEMR\\\\Services\\\\FormService\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Controllers\\\\Interface\\\\Forms\\\\Observation\\\\ObservationController\\:\\:getObservationService\\(\\) should return OpenEMR\\\\Services\\\\ObservationService but returns OpenEMR\\\\Services\\\\ObservationService\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\AbstractModuleActionListener\\:\\:getLoggedInUser\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\Header\\:\\:readConfigFile\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\Kernel\\:\\:getEventDispatcher\\(\\) should return Symfony\\\\Component\\\\EventDispatcher\\\\EventDispatcherInterface but returns object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/Kernel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\ModulesApplication\\:\\:oemr_zend_load_modules_from_db\\(\\) should return array\\<string\\> but returns list\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/ModulesApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Cqm\\\\CqmClient\\:\\:calculate\\(\\) should return Psr\\\\Http\\\\Message\\\\StreamInterface but returns array\\<int, string\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/CqmClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Cqm\\\\CqmClient\\:\\:calculate\\(\\) should return Psr\\\\Http\\\\Message\\\\StreamInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/CqmClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Cqm\\\\CqmClient\\:\\:getHealth\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/CqmClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Cqm\\\\CqmClient\\:\\:getVersion\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/CqmClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Cqm\\\\CqmClient\\:\\:shutdown\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/CqmClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Cqm\\\\QrdaControllers\\\\QrdaReportController\\:\\:getCategoryIIIReport\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Cqm\\\\QrdaControllers\\\\QrdaReportController\\:\\:getConsolidatedCategoryIIIReport\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Appointments\\\\AppointmentRenderEvent\\:\\:getAppt\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Appointments/AppointmentRenderEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Appointments\\\\AppointmentSetEvent\\:\\:givenAppointmentData\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Appointments/AppointmentSetEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Command\\\\CommandRunnerFilterEvent\\:\\:getCommands\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Command/CommandRunnerFilterEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Core\\\\Sanitize\\\\IsAcceptedFileFilterEvent\\:\\:getAcceptedList\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Core/Sanitize/IsAcceptedFileFilterEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Messaging\\\\SendNotificationEvent\\:\\:getPid\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Messaging/SendNotificationEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Messaging\\\\SendNotificationEvent\\:\\:getSendNotificationMethod\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Messaging/SendNotificationEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Messaging\\\\SendSmsEvent\\:\\:fetchPatientPhone\\(\\) should return array\\|bool\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Messaging/SendSmsEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:canAdd\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:canCollapse\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:canEdit\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:getAcl\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:getBackgroundColorClass\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:getEventDispatcher\\(\\) should return Symfony\\\\Contracts\\\\EventDispatcher\\\\EventDispatcherInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:getIdentifier\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:getTemplateFile\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:getTemplateVariables\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:getTextColorClass\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:getTitle\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\Card\\\\CardModel\\:\\:isInitiallyCollapsed\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/CardModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:getComponentsAsString\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:getSectionsAsString\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\RestApiExtend\\\\RestApiSecurityCheckEvent\\:\\:getRestRequest\\(\\) should return OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest but returns OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/RestApiExtend/RestApiSecurityCheckEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\RestApiExtend\\\\RestApiSecurityCheckEvent\\:\\:getScopeType\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/RestApiExtend/RestApiSecurityCheckEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\User\\\\UserCreatedEvent\\:\\:getUsername\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/User/UserCreatedEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\User\\\\UserEditRenderEvent\\:\\:getUserId\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/User/UserEditRenderEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\BaseActionButtonHelper\\:\\:getAnchorClasses\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\BaseActionButtonHelper\\:\\:getAttributes\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\BaseActionButtonHelper\\:\\:getClickHandlerFunctionName\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\BaseActionButtonHelper\\:\\:getClickHandlerTemplateName\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\BaseActionButtonHelper\\:\\:getDisplayText\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\BaseActionButtonHelper\\:\\:getID\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\BaseActionButtonHelper\\:\\:getIconClass\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\UserInterface\\\\BaseActionButtonHelper\\:\\:getTitle\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\DomainModels\\\\OpenEMRFHIRDateTime\\:\\:jsonSerialize\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/DomainModels/OpenEMRFHIRDateTime.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\DomainModels\\\\OpenEMRFhirQuestionnaireResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/DomainModels/OpenEMRFhirQuestionnaireResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\Export\\\\ExportJob\\:\\:getResources\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/Export/ExportJob.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\SMART\\\\ExternalClinicalDecisionSupport\\\\DecisionSupportInterventionEntity\\:\\:getClient\\(\\) should return OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ClientEntity but returns OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ClientEntity\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/DecisionSupportInterventionEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\Gacl\\:\\:acl_check\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\Gacl\\:\\:acl_query\\(\\) should return array but returns false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\Gacl\\:\\:acl_query\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\Gacl\\:\\:acl_return_value\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\Gacl\\:\\:debug_db\\(\\) should return string but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:_rebuild_tree\\(\\) should return int but returns false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:add_acl\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:add_group\\(\\) should return int but returns false\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:add_object\\(\\) should return int but returns false\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:add_object\\(\\) should return int but returns true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:add_object_section\\(\\) should return int but returns false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:count_all\\(\\) should return int but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:format_groups\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_acl\\(\\) should return bool but returns array\\<string, mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_group_children\\(\\) should return array but returns array\\|bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_group_children\\(\\) should return array but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_group_data\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_group_id\\(\\) should return int but returns false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_group_id\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_group_objects\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_group_parent_id\\(\\) should return int but returns false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_group_parent_id\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object\\(\\) should return OpenEMR\\\\Gacl\\\\ADORecordSet but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object\\(\\) should return OpenEMR\\\\Gacl\\\\ADORecordSet but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object_data\\(\\) should return array but returns false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object_groups\\(\\) should return array but returns false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object_id\\(\\) should return int but returns false\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object_id\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object_section_section_id\\(\\) should return int but returns false\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object_section_section_id\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object_section_value\\(\\) should return string but returns false\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object_section_value\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_objects\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_root_group_id\\(\\) should return int but returns false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_root_group_id\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_schema_version\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_section_data\\(\\) should return array but returns false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_ungrouped_objects\\(\\) should return array but returns false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_version\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:search_acl\\(\\) should return array but returns array\\|bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:sort_groups\\(\\) should return array but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Health\\\\Check\\\\CacheCheck\\:\\:getName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/CacheCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Health\\\\Check\\\\DatabaseCheck\\:\\:getName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/DatabaseCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Health\\\\Check\\\\FilesystemCheck\\:\\:getName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/FilesystemCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Health\\\\Check\\\\InstallationCheck\\:\\:getName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/InstallationCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Health\\\\Check\\\\OAuthKeysCheck\\:\\:getName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/OAuthKeysCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Health\\\\Check\\\\SessionCheck\\:\\:getName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/SessionCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getChildren\\(\\) should return OpenEMR\\\\Menu\\\\MenuItems but returns OpenEMR\\\\Menu\\\\MenuItems\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\MainMenuRole\\:\\:getMenu\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/MainMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\PatientMenuRole\\:\\:getAbsoluteWebRoot\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/PatientMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\PatientMenuRole\\:\\:getMenu\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/PatientMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\OeUI\\\\OemrUI\\:\\:helpIconListener\\(\\) should return string but returns OpenEMR\\\\Events\\\\UserInterface\\\\PageHeadingRenderEvent\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\OeUI\\\\OemrUI\\:\\:pageHeading\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Patient\\\\Cards\\\\CareExperiencePreferenceViewCard\\:\\:getUserDisplay\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareExperiencePreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Patient\\\\Cards\\\\TreatmentPreferenceViewCard\\:\\:getUserDisplay\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/TreatmentPreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\PaymentProcessing\\\\Recorder\\:\\:getNextSequenceNumber\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Recorder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Pharmacy\\\\Services\\\\ImportPharmacies\\:\\:importPharmacies\\(\\) should return string but returns int\\<0, max\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Pharmacy/Services/ImportPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Reports\\\\AMC\\\\Trackers\\\\AMCItemTracker\\:\\:getResults\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Reports/AMC/Trackers/AMCItemTracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\AuthorizationController\\:\\:getUuidUserAccount\\(\\) should return OpenEMR\\\\Common\\\\Auth\\\\UuidUserAccount but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:getRequestEndPoint\\(\\) should return string but returns array\\<string\\>\\|string\\|false\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirAllergyIntoleranceRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirAppointmentRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirCarePlanRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCarePlanRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirCareTeamRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCareTeamRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirCoverageRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCoverageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirDeviceRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDeviceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirDiagnosticReportRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDiagnosticReportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirDocumentReferenceRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentReferenceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirEncounterRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirEncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirGroupRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGroupRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirImmunizationRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirLocationRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirLocationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirMedicationRequestRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirMedicationRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirObservationRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirObservationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirPersonRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirPractitionerRoleRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRoleRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirProcedureRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirProvenanceRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProvenanceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirQuestionnaireResponseRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBundle\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireResponseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirQuestionnaireResponseRestController\\:\\:getFhirQuestionnaireResponseService\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService but returns OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireResponseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirQuestionnaireRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBundle but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirServiceRequestRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirServiceRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirValueSetRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirValueSetRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Finder\\\\FhirRouteFinder\\:\\:find\\(\\) should return array\\<string, mixed\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Finder/FhirRouteFinder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FhirOperationDefinitionRestController\\:\\:createResponseForCode\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\ResponseInterface but returns Psr\\\\Http\\\\Message\\\\ResponseInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDefinitionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FhirOperationDefinitionRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FHIR but returns OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\ResponseInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDefinitionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FhirOperationDocRefRestController\\:\\:createResponseForCode\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\ResponseInterface but returns Psr\\\\Http\\\\Message\\\\ResponseInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FhirOperationDocRefRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\FHIR but returns OpenEMR\\\\RestControllers\\\\FHIR\\\\Operations\\\\ResponseInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\ProfileMappers\\\\FhirConditionProfileMapper\\:\\:profileResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/ProfileMappers/FhirConditionProfileMapper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\Finder\\\\PortalRouteFinder\\:\\:find\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Finder/PortalRouteFinder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\Finder\\\\StandardRouteFinder\\:\\:find\\(\\) should return array\\<string, mixed\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Finder/StandardRouteFinder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:\\:handleProcessingResult\\(\\) should return array\\<array\\> but returns array\\<string, mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/RestControllerHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\SMART\\\\PatientContextSearchController\\:\\:getPatientForUser\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/PatientContextSearchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\SMART\\\\SMARTAuthorizationController\\:\\:getClientRedirectURI\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/SMARTAuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\TransactionRestController\\:\\:CreateTransaction\\(\\) should return OpenEMR\\\\RestControllers\\\\a but returns array\\<array\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TransactionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\AllergyIntoleranceService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\AllergyIntoleranceService\\:\\:insert\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\AllergyIntoleranceService\\:\\:update\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\AppointmentService\\:\\:getAppointmentStatuses\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\AppointmentService\\:\\:getEncounterForAppointment\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\AppointmentService\\:\\:getEncounterForAppointment\\(\\) should return array but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\BaseService\\:\\:getFields\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\BaseService\\:\\:getFreshId\\(\\) should return string but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\BaseService\\:\\:getIdByUuid\\(\\) should return string\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\BaseService\\:\\:getUuidById\\(\\) should return string\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Cda\\\\CdaTemplateParse\\:\\:parseCDAEntryComponents\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Cda\\\\CdaTemplateParse\\:\\:parseQRDAPatientDataSection\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Cda\\\\CdaTemplateParse\\:\\:parseUnstructuredComponents\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Cda\\\\CdaValidateDocuments\\:\\:validateXmlXsd\\(\\) should return bool but returns array\\<string, list\\<string\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ClinicalNotesService\\:\\:getDocumentIdFromUuid\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ClinicalNotesService\\:\\:getProcedureResultIdFromUuid\\(\\) should return int\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\CodeTypesService\\:\\:collectCodeTypes\\(\\) should return array\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\CodeTypesService\\:\\:getCodeTypeForSystemUrl\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\CodeTypesService\\:\\:getCodeWithType\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\CodeTypesService\\:\\:getOpenEMRCodeForSystemAndCode\\(\\) should return string but returns bool\\|float\\|int\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\CodeTypesService\\:\\:isCPT4Installed\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\CodeTypesService\\:\\:isRXNORMInstalled\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ConditionService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ConditionService\\:\\:insert\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ConditionService\\:\\:update\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactAddressService\\:\\:deactivateAddress\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactAddressService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactAddressService\\:\\:getValidAddressTypes\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactAddressService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactAddressService\\:\\:getValidAddressUses\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactAddressService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactAddressService\\:\\:saveContactAddress\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactAddressService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactRelationService\\:\\:getOrCreatePersonForPatient\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactRelationService\\:\\:getValidRelationshipTypes\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactRelationService\\:\\:getValidRoleTypes\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactTelecomService\\:\\:deactivateTelecom\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactTelecomService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactTelecomService\\:\\:getValidTelecomSystems\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactTelecomService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ContactTelecomService\\:\\:getValidTelecomUses\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactTelecomService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\DocumentTemplates\\\\DocumentTemplateRender\\:\\:dataFixup\\(\\) should return array\\<string\\>\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateRender.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\DocumentTemplates\\\\DocumentTemplateRender\\:\\:fetchTemplateDocument\\(\\) should return array but returns array\\|false\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateRender.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\DocumentTemplates\\\\DocumentTemplateService\\:\\:fetchPortalAuthUsers\\(\\) should return array\\<array\\<string\\>\\> but returns list\\<array\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\DocumentTemplates\\\\DocumentTemplateService\\:\\:getProfileActiveStatus\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\DocumentTemplates\\\\DocumentTemplateService\\:\\:getProfileActiveStatus\\(\\) should return int but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\DocumentTemplates\\\\DocumentTemplateService\\:\\:savePatientGroupsByProfile\\(\\) should return bool but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\DocumentTemplates\\\\DocumentTemplateService\\:\\:setProfileActiveStatus\\(\\) should return int but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\DocumentTemplates\\\\DocumentTemplateService\\:\\:uploadTemplate\\(\\) should return int but returns array\\|float\\|int\\|string\\|false\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\DocumentTemplates\\\\DocumentTemplateService\\:\\:uploadTemplate\\(\\) should return int but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getEncounterById\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getEncounter\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getEncountersByDateRange\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getMostRecentEncounterForPatient\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getOneByPidEid\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getOrderingProviderID\\(\\) should return string but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getOrderingProviderID\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getReferringProviderID\\(\\) should return string but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getReferringProviderID\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getSensitivity\\(\\) should return string but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:getSensitivity\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:insertEncounter\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:updateEncounter\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\EncounterService\\:\\:updateEncounter\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:computeVerificationStatus\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:getConditionFhirUuid\\(\\) should return string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:getServiceForCategory\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:computeVerificationStatus\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:getConditionService\\(\\) should return OpenEMR\\\\Services\\\\ConditionService but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:getServiceForCategory\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:computeVerificationStatus\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:getConditionService\\(\\) should return OpenEMR\\\\Services\\\\ConditionService but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:getServiceForCategory\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportClinicalNotesService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\the but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportClinicalNotesService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\the but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportClinicalNotesService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportClinicalNotesService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportLaboratoryService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportLaboratoryService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportLaboratoryService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportLaboratoryService\\:\\:supportsCategory\\(\\) should return string but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirClinicalNotesService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\the but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirClinicalNotesService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\the but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirClinicalNotesService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirPatientDocumentReferenceService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirPatientDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirPatientDocumentReferenceService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirPatientDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirPatientDocumentReferenceService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirPatientDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirPatientDocumentReferenceService\\:\\:searchForOpenEMRRecords\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirPatientDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirAllergyIntoleranceService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirAllergyIntoleranceService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAllergyIntolerance but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\the but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\the but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\the but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAppointment\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCarePlanService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCarePlanService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCarePlanService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCarePlanService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCarePlan but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:getServiceForCategory\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCoverageService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCoverageService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCoverage but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\the but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\the but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\the but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDevice\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDiagnosticReportService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDiagnosticReportService\\:\\:getServiceForCategory\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDiagnosticReportService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDiagnosticReportService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocRefService\\:\\:createOpenEMRSearchParameters\\(\\) should return array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\> but returns array\\<array\\<string, mixed\\>\\|OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocRefService\\:\\:generateCCD\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocRefService\\:\\:getMostCurrentCCDReference\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocRefService\\:\\:getPatientRecordForSearchParameters\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocumentReferenceService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocumentReferenceService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocumentReferenceService\\:\\:getServiceForCategory\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocumentReferenceService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDocumentReferenceService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirEncounterService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirEncounterService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirEncounterService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREncounter but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirEncounterService\\:\\:searchForOpenEMRRecords\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirExportJobService\\:\\:deleteJob\\(\\) should return OpenEMR\\\\FHIR\\\\Export\\\\ExportJob but returns true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirExportJobService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirGoalService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGoalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirGoalService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGoalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirGoalService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRGoal but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGoalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirGroupService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirGroupService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirGroupService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirImmunizationService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirImmunizationService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirImmunizationService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirImmunizationService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImmunization but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirLocationService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirLocationService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRLocation but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMediaService\\:\\:searchForOpenEMRRecords\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMediaService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationDispenseService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationDispenseService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationDispenseService\\:\\:getServiceForCategory\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationDispenseService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationDispenseService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedication but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationService\\:\\:searchForOpenEMRRecords\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirObservationService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirObservationService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirObservationService\\:\\:getServiceForCategory\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirObservationService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirObservationService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirOrganizationService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirOrganizationService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirOrganizationService\\:\\:update\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:extractInterpreterNeeded\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:extractSex\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:getCachedListOptionByCode\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:getCachedListOption\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:getProfileURIs\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPatient but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPersonService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPractitioner but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPerson\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPersonService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPractitioner but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPersonService\\:\\:searchForOpenEMRRecords\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerRoleService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerRoleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerRoleService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPractitionerRole but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerRoleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPractitioner but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\the but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\:\\:insert\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:getServiceForCategory\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirRelatedPersonService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirRelatedPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createOpenEMRSearchParameters\\(\\) should return array\\<OpenEMR\\\\Services\\\\Search\\\\ISearchField\\> but returns array\\<array\\<string, mixed\\>\\|OpenEMR\\\\Services\\\\Search\\\\ISearchField\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:getFhirApiURL\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:update\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceRequestService\\:\\:buildOrderCode\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeableConcept\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceRequestService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceRequestService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceRequestService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceRequestService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRServiceRequest but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirSpecimenService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirSpecimenService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSpecimen but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirValueSetService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValueSetService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirValueSetService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValueSetService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Group\\\\FhirPatientProviderGroupService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Group/FhirPatientProviderGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Group\\\\FhirPatientProviderGroupService\\:\\:searchForOpenEMRRecords\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Group/FhirPatientProviderGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\MedicationDispense\\\\FhirMedicationDispenseLocalDispensaryService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\MedicationDispense\\\\FhirMedicationDispenseLocalDispensaryService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\MedicationDispense\\\\FhirMedicationDispenseLocalDispensaryService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\MedicationDispense\\\\FhirMedicationDispenseLocalDispensaryService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\MedicationDispense\\\\FhirMedicationDispenseLocalDispensaryService\\:\\:mapMedication\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeableConcept but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\MedicationDispense\\\\FhirMedicationDispenseLocalDispensaryService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationDispense\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationCareExperiencePreferenceService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationCareExperiencePreferenceService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationCareExperiencePreferenceService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string but returns array\\|OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRObservation\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:getListOption\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\the but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRObservation\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationObservationFormService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationObservationFormService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationObservationFormService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationQuestionnaireItemService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationQuestionnaireItemService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationTreatmentInterventionPreferenceService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationTreatmentInterventionPreferenceService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationTreatmentInterventionPreferenceService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string but returns array\\|OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRObservation\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:getProfileURIs\\(\\) should return array\\<string\\> but returns list\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:loadSearchParameters\\(\\) should return array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> but returns array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationFacilityService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROrganization but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationFacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationInsuranceService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROrganization but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationInsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationProcedureProviderService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROrganization but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationProcedureProviderService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProcedure but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:parseOpenEMRRecord\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProcedure but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Questionnaire\\\\FhirQuestionnaireFormService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Questionnaire\\\\FhirQuestionnaireFormService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormService\\:\\:createProvenanceResource\\(\\) should return OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Utils\\\\FhirServiceLocator\\:\\:findServices\\(\\) should return array\\<OpenEMR\\\\Services\\\\FHIR\\\\IFhirExportableResourceService\\> but returns array\\<string, object\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Utils/FhirServiceLocator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FacilityService\\:\\:getOne\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FacilityService\\:\\:insert\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FacilityService\\:\\:update\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Globals\\\\UserSettingsService\\:\\:getUserSetting\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Globals\\\\UserSettingsService\\:\\:prevSetting\\(\\) should return OpenEMR\\\\Services\\\\Globals\\\\Prior but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\GroupService\\:\\:searchPatientProviderGroups\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/GroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ImageUtilities\\\\HandleImageService\\:\\:calculateNewDimensions\\(\\) should return array\\<float\\|int\\> but returns array\\<string, mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ImageUtilities/HandleImageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ImageUtilities\\\\HandleImageService\\:\\:convertImageToPdfUseImagick\\(\\) should return string\\|false but returns true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ImageUtilities/HandleImageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ImmunizationService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\InsuranceCompanyService\\:\\:getAllByPayerID\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceCompanyService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\InsuranceCompanyService\\:\\:getOne\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceCompanyService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\InsuranceService\\:\\:getPoliciesByPayerByEffectiveDate\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\InsuranceService\\:\\:insert\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\JWTClientAuthenticationService\\:\\:extractClientIdFromJWT\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/JWTClientAuthenticationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ListService\\:\\:getListOptionByCode\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ListService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ListService\\:\\:getListOption\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ListService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ListService\\:\\:getListOption\\(\\) should return array but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ListService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\LogoService\\:\\:getLogo\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/LogoService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ObservationService\\:\\:getObservationById\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ObservationService\\:\\:getObservationTypeDisplayName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ObservationService\\:\\:getReasonStatusDisplay\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ObservationService\\:\\:getStatusDisplayName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ObservationService\\:\\:getSubObservations\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientAdvanceDirectiveService\\:\\:getAdvanceDirectiveDocuments\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientIssuesService\\:\\:linkIssueToEncounter\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientIssuesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:databaseInsert\\(\\) should return int\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:getPatientAgeDisplay\\(\\) should return string but returns mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:getPatientPictureDocumentId\\(\\) should return float\\|int but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:insert\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:search\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:update\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientTrackerService\\:\\:collectApptStatusSettings\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientTrackerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientTrackerService\\:\\:collect_checkin\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientTrackerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientTrackerService\\:\\:collect_checkout\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientTrackerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientTrackerService\\:\\:is_tracker_encounter_exist\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientTrackerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientTrackerService\\:\\:manage_tracker_status\\(\\) should return bool\\|int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientTrackerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PractitionerService\\:\\:insert\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PractitionerService\\:\\:update\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ProcedureOrderRelationshipService\\:\\:cleanupOrphanedRecords\\(\\) should return int but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ProcedureOrderRelationshipService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ProductRegistrationService\\:\\:entryExist\\(\\) should return int\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ProductRegistrationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ProductRegistrationService\\:\\:getRegistrationEmail\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ProductRegistrationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qdm\\\\CqmCalculator\\:\\:calculateMeasure\\(\\) should return array\\|Psr\\\\Http\\\\Message\\\\StreamInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/CqmCalculator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qdm\\\\QdmRecord\\:\\:getData\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/QdmRecord.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qdm\\\\QdmRecord\\:\\:getEntityCount\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/QdmRecord.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qdm\\\\QdmRequestSome\\:\\:getFilter\\(\\) should return OpenEMR\\\\Events\\\\BoundFilter\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/QdmRequestSome.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qdm\\\\Services\\\\AbstractQdmService\\:\\:getRequest\\(\\) should return OpenEMR\\\\Services\\\\Qdm\\\\Interfaces\\\\QdmRequestInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractQdmService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qrda\\\\Cat1\\:\\:id_or_null_flavor\\(\\) should return bool but returns string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qrda\\\\Cat1\\:\\:relevant_date_period_or_null_flavor\\(\\) should return string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qrda\\\\Cat3\\:\\:cms_payer_code\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat3.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qrda\\\\Cat3\\:\\:relevant_date_period_or_null_flavor\\(\\) should return string but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat3.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qrda\\\\QrdaReportService\\:\\:generateCategoryIIIXml\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/QrdaReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qrda\\\\Util\\\\DateHelper\\:\\:format_datetime\\(\\) should return string\\|false but returns numeric\\-string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Util/DateHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Qrda\\\\Util\\\\DateHelper\\:\\:format_datetime_cqm\\(\\) should return string\\|false but returns non\\-falsy\\-string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Util/DateHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\QuestionnaireResponseService\\:\\:extractAnswerValue\\(\\) should return string but returns mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\QuestionnaireResponseService\\:\\:groupByItemsRecursively\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\SDOH\\\\HistorySdohService\\:\\:ccDisplay\\(\\) should return string but returns mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\SDOH\\\\HistorySdohService\\:\\:getCurrentGoalsResource\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Search\\\\BasicSearchField\\:\\:getModifier\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/BasicSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Search\\\\BasicSearchField\\:\\:getType\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/BasicSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Search\\\\BasicSearchField\\:\\:getValues\\(\\) should return array\\<mixed\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/BasicSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Search\\\\BasicSearchField\\:\\:isAnd\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/BasicSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Search\\\\DateSearchField\\:\\:getValues\\(\\) should return array\\<OpenEMR\\\\Services\\\\Search\\\\SearchFieldComparableValue\\> but returns array\\<mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/DateSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Search\\\\FHIRSearchFieldFactory\\:\\:createFieldForType\\(\\) should return OpenEMR\\\\Services\\\\Search\\\\DateSearchField\\|OpenEMR\\\\Services\\\\Search\\\\StringSearchField\\|OpenEMR\\\\Services\\\\Search\\\\TokenSearchField but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Search/FHIRSearchFieldFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\:\\:getMappedFields\\(\\) should return array\\<OpenEMR\\\\Services\\\\Search\\\\ServiceField\\>\\|string but returns array\\<OpenEMR\\\\Services\\\\Search\\\\ServiceField\\|string\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FhirSearchParameterDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Search\\\\TokenSearchValue\\:\\:getCode\\(\\) should return float\\|int\\|string but returns bool\\|float\\|int\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/TokenSearchValue.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getAuthGroupForUser\\(\\) should return bool\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getIdByUsername\\(\\) should return array but returns false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getIdByUsername\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getUserByUUID\\(\\) should return array\\{id\\: numeric\\-string, uuid\\: string\\|null, username\\: string\\|null, password\\: string\\|null, authorized\\: numeric\\-string\\|null, info\\: string\\|null, source\\: numeric\\-string\\|null, fname\\: string\\|null, \\.\\.\\.\\}\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getUserByUsername\\(\\) should return array\\{id\\: numeric\\-string, uuid\\: string\\|null, username\\: string\\|null, password\\: string\\|null, authorized\\: numeric\\-string\\|null, info\\: string\\|null, source\\: numeric\\-string\\|null, fname\\: string\\|null, \\.\\.\\.\\}\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getUserForCalendar\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getUser\\(\\) should return array\\{id\\: numeric\\-string, uuid\\: string\\|null, username\\: string\\|null, password\\: string\\|null, authorized\\: numeric\\-string\\|null, info\\: string\\|null, source\\: numeric\\-string\\|null, fname\\: string\\|null, \\.\\.\\.\\}\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getUsersForCalendar\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:databaseName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:getRenderOutputBuffer\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:getTablesList\\(\\) should return OpenEMR\\\\Services\\\\Utils\\\\SQLStatement but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:isRenderOutputToScreen\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:isThrowExceptionOnError\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\VitalsService\\:\\:createResultRecordFromDatabaseResult\\(\\) should return array\\<string, mixed\\> but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\VitalsService\\:\\:getEventDispatcher\\(\\) should return Symfony\\\\Component\\\\EventDispatcher\\\\EventDispatcherInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\VitalsService\\:\\:getVitalsForForm\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\VitalsService\\:\\:getVitalsHistoryForPatient\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Telemetry\\\\GeoTelemetry\\:\\:fetchJson\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/GeoTelemetry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Telemetry\\\\GeoTelemetry\\:\\:fetchText\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/GeoTelemetry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Telemetry\\\\GeoTelemetry\\:\\:getCachedGeoData\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/GeoTelemetry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Telemetry\\\\TelemetryService\\:\\:getUniqueInstallationUuid\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Telemetry\\\\TelemetryService\\:\\:reportUsageData\\(\\) should return bool\\|int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\USPS\\\\USPSAddressVerifyV3\\:\\:doRequest\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSAddressVerifyV3.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\USPS\\\\USPSAddressVerifyV3\\:\\:getToken\\(\\) should return string but returns string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSAddressVerifyV3.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Validators\\\\BaseValidator\\:\\:isValidContext\\(\\) should return true but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Validators\\\\ProcessingResult\\:\\:extractDataArray\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/ProcessingResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\G10_Certification\\\\BulkPatientExport311APITest\\:\\:getInfernoJWKS\\(\\) should return stdClass but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/BulkPatientExport311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\G10_Certification\\\\BulkPatientExport311APITest\\:\\:getTestGroupResponse\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/BulkPatientExport311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\G10_Certification\\\\SinglePatient311APITest\\:\\:getTestGroupResponse\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatient311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\G10_Certification\\\\SinglePatient700APITest\\:\\:getTestGroupResponse\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatient700APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\G10_Certification\\\\SinglePatientApi\\\\CapabilityStatementTest\\:\\:getTestGroupResponse\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/CapabilityStatementTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\G9_Certification\\\\CCDADocRefGenerationTest\\:\\:getDocumentGenerationTime\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\US_Core_311\\\\InfernoSinglePatientAPITest\\:\\:getTestGroupResponse\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/US_Core_311/InfernoSinglePatientAPITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\BbCreateStaffTest\\:\\:gatherModalDiagnostics\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getNextId\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\the but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getUnregisteredUuid\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\uuid4 but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:installFixturesForTable\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\the but returns int\\<0, max\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\ConditionFixtureManager\\:\\:getNextEncounterId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/ConditionFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\ConditionFixtureManager\\:\\:getNextListId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/ConditionFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\ConditionFixtureManager\\:\\:getNextPid\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/ConditionFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FacilityFixtureManager\\:\\:installSingleFacilityFixture\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\count but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FacilityFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getFhirPatientFixtures\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getNextPid\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\the but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getUnregisteredUuid\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\uuid4 but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:installFixtures\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\the but returns int\\<0, max\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:installSinglePatientFixture\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\count but returns OpenEMR\\\\Tests\\\\Fixtures\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\GaclFixtureManager\\:\\:installFixtures\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/GaclFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\MedicationDispenseFixtureManager\\:\\:createDrug\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/MedicationDispenseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\MedicationDispenseFixtureManager\\:\\:createEncounter\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/MedicationDispenseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\MedicationDispenseFixtureManager\\:\\:createPatient\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/MedicationDispenseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getNextId\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\the but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getUnregisteredUuid\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\uuid4 but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:installFixtures\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\the but returns int\\<0, max\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:installSinglePractitionerFixture\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\count but returns OpenEMR\\\\Tests\\\\Fixtures\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Isolated\\\\Billing\\\\SimpleBillingClaimMock\\:\\:getPartner\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Billing/BillingClaimBatchTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Isolated\\\\Core\\\\Traits\\\\SingletonC\\:\\:createInstance\\(\\) should return static\\(OpenEMR\\\\Tests\\\\Isolated\\\\Core\\\\Traits\\\\SingletonC\\) but returns OpenEMR\\\\Tests\\\\Isolated\\\\Core\\\\Traits\\\\SingletonC\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/Traits/SingletonTraitTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\BackgroundTaskManagerStub\\:\\:getLastBinds\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/BackgroundTaskManagerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\BackgroundTaskManagerStub\\:\\:getLastSql\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/BackgroundTaskManagerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Anonymous function should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirPatientServiceMappingTest\\:\\:findTelecomEntry\\(\\) should return OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\matching but returns array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirPatientServiceMappingTest\\:\\:findTelecomEntry\\(\\) should return OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\matching but returns list\\<OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContactPoint\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohServiceTest\\:\\:insertSdohRecord\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientServiceTest\\:\\:getMappedUuidRecord\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormServiceIntegrationTest\\:\\:createTestQuestionnaireResponseWithItems\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormServiceIntegrationTest\\:\\:createTestQuestionnaireResponse\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\Modules\\\\CareCoordination\\\\Model\\\\CcdaServiceDocumentRequestorTest\\:\\:getDocumentGenerationTime\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Unit\\\\Rx\\\\RxListTest\\:\\:invokeParseToTokens\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Rx/RxListTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
