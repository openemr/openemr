<?php

/**
 * Minimal PSR-18 HTTP client fake for testing OidcDiscoveryClient.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Discovery;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class FakeHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    private array $requests = [];

    private ResponseInterface $nextResponse;
    private ?\Throwable $nextException = null;

    public function __construct()
    {
        $this->nextResponse = new Response(200, [], '{}');
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        if ($this->nextException !== null) {
            throw $this->nextException;
        }

        return $this->nextResponse;
    }

    public function setNextResponse(int $statusCode, string $body): void
    {
        $this->nextResponse = new Response($statusCode, ['Content-Type' => 'application/json'], $body);
        $this->nextException = null;
    }

    public function setNextException(\Throwable $exception): void
    {
        $this->nextException = $exception;
    }

    /**
     * @return list<RequestInterface>
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getLastRequestUri(): ?string
    {
        $last = end($this->requests);
        return $last !== false ? (string) $last->getUri() : null;
    }

    public function getRequestCount(): int
    {
        return count($this->requests);
    }
}
