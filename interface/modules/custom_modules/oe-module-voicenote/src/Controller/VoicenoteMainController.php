<?php

namespace OEMR\OpenEMR\Modules\Voicenote\Controller;

use OEMR\OpenEMR\Modules\Voicenote\Bootstrap;
use OEMR\OpenEMR\Modules\Voicenote\Controller\AddonFrontendSettingsController;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class VoicenoteMainController
{
    const PATIENT_PORTAL_USER  = 'portal-user';

    const APPOINTMENT_TRANSFER_STATUS = "TRNSFR";

    /**
     * Status code sent to client to disable registration check as the user is not enrolled or their enrollment is
     * suspended.
     */
    const REGISTRATION_CHECK_REQUIRES_ENROLLMENT_CODE = 402;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var boolean  Whether we are running as a patient in the portal context
     */
    private $isPatient;

    /**
     * @var string The location where the module assets are stored
     */
    private $assetPath;

    /**
     * @var \OEMR\OpenEMR\Modules\Voicenote\Repository\VoicenoteSessionRepository
     */
    private $sessionRepository;


    public function __construct(Environment $twig, LoggerInterface $logger, $assetPath, $isPatient = false)
    {
        $this->assetPath = $assetPath;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->isPatient = $isPatient;
    }

    public function dispatch($action, $queryVars)
    {
        $this->logger->debug("VoicenoteMainController->dispatch()", ['action' => $action, 'queryVars' => $queryVars, 'isPatient' => $this->isPatient]);

        if ($action == 'get_voicenote_settings') {
            return $this->getVoicenoteSettingsAction($queryVars);
        } if ($action == 'voicenote_popup') {
            return $this->getVoicenotePopupAction($queryVars);
        } if ($action == 'voicenote_layout') {
            return $this->getVoicenoteLayoutAction($queryVars);
        } else {
            $this->logger->error(self::class . '->dispatch() invalid action found', ['action' => $action]);
            echo "action not supported";
            return;
        }
    }
    
    public function getVoicenoteSettingsAction($queryVars)
    {
        $controller = new VoicenoteFrontendSettingsController($this->assetPath, $this->twig);
        echo $controller->renderFrontendSettings($queryVars);
    }

    public function getVoicenotePopupAction($queryVars)
    {
        $controller = new VoicenotePopupController($this->assetPath, $this->twig);
        echo $controller->renderVoicenotePopup($queryVars);
    }

    public function getVoicenoteLayoutAction($queryVars)
    {
        $controller = new VoicenotePopupController($this->assetPath, $this->twig);
        echo $controller->renderVoicenoteLayout($queryVars);
    }
}
