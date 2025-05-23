<?php

/**
 * DemographicsViewCard - presentation view of a patient's demographics information in a card widget.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Patient\Cards;

use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent;
use OpenEMR\Common\Acl\AclMain;

class DemographicsViewCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/card/tab_base.html.twig';

    private const CARD_ID = 'demographic';

    private $patientData;
    private $employerData;

    public function __construct($patientData, $employerData, array $opts = [])
    {
        $this->patientData = $patientData;
        $this->employerData = $employerData;
        $opts = $this->setupOpts($opts);
        parent::__construct($opts);
    }

    private function setupOpts(array $opts)
    {
        $opts['acl'] = ['patients', 'demo'];
        $opts['title'] = xl('Demographics');
        $opts['btnLink'] = 'demographics_full.php';
        $opts['linkMethod'] = 'html';
        $opts['edit'] = true;
        $opts['add'] = false;
        $opts['requireRestore'] = (!isset($_SESSION['patient_portal_onsite_two'])) ? true : false;
        $opts['initiallyCollapsed'] = getUserSetting("demographics_ps_expand") == 0 ? true : false;
        $opts['identifier'] = self::CARD_ID;
        $opts['templateFile'] = self::TEMPLATE_FILE;
        $opts['initiallyCollapsed'] = (getUserSetting(self::CARD_ID . '_expand') == 0);
        $opts['templateVariables'] = [];
        return $opts;
    }

    public function getTemplateVariables(): array
    {
        // having us do this allows us to defer the execution of the expensive functions until we need them
        $templateVars = parent::getTemplateVariables();
        $dataVars = $this->setupDemographicsData();
        return array_merge($templateVars, $dataVars);
    }

    private function setupDemographicsData()
    {
        $dispatchResult = $this->getEventDispatcher()->dispatch(new RenderEvent(self::CARD_ID), RenderEvent::EVENT_HANDLE);
        $auth = ACLMain::aclCheckCore('patients', 'demo', '', 'write');
        $viewArgs = [
            'requireRestore' => (!isset($_SESSION['patient_portal_onsite_two'])) ? true : false,
            'initiallyCollapsed' => (getUserSetting("demographics_ps_expand") == 0) ? true : false,
            'tabID' => "DEM",
            'title' => xl("Demographics"),
            'id' => self::CARD_ID . '_ps_expand',
            'btnLabel' => "Edit",
            'btnLink' => "demographics_full.php",
            'linkMethod' => 'html',
            'auth' => $auth,
            'result' => $this->patientData,
            'result2' => $this->employerData,
            'prependedInjection' => $dispatchResult->getPrependedInjection(),
            'appendedInjection' => $dispatchResult->getAppendedInjection()
        ];
        return $viewArgs;
    }
}
