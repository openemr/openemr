<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\View\Exception;
use Laminas\View\Model\ModelInterface as Model;

/**
 * View helper for retrieving layout object
 */
class Layout extends AbstractHelper
{
    /**
     * @var ViewModel
     */
    protected $viewModelHelper;

    /**
     * Set layout template or retrieve "layout" view model
     *
     * If no arguments are given, grabs the "root" or "layout" view model.
     * Otherwise, attempts to set the template for that view model.
     *
     * @param  null|string $template
     * @return Layout
     */
    public function __invoke($template = null)
    {
        if (null === $template) {
            return $this->getRoot();
        }

        return $this->setTemplate($template);
    }

    /**
     * Get layout template
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->getRoot()->getTemplate();
    }

    /**
     * Get the root view model
     *
     * @throws Exception\RuntimeException
     * @return null|Model
     */
    protected function getRoot()
    {
        $helper = $this->getViewModelHelper();

        if (! $helper->hasRoot()) {
            throw new Exception\RuntimeException(sprintf(
                '%s: no view model currently registered as root in renderer',
                __METHOD__
            ));
        }

        return $helper->getRoot();
    }

    /**
     * Set layout template
     *
     * @param  string $template
     * @return Layout
     */
    public function setTemplate($template)
    {
        $this->getRoot()->setTemplate((string) $template);
        return $this;
    }

    /**
     * Retrieve the view model helper
     *
     * @return ViewModel
     */
    protected function getViewModelHelper()
    {
        if (null === $this->viewModelHelper) {
            $this->viewModelHelper = $this->getView()->plugin('view_model');
        }

        return $this->viewModelHelper;
    }
}
