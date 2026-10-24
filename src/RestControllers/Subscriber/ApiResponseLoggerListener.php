<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\Audit\ApiLogRedactor;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiResponseLoggerListener implements EventSubscriberInterface
{
    use SystemLoggerAwareTrait;

    private EventAuditLogger $eventAuditLogger;

    private ApiLogRedactor $redactor;

    public function setEventAuditLogger(EventAuditLogger $eventAuditLogger): void
    {
        $this->eventAuditLogger = $eventAuditLogger;
    }
    public function getEventAuditLogger(): EventAuditLogger
    {
        $this->eventAuditLogger ??= EventAuditLogger::getInstance();
        return $this->eventAuditLogger;
    }

    public function setRedactor(ApiLogRedactor $redactor): void
    {
        $this->redactor = $redactor;
    }

    public function getRedactor(): ApiLogRedactor
    {
        $this->redactor ??= new ApiLogRedactor();
        return $this->redactor;
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
            $url = $request->getRequestUri();
            if ($globalsBag->getInt('api_log_option') === 1) {
                $this->logger?->debug("ApiResponseLoggerListener::onRequestTerminated api_log_option set to 1, skipping log and request");
                // Do not log the response or request body at minimal level
                $logRequestBody = '';
                $logResponse = '';
            } else {
                if ($this->shouldLogResponse($response)) {
                    // getContent() returns false for streamed responses, which we log as empty.
                    $content = $response->getContent();
                    $logResponse = is_string($content) ? $content : '';
                } else {
                    $logResponse = '';
                    $this->logger?->debug("ApiResponseLoggerListener::onRequestTerminated skipping log of response, not a json response");
                }

                if ($this->shouldLogRequest($request)) {
                    $logRequestBody = $request->getContent();
                } else {
                    $logRequestBody = '';
                    $this->logger?->debug("ApiResponseLoggerListener::onRequestTerminated skipping log of request body, not a json or form-encoded request");
                }

                // Replace transient OAuth2 field values with a sentinel before writing;
                // no-op for non-OAuth2 URLs.
                $redactor = $this->getRedactor();
                $logRequestBody = $redactor->redactRequest($url, $logRequestBody);
                $logResponse = $redactor->redactResponse($url, $logResponse);
            }

            // prepare values and call the log function
            $eventName = 'api';
            $category = 'api';
            $method = $request->getMethod();
            $patientId = (int)($session->get('pid', 0));
            $userId = (int)($session->get('authUserID', 0));
            $api = [
                'user_id' => $userId,
                'client_id' => $request->getClientId() ?? '',
                'patient_id' => $patientId,
                'method' => $method,
                'request' => $request->getResource() ?? '',
                'request_url' => $url,
                'request_body' => $logRequestBody,
                'response' => $logResponse
            ];
            if ($patientId === 0) {
                $patientId = null; //entries in log table are blank for no patient_id, whereas in api_log are 0, which is why above $api value uses 0 when empty
            }
            $this->getEventAuditLogger()->recordLogItem(
                1,
                $eventName,
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
     * Checks if we should log the response interface (we don't want to log binary documents or anything like that).
     * We only log responses with a content-type of application/json or application/fhir+json.
     */
    private function shouldLogResponse(Response $response): bool
    {
        if ($response->headers->has('Content-Type')) {
            $contentType = $response->headers->get('Content-Type');
            if (in_array($contentType, ['application/json', 'application/fhir+json'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Mirror of shouldLogResponse() for the request side: whitelists the
     * content-types we expect for API requests. x-www-form-urlencoded is on
     * the list so OAuth2 token requests get captured (post-redaction);
     * everything outside the whitelist (file uploads, FHIR Binary POSTs,
     * NDJSON bulk-import) is dropped from the request_body column, same way
     * the response column already drops binary and streamed responses.
     */
    private function shouldLogRequest(Request $request): bool
    {
        $contentType = $request->headers->get('Content-Type');
        if (!is_string($contentType)) {
            return false;
        }
        // Strip any ";charset=…" suffix a client may have added.
        $mediaType = trim(strtok($contentType, ';') ?: '');
        return in_array($mediaType, [
            'application/json',
            'application/fhir+json',
            'application/x-www-form-urlencoded',
        ], true);
    }
}
