<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Model;

/**
 * Interface describing methods for clearing the state of a view model.
 *
 * View models implementing this interface allow clearing children, options,
 * and variables.
 */
interface ClearableModelInterface
{
    public function clearChildren();
    public function clearOptions();
    public function clearVariables();
}
