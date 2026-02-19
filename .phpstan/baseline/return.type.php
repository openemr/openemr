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
    'message' => '#^Method C_FormPainMap\\:\\:createModel\\(\\) should return Model but returns FormPainMap\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_FormPainMap\\:\\:getImage\\(\\) should return The but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_FormPainMap\\:\\:getOptionList\\(\\) should return A but returns array\\<int, string\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method C_FormPainMap\\:\\:getOptionsLabel\\(\\) should return The but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/C_FormPainMap.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method FormPainMap\\:\\:getCode\\(\\) should return A but returns FORM_CODE\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/FormPainMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method FormPainMap\\:\\:getTitle\\(\\) should return The but returns FORM_TITLE\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/FormPainMap.php',
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
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\TelehealthGlobalConfig\\:\\:isTelehealthCoreSettingsConfigured\\(\\) should return void\\|false but returns true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/TelehealthGlobalConfig.php',
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
    'message' => '#^Function cron_GetAlertPatientData\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rc_sms_notification_cron_update_entry\\(\\) should return int but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
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
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\AppDispatch\\:\\:validEmail\\(\\) should return bool but returns mixed\\.$#',
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
    'message' => '#^Method Application\\\\Controller\\\\IndexController\\:\\:getApplicationTable\\(\\) should return Application\\\\Controller\\\\type but returns Application\\\\Model\\\\ApplicationTable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Controller\\\\IndexController\\:\\:searchAction\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Controller\\\\SendtoController\\:\\:getApplicationTable\\(\\) should return Application\\\\Controller\\\\type but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Controller\\\\SendtoController\\:\\:getSendtoTable\\(\\) should return Application\\\\Controller\\\\type but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
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
    'message' => '#^Method Application\\\\Model\\\\ApplicationTable\\:\\:quoteValue\\(\\) should return Application\\\\Model\\\\type but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Model\\\\ApplicationTable\\:\\:zQuery\\(\\) should return Application\\\\Model\\\\type but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Application\\\\Plugin\\\\CommonPlugin\\:\\:checkACL\\(\\) should return Application\\\\Plugin\\\\type but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/CommonPlugin.php',
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
    'message' => '#^Method Carecoordination\\\\Controller\\\\MapperController\\:\\:getMapperTable\\(\\) should return Carecoordination\\\\Controller\\\\type but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\CarecoordinationTable\\:\\:cleanCcdaXmlContent\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\CarecoordinationTable\\:\\:getCCDAComponents\\(\\) should return array\\<string\\> but returns array\\.$#',
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
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getDetails\\(\\) should return array\\|null but returns mixed\\.$#',
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
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getRepresentedOrganization\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getSettings\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:getUserDetails\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Ccr\\\\Controller\\\\CcrController\\:\\:getCcrTable\\(\\) should return Ccr\\\\Controller\\\\type but returns mixed\\.$#',
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
    'message' => '#^Method Documents\\\\Model\\\\DocumentsTable\\:\\:getDocument\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Model/DocumentsTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Documents\\\\Plugin\\\\Documents\\:\\:getDocument\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Plugin/Documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Controller\\\\ImmunizationController\\:\\:format_cvx_code\\(\\) should return Immunization\\\\Controller\\\\type but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Controller\\\\ImmunizationController\\:\\:format_ethnicity\\(\\) should return Immunization\\\\Controller\\\\type but returns string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\Immunization\\:\\:getInputFilter\\(\\) should return Laminas\\\\InputFilter\\\\InputFilterInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/Immunization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\ImmunizationTable\\:\\:getImmunizationObservationResultsData\\(\\) should return Immunization\\\\Model\\\\type but returns list\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Immunization\\\\Model\\\\ImmunizationTable\\:\\:immunizedPatientDetails\\(\\) should return Immunization\\\\Model\\\\type but returns mixed\\.$#',
    'count' => 2,
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
    'message' => '#^Method Installer\\\\Model\\\\InstModuleTable\\:\\:getConfigSettings\\(\\) should return Laminas\\\\Db\\\\Adapter\\\\Driver\\\\Pdo\\\\Result but returns Application\\\\Model\\\\type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModuleTable\\:\\:getObject\\(\\) should return Installer\\\\Model\\\\type but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
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
    'message' => '#^Method Installer\\\\Model\\\\InstModuleTable\\:\\:register\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Model\\\\InstModuleTable\\:\\:validateNickName\\(\\) should return bool but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Multipledb\\\\Controller\\\\BaseController\\:\\:getPostParamsArray\\(\\) should return Multipledb\\\\Controller\\\\post but returns array\\<int\\|string, array\\<mixed\\>\\|string\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Multipledb\\\\Controller\\\\BaseController\\:\\:getUserId\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Multipledb\\\\Controller\\\\MultipledbController\\:\\:indexAction\\(\\) should return Laminas\\\\Stdlib\\\\ResponseInterface but returns Laminas\\\\View\\\\Model\\\\ViewModel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php',
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
    'message' => '#^Method NumberToText\\:\\:convert\\(\\) should return The but returns string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/NumberToText.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method NumberToText\\:\\:n2t_convertthree\\(\\) should return The but returns string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/NumberToText.class.php',
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
    'message' => '#^Function oeFormatShortDate\\(\\) should return string but returns mixed\\.$#',
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
    'message' => '#^Function cron_getFacilitiesMap\\(\\) should return array\\{msg_map\\: array\\<int, string\\>, phone_map\\: array\\<int, string\\>\\} but returns array\\{msg_map\\: array\\<mixed\\>, phone_map\\: array\\}\\.$#',
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
    'message' => '#^Function hsc_private_xl_or_warn\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/htmlspecialchars.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function js_escape\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/htmlspecialchars.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getImmunizationList\\(\\) should return recordset but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/immunization_helper.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLabProviders\\(\\) should return array\\|null but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getEmployerData\\(\\) should return OpenEMR\\\\Common\\\\Database\\\\recordset but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
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
    'message' => '#^Function get_db\\(\\) should return connection but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function privStatement\\(\\) should return ADORecordSet but returns mixed\\.$#',
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
    'message' => '#^Function sqlQ\\(\\) should return recordset but returns ADORecordSet\\.$#',
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
    'message' => '#^Function getUserSetting\\(\\) should return Effective but returns OpenEMR\\\\Services\\\\Globals\\\\Effective\\.$#',
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
    'message' => '#^Method xmltoarray_parser_htmlfix\\:\\:_struct_to_array\\(\\) should return The but returns array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/xmltoarray_parser_htmlfix.php',
];
$ignoreErrors[] = [
    'message' => '#^Method xmltoarray_parser_htmlfix\\:\\:createArray\\(\\) should return The but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/xmltoarray_parser_htmlfix.php',
];
$ignoreErrors[] = [
    'message' => '#^Method xmltoarray_parser_htmlfix\\:\\:fix_html_entities\\(\\) should return A but returns \\(array\\<string\\>\\|string\\)\\.$#',
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
    'message' => '#^Method ApplicationTable\\:\\:zQuery\\(\\) should return type but returns ADORecordSet\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
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
    'message' => '#^Method DataDriverMySQLi\\:\\:Open\\(\\) should return connection but returns mysqli\\|false\\.$#',
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
    'message' => '#^Method PortalController\\:\\:GetGUID\\(\\) should return string but returns mixed\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Billing\\\\HCFAInfo\\:\\:getPosition\\(\\) should return OpenEMR\\\\Billing\\\\type but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/HCFAInfo.php',
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
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\Common\\:\\:src_dir\\(\\) should return string but returns mixed\\.$#',
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
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaAgeBuilder\\:\\:resolveRuleCriteriaType\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaAgeBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaDatabaseBuilder\\:\\:build\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaDatabaseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaDatabaseBuilder\\:\\:resolveRuleCriteriaType\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaDatabaseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaFactory\\:\\:getBuilderFor\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaListsBuilder\\:\\:build\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaListsBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaListsBuilder\\:\\:resolveRuleCriteriaType\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaListsBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaSexBuilder\\:\\:resolveRuleCriteriaType\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaSexBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType\\:\\:from\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteriaType but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:createFilterRuleCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:createTargetRuleCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleAction\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleAction but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleFilterCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleFilterCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleTargetActionGroups\\(\\) should return array but returns OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleTargetActionGroup\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleTargetCriteriaByGroupId\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleTargetCriteriaByGroupId\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleTargetCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRuleTargetCriteria\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria but returns null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleManager\\:\\:getRule\\(\\) should return OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\Rule but returns null\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Common\\\\Acl\\\\AclExtended\\:\\:getAclVersion\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\AuthUtils\\:\\:collectIpLoginFailsSql\\(\\) should return recordset\\|false but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
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
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ClientRepository\\:\\:getCryptoGen\\(\\) should return OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php',
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
    'message' => '#^Method OpenEMR\\\\Common\\\\Session\\\\PHPSessionWrapper\\:\\:getId\\(\\) should return string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/PHPSessionWrapper.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Session\\\\SessionUtil\\:\\:getAppCookie\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Session\\\\SessionUtil\\:\\:portalSessionStart\\(\\) should return Symfony\\\\Component\\\\HttpFoundation\\\\Session\\\\Session but returns mixed\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Core\\\\OEHttpKernel\\:\\:getEventDispatcher\\(\\) should return Symfony\\\\Component\\\\EventDispatcher\\\\EventDispatcherInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/OEHttpKernel.php',
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
    'message' => '#^Method OpenEMR\\\\Entity\\\\Core\\\\SqlConfig\\:\\:getConfig\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Entity/Core/SqlConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Entity\\\\Core\\\\SqlConfig\\:\\:getDatabaseName\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Entity/Core/SqlConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Entity\\\\Core\\\\SqlConfig\\:\\:getHost\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Entity/Core/SqlConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Entity\\\\Core\\\\SqlConfig\\:\\:getPass\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Entity/Core/SqlConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Entity\\\\Core\\\\SqlConfig\\:\\:getPort\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Entity/Core/SqlConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Entity\\\\Core\\\\SqlConfig\\:\\:getUser\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Entity/Core/SqlConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Events\\\\AbstractBoundFilterEvent\\:\\:getBoundFilter\\(\\) should return string but returns OpenEMR\\\\Events\\\\BoundFilter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/AbstractBoundFilterEvent.php',
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
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAccount\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRAccount.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRActivityDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRActivityDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAdverseEvent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRAdverseEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAllergyIntolerance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRAllergyIntolerance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAppointment\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRAppointment.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAppointmentResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRAppointmentResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAuditEvent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRAuditEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRBasic\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRBasic.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRBiologicallyDerivedProduct\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRBiologicallyDerivedProduct.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRBodyStructure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRBodyStructure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCapabilityStatement\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCapabilityStatement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCarePlan\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCarePlan.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCareTeam.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCatalogEntry\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCatalogEntry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRChargeItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRChargeItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRChargeItemDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRChargeItemDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRClaim\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRClaim.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRClaimResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRClaimResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRClinicalImpression\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRClinicalImpression.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCodeSystem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCodeSystem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCommunication\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCommunication.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCommunicationRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCommunicationRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCompartmentDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCompartmentDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRComposition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRComposition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRConceptMap\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRConceptMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCondition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRConsent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRConsent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRContract\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRContract.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCoverage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCoverage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCoverageEligibilityRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCoverageEligibilityRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCoverageEligibilityResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRCoverageEligibilityResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDetectedIssue\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRDetectedIssue.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDevice\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRDevice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDeviceDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRDeviceDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDeviceMetric\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRDeviceMetric.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDeviceRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRDeviceRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDeviceUseStatement\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRDeviceUseStatement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDiagnosticReport\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRDiagnosticReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentManifest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRDocumentManifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRDocumentReference.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREffectEvidenceSynthesis\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIREffectEvidenceSynthesis.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREncounter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIREncounter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREndpoint\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIREndpoint.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREnrollmentRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIREnrollmentRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREnrollmentResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIREnrollmentResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREpisodeOfCare\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIREpisodeOfCare.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREventDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIREventDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREvidence\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIREvidence.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREvidenceVariable\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIREvidenceVariable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRExampleScenario\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRExampleScenario.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRExplanationOfBenefit\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRExplanationOfBenefit.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRFamilyMemberHistory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRFamilyMemberHistory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRFlag\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRFlag.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRGoal\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRGoal.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRGraphDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRGraphDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRGroup\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRGuidanceResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRGuidanceResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRHealthcareService\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRHealthcareService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImagingStudy\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRImagingStudy.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImmunization\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRImmunization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImmunizationEvaluation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRImmunizationEvaluation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImmunizationRecommendation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRImmunizationRecommendation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImplementationGuide\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRImplementationGuide.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRInsurancePlan\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRInsurancePlan.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRInvoice\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRInvoice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRLibrary\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRLibrary.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRLinkage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRLinkage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRList\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRLocation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRLocation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMeasure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMeasure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMeasureReport\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMeasureReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedia\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedia.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedication\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedication.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationAdministration\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicationAdministration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationDispense\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicationDispense.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationKnowledge\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicationKnowledge.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicationRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationStatement\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicationStatement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProduct\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProduct.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductAuthorization\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProductAuthorization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductContraindication\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProductContraindication.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductIndication\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProductIndication.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductIngredient\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProductIngredient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductInteraction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProductInteraction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductManufactured\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProductManufactured.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductPackaged\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProductPackaged.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductPharmaceutical\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProductPharmaceutical.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductUndesirableEffect\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMedicinalProductUndesirableEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMessageDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMessageDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMessageHeader\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMessageHeader.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMolecularSequence\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRMolecularSequence.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRNamingSystem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRNamingSystem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRNutritionOrder\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRNutritionOrder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRObservation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRObservation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRObservationDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRObservationDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROperationDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIROperationDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROperationOutcome\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIROperationOutcome.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROrganization\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIROrganization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROrganizationAffiliation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIROrganizationAffiliation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPatient\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRPatient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPaymentNotice\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRPaymentNotice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPaymentReconciliation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRPaymentReconciliation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPerson\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRPerson.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPlanDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRPlanDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPractitioner\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRPractitioner.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPractitionerRole\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRPractitionerRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProcedure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRProcedure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRProvenance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRQuestionnaire\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRQuestionnaire.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRQuestionnaireResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRQuestionnaireResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRRelatedPerson\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRRelatedPerson.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRRequestGroup\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRRequestGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRResearchDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRResearchDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRResearchElementDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRResearchElementDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRResearchStudy\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRResearchStudy.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRResearchSubject\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRResearchSubject.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRRiskAssessment\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRRiskAssessment.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRRiskEvidenceSynthesis\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRRiskEvidenceSynthesis.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSchedule\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSchedule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSearchParameter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSearchParameter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRServiceRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRServiceRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSlot\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSlot.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSpecimen\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSpecimen.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSpecimenDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSpecimenDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRStructureDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRStructureDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRStructureMap\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRStructureMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubscription\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSubscription.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSubstance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceNucleicAcid\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSubstanceNucleicAcid.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstancePolymer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSubstancePolymer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceProtein\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSubstanceProtein.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceReferenceInformation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSubstanceReferenceInformation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceSourceMaterial\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSubstanceSourceMaterial.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceSpecification\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSubstanceSpecification.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSupplyDelivery\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSupplyDelivery.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSupplyRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRSupplyRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRTask\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRTask.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRTerminologyCapabilities\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRTerminologyCapabilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRTestReport\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRTestReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRTestScript\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRTestScript.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRValueSet\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRValueSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRVerificationResult\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRVerificationResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRVisionPrescription\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRDomainResource/FHIRVisionPrescription.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAccountStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAccountStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionCardinalityBehavior\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionCardinalityBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionConditionKind\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionConditionKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionGroupingBehavior\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionGroupingBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionParticipantType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionParticipantType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionPrecheckBehavior\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionPrecheckBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionRelationshipType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionRelationshipType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionRequiredBehavior\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionRequiredBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionSelectionBehavior\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionSelectionBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAddress\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAddressType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAddressType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAddressUse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAddressUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAdministrativeGender\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAdministrativeGender.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAdverseEventActuality\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAdverseEventActuality.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAggregationMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAggregationMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAllergyIntoleranceCategory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAllergyIntoleranceCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAllergyIntoleranceCriticality\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAllergyIntoleranceCriticality.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAllergyIntoleranceSeverity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAllergyIntoleranceSeverity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAllergyIntoleranceType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAllergyIntoleranceType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAnnotation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAnnotation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAppointmentStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAppointmentStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAssertionDirectionType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAssertionDirectionType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAssertionOperatorType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAssertionOperatorType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAssertionResponseTypes\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAssertionResponseTypes.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAttachment\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAttachment.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAuditEventAction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAuditEventAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAuditEventAgentNetworkType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAuditEventAgentNetworkType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAuditEventOutcome\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAuditEventOutcome.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBackboneElement\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBackboneElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBase64Binary\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBase64Binary.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBindingStrength\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBindingStrength.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBiologicallyDerivedProductCategory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBiologicallyDerivedProductCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBiologicallyDerivedProductStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBiologicallyDerivedProductStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBiologicallyDerivedProductStorageScale\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBiologicallyDerivedProductStorageScale.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBoolean\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBoolean.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBundleType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBundleType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCanonical\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCanonical.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCapabilityStatementKind\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCapabilityStatementKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCarePlanActivityKind\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCarePlanActivityKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCarePlanActivityStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCarePlanActivityStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCarePlanIntent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCarePlanIntent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCareTeamStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCareTeamStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCatalogEntryRelationType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCatalogEntryRelationType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRChargeItemStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRChargeItemStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRClaimProcessingCodes\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRClaimProcessingCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRClinicalImpressionStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRClinicalImpressionStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeSearchSupport\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCodeSearchSupport.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeSystemContentMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCodeSystemContentMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeSystemHierarchyMeaning\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCodeSystemHierarchyMeaning.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeableConcept\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCodeableConcept.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCoding\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCoding.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCompartmentType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCompartmentType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCompositionAttestationMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCompositionAttestationMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCompositionStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCompositionStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConceptMapEquivalence\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConceptMapEquivalence.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConceptMapGroupUnmappedMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConceptMapGroupUnmappedMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConditionalDeleteStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConditionalDeleteStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConditionalReadStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConditionalReadStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConsentDataMeaning\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConsentDataMeaning.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConsentProvisionType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConsentProvisionType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConsentState\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConsentState.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConstraintSeverity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConstraintSeverity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContactDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContactDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContactPoint\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContactPoint.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContactPointSystem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContactPointSystem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContactPointUse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContactPointUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContractResourcePublicationStatusCodes\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContractResourcePublicationStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContractResourceStatusCodes\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContractResourceStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContributor\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContributor.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContributorType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContributorType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDataRequirement\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDataRequirement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDate\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDateTime\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDateTime.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDaysOfWeek\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDaysOfWeek.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDecimal\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDecimal.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDetectedIssueSeverity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDetectedIssueSeverity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricCalibrationState\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricCalibrationState.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricCalibrationType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricCalibrationType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricCategory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricColor\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricColor.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricOperationalStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricOperationalStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceNameType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceNameType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceUseStatementStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceUseStatementStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDiagnosticReportStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDiagnosticReportStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDiscriminatorType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDiscriminatorType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDocumentMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDocumentMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDocumentReferenceStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDocumentReferenceStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDocumentRelationshipType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDocumentRelationshipType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREligibilityRequestPurpose\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREligibilityRequestPurpose.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREligibilityResponsePurpose\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREligibilityResponsePurpose.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREnableWhenBehavior\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREnableWhenBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREncounterLocationStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREncounterLocationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREncounterStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREncounterStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREndpointStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREndpointStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREpisodeOfCareStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREpisodeOfCareStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREventCapabilityMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREventCapabilityMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREventStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREventStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREventTiming\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREventTiming.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREvidenceVariableType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREvidenceVariableType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExampleScenarioActorType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExampleScenarioActorType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExplanationOfBenefitStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExplanationOfBenefitStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExposureState\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExposureState.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExpression\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExpression.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExpressionLanguage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExpressionLanguage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExtension\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExtensionContextType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExtensionContextType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFHIRDeviceStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFHIRDeviceStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFHIRSubstanceStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFHIRSubstanceStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFHIRVersion\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFHIRVersion.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFamilyHistoryStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFamilyHistoryStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFilterOperator\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFilterOperator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFinancialResourceStatusCodes\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFinancialResourceStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFlagStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFlagStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGoalLifecycleStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGoalLifecycleStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGraphCompartmentRule\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGraphCompartmentRule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGraphCompartmentUse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGraphCompartmentUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGroupMeasure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGroupMeasure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGroupType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGroupType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGuidanceResponseStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGuidanceResponseStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGuidePageGeneration\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGuidePageGeneration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGuideParameterCode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGuideParameterCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRHTTPVerb\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRHTTPVerb.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRHumanName\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRHumanName.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRId\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRId.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRIdentifier\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRIdentifier.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRIdentifierUse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRIdentifierUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRIdentityAssuranceLevel\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRIdentityAssuranceLevel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRImagingStudyStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRImagingStudyStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRImmunizationEvaluationStatusCodes\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRImmunizationEvaluationStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRImmunizationStatusCodes\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRImmunizationStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRInstant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRInstant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRInteger\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRInteger.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRInvoicePriceComponentType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRInvoicePriceComponentType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRInvoiceStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRInvoiceStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRIssueSeverity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRIssueSeverity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRIssueType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRIssueType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRLinkType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRLinkType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRLinkageType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRLinkageType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRListMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRListMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRListStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRListStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRLocationMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRLocationMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRLocationStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRLocationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMarkdown\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMarkdown.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMeasureReportStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMeasureReportStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMeasureReportType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMeasureReportType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMedicationRequestIntent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMedicationRequestIntent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMedicationStatusCodes\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMedicationStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMedicationrequestStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMedicationrequestStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMessageSignificanceCategory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMessageSignificanceCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMessageheaderResponseRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMessageheaderResponseRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMeta\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMeta.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMoney\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMoney.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNameUse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNameUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNamingSystemIdentifierType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNamingSystemIdentifierType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNamingSystemType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNamingSystemType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNarrative\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNarrative.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNarrativeStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNarrativeStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNoteType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNoteType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRObservationDataType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRObservationDataType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRObservationRangeCategory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRObservationRangeCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRObservationStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRObservationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIROid\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIROid.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIROperationKind\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIROperationKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIROperationParameterUse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIROperationParameterUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIROrientationType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIROrientationType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRParameterDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRParameterDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRParticipantRequired\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRParticipantRequired.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRParticipationStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRParticipationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPeriod\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRPeriod.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPositiveInt\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRPositiveInt.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPropertyRepresentation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRPropertyRepresentation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPropertyType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRPropertyType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRProvenanceEntityRole\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRProvenanceEntityRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPublicationStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRPublicationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQualityType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQualityType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuantity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuantity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuantity\\\\FHIRAge\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuantity/FHIRAge.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuantity\\\\FHIRCount\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuantity/FHIRCount.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuantity\\\\FHIRDistance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuantity/FHIRDistance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuantity\\\\FHIRDuration\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuantity/FHIRDuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuantityComparator\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuantityComparator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuestionnaireItemOperator\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuestionnaireItemOperator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuestionnaireItemType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuestionnaireItemType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuestionnaireResponseStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuestionnaireResponseStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRange\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRange.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRatio\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRatio.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReference\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRReference.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReferenceHandlingPolicy\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRReferenceHandlingPolicy.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReferenceVersionRules\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRReferenceVersionRules.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRelatedArtifact\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRelatedArtifact.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRelatedArtifactType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRelatedArtifactType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRemittanceOutcome\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRemittanceOutcome.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRepositoryType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRepositoryType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRequestIntent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRequestIntent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRequestPriority\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRequestPriority.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRequestResourceType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRequestResourceType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRequestStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRequestStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResearchElementType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResearchElementType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResearchStudyStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResearchStudyStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResearchSubjectStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResearchSubjectStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResourceType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResourceType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResourceVersionPolicy\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResourceVersionPolicy.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResponseType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResponseType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRestfulCapabilityMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRestfulCapabilityMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSPDXLicense\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSPDXLicense.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSampledData\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSampledData.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSampledDataDataType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSampledDataDataType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSearchComparator\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSearchComparator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSearchEntryMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSearchEntryMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSearchModifierCode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSearchModifierCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSearchParamType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSearchParamType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSequenceType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSequenceType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSignature\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSignature.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSlicingRules\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSlicingRules.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSlotStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSlotStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSortDirection\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSortDirection.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSpecimenContainedPreference\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSpecimenContainedPreference.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSpecimenStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSpecimenStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStrandType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStrandType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRString\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRString.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureDefinitionKind\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureDefinitionKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapContextType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapContextType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapGroupTypeMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapGroupTypeMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapInputMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapInputMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapModelMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapModelMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapSourceListMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapSourceListMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapTargetListMode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapTargetListMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapTransform\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapTransform.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSubscriptionChannelType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSubscriptionChannelType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSubscriptionStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSubscriptionStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSupplyDeliveryStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSupplyDeliveryStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSupplyRequestStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSupplyRequestStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSystemRestfulInteraction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSystemRestfulInteraction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTaskIntent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTaskIntent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTaskStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTaskStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestReportActionResult\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestReportActionResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestReportParticipantType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestReportParticipantType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestReportResult\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestReportResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestReportStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestReportStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestScriptRequestMethodCode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestScriptRequestMethodCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTime\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTime.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTriggerDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTriggerDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTriggerType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTriggerType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTypeDerivationRule\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTypeDerivationRule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTypeRestfulInteraction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTypeRestfulInteraction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUDIEntryType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUDIEntryType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUnitsOfTime\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUnitsOfTime.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUnsignedInt\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUnsignedInt.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUri\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUri.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUrl\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUrl.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUsageContext\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUsageContext.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUuid\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUuid.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRVConfidentialityClassification\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRVConfidentialityClassification.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRVariableType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRVariableType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRVisionBase\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRVisionBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRVisionEyes\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRVisionEyes.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRXPathUsageType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRXPathUsageType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAccount\\\\FHIRAccountCoverage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAccount/FHIRAccountCoverage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAccount\\\\FHIRAccountGuarantor\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAccount/FHIRAccountGuarantor.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRActivityDefinition\\\\FHIRActivityDefinitionDynamicValue\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRActivityDefinition/FHIRActivityDefinitionDynamicValue.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRActivityDefinition\\\\FHIRActivityDefinitionParticipant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRActivityDefinition/FHIRActivityDefinitionParticipant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAdverseEvent\\\\FHIRAdverseEventCausality\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAdverseEvent/FHIRAdverseEventCausality.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAdverseEvent\\\\FHIRAdverseEventSuspectEntity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAdverseEvent/FHIRAdverseEventSuspectEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAllergyIntolerance\\\\FHIRAllergyIntoleranceReaction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAllergyIntolerance/FHIRAllergyIntoleranceReaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAppointment\\\\FHIRAppointmentParticipant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAppointment/FHIRAppointmentParticipant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAuditEvent\\\\FHIRAuditEventAgent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAuditEvent/FHIRAuditEventAgent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAuditEvent\\\\FHIRAuditEventDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAuditEvent/FHIRAuditEventDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAuditEvent\\\\FHIRAuditEventEntity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAuditEvent/FHIRAuditEventEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAuditEvent\\\\FHIRAuditEventNetwork\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAuditEvent/FHIRAuditEventNetwork.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRAuditEvent\\\\FHIRAuditEventSource\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRAuditEvent/FHIRAuditEventSource.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBinary\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBinary.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBiologicallyDerivedProduct\\\\FHIRBiologicallyDerivedProductCollection\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBiologicallyDerivedProduct/FHIRBiologicallyDerivedProductCollection.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBiologicallyDerivedProduct\\\\FHIRBiologicallyDerivedProductManipulation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBiologicallyDerivedProduct/FHIRBiologicallyDerivedProductManipulation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBiologicallyDerivedProduct\\\\FHIRBiologicallyDerivedProductProcessing\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBiologicallyDerivedProduct/FHIRBiologicallyDerivedProductProcessing.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBiologicallyDerivedProduct\\\\FHIRBiologicallyDerivedProductStorage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBiologicallyDerivedProduct/FHIRBiologicallyDerivedProductStorage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBundle\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBundle.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBundle\\\\FHIRBundleEntry\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBundle/FHIRBundleEntry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBundle\\\\FHIRBundleLink\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBundle/FHIRBundleLink.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBundle\\\\FHIRBundleRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBundle/FHIRBundleRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBundle\\\\FHIRBundleResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBundle/FHIRBundleResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBundle\\\\FHIRBundleSearch\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRBundle/FHIRBundleSearch.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementDocument\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementDocument.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementEndpoint\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementEndpoint.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementImplementation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementImplementation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementInteraction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementInteraction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementInteraction1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementInteraction1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementMessaging\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementMessaging.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementOperation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementOperation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementResource\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementResource.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementRest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementRest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementSearchParam\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementSearchParam.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementSecurity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementSecurity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementSoftware\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementSoftware.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCapabilityStatement\\\\FHIRCapabilityStatementSupportedMessage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCapabilityStatement/FHIRCapabilityStatementSupportedMessage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCarePlan\\\\FHIRCarePlanActivity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCarePlan/FHIRCarePlanActivity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCarePlan\\\\FHIRCarePlanDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCarePlan/FHIRCarePlanDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCareTeam\\\\FHIRCareTeamParticipant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCareTeam/FHIRCareTeamParticipant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCatalogEntry\\\\FHIRCatalogEntryRelatedEntry\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCatalogEntry/FHIRCatalogEntryRelatedEntry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRChargeItem\\\\FHIRChargeItemPerformer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRChargeItem/FHIRChargeItemPerformer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRChargeItemDefinition\\\\FHIRChargeItemDefinitionApplicability\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRChargeItemDefinition/FHIRChargeItemDefinitionApplicability.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRChargeItemDefinition\\\\FHIRChargeItemDefinitionPriceComponent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRChargeItemDefinition/FHIRChargeItemDefinitionPriceComponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRChargeItemDefinition\\\\FHIRChargeItemDefinitionPropertyGroup\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRChargeItemDefinition/FHIRChargeItemDefinitionPropertyGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimAccident\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimAccident.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimCareTeam\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimCareTeam.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimDiagnosis\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimDiagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimInsurance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimInsurance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimPayee\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimPayee.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimProcedure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimProcedure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimRelated\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimRelated.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimSubDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimSubDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaim\\\\FHIRClaimSupportingInfo\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaim/FHIRClaimSupportingInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseAddItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseAddItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseAdjudication\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseAdjudication.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseDetail1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseDetail1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseError\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseError.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseInsurance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseInsurance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponsePayment\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponsePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseProcessNote\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseProcessNote.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseSubDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseSubDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseSubDetail1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseSubDetail1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClaimResponse\\\\FHIRClaimResponseTotal\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClaimResponse/FHIRClaimResponseTotal.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClinicalImpression\\\\FHIRClinicalImpressionFinding\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClinicalImpression/FHIRClinicalImpressionFinding.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRClinicalImpression\\\\FHIRClinicalImpressionInvestigation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRClinicalImpression/FHIRClinicalImpressionInvestigation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCodeSystem\\\\FHIRCodeSystemConcept\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCodeSystem/FHIRCodeSystemConcept.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCodeSystem\\\\FHIRCodeSystemDesignation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCodeSystem/FHIRCodeSystemDesignation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCodeSystem\\\\FHIRCodeSystemFilter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCodeSystem/FHIRCodeSystemFilter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCodeSystem\\\\FHIRCodeSystemProperty\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCodeSystem/FHIRCodeSystemProperty.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCodeSystem\\\\FHIRCodeSystemProperty1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCodeSystem/FHIRCodeSystemProperty1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCommunication\\\\FHIRCommunicationPayload\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCommunication/FHIRCommunicationPayload.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCommunicationRequest\\\\FHIRCommunicationRequestPayload\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCommunicationRequest/FHIRCommunicationRequestPayload.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCompartmentDefinition\\\\FHIRCompartmentDefinitionResource\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCompartmentDefinition/FHIRCompartmentDefinitionResource.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRComposition\\\\FHIRCompositionAttester\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRComposition/FHIRCompositionAttester.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRComposition\\\\FHIRCompositionEvent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRComposition/FHIRCompositionEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRComposition\\\\FHIRCompositionRelatesTo\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRComposition/FHIRCompositionRelatesTo.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRComposition\\\\FHIRCompositionSection\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRComposition/FHIRCompositionSection.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConceptMap\\\\FHIRConceptMapDependsOn\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConceptMap/FHIRConceptMapDependsOn.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConceptMap\\\\FHIRConceptMapElement\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConceptMap/FHIRConceptMapElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConceptMap\\\\FHIRConceptMapGroup\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConceptMap/FHIRConceptMapGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConceptMap\\\\FHIRConceptMapTarget\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConceptMap/FHIRConceptMapTarget.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConceptMap\\\\FHIRConceptMapUnmapped\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConceptMap/FHIRConceptMapUnmapped.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCondition\\\\FHIRConditionEvidence\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCondition/FHIRConditionEvidence.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCondition\\\\FHIRConditionStage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCondition/FHIRConditionStage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConsent\\\\FHIRConsentActor\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConsent/FHIRConsentActor.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConsent\\\\FHIRConsentData\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConsent/FHIRConsentData.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConsent\\\\FHIRConsentPolicy\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConsent/FHIRConsentPolicy.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConsent\\\\FHIRConsentProvision\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConsent/FHIRConsentProvision.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRConsent\\\\FHIRConsentVerification\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRConsent/FHIRConsentVerification.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractAction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractAnswer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractAnswer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractAsset\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractAsset.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractContentDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractContentDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractContext\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractContext.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractFriendly\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractFriendly.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractLegal\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractLegal.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractOffer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractOffer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractParty\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractParty.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractRule\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractRule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractSecurityLabel\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractSecurityLabel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractSigner\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractSigner.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractSubject\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractSubject.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractTerm\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractTerm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRContract\\\\FHIRContractValuedItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRContract/FHIRContractValuedItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverage\\\\FHIRCoverageClass\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverage/FHIRCoverageClass.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverage\\\\FHIRCoverageCostToBeneficiary\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverage/FHIRCoverageCostToBeneficiary.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverage\\\\FHIRCoverageException\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverage/FHIRCoverageException.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverageEligibilityRequest\\\\FHIRCoverageEligibilityRequestDiagnosis\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverageEligibilityRequest/FHIRCoverageEligibilityRequestDiagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverageEligibilityRequest\\\\FHIRCoverageEligibilityRequestInsurance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverageEligibilityRequest/FHIRCoverageEligibilityRequestInsurance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverageEligibilityRequest\\\\FHIRCoverageEligibilityRequestItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverageEligibilityRequest/FHIRCoverageEligibilityRequestItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverageEligibilityRequest\\\\FHIRCoverageEligibilityRequestSupportingInfo\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverageEligibilityRequest/FHIRCoverageEligibilityRequestSupportingInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverageEligibilityResponse\\\\FHIRCoverageEligibilityResponseBenefit\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverageEligibilityResponse/FHIRCoverageEligibilityResponseBenefit.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverageEligibilityResponse\\\\FHIRCoverageEligibilityResponseError\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverageEligibilityResponse/FHIRCoverageEligibilityResponseError.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverageEligibilityResponse\\\\FHIRCoverageEligibilityResponseInsurance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverageEligibilityResponse/FHIRCoverageEligibilityResponseInsurance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRCoverageEligibilityResponse\\\\FHIRCoverageEligibilityResponseItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRCoverageEligibilityResponse/FHIRCoverageEligibilityResponseItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDataRequirement\\\\FHIRDataRequirementCodeFilter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDataRequirement/FHIRDataRequirementCodeFilter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDataRequirement\\\\FHIRDataRequirementDateFilter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDataRequirement/FHIRDataRequirementDateFilter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDataRequirement\\\\FHIRDataRequirementSort\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDataRequirement/FHIRDataRequirementSort.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDetectedIssue\\\\FHIRDetectedIssueEvidence\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDetectedIssue/FHIRDetectedIssueEvidence.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDetectedIssue\\\\FHIRDetectedIssueMitigation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDetectedIssue/FHIRDetectedIssueMitigation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDevice\\\\FHIRDeviceDeviceName\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDevice/FHIRDeviceDeviceName.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDevice\\\\FHIRDeviceProperty\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDevice/FHIRDeviceProperty.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDevice\\\\FHIRDeviceSpecialization\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDevice/FHIRDeviceSpecialization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDevice\\\\FHIRDeviceUdiCarrier\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDevice/FHIRDeviceUdiCarrier.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDevice\\\\FHIRDeviceVersion\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDevice/FHIRDeviceVersion.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDeviceDefinition\\\\FHIRDeviceDefinitionCapability\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDeviceDefinition/FHIRDeviceDefinitionCapability.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDeviceDefinition\\\\FHIRDeviceDefinitionDeviceName\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDeviceDefinition/FHIRDeviceDefinitionDeviceName.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDeviceDefinition\\\\FHIRDeviceDefinitionMaterial\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDeviceDefinition/FHIRDeviceDefinitionMaterial.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDeviceDefinition\\\\FHIRDeviceDefinitionProperty\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDeviceDefinition/FHIRDeviceDefinitionProperty.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDeviceDefinition\\\\FHIRDeviceDefinitionSpecialization\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDeviceDefinition/FHIRDeviceDefinitionSpecialization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDeviceDefinition\\\\FHIRDeviceDefinitionUdiDeviceIdentifier\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDeviceDefinition/FHIRDeviceDefinitionUdiDeviceIdentifier.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDeviceMetric\\\\FHIRDeviceMetricCalibration\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDeviceMetric/FHIRDeviceMetricCalibration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDeviceRequest\\\\FHIRDeviceRequestParameter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDeviceRequest/FHIRDeviceRequestParameter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDiagnosticReport\\\\FHIRDiagnosticReportMedia\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDiagnosticReport/FHIRDiagnosticReportMedia.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDocumentManifest\\\\FHIRDocumentManifestRelated\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDocumentManifest/FHIRDocumentManifestRelated.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDocumentReference\\\\FHIRDocumentReferenceContent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDocumentReference/FHIRDocumentReferenceContent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDocumentReference\\\\FHIRDocumentReferenceContext\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDocumentReference/FHIRDocumentReferenceContext.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDocumentReference\\\\FHIRDocumentReferenceRelatesTo\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDocumentReference/FHIRDocumentReferenceRelatesTo.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDomainResource.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDosage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDosage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDosage\\\\FHIRDosageDoseAndRate\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDosage/FHIRDosageDoseAndRate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREffectEvidenceSynthesis\\\\FHIREffectEvidenceSynthesisCertainty\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREffectEvidenceSynthesis/FHIREffectEvidenceSynthesisCertainty.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREffectEvidenceSynthesis\\\\FHIREffectEvidenceSynthesisCertaintySubcomponent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREffectEvidenceSynthesis/FHIREffectEvidenceSynthesisCertaintySubcomponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREffectEvidenceSynthesis\\\\FHIREffectEvidenceSynthesisEffectEstimate\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREffectEvidenceSynthesis/FHIREffectEvidenceSynthesisEffectEstimate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREffectEvidenceSynthesis\\\\FHIREffectEvidenceSynthesisPrecisionEstimate\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREffectEvidenceSynthesis/FHIREffectEvidenceSynthesisPrecisionEstimate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREffectEvidenceSynthesis\\\\FHIREffectEvidenceSynthesisResultsByExposure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREffectEvidenceSynthesis/FHIREffectEvidenceSynthesisResultsByExposure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREffectEvidenceSynthesis\\\\FHIREffectEvidenceSynthesisSampleSize\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREffectEvidenceSynthesis/FHIREffectEvidenceSynthesisSampleSize.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRElementDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRElementDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRElementDefinition\\\\FHIRElementDefinitionBase\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRElementDefinition/FHIRElementDefinitionBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRElementDefinition\\\\FHIRElementDefinitionBinding\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRElementDefinition/FHIRElementDefinitionBinding.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRElementDefinition\\\\FHIRElementDefinitionConstraint\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRElementDefinition/FHIRElementDefinitionConstraint.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRElementDefinition\\\\FHIRElementDefinitionDiscriminator\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRElementDefinition/FHIRElementDefinitionDiscriminator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRElementDefinition\\\\FHIRElementDefinitionExample\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRElementDefinition/FHIRElementDefinitionExample.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRElementDefinition\\\\FHIRElementDefinitionMapping\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRElementDefinition/FHIRElementDefinitionMapping.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRElementDefinition\\\\FHIRElementDefinitionSlicing\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRElementDefinition/FHIRElementDefinitionSlicing.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRElementDefinition\\\\FHIRElementDefinitionType\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRElementDefinition/FHIRElementDefinitionType.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREncounter\\\\FHIREncounterClassHistory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREncounter/FHIREncounterClassHistory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREncounter\\\\FHIREncounterDiagnosis\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREncounter/FHIREncounterDiagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREncounter\\\\FHIREncounterHospitalization\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREncounter/FHIREncounterHospitalization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREncounter\\\\FHIREncounterLocation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREncounter/FHIREncounterLocation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREncounter\\\\FHIREncounterParticipant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREncounter/FHIREncounterParticipant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREncounter\\\\FHIREncounterStatusHistory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREncounter/FHIREncounterStatusHistory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREpisodeOfCare\\\\FHIREpisodeOfCareDiagnosis\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREpisodeOfCare/FHIREpisodeOfCareDiagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREpisodeOfCare\\\\FHIREpisodeOfCareStatusHistory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREpisodeOfCare/FHIREpisodeOfCareStatusHistory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIREvidenceVariable\\\\FHIREvidenceVariableCharacteristic\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIREvidenceVariable/FHIREvidenceVariableCharacteristic.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExampleScenario\\\\FHIRExampleScenarioActor\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExampleScenario/FHIRExampleScenarioActor.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExampleScenario\\\\FHIRExampleScenarioAlternative\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExampleScenario/FHIRExampleScenarioAlternative.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExampleScenario\\\\FHIRExampleScenarioContainedInstance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExampleScenario/FHIRExampleScenarioContainedInstance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExampleScenario\\\\FHIRExampleScenarioInstance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExampleScenario/FHIRExampleScenarioInstance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExampleScenario\\\\FHIRExampleScenarioOperation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExampleScenario/FHIRExampleScenarioOperation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExampleScenario\\\\FHIRExampleScenarioProcess\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExampleScenario/FHIRExampleScenarioProcess.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExampleScenario\\\\FHIRExampleScenarioStep\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExampleScenario/FHIRExampleScenarioStep.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExampleScenario\\\\FHIRExampleScenarioVersion\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExampleScenario/FHIRExampleScenarioVersion.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitAccident\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitAccident.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitAddItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitAddItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitAdjudication\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitAdjudication.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitBenefitBalance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitBenefitBalance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitCareTeam\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitCareTeam.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitDetail1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitDetail1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitDiagnosis\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitDiagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitFinancial\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitFinancial.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitInsurance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitInsurance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitPayee\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitPayee.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitPayment\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitPayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitProcedure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitProcedure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitProcessNote\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitProcessNote.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitRelated\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitRelated.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitSubDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitSubDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitSubDetail1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitSubDetail1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitSupportingInfo\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitSupportingInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRExplanationOfBenefit\\\\FHIRExplanationOfBenefitTotal\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRExplanationOfBenefit/FHIRExplanationOfBenefitTotal.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRFamilyMemberHistory\\\\FHIRFamilyMemberHistoryCondition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRFamilyMemberHistory/FHIRFamilyMemberHistoryCondition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRGoal\\\\FHIRGoalTarget\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRGoal/FHIRGoalTarget.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRGraphDefinition\\\\FHIRGraphDefinitionCompartment\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRGraphDefinition/FHIRGraphDefinitionCompartment.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRGraphDefinition\\\\FHIRGraphDefinitionLink\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRGraphDefinition/FHIRGraphDefinitionLink.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRGraphDefinition\\\\FHIRGraphDefinitionTarget\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRGraphDefinition/FHIRGraphDefinitionTarget.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRGroup\\\\FHIRGroupCharacteristic\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRGroup/FHIRGroupCharacteristic.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRGroup\\\\FHIRGroupMember\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRGroup/FHIRGroupMember.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRHealthcareService\\\\FHIRHealthcareServiceAvailableTime\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRHealthcareService/FHIRHealthcareServiceAvailableTime.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRHealthcareService\\\\FHIRHealthcareServiceEligibility\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRHealthcareService/FHIRHealthcareServiceEligibility.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRHealthcareService\\\\FHIRHealthcareServiceNotAvailable\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRHealthcareService/FHIRHealthcareServiceNotAvailable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImagingStudy\\\\FHIRImagingStudyInstance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImagingStudy/FHIRImagingStudyInstance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImagingStudy\\\\FHIRImagingStudyPerformer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImagingStudy/FHIRImagingStudyPerformer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImagingStudy\\\\FHIRImagingStudySeries\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImagingStudy/FHIRImagingStudySeries.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImmunization\\\\FHIRImmunizationEducation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImmunization/FHIRImmunizationEducation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImmunization\\\\FHIRImmunizationPerformer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImmunization/FHIRImmunizationPerformer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImmunization\\\\FHIRImmunizationProtocolApplied\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImmunization/FHIRImmunizationProtocolApplied.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImmunization\\\\FHIRImmunizationReaction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImmunization/FHIRImmunizationReaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImmunizationRecommendation\\\\FHIRImmunizationRecommendationDateCriterion\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImmunizationRecommendation/FHIRImmunizationRecommendationDateCriterion.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImmunizationRecommendation\\\\FHIRImmunizationRecommendationRecommendation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImmunizationRecommendation/FHIRImmunizationRecommendationRecommendation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuideDefinition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuideDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuideDependsOn\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuideDependsOn.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuideGlobal\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuideGlobal.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuideGrouping\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuideGrouping.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuideManifest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuideManifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuidePage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuidePage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuidePage1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuidePage1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuideParameter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuideParameter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuideResource\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuideResource.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuideResource1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuideResource1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRImplementationGuide\\\\FHIRImplementationGuideTemplate\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRImplementationGuide/FHIRImplementationGuideTemplate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInsurancePlan\\\\FHIRInsurancePlanBenefit\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInsurancePlan/FHIRInsurancePlanBenefit.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInsurancePlan\\\\FHIRInsurancePlanBenefit1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInsurancePlan/FHIRInsurancePlanBenefit1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInsurancePlan\\\\FHIRInsurancePlanContact\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInsurancePlan/FHIRInsurancePlanContact.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInsurancePlan\\\\FHIRInsurancePlanCost\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInsurancePlan/FHIRInsurancePlanCost.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInsurancePlan\\\\FHIRInsurancePlanCoverage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInsurancePlan/FHIRInsurancePlanCoverage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInsurancePlan\\\\FHIRInsurancePlanGeneralCost\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInsurancePlan/FHIRInsurancePlanGeneralCost.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInsurancePlan\\\\FHIRInsurancePlanLimit\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInsurancePlan/FHIRInsurancePlanLimit.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInsurancePlan\\\\FHIRInsurancePlanPlan\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInsurancePlan/FHIRInsurancePlanPlan.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInsurancePlan\\\\FHIRInsurancePlanSpecificCost\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInsurancePlan/FHIRInsurancePlanSpecificCost.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInvoice\\\\FHIRInvoiceLineItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInvoice/FHIRInvoiceLineItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInvoice\\\\FHIRInvoiceParticipant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInvoice/FHIRInvoiceParticipant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRInvoice\\\\FHIRInvoicePriceComponent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRInvoice/FHIRInvoicePriceComponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRLinkage\\\\FHIRLinkageItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRLinkage/FHIRLinkageItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRList\\\\FHIRListEntry\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRList/FHIRListEntry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRLocation\\\\FHIRLocationHoursOfOperation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRLocation/FHIRLocationHoursOfOperation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRLocation\\\\FHIRLocationPosition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRLocation/FHIRLocationPosition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMarketingStatus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMarketingStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasure\\\\FHIRMeasureComponent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasure/FHIRMeasureComponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasure\\\\FHIRMeasureGroup\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasure/FHIRMeasureGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasure\\\\FHIRMeasurePopulation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasure/FHIRMeasurePopulation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasure\\\\FHIRMeasureStratifier\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasure/FHIRMeasureStratifier.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasure\\\\FHIRMeasureSupplementalData\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasure/FHIRMeasureSupplementalData.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasureReport\\\\FHIRMeasureReportComponent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasureReport/FHIRMeasureReportComponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasureReport\\\\FHIRMeasureReportGroup\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasureReport/FHIRMeasureReportGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasureReport\\\\FHIRMeasureReportPopulation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasureReport/FHIRMeasureReportPopulation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasureReport\\\\FHIRMeasureReportPopulation1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasureReport/FHIRMeasureReportPopulation1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasureReport\\\\FHIRMeasureReportStratifier\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasureReport/FHIRMeasureReportStratifier.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMeasureReport\\\\FHIRMeasureReportStratum\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMeasureReport/FHIRMeasureReportStratum.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedication\\\\FHIRMedicationBatch\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedication/FHIRMedicationBatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedication\\\\FHIRMedicationIngredient\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedication/FHIRMedicationIngredient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationAdministration\\\\FHIRMedicationAdministrationDosage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationAdministration/FHIRMedicationAdministrationDosage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationAdministration\\\\FHIRMedicationAdministrationPerformer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationAdministration/FHIRMedicationAdministrationPerformer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationDispense\\\\FHIRMedicationDispensePerformer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationDispense/FHIRMedicationDispensePerformer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationDispense\\\\FHIRMedicationDispenseSubstitution\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationDispense/FHIRMedicationDispenseSubstitution.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeAdministrationGuidelines\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeAdministrationGuidelines.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeCost\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeCost.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeDosage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeDosage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeDrugCharacteristic\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeDrugCharacteristic.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeIngredient\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeIngredient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeKinetics\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeKinetics.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeMaxDispense\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeMaxDispense.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeMedicineClassification\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeMedicineClassification.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeMonitoringProgram\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeMonitoringProgram.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeMonograph\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeMonograph.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgePackaging\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgePackaging.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgePatientCharacteristics\\:\\:__toString\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgePatientCharacteristics.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgePatientCharacteristics\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgePatientCharacteristics.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeRegulatory\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeRegulatory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeRelatedMedicationKnowledge\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeRelatedMedicationKnowledge.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeSchedule\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeSchedule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationKnowledge\\\\FHIRMedicationKnowledgeSubstitution\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationKnowledge/FHIRMedicationKnowledgeSubstitution.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationRequest\\\\FHIRMedicationRequestDispenseRequest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationRequest/FHIRMedicationRequestDispenseRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationRequest\\\\FHIRMedicationRequestInitialFill\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationRequest/FHIRMedicationRequestInitialFill.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationRequest\\\\FHIRMedicationRequestSubstitution\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicationRequest/FHIRMedicationRequestSubstitution.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProduct\\\\FHIRMedicinalProductCountryLanguage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProduct/FHIRMedicinalProductCountryLanguage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProduct\\\\FHIRMedicinalProductManufacturingBusinessOperation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProduct/FHIRMedicinalProductManufacturingBusinessOperation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProduct\\\\FHIRMedicinalProductName\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProduct/FHIRMedicinalProductName.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProduct\\\\FHIRMedicinalProductNamePart\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProduct/FHIRMedicinalProductNamePart.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProduct\\\\FHIRMedicinalProductSpecialDesignation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProduct/FHIRMedicinalProductSpecialDesignation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductAuthorization\\\\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductAuthorization/FHIRMedicinalProductAuthorizationJurisdictionalAuthorization.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductAuthorization\\\\FHIRMedicinalProductAuthorizationProcedure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductAuthorization/FHIRMedicinalProductAuthorizationProcedure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductContraindication\\\\FHIRMedicinalProductContraindicationOtherTherapy\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductContraindication/FHIRMedicinalProductContraindicationOtherTherapy.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductIndication\\\\FHIRMedicinalProductIndicationOtherTherapy\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductIndication/FHIRMedicinalProductIndicationOtherTherapy.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductIngredient\\\\FHIRMedicinalProductIngredientReferenceStrength\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductIngredient/FHIRMedicinalProductIngredientReferenceStrength.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductIngredient\\\\FHIRMedicinalProductIngredientSpecifiedSubstance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductIngredient/FHIRMedicinalProductIngredientSpecifiedSubstance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductIngredient\\\\FHIRMedicinalProductIngredientStrength\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductIngredient/FHIRMedicinalProductIngredientStrength.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductIngredient\\\\FHIRMedicinalProductIngredientSubstance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductIngredient/FHIRMedicinalProductIngredientSubstance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductInteraction\\\\FHIRMedicinalProductInteractionInteractant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductInteraction/FHIRMedicinalProductInteractionInteractant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductPackaged\\\\FHIRMedicinalProductPackagedBatchIdentifier\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductPackaged/FHIRMedicinalProductPackagedBatchIdentifier.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductPackaged\\\\FHIRMedicinalProductPackagedPackageItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductPackaged/FHIRMedicinalProductPackagedPackageItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductPharmaceutical\\\\FHIRMedicinalProductPharmaceuticalCharacteristics\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductPharmaceutical/FHIRMedicinalProductPharmaceuticalCharacteristics.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductPharmaceutical\\\\FHIRMedicinalProductPharmaceuticalRouteOfAdministration\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductPharmaceutical/FHIRMedicinalProductPharmaceuticalRouteOfAdministration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductPharmaceutical\\\\FHIRMedicinalProductPharmaceuticalTargetSpecies\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductPharmaceutical/FHIRMedicinalProductPharmaceuticalTargetSpecies.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicinalProductPharmaceutical\\\\FHIRMedicinalProductPharmaceuticalWithdrawalPeriod\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMedicinalProductPharmaceutical/FHIRMedicinalProductPharmaceuticalWithdrawalPeriod.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMessageDefinition\\\\FHIRMessageDefinitionAllowedResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMessageDefinition/FHIRMessageDefinitionAllowedResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMessageDefinition\\\\FHIRMessageDefinitionFocus\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMessageDefinition/FHIRMessageDefinitionFocus.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMessageHeader\\\\FHIRMessageHeaderDestination\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMessageHeader/FHIRMessageHeaderDestination.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMessageHeader\\\\FHIRMessageHeaderResponse\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMessageHeader/FHIRMessageHeaderResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMessageHeader\\\\FHIRMessageHeaderSource\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMessageHeader/FHIRMessageHeaderSource.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMolecularSequence\\\\FHIRMolecularSequenceInner\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMolecularSequence/FHIRMolecularSequenceInner.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMolecularSequence\\\\FHIRMolecularSequenceOuter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMolecularSequence/FHIRMolecularSequenceOuter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMolecularSequence\\\\FHIRMolecularSequenceQuality\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMolecularSequence/FHIRMolecularSequenceQuality.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMolecularSequence\\\\FHIRMolecularSequenceReferenceSeq\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMolecularSequence/FHIRMolecularSequenceReferenceSeq.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMolecularSequence\\\\FHIRMolecularSequenceRepository\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMolecularSequence/FHIRMolecularSequenceRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMolecularSequence\\\\FHIRMolecularSequenceRoc\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMolecularSequence/FHIRMolecularSequenceRoc.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMolecularSequence\\\\FHIRMolecularSequenceStructureVariant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMolecularSequence/FHIRMolecularSequenceStructureVariant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMolecularSequence\\\\FHIRMolecularSequenceVariant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRMolecularSequence/FHIRMolecularSequenceVariant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRNamingSystem\\\\FHIRNamingSystemUniqueId\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRNamingSystem/FHIRNamingSystemUniqueId.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRNutritionOrder\\\\FHIRNutritionOrderAdministration\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRNutritionOrder/FHIRNutritionOrderAdministration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRNutritionOrder\\\\FHIRNutritionOrderEnteralFormula\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRNutritionOrder/FHIRNutritionOrderEnteralFormula.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRNutritionOrder\\\\FHIRNutritionOrderNutrient\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRNutritionOrder/FHIRNutritionOrderNutrient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRNutritionOrder\\\\FHIRNutritionOrderOralDiet\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRNutritionOrder/FHIRNutritionOrderOralDiet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRNutritionOrder\\\\FHIRNutritionOrderSupplement\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRNutritionOrder/FHIRNutritionOrderSupplement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRNutritionOrder\\\\FHIRNutritionOrderTexture\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRNutritionOrder/FHIRNutritionOrderTexture.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRObservation\\\\FHIRObservationComponent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRObservation/FHIRObservationComponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRObservation\\\\FHIRObservationReferenceRange\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRObservation/FHIRObservationReferenceRange.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRObservationDefinition\\\\FHIRObservationDefinitionQualifiedInterval\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRObservationDefinition/FHIRObservationDefinitionQualifiedInterval.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRObservationDefinition\\\\FHIRObservationDefinitionQuantitativeDetails\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRObservationDefinition/FHIRObservationDefinitionQuantitativeDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIROperationDefinition\\\\FHIROperationDefinitionBinding\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIROperationDefinition/FHIROperationDefinitionBinding.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIROperationDefinition\\\\FHIROperationDefinitionOverload\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIROperationDefinition/FHIROperationDefinitionOverload.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIROperationDefinition\\\\FHIROperationDefinitionParameter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIROperationDefinition/FHIROperationDefinitionParameter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIROperationDefinition\\\\FHIROperationDefinitionReferencedFrom\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIROperationDefinition/FHIROperationDefinitionReferencedFrom.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIROperationOutcome\\\\FHIROperationOutcomeIssue\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIROperationOutcome/FHIROperationOutcomeIssue.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIROrganization\\\\FHIROrganizationContact\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIROrganization/FHIROrganizationContact.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRParameters\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRParameters.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRParameters\\\\FHIRParametersParameter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRParameters/FHIRParametersParameter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPatient\\\\FHIRPatientCommunication\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPatient/FHIRPatientCommunication.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPatient\\\\FHIRPatientContact\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPatient/FHIRPatientContact.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPatient\\\\FHIRPatientLink\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPatient/FHIRPatientLink.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPaymentReconciliation\\\\FHIRPaymentReconciliationDetail\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPaymentReconciliation/FHIRPaymentReconciliationDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPaymentReconciliation\\\\FHIRPaymentReconciliationProcessNote\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPaymentReconciliation/FHIRPaymentReconciliationProcessNote.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPerson\\\\FHIRPersonLink\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPerson/FHIRPersonLink.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPlanDefinition\\\\FHIRPlanDefinitionAction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPlanDefinition/FHIRPlanDefinitionAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPlanDefinition\\\\FHIRPlanDefinitionCondition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPlanDefinition/FHIRPlanDefinitionCondition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPlanDefinition\\\\FHIRPlanDefinitionDynamicValue\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPlanDefinition/FHIRPlanDefinitionDynamicValue.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPlanDefinition\\\\FHIRPlanDefinitionGoal\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPlanDefinition/FHIRPlanDefinitionGoal.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPlanDefinition\\\\FHIRPlanDefinitionParticipant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPlanDefinition/FHIRPlanDefinitionParticipant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPlanDefinition\\\\FHIRPlanDefinitionRelatedAction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPlanDefinition/FHIRPlanDefinitionRelatedAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPlanDefinition\\\\FHIRPlanDefinitionTarget\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPlanDefinition/FHIRPlanDefinitionTarget.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPopulation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPopulation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPractitioner\\\\FHIRPractitionerQualification\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPractitioner/FHIRPractitionerQualification.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPractitionerRole\\\\FHIRPractitionerRoleAvailableTime\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPractitionerRole/FHIRPractitionerRoleAvailableTime.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRPractitionerRole\\\\FHIRPractitionerRoleNotAvailable\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRPractitionerRole/FHIRPractitionerRoleNotAvailable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRProcedure\\\\FHIRProcedureFocalDevice\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRProcedure/FHIRProcedureFocalDevice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRProcedure\\\\FHIRProcedurePerformer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRProcedure/FHIRProcedurePerformer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRProdCharacteristic\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRProdCharacteristic.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRProductShelfLife\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRProductShelfLife.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRProvenance\\\\FHIRProvenanceAgent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRProvenance/FHIRProvenanceAgent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRProvenance\\\\FHIRProvenanceEntity\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRProvenance/FHIRProvenanceEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRQuestionnaire\\\\FHIRQuestionnaireAnswerOption\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRQuestionnaire/FHIRQuestionnaireAnswerOption.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRQuestionnaire\\\\FHIRQuestionnaireEnableWhen\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRQuestionnaire/FHIRQuestionnaireEnableWhen.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRQuestionnaire\\\\FHIRQuestionnaireInitial\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRQuestionnaire/FHIRQuestionnaireInitial.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRQuestionnaire\\\\FHIRQuestionnaireItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRQuestionnaire/FHIRQuestionnaireItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRQuestionnaireResponse\\\\FHIRQuestionnaireResponseAnswer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRQuestionnaireResponse/FHIRQuestionnaireResponseAnswer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRQuestionnaireResponse\\\\FHIRQuestionnaireResponseItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRQuestionnaireResponse/FHIRQuestionnaireResponseItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRelatedPerson\\\\FHIRRelatedPersonCommunication\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRelatedPerson/FHIRRelatedPersonCommunication.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRequestGroup\\\\FHIRRequestGroupAction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRequestGroup/FHIRRequestGroupAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRequestGroup\\\\FHIRRequestGroupCondition\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRequestGroup/FHIRRequestGroupCondition.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRequestGroup\\\\FHIRRequestGroupRelatedAction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRequestGroup/FHIRRequestGroupRelatedAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRResearchElementDefinition\\\\FHIRResearchElementDefinitionCharacteristic\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRResearchElementDefinition/FHIRResearchElementDefinitionCharacteristic.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRResearchStudy\\\\FHIRResearchStudyArm\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRResearchStudy/FHIRResearchStudyArm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRResearchStudy\\\\FHIRResearchStudyObjective\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRResearchStudy/FHIRResearchStudyObjective.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRiskAssessment\\\\FHIRRiskAssessmentPrediction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRiskAssessment/FHIRRiskAssessmentPrediction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRiskEvidenceSynthesis\\\\FHIRRiskEvidenceSynthesisCertainty\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRiskEvidenceSynthesis/FHIRRiskEvidenceSynthesisCertainty.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRiskEvidenceSynthesis\\\\FHIRRiskEvidenceSynthesisCertaintySubcomponent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRiskEvidenceSynthesis/FHIRRiskEvidenceSynthesisCertaintySubcomponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRiskEvidenceSynthesis\\\\FHIRRiskEvidenceSynthesisPrecisionEstimate\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRiskEvidenceSynthesis/FHIRRiskEvidenceSynthesisPrecisionEstimate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRiskEvidenceSynthesis\\\\FHIRRiskEvidenceSynthesisRiskEstimate\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRiskEvidenceSynthesis/FHIRRiskEvidenceSynthesisRiskEstimate.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRRiskEvidenceSynthesis\\\\FHIRRiskEvidenceSynthesisSampleSize\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRRiskEvidenceSynthesis/FHIRRiskEvidenceSynthesisSampleSize.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSearchParameter\\\\FHIRSearchParameterComponent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSearchParameter/FHIRSearchParameterComponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSpecimen\\\\FHIRSpecimenCollection\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSpecimen/FHIRSpecimenCollection.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSpecimen\\\\FHIRSpecimenContainer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSpecimen/FHIRSpecimenContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSpecimen\\\\FHIRSpecimenProcessing\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSpecimen/FHIRSpecimenProcessing.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSpecimenDefinition\\\\FHIRSpecimenDefinitionAdditive\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSpecimenDefinition/FHIRSpecimenDefinitionAdditive.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSpecimenDefinition\\\\FHIRSpecimenDefinitionContainer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSpecimenDefinition/FHIRSpecimenDefinitionContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSpecimenDefinition\\\\FHIRSpecimenDefinitionHandling\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSpecimenDefinition/FHIRSpecimenDefinitionHandling.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSpecimenDefinition\\\\FHIRSpecimenDefinitionTypeTested\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSpecimenDefinition/FHIRSpecimenDefinitionTypeTested.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureDefinition\\\\FHIRStructureDefinitionContext\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureDefinition/FHIRStructureDefinitionContext.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureDefinition\\\\FHIRStructureDefinitionDifferential\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureDefinition/FHIRStructureDefinitionDifferential.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureDefinition\\\\FHIRStructureDefinitionMapping\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureDefinition/FHIRStructureDefinitionMapping.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureDefinition\\\\FHIRStructureDefinitionSnapshot\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureDefinition/FHIRStructureDefinitionSnapshot.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureMap\\\\FHIRStructureMapDependent\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureMap/FHIRStructureMapDependent.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureMap\\\\FHIRStructureMapGroup\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureMap/FHIRStructureMapGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureMap\\\\FHIRStructureMapInput\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureMap/FHIRStructureMapInput.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureMap\\\\FHIRStructureMapParameter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureMap/FHIRStructureMapParameter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureMap\\\\FHIRStructureMapRule\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureMap/FHIRStructureMapRule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureMap\\\\FHIRStructureMapSource\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureMap/FHIRStructureMapSource.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureMap\\\\FHIRStructureMapStructure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureMap/FHIRStructureMapStructure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRStructureMap\\\\FHIRStructureMapTarget\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRStructureMap/FHIRStructureMapTarget.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubscription\\\\FHIRSubscriptionChannel\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubscription/FHIRSubscriptionChannel.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstance\\\\FHIRSubstanceIngredient\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstance/FHIRSubstanceIngredient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstance\\\\FHIRSubstanceInstance\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstance/FHIRSubstanceInstance.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceAmount\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceAmount.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceAmount\\\\FHIRSubstanceAmountReferenceRange\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceAmount/FHIRSubstanceAmountReferenceRange.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceNucleicAcid\\\\FHIRSubstanceNucleicAcidLinkage\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceNucleicAcid/FHIRSubstanceNucleicAcidLinkage.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceNucleicAcid\\\\FHIRSubstanceNucleicAcidSubunit\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceNucleicAcid/FHIRSubstanceNucleicAcidSubunit.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceNucleicAcid\\\\FHIRSubstanceNucleicAcidSugar\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceNucleicAcid/FHIRSubstanceNucleicAcidSugar.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstancePolymer\\\\FHIRSubstancePolymerDegreeOfPolymerisation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstancePolymer/FHIRSubstancePolymerDegreeOfPolymerisation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstancePolymer\\\\FHIRSubstancePolymerMonomerSet\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstancePolymer/FHIRSubstancePolymerMonomerSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstancePolymer\\\\FHIRSubstancePolymerRepeat\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstancePolymer/FHIRSubstancePolymerRepeat.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstancePolymer\\\\FHIRSubstancePolymerRepeatUnit\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstancePolymer/FHIRSubstancePolymerRepeatUnit.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstancePolymer\\\\FHIRSubstancePolymerStartingMaterial\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstancePolymer/FHIRSubstancePolymerStartingMaterial.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstancePolymer\\\\FHIRSubstancePolymerStructuralRepresentation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstancePolymer/FHIRSubstancePolymerStructuralRepresentation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceProtein\\\\FHIRSubstanceProteinSubunit\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceProtein/FHIRSubstanceProteinSubunit.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceReferenceInformation\\\\FHIRSubstanceReferenceInformationClassification\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceReferenceInformation/FHIRSubstanceReferenceInformationClassification.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceReferenceInformation\\\\FHIRSubstanceReferenceInformationGene\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceReferenceInformation/FHIRSubstanceReferenceInformationGene.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceReferenceInformation\\\\FHIRSubstanceReferenceInformationGeneElement\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceReferenceInformation/FHIRSubstanceReferenceInformationGeneElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceReferenceInformation\\\\FHIRSubstanceReferenceInformationTarget\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceReferenceInformation/FHIRSubstanceReferenceInformationTarget.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSourceMaterial\\\\FHIRSubstanceSourceMaterialAuthor\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSourceMaterial/FHIRSubstanceSourceMaterialAuthor.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSourceMaterial\\\\FHIRSubstanceSourceMaterialFractionDescription\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSourceMaterial/FHIRSubstanceSourceMaterialFractionDescription.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSourceMaterial\\\\FHIRSubstanceSourceMaterialHybrid\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSourceMaterial/FHIRSubstanceSourceMaterialHybrid.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSourceMaterial\\\\FHIRSubstanceSourceMaterialOrganism\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSourceMaterial/FHIRSubstanceSourceMaterialOrganism.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSourceMaterial\\\\FHIRSubstanceSourceMaterialOrganismGeneral\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSourceMaterial/FHIRSubstanceSourceMaterialOrganismGeneral.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSourceMaterial\\\\FHIRSubstanceSourceMaterialPartDescription\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSourceMaterial/FHIRSubstanceSourceMaterialPartDescription.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationCode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationIsotope\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationIsotope.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationMoiety\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationMoiety.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationMolecularWeight\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationMolecularWeight.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationName\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationName.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationOfficial\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationOfficial.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationProperty\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationProperty.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationRelationship\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationRelationship.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationRepresentation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationRepresentation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSubstanceSpecification\\\\FHIRSubstanceSpecificationStructure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSubstanceSpecification/FHIRSubstanceSpecificationStructure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSupplyDelivery\\\\FHIRSupplyDeliverySuppliedItem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSupplyDelivery/FHIRSupplyDeliverySuppliedItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRSupplyRequest\\\\FHIRSupplyRequestParameter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRSupplyRequest/FHIRSupplyRequestParameter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTask\\\\FHIRTaskInput\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTask/FHIRTaskInput.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTask\\\\FHIRTaskOutput\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTask/FHIRTaskOutput.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTask\\\\FHIRTaskRestriction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTask/FHIRTaskRestriction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesClosure\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesClosure.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesCodeSystem\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesCodeSystem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesExpansion\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesExpansion.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesFilter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesFilter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesImplementation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesImplementation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesParameter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesParameter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesSoftware\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesSoftware.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesTranslation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesTranslation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesValidateCode\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesValidateCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTerminologyCapabilities\\\\FHIRTerminologyCapabilitiesVersion\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTerminologyCapabilities/FHIRTerminologyCapabilitiesVersion.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestReport\\\\FHIRTestReportAction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestReport/FHIRTestReportAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestReport\\\\FHIRTestReportAction1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestReport/FHIRTestReportAction1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestReport\\\\FHIRTestReportAction2\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestReport/FHIRTestReportAction2.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestReport\\\\FHIRTestReportAssert\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestReport/FHIRTestReportAssert.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestReport\\\\FHIRTestReportOperation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestReport/FHIRTestReportOperation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestReport\\\\FHIRTestReportParticipant\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestReport/FHIRTestReportParticipant.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestReport\\\\FHIRTestReportSetup\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestReport/FHIRTestReportSetup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestReport\\\\FHIRTestReportTeardown\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestReport/FHIRTestReportTeardown.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestReport\\\\FHIRTestReportTest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestReport/FHIRTestReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptAction\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptAction1\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptAction1.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptAction2\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptAction2.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptAssert\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptAssert.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptCapability\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptCapability.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptDestination\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptDestination.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptFixture\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptFixture.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptLink\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptLink.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptMetadata\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptMetadata.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptOperation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptOperation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptOrigin\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptOrigin.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptRequestHeader\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptRequestHeader.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptSetup\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptSetup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptTeardown\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptTeardown.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptTest\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTestScript\\\\FHIRTestScriptVariable\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTestScript/FHIRTestScriptVariable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTiming\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTiming.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTiming\\\\FHIRTimingRepeat\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRTiming/FHIRTimingRepeat.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRValueSet\\\\FHIRValueSetCompose\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRValueSet/FHIRValueSetCompose.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRValueSet\\\\FHIRValueSetConcept\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRValueSet/FHIRValueSetConcept.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRValueSet\\\\FHIRValueSetContains\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRValueSet/FHIRValueSetContains.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRValueSet\\\\FHIRValueSetDesignation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRValueSet/FHIRValueSetDesignation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRValueSet\\\\FHIRValueSetExpansion\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRValueSet/FHIRValueSetExpansion.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRValueSet\\\\FHIRValueSetFilter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRValueSet/FHIRValueSetFilter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRValueSet\\\\FHIRValueSetInclude\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRValueSet/FHIRValueSetInclude.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRValueSet\\\\FHIRValueSetParameter\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRValueSet/FHIRValueSetParameter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRVerificationResult\\\\FHIRVerificationResultAttestation\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRVerificationResult/FHIRVerificationResultAttestation.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRVerificationResult\\\\FHIRVerificationResultPrimarySource\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRVerificationResult/FHIRVerificationResultPrimarySource.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRVerificationResult\\\\FHIRVerificationResultValidator\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRVerificationResult/FHIRVerificationResultValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRVisionPrescription\\\\FHIRVisionPrescriptionLensSpecification\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRVisionPrescription/FHIRVisionPrescriptionLensSpecification.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRVisionPrescription\\\\FHIRVisionPrescriptionPrism\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRVisionPrescription/FHIRVisionPrescriptionPrism.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:xmlSerialize\\(\\) should return SimpleXMLElement\\|string but returns string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\PHPFHIRParserMap\\:\\:key\\(\\) should return string but returns int\\|string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/PHPFHIRParserMap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\PHPFHIRResponseParser\\:\\:_parseJson\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/PHPFHIRResponseParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\PHPFHIRResponseParser\\:\\:_parseXML\\(\\) should return object but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/PHPFHIRResponseParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\R4\\\\PHPFHIRResponseParser\\:\\:_tryGetMapEntry\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/PHPFHIRResponseParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\SMART\\\\ClientAdminController\\:\\:dispatch\\(\\) should return Symfony\\\\Component\\\\HttpFoundation\\\\Response but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\SMART\\\\ClientAdminController\\:\\:getCSRFToken\\(\\) should return string\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\SMART\\\\ClientAdminController\\:\\:getDatabaseRecordForToken\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\SMART\\\\ClientAdminController\\:\\:getRefreshTokensForClientUser\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\FHIR\\\\SMART\\\\ClientAdminController\\:\\:parseTokenIntoParts\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
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
    'count' => 5,
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
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getAcl\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getAttributes\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getChildren\\(\\) should return OpenEMR\\\\Menu\\\\MenuItems but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getDisplayText\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getGlobalReqStrict\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getGlobalReq\\(\\) should return array\\|string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getId\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getLinkClassList\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getLinkContainerClassList\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getPostTextContent\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getPreTextContent\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getRequirements\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getTarget\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/BaseMenuItem.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Menu\\\\BaseMenuItem\\:\\:getUrl\\(\\) should return string but returns mixed\\.$#',
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
    'message' => '#^Method OpenEMR\\\\PaymentProcessing\\\\Rainforest\\\\Webhooks\\\\Webhook\\:\\:getMerchantId\\(\\) should return string\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Rainforest/Webhooks/Webhook.php',
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
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\a but returns array\\<array\\>\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:getOne\\(\\) should return OpenEMR\\\\RestControllers\\\\a but returns array\\<array\\>\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:post\\(\\) should return OpenEMR\\\\RestControllers\\\\a but returns array\\<array\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:put\\(\\) should return OpenEMR\\\\RestControllers\\\\a but returns array\\<array\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
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
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirGoalRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGoalRestController.php',
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
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirSpecimenRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirSpecimenRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirValueSetRestController\\:\\:getAll\\(\\) should return OpenEMR\\\\RestControllers\\\\FHIR\\\\FHIR but returns Symfony\\\\Component\\\\HttpFoundation\\\\Response\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirValueSetRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FHIR\\\\Finder\\\\FhirRouteFinder\\:\\:find\\(\\) should return array but returns mixed\\.$#',
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
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FacilityRestController\\:\\:getAll\\(\\) should return Nyholm\\\\Psr7\\\\Response but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FacilityRestController\\:\\:getOne\\(\\) should return Nyholm\\\\Psr7\\\\Response but returns mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FacilityRestController\\:\\:patch\\(\\) should return Nyholm\\\\Psr7\\\\Response but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\FacilityRestController\\:\\:post\\(\\) should return Nyholm\\\\Psr7\\\\Response but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\Finder\\\\PortalRouteFinder\\:\\:find\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Finder/PortalRouteFinder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\Finder\\\\StandardRouteFinder\\:\\:find\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Finder/StandardRouteFinder.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\PatientRestController\\:\\:post\\(\\) should return Psr\\\\Http\\\\Message\\\\ResponseInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PatientRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\PatientRestController\\:\\:put\\(\\) should return Psr\\\\Http\\\\Message\\\\ResponseInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PatientRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\PractitionerRestController\\:\\:patch\\(\\) should return OpenEMR\\\\RestControllers\\\\a but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\PractitionerRestController\\:\\:post\\(\\) should return OpenEMR\\\\RestControllers\\\\a but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
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
    'message' => '#^Method OpenEMR\\\\Services\\\\Globals\\\\UserSettingsService\\:\\:collectAndOrganizeExpandSetting\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Globals\\\\UserSettingsService\\:\\:getUserSetting\\(\\) should return OpenEMR\\\\Services\\\\Globals\\\\Effective but returns mixed\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:getChartTrackerInformationActivity\\(\\) should return OpenEMR\\\\Services\\\\recordset but returns ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientService\\:\\:getChartTrackerInformation\\(\\) should return OpenEMR\\\\Services\\\\recordset but returns ADORecordSet\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Services\\\\PrescriptionService\\:\\:getAll\\(\\) should return OpenEMR\\\\Validators\\\\ProcessingResult but returns array\\|float\\|int\\|string\\|false\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PrescriptionService.php',
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
    'message' => '#^Method OpenEMR\\\\Services\\\\Qdm\\\\MeasureService\\:\\:getCurrentReportingYear\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/MeasureService.php',
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
    'message' => '#^Method OpenEMR\\\\Services\\\\Search\\\\ServiceField\\:\\:getType\\(\\) should return string but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/ServiceField.php',
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
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getUserByUUID\\(\\) should return array\\{id\\: int, uuid\\: string\\|null, username\\: string\\|null, password\\: string\\|null, authorized\\: int\\|null, info\\: string\\|null, source\\: int\\|null, fname\\: string\\|null, \\.\\.\\.\\}\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getUserByUsername\\(\\) should return array\\{id\\: int, uuid\\: string\\|null, username\\: string\\|null, password\\: string\\|null, authorized\\: int\\|null, info\\: string\\|null, source\\: int\\|null, fname\\: string\\|null, \\.\\.\\.\\}\\|false but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getUserForCalendar\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\UserService\\:\\:getUser\\(\\) should return array\\{id\\: int, uuid\\: string\\|null, username\\: string\\|null, password\\: string\\|null, authorized\\: int\\|null, info\\: string\\|null, source\\: int\\|null, fname\\: string\\|null, \\.\\.\\.\\}\\|false but returns mixed\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Services\\\\VersionService\\:\\:fetch\\(\\) should return array but returns array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VersionService.php',
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
    'message' => '#^Method OpenEMR\\\\Validators\\\\BaseValidator\\:\\:getInnerValidator\\(\\) should return Particle\\\\Validator\\\\Validator but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Validators\\\\BaseValidator\\:\\:isValidContext\\(\\) should return true but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Validators\\\\BaseValidator\\:\\:validateId\\(\\) should return true but returns OpenEMR\\\\Validators\\\\ProcessingResult\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Validators\\\\ProcessingResult\\:\\:extractDataArray\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/ProcessingResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Validators\\\\ProcessingResult\\:\\:hasErrors\\(\\) should return true but returns bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/ProcessingResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Api\\\\ApiTestClient\\:\\:post\\(\\) should return Psr\\\\Http\\\\Message\\\\ResponseInterface but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Api\\\\ApiTestClient\\:\\:setAuthToken\\(\\) should return OpenEMR\\\\Tests\\\\Api\\\\the but returns Psr\\\\Http\\\\Message\\\\ResponseInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Api\\\\BulkAPITestClient\\:\\:setAuthToken\\(\\) should return OpenEMR\\\\Tests\\\\Api\\\\the but returns Psr\\\\Http\\\\Message\\\\ResponseInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/BulkAPITestClient.php',
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
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\EmailSendTest\\:\\:getLatestEmailForRecipient\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EmailSendTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\EmailSendTest\\:\\:getMailpitMessageCount\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EmailSendTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\EmailSendTest\\:\\:getMailpitMessage\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EmailSendTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\EmailSendTest\\:\\:getMailpitMessages\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EmailSendTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\EmailSendTest\\:\\:searchMailpitMessages\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EmailSendTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\EmailSendTest\\:\\:waitForEmail\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EmailSendTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\FaxSmsEmailTest\\:\\:getLatestEmailForRecipient\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FaxSmsEmailTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\FaxSmsEmailTest\\:\\:getMailpitMessageCount\\(\\) should return int but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FaxSmsEmailTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\FaxSmsEmailTest\\:\\:getMailpitMessage\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FaxSmsEmailTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\FaxSmsEmailTest\\:\\:getMailpitMessages\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FaxSmsEmailTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\FaxSmsEmailTest\\:\\:searchMailpitMessages\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FaxSmsEmailTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\E2e\\\\FaxSmsEmailTest\\:\\:waitForEmail\\(\\) should return array\\|null but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FaxSmsEmailTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getNextId\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\the but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getSingleEntry\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\random but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getSingleFixture\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\a but returns OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:loadJsonFile\\(\\) should return array but returns mixed\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FacilityFixtureManager\\:\\:getFacilityFixtures\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FacilityFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FacilityFixtureManager\\:\\:getFhirFacilityFixtures\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FacilityFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FacilityFixtureManager\\:\\:getSingleFacilityFixture\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\a but returns OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FacilityFixtureManager.php',
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
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getPatientFixtures\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getSingleEntry\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\random but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getSinglePatientFixture\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\a but returns OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:loadJsonFile\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\GaclFixtureManager\\:\\:getSingleFixture\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/GaclFixtureManager.php',
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
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getFhirPractitionerFixtures\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getNextId\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\the but returns int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getPractitionerFixtures\\(\\) should return array but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getSingleEntry\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\random but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getSinglePractitionerFixture\\(\\) should return OpenEMR\\\\Tests\\\\Fixtures\\\\a but returns OpenEMR\\\\Tests\\\\Fixtures\\\\random\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:loadJsonFile\\(\\) should return array but returns mixed\\.$#',
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
    'message' => '#^Method OpenEMR\\\\Tests\\\\Isolated\\\\Validators\\\\BaseValidatorTestStub\\:\\:getInnerValidator\\(\\) should return Particle\\\\Validator\\\\Validator but returns mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/BaseValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Isolated\\\\Validators\\\\BaseValidatorTestStub\\:\\:validateId\\(\\) should return true but returns OpenEMR\\\\Validators\\\\ProcessingResult\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/BaseValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Isolated\\\\Validators\\\\CoverageValidatorStub\\:\\:validateId\\(\\) should return true but returns OpenEMR\\\\Validators\\\\ProcessingResult\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
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
