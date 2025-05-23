<?php

namespace OpenEMR\Common\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use OpenEMR\Common\System\System;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Process\Process;

abstract class HttpClient
{
    protected $system;

    /**
     * Path to the service's index.js or server.js on the file system
     *
     * @var string
     */
    protected $servicePath;

    /**
     * @var string
     */
    protected $apiUri;

    protected $port;

    /**
     * @var Client
     */
    protected $client;

    /**
     * CqmClient constructor.
     *
     * @param string $apiUri
     * @param Client|null $client
     */
    public function __construct(
        System $system,
        string $servicePath,
        string $apiUri = 'http://localhost',
        string $port = '6660'
    ) {
        $this->system = $system;
        $this->servicePath = $servicePath;
        $this->apiUri = $apiUri;
        $this->port = $port;
        $this->client = new Client();
    }

    abstract protected function getCommand();

    protected function url()
    {
        return $this->apiUri . ":" . $this->port;
    }

    public function start()
    {
        return $this->system->run_node_background_process($this->getCommand());
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $options
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request(string $method, string $path, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $this->url() . $path, $options);
    }
}
