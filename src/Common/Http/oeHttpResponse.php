<?php

/**
 * Rest Response
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

class oeHttpResponse
{
    public function __construct($response)
    {
        $this->response = $response;
    }

    public function body()
    {
        return (string)$this->response->getBody();
    }

    public function json($asArray = true)
    {
        return json_decode($this->response->getBody(), $asArray);
    }

    public function header($header, $asArray = false)
    {
        return $this->response->getHeader($header, $asArray);
    }

    public function headers()
    {
        return $this->response->getHeaders();
    }

    public function status()
    {
        return $this->response->getStatusCode();
    }

    public function __call($method, $args)
    {
        return $this->response->{$method}(...$args);
    }
}
