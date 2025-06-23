<?php

/**
 * Portal Card
 *
 * A class representing the Patient Portal card displayed on the MRD.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Patient\Cards;

use OpenEMR\Events\Patient\Summary\Card\RenderEvent;
use OpenEMR\Events\Patient\Card\Card;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\SectionEvent;

class PortalCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/partials/portal.html.twig';

    private const CARD_ID = 'patient_portal';

    private $opts = [];

    /**
     * @var EventDispatcher
     */
    private $ed;

    public function __construct()
    {
        global $GLOBALS;
        $this->ed = $GLOBALS['kernel']->getEventDispatcher();

        $this->setOpts();
        parent::__construct($this->opts);

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
        $this->addListener();
    }

    private function renderCard()
    {
        $dispatchResult = $this->ed->dispatch(new RenderEvent(self::CARD_ID), RenderEvent::EVENT_HANDLE);
        $this->opts['templateVariables']['prependedInjection'] = $dispatchResult->getPrependedInjection();
        $this->opts['templateVariables']['appendedInjection'] = $dispatchResult->getAppendedInjection();
    }

    // used in twigs
    private function setOpts()
    {
        global $GLOBALS;
        global $pid;
        // RM get name for 'choices' group, i.e. group 4 in 'layout_gropu_properties' table in db
        $sql = "SELECT grp_title FROM layout_group_properties WHERE grp_group_id = 4 AND grp_form_id = 'DEM'";
        $res = sqlStatement($sql);
        $nrow = sqlFetchArray($res);
        $groupName = ($nrow['grp_title']  !== '' ? $nrow['grp_title'] : 'Choices');

        $this->opts = [
            'acl' => ['patients', 'demo'],
            'initiallyCollapsed' => (getUserSetting(self::CARD_ID . '_expand') == 0),
            'add' => false,
            'edit' => false,
            'collapse' => true,
            'templateFile' => self::TEMPLATE_FILE,
            'identifier' => self::CARD_ID,
            'title' => xl('Patient Portal') . ' / ' . xl('API Access'),
            'templateVariables' => [
               'allowpp' => $groupName,
                'isPortalEnabled' => isPortalEnabled(),
                'isPortalSiteAddressValid' => isPortalSiteAddressValid(),
                'isPortalAllowed' => isPortalAllowed($pid),
                'portalLoginHref' => $GLOBALS['webroot'] . "/interface/patient_file/summary/create_portallogin.php",
                'isApiAllowed' => isApiAllowed($pid),
                'areCredentialsCreated' => areCredentialsCreated($pid),
                'isContactEmail' => isContactEmail($pid),
                'isEnforceSigninEmailPortal' => isEnforceSigninEmailPortal($pid)
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
