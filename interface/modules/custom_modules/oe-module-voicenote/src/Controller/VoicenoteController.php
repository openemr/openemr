<?php

namespace OEMR\OpenEMR\Modules\Voicenote\Controller;

use OEMR\OpenEMR\Modules\Voicenote\VoicenoteGlobalConfig;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;
use OpenEMR\Events\Core\ScriptFilterEvent;
use OpenEMR\Events\Core\StyleFilterEvent;
use OpenEMR\Common\Utils\CacheUtils;
use OpenEMR\Common\Logging\SystemLogger;

class VoicenoteController
{
    private $logger;
    private $assetPath;
    /**
     * @var The database record if of the currently logged in user
     */
    private $loggedInUserId;

    /**
     * @var Environment Twig container
     */
    private $twig;

    public function __construct(VoicenoteGlobalConfig $config, Environment $twig, SystemLogger $logger, $assetPath, $loggedInUserId)
    {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->assetPath = $assetPath;
        $this->loggedInUserId = $loggedInUserId;
    }

    public function subscribeToEvents(EventDispatcher $eventDispatcher)
    {
        $eventDispatcher->addListener(ScriptFilterEvent::EVENT_NAME, [$this, 'addVoicenoteJavascript']);
        $eventDispatcher->addListener(StyleFilterEvent::EVENT_NAME, [$this, 'addVoicenoteStylesheet']);
    }

    public function addVoicenoteStylesheet(StyleFilterEvent $event)
    {
        $pageName = basename($event->getPageName());
        if($pageName == "main.php") {
            $styles = $event->getStyles();
            $styles[] = $this->getAssetPath() . CacheUtils::addAssetCacheParamToPath("css/voicenote.css");
            $event->setStyles($styles);
        }
    }

    public function addVoicenoteJavascript(ScriptFilterEvent $event)
    {
        $pageName = $event->getPageName();
        $scripts = $event->getScripts();
        //echo $pageName;
        if($pageName == "login.php") return false;

        $scripts[] = $this->getAssetPath() . "../index.php?action=get_voicenote_settings";

        if($pageName == "main.php") {
            $scripts[] = $this->getAssetPath() . "js/voicenote.js";
        }

        $scripts[] = $this->getAssetPath() . "js/vnote.js";
        $event->setScripts($scripts);
    }

    private function getAssetPath()
    {
        return $this->assetPath;
    }
}