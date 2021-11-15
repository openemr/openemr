<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Controller\Plugin;

use Laminas\Mvc\Console\View\ViewModel as ConsoleModel;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class CreateConsoleNotFoundModel extends AbstractPlugin
{
    /**
     * Create a console view model representing a "not found" action
     *
     * @return ConsoleModel
     */
    public function __invoke()
    {
        $viewModel = new ConsoleModel();

        $viewModel->setErrorLevel(1);
        $viewModel->setResult('Page not found');

        return $viewModel;
    }
}
