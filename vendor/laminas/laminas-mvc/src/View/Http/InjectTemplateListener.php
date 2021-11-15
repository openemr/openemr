<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\View\Http;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface as Events;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\StringUtils;
use Laminas\View\Model\ModelInterface as ViewModel;

class InjectTemplateListener extends AbstractListenerAggregate
{
    /**
     * Array of controller namespace -> template mappings
     *
     * @var array
     */
    protected $controllerMap = [];

    /**
     * Flag to force the use of the route match controller param
     *
     * @var boolean
     */
    protected $preferRouteMatchController = false;

    /**
     * {@inheritDoc}
     */
    public function attach(Events $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'injectTemplate'], -90);
    }

    /**
     * Inject a template into the view model, if none present
     *
     * Template is derived from the controller found in the route match, and,
     * optionally, the action, if present.
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function injectTemplate(MvcEvent $e)
    {
        $model = $e->getResult();
        if (! $model instanceof ViewModel) {
            return;
        }

        $template = $model->getTemplate();
        if (! empty($template)) {
            return;
        }

        $routeMatch = $e->getRouteMatch();
        if ($preferRouteMatchController = $routeMatch->getParam('prefer_route_match_controller', false)) {
            $this->setPreferRouteMatchController($preferRouteMatchController);
        }

        $controller = $e->getTarget();
        if (is_object($controller)) {
            $controller = get_class($controller);
        }

        $routeMatchController = $routeMatch->getParam('controller', '');
        if (! $controller || ($this->preferRouteMatchController && $routeMatchController)) {
            $controller = $routeMatchController;
        }

        $template = $this->mapController($controller);

        $action     = $routeMatch->getParam('action');
        if (null !== $action) {
            $template .= '/' . $this->inflectName($action);
        }
        $model->setTemplate($template);
    }

    /**
     * Set map of controller namespace -> template pairs
     *
     * @param  array $map
     * @return self
     */
    public function setControllerMap(array $map)
    {
        krsort($map);
        $this->controllerMap = $map;
        return $this;
    }

    /**
     * Maps controller to template if controller namespace is whitelisted or mapped
     *
     * @param string $controller controller FQCN
     * @return string|false template name or false if controller was not matched
     */
    public function mapController($controller)
    {
        $mapped = '';
        foreach ($this->controllerMap as $namespace => $replacement) {
            if (// Allow disabling rule by setting value to false since config
                // merging have no feature to remove entries
                false == $replacement
                // Match full class or full namespace
                || ! ($controller === $namespace || strpos($controller, $namespace . '\\') === 0)
            ) {
                continue;
            }

            // Map namespace to $replacement if its value is string
            if (is_string($replacement)) {
                $mapped = rtrim($replacement, '/') . '/';
                $controller = substr($controller, strlen($namespace) + 1) ?: '';
                break;
            }
        }

        //strip Controller namespace(s) (but not classname)
        $parts = explode('\\', $controller);
        array_pop($parts);
        $parts = array_diff($parts, ['Controller']);
        //strip trailing Controller in class name
        $parts[] = $this->deriveControllerClass($controller);
        $controller = implode('/', $parts);

        $template = trim($mapped . $controller, '/');

        // inflect CamelCase to dash
        return $this->inflectName($template);
    }

    /**
     * Inflect a name to a normalized value
     *
     * Inlines the logic from laminas-filter's Word\CamelCaseToDash filter.
     *
     * @param  string $name
     * @return string
     */
    protected function inflectName($name)
    {
        if (StringUtils::hasPcreUnicodeSupport()) {
            $pattern     = ['#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#', '#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#'];
            $replacement = ['-\1', '-\1'];
        } else {
            $pattern     = ['#(?<=(?:[A-Z]))([A-Z]+)([A-Z][a-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#'];
            $replacement = ['\1-\2', '-\1'];
        }

        $name = preg_replace($pattern, $replacement, $name);
        return strtolower($name);
    }

    /**
     * Determine the name of the controller
     *
     * Strip the namespace, and the suffix "Controller" if present.
     *
     * @param  string $controller
     * @return string
     */
    protected function deriveControllerClass($controller)
    {
        if (false !== strpos($controller, '\\')) {
            $controller = substr($controller, strrpos($controller, '\\') + 1);
        }

        if ((10 < strlen($controller))
            && ('Controller' == substr($controller, -10))
        ) {
            $controller = substr($controller, 0, -10);
        }

        return $controller;
    }

    /**
     * Sets the flag to instruct the listener to prefer the route match controller param
     * over the class name
     *
     * @param boolean $preferRouteMatchController
     */
    public function setPreferRouteMatchController($preferRouteMatchController)
    {
        $this->preferRouteMatchController = (bool) $preferRouteMatchController;
    }

    /**
     * @return boolean
     */
    public function isPreferRouteMatchController()
    {
        return $this->preferRouteMatchController;
    }
}
