<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Globals;

use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

// @todo Move initialization to classes
require_once(__DIR__ . "/../../../../library/globals.inc.php"); // As we need section names

#[Group('setting')]
#[CoversClass(GlobalsService::class)]
#[CoversMethod(GlobalsService::class, 'getUserSpecificSections')]
#[CoversMethod(GlobalsService::class, 'getUserSpecificSettings')]
#[CoversMethod(GlobalsService::class, 'getSettingDataType')]
class GlobalsServiceTest extends TestCase
{
    #[Test]
    public function getUserSpecificSectionsTest(): void
    {
        $this->assertEquals([
            'Appearance',
            'Locale',
            'Features',
            'Billing',
            'Report',
            'Calendar',
            'CDR',
            'Connectors',
            'Questionnaires',
            'Carecoordination',
        ], GlobalsServiceFactory::getInstance()->getUserSpecificSections());
    }

    #[Test]
    public function getUserSpecificSettingsTest(): void
    {
        $this->assertEquals([
            'default_top_pane',
            'default_second_tab',
            'theme_tabs_layout',
            'css_header',
            'enable_compact_mode',
            'vertical_responsive_menu', // not exists
            'search_any_patient',
            'default_encounter_view',
            'gbl_pt_list_page_size',
            'gbl_pt_list_new_window',
            'units_of_measurement',
            'us_weight_format',
            'date_display_format',
            'time_display_format',
            'enable_help',
            'text_templates_enabled',
            'posting_adj_disable',
            'messages_due_date',
            'expand_form',
            'ledger_begin_date',
            'print_next_appointment_on_ledger',
            'calendar_view_type',
            'event_color',
            'pat_trkr_timer',
            'ptkr_visit_reason',
            'ptkr_date_range',
            'ptkr_start_date',
            'ptkr_end_date',
            'checkout_roll_off',
            'patient_birthday_alert',
            'patient_birthday_alert_manual_off',
            'erx_import_status_message',
            'questionnaire_display_LOINCnote',
            'questionnaire_display_style',
            'questionnaire_display_fullscreen',
            'ccda_view_max_sections',
            'ccda_ccd_section_sort_order',
            'ccda_referral_section_sort_order',
            'ccda_toc_section_sort_order',
            'ccda_careplan_section_sort_order',
            'ccda_default_section_sort_order',
        ], GlobalsServiceFactory::getInstance()->getUserSpecificSettings());
    }

    #[Test]
    #[DataProvider('getSettingDataTypeDataProvider')]
    public function getSettingDataTypeTest(
        string $settingKey,
        string $expectedDataType
    ): void {
        Assert::oneOf(
            $expectedDataType,
            GlobalSetting::ALL_DATA_TYPES,
        );

        $globalsService = GlobalsServiceFactory::getInstance();
        $dataType = $globalsService->getSettingDataType($settingKey);
        $this->assertEquals(
            $expectedDataType,
            $dataType,
            sprintf(
                'Setting %s expected to have data type %s, got: %s. Other settings with expected data type are: %s',
                $settingKey,
                $expectedDataType,
                $dataType,
                implode(
                    ', ',
                    iterator_to_array(
                        $globalsService->getSettingsByDataType($expectedDataType)
                    )
                )
            )
        );
    }

