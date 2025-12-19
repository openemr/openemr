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

namespace OpenEMR\Tests\Setting\Service\Global;

use OpenEMR\Services\Globals\GlobalSettingSection;
use OpenEMR\Setting\Service\Factory\SettingServiceFactory;
use OpenEMR\Setting\Service\Global\GlobalSettingService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('setting')]
#[CoversClass(GlobalSettingService::class)]
#[CoversMethod(GlobalSettingService::class, 'getSettingKeysBySectionName')]
class GlobalSettingServiceTest extends TestCase
{
    #[Test]
    public function dataProviderConsistencyTest(): void
    {
        $this->assertEquals(
            GlobalSettingSection::ALL_SECTIONS,
            array_map(
                static fn ($data): string => $data[0],
                iterator_to_array(
                    self::getSettingKeysBySectionNameDataProvider()
                )
            )
        );
    }

    #[Test]
    #[DataProvider('getSettingKeysBySectionNameDataProvider')]
    public function getSettingKeysBySectionNameTest(
        string $sectionName,
        array $expectedSectionSessionKeys,
    ): void {
        $settingService = SettingServiceFactory::createGlobal();
        $reflection = new \ReflectionClass($settingService);
        $method = $reflection->getMethod('getSettingKeysBySectionName');

        $this->assertEquals(
            $expectedSectionSessionKeys,
            $method->invokeArgs($settingService, [$sectionName])
        );
    }

