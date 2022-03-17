<?php

/*
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com>
 */

namespace OpenEMR\Patient\Cards;

use OpenEMR\Events\Patient\Summary\Card\RenderEvent;
use OpenEMR\Events\Patient\Card\Card;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\SectionEvent;

class PortalCard extends CardModel
{

    const TEMPLATE_FILE = 'patient/partials/portal.html.twig';

    const CARD_ID = 'patient_portal';

    private $opts = [];

    /**
     * @var EventDispatcher
     */
    private $ed;

    public function __construct()
    {
        global $GLOBALS;
        $this->ed = $GLOBALS['kernel']->getEventDispatcher();

        $this->title = xl('Patient Portal');
        $this->identifier = self::CARD_ID;

        $this->processCard();
    }

    /**
     * Handle everything
     *
     * Render the actual Card, including dispatching the Render Event. Set the options for the Section render, attach
     * a listener to the SectionEvent. This is called from the constructor and cannot be accessed publicly.
     *
     * @return void
     */
    private function processCard()
    {
        $this->renderCard();
        $this->setOpts();
        $this->addListener();
    }

    private function renderCard()
    {
        $dispatchResult = $this->ed->dispatch(RenderEvent::EVENT_HANDLE, new RenderEvent(self::CARD_ID));
        $this->opts['templateVariables']['prependedInjection'] = $dispatchResult->getPrependedInjection();
        $this->opts['templateVariables']['appendedInjection'] = $dispatchResult->getAppendedInjection();
    }

    private function setOpts()
    {
        $this->opts = [
            'acl' => ['patients', 'dem'],
            'initiallyCollapsed' => false,
            'add' => true,
            'edit' => false,
            'collapse' => true,
            'templateFile' => self::TEMPLATE_FILE,
            'templateVariables' => [
                'portalAuthorized' => portalAuthorized($pid),
                'portalLoginHref' => $portal_login_href,
                'title' => xl('Patient Portal'),
                'id' => self::CARD_ID,
                'initiallyCollapsed' => (getUserSetting(self::CARD_ID) == 0) ? false : true,
            ],
        ];
    }

    private function getOpts()
    {
        return $this->opts;
    }

    private function addListener()
    {
        $this->ed->addListener(SectionEvent::EVENT_HANDLE, [$this, 'addPatientCardToSection']);
    }

    public function addPatientCardToSection(SectionEvent $e)
    {
        if ($e->getSection('secondary')) {
            $card = new CardModel($this->getOpts());
            $e->addCard($card);
        }
        return $e;
    }
}
