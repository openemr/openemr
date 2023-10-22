<?php

/**
 * Capability holds the enumerated capabilities for SMART
 * The SMART extension capabilites that our system supports
 * @see http://hl7.org/fhir/smart-app-launch/conformance/index.html
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\SMART;

class Capability
{
    /**
     * The SMART extension capabilites that our system supports
     * @see https://hl7.org/fhir/smart-app-launch/conformance/index.html
     *
     * All of these capabilities for MU3 are required to be implemented before HIT certification
     * can be complete.
     * @see ONC final rule commentary https://www.federalregister.gov/d/2020-07419/p-1184 Accessed on December 9th 2020
     */
    const SUPPORTED_CAPABILITIES = [self::LAUNCH_EHR, self::CONTEXT_BANNER, self::CONTEXT_EHR_PATIENT
        , self::CONTEXT_STYLE, self::SSO_OPENID_CONNECTION, self::CLIENT_CONFIDENTIAL_SYMMETRIC, self::PERMISSION_USER
        , self::CONTEXT_STANDALONE_PATIENT, self::LAUNCH_STANDALONE, self::PERMISSION_PATIENT
        , self::PERMISSION_OFFLINE, self::CLIENT_PUBLIC];

    const FHIR_SUPPORTED_CAPABILITIES = [
        self::LAUNCH_EHR, self::CONTEXT_BANNER_PASSTHROUGH, self::CONTEXT_EHR_PATIENT
        , self::CONTEXT_STYLE_PASSTHROUGH, self::SSO_OPENID_CONNECTION, self::CLIENT_CONFIDENTIAL_SYMMETRIC, self::PERMISSION_USER
        , self::CONTEXT_STANDALONE_PATIENT, self::LAUNCH_STANDALONE, self::PERMISSION_PATIENT
        , self::PERMISSION_OFFLINE, self::CLIENT_PUBLIC
    ];

    // support for SMART’s EHR Launch mode
    const LAUNCH_EHR = 'launch-ehr';

    // support for SMART’s Standalone Launch mode
    const LAUNCH_STANDALONE = 'launch-standalone';

    // support for SMART’s public client profile (no client authentication)
    const CLIENT_PUBLIC = 'client-public';

    // support for SMART’s confidential client profile (symmetric client secret authentication)
    const CLIENT_CONFIDENTIAL_SYMMETRIC = "client-confidential-symmetric";

    // support for SMART’s OpenID Connect profile
    const SSO_OPENID_CONNECTION = "sso-openid-connect";

    // support for “need patient banner” launch context (conveyed via need_patient_banner token parameter)
    const CONTEXT_BANNER = "context-banner";

    // FHIR capability statement for some reason requires this capability which is the same as context-banner...
    const CONTEXT_BANNER_PASSTHROUGH = "context-passthrough-banner";

    // support for “SMART style URL” launch context (conveyed via smart_style_url token parameter)
    // NOTE: context-style is marked in HL7 SMART as EXPERIMENTAL, so expect this to change in time
    // HL7/SMART chat forum was a bit confused by ONC's decision to include this, so again expect
    // to see this change.
    // @see SMARTSessionTokenContextBuilder->getSmartStyleURL()
    const CONTEXT_STYLE = "context-style";

    // FHIR capability statement for some reason requires this capability which is the same as context-style...
    const CONTEXT_STYLE_PASSTHROUGH = "context-passthrough-style";

    // support for patient-level launch context (requested by launch scope, conveyed via patient token parameter)
    const CONTEXT_EHR_PATIENT = "context-ehr-patient";

    // support for patient-level launch context (requested by launch scope, conveyed via encounter token parameter)
    const CONTEXT_EHR_ENCOUNTER = "context-ehr-encounter";

    // support for patient-level launch context (requested by launch/patient scope, conveyed via patient token parameter)
    const CONTEXT_STANDALONE_PATIENT = "context-standalone-patient";
    // support for encounter-level launch context (requested by launch/encounter scope, conveyed via encounter token
    const CONTEXT_STANDALONE_ENCOUNTER = "context-standalone-encounter";

    const PERMISSION_ONLINE = "permission-online";

    // support for refresh tokens (requested by offline_access scope)
    const PERMISSION_OFFLINE = "permission-offline";

    // support for patient-level scopes (e.g. patient/Observation.read)
    const PERMISSION_PATIENT = "permission-patient";

    // support for user-level scopes (e.g. user/Appointment.read)
    const PERMISSION_USER = "permission-user";

    /**
     * Support for SMART v1 scopes (e.g. patient/Observation.read) - This is a SMART 2.0 capability identifier in R5
     */
    const PERMISSION_V1 = "permission-v1";

    /**
     * Support for SMART v2 scopes with more granular controls (e.g. patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs)
     */
    const PERMISSION_V2 = "permission-v2";
}
