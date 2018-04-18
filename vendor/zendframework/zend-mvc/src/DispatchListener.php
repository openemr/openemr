<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc;

use ArrayObject;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\Stdlib\ArrayUtils;

/**
 * Default dispatch listener
 *
 * Pulls controllers from the service manager's "ControllerManager" service.
 *
 * If the controller cannot be found a "404" result is set up. Otherwise it
 * will continue to try to load the controller.
 *
 * If the controller is not dispatchable it sets up a "404" result. In case
 * of any other exceptions it trigger the "dispatch.error" event in an attempt
 * to return a 500 status.
 *
 * If the controller subscribes to InjectApplicationEventInterface, it injects
 * the current MvcEvent into the controller.
 *
 * It then calls the controller's "dispatch" method, passing it the request and
 * response. If an exception occurs, it triggers the "dispatch.error" event,
 * in an attempt to return a 500 status.
 *
 * The return value of dispatching the controller is placed into the result
 * property of the MvcEvent, and returned.
 */
class DispatchListener extends AbstractListenerAggregate
{
    /**
     * @var Controller\ControllerManager
     */
    private $controllerManager;

    /**
     * @param Controller\ControllerManager $controllerManager
     */
    public function __construct(Controller\ControllerManager $controllerManager)
    {
        $this->controllerManager = $controllerManager;
    }

    /**
     * Attach listeners to an event manager
     *
     * @param  EventManagerInterface $events
     * @param  int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch']);
        if (function_exists('zend_monitor_custom_event_ex')) {
            $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'reportMonitorEvent']);
        }
    }

    /**
     * Listen to the "dispatch" event
     *
     * @param  MvcEvent $e
     * @return mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        $routeMatch        = $e->getRouteMatch();
        $controllerName    = $routeMatch instanceof Router\RouteMatch
            ? $routeMatch->getParam('controller', 'not-found')
            : 'not-found';
        $application       = $e->getApplication();
        $events            = $application->getEventManager();
        $controllerManager = $this->controllerManager;


        // Query abstract controllers, too!
        if (! $controllerManager->has($controllerName)) {
            $return = $this->marshalControllerNotFoundEvent($application::ERROR_CONTROLLER_NOT_FOUND, $controllerName, $e, $application);
            return $this->complete($return, $e);
        }

        try {
            $controller = $controllerManager->get($controllerName);
        } catch (Exception\InvalidControllerException $exception) {
            $return = $this->marshalControllerNotFoundEvent($application::ERROR_CONTROLLER_INVALID, $controllerName, $e, $application, $exception);
            return $this->complete($return, $e);
        } catch (InvalidServiceException $exception) {
            $return = $this->marshalControllerNotFoundEvent($application::ERROR_CONTROLLER_INVALID, $controllerName, $e, $application, $exception);
            return $this->complete($return, $e);
        } catch (\Throwable $exception) {
            $return = $this->marshalBadControllerEvent($controllerName, $e, $application, $exception);
            return $this->complete($return, $e);
        } catch (\Exception $exception) {  // @TODO clean up once PHP 7 requirement is enforced
            $return = $this->marshalBadControllerEvent($controllerName, $e, $application, $exception);
            return $this->complete($return, $e);
        }

        if ($controller instanceof InjectApplicationEventInterface) {
            $controller->setEvent($e);
        }

        $request  = $e->getRequest();
        $response = $application->getResponse();
        $caughtException = null;

        try {
            $return = $controller->dispatch($request, $response);
        } catch (\Throwable $ex) {
            $caughtException = $ex;
        } catch (\Exception $ex) {  // @TODO clean up once PHP 7 requirement is enforced
            $caughtException = $ex;
        }

        if ($caughtException !== null) {
            $e->setName(MvcEvent::EVENT_DISPATCH_ERROR);
            $e->setError($application::ERROR_EXCEPTION);
            $e->setController($controllerName);
            $e->setControllerClass(get_class($controller));
            $e->setParam('exception', $caughtException);

            $return = $application->getEventManager()->triggerEvent($e)->last();
            if (! $return) {
                $return = $e->getResult();
            }
        }

        return $this->complete($return, $e);
    }

    /**
     * @param MvcEvent $e
     */
    public function reportMonitorEvent(MvcEvent $e)
    {
        $error     = $e->getError();
        $exception = $e->getParam('exception');
        if ($exception instanceof \Exception || $exception instanceof \Throwable) {  // @TODO clean up once PHP 7 requirement is enforced
            zend_monitor_custom_event_ex($error, $exception->getMessage(), 'Zend Framework Exception', ['code' => $exception->getCode(), 'trace' => $exception->getTraceAsString()]);
        }
    }

    /**
     * Complete the dispatch
     *
     * @param  mixed $return
     * @param  MvcEvent $event
     * @return mixed
     */
    protected function complete($return, MvcEvent $event)
    {
        if (!is_object($return)) {
            if (ArrayUtils::hasStringKeys($return)) {
                $return = new ArrayObject($return, ArrayObject::ARRAY_AS_PROPS);
            }
        }
        $event->setResult($return);
        return $return;
    }

    /**
     * Marshal a controller not found exception event
     *
     * @param  string $type
     * @param  string $controllerName
     * @param  MvcEvent $event
     * @param  Application $application
     * @param  \Exception $exception
     * @return mixed
     */
    protected function marshalControllerNotFoundEvent(
        $type,
        $controllerName,
        MvcEvent $event,
        Application $application,
        \Exception $exception = null
    ) {
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR);
        $event->setError($type);
        $event->setController($controllerName);
        $event->setControllerClass('invalid controller class or alias: ' . $controllerName);
        if ($exception !== null) {
            $event->setParam('exception', $exception);
        }

        $events  = $application->getEventManager();
        $results = $events->triggerEvent($event);
        $return  = $results->last();
        if (! $return) {
            $return = $event->getResult();
        }
        return $return;
    }

    /**
     * Marshal a controller not found exception event
     *
     * @deprecated Use marshalControllerNotFoundEvent() instead
     * @param  string $type
     * @param  string $controllerName
     * @param  MvcEvent $event
     * @param  Application $application
     * @param  \Exception $exception
     * @return mixed
     */
    protected function marshallControllerNotFoundEvent(
        $type,
        $controllerName,
        MvcEvent $event,
        Application $application,
        \Exception $exception = null
    ) {
        trigger_error(sprintf(
            '%s is deprecated; please use %s::marshalControllerNotFoundEvent instead',
            __METHOD__,
            __CLASS__
        ), E_USER_DEPRECATED);

        return $this->marshalControllerNotFoundEvent($type, $controllerName, $event, $application, $exception);
    }

    /**
     * Marshal a bad controller exception event
     *
     * @todo   Update $exception typehint to "Throwable" once PHP 7 requirement
     *         is enforced
     * @param  string $controllerName
     * @param  MvcEvent $event
     * @param  Application $application
     * @param  \Exception|\Throwable $exception
     * @return mixed
     */
    protected function marshalBadControllerEvent($controllerName, MvcEvent $event, Application $application, $exception)
    {
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR);
        $event->setError($application::ERROR_EXCEPTION);
        $event->setController($controllerName);
        $event->setParam('exception', $exception);

        $events  = $application->getEventManager();
        $results = $events->triggerEvent($event);
        $return  = $results->last();
        if (! $return) {
            return $event->getResult();
        }

        return $return;
    }
}
