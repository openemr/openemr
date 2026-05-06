<?php

/**
 * PSR-18 HTTP client that closes the DNS-rebinding / TOCTOU window in
 * the OIDC URL-fetching path.
 *
 * The plain `OidcUrlValidator::validateDiscoveryUrl()` /
 * `validateJwksUri()` flow resolves DNS at validation time, but the
 * subsequent HTTP request issued by the inner Guzzle client performs
 * its own DNS lookup at connect time — between those two lookups an
 * attacker who controls authoritative DNS for the host can flip the
 * answer from a public IP (passes validation) to 127.0.0.1 /
 * 169.254.169.254 / RFC1918 (the actual connection lands on internal
 * services).
 *
 * This wrapper collapses the window:
 *  1. Extract host + port from the request URI.
 *  2. Resolve DNS once via the validator (single source of truth).
 *  3. Validate every resolved IP against the privacy policy.
 *  4. Dispatch via the inner Guzzle client with `CURLOPT_RESOLVE`
 *     pinning the connection to the validated IPs.
 *  5. Disable HTTP redirects so a 3xx response can't smuggle the
 *     connection over to a freshly-resolved (and potentially
 *     attacker-rebound) host.
 *
 * Validation, resolution, and connection are now wired together: cURL
 * uses exactly the IPs the validator approved, never re-resolves the
 * hostname mid-request, and the original Host header / TLS SNI travel
 * unchanged so certificate verification remains correct.
 *
 * Used to wrap any Guzzle client that issues outbound HTTP for OIDC
 * discovery, JWKS fetches, or any other path where an admin-typed or
 * client-registered URL becomes the request target.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Discovery;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final readonly class SsrfSafeHttpClient implements ClientInterface
{
    private const HTTP_DEFAULT_PORT = 80;
    private const HTTPS_DEFAULT_PORT = 443;

    /**
     * @param GuzzleClientInterface $inner Guzzle client to dispatch through.
     *   Must be Guzzle (not a generic PSR-18 client) because the cURL
     *   `resolve` option used to pin the connection is Guzzle-specific.
     * @param OidcHostResolverInterface $resolver Source of DNS resolution
     *   and the privacy policy. Production wires this with
     *   {@see OidcUrlValidator} (strict — https-only, no private/loopback;
     *   relaxed in dev for docker mock services). Note: scheme/userinfo
     *   checks are NOT applied here — they belong on the *configured* URL
     *   (issuer, jwks_uri) at admin-config or DCR-storage time. This wrapper
     *   only addresses the resolve-vs-connect race for whatever URL the
     *   caller already accepted.
     */
    public function __construct(
        private GuzzleClientInterface $inner,
        private OidcHostResolverInterface $resolver,
        private LoggerInterface|NullLogger $logger = new NullLogger(),
    ) {
    }

    /**
     * @throws ClientExceptionInterface PSR-18 contract: any failure
     *   (validator block, transport error, malformed response) becomes
     *   an exception implementing this interface so existing PSR-18
     *   call sites handle it uniformly.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();
        $host = $uri->getHost();
        if ($host === '') {
            throw new SsrfBlockedException('Outbound request URI has no host');
        }

        $scheme = strtolower($uri->getScheme());
        $port = $uri->getPort() ?? ($scheme === 'https' ? self::HTTPS_DEFAULT_PORT : self::HTTP_DEFAULT_PORT);

        try {
            $resolvedIps = $this->resolver->resolveAndAssert($host);
        } catch (OidcUrlValidationException $exception) {
            // Block before any network I/O. Logged at warning so operators
            // can see attempted SSRF attempts, but the exception payload
            // exposes only the validator's reason — never the resolved IPs.
            $this->logger->warning(
                'SsrfSafeHttpClient blocked outbound request',
                ['host' => $host, 'reason' => $exception->getMessage()],
            );
            throw new SsrfBlockedException(
                "Blocked outbound request to {$host}: " . $exception->getMessage(),
                0,
                $exception,
            );
        }

        // CURLOPT_RESOLVE syntax: ["host:port:ip1,ip2,..."]. cURL connects
        // to one of the listed IPs and never consults the system resolver
        // for this hostname — no second DNS lookup, no rebinding window.
        $resolveEntry = sprintf('%s:%d:%s', $host, $port, implode(',', $resolvedIps));

        $options = [
            'curl' => [\CURLOPT_RESOLVE => [$resolveEntry]],
            // Disable redirect-following: a 3xx pointing at a different
            // hostname would trigger fresh DNS resolution by Guzzle/cURL,
            // re-opening the rebinding window for the new host. OIDC
            // discovery and JWKS endpoints are canonical URLs; redirects
            // are not part of the spec for either.
            'allow_redirects' => false,
        ];

        try {
            return $this->inner->send($request, $options);
        } catch (GuzzleException $exception) {
            // Translate Guzzle's exception hierarchy into PSR-18.
            // GuzzleException doesn't extend Throwable's PSR-18
            // counterpart, so we wrap explicitly.
            throw $this->translateGuzzleException($exception, $request);
        } catch (Throwable $exception) {
            // Defensive: any other exception (e.g. cURL extension issues)
            // still has to satisfy PSR-18's contract.
            throw new SsrfBlockedException(
                'Outbound request failed: ' . $exception->getMessage(),
                0,
                $exception,
            );
        }
    }

    private function translateGuzzleException(
        GuzzleException $exception,
        RequestInterface $request,
    ): ClientExceptionInterface {
        // Guzzle's ConnectException is a network-layer failure (DNS,
        // refused connection, timeout) — map to NetworkExceptionInterface.
        // RequestException covers request/response problems — map to
        // RequestExceptionInterface. Both PSR-18 sub-interfaces require a
        // RequestInterface accessor, so we use small anonymous classes.
        if ($exception instanceof ConnectException) {
            return new class ($exception->getMessage(), $request, $exception) extends \RuntimeException implements NetworkExceptionInterface {
                public function __construct(
                    string $message,
                    private readonly RequestInterface $request,
                    Throwable $previous,
                ) {
                    parent::__construct($message, 0, $previous);
                }
                public function getRequest(): RequestInterface
                {
                    return $this->request;
                }
            };
        }

        return new class ($exception->getMessage(), $request, $exception) extends \RuntimeException implements RequestExceptionInterface {
            public function __construct(
                string $message,
                private readonly RequestInterface $request,
                Throwable $previous,
            ) {
                parent::__construct($message, 0, $previous);
            }
            public function getRequest(): RequestInterface
            {
                return $this->request;
            }
        };
    }
}
