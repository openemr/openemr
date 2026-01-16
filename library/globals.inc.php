<?php

/*
 * This program sets the global variables.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021-2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//  Current supported languages:    // Allow capture of term for translation:
//   Albanian                       // xl('Albanian')
//   Amharic                        // xl('Amharic')
//   Arabic                         // xl('Arabic')
//   Armenian                       // xl('Armenian')
//   Bahasa Indonesia               // xl('Bahasa Indonesia')
//   Bengali                        // xl('Bengali')
//   Bosnian                        // xl('Bosnian')
//   Bulgarian                      // xl('Bulgarian')
//   Chinese (Simplified)           // xl('Chinese (Simplified)')
//   Chinese (Traditional)          // xl('Chinese (Traditional)')
//   Croatian                       // xl('Croatian')
//   Czech                          // xl('Czech')
//   Danish                         // xl('Danish')
//   Dutch                          // xl('Dutch')
//   English (Australian)           // xl('English (Australian)')
//   English (Indian)               // xl('English (Indian)')
//   English (Standard)             // xl('English (Standard)')
//   Estonian                       // xl('Estonian')
//   Filipino                       // xl('Filipino')
//   Finnish                        // xl('Finnish')
//   French                         // xl('French (Standard)')
//   French                         // xl('French (Canadian)')
//   Georgian                       // xl('Georgian')
//   German                         // xl('German')
//   Greek                          // xl('Greek')
//   Gujarati                       // xl('Gujarati')
//   Hebrew                         // xl('Hebrew')
//   Hindi                          // xl('Hindi')
//   Hungarian                      // xl('Hungarian')
//   Italian                        // xl('Italian')
//   Japanese                       // xl('Japanese')
//   Korean                         // xl('Korean')
//   Lao                            // xl('Lao')
//   Lithuanian                     // xl('Lithuanian')
//   Marathi                        // xl('Marathi')
//   Mongolian                      // xl('Mongolian')
//   Norwegian                      // xl('Norwegian')
//   Persian                        // xl('Persian')
//   Polish                         // xl('Polish')
//   Portuguese (Brazilian)         // xl('Portuguese (Brazilian)')
//   Portuguese (European)          // xl('Portuguese (European)')
//   Portuguese (European)          // xl('Portuguese (Angolan)')
//   Romanian                       // xl('Romanian')
//   Russian                        // xl('Russian')
//   Serbian                        // xl('Serbian')
//   Sinhala                        // xl('Sinhala')
//   Slovak                         // xl('Slovak')
//   Somali                         // xl('Somali')
//   Spanish (Latin American)       // xl('Spanish (Latin American)')
//   Spanish (Spain)                // xl('Spanish (Spain)')
//   Swedish                        // xl('Swedish')
//   Tamil                          // xl('Tamil')
//   Telugu                         // xl('Telugu')
//   Thai                           // xl('Thai')
//   Turkish                        // xl('Turkish')
//   Ukrainian                      // xl('Ukrainian')
//   Urdu                           // xl('Urdu')
//   Uzbek                          // xl('Uzbek')
//   Vietnamese                     // xl('Vietnamese')

use OpenEMR\Common\Forms\FormActionBarSettings;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\OeUI\RenderFormFieldHelper;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\Globals\GlobalAppearanceEnum;
use OpenEMR\Services\Globals\GlobalFeaturesEnum;

// OS-dependent stuff.
if (stristr(PHP_OS, 'WIN')) {
    // MS Windows
    $mysql_bin_dir = 'C:/xampp/mysql/bin';
    $perl_bin_dir = 'C:/xampp/perl/bin';
    $temporary_files_dir = 'C:/windows/temp';
    $backup_log_dir = 'C:/windows/temp';
} else {
    // Everything else
    $mysql_bin_dir = '/usr/bin';
    $perl_bin_dir = '/usr/bin';
    $temporary_files_dir = '/tmp';
    $backup_log_dir = '/tmp';
}

function getDefaultRenderListOptions()
{
    return [
        RenderFormFieldHelper::SHOW_ON_NEW_ONLY => xl('Show on New Form Only'),
        RenderFormFieldHelper::SHOW_ON_EDIT_ONLY => xl('Show on Edit Form Only'),
        RenderFormFieldHelper::SHOW_ALL => xl('Show on New and Edit Form'),
        RenderFormFieldHelper::HIDE_ALL => xl('Hide on New and Edit Form'),
    ];
}

// Language constant declarations:
// xl('Appearance')
// xl('Locale')
// xl('Features')
// xl('Calendar')
// xl('Security')
// xl('Notifications')
// xl('Miscellaneous')

// List of user specific tabs and globals
$USER_SPECIFIC_TABS = ['Appearance',
    'Locale',
    'Features',
    'Billing',
    'Report',
    'Calendar',
    'CDR',
    'Connectors',
    'Questionnaires'];
$USER_SPECIFIC_GLOBALS = ['default_top_pane',
    'default_second_tab',
    'theme_tabs_layout',
    'css_header',
    'enable_compact_mode',
    'vertical_responsive_menu',
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
    'questionnaire_display_fullscreen'
];

// Gets array of time zones supported by PHP.
//
function gblTimeZones()
{
    $zones = timezone_identifiers_list();
    $arr = ['' => xl('Unassigned')];
    foreach ($zones as $zone) {
        $arr[$zone] = str_replace('_', ' ', $zone);
    }

    return $arr;
}

$GLOBALS_METADATA = [

    // Appearance Tab
    //
    'Appearance' => [

        'theme_tabs_layout' => [
            xl('Tabs Layout Theme') . '*',
            'tabs_css',
            'tabs_style_full.css',
            xl('Theme of the tabs layout (need to logout and then login to see this new setting).')
        ],

        'css_header' => [
            // Note: Do not change this as it is only for theme defaults and adding themes here does nothing
            xl('General Theme') . '*',
            'css',
            'style_light.css',
            xl('Pick a general theme (need to logout/login after changing this setting).')
        ],
        'hide_dashboard_cards' => [
            xl('Hide selected cards on patient dashboard'),
            'm_dashboard_cards',
            '',
            xl('Multi (Shift or CTRL) Select the cards you want to hide on the patient dashboard.')
        ],
        'window_title_add_patient_name' => [
            xl('Add Patient Name To Window Title'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Adds the patient name to the end of the window title.')
        ],

        'enable_compact_mode' => [
            xl('Enable Compact Mode'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Changes the current theme to be more compact.')
        ],

        'search_any_patient' => [
            xl('Search Patient By Any Demographics'),
            [
                'dual' => xl('Dual'),
                'comprehensive' => xl('Comprehensive'),
                'fixed' => xl('Fixed'),
                'none' => xl('None'),
            ],
            'dual', // default
            xl('Search Patient By Any Demographics, Dual additionally lets direct access to Patient Finder, Comprehensive has collapsed input box, Fixed is similar to Dual with fixed size, None is do not show')
        ],

        'default_encounter_view' => [
            xl('Default Encounter View'),               // descriptive name
            [
                '0' => xl('Clinical View'),
                '1' => xl('Billing View'),
            ],
            '0',                              // default = tree menu
            xl('Choose your default encounter view')
        ],

        'enable_group_therapy' => [
            xl('Enable Group Therapy'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Enables groups module in system.')
        ],

        'full_new_patient_form' => [
            xl('New Patient Form'),

            [
                '0' => xl('Old-style static form without search or duplication check'),
                '1' => xl('All demographics fields, with search and duplication check'),
                '2' => xl('Mandatory or specified fields only, search and dup check'),
                '3' => xl('Mandatory or specified fields only, dup check, no search'),
                '4' => xl('Mandatory or specified fields only, use patient validation Zend module'),
            ],
            '1',                              // default
            xl('Style of form used for adding new patients')
        ],

        'gbl_edit_patient_form' => [
            xl('Modify Patient Form'),

            [
                '0' => xl('Standard check'),
                '1' => xl('Zend Module check in addition to standard check')
            ],
            '0',                              // default
            xl('Validation mechanism for when modifying patient demographics.')
        ],

        'patient_search_results_style' => [
            xl('Patient Search Results Style'),
            [
                '0' => xl('Encounter statistics'),
                '1' => xl('Mandatory and specified fields'),
            ],
            '0',                              // default
            xl('Type of columns displayed for patient search results')
        ],

        'gbl_nav_visit_forms' => [
            xl('Navigation Area Visit Forms'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Navigation area includes encounter forms')
        ],

        GlobalAppearanceEnum::SIMPLIFIED_PRESCRIPTIONS->value  => [
            xl('Simplified Prescriptions'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Omit form, route and interval which then become part of dosage')
        ],

        'simplified_copay' => [
            xl('Simplified Co-Pay'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Omit method of payment from the co-pay panel')
        ],

        'use_charges_panel' => [
            xl('Use Charges Panel'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Enables the old Charges panel for entering billing codes and payments. Not recommended, use the Fee Sheet instead.')
        ],

        // TajEmo Work BY CB 2012/06/21 10:42:31 AM added option to Hide Fees
        'enable_fees_in_left_menu' => [
            xl('Enable Fees Submenu'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Enable Fees Submenu')
        ],
        'enable_batch_payment' => [
            xl('Enable Batch Payment'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Enable Batch Payment')
        ],
        'enable_posting' => [
            xl('Enable Posting'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Enable Posting')
        ],
        // EDI history  2012-09-13
        'enable_edihistory_in_left_menu' => [
            xl('Enable EDI History'),
            'bool',                           // data type
            '1',                              // default = true
            xl('EDI History (under Fees) for storing and interpreting EDI claim response files')
        ],

        'encounter_page_size' => [
            xl('Encounter Page Size'),
            [
                '0' => xl('Show All'),
                '5' => '5',
                '10' => '10',
                '15' => '15',
                '20' => '20',
                '25' => '25',
                '50' => '50',
            ],
            '20',
            xl('Number of encounters to display per page.')
        ],

        'gbl_pt_list_page_size' => [
            xl('Patient List Page Size'),
            [
                '10' => '10',
                '25' => '25',
                '50' => '50',
                '100' => '100',
            ],
            '10',
            xl('Number of patients to display per page in the patient list.')
        ],

        'gbl_pt_list_new_window' => [
            xl('Patient List New Window'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Default state of New Window checkbox in the patient list.')
        ],

        'right_justify_labels_demographics' => [
            xl('Right Justify Labels in Demographics'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Right justify labels in Demographics for easier readability.')
        ],

        'num_of_messages_displayed' => [
            xl('Number of Messages Displayed in Patient Summary'),
            'num',
            '3',
            xl('This is the number of messages that will be displayed in the messages widget in the patient summary screen.')
        ],

        'recent_patient_count' => [
            xl('Maximum number of patients on Recent Patient list'),
            'num',
            '20',
            xl('The maximum number of patients on the Recent Patient list'),
        ],

        'gbl_vitals_options' => [
            xl('Vitals Form Options'),
            [
                '0' => xl('Standard'),
                '1' => xl('Omit circumferences'),
            ],
            '0',                              // default
            xl('Special treatment for the Vitals form')
        ],

        'gbl_vitals_max_history_cols' => [
            xl('Vitals Form Max Historical Columns To Display'),
            'num',
            '2',                              // default
            xl('The number of historical vital columns to display on medium to large screen displays')
        ],

        'gb_how_sort_list' => [
            xl('How to sort a drop-lists'),
            [
                '0' => xl('Sort by seq'),
                '1' => xl('Sort alphabetically')
            ],
            '0',
            xl('What kind of sorting will be in the drop lists.')
        ],

        'prevent_browser_refresh' => [
            xl('Prevent Web Browser Refresh') . '*',
            [
                '0' => xl('Do not warn or prevent web browser refresh'),
                '1' => xl('Warn, but do not prevent web browser refresh'),
                '2' => xl('Warn and prevent web browser refresh')
            ],
            '2',                              // default = true
            xl('Recommended setting is warn and prevent web browser refresh. Only use other settings if needed and use at own risk.')
        ],

        'form_actionbar_position' => [
            xl('Form ActionBar (save, cancel, etc) position')
            ,FormActionBarSettings::getGlobalSettingsList()
            ,FormActionBarSettings::getDefaultSetting() // default = top of the form
            ,xl('Placement of the save/cancel, and other bottons where supported (Demographics, Encounter Forms, etc).')
        ],
    ],

    'Branding' => [
        'openemr_name' => [
            xl('Application Title'),
            'text',
            'OpenEMR',
            xl('Application name used throughout the user interface.')
        ],

        'machine_name' => [
            xl('Application Machine Name'),
            'text',
            'openemr',
            xl('The machine name of the application. Used to identify the EMR in various messaging systems like HL7. Should not contain spaces'),
        ],

        'display_main_menu_logo' => [
            xl('Display main menu logo'),
            'bool',
            '1',
            xl('Dislay main menu logo'),
        ],

        'online_support_link' => [
            xl('Online Support Link'),
            'text',                           // data type
            'http://open-emr.org/',
            xl('URL to a support page.')
        ],

        'user_manual_link' => [
            xl('User Manual Link Override'),
            'text',
            '',
            xl("Point to a custom user manual. Leave blank for the default, auto-generated URL for specific version of application"),
        ],

        'support_phone_number' => [
            xl('Support Phone Number'),
            'text',
            '',
            xl('Phone Number for Vendor Support that Appears on the About Page.')
        ],

        'display_acknowledgements' => [
            xl('Display links to the acknowledgements page'),
            'bool',
            '1',
            xl('Used on the login and about pages'),
        ],

        'display_review_link' => [
            xl('Display the Review link on the About page'),
            'bool',
            '1',
            xl('Display the Review link on the About page'),
        ],

        'display_donations_link' => [
            xl('Display the Donations link on the About page'),
            'bool',
            '1',
            xl('Display the Donations link on the About page'),
        ],
    ],

    // Login Page
    'Login Page' => [
        'login_page_layout' => [
            xl('Login Page Layout') . '*',
            [
                'login/layouts/vertical_box.html.twig' => xl("Vertical Box"),
                'login/layouts/horizontal_box_left_logo.html.twig' => xl("Horizontal Box, Logo on Left"),
                'login/layouts/horizontal_box_right_logo.html.twig' => xl("Horizontal Box, Logo on Right"),
                'login/layouts/horizontal_band_right_logo.html.twig' => xl("Horizontal Band, Logo on Right"),
                'login/layouts/horizontal_band_left_logo.html.twig' => xl("Horizontal Band, Logo on Left"),
                "login/layouts/vertical_band.html.twig" => xl("Vertical Band"),
            ],
            'login/layouts/vertical_band.html.twig',
            xl('Changes the layout of the login page.')
        ],

        'primary_logo_width' => [
            xl('Width of primary logo compared to the container'),
            [
                'w-25' => '25%',
                'w-50' => '50%',
                'w-75' => '75%',
                'w-100' => '100%'
            ],
            'w-50',
            xl('Determine the width of the primary logo compared to the container'),
        ],

        'secondary_logo_width' => [
            xl('Width of secondary logo compared to the container'),
            [
                'w-25' => '25%',
                'w-50' => '50%',
                'w-75' => '75%',
                'w-100' => '100%'
            ],
            'w-50',
            xl('Determine the width of the secondary logo compared to the container'),
        ],

        'logo_position' => [
            xl('Logo Positioning'),
            [
                'flex-column' => 'Stacked',
                'flex-row' => 'Side by Side',
            ],
            'flex-column',
            xl('How the logos will be rendered relative to each other'),
        ],

        'display_acknowledgements_on_login' => [
            xl('Display links to the acknowledgements page'),
            'bool',
            '1',
            xl('Used on the login screen'),
        ],

        'show_tagline_on_login' => [
            xl('Show Tagline on Login Page') . "*",
            'bool',
            '1',
            xl('Show the tagline from the login screen'),
        ],

        'login_tagline_text' => [
            xl('Login Page Tagline') . "*",
            'text',
            xl("The most popular open-source Electronic Health Record and Medical Practice Management solution."),
            xl("Tagline text on the login page")
        ],

        'show_labels_on_login_form' => [
            xl('Show Username and Password Labels on Login Page') . "*",
            'bool',
            '1',
            xl('Show labels on the login form'),
        ],

        'show_label_login' => [
            xl('Show Title on Login'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Show Title on Login')
        ],

        'show_primary_logo' => [
            xl('Show primary logo on login'),
            'bool',
            '1',
            xl('Show primary logo on login'),
        ],

        'extra_logo_login' => [
            xl('Show Secondary Logo on Login'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Show Secondary Logo on Login')
        ],

        'secondary_logo_position' => [
            xl('Order of the Secondary logo'),
            [
                'first' => xl('First Position'),
                'second' => xl('Second Position'),
            ],
            'second',
            xl('Place the secondary logo first, or second'),
        ],

        'tiny_logo_1' => [
            xl('Show Mini Logo 1'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Show Mini Logo 1')
        ],

        'tiny_logo_2' => [
            xl('Show Mini Logo 2'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Show Mini Logo 2')
        ],
    ],

    // Locale Tab
    //
    'Locale' => [

        'language_default' => [
            xl('Default Language'),
            'lang',                           // data type
            'English (Standard)',             // default = english
            xl('Default language if no other is allowed or chosen.')
        ],

        'language_menu_showall' => [
            xl('All Languages Allowed'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Allow all available languages as choices on menu at login.')
        ],

        'language_menu_other' => [
            xl('Allowed Languages'),
            'm_lang',                         // data type
            '',                               // default = none
            xl('Select which languages, if any, may be chosen at login. (only pertinent if above All Languages Allowed is turned off)')
        ],

        'allow_debug_language' => [
            xl('Allow Debugging Language'),
            'bool',                           // data type
            '1',                              // default = true during development and false for production releases
            xl('This will allow selection of the debugging (\'dummy\') language.')
        ],

        'translate_no_safe_apostrophe' => [
            xl('Do Not Use Safe Apostrophe'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will turn off use of safe apostrophe, which is done by converting \' and " to `.(it is highly recommended that this setting is turned off and that safe apostrophe\'s are used)')
        ],

        'disable_translation' => [
            xl('Disable Translation Engine'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Completely disable the translation engine. When enabled, xl() returns the input string unchanged. Use this for English-only deployments to eliminate translation overhead.')
        ],

        'translation_preload_cache' => [
            xl('Preload Translation Cache'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Load all translations into memory at the start of each request. This uses more memory but eliminates database queries for translations, improving performance for sites with many translated strings.')
        ],

        'translate_layout' => [
            xl('Translate Layouts'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Is text from form layouts to be translated?')
        ],

        'translate_lists' => [
            xl('Translate Lists'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Is text from lists to be translated?')
        ],

        'translate_gacl_groups' => [
            xl('Translate Access Control Groups'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Are access control group names to be translated?')
        ],

        'translate_form_titles' => [
            xl('Translate Patient Note Titles'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Are patient note titles to be translated?')
        ],

        'translate_document_categories' => [
            xl('Translate Document Categories'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Are document category names to be translated?')
        ],

        'translate_appt_categories' => [
            xl('Translate Appointment Categories'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Are appointment category names to be translated?')
        ],

        'units_of_measurement' => [
            xl('Units for Visit Forms'),
            [
                '1' => xl('Show both US and metric (main unit is US)'),
                '2' => xl('Show both US and metric (main unit is metric)'),
                '3' => xl('Show US only'),
                '4' => xl('Show metric only'),
            ],
            '1',                              // default = Both/US
            xl('Applies to the Vitals form and Growth Chart')
        ],

        'us_weight_format' => [
            xl('Display Format for US Weights'),
            [
                '1' => xl('Show pounds as decimal value'),
                '2' => xl('Show pounds and ounces')
            ],
            '1',
            xl('Applies to Vitals form')
        ],

        'phone_country_code' => [
            xl('Telephone Country Code'),
            'num',
            '1',                              // default = North America
            xl('1 = North America. See http://www.wtng.info/ for a list of other country codes.')
        ],

        'date_display_format' => [
            xl('Date Display Format'),
            [
                '0' => xl('YYYY-MM-DD'),
                '1' => xl('MM/DD/YYYY'),
                '2' => xl('DD/MM/YYYY'),
            ],
            '0',
            xl('Format used to display most dates.')
        ],

        'time_display_format' => [
            xl('Time Display Format'),
            [
                '0' => xl('24 hr'),
                '1' => xl('12 hr'),
            ],
            '0',
            xl('Format used to display most times.')
        ],

        'gbl_time_zone' => [
            xl('Time Zone'),
            gblTimeZones(),
            '',
            xl('If unassigned will default to php.ini setting for date.timezone.')
        ],

        'currency_decimals' => [
            xl('Currency Decimal Places'),
            [
                '0' => xl('0'),
                '1' => xl('1'),
                '2' => xl('2'),
            ],
            '2',
            xl('Number of digits after decimal point for currency, usually 0 or 2.')
        ],

        'currency_dec_point' => [
            xl('Currency Decimal Point Symbol'),
            [
                '.' => xl('Period'),
                ',' => xl('Comma'),
            ],
            '.',
            xl('Symbol used as the decimal point for currency. Not used if Decimal Places is 0.')
        ],

        'currency_thousands_sep' => [
            xl('Currency Thousands Separator'),
            [
                ',' => xl('Comma'),
                '.' => xl('Period'),
                ' ' => xl('Space'),
                '' => xl('None'),
            ],
            ',',
            xl('Symbol used to separate thousands for currency.')
        ],

        'gbl_currency_symbol' => [
            xl('Currency Designator'),
            'text',                           // data type
            '$',                              // default
            xl('Code or symbol to indicate currency')
        ],
        'age_display_format' => [xl('Age Display Format'),
            [
                '0' => xl('Years or months'),
                '1' => xl('Years, months and days')
            ],
            '0',
            xl('Format for age display')
        ],
        'age_display_limit' => [
            xl('Age in Years for Display Format Change'),
            'num',
            '3',
            xl('If YMD is selected for age display, switch to just Years when patients older than this value in years')
        ],
        // Reference - https://en.wikipedia.org/wiki/Workweek_and_weekend#Around_the_world
        'weekend_days' => [
            xl('Your weekend days'),
            [
                '6,0' => xl('Saturday') . ' - ' . xl('Sunday'),
                '0' => xl('Sunday'),
                '5' => xl('Friday'),
                '6' => xl('Saturday'),
                '5,6' => xl('Friday') . ' - ' . xl('Saturday'),
            ],
            '6,0'
            , xl('which days are your weekend days?')
        ]

    ],

    // Features Tab
    //
    'Features' => [

        'specific_application' => [
            xl('Specific Application'),
            [
                '0' => xl('None'),
                '2' => xl('IPPF'),
                '3' => xl('Weight loss clinic'),
            ],
            '0',                              // default
            xl('Indicator for specialized usage')
        ],

        GlobalFeaturesEnum::INHOUSE_PHARMACY->value => [
            xl('Drugs and Products'),
            [
                '0' => xl('Do not inventory and sell any products'),
                '1' => xl('Inventory and sell drugs only'),
                '2' => xl('Inventory and sell both drugs and non-drug products'),
                '3' => xl('Products but no prescription drugs and no templates'),
            ],
            '0',                              // default
            xl('Option to support inventory and sales of products')
        ],

        'disable_chart_tracker' => [
            xl('Disable Chart Tracker'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Removes the Chart Tracker feature')
        ],

        'disable_immunizations' => [
            xl('Disable Immunizations'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Removes support for immunizations')
        ],

        'disable_prescriptions' => [
            xl('Disable Prescriptions'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Removes support for prescriptions')
        ],

        'text_templates_enabled' => [
            xl('Enable Text Templates in Encounter Forms'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Allow Double Click to select Nation Note text template from any encounter form text area')
        ],

        'omit_employers' => [
            xl('Omit Employers'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Omit employer information in patient demographics')
        ],

        'select_multi_providers' => [
            xl('Support Multi-Provider Events'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Support calendar events that apply to multiple providers')
        ],

        'disable_non_default_groups' => [
            xl('Disable User Groups'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Normally this should be checked. Not related to access control.')
        ],

        'ignore_pnotes_authorization' => [
            xl('Skip Authorization of Patient Notes'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Do not require patient notes to be authorized')
        ],

        'support_encounter_claims' => [
            xl('Allow Encounter Claims'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Allow creation of claims containing diagnoses but not procedures or charges. Most clinics do not want this.')
        ],

        'advance_directives_warning' => [
            xl('Advance Directives Warning'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Display advance directives in the demographics page.')
        ],

        'configuration_import_export' => [
            xl('Configuration Export/Import'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Support export/import of configuration data via the Backup page.')
        ],

        'restrict_user_facility' => [
            xl('Restrict Users to Facilities'),
            'bool',                           // data type
            '0',                              // default
            xl('Restrict non-authorized users to the Schedule Facilities set in User admin.')
        ],

        'set_facility_cookie' => [
            xl('Remember Selected Facility'),
            'bool',                           // data type
            '0',                              // default
            xl('Set a facility cookie to remember the selected facility between logins.')
        ],

        'login_into_facility' => [
            xl('Login Into Facility'),
            'bool',                           // data type
            '0',                              // default
            xl('Select your current facility in the login page')
        ],

        'receipts_by_provider' => [
            xl('Print Receipts by Provider'),
            'bool',
            '0',                              // default
            xl('Causes Receipts to Print Encounter/Primary Provider Info')
        ],

        'discount_by_money' => [
            xl('Discounts as Monetary Amounts'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Discounts at checkout time are entered as money amounts, as opposed to percentage.')
        ],


        'gbl_form_save_close' => [
            xl('Display Save and Close Visit button in LBFs'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This is helpful if visits usually do not have charges.')
        ],

        'gbl_mask_patient_id' => [
            xl('Mask for Patient IDs'),
            'text',                           // data type
            '',                               // default
            xl('Specifies formatting for the external patient ID.  # = digit, @ = alpha, * = any character.  Empty if not used.')
        ],

        'gbl_mask_invoice_number' => [
            xl('Mask for Invoice Numbers'),
            'text',                           // data type
            '',                               // default
            xl('Specifies formatting for invoice reference numbers.  # = digit, @ = alpha, * = any character.  Empty if not used.')
        ],

        'gbl_mask_product_id' => [
            xl('Mask for Product IDs'),
            'text',                           // data type
            '',                               // default
            xl('Specifies formatting for product NDC fields.  # = digit, @ = alpha, * = any character.  Empty if not used.')
        ],

        'activate_ccr_ccd_report' => [
            xl('Activate CCR/CCD Reporting'),
            'bool',                           // data type
            '1',                              // default = true
            xl('This will activate the CCR(Continuity of Care Record) and CCD(Continuity of Care Document) reporting.')
        ],

        'drive_encryption' => [
            xl('Enable Encryption of Items Stored on Drive (Strongly recommend keeping this on)'),
            'bool',                           // data type
            '1',                              // default = true
            xl('This will enable encryption of items that are stored on the drive. Strongly recommend keeping this setting on for security purposes.')
        ],

        'couchdb_encryption' => [
            xl('Enable Encryption of Items Stored on CouchDB'),
            'bool',                           // data type
            '1',                              // default = true
            xl('This will enable encryption of items that are stored on CouchDB.')
        ],

        'hide_document_encryption' => [
            xl('Hide Encryption/Decryption Options In Document Management'),
            'bool',                           // data type
            '0',                              // default = true
            xl('This will deactivate document the encryption and decryption features, and hide them in the UI.')
        ],

        'use_custom_immun_list' => [
            xl('Use Custom Immunization List'),
            'bool',                           // data type
            '0',                              // default = true
            xl('This will use the custom immunizations list rather than the standard CVX immunization list.')
        ],

        'amendments' => [
            xl('Amendments'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Enable amendments feature')
        ],

        'allow_pat_delete' => [
            xl('Allow Administrators to Delete Patients'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Allow Administrators to Delete Patients')

        ],

        'observation_results_immunization' => [
            xl('Immunization Observation Results'),
            'bool',                           // data type
            '1',                              // default
            xl('Observation Results in Immunization')
        ],

        'enable_help' => [
            xl('Enable Help Modal'),
            [
                '0' => xl('Hide Help Modal'),
                '1' => xl('Show Help Modal'),
                '2' => xl('Disable Help Modal'),
            ],                       // data type
            '1',                     // default = Print End of Day Report 1
            xl('This will allow the display of help modal on help enabled pages')
        ],
        'messages_due_date' => [
            xl('Messages - due date'),
            'bool',                           // data type
            '0',                              // default false
            xl('Enables choose due date to message')
        ],

        'expand_form' => [
            xl('Expand Form'),
            'bool',                           // data type
            '1',                              // default true
            xl('Open all expandable forms in expanded state')
        ],

        'graph_data_warning' => [
            xl('Graphing Data Warning'),
            'bool',                           // data type
            '0',                              // default false
            xl('Warn if not enough data to graph')
        ],

    ],
    // Report Tab
    //
    'Report' => [

        'use_custom_daysheet' => [
            xl('Use Custom End of Day Report'),
            [
                '0' => xl('None'),
                '1' => xl('Print End of Day Report 1'),
                '2' => xl('Print End of Day Report 2'),
                '3' => xl('Print End of Day Report 3'),
            ],                       // data type
            '1',                     // default = Print End of Day Report 1
            xl('This will allow the use of the custom End of Day report and indicate which report to use.')
        ],

        'daysheet_provider_totals' => [
            xl('End of Day by Provider or allow Totals Only'),
            [
                '0' => xl('Provider'),
                '1' => xl('Totals Only'),
            ],
            '1',                              // default
            xl('This specifies the Printing of the Custom End of Day Report grouped Provider or allow the Printing of Totals Only')
        ],

        'ledger_begin_date' => [
            xl('Beginning Date for Ledger Report'),
            [
                'Y1' => xl('One Year Ago'),
                'Y2' => xl('Two Years Ago'),
                'M6' => xl('Six Months Ago'),
                'M3' => xl('Three Months Ago'),
                'M1' => xl('One Month Ago'),
                'D1' => xl('One Day Ago'),
            ],
            'Y1',                     // default = One Year
            xl('This is the Beginning date for the Ledger Report.')
        ],

        'print_next_appointment_on_ledger' => [
            xl('Print the Next Appointment on the Bottom of the Ledger'),
            'bool',                           // data type
            '1',                              // default = true
            xl('This Will Print the Next Appointment on the Bottom of the Patient Ledger')
        ],

        'sales_report_invoice' => [
            xl('Display Invoice Number or Patient Name or Both in the Sales Report'),
            [
                '0' => xl('Invoice Number'),
                '1' => xl('Patient Name and ID'),
                '2' => xl('Patient Name and Invoice'),
            ],
            '2',                              // default = 2
            xl('This will Display the Invoice Number in the Sales Report or the Patient Name and ID or Patient Name and Invoice Number.')
        ],

        'cash_receipts_report_invoice' => [
            xl('Display Invoice Number or Patient Name in the Cash Receipt Report'),
            [
                '0' => xl('Invoice Number'),
                '1' => xl('Patient Name'),
            ],
            '0',                              // default = 0
            xl('Display Invoice Number or Patient Name in the Cash Receipt Report')
        ],

    ],

    // Billing Tab

    'Billing' => [

        // It would be good to eventually rename this to "billing_enabled" and inverse the setting value.
        'hide_billing_widget' => [
            xl('Hide Billing features'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will hide billing features throughout the program.')
        ],

        'force_billing_widget_open' => [
            xl('Force Billing Widget Open'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will force the Billing Widget in the Patient Summary screen to always be open.')
        ],


        'ub04_support' => [
            xl('Activate UB04/837I Claim Support'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Allow institutional claims support.')
        ],

        'top_ubmargin_default' => [
            xl('Default top print margin for UB04'),
            'num', // data type
            '14', // default
            xl('This is the default top print margin for UB04. It will adjust the final printed output up or down.')
        ],

        'left_ubmargin_default' => [
            xl('Default left print margin for UB04'),
            'num', // data type
            '11', // default
            xl('This is the default left print margin for UB04. It will adjust the final printed output left or right.')
        ],

        'cms_top_margin_default' => [
            xl('Default top print margin for CMS 1500'),
            'num', // data type
            '24', // default
            xl('This is the default top print margin for CMS 1500. It will adjust the final printed output up or down.')
        ],

        'cms_left_margin_default' => [
            xl('Default left print margin for CMS 1500'),
            'num', // data type
            '20', // default
            xl('This is the default left print margin for CMS 1500. It will adjust the final printed output left or right.')
        ],

        'preprinted_cms_1500' => [
            xl('Prints the CMS 1500 on the Preprinted form'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Overlay CMS 1500 on the Preprinted form')
        ],

        'cms_1500_box_31_format' => [
            xl('CMS 1500: Box 31 Format'),
            [
                '0' => xl('Signature on File'),
                '1' => xl('Firstname Lastname'),
                '2' => xl('None'),
            ],
            '0',                              // default
            xl('This specifies whether to include date in Box 31.')
        ],

        'cms_1500_box_31_date' => [
            xl('CMS 1500: Date in Box 31 (Signature)'),
            [
                '0' => xl('None'),
                '1' => xl('Date of Service'),
                '2' => xl('Today'),
            ],
            '0',                              // default
            xl('This specifies whether to include date in Box 31.')
        ],

        'default_search_code_type' => [
            xl('Default Search Code Type'),
            'all_code_types',  // data type
            'ICD10',                 // default
            xl('The default code type to search for in the Fee Sheet.')
        ],

        'default_rendering_provider' => [
            xl('Default Rendering Provider in Fee Sheet'),
            [
                '0' => xl('Logged in User if provider, otherwise Current Provider'),
                '1' => xl('Current Provider'),
                '2' => xl('Current Logged in User'),
            ],
            '1',
            xl('Default selection for rendering provider in fee sheet.')
        ],

        'posting_adj_disable' => [
            xl('Disable Auto Adjustment Calculations in EOB Posting'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Turn off auto calculations of adjustments in EOB')
        ],

        'force_claim_balancing' => [
            xl('Force claim balancing in EOB Posting'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Force claim balancing in EOB Posting')
        ],

        'show_payment_history' => [
            xl('Show all payment history in Patient Ledger'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Turn on to show all payment history in Patient Ledger')
        ],

        'void_checkout_reopen' => [
            xl('Void Checkout and Reopen in Fee Sheet'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Void Checkout and Reopen in Fee Sheet')
        ],

        'support_fee_sheet_line_item_provider' => [
            xl('Support provider in line item in fee sheet'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This Enables provider in line item in the fee sheet')
        ],

        'default_fee_sheet_line_item_provider' => [
            xl('Default to a provider for line item in the fee sheet'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Default to a provider for line item in the fee sheet.(only applicable if Support line item billing in option above)')
        ],

        'include_inactive_providers' => [
            xl('Include inactive providers in the fee sheet'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Include inactive providers in the fee sheet.')
        ],

        'replicate_justification' => [
            xl('Automatically replicate justification codes in Fee Sheet'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Automatically replicate justification codes in Fee Sheet (basically fills in the blanks with the justification code above it).')
        ],

        'display_units_in_billing' => [
            xl('Display the Units Column on the Billing Screen'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Display the Units Column on the Billing Screen')
        ],

        'notes_to_display_in_Billing' => [
            xl('Which notes are to be displayed in the Billing Screen'),
            [
                '0' => xl('None'),
                '1' => xl('Encounter Billing Note'),
                '2' => xl('Patient Billing Note'),
                '3' => xl('All'),
            ],
            '3',
            xl('Display the Encounter Billing Note or Patient Billing Note or Both in the Billing Screen.')
        ],

        'MedicareReferrerIsRenderer' => [
            xl('Medicare Referrer Is Renderer'),
            'bool',                           // data type
            '0',                              // default = false
            xl('For Medicare only, forces the referring provider to be the same as the rendering provider.')
        ],

        'statement_logo' => [
            xl('Statement Logo GIF Filename'),
            'text',                           // data type
            'practice_logo.gif',                               // data type
            xl('Place your logo in sites/default/images and type the filename including gif extension here.')
        ],

        'use_custom_statement' => [
            xl('Use Custom Statement'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will use the custom Statement showing the description instead of the codes.')
        ],

        'statement_appearance' => [
            xl('Statement Appearance'),
            [
                '0' => xl('Plain Text'),
                '1' => xl('Modern/images')
            ],                          // data type
            '1',                              // default = true
            xl('Patient statements can be generated as plain text or with a modern graphical appearance.')
        ],

        'billing_phone_number' => [
            xl('Custom Billing Phone Number'),
            'text',                           // data type
            '',
            xl('Phone number for billing inquiries')
        ],

        'show_aging_on_custom_statement' => [
            xl('Show Aging on Custom Statement'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will Show Aging on the custom Statement.')
        ],

        'use_statement_print_exclusion' => [
            xl('Allow Statement Exclusions from Printing'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will enable the Ability to Exclude Selected Patient Statements from Printing.')
        ],

        'minimum_amount_to_print' => [
            xl('Total Minimum Amount of Statement to Allow Printing'),
            'num',                           // data type
            '1.00',
            xl('Total Minimum Dollar Amount of Statement to Allow Printing.(only applicable if Allow Statement Exclusions from Printing is enabled)')
        ],

        'statement_bill_note_print' => [
            xl('Print Patient Billing Note'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will allow printing of the Patient Billing Note on the statements.')
        ],

        'number_appointments_on_statement' => [
            xl('Number of Appointments on Statement'),
            'num',                           // data type
            '0',                              // default = 0
            xl('The Number of Future Appointments to Display on the Statement.')
        ],

        'statement_message_to_patient' => [
            xl('Print Custom Message'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will allow printing of a custom Message on the statements.')
        ],

        'statement_msg_text' => [
            xl('Custom Statement message'),
            'text',                           // data type
            '',
            xl('Text for Custom statement message.')
        ],

        'use_dunning_message' => [
            xl('Use Custom Dunning Messages'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will allow use of the custom Dunning Messages on the statements.')
        ],

        'first_dun_msg_set' => [
            xl('Number of days before showing first account message'),
            'num',                           // data type
            '30',
            xl('Number of days before showing first account message.')
        ],

        'first_dun_msg_text' => [
            xl('First account message'),
            'text',                           // data type
            '',
            xl('Text for first account message.')
        ],

        'second_dun_msg_set' => [
            xl('Number of days before showing second account message'),
            'num',                           // data type
            '60',
            xl('Number of days before showing second account message')
        ],

        'second_dun_msg_text' => [
            xl('Second account message'),
            'text',                           // data type
            '',
            xl('Text for second account message.')
        ],

        'third_dun_msg_set' => [
            xl('Number of days before showing third account message'),
            'num',                           // data type
            '90',
            xl('Number of days before showing third account message')
        ],

        'third_dun_msg_text' => [
            xl('Third account message'),
            'text',                           // data type
            '',
            xl('Text for third account message.')
        ],

        'fourth_dun_msg_set' => [
            xl('Number of days before showing fourth account message'),
            'num',                           // data type
            '120',
            xl('Number of days before showing fourth account message')
        ],

        'fourth_dun_msg_text' => [
            xl('Fourth account message'),
            'text',                           // data type
            '',
            xl('Text for fourth account message.')
        ],

        'fifth_dun_msg_set' => [
            xl('Number of days before showing fifth account message'),
            'num',                           // data type
            '150',
            xl('Number of days before showing fifth account message')
        ],

        'fifth_dun_msg_text' => [
            xl('Fifth account message'),
            'text',                           // data type
            '',
            xl('Text for fifth account message.')
        ],

        'save_codes_history' => [
            xl('Save codes history'),
            'bool',                           // data type
            '1',                              // default
            xl('Save codes history')
        ],

        'enable_percent_pricing' => [
            xl('Enable percent-based price levels'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable percent-based price levels')
        ],

        'gen_x12_based_on_ins_co' => [
            xl('Generate X-12 Based On Insurance Company'),
            'bool',                           // data type
            '0',                              // default = false
            xl('For sending claims directly to insurance company, based on X12 Partner Settings')
        ],

        'auto_sftp_claims_to_x12_partner' => [
            xl('Automatically SFTP Claims To X12 Partner'),
            'bool',                           // data type
            '0',                              // default = false
            xl('For automatically sending claims that are generated in EDI directory to the X12 partner using SFTP credentials X12 Partner Settings')
        ],

        'enable_swap_secondary_insurance' => [
            xl('Enable Swap Secondary Insurance Editing Demographics'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable swap secondary insurance')
        ],

        'add_unmatched_code_from_ins_co_era_to_billing' => [
            xl('Enable adding unmatched code from insurance company to billing table'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable adding unmatched code from insurance company to billing table')
        ],

    ],

    // E-Sign Tab
    //
    'E-Sign' => [

        'esign_all' => [
            xl('Allows E-Sign on the entire encounter'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will enable signing an entire encounter, rather than individual forms')
        ],

        'lock_esign_all' => [
            xl('Lock e-signed encounters and their forms'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will disable the Edit button on all forms whose parent encounter is e-signed')
        ],

        'esign_individual' => [
            xl('Allows E-Signing Individual Forms'),
            'bool',                           // data type
            '1',                              // default = false
            xl('This will enable signing individual forms separately')
        ],

        'lock_esign_individual' => [
            xl('Lock an e-signed form individually'),
            'bool',                           // data type
            '1',                              // default = false
            xl('This will disable the Edit button on any form that is e-signed')
        ],

        'esign_lock_toggle' => [
            xl('Enable lock toggle'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will give the user the option to lock (separate locking and signing)')
        ],

        'esign_report_show_only_signed' => [
            xl('Only Include E-Signed Forms On Report'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will hide any encounter forms not E-Signed on the patient report')
        ],

        'esign_report_hide_empty_sig' => [
            xl('Hide Empty E-Sign Logs On Report'),
            'bool',                           // data type
            '1',                              // default = false
            xl('This will hide empty e-sign logs on the patient report')
        ],

        'esign_report_hide_all_sig' => [
            xl('Exclude All E-Sign Logs On Report'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This will hide any e-sign logs on the patient report')
        ],
    ],
    //Documents Tab
    'Documents' => [

        'document_storage_method' => [
            xl('Document Storage Method'),
            [
                '0' => xl('Hard Disk'),
                '1' => xl('CouchDB')
            ],
            '0',                              // default
            xl('Option to save method of document storage.')
        ],

        'couchdb_host' => [
            xl('CouchDB HostName'),
            'text',
            'localhost',
            xl('CouchDB host'),
        ],
        'couchdb_user' => [
            xl('CouchDB UserName'),
            'text',
            '',
            xl('Username to connect to CouchDB'),
        ],
        'couchdb_pass' => [
            xl('CouchDB Password'),
            'encrypted',                     // data type
            '',
            xl('Password to connect to CouchDB'),
        ],
        'couchdb_port' => [
            xl('CouchDB Port'),
            'text',
            '6984',
            xl('CouchDB port'),
        ],
        'couchdb_dbase' => [
            xl('CouchDB Database'),
            'text',
            '',
            xl('CouchDB database name'),
        ],
        'couchdb_connection_ssl' => [
            xl('CouchDB Connection SSL'),
            'bool',
            '1',
            xl('Use SSL (encrypted) connection to CouchDB'),
        ],
        'couchdb_ssl_allow_selfsigned' => [
            xl('CouchDB SSL Allow Selfsigned Certificate'),
            'bool',
            '0',
            xl('Allow self-signed certificate for SSL (encrypted) connection to CouchDB'),
        ],
        'couchdb_log' => [
            xl('CouchDB Log Enable'),
            'bool',
            '0',
            xl('Enable log for document uploads/downloads to CouchDB'),
        ],

        'expand_document_tree' => [
            xl('Expand All Document Categories'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Expand All Document Categories by Default')
        ],

        'patient_id_category_name' => [
            xl('Patient ID Category Name'),
            'text',                           // data type
            'Patient ID card',                // default
            xl('Optional category name for an ID Card image that can be viewed from the patient summary page.')
        ],

        'patient_photo_category_name' => [
            xl('Patient Photo Category Name'),
            'text',                           // data type
            'Patient Photograph',             // default
            xl('Optional category name for photo images that can be viewed from the patient summary page.')
        ],

        'lab_results_category_name' => [
            xl('Lab Results Category Name'),
            'text',                           // data type
            'Lab Report',                     // default
            xl('Document category name for storage of electronically received lab results.')
        ],

        'gbl_mdm_category_name' => [
            xl('MDM Document Category Name'),
            'text',                           // data type
            'Lab Report',                     // default
            xl('Document category name for storage of electronically received MDM documents.')
        ],
        'generate_doc_thumb' => [
            xl('Generate thumbnail'),
            'bool',
            '0',
            xl('Generate thumbnail images'),
        ],
        'thumb_doc_max_size' => [
            xl('Thumbnail size'),
            'text',          // data type
            '100',           // default
            xl('Maximum size of thumbnail file')
        ],
    ],

    // Calendar Tab
    //
    'Calendar' => [

        'disable_calendar' => [
            xl('Disable Calendar'),
            'bool',                           // data type
            '0',                              // default
            xl('Do not display the calendar.')
        ],

        'schedule_start' => [
            xl('Calendar Starting Hour'),
            'hour',
            '8',                              // default
            xl('Beginning hour of day for calendar events.')
        ],

        'schedule_end' => [
            xl('Calendar Ending Hour'),
            'hour',
            '17',                             // default
            xl('Ending hour of day for calendar events.')
        ],

        'calendar_interval' => [
            xl('Calendar Interval'),
            [
                '5' => '5',
                '10' => '10',
                '15' => '15',
                '20' => '20',
                '30' => '30',
                '60' => '60',
            ],
            '15',                              // default
            xl('The time granularity of the calendar and the smallest interval in minutes for an appointment slot.')
        ],

        'calendar_view_type' => [
            xl('Default Calendar View'),
            [
                'day' => xl('Day'),
                'week' => xl('Week'),
                'month' => xl('Month'),
            ],
            'day',                              // default
            xl('This sets the Default Calendar View, Default is Day.')
        ],

        'first_day_week' => [
            xl('First day in the week'),
            [
                '1' => xl('Monday'),
                '0' => xl('Sunday'),
                '6' => xl('Saturday')
            ],
            '1',
            xl('Your first day of the week.')
        ],

        'calendar_appt_style' => [
            xl('Appointment Display Style'),
            [
                '1' => xl('Last name'),
                '2' => xl('Last name, first name'),
                '3' => xl('Last name, first name (title)'),
                '4' => xl('Last name, first name (title: comments)'),
                '5' => xl('Last name, first name, address (title: comments)')
            ],
            '2',                               // default
            xl('This determines how appointments display on the calendar.')
        ],

        'event_color' => [
            xl('Appointment/Event Color'),
            [
                '1' => xl('Category Color Schema'),
                '2' => xl('Facility Color Schema'),
            ],                           // data type
            '1',                              // default
            xl('This determines which color schema used for appointment')
        ],

        'number_of_appts_to_show' => [
            xl('Appointments - Patient Summary - Number to Display'),
            'num',
            '10',
            xl('Number of Appointments to display in the Patient Summary')
        ],

        'number_of_group_appts_to_show' => [
            xl('Appointments - Group Summary - Number to Display'),
            'num',
            '10',
            xl('Number of Appointments to display in the Group Summary')
        ],

        'number_of_ex_appts_to_show' => [
            xl('Excluded Appointments - Tooltip - Number to Display'),
            'num',
            '15',
            xl('Number of Excluded Appointments to display in the Tooltip')
        ],

        'appt_display_sets_option' => [
            xl('Appointment Display Sets - Ignore Display Limit (Last Set)'),
            'bool',                           // data type
            '1',                              // default
            xl('Override (if necessary) the appointment display limit to allow all appointments to be displayed for the last set')
        ],

        'appt_display_sets_color_1' => [
            xl('Appointment Display Sets - Color 1'),
            'color_code',
            '#FFFFFF',
            xl('Color for odd sets (except when last set is odd and all member appointments are displayed and at least one subsequent scheduled appointment exists (not displayed) or not all member appointments are displayed).')
        ],

        'appt_display_sets_color_2' => [
            xl('Appointment Display Sets - Color 2'),
            'color_code',
            '#E6E6FF',
            xl('Color for even sets (except when last set is even and all member appointments are displayed and at least one subsequent scheduled appointment exists (not displayed) or not all member appointments are displayed).')
        ],

        'appt_display_sets_color_3' => [
            xl('Appointment Display Sets - Color 3'),
            'color_code',
            '#E6FFE6',
            xl('Color for the last set when all member appointments are displayed and at least one subsequent scheduled appointment exists (not displayed).')
        ],

        'appt_display_sets_color_4' => [
            xl('Appointment Display Sets - Color 4'),
            'color_code',
            '#FFE6FF',
            xl('Color for the last set when not all member appointments are displayed.')
        ],

        'appt_recurrences_widget' => [
            xl('Recurrent Appointment Display Widget'),
            'bool',                           // data type
            '1',                              // default
            xl('Display the recurrent appointment widget in the patient summary.')
        ],

        'num_past_appointments_to_show' => [
            xl('Past Appointment Display Widget'),
            'num',                           // data type
            '0',                             // default = false
            xl('A positive number will show that many past appointments on a Widget in the Patient Summary screen (a negative number will show the past appointments in descending order)')
        ],

        'docs_see_entire_calendar' => [
            xl('Providers See Entire Calendar'),
            'bool',                           // data type
            '0',                              // default
            xl('Check this if you want providers to see all appointments by default and not just their own.')
        ],

        'auto_create_new_encounters' => [
            xl('Auto-Create New Encounters'),
            [
                '0' => xl('Off'),
                '1' => xl('One Encounter Per Day'),
                '2' => xl('Allow Encounter For Each Appointment')
            ],
            '1',
            xl('Automatically create a new encounter when an appointment check in status is selected.') . " " .
            xl('The Each Appointment option will allow a new encounter regardless of same day visit.') . " " .
            xl('The appointment status changes and encounter creations are managed through the Patient Tracker.')
        ],

        'auto_create_prevent_reason' => [
            xl('Prevent Appointment Comment as Visit Reason'),
            'bool',                           // data type
            '0',                              // default
            xl('Check this if you do not want to allow the appointments comment as the visit reason when auto-creating new encounter for appointment.')
        ],

        'allow_early_check_in' => [
            xl('Allow Early Check In'),
            'bool',                           // data type
            '1',                              // default
            xl("Allow Check In before the appointment's time.")
        ],

        'submit_changes_for_all_appts_at_once' => [
            xl('Submit Changes For All Appts At Once'),
            'bool',                           // data type
            '1',                              // default
            xl('Enables to submit changes for all appointments of a recurrence at once.')
        ],

        'disable_pat_trkr' => [
            xl('Flow Board: Disable'),
            'bool',                           // data type
            '0',                              // default
            xl('Completely remove the ability to display the Patient Flow Board.')
        ],

        'ptkr_visit_reason' => [
            xl('Flow Board: Show Visit Reason'),
            'bool',                           // data type
            '0',                              // default = false
            xl('When Checked, Visit Reason Will Show in Patient Flow Board.')
        ],

        'ptkr_show_pid' => [
            xl('Flow Board: Show Patient ID'),
            'bool',                           // data type
            '1',                              // default = true
            xl('When Checked, Patient ID Will Show in Patient Flow Board.')
        ],

        'ptkr_show_encounter' => [
            xl('Flow Board: Show Encounter Number'),
            'bool',                           // data type
            '1',                              // default = true
            xl('When Checked, Patient Encounter Number Will Show in Patient Flow Board.')
        ],

        'ptkr_show_staff' => [
            xl('Flow Board: Show Staff Action'),
            'bool',                           // data type
            '1',                              // default = true
            xl('When Checked, Last Staff to Update Board Will Show in Patient Flow Board.')
        ],

        'ptkr_date_range' => [
            xl('Flow Board: Allow Date Range'),
            'bool',                          // data type
            '1',                             // default = true
            xl('This Allows a Date Range to be Selected in Patient Flow Board.')
        ],

        'ptkr_start_date' => [
            xl('Flow Board: Default Starting Date'),
            [
                'D0' => xl('Current Day'),
                'B0' => xl('Beginning of Current Work Week'),
            ],
            'D0',                    // default = Current Day
            xl('This is the default Beginning date for the Patient Flow Board. (only applicable if Allow Date Range in option above is Enabled)')
        ],

        'ptkr_end_date' => [
            xl('Flow Board: Default Ending Date'),
            [
                'Y1' => xl('One Year Ahead'),
                'Y2' => xl('Two Years Ahead'),
                'M6' => xl('Six Months Ahead'),
                'M3' => xl('Three Months Ahead'),
                'M1' => xl('One Month Ahead'),
                'D7' => xl('One Week Ahead'),
                'D1' => xl('One Day Ahead'),
                'D0' => xl('Current Day'),
            ],
            'D0',                     // default = One Day Ahead
            xl('This is the default Ending date for the Patient Flow Board. (only applicable if Allow Date Range in option above is Enabled)')
        ],

        'pat_trkr_timer' => [
            xl('Flow Board: Timer Refresh Interval'),
            [
                '0' => xl('No automatic refresh'),
                '0:10' => '10',
                '0:20' => '20',
                '0:30' => '30',
                '0:40' => '40',
                '0:50' => '50',
                '0:59' => '60',
            ],
            '0:20',                              // default
            xl('The screen refresh time in Seconds for the Patient Flow Board Screen.')
        ],

        'checkout_roll_off' => [
            xl('Flow Board: display completed checkouts (minutes)'),
            'num',
            '0',                       // default
            xl('Flow Board will only display completed checkouts for this many minutes. Zero is continuous display.')
        ],

        'drug_screen' => [
            xl('Flow Board: Enable Random Drug Testing'),
            'bool',                           // data type
            '0',                              // default
            xl('Allow Patient Flow Board to Select Patients for Drug Testing.')
        ],

        'drug_testing_percentage' => [
            xl('Flow Board: Percentage of Patients to Drug Test'),
            'num',
            '33',                       // default
            xl('Percentage of Patients to select for Random Drug Testing.')
        ],

        'maximum_drug_test_yearly' => [
            xl('Flow Board: Max tests per Patient per year'),
            'num',
            '0',                       // default
            xl('Maximum number of times a Patient can be tested in a year. Zero is no limit.')
        ],

        'disable_rcb' => [
            xl('Recall Board: Disable'),
            'bool',                           // data type
            '0',                              // default
            xl('Do not display the Recall Board.')
        ],


    ],
    // Insurance Tab
    'Insurance' => [
        'enable_eligibility_requests' => [
            xl('Enable Insurance Eligibility'),
            'bool',
            '0',
            xl('Allow insurance eligibility checks using an X12 Partner')
        ],

        'simplified_demographics' => [
            xl('Simplified Demographics'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Omit insurance and some other things from the demographics form')
        ],

        'insurance_information' => [
            xl('Show Additional Insurance Information'),               // descriptive name
            [
                '0' => xl('None'),
                '1' => xl('Address Only'),
                '2' => xl('Address and Postal Code'),
                '3' => xl('Address and State'),
                '4' => xl('Address, State and Postal Code'),
                '5' => xl('Address, City, State and Postal Code'),
                '6' => xl('Address, City, State, Postal Code, Payer ID'),
                '7' => xl('Postal Code and Box Number')
            ],
            '6',                              // default
            xl('Show Insurance Address Information in the Insurance Panel of Demographics.')
        ],

        'disable_eligibility_log' => [
            xl('Disable Insurance Eligibility Reports Download'),
            'bool',
            '0',
            xl('Do not allow insurance eligibility report log download')
        ],

        'insurance_only_one' => [
            xl('Allow only one insurance'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Allow more than one insurance')
        ],
    ],
    // Security Tab
    //
    'Security' => [
        'sql_string_no_show_screen' => [
            xl('Mode - Do Not Show SQL Queries'),
            'bool',                           // data type
            '0',                              // default
            xl('Do not allow SQL queries to be outputted to screen.')
        ],
        'timeout' => [
            xl('Idle Session Timeout Seconds'),
            'num',                            // data type
            '7200',                           // default
            xl('Maximum idle time in seconds before logout. Default is 7200 (2 hours).')
        ],
        'portal_timeout' => [
            xl('Portal Idle Session Timeout Seconds'),
            'num',                            // data type
            '1800',                           // default
            xl('Maximum idle time in seconds before logout. Default is 1800 (30 minutes).')
        ],
        'secure_upload' => [
            xl('Secure Upload Files with White List'),
            'bool',                           // data type
            '1',                              // default
            xl('Block all files types that are not found in the White List. Can find interface to edit the White List at Administration->Files.')
        ],
        'secure_password' => [
            xl('Require Strong Passwords'),
            'bool',                           // data type
            '1',                              // default
            xl('Strong password means at least one of each: a number, a lowercase letter, an uppercase letter, a special character.')
        ],

        'gbl_minimum_password_length' => [
            xl('Minimum Password Length'),
            [
                '0' => xl('No Minimum'),
                '4' => '4',
                '5' => '5',
                '6' => '6',
                '7' => '7',
                '8' => '8',
                '9' => '9',
                '10' => '10',
                '11' => '11',
                '12' => '12',
                '13' => '13',
                '14' => '14',
                '15' => '15',
                '16' => '16',
                '17' => '17',
                '18' => '18',
                '19' => '19',
                '20' => '20',
            ],
            '9',                              // default
            xl('Minimum length of password.')
        ],

        'gbl_maximum_password_length' => [
            xl('Maximum Password Length'),
            [
                '0' => xl('No Maximum'),
                '72' => '72',
            ],
            '72',                             // default
            xl('Maximum length of password (Recommend using the default value of 72 unless you know what you are doing).')
        ],

        'password_history' => [
            xl('Require Unique Passwords'),
            [
                '0' => xl('No'),
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
            ],
            '5',                              // default
            xl('Set to the number of prior passwords that are not allowed to use when changing a password.')
        ],

        'password_expiration_days' => [
            xl('Default Password Expiration Days'),
            'num',                            // data type
            '180',                            // default
            xl('Default password expiration period in days. 0 means this feature is disabled.')
        ],

        'password_grace_time' => [
            xl('Password Expiration Grace Period'),
            'num',                            // data type
            '30',                             // default
            xl('Period in days where a user may login with an expired password.')
        ],

        'password_max_failed_logins' => [
            xl('Maximum Failed Login Attempts For User'),
            'num',                            // data type
            '20',                             // default
            xl('Maximum Failed Login Attempts For User (0 for no maximum).')
        ],

        'time_reset_password_max_failed_logins' => [
            xl('Time (seconds) to Reset Maximum Failed Login Attempts For User'),
            'num',                            // data type
            '3600',                           // default to 1 hour
            xl('Time (seconds) to Reset Maximum Failed Login Attempts Counter For User (0 for no reset).')
        ],

        'ip_max_failed_logins' => [
            xl('Maximum Failed Login Attempts From IP Address'),
            'num',                            // data type
            '100',                            // default
            xl('Maximum Failed Login Attempts From IP Address (0 for no maximum).')
        ],

        'ip_time_reset_password_max_failed_logins' => [
            xl('Time (seconds) to Reset Maximum Failed Login Attempts From IP Address'),
            'num',                            // data type
            '3600',                           // default to 1 hour
            xl('Time (seconds) to Reset Maximum Failed Login Attempts Counter From IP Address (0 for no reset).')
        ],

        'gbl_fac_warehouse_restrictions' => [
            xl('Enable Facility/Warehouse Permissions'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable facility/warehouse restrictions in the user administration form.')
        ],

        'is_client_ssl_enabled' => [
            xl('Enable Client SSL'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable client SSL certificate authentication.')
        ],

        'certificate_authority_crt' => [
            xl('Path to CA Certificate File'),
            'text',                           // data type
            '',                               // default
            xl('Set this to the full absolute path. For creating client SSL certificates for HTTPS.')
        ],

        'certificate_authority_key' => [
            xl('Path to CA Key File'),
            'text',                           // data type
            '',                               // default
            xl('Set this to the full absolute path. For creating client SSL certificates for HTTPS.')
        ],

        'client_certificate_valid_in_days' => [
            xl('Client Certificate Expiration Days'),
            'num',                            // data type
            '365',                            // default
            xl('Number of days that the client certificate is valid.')
        ],

        'Emergency_Login_email_id' => [
            xl('Emergency Login Email Address'),
            'text',                           // data type
            '',                               // default
            xl('Email address, if any, to receive emergency login user activation messages.')
        ],

        'new_validate' => [
            xl('New form validation'),
            'bool',
            '1',
            xl('New form validation')
        ],

        'allow_multiple_databases' => [
            xl('Allow multiple databases'),
            'bool',
            '0',
            xl('Allow to use with multiple database')
        ],

        'safe_key_database' => [
            xl('Safe key database'),
            'text',                           // data type
            '',                               // default
            xl('Key for multiple database credentials encryption')
        ],

        'google_signin_enabled' => [
            xl('Enable Google Sign-In'),
            'bool',
            '0',
            xl('Enable Authentication Using Google Sign-in')
        ],

        'google_signin_client_id' => [
            xl('Google Sign-In Client ID'),
            'text',
            '',
            xl('This Client ID Is Provided By Google For Your App (Required For Google Sign-in)')
        ],

        'gbl_ldap_enabled' => [
            xl('Use LDAP for Authentication'),
            'bool',
            '0',
            xl('If enabled, use LDAP for login and authentication.')
        ],
        'gbl_ldap_host' => [
            xl('LDAP - Server Name or URI'),
            'text',
            '',
            xl('The hostname or URI of your LDAP or Active Directory server.')
        ],
        'gbl_ldap_dn' => [
            xl('LDAP - Distinguished Name of User'),
            'text',
            '',
            xl('Embed {login} where the OpenEMR login name of the user is to be; for example: uid={login},dc=example,dc=com')
        ],
        'gbl_ldap_exclusions' => [
            xl('LDAP - Login Exclusions'),
            'text',
            '',
            xl('Comma-separated list of login names to use normal authentication instead of LDAP; useful for setup and debugging.')
        ],

        'gbl_debug_hash_verify_execution_time' => [
            xl('Debug Hash Verification Time'),
            'bool',
            '0',
            xl('If enabled, this will send the execution time it took to verify hash to the php error log.')
        ],

        'gbl_auth_hash_algo' => [
            xl('Hash Algorithm for Authentication'),
            [
                'DEFAULT' => xl('PHP Default'),
                'BCRYPT' => 'Bcrypt',
                'ARGON2I' => 'Argon2I',
                'ARGON2ID' => 'Argon2ID',
                'SHA512HASH' => 'SHA512 (ONC 2015)',
            ],
            'DEFAULT',                // default
            xl('Hashing algorithm for authentication. Suggest PHP Default unless you know what you are doing.')
        ],

        'gbl_auth_bcrypt_hash_cost' => [
            xl('Authentication Bcrypt Hash Cost'),
            [
                'DEFAULT' => xl('PHP Default'),
                '5' => '5',
                '6' => '6',
                '7' => '7',
                '8' => '8',
                '9' => '9',
                '10' => '10',
                '11' => '11',
                '12' => '12',
                '13' => '13',
                '14' => '14',
                '15' => '15',
                '16' => '16',
                '17' => '17',
                '18' => '18',
                '19' => '19',
                '20' => '20',
            ],
            'DEFAULT',                // default
            xl('Authentication bcrypt hash cost. Suggest PHP Default unless you know what you are doing.')
        ],

        'gbl_auth_argon_hash_memory_cost' => [
            xl('Authentication Argon Hash Memory Cost'),
            [
                'DEFAULT' => xl('PHP Default'),
                '512' => '512',
                '1024' => '1024',
                '2048' => '2048',
                '4096' => '4096',
                '8192' => '8192',
                '16384' => '16384',
                '32768' => '32768',
                '65536' => '65536',
                '131072' => '131072',
                '262144' => '262144',
                '524288' => '524288',
                '1048576' => '1048576',
                '2097152' => '2097152',
            ],
            'DEFAULT',                // default
            xl('Authentication argon hash memory cost. Suggest PHP Default unless you know what you are doing.')
        ],

        'gbl_auth_argon_hash_time_cost' => [
            xl('Authentication Argon Hash Time Cost'),
            [
                'DEFAULT' => xl('PHP Default'),
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
                '7' => '7',
                '8' => '8',
                '9' => '9',
                '10' => '10',
                '11' => '11',
                '12' => '12',
                '13' => '13',
                '14' => '14',
                '15' => '15',
                '16' => '16',
                '17' => '17',
                '18' => '18',
                '19' => '19',
                '20' => '20',
            ],
            'DEFAULT',                // default
            xl('Authentication argon hash time cost. Suggest PHP Default unless you know what you are doing.')
        ],

        'gbl_auth_argon_hash_thread_cost' => [
            xl('Authentication Argon Hash Thread Number'),
            [
                'DEFAULT' => xl('PHP Default'),
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
                '7' => '7',
                '8' => '8',
                '9' => '9',
                '10' => '10',
                '11' => '11',
                '12' => '12',
                '13' => '13',
                '14' => '14',
                '15' => '15',
                '16' => '16',
                '17' => '17',
                '18' => '18',
                '19' => '19',
                '20' => '20',
            ],
            'DEFAULT',                // default
            xl('Authentication argon hash thread number. Suggest PHP Default unless you know what you are doing.')
        ],

        'gbl_auth_sha512_rounds' => [
            xl('Authentication SHA512 Hash Rounds Number'),
            [
                '1000' => '1000',
                '5000' => '5000',
                '10000' => '10000',
                '15000' => '15000',
                '20000' => '20000',
                '30000' => '30000',
                '40000' => '40000',
                '50000' => '50000',
                '75000' => '75000',
                '100000' => '100000',
                '200000' => '200000',
                '300000' => '300000',
                '400000' => '400000',
                '500000' => '500000',
                '750000' => '750000',
                '1000000' => '1000000',
                '2000000' => '2000000',
                '3000000' => '3000000',
                '4000000' => '4000000',
                '5000000' => '5000000',
                '6000000' => '6000000',
                '7000000' => '7000000',
                '8000000' => '8000000',
                '9000000' => '9000000',
            ],
            '100000',                // default
            xl('Authentication SHA512 hash rounds number.')
        ],

    ],

    // Notifications Tab
    //
    'Notifications' => [

        'patient_reminder_sender_name' => [
            xl('Patient Reminder Sender Name'),
            'text',                           // data type
            '',                               // default
            xl('Name of the sender for patient reminders.')
        ],

        'patient_reminder_sender_email' => [
            xl('Patient Reminder Sender Email'),
            'text',                           // data type
            '',                               // default
            xl('Email address of the sender for patient reminders. Replies to patient reminders will be directed to this address. It is important to use an address from your clinic\'s domain to avoid help prevent patient reminders from going to junk mail folders.')
        ],

        'practice_return_email_path' => [
            xl('Notification Email Address'),
            'text',                           // data type
            '',                               // default
            xl('Email address, if any, to receive administrative notifications.')
        ],

        'EMAIL_METHOD' => [
            xl('Email Transport Method'),
            [
                'PHPMAIL' => 'PHPMAIL',
                'SENDMAIL' => 'SENDMAIL',
                'SMTP' => 'SMTP',
            ],
            'SMTP',                             // default
            xl('Method for sending outgoing email.')
        ],

        'SMTP_HOST' => [
            xl('SMTP Server Hostname'),
            'text',                           // data type
            'localhost',                      // default
            xl('If SMTP is used, the server`s hostname or IP address.')
        ],

        'SMTP_PORT' => [
            xl('SMTP Server Port Number'),
            'num',                            // data type
            '25',                             // default
            xl('If SMTP is used, the server`s TCP port number (usually 25).')
        ],

        'SMTP_USER' => [
            xl('SMTP User for Authentication'),
            'text',                           // data type
            '',                               // default
            xl('Must be empty if SMTP authentication is not used.')
        ],

        'SMTP_PASS' => [
            xl('SMTP Password for Authentication'),
            'encrypted',                           // data type
            '',                               // default
            xl('Must be empty if SMTP authentication is not used.')
        ],

        'SMTP_SECURE' => [
            xl('SMTP Security Protocol'),
            [
                '' => xl('None'),
                'ssl' => 'SSL',
                'tls' => 'TLS'
            ],
            '',
            xl('SMTP security protocol to connect with. Required by some servers such as gmail.')
        ],

        'EMAIL_NOTIFICATION_HOUR' => [
            xl('Email Notification Hours'),
            'num',                            // data type
            '50',                             // default
            xl('Number of hours in advance to send email notifications.')
        ],

        'SMS_NOTIFICATION_HOUR' => [
            xl('SMS Notification Hours'),
            'num',                            // data type
            '50',                             // default
            xl('Number of hours in advance to send SMS notifications.')
        ],

        'SMS_GATEWAY_USENAME' => [
            xl('SMS Gateway Username'),
            'text',                           // data type
            '',                               // default
            xl('Username for SMS Gateway.')
        ],

        'SMS_GATEWAY_PASSWORD' => [
            xl('SMS Gateway Password'),
            'text',                           // data type
            '',                               // default
            xl('Password for SMS Gateway.')
        ],

        'SMS_GATEWAY_APIKEY' => [
            xl('SMS Gateway API Key'),
            'text',                           // data type
            '',                               // default
            xl('API key for SMS Gateway.')
        ],

        'phone_notification_hour' => [
            xl('Phone Notification Hour'),
            'num',                            // data type
            '50',                             // default
            xl('Number of hours in advance to send Phone notification.')
        ],

        'phone_gateway_username' => [
            xl('Phone Gateway Username'),
            'text',                           // data type
            '',                               // default
            xl('Username for Phone Gateway.')
        ],

        'phone_gateway_password' => [
            xl('Phone Gateway Password'),
            'encrypted',                      // data type
            '',                               // default
            xl('Password for Phone Gateway.')
        ],

        'phone_gateway_url' => [
            xl('Phone Gateway URL'),
            'text',                           // data type
            '',                               // default
            xl('URL for Phone Gateway.')
        ],

    ],

    // CDR (Clinical Decision Rules)
    //
    'CDR' => [

        'enable_cdr' => [
            xl('Enable Clinical Decisions Rules (CDR)'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Clinical Decisions Rules (CDR)')
        ],

        'enable_allergy_check' => [
            xl('Enable Allergy Check'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Allergy Check Against Medications and Prescriptions')
        ],

        'enable_alert_log' => [
            xl('Enable Alert Log'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Alert Logging')
        ],

        'enable_cdr_new_crp' => [
            xl('Enable Clinical Passive New Reminder(s) Popup'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Clinical Passive New Reminder(s) Popup')
        ],

        'enable_cdr_crw' => [
            xl('Enable Clinical Passive Reminder Widget'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Clinical Passive Reminder Widget')
        ],

        'enable_cdr_crp' => [
            xl('Enable Clinical Active Reminder Popup'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Clinical Active Reminder Popup')
        ],

        'enable_cdr_prw' => [
            xl('Enable Patient Reminder Widget'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Patient Reminder Widget')
        ],

        'enable_cqm' => [
            xl('Enable CQM Reporting'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Clinical Quality Measure (CQM) Reporting')
        ],

        'pqri_registry_name' => [
            xl('PQRI Registry Name'),
            'text',                           // data type
            'Model Registry',                               // default
            xl('PQRI Registry Name')
        ],

        'pqri_registry_id' => [
            xl('PQRI Registry ID'),
            'text',                           // data type
            '125789123',                               // default
            xl('PQRI Registry ID')
        ],

        'cqm_performance_period' => [
            xl('Eligible Clinician eCQM Performance Period'),
            'text',                           // data type
            '2025', // default set
            xl('Enter the eCQM Performance Period year. For example 2025')
        ],

        'enable_amc' => [
            xl('Enable AMC Reporting'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Automated Measure Calculations (AMC) Reporting')
        ],

        'enable_amc_prompting' => [
            xl('Enable AMC Prompting'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Prompting For Automated Measure Calculations (AMC) Required Data')
        ],

        'enable_amc_tracking' => [
            xl('Enable AMC Tracking'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Reporting of Tracking Date For Automated Measure Calculations (AMC)')
        ],

        'cdr_report_nice' => [
            xl('CDR Reports Processing Priority'),
            [
                '' => xl('Default Priority'),
                '5' => xl('Moderate Priority'),
                '10' => xl('Moderate/Low Priority'),
                '15' => xl('Low Priority'),
                '20' => xl('Lowest Priority')
            ],
            '',                               // default
            xl('Set processing priority for CDR engine based reports.')
        ],

        'pat_rem_clin_nice' => [
            xl('Patient Reminder Creation Processing Priority'),
            [
                '' => xl('Default Priority'),
                '5' => xl('Moderate Priority'),
                '10' => xl('Moderate/Low Priority'),
                '15' => xl('Low Priority'),
                '20' => xl('Lowest Priority')
            ],
            '',                               // default
            xl('Set processing priority for creation of Patient Reminders (in full clinic mode).')
        ],

        'report_itemizing_standard' => [
            xl('Enable Standard Report Itemization'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Itemization of Standard Clinical Rules Reports')
        ],

        'report_itemizing_cqm' => [
            xl('Enable CQM Report Itemization'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Itemization of CQM Reports')
        ],

        'report_itemizing_amc' => [
            xl('Enable AMC Report Itemization'),
            'bool',                           // data type
            '1',                               // default
            xl('Enable Itemization of AMC Reports')
        ],
        'dated_reminders_max_alerts_to_show' => [
            xl('Dated reminders maximum alerts to show'),
            'num',                           // data type
            '5',                               // default
            xl('Dated reminders maximum alerts to show')
        ],
        'patient_birthday_alert' => [
            xl('Alert on patient birthday'),
            [
                '0' => xl('No alert'),
                '1' => xl('Alert only on birthday'),
                '2' => xl('Alert on and after birthday'),
                '3' => xl('Alert on and up to 28 days after birthday')
            ],
            '1',                              // default
            xl('Alert on patient birthday')
        ],
        'patient_birthday_alert_manual_off' => [
            xl('Patient birthday alert requires turning off'),
            'bool',                           // data type
            '0',                              // default
            xl('Patient birthday alert requires turning off')
        ]
    ],

    // Logging
    //
    'Logging' => [

        'user_debug' => [
            xl('User Debugging Options'),
            [
                '0' => xl('None'),
                '1' => xl('Display Window Errors Only'),
                '2' => xl('Display Application Errors Only'),
                '3' => xl('All'),
            ],
            '0',                               // default
            xl('User Debugging Mode.')
        ],

        'enable_auditlog' => [
            xl('Enable Audit Logging'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable Audit Logging')
        ],

        'audit_events_patient-record' => [
            xl('Audit Logging Patient Record'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable logging of patient record modifications.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],

        'audit_events_scheduling' => [
            xl('Audit Logging Scheduling'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable logging of scheduling activities.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],

        'audit_events_order' => [
            xl('Audit Logging Order'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable logging of ordering activities.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],
        'audit_events_lab-results' => [
            xl('Audit Logging Lab Results'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable logging of lab result activities.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],
        'audit_events_security-administration' => [
            xl('Audit Logging Security Administration'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable logging of security and administration activities.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],

        'audit_events_backup' => [
            xl('Audit Logging Backups'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable logging of backup related activities.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],

        'audit_events_other' => [
            xl('Audit Logging Miscellaneous'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable logging of miscellaneous activities.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],

        'audit_events_query' => [
            xl('Audit Logging SELECT Query'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable logging of all SQL SELECT queries.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],

        'audit_events_cdr' => [
            xl('Audit CDR Engine Queries'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable logging of CDR Engine Queries.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],
        'audit_events_http-request' => [
            xl('Audit Logging user page history.'),
            'bool',                           // data type
            '1',                              // turn it on by default for better traceability in the logs
            xl('Enable logging of HTTP page requests as the user utilizes the web interface.') . ' (' . xl('Note that Audit Logging needs to be enabled above') . ')'
        ],
        'gbl_force_log_breakglass' => [
            xl('Audit all Emergency User Queries'),
            'bool',                           // data type
            '1',                              // default
            xl('Force logging of all Emergency User (ie. breakglass) activities.')
        ],

        'enable_atna_audit' => [
            xl('Enable ATNA Auditing'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable Audit Trail and Node Authentication (ATNA).')
        ],

        'atna_audit_host' => [
            xl('ATNA audit host'),
            'text',                           // data type
            '',                               // default
            xl('The hostname of the ATNA audit repository machine.')
        ],

        'atna_audit_port' => [
            xl('ATNA audit port'),
            'text',                           // data type
            '6514',                           // default
            xl('Listening port of the RFC 5425 TLS syslog server.')
        ],

        'atna_audit_localcert' => [
            xl('ATNA audit local certificate'),
            'text',                           // data type
            '',                               // default
            xl('Certificate to send to RFC 5425 TLS syslog server.')
        ],

        'atna_audit_cacert' => [
            xl('ATNA audit CA certificate'),
            'text',                           // data type
            '',                               // default
            xl('CA Certificate for verifying the RFC 5425 TLS syslog server.')
        ],

        'enable_auditlog_encryption' => [
            xl('Enable Audit Log Encryption'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable Audit Log Encryption')
        ],

        'api_log_option' => [
            xl('API Log Option'),
            [
                '0' => xl('No logging'),
                '1' => xl('Minimal Logging'),
                '2' => xl('Full Logging'),
            ],
            '2',                               // default
            xl('API Log Option (Full includes requests and responses).')
        ],

        'billing_log_option' => [
            xl('Billing Log Option'),
            [
                '1' => xl('Billing Log Append'),
                '2' => xl('Billing Log Overwrite')
            ],
            '1',                               // default
            xl('Billing log setting to append or overwrite the log file.')
        ],

        'gbl_print_log_option' => [
            xl('Printing Log Option'),
            [
                '0' => xl('No logging'),
                '1' => xl('Hide print feature'),
                '2' => xl('Log entire document'),
            ],
            '2',                               // default
            xl('Individual pages can override 2nd and 3rd options by implementing a log message.')
        ],

        'system_error_logging' => [
            xl('System Error Logging Options'),
            [
                'WARNING' => xl('Standard Error Logging'),
                'DEBUG' => xl('Debug Error Logging'),
            ],
            'WARNING',                        // default
            xl('System Error Logging Options.')
        ],

    ],

    // Miscellaneous Tab
    //
    'Miscellaneous' => [

        'enable_database_connection_pooling' => [
            xl('Enable Database Connection Pooling'),
            'bool',                           // data type
            '1',                              // default
            xl('Enable Database Connection Pooling')
        ],

        'mysql_bin_dir' => [
            xl('Path to MySQL Binaries'),
            'text',                           // data type
            $mysql_bin_dir,                   // default
            xl('Full path to directory containing MySQL executables.')
        ],

        'perl_bin_dir' => [
            xl('Path to Perl Binaries'),
            'text',                           // data type
            $perl_bin_dir,                    // default
            xl('Full path to directory containing Perl executables.')
        ],

        'temporary_files_dir' => [
            xl('Path to Temporary Files'),
            'text',                           // data type
            $temporary_files_dir,             // default
            xl('Full path to directory used for temporary files.')
        ],

        'backup_log_dir' => [
            xl('Path for Event Log Backup'),
            'text',                           // data type
            $backup_log_dir,                  // default
            xl('Full path to directory for event log backup.')
        ],

        'state_data_type' => [
            xl('State Data Type'),
            [
                '2' => xl('Text field'),
                '1' => xl('Single-selection list'),
                '26' => xl('Single-selection list with ability to add to the list'),
            ],
            '26',                             // default
            xl('Field type to use for employer or subscriber state in demographics.')
        ],

        'state_list' => [
            xl('State list'),
            'text',                           // data type
            'state',                          // default
            xl('List used by above State Data Type option.')
        ],

        'state_custom_addlist_widget' => [
            xl('State List Widget Custom Fields'),
            'bool',                           // data type
            '1',                              // default
            xl('Show the custom state form for the add list widget (will ask for title and abbreviation).')
        ],

        'country_data_type' => [
            xl('Country Data Type'),
            [
                '2' => xl('Text field'),
                '1' => xl('Single-selection list'),
                '26' => xl('Single-selection list with ability to add to the list'),
            ],
            '26',                             // default
            xl('Field type to use for employer or subscriber country in demographics.')
        ],

        'country_list' => [
            xl('Country list'),
            'text',                           // data type
            'country',                          // default
            xl('List used by above Country Data Type option.')
        ],

        'post_to_date_benchmark' => [
            xl('Financial Close Date (yyyy-mm-dd)'),
            'text',                           // data type
            date('Y-m-d', time() - (10 * 24 * 60 * 60)),                // default
            xl('The payments posted cannot go below this date.This ensures that after taking the final report nobody post for previous dates.')
        ],

        'enable_hylafax' => [
            xl('Enable Hylafax Support'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable Hylafax Support')
        ],

        'hylafax_server' => [
            xl('Hylafax Server'),
            'text',                           // data type
            'localhost',                      // default
            xl('Hylafax server hostname.')
        ],

        'hylafax_basedir' => [
            xl('Hylafax Directory'),
            'text',                           // data type
            '/var/spool/hylafax',             // default
            xl('Location where Hylafax stores faxes.')
        ],

        'enable_scanner' => [
            xl('Enable Scanner Support'),
            'bool',                           // data type
            '0',                              // default
            xl('Enable Scanner Support')
        ],

        'scanner_output_directory' => [
            xl('Scanner Directory'),
            'text',                           // data type
            '/mnt/scan_docs',                 // default
            xl('Location where scans are stored.')
        ],

        'unique_installation_id' => [
            xl('Unique Installation ID'),
            'if_empty_create_random_uuid',    // data type
            '',                 // default
            xl('Unique installation ID. Creates a random UUID if empty.')
        ],
    ],

    // Portal Tab
    //
    'Portal' => [

        'portal_onsite_two_enable' => [
            xl('Enable Patient Portal'),
            'bool',                           // data type
            '0',
            xl('Enable Patient Portal')
        ],

        'portal_onsite_two_address' => [
            xl('Patient Portal Site Address'),
            'text',                           // data type
            'https://your_web_site.com/openemr/portal',
            xl('Website link for the Patient Portal.')
        ],

        'portal_css_header' => [
            xl('Portal Default Theme'),
            [
                'style_light.css' => xl('Light'),
                'style_dark.css' => xl('Dark')
            ],
            'style_light.css',
            xl('Pick a default portal theme.')
        ],

        'portal_force_credential_reset' => [
            xl('Portal Login Forced Credential Reset'),
            [
                '0' => xl('Allow (Recommended)'),
                '1' => xl('Disable'),
                '2' => xl('User optional from credential dialog.')
            ],
            '0',
            xl('Select the credentials create or reset behavior for forcing patient to change password on portal login.') .
            xl('User optional persists the options checkbox state in the credential dialog to allow deciding on a patient by patient basis.')
        ],

        'portal_onsite_two_basepath' => [
            xl('Portal Uses Server Base Path (internal)'),
            'bool',
            '0',
            xl('Use servers protocol and host in urls (portal internal only).')
        ],

        'use_email_for_portal_username' => [
            xl('Use Patients on-record E-Mail for new Portal Login Username'),
            'bool',
            '1',
            xl('Use contact email when creating portal credentials.')
        ],

        'enforce_signin_email' => [
            xl('Require Patients to enter their on-record email for Portal Login'),
            'bool',
            '1',
            xl('Patient is required to enter their contact e-mail if present in Demographics Contact.')
        ],

        'google_recaptcha_site_key' => [
            xl('Google reCAPTCHA V2 site key'),
            'text',
            '',
            xl('Google reCAPTCHA V2 site key')
        ],

        'google_recaptcha_secret_key' => [
            xl('Google reCAPTCHA V2 secret key'),
            'encrypted',
            '',
            xl('Google reCAPTCHA V2 secret key')
        ],

        'portal_primary_menu_logo_height' => [
            xl('Primary Menu Logo Height'),
            'text',
            '30',
            xl('The height of the portal logo located on the primary navbar in pixels without a suffix'),
        ],

        'portal_onsite_two_register' => [
            xl('Allow New Patient Registration Widget') . ' ' . xl('This requires reCAPTCHA to be setup'),
            'bool',                           // data type
            '0',
            xl('Enable Patient Portal new patient to self register.')
        ],

        'portal_two_pass_reset' => [
            xl('Allow Patients to Reset Credentials') . ' ' . xl('This requires reCAPTCHA to be setup'),
            'bool',                           // data type
            '0',
            xl('Patient may change their logon from portal login dialog.')
        ],

        'allow_portal_appointments' => [
            xl('Allow Online Appointments'),
            'bool',                           // data type
            '1',
            xl('Allow Patient to make and view appointments online.')
        ],

        'allow_custom_report' => [
            xl('Allow Online Custom Content Report'),
            'bool',                           // data type
            '1',
            xl('Allow Patient to use Custom Content Report.')
        ],

        'portal_two_ledger' => [
            xl('Allow Patient Billing Summary Report Online'),
            'bool',                           // data type
            '1',
            xl('Allow Patient to view their accounting ledger online.')
        ],

        'portal_two_payments' => [
            xl('Allow Online Payments'),
            'bool',                           // data type
            '0',
            xl('Allow Patient to make payments online.')
        ],

        'portal_onsite_document_download' => [
            xl('Enable Patient Portal Document Download'),
            'bool',                           // data type
            '1',
            xl('Enables the ability to download documents in the Patient Portal by the user.')
        ],

        'allow_portal_uploads' => [
            xl('Allow Patient Uploads from Portal Documents'),
            'bool',
            '1',
            xl('Enables the ability for patient to upload documents to Documents Onsite Patient category.')
        ],

        'show_insurance_in_profile' => [
            xl('Allow Insurances in Patient Profile'),
            'bool',
            '1',
            xl('UnCheck to not show insurances in Profile.')
        ],

        'show_portal_primary_logo' => [
            xl('Show Primary Logo on portal login page'),
            'bool',
            '1',
            xl('Show primary logo on login'),
        ],

        'extra_portal_logo_login' => [
            xl('Show Secondary Logo on portal Login page'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Show Secondary Logo on Login')
        ],

        'secondary_portal_logo_position' => [
            xl('Order of the Secondary logo'),
            [
                'first' => xl('First Position'),
                'second' => xl('Second Position'),
            ],
            'second',
            xl('Place the secondary logo first, or second'),
        ],
    ],

    // Connectors Tab
    //
    'Connectors' => [

        GlobalConnectorsEnum::SITE_ADDRESS_OAUTH->value => [
            xl('Site Address Override (if needed for OAuth2, FHIR, CCDA, or Payment Processing)'),
            'text',
            '',
            xl('The hostname for OAuth2, FHIR, CCDA, and Payment Processing, pointing back back to this OpenEMR instance. e.g. https://localhost:9300.')
        ],

        GlobalConnectorsEnum::REST_FHIR_API->value => [
            xl('Enable OpenEMR Standard FHIR REST API'),
            'bool',
            '0',
            xl('Enable OpenEMR Standard FHIR RESTful API.')
        ],

        GlobalConnectorsEnum::REST_SYSTEM_SCOPES_API->value => [
            xl('Enable OpenEMR FHIR System Scopes (Turn on only if you know what you are doing)'),
            'bool',
            '0',
            xl('Enable OpenEMR FHIR System Scopes.')
        ],

        GlobalConnectorsEnum::REST_API->value => [
            xl('Enable OpenEMR Standard REST API'),
            'bool',
            '0',
            xl('Enable OpenEMR Standard RESTful API.')
        ],

        GlobalConnectorsEnum::REST_PORTAL_API->value => [
            xl('Enable OpenEMR Patient Portal REST API (EXPERIMENTAL)'),
            'bool',
            '0',
            xl('Enable OpenEMR Patient Portal RESTful API.')
        ],

        GlobalConnectorsEnum::OAUTH_PASSWORD_GRANT->value => [
            xl('Enable OAuth2 Password Grant (Not considered secure)'),
            [
                0 => xl('Off (Recommended setting)'),
                1 => xl('On for Users Role'),
                2 => xl('On for Patient Role'),
                3 => xl('On for Both Roles')
            ],
            '0',
            xl('Enable OAuth2 Password Grant. Recommend turning this setting off for production server. Recommend only using for testing.')
        ],
        GlobalConnectorsEnum::OAUTH_APP_MANUAL_APPROVAL->value => [
            xl('OAuth2 App Manual Approval Settings'),
            [
                0 => xl('Patient standalone apps Auto Approved, EHR-Launch,Provider&System Apps require manual approval')
            , 1 => xl('Manually Approve All Apps (USA jurisdictions must approve all patient standalone apps within 48 hours)')
//                ,2 => xl('All apps Auto Approved') we could add this setting at a latter date
            ],
            '0',
            xl('Approval settings for 3rd party app/api access')
        ],
        GlobalConnectorsEnum::OAUTH_EHR_LAUNCH_AUTHORIZATION_FLOW_SKIP->value => [
            xl('OAuth2 EHR-Launch Authorization Flow Skip Enable App Setting'),
            'bool',
            '1',
            xl('Enable an OAuth2 Client application to be configured to skip the login screen and the scope authorization screen if the user is already logged into the EHR.')
        ],
        GlobalConnectorsEnum::FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION->value => [
            xl('Maximum supported version for US Core FHIR Implementation Guide -- note if a resource/profile is forwards compatible it will still be supported'),
            [
                '3.1.1' => 'US Core 3.1.1',
                '7.0.0' => 'US Core 7.0.0',
                '8.0.0' => 'US Core 8.0.0',
            ],
            '8.0.0',
            xl('Set the maximum compatability version of US Core IG to support.  If a resource profile has breaking changes it will not exceed the given profile. If a profile has backwards breaking changes it will skip that profile version if a later version is supported.')
        ],
        'cc_front_payments' => [
            xl('Accept Credit Card transactions from Front Payments'),
            'bool',
            '0',
            xl('Allow manual entry and authorise credit card payments. Ensure a gateway is enabled.')
        ],
        'cc_stripe_terminal' => [
            xl('In person payments with Stripe Verifone P400'),
            'bool',
            '0',
            xl('Allow in person credit card payments using Stripe Verifone P400. Ensure Stripe gateway is enabled.')
        ],
        'payment_gateway' => [
            xl('Select Credit Card Payment Gateway'),
            [
                'InHouse' => xl('In House Authorize Payments'),
                'AuthorizeNet' => xl('Gateway for AuthorizeNet Manual Payments'),
                'Sphere' => xl('Gateway for Sphere Payments'),
                'Stripe' => xl('Gateway for Stripe Manual Payments')
            ],
            'InHouse',
            xl('Enable a Payment Gateway Service for processing credit card transactions')
        ],

        'gateway_mode_production' => [
            xl('Set Gateway to Production Mode'),
            'bool',                           // data type
            '0',
            xl('Check this to go live. Not checked is testing mode.')
        ],

        'gateway_public_key' => [
            xl('Gateway Publishable Key'),
            'encrypted',
            '',
            xl('The public access key for secure tokenize of credit or debit card authorization. PCI compliance')
        ],

        'gateway_api_key' => [
            xl('Gateway API Login Auth Name or Secret'),
            'encrypted',
            '',
            xl('The Auth Name or API key for selected account. Auth Name for Authorize.Net and API Secret for Stripe.')
        ],

        'gateway_transaction_key' => [
            xl('Gateway Transaction Key'),
            'encrypted',
            '',
            xl('Mainly Authorize.Net uses two keys')
        ],

        'sphere_clinicfront_trxcustid' => [
            xl('Sphere Clinicfront over phone (MOTO) Transaction CustID'),
            'encrypted',
            '',
            xl('Sphere Clinicfront over phone (MOTO) Transaction CustID')
        ],

        'sphere_clinicfront_trxcustid_licensekey' => [
            xl('Sphere Clinicfront over phone (MOTO) Transaction CustID License Key'),
            'encrypted',
            '',
            xl('Sphere Clinicfront over phone (MOTO) Transaction CustID License Key')
        ],

        'sphere_moto_tc_link_pass' => [
            xl('Sphere MOTO TC Link Password'),
            'encrypted',
            '',
            xl('Sphere MOTO TC Link Password')
        ],

        'sphere_clinicfront_retail_trxcustid' => [
            xl('Sphere Clinicfront in person (RETAIL) Transaction CustID'),
            'encrypted',
            '',
            xl('Sphere Clinicfront in person (RETAIL) Transaction CustID')
        ],

        'sphere_clinicfront_retail_trxcustid_licensekey' => [
            xl('Sphere Clinicfront in person (RETAIL) Transaction CustID License Key'),
            'encrypted',
            '',
            xl('Sphere Clinicfront in person (RETAIL) Transaction CustID License Key')
        ],

        'sphere_retail_tc_link_pass' => [
            xl('Sphere RETAIL TC Link Password'),
            'encrypted',
            '',
            xl('Sphere RETAIL TC Link Password')
        ],

        'sphere_patientfront_trxcustid' => [
            xl('Sphere Patientfront (Ecomm) Transaction CustID'),
            'encrypted',
            '',
            xl('Sphere Patientfront (Ecomm) Transaction CustID')
        ],

        'sphere_patientfront_trxcustid_licensekey' => [
            xl('Sphere Patientfront (Ecomm) Transaction CustID License Key'),
            'encrypted',
            '',
            xl('Sphere Patientfront (Ecomm) Transaction CustID License Key')
        ],

        'sphere_ecomm_tc_link_pass' => [
            xl('Sphere Ecomm TC Link Password'),
            'encrypted',
            '',
            xl('Sphere Ecomm TC Link Password')
        ],

        'sphere_credit_void_confirm_pin' => [
            xl('Sphere Void/Credit Confirmation PIN'),
            'encrypted_hash',
            '',
            xl('Sphere Void/Credit Confirmation Password. OpenEMR confirms pin/password before proceeding with void/credit.')
        ],

        'medex_enable' => [
            xl('Enable MedEx Communication Service'),
            'bool',                           // data type
            '0',
            xl('Enable MedEx Communication Service')
        ],

        'erx_enable' => [
            xl('Enable NewCrop eRx Service'),
            'bool',
            '0',
            xl('Enable NewCrop eRx Service.')
        ],

        'erx_newcrop_path' => [
            xl('NewCrop eRx Site Address'),
            'text',
            'https://secure.newcropaccounts.com/ux2/InterfaceV7/RxEntry.aspx',
            xl('URL for NewCrop eRx Site Address.')
        ],

        'erx_newcrop_path_soap' => [
            xl('NewCrop eRx Web Service Address'),
            'text',
            'https://secure.newcropaccounts.com/v7/WebServices/Update1.asmx?WSDL;https://secure.newcropaccounts.com/v7/WebServices/Patient.asmx?WSDL',
            xl('URLs for NewCrop eRx Service Address, separated by a semi-colon.')
        ],

        'erx_soap_ttl_allergies' => [
            xl('NewCrop eRx SOAP Request Time-To-Live for Allergies'),
            'num',
            '21600',
            xl('Time-To-Live for NewCrop eRx Allergies SOAP Request in seconds.')
        ],

        'erx_soap_ttl_medications' => [
            xl('NewCrop eRx SOAP Request Time-To-Live for Medications'),
            'num',
            '21600',
            xl('Time-To-Live for NewCrop eRx Medications SOAP Request in seconds.')
        ],

        'erx_account_partner_name' => [
            xl('NewCrop eRx Partner Name'),
            'text',
            '',
            xl('Partner Name issued for NewCrop eRx service.')
        ],

        'erx_account_name' => [
            xl('NewCrop eRx Name'),
            'text',
            '',
            xl('Account Name issued for NewCrop eRx service.')
        ],

        'erx_account_password' => [
            xl('NewCrop eRx Password'),
            'encrypted',
            '',
            xl('Account Password issued for NewCrop eRx service.')
        ],

        'erx_account_id' => [
            xl('NewCrop eRx Account Id'),
            'text',
            '1',
            xl('Account Id issued for NewCrop eRx service, used to separate multi-facility accounts.')
        ],

        'erx_upload_active' => [
            xl('Only upload active prescriptions'),
            'bool',
            '0',
            xl('Only upload active prescriptions to NewCrop eRx.')
        ],

        'erx_import_status_message' => [
            xl('Enable NewCrop eRx import status message'),
            'bool',
            '0',
            xl('Enable import status message after visiting NewCrop eRx.')
        ],

        'erx_medication_display' => [
            xl('Do not display NewCrop eRx Medications uploaded'),
            'bool',
            '0',
            xl('Do not display Medications uploaded after visiting NewCrop eRx.')
        ],

        'erx_allergy_display' => [
            xl('Do not display NewCrop eRx Allergy uploaded'),
            'bool',
            '0',
            xl('Do not display Allergies uploaded after visiting NewCrop eRx.')
        ],

        'erx_default_patient_country' => [
            xl('NewCrop eRx Default Patient Country'),
            [
                '' => '',
                'US' => xl('USA'),
                'CA' => xl('Canada'),
                'MX' => xl('Mexico'),
            ],
            '',
            xl('Default Patient Country sent to NewCrop eRx, only if patient country is not set.'),
        ],

        'erx_debug_setting' => [
            xl('NewCrop eRx Debug Setting'),
            [
                0 => xl('None'),
                1 => xl('Request Only'),
                2 => xl('Response Only'),
                3 => xl('Request & Response'),
            ],
            '0',
            xl('Log all NewCrop eRx Requests and / or Responses.'),
        ],

        'ccda_alt_service_enable' => [
            xl('Enable C-CDA Service'),
            [
                0 => xl('Off'),
                1 => xl('Care Coordination Only'),
                2 => xl('Portal Only'),
                3 => xl('Both'),
            ],
            '0',
            xl('Enable C-CDA Service')
        ],

        'phimail_enable' => [
            xl('Enable phiMail Direct Messaging Service'),
            'bool',                           // data type
            '0',
            xl('Enable phiMail Direct Messaging Service')
        ],
        'phimail_testmode_disabled' => [
            xl('Disable phiMail Test Mode'),
            'bool',                           // data type
            '0',
            xl('When you are ready to run phiMail in production mode. Turn on this flag.')
        ],
        'phimail_verifyrecipientreceived_enable' => [
            xl("phiMail default force message receipt confirmation to on"),
            'bool',
            '0',
            xl("Marks a message as succesful only if recipient confirms they received the message.  This can fail messages that otherwise would have been received if the recipient's system does not support confirmation receipt")
        ],

        'phimail_server_address' => [
            xl('phiMail Server Address'),
            'text',                           // data type
            'https://phimail.example.com:32541',
            xl('Contact EMR Direct to subscribe to the phiMail Direct messaging service')
        ],

        'phimail_username' => [
            xl('phiMail Username'),
            'text',                           // data type
            '',
            xl('Contact EMR Direct to subscribe to the phiMail Direct messaging service')
        ],

        'phimail_password' => [
            xl('phiMail Password'),
            'encrypted',                      // data type
            '',
            xl('Contact EMR Direct to subscribe to the phiMail Direct messaging service')
        ],

        'phimail_notify' => [
            xl('phiMail notification user'),
            'text',                           // data type
            'admin',
            xl('This user will receive notification of new incoming Direct messages')
        ],

        'phimail_interval' => [
            xl('phiMail Message Check Interval (minutes)'),
            'num',                           // data type
            '5',
            xl('Interval between message checks (set to zero for manual checks only)')
        ],

        'phimail_ccd_enable' => [
            xl('phiMail Allow CCD Send'),
            'bool',                           // data type
            '0',
            xl('phiMail Allow CCD Send')
        ],

        'phimail_ccr_enable' => [
            xl('phiMail Allow CCR Send'),
            'bool',                           // data type
            '0',
            xl('phiMail Allow CCR Send')
        ],

        'easipro_enable' => [
            xl('Enable Easipro'),
            'bool',                           // data type
            '0',
            xl('Enable Easipro. For licensing options for this feature, please contact') . ' api@assessmentcenter.net'
        ],

        'easipro_server' => [
            xl('Easipro Server'),
            'text',                           // data type
            '',
            xl('Easipro Server')
        ],

        'easipro_name' => [
            xl('Easipro Server Username'),
            'text',                           // data type
            '',
            xl('Easipro Server Username')
        ],

        'easipro_pass' => [
            xl('Easipro Server Password'),
            'encrypted',                      // data type
            '',
            xl('Easipro Server Password')
        ],

        'usps_apiv3_client_id' => [
            xl('USPS API Client ID'),
            'encrypted',
            '',
            xl('Client ID for USPS API v3')
        ],

        'usps_apiv3_client_secret' => [
            xl('USPS API Client Secret'),
            'encrypted',
            '',
            xl('Client Secret for USPS API v3')
        ],

        'ccda_validation_disable' => [
            xl('Disable All import CDA Validation Reporting'),
            'bool',                           // data type
            '0',
            xl('Disable All CDA conformance and validation services to improve import performance')
        ],

        'mdht_conformance_server_enable' => [
            xl('Use MDHT External Validation Service'),
            'bool',                           // data type
            '0',
            xl('Enable CCDA conformance and validation API service')
        ],

        'mdht_conformance_server' => [
            xl('CCDA MDHT Validation API Server Address'),
            'text',                           // data type
            '',
            xl('CCDA conformance and validation API service URL. For testing (using ONLY test data) you can default to http://ccda.healthit.gov which should not be used to transmit PHI. Production sites can deploy their own by following instructions here https://github.com/onc-healthit/reference-ccda-validator.')
        ],
    ],

    'Rx' => [
        'rx_enable_DEA' => [
            xl('Rx Enable DEA #'),
            'bool',                           // data type
            '1',
            xl('Rx Enable DEA #')
        ],
        'rx_show_DEA' => [
            xl('Rx Show DEA #'),
            'bool',                           // data type
            '0',
            xl('Rx Show DEA #')
        ],
        'rx_enable_NPI' => [
            xl('Rx Enable NPI'),
            'bool',                           // data type
            '0',
            xl('Rx Enable NPI')
        ],
        'rx_show_NPI' => [
            xl('Rx Show NPI'),
            'bool',                           // data type
            '0',
            xl('Rx Show NPI')
        ],
        'rx_enable_SLN' => [
            xl('Rx Enable State Lic. #'),
            'bool',                           // data type
            '0',
            xl('Rx Enable State Lic. #')
        ],
        'rx_show_SLN' => [
            xl('Rx Show State Lic. #'),
            'bool',                           // data type
            '0',
            xl('Rx Show State Lic. #')
        ],
        'rx_show_drug_drug' => [
            xl('Rx NLM Drug-Drug'),
            'bool',                           // data type
            '0',
            xl('Rx NLM Drug-Drug')
        ],
        'rx_paper_size' => [
            xl('Rx Paper Size'),               // descriptive name
            [
                'LETTER' => xl('Letter Paper Size'),
                'LEGAL' => xl('Legal Paper Size'),
                'FOLIO' => xl('Folio Paper Size'),
                'EXECUTIVE' => xl('Executive Paper Size'),
                '4A0' => ('4A0' . " " . xl('Paper Size')),
                '2A0' => ('2A0' . " " . xl('Paper Size')),
                'A0' => ('A0' . " " . xl('Paper Size')),
                'A1' => ('A1' . " " . xl('Paper Size')),
                'A2' => ('A2' . " " . xl('Paper Size')),
                'A3' => ('A3' . " " . xl('Paper Size')),
                'A4' => ('A4' . " " . xl('Paper Size')),
                'A5' => ('A5' . " " . xl('Paper Size')),
                'A6' => ('A6' . " " . xl('Paper Size')),
                'A7' => ('A7' . " " . xl('Paper Size')),
                'A8' => ('A8' . " " . xl('Paper Size')),
                'A9' => ('A9' . " " . xl('Paper Size')),
                'A10' => ('A10' . " " . xl('Paper Size')),
                'B0' => ('B0' . " " . xl('Paper Size')),
                'B1' => ('B1' . " " . xl('Paper Size')),
                'B2' => ('B2' . " " . xl('Paper Size')),
                'B3' => ('B3' . " " . xl('Paper Size')),
                'B4' => ('B4' . " " . xl('Paper Size')),
                'B5' => ('B5' . " " . xl('Paper Size')),
                'B6' => ('B6' . " " . xl('Paper Size')),
                'B7' => ('B7' . " " . xl('Paper Size')),
                'B8' => ('B8' . " " . xl('Paper Size')),
                'B9' => ('B9' . " " . xl('Paper Size')),
                'B10' => ('B10' . " " . xl('Paper Size')),
                'C0' => ('C0' . " " . xl('Paper Size')),
                'C1' => ('C1' . " " . xl('Paper Size')),
                'C2' => ('C2' . " " . xl('Paper Size')),
                'C3' => ('C3' . " " . xl('Paper Size')),
                'C4' => ('C4' . " " . xl('Paper Size')),
                'C5' => ('C5' . " " . xl('Paper Size')),
                'C6' => ('C6' . " " . xl('Paper Size')),
                'C7' => ('C7' . " " . xl('Paper Size')),
                'C8' => ('C8' . " " . xl('Paper Size')),
                'C9' => ('C9' . " " . xl('Paper Size')),
                'C10' => ('C10' . " " . xl('Paper Size')),
                'RA0' => ('RA0' . " " . xl('Paper Size')),
                'RA1' => ('RA1' . " " . xl('Paper Size')),
                'RA2' => ('RA2' . " " . xl('Paper Size')),
                'RA3' => ('RA3' . " " . xl('Paper Size')),
                'RA4' => ('RA4' . " " . xl('Paper Size')),
                'SRA0' => ('SRA0' . " " . xl('Paper Size')),
                'SRA1' => ('SRA1' . " " . xl('Paper Size')),
                'SRA2' => ('SRA2' . " " . xl('Paper Size')),
                'SRA3' => ('SRA3' . " " . xl('Paper Size')),
                'SRA4' => ('SRA4' . " " . xl('Paper Size')),
            ],
            'LETTER',                              // default = tree menu
            xl('Rx Paper Size')
        ],
        'rx_left_margin' => [
            xl('Rx Left Margin (px)'),
            'num',
            '30',
            xl('Rx Left Margin (px)')
        ],
        'rx_right_margin' => [
            xl('Rx Right Margin (px)'),
            'num',
            '30',
            xl('Rx Right Margin (px)')
        ],
        'rx_top_margin' => [
            xl('Rx Top Margin (px)'),
            'num',
            '72',
            xl('Rx Top Margin (px)')
        ],
        'rx_bottom_margin' => [
            xl('Rx Bottom Margin (px)'),
            'num',
            '30',
            xl('Rx Bottom Margin (px)')
        ],
        'rx_use_fax_template' => [
            xl('Show button for download fax template'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Show button in the prescription list for download fax template')
        ],
        'rx_zend_html_template' => [
            xl('Rx html print - zend module'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Use an html template from zend module')
        ],
        'rx_zend_html_action' => [
            xl('Name of zend template for html print'),
            'text',                           // data type
            'default',
            xl('Name of zend template for html print, possible to add custom template in the PrescriptionTemplate module')
        ],
        'rx_zend_pdf_template' => [
            xl('Rx pdf - zend template'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Use a pdf template from zend module')
        ],
        'rx_zend_pdf_action' => [
            xl('Name of zend template for pdf export'),
            'text',                           // data type
            'default',
            xl('Name of zend template for pdf export, possible to add custom template in the PrescriptionTemplate module')
        ],
        'rx_send_email' => [
            xl('Allow email sending of prescriptions'),
            'bool',                           // data type
            '1',
            xl('Enable email option (available on prescriptions list screen) for emailing prescriptions')
        ],
    ],
    'PDF' => [
        'pdf_layout' => [
            xl('Layout'),
            [
                'P' => xl('Portrait'),
                'L' => xl('Landscape')
            ],
            'P', //defaut
            xl("Choose Layout Direction"),
        ],
        'pdf_language' => [
            xl('PDF Language'),
            [
                'aa' => xl('Afar'),
                'af' => xl('Afrikaans'),
                'ak' => xl('Akan'),
                'sq' => xl('Albanian'),
                'am' => xl('Amharic'),
                'ar' => xl('Arabic'),
                'an' => xl('Aragonese'),
                'hy' => xl('Armenian'),
                'as' => xl('Assamese'),
                'av' => xl('Avaric'),
                'ae' => xl('Avestan'),
                'ay' => xl('Aymara'),
                'az' => xl('Azerbaijani'),
                'bm' => xl('Bambara'),
                'ba' => xl('Bashkir'),
                'eu' => xl('Basque'),
                'be' => xl('Belarusian'),
                'bn' => xl('Bengali- Bangla'),
                'bh' => xl('Bihari'),
                'bi' => xl('Bislama'),
                'bs' => xl('Bosnian'),
                'br' => xl('Breton'),
                'bg' => xl('Bulgarian'),
                'my' => xl('Burmese'),
                'ca' => xl('Catalan- Valencian'),
                'ch' => xl('Chamorro'),
                'ce' => xl('Chechen'),
                'ny' => xl('Chichewa- Chewa- Nyanja'),
                'zh' => xl('Chinese'),
                'cv' => xl('Chuvash'),
                'kw' => xl('Cornish'),
                'co' => xl('Corsican'),
                'cr' => xl('Cree'),
                'hr' => xl('Croatian'),
                'cs' => xl('Czech'),
                'da' => xl('Danish'),
                'dv' => xl('Divehi- Dhivehi- Maldivian-'),
                'nl' => xl('Dutch'),
                'dz' => xl('Dzongkha'),
                'en' => xl('English'),
                'eo' => xl('Esperanto'),
                'et' => xl('Estonian'),
                'ee' => xl('Ewe'),
                'fo' => xl('Faroese'),
                'fj' => xl('Fijian'),
                'fi' => xl('Finnish'),
                'fr' => xl('French'),
                'ff' => xl('Fula- Fulah- Pulaar- Pular'),
                'gl' => xl('Galician'),
                'ka' => xl('Georgian'),
                'de' => xl('German'),
                'el' => xl('Greek, Modern'),
                'gn' => xl('Guarani'),
                'gu' => xl('Gujarati'),
                'ht' => xl('Haitian- Haitian Creole'),
                'ha' => xl('Hausa'),
                'he' => xl('Hebrew (modern)'),
                'hz' => xl('Herero'),
                'hi' => xl('Hindi'),
                'ho' => xl('Hiri Motu'),
                'hu' => xl('Hungarian'),
                'ia' => xl('Interlingua'),
                'id' => xl('Indonesian'),
                'ie' => xl('Interlingue'),
                'ga' => xl('Irish'),
                'ig' => xl('Igbo'),
                'ik' => xl('Inupiaq'),
                'io' => xl('Ido'),
                'is' => xl('Icelandic'),
                'it' => xl('Italian'),
                'iu' => xl('Inuktitut'),
                'ja' => xl('Japanese'),
                'jv' => xl('Javanese'),
                'kl' => xl('Kalaallisut, Greenlandic'),
                'kn' => xl('Kannada'),
                'kr' => xl('Kanuri'),
                'ks' => xl('Kashmiri'),
                'kk' => xl('Kazakh'),
                'km' => xl('Khmer'),
                'ki' => xl('Kikuyu, Gikuyu'),
                'rw' => xl('Kinyarwanda'),
                'ky' => xl('Kyrgyz'),
                'kv' => xl('Komi'),
                'kg' => xl('Kongo'),
                'ko' => xl('Korean'),
                'ku' => xl('Kurdish'),
                'kj' => xl('Kwanyama, Kuanyama'),
                'la' => xl('Latin'),
                'lb' => xl('Luxembourgish, Letzeburgesch'),
                'lg' => xl('Ganda'),
                'li' => xl('Limburgish, Limburgan, Limburger'),
                'ln' => xl('Lingala'),
                'lo' => xl('Lao'),
                'lt' => xl('Lithuanian'),
                'lu' => xl('Luba-Katanga'),
                'lv' => xl('Latvian'),
                'gv' => xl('Manx'),
                'mk' => xl('Macedonian'),
                'mg' => xl('Malagasy'),
                'ms' => xl('Malay'),
                'ml' => xl('Malayalam'),
                'mt' => xl('Maltese'),
                'mi' => xl('Maori'),
                'mr' => xl('Marathi (Marathi)'),
                'mh' => xl('Marshallese'),
                'mn' => xl('Mongolian'),
                'na' => xl('Nauru'),
                'nv' => xl('Navajo, Navaho'),
                'nb' => xl('Norwegian Bokmal'),
                'nd' => xl('North Ndebele'),
                'ne' => xl('Nepali'),
                'ng' => xl('Ndonga'),
                'nn' => xl('Norwegian Nynorsk'),
                'no' => xl('Norwegian'),
                'ii' => xl('Nuosu'),
                'nr' => xl('South Ndebele'),
                'oc' => xl('Occitan'),
                'oj' => xl('Ojibwe, Ojibwa'),
                'cu' => xl('Old Church Slavonic, Church Slavonic, Old Bulgarian'),
                'om' => xl('Oromo'),
                'or' => xl('Oriya'),
                'os' => xl('Ossetian, Ossetic'),
                'pa' => xl('Panjabi, Punjabi'),
                'pi' => xl('Pali'),
                'fa' => xl('Persian (Farsi)'),
                'pl' => xl('Polish'),
                'ps' => xl('Pashto, Pushto'),
                'pt' => xl('Portuguese'),
                'qu' => xl('Quechua'),
                'rm' => xl('Romansh'),
                'rn' => xl('Kirundi'),
                'ro' => xl('Romanian'),
                'ru' => xl('Russian'),
                'sa' => xl('Sanskrit (Samskrta)'),
                'sc' => xl('Sardinian'),
                'sd' => xl('Sindhi'),
                'se' => xl('Northern Sami'),
                'sm' => xl('Samoan'),
                'sg' => xl('Sango'),
                'sr' => xl('Serbian'),
                'gd' => xl('Scottish Gaelic- Gaelic'),
                'sn' => xl('Shona'),
                'si' => xl('Sinhala, Sinhalese'),
                'sk' => xl('Slovak'),
                'sl' => xl('Slovene'),
                'so' => xl('Somali'),
                'st' => xl('Southern Sotho'),
                'es' => xl('Spanish- Castilian'),
                'su' => xl('Sundanese'),
                'sw' => xl('Swahili'),
                'ss' => xl('Swati'),
                'sv' => xl('Swedish'),
                'ta' => xl('Tamil'),
                'te' => xl('Telugu'),
                'tg' => xl('Tajik'),
                'th' => xl('Thai'),
                'ti' => xl('Tigrinya'),
                'bo' => xl('Tibetan Standard, Tibetan, Central'),
                'tk' => xl('Turkmen'),
                'tl' => xl('Tagalog'),
                'tn' => xl('Tswana'),
                'to' => xl('Tonga (Tonga Islands)'),
                'tr' => xl('Turkish'),
                'ts' => xl('Tsonga'),
                'tt' => xl('Tatar'),
                'tw' => xl('Twi'),
                'ty' => xl('Tahitian'),
                'ug' => xl('Uyghur, Uighur'),
                'uk' => xl('Ukrainian'),
                'ur' => xl('Urdu'),
                'uz' => xl('Uzbek'),
                've' => xl('Venda'),
                'vi' => xl('Vietnamese'),
                'vo' => xl('Volapuk'),
                'wa' => xl('Walloon'),
                'cy' => xl('Welsh'),
                'wo' => xl('Wolof'),
                'fy' => xl('Western Frisian'),
                'xh' => xl('Xhosa'),
                'yi' => xl('Yiddish'),
                'yo' => xl('Yoruba'),
                'za' => xl('Zhuang, Chuang'),
                'zu' => xl('Zulu'),
            ],
            'en', // default English
            xl('Choose PDF languange Preference'),
        ],
        'pdf_size' => [
            xl('Paper Size'),               // Descriptive Name
            [
                'LETTER' => xl('Letter Paper Size'),
                'LEGAL' => xl('Legal Paper Size'),
                'FOLIO' => xl('Folio Paper Size'),
                'EXECUTIVE' => xl('Executive Paper Size'),
                '4A0' => ('4A0' . " " . xl('Paper Size')),
                '2A0' => ('2A0' . " " . xl('Paper Size')),
                'A0' => ('A0' . " " . xl('Paper Size')),
                'A1' => ('A1' . " " . xl('Paper Size')),
                'A2' => ('A2' . " " . xl('Paper Size')),
                'A3' => ('A3' . " " . xl('Paper Size')),
                'A4' => ('A4' . " " . xl('Paper Size')),
                'A5' => ('A5' . " " . xl('Paper Size')),
                'A6' => ('A6' . " " . xl('Paper Size')),
                'A7' => ('A7' . " " . xl('Paper Size')),
                'A8' => ('A8' . " " . xl('Paper Size')),
                'A9' => ('A9' . " " . xl('Paper Size')),
                'A10' => ('A10' . " " . xl('Paper Size')),
                'B0' => ('B0' . " " . xl('Paper Size')),
                'B1' => ('B1' . " " . xl('Paper Size')),
                'B2' => ('B2' . " " . xl('Paper Size')),
                'B3' => ('B3' . " " . xl('Paper Size')),
                'B4' => ('B4' . " " . xl('Paper Size')),
                'B5' => ('B5' . " " . xl('Paper Size')),
                'B6' => ('B6' . " " . xl('Paper Size')),
                'B7' => ('B7' . " " . xl('Paper Size')),
                'B8' => ('B8' . " " . xl('Paper Size')),
                'B9' => ('B9' . " " . xl('Paper Size')),
                'B10' => ('B10' . " " . xl('Paper Size')),
                'C0' => ('C0' . " " . xl('Paper Size')),
                'C1' => ('C1' . " " . xl('Paper Size')),
                'C2' => ('C2' . " " . xl('Paper Size')),
                'C3' => ('C3' . " " . xl('Paper Size')),
                'C4' => ('C4' . " " . xl('Paper Size')),
                'C5' => ('C5' . " " . xl('Paper Size')),
                'C6' => ('C6' . " " . xl('Paper Size')),
                'C7' => ('C7' . " " . xl('Paper Size')),
                'C8' => ('C8' . " " . xl('Paper Size')),
                'C9' => ('C9' . " " . xl('Paper Size')),
                'C10' => ('C10' . " " . xl('Paper Size')),
                'RA0' => ('RA0' . " " . xl('Paper Size')),
                'RA1' => ('RA1' . " " . xl('Paper Size')),
                'RA2' => ('RA2' . " " . xl('Paper Size')),
                'RA3' => ('RA3' . " " . xl('Paper Size')),
                'RA4' => ('RA4' . " " . xl('Paper Size')),
                'SRA0' => ('SRA0' . " " . xl('Paper Size')),
                'SRA1' => ('SRA1' . " " . xl('Paper Size')),
                'SRA2' => ('SRA2' . " " . xl('Paper Size')),
                'SRA3' => ('SRA3' . " " . xl('Paper Size')),
                'SRA4' => ('SRA4' . " " . xl('Paper Size')),
            ],
            'LETTER',
            xl('Choose Paper Size')
        ],

        'pdf_font_size' => [
            xl('PDF Font Size in Pt'),
            'num',
            '10',
            xl('Sets the font size for most PDF text in pt')
        ],

        'pdf_left_margin' => [
            xl('Left Margin (mm)'),
            'num',
            '5',
            xl('Left Margin (mm)')
        ],
        'pdf_right_margin' => [
            xl('Right Margin (mm)'),
            'num',
            '5',
            xl('Right Margin (mm)')
        ],
        'pdf_top_margin' => [
            xl('Top Margin (mm)'),
            'num',
            '5',
            xl('Top Margin (mm)')
        ],
        'pdf_bottom_margin' => [
            xl('Bottom Margin (px)'),
            'num',
            '8',
            xl('Bottom Margin (px)')
        ],
        'pdf_output' => [
            xl('Output Type'),
            [
                'D' => xl('Download'),
                'I' => xl('Inline')
            ],
            'D', //defaut
            xl("Choose Download or Display Inline"),
        ],

        'chart_label_type' => [
            xl('Patient Label Type'),
            [
                '0' => xl('None'),
                '1' => '5160',
                '2' => '5161',
                '3' => '5162'
            ],
            '1', // default
            xl('Avery Label type for printing patient labels from popups in left nav screen'),
        ],

        'barcode_label_type' => [
            xl('Barcode Label Type'),
            [
                '0' => xl('None'),
                '1' => 'std25',
                '2' => 'int25',
                '3' => 'ean8',
                '4' => 'ean13',
                '5' => 'upc',
                '6' => 'code11',
                '7' => 'code39',
                '8' => 'code93',
                '9' => 'code128',
                '10' => 'codabar',
                '11' => 'msi',
                '12' => 'datamatrix'
            ],
            '9',                              // default = None
            xl('Barcode type for printing barcode labels from popups in left nav screen.')
        ],

        'addr_label_type' => [
            xl('Print Patient Address Label'),
            'bool',                           // data type
            '1',                              // default = false
            xl('Select to print patient address labels from popups in left nav screen.')
        ],

        'env_x_width' => [
            xl('Envelope Width in mm'),
            'num',                           // data type
            '104.775',
            xl('In Portrait mode, determines the width of the envelope along the x-axis in mm')
        ],

        'env_y_height' => [
            xl('Envelope Height in mm'),
            'num',                           // data type
            '241.3',
            xl('In Portrait mode, determines the height of the envelope along the y-axis in mm')
        ],

        'env_font_size' => [
            xl('Envelope Font Size in Pt'),
            'num',                           // data type
            '14',
            xl('Sets the font of the address text on the envelope in mm')
        ],

        'env_x_dist' => [
            xl('Envelope x-axis starting pt'),
            'num',                           // data type
            '65',
            xl('Distance from the \'top\' of the envelope in mm')
        ],

        'env_y_dist' => [
            xl('Envelope y-axis starting pt'),
            'num',                           // data type
            '220',
            xl('Distance from the right most edge of the envelope in portrait position in mm')
        ],

    ],

    'Patient Banner Bar' => [
        'patient_name_display' => [
            xl('Patient Name Display'),
            [
                'btn' => xl('As Button'),
                'text' => xl('As Text Link'),
                'text-large' => xl('As Large Text Link'),
            ],
            'text-large',
            xl('How to display the patient name'),
        ],
    ],

    'Encounter Form' => [
        'default_chief_complaint' => [
            xl('Default Reason for Visit'),
            'text',                           // data type
            '',
            xl('You may put text here as the default complaint in the New Patient Encounter form.')
        ],

        'default_visit_category' => [
            xl('Default Visit Category'),
            'default_visit_category',
            '_blank',
            xl('Define a default visit category'),
        ],

        'enable_follow_up_encounters' => [
            xl('Enable follow-up encounters'),
            'bool',
            '0',
            xl('Enable follow-up encounters feature')
        ],

        'gbl_visit_referral_source' => [
            xl('Referral Source for Encounters'),
            'bool',                           // data type
            '0',                              // default = false
            xl('A referral source may be specified for each visit.')
        ],

        'gbl_visit_onset_date' => [
            xl('Onset/Hosp Date for Encounters'),
            'bool',                           // data type
            '1',                              // default = true
            xl('An onset/hospitalization date may be specified for each visit.')
        ],

        'set_pos_code_encounter' => [
            xl('Set POS code in Encounter'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This feature will allow the default POS facility code to be overridden from the encounter.')
        ],

        'set_service_facility_encounter' => [
            xl('Set Service Facility in Encounter To First Care Team Facility'),
            'bool',                           // data type
            '0',                              // default = false
            xl('This feature will allow the default service facility to be selected by the care team facility in Choices.')
        ],

        'enc_service_date' => [
            xl('Show Date of Service on Encounter Form'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl('How to display the Date of Service on the Encounter form. Defaults to the current time on a new form'),
        ],

        'enc_sensitivity_visibility' => [
            xl('Show Sensitivity on Encounter Form'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl('How to display the sensitivity option'),
        ],

        'enc_in_collection' => [
            xl('Show In Collection on Encounter Form'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl("How to display the 'In Collection' option. May be overriden by Hide Billing Widget setting"),
        ],

        'enc_enable_issues' => [
            xl('Allow Linking/Adding Issues on Encounter'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl('Allow issues to be linked or added to an encounter'),
        ],

        'enc_enable_referring_provider' => [
            xl('Show Referring Provider option on Encounters'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl('Display the Referring Provider option on Encounters'),
        ],

        'enc_enable_facility' => [
            xl('Show Facility option on Encounters'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl('Display the Referring Provider option on Encounters'),
        ],

        'enc_enable_discharge_disposition' => [
            xl('Show Discharge Disposition option on Encounters'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl('Display the Discharge Disposition option on the Encounter form'),
        ],

        'enc_enable_visit_category' => [
            xl('Show Visit Category option on Encounters'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl('Show Visit Category option on Encounters'),
        ],

        'enc_enable_class' => [
            xl('Show Encounter Class option on Encounters'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl('Show Encounter Class option on Encounters'),
        ],

        'enc_enable_type' => [
            xl('Show Encounter Type option on Encounters'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::SHOW_ALL,
            xl('Show Encounter Type option on Encounters'),
        ],

        'enc_enable_ordering_provider' => [
            xl('Show Ordering Provider option on Encounters'),
            getDefaultRenderListOptions(),
            RenderFormFieldHelper::HIDE_ALL,
            xl('Display the Ordering Provider option on Encounters'),
        ],
    ],

    'Questionnaires' => [
        'questionnaire_display_LOINCnote' => [
            xl('Display LOINC note on questionnaires'),
            [
                '0' => xl('At the top of the page only'),
                '1' => xl('At the foot of the page only'),
                '2' => xl('At the top of the page and at the foot of the page'),
                '3' => xl('Do not display the note')
            ],
            '0' ,                          // default = display at top of form
            xl('Configure where LOINC statement should be displayed')
        ],

        'questionnaire_display_style' => [
            xl('Questionnaire Form Display Style'),
            [
                '0' => xl('OpenEMR Auto Select Dark/Light Themed Version'),
                '1' => xl('LForms Project Maintained Light Version(Original)'),
                '3' => xl('OpenEMR Light Theme Version Always'),
                '4' => xl('OpenEMR Dark Theme Version Always'),
            ],
            '0' ,                          // default = display at top of form
            xl('Choose OpenEMR auto select based on core theme styles(OpenEMR dark theme turns on Questionnaire dark, LForms project maintained light styles(Original) or default to always dark or light regardless of core themes.')
        ],

        'questionnaire_display_fullscreen' => [
            xl('Turn off full screen display for Questionnaire Forms'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Default form width of Questionnaire display.')
        ],
    ],
];


if (!empty($GLOBALS['ippf_specific'])) {
    $GLOBALS['GLOBALS_METADATA']['IPPF Menu'] = [

        'gbl_menu_stats_ippf' => [
            xl('IPPF Statistics Reporting'),
            'bool',                           // data type
            '1',                              // default
            xl('IPPF statistical reports.')
        ],

        'gbl_menu_stats_gcac' => [
            xl('GCAC Statistics Reporting'),
            'bool',                           // data type
            '0',                              // default
            xl('GCAC statistical reports.')
        ],

        'gbl_menu_stats_ma' => [
            xl('MA Statistics Reporting'),
            'bool',                           // data type
            '1',                              // default
            xl('MA statistical reports.')
        ],

        'gbl_menu_stats_cyp' => [
            xl('CYP Statistics Reporting'),
            'bool',                           // data type
            '1',                              // default
            xl('CYP statistical reports.')
        ],

        'gbl_menu_stats_daily' => [
            xl('Daily Statistics Reporting'),
            'bool',                           // data type
            '0',                              // default
            xl('Daily statistical reports.')
        ],

        'gbl_menu_stats_c3' => [
            xl('C3 Statistics Reporting'),
            'bool',                           // data type
            '0',                              // default
            xl('C3 statistical reports.')
        ],

        'gbl_menu_stats_cc' => [
            xl('Cervical Cancer Reporting'),
            'bool',                           // data type
            '0',                              // default
            xl('Cervical cancer statistical reports.')
        ],

        'gbl_menu_stats_sinadi' => [
            xl('SINADI Report'),
            'bool',                           // data type
            '0',                              // default
            xl('Uruguay SINADI statistical report.')
        ],

        'gbl_menu_visits_by_item' => [
            xl('Visits by Item Report'),
            'bool',                           // data type
            '0',                              // default
            xl('Visits by Item Report')
        ],

        'gbl_menu_acct_trans' => [
            xl('Accounting Transactions Export'),
            'bool',                           // data type
            '0',                              // default
            xl('Accounting transactions export to CSV')
        ],

        'gbl_menu_projects' => [
            xl('Restricted Projects Reporting'),
            'bool', // data type
            '0', // default
            xl('For IPPF Belize and maybe others')
        ],

        'gbl_menu_surinam_insurance' => [
            xl('LOBI Insurers Report'),
            'bool', // data type
            '0', // default
            xl('For IPPF Suriname and maybe others')
        ],

        'gbl_menu_netsuite' => [
            xl('NetSuite Reports'),
            'bool', // data type
            '0', // default
            xl('For NetSuite financial integration')
        ],

        'gbl_menu_ive_clients' => [
            xl('IVE Client List'),
            'bool',                           // data type
            '0',                              // default
            xl('Client List of IVE Activity')
        ],

        'gbl_menu_shifts' => [
            xl('Shifts Reporting'),
            'bool', // data type
            '0', // default
            xl('For IPPF Argentina and maybe others')
        ],

        'gbl_menu_service_and_client_volume' => [
            xl('Service and Client Volume Report'),
            'bool', // data type
            '1', // default
            xl('Service and Client Volume Report')
        ],
    ];

    $GLOBALS['GLOBALS_METADATA']['IPPF Features'] = [

        'gbl_rapid_workflow' => [
            xl('Rapid Workflow Option'),
            [
                '0' => xl('None'),
                'LBFmsivd' => xl('MSI (requires LBFmsivd form)'),
                'fee_sheet' => xl('Fee Sheet and Checkout'),
            ],
            '0',                              // default
            xl('Activates custom work flow logic')
        ],

        'gbl_new_acceptor_policy' => [
            xl('New Acceptor Policy'),
            [
                '0' => xl('Not applicable'),
                '1' => xl('Simplified; Contraceptive Start Date on Tally Sheet'),
                '3' => xl('Contraception Form; Acceptors New to Modern Contraception'),
            ],
            '1',                              // default
            xl('Applicable only for family planning clinics')
        ],

        'gbl_min_max_months' => [
            xl('Min/Max Inventory as Months'),
            'bool',                           // data type
            '1',                              // default = true
            xl('Min/max inventory is expressed as months of supply instead of units')
        ],

        'gbl_restrict_provider_facility' => [
            xl('Restrict Providers by Facility'),
            'bool',                           // data type
            '0',                              // default
            xl('Limit service provider selection according to the facility of the logged-in user.')
        ],

        'gbl_checkout_line_adjustments' => [
            xl('Adjustments at Checkout'),
            [
                '0' => xl('Invoice Level Only'),
                '1' => xl('Line Items Only'),
                '2' => xl('Invoice and Line Levels'),
            ],
            '1',                              // default = line items only
            xl('Discounts at checkout time may be entered per invoice or per line item or both.')
        ],

        'gbl_checkout_charges' => [
            xl('Unit Price in Checkout and Receipt'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Include line item unit price amounts in checkout and receipts.')
        ],

        'gbl_charge_categories' => [
            xl('Customers in Checkout and Receipt'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Include Customers in checkout and receipts. See the Customers list.')
        ],

        'gbl_auto_create_rx' => [
            xl('Automatically Create Prescriptions'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Prescriptions may be created from the Fee Sheet.')
        ],

        'gbl_checkout_receipt_note' => [
            xl('Checkout Receipt Note'),
            'text',                           // data type
            '',
            xl('This note goes on the bottom of every checkout receipt.')
        ],

        'gbl_custom_receipt' => [
            xl('Custom Checkout Receipt'),
            [
                '0' => xl('None'),
                'checkout_receipt_general.inc.php' => xl('POS Printer'),
                'checkout_receipt_panama.inc.php' => xl('Panama'),
            ],
            '0',                              // default
            xl('Present an additional PDF custom receipt after checkout.')
        ],

        'gbl_ma_ippf_code_restriction' => [
            xl('Allow More than one MA/IPPF code mapping'),
            'bool',                           // data type
            '0',                              // default = false
            xl('Disable the restriction of only one IPPF code per MA code in superbill')
        ],

        'gbl_uruguay_asse_url' => [
            xl('Uruguay ASSE URL'),
            'text',                           // data type
            '',
            xl('URL of ASSE SOAP server. Must be blank if not a Uruguay site. Enter "test" for dummy data.')
        ],

        'gbl_uruguay_asse_token' => [
            xl('Uruguay ASSE Token'),
            'text',                           // data type
            '',
            xl('Token for connection to ASSE SOAP server')
        ],
    ];
} // end if ippf_specific

if (empty($skipGlobalEvent)) {
    $globalsInitEvent = new GlobalsInitializedEvent(new GlobalsService($GLOBALS_METADATA, $USER_SPECIFIC_GLOBALS, $USER_SPECIFIC_TABS));
    $globalsInitEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch($globalsInitEvent, GlobalsInitializedEvent::EVENT_HANDLE, 10);
    $globalsService = $globalsInitEvent->getGlobalsService()->save();
}
