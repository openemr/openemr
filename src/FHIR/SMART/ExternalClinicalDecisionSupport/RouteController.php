<?php

namespace OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\FHIR\SMART\ActionUrlBuilder;
use OpenEMR\Services\DecisionSupportInterventionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class RouteController
{
    const EXTERNAL_CDR_ACTION = "external-cdr";

    const CDR_ACTION_INFO = "cdr-info";

    public function __construct(
        private ClientRepository $repo,
        private LoggerInterface $logger,
        private Environment $twig,
        private ActionUrlBuilder $actionUrlBuilder,
        private DecisionSupportInterventionService $dsiService
    ) {
        $this->setTwigEnvironment($twig);
    }

    public function getDecisionsSupportInterventionService()
    {
        return $this->dsiService;
    }

    public function setTwigEnvironment(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function supportsRequest($request)
    {
        // make sure the request matches the EXTERNAL_CDR_ACTION route either standalone or as a prefix
        $action = $request->get('action', '');
        return $action === self::EXTERNAL_CDR_ACTION ||
            str_starts_with($action, self::EXTERNAL_CDR_ACTION . '/');
    }

    public function parseRequest(Request $request)
    {
        $parts = explode("/", $request->get('action'));

        $mainAction = $parts[0] ?? null;
        $mainActionChild = $parts[1] ?? null;
        $subAction = $parts[2] ?? null;
        return [
            'mainAction' => $mainAction,
            'mainActionChild' => $mainActionChild,
            'subAction' => $subAction
        ];
    }

    public function dispatch(Request $request)
    {
        // TODO: we could pop off the first part of the action and treat the rest as the main action
        if (!$this->supportsRequest($request)) {
            throw new \InvalidArgumentException("Invalid request");
        }

        // parse the request and extract array values into variables
        // yes I could use extract... but prefer to be explicit
        ['mainAction' => $mainAction
        ,'mainActionChild' => $mainActionChild
        ,'subAction' => $subAction] = $this->parseRequest($request);

        if ($mainActionChild == 'edit') {
            return $this->editAction($request);
        } else if ($mainActionChild == 'save') {
            return $this->saveAction($request);
        } else if ($mainActionChild == 'cdr-info') {
            return $this->cdrInfoAction($request);
        } else {
            return $this->notFoundAction($request);
        }
    }

    public function notFoundAction(Request $request): Response
    {
        $bodyContents = $this->twig->render("interface/smart/admin-client/404.html.twig");
        return new Response($bodyContents, 404);
    }

    public function cdrInfoAction(Request $request): Response
    {
        $serviceId = $request->get('serviceId', '');
        $csrfToken = $request->get('csrf_token', '');

        if (CsrfUtils::verifyCsrfToken($csrfToken) === false) {
            return $this->notFoundAction($request);
        }
        if (empty(trim($serviceId))) {
            return $this->notFoundAction($request);
        }
        $dsiService = $this->getDecisionsSupportInterventionService();
        $service = $dsiService->getService($serviceId);
        if ($service == null) {
            return $this->notFoundAction($request);
        }

        // certification requirement, show a message on each field if the field is empty that the provider did not provide any information
        foreach ($service->getFields() as $field) {
            if (empty($field['value'])) {
                $service->setFieldValue($field['name'], xl("This DSI provider did not provide any information for this field"));
            }
        }

        $params = $this->getRootParams();
        $params['showEditLink'] = AclMain::aclCheckCore("admin", "super");
        $params['nav']['subtitle'] = $service->getName();
        $params['service'] = $service;
        $bodyContents = $this->twig->render("interface/smart/admin-client/external-cdr-info.html.twig", $params);
        return new Response($bodyContents);
    }

    public function editAction(Request $request)
    {
        ['subAction' => $serviceId] = $this->parseRequest($request);
        $params = $this->getRootParams();
        $dsiService = $this->getDecisionsSupportInterventionService();
        $service = $dsiService->getService($serviceId);
        if ($service == null) {
            return $this->notFoundAction($request);
        }
        $status = $request->get('status', '');
        $saveMessage = "";
        $alertType = "";
        if ($status == 'success') {
            $saveMessage = xl("Save successful");
            $alertType = "success";
        } else if (!empty($status)) { // everything else is treated as a failure
            $alertType = "danger";
            $saveMessage = xl("Save failed.") . " " . xl("Check the system error logs for more information.");
        }
        $params['nav']['navs'] = [];
        // only admin users can edit the attributes.
        $params['smartAppEdit'] = $this->actionUrlBuilder->buildUrl(['edit', $service->getClient()->getIdentifier()], ['fragment' => 'services']);
        $params['clientListUrl'] = $this->actionUrlBuilder->buildUrl([self::EXTERNAL_CDR_ACTION, 'list']);
        $params['alertType'] = $alertType;
        $params['saveMessage'] = $saveMessage;
        $params['service'] = $service;
        $params['saveAction'] = $this->actionUrlBuilder->buildUrl([self::EXTERNAL_CDR_ACTION, 'save', $service->getClient()->getIdentifier()]);
        $params['disableClientUrl'] = $this->actionUrlBuilder->buildUrl([self::EXTERNAL_CDR_ACTION, 'disable', $service->getClient()->getIdentifier()]);
        $params['enableClientUrl'] = $this->actionUrlBuilder->buildUrl([self::EXTERNAL_CDR_ACTION, 'enable', $service->getClient()->getIdentifier()]);
        $params['predictiveDSIListID'] = DecisionSupportInterventionService::LIST_ID_PREDICTIVE_DSI;
        $params['evidenceDSIListID'] = DecisionSupportInterventionService::LIST_ID_EVIDENCE_DSI;
        $bodyContents = $this->twig->render("interface/smart/admin-client/external-cdr-edit.html.twig", $params);
        return new Response($bodyContents);
    }

    public function getRootParams()
    {
        return [
            'nav' => [
                'title' => xl('Decision Support Intervention Service'),
                'navs' => [
                    ['title' => xl('Smart Apps'), 'url' => $this->actionUrlBuilder->buildUrl('')]
                ]
            ]
        ];
    }

    private function saveAction(Request $request)
    {
        // TODO: @adunsulag need to handle CSRF token in save action
        ['subAction' => $serviceId] = $this->parseRequest($request);
        $csrfToken = $request->get('_token', '');
        if (!CsrfUtils::verifyCsrfToken($csrfToken)) {
            throw new CsrfInvalidException(xlt('Authentication Error'));
        }

        $dsiService = $this->getDecisionsSupportInterventionService();
        $service = $dsiService->getService($serviceId);

        if ($service == null) {
            return $this->notFoundAction($request);
        }

        $fields = $service->getFields();
        foreach ($fields as $field) {
            $value = $request->get($field['name'], '');
            $service->setFieldValue($field['name'], $value);
        }
        try {
            $fields = $service->getFields();
            if ($service->getType() == PredictiveDSIServiceEntity::TYPE) {
                $dsiService->updatePredictiveDSIAttributes($service->getId(), $_SESSION['authUserID'], $fields);
            } else {
                $dsiService->updateEvidenceDSIAttributes($service->getId(), $_SESSION['authUserID'], $fields);
            }
            $status = "success";
        } catch (\Exception $e) {
            $this->logger->error("Error saving service", ['exception' => $e]);
            $status = "failed";
        }

        $saveResponseUrl = $this->actionUrlBuilder->buildUrl([self::EXTERNAL_CDR_ACTION, 'edit', $service->getId()], ['queryParams' => ['status' => $status]]);
        $response = new Response("", 302, ['Location' => $saveResponseUrl]);
        return $response;
    }
}
