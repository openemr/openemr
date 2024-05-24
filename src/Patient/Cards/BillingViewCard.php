<?php

/**
 * BillingViewCard - presentation view of a patient's billing information in a card widget.
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

class BillingViewCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/card/billing.html.twig';

    private const CARD_ID = 'billing';

    private $pid;

    private $insco_name;

    private $primaryInsurance;

    private $billingNote;

    public function __construct($pid, $insco_name, $billingNote, $primaryInsurance, array $opts = [])
    {
        $this->pid = $pid;
        $this->insco_name = $insco_name;
        $this->primaryInsurance = $primaryInsurance;
        $this->billingNote = $billingNote;
        $opts = $this->setupOpts($opts);
        parent::__construct($opts);
    }
    private function setupOpts(array $opts)
    {
        $opts['acl'] = [];
        $opts['title'] = xl('Billing');
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
        $billingDataVars = $this->setupBillingData();
        return array_merge($templateVars, $billingDataVars);
    }

    private function setupBillingData()
    {
        $pid = $this->pid;
        $ed = $this->getEventDispatcher();
        $forceBillingExpandAlways = ($GLOBALS['force_billing_widget_open']) ? true : false;
        $patientbalance = get_patient_balance($pid, false);
        $insurancebalance = get_patient_balance($pid, true) - $patientbalance;
        $totalbalance = $patientbalance + $insurancebalance;
        $unallocated_amt = get_unallocated_patient_balance($pid);
        $collectionbalance = get_patient_balance($pid, false, false, true);

        $id = self::CARD_ID . "_ps_expand";
        $dispatchResult = $ed->dispatch(new RenderEvent('billing'), RenderEvent::EVENT_HANDLE);

        $viewArgs = [
            'title' => xl('Billing'),
            'id' => $id,
            'initiallyCollapsed' => (getUserSetting($id) == 0) ? true : false,
            'hideBtn' => true,
            'patientBalance' => $patientbalance,
            'insuranceBalance' => $insurancebalance,
            'totalBalance' => $totalbalance,
            'collectionBalance' => $collectionbalance,
            'unallocated' => $unallocated_amt,
            'forceAlwaysOpen' => $forceBillingExpandAlways,
            'prependedInjection' => $dispatchResult->getPrependedInjection(),
            'appendedInjection' => $dispatchResult->getAppendedInjection(),
        ];

        if (!empty($this->billingNote)) {
            $viewArgs['billingNote'] = $this->billingNote;
        }

        if (!empty($this->primaryInsurance['provider'])) {
            $viewArgs['provider'] = true;
            $viewArgs['insName'] = $this->insco_name;
            $viewArgs['copay'] = $this->primaryInsurance['copay'];
            $viewArgs['effDate'] = $this->primaryInsurance['effdate'];
            $viewArgs['effDateEnd'] = $this->primaryInsurance['effdate_end'];
        }
        return $viewArgs;
    }
}
