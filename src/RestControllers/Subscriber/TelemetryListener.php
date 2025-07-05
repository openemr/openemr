<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Telemetry\TelemetryService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TelemetryListener implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'onRequestTerminated',
        ];
    }

    public function onRequestTerminated(TerminateEvent $event) {
        $request = $event->getRequest();
        try {
            $userRole = $request->attributes->get('userRole', 'UNKNOWN');
            (new TelemetryService())->trackApiRequestEvent([
                'eventType' => 'API',
                'eventLabel' => strtoupper($_SESSION['api'] ?? 'UNKNOWN'),
                'eventUrl' => $request->getRequestMethod() . ' ' . $request->getPathInfo(),
                'eventTarget' => $userRole,
            ]);
            exit;
        } catch (\Exception $e) {
            $kernel = $event->getKernel();
            if ($kernel instanceof OEHttpKernel) {
                $logger = $kernel->getSystemLogger();
                $logger->error("TelemetryListener telemetry error", ['exception' => $e]);
            } else {
                error_log("TelemetryListener telemetry error: " . $e->getMessage());
            }
        }
    }
}
