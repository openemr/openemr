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

namespace OpenEMR\Services\Globals;

interface GlobalSettingSection
{
    const APPEARANCE = "Appearance";
    const BILLING = "Billing";
    const BRANDING = "Branding";
    const CALENDAR = "Calendar";
    const CARE_COORDINATION = "Carecoordination";
    const CDR = "CDR";
    const CONNECTORS = "Connectors";
    const DOCUMENTS = "Documents";
    const ENCOUNTER_FORM = "Encounter Form";
    const E_SIGN = "E-Sign";
    const FEATURES = "Features";
    const INSURANCE = "Insurance";
    const LOCALE = "Locale";
    const LOGGING = "Logging";
    const LOGIN_PAGE = "Login Page";
    const MISCELLANEOUS = "Miscellaneous";
    const NOTIFICATIONS = "Notifications";
    const PATIENT_BANNER_BAR = "Patient Banner Bar";
    const PDF = "PDF";
    const PORTAL = "Portal";
    const QUESTIONNAIRES = "Questionnaires";
    const REPORT = "Report";
    const RX = "Rx";
    const SECURITY = "Security";

    const ALL_SECTIONS = [
        self::APPEARANCE,
        self::BILLING,
        self::BRANDING,
        self::CALENDAR,
        self::CARE_COORDINATION,
        self::CDR,
        self::CONNECTORS,
        self::DOCUMENTS,
        self::ENCOUNTER_FORM,
        self::E_SIGN,
        self::FEATURES,
        self::INSURANCE,
        self::LOCALE,
        self::LOGGING,
        self::LOGIN_PAGE,
        self::MISCELLANEOUS,
        self::NOTIFICATIONS,
        self::PATIENT_BANNER_BAR,
        self::PDF,
        self::PORTAL,
        self::QUESTIONNAIRES,
        self::REPORT,
        self::RX,
        self::SECURITY,
    ];

    const USER_SPECIFIC_SECTIONS = [
        self::APPEARANCE,
        self::BILLING,
        self::CALENDAR,
        self::CARE_COORDINATION,
        self::CDR,
        self::CONNECTORS,
        self::FEATURES,
        self::LOCALE,
        self::QUESTIONNAIRES,
        self::REPORT,
    ];
}
