<?php

namespace OEMR\OpenEMR\Modules\Voicenote\Controller;

use Twig\Environment;

class VoicenotePopupController
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

    public function renderVoicenotePopup()
    {
        $assetPath = $this->assetPath;
        $modulePath = dirname(dirname($assetPath)) . "/"; // make sure to end with a path
        echo $this->twig->render("oemr/voicenote-popup.twig", ['assetPath' => $assetPath]);
    }

    public function renderVoicenoteLayout()
    {
        $assetPath = $this->assetPath;
        $modulePath = dirname(dirname($assetPath)) . "/"; // make sure to end with a path
        echo $this->twig->render("oemr/voicenote-layout.twig", []);
    }
}
