<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\Http\Response;
use Laminas\Json\Json as JsonFormatter;

/**
 * Helper for simplifying JSON responses
 */
class Json extends AbstractHelper
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * Encode data as JSON and set response header
     *
     * @param  mixed $data
     * @param  array $jsonOptions Options to pass to JsonFormatter::encode()
     * @return string|void
     */
    public function __invoke($data, array $jsonOptions = [])
    {
        $data = JsonFormatter::encode($data, null, $jsonOptions);

        if ($this->response instanceof Response) {
            $headers = $this->response->getHeaders();
            $headers->addHeaderLine('Content-Type', 'application/json');
        }

        return $data;
    }

    /**
     * Set the response object
     *
     * @param  Response $response
     * @return Json
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }
}
