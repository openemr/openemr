<?php

/**
 * Rest Response
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

class oeHttpResponse
{
    public function __construct(
        private readonly object $response
    )
    {
    }

    public function body(): string
    {
        return (string)$this->response->getBody();
    }

    public function json(bool $asArray = true): mixed
    {
        return json_decode((string) $this->response->getBody(), $asArray);
    }

    /**
     * @return string[]
     */
    public function header(string $header): array
    {
        return $this->response->getHeader($header);
    }

    /**
     * @return array<string, string[]>
     */
    public function headers(): array
    {
        return $this->response->getHeaders();
    }

    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * @param array<int, mixed> $args
     */
    public function __call(string $method, array $args): mixed
    {
        return $this->response->{$method}(...$args);
    }
}
