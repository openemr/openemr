<?php

namespace OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport;

use http\Env;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\SMART\ActionUrlBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class RouteController
{
    const EXTERNAL_CDR_ACTION = "external-cdr";

    public function __construct(
        private ClientRepository $repo
        , private LoggerInterface $logger
        , private Environment $twig
        , private ActionUrlBuilder $actionUrlBuilder)
    {
        $this->setTwigEnvironment($twig);
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

        if (empty($mainActionChild) || $mainActionChild === 'list') {
            return $this->listAction($request);
        } else if ($mainActionChild == 'edit') {
            return $this->editAction($request);
        } else {
            return $this->notFoundAction($request);
        }

    }

    public function notFoundAction(Request $request) : Response
    {
        $bodyContents = $this->twig->render("interface/smart/admin-client/404.html.twig");
        return new Response($bodyContents, 404);
    }

    public function listAction(Request $request)
    {
        $serverConfig = new ServerConfig();
        // TODO: @adunsulag will need to expose the Questionnaire endpoint as part of this.
        $questionnaire = $this->twig->render("api/smart/dsi-service-questionnaire.json.twig", ['fhirUrl' => $serverConfig->getFhirUrl()]);
        $params = $this->getRootParams();
        $services = [];
        $service = $this->getSampleService($questionnaire);
        $services[] = $service;
        $params['services'] = $services;
        $params['editUrl'] = $this->actionUrlBuilder->buildUrl([self::EXTERNAL_CDR_ACTION, 'edit', $service->getClient()->getIdentifier()]);
        $params['clientEdit'] = $this->actionUrlBuilder->buildUrl(['edit', $service->getClient()->getIdentifier()]);
//        $client->setService(new EvidenceBasedDSIServiceEntity());
        $bodyContents = $this->twig->render("interface/smart/admin-client/external-cdr-list.html.twig", $params);
        return new Response($bodyContents);
    }

    public function editAction(Request $request)
    {
        ['subAction' => $serviceId] = $this->parseRequest($request);
        $serverConfig = new ServerConfig();
        // TODO: @adunsulag will need to expose the Questionnaire endpoint as part of this.
        $questionnaire = $this->twig->render("api/smart/dsi-service-questionnaire.json.twig", ['fhirUrl' => $serverConfig->getFhirUrl()]);
        $params = $this->getRootParams();
        $params['service'] = $this->getSampleService($questionnaire);
        $params['service']->getClient()->setIdentifier($serviceId);

        $qr = $this->twig->render("api/smart/dsi-service-qr-test.json.twig");
        $params['service']->populateServiceWithFhirQuestionnaire($questionnaire, $qr);
        $client = $params['service']->getClient();
        $params['questionnaire'] = $questionnaire;
        $params['clientEdit'] = $this->actionUrlBuilder->buildUrl(['edit', $client->getIdentifier()]);
//        $client->setService(new EvidenceBasedDSIServiceEntity());
        $bodyContents = $this->twig->render("interface/smart/admin-client/external-cdr-edit.html.twig", $params);
        return new Response($bodyContents);
    }

    public function getRootParams()
    {
        return [
            'nav' => [
                'title' => xl('External Clinical Decision Support'),
                'navs' => [
                    ['title' => xl('Smart Apps'), 'url' => $this->actionUrlBuilder->buildUrl('')]
                ]
            ]
        ];
    }

    private function getSampleService(string $questionnaire) :DecisionSupportInterventionEntity
    {
        $client = new ClientEntity();
        $client->setIdentifier('blah-1');
        $client->setName("New Test Client");
        $service = new PredictiveDSIServiceEntity($client);
        $service->populateServiceWithFhirQuestionnaire($questionnaire);
        return $service;
    }
}
