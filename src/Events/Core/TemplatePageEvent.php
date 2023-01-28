<?php

/**
 * TemplatePageEvent class is fired from pages inside OpenEMR and serves as a base class for a variety of system page
 * rendering events.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2023 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2023 Providence Healthtech
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Core;

class TemplatePageEvent
{
    const CONTEXT_ARGUMENT_SCRIPT_NAME = "script_name";

    const RENDER_EVENT = "events.core.page";
    /**
     * Context variables used for filtering the event
     * @var array
     */
    private $context;

    /**
     * Array of twig varibles passed to the twig template
     *
     * @var array
     */
    private $twigVariables;

    /**
     * The twig template to render
     *
     * @var string
     */
    private $twigTemplate;

    private $pageID;

    /**
     * @var string The name of the template page.
     */
    private $pageName;

    public function __construct(string $pageName, $context = array(), $twigTemplate = "", $twigVariables = [])
    {
        $this->setContext($context);
        $this->setPageName($pageName);
        $this->setTwigTemplate($twigTemplate);
        $this->setTwigVariables($twigVariables);
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     * @return TemplatePageEvent
     */
    public function setContext(array $context): TemplatePageEvent
    {
        $this->context = $context;
        return $this;
    }

    public function getContextArgument($key)
    {
        return $this->getContext()[$key] ?? null;
    }

    /**
     * Sets the context argument
     * @param $key string
     * @param $value
     */
    public function setContextArgument(string $key, $value)
    {
        $this->context[$key] = $value;
    }

    /**
     * Get the array of twig variables to render to a page
     *
     * @return array
     */
    public function getTwigVariables(): array
    {
        return $this->twigVariables ?? [];
    }

    /**
     * Set the twig variables array.
     *
     * Performs an array_merge between current twig variables and $variables
     * argument. use emptyTwigVariables() to completely remove all variables
     * from the stack.
     *
     * @param array $variables
     * @return void
     */
    public function setTwigVariables(array $variables)
    {
        $this->twigVariables = array_merge($this->getTwigVariables(), $variables);
        return $this;
    }

    public function emptyTwigVariables(): void
    {
        $this->twigVariables = [];
    }

    public function setTwigTemplate(string $template)
    {
        $this->twigTemplate = $template;
        return $this;
    }

    public function getTwigTemplate(): string
    {
        return $this->twigTemplate ?? "";
    }

    /**
     * @return string
     */
    public function getPageName(): string
    {
        return $this->pageName;
    }

    /**
     * @param string $pageName
     * @return TemplatePageEvent
     */
    public function setPageName(string $pageName): TemplatePageEvent
    {
        $this->pageName = $pageName;
        return $this;
    }
}
