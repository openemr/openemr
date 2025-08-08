<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiResponseLoggerListener implements EventSubscriberInterface
{
    use SystemLoggerAwareTrait;

    private EventAuditLogger $eventAuditLogger;

    public function setEventAuditLogger(EventAuditLogger $eventAuditLogger): void
    {
        $this->eventAuditLogger = $eventAuditLogger;
    }
    public function getEventAuditLogger(): EventAuditLogger
    {
        if (!isset($this->eventAuditLogger)) {
            $this->eventAuditLogger = EventAuditLogger::instance();
        }
        return $this->eventAuditLogger;
    }
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'onRequestTerminated',
        ];
    }

    public function onRequestTerminated(TerminateEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        if (!$request instanceof HttpRestRequest) {
            return; // only handle HttpRestRequest
        }
        $session = $request->getSession();
        $kernel = $event->getKernel();
        $globalsBag = $kernel instanceof OEHttpKernel ? $kernel->getGlobalsBag() : new OEGlobalsBag([]);

        // only log when using standard api calls (skip when using local api calls from within OpenEMR)
        //  and when api log option is set
        if (
            !$request->isLocalApi() &&
            // we don't log unit test pieces.
            !$request->attributes->has("skipResponseLogging") &&
            $globalsBag->getInt('api_log_option') > 0
        ) {
            if ($globalsBag->getInt('api_log_option') == 1) {
                $this->getSystemLogger()->debug("ApiResponseLoggerListener::onRequestTerminated api_log_option set to 1, skipping log and request");
                // Do not log the response and requestBody
                $logResponse = '';
            } else if ($this->shouldLogResponse($response)) {
                // If the response is a Symfony Response, we can get the content directly
                $logResponse = $response->getContent();
            } else {
                $logResponse = '';
                $this->getSystemLogger()->debug("ApiResponseLoggerListener::onRequestTerminated skipping log of response, not a json response");
            }

            // prepare values and call the log function
            $event = 'api';
            $category = 'api';
            $method = $request->getMethod();
            $url = $request->getRequestUri();
            $patientId = (int)($session->get('pid', 0));
            $userId = (int)($session->get('authUserID', 0));
            $api = [
                'user_id' => $userId,
                'patient_id' => $patientId,
                'method' => $method,
                'request' => $request->getResource() ?? '',
                'request_url' => $url,
                // note due to the way responses are handled now, the request_body and response are going to be the same
                'request_body' => $logResponse,
                'response' => $logResponse
            ];
            if ($patientId === 0) {
                $patientId = null; //entries in log table are blank for no patient_id, whereas in api_log are 0, which is why above $api value uses 0 when empty
            }
            $this->getEventAuditLogger()->recordLogItem(
                1,
                $event,
                $session->get('authUser', ''),
                $session->get('authProvider', ''),
                'api log',
                $patientId,
                $category,
                'open-emr',
                null,
                null,
                '',
                $api
            );
        }
    }

    /**
     * Checks if we should log the response interface (we don't want to log binary documents or anything like that)
     * We only log requests with a content-type of any form of json fhir+application/json or application/json
     * @param Response $response
     * @return bool If the request should be logged, false otherwise
     */
    private function shouldLogResponse(Response $response): bool
    {
        // If the response is a Symfony Response, we can check the content type directly
        if ($response->headers->has('Content-Type')) {
            $contentType = $response->headers->get('Content-Type');
            if (in_array($contentType, ['application/json', 'application/fhir+json'])) {
                return true;
            }
        }
        return false;
    }
}