    /**
     * @see GlobalSettingSection::ALL_SECTIONS
     */
    public static function getSettingKeysBySectionNameDataProvider(): iterable
    {
        yield [GlobalSettingSection::APPEARANCE, [
            'theme_tabs_layout',
            'css_header',
            'hide_dashboard_cards',
            'window_title_add_patient_name',
            'enable_compact_mode',
            'search_any_patient',
            'default_encounter_view',
            'enable_group_therapy',
            'full_new_patient_form',
            'gbl_edit_patient_form',
            'patient_search_results_style',
            'gbl_nav_visit_forms',
            'simplified_prescriptions',
            'simplified_copay',
            'use_charges_panel',
            'enable_fees_in_left_menu',
            'enable_batch_payment',
            'enable_posting',
            'enable_edihistory_in_left_menu',
            'encounter_page_size',
            'gbl_pt_list_page_size',
            'gbl_pt_list_new_window',
            'right_justify_labels_demographics',
            'num_of_messages_displayed',
            'recent_patient_count',
            'gbl_vitals_options',
            'gbl_vitals_max_history_cols',
            'gb_how_sort_list',
            'prevent_browser_refresh',
            'form_actionbar_position',
        ]];

        yield [GlobalSettingSection::BILLING, [
            'hide_billing_widget',
            'force_billing_widget_open',
            'ub04_support',
            'top_ubmargin_default',
            'left_ubmargin_default',
            'cms_top_margin_default',
            'cms_left_margin_default',
            'preprinted_cms_1500',
            'cms_1500_box_31_format',
            'cms_1500_box_31_date',
            'default_search_code_type',
            'default_rendering_provider',
            'posting_adj_disable',
            'force_claim_balancing',
            'show_payment_history',
            'void_checkout_reopen',
            'support_fee_sheet_line_item_provider',
            'default_fee_sheet_line_item_provider',
            'include_inactive_providers',
            'replicate_justification',
            'display_units_in_billing',
            'notes_to_display_in_Billing',
            'MedicareReferrerIsRenderer',
            'statement_logo',
            'use_custom_statement',
            'statement_appearance',
            'billing_phone_number',
            'show_aging_on_custom_statement',
            'use_statement_print_exclusion',
            'minimum_amount_to_print',
            'statement_bill_note_print',
            'number_appointments_on_statement',
            'statement_message_to_patient',
            'statement_msg_text',
            'use_dunning_message',
            'first_dun_msg_set',
            'first_dun_msg_text',
            'second_dun_msg_set',
            'second_dun_msg_text',
            'third_dun_msg_set',
            'third_dun_msg_text',
            'fourth_dun_msg_set',
            'fourth_dun_msg_text',
            'fifth_dun_msg_set',
            'fifth_dun_msg_text',
            'save_codes_history',
            'enable_percent_pricing',
            'gen_x12_based_on_ins_co',
            'auto_sftp_claims_to_x12_partner',
            'enable_swap_secondary_insurance',
            'add_unmatched_code_from_ins_co_era_to_billing',
        ]];

        yield [GlobalSettingSection::BRANDING, [
            'openemr_name',
            'machine_name',
            'display_main_menu_logo',
            'online_support_link',
            'user_manual_link',
            'support_phone_number',
            'display_acknowledgements',
            'display_review_link',
            'display_donations_link',
        ]];

        yield [GlobalSettingSection::CALENDAR, [
            'disable_calendar',
            'schedule_start',
            'schedule_end',
            'calendar_interval',
            'calendar_view_type',
            'first_day_week',
            'calendar_appt_style',
            'event_color',
            'number_of_appts_to_show',
            'number_of_group_appts_to_show',
            'number_of_ex_appts_to_show',
            'appt_display_sets_option',
            'appt_display_sets_color_1',
            'appt_display_sets_color_2',
            'appt_display_sets_color_3',
            'appt_display_sets_color_4',
            'appt_recurrences_widget',
            'num_past_appointments_to_show',
            'docs_see_entire_calendar',
            'auto_create_new_encounters',
            'auto_create_prevent_reason',
            'allow_early_check_in',
            'submit_changes_for_all_appts_at_once',
            'disable_pat_trkr',
            'ptkr_visit_reason',
            'ptkr_show_pid',
            'ptkr_show_encounter',
            'ptkr_show_staff',
            'ptkr_date_range',
            'ptkr_start_date',
            'ptkr_end_date',
            'pat_trkr_timer',
            'checkout_roll_off',
            'drug_screen',
            'drug_testing_percentage',
            'maximum_drug_test_yearly',
            'disable_rcb',
        ]];

        yield [GlobalSettingSection::CARE_COORDINATION, [
            'ccda_view_max_sections',
            'ccda_ccd_section_sort_order',
            'ccda_referral_section_sort_order',
            'ccda_toc_section_sort_order',
            'ccda_careplan_section_sort_order',
            'ccda_default_section_sort_order',
        ]];

        yield [GlobalSettingSection::CDR, [
            'enable_cdr',
            'enable_allergy_check',
            'enable_alert_log',
            'enable_cdr_new_crp',
            'enable_cdr_crw',
            'enable_cdr_crp',
            'enable_cdr_prw',
            'enable_cqm',
            'pqri_registry_name',
            'pqri_registry_id',
            'cqm_performance_period',
            'enable_amc',
            'enable_amc_prompting',
            'enable_amc_tracking',
            'cdr_report_nice',
            'pat_rem_clin_nice',
            'report_itemizing_standard',
            'report_itemizing_cqm',
            'report_itemizing_amc',
            'dated_reminders_max_alerts_to_show',
            'patient_birthday_alert',
            'patient_birthday_alert_manual_off',
        ]];

        yield [GlobalSettingSection::CONNECTORS, [
            'site_addr_oath',
            'rest_fhir_api',
            'rest_system_scopes_api',
            'rest_api',
            'rest_portal_api',
            'oauth_password_grant',
            'oauth_app_manual_approval',
            'oauth_ehr_launch_authorization_flow_skip',
            'fhir_us_core_profile_version',
            'cc_front_payments',
            'cc_stripe_terminal',
            'payment_gateway',
            'gateway_mode_production',
            'gateway_public_key',
            'gateway_api_key',
            'gateway_transaction_key',
            'sphere_clinicfront_trxcustid',
            'sphere_clinicfront_trxcustid_licensekey',
            'sphere_moto_tc_link_pass',
            'sphere_clinicfront_retail_trxcustid',
            'sphere_clinicfront_retail_trxcustid_licensekey',
            'sphere_retail_tc_link_pass',
            'sphere_patientfront_trxcustid',
            'sphere_patientfront_trxcustid_licensekey',
            'sphere_ecomm_tc_link_pass',
            'sphere_credit_void_confirm_pin',
            'medex_enable',
            'erx_enable',
            'erx_newcrop_path',
            'erx_newcrop_path_soap',
            'erx_soap_ttl_allergies',
            'erx_soap_ttl_medications',
            'erx_account_partner_name',
            'erx_account_name',
            'erx_account_password',
            'erx_account_id',
            'erx_upload_active',
            'erx_import_status_message',
            'erx_medication_display',
            'erx_allergy_display',
            'erx_default_patient_country',
            'erx_debug_setting',
            'ccda_alt_service_enable',
            'phimail_enable',
            'phimail_testmode_disabled',
            'phimail_verifyrecipientreceived_enable',
            'phimail_server_address',
            'phimail_username',
            'phimail_password',
            'phimail_notify',
            'phimail_interval',
            'phimail_ccd_enable',
            'phimail_ccr_enable',
            'easipro_enable',
            'easipro_server',
            'easipro_name',
            'easipro_pass',
            'usps_apiv3_client_id',
            'usps_apiv3_client_secret',
            'ccda_validation_disable',
            'mdht_conformance_server_enable',
            'mdht_conformance_server',
        ]];

        yield [GlobalSettingSection::DOCUMENTS, [
            'document_storage_method',
            'couchdb_host',
            'couchdb_user',
            'couchdb_pass',
            'couchdb_port',
            'couchdb_dbase',
            'couchdb_connection_ssl',
            'couchdb_ssl_allow_selfsigned',
            'couchdb_log',
            'expand_document_tree',
            'patient_id_category_name',
            'patient_photo_category_name',
            'lab_results_category_name',
            'gbl_mdm_category_name',
            'generate_doc_thumb',
            'thumb_doc_max_size',
        ]];

        yield [GlobalSettingSection::ENCOUNTER_FORM, [
            'default_chief_complaint',
            'default_visit_category',
            'enable_follow_up_encounters',
            'gbl_visit_referral_source',
            'gbl_visit_onset_date',
            'set_pos_code_encounter',
            'set_service_facility_encounter',
            'enc_service_date',
            'enc_sensitivity_visibility',
            'enc_in_collection',
            'enc_enable_issues',
            'enc_enable_referring_provider',
            'enc_enable_facility',
            'enc_enable_discharge_disposition',
            'enc_enable_visit_category',
            'enc_enable_class',
            'enc_enable_type',
            'enc_enable_ordering_provider',
        ]];

        yield [GlobalSettingSection::E_SIGN, [
            'esign_all',
            'lock_esign_all',
            'esign_individual',
            'lock_esign_individual',
            'esign_lock_toggle',
            'esign_report_show_only_signed',
            'esign_report_hide_empty_sig',
            'esign_report_hide_all_sig',
        ]];

        yield [GlobalSettingSection::FEATURES, [
            'specific_application',
            'inhouse_pharmacy',
            'disable_chart_tracker',
            'disable_immunizations',
            'disable_prescriptions',
            'text_templates_enabled',
            'omit_employers',
            'select_multi_providers',
            'disable_non_default_groups',
            'ignore_pnotes_authorization',
            'support_encounter_claims',
            'advance_directives_warning',
            'configuration_import_export',
            'restrict_user_facility',
            'set_facility_cookie',
            'login_into_facility',
            'receipts_by_provider',
            'discount_by_money',
            'gbl_form_save_close',
            'gbl_mask_patient_id',
            'gbl_mask_invoice_number',
            'gbl_mask_product_id',
            'activate_ccr_ccd_report',
            'drive_encryption',
            'couchdb_encryption',
            'hide_document_encryption',
            'use_custom_immun_list',
            'amendments',
            'allow_pat_delete',
            'observation_results_immunization',
            'enable_help',
            'messages_due_date',
            'expand_form',
            'graph_data_warning',
        ]];

        yield [GlobalSettingSection::INSURANCE, [
            'enable_eligibility_requests',
            'simplified_demographics',
            'insurance_information',
            'disable_eligibility_log',
            'insurance_only_one',
        ]];

        yield [GlobalSettingSection::LOCALE, [
            'language_default',
            'language_menu_showall',
            'language_menu_other',
            'allow_debug_language',
            'translate_no_safe_apostrophe',
            'translate_layout',
            'translate_lists',
            'translate_gacl_groups',
            'translate_form_titles',
            'translate_document_categories',
            'translate_appt_categories',
            'units_of_measurement',
            'us_weight_format',
            'phone_country_code',
            'date_display_format',
            'time_display_format',
            'gbl_time_zone',
            'currency_decimals',
            'currency_dec_point',
            'currency_thousands_sep',
            'gbl_currency_symbol',
            'age_display_format',
            'age_display_limit',
            'weekend_days',
        ]];

        yield [GlobalSettingSection::LOGGING, [
            'user_debug',
            'enable_auditlog',
            'audit_events_patient-record',
            'audit_events_scheduling',
            'audit_events_order',
            'audit_events_lab-results',
            'audit_events_security-administration',
            'audit_events_backup',
            'audit_events_other',
            'audit_events_query',
            'audit_events_cdr',
            'audit_events_http-request',
            'gbl_force_log_breakglass',
            'enable_atna_audit',
            'atna_audit_host',
            'atna_audit_port',
            'atna_audit_localcert',
            'atna_audit_cacert',
            'enable_auditlog_encryption',
            'api_log_option',
            'billing_log_option',
            'gbl_print_log_option',
            'system_error_logging',
        ]];

        yield [GlobalSettingSection::LOGIN_PAGE, [
            'login_page_layout',
            'primary_logo_width',
            'secondary_logo_width',
            'logo_position',
            'display_acknowledgements_on_login',
            'show_tagline_on_login',
            'login_tagline_text',
            'show_labels_on_login_form',
            'show_label_login',
            'show_primary_logo',
            'extra_logo_login',
            'secondary_logo_position',
            'tiny_logo_1',
            'tiny_logo_2',
        ]];

        yield [GlobalSettingSection::MISCELLANEOUS, [
            'enable_database_connection_pooling',
            'mysql_bin_dir',
            'perl_bin_dir',
            'temporary_files_dir',
            'backup_log_dir',
            'state_data_type',
            'state_list',
            'state_custom_addlist_widget',
            'country_data_type',
            'country_list',
            'post_to_date_benchmark',
            'enable_hylafax',
            'hylafax_server',
            'hylafax_basedir',
            'enable_scanner',
            'scanner_output_directory',
            'unique_installation_id',
        ]];

        yield [GlobalSettingSection::NOTIFICATIONS, [
            'patient_reminder_sender_name',
            'patient_reminder_sender_email',
            'practice_return_email_path',
            'EMAIL_METHOD',
            'SMTP_HOST',
            'SMTP_PORT',
            'SMTP_USER',
            'SMTP_PASS',
            'SMTP_SECURE',
            'EMAIL_NOTIFICATION_HOUR',
            'SMS_NOTIFICATION_HOUR',
            'SMS_GATEWAY_USENAME',
            'SMS_GATEWAY_PASSWORD',
            'SMS_GATEWAY_APIKEY',
            'phone_notification_hour',
            'phone_gateway_username',
            'phone_gateway_password',
            'phone_gateway_url',
        ]];

        yield [GlobalSettingSection::PATIENT_BANNER_BAR, [
            'patient_name_display',
        ]];

        yield [GlobalSettingSection::PDF, [
            'pdf_layout',
            'pdf_language',
            'pdf_size',
            'pdf_font_size',
            'pdf_left_margin',
            'pdf_right_margin',
            'pdf_top_margin',
            'pdf_bottom_margin',
            'pdf_output',
            'chart_label_type',
            'barcode_label_type',
            'addr_label_type',
            'env_x_width',
            'env_y_height',
            'env_font_size',
            'env_x_dist',
            'env_y_dist',
        ]];

        yield [GlobalSettingSection::PORTAL, [
            'portal_onsite_two_enable',
            'portal_onsite_two_address',
            'portal_css_header',
            'portal_force_credential_reset',
            'portal_onsite_two_basepath',
            'use_email_for_portal_username',
            'enforce_signin_email',
            'google_recaptcha_site_key',
            'google_recaptcha_secret_key',
            'portal_primary_menu_logo_height',
            'portal_onsite_two_register',
            'portal_two_pass_reset',
            'allow_portal_appointments',
            'allow_custom_report',
            'portal_two_ledger',
            'portal_two_payments',
            'portal_onsite_document_download',
            'allow_portal_uploads',
            'show_insurance_in_profile',
            'show_portal_primary_logo',
            'extra_portal_logo_login',
            'secondary_portal_logo_position',
        ]];

        yield [GlobalSettingSection::QUESTIONNAIRES, [
            'questionnaire_display_LOINCnote',
            'questionnaire_display_style',
            'questionnaire_display_fullscreen',

        ]];
        yield [GlobalSettingSection::REPORT, [
            'use_custom_daysheet',
            'daysheet_provider_totals',
            'ledger_begin_date',
            'print_next_appointment_on_ledger',
            'sales_report_invoice',
            'cash_receipts_report_invoice',
        ]];

        yield [GlobalSettingSection::RX, [
            'rx_enable_DEA',
            'rx_show_DEA',
            'rx_enable_NPI',
            'rx_show_NPI',
            'rx_enable_SLN',
            'rx_show_SLN',
            'rx_show_drug_drug',
            'rx_paper_size',
            'rx_left_margin',
            'rx_right_margin',
            'rx_top_margin',
            'rx_bottom_margin',
            'rx_use_fax_template',
            'rx_zend_html_template',
            'rx_zend_html_action',
            'rx_zend_pdf_template',
            'rx_zend_pdf_action',
            'rx_send_email',
        ]];

        yield [GlobalSettingSection::SECURITY, [
            'sql_string_no_show_screen',
            'timeout',
            'portal_timeout',
            'secure_upload',
            'secure_password',
            'gbl_minimum_password_length',
            'gbl_maximum_password_length',
            'password_history',
            'password_expiration_days',
            'password_grace_time',
            'password_max_failed_logins',
            'time_reset_password_max_failed_logins',
            'ip_max_failed_logins',
            'ip_time_reset_password_max_failed_logins',
            'gbl_fac_warehouse_restrictions',
            'is_client_ssl_enabled',
            'certificate_authority_crt',
            'certificate_authority_key',
            'client_certificate_valid_in_days',
            'Emergency_Login_email_id',
            'new_validate',
            'allow_multiple_databases',
            'safe_key_database',
            'google_signin_enabled',
            'google_signin_client_id',
            'gbl_ldap_enabled',
            'gbl_ldap_host',
            'gbl_ldap_dn',
            'gbl_ldap_exclusions',
            'gbl_debug_hash_verify_execution_time',
            'gbl_auth_hash_algo',
            'gbl_auth_bcrypt_hash_cost',
            'gbl_auth_argon_hash_memory_cost',
            'gbl_auth_argon_hash_time_cost',
            'gbl_auth_argon_hash_thread_cost',
            'gbl_auth_sha512_rounds',
        ]];
    }
}
