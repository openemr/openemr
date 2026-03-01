<?php

/**
 * PatientRelationshipViewCard - presentation view of patient relationships in a card widget.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com> AI-generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Patient\Cards;

use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Services\PatientRelationshipService;
use OpenEMR\Services\PatientService;

class PatientRelationshipViewCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/card/patient_relationships.html.twig';
    private const CARD_ID = 'patient_relationships';

    private readonly PatientRelationshipService $relationshipService;

    public function __construct(private $patientData, array $opts = [])
    {
        $this->relationshipService = new PatientRelationshipService(new PatientService());
        $opts = $this->setupOpts($opts);
        parent::__construct($opts);
    }

    private function setupOpts(array $opts)
    {
        $opts['acl'] = ['patients', 'demo'];
        $opts['title'] = xl('Patient Relationships');
        $opts['btnLink'] = '#';
        $opts['linkMethod'] = 'javascript';
        $opts['edit'] = true;
        $opts['add'] = true;
        $opts['requireRestore'] = (!isset($_SESSION['patient_portal_onsite_two'])) ? true : false;
        $opts['initiallyCollapsed'] = getUserSetting(self::CARD_ID . "_ps_expand") == 1 ? false : true;
        $opts['identifier'] = self::CARD_ID;
        $opts['templateFile'] = self::TEMPLATE_FILE;
        $opts['templateVariables'] = [];
        return $opts;
    }

    public function getTemplateVariables(): array
    {
        $templateVars = parent::getTemplateVariables();
        $dataVars = $this->setupRelationshipData();
        return array_merge($templateVars, $dataVars);
    }

    private function setupRelationshipData()
    {
        $dispatchResult = $this->getEventDispatcher()->dispatch(new RenderEvent(self::CARD_ID), RenderEvent::EVENT_HANDLE);
        $auth = AclMain::aclCheckCore('patients', 'demo', '', 'write');

        // Get relationships for this patient
        $relationships = [];
        $relationshipTypes = [];

        $patientId = $this->patientData['id'] ?? $this->patientData['pid'] ?? null;

        if (!empty($patientId)) {
            $relationshipResult = $this->relationshipService->getPatientRelationships((int)$patientId);
            if (!$relationshipResult->hasErrors()) {
                $relationships = $relationshipResult->getData();
            }

            $relationshipTypes = $this->relationshipService->getRelationshipTypes();
        }

        $viewArgs = [
            'requireRestore' => (!isset($_SESSION['patient_portal_onsite_two'])) ? true : false,
            'initiallyCollapsed' => (getUserSetting(self::CARD_ID . '_ps_expand') == 1) ? false : true,
            'tabID' => "REL",
            'title' => xl("Patient Relationships"),
            'id' => self::CARD_ID . '_ps_expand',
            'btnLabel' => "Add",
            'btnLink' => "#",
            'linkMethod' => 'javascript',
            'auth' => $auth,
            'patient_id' => $patientId ?? '',
            'csrf_token' => \OpenEMR\Common\Csrf\CsrfUtils::collectCsrfToken(),
            'relationships' => $relationships,
            'relationship_types' => $relationshipTypes,
            'prependedInjection' => $dispatchResult->getPrependedInjection(),
            'appendedInjection' => $dispatchResult->getAppendedInjection()
        ];
        return $viewArgs;
    }
}
