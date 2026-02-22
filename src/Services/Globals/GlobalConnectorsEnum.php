<?php

/*
 * GlobalConnectorsEnum.php  Holds the list of Connectors settings in Globals that are found in /library/globals.inc.php
 * This allows constants to be type checked and autocompleted in IDEs when used in code
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Globals;

enum GlobalConnectorsEnum: string
{
    case SITE_ADDRESS_OAUTH = 'site_addr_oath';
    case REST_FHIR_API = 'rest_fhir_api';
    case REST_SYSTEM_SCOPES_API = 'rest_system_scopes_api';
    case REST_API = 'rest_api';
    case REST_PORTAL_API = 'rest_portal_api';
    case OAUTH_PASSWORD_GRANT = 'oauth_password_grant';
    case OAUTH_APP_MANUAL_APPROVAL = 'oauth_app_manual_approval';
    case OAUTH_EHR_LAUNCH_AUTHORIZATION_FLOW_SKIP = 'oauth_ehr_launch_authorization_flow_skip';
    case FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION = 'fhir_us_core_profile_version';
    // TODO: move the rest of the Connectors settings from globals.inc.php into this file

    // TODO: add methods for handling things like the descriptions and supported data types if needed
}
