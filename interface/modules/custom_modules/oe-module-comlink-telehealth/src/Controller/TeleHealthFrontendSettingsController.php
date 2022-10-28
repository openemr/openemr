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

use Twig\Environment;

class TeleHealthFrontendSettingsController
{
    /**
     * @var Environment The twig environment
     */
    private $twig;

    public function __construct(string $assetPath, Environment $twig)
    {
        $this->assetPath = $assetPath;
        $this->twig = $twig;
    }

    public function renderFrontendSettings()
    {
        $assetPath = $this->assetPath;
        // strip off the assets, and public folder to get to the base of our module directory
        $modulePath = dirname(dirname($assetPath)) . "/"; // make sure to end with a path
        echo $this->twig->render("comlink/telehealth-frontend-settings.js.twig", [
            'settings' => [
                'translations' => $this->getTranslationSettings()
                ,'modulePath' => $modulePath
                ,'assetPath' => $assetPath
            ]
        ]);
    }
    public function getTranslationSettings()
    {
        $translations = [
                'CALL_CONNECT_FAILED' => xl("Failed to connect the call."),
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
                'APPOINTMENT_STATUS_UPDATE_FAILED' => xl('There was an error in saving the telehealth appointment status.  Please contact support or update the appointment manually in the calendar')
        ];
        return $translations;
    }
}
