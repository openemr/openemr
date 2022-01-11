<?php

/**
 * TemplatePageEvent class is fired from pages inside OpenEMR and serves as a base class for a variety of system page
 * rendering events.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Core;

class TemplatePageEvent
{
    /**
     * Context variables used for filtering the event
     * @var array
     */
    private $context;

    /**
     * @var string The name of the template page.
     */
    private $pageName;

    public function __construct(string $pageName, $context = array())
    {
        $this->setContext($context);
        $this->setPageName($pageName);
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
