<?php

/**
 * Contains all of the translations used by the client side portion of the TeleHealth.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Controller;

use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use OpenEMR\Common\Csrf\CsrfUtils;
use Twig\Environment;

class TeleHealthFrontendSettingsController
{
    /**
     * @var Environment The twig environment
     */
    private $twig;

    /**
     * @var TelehealthGlobalConfig
     */
    private $config;

    public function __construct(string $assetPath, Environment $twig, TelehealthGlobalConfig $config)
    {
        $this->assetPath = $assetPath;
        $this->twig = $twig;
        $this->config = $config;
    }

    public function renderFrontendSettings($isPatient = true)
    {
        $assetPath = $this->assetPath;
        // strip off the assets, and public folder to get to the base of our module directory
        // assetPath is a url path but dirname still operates fine on both / and \ characters so we are fine here.
        $modulePath = dirname(dirname($assetPath)) . "/"; // make sure to end with a path
        $data = [
            'settings' => [
                'translations' => $this->getTranslationSettings()
                ,'modulePath' => $modulePath
                ,'assetPath' => $assetPath
                ,'fhirPath' => $this->config->getFHIRPath()
                ,'apiCSRFToken' => ''
                ,'features' => [
                    'thirdPartyInvitations' => $this->config->isThirdPartyInvitationsEnabled()
                    ,'minimizeWindow' => [
                        'enabled' => true
                        ,'defaultPosition' => $this->config->getMinimizedSessionDefaultPosition()
                    ]
                ]
            ]
        ];
        // we only allow the CSRF token if we are not a patient
        // if we ever need to allow local OpenEMR api access to patients we can remove this check, but to minimize api attack surface
        // we will prohibit it for now until a better threat analysis has been done.
        if (!$isPatient) {
            $data['settings']['apiCSRFToken'] = CsrfUtils::collectCsrfToken('api');
        }
        echo $this->twig->render("comlink/telehealth-frontend-settings.js.twig", $data);
    }
    public function getTranslationSettings()
    {
        $translations = [
                'CALL_CONNECT_FAILED' => xl("Failed to connect the call."),
                'BRIDGE_FAILED' => xl("Failed to establish a connection with the telehealth service provider.  Check your internet connection or contact support to verify the service is setup correctly."),
                'SESSION_LAUNCH_FAILED' => xl("There was an error in launching your telehealth session.  Please try again or contact support"),
                'DUPLICATE_SESSION' => xl("You are already in a conference session.  Please hangup the current call to start a new telehealth session"),
                'HOST_LEFT' => xl("Host left the call"),
                'PROVIDER_SESSION_START_PROMPT' => xl("Would you like to start a telehealth session with this patient (This will create an encounter if one does not exist)?"),
                'PROVIDER_SESSION_CLONE_START_PROMPT' => xl("This appointment belongs to a different provider. Would you still like to start a telehealth session (this will copy the appointment to your calendar and create an encounter if needed)?"),
                'PROVIDER_SESSION_TELEHEALTH_UNENROLLED' => xl("This is a Telehealth session appointment.  If you would like to provide telehealth sessions to your clients contact your administrator to enroll today."),
                'CONFIRM_SESSION_CLOSE' => xl("Are you sure you want to close this session?"),
                "TELEHEALTH_MODAL_TITLE" => xl("TeleHealth Session"),
                "TELEHEALTH_MODAL_CONFIRM_TITLE" => xl("Confirm Session Close"),
                "UPDATE_APPOINTMENT_STATUS" => xl("Update appointment status"),
                "STATUS_NO_SHOW" => xl("No Show"),
                "STATUS_CANCELED" => xl("Canceled"),
                "STATUS_CHECKED_OUT" => xl("Checked Out"),
                "CONFIRM" => xl("Confirm"),
                "CALENDAR_EVENT_DISABLED" => xl("TeleHealth Sessions can only be launched within two hours of the current appointment time."),
                "CALENDAR_EVENT_COMPLETE" => xl("This TeleHealth appointment has been completed."),
                "STATUS_SKIP_UPDATE" => xl("Skip Update"),
                "STATUS_NO_UPDATE" => xl("No Change"),
                "STATUS_OTHER" => xl("Other"),
                'APPOINTMENT_STATUS_UPDATE_FAILED' => xl('There was an error in saving the telehealth appointment status.  Please contact support or update the appointment manually in the calendar'),
                'OPERATION_FAILED' => xl("There was a system error in completing this operation. Please try again or contact customer support if you continue to experience problems"),
                'SEARCH_REQUIRES_INPUT' => xl("Please enter a value to search the patient list"),
                'SEARCH_RESULTS_NOT_FOUND' => xl("No search results were found"),
                'PATIENT_INVITATION_PROCESSING' => xl("Sending Invitation"),
                'PATIENT_INVITATION_SUCCESS' => xl("Invitation Sent"),
                'CLIPBOARD_COPY_SUCCESS' => xl("Information copied to clipboard"),
                'CLIPBOARD_COPY_FAILURE' => xl("Failed to copy information to clipboard, try again or contact support"),
                'PATIENT_CREATE_INVALID_DOB' => xl("Patient date of birth is missing, or invalid"),
                'PATIENT_CREATE_INVALID_EMAIL' => xl("Patient email is missing, invalid, or cannot be processed by this system"),
                'PATIENT_CREATE_INVALID_NAME' => xl("Patient name is missing, or invalid"),
                'PATIENT_INVITATION_GENERATED' => xl("Session Link Generated"),
                'PATIENT_INVITATION_FAILURE' => xl("There was an error in generating the session link, try again or contact support"),
                'PATIENT_SETUP_FOR_TELEHEALTH_SUCCESS' => xl("Patient has portal credentials and is setup for telehealth sessions"),
                'PATIENT_SETUP_FOR_TELEHEALTH_FAILED' => xl("Patient is missing portal credentials, has not authorized the portal, or has not verified their email address."),
                'PATIENT_SETUP_FOR_TELEHEALTH_VALIDATING' => xl("Checking if patient is setup for telehealth appointment...")
        ];
        return $translations;
    }
}