    /**
     * This test was made just to have some source of settingKey => dataType
     * pairs examples for manual settings testing
     */
    public static function getSettingDataTypeDataProvider(): iterable
    {
        // No related settings exist yet
        // yield ['', GlobalSetting::DATA_TYPE_ADDRESS_BOOK];
        // yield ['', GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION];
        // yield ['', GlobalSetting::DATA_TYPE_PASS];

        // GlobalSetting::DATA_TYPE_ENUM
        yield ['search_any_patient', GlobalSetting::DATA_TYPE_ENUM];
        yield ['default_encounter_view', GlobalSetting::DATA_TYPE_ENUM];
        yield ['full_new_patient_form', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_edit_patient_form', GlobalSetting::DATA_TYPE_ENUM];
        yield ['patient_search_results_style', GlobalSetting::DATA_TYPE_ENUM];
        yield ['encounter_page_size', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_pt_list_page_size', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_vitals_options', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gb_how_sort_list', GlobalSetting::DATA_TYPE_ENUM];
        yield ['prevent_browser_refresh', GlobalSetting::DATA_TYPE_ENUM];
        yield ['form_actionbar_position', GlobalSetting::DATA_TYPE_ENUM];
        yield ['login_page_layout', GlobalSetting::DATA_TYPE_ENUM];
        yield ['primary_logo_width', GlobalSetting::DATA_TYPE_ENUM];
        yield ['secondary_logo_width', GlobalSetting::DATA_TYPE_ENUM];
        yield ['logo_position', GlobalSetting::DATA_TYPE_ENUM];
        yield ['secondary_logo_position', GlobalSetting::DATA_TYPE_ENUM];
        yield ['units_of_measurement', GlobalSetting::DATA_TYPE_ENUM];
        yield ['us_weight_format', GlobalSetting::DATA_TYPE_ENUM];
        yield ['date_display_format', GlobalSetting::DATA_TYPE_ENUM];
        yield ['time_display_format', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_time_zone', GlobalSetting::DATA_TYPE_ENUM];
        yield ['currency_decimals', GlobalSetting::DATA_TYPE_ENUM];
        yield ['currency_dec_point', GlobalSetting::DATA_TYPE_ENUM];
        yield ['currency_thousands_sep', GlobalSetting::DATA_TYPE_ENUM];
        yield ['age_display_format', GlobalSetting::DATA_TYPE_ENUM];
        yield ['weekend_days', GlobalSetting::DATA_TYPE_ENUM];
        yield ['specific_application', GlobalSetting::DATA_TYPE_ENUM];
        yield ['inhouse_pharmacy', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enable_help', GlobalSetting::DATA_TYPE_ENUM];
        yield ['use_custom_daysheet', GlobalSetting::DATA_TYPE_ENUM];
        yield ['daysheet_provider_totals', GlobalSetting::DATA_TYPE_ENUM];
        yield ['ledger_begin_date', GlobalSetting::DATA_TYPE_ENUM];
        yield ['sales_report_invoice', GlobalSetting::DATA_TYPE_ENUM];
        yield ['cash_receipts_report_invoice', GlobalSetting::DATA_TYPE_ENUM];
        yield ['cms_1500_box_31_format', GlobalSetting::DATA_TYPE_ENUM];
        yield ['cms_1500_box_31_date', GlobalSetting::DATA_TYPE_ENUM];
        yield ['default_rendering_provider', GlobalSetting::DATA_TYPE_ENUM];
        yield ['notes_to_display_in_Billing', GlobalSetting::DATA_TYPE_ENUM];
        yield ['statement_appearance', GlobalSetting::DATA_TYPE_ENUM];
        yield ['document_storage_method', GlobalSetting::DATA_TYPE_ENUM];
        yield ['calendar_interval', GlobalSetting::DATA_TYPE_ENUM];
        yield ['calendar_view_type', GlobalSetting::DATA_TYPE_ENUM];
        yield ['first_day_week', GlobalSetting::DATA_TYPE_ENUM];
        yield ['calendar_appt_style', GlobalSetting::DATA_TYPE_ENUM];
        yield ['event_color', GlobalSetting::DATA_TYPE_ENUM];
        yield ['auto_create_new_encounters', GlobalSetting::DATA_TYPE_ENUM];
        yield ['ptkr_start_date', GlobalSetting::DATA_TYPE_ENUM];
        yield ['ptkr_end_date', GlobalSetting::DATA_TYPE_ENUM];
        yield ['pat_trkr_timer', GlobalSetting::DATA_TYPE_ENUM];
        yield ['insurance_information', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_minimum_password_length', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_maximum_password_length', GlobalSetting::DATA_TYPE_ENUM];
        yield ['password_history', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_auth_hash_algo', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_auth_bcrypt_hash_cost', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_auth_argon_hash_memory_cost', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_auth_argon_hash_time_cost', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_auth_argon_hash_thread_cost', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_auth_sha512_rounds', GlobalSetting::DATA_TYPE_ENUM];
        yield ['EMAIL_METHOD', GlobalSetting::DATA_TYPE_ENUM];
        yield ['SMTP_SECURE', GlobalSetting::DATA_TYPE_ENUM];
        yield ['cdr_report_nice', GlobalSetting::DATA_TYPE_ENUM];
        yield ['pat_rem_clin_nice', GlobalSetting::DATA_TYPE_ENUM];
        yield ['patient_birthday_alert', GlobalSetting::DATA_TYPE_ENUM];
        yield ['user_debug', GlobalSetting::DATA_TYPE_ENUM];
        yield ['api_log_option', GlobalSetting::DATA_TYPE_ENUM];
        yield ['billing_log_option', GlobalSetting::DATA_TYPE_ENUM];
        yield ['gbl_print_log_option', GlobalSetting::DATA_TYPE_ENUM];
        yield ['system_error_logging', GlobalSetting::DATA_TYPE_ENUM];
        yield ['state_data_type', GlobalSetting::DATA_TYPE_ENUM];
        yield ['country_data_type', GlobalSetting::DATA_TYPE_ENUM];
        yield ['portal_css_header', GlobalSetting::DATA_TYPE_ENUM];
        yield ['portal_force_credential_reset', GlobalSetting::DATA_TYPE_ENUM];
        yield ['secondary_portal_logo_position', GlobalSetting::DATA_TYPE_ENUM];
        yield ['oauth_password_grant', GlobalSetting::DATA_TYPE_ENUM];
        yield ['oauth_app_manual_approval', GlobalSetting::DATA_TYPE_ENUM];
        yield ['fhir_us_core_profile_version', GlobalSetting::DATA_TYPE_ENUM];
        yield ['payment_gateway', GlobalSetting::DATA_TYPE_ENUM];
        yield ['erx_default_patient_country', GlobalSetting::DATA_TYPE_ENUM];
        yield ['erx_debug_setting', GlobalSetting::DATA_TYPE_ENUM];
        yield ['ccda_alt_service_enable', GlobalSetting::DATA_TYPE_ENUM];
        yield ['rx_paper_size', GlobalSetting::DATA_TYPE_ENUM];
        yield ['pdf_layout', GlobalSetting::DATA_TYPE_ENUM];
        yield ['pdf_language', GlobalSetting::DATA_TYPE_ENUM];
        yield ['pdf_size', GlobalSetting::DATA_TYPE_ENUM];
        yield ['pdf_output', GlobalSetting::DATA_TYPE_ENUM];
        yield ['chart_label_type', GlobalSetting::DATA_TYPE_ENUM];
        yield ['barcode_label_type', GlobalSetting::DATA_TYPE_ENUM];
        yield ['patient_name_display', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_service_date', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_sensitivity_visibility', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_in_collection', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_enable_issues', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_enable_referring_provider', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_enable_facility', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_enable_discharge_disposition', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_enable_visit_category', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_enable_class', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_enable_type', GlobalSetting::DATA_TYPE_ENUM];
        yield ['enc_enable_ordering_provider', GlobalSetting::DATA_TYPE_ENUM];
        yield ['questionnaire_display_LOINCnote', GlobalSetting::DATA_TYPE_ENUM];
        yield ['questionnaire_display_style', GlobalSetting::DATA_TYPE_ENUM];

        // GlobalSetting::DATA_TYPE_BOOL
        yield ['MedicareReferrerIsRenderer', GlobalSetting::DATA_TYPE_BOOL];
        yield ['activate_ccr_ccd_report', GlobalSetting::DATA_TYPE_BOOL];
        yield ['add_unmatched_code_from_ins_co_era_to_billing', GlobalSetting::DATA_TYPE_BOOL];
        yield ['addr_label_type', GlobalSetting::DATA_TYPE_BOOL];
        yield ['advance_directives_warning', GlobalSetting::DATA_TYPE_BOOL];
        yield ['allow_custom_report', GlobalSetting::DATA_TYPE_BOOL];
        yield ['allow_debug_language', GlobalSetting::DATA_TYPE_BOOL];
        yield ['allow_early_check_in', GlobalSetting::DATA_TYPE_BOOL];
        yield ['allow_multiple_databases', GlobalSetting::DATA_TYPE_BOOL];
        yield ['allow_pat_delete', GlobalSetting::DATA_TYPE_BOOL];
        yield ['allow_portal_appointments', GlobalSetting::DATA_TYPE_BOOL];
        yield ['allow_portal_uploads', GlobalSetting::DATA_TYPE_BOOL];
        yield ['amendments', GlobalSetting::DATA_TYPE_BOOL];
        yield ['appt_display_sets_option', GlobalSetting::DATA_TYPE_BOOL];
        yield ['appt_recurrences_widget', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_backup', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_cdr', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_http-request', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_lab-results', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_order', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_other', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_patient-record', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_query', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_scheduling', GlobalSetting::DATA_TYPE_BOOL];
        yield ['audit_events_security-administration', GlobalSetting::DATA_TYPE_BOOL];
        yield ['auto_create_prevent_reason', GlobalSetting::DATA_TYPE_BOOL];
        yield ['auto_sftp_claims_to_x12_partner', GlobalSetting::DATA_TYPE_BOOL];
        yield ['cc_front_payments', GlobalSetting::DATA_TYPE_BOOL];
        yield ['cc_stripe_terminal', GlobalSetting::DATA_TYPE_BOOL];
        yield ['ccda_validation_disable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['configuration_import_export', GlobalSetting::DATA_TYPE_BOOL];
        yield ['couchdb_connection_ssl', GlobalSetting::DATA_TYPE_BOOL];
        yield ['couchdb_encryption', GlobalSetting::DATA_TYPE_BOOL];
        yield ['couchdb_log', GlobalSetting::DATA_TYPE_BOOL];
        yield ['couchdb_ssl_allow_selfsigned', GlobalSetting::DATA_TYPE_BOOL];
        yield ['default_fee_sheet_line_item_provider', GlobalSetting::DATA_TYPE_BOOL];
        yield ['disable_calendar', GlobalSetting::DATA_TYPE_BOOL];
        yield ['disable_chart_tracker', GlobalSetting::DATA_TYPE_BOOL];
        yield ['disable_eligibility_log', GlobalSetting::DATA_TYPE_BOOL];
        yield ['disable_immunizations', GlobalSetting::DATA_TYPE_BOOL];
        yield ['disable_non_default_groups', GlobalSetting::DATA_TYPE_BOOL];
        yield ['disable_pat_trkr', GlobalSetting::DATA_TYPE_BOOL];
        yield ['disable_prescriptions', GlobalSetting::DATA_TYPE_BOOL];
        yield ['disable_rcb', GlobalSetting::DATA_TYPE_BOOL];
        yield ['discount_by_money', GlobalSetting::DATA_TYPE_BOOL];
        yield ['display_acknowledgements', GlobalSetting::DATA_TYPE_BOOL];
        yield ['display_acknowledgements_on_login', GlobalSetting::DATA_TYPE_BOOL];
        yield ['display_donations_link', GlobalSetting::DATA_TYPE_BOOL];
        yield ['display_main_menu_logo', GlobalSetting::DATA_TYPE_BOOL];
        yield ['display_review_link', GlobalSetting::DATA_TYPE_BOOL];
        yield ['display_units_in_billing', GlobalSetting::DATA_TYPE_BOOL];
        yield ['docs_see_entire_calendar', GlobalSetting::DATA_TYPE_BOOL];
        yield ['drive_encryption', GlobalSetting::DATA_TYPE_BOOL];
        yield ['drug_screen', GlobalSetting::DATA_TYPE_BOOL];
        yield ['easipro_enable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_alert_log', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_allergy_check', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_amc', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_amc_prompting', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_amc_tracking', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_atna_audit', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_auditlog', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_auditlog_encryption', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_batch_payment', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_cdr', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_cdr_crp', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_cdr_crw', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_cdr_new_crp', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_cdr_prw', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_compact_mode', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_cqm', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_database_connection_pooling', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_edihistory_in_left_menu', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_eligibility_requests', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_fees_in_left_menu', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_follow_up_encounters', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_group_therapy', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_hylafax', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_percent_pricing', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_posting', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_scanner', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enable_swap_secondary_insurance', GlobalSetting::DATA_TYPE_BOOL];
        yield ['enforce_signin_email', GlobalSetting::DATA_TYPE_BOOL];
        yield ['erx_allergy_display', GlobalSetting::DATA_TYPE_BOOL];
        yield ['erx_enable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['erx_import_status_message', GlobalSetting::DATA_TYPE_BOOL];
        yield ['erx_medication_display', GlobalSetting::DATA_TYPE_BOOL];
        yield ['erx_upload_active', GlobalSetting::DATA_TYPE_BOOL];
        yield ['esign_all', GlobalSetting::DATA_TYPE_BOOL];
        yield ['esign_individual', GlobalSetting::DATA_TYPE_BOOL];
        yield ['esign_lock_toggle', GlobalSetting::DATA_TYPE_BOOL];
        yield ['esign_report_hide_all_sig', GlobalSetting::DATA_TYPE_BOOL];
        yield ['esign_report_hide_empty_sig', GlobalSetting::DATA_TYPE_BOOL];
        yield ['esign_report_show_only_signed', GlobalSetting::DATA_TYPE_BOOL];
        yield ['expand_document_tree', GlobalSetting::DATA_TYPE_BOOL];
        yield ['expand_form', GlobalSetting::DATA_TYPE_BOOL];
        yield ['extra_logo_login', GlobalSetting::DATA_TYPE_BOOL];
        yield ['extra_portal_logo_login', GlobalSetting::DATA_TYPE_BOOL];
        yield ['force_billing_widget_open', GlobalSetting::DATA_TYPE_BOOL];
        yield ['force_claim_balancing', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gateway_mode_production', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gbl_debug_hash_verify_execution_time', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gbl_fac_warehouse_restrictions', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gbl_force_log_breakglass', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gbl_form_save_close', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gbl_ldap_enabled', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gbl_nav_visit_forms', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gbl_pt_list_new_window', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gbl_visit_onset_date', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gbl_visit_referral_source', GlobalSetting::DATA_TYPE_BOOL];
        yield ['gen_x12_based_on_ins_co', GlobalSetting::DATA_TYPE_BOOL];
        yield ['generate_doc_thumb', GlobalSetting::DATA_TYPE_BOOL];
        yield ['google_signin_enabled', GlobalSetting::DATA_TYPE_BOOL];
        yield ['graph_data_warning', GlobalSetting::DATA_TYPE_BOOL];
        yield ['hide_billing_widget', GlobalSetting::DATA_TYPE_BOOL];
        yield ['hide_document_encryption', GlobalSetting::DATA_TYPE_BOOL];
        yield ['ignore_pnotes_authorization', GlobalSetting::DATA_TYPE_BOOL];
        yield ['include_inactive_providers', GlobalSetting::DATA_TYPE_BOOL];
        yield ['insurance_only_one', GlobalSetting::DATA_TYPE_BOOL];
        yield ['is_client_ssl_enabled', GlobalSetting::DATA_TYPE_BOOL];
        yield ['language_menu_showall', GlobalSetting::DATA_TYPE_BOOL];
        yield ['lock_esign_all', GlobalSetting::DATA_TYPE_BOOL];
        yield ['lock_esign_individual', GlobalSetting::DATA_TYPE_BOOL];
        yield ['login_into_facility', GlobalSetting::DATA_TYPE_BOOL];
        yield ['mdht_conformance_server_enable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['medex_enable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['messages_due_date', GlobalSetting::DATA_TYPE_BOOL];
        yield ['new_validate', GlobalSetting::DATA_TYPE_BOOL];
        yield ['oauth_ehr_launch_authorization_flow_skip', GlobalSetting::DATA_TYPE_BOOL];
        yield ['observation_results_immunization', GlobalSetting::DATA_TYPE_BOOL];
        yield ['omit_employers', GlobalSetting::DATA_TYPE_BOOL];
        yield ['patient_birthday_alert_manual_off', GlobalSetting::DATA_TYPE_BOOL];
        yield ['phimail_ccd_enable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['phimail_ccr_enable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['phimail_enable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['phimail_testmode_disabled', GlobalSetting::DATA_TYPE_BOOL];
        yield ['phimail_verifyrecipientreceived_enable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['portal_onsite_document_download', GlobalSetting::DATA_TYPE_BOOL];
        yield ['portal_onsite_two_basepath', GlobalSetting::DATA_TYPE_BOOL];
        yield ['portal_onsite_two_enable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['portal_onsite_two_register', GlobalSetting::DATA_TYPE_BOOL];
        yield ['portal_two_ledger', GlobalSetting::DATA_TYPE_BOOL];
        yield ['portal_two_pass_reset', GlobalSetting::DATA_TYPE_BOOL];
        yield ['portal_two_payments', GlobalSetting::DATA_TYPE_BOOL];
        yield ['posting_adj_disable', GlobalSetting::DATA_TYPE_BOOL];
        yield ['preprinted_cms_1500', GlobalSetting::DATA_TYPE_BOOL];
        yield ['print_next_appointment_on_ledger', GlobalSetting::DATA_TYPE_BOOL];
        yield ['ptkr_date_range', GlobalSetting::DATA_TYPE_BOOL];
        yield ['ptkr_show_encounter', GlobalSetting::DATA_TYPE_BOOL];
        yield ['ptkr_show_pid', GlobalSetting::DATA_TYPE_BOOL];
        yield ['ptkr_show_staff', GlobalSetting::DATA_TYPE_BOOL];
        yield ['ptkr_visit_reason', GlobalSetting::DATA_TYPE_BOOL];
        yield ['questionnaire_display_fullscreen', GlobalSetting::DATA_TYPE_BOOL];
        yield ['receipts_by_provider', GlobalSetting::DATA_TYPE_BOOL];
        yield ['replicate_justification', GlobalSetting::DATA_TYPE_BOOL];
        yield ['report_itemizing_amc', GlobalSetting::DATA_TYPE_BOOL];
        yield ['report_itemizing_cqm', GlobalSetting::DATA_TYPE_BOOL];
        yield ['report_itemizing_standard', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rest_api', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rest_fhir_api', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rest_portal_api', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rest_system_scopes_api', GlobalSetting::DATA_TYPE_BOOL];
        yield ['restrict_user_facility', GlobalSetting::DATA_TYPE_BOOL];
        yield ['right_justify_labels_demographics', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_enable_DEA', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_enable_NPI', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_enable_SLN', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_send_email', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_show_DEA', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_show_NPI', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_show_SLN', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_show_drug_drug', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_use_fax_template', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_zend_html_template', GlobalSetting::DATA_TYPE_BOOL];
        yield ['rx_zend_pdf_template', GlobalSetting::DATA_TYPE_BOOL];
        yield ['save_codes_history', GlobalSetting::DATA_TYPE_BOOL];
        yield ['secure_password', GlobalSetting::DATA_TYPE_BOOL];
        yield ['secure_upload', GlobalSetting::DATA_TYPE_BOOL];
        yield ['select_multi_providers', GlobalSetting::DATA_TYPE_BOOL];
        yield ['set_facility_cookie', GlobalSetting::DATA_TYPE_BOOL];
        yield ['set_pos_code_encounter', GlobalSetting::DATA_TYPE_BOOL];
        yield ['set_service_facility_encounter', GlobalSetting::DATA_TYPE_BOOL];
        yield ['show_aging_on_custom_statement', GlobalSetting::DATA_TYPE_BOOL];
        yield ['show_insurance_in_profile', GlobalSetting::DATA_TYPE_BOOL];
        yield ['show_label_login', GlobalSetting::DATA_TYPE_BOOL];
        yield ['show_labels_on_login_form', GlobalSetting::DATA_TYPE_BOOL];
        yield ['show_payment_history', GlobalSetting::DATA_TYPE_BOOL];
        yield ['show_portal_primary_logo', GlobalSetting::DATA_TYPE_BOOL];
        yield ['show_primary_logo', GlobalSetting::DATA_TYPE_BOOL];
        yield ['show_tagline_on_login', GlobalSetting::DATA_TYPE_BOOL];
        yield ['simplified_copay', GlobalSetting::DATA_TYPE_BOOL];
        yield ['simplified_demographics', GlobalSetting::DATA_TYPE_BOOL];
        yield ['simplified_prescriptions', GlobalSetting::DATA_TYPE_BOOL];
        yield ['sql_string_no_show_screen', GlobalSetting::DATA_TYPE_BOOL];
        yield ['state_custom_addlist_widget', GlobalSetting::DATA_TYPE_BOOL];
        yield ['statement_bill_note_print', GlobalSetting::DATA_TYPE_BOOL];
        yield ['statement_message_to_patient', GlobalSetting::DATA_TYPE_BOOL];
        yield ['submit_changes_for_all_appts_at_once', GlobalSetting::DATA_TYPE_BOOL];
        yield ['support_encounter_claims', GlobalSetting::DATA_TYPE_BOOL];
        yield ['support_fee_sheet_line_item_provider', GlobalSetting::DATA_TYPE_BOOL];
        yield ['text_templates_enabled', GlobalSetting::DATA_TYPE_BOOL];
        yield ['tiny_logo_1', GlobalSetting::DATA_TYPE_BOOL];
        yield ['tiny_logo_2', GlobalSetting::DATA_TYPE_BOOL];
        yield ['translate_appt_categories', GlobalSetting::DATA_TYPE_BOOL];
        yield ['translate_document_categories', GlobalSetting::DATA_TYPE_BOOL];
        yield ['translate_form_titles', GlobalSetting::DATA_TYPE_BOOL];
        yield ['translate_gacl_groups', GlobalSetting::DATA_TYPE_BOOL];
        yield ['translate_layout', GlobalSetting::DATA_TYPE_BOOL];
        yield ['translate_lists', GlobalSetting::DATA_TYPE_BOOL];
        yield ['translate_no_safe_apostrophe', GlobalSetting::DATA_TYPE_BOOL];
        yield ['ub04_support', GlobalSetting::DATA_TYPE_BOOL];
        yield ['use_charges_panel', GlobalSetting::DATA_TYPE_BOOL];
        yield ['use_custom_immun_list', GlobalSetting::DATA_TYPE_BOOL];
        yield ['use_custom_statement', GlobalSetting::DATA_TYPE_BOOL];
        yield ['use_dunning_message', GlobalSetting::DATA_TYPE_BOOL];
        yield ['use_email_for_portal_username', GlobalSetting::DATA_TYPE_BOOL];
        yield ['use_statement_print_exclusion', GlobalSetting::DATA_TYPE_BOOL];
        yield ['void_checkout_reopen', GlobalSetting::DATA_TYPE_BOOL];
        yield ['window_title_add_patient_name', GlobalSetting::DATA_TYPE_BOOL];

        // GlobalSetting::DATA_TYPE_CODE_TYPES
        yield ['default_search_code_type', GlobalSetting::DATA_TYPE_CODE_TYPES];

        // GlobalSetting::DATA_TYPE_COLOR_CODE
        yield ['appt_display_sets_color_1', GlobalSetting::DATA_TYPE_COLOR_CODE];
        yield ['appt_display_sets_color_2', GlobalSetting::DATA_TYPE_COLOR_CODE];
        yield ['appt_display_sets_color_3', GlobalSetting::DATA_TYPE_COLOR_CODE];
        yield ['appt_display_sets_color_4', GlobalSetting::DATA_TYPE_COLOR_CODE];

        // GlobalSetting::DATA_TYPE_CSS
        yield ['css_header', GlobalSetting::DATA_TYPE_CSS];

        // GlobalSetting::DATA_TYPE_DEFAULT_RANDOM_UUID
        yield ['unique_installation_id', GlobalSetting::DATA_TYPE_DEFAULT_RANDOM_UUID];

        // GlobalSetting::DATA_TYPE_DEFAULT_VISIT_CATEGORY
        yield ['default_visit_category', GlobalSetting::DATA_TYPE_DEFAULT_VISIT_CATEGORY];

        // GlobalSetting::DATA_TYPE_ENCRYPTED
        yield ['SMTP_PASS', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['couchdb_pass', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['easipro_pass', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['erx_account_password', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['gateway_api_key', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['gateway_public_key', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['gateway_transaction_key', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['google_recaptcha_secret_key', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['phimail_password', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['phone_gateway_password', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['sphere_clinicfront_retail_trxcustid', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['sphere_clinicfront_retail_trxcustid_licensekey', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['sphere_clinicfront_trxcustid', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['sphere_clinicfront_trxcustid_licensekey', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['sphere_ecomm_tc_link_pass', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['sphere_moto_tc_link_pass', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['sphere_patientfront_trxcustid', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['sphere_patientfront_trxcustid_licensekey', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['sphere_retail_tc_link_pass', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['usps_apiv3_client_id', GlobalSetting::DATA_TYPE_ENCRYPTED];
        yield ['usps_apiv3_client_secret', GlobalSetting::DATA_TYPE_ENCRYPTED];

        // GlobalSetting::DATA_TYPE_ENCRYPTED_HASH
        yield ['sphere_credit_void_confirm_pin', GlobalSetting::DATA_TYPE_ENCRYPTED_HASH];

        // GlobalSetting::DATA_TYPE_HOUR
        yield ['schedule_start', GlobalSetting::DATA_TYPE_HOUR];
        yield ['schedule_end', GlobalSetting::DATA_TYPE_HOUR];

        // GlobalSetting::DATA_TYPE_LANGUAGE
        yield ['language_default', GlobalSetting::DATA_TYPE_LANGUAGE];

        // GlobalSetting::DATA_TYPE_MULTI_DASHBOARD_CARDS
        yield ['hide_dashboard_cards', GlobalSetting::DATA_TYPE_MULTI_DASHBOARD_CARDS];

        // GlobalSetting::DATA_TYPE_MULTI_LANGUAGE_SELECT
        yield ['language_menu_other', GlobalSetting::DATA_TYPE_MULTI_LANGUAGE_SELECT];

        // GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR
        yield ['ccda_ccd_section_sort_order', GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR];
        yield ['ccda_referral_section_sort_order', GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR];
        yield ['ccda_toc_section_sort_order', GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR];
        yield ['ccda_careplan_section_sort_order', GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR];
        yield ['ccda_default_section_sort_order', GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR];

        // GlobalSetting::DATA_TYPE_NUMBER
        yield ['EMAIL_NOTIFICATION_HOUR', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['SMS_NOTIFICATION_HOUR', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['SMTP_PORT', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['age_display_limit', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['ccda_view_max_sections', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['checkout_roll_off', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['client_certificate_valid_in_days', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['cms_left_margin_default', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['cms_top_margin_default', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['dated_reminders_max_alerts_to_show', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['drug_testing_percentage', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['env_font_size', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['env_x_dist', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['env_x_width', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['env_y_dist', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['env_y_height', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['erx_soap_ttl_allergies', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['erx_soap_ttl_medications', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['fifth_dun_msg_set', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['first_dun_msg_set', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['fourth_dun_msg_set', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['gbl_vitals_max_history_cols', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['ip_max_failed_logins', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['ip_time_reset_password_max_failed_logins', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['left_ubmargin_default', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['maximum_drug_test_yearly', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['minimum_amount_to_print', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['num_of_messages_displayed', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['num_past_appointments_to_show', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['number_appointments_on_statement', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['number_of_appts_to_show', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['number_of_ex_appts_to_show', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['number_of_group_appts_to_show', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['password_expiration_days', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['password_grace_time', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['password_max_failed_logins', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['pdf_bottom_margin', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['pdf_font_size', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['pdf_left_margin', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['pdf_right_margin', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['pdf_top_margin', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['phimail_interval', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['phone_country_code', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['phone_notification_hour', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['portal_timeout', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['recent_patient_count', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['rx_bottom_margin', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['rx_left_margin', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['rx_right_margin', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['rx_top_margin', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['second_dun_msg_set', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['third_dun_msg_set', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['time_reset_password_max_failed_logins', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['timeout', GlobalSetting::DATA_TYPE_NUMBER];
        yield ['top_ubmargin_default', GlobalSetting::DATA_TYPE_NUMBER];

        // GlobalSetting::DATA_TYPE_TABS_CSS
        yield ['theme_tabs_layout', GlobalSetting::DATA_TYPE_TABS_CSS];

        // GlobalSetting::DATA_TYPE_TEXT
        yield ['openemr_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['machine_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['online_support_link', GlobalSetting::DATA_TYPE_TEXT];
        yield ['user_manual_link', GlobalSetting::DATA_TYPE_TEXT];
        yield ['support_phone_number', GlobalSetting::DATA_TYPE_TEXT];
        yield ['login_tagline_text', GlobalSetting::DATA_TYPE_TEXT];
        yield ['gbl_currency_symbol', GlobalSetting::DATA_TYPE_TEXT];
        yield ['gbl_mask_patient_id', GlobalSetting::DATA_TYPE_TEXT];
        yield ['gbl_mask_invoice_number', GlobalSetting::DATA_TYPE_TEXT];
        yield ['gbl_mask_product_id', GlobalSetting::DATA_TYPE_TEXT];
        yield ['statement_logo', GlobalSetting::DATA_TYPE_TEXT];
        yield ['billing_phone_number', GlobalSetting::DATA_TYPE_TEXT];
        yield ['statement_msg_text', GlobalSetting::DATA_TYPE_TEXT];
        yield ['first_dun_msg_text', GlobalSetting::DATA_TYPE_TEXT];
        yield ['second_dun_msg_text', GlobalSetting::DATA_TYPE_TEXT];
        yield ['third_dun_msg_text', GlobalSetting::DATA_TYPE_TEXT];
        yield ['fourth_dun_msg_text', GlobalSetting::DATA_TYPE_TEXT];
        yield ['fifth_dun_msg_text', GlobalSetting::DATA_TYPE_TEXT];
        yield ['couchdb_host', GlobalSetting::DATA_TYPE_TEXT];
        yield ['couchdb_user', GlobalSetting::DATA_TYPE_TEXT];
        yield ['couchdb_port', GlobalSetting::DATA_TYPE_TEXT];
        yield ['couchdb_dbase', GlobalSetting::DATA_TYPE_TEXT];
        yield ['patient_id_category_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['patient_photo_category_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['lab_results_category_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['gbl_mdm_category_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['thumb_doc_max_size', GlobalSetting::DATA_TYPE_TEXT];
        yield ['certificate_authority_crt', GlobalSetting::DATA_TYPE_TEXT];
        yield ['certificate_authority_key', GlobalSetting::DATA_TYPE_TEXT];
        yield ['Emergency_Login_email_id', GlobalSetting::DATA_TYPE_TEXT];
        yield ['safe_key_database', GlobalSetting::DATA_TYPE_TEXT];
        yield ['google_signin_client_id', GlobalSetting::DATA_TYPE_TEXT];
        yield ['gbl_ldap_host', GlobalSetting::DATA_TYPE_TEXT];
        yield ['gbl_ldap_dn', GlobalSetting::DATA_TYPE_TEXT];
        yield ['gbl_ldap_exclusions', GlobalSetting::DATA_TYPE_TEXT];
        yield ['patient_reminder_sender_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['patient_reminder_sender_email', GlobalSetting::DATA_TYPE_TEXT];
        yield ['practice_return_email_path', GlobalSetting::DATA_TYPE_TEXT];
        yield ['SMTP_HOST', GlobalSetting::DATA_TYPE_TEXT];
        yield ['SMTP_USER', GlobalSetting::DATA_TYPE_TEXT];
        yield ['SMS_GATEWAY_USENAME', GlobalSetting::DATA_TYPE_TEXT];
        yield ['SMS_GATEWAY_PASSWORD', GlobalSetting::DATA_TYPE_TEXT];
        yield ['SMS_GATEWAY_APIKEY', GlobalSetting::DATA_TYPE_TEXT];
        yield ['phone_gateway_username', GlobalSetting::DATA_TYPE_TEXT];
        yield ['phone_gateway_url', GlobalSetting::DATA_TYPE_TEXT];
        yield ['pqri_registry_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['pqri_registry_id', GlobalSetting::DATA_TYPE_TEXT];
        yield ['cqm_performance_period', GlobalSetting::DATA_TYPE_TEXT];
        yield ['atna_audit_host', GlobalSetting::DATA_TYPE_TEXT];
        yield ['atna_audit_port', GlobalSetting::DATA_TYPE_TEXT];
        yield ['atna_audit_localcert', GlobalSetting::DATA_TYPE_TEXT];
        yield ['atna_audit_cacert', GlobalSetting::DATA_TYPE_TEXT];
        yield ['mysql_bin_dir', GlobalSetting::DATA_TYPE_TEXT];
        yield ['perl_bin_dir', GlobalSetting::DATA_TYPE_TEXT];
        yield ['temporary_files_dir', GlobalSetting::DATA_TYPE_TEXT];
        yield ['backup_log_dir', GlobalSetting::DATA_TYPE_TEXT];
        yield ['state_list', GlobalSetting::DATA_TYPE_TEXT];
        yield ['country_list', GlobalSetting::DATA_TYPE_TEXT];
        yield ['post_to_date_benchmark', GlobalSetting::DATA_TYPE_TEXT];
        yield ['hylafax_server', GlobalSetting::DATA_TYPE_TEXT];
        yield ['hylafax_basedir', GlobalSetting::DATA_TYPE_TEXT];
        yield ['scanner_output_directory', GlobalSetting::DATA_TYPE_TEXT];
        yield ['portal_onsite_two_address', GlobalSetting::DATA_TYPE_TEXT];
        yield ['google_recaptcha_site_key', GlobalSetting::DATA_TYPE_TEXT];
        yield ['portal_primary_menu_logo_height', GlobalSetting::DATA_TYPE_TEXT];
        yield ['site_addr_oath', GlobalSetting::DATA_TYPE_TEXT];
        yield ['erx_newcrop_path', GlobalSetting::DATA_TYPE_TEXT];
        yield ['erx_newcrop_path_soap', GlobalSetting::DATA_TYPE_TEXT];
        yield ['erx_account_partner_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['erx_account_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['erx_account_id', GlobalSetting::DATA_TYPE_TEXT];
        yield ['phimail_server_address', GlobalSetting::DATA_TYPE_TEXT];
        yield ['phimail_username', GlobalSetting::DATA_TYPE_TEXT];
        yield ['phimail_notify', GlobalSetting::DATA_TYPE_TEXT];
        yield ['easipro_server', GlobalSetting::DATA_TYPE_TEXT];
        yield ['easipro_name', GlobalSetting::DATA_TYPE_TEXT];
        yield ['mdht_conformance_server', GlobalSetting::DATA_TYPE_TEXT];
        yield ['rx_zend_html_action', GlobalSetting::DATA_TYPE_TEXT];
        yield ['rx_zend_pdf_action', GlobalSetting::DATA_TYPE_TEXT];
        yield ['default_chief_complaint', GlobalSetting::DATA_TYPE_TEXT];
    }
}
