<?php
/**
 *
 * json of menu structure (use this when not in the database)
 *
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */

$menu_json='[
  {"label":"Calendar","menu_id":"cal0","target":"lst","url":"/interface/main/main_info.php","children":[],"requirement":0},
  {"label":"Flow Board","menu_id":"pfb0","target":"lst","url":"/interface/patient_tracker/patient_tracker.php?skip_timeout_reset=1","children":[],"requirement":0},
  {"label":"Messages ","menu_id":"msg0","target":"pat","url":"/interface/main/messages/messages.php?form_active=1","children":[],"requirement":0},
  {"label":"Patient/Client","menu_id":"patimg","children":[
    {"label":"Patients","menu_id":"fin0","target":"lst","url":"/interface/main/finder/dynamic_finder.php","children":[],"requirement":0},
    {"label":"New/Search","menu_id":"new0","target":"pat","url":"/interface/new/new.php","children":[],"requirement":0},
    {"label":"Summary","menu_id":"dem1","target":"pat","url":"/interface/patient_file/summary/demographics.php","children":[],"requirement":1},
    {"label":"Visits","children":[
      {"label":"Create Visit","menu_id":"nen1","target":"enc","url":"/interface/forms/newpatient/new.php?autoloaded=1&calenc=","children":[],"requirement":1},
      {"label":"Current","menu_id":"enc2","target":"enc","url":"/interface/patient_file/encounter/encounter_top.php","children":[],"requirement":3},
      {"label":"Visit History","menu_id":"ens1","target":"enc","url":"/interface/patient_file/history/encounters.php","children":[],"requirement":1}],"requirement":0},
    {"label":"Records","children":[
    {"label":"Patient Record Request","menu_id":"prq1","target":"enc","url":"/interface/patient_file/transaction/record_request.php","children":[],"requirement":1}],"requirement":0},
    {"label":"Visit Forms","children":[],"requirement":0},
    {"label":"Import","children":[
      {"label":"Upload","menu_id":"ccr0","target":"pat","url":"/interface/patient_file/ccr_import.php","children":[],"requirement":0},
      {"label":"Pending Approval","menu_id":"apr0","target":"pat","url":"/interface/patient_file/ccr_pending_approval.php","children":[],"requirement":0}],"requirement":0}],"requirement":0},
  {"label":"Fees","menu_id":"feeimg","children":[
    {"label":"Fee Sheet","menu_id":"cod2","target":"enc","url":"/interface/patient_file/encounter/load_form.php?formname=fee_sheet","children":[],"requirement":2},
    {"label":"Payment","menu_id":"pay1","target":"enc","url":"/interface/patient_file/front_payment.php","children":[],"requirement":1},
    {"label":"Checkout","menu_id":"bil1","target":"enc","url":"/interface/patient_file/pos_checkout.php?framed=1","children":[],"requirement":1},
    {"label":"Billing","menu_id":"bil0","target":"pat","url":"/interface/billing/billing_report.php","children":[],"requirement":0},
    {"label":"Batch Payments","menu_id":"npa0","target":"pat","url":"/interface/billing/new_payment.php","children":[],"requirement":0},
    {"label":"Posting","menu_id":"eob","target":"pat","url":"/interface/billing/sl_eob_search.php","children":[],"requirement":0,"acl_req":["acct","eob"]},
    {"label":"EDI History","menu_id":"edi0","target":"pat","url":"/interface/billing/edih_view.php","children":[],"requirement":0,"acl_req":["acct","eob"],"global_req":"enable_edihistory_in_left_menu"}],"requirement":0},
  {"label":"Modules","menu_id":"modimg","children":[
    {"label":"Manage Modules","menu_id":"adm0","target":"pat","url":"/interface/modules/zend_modules/public/Installer","children":[],"requirement":0}],"requirement":0},
  {"label":"Procedures","menu_id":"proimg","children":[
    {"label":"Providers","menu_id":"orl0","target":"pat","url":"/interface/orders/procedure_provider_list.php","children":[],"requirement":0},
    {"label":"Configuration","menu_id":"ort0","target":"pat","url":"/interface/orders/types.php","children":[],"requirement":0},
    {"label":"Load Compendium","menu_id":"orc0","target":"pat","url":"/interface/orders/load_compendium.php","children":[],"requirement":0},
    {"label":"Pending Review","menu_id":"orp1","target":"enc","url":"/interface/orders/orders_results.php?review=1","children":[],"requirement":1},
    {"label":"Patient Results","menu_id":"orr1","target":"enc","url":"/interface/orders/orders_results.php","children":[],"requirement":1},
    {"label":"Lab Overview","menu_id":"lda1","target":"enc","url":"/interface/patient_file/summary/labdata.php","children":[],"requirement":1},
    {"label":"Batch Results","menu_id":"orb0","target":"pat","url":"/interface/orders/orders_results.php?batch=1","children":[],"requirement":0},
    {"label":"Electronic Reports","menu_id":"ore0","target":"pat","url":"/interface/orders/list_reports.php","children":[],"requirement":0},
    {"label":"Lab Documents","menu_id":"dld0","target":"pat","url":"/interface/main/display_documents.php","children":[],"requirement":0}],"requirement":0},
  {"label":"Administration","menu_id":"admimg","children":[
    {"label":"Globals","menu_id":"adm0","target":"adm","url":"/interface/super/edit_globals.php","children":[],"requirement":0,"acl_req":["admin","super"]},
    {"label":"Facilities","menu_id":"adm0","target":"adm","url":"/interface/usergroup/facilities.php","children":[],"requirement":0,"acl_req":["admin","users"]},
    {"label":"Users","menu_id":"adm0","target":"adm","url":"/interface/usergroup/usergroup_admin.php","children":[],"requirement":0,"acl_req":["admin","users"]},
    {"label":"Addr Book","menu_id":"adb0","target":"adm","url":"/interface/usergroup/addrbook_list.php","children":[],"requirement":0,"acl_req":["admin","practice"]},
    {"label":"Practice","menu_id":"adm0","target":"adm","url":"/controller.php?practice_settings&pharmacy&action=list","children":[],"requirement":0,"acl_req":["admin","practice"]},
    {"label":"Codes","menu_id":"sup0","target":"adm","url":"/interface/patient_file/encounter/superbill_custom_full.php","children":[],"requirement":0,"acl_req":["admin","superbill"]},
    {"label":"Layouts","menu_id":"adm0","target":"adm","url":"/interface/super/edit_layout.php","children":[],"requirement":0,"acl_req":["admin","super"]},
    {"label":"Lists","menu_id":"adm0","target":"adm","url":"/interface/super/edit_list.php","children":[],"requirement":0,"acl_req":["admin","super"]},
    {"label":"ACL","menu_id":"adm0","target":"adm","url":"/interface/usergroup/adminacl.php","children":[],"requirement":0,"acl_req":["admin","acl"]},
    {"label":"Files","menu_id":"adm0","target":"adm","url":"/interface/super/manage_site_files.php","children":[],"requirement":0,"acl_req":["admin","super"]},
    {"label":"Backup","menu_id":"adm0","target":"adm","url":"/interface/main/backup.php","children":[],"requirement":0,"acl_req":["admin","super"]},
    {"label":"Rules","menu_id":"adm0","target":"adm","url":"/interface/super/rules/index.php?action=browse!list","children":[],"requirement":0,"acl_req":["admin","super"],"global_req":"enable_cdr"},
    {"label":"Alerts","menu_id":"adm0","target":"adm","url":"/interface/super/rules/index.php?action=alerts!listactmgr","children":[],"requirement":0,"acl_req":["admin","super"],"global_req":"enable_cdr"},
    {"label":"Patient Reminders","menu_id":"adm0","target":"adm","url":"/interface/patient_file/reminder/patient_reminders.php?mode=admin&patient_id=","children":[],"requirement":0,"acl_req":["admin","super"],"global_req":"enable_cdr"},
    {"label":"Other","children":[
      {"label":"Language","menu_id":"adm0","target":"adm","url":"/interface/language/language.php","children":[],"requirement":0,"acl_req":["admin","language"]},
      {"label":"Forms","menu_id":"adm0","target":"adm","url":"/interface/forms_admin/forms_admin.php","children":[],"requirement":0,"acl_req":["admin","forms"]},
      {"label":"Calendar","menu_id":"adm0","target":"lst","url":"/interface/main/calendar/index.php?module=PostCalendar&type=admin&func=modifyconfig","children":[],"requirement":0,"acl_req":["admin","calendar"]},
      {"label":"Logs","menu_id":"adm0","target":"adm","url":"/interface/logview/logview.php","children":[],"requirement":0,"acl_req":["admin","users"]},
      {"label":"Database","menu_id":"adm0","target":"adm","url":"/phpmyadmin/index.php","children":[],"requirement":0,"acl_req":["admin","database"],"global_req":"!disable_phpmyadmin_link"},
      {"label":"Certificates","menu_id":"adm0","target":"adm","url":"/interface/usergroup/ssl_certificates_admin.php","children":[],"requirement":0,"acl_req":["admin","users"]},
      {"label":"Native Data Loads","menu_id":"adm0","target":"adm","url":"/interface/super/load_codes.php","children":[],"requirement":0,"acl_req":["admin","super"]},
      {"label":"External Data Loads","menu_id":"adm0","target":"adm","url":"/interface/code_systems/dataloads_ajax.php","children":[],"requirement":0,"acl_req":["admin","super"]},
      {"label":"Merge Patients","menu_id":"adm0","target":"adm","url":"/interface/patient_file/merge_patients.php","children":[],"requirement":0,"acl_req":["admin","super"]},
      {"label":"Import Holidays","menu_id":"adm0","target":"adm","url":"/interface/main/holidays/import_holidays.php","children":[],"requirement":0,"acl_req":["admin","super"]}],"requirement":0}],"requirement":0},
  {"label":"Reports","menu_id":"repimg","children":[
    {"label":"Clients","children":[
      {"label":"List","menu_id":"rep0","target":"rep","url":"/interface/reports/patient_list.php","children":[],"requirement":0},
      {"label":"Rx","menu_id":"rep0","target":"rep","url":"/interface/reports/prescriptions_report.php","children":[],"requirement":0},
      {"label":"Patient List Creation","menu_id":"rep0","target":"rep","url":"/interface/reports/patient_list_creation.php","children":[],"requirement":0},
      {"label":"Clinical","menu_id":"rep0","target":"rep","url":"/interface/reports/clinical_reports.php","children":[],"requirement":0},
      {"label":"Referrals","menu_id":"rep0","target":"rep","url":"/interface/reports/referrals_report.php","children":[],"requirement":0},
      {"label":"Immunization Registry","menu_id":"rep0","target":"rep","url":"/interface/reports/immunization_report.php","children":[],"requirement":0}],"requirement":0},
    {"label":"Clinic","children":[
      {"label":"Report Results","menu_id":"rep0","target":"rep","url":"/interface/reports/report_results.php","children":[],"requirement":0},
      {"label":"Standard Measures","menu_id":"rep0","target":"rep","url":"/interface/reports/cqm.php?type=standard","children":[],"requirement":0},
      {"label":"Quality Measures (CQM)","menu_id":"rep0","target":"rep","url":"/interface/reports/cqm.php?type=cqm","children":[],"requirement":0},
      {"label":"Automated Measures (AMC)","menu_id":"rep0","target":"rep","url":"/interface/reports/cqm.php?type=amc","children":[],"requirement":0},
      {"label":"AMC Tracking","menu_id":"rep0","target":"rep","url":"/interface/reports/amc_tracking.php","children":[],"requirement":0},
      {"label":"Alerts Log","menu_id":"rep0","target":"rep","url":"/interface/reports/cdr_log.php","children":[],"requirement":0}],"requirement":0},
    {"label":"Visits","children":[
      {"label":"Daily Summary","menu_id":"rep0","target":"rep","url":"/interface/reports/daily_summary_report.php","children":[],"requirement":0},
      {"label":"Appointments","menu_id":"rep0","target":"rep","url":"/interface/reports/appointments_report.php","children":[],"requirement":0},
      {"label":"Patient Flow Board","menu_id":"rep0","target":"rep","url":"/interface/reports/patient_flow_board_report.php","children":[],"requirement":0},
      {"label":"Encounters","menu_id":"rep0","target":"rep","url":"/interface/reports/encounters_report.php","children":[],"requirement":0},
      {"label":"Appt-Enc","menu_id":"rep0","target":"rep","url":"/interface/reports/appt_encounter_report.php","children":[],"requirement":0},
      {"label":"Superbill","menu_id":"rep0","target":"rep","url":"/interface/reports/custom_report_range.php","children":[],"requirement":0},
      {"label":"Eligibility","menu_id":"rep0","target":"rep","url":"/interface/reports/edi_270.php","children":[],"requirement":0},
      {"label":"Eligibility Response","menu_id":"rep0","target":"rep","url":"/interface/reports/edi_271.php","children":[],"requirement":0},
      {"label":"Chart Activity","menu_id":"rep0","target":"rep","url":"/interface/reports/chart_location_activity.php","children":[],"requirement":0},
      {"label":"Charts Out","menu_id":"rep0","target":"rep","url":"/interface/reports/charts_checked_out.php","children":[],"requirement":0},
      {"label":"Services","menu_id":"rep0","target":"rep","url":"/interface/reports/services_by_category.php","children":[],"requirement":0},
      {"label":"Syndromic Surveillance","menu_id":"rep0","target":"rep","url":"/interface/reports/non_reported.php","children":[],"requirement":0}],"requirement":0},
    {"label":"Financial","children":[
      {"label":"Sales","menu_id":"rep0","target":"rep","url":"/interface/reports/sales_by_item.php","children":[],"requirement":0},
      {"label":"Cash Rec","menu_id":"rep0","target":"rep","url":"/interface/billing/sl_receipts_report.php","children":[],"requirement":0},
      {"label":"Front Rec","menu_id":"rep0","target":"rep","url":"/interface/reports/front_receipts_report.php","children":[],"requirement":0},
      {"label":"Pmt Method","menu_id":"rep0","target":"rep","url":"/interface/reports/receipts_by_method_report.php","children":[],"requirement":0},
      {"label":"Collections","menu_id":"rep0","target":"rep","url":"/interface/reports/collections_report.php","children":[],"requirement":0},
      {"label":"Pat Ledger","menu_id":"rep0","target":"rep","url":"/interface/reports/pat_ledger.php?form=0","children":[],"requirement":0},
      {"label":"Financial Summary by Service Code","menu_id":"rep0","target":"rep","url":"/interface/reports/svc_code_financial_report.php","children":[],"requirement":0}],"requirement":0},
    {"label":"Procedures","children":[
      {"label":"Pending Res","url":"/interface/orders/pending_orders.php","target":"rep","children":[],"requirement":0},
      {"label":"Statistics","url":"/interface/orders/procedure_stats.php","target":"rep","children":[],"requirement":0}],"requirement":0},
    {"label":"Insurance","children":[
      {"label":"Distribution","menu_id":"rep0","target":"rep","url":"/interface/reports/insurance_allocation_report.php","children":[],"requirement":0},
      {"label":"Indigents","menu_id":"rep0","target":"rep","url":"/interface/billing/indigent_patients_report.php","children":[],"requirement":0},
      {"label":"Unique SP","menu_id":"rep0","target":"rep","url":"/interface/reports/unique_seen_patients_report.php","children":[],"requirement":0}],"requirement":0},
    {"label":"Blank Forms","children":[
      {"label":"Demographics","url":"/interface/patient_file/summary/demographics_print.php","target":"rep","children":[],"requirement":0},
      {"label":"Superbill/Fee Sheet","url":"/interface/patient_file/printed_fee_sheet.php","target":"rep","children":[],"requirement":0},
      {"label":"Referral","url":"/interface/patient_file/transaction/print_referral.php","target":"rep","children":[],"requirement":0}],"requirement":0},
    {"label":"Services","children":[
      {"label":"Background Services","menu_id":"rep0","target":"rep","url":"/interface/reports/background_services.php","children":[],"requirement":0},
      {"label":"Direct Message Log","menu_id":"rep0","target":"rep","url":"/interface/reports/direct_message_log.php","children":[],"requirement":0}],"requirement":0}],"requirement":0},
  {"label":"Miscellaneous","menu_id":"misimg","children":[
    {"label":"Patient Education","menu_id":"ped0","target":"msc","url":"/interface/reports/patient_edu_web_lookup.php","children":[],"requirement":0},
    {"label":"Authorizations","menu_id":"aun0","target":"msc","url":"/interface/main/authorizations/authorizations.php","children":[],"requirement":0},
    {"label":"Fax/Scan","menu_id":"fax","target":"msc","url":"/interface/fax/faxq.php","children":[],"requirement":0,"global_req":["enable_hylafax","enable_scanner"]},
    {"label":"Addr Book","menu_id":"adb0","target":"msc","url":"/interface/usergroup/addrbook_list.php","children":[],"requirement":0,"acl_req":["admin","practice"]},
    {"label":"Order Catalog","menu_id":"ort0","target":"msc","url":"/interface/orders/types.php","children":[],"requirement":0},
    {"label":"Chart Tracker","menu_id":"cht0","target":"msc","url":"/custom/chart_tracker.php","children":[],"requirement":0},
    {"label":"Ofc Notes","menu_id":"ono0","target":"msc","url":"/interface/main/onotes/office_comments.php","children":[],"requirement":0},
    {"label":"BatchCom","menu_id":"adm0","target":"msc","url":"/interface/batchcom/batchcom.php","children":[],"requirement":0},
    {"label":"Password","menu_id":"pwd0","target":"msc","url":"/interface/usergroup/user_info.php","children":[],"requirement":0},
    {"label":"Preferences","menu_id":"prf0","target":"msc","url":"/interface/super/edit_globals.php?mode=user","children":[],"requirement":0},
    {"label":"New Documents","menu_id":"adm0","target":"msc","url":"/controller.php?document&list&patient_id=00","children":[],"requirement":0},
    {"label":"Document Templates","menu_id":"adm0","target":"msc","url":"/interface/super/manage_document_templates.php","children":[],"requirement":0}],"requirement":0},
  {"label":"Popups","menu_id":"popup","children":[
    {"label":"Issues","menu_id":"Popup:Issues","url":"/interface/patient_file/problem_encounter.php","target":"pop","children":[],"requirement":1},
    {"label":"Export","menu_id":"Popup:Export","url":"/custom/export_xml.php","target":"pop","children":[],"requirement":1},
    {"label":"Import","menu_id":"Popup:Import","url":"/custom/import_xml.php","target":"pop","children":[],"requirement":1},
    {"label":"Appts","menu_id":"Popup:Appts","url":"/interface/reports/appointments_report.php?patient=","target":"pop","children":[],"requirement":1},
    {"label":"Refer","menu_id":"Popup:Refer","url":"/custom/refer.php","target":"pop","children":[],"requirement":1},
    {"label":"Superbill","menu_id":"Popup:Superbill","url":"/interface/patient_file/printed_fee_sheet.php?fill=1","target":"pop","children":[],"requirement":1},
    {"label":"Payment","menu_id":"Popup:Payment","url":"/interface/patient_file/front_payment.php","target":"pop","children":[],"requirement":1},
    {"label":"Letter","menu_id":"Popup:Letter","url":"/interface/patient_file/letter.php","target":"pop","children":[],"requirement":1},
    {"label":"Chart Label","menu_id":"Popup:Chart Label","url":"/interface/patient_file/label.php","target":"pop","children":[],"requirement":1},
    {"label":"Barcode Label","menu_id":"Popup:Barcode Label","url":"/interface/patient_file/barcode_label.php","target":"pop","children":[],"requirement":1},
    {"label":"Address Label","menu_id":"Popup:Address Label","url":"/interface/patient_file/addr_label.php","target":"pop","children":[],"requirement":1}],"requirement":0},
  {"label":"About","menu_id":"abo0","target":"msc","url":"/interface/main/about_page.php","children":[],"requirement":0}
]';
