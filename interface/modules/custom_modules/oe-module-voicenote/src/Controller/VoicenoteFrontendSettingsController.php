<?php

namespace OEMR\OpenEMR\Modules\Voicenote\Controller;

use Twig\Environment;

class VoicenoteFrontendSettingsController
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
        echo $this->twig->render("oemr/voicenote-frontend-settings.js.twig", [
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
        ];
        return $translations;
    }
}
