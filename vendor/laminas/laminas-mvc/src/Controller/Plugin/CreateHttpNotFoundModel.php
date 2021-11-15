<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Controller\Plugin;

use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

class CreateHttpNotFoundModel extends AbstractPlugin
{
    /**
     * Create an HTTP view model representing a "not found" page
     *
     * @param  Response $response
     *
     * @return ViewModel
     */
    public function __invoke(Response $response)
    {
        $response->setStatusCode(404);

        return new ViewModel(['content' => 'Page not found']);
    }
}
