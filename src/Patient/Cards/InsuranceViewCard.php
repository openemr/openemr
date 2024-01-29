<?php

/**
 * InsuranceViewCard - presentation view of a patient's insurance information in a card widget.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Patient\Cards;

use OpenEMR\Billing\EDI270;
use OpenEMR\Billing\InsurancePolicyTypes;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent;
use InsuranceCompany;
use OpenEMR\Services\InsuranceService;

class InsuranceViewCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/card/insurance.html.twig';

    private const CARD_ID_EXPAND = 'insurance_ps_expand';

    private const CARD_ID = 'insurance';

    private $pid;

    private $policy_types;

    public function __construct($pid, array $opts = [])
    {
        $this->pid = $pid;
        $this->policy_types = InsurancePolicyTypes::getTranslatedPolicyTypes();
        $opts = $this->setupOpts($opts);
        parent::__construct($opts);
    }

    private function setupOpts(array $opts)
    {
        $initiallyCollapsed = $this->getUserCardSetting(self::CARD_ID_EXPAND) == 0;
        $authCheck = AclMain::aclCheckCore('patients', 'demo', '', 'write');
        $newOpts = [
            'acl' => ['patients', 'demo'],
            'initiallyCollapsed' => $initiallyCollapsed,
            'add' => true,
            'edit' => true,
            'collapse' => true,
            'templateFile' => self::TEMPLATE_FILE,
            'identifier' => self::CARD_ID,
            'title' => xl('Insurance'),
            'templateVariables' => [
                'title' => xl("Insurance"),
                'id' => self::CARD_ID_EXPAND,
                'btnLabel' => "Edit",
                'btnLink' => "insurance_edit.php",
                'linkMethod' => 'html',
                'initiallyCollapsed' => $initiallyCollapsed ? true : false,
                'enable_eligibility_requests' => $GLOBALS['enable_eligibility_requests'],
                'auth' => $authCheck
            ]
        ];
        return array_merge($opts, $newOpts);
    }

    public function getTemplateVariables(): array
    {
        // having us do this allows us to defer the execution of the expensive functions until we need them
        $templateVars = parent::getTemplateVariables();
        $dispatchResult = $this->getEventDispatcher()->dispatch(new RenderEvent(self::CARD_ID), RenderEvent::EVENT_HANDLE);
        $templateVars['prependedInjection'] = $dispatchResult->getPrependedInjection();
        $templateVars['appendedInjection'] = $dispatchResult->getAppendedInjection();
        $templateVars['ins'] = $this->getInsuranceData();
        $templateVars['types'] = ['primary', 'secondary', 'tertiary'];
        $templateVars['eligibility'] = $this->getEligibilityOutput();
        return $templateVars;
    }

    private function getInsuranceTypeArray()
    {
        // TODO: @adunsulag should we move this into a class?  It's copied everywhere...
        if ($GLOBALS['insurance_only_one']) {
            $insurance_array = array('primary');
        } else {
            $insurance_array = array('primary', 'secondary', 'tertiary');
        }
        return $insurance_array;
    }
    private function getInsuranceData()
    {
        $pid = $this->pid;

        $insuranceService = new InsuranceService();
//        $insurancePolicies = $insuranceService->search(['pid' => $pid]);

        $insurancePolicies = $insuranceService->getPoliciesOrganizedByTypeForPatientPid($pid);
        $policiesByType = [];

        foreach ($insurancePolicies as $type => $organizedPolicies) {
            $mostRecentEffectiveDate = $organizedPolicies['current']['date'];
            $populatedCurrent = $this->populateInsurancePolicy($organizedPolicies['current']);

            // if its a primary insurance we always include it, but if its self-pay on secondary/tertiary we just don't want to display those
            if ($type == 'primary' || !empty($populatedCurrent['provider'])) {
                $policiesByType[$type] = [
                    'current' => $populatedCurrent
                    , 'policies' => [$populatedCurrent]
                ];
                foreach ($organizedPolicies['history'] as $policy) {
                    $policiesByType[$type]['policies'][] = $this->populateInsurancePolicy($policy, $mostRecentEffectiveDate);
                }
            }
        }
        return $policiesByType;
    }

    private function populateInsurancePolicy($row, $mostRecentEffectiveDate = null)
    {
        $policy_types = $this->policy_types;
        $row['date_end_missing'] = false;
        if ($row['provider']) {
            $icobj = new InsuranceCompany($row['provider']);
            $adobj = $icobj->get_address();
            $row['insco'] = [
                'name' => trim($icobj->get_name()),
                'display_name' => $icobj->get_display_name(),
                'address' => [
                    'line1' => $adobj->get_line1(),
                    'line2' => $adobj->get_line2(),
                    'city' => $adobj->get_city(),
                    'state' => $adobj->get_state(),
                    'postal' => $adobj->get_zip(),
                    'country' => $adobj->get_country()
                ],
            ];
            $row['policy_type'] = (!empty($row['policy_type'])) ? $policy_types[$row['policy_type']] : false;
            $row['dispFromDate'] = $row['date'] ? true : false;
            $mname = ($row['subscriber_mname'] != "") ? $row['subscriber_mname'] : "";
            $row['subscriber_full_name'] = str_replace("%mname%", $mname, "{$row['subscriber_fname']} %mname% {$row['subscriber_lname']}");
        } else {
            $row['dispFromDate'] = $row['date'] ? true : false;
            $row['insco'] = [
                'name' => xl('Self-Pay'),
                'display_name' => xl('Self-Pay'),
                'address' => [
                    'line1' => '',
                    'line2' => '',
                    'city' => '',
                    'state' => '',
                    'postal' => '',
                    'country' => ''
                ],
            ];
            $row['policy_type'] = false;
            $mname = ''; //($row['subscriber_mname'] != "") ? $row['subscriber_mname'] : "";
            $row['subscriber_full_name'] = ' '; // str_replace("%mname%", $mname, "{$row['subscriber_fname']} %mname% {$row['subscriber_lname']}");
        }
        return $row;
    }

    private function getEligibilityOutput()
    {
        $output = '';
        $pid = $this->pid;
        if ($GLOBALS["enable_eligibility_requests"]) {
            if (($_POST['status_update'] ?? '') === 'true') {
                unset($_POST['status_update']);
                $showEligibility = true;
                $ok = EDI270::requestEligibleTransaction($pid);
                if ($ok === true) {
                    ob_start();
                    EDI270::showEligibilityInformation($pid, false);
                    $output = ob_get_contents();
                    ob_end_clean();
                } else {
                    $output = $ok;
                }
            } else {
                ob_start();
                EDI270::showEligibilityInformation($pid, true);
                $output = ob_get_contents();
                ob_end_clean();
            }
        } else {
            ob_start();
            EDI270::showEligibilityInformation($pid, true);
            $output = ob_get_contents();
            ob_end_clean();
        }
        return $output;
    }

    private function getUserCardSetting($settingName)
    {
        return \getUserSetting($settingName);
    }
}
