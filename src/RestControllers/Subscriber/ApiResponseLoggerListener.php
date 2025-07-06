<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiResponseLoggerListener implements EventSubscriberInterface
{
    private EventAuditLogger $eventAuditLogger;

    public function setEventAuditLogger(EventAuditLogger $eventAuditLogger)
    {
        $this->eventAuditLogger = $eventAuditLogger;
    }
    public function getEventAuditLogger()
    {
        if (!isset($this->eventAuditLogger)) {
            $this->eventAuditLogger = EventAuditLogger::instance();
        }
        return $this->eventAuditLogger;
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'onRequestTerminated',
        ];
    }

    public function onRequestTerminated(TerminateEvent $event) {
        $response = $event->getResponse();
        $request = $event->getRequest();
        if (!$request instanceof HttpRestRequest) {
            return; // only handle HttpRestRequest
        }
        $logResponse = $response;

        // only log when using standard api calls (skip when using local api calls from within OpenEMR)
        //  and when api log option is set
        if (!$request->isLocalApi() &&
            // we don't log unit test pieces.
            !$request->attributes->has("skipResponseLogging")
            && $GLOBALS['api_log_option']) {
            if ($GLOBALS['api_log_option'] == 1) {
                // Do not log the response and requestBody
                $logResponse = '';
                $requestBody = '';
            }
            if ($response instanceof ResponseInterface) {
                if ($this->shouldLogResponse($response)) {
                    $body = $response->getBody();
                    $logResponse = $body->getContents();
                    $body->rewind();
                } else {
                    $logResponse = 'Content not application/json - Skip binary data';
                }
            } else {
                $logResponse = (!empty($logResponse)) ? json_encode($response) : '';
            }

            // convert pertinent elements to json
            $requestBody = (!empty($requestBody)) ? json_encode($requestBody) : '';

            // prepare values and call the log function
            $event = 'api';
            $category = 'api';
            $method = $request->getMethod();
            $url = $request->getRequestUri();
            $patientId = (int)($_SESSION['pid'] ?? 0);
            $userId = (int)($_SESSION['authUserID'] ?? 0);
            $api = [
                'user_id' => $userId,
                'patient_id' => $patientId,
                'method' => $method,
                'request' => $request->getResource() ?? '',
                'request_url' => $url,
                'request_body' => $requestBody,
                'response' => $logResponse
            ];
            if ($patientId === 0) {
                $patientId = null; //entries in log table are blank for no patient_id, whereas in api_log are 0, which is why above $api value uses 0 when empty
            }
            $this->getEventAuditLogger()->recordLogItem(1, $event, ($_SESSION['authUser'] ?? '')
                , ($_SESSION['authProvider'] ?? ''), 'api log', $patientId, $category, 'open-emr'
                , null, null, '', $api);
        }

    }

    /**
     * Checks if we should log the response interface (we don't want to log binary documents or anything like that)
     * We only log requests with a content-type of any form of json fhir+application/json or application/json
     * @param ResponseInterface $response
     * @return bool If the request should be logged, false otherwise
     */
    private function shouldLogResponse(ResponseInterface $response)
    {
        if ($response->hasHeader("Content-Type")) {
            $contentType = $response->getHeaderLine("Content-Type");
            if ($contentType === 'application/json') {
                return true;
            }
        }

        return false;
    }
}
