<?php declare(strict_types = 1);

// total 334 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlClose\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/de_identification_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/chart_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getEmployerData\\(\\)\\:
use EmployerService\\-\\>getMostRecentEmployerData\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function get_magic_quotes_runtime\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlClose\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function get_db\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collectAndOrganizeExpandSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:collectAndOrganizeExpandSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function prevSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:prevSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_invoice.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlQ\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function prevSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:prevSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/sl_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sellDrug\\(\\)\\:
Use DrugSalesService\\:\\:sellDrug instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/easipro/pro.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/ajax_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function isOption\\(\\)\\:
use LayoutsUtils\\:\\:isOption$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function updateEmployerData\\(\\)\\:
Use EmployerService\\-\\>updateEmployerData\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function isOption\\(\\)\\:
use LayoutsUtils\\:\\:isOption$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function isOption\\(\\)\\:
use LayoutsUtils\\:\\:isOption$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/aftercare_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ankleinjury/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/bronchitis/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/care_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_instructions/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/dictation/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function prevSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:prevSetting$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function prevSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:prevSetting$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/functional_cognitive_status/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/note/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/phq9/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/C_FormPriorAuth.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function checkUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:checkUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/reviewofs/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ros/C_FormROS.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/sdoh/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/soap/C_FormSOAP.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/track_anything/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/transfer_summary/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/logview/logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/dated_reminders/dated_reminders_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function prevSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:prevSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function prevSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:prevSetting$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function updateEmployerData\\(\\)\\:
Use EmployerService\\-\\>updateEmployerData\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function updateEmployerData\\(\\)\\:
Use EmployerService\\-\\>updateEmployerData\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function updateEmployerData\\(\\)\\:
Use EmployerService\\-\\>updateEmployerData\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlQ\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getFormByEncounter\\(\\)\\:
Use FormService\\:\\:getFormByEncounter\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getFormByEncounter\\(\\)\\:
Use FormService\\:\\:getFormByEncounter\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserIDInfo\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserIDInfo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getEmployerData\\(\\)\\:
use EmployerService\\-\\>getMostRecentEmployerData\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getEmployerData\\(\\)\\:
use EmployerService\\-\\>getMostRecentEmployerData\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getEmployerData\\(\\)\\:
use EmployerService\\-\\>getMostRecentEmployerData\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getEmployerData\\(\\)\\:
use EmployerService\\-\\>getMostRecentEmployerData\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function updateEmployerData\\(\\)\\:
Use EmployerService\\-\\>updateEmployerData\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatTime\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function prevSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:prevSetting$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/amc_tracking.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/audit_log_tamper_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/chart_location_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/charts_checked_out.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/clinical_reports.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/reports/clinical_reports.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/cqm.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/direct_message_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getFormByEncounter\\(\\)\\:
Use FormService\\:\\:getFormByEncounter\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/encounters_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/ip_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatTime\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/patient_flow_board_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/reports/patient_list_creation.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/reports/payment_processing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function collect_codetypes\\(\\)\\:
use CodeTypesService\\:\\:collectCodeTypes\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/report_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function removeUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:removeUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function setUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:setUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserIDInfo\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserIDInfo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_registrations.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserIDInfo\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserIDInfo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_totp.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserIDInfo\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserIDInfo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_u2f.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserIDInfo\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserIDInfo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/user_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/FeeSheet.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sellDrug\\(\\)\\:
Use DrugSalesService\\:\\:sellDrug instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/FeeSheet.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sellDrug\\(\\)\\:
Use DrugSalesService\\:\\:sellDrug instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/FeeSheetHtml.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function checkUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:checkUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/adminacl_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sellDrug\\(\\)\\:
Use DrugSalesService\\:\\:sellDrug instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/code_attributes_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function setUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:setUserSetting$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/user_settings.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlQ\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Company.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlQ\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlQ\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/PhoneNumber.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function get_db\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlQ\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlQ\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Provider.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function checkUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:checkUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/custom_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function isOption\\(\\)\\:
use LayoutsUtils\\:\\:isOption$#',
    'count' => 33,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlClose\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlClose\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_email_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlClose\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserIDInfo\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserIDInfo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_patient_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserIDInfo\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserIDInfo$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/messaging/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getEmployerData\\(\\)\\:
use EmployerService\\-\\>getMostRecentEmployerData\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerLog.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function checkUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:checkUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function sqlQ\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Address.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function get_db\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ORDataObject.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Twig/TwigExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/SmartLaunchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Patient/Cards/BillingViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareExperiencePreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareTeamViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Patient/Cards/DemographicsViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/PortalCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Patient/Cards/TreatmentPreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function getUserSetting\\(\\)\\:
7\\.0\\.3 see UserSettingsService\\:\\:getUserSetting$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function addForm\\(\\)\\:
Use FormService\\:\\:addForm\\(\\) instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated function oeFormatDateTime\\(\\)\\:
use DateFormatterUtils\\:\\:oeFormatDateTime\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/log/view.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
